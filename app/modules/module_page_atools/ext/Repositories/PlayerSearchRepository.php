<?php

namespace app\modules\module_page_atools\ext\Repositories;

use app\modules\module_page_atools\ext\ModuleHelper;

class PlayerSearchRepository
{
    private const CANDIDATE_LIMIT = 10;
    private const REPORTS_PREVIEW_LIMIT = 2;

    protected $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function isReportsConnected(): bool
    {
        return !empty($this->Db->db_data['Reports']);
    }

    public function resolveSteam64(string $query): string
    {
        $query = trim($query);
        if ($query === '') {
            return '';
        }

        $steam64 = ModuleHelper::toSteam64($query);
        if (preg_match('/^7656119\d{10}$/', $steam64)) {
            return $steam64;
        }

        $steam32 = ModuleHelper::toSteam32($query);
        if (preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $steam32)) {
            $from32 = con_steam64($steam32);

            return is_string($from32) && preg_match('/^7656119\d{10}$/', $from32) ? $from32 : '';
        }

        if (ctype_digit($query)) {
            $fromDigits = con_steam64($query);
            if (is_string($fromDigits) && preg_match('/^7656119\d{10}$/', $fromDigits)) {
                return $fromDigits;
            }
        }

        return '';
    }

    public function findCandidatesByNickname(string $name): array
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) < 2) {
            return [];
        }

        $candidates = [];
        $like = '%' . $name . '%';

        if (!empty($this->Db->db_data['lk'])) {
            $rows = $this->Db->queryAll(
                'lk',
                0,
                0,
                'SELECT `auth`, `name` FROM `lk` WHERE `name` LIKE ? LIMIT 5',
                [$like]
            );
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $this->pushCandidate($candidates, (string) ($row['auth'] ?? ''), (string) ($row['name'] ?? ''));
                }
            }
        }

        if ($this->isReportsConnected()) {
            $rows = $this->Db->queryAll(
                'Reports',
                0,
                0,
                'SELECT DISTINCT `steamid_intruder`, `name_intruder`
                 FROM `rs_reports`
                 WHERE `name_intruder` LIKE ?
                 LIMIT 5',
                [$like]
            );
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $this->pushCandidate(
                        $candidates,
                        (string) ($row['steamid_intruder'] ?? ''),
                        (string) ($row['name_intruder'] ?? '')
                    );
                }
            }
        }

        return array_values($candidates);
    }

    public function countReportsForPlayer(string $steam64): int
    {
        if (!$this->isReportsConnected() || $steam64 === '') {
            return 0;
        }

        $count = $this->Db->queryNum(
            'Reports',
            0,
            0,
            'SELECT COUNT(*) FROM `rs_reports` WHERE `steamid_intruder` = ?',
            [$steam64]
        );

        return (int) ($count[0] ?? 0);
    }

    public function fetchReportsForPlayer(string $steam64, int $limit = self::REPORTS_PREVIEW_LIMIT): array
    {
        if (!$this->isReportsConnected() || $steam64 === '') {
            return [];
        }

        $limit = max(1, min(20, $limit));
        $rows = $this->Db->queryAll(
            'Reports',
            0,
            0,
            'SELECT `id`, `sid`, `time`, `status`, `verdict`, `reason`, `kills`, `deaths`,
                    `name_intruder`, `steamid_intruder`, `name_admin_verdict`, `steamid_admin_verdict`, `time_verdict`
             FROM `rs_reports`
             WHERE `steamid_intruder` = ?
             ORDER BY `time` DESC
             LIMIT ' . (int) $limit,
            [$steam64]
        );

        return is_array($rows) ? $rows : [];
    }

    private function pushCandidate(array &$candidates, string $steamInput, string $name): void
    {
        if (count($candidates) >= self::CANDIDATE_LIMIT) {
            return;
        }

        $steam64 = $this->resolveSteam64($steamInput);
        if ($steam64 === '' || isset($candidates[$steam64])) {
            return;
        }

        $candidates[$steam64] = [
            'steamid' => $steam64,
            'name' => trim($name),
        ];
    }
}
