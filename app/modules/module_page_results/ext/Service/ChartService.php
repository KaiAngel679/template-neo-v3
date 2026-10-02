<?php

namespace app\modules\module_page_results\ext\Service;

use app\modules\module_page_results\ext\Repository\AdminRepository;
use app\modules\module_page_results\ext\Repository\ServerRepository;
use app\modules\module_page_results\ext\Repository\StatsRepository;
use app\modules\module_page_results\ext\Service\Helper;

class ChartService
{
  private $adminRepository;
  private $serverRepository;
  private $statsRepository;
  private $Translate;

  public function __construct($Db, $Translate)
  {
    $this->adminRepository = new AdminRepository($Db);
    $this->serverRepository = new ServerRepository();
    $this->statsRepository = new StatsRepository($Db, $Translate);
    $this->Translate = $Translate;
  }

  public function getChartsData(array $postData): array
  {
    $steamid = $postData['steamid'] ?? null;
    if (empty($steamid)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_InvalidSteamID')];
    }

    $dateFrom = $postData['dateFrom'] ?? null;
    $dateTo = $postData['dateTo'] ?? null;
    $date_start = $dateFrom ? strtotime($dateFrom . ' 00:00:00') : null;
    $date_end = $dateTo ? strtotime($dateTo . ' 23:59:59') : null;

    $server = Helper::normalizeArrayFilter($postData['server'] ?? []);
    $server_sids = $this->serverRepository->getFilteredSids($server);
    $server_sids_str = implode(',', $server_sids);
    $server_rids = $this->serverRepository->getFilteredRids($server);
    $server_rids_str = implode(',', $server_rids);

    $admins = $this->adminRepository->getAll();
    $filteredAdmins = array_filter($admins, function ($admin) use ($steamid) {
      return $admin['steam'] === $steamid;
    });

    if (empty($filteredAdmins)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_AdminNotFound')];
    }

    return [
      'categories' => $this->getDateLabels($date_start, $date_end),
      'playtime' => $this->getDataByDays($filteredAdmins, $server_sids_str, $server_rids_str, $date_start, $date_end, 'playtime'),
      'bans' => $this->getDataByDays($filteredAdmins, $server_sids_str, $server_rids_str, $date_start, $date_end, 'bans'),
      'mutes_gags' => $this->getDataByDays($filteredAdmins, $server_sids_str, $server_rids_str, $date_start, $date_end, 'mutes_gags'),
      'reports' => $this->getDataByDays($filteredAdmins, $server_sids_str, $server_rids_str, $date_start, $date_end, 'reports'),
      'checks' => $this->getDataByDays($filteredAdmins, $server_sids_str, $server_rids_str, $date_start, $date_end, 'checks'),
    ];
  }

  private function getDateLabels(int $date_start, int $date_end): array
  {
    $labels = [];
    $current = $date_start;
    while ($current <= $date_end) {
      $labels[] = date('d.m.Y', $current);
      $current += 24 * 3600;
    }
    return $labels;
  }

  private function getDataByDays(array $admins, string $serverSidsStr, string $serverRidsStr, int $date_start, int $date_end, string $type): array
  {
    $steamids = array_column($admins, 'steam');
    if (empty($steamids)) return [];
    $steamid = $steamids[0];

    $days = [];
    $current = $date_start;
    while ($current <= $date_end) {
      $days[date('Y-m-d', $current)] = 0;
      $current += 86400;
    }

    switch ($type) {
      case 'playtime':
        $rows = $this->statsRepository->getPlaytimeByDays($steamid, $serverSidsStr, $date_start, $date_end);
        foreach ($rows as $r) $days[$r['day']] = round(($r['total'] ?? 0) / 3600, 2);
        break;
      case 'bans':
      case 'mutes_gags':
        $rows = $this->statsRepository->getPunishmentsByDays($steamid, $serverSidsStr, $date_start, $date_end);
        foreach ($rows as $r) {
          if ($type === 'bans') $days[$r['day']] = (int)($r['bans'] ?? 0);
          else $days[$r['day']] = (int)(($r['mutes'] ?? 0) + ($r['gags'] ?? 0));
        }
        break;
      case 'reports':
        $rows = $this->statsRepository->getReportsByDays($steamid, $serverRidsStr, $date_start, $date_end);
        foreach ($rows as $r) $days[$r['day']] = (int)($r['cnt'] ?? 0);
        break;
      case 'checks':
        $rows = $this->statsRepository->getChecksByDays($steamid, $serverSidsStr, $date_start, $date_end);
        foreach ($rows as $r) $days[$r['day']] = (int)($r['cnt'] ?? 0);
        break;
    }
    return array_values($days);
  }
}
