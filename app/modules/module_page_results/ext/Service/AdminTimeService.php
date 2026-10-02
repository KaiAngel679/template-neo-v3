<?php

namespace app\modules\module_page_results\ext\Service;

use app\modules\module_page_help\ext\Help;
use app\modules\module_page_results\ext\Repository\AccessRepository;
use app\modules\module_page_results\ext\Repository\AdminRepository;
use app\modules\module_page_results\ext\Repository\ServerRepository;
use app\modules\module_page_results\ext\Repository\StatsRepository;
use app\modules\module_page_results\ext\Repository\SettingsRepository;
use app\modules\module_page_results\ext\Service\Helper;

class AdminTimeService
{

  private $adminRepository;
  private $serverRepository;
  private $statsRepository;
  private $settingsRepository;
  private $General;
  private $Modules;
  private $Translate;
  private $accessRepository;

  public function __construct(
    $General,
    $Translate,
    $Modules,
    $Db
  ) {
    $this->accessRepository = new AccessRepository($Db);
    $this->adminRepository = new AdminRepository($Db);
    $this->serverRepository = new serverRepository();
    $this->statsRepository = new statsRepository($Db, $Translate);
    $this->settingsRepository = new settingsRepository();
    $this->General = $General;
    $this->Modules = $Modules;
    $this->Translate = $Translate;
  }

  public function getAdminTimeData(array $postData, bool $only_theirs = false): array
  {
    $perPage = 10;
    $page = (int)($postData['page'] ?? 1);
    $server = Helper::normalizeArrayFilter($postData['server'] ?? []);
    $group = Helper::normalizeArrayFilter($postData['group'] ?? []);
    $timePlayedEnable = !empty($postData['timePlayedEnable']);
    $searchAdmin = trim($postData['searchAdmin'] ?? '');
    $sort = $postData['sort'] ?? 'group';
    $order = $postData['order'] ?? 'asc';

    $date_start = ($dateFrom = $postData['dateFrom'] ?? null) ? strtotime($dateFrom . ' 00:00:00') : null;
    $date_end = ($dateTo = $postData['dateTo'] ?? null) ? strtotime($dateTo . ' 23:59:59') : null;

    $settings = $this->settingsRepository->getSettings();
    $access = $this->accessRepository->checkAccess($_SESSION['steamid64'] ?? '');
    $minTimeHours = (int)($settings['time'] ?? 0);
    $hasAccess = $access && (empty($access['awardwarns']) && empty($access['full']));

    $server_sids_str = implode(',', $this->serverRepository->getFilteredSids($server));
    $server_rids_str = implode(',', $this->serverRepository->getFilteredRids($server));

    $groupsMap = [];
    foreach ($this->adminRepository->getGroups() as $g) {
      $groupsMap[(string)$g['id']] = ['name' => $g['name'], 'immunity' => (int)($g['immunity'] ?? 0)];
    }

    $admins = $only_theirs
      ? $this->adminRepository->getBySteamArray($_SESSION['steamid64'] ?? '')
      : $this->adminRepository->getAll();

    $preFilteredAdmins = [];
    foreach ($admins as $admin) {
      if (!Helper::matchesArrayFilter($server, explode(';', $admin['server'] ?? ''))) continue;
      if (!Helper::matchesArrayFilter($group, [$admin['group'] ?? ''])) continue;

      if (
        $searchAdmin && stripos($this->General->checkName($admin['steam']), $searchAdmin) === false
        && stripos($admin['steam'] ?? '', $searchAdmin) === false
      ) continue;

      $preFilteredAdmins[$admin['steam']] = $admin;
    }

    if (empty($preFilteredAdmins)) {
      return ['data' => [], 'page_max' => 1];
    }

    $sortFields = [
      'playtime' => 'time_played_value',
      'bans' => 'bans',
      'gags' => 'mutes_gags',
      'checks' => 'checks',
      'reports' => 'reports',
      'group' => 'immunity'
    ];
    $field = $sortFields[$sort] ?? 'immunity';
    $dir = $order === 'desc' ? -1 : 1;

    if ($sort === 'group' && !$timePlayedEnable) {
      foreach ($preFilteredAdmins as &$admin) {
        $admin['immunity'] = $groupsMap[$admin['group']]['immunity'] ?? 0;
      }
      unset($admin);
    }

    $steamids = array_column($preFilteredAdmins, 'steam');
    $playedTimeData = $this->statsRepository->getAdminPlayedTimeBatch($steamids, $server_sids_str, $date_start, $date_end);
    $statsData = $this->statsRepository->getAdminInfoBatch($steamids, $server_sids_str, $date_start, $date_end);
    $reportsData = $this->statsRepository->getReportsCountBatch($steamids, $server_rids_str, $date_start, $date_end);
    $checksData = $this->statsRepository->getCheckCountBatch($steamids, $server_sids_str, $date_start, $date_end);

    $filteredAdmins = [];
    foreach ($preFilteredAdmins as $admin) {
      $steamid = $admin['steam'];
      $playedTime = $playedTimeData[$steamid] ?? ['total_played' => 0, 'sessions_count' => 0];
      $totalHours = (int)($playedTime['total_played'] ?? 0) / 3600;

      if ($timePlayedEnable && $totalHours < $minTimeHours) continue;

      $stats = $statsData[$steamid] ?? ['bans_count' => 0, 'mutes_count' => 0, 'gags_count' => 0];
      $groupInfo = $groupsMap[$admin['group']] ?? ['name' => '', 'immunity' => 0];

      $filteredAdmins[$steamid] = [
        'steam' => $steamid,
        'name' => $this->General->checkName($steamid),
        'checked_avatar' => $this->General->checkAvatar($steamid),
        'group' => $admin['group'],
        'group_name' => $groupInfo['name'],
        'immunity' => $groupInfo['immunity'],
        'time_played_value' => $totalHours,
        'time_played' => $this->hoursFormatted($playedTime['total_played'] ?? 0),
        'sessions' => $playedTime['sessions_count'] ?? 0,
        'bans' => $stats['bans_count'] ?? 0,
        'mutes' => $stats['mutes_count'] ?? 0,
        'gags' => $stats['gags_count'] ?? 0,
        'mutes_gags' => ($stats['mutes_count'] ?? 0) + ($stats['gags_count'] ?? 0),
        'reports' => $reportsData[$steamid] ?? 0,
        'checks' => $checksData[$steamid] ?? 0,
        'access' => !$hasAccess,
      ];
    }

    $filteredAdmins = array_values($filteredAdmins);
    usort($filteredAdmins, fn($a, $b) => ($a[$field] <=> $b[$field]) * $dir);

    $totalPages = max(1, (int)ceil(count($filteredAdmins) / $perPage));
    $page = max(1, min($page, $totalPages));

    return [
      'data' => array_slice($filteredAdmins, ($page - 1) * $perPage, $perPage),
      'page_max' => $totalPages,
    ];
  }

  public function getSessionsData(array $postData): array
  {
    $perPage = 10;
    $page = (int)($postData['page'] ?? 1);
    $steamid = $postData['steamid'] ?? null;
    $dateFrom = $postData['dateFrom'] ?? null;
    $dateTo = $postData['dateTo'] ?? null;
    $server = Helper::normalizeArrayFilter($postData['server'] ?? []);
    $date_start = $dateFrom ? strtotime($dateFrom . ' 00:00:00') : null;
    $date_end = $dateTo ? strtotime($dateTo . ' 23:59:59') : null;

    $server_sids = $this->serverRepository->getFilteredSids($server);
    $server_ids_str = implode(',', $server_sids);

    $sessions = $this->statsRepository->getAdminSessions($steamid, $server_ids_str, $date_start, $date_end);
    $serverNames = $this->serverRepository->getSidMap();

    foreach ($sessions as &$session) {
      $session['server_name'] = $serverNames[$session['server_id']] ?? '';
      $session['played_time'] = $this->hoursFormatted($session['played_time'] ?? 0);
      $session['connect_time'] = date('H:i:s', $session['connect_time'] ?? 0);
      $session['disconnect_time'] = date('H:i:s', $session['disconnect_time'] ?? 0);
    }

    $total = count($sessions);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));

    return [
      'lable' => $this->Translate->get_translate_module_phrase('module_page_results', '_sessions') . ': ' . $dateFrom . ' - ' . $dateTo,
      'data' => array_slice($sessions, ($page - 1) * $perPage, $perPage),
      'page_max' => $totalPages
    ];
  }

  private function hoursFormatted(int $seconds): string
  {
    if ($seconds <= 0) {
      return $this->Translate->get_translate_module_phrase('module_page_results', '_nodata');
    }

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    $parts = [];
    if ($hours > 0) {
      $parts[] = $hours . $this->Translate->get_translate_module_phrase('module_page_results', '_h');
    }
    if ($minutes > 0) {
      $parts[] = $minutes . $this->Translate->get_translate_module_phrase('module_page_results', '_m');
    }
    if ($remainingSeconds > 0 || empty($parts)) {
      $parts[] = $remainingSeconds . $this->Translate->get_translate_module_phrase('module_page_results', '_s');
    }

    return implode(' ', $parts);
  }
}
