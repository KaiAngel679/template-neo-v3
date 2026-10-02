<?php

namespace app\modules\module_page_atools\ext\Repositories;

class ExperienceRepository
{
    protected $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function isConnected(): bool
    {
        return !empty($this->Db->db_data['LevelsRanks']);
    }

    public function buildStatsKey(array $entry): string
    {
        return sprintf(
            '%s;%d;%d;%s',
            $entry['DB_mod'],
            $entry['USER_ID'],
            $entry['DB_num'],
            $entry['Table']
        );
    }

    public function resolveTables(string $statsKey): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        if ($statsKey === '' || $statsKey === '-1') {
            return $this->Db->db_data['LevelsRanks'];
        }

        foreach ($this->Db->db_data['LevelsRanks'] as $entry) {
            if ($this->buildStatsKey($entry) === $statsKey) {
                return [$entry];
            }
        }

        return [];
    }

    public function countPlayers(array $tables, string $searchSteam, string $searchName): int
    {
        $total = 0;
        foreach ($tables as $entry) {
            $total += $this->countPlayersInTable($entry, $searchSteam, $searchName);
        }

        return $total;
    }

    public function fetchPlayers(array $tables, string $searchSteam, string $searchName, string $sort, int $limit, int $offset): array
    {
        if ($tables === []) {
            return [];
        }

        if (count($tables) === 1) {
            return $this->fetchPlayersFromTable($tables[0], $searchSteam, $searchName, $sort, $limit, $offset);
        }

        $rows = [];
        foreach ($tables as $entry) {
            $rows = array_merge(
                $rows,
                $this->fetchPlayersFromTable($entry, $searchSteam, $searchName, $sort, PHP_INT_MAX, 0)
            );
        }

        usort($rows, static function (array $a, array $b) use ($sort): int {
            $cmp = ($a['value'] ?? 0) <=> ($b['value'] ?? 0);
            if ($cmp === 0) {
                return strcmp((string) ($a['steam'] ?? ''), (string) ($b['steam'] ?? ''));
            }

            return $sort === 'up' ? $cmp : -$cmp;
        });

        return array_slice($rows, $offset, $limit);
    }

    public function getPlayerByStatsKey(string $statsKey, string $steam): ?array
    {
        $tables = $this->resolveTables($statsKey);
        if ($tables === [] || $steam === '') {
            return null;
        }

        return $this->getPlayerInTable($tables[0], $steam);
    }

    public function getPlayerByServerStats(string $serverStats, string $steam): ?array
    {
        $parts = explode(';', $serverStats, 4);
        if (count($parts) !== 4 || $parts[0] !== 'LevelsRanks' || $steam === '') {
            return null;
        }

        $row = $this->Db->query(
            'LevelsRanks',
            (int) $parts[1],
            (int) $parts[2],
            'SELECT * FROM `' . $parts[3] . '` WHERE `steam` = ? LIMIT 1',
            [$steam]
        );

        return is_array($row) && $row !== [] ? $row : null;
    }

    public function updateValue(string $statsKey, string $steam, int $value): bool
    {
        $tables = $this->resolveTables($statsKey);
        if ($tables === [] || $steam === '') {
            return false;
        }

        $entry = $tables[0];
        $this->Db->query(
            'LevelsRanks',
            $entry['USER_ID'],
            $entry['DB_num'],
            'UPDATE `' . $entry['Table'] . '` SET `value` = ? WHERE `steam` = ?',
            [$value, $steam]
        );

        return true;
    }

    public function resetPlayers(array $players): int
    {
        $affected = 0;
        foreach ($players as $player) {
            $statsKey = (string) ($player['stats_key'] ?? '');
            $steam = (string) ($player['steam'] ?? '');
            $tables = $this->resolveTables($statsKey);
            if ($tables === [] || $steam === '') {
                continue;
            }

            $entry = $tables[0];
            $this->Db->query(
                'LevelsRanks',
                $entry['USER_ID'],
                $entry['DB_num'],
                'UPDATE `' . $entry['Table'] . '` SET `value` = 0, `rank` = 0 WHERE `steam` = ?',
                [$steam]
            );
            $affected++;
        }

        return $affected;
    }

    public function wipeAllStats(): void
    {
        $this->wipeStats($this->resolveTables('-1'));
    }

    public function wipeStats(array $tables): void
    {
        if (!$this->isConnected() || $tables === []) {
            return;
        }

        foreach ($tables as $entry) {
            $this->Db->queryAll(
                'LevelsRanks',
                $entry['USER_ID'],
                $entry['DB_num'],
                'UPDATE `' . $entry['Table'] . '` SET `value` = 0, `rank` = 0, `kills` = 0, `deaths` = 0, `shoots` = 0, `hits` = 0, `headshots` = 0, `assists` = 0, `round_win` = 0, `round_lose` = 0'
            );
        }
    }

    public function deleteEmptyPlayers(): void
    {
        $this->deleteEmptyPlayersInTables($this->resolveTables('-1'));
    }

    public function deleteEmptyPlayersInTables(array $tables): void
    {
        if (!$this->isConnected() || $tables === []) {
            return;
        }

        $sql = 'DELETE FROM `%s` WHERE `value` = 0 AND `rank` = 0 AND `kills` = 0 AND `deaths` = 0 AND `hits` = 0 AND `headshots` = 0 AND `assists` = 0 AND `round_win` = 0 AND `round_lose` = 0 AND `playtime` = 0';

        foreach ($tables as $entry) {
            $this->Db->queryAll(
                'LevelsRanks',
                $entry['USER_ID'],
                $entry['DB_num'],
                sprintf($sql, $entry['Table'])
            );
        }
    }

    private function countPlayersInTable(array $entry, string $searchSteam, string $searchName): int
    {
        [$whereSql, $params] = $this->buildSearchWhere($searchSteam, $searchName);
        $row = $this->Db->query(
            'LevelsRanks',
            $entry['USER_ID'],
            $entry['DB_num'],
            'SELECT COUNT(*) AS total FROM `' . $entry['Table'] . '`' . $whereSql,
            $params
        );

        return (int) ($row['total'] ?? 0);
    }

    private function fetchPlayersFromTable(array $entry, string $searchSteam, string $searchName, string $sort, int $limit, int $offset): array
    {
        [$whereSql, $params] = $this->buildSearchWhere($searchSteam, $searchName);
        $order = $sort === 'up' ? 'ASC' : 'DESC';
        $sql = 'SELECT `steam`, `name`, `value`, `rank`, `kills`, `deaths`, `shoots`, `hits`, `headshots`, `playtime`, `lastconnect`
                FROM `' . $entry['Table'] . '`' . $whereSql . " ORDER BY `value` {$order}, `steam` ASC LIMIT {$limit} OFFSET {$offset}";

        $rows = $this->Db->queryAll('LevelsRanks', $entry['USER_ID'], $entry['DB_num'], $sql, $params);
        if (!is_array($rows)) {
            return [];
        }

        $statsKey = $this->buildStatsKey($entry);
        $statsName = (string) ($entry['name'] ?? '');

        foreach ($rows as &$row) {
            $row['stats_key'] = $statsKey;
            $row['stats_name'] = $statsName;
        }
        unset($row);

        return $rows;
    }

    private function getPlayerInTable(array $entry, string $steam): ?array
    {
        $row = $this->Db->query(
            'LevelsRanks',
            $entry['USER_ID'],
            $entry['DB_num'],
            'SELECT * FROM `' . $entry['Table'] . '` WHERE `steam` = ? LIMIT 1',
            [$steam]
        );

        return is_array($row) && $row !== [] ? $row : null;
    }

    private function buildSearchWhere(string $searchSteam, string $searchName): array
    {
        $where = [];
        $params = [];

        if ($searchSteam !== '') {
            $where[] = '`steam` = ?';
            $params[] = $searchSteam;
        }
        if ($searchName !== '') {
            $where[] = '`name` LIKE ?';
            $params[] = '%' . $searchName . '%';
        }

        if ($where === []) {
            return ['', []];
        }

        return [' WHERE ' . implode(' OR ', $where), $params];
    }
}
