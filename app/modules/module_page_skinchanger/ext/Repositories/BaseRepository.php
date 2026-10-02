<?php

namespace app\modules\module_page_skinchanger\ext\Repositories;

abstract class BaseRepository
{
  private static array $memoryCache = [];
  private static ?bool $apcuAvailable = null;
  private static string $cachePrefix = 'skinchanger_';

  private static function ensureApcuInit(): void
  {
    if (self::$apcuAvailable === null) {
      self::$apcuAvailable = extension_loaded('apcu') && ini_get('apc.enabled');
    }
  }

  protected function saveToJson(string $file, array $data, string $folder = ''): bool
  {
    self::ensureApcuInit();
    $cacheKey = $folder . '/' . $file;
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);

    if (self::$apcuAvailable) {
      apcu_store(self::$cachePrefix . $cacheKey, $data, 3600);
      self::$memoryCache[$cacheKey] = $data;
      return $this->writeToFile($file, $json, $folder);
    }

    unset(self::$memoryCache[$cacheKey]);
    self::$memoryCache[$cacheKey] = $data;
    return $this->writeToFile($file, $json, $folder);
  }

  private function writeToFile(string $file, string $json, string $folder = ''): bool
  {
    if (!empty($folder)) {
      $path = MODULES . '/module_page_skinchanger/cache/' . $folder . '/';
    } else {
      $path = MODULES . '/module_page_skinchanger/cache/';
    }
    if (!is_dir($path)) {
      mkdir($path, 0755, true);
    }
    $tmpFile = $path . $file . '.json.tmp';
    if (file_put_contents($tmpFile, $json, LOCK_EX) === false) {
      return false;
    }
    return rename($tmpFile, $path . $file . '.json');
  }

  public static function clearMemoryCache(): void
  {
    self::ensureApcuInit();
    self::$memoryCache = [];

    if (self::$apcuAvailable) {
      apcu_clear_cache();
    }
  }

  protected function loadFromJson(string $file, string $folder = '')
  {
    self::ensureApcuInit();
    $cacheKey = $folder . '/' . $file;

    if (self::$apcuAvailable) {
      $success = false;
      $data = apcu_fetch(self::$cachePrefix . $cacheKey, $success);
      if ($success) {
        return $data;
      }
      $data = $this->readFromFile($file, $folder);
      if ($data !== null) {
        apcu_store(self::$cachePrefix . $cacheKey, $data, 3600);
      }
      return $data;
    }

    if (isset(self::$memoryCache[$cacheKey])) {
      return self::$memoryCache[$cacheKey];
    }

    $data = $this->readFromFile($file, $folder);
    if ($data !== null) {
      self::$memoryCache[$cacheKey] = $data;
    }
    return $data;
  }

  private function readFromFile(string $file, string $folder = '')
  {
    if (!empty($folder)) {
      $path = MODULES . '/module_page_skinchanger/cache/' . $folder . '/' . $file . '.json';
    } else {
      $path = MODULES . '/module_page_skinchanger/cache/' . $file . '.json';
    }
    if (!file_exists($path)) {
      return null;
    }
    return json_decode(file_get_contents($path), true);
  }
}
