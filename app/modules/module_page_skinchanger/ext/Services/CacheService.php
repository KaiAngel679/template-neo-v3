<?php

namespace app\modules\module_page_skinchanger\ext\Services;

use app\modules\module_page_skinchanger\ext\Repositories\{
  CacheRepository,
  BaseRepository
};

class CacheService
{
  protected $Translate, $languages, $CacheRepository;

  protected $cacheLanguages = ['ru', 'en', 'de', 'ua'];
  protected $apiFallbackLanguage = 'en';
  protected $apiBaseUrl = 'https://raw.githubusercontent.com/Revolution792/CSGO-API/refs/heads/main/public/api';

  public function __construct($Translate)
  {
    $this->Translate     = $Translate;
    $this->languages     = strtolower($_SESSION['language']);
    $this->CacheRepository = new CacheRepository();
  }

  private function loadExclusions()
  {
    $path = MODULES . '/module_page_skinchanger/cache/exclusions.json';
    if (!file_exists($path)) {
      return [
        'skins' => ['exclude' => [], 'replace' => []],
        'stickers' => ['exclude' => [], 'replace' => []],
        'keychains' => ['exclude' => [], 'replace' => []],
        'agents' => ['exclude' => [], 'replace' => []],
        'music' => ['exclude' => [], 'replace' => []],
        'collectibles' => ['exclude' => [], 'replace' => []]
      ];
    }
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
  }

  private function applyExclusions(array $items, string $type)
  {
    $config = $this->loadExclusions();
    $exclude = $config[$type]['exclude'] ?? [];
    $replace = $config[$type]['replace'] ?? [];

    $filtered = [];
    foreach ($items as $item) {
      $id = $item['id'] ?? ($item['id_skin'] ?? null);
      if (in_array($id, $exclude, false) || in_array((string)$id, $exclude, false) || in_array((int)$id, $exclude, false)) {
        continue;
      }
      if (isset($replace[$id]) && !empty($replace[$id])) {
        $item['image'] = $replace[$id];
      } elseif (isset($replace[(string)$id]) && !empty($replace[(string)$id])) {
        $item['image'] = $replace[(string)$id];
      }
      $filtered[] = $item;
    }
    return $filtered;
  }

  public function cacheUpdate($name = '')
  {
    $lockFile = MODULES . '/module_page_skinchanger/cache/.update.lock';

    if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 60) {
      return ['status' => 'error', 'text' => 'Cache update already in progress'];
    }

    @touch($lockFile);
    try {
      $result = $this->runCacheUpdate($name);

      $cacheRoot = MODULES . '/module_page_skinchanger/cache/';
      $metaFile  = $cacheRoot . 'meta.json';
      $meta = file_exists($metaFile)
        ? (json_decode(file_get_contents($metaFile), true) ?: [])
        : [];
      unset($meta['remote_checked_at']);
      @file_put_contents($metaFile, json_encode($meta));

      $this->snapshotDownloadedShas();

      BaseRepository::clearMemoryCache();
    } finally {
      @unlink($lockFile);
    }

    return $result;
  }

  public function runCacheUpdate($name = '')
  {
    switch ($name) {
      case 'skins':
        return $this->cacheSkins();
      case 'keychains':
        return $this->cacheKeychains();
      case 'stickers':
        return $this->cacheStickers();
      case 'agents':
        return $this->cacheAgents();
      case 'music':
        return $this->cacheMusic();
      case 'coins':
        return $this->cacheCoins();
      case 'collections':
        return $this->cacheCollections();
      default:
        $this->cacheAll();
        return ['status' => 'success', 'text' => 'Cache updated successfully'];
    }
  }

  private function cacheAll(): void
  {
    $languages = $this->cacheLanguages;

    $endpoints = [
      'skins'       => $this->apiBaseUrl . '/%s/skins.json',
      'keychains'   => $this->apiBaseUrl . '/%s/keychains.json',
      'stickers'    => $this->apiBaseUrl . '/%s/stickers.json',
      'agents'      => $this->apiBaseUrl . '/%s/agents.json',
      'music'       => $this->apiBaseUrl . '/%s/music_kits.json',
      'coins'       => $this->apiBaseUrl . '/%s/collectibles.json',
      'collections' => $this->apiBaseUrl . '/%s/collections.json',
    ];

    $allLanguages = array_unique(array_merge($languages, [$this->apiFallbackLanguage]));

    $multiHandle = curl_multi_init();
    $curlHandles = [];

    foreach ($endpoints as $type => $pathTemplate) {
      foreach ($allLanguages as $language) {
        $url = sprintf($pathTemplate, $language);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_TIMEOUT        => 30,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_SSL_VERIFYPEER => false,
        ]);
        curl_multi_add_handle($multiHandle, $ch);
        $curlHandles[$type][$language] = $ch;
      }
    }

    $running = null;
    do {
      curl_multi_exec($multiHandle, $running);
      curl_multi_select($multiHandle);
    } while ($running > 0);

    $allData = [];
    foreach ($curlHandles as $type => $handles) {
      $fallback = null;
      foreach ($handles as $language => $ch) {
        $response = curl_multi_getcontent($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $data = ($httpCode === 200 && $response) ? json_decode($response, true) : null;
        $parsed = (!empty($data) && is_array($data)) ? $data : null;
        $allData[$type][$language] = $parsed;
        if ($language === $this->apiFallbackLanguage && $parsed !== null) {
          $fallback = $parsed;
        }
        curl_multi_remove_handle($multiHandle, $ch);
        curl_close($ch);
      }
      foreach ($languages as $language) {
        if ($allData[$type][$language] === null) {
          $allData[$type][$language] = $fallback;
        }
      }
    }

    curl_multi_close($multiHandle);

    $this->cacheSkins($allData['skins']);
    $this->cacheKeychains($allData['keychains']);
    $this->cacheStickers($allData['stickers']);
    $this->cacheAgents($allData['agents']);
    $this->cacheMusic($allData['music']);
    $this->cacheCoins($allData['coins']);
    $this->cacheCollections($allData['collections']);
  }

  public function cacheSkins(array $preloadedData = null)
  {
    $languages = $this->cacheLanguages;
    $createSkinStructure = static function ($skinInfo) {
      $collectionIds = [];
      if (!empty($skinInfo['collections']) && is_array($skinInfo['collections'])) {
        foreach ($skinInfo['collections'] as $collection) {
          if (!empty($collection['id'])) {
            $collectionIds[] = $collection['id'];
          }
        }
      }

      $crateIds = [];
      if (!empty($skinInfo['crates']) && is_array($skinInfo['crates'])) {
        foreach ($skinInfo['crates'] as $crate) {
          if (!empty($crate['id'])) {
            $crateIds[] = $crate['id'];
          }
        }
      }

      return [
        "id_skin" => $skinInfo['paint_index'],
        "name" => $skinInfo["name"],
        "image" => $skinInfo["image"],
        "id_rarity" => $skinInfo["rarity"]['id'],
        "rarity" => $skinInfo["rarity"]['name'],
        "collections" => $collectionIds,
        "crates" => $crateIds,
        "legacy_model" => $skinInfo["legacy_model"] ?? false
      ];
    };

    $allSkinsData = $preloadedData ?? $this->fetch_multi_api_data($this->apiBaseUrl . '/%s/skins.json', $languages);
    $SkinsTemplate = $this->CacheRepository->getCache("standard");

    foreach ($languages as $language) {

      $SkinsData = $allSkinsData[$language] ?? [];

      $skinsByWeapon = [];
      $weaponNames = [];
      foreach ($SkinsData as $Skin) {
        $weaponId = $Skin['weapon']['id'];
        $skinsByWeapon[$weaponId][] = $Skin;
        if (!isset($weaponNames[$weaponId]) && !empty($Skin['weapon']['name'])) {
          $weaponNames[$weaponId] = $Skin['weapon']['name'];
        }
      }

      $SkinsCache = $SkinsTemplate;
      foreach ($SkinsCache as &$cacheEntry) {
        $idName = $cacheEntry["id_name"];
        if (!isset($cacheEntry["skins"])) {
          $cacheEntry["skins"] = [];
        }
        if (isset($weaponNames[$idName])) {
          $newName = $weaponNames[$idName];
          $type = $cacheEntry["type"] ?? '';
          if (($type === 'Knife' || $type === 'Gloves') && mb_strpos($newName, '★') === false) {
            $newName = '★ ' . $newName;
          }
          $cacheEntry["name"] = $newName;
        }
        if (empty($skinsByWeapon[$idName])) continue;

        foreach ($skinsByWeapon[$idName] as $Skin) {
          $paintIndex = $Skin['paint_index'];
          if (!isset($cacheEntry["skins"][$paintIndex])) {
            $cacheEntry["skins"][$paintIndex] = $createSkinStructure($Skin);
          }
        }

        $cacheEntry["skins"] = $this->applyExclusions(array_values($cacheEntry["skins"]), 'skins');
      }
      unset($cacheEntry);

      $this->CacheRepository->setCache($language, $SkinsCache, "skins");
    }

    return ['status' => 'success', 'text' => 'Skins cache updated'];
  }

  public function cacheKeychains(array $preloadedData = null)
  {
    $languages = $this->cacheLanguages;

    $allKeychainsData = $preloadedData ?? $this->fetch_multi_api_data($this->apiBaseUrl . '/%s/keychains.json', $languages);

    foreach ($languages as $language) {

      $KeychainsData = $allKeychainsData[$language] ?? [];

      $newFormat = [];
      foreach ($KeychainsData as $cacheEntry) {
        if (!empty($cacheEntry['def_index'])) {
          $newFormat[$cacheEntry['def_index']] = [
            "id" => $cacheEntry['def_index'],
            "name" => $cacheEntry["name"],
            "image" => $cacheEntry["image"],
            "id_rarity" => $cacheEntry["rarity"]['id'],
            "rarity" => $cacheEntry["rarity"]['name'],
          ];
        }
      }

      $newFormat = $this->applyExclusions(array_values($newFormat), 'keychains');
      $indexed = [];
      foreach ($newFormat as $item) {
        $indexed[$item['id']] = $item;
        unset($indexed[$item['id']]['id']);
      }

      $this->CacheRepository->setCache($language, $indexed, "keychains");
    }

    return ['status' => 'success', 'text' => 'Skins cache updated'];
  }

  public function cacheStickers(array $preloadedData = null)
  {
    $languages = $this->cacheLanguages;

    $allStickersData = $preloadedData ?? $this->fetch_multi_api_data($this->apiBaseUrl . '/%s/stickers.json', $languages);

    foreach ($languages as $language) {

      $StickersData = $allStickersData[$language] ?? [];

      $newFormat = [];
      foreach ($StickersData as $cacheEntry) {
        if (!empty($cacheEntry['def_index'])) {
          $newFormat[$cacheEntry['def_index']] = [
            "id" => $cacheEntry['def_index'],
            "name" => $cacheEntry["name"],
            "image" => $cacheEntry["image"],
            "id_rarity" => $cacheEntry["rarity"]['id'],
            "rarity" => $cacheEntry["rarity"]['name'],
          ];
        }
      }

      $newFormat = $this->applyExclusions(array_values($newFormat), 'stickers');
      $indexed = [];
      foreach ($newFormat as $item) {
        $indexed[$item['id']] = $item;
        unset($indexed[$item['id']]['id']);
      }

      $this->CacheRepository->setCache($language, $indexed, "stickers");
    }

    return ['status' => 'success', 'text' => 'Skins cache updated'];
  }

  public function cacheAgents(array $preloadedData = null)
  {
    $languages = $this->cacheLanguages;

    $createAgentStructure = static function ($AgentInfo) {
      if (empty($AgentInfo['def_index'])) return null;

      if (preg_match('/(?:characters|agents)\/models\/([^\/]+)\/([^\/]+)\.vmdl$/', $AgentInfo["model_player"], $matches)) {
        $modelPath = "{$matches[1]}/{$matches[2]}";
      } else {
        $modelPath = NULL;
      }

      return [
        "id" => $AgentInfo['def_index'],
        "name" => $AgentInfo["name"],
        "image" => $AgentInfo["image"],
        "model" => $modelPath,
        "team"  => ($AgentInfo["team"]['id'] == 'terrorists') ? 0 : 1,
        "id_rarity" => $AgentInfo["rarity"]['id'],
        "rarity" => $AgentInfo["rarity"]['name']
      ];
    };

    $allAgentsData = $preloadedData ?? $this->fetch_multi_api_data($this->apiBaseUrl . '/%s/agents.json', $languages);

    foreach ($languages as $language) {

      $AgentsData = $allAgentsData[$language] ?? [];

      foreach ($AgentsData as &$cacheEntry) {
        $cacheEntry = $createAgentStructure($cacheEntry);
      }
      unset($cacheEntry);

      $AgentsData = array_filter($AgentsData);
      $AgentsData = $this->applyExclusions(array_values($AgentsData), 'agents');

      $this->CacheRepository->setCache($language, $AgentsData, "agents");
    }

    return ['status' => 'success', 'text' => 'Skins cache updated'];
  }

  public function cacheMusic(array $preloadedData = null)
  {
    $languages = $this->cacheLanguages;

    $createMusicStructure = static function ($MusicInfo) {
      if (empty($MusicInfo['def_index'])) return null;

      return [
        "id" => $MusicInfo['def_index'],
        "name" => $MusicInfo["name"],
        "image" => $MusicInfo["image"],
        "id_rarity" => $MusicInfo["rarity"]['id'],
        "rarity" => $MusicInfo["rarity"]['name']
      ];
    };

    $allMusicData = $preloadedData ?? $this->fetch_multi_api_data($this->apiBaseUrl . '/%s/music_kits.json', $languages);

    foreach ($languages as $language) {

      $MusicData = $allMusicData[$language] ?? [];

      $filtered = [];
      foreach ($MusicData as $cacheEntry) {
        $cacheEntryID = explode('-', $cacheEntry['id'])[1];
        if (stripos($cacheEntryID, "_st") === false) {
          $filtered[] = $createMusicStructure($cacheEntry);
        }
      }
      $filtered = array_filter($filtered);
      $filtered = $this->applyExclusions(array_values($filtered), 'music');

      $this->CacheRepository->setCache($language, $filtered, "music");
    }

    return ['status' => 'success', 'text' => 'Skins cache updated'];
  }

  public function cacheCoins(array $preloadedData = null)
  {
    $languages = $this->cacheLanguages;

    $createMoneyStructure = static function ($MoneyInfo) {
      if (empty($MoneyInfo['def_index'])) return null;

      return [
        "id" => $MoneyInfo['def_index'],
        "name" => $MoneyInfo["name"],
        "image" => $MoneyInfo["image"],
        "type" => $MoneyInfo["type"],
        "id_rarity" => $MoneyInfo["rarity"]['id'],
        "rarity" => $MoneyInfo["rarity"]['name']
      ];
    };

    $allMoneyData = $preloadedData ?? $this->fetch_multi_api_data($this->apiBaseUrl . '/%s/collectibles.json', $languages);

    foreach ($languages as $language) {

      $MoneyData = $allMoneyData[$language] ?? [];

      $filtered = [];
      foreach ($MoneyData as $cacheEntry) {
        if (stripos($cacheEntry["type"], "Pass") === false && stripos($cacheEntry["type"], "Stars for Operation") === false) {
          $filtered[] = $createMoneyStructure($cacheEntry);
        }
      }
      $filtered = array_filter($filtered);
      $filtered = $this->applyExclusions(array_values($filtered), 'collectibles');

      $this->CacheRepository->setCache($language, $filtered, "collectibles");
    }

    return ['status' => 'success', 'text' => 'Skins cache updated'];
  }

  public function cacheCollections(array $preloadedData = null)
  {
    $languages = $this->cacheLanguages;

    $createCollectionStructure = static function ($CollectionInfo) {
      if (empty($CollectionInfo['id'])) return null;

      $crates = [];
      if (!empty($CollectionInfo['crates']) && is_array($CollectionInfo['crates'])) {
        foreach ($CollectionInfo['crates'] as $crate) {
          if (!empty($crate['id'])) {
            $crates[] = $crate['id'];
          }
        }
      }

      return [
        "id" => $CollectionInfo['id'],
        "name" => $CollectionInfo["name"],
        "image" => $CollectionInfo["image"] ?? null,
        "crates" => $crates
      ];
    };

    $allCollectionsData = $preloadedData ?? $this->fetch_multi_api_data($this->apiBaseUrl . '/%s/collections.json', $languages);

    foreach ($languages as $language) {

      $CollectionsData = $allCollectionsData[$language] ?? [];

      $filtered = [];
      foreach ($CollectionsData as $cacheEntry) {
        $structured = $createCollectionStructure($cacheEntry);
        if ($structured !== null) {
          $filtered[] = $structured;
        }
      }

      $this->CacheRepository->setCache($language, $filtered, "collections");
    }

    return ['status' => 'success', 'text' => 'Skins cache updated'];
  }

  protected function fetch_multi_api_data($pathTemplate, $languages)
  {
    $multiHandle = curl_multi_init();
    $curlHandles = [];

    $allLanguages = array_unique(array_merge($languages, [$this->apiFallbackLanguage]));

    foreach ($allLanguages as $language) {
      $url = sprintf($pathTemplate, $language);
      $ch = curl_init($url);
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
      ]);
      curl_multi_add_handle($multiHandle, $ch);
      $curlHandles[$language] = $ch;
    }

    $running = null;
    do {
      curl_multi_exec($multiHandle, $running);
      curl_multi_select($multiHandle);
    } while ($running > 0);

    $rawResults = [];
    foreach ($curlHandles as $language => $ch) {
      $response = curl_multi_getcontent($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $data = ($httpCode === 200 && $response) ? json_decode($response, true) : null;
      $rawResults[$language] = (!empty($data) && is_array($data)) ? $data : null;
      curl_multi_remove_handle($multiHandle, $ch);
      curl_close($ch);
    }

    curl_multi_close($multiHandle);

    $fallbackData = $rawResults[$this->apiFallbackLanguage] ?? [];
    $results = [];
    foreach ($languages as $language) {
      $results[$language] = $rawResults[$language] ?? $fallbackData;
    }

    return $results;
  }

  private function fetchRemoteFileShas(): ?array
  {
    $cacheRoot = MODULES . '/module_page_skinchanger/cache/';
    $metaFile  = $cacheRoot . 'meta.json';
    $metaTtl   = 3600;

    $meta = file_exists($metaFile)
      ? (json_decode(file_get_contents($metaFile), true) ?: [])
      : [];

    if (
      !empty($meta['remote_shas']) &&
      !empty($meta['remote_checked_at']) &&
      (time() - (int) $meta['remote_checked_at']) < $metaTtl
    ) {
      return $meta['remote_shas'];
    }

    $url = 'https://api.github.com/repos/Revolution792/CSGO-API/contents/public/api/ru';
    $ctx = stream_context_create(['http' => [
      'header'  => "User-Agent: PHP\r\n",
      'timeout' => 5,
    ]]);
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) return null;

    $files = json_decode($json, true);
    if (!is_array($files)) return null;

    $shas = [];
    foreach ($files as $f) {
      if (!empty($f['name']) && !empty($f['sha'])) {
        $shas[$f['name']] = $f['sha'];
      }
    }

    $meta['remote_shas']       = $shas;
    $meta['remote_checked_at'] = time();
    @file_put_contents($metaFile, json_encode($meta));

    return $shas;
  }

  public function snapshotDownloadedShas(): void
  {
    $remoteShas = $this->fetchRemoteFileShas();
    if (!$remoteShas) return;

    $cacheRoot = MODULES . '/module_page_skinchanger/cache/';
    $metaFile  = $cacheRoot . 'meta.json';
    $meta = file_exists($metaFile)
      ? (json_decode(file_get_contents($metaFile), true) ?: [])
      : [];

    $meta['downloaded_shas'] = $remoteShas;
    $meta['downloaded_at']   = time();
    @file_put_contents($metaFile, json_encode($meta));
  }
}
