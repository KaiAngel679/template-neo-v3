<?php

namespace app\modules\module_page_results\ext\Service;

use app\modules\module_page_results\ext\Repository\AdminRepository;
use app\modules\module_page_results\ext\Repository\ServerRepository;
use app\modules\module_page_results\ext\Repository\ResultRepository;
use app\modules\module_page_results\ext\Repository\AwardRepository;
use app\modules\module_page_results\ext\Repository\StatsRepository;
use app\modules\module_page_results\ext\Repository\SettingsRepository;
use app\modules\module_page_results\ext\Service\LogsService;


class ReportService
{
  private $adminRepository;
  private $serverRepository;
  private $resultRepository;
  private $awardRepository;
  private $statsRepository;
  private $settingsRepository;
  private $logsService;
  private $General;
  private $Translate;


  public function __construct($Db, $General, $Translate)
  {
    $this->adminRepository = new AdminRepository($Db);
    $this->serverRepository = new ServerRepository();
    $this->resultRepository = new ResultRepository();
    $this->awardRepository = new AwardRepository();
    $this->statsRepository = new StatsRepository($Db, $Translate);
    $this->logsService = new LogsService($Translate, $General);
    $this->settingsRepository = new SettingsRepository();
    $this->General = $General;
    $this->Translate = $Translate;
  }

  public function generateWeeklyReports($referenceTime = null, $force = false): array
  {
    $period = $this->getPreviousWeekPeriod($referenceTime);
    $periodId = $this->resultRepository->getOrCreatePeriodId($period['start']);

    $admins = $this->adminRepository->getAll();
    $groups = $this->adminRepository->getGroups();
    $groupsById = array_column($groups, 'name', 'id');

    $servers = $this->serverRepository->getAll();
    $settings = $this->settingsRepository->getSettings();
    $allInOneReport = (bool)($settings['allInOneReport'] ?? false);
    $awards = $this->awardRepository->getAll();

    $generatedCount = 0;
    $allAdminsReport = [];
    $serversReport = [];

    foreach ($servers as $server) {
      $serverId = (int)($server['id'] ?? 0);
      $serverSid = (int)($server['sid'] ?? 0);

      if ($serverId <= 0 || $serverSid <= 0) {
        continue;
      }

      $admins_filtred = array_filter($admins, function ($admin) use ($serverId, $period, $force) {
        $adminServersRaw = $admin['servers'] ?? ($admin['server'] ?? '');
        $adminServers = array_filter(array_map('trim', explode(';', (string)$adminServersRaw)));
        $serverMatch = in_array((string)$serverId, $adminServers, true) || in_array('-1', $adminServers, true);
        if (!$force) {
          $dateAdded = (int) ($admin['date_added'] ?? $period['start']);
          return $serverMatch && $dateAdded < $period['start'];
        }
        return $serverMatch;
      });

      if (count($admins_filtred) === 0) {
        continue;
      }

      $adminsReport = [];
      $serverRid = (int)($server['rid'] ?? 0);

      foreach ($admins_filtred as $admin) {
        $stats = $this->statsRepository->getAdminInfo($admin['steam'], $serverSid, $period['start'], $period['end']);
        $playedTime = $this->statsRepository->getAdminPlayedTime($admin['steam'], $serverSid, $period['start'], $period['end']);

        $adminReport = [
          'steamid' => $admin['steam'],
          'name' => $this->General->checkName($admin['steam']),
          'group_id' => $admin['group'],
          'group_name' => $groupsById[$admin['group']] ?? '',
          'bans' => (int)($stats['bans_count'] ?? 0),
          'mutes' => (int)($stats['mutes_count'] ?? 0),
          'gags' => (int)($stats['gags_count'] ?? 0),
          'reports' => (int)$this->statsRepository->getReportsCount($admin['steam'], $serverRid, $period['start'], $period['end']),
          'checks' => (int)$this->statsRepository->getCheckCount($admin['steam'], $serverSid, $period['start'], $period['end']),
          'played_time' => (int)($playedTime['total_played'] ?? 0),
          'sessions_count' => (int)($playedTime['count_sessions'] ?? 0),
          'award_sum' => $this->calculateAwardForPlayedTime((int)($playedTime['total_played'] ?? 0), $awards),
        ];

        if ($allInOneReport) {
          $adminReport['servers'] = [['server_id' => $serverId, 'server_name' => $server['name'] ?? ('#' . $serverId)]];
          
          $existingKey = array_search($admin['steam'], array_column($allAdminsReport, 'steamid'));
          if ($existingKey !== false) {
            $allAdminsReport[$existingKey]['bans'] += $adminReport['bans'];
            $allAdminsReport[$existingKey]['mutes'] += $adminReport['mutes'];
            $allAdminsReport[$existingKey]['gags'] += $adminReport['gags'];
            $allAdminsReport[$existingKey]['reports'] += $adminReport['reports'];
            $allAdminsReport[$existingKey]['checks'] += $adminReport['checks'];
            $allAdminsReport[$existingKey]['played_time'] += $adminReport['played_time'];
            $allAdminsReport[$existingKey]['servers'][] = $adminReport['servers'][0];
          } else {
            $allAdminsReport[] = $adminReport;
          }
        } else {
          $adminsReport[] = $adminReport;
        }
      }

      if (!$allInOneReport) {
        $serversReport[] = ['name' => $server['name'] ?? ('#' . $serverId), 'admins' => count($adminsReport)];
        $this->saveServerReport($periodId, $serverId, $server['name'] ?? ('#' . $serverId), $period, $adminsReport);
        $generatedCount++;
      }
    }

    if ($allInOneReport && count($allAdminsReport) > 0) {
      foreach ($allAdminsReport as &$adminData) {
        $adminData['award_sum'] = $this->calculateAwardForPlayedTime((int)$adminData['played_time'], $awards);
      }
      unset($adminData);
      $allServersName = $this->Translate->get_translate_module_phrase('module_page_results', '_allServers');
      $serversReport[] = ['name' => $allServersName, 'admins' => count($allAdminsReport)];
      $this->saveServerReport($periodId, 0, $allServersName, $period, $allAdminsReport);
      
      $generatedCount++;
    }

    $this->logsService->sendGenerateMessage($periodId, $period['label'], $serversReport);

    return ['success' => 'Сформировано отчётов: ' . $generatedCount];
  }

  private function saveServerReport($periodId, $serverId, $serverName, $period, $adminsReport): void
  {
    $serverData = [
      'server_id' => $serverId,
      'server_name' => $serverName,
      'period_start' => $period['start'],
      'period_end' => $period['end'] - 1,
      'period_label' => $period['label'],
      'admins_count' => count($adminsReport),
      'award_taken' => false,
      'award_give_by' => '',
      'admins' => $adminsReport,
      'skiped_admins' => []
    ];

    $this->resultRepository->save($periodId, $serverData);
    $this->logsService->writeLog('INFO', sprintf(
      $this->Translate->get_translate_module_phrase('module_page_results', '_GenerateReportForServer'),
      $serverName,
      $period['label'],
      count($adminsReport)
    ));
  }

  public function calculateAwardForPlayedTime(int $playedTime, array $awards): float
  {
    $awardSum = 0;
    foreach ($awards as $award) {
      $timeThreshold = (int)($award['time'] ?? 0) * 3600;
      if ($playedTime >= $timeThreshold) {
        $amountStr = trim($award['amount'] ?? '');
        if (is_numeric($amountStr)) {
          $awardSum += (float)$amountStr;
        }
      }
    }
    return $awardSum;
  }

  private function getPreviousWeekPeriod($referenceTime = null): array
  {
    $now = $referenceTime ? new \DateTime('@' . (int)$referenceTime) : new \DateTime('now');
    $now->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    $currentMonday = (clone $now)->modify('monday this week')->setTime(0, 0, 0);
    $start = (clone $currentMonday)->modify('-7 days');
    $end = (clone $currentMonday);

    return [
      'start' => $start->getTimestamp(),
      'end' => $end->getTimestamp(),
      'label' => $start->format('d.m.y') . ' - ' . $end->modify('-1 day')->format('d.m.y'),
    ];
  }
}
