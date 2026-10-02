<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\ModuleHelper;

class PlaytimeService
{
    private $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function getHoursForSteam(string $steamid64): int
    {
        $steamid64 = ModuleHelper::toSteam64($steamid64);
        if ($steamid64 === '' || empty($this->Db->db_data['LevelsRanks'])) {
            return 0;
        }

        $steam32 = ModuleHelper::toSteam32($steamid64);
        $steamShort = $this->steam32Short($steam32);
        $steamVariants = $this->steam32Variants($steam32);

        $totalSeconds = 0;

        foreach ($this->Db->db_data['LevelsRanks'] as $entry) {
            $table = (string) ($entry['Table'] ?? '');
            if ($table === '') {
                continue;
            }

            $seconds = $this->fetchPlaytimeSeconds(
                (int) $entry['USER_ID'],
                (int) $entry['DB_num'],
                $table,
                (int) ($entry['steam'] ?? 1),
                $steamid64,
                $steamVariants,
                $steamShort
            );
            $totalSeconds += $seconds;
        }

        return (int) floor($totalSeconds / 3600);
    }

    private function fetchPlaytimeSeconds(
        int $userId,
        int $dbNum,
        string $table,
        int $steamMode,
        string $steam64,
        array $steam32Variants,
        ?string $steamShort
    ): int {

        if ($steamMode === 0 && $steam64 !== '') {
            $row = $this->Db->query(
                'LevelsRanks',
                $userId,
                $dbNum,
                'SELECT `playtime` FROM `' . $table . '` WHERE `steam` = ? LIMIT 1',
                [$steam64]
            );
            if (is_array($row) && $row !== []) {
                return max(0, (int) ($row['playtime'] ?? 0));
            }
        }

        if ($steam32Variants !== []) {
            $placeholders = implode(',', array_fill(0, count($steam32Variants), '?'));
            $row = $this->Db->query(
                'LevelsRanks',
                $userId,
                $dbNum,
                'SELECT `playtime` FROM `' . $table . '` WHERE `steam` IN (' . $placeholders . ') LIMIT 1',
                $steam32Variants
            );
            if (is_array($row) && $row !== []) {
                return max(0, (int) ($row['playtime'] ?? 0));
            }
        }

        if ($steamShort !== null && $steamShort !== '') {
            $row = $this->Db->query(
                'LevelsRanks',
                $userId,
                $dbNum,
                'SELECT `playtime` FROM `' . $table . '` WHERE `steam` LIKE ? LIMIT 1',
                ['%' . $steamShort]
            );
            if (is_array($row) && $row !== []) {
                return max(0, (int) ($row['playtime'] ?? 0));
            }
        }

        if ($steamMode !== 0 && $steam64 !== '') {
            $row = $this->Db->query(
                'LevelsRanks',
                $userId,
                $dbNum,
                'SELECT `playtime` FROM `' . $table . '` WHERE `steam` = ? LIMIT 1',
                [$steam64]
            );
            if (is_array($row) && $row !== []) {
                return max(0, (int) ($row['playtime'] ?? 0));
            }
        }

        return 0;
    }

    private function steam32Short(string $steam32): ?string
    {
        if (preg_match('/^STEAM_[01]:([01]:\d+)$/i', $steam32, $m)) {
            return $m[1];
        }

        return null;
    }

    private function steam32Variants(string $steam32): array
    {
        if (!preg_match('/^STEAM_[01]:([01]:\d+)$/i', $steam32, $m)) {
            return $steam32 !== '' && $steam32 !== '0' ? [$steam32] : [];
        }

        $tail = $m[1];

        return array_values(array_unique([
            'STEAM_1:' . $tail,
            'STEAM_0:' . $tail,
        ]));
    }
}
