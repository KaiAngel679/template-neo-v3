<?php

namespace app\modules\module_page_atools\ext\Repositories\AdminDrivers;

class IksAdminDriver implements AdminDriverInterface
{
    use AdminTrait;
    use PunishmentTrait;
    use CheckTrait;

    private $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function getGroups(): array
    {
        return $this->Db->queryAll('IksAdminNew', 0, 0, 'SELECT `id`, `name`, `flags`, `immunity` FROM iks_groups ORDER BY immunity DESC');
    }

    public function createAdminGroup(string $name, int $immunity, string $flags): void
    {
        $this->Db->query(
            'IksAdminNew',
            0,
            0,
            'INSERT INTO iks_groups (`flags`, `name`, `immunity`) VALUES (?, ?, ?)',
            [$flags, $name, $immunity]
        );
    }

    public function updateAdminGroup(int $id, string $name, int $immunity, string $flags): void
    {
        $this->Db->query(
            'IksAdminNew',
            0,
            0,
            'UPDATE iks_groups SET flags = ?, name = ?, immunity = ? WHERE id = ?',
            [$flags, $name, $immunity, $id]
        );
    }

    public function deleteAdminGroup(int $id): void
    {
        $this->Db->query('IksAdminNew', 0, 0, 'DELETE FROM iks_groups WHERE id = ? LIMIT 1', [$id]);
    }

    public function issetAdmin(string $steamid): bool
    {
        $sql = 'SELECT a.id FROM iks_admins a
                INNER JOIN iks_admin_to_server ats ON a.id = ats.admin_id
                WHERE a.steam_id = ? AND a.is_disabled = 0 AND (a.deleted_at IS NULL OR a.deleted_at = 0)
                LIMIT 1';
        $result = $this->Db->query('IksAdminNew', 0, 0, $sql, [$steamid]);

        return !empty($result);
    }

    public function createAdmin(string $steamid, string $name, int $group, int $expire, array $servers): void
    {
        $expireTime = $expire > 0 ? time() + $expire : 0;
        $serverIds = $this->normalizeUpdateServerIds($servers);

        $adminId = $this->resolveAdminId($steamid)['id'];

        if (!empty($adminId)) {
            $this->Db->query(
                'IksAdminNew',
                0,
                0,
                'UPDATE iks_admins SET group_id = ?, name = ?, end_at = ?, is_disabled = 0, deleted_at = NULL, updated_at = UNIX_TIMESTAMP() WHERE id = ?',
                [$group, $name, $expireTime, $adminId]
            );
        } else {
            $this->Db->query(
                'IksAdminNew',
                0,
                0,
                'INSERT INTO iks_admins (`steam_id`, `name`, `group_id`, `end_at`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())',
                [$steamid, $name, $group, $expireTime]
            );
            $adminId = $this->Db->lastInsertId('IksAdminNew');
        }

        if ($adminId) {
            $this->syncIksAdminToServer($adminId, $serverIds);
        }
    }

    public function getAdminsList(array $servers, int $group, int $limit, int $offset, string $search, ?array $allowedSteamids = null): array
    {
        $sql = "SELECT
                    a.id,
                    a.name,
                    a.steam_id as steamid,
                    COALESCE(g.name, '') as group_name,
                    COALESCE(g.id, a.group_id) as group_id,
                    a.end_at as expires,
                    GROUP_CONCAT(DISTINCT IFNULL(ats.server_id, -1) ORDER BY IFNULL(ats.server_id, -1) SEPARATOR ',') as server_ids
                FROM iks_admins a
                INNER JOIN iks_admin_to_server ats ON a.id = ats.admin_id
                LEFT JOIN iks_groups g ON a.group_id = g.id
                WHERE a.is_disabled = 0 AND (a.deleted_at IS NULL OR a.deleted_at = 0)";

        [$where, $filterParams] = $this->appendCs2AdminFilters($servers, $group, $search, 'ats', true, 'a.steam_id', $allowedSteamids);
        $sql .= $where;
        $sql .= ' GROUP BY a.id, a.name, a.steam_id, g.name, g.id, a.group_id, a.end_at';
        $sql .= ' ORDER BY expires ASC LIMIT ? OFFSET ?';

        return $this->Db->queryAll('IksAdminNew', 0, 0, $sql, array_merge($filterParams, [$limit, $offset]));
    }

    public function getAdminsCount(array $servers, int $group, string $search, ?array $allowedSteamids = null): int
    {
        $sql = "SELECT COUNT(DISTINCT a.id) as total
                FROM iks_admins a
                INNER JOIN iks_admin_to_server ats ON a.id = ats.admin_id
                LEFT JOIN iks_groups g ON a.group_id = g.id
                WHERE a.is_disabled = 0 AND (a.deleted_at IS NULL OR a.deleted_at = 0)";

        [$where, $params] = $this->appendCs2AdminFilters($servers, $group, $search, 'ats', true, 'a.steam_id', $allowedSteamids);
        $sql .= $where;

        $result = $this->Db->query('IksAdminNew', 0, 0, $sql, $params);

        return (int) ($result['total'] ?? 0);
    }

    public function getAdminServers(int $adminId): array
    {
        return $this->Db->queryAll(
            'IksAdminNew',
            0,
            0,
            'SELECT server_id FROM iks_admin_to_server WHERE admin_id = ?',
            [$adminId]
        );
    }

    public function deleteAdmin(int $adminId): void
    {
        $this->Db->query(
            'IksAdminNew',
            0,
            0,
            'UPDATE iks_admins SET group_id = null, is_disabled = 1, deleted_at = UNIX_TIMESTAMP() WHERE id = ?',
            [$adminId]
        );
        $this->Db->query('IksAdminNew', 0, 0, 'DELETE FROM iks_admin_to_server WHERE admin_id = ?', [$adminId]);
    }

    public function getAdminById(int $adminId): ?array
    {
        $row = $this->Db->query(
            'IksAdminNew',
            0,
            0,
            'SELECT * FROM iks_admins WHERE id = ? AND is_disabled = 0 AND (deleted_at IS NULL OR deleted_at = 0)',
            [$adminId]
        );

        if (empty($row)) {
            return null;
        }

        if (!isset($row['steamid']) && isset($row['steam_id'])) {
            $row['steamid'] = $row['steam_id'];
        }

        return $row;
    }

    public function updateAdmin(int $adminId, string $group, string $expire, array $servers): void
    {
        $expireTime = $expire > 0 ? time() + $expire : 0;
        $this->Db->query(
            'IksAdminNew',
            0,
            0,
            'UPDATE iks_admins SET group_id = ?, end_at = ?, updated_at = UNIX_TIMESTAMP() WHERE id = ?',
            [$group, $expireTime, $adminId]
        );
        $this->syncIksAdminToServer($adminId, $this->normalizeUpdateServerIds($servers));
    }

    private function syncIksAdminToServer(int $adminId, array $serversInput): void
    {
        $targetIds = $this->normalizeUpdateServerIds($serversInput);

        $this->Db->query('IksAdminNew', 0, 0, 'DELETE FROM iks_admin_to_server WHERE admin_id = ?', [$adminId]);

        if (in_array(-1, $targetIds, true)) {
            $this->Db->query(
                'IksAdminNew',
                0,
                0,
                'INSERT INTO iks_admin_to_server (`admin_id`, `server_id`) VALUES (?, NULL)',
                [$adminId]
            );

            return;
        }

        foreach ($targetIds as $serverId) {
            $this->Db->query(
                'IksAdminNew',
                0,
                0,
                'INSERT INTO iks_admin_to_server (`admin_id`, `server_id`) VALUES (?, ?)',
                [$adminId, $serverId]
            );
        }
    }

    public function createPunishment(string $steamid, string $name, ?string $ip, int $type, string $reason, int $duration, array $serverIds, string $adminSteamid): void
    {
        $end = $duration > 0 ? time() + $duration : 0;
        $adminId = $this->resolveAdminId($adminSteamid)['id'];
        $servers = $this->normalizeUpdateServerIds($serverIds);
        $ipValue = $ip != null && $ip != '' ? $ip : null;

        if ($type == 0) {
            $banType = 0;
            if ($ipValue != null) {
                $banType = 1;
            }

            foreach ($servers as $serverId) {
                $this->Db->query(
                    'IksAdminNew',
                    0,
                    0,
                    'INSERT INTO iks_bans (`steam_id`, `ip`, `name`, `duration`, `reason`, `ban_type`, `server_id`, `admin_id`, `created_at`, `end_at`, `updated_at`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP(), ?, UNIX_TIMESTAMP())',
                    [$steamid, $ipValue, $name, $duration, $reason, $banType, $this->punishmentBindingToIksServerId($serverId), $adminId, $end]
                );
            }

            return;
        }

        switch ($type) {
            case 2:
                $muteType = 1;
                break;
            case 3:
                $muteType = 2;
                break;
            default:
                $muteType = 0;
                break;
        }

        foreach ($servers as $serverId) {
            $this->Db->query(
                'IksAdminNew',
                0,
                0,
                'INSERT INTO iks_comms (`steam_id`, `ip`, `name`, `duration`, `reason`, `mute_type`, `server_id`, `admin_id`, `created_at`, `end_at`, `updated_at`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP(), ?, UNIX_TIMESTAMP())',
                [$steamid, $ipValue, $name, $duration, $reason, $muteType, $this->punishmentBindingToIksServerId($serverId), $adminId, $end]
            );
        }
    }

    private function resolveAdminId(string $adminSteamid): array
    {
        return $this->Db->query(
            'IksAdminNew',
            0,
            0,
            'SELECT id FROM iks_admins WHERE steam_id = ? AND is_disabled = 0 AND (deleted_at IS NULL OR deleted_at = 0) LIMIT 1',
            [$adminSteamid]
        );
    }

    public function getListAdmins(): array
    {
        return $this->Db->queryAll(
            'IksAdminNew',
            0,
            0,
            'SELECT DISTINCT a.id, a.name, a.steam_id as steamid
                FROM iks_admins a
                INNER JOIN iks_admin_to_server ats ON a.id = ats.admin_id
                WHERE a.is_disabled = 0 AND (a.deleted_at IS NULL OR a.deleted_at = 0) AND (a.end_at IS NULL OR a.end_at = 0 OR a.end_at > UNIX_TIMESTAMP())'
        );
    }

    public function getPunishmentsList(array $filters): array
    {
        [$sql, $params] = $this->buildPunishmentsQuery($filters);
        $params[] = $filters['limit'];
        $params[] = $filters['offset'];

        return $this->Db->queryAll('IksAdminNew', 0, 0, $sql . ' ORDER BY p.created_at DESC LIMIT ? OFFSET ?', $params);
    }

    public function getPunishmentsCount(array $filters): int
    {
        [$sql, $params] = $this->buildPunishmentsQuery($filters, true);
        $row = $this->Db->query('IksAdminNew', 0, 0, $sql, $params);

        return (int) ($row['total'] ?? 0);
    }

    public function filterRemovablePunishmentIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids == []) {
            return [];
        }

        $removable = [];
        foreach ($ids as $id) {
            $row = $this->Db->query(
                'IksAdminNew',
                0,
                0,
                'SELECT end_at, unbanned_by FROM iks_bans WHERE id = ? LIMIT 1',
                [$id]
            );
            if (!$row) {
                $row = $this->Db->query(
                    'IksAdminNew',
                    0,
                    0,
                    'SELECT end_at, unbanned_by FROM iks_comms WHERE id = ? LIMIT 1',
                    [$id]
                );
            }
            if (!$row || !empty($row['unbanned_by'])) {
                continue;
            }
            if ($row['end_at'] > 0 && $row['end_at'] <= time()) {
                continue;
            }
            $removable[] = $id;
        }

        return $removable;
    }

    public function removePunishmentsByIds(array $ids, string $issuerSteamid64): void
    {
        $ids = $this->filterRemovablePunishmentIds($ids);
        if ($ids == []) {
            return;
        }

        foreach ($ids as $id) {
            $row = $this->Db->query('IksAdminNew', 0, 0, 'SELECT id, steam_id, created_at, admin_id, reason, end_at, duration, name FROM iks_bans WHERE id = ? LIMIT 1', [$id]);
            $table = 'iks_bans';
            if (!$row) {
                $row = $this->Db->query('IksAdminNew', 0, 0, 'SELECT id, steam_id, created_at, admin_id, reason, end_at, duration, name FROM iks_comms WHERE id = ? LIMIT 1', [$id]);
                $table = 'iks_comms';
            }
            if (!$row) {
                continue;
            }

            $issuer = $this->resolveAdminId($issuerSteamid64);

            $this->Db->query(
                'IksAdminNew',
                0,
                0,
                "UPDATE {$table}
                    SET unbanned_by = ?
                    WHERE steam_id = ? AND created_at = ? AND admin_id = ? AND reason = ? AND end_at = ? AND duration = ? AND name = ?",
                [$issuer['id'], $row['steam_id'], $row['created_at'], $row['admin_id'], $row['reason'], $row['end_at'], $row['duration'], $row['name']]
            );
        }
    }

    public function deletePunishmentsByIds(array $ids): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids == []) {
            return;
        }

        foreach ($ids as $id) {
            $row = $this->Db->query('IksAdminNew', 0, 0, 'SELECT id, steam_id, created_at, admin_id, reason, end_at, duration, name FROM iks_bans WHERE id = ? LIMIT 1', [$id]);
            $table = 'iks_bans';
            if (!$row) {
                $row = $this->Db->query('IksAdminNew', 0, 0, 'SELECT id, steam_id, created_at, admin_id, reason, end_at, duration, name FROM iks_comms WHERE id = ? LIMIT 1', [$id]);
                $table = 'iks_comms';
            }
            if (!$row) {
                continue;
            }

            $this->Db->query(
                'IksAdminNew',
                0,
                0,
                "DELETE FROM {$table}
                    WHERE steam_id = ? AND created_at = ? AND admin_id = ? AND reason = ? AND end_at = ? AND duration = ? AND name = ?",
                [$row['steam_id'], $row['created_at'], $row['admin_id'], $row['reason'], $row['end_at'], $row['duration'], $row['name']]
            );
        }
    }

    public function updatePunishmentById(int $id, ?string $ip, int $punishType, string $reason, int $durationSeconds, array $serverIds): bool
    {
        $row = $this->Db->query(
            'IksAdminNew',
            0,
            0,
            'SELECT id, steam_id, ip, name, created_at, admin_id, reason, end_at, duration, server_id, unbanned_by, ban_type
                FROM iks_bans WHERE id = ? LIMIT 1',
            [$id]
        );
        $table = 'iks_bans';
        $isBan = true;
        if (!$row) {
            $row = $this->Db->query(
                'IksAdminNew',
                0,
                0,
                'SELECT id, steam_id, ip, name, created_at, admin_id, reason, end_at, duration, server_id, unbanned_by, mute_type
                    FROM iks_comms WHERE id = ? LIMIT 1',
                [$id]
            );
            $table = 'iks_comms';
            $isBan = false;
        }
        if (!$row || !empty($row['unbanned_by'])) {
            return false;
        }
        if ($row['end_at'] > 0 && $row['end_at'] <= time()) {
            return false;
        }
        if ($isBan && $punishType != 0) {
            return false;
        }
        if (!$isBan && $punishType == 0) {
            return false;
        }

        $newEnd = $durationSeconds > 0 ? time() + $durationSeconds : 0;
        $ipValue = $ip != null && $ip != '' ? $ip : null;
        $servers = $this->normalizeUpdateServerIds($serverIds);
        $where = 'steam_id = ? AND created_at = ? AND admin_id = ? AND reason = ? AND end_at = ? AND duration = ? AND name = ? AND unbanned_by IS NULL';
        $whereParams = [$row['steam_id'], $row['created_at'], $row['admin_id'], $row['reason'], $row['end_at'], $row['duration'], $row['name']];

        if ($isBan) {
            $banType = $ipValue != null ? 1 : 0;
            $this->Db->query(
                'IksAdminNew',
                0,
                0,
                "UPDATE {$table} SET reason = ?, duration = ?, end_at = ?, ip = ?, ban_type = ?, updated_at = UNIX_TIMESTAMP() WHERE {$where}",
                array_merge([$reason, $durationSeconds, $newEnd, $ipValue, $banType], $whereParams)
            );
        } else {
            $muteType = $this->resolveMuteType($punishType);
            $this->Db->query(
                'IksAdminNew',
                0,
                0,
                "UPDATE {$table} SET reason = ?, duration = ?, end_at = ?, ip = ?, mute_type = ?, updated_at = UNIX_TIMESTAMP() WHERE {$where}",
                array_merge([$reason, $durationSeconds, $newEnd, $ipValue, $muteType], $whereParams)
            );
        }

        $currentRows = $this->Db->queryAll(
            'IksAdminNew',
            0,
            0,
            "SELECT id, server_id FROM {$table} WHERE {$where}",
            $whereParams
        );
        $currentServers = array_map(function ($r) {
            return $this->punishmentIksServerIdToBinding($r['server_id']);
        }, $currentRows);
        $toRemove = array_diff($currentServers, $servers);
        $toAdd = array_diff($servers, $currentServers);

        foreach ($toRemove as $serverId) {
            if ($serverId == -1) {
                $this->Db->query(
                    'IksAdminNew',
                    0,
                    0,
                    "DELETE FROM {$table} WHERE {$where} AND server_id IS NULL",
                    $whereParams
                );
                continue;
            }

            $this->Db->query(
                'IksAdminNew',
                0,
                0,
                "DELETE FROM {$table} WHERE {$where} AND server_id = ?",
                array_merge($whereParams, [(int) $serverId])
            );
        }

        foreach ($toAdd as $serverId) {
            $bindServer = $this->punishmentBindingToIksServerId((int) $serverId);
            if ($isBan) {
                $banType = $ipValue != null ? 1 : 0;
                $this->Db->query(
                    'IksAdminNew',
                    0,
                    0,
                    'INSERT INTO iks_bans (`steam_id`, `ip`, `name`, `duration`, `reason`, `ban_type`, `server_id`, `admin_id`, `created_at`, `end_at`, `updated_at`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())',
                    [$row['steam_id'], $ipValue, $row['name'], $durationSeconds, $reason, $banType, $bindServer, $row['admin_id'], $row['created_at'], $newEnd]
                );
            } else {
                $muteType = $this->resolveMuteType($punishType);
                $this->Db->query(
                    'IksAdminNew',
                    0,
                    0,
                    'INSERT INTO iks_comms (`steam_id`, `ip`, `name`, `duration`, `reason`, `mute_type`, `server_id`, `admin_id`, `created_at`, `end_at`, `updated_at`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())',
                    [$row['steam_id'], $ipValue, $row['name'], $durationSeconds, $reason, $muteType, $bindServer, $row['admin_id'], $row['created_at'], $newEnd]
                );
            }
        }

        return true;
    }

    public function getPunishmentOffenderSteamidsByIds(array $ids): array
    {
        return $this->fetchPunishmentOffenderSteamids($ids, 'IksAdminNew', [
            'SELECT DISTINCT steam_id AS steamid FROM iks_bans WHERE id IN (%s)',
            'SELECT DISTINCT steam_id AS steamid FROM iks_comms WHERE id IN (%s)',
        ]);
    }

    private function buildPunishmentsQuery(array $filters, bool $count = false): array
    {
        $isBan = ($filters['punish_type'] ?? 'ban') == 'ban';
        $table = $isBan ? 'iks_bans' : 'iks_comms';
        $params = [];

        $where = 'WHERE 1=1';
        $where .= $this->punishmentAdminWhere($filters['admin'] ?? -1, 'p.admin_id', $params);
        $where .= $this->punishmentDateWhere($filters['date_from'] ?? '', $filters['date_to'] ?? '', 'p.created_at', $params);
        $where .= $this->punishmentSearchWhere($filters['search'] ?? '', 'p.name', 'p.steam_id', false, $params);

        $expireFilter = $filters['expire_filter'] ?? 'all';
        if ($expireFilter == 'active') {
            $where .= ' AND p.unbanned_by IS NULL AND (p.end_at = 0 OR p.end_at > UNIX_TIMESTAMP())';
        } elseif ($expireFilter == 'expired') {
            $where .= ' AND (p.unbanned_by IS NOT NULL OR (p.end_at > 0 AND p.end_at <= UNIX_TIMESTAMP()))';
        }

        $groupBy = ' GROUP BY p.steam_id, p.created_at, p.admin_id, p.reason, p.end_at, p.duration, p.unbanned_by, p.name';
        $having = $this->punishmentServersHaving($filters['servers'] ?? [-1], 'p.server_id', true, -1, $params);

        if ($count) {
            $sql = "SELECT COUNT(*) AS total FROM (
                SELECT MIN(p.id) AS id
                FROM {$table} p
                LEFT JOIN iks_admins a ON a.id = p.admin_id
                {$where}
                {$groupBy}
                {$having}
            ) grouped";

            return [$sql, $params];
        }

        $typeSelect = $isBan
            ? '0 AS punish_type,'
            : 'CASE p.mute_type WHEN 1 THEN 2 WHEN 2 THEN 3 ELSE 1 END AS punish_type,';
        $sql = "SELECT MIN(p.id) AS id, p.name, p.steam_id AS steamid, p.created_at AS created, p.end_at AS expires, p.duration AS length,
            MAX(p.ip) AS ip, {$typeSelect} p.reason, p.unbanned_by,
            GROUP_CONCAT(DISTINCT IFNULL(p.server_id, -1) ORDER BY IFNULL(p.server_id, -1) SEPARATOR ',') AS server_ids,
            MAX(a.name) AS admin_name, MAX(a.steam_id) AS admin_steamid
            FROM {$table} p
            LEFT JOIN iks_admins a ON a.id = p.admin_id
            {$where}
            {$groupBy}
            {$having}";

        return [$sql, $params];
    }

    private function resolveMuteType(int $punishType): int
    {
        switch ($punishType) {
            case 2:
                return 1;
            case 3:
                return 2;
            default:
                return 0;
        }
    }

    public function getChecksList(array $filters): array
    {
        [$sql, $params] = $this->buildIksChecksQuery($filters);
        $params[] = $filters['limit'];
        $params[] = $filters['offset'];

        return $this->Db->queryAll('IksAdminNew', 0, 0, $sql . ' ORDER BY cr.id DESC LIMIT ? OFFSET ?', $params);
    }

    public function getChecksCount(array $filters): int
    {
        [$sql, $params] = $this->buildIksChecksQuery($filters, true);
        $row = $this->Db->query('IksAdminNew', 0, 0, $sql, $params);

        return (int) ($row['total'] ?? 0);
    }

    public function getChecksByIds(array $ids): array
    {
        [$sql, $params] = $this->buildIksChecksByIdsQuery($ids);
        if ($sql == '') {
            return [];
        }

        return $this->Db->queryAll('IksAdminNew', 0, 0, $sql, $params);
    }

    public function getChecksVerdicts(): array
    {
        return $this->Db->queryAll(
            'IksAdminNew',
            0,
            0,
            "SELECT DISTINCT check_result, result_reason
             FROM iks_check_results
             ORDER BY check_result ASC, result_reason ASC"
        );
    }

    public function deleteChecksByIds(array $ids): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids == []) {
            return;
        }

        foreach ($ids as $id) {
            if ($id <= 0) {
                continue;
            }
            $this->Db->query('IksAdminNew', 0, 0, 'DELETE FROM iks_check_results WHERE id = ?', [$id]);
        }
    }

    public function resolveCheckAdminSteamid(int $adminId): ?string
    {
        $admin = $this->getAdminById($adminId);
        if ($admin == null || $admin == []) {
            return null;
        }

        $steamid = $admin['steam_id'] ?? $admin['steamid'] ?? null;

        return $steamid ? (string) $steamid : null;
    }
}
