<?php

namespace app\modules\module_page_atools\ext\Repositories;

use app\modules\module_page_atools\ext\Repositories\AdminDrivers\AdminDriverFactory;

class DashboardRepository
{
    private $Db;
    private $AdminRepository;
    private $FinanceRepository;
    private $VipRepository;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
        $this->AdminRepository = new AdminRepository($Db);
        $this->FinanceRepository = new FinanceRepository($Db);
        $this->VipRepository = new VipRepository($Db);
    }

    public function countPlayersSinceToday(): int
    {
        if (empty($this->Db->db_data['LevelsRanks'])) {
            return 0;
        }

        $total = 0;
        for ($i = 0; $i < $this->Db->table_count['LevelsRanks']; $i++) {
            $total += (int) ($this->Db->queryNum(
                'LevelsRanks',
                $this->Db->db_data['LevelsRanks'][$i]['USER_ID'],
                $this->Db->db_data['LevelsRanks'][$i]['DB_num'],
                'SELECT COUNT(*) FROM ' . $this->Db->db_data['LevelsRanks'][$i]['Table'] . ' WHERE `lastconnect` >= UNIX_TIMESTAMP(CURDATE()) LIMIT 1'
            )[0] ?? 0);
        }

        return $total;
    }

    public function aggregateVipStats(array $targets, ?string $excludeGroup = null): array
    {
        if (!$this->VipRepository->isConnected() || $targets === []) {
            return ['total' => 0, 'forever' => 0];
        }

        $vipTotal = 0;
        $vipForever = 0;
        foreach ($targets as $target) {
            $stats = $this->VipRepository->countUsersStats(
                (string) ($target['server_vip'] ?? ''),
                (int) ($target['sid'] ?? 0),
                $excludeGroup
            );
            $vipTotal += (int) ($stats['total'] ?? 0);
            $vipForever += (int) ($stats['forever'] ?? 0);
        }

        return ['total' => $vipTotal, 'forever' => $vipForever];
    }

    public function getViolatorsDayStats(): array
    {
        if (empty($this->Db->db_data['LevelsRanks'])) {
            return [
                'available' => false,
                'players_day' => 0,
                'punished_day' => 0,
            ];
        }

        return [
            'available' => true,
            'players_day' => $this->countPlayersSinceToday(),
            'punished_day' => $this->countUniquePunishedPlayersSince(strtotime('today')),
        ];
    }

    public function getAdminPunishmentCounts(): array
    {
        $adminCount = 0;
        $temporaryAdminCount = 0;
        $muteCount = 0;
        $banCount = 0;
        $activeMuteCount = 0;
        $activeBanCount = 0;

        foreach (['csgo', 'cs2'] as $type) {
            $adminCount += $this->AdminRepository->getAdminsCount($type, [-1], -1);
            $temporaryAdminCount += $this->countTemporaryAdmins($type);
            $muteCount += $this->AdminRepository->getPunishmentsCount($type, ['punish_type' => 'mute']);
            $banCount += $this->AdminRepository->getPunishmentsCount($type, ['punish_type' => 'ban']);
            $activeMuteCount += $this->AdminRepository->getPunishmentsCount($type, ['punish_type' => 'mute', 'expire_filter' => 'active']);
            $activeBanCount += $this->AdminRepository->getPunishmentsCount($type, ['punish_type' => 'ban', 'expire_filter' => 'active']);
        }

        return [
            'admin_count' => $adminCount,
            'temporary_admin_count' => $temporaryAdminCount,
            'mute_count' => $muteCount,
            'ban_count' => $banCount,
            'active_mute_count' => $activeMuteCount,
            'active_ban_count' => $activeBanCount,
        ];
    }

    public function getChecksCounts(): array
    {
        return [
            'check_count' => $this->AdminRepository->getChecksCount([]),
            'check_count_30' => $this->AdminRepository->getChecksCount([
                'date_from' => date('Y-m-d', strtotime('-30 days')),
            ]),
        ];
    }

    public function getRevenueWeekStats(): array
    {
        if (!$this->FinanceRepository->isLkConnected()) {
            return [
                'available' => false,
                'revenue_week' => 0.0,
                'revenue_week_trend' => 0.0,
            ];
        }

        $weekStart = strtotime('monday this week 00:00:00') ?: strtotime('today 00:00:00');
        $now = time();
        $elapsed = max(0, $now - $weekStart);
        $prevWeekStart = strtotime('monday last week 00:00:00') ?: ($weekStart - 7 * 86400);
        $prevWeekEnd = $prevWeekStart + $elapsed;

        $revenueWeek = $this->FinanceRepository->sumRevenueBetween($weekStart, $now);
        $revenuePrevWeek = $this->FinanceRepository->sumRevenueBetween($prevWeekStart, $prevWeekEnd);

        $trend = 0.0;
        if ($revenuePrevWeek <= 0) {
            $trend = $revenueWeek > 0 ? 100.0 : 0.0;
        } else {
            $trend = round(($revenueWeek - $revenuePrevWeek) / $revenuePrevWeek * 100, 1);
        }

        return [
            'available' => true,
            'revenue_week' => $revenueWeek,
            'revenue_week_trend' => $trend,
        ];
    }

    public function getReportsBlock(string $steamid64): array
    {
        if (!$this->hasReportsAccess($steamid64)) {
            return [
                'available' => false,
                'report_count' => 0,
                'report_reviewed_count' => 0,
            ];
        }

        $reportStats = $this->getReportsStats();

        return [
            'available' => true,
            'report_count' => (int) ($reportStats['all_count'] ?? 0),
            'report_reviewed_count' => (int) ($reportStats['verdicted'] ?? 0),
        ];
    }

    public function countTemporaryAdmins(string $type): int
    {
        if ($type === 'cs2') {
            $backend = AdminDriverFactory::resolveCs2Backend($this->Db);
            if ($backend === 'IksAdminNew') {
                $row = $this->Db->query(
                    'IksAdminNew',
                    0,
                    0,
                    'SELECT COUNT(DISTINCT a.id) AS total
                        FROM iks_admins a
                        WHERE a.is_disabled = 0
                          AND (a.deleted_at IS NULL OR a.deleted_at = 0)
                          AND a.end_at > 0
                          AND a.end_at > UNIX_TIMESTAMP()',
                    []
                );

                return (int) ($row['total'] ?? 0);
            }
            if ($backend === 'AdminSystem') {
                $row = $this->Db->query(
                    'AdminSystem',
                    0,
                    0,
                    'SELECT COUNT(DISTINCT a.id) AS total
                        FROM as_admins a
                        INNER JOIN as_admins_servers asa ON a.id = asa.admin_id
                        WHERE asa.expires > 0 AND asa.expires > UNIX_TIMESTAMP()',
                    []
                );

                return (int) ($row['total'] ?? 0);
            }

            return 0;
        }

        if ($type === 'csgo' && !empty($this->Db->db_data['SourceBans'])) {
            $row = $this->Db->query(
                'SourceBans',
                0,
                0,
                'SELECT COUNT(DISTINCT a.aid) AS total
                    FROM sb_admins a
                    WHERE a.expired > 0 AND a.expired > UNIX_TIMESTAMP()',
                []
            );

            return (int) ($row['total'] ?? 0);
        }

        return 0;
    }

    public function countUniquePunishedPlayersSince(int $since): int
    {
        $steamids = [];
        foreach (['csgo', 'cs2'] as $type) {
            foreach ($this->fetchPunishedSteamids($type, $since) as $steamid) {
                $steamid64 = con_steam64((string) $steamid);
                if ($steamid64 === false || $steamid64 === '' || $steamid64 === '0') {
                    continue;
                }
                $steamids[$steamid64] = true;
            }
        }

        return count($steamids);
    }

    public function getPunishmentDailyStats(string $type, string $punishType, int $since): array
    {
        $issued = [];
        $removed = [];

        if ($type === 'cs2') {
            $backend = AdminDriverFactory::resolveCs2Backend($this->Db);
            if ($backend === 'IksAdminNew') {
                $table = $punishType === 'ban' ? 'iks_bans' : 'iks_comms';
                $issued = $this->mapDailyRows($this->Db->queryAll(
                    'IksAdminNew',
                    0,
                    0,
                    "SELECT FROM_UNIXTIME(created_at, '%Y-%m-%d') AS day, COUNT(*) AS cnt
                        FROM {$table}
                        WHERE created_at >= ?
                        GROUP BY day",
                    [$since]
                ));
                $removed = $this->mapDailyRows($this->Db->queryAll(
                    'IksAdminNew',
                    0,
                    0,
                    "SELECT FROM_UNIXTIME(updated_at, '%Y-%m-%d') AS day, COUNT(*) AS cnt
                        FROM {$table}
                        WHERE unbanned_by IS NOT NULL AND updated_at >= ?
                        GROUP BY day",
                    [$since]
                ));
            } elseif ($backend === 'AdminSystem' && $punishType === 'ban') {
                $issued = $this->mapDailyRows($this->Db->queryAll(
                    'AdminSystem',
                    0,
                    0,
                    "SELECT FROM_UNIXTIME(created, '%Y-%m-%d') AS day, COUNT(*) AS cnt
                        FROM as_punishments
                        WHERE created >= ? AND punish_type = 0
                        GROUP BY day",
                    [$since]
                ));
            }
        } elseif ($type === 'csgo' && !empty($this->Db->db_data['SourceBans'])) {
            $table = $punishType === 'ban' ? 'sb_bans' : 'sb_comms';
            $issued = $this->mapDailyRows($this->Db->queryAll(
                'SourceBans',
                0,
                0,
                "SELECT FROM_UNIXTIME(created, '%Y-%m-%d') AS day, COUNT(*) AS cnt
                    FROM {$table}
                    WHERE created >= ?
                    GROUP BY day",
                [$since]
            ));
            $removed = $this->mapDailyRows($this->Db->queryAll(
                'SourceBans',
                0,
                0,
                "SELECT FROM_UNIXTIME(RemovedOn, '%Y-%m-%d') AS day, COUNT(*) AS cnt
                    FROM {$table}
                    WHERE RemoveType = 'U' AND RemovedOn >= ?
                    GROUP BY day",
                [$since]
            ));
        }

        return ['issued' => $issued, 'removed' => $removed];
    }

    public function getMuteTypeDailyStats(string $type, int $since): array
    {
        $mute = [];
        $gag = [];

        if ($type === 'cs2') {
            $backend = AdminDriverFactory::resolveCs2Backend($this->Db);
            if ($backend === 'IksAdminNew') {
                $mute = $this->mapDailyRows($this->Db->queryAll(
                    'IksAdminNew',
                    0,
                    0,
                    "SELECT FROM_UNIXTIME(created_at, '%Y-%m-%d') AS day, COUNT(*) AS cnt
                        FROM iks_comms
                        WHERE created_at >= ? AND (mute_type = 0 OR mute_type IS NULL)
                        GROUP BY day",
                    [$since]
                ));
                $gag = $this->mapDailyRows($this->Db->queryAll(
                    'IksAdminNew',
                    0,
                    0,
                    "SELECT FROM_UNIXTIME(created_at, '%Y-%m-%d') AS day, COUNT(*) AS cnt
                        FROM iks_comms
                        WHERE created_at >= ? AND mute_type = 1
                        GROUP BY day",
                    [$since]
                ));
            } elseif ($backend === 'AdminSystem') {
                $mute = $this->mapDailyRows($this->Db->queryAll(
                    'AdminSystem',
                    0,
                    0,
                    "SELECT FROM_UNIXTIME(created, '%Y-%m-%d') AS day, COUNT(*) AS cnt
                        FROM as_punishments
                        WHERE created >= ? AND punish_type IN (1, 3)
                        GROUP BY day",
                    [$since]
                ));
                $gag = $this->mapDailyRows($this->Db->queryAll(
                    'AdminSystem',
                    0,
                    0,
                    "SELECT FROM_UNIXTIME(created, '%Y-%m-%d') AS day, COUNT(*) AS cnt
                        FROM as_punishments
                        WHERE created >= ? AND punish_type IN (2, 3)
                        GROUP BY day",
                    [$since]
                ));
            }
        } elseif ($type === 'csgo' && !empty($this->Db->db_data['SourceBans'])) {
            $mute = $this->mapDailyRows($this->Db->queryAll(
                'SourceBans',
                0,
                0,
                "SELECT FROM_UNIXTIME(created, '%Y-%m-%d') AS day, COUNT(*) AS cnt
                    FROM sb_comms
                    WHERE created >= ? AND (type = 1 OR type = 2)
                    GROUP BY day",
                [$since]
            ));
            $gag = $this->mapDailyRows($this->Db->queryAll(
                'SourceBans',
                0,
                0,
                "SELECT FROM_UNIXTIME(created, '%Y-%m-%d') AS day, COUNT(*) AS cnt
                    FROM sb_comms
                    WHERE created >= ? AND type = 3
                    GROUP BY day",
                [$since]
            ));
        }

        return ['mute' => $mute, 'gag' => $gag];
    }

    public function hasReportsAccess(string $steamid64): bool
    {
        if (empty($this->Db->db_data['Reports'])) {
            return false;
        }
        if (isset($_SESSION['user_admin'])) {
            return true;
        }
        if (trim($steamid64) == '') {
            return false;
        }

        return !empty($this->Db->query('Reports', 0, 0, 'SELECT `sid` FROM `rs_admins` WHERE `steamid` = ? LIMIT 1', [$steamid64]));
    }

    public function getReportsStats(): array
    {
        if (empty($this->Db->db_data['Reports'])) {
            return ['all_count' => 0, 'verdicted' => 0];
        }

        $row = $this->Db->query('Reports', 0, 0, "SELECT 
            (SELECT COUNT(*) FROM `rs_reports`) as `all_count`,
            (SELECT COUNT(*) FROM `rs_reports` WHERE `steamid_admin_verdict` IS NOT NULL) as `verdicted`
            FROM `rs_reports` LIMIT 1");

        return [
            'all_count' => (int) ($row['all_count'] ?? 0),
            'verdicted' => (int) ($row['verdicted'] ?? 0),
        ];
    }

    public function getTopReportAdmins(int $since, int $limit = 10): array
    {
        if (empty($this->Db->db_data['Reports'])) {
            return [];
        }

        $rows = $this->Db->queryAll('Reports', 0, 0, "SELECT
            `steamid_admin_verdict` AS steamid,
            COUNT(*) AS cnt
            FROM `rs_reports`
            WHERE `status` = 1
              AND `time_verdict` IS NOT NULL
              AND `steamid_admin_verdict` IS NOT NULL" . RepositoryHelper::sqlExcludeInvalidPlayerSteamid('`steamid_admin_verdict`') . "
              AND `time_verdict` >= ?
            GROUP BY `steamid_admin_verdict`
            ORDER BY cnt DESC
            LIMIT ?", [$since, $limit]);

        return is_array($rows) ? $rows : [];
    }

    public function getTopAdminsByMetric(string $type, string $metric, int $since, int $limit = 10): array
    {
        if ($metric == 'ban') {
            return $this->fetchTopPunishAdmins($type, 'ban', $since, $limit);
        }
        if ($metric == 'mute') {
            return $this->fetchTopPunishAdmins($type, 'mute', $since, $limit);
        }
        if ($metric == 'check') {
            $backend = AdminDriverFactory::resolveCs2Backend($this->Db);

            return $this->fetchTopCheckAdmins($backend !== null ? 'cs2' : 'csgo', $since, $limit);
        }

        return [];
    }

    public function hasTopAdminsByMetric(string $metric, int $since): bool
    {
        if ($metric === 'report') {
            return !empty($this->Db->db_data['Reports']);
        }

        if ($metric === 'check') {
            return $this->hasChecksBackend();
        }

        return AdminDriverFactory::resolve($this->Db, 'csgo') !== null
            || AdminDriverFactory::resolveCs2Backend($this->Db) !== null;
    }

    public function hasChecksBackend(): bool
    {
        if (!empty($this->Db->db_data['Check'])) {
            return true;
        }

        $backend = AdminDriverFactory::resolveCs2Backend($this->Db);

        return $backend === 'AdminSystem' || $backend === 'IksAdminNew';
    }

    private function fetchTopPunishAdmins(string $type, string $punishType, int $since, int $limit): array
    {
        if ($type == 'cs2') {
            $backend = AdminDriverFactory::resolveCs2Backend($this->Db);
            if ($backend === 'IksAdminNew') {
                $table = $punishType == 'ban' ? 'iks_bans' : 'iks_comms';
                $skipConsole = RepositoryHelper::sqlExcludeIksAdminConsole('a');

                return $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT a.steam_id AS steamid, MAX(a.name) AS name, COUNT(*) AS cnt
                    FROM {$table} p
                    JOIN iks_admins a ON a.id = p.admin_id
                    WHERE p.created_at >= ?{$skipConsole}
                    GROUP BY a.steam_id
                    ORDER BY cnt DESC
                    LIMIT ?", [$since, $limit]) ?: [];
            }
            if ($backend === 'AdminSystem') {
                $skipConsole = RepositoryHelper::sqlExcludeAdminSystemConsole('a');
                $typeSql = $punishType == 'ban' ? 'p.punish_type = 0' : 'p.punish_type IN (1, 2, 3)';

                return $this->Db->queryAll('AdminSystem', 0, 0, 'SELECT a.steamid, MAX(a.name) AS name, COUNT(*) AS cnt
                    FROM as_punishments p
                    JOIN as_admins a ON a.id = p.admin_id
                    WHERE p.created >= ? AND ' . $typeSql . $skipConsole . '
                    GROUP BY a.steamid
                    ORDER BY cnt DESC
                    LIMIT ?', [$since, $limit]) ?: [];
            }

            return [];
        }

        if ($type == 'csgo' && !empty($this->Db->db_data['SourceBans'])) {
            $table = $punishType == 'ban' ? 'sb_bans' : 'sb_comms';
            $skipConsole = RepositoryHelper::sqlExcludeSourceBansConsole('a');

            return $this->Db->queryAll('SourceBans', 0, 0, "SELECT a.authid AS steamid, MAX(a.user) AS name, COUNT(*) AS cnt
                FROM {$table} p
                JOIN sb_admins a ON a.aid = p.aid
                WHERE p.created >= ?{$skipConsole}
                GROUP BY a.authid
                ORDER BY cnt DESC
                LIMIT ?", [$since, $limit]) ?: [];
        }

        return [];
    }

    private function fetchTopCheckAdmins(string $type, int $since, int $limit): array
    {
        if ($type === 'cs2') {
            $backend = AdminDriverFactory::resolveCs2Backend($this->Db);
            if ($backend === 'IksAdminNew') {
                $skipConsole = RepositoryHelper::sqlExcludeIksAdminConsole('a');

                return $this->Db->queryAll('IksAdminNew', 0, 0, 'SELECT a.steam_id AS steamid, MAX(a.name) AS name, COUNT(*) AS cnt
                    FROM iks_check_results cr
                    JOIN iks_admins a ON cr.admin_id = a.id
                    WHERE cr.created_at >= ?' . $skipConsole . '
                    GROUP BY a.steam_id
                    ORDER BY cnt DESC
                    LIMIT ?', [$since, $limit]) ?: [];
            }

            if ($backend === 'AdminSystem') {
                $skipInvalid = RepositoryHelper::sqlExcludeInvalidPlayerSteamid('`admin_steamid`');

                return $this->Db->queryAll('AdminSystem', 0, 0, "SELECT `admin_steamid` AS steamid, MAX(`admin_name`) AS name, COUNT(*) AS cnt
                    FROM `checkcheats_stats`
                    WHERE `datestart` >= ?{$skipInvalid}
                    GROUP BY `admin_steamid`
                    ORDER BY cnt DESC
                    LIMIT ?", [$since, $limit]) ?: [];
            }
        }

        if ($type === 'csgo' && !empty($this->Db->db_data['Check'])) {
            $table = (string) ($this->Db->db_data['Check'][0]['Table'] ?? 'checkcheats_stats');
            $skipInvalid = RepositoryHelper::sqlExcludeInvalidPlayerSteamid('`admin_steamid`');

            return $this->Db->queryAll('Check', 0, 0, "SELECT `admin_steamid` AS steamid, MAX(`admin_name`) AS name, COUNT(*) AS cnt
                FROM `{$table}`
                WHERE `datestart` >= ?{$skipInvalid}
                GROUP BY `admin_steamid`
                ORDER BY cnt DESC
                LIMIT ?", [$since, $limit]) ?: [];
        }

        return [];
    }

    private function mapDailyRows(?array $rows): array
    {
        $out = [];
        if (!is_array($rows)) {
            return $out;
        }
        foreach ($rows as $row) {
            if (empty($row['day'])) {
                continue;
            }
            $out[(string) $row['day']] = (int) ($row['cnt'] ?? 0);
        }

        return $out;
    }

    private function fetchPunishedSteamids(string $type, int $since): array
    {
        if ($type == 'cs2') {
            $backend = AdminDriverFactory::resolveCs2Backend($this->Db);
            if ($backend === 'IksAdminNew') {
                $skipInvalid = RepositoryHelper::sqlExcludeInvalidPlayerSteamid('`steam_id`');
                $rows = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT DISTINCT `steam_id` AS steamid FROM `iks_bans` WHERE `created_at` >= ?{$skipInvalid}
                    UNION SELECT DISTINCT `steam_id` AS steamid FROM `iks_comms` WHERE `created_at` >= ?{$skipInvalid}", [$since, $since]);
            } elseif ($backend === 'AdminSystem') {
                $skipInvalid = RepositoryHelper::sqlExcludeInvalidPlayerSteamid('`steamid`');
                $rows = $this->Db->queryAll('AdminSystem', 0, 0, 'SELECT DISTINCT `steamid` AS steamid FROM `as_punishments` WHERE `created` >= ?' . $skipInvalid, [$since]);
            } else {
                return [];
            }
        } elseif ($type == 'csgo' && !empty($this->Db->db_data['SourceBans'])) {
            $skipInvalid = RepositoryHelper::sqlExcludeInvalidPlayerSteamid('`authid`');
            $rows = $this->Db->queryAll('SourceBans', 0, 0, "SELECT DISTINCT `authid` AS steamid FROM `sb_bans` WHERE `created` >= ?{$skipInvalid}
                UNION SELECT DISTINCT `authid` AS steamid FROM `sb_comms` WHERE `created` >= ?{$skipInvalid}", [$since, $since]);
        } else {
            return [];
        }

        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!empty($row['steamid'])) {
                $out[] = (string) $row['steamid'];
            }
        }

        return $out;
    }
}
