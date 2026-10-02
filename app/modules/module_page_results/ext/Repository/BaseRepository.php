<?php

namespace app\modules\module_page_results\ext\Repository;

abstract class BaseRepository
{
  protected function saveToJson(string $folder, string $file, $data): bool
  {
    $basePath = defined('MODULES') ? MODULES : ($_SERVER['DOCUMENT_ROOT'] . '/app/modules/');
    $path = $basePath . 'module_page_results/cache/' . $folder . '/';
    if (!is_dir($path)) {
      mkdir($path, 0777, true);
    }
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return file_put_contents($path . $file . '.json', $json) !== false;
  }

  protected function loadFromJson(string $folder, string $file)
  {
    $basePath = defined('MODULES') ? MODULES : ($_SERVER['DOCUMENT_ROOT'] . '/app/modules/');
    $path = $basePath . 'module_page_results/cache/' . $folder . '/' . $file . '.json';
    if (!file_exists($path)) {
      return null;
    }
    $content = file_get_contents($path);
    return json_decode($content, true);
  }

  protected function getJsonFilePath(string $folder): string
  {
    $basePath = defined('MODULES') ? MODULES : ($_SERVER['DOCUMENT_ROOT'] . '/app/modules/');
    return $basePath . 'module_page_results/cache/' . $folder . '/';
  }

  protected function getNextId(array $items, string $idField = 'id'): int
  {
    $maxId = 0;
    foreach ($items as $item) {
      if ((int)($item[$idField] ?? 0) >= $maxId) {
        $maxId = (int)($item[$idField] ?? 0);
      }
    }
    return $maxId + 1;
  }
}
