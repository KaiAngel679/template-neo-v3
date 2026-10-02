<?php

namespace app\modules\module_page_results\ext;

use app\modules\module_page_results\ext\Service\{
  AdminTimeService,
  ChartService,
  AwardService,
  AccessService,
  ReportService,
  ResultsService,
  CronService,
  AdminService,
  ServerService,
  SettingsService,
  WarnService,
  LogsService
};

class Results
{
  public $Db;
  public $General;
  public $Translate;
  public $Modules;
  public $settings;
  private $adminTimeService;
  private $chartService;
  private $awardService;
  private $accessService;
  private $resultsService;
  private $reportService;
  private $cronService;
  private $adminService;
  private $serverService;
  private $settingsService;
  private $warnService;
  public $logsService;
  private $onlyTheirs;

  public function __construct($Db, $General, $Translate, $Modules)
  {
    $this->Db = $Db;
    $this->General = $General;
    $this->Translate = $Translate;
    $this->Modules = $Modules;
    $this->adminTimeService = new AdminTimeService($this->General, $this->Translate, $this->Modules, $this->Db);
    $this->chartService = new ChartService($this->Db, $this->Translate);
    $this->resultsService = new ResultsService($this->Db, $this->General, $this->Modules, $this->Translate);
    $this->reportService = new ReportService($this->Db, $this->General, $this->Translate);
    $this->cronService = new CronService($this->General, $this->Translate, $this->Db);
    $this->adminService = new AdminService($this->General, $this->Translate, $this->Db);
    $this->serverService = new ServerService($this->Translate);
    $this->accessService = new AccessService($this->Translate, $this->Db);
    $this->settingsService = new SettingsService($this->Translate);
    $this->awardService = new AwardService($this->Translate, $this->General, $this->Db);
    $this->warnService = new WarnService($this->General, $this->Translate, $this->Modules, $this->Db);
    $this->logsService = new LogsService($this->Translate, $this->General);
    $this->onlyTheirs = $this->onlyTheirs();
  }

  public function checkAccess(array $permissions = []): ?array
  {
    return $this->accessService->checkAccess($_SESSION['steamid64'] ?? '', $permissions);
  }

  public function onlyTheirs(): bool
  {
    if ($this->settingsService->getSettings()['only_theirs'] ?? false) {
      $access = $this->checkAccess();
      if ($access['theirs']) {
        return true;
      }
    }
    return false;
  }

  public function getSettings(): array
  {
    return $this->settingsService->getSettings();
  }

  public function saveSettings($data): array
  {
    return $this->settingsService->saveSettings($data);
  }

  public function saveDiscord($data): array
  {
    return $this->settingsService->saveDiscord($data);
  }

  public function renderLogs($page, $date)
  {
    return $this->logsService->renderLogs($page, $date);
  }

  public function getDatesWithLogs(): array
  {
    return $this->logsService->getDatesWithLogs();
  }

  public function clearLogs(): array
  {
    return $this->logsService->clearLogs();
  }

  public function renderResults($postData): array
  {

    return $this->resultsService->renderResults($postData, $this->onlyTheirs);
  }

  public function renderResultDetails(int $id, int $serverId): array
  {
    return $this->resultsService->renderResultDetails($id, $serverId, $this->onlyTheirs);
  }

  public function getAllResults(): array
  {
    return $this->resultsService->getAll();
  }

  public function getResult(int $id): ?array
  {
    return $this->resultsService->getById($id);
  }

  public function renderAdminTime($postData): array
  {
    return $this->adminTimeService->getAdminTimeData($postData, $this->onlyTheirs);
  }

  public function renderCharts($postData): array
  {
    return $this->chartService->getChartsData($postData);
  }

  public function renderSessions($postData): array
  {
    return $this->adminTimeService->getSessionsData($postData);
  }

  public function renderAdmins($page = 1, $server = 0, $group = 0): array
  {
    return $this->adminService->renderAdmins([
      'page' => $page,
      'server' => $server ? [$server] : [],
      'group' => $group ? [$group] : [],
    ]);
  }

  public function renderAdmin(int $id): ?array
  {
    return $this->adminService->renderAdmin($id);
  }

  public function addAdmin($post): array
  {
    return $this->adminService->addAdmin($post);
  }

  public function editAdmin($post): array
  {
    return $this->adminService->editAdmin($post);
  }

  public function deleteAdmin(int $id): array
  {
    return $this->adminService->deleteAdmin(['id' => $id]);
  }

  public function importAdmins($post): array
  {
    return $this->adminService->importAdmins($post);
  }

  public function getGroups(): array
  {
    return $this->adminService->getGroups();
  }

  public function getServers(): array
  {
    return $this->serverService->getAll();
  }

  public function getServer(int $id): ?array
  {
    return $this->serverService->getById($id);
  }

  public function getServerBySid(int $sid): ?array
  {
    return $this->serverService->getBySid($sid);
  }

  public function getServerByRid(int $rid): ?array
  {
    return $this->serverService->getByRid($rid);
  }

  public function getServersSidMap(): array
  {
    return $this->serverService->getSidMap();
  }

  public function getServersRidMap(): array
  {
    return $this->serverService->getRidMap();
  }

  public function getServersIdMap(): array
  {
    return $this->serverService->getIdMap();
  }

  public function addServer($data): array
  {
    return $this->serverService->addServer($data);
  }

  public function deleteServer(int $id): array
  {
    return $this->serverService->deleteServer(['id' => $id]);
  }

  public function getAwards(): array
  {
    return $this->awardService->getAll();
  }

  public function getAward(int $id): ?array
  {
    return $this->awardService->getById($id);
  }

  public function addAward($data): array
  {
    return $this->awardService->addAward($data);
  }

  public function editAward($data): array
  {
    return $this->awardService->editAward($data);
  }

  public function deleteAward(int $id): array
  {
    return $this->awardService->deleteAward(['id' => $id]);
  }

  public function grantAwards(int $resultId, int $serverId): array
  {
    return $this->awardService->grantAwards($resultId, $serverId);
  }

  public function calculateAwardForPlayedTime($playedTime, $awards): float
  {
    return $this->reportService->calculateAwardForPlayedTime((int)$playedTime, (array)$awards);
  }

  public function renderWarns($page = 1): array
  {
    return $this->warnService->renderWarns((int)$page);
  }

  public function giveWarnManual($data): array
  {
    return $this->warnService->giveWarnManual($data);
  }

  public function getAccesses(): array
  {
    return $this->accessService->getAccesses();
  }

  public function addAccess($data): array
  {
    return $this->accessService->addAccess($data);
  }

  public function deleteAccess(int $id): array
  {
    return $this->accessService->deleteAccess(['id' => $id]);
  }

  public function giveAwardManual($data): array
  {
    return $this->awardService->giveAwardManual($data);
  }

  public function generateWeeklyReports($referenceTime = null, $force = false): array
  {
    return $this->reportService->generateWeeklyReports($referenceTime, $force);
  }

  public function manualGenerate($countWeeks = 1)
  {
    for ($i = 0; $i < $countWeeks; $i++) {
      $weekOffset = $i * 7 * 24 * 60 * 60;
      $referenceTime = time() - $weekOffset;
      $this->generateWeeklyReports($referenceTime, true);
    }
    return ['success' => sprintf($this->Translate->get_translate_module_phrase('module_page_results', '_ReportGenerated'), $countWeeks)];
  }

  public function processCronSession()
  {
    return $this->cronService->processCronSession();
  }
}
