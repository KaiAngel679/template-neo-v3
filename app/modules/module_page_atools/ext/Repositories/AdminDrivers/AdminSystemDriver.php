<?php

namespace app\modules\module_page_atools\ext\Repositories\AdminDrivers;

class AdminSystemDriver implements AdminDriverInterface
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
        return $this->Db->queryAll('AdminSystem', 0, 0, 'SELECT `id`, `name`, `flags`, `immunity` FROM as_groups ORDER BY immunity DESC');
    }

    public function createAdminGroup(string $name, int $immunity, string $flags): void
    {
        $this->Db->query(
            'AdminSystem',
            0,
            0,
            'INSERT INTO as_groups (`flags`, `name`, `immunity`) VALUES (?, ?, ?)',
            [$flags, $name, $immunity]
        );
    }

    public function updateAdminGroup(int $id, string $name, int $immunity, string $flags): void
    {
        $this->Db->query(
            'AdminSystem',
            0,
            0,
            'UPDATE as_groups SET flags = ?, name = ?, immunity = ? WHERE id = ?',
            [$flags, $name, $immunity, $id]
        );
    }

    public function deleteAdminGroup(int $id): void
    {
        $this->Db->query('AdminSystem', 0, 0, 'DELETE FROM as_groups WHERE id = ? LIMIT 1', [$id]);
    }

    public function issetAdmin(string $steamid): bool
    {
        $sql = 'SELECT a.id FROM as_admins a
                INNER JOIN as_admins_servers asa ON a.id = asa.admin_id
                WHERE a.steamid = ?
                LIMIT 1';
        $result = $this->Db->query('AdminSystem', 0, 0, $sql, [$steamid]);

        return !empty($result);
    }

    public function createAdmin(string $steamid, string $name, int $group, int $expire, array $servers): void
    {
        $expireTime = $expire > 0 ? time() + $expire : 0;
        $serverIds = $this->normalizeUpdateServerIds($servers);
        $adminId = $this->resolveAdminId($steamid)['id'];

        if (!empty($adminId)) {
            $this->Db->query(
                'AdminSystem',
                0,
                0,
                'UPDATE as_admins SET name = ? WHERE id = ?',
                [$name, $adminId]
            );
        } else {
            $this->Db->query('AdminSystem', 0, 0, 'INSERT INTO as_admins (`name`, `steamid`) VALUES (?, ?)', [$name, $steamid]);
            $adminId = $this->Db->lastInsertId('AdminSystem');
        }

        if ($adminId) {
            $this->syncAsAdminServers($adminId, $group, $expireTime, $serverIds);
        }
    }

    public function getAdminsList(array $servers, int $group, int $limit, int $offset, string $search, ?array $allowedSteamids = null): array
    {
        $sql = "SELECT
                    a.id,
                    a.name,
                    a.steamid,
                    COALESCE(g.name, '') as group_name,
                    COALESCE(g.id, asa.group_id) as group_id,
                    MIN(asa.expires) as expires,
                    GROUP_CONCAT(DISTINCT asa.server_id ORDER BY asa.server_id SEPARATOR ',') as server_ids,
                    GROUP_CONCAT(DISTINCT asa.expires ORDER BY asa.server_id SEPARATOR ',') as server_expires
                FROM as_admins a
                INNER JOIN as_admins_servers asa ON a.id = asa.admin_id
                LEFT JOIN as_groups g ON asa.group_id = g.id
                WHERE 1=1";

        [$where, $filterParams] = $this->appendCs2AdminFilters($servers, $group, $search, 'asa', false, 'a.steamid', $allowedSteamids);
        $sql .= $where;
        $sql .= ' GROUP BY a.id, a.name, a.steamid, g.name, g.id, asa.group_id';
        $sql .= ' ORDER BY expires ASC LIMIT ? OFFSET ?';

        return $this->Db->queryAll('AdminSystem', 0, 0, $sql, array_merge($filterParams, [$limit, $offset]));
    }

    public function getAdminsCount(array $servers, int $group, string $search, ?array $allowedSteamids = null): int
    {
        $sql = "SELECT COUNT(DISTINCT a.id) as total
                FROM as_admins a
                INNER JOIN as_admins_servers asa ON a.id = asa.admin_id
                LEFT JOIN as_groups g ON asa.group_id = g.id
                WHERE 1=1";

        [$where, $params] = $this->appendCs2AdminFilters($servers, $group, $search, 'asa', false, 'a.steamid', $allowedSteamids);
        $sql .= $where;

        $result = $this->Db->query('AdminSystem', 0, 0, $sql, $params);

        return (int) ($result['total'] ?? 0);
    }

    public function getAdminServers(int $adminId): array
    {
        return $this->Db->queryAll(
            'AdminSystem',
            0,
            0,
            'SELECT server_id, expires FROM as_admins_servers WHERE admin_id = ?',
            [$adminId]
        );
    }

    public function deleteAdmin(int $adminId): void
    {
        $this->Db->query('AdminSystem', 0, 0, 'DELETE FROM as_admins_servers WHERE admin_id = ?', [$adminId]);
    }

    public function getAdminById(int $adminId): ?array
    {
        $row = $this->Db->query('AdminSystem', 0, 0, 'SELECT * FROM as_admins WHERE id = ?', [$adminId]);

        return !empty($row) ? $row : [];
    }

    public function updateAdmin(int $adminId, string $group, string $expire, array $servers): void
    {
        $expireTime = $expire > 0 ? time() + $expire : 0;
        $this->syncAsAdminServers(
            $adminId,
            $group,
            $expireTime,
            $this->normalizeUpdateServerIds($servers)
        );
    }

    private function currentAdminServerIds(int $adminId): array
    {
        return array_map('intval', array_column($this->getAdminServers($adminId), 'server_id'));
    }

    private function syncAsAdminServers(int $adminId, int $groupId, int $expireTime, array $targetIds): void
    {
        $current = $this->currentAdminServerIds($adminId);
        foreach (array_diff($current, $targetIds) as $serverId) {
            $this->Db->query(
                'AdminSystem',
                0,
                0,
                'DELETE FROM as_admins_servers WHERE admin_id = ? AND server_id = ?',
                [$adminId, $serverId]
            );
        }
        foreach (array_diff($targetIds, $current) as $serverId) {
            $this->Db->query(
                'AdminSystem',
                0,
                0,
                'INSERT INTO as_admins_servers (`admin_id`, `group_id`, `expires`, `server_id`) VALUES (?, ?, ?, ?)',
                [$adminId, $groupId, $expireTime, $serverId]
            );
        }
        foreach (array_intersect($targetIds, $current) as $serverId) {
            $this->Db->query(
                'AdminSystem',
                0,
                0,
                'UPDATE as_admins_servers SET group_id = ?, expires = ? WHERE admin_id = ? AND server_id = ?',
                [$groupId, $expireTime, $adminId, $serverId]
            );
        }
    }

    public function createPunishment(string $steamid, string $name, ?string $ip, int $type, string $reason, int $duration, array $serverIds, string $adminSteamid): void
    {
        $end = $duration > 0 ? time() + $duration : 0;
        $adminId = $this->resolveAdminId($adminSteamid)['id'];
        $servers = $this->normalizeUpdateServerIds($serverIds);
        $ipValue = $ip != '' ? $ip : null;

        foreach ($servers as $serverId) {
            $this->Db->query(
                'AdminSystem',
                0,
                0,
                'INSERT INTO as_punishments (`name`, `steamid`, `ip`, `admin_id`, `created`, `expires`, `reason`, `unpunish_admin_id`, `server_id`, `punish_type`)
                    VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(), ?, ?, NULL, ?, ?)',
                [$name, $steamid, $ipValue, $adminId, $end, $reason, $this->punishmentBindingToAdminSystemServerId($serverId), $type]
            );
        }
    }

    private function resolveAdminId(string $adminSteamid): array
    {
        return $this->Db->query(
            'AdminSystem',
            0,
            0,
            'SELECT id FROM as_admins WHERE steamid = ? LIMIT 1',
            [$adminSteamid]
        );
    }

    public function getListAdmins(): array
    {
        return $this->Db->queryAll(
            'AdminSystem',
            0,
            0,
            'SELECT DISTINCT a.id, a.name, a.steamid
                FROM as_admins a
                INNER JOIN as_admins_servers asa ON a.id = asa.admin_id
                WHERE asa.expires = 0 OR asa.expires > UNIX_TIMESTAMP()'
        );
    }

    public function getPunishmentsList(array $filters): array
    {
        [$sql, $params] = $this->buildPunishmentsQuery($filters);
        $params[] = $filters['limit'];
        $params[] = $filters['offset'];

        return $this->Db->queryAll('AdminSystem', 0, 0, $sql . ' ORDER BY p.created DESC LIMIT ? OFFSET ?', $params);
    }

    public function getPunishmentsCount(array $filters): int
    {
        [$sql, $params] = $this->buildPunishmentsQuery($filters, true);
        $row = $this->Db->query('AdminSystem', 0, 0, $sql, $params);

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
                'AdminSystem',
                0,
                0,
                'SELECT expires, unpunish_admin_id FROM as_punishments WHERE id = ? LIMIT 1',
                [$id]
            );
            if (!$row || !empty($row['unpunish_admin_id'])) {
                continue;
            }
            if ($row['expires'] > 0 && $row['expires'] <= time()) {
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

        $issuer = $this->resolveAdminId($issuerSteamid64);

        foreach ($ids as $id) {
            $row = $this->Db->query(
                'AdminSystem',
                0,
                0,
                'SELECT steamid, created, admin_id, reason, expires, punish_type, name FROM as_punishments WHERE id = ? LIMIT 1',
                [$id]
            );
            if (!$row) {
                continue;
            }

            $this->Db->query(
                'AdminSystem',
                0,
                0,
                'UPDATE as_punishments
                    SET unpunish_admin_id = ?
                    WHERE steamid = ? AND created = ? AND admin_id = ? AND reason = ? AND expires = ? AND punish_type = ? AND name = ?',
                [$issuer['id'], $row['steamid'], $row['created'], $row['admin_id'], $row['reason'], $row['expires'], $row['punish_type'], $row['name']]
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
            $row = $this->Db->query(
                'AdminSystem',
                0,
                0,
                'SELECT steamid, created, admin_id, reason, expires, punish_type, name FROM as_punishments WHERE id = ? LIMIT 1',
                [$id]
            );
            if (!$row) {
                continue;
            }

            $this->Db->query(
                'AdminSystem',
                0,
                0,
                'DELETE FROM as_punishments
                    WHERE steamid = ? AND created = ? AND admin_id = ? AND reason = ? AND expires = ? AND punish_type = ? AND name = ?',
                [$row['steamid'], $row['created'], $row['admin_id'], $row['reason'], $row['expires'], $row['punish_type'], $row['name']]
            );
        }
    }

    public function updatePunishmentById(int $id, ?string $ip, int $punishType, string $reason, int $durationSeconds, array $serverIds): bool
    {
        $row = $this->Db->query(
            'AdminSystem',
            0,
            0,
            'SELECT id, name, steamid, ip, created, admin_id, reason, expires, punish_type, unpunish_admin_id
                FROM as_punishments WHERE id = ? LIMIT 1',
            [$id]
        );
        if (!$row || !empty($row['unpunish_admin_id'])) {
            return false;
        }
        if ($row['expires'] > 0 && $row['expires'] <= time()) {
            return false;
        }

        $newExpires = $durationSeconds > 0 ? time() + $durationSeconds : 0;
        $ipValue = $ip != null && $ip != '' ? $ip : null;
        $servers = $this->normalizeUpdateServerIds($serverIds);

        $where = 'steamid = ? AND created = ? AND admin_id = ? AND reason = ? AND expires = ? AND punish_type = ? AND name = ? AND unpunish_admin_id IS NULL';
        $whereParams = [$row['steamid'], $row['created'], $row['admin_id'], $row['reason'], $row['expires'], $row['punish_type'], $row['name']];

        $this->Db->query(
            'AdminSystem',
            0,
            0,
            "UPDATE as_punishments SET reason = ?, expires = ?, punish_type = ?, ip = ? WHERE {$where}",
            array_merge([$reason, $newExpires, $punishType, $ipValue], $whereParams)
        );

        $currentRows = $this->Db->queryAll(
            'AdminSystem',
            0,
            0,
            "SELECT id, server_id FROM as_punishments WHERE {$where}",
            $whereParams
        );
        $currentServers = array_map(static function ($r) {
            return (int) $r['server_id'];
        }, $currentRows);
        $toRemove = array_diff($currentServers, $servers);
        $toAdd = array_diff($servers, $currentServers);

        foreach ($toRemove as $serverId) {
            $this->Db->query(
                'AdminSystem',
                0,
                0,
                "DELETE FROM as_punishments WHERE {$where} AND server_id = ?",
                array_merge($whereParams, [$this->punishmentBindingToAdminSystemServerId((int) $serverId)])
            );
        }

        foreach ($toAdd as $serverId) {
            $this->Db->query(
                'AdminSystem',
                0,
                0,
                'INSERT INTO as_punishments (`name`, `steamid`, `ip`, `admin_id`, `created`, `expires`, `reason`, `unpunish_admin_id`, `server_id`, `punish_type`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NULL, ?, ?)',
                [$row['name'], $row['steamid'], $ipValue, $row['admin_id'], $row['created'], $newExpires, $reason, $this->punishmentBindingToAdminSystemServerId((int) $serverId), $punishType]
            );
        }

        return true;
    }

    public function getPunishmentOffenderSteamidsByIds(array $ids): array
    {
        return $this->fetchPunishmentOffenderSteamids($ids, 'AdminSystem', [
            'SELECT DISTINCT steamid FROM as_punishments WHERE id IN (%s)',
        ]);
    }

    private function buildPunishmentsQuery(array $filters, bool $count = false): array
    {
        $isBan = ($filters['punish_type'] ?? 'ban') == 'ban';
        $typeSql = $isBan ? 'p.punish_type = 0' : 'p.punish_type != 0';
        $params = [];

        $where = "WHERE {$typeSql}";
        $where .= $this->punishmentAdminWhere($filters['admin'] ?? -1, 'p.admin_id', $params);
        $where .= $this->punishmentDateWhere($filters['date_from'] ?? '', $filters['date_to'] ?? '', 'p.created', $params);
        $where .= $this->punishmentSearchWhere($filters['search'] ?? '', 'p.name', 'p.steamid', false, $params);

        $expireFilter = $filters['expire_filter'] ?? 'all';
        if ($expireFilter == 'active') {
            $where .= ' AND p.unpunish_admin_id IS NULL AND (p.expires = 0 OR p.expires > UNIX_TIMESTAMP())';
        } elseif ($expireFilter == 'expired') {
            $where .= ' AND (p.unpunish_admin_id IS NOT NULL OR (p.expires > 0 AND p.expires <= UNIX_TIMESTAMP()))';
        }

        $groupBy = ' GROUP BY p.steamid, p.created, p.admin_id, p.reason, p.expires, p.unpunish_admin_id, p.punish_type, p.name';
        $having = $this->punishmentServersHaving((array) ($filters['servers'] ?? [-1]), 'p.server_id', false, -1, $params);

        if ($count) {
            $sql = "SELECT COUNT(*) AS total FROM (
                SELECT MIN(p.id) AS id
                FROM as_punishments p
                LEFT JOIN as_admins a ON a.id = p.admin_id
                {$where}
                {$groupBy}
                {$having}
            ) grouped";

            return [$sql, $params];
        }

        $sql = "SELECT MIN(p.id) AS id, p.name, p.steamid, p.created, p.expires, p.reason, p.unpunish_admin_id, p.punish_type,
            MAX(p.ip) AS ip,
            GROUP_CONCAT(DISTINCT p.server_id ORDER BY p.server_id SEPARATOR ',') AS server_ids,
            MAX(a.name) AS admin_name, MAX(a.steamid) AS admin_steamid
            FROM as_punishments p
            LEFT JOIN as_admins a ON a.id = p.admin_id
            {$where}
            {$groupBy}
            {$having}";

        return [$sql, $params];
    }

    public function getChecksList(array $filters): array
    {
        [$sql, $params] = $this->buildStatsChecksQuery($filters);
        $params[] = $filters['limit'];
        $params[] = $filters['offset'];

        return $this->Db->queryAll('AdminSystem', 0, 0, $sql . ' ORDER BY id DESC LIMIT ? OFFSET ?', $params);
    }

    public function getChecksCount(array $filters): int
    {
        [$sql, $params] = $this->buildStatsChecksQuery($filters, true);
        $row = $this->Db->query('AdminSystem', 0, 0, $sql, $params);

        return (int) ($row['total'] ?? 0);
    }

    public function getChecksByIds(array $ids): array
    {
        [$sql, $params] = $this->buildStatsChecksByIdsQuery($ids);
        if ($sql === '') {
            return [];
        }

        return $this->Db->queryAll('AdminSystem', 0, 0, $sql, $params);
    }

    public function getChecksVerdicts(): array
    {
        return $this->Db->queryAll(
            'AdminSystem',
            0,
            0,
            "SELECT DISTINCT verdict
             FROM checkcheats_stats
             WHERE verdict IS NOT NULL AND TRIM(verdict) <> ''
             ORDER BY verdict ASC"
        );
    }

    public function deleteChecksByIds(array $ids): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids == []) {
            return;
        }

        foreach ($ids as $id) {
            $this->Db->query('AdminSystem', 0, 0, 'DELETE FROM checkcheats_stats WHERE id = ?', [$id]);
        }
    }

    public function resolveCheckAdminSteamid(int $adminId): ?string
    {
        $admin = $this->getAdminById($adminId);

        return !empty($admin['steamid']) ? (string) $admin['steamid'] : null;
    }
}
