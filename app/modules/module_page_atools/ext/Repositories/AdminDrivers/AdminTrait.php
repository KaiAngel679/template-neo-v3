<?php

namespace app\modules\module_page_atools\ext\Repositories\AdminDrivers;

use app\modules\module_page_atools\ext\ModuleHelper;

trait AdminTrait
{
    private function appendAllowedSteamidsSql(?array $allowedSteamids, string $steamColumn, array &$params): string
    {
        if ($allowedSteamids === null) {
            return '';
        }

        if ($allowedSteamids === []) {
            return ' AND 1 = 0';
        }

        $placeholders = implode(',', array_fill(0, count($allowedSteamids), '?'));
        $params = array_merge($params, $allowedSteamids);

        return " AND {$steamColumn} IN ({$placeholders})";
    }

    private function appendCs2AdminFilters(array $servers, int $group, string $search, string $serverAlias, bool $globalServerMatchIsNull, string $steamColumn, ?array $allowedSteamids = null): array
    {
        $sql = '';
        $params = [];

        $search = ModuleHelper::toSteam64($search);

        if (!in_array(-1, $servers)) {
            $placeholders = implode(',', array_fill(0, count($servers), '?'));
            $global = $globalServerMatchIsNull
                ? "{$serverAlias}.server_id IS NULL"
                : "{$serverAlias}.server_id = -1";
            $sql .= " AND ({$serverAlias}.server_id IN ($placeholders) OR $global)";
            $params = array_merge($params, $servers);
        }

        if ($group != -1) {
            $sql .= ' AND g.id = ?';
            $params[] = $group;
        }

        if ($search !== '') {
            $sql .= " AND (a.name LIKE ? OR {$steamColumn} LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= $this->appendAllowedSteamidsSql($allowedSteamids, $steamColumn, $params);

        return [$sql, $params];
    }

    private function normalizeUpdateServerIds(array $servers): array
    {
        $ids = array_values(array_unique(array_map('intval', $servers)));

        return in_array(-1, $ids, true) ? [-1] : $ids;
    }
}
