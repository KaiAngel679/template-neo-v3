<?php

namespace app\modules\module_page_atools\ext\Repositories\AdminDrivers;

use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\RepositoryHelper;

trait PunishmentTrait
{
    private function punishmentSearchWhere(string $search, string $nameCol, string $steamCol, bool $steam32, array &$params): string
    {
        $search = trim($search);
        if ($search == '') {
            return '';
        }

        $steam = $steam32 ? ModuleHelper::toSteam32($search) : ModuleHelper::toSteam64($search);
        $params[] = '%' . $search . '%';
        $params[] = '%' . $steam . '%';

        return " AND ({$nameCol} LIKE ? OR {$steamCol} LIKE ?)";
    }

    private function punishmentAdminWhere(int $adminId, string $adminCol, array &$params): string
    {
        if ($adminId == -1) {
            return '';
        }

        $params[] = $adminId;

        return " AND {$adminCol} = ?";
    }

    private function punishmentDateWhere(string $dateFrom, string $dateTo, string $createdCol, array &$params): string
    {
        $sql = '';

        if ($dateFrom !== '') {
            $params[] = strtotime($dateFrom . ' 00:00:00');
            $sql .= " AND {$createdCol} >= ?";
        }

        if ($dateTo !== '') {
            $params[] = strtotime($dateTo . ' 23:59:59');
            $sql .= " AND {$createdCol} <= ?";
        }

        return $sql;
    }

    private function punishmentServersHaving(array $servers, string $serverCol, bool $allowNull, int $globalId, array &$params): string
    {
        if (in_array(-1, $servers, true) || $servers == []) {
            return '';
        }

        $conditions = [];
        foreach ($servers as $serverId) {
            $params[] = $serverId;
            $conditions[] = "{$serverCol} = ?";
        }
        if ($globalId >= 0) {
            $conditions[] = "{$serverCol} = {$globalId}";
        }
        if ($allowNull) {
            $conditions[] = "{$serverCol} IS NULL";
        }

        $match = implode(' OR ', $conditions);

        return " HAVING SUM(CASE WHEN {$match} THEN 1 ELSE 0 END) > 0";
    }

    protected function punishmentBindingToAdminSystemServerId(int $bindingId): int
    {
        return $bindingId;
    }

    protected function punishmentBindingToIksServerId(int $bindingId): ?int
    {
        return $bindingId === -1 ? null : $bindingId;
    }

    protected function punishmentIksServerIdToBinding($serverId): int
    {
        return $serverId === null ? -1 : (int) $serverId;
    }

    protected function punishmentBindingToSourceBansSid(int $bindingId): int
    {
        return $bindingId === -1 ? 0 : $bindingId;
    }

    protected function punishmentSourceBansSidToBinding($sid): int
    {
        return (int) $sid === 0 ? -1 : (int) $sid;
    }

    private function fetchPunishmentOffenderSteamids(array $ids, string $dbKey, array $queries): array
    {
        $ids = RepositoryHelper::uniquePositiveIntIds($ids);
        if ($ids === []) {
            return [];
        }

        $placeholders = RepositoryHelper::inPlaceholders(count($ids));
        $steamids = [];
        foreach ($queries as $query) {
            $rows = $this->Db->queryAll($dbKey, 0, 0, sprintf($query, $placeholders), $ids);
            foreach ($rows as $row) {
                if (!empty($row['steamid'])) {
                    $steamids[] = $row['steamid'];
                }
            }
        }

        return array_values(array_unique($steamids));
    }
}
