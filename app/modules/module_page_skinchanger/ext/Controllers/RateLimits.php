<?php

namespace app\modules\module_page_skinchanger\ext\Controllers;

class RateLimits
{
  private const GROUPS = [
    'write'      => [60,  60],
    'collection' => [20,  60],
    'like'       => [30,  60],
    'search'     => [40,  60],
    'read'       => [80,  60],
  ];

  private string $storageDir;

  public function __construct()
  {
    $this->storageDir = MODULES . 'module_page_skinchanger/ratelimits/';
  }

  public function check(string $group): bool
  {
    if (!isset(self::GROUPS[$group])) {
      return true;
    }

    [$limit, $window] = self::GROUPS[$group];
    $dir  = $this->storageDir . $group . '/';
    $file = $dir . $this->clientKey() . '.json';

    if (!is_dir($dir)) {
      @mkdir($dir, 0755, true);
    }

    $this->cleanup($dir, $window);

    $log = [];
    if (file_exists($file)) {
      $log = json_decode(file_get_contents($file), true) ?? [];
      $log = array_values(array_filter($log, fn($ts) => $ts > time() - $window));
    }

    if (count($log) >= $limit) {
      return false;
    }

    $log[] = time();
    @file_put_contents($file, json_encode($log), LOCK_EX);

    return true;
  }

  private function clientKey(): string
  {
    if (!empty($_SESSION['steamid'])) {
      return md5((string)$_SESSION['steamid']);
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return 'ip_' . md5($ip);
  }

  private function cleanup(string $dir, int $window): void
  {
    static $cleaned = [];
    if (isset($cleaned[$dir])) {
      return;
    }
    $cleaned[$dir] = true;

    $files = glob($dir . '*.json') ?: [];
    $now   = time();
    foreach ($files as $f) {
      $log = json_decode(@file_get_contents($f), true);
      if (empty($log) || max($log) < $now - $window) {
        @unlink($f);
      }
    }
  }
}
