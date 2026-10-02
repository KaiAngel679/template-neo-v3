<?php

namespace app\modules\module_page_results\ext\Repository;

class ResultRepository extends BaseRepository
{
  private function getResultsPath(): string
  {
    return MODULES . 'module_page_results/cache/results/';
  }

  public function getAll(): array
  {
    $resultsPath = $this->getResultsPath();
    if (!is_dir($resultsPath)) {
      return [];
    }

    $results = [];
    $files = glob($resultsPath . '*.json');

    foreach ($files as $file) {
      $content = file_get_contents($file);
      $data = json_decode($content, true);
      if ($data) {
        $results[] = $data;
      }
    }

    usort($results, function ($a, $b) {
      return ($b['period_start'] ?? 0) <=> ($a['period_start'] ?? 0);
    });

    return $results;
  }

  public function getById(int $id): array
  {
    $resultsPath = $this->getResultsPath();
    if (!is_dir($resultsPath)) {
      return [];
    }
    $filePath = $resultsPath . $id . '.json';
    if (!file_exists($filePath)) {
      return [];
    }
    $content = file_get_contents($filePath);
    $data = json_decode($content, true);
    return $data ?: [];
  }

  public function save(int $periodId, array $serverData): bool
  {
    $path = $this->getResultsPath();
    if (!is_dir($path)) {
      mkdir($path, 0777, true);
    }

    $filePath = $path . $periodId . '.json';
    $periodReport = null;

    if (file_exists($filePath)) {
      $content = file_get_contents($filePath);
      $periodReport = json_decode($content, true);
    }

    if (!$periodReport) {
      $periodReport = [
        'id' => $periodId,
        'period_start' => $serverData['period_start'],
        'period_end' => $serverData['period_end'],
        'period_label' => $serverData['period_label'],
        'created_at' => time(),
        'servers' => []
      ];
    }

    $serverExists = false;
    foreach ($periodReport['servers'] as $index => $server) {
      if ((int)$server['server_id'] === (int)$serverData['server_id']) {
        $periodReport['servers'][$index] = $serverData;
        $serverExists = true;
        break;
      }
    }

    if (!$serverExists) {
      $periodReport['servers'][] = $serverData;
    }

    $json = json_encode($periodReport, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return file_put_contents($filePath, $json) !== false;
  }

  public function getNextReportId(): int
  {
    $results = $this->getAll();
    $maxId = 0;
    foreach ($results as $report) {
      if (!empty($report['id']) && $report['id'] > $maxId) {
        $maxId = $report['id'];
      }
    }
    return $maxId + 1;
  }

  public function getOrCreatePeriodId(int $periodStart): int
  {
    $results = $this->getAll();
    foreach ($results as $report) {
      if ((int)($report['period_start'] ?? 0) === $periodStart) {
        return $report['id'];
      }
    }
    return $this->getNextReportId();
  }
}
