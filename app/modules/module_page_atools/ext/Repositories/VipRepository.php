<?php

namespace app\modules\module_page_atools\ext\Repositories;

class VipRepository
{
    protected $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function isConnected(): bool
    {
        return !empty($this->Db->db_data['Vips']);
    }

    public function countUsers(string $serverVip, int $sid, ?string $excludeGroup = null, bool $foreverOnly = false): int
    {
        $conn = $this->parseServerConnection($serverVip);
        if ($conn == null || $sid <= 0) {
            return 0;
        }

        $where = ['(`expires` = 0 OR `expires` > ?)', '`sid` = ?'];
        $params = [time(), $sid];

        if ($foreverOnly) {
            $where[] = '`expires` = 0';
        }

        if ($excludeGroup != null && $excludeGroup != '') {
            $where[] = '`group` != ?';
            $params[] = $excludeGroup;
        }

        $row = $this->Db->query(
            $conn['mod'],
            $conn['user_id'],
            $conn['db_num'],
            'SELECT COUNT(*) AS total FROM `' . $conn['table'] . 'users` WHERE ' . implode(' AND ', $where),
            $params
        );

        return (int) ($row['total'] ?? 0);
    }

    public function countUsersStats(string $serverVip, int $sid, ?string $excludeGroup = null): array
    {
        $conn = $this->parseServerConnection($serverVip);
        if ($conn == null || $sid <= 0) {
            return ['total' => 0, 'forever' => 0];
        }

        $where = ['(`expires` = 0 OR `expires` > ?)', '`sid` = ?'];
        $params = [time(), $sid];

        if ($excludeGroup != null && $excludeGroup != '') {
            $where[] = '`group` != ?';
            $params[] = $excludeGroup;
        }

        $row = $this->Db->query(
            $conn['mod'],
            $conn['user_id'],
            $conn['db_num'],
            'SELECT COUNT(*) AS total, SUM(CASE WHEN `expires` = 0 THEN 1 ELSE 0 END) AS forever
                FROM `' . $conn['table'] . 'users` WHERE ' . implode(' AND ', $where),
            $params
        );

        return [
            'total' => (int) ($row['total'] ?? 0),
            'forever' => (int) ($row['forever'] ?? 0),
        ];
    }

    public function privilegeExists(string $serverVip, int $sid, int $accountId): bool
    {
        $conn = $this->parseServerConnection($serverVip);
        if ($conn == null || $sid <= 0 || $accountId <= 0) {
            return false;
        }

        $row = $this->Db->query(
            $conn['mod'],
            $conn['user_id'],
            $conn['db_num'],
            'SELECT `account_id` FROM `' . $conn['table'] . 'users` WHERE `account_id` = ? AND `sid` = ? LIMIT 1',
            [$accountId, $sid]
        );

        return is_array($row) && !empty($row['account_id']);
    }

    public function insertPrivilege(string $serverVip, int $sid, int $accountId, string $name, string $group, int $expires): void
    {
        $conn = $this->parseServerConnection($serverVip);
        if ($conn == null || $sid <= 0 || $accountId <= 0) {
            return;
        }

        $this->Db->query(
            $conn['mod'],
            $conn['user_id'],
            $conn['db_num'],
            'INSERT INTO `' . $conn['table'] . 'users` (`account_id`, `name`, `lastvisit`, `sid`, `group`, `expires`)
                VALUES (?, ?, 0, ?, ?, ?)',
            [$accountId, $name, $sid, $group, $expires]
        );
    }

    public function deletePrivilege(string $serverVip, int $sid, int $accountId): bool
    {
        $conn = $this->parseServerConnection($serverVip);
        if ($conn == null || $sid <= 0 || $accountId <= 0) {
            return false;
        }

        $this->Db->query(
            $conn['mod'],
            $conn['user_id'],
            $conn['db_num'],
            'DELETE FROM `' . $conn['table'] . 'users` WHERE `account_id` = ? AND `sid` = ?',
            [$accountId, $sid]
        );

        return true;
    }

    public function updatePrivilege(string $serverVip, int $sid, int $accountId, string $group, int $expires): bool
    {
        $conn = $this->parseServerConnection($serverVip);
        if ($conn == null || $sid <= 0 || $accountId <= 0) {
            return false;
        }

        $this->Db->query(
            $conn['mod'],
            $conn['user_id'],
            $conn['db_num'],
            'UPDATE `' . $conn['table'] . 'users` SET `group` = ?, `expires` = ? WHERE `account_id` = ? AND `sid` = ?',
            [$group, $expires, $accountId, $sid]
        );

        return true;
    }

    public function fetchUsers(string $serverVip, int $sid, string $groupIni, int $accountId, string $searchName, ?string $excludeGroup = null, string $expireFilter = 'all'): array
    {
        $conn = $this->parseServerConnection($serverVip);
        if ($conn == null || $sid <= 0) {
            return [];
        }

        $expireFilter = $expireFilter !== '' ? $expireFilter : 'all';
        if ($expireFilter === 'forever') {
            $where = ['`expires` = 0', '`sid` = ?'];
            $params = [$sid];
        } elseif ($expireFilter === 'temporary') {
            $where = ['`expires` > 0', '`expires` > ?', '`sid` = ?'];
            $params = [time(), $sid];
        } elseif ($expireFilter === 'expired') {
            $where = ['`expires` > 0', '`expires` <= ?', '`sid` = ?'];
            $params = [time(), $sid];
        } else {
            $where = ['`sid` = ?'];
            $params = [$sid];
        }

        if ($groupIni != '' && $groupIni != '-1') {
            $where[] = '`group` = ?';
            $params[] = $groupIni;
        }

        if ($excludeGroup != null && $excludeGroup != '') {
            $where[] = '`group` != ?';
            $params[] = $excludeGroup;
        }

        if ($accountId > 0) {
            $where[] = '`account_id` = ?';
            $params[] = $accountId;
        } elseif ($searchName != '') {
            $where[] = '`name` LIKE ?';
            $params[] = '%' . $searchName . '%';
        }

        $rows = $this->Db->queryAll(
            $conn['mod'],
            $conn['user_id'],
            $conn['db_num'],
            'SELECT `account_id`, `name`, `sid`, `group`, `expires`, `lastvisit`
                FROM `' . $conn['table'] . 'users`
                WHERE ' . implode(' AND ', $where),
            $params
        );

        return is_array($rows) ? $rows : [];
    }

    private function parseServerConnection(string $serverVip): ?array
    {
        $serverVip = trim($serverVip);
        if ($serverVip === '') {
            return null;
        }

        $parts = explode(';', $serverVip, 4);
        if (count($parts) < 4 || $parts[0] === '' || $parts[3] === '') {
            return null;
        }

        return [
            'mod' => $parts[0],
            'user_id' => (int) $parts[1],
            'db_num' => (int) $parts[2],
            'table' => $parts[3],
        ];
    }
}
