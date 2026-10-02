<?php

namespace app\modules\module_page_results\ext\Service;
use app\modules\module_page_results\ext\Repository\ResultRepository;
use app\modules\module_page_results\ext\Repository\AccessRepository;
use app\modules\module_page_results\ext\Repository\SettingsRepository;

class ResultsService
{
  private $resultRepository;
  private $settingsRepository;
  private $accessRepository;
  private $General;
  private $Modules;
  private $Translate;

  public function __construct(
    $Db,
    $General,
    $Modules,
    $Translate
  ) {
    $this->accessRepository = new AccessRepository($Db);
    $this->resultRepository = new ResultRepository();
    $this->settingsRepository = new SettingsRepository();
    $this->General = $General;
    $this->Modules = $Modules;
    $this->Translate = $Translate;
  }



  public function renderResults($postData, bool $onlyTheirs = false): array
  {
    $page = (int)($postData['page'] ?? 1);
    $server = (int)($postData['server'] ?? 0);
    $results = $this->resultRepository->getAll();
    $allServers = [];
    foreach ($results as $index => $result) {
      $servers = $result['servers'] ?? [];
      if ($server > 0) {
        $servers = array_filter($servers, function ($s) use ($server) {
          return (int)($s['server_id'] ?? 0) === $server;
        });
      }
      foreach ($servers as $serverItem) {
        if ($onlyTheirs) {
          $admins = $serverItem['admins'] ?? [];
          $found = false;
          foreach ($admins as $admin) {
            if (($admin['steamid'] ?? '') === ($_SESSION['steamid64'] ?? '')) {
              $found = true;
              break;
            }
          }
          if (!$found) {
            continue;
          }
        }
        $allServers[] = [
          'new' => $index === 0,
          'result_id' => $result['id'],
          'period_start' => $result['period_start'],
          'period_end' => $result['period_end'],
          'period_label' => $result['period_label'],
          'created_at' => $result['created_at'],
          'server' => $serverItem,
        ];
      }
    }

    $perPage = 30;
    $totalServers = count($allServers);
    $totalPages = $totalServers > 0 ? (int)ceil($totalServers / $perPage) : 1;
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    $pagedServers = array_slice($allServers, $offset, $perPage);

    $summary = [];
    foreach ($pagedServers as $i => $item) {
      $summary[] = [
        'new' => $item['new'] ?? false,
        'id' => $item['result_id'],
        'period_start' => $item['period_start'],
        'period_end' => $item['period_end'],
        'period_label' => $item['period_label'],
        'servers' => [
          $item['server']
        ],
        'created_at' => $item['created_at'],
      ];
    }

    return [
      'results' => $summary,
      'page_max' => $totalPages,
    ];
  }

  public function renderResultDetails(int $id, int $serverId, bool $onlyTheirs = false): array
  {
    $result = $this->resultRepository->getById($id);
    $settings = $this->settingsRepository->getSettings();
    $access = $this->accessRepository->checkAccess($_SESSION['steamid64']);

    if (empty($result) || (int)($result['id'] ?? 0) !== $id) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_ResultNotFound')];
    }

    $servers = $result['servers'] ?? [];
    foreach ($servers as $server) {
      if ((int)($server['server_id'] ?? 0) === $serverId) {
        $admins = $server['admins'] ?? [];
        if ($onlyTheirs) {
          $found = false;
          $currentUserSteamId = $_SESSION['steamid64'] ?? '';
          foreach ($admins as $admin) {
            if (($admin['steamid'] ?? '') === $currentUserSteamId) {
              $found = true;
              break;
            }
          }
          if (!$found) {
            return ['error' => $this->Translate->get_translate_phrase('_accessDenied')];
          }
          $admins = array_filter($admins, function($admin) use ($currentUserSteamId) {
            return ($admin['steamid'] ?? '') === $currentUserSteamId;
          });
          $admins = array_values($admins);
        }
        foreach ($admins as &$admin) {
          $minTimeSeconds = (int)($settings['time'] ?? 0) * 3600;
          $admin['name'] = $this->General->checkName($admin['steamid'] ?? '');
          $admin['checked_avatar'] = $this->General->checkAvatar($admin['steamid'] ?? '');
          $admin['verdict'] = empty($admin['played_time'])
            ? $this->Translate->get_translate_phrase('_Unknown')
            : ($admin['played_time'] >= $minTimeSeconds
              ? $this->Translate->get_translate_module_phrase('module_page_results', '_NormCompleted')
              : $this->Translate->get_translate_module_phrase('module_page_results', '_NormNotCompleted'));
          $admin['verdict_class'] = empty($admin['played_time'])
            ? ''
            : ($admin['played_time'] >= $minTimeSeconds ? 'completed' : 'not-completed');
          $admin['time_played'] = empty($admin['played_time'])
            ? $this->Translate->get_translate_module_phrase('module_page_results', '_noData')
            : $this->hoursFormatted($admin['played_time']);
          $admin['sessions_count'] = $admin['sessions_count'] ?? 0;
        }
        $skipedAdmins = [];
        foreach($server['skiped_admins'] ?? [] as $item) {
          $steamid = is_array($item) && isset($item['steamid']) ? $item['steamid'] : (is_object($item) ? $item->steamid : $item);
          if (is_array($steamid)) {
            $steamid = $steamid['steamid'] ?? '';
          } elseif (is_object($steamid)) {
            $steamid = $steamid->steamid ?? '';
          }
          if (!empty($steamid)) {
            $skipedAdmins[] = [
              'steamid' => $steamid,
              'name' => $this->General->checkName($steamid),
              'checked_avatar' => $this->General->checkAvatar($steamid),
            ];
          }
        }
        return [
          'result_id' => $result['id'],
          'server_id' => $serverId,
          'result' => $admins,
          'server' => $server['server_name'] ?? '',
          'award_taken' => $access && (empty($access['awardwarns']) && empty($access['full'])) ? true : $server['award_taken'] ?? false,
          'period_label' => $result['period_label'] ?? '',
          'admins_skiped' => $skipedAdmins
        ];
      }
    }

    return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_ResultNotFound')];
  }

  public function getAll(): array
  {
    return $this->resultRepository->getAll();
  }

  public function getById(int $id): ?array
  {
    return $this->resultRepository->getById($id);
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
