<?php

namespace app\modules\module_page_results\ext\Repository;

class LogsRepository extends BaseRepository
{
  public function __construct()
  {
  }

  public function writeLog(string $level, string $message): void
  {
    $date = date('d-m-Y');
    $existingLogs = $this->loadFromJson('logs', $date) ?: [];
    
    $existingLogs[] = [
      'timestamp' => time(),
      'level' => $level,
      'message' => $message
    ];
    
    $this->saveToJson('logs', $date, $existingLogs);
  }

  public function getDatesWithLogs(): array
  {
    $logsDir = $this->getJsonFilePath('logs');
    if (!is_dir($logsDir)) {
      return [];
    }
    $files = scandir($logsDir);
    $dates = [];
    foreach ($files as $file) {
      if (preg_match('/^\d{2}-\d{2}-\d{4}\.json$/', $file)) {
        $dates[] = pathinfo($file, PATHINFO_FILENAME);
      }
    }
    rsort($dates);
    return $dates;
  }

  public function getLogs($page, $date): array
  {
    $date = empty($date) ? date('d-m-Y') : $date;
    $logs = $this->loadFromJson('logs', $date) ?: [];
    usort($logs, function ($a, $b) {
      return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
    });
    $perPage = 13;
    $totalLogs = count($logs);
    $totalPages = ceil($totalLogs / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    $pagedLogs = array_slice($logs, $offset, $perPage);
    foreach ($pagedLogs as &$log) {
      $log['formatted_time'] = isset($log['timestamp']) ? date('d.m.Y H:i:s', $log['timestamp']) : '';
    }
    return [
      'data' => $pagedLogs,
      'page_max' => $totalPages,
    ];
  }

  public function clearLogs(): void
  {
    $logsDir = $this->getJsonFilePath('logs');
    if (!is_dir($logsDir)) {
      return;
    }
    $files = scandir($logsDir);
    foreach ($files as $file) {
      if (preg_match('/^\d{2}-\d{2}-\d{4}\.json$/', $file)) {
        unlink($logsDir . DIRECTORY_SEPARATOR . $file);
      }
    }
  }
}