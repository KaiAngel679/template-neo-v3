<?php

namespace app\modules\module_page_results\ext\Service;

use app\modules\module_page_results\ext\Repository\CacheRepository;
use app\modules\module_page_results\ext\Service\ReportService;
use app\modules\module_page_results\ext\Service\LogsService;

class CronService
{
  private $reportService;
  private $cacheRepository;
  private $logsService;

  public function __construct($General, $Translate, $Db)
  {
    $this->reportService = new ReportService($Db, $General, $Translate);
    $this->cacheRepository = new CacheRepository();
    $this->logsService = new LogsService($Translate, $General);
  }

  public function processCronSession()
  {
    $now = time();
    $isMonday = (int)date('N', $now) === 1;

    if (!$isMonday) {
      return ['success' => 'Ожидание следующего понедельника'];
    }

    $cache = $this->cacheRepository->getCache();
    $lastReportDate = $cache['last_report_date'] ?? '';
    $today = date('Y-m-d', $now);
    
    if ($lastReportDate === $today) {
      return ['success' => 'Отчет уже сформирован'];
    }

    return $this->generateReports($cache, $now);
  }

  private function generateReports(array $cache, int $now): array
  {
    try {
      $result = $this->reportService->generateWeeklyReports();
      $cache['last_report_date'] = date('Y-m-d', $now);
      $cache['last_report_at'] = date('Y-m-d H:i:s', $now);
      $this->cacheRepository->saveCache($cache);
      $this->logsService->writeLog('INFO', 'CronSession выполнен: ' . $result['success'] ?? $result);
      return $result;
    } catch (\Exception $e) {
      $this->logsService->writeLog('ERROR', 'Ошибка cron session: ' . $e->getMessage());
      return ['error' => $e->getMessage()];
    }
  }
}
