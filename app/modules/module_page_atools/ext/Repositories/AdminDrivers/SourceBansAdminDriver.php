<?php

namespace app\modules\module_page_atools\ext\Repositories\AdminDrivers;

use app\modules\module_page_atools\ext\ModuleHelper;

class SourceBansAdminDriver implements AdminDriverInterface
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
        return $this->Db->queryAll('SourceBans', 0, 0, 'SELECT `id`, `name`, `flags`, `immunity` FROM sb_srvgroups ORDER BY immunity DESC');
    }

    public function createAdminGroup(string $name, int $immunity, string $flags): void
    {
        $this->Db->query(
            'SourceBans',
            0,
            0,
            "INSERT INTO sb_srvgroups (`flags`, `immunity`, `name`, `groups_immune`, `maxbantime`, `maxmutetime`) VALUES (?, ?, ?, '', -1, -1)",
            [$flags, $immunity, $name]
        );
    }

    public function updateAdminGroup(int $id, string $name, int $immunity, string $flags): void
    {
        $old = $this->getGroupById($id);

        $this->Db->query(
            'SourceBans',
            0,
            0,
            'UPDATE sb_srvgroups SET flags = ?, immunity = ?, name = ? WHERE id = ?',
            [$flags, $immunity, $name, $id]
        );

        if (!empty($old['name']) && $old['name'] !== $name) {
            $this->Db->query(
                'SourceBans',
                0,
                0,
                'UPDATE sb_admins SET srv_group = ?, srv_flags = ?, immunity = ? WHERE srv_group = ?',
                [$name, $flags, $immunity, $old['name']]
            );
        }
    }

    public function deleteAdminGroup(int $id): void
    {
        $this->Db->query('SourceBans', 0, 0, 'DELETE FROM sb_srvgroups WHERE id = ? LIMIT 1', [$id]);
    }

    private function getGroupById(int $groupId): array
    {
        return $this->Db->query('SourceBans', 0, 0, 'SELECT `id`, `name`, `flags`, `immunity` FROM sb_srvgroups WHERE `id` = ? LIMIT 1', [$groupId]);
    }

    public function issetAdmin(string $steamid): bool
    {
        $sql = 'SELECT a.aid FROM sb_admins a
                INNER JOIN sb_admins_servers_groups asg ON a.aid = asg.admin_id
                WHERE a.authid = ?
                LIMIT 1';
        $result = $this->Db->query('SourceBans', 0, 0, $sql, [$steamid]);

        return !empty($result);
    }

    public function createAdmin(string $steamid, string $name, int $group, int $expire, array $servers): void
    {
        $expireTime = $expire > 0 ? time() + $expire : 0;
        $serverIds = $this->expandSourceBansServerIds($this->normalizeUpdateServerIds($servers));
        $groupRow = $this->getGroupById($group);
        $adminId = $this->resolveAdminId($steamid)['aid'];

        if (!empty($adminId)) {
            $this->Db->query(
                'SourceBans',
                0,
                0,
                'UPDATE sb_admins SET user = ?, expired = ?, immunity = ?, srv_group = ?, srv_flags = ?, lastvisit = ? WHERE aid = ?',
                [$name, $expireTime, $groupRow['immunity'], $groupRow['name'], $groupRow['flags'], time(), $adminId]
            );
        } else {
            $password = sha1('password' . random_int(1, 99999));
            $email = 'admtools_' . random_int(1, 99999) . '@r8.dev';

            $this->Db->query(
                'SourceBans',
                0,
                0,
                'INSERT INTO sb_admins (`user`, `authid`, `password`, `gid`, `email`, `extraflags`, `immunity`, `srv_group`, `srv_flags`, `lastvisit`, `expired`, `comment`)
                    VALUES (?, ?, ?, 0, ?, 0, ?, ?, ?, ?, ?, ?)',
                [$name, $steamid, $password, $email, $groupRow['immunity'], $groupRow['name'], $groupRow['flags'], time(), $expireTime, 'admtools']
            );

            $adminId = $this->Db->lastInsertId('SourceBans', 0, 0);
        }

        if (!$adminId) {
            return;
        }

        foreach ($serverIds as $sid) {
            $this->insertAdminServerGroup($adminId, $group, $sid);
        }
    }

    private function expandSourceBansServerIds(array $serverIds): array
    {
        if (!in_array(-1, $serverIds, true)) {
            return $serverIds;
        }

        $rows = $this->Db->queryAll('SourceBans', 0, 0, 'SELECT `sid` FROM `sb_servers` ORDER BY `sid` ASC', []);
        if (!is_array($rows) || $rows === []) {
            return $serverIds;
        }

        $ids = [];
        foreach ($rows as $row) {
            $sid = (int) ($row['sid'] ?? 0);
            if ($sid > 0) {
                $ids[] = $sid;
            }
        }

        return $ids !== [] ? array_values(array_unique($ids)) : $serverIds;
    }

    private function insertAdminServerGroup(int $adminId, int $groupId, int $serverId): void
    {
        $this->Db->query(
            'SourceBans',
            0,
            0,
            'INSERT INTO sb_admins_servers_groups (`admin_id`, `group_id`, `srv_group_id`, `server_id`) VALUES (?, ?, -1, ?)',
            [$adminId, $groupId, $serverId]
        );
    }

    public function getAdminsList(array $servers, int $group, int $limit, int $offset, string $search, ?array $allowedSteamids = null): array
    {
        $sql = "SELECT
                    a.aid AS id,
                    a.user AS name,
                    a.authid AS steamid,
                    GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS group_name,
                    MIN(g.id) AS group_id,
                    a.expired AS expires,
                    GROUP_CONCAT(DISTINCT asg.server_id ORDER BY asg.server_id SEPARATOR ',') AS server_ids,
                    GROUP_CONCAT(DISTINCT a.expired ORDER BY asg.server_id SEPARATOR ',') AS server_expires
                FROM sb_admins a
                INNER JOIN sb_admins_servers_groups asg ON a.aid = asg.admin_id
                LEFT JOIN sb_srvgroups g ON asg.group_id = g.id
                WHERE (a.expired > UNIX_TIMESTAMP() OR a.expired = 0)";

        $params = $this->appendListFilters($servers, $group, $search, $sql, $allowedSteamids);

        $sql .= ' GROUP BY a.aid, a.user, a.authid, a.expired';
        $sql .= ' ORDER BY a.aid DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        return $this->Db->queryAll('SourceBans', 0, 0, $sql, $params);
    }

    public function getAdminsCount(array $servers, int $group, string $search, ?array $allowedSteamids = null): int
    {
        $sql = "SELECT COUNT(DISTINCT a.aid) AS total
                FROM sb_admins a
                INNER JOIN sb_admins_servers_groups asg ON a.aid = asg.admin_id
                LEFT JOIN sb_srvgroups g ON asg.group_id = g.id
                WHERE (a.expired > UNIX_TIMESTAMP() OR a.expired = 0)";

        $params = $this->appendListFilters($servers, $group, $search, $sql, $allowedSteamids);

        $result = $this->Db->query('SourceBans', 0, 0, $sql, $params);

        return ($result['total'] ?? 0);
    }

    private function appendListFilters(array $servers, int $group, string $search, string &$sql, ?array $allowedSteamids = null): array
    {
        $params = [];

        $search = ModuleHelper::toSteam32($search);

        if (!in_array(-1, $servers)) {
            $placeholders = implode(',', array_fill(0, count($servers), '?'));
            $sql .= " AND (asg.server_id IN ($placeholders) OR asg.server_id = -1 OR asg.server_id = '-1')";
            $params = array_merge($params, $servers);
        }

        if ($group != -1) {
            $sql .= ' AND g.id = ?';
            $params[] = $group;
        }

        if ($search !== '') {
            $sql .= ' AND (a.user LIKE ? OR a.authid LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= $this->appendAllowedSteamidsSql($allowedSteamids, 'a.authid', $params);

        return $params;
    }

    public function getAdminServers(int $adminId): array
    {
        return $this->Db->queryAll(
            'SourceBans',
            0,
            0,
            'SELECT server_id FROM sb_admins_servers_groups WHERE admin_id = ?',
            [$adminId]
        );
    }

    public function deleteAdmin(int $adminId): void
    {
        $this->Db->query('SourceBans', 0, 0, 'DELETE FROM sb_admins_servers_groups WHERE admin_id = ?', [$adminId]);
    }

    public function getAdminById(int $adminId): ?array
    {
        $row = $this->Db->query('SourceBans', 0, 0, 'SELECT * FROM sb_admins WHERE aid = ?', [$adminId]);
        if (!is_array($row) || empty($row['aid'])) {
            return null;
        }
        $row['id'] = $row['aid'];
        if (!isset($row['steamid']) && isset($row['authid'])) {
            $row['steamid'] = $row['authid'];
        }

        return $row;
    }

    public function updateAdmin(int $adminId, string $group, string $expire, array $servers): void
    {
        $expireTime = $expire > 0 ? time() + $expire : 0;
        $groupRow = $this->getGroupById($group);

        $this->Db->query(
            'SourceBans',
            0,
            0,
            'UPDATE sb_admins SET expired = ?, immunity = ?, srv_group = ?, srv_flags = ? WHERE aid = ?',
            [$expireTime, $groupRow['immunity'], $groupRow['name'], $groupRow['flags'], $adminId]
        );

        $serverIds = $this->expandSourceBansServerIds($this->normalizeUpdateServerIds($servers));

        $this->Db->query('SourceBans', 0, 0, 'DELETE FROM sb_admins_servers_groups WHERE admin_id = ?', [$adminId]);

        foreach ($serverIds as $sid) {
            $this->insertAdminServerGroup($adminId, $group, $sid);
        }
    }

    public function createPunishment(string $steamid, string $name, ?string $ip, int $type, string $reason, int $duration, array $serverIds, string $adminSteamid): void
    {
        $end = $duration > 0 ? time() + $duration : 0;
        $adminId = $this->resolveAdminId($adminSteamid)['aid'];
        $servers = $this->normalizeUpdateServerIds($serverIds);
        $ipValue = $ip != null && $ip != '' ? $ip : null;

        if ($type == 0) {
            $banType = 0;
            if ($ipValue != null) {
                $banType = 1;
            }
            foreach ($servers as $serverId) {
                $this->Db->query(
                    'SourceBans',
                    0,
                    0,
                    'INSERT INTO sb_bans (`name`, `authid`, `ip`, `created`, `ends`, `length`, `reason`, `aid`, `adminIp`, `sid`, `type`)
                        VALUES (?, ?, ?, UNIX_TIMESTAMP(), ?, ?, ?, ?, ?, ?, ?)',
                    [$name, $steamid, $ipValue, $end, $duration, $reason, $adminId, $adminSteamid, $this->punishmentBindingToSourceBansSid($serverId), $banType]
                );
            }

            return;
        }

        switch ($type) {
            case 2:
                $commType = 2;
                break;
            case 3:
                $commType = 3;
                break;
            default:
                $commType = 1;
                break;
        }

        foreach ($servers as $serverId) {
            $this->Db->query(
                'SourceBans',
                0,
                0,
                'INSERT INTO sb_comms (`name`, `authid`, `created`, `ends`, `length`, `reason`, `aid`, `adminIp`, `sid`, `type`)
                    VALUES (?, ?, UNIX_TIMESTAMP(), ?, ?, ?, ?, ?, ?, ?)',
                [$name, $steamid, $end, $duration, $reason, $adminId, $adminSteamid, $this->punishmentBindingToSourceBansSid($serverId), $commType]
            );
        }
    }

    private function resolveAdminId(string $adminSteamid): array
    {
        return $this->Db->query(
            'SourceBans',
            0,
            0,
            'SELECT aid FROM sb_admins WHERE authid = ? LIMIT 1',
            [$adminSteamid]
        );
    }

    public function getListAdmins(): array
    {
        return $this->Db->queryAll(
            'SourceBans',
            0,
            0,
            'SELECT DISTINCT a.aid AS id, a.user AS name, a.authid AS steamid
                FROM sb_admins a
                INNER JOIN sb_admins_servers_groups asg ON a.aid = asg.admin_id
                WHERE (a.expired > UNIX_TIMESTAMP() OR a.expired = 0)'
        );
    }

    public function getPunishmentsList(array $filters): array
    {
        [$sql, $params] = $this->buildPunishmentsQuery($filters);
        $params[] = $filters['limit'];
        $params[] = $filters['offset'];

        return $this->Db->queryAll('SourceBans', 0, 0, $sql . ' ORDER BY p.created DESC LIMIT ? OFFSET ?', $params);
    }

    public function getPunishmentsCount(array $filters): int
    {
        [$sql, $params] = $this->buildPunishmentsQuery($filters, true);
        $row = $this->Db->query('SourceBans', 0, 0, $sql, $params);

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
                'SourceBans',
                0,
                0,
                'SELECT ends, RemoveType FROM sb_bans WHERE bid = ? LIMIT 1',
                [$id]
            );
            if (!$row) {
                $row = $this->Db->query(
                    'SourceBans',
                    0,
                    0,
                    'SELECT ends, RemoveType FROM sb_comms WHERE bid = ? LIMIT 1',
                    [$id]
                );
            }
            if (!$row || ($row['RemoveType'] ?? '') === 'U') {
                continue;
            }
            if ($row['ends'] > 0 && $row['ends'] <= time()) {
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
            $row = $this->Db->query(
                'SourceBans',
                0,
                0,
                'SELECT bid AS id, authid, created, aid, reason, ends, length, name, sid FROM sb_bans WHERE bid = ? LIMIT 1',
                [$id]
            );
            $table = 'sb_bans';
            if (!$row) {
                $row = $this->Db->query(
                    'SourceBans',
                    0,
                    0,
                    'SELECT bid AS id, authid, created, aid, reason, ends, length, name, sid FROM sb_comms WHERE bid = ? LIMIT 1',
                    [$id]
                );
                $table = 'sb_comms';
            }
            if (!$row) {
                continue;
            }

            $issuer = $this->resolveAdminId($issuerSteamid64);

            $this->Db->query(
                'SourceBans',
                0,
                0,
                "UPDATE {$table}
                    SET RemovedBy = ?, RemoveType = 'U', RemovedOn = UNIX_TIMESTAMP()
                    WHERE authid = ? AND created = ? AND aid = ? AND reason = ? AND ends = ? AND length = ? AND name = ?",
                [$issuer['aid'], $row['authid'], $row['created'], $row['aid'], $row['reason'], $row['ends'], $row['length'], $row['name']]
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
            $row = $this->Db->query('SourceBans', 0, 0, 'SELECT bid AS id, authid, created, aid, reason, ends, length, name FROM sb_bans WHERE bid = ? LIMIT 1', [$id]);
            $table = 'sb_bans';
            if (!$row) {
                $row = $this->Db->query('SourceBans', 0, 0, 'SELECT bid AS id, authid, created, aid, reason, ends, length, name FROM sb_comms WHERE bid = ? LIMIT 1', [$id]);
                $table = 'sb_comms';
            }
            if (!$row) {
                continue;
            }

            $this->Db->query(
                'SourceBans',
                0,
                0,
                "DELETE FROM {$table}
                    WHERE authid = ? AND created = ? AND aid = ? AND reason = ? AND ends = ? AND length = ? AND name = ?",
                [$row['authid'], $row['created'], $row['aid'], $row['reason'], $row['ends'], $row['length'], $row['name']]
            );
        }
    }

    public function updatePunishmentById(int $id, ?string $ip, int $punishType, string $reason, int $durationSeconds, array $serverIds): bool
    {
        $row = $this->Db->query(
            'SourceBans',
            0,
            0,
            'SELECT bid AS id, authid, ip, created, aid, reason, ends, length, name, sid, type, RemoveType, adminIp
                FROM sb_bans WHERE bid = ? LIMIT 1',
            [$id]
        );
        $table = 'sb_bans';
        $isBan = true;
        if (!$row) {
            $row = $this->Db->query(
                'SourceBans',
                0,
                0,
                'SELECT bid AS id, authid, created, aid, reason, ends, length, name, sid, type, RemoveType, adminIp
                    FROM sb_comms WHERE bid = ? LIMIT 1',
                [$id]
            );
            $table = 'sb_comms';
            $isBan = false;
        }
        if (!$row || ($row['RemoveType'] ?? '') === 'U') {
            return false;
        }
        if ($row['ends'] > 0 && $row['ends'] <= time()) {
            return false;
        }
        if ($isBan && $punishType !== 0) {
            return false;
        }
        if (!$isBan && $punishType === 0) {
            return false;
        }

        $newEnd = $durationSeconds > 0 ? time() + $durationSeconds : 0;
        $servers = $this->normalizeUpdateServerIds($serverIds);
        $where = 'authid = ? AND created = ? AND aid = ? AND reason = ? AND ends = ? AND length = ? AND name = ? AND (RemoveType IS NULL OR RemoveType != \'U\')';
        $whereParams = [$row['authid'], $row['created'], $row['aid'], $row['reason'], $row['ends'], $row['length'], $row['name']];

        if ($isBan) {
            $ipValue = $ip != null && $ip != '' ? $ip : null;
            $banType = $ipValue != null ? 1 : 0;
            $this->Db->query(
                'SourceBans',
                0,
                0,
                "UPDATE {$table} SET reason = ?, ends = ?, length = ?, ip = ?, type = ? WHERE {$where}",
                array_merge([$reason, $newEnd, $durationSeconds, $ipValue, $banType], $whereParams)
            );
        } else {
            $commType = $this->resolveCommType($punishType);
            $this->Db->query(
                'SourceBans',
                0,
                0,
                "UPDATE {$table} SET reason = ?, ends = ?, length = ?, type = ? WHERE {$where}",
                array_merge([$reason, $newEnd, $durationSeconds, $commType], $whereParams)
            );
        }

        $currentRows = $this->Db->queryAll(
            'SourceBans',
            0,
            0,
            "SELECT bid AS id, sid FROM {$table} WHERE {$where}",
            $whereParams
        );
        $currentServers = array_map(function ($r) {
            return $this->punishmentSourceBansSidToBinding($r['sid']);
        }, $currentRows);
        $toRemove = array_diff($currentServers, $servers);
        $toAdd = array_diff($servers, $currentServers);

        foreach ($toRemove as $serverId) {
            $this->Db->query(
                'SourceBans',
                0,
                0,
                "DELETE FROM {$table} WHERE {$where} AND sid = ?",
                array_merge($whereParams, [$this->punishmentBindingToSourceBansSid((int) $serverId)])
            );
        }

        foreach ($toAdd as $serverId) {
            $dbSid = $this->punishmentBindingToSourceBansSid((int) $serverId);
            if ($isBan) {
                $ipValue = $ip != null && $ip != '' ? $ip : null;
                $banType = $ipValue != null ? 1 : 0;
                $this->Db->query(
                    'SourceBans',
                    0,
                    0,
                    'INSERT INTO sb_bans (`name`, `authid`, `ip`, `created`, `ends`, `length`, `reason`, `aid`, `adminIp`, `sid`, `type`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$row['name'], $row['authid'], $ipValue, $row['created'], $newEnd, $durationSeconds, $reason, $row['aid'], $row['adminIp'], $dbSid, $banType]
                );
            } else {
                $commType = $this->resolveCommType($punishType);
                $this->Db->query(
                    'SourceBans',
                    0,
                    0,
                    'INSERT INTO sb_comms (`name`, `authid`, `created`, `ends`, `length`, `reason`, `aid`, `adminIp`, `sid`, `type`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$row['name'], $row['authid'], $row['created'], $newEnd, $durationSeconds, $reason, $row['aid'], $row['adminIp'], $dbSid, $commType]
                );
            }
        }

        return true;
    }

    public function getPunishmentOffenderSteamidsByIds(array $ids): array
    {
        return $this->fetchPunishmentOffenderSteamids($ids, 'SourceBans', [
            'SELECT DISTINCT authid AS steamid FROM sb_bans WHERE bid IN (%s)',
            'SELECT DISTINCT authid AS steamid FROM sb_comms WHERE bid IN (%s)',
        ]);
    }

    private function buildPunishmentsQuery(array $filters, bool $count = false): array
    {
        $isBan = ($filters['punish_type'] ?? 'ban') == 'ban';
        $table = $isBan ? 'sb_bans' : 'sb_comms';
        $params = [];

        $where = 'WHERE 1=1';
        $where .= $this->punishmentAdminWhere($filters['admin'] ?? -1, 'p.aid', $params);
        $where .= $this->punishmentDateWhere($filters['date_from'] ?? '', $filters['date_to'] ?? '', 'p.created', $params);
        $where .= $this->punishmentSearchWhere($filters['search'] ?? '', 'p.name', 'p.authid', true, $params);

        $expireFilter = $filters['expire_filter'] ?? 'all';
        if ($expireFilter == 'active') {
            $where .= " AND (p.RemoveType IS NULL OR p.RemoveType != 'U') AND (p.length = 0 OR p.ends = 0 OR p.ends > UNIX_TIMESTAMP())";
        } elseif ($expireFilter == 'expired') {
            $where .= " AND (p.RemoveType = 'U' OR (p.length > 0 AND p.ends > 0 AND p.ends <= UNIX_TIMESTAMP()))";
        }

        $groupBy = ' GROUP BY p.authid, p.created, p.aid, p.reason, p.ends, p.length, p.RemoveType, p.name';
        $having = $this->punishmentServersHaving($filters['servers'] ?? [-1], 'p.sid', false, 0, $params);

        if ($count) {
            $sql = "SELECT COUNT(*) AS total FROM (
                SELECT MIN(p.bid) AS id
                FROM {$table} p
                LEFT JOIN sb_admins a ON a.aid = p.aid
                {$where}
                {$groupBy}
                {$having}
            ) grouped";

            return [$sql, $params];
        }

        $ipSelect = $isBan ? 'MAX(p.ip) AS ip,' : '';
        $typeSelect = $isBan ? '0 AS punish_type,' : 'p.type AS punish_type,';
        $sql = "SELECT MIN(p.bid) AS id, p.name, p.authid AS steamid, p.created, p.ends AS expires, p.length, p.reason,
            {$ipSelect}
            {$typeSelect}
            p.RemoveType,
            GROUP_CONCAT(DISTINCT p.sid ORDER BY p.sid SEPARATOR ',') AS server_ids,
            MAX(a.user) AS admin_name, MAX(a.authid) AS admin_steamid
            FROM {$table} p
            LEFT JOIN sb_admins a ON a.aid = p.aid
            {$where}
            {$groupBy}
            {$having}";

        return [$sql, $params];
    }

    private function resolveCommType(int $punishType): int
    {
        switch ($punishType) {
            case 2:
                return 2;
            case 3:
                return 3;
            default:
                return 1;
        }
    }

    public function getChecksList(array $filters): array
    {
        return [];
    }

    public function getChecksCount(array $filters): int
    {
        return 0;
    }

    public function getChecksByIds(array $ids): array
    {
        return [];
    }

    public function getChecksVerdicts(): array
    {
        return [];
    }

    public function deleteChecksByIds(array $ids): void
    {
        return;
    }

    public function resolveCheckAdminSteamid(int $adminId): ?string
    {
        return null;
    }

    public function createAdminWarn(int $recipientAid, string $targetSteamid, string $issuerSteamid, int $expires, string $reason): bool
    {
        if ($recipientAid <= 0) {
            $recipientAid = (int) ($this->resolveAdminId(con_steam32($targetSteamid))['aid'] ?? 0);
        }
        if ($recipientAid <= 0) {
            return false;
        }

        $fromAid = (int) ($this->resolveAdminId(con_steam32($issuerSteamid))['aid'] ?? 0);
        if ($fromAid <= 0) {
            return false;
        }

        $this->Db->query(
            'SourceBans',
            0,
            0,
            'INSERT INTO sb_warns (`arecipient`, `afrom`, `expires`, `reason`) VALUES (?, ?, ?, ?)',
            [$recipientAid, $fromAid, $expires, mb_substr(trim($reason), 0, 255)]
        );

        return true;
    }
}
