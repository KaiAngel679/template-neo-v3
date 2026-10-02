<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\DashboardRepository;

class MainService
{
    private $DashboardRepository, $ServersService, $RendersService, $General, $Db, $Translate;

    public function __construct(object $Db, object $General, object $Translate)
    {
        $this->Db = $Db;
        $this->Translate = $Translate;
        $this->DashboardRepository = new DashboardRepository($Db);
        $this->ServersService = new ServersService($General);
        $this->RendersService = new RendersService($Db, $General, $Translate);
        $this->General = $General;
    }

    public function getPageStats(): array
    {
        $adminPunishment = $this->DashboardRepository->getAdminPunishmentCounts();
        $checks = $this->DashboardRepository->getChecksCounts();
        $vipStats = $this->DashboardRepository->aggregateVipStats(
            $this->buildVipTargets(),
            $this->RendersService->hiddenVipTestGroupIni()
        );
        $reports = $this->DashboardRepository->getReportsBlock((string) ($_SESSION['steamid64'] ?? ''));
        $violators = $this->DashboardRepository->getViolatorsDayStats();
        $revenue = $this->DashboardRepository->getRevenueWeekStats();

        $adminCount = (int) $adminPunishment['admin_count'];
        $temporaryAdminCount = (int) $adminPunishment['temporary_admin_count'];
        $muteCount = (int) $adminPunishment['mute_count'];
        $banCount = (int) $adminPunishment['ban_count'];
        $activeMuteCount = (int) $adminPunishment['active_mute_count'];
        $activeBanCount = (int) $adminPunishment['active_ban_count'];
        $vipTotal = (int) $vipStats['total'];
        $vipForever = (int) $vipStats['forever'];
        $reportCount = (int) $reports['report_count'];
        $reportReviewed = (int) $reports['report_reviewed_count'];
        $playersDay = (int) $violators['players_day'];
        $punishedDay = (int) $violators['punished_day'];
        $revenueWeek = (float) $revenue['revenue_week'];
        $revenueWeekTrend = (float) $revenue['revenue_week_trend'];

        return [
            'admin_count' => $adminCount,
            'temporary_admin_count' => $temporaryAdminCount,
            'temporary_admin_percent' => $this->calcPercent($temporaryAdminCount, $adminCount),
            'vip_count' => $vipTotal,
            'vip_forever_count' => $vipForever,
            'vip_forever_percent' => $this->calcPercent($vipForever, $vipTotal),
            'mute_count' => $muteCount,
            'ban_count' => $banCount,
            'active_mute_count' => $activeMuteCount,
            'active_ban_count' => $activeBanCount,
            'mute_active_percent' => $this->calcPercent($activeMuteCount, $muteCount),
            'ban_active_percent' => $this->calcPercent($activeBanCount, $banCount),
            'check_count' => (int) $checks['check_count'],
            'check_count_30' => (int) $checks['check_count_30'],
            'reports_available' => (bool) $reports['available'],
            'report_count' => $reportCount,
            'report_reviewed_count' => $reportReviewed,
            'report_reviewed_percent' => $this->calcPercent($reportReviewed, $reportCount),
            'violators_day_available' => (bool) $violators['available'],
            'players_day_count' => $playersDay,
            'punished_day_count' => $punishedDay,
            'violators_day_percent' => $this->calcPercent($punishedDay, $playersDay),
            'revenue_available' => (bool) $revenue['available'],
            'revenue_week' => $revenueWeek,
            'revenue_week_formatted' => number_format($revenueWeek, 0, '.', ' '),
            'revenue_week_trend' => $revenueWeekTrend,
            'revenue_trend_up' => $revenueWeekTrend >= 0,
            'revenue_trend_label' => ($revenueWeekTrend >= 0 ? '+' : '') . number_format($revenueWeekTrend, 1, '.', '') . '%',
        ];
    }

    public function getCharts(int $days, string $chart): array
    {
        $days = max(1, min(90, $days));
        $since = strtotime('-' . ($days - 1) . ' days 00:00:00');
        $labels = [];
        $keys = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $key = date('Y-m-d', strtotime('-' . $i . ' days'));
            $keys[] = $key;
            $labels[] = date('d.m', strtotime($key));
        }

        if ($chart == 'mutes') {
            $muteMap = [];
            $gagMap = [];
            foreach (['csgo', 'cs2'] as $type) {
                $part = $this->DashboardRepository->getMuteTypeDailyStats($type, $since);
                foreach ($part['mute'] as $day => $count) {
                    $muteMap[$day] = ($muteMap[$day] ?? 0) + $count;
                }
                foreach ($part['gag'] as $day => $count) {
                    $gagMap[$day] = ($gagMap[$day] ?? 0) + $count;
                }
            }

            return [
                'status' => 'success',
                'labels' => $labels,
                'series' => [
                    ['name' => ModuleHelper::phrase($this->Translate, '_at_chartMute'), 'data' => $this->buildChartSeries($keys, $muteMap)],
                    ['name' => ModuleHelper::phrase($this->Translate, '_at_chartGag'), 'data' => $this->buildChartSeries($keys, $gagMap)],
                ],
            ];
        }

        $issuedMap = [];
        $removedMap = [];
        foreach (['csgo', 'cs2'] as $type) {
            $part = $this->DashboardRepository->getPunishmentDailyStats($type, 'ban', $since);
            foreach ($part['issued'] as $day => $count) {
                $issuedMap[$day] = ($issuedMap[$day] ?? 0) + $count;
            }
            foreach ($part['removed'] as $day => $count) {
                $removedMap[$day] = ($removedMap[$day] ?? 0) + $count;
            }
        }

        return [
            'status' => 'success',
            'labels' => $labels,
            'series' => [
                ['name' => ModuleHelper::phrase($this->Translate, '_at_chartIssued'), 'data' => $this->buildChartSeries($keys, $issuedMap)],
                ['name' => ModuleHelper::phrase($this->Translate, '_at_chartRemoved'), 'data' => $this->buildChartSeries($keys, $removedMap)],
            ],
        ];
    }

    public function getTopAdmins(string $metric, int $limit = 10): array
    {
        if (!in_array($metric, ['ban', 'mute', 'check', 'report'], true)) {
            $metric = 'ban';
        }

        $since = strtotime(date('Y-m-01 00:00:00'));
        $merged = [];

        if ($metric == 'report') {
            if (!$this->DashboardRepository->hasReportsAccess((string) ($_SESSION['steamid64'] ?? ''))) {
                return ['status' => 'success', 'data' => [], 'metric' => $metric];
            }
            foreach ($this->DashboardRepository->getTopReportAdmins($since, $limit) as $row) {
                $steamid = con_steam64((string) ($row['steamid'] ?? ''));
                if ($steamid == '') {
                    continue;
                }
                $merged[$steamid] = [
                    'steamid' => $steamid,
                    'name' => '',
                    'count' => (int) ($row['cnt'] ?? 0),
                ];
            }
        } else {
            $types = $metric === 'check' ? ['cs2'] : ['csgo', 'cs2'];
            foreach ($types as $type) {
                foreach ($this->DashboardRepository->getTopAdminsByMetric($type, $metric, $since, $limit) as $row) {
                    $steamid = con_steam64((string) ($row['steamid'] ?? ''));
                    if ($steamid == '') {
                        continue;
                    }
                    if (!isset($merged[$steamid])) {
                        $merged[$steamid] = [
                            'steamid' => $steamid,
                            'name' => (string) ($row['name'] ?? ''),
                            'count' => 0,
                        ];
                    }
                    $merged[$steamid]['count'] += (int) ($row['cnt'] ?? 0);
                }
            }
        }

        usort($merged, function (array $a, array $b) {
            return $b['count'] <=> $a['count'];
        });
        $merged = array_slice(array_values($merged), 0, $limit);

        $data = [];
        foreach ($merged as $row) {
            $data[] = [
                'steamid' => $row['steamid'],
                'name' => ModuleHelper::resolveDisplayName($this->General, $row['steamid'], $row['name'] != '' ? $row['name'] : null),
                'avatar' => $this->General->getAvatar($row['steamid'], 3),
                'count' => $row['count'],
            ];
        }

        return ['status' => 'success', 'data' => $data, 'metric' => $metric];
    }

    public function getTopMetrics(array $allowed): array
    {
        $since = strtotime(date('Y-m-01 00:00:00'));
        $labels = [
            'ban' => ['label' => ModuleHelper::phrase($this->Translate, '_at_topMetricBans'), 'icon' => 'block'],
            'mute' => ['label' => ModuleHelper::phrase($this->Translate, '_at_topMetricMutes'), 'icon' => 'micro-slash'],
            'check' => ['label' => ModuleHelper::phrase($this->Translate, '_at_topMetricChecks'), 'icon' => 'check-circle'],
            'report' => ['label' => ModuleHelper::phrase($this->Translate, '_at_topMetricReports'), 'icon' => 'list-info'],
        ];
        $out = [];

        foreach ($allowed as $metric) {
            if (!isset($labels[$metric])) {
                continue;
            }
            if ($metric === 'report' && !$this->DashboardRepository->hasReportsAccess((string) ($_SESSION['steamid64'] ?? ''))) {
                continue;
            }
            if ($metric === 'check' && !$this->DashboardRepository->hasChecksBackend()) {
                continue;
            }
            if (!$this->DashboardRepository->hasTopAdminsByMetric($metric, $since)) {
                continue;
            }

            $out[] = [
                'metric' => $metric,
                'label' => $labels[$metric]['label'],
                'icon' => $labels[$metric]['icon'],
                'active' => $out == [],
            ];
        }

        return $out;
    }

    public function hasReportsAccess(): bool
    {
        return $this->DashboardRepository->hasReportsAccess((string) ($_SESSION['steamid64'] ?? ''));
    }

    public function hasChecksBackend(): bool
    {
        return $this->DashboardRepository->hasChecksBackend();
    }

    private function buildVipTargets(): array
    {
        $targets = [];
        foreach ($this->ServersService->returnServersByIds(array_column($this->ServersService->getServers(), 'id')) as $server) {
            $serverVip = trim((string) ($server['server_vip'] ?? ''));
            $sid = (int) ($server['server_vip_id'] ?? 0);
            if ($serverVip == '' || $sid <= 0) {
                continue;
            }
            $key = $serverVip . '|' . $sid;
            if (!isset($targets[$key])) {
                $targets[$key] = ['server_vip' => $serverVip, 'sid' => $sid];
            }
        }

        return array_values($targets);
    }

    private function calcPercent(int $part, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round($part / $total * 100, 2);
    }

    private function buildChartSeries(array $keys, array $map): array
    {
        $out = [];
        foreach ($keys as $key) {
            $out[] = (int) ($map[$key] ?? 0);
        }

        return $out;
    }
}
