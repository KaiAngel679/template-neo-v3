<?php

namespace app\modules\module_page_atools\ext\Repositories\AdminDrivers;

use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\RepositoryHelper;

trait CheckTrait
{
    private function buildStatsChecksQuery(array $filters, bool $count = false): array
    {
        $params = [];
        $where = 'WHERE 1=1';
        $where .= $this->checkServersWhere($filters['servers'] ?? [-1], 'server_id', $params);
        $where .= $this->checkAdminWhereStats($filters, $params);
        $where .= $this->checkVerdictWhereStats($filters['verdict'] ?? 'all', $params);
        $where .= $this->checkDateWhere($filters['date_from'] ?? '', $filters['date_to'] ?? '', 'datestart', $params);
        $where .= $this->checkSearchWhereStats($filters['search'] ?? '', $params);

        if ($count) {
            return ["SELECT COUNT(*) AS total FROM `checkcheats_stats` {$where}", $params];
        }

        return ["SELECT * FROM `checkcheats_stats` {$where}", $params];
    }

    private function buildIksChecksQuery(array $filters, bool $count = false): array
    {
        $params = [];
        $where = 'WHERE 1=1';
        $where .= $this->checkServersWhere($filters['servers'] ?? [-1], 'cr.server_id', $params);
        $where .= $this->checkAdminWhereIks($filters['admin'] ?? -1, $params);
        $where .= $this->checkVerdictWhereIks($filters['verdict'] ?? 'all', $params);
        $where .= $this->checkDateWhere($filters['date_from'] ?? '', $filters['date_to'] ?? '', 'cr.created_at', $params);
        $where .= $this->checkSearchWhereIks($filters['search'] ?? '', $params);

        if ($count) {
            return [
                "SELECT COUNT(*) AS total
                 FROM iks_check_results cr
                 JOIN iks_admins a ON cr.admin_id = a.id
                 {$where}",
                $params,
            ];
        }

        return [
            "SELECT
                cr.*,
                cr.steam_id AS player_steamid,
                cr.name AS player_name,
                cr.created_at AS datestart,
                cr.result_reason AS verdict,
                a.steam_id AS admin_steamid,
                a.name AS admin_name
             FROM iks_check_results cr
             JOIN iks_admins a ON cr.admin_id = a.id
             {$where}",
            $params,
        ];
    }

    private function checkServersWhere(array $servers, string $column, array &$params): string
    {
        if (in_array(-1, $servers, true) || $servers == []) {
            return '';
        }

        $placeholders = implode(',', array_fill(0, count($servers), '?'));
        foreach ($servers as $serverId) {
            $params[] = $serverId;
        }

        return " AND {$column} IN ({$placeholders})";
    }

    private function checkAdminWhereStats(array $filters, array &$params): string
    {
        if ((int) ($filters['admin'] ?? -1) == -1) {
            return '';
        }

        if (!empty($filters['admin_steamid'])) {
            $params[] = $filters['admin_steamid'];
            $params[] = con_steam32($filters['admin_steamid']);

            return ' AND (`admin_steamid` = ? OR `admin_steamid` = ?)';
        }

        return '';
    }

    private function checkAdminWhereIks(int $adminId, array &$params): string
    {
        if ($adminId == -1) {
            return '';
        }

        $params[] = $adminId;

        return ' AND cr.admin_id = ?';
    }

    private function checkVerdictWhereStats(string $verdict, array &$params): string
    {
        if ($verdict == 'all' || $verdict == '-1' || $verdict == '') {
            return '';
        }

        $params[] = $verdict;

        return ' AND `verdict` = ?';
    }

    private function checkVerdictWhereIks(string $verdict, array &$params): string
    {
        if ($verdict == 'all' || $verdict == '-1' || $verdict == '') {
            return '';
        }

        if (is_numeric($verdict) && (int) $verdict >= 0 && (int) $verdict <= 5) {
            $params[] = (int) $verdict;

            return ' AND cr.check_result = ?';
        }

        $params[] = $verdict;

        return ' AND cr.result_reason = ?';
    }

    private function checkDateWhere(string $dateFrom, string $dateTo, string $column, array &$params): string
    {
        $sql = '';

        if ($dateFrom != '') {
            $params[] = strtotime($dateFrom . ' 00:00:00');
            $sql .= " AND {$column} >= ?";
        }

        if ($dateTo != '') {
            $params[] = strtotime($dateTo . ' 23:59:59');
            $sql .= " AND {$column} <= ?";
        }

        return $sql;
    }

    private function checkSearchWhereStats(string $search, array &$params): string
    {
        $search = trim($search);
        if ($search == '') {
            return '';
        }

        $steam64 = ModuleHelper::toSteam64($search);
        $steam32 = ModuleHelper::toSteam32($search);
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $steam64;
        $params[] = $steam32;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;

        return ' AND (`player_steamid` LIKE ? OR `player_name` LIKE ? OR `player_steamid` = ? OR `player_steamid` = ? OR `admin_steamid` LIKE ? OR `admin_name` LIKE ? OR `suspect_discord` LIKE ?)';
    }

    private function checkSearchWhereIks(string $search, array &$params): string
    {
        $search = trim($search);
        if ($search == '') {
            return '';
        }

        $steam64 = ModuleHelper::toSteam64($search);
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $steam64;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;

        return ' AND (cr.steam_id LIKE ? OR cr.name LIKE ? OR cr.steam_id = ? OR a.steam_id LIKE ? OR a.name LIKE ? OR cr.discord LIKE ?)';
    }

    private function buildStatsChecksByIdsQuery(array $ids): array
    {
        $ids = RepositoryHelper::uniquePositiveIntIds($ids);
        if ($ids === []) {
            return ['', []];
        }

        $placeholders = RepositoryHelper::inPlaceholders(count($ids));

        return ["SELECT * FROM `checkcheats_stats` WHERE id IN ({$placeholders})", $ids];
    }

    private function buildIksChecksByIdsQuery(array $ids): array
    {
        $ids = RepositoryHelper::uniquePositiveIntIds($ids);
        if ($ids === []) {
            return ['', []];
        }

        $placeholders = RepositoryHelper::inPlaceholders(count($ids));

        return [
            "SELECT
                cr.*,
                cr.steam_id AS player_steamid,
                cr.name AS player_name,
                cr.created_at AS datestart,
                cr.result_reason AS verdict,
                a.steam_id AS admin_steamid,
                a.name AS admin_name
             FROM iks_check_results cr
             JOIN iks_admins a ON cr.admin_id = a.id
             WHERE cr.id IN ({$placeholders})",
            $ids,
        ];
    }
}
