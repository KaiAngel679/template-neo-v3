<?php


namespace app\modules\module_page_skinchanger\ext\Controllers;

class ActionsController
{
  public object $SkinchangerController;
  public object $Db;
  public object $Translate;
  public object $General;
  private const ALLOWED_LANGUAGES = ['ru', 'en', 'de', 'ua'];
  private string $language;

  public function __construct(object $Db, object $Translate, object $General)
  {
    $this->Db = $Db;
    $this->Translate = $Translate;
    $this->General = $General;
    $this->SkinchangerController = new SkinchangerController($Db, $Translate, $General);
    $currentLanguage = strtolower($_SESSION['language'] ?? 'en');
    $this->language = in_array($currentLanguage, self::ALLOWED_LANGUAGES, true) ? $currentLanguage : 'en';
  }

  private function removeZalgo(string $text): string
  {
    return preg_replace('/[\x{0300}-\x{036F}\x{0483}-\x{0489}\x{1AB0}-\x{1AFF}\x{1DC0}-\x{1DFF}\x{20D0}-\x{20FF}\x{FE20}-\x{FE2F}]/u', '', $text);
  }

  public function handle(string $action): void
  {
    $method = 'action' . ucfirst($action);
    if (method_exists($this, $method)) {
      $this->$method();
    } else {
      $this->json(['success' => false], 404);
    }
  }

  private function json(array $data, int $statusCode = 200): void
  {
    $jsonData = json_encode($data);

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $statusCode === 200) {
      header('Cache-Control: no-store, no-cache, must-revalidate');
      header('Pragma: no-cache');
    }

    http_response_code($statusCode);
    header('Content-Type: application/json');
    exit($jsonData);
  }

  private function requireAdmin(): void
  {
    if (!isset($_SESSION['user_admin'])) {
      $this->json(['success' => false, 'error' => 'forbidden'], 403);
    }
  }

  private function requireUser(): void
  {
    if (empty($_SESSION['steamid'])) {
      $this->json(['success' => false, 'error' => 'unauthorized'], 401);
    }
  }

  private function input(string $key, string $filter = 'string', $default = null)
  {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
      $data = $_GET;
    } else {
      static $bodyData = null;
      if ($bodyData === null) {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
          $raw = file_get_contents('php://input');
          $bodyData = json_decode($raw, true) ?? [];
        } else {
          $bodyData = $_POST;
        }
      }
      $data = $bodyData;
    }

    if (!isset($data[$key])) {
      if (isset($_GET[$key])) {
        $val = $_GET[$key];
      } else {
        return $default;
      }
    } else {
      $val = $data[$key];
    }
    if ($filter === 'int')   return (int) $val;
    if ($filter === 'float') return (float) $val;
    if ($filter === 'alpha') return preg_replace('/[^a-z_]/', '', strtolower($val));
    return $val;
  }

  private function normalizeItems(array $items, string $stripPrefix = ''): array
  {
    $result = [];
    foreach ($items as $id => $item) {
      $item['id'] = (int) $id;
      if ($stripPrefix && isset($item['name'])) {
        $item['name'] = preg_replace($stripPrefix, '', $item['name']);
      }
      $result[] = $item;
    }
    return $result;
  }

  private function fetchRemoteFileShas(): ?array
  {
    $cacheRoot = MODULES . 'module_page_skinchanger/cache/';
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

  private function buildCacheInfo(): array
  {
    $cacheRoot      = MODULES . 'module_page_skinchanger/cache/';
    $metaFile       = $cacheRoot . 'meta.json';
    $meta           = file_exists($metaFile)
      ? (json_decode(file_get_contents($metaFile), true) ?: [])
      : [];
    $remoteShas     = $this->fetchRemoteFileShas();
    $downloadedShas = $meta['downloaded_shas'] ?? [];

    $fileMap = [
      'skins'     => 'skins.json',
      'stickers'  => 'stickers.json',
      'keychains' => 'keychains.json',
      'agents'    => 'agents.json',
      'coins'     => 'collectibles.json',
      'music'     => 'music_kits.json',
      'collections' => 'collections.json',
    ];

    $localFiles = [
      'skins'     => $cacheRoot . 'skins/ru.json',
      'stickers'  => $cacheRoot . 'stickers/ru.json',
      'keychains' => $cacheRoot . 'keychains/ru.json',
      'agents'    => $cacheRoot . 'agents/ru.json',
      'coins'     => $cacheRoot . 'collectibles/ru.json',
      'music'     => $cacheRoot . 'music/ru.json',
      'collections' => $cacheRoot . 'collections/ru.json',
    ];

    $result = [];
    foreach ($localFiles as $type => $file) {
      if (!file_exists($file)) {
        $result[$type] = ['exists' => false, 'mtime' => null, 'stale' => true, 'date' => null];
        continue;
      }
      $mtime      = filemtime($file);
      $remoteFile = $fileMap[$type];

      if ($remoteShas !== null && !empty($downloadedShas) && isset($remoteShas[$remoteFile]) && isset($downloadedShas[$remoteFile])) {
        $stale = $remoteShas[$remoteFile] !== $downloadedShas[$remoteFile];
      } else {
        $stale = (time() - $mtime) > (7 * 86400);
      }

      $result[$type] = [
        'exists' => true,
        'mtime'  => $mtime,
        'date'   => date('d.m.Y H:i', $mtime),
        'stale'  => $stale,
      ];
    }
    return $result;
  }

  private function actionGetCacheInfo(): void
  {
    $this->requireAdmin();
    $this->json(['success' => true, 'data' => $this->buildCacheInfo()]);
  }

  private function actionGetCategories(): void
  {
    $this->json(['success' => true, 'data' => $this->SkinchangerController->getCategories($this->language)]);
  }

  private function actionGetSkins(): void
  {
    $id = $this->input('id', 'int', 0);
    if (!$id) $this->json(['success' => false], 400);
    $skin = $this->SkinchangerController->getSkinByIdSorted($this->language, $id);
    $this->json(['success' => (bool) $skin, 'data' => $skin]);
  }

  private function actionCacheUpdate(): void
  {
    $this->requireAdmin();
    $name = $this->input('name', 'alpha', '');
    $this->json(['success' => true, 'data' => $this->SkinchangerController->cacheUpdate($name)]);
  }

  private function actionGetAgents(): void
  {
    $items = $this->SkinchangerController->getAgents($this->language);
    $this->json(['success' => true, 'data' => $this->normalizeItems(is_array($items) ? $items : [])]);
  }

  private function actionGetMusic(): void
  {
    $items = $this->SkinchangerController->getMusic($this->language);
    $this->json(['success' => true, 'data' => $this->normalizeItems(is_array($items) ? $items : [])]);
  }

  private function actionGetCoins(): void
  {
    $items = $this->SkinchangerController->getCoins($this->language);
    $this->json(['success' => true, 'data' => $this->normalizeItems(is_array($items) ? $items : [])]);
  }

  private function actionSearchSkins(): void
  {
    $query = trim($this->input('query', 'string', ''));
    if (mb_strlen($query) < 2) {
      $this->json(['success' => true, 'data' => []]);
      return;
    }
    $results = $this->SkinchangerController->searchSkins($query);
    $this->json(['success' => true, 'data' => $results]);
  }

  private function actionGetCollectibles(): void
  {
    $type = $this->input('type', 'alpha', 'sticker');
    $lang = $this->language;
    if ($type === 'keychain') {
      $items  = $this->SkinchangerController->getKeychains($lang);
      $enItems = ($lang !== 'en') ? $this->SkinchangerController->getKeychains('en') : [];
      $prefix = '/^(Брелок|Charm)\s*\|\s*/u';
    } else {
      $items  = $this->SkinchangerController->getStickers($lang);
      $enItems = ($lang !== 'en') ? $this->SkinchangerController->getStickers('en') : [];
      $prefix = '/^(Наклейка|Sticker)\s*\|\s*/u';
    }

    $enIndex = [];
    if (!empty($enItems)) {
      foreach ($enItems as $item) {
        $enIndex[$item['id']] = preg_replace($prefix, '', $item['name'] ?? '');
      }
    }

    $rarityOrder = ['rarity_contraband' => 0, 'rarity_ancient' => 1, 'rarity_legendary' => 2, 'rarity_mythical' => 3, 'rarity_rare' => 4, 'rarity_default' => 5];
    $normalized = $this->normalizeItems(is_array($items) ? $items : [], $prefix);

    foreach ($normalized as &$item) {
      $item['name_en'] = $enIndex[$item['id']] ?? '';
    }

    usort($normalized, function ($a, $b) use ($rarityOrder) {
      $ra = $rarityOrder[$a['id_rarity'] ?? ''] ?? 99;
      $rb = $rarityOrder[$b['id_rarity'] ?? ''] ?? 99;
      return $ra <=> $rb;
    });
    $this->json(['success' => true, 'data' => $normalized]);
  }

  private function actionGetPlaceholders(): void
  {
    $this->requireUser();
    $this->json(['success' => true, 'data' => $this->SkinchangerController->getPlaceholders()]);
  }

  private function actionGetPlayerSkins(): void
  {
    $this->requireUser();
    $data = $this->SkinchangerController->getPlayerSkins($this->language);

    $logLine = date('[Y-m-d H:i:s]') . ' GET /player/skins (' . count($data ?: []) . '): ' . implode(', ', array_map(fn($s) => ($s['weapon_name'] ?? $s['weapon_index']) . ':' . ($s['side'] ?? '?') . '(wi=' . $s['weapon_index'] . ')', $data ?: [])) . "\n---\n";

    $this->json(['success' => true, 'data' => $data ?: []]);
  }

  private function actionResetAll(): void
  {
    $this->requireUser();
    $result = $this->SkinchangerController->resetAll();
    if (!$result['success']) {
      $this->json($result);
      return;
    }
    $collectionId = $this->input('collection_id', 'int', 0);
    $editMode     = $this->input('edit_mode', 'int', 0);
    $this->respondWithSkins($collectionId, $editMode);
  }

  private function actionGetPlayerCollections(): void
  {
    $this->requireUser();
    if (!$this->tablesInstalled()) {
      $this->json(['success' => true, 'data' => ['tables_installed' => false, 'collections' => null, 'limit' => 0]]);
      return;
    }
    $this->json(['success' => true, 'data' => $this->SkinchangerController->getPlayerCollections()]);
  }

  private function actionCreateCollection(): void
  {
    $this->requireUser();
    if (!$this->tablesInstalled()) {
      $this->json(['success' => false, 'error' => 'tables_not_installed'], 503);
      return;
    }
    $raw      = $this->input('name', 'string', '');
    $name     = $this->removeZalgo(mb_substr(trim($raw), 0, 32));
    $isPublic = (bool)$this->input('is_public', 'int', 0);
    $this->json($this->SkinchangerController->createCollection($name, $isPublic));
  }

  private function actionRenameCollection(): void
  {
    $this->requireUser();
    $id   = $this->input('collection_id', 'int', 0);
    $raw  = $this->input('name', 'string', '');
    $name = $this->removeZalgo(mb_substr(trim($raw), 0, 32));
    $this->json($this->SkinchangerController->renameCollection($id, $name));
  }

  private function actionDeleteCollection(): void
  {
    $this->requireUser();
    $id     = $this->input('collection_id', 'int', 0);
    $result = $this->SkinchangerController->deleteCollection($id);
    if (!$result['success']) {
      $this->json($result);
      return;
    }

    if (!empty($result['was_active'])) {
      $collectionsData = $this->SkinchangerController->getPlayerCollections();
      $collections     = $collectionsData['collections'] ?? [];
      if (!empty($collections)) {
        $this->SkinchangerController->activateCollection((int)$collections[0]['id']);
      } else {
        $this->SkinchangerController->resetAll();
        $created = $this->SkinchangerController->createMainCollection();
        if (!empty($created['id'])) {
          $this->SkinchangerController->activateCollection((int)$created['id']);
        }
      }
    }

    $newCollectionsData = $this->SkinchangerController->getPlayerCollections();
    $skins              = $this->SkinchangerController->getPlayerSkins() ?: [];
    $this->json([
      'success'     => true,
      'collections' => $newCollectionsData,
      'skins'       => $skins,
    ]);
  }

  private function actionActivateCollection(): void
  {
    $this->requireUser();
    $id = $this->input('collection_id', 'int', 0);
    $install = $this->input('install', 'int', 0);
    if ($install) {
      $name = $this->removeZalgo(trim($this->input('name', 'string', '')));
      $this->json($this->SkinchangerController->installCollection($id, $name));
    } else {
      $this->json($this->SkinchangerController->activateCollection($id));
    }
  }

  private function actionSaveCollectionSnapshot(): void
  {
    $this->requireUser();
    $id = $this->input('collection_id', 'int', 0);
    $this->json($this->SkinchangerController->saveSnapshot($id));
  }

  private function actionSetCollectionPublic(): void
  {
    $this->requireUser();
    $id       = $this->input('collection_id', 'int', 0);
    $isPublic = (bool)$this->input('is_public', 'int', 0);
    $this->json($this->SkinchangerController->setCollectionPublic($id, $isPublic));
  }

  private function actionToggleCollectionLike(): void
  {
    $this->requireUser();
    $id = $this->input('collection_id', 'int', 0);
    $this->json($this->SkinchangerController->toggleLike($id));
  }

  private function actionGetPublicCollections(): void
  {
    $page  = max(1, $this->input('page', 'int', 1));
    $order = in_array($this->input('order', 'alpha', 'likes'), ['likes', 'installs'], true)
      ? $this->input('order', 'alpha', 'likes') : 'likes';

    $filters = [
      'search'       => trim($this->input('search', 'string', '')),
      'sort'         => $this->input('sort', 'string', ''),
      'with_downloads' => (bool)$this->input('with_downloads', 'int', 0),
      'liked_me'     => (bool)$this->input('liked_me', 'int', 0),
      'my_only'      => (bool)$this->input('my_only', 'int', 0),
    ];

    $this->json(['success' => true, 'data' => $this->SkinchangerController->getPublicCollections($this->language, $page, 6, $order, $filters)]);
  }

  private function actionGetDefaultCollections(): void
  {
    $this->json(['success' => true, 'data' => $this->SkinchangerController->getDefaultCollections($this->language)]);
  }

  private function actionActivateDefaultCollection(): void
  {
    $this->requireUser();
    $presetId = $this->input('preset_id', 'int', 0);
    if (!$presetId) {
      $this->json(['success' => false, 'error' => 'invalid'], 400);
      return;
    }
    $this->json($this->SkinchangerController->activateDefaultCollection($presetId));
  }

  private function actionGetPresetDetail(): void
  {
    $id = $this->input('preset_id', 'int', 0);
    $this->json($this->SkinchangerController->getPresetDetail($this->language, $id));
  }

  private function actionDeletePreset(): void
  {
    $this->requireAdmin();
    $id = $this->input('preset_id', 'int', 0);
    $this->json($this->SkinchangerController->deletePreset($id));
  }

  private function actionReorderPresets(): void
  {
    $this->requireAdmin();
    $idsStr = $this->input('ids', 'string', '');
    $ids = array_values(array_filter(array_map('intval', explode(',', $idsStr))));
    $this->json($this->SkinchangerController->reorderPresets($ids));
  }

  private function actionGetCollectionDetail(): void
  {
    $id = $this->input('collection_id', 'int', 0);
    $this->json($this->SkinchangerController->getCollectionDetail($this->language, $id));
  }

  private function actionSetCollectionDefault(): void
  {
    $this->requireAdmin();
    $id = $this->input('collection_id', 'int', 0);
    $isDefault = (bool)$this->input('is_default', 'int', 0);
    $this->json($this->SkinchangerController->setCollectionDefault($id, $isDefault));
  }

  private function actionCopyAsDefault(): void
  {
    $this->requireAdmin();
    $id   = $this->input('collection_id', 'int', 0);
    $name = $this->removeZalgo(trim($this->input('name', 'string', '')));
    $this->json($this->SkinchangerController->copyAsDefault($id, $name));
  }

  private function actionSaveCollectionSettings(): void
  {
    $this->requireAdmin();
    $limit = $this->input('collections_limit', 'int', 1);
    $limit = max(1, min(100, $limit));
    $plugin = $this->input('plugin', 'alpha', 'pisex');
    $allowed = ['pisex', 'weaponpaints'];
    if (!in_array($plugin, $allowed, true)) $plugin = 'pisex';

    $vipGroups = [];
    $rawGroups = json_decode($this->input('vip_groups', 'string', '[]'), true) ?? [];
    if (is_array($rawGroups)) {
      foreach ($rawGroups as $entry) {
        $name  = mb_substr(trim((string)($entry['group'] ?? '')), 0, 64);
        $slots = max(1, min(100, (int)($entry['slots'] ?? 0)));
        if ($name !== '') {
          $vipGroups[] = ['group' => $name, 'slots' => $slots];
        }
      }
    }

    $newCollections = [];
    $rawCollections = json_decode($this->input('new_collections', 'string', '[]'), true) ?? [];
    if (is_array($rawCollections)) {
      foreach ($rawCollections as $collectionId) {
        $collectionId = trim((string)$collectionId);
        if ($collectionId !== '') {
          $newCollections[] = $collectionId;
        }
      }
    }

    $this->json(['success' => $this->SkinchangerController->saveCollectionSettings([
      'collections_limit' => $limit,
      'plugin'            => $plugin,
      'vip_groups'        => $vipGroups,
      'vip_access_groups' => preg_replace('/[^a-zA-Z0-9_;,\- ]/', '', (string)($this->input('vip_access_groups', 'string', ''))),
      'new_collections'   => $newCollections,
    ])]);
  }

  private function actionInstallTables(): void
  {
    $this->requireAdmin();
    $Db  = $this->Db;
    $sqls = [
      "CREATE TABLE IF NOT EXISTS `lvl_web_skins_collections` (
        `id`             INT          NOT NULL AUTO_INCREMENT,
        `steamid`        VARCHAR(64)  NOT NULL DEFAULT '',
        `name`           VARCHAR(64)  NOT NULL DEFAULT 'Collection',
        `is_active`      TINYINT(1)   NOT NULL DEFAULT 0,
        `is_public`      TINYINT(1)   NOT NULL DEFAULT 0,
        `is_default`     TINYINT(1)   NOT NULL DEFAULT 0,
        `installs_count` INT          NOT NULL DEFAULT 0,
        `created_at`     INT          NOT NULL DEFAULT 0,
        `updated_at`     INT          NOT NULL DEFAULT 0,
        `published_at`   INT          NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `idx_steamid` (`steamid`),
        KEY `idx_is_public` (`is_public`),
        KEY `idx_is_default` (`is_default`),
        KEY `idx_installs` (`installs_count`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

      "CREATE TABLE IF NOT EXISTS `lvl_web_skins_collection_skins` (
        `id`              INT          NOT NULL AUTO_INCREMENT,
        `collection_id`   INT          NOT NULL DEFAULT 0,
        `server_id`       INT          NOT NULL DEFAULT 0,
        `team`            TINYINT      NOT NULL DEFAULT 0,
        `weapon_index`    INT          NOT NULL DEFAULT 0,
        `skin`            VARCHAR(32)  NOT NULL DEFAULT '0;0;0.0',
        `stattrack`       TINYINT(1)   NOT NULL DEFAULT 0,
        `stattrack_count` INT          NOT NULL DEFAULT 0,
        `stickers`        VARCHAR(64)  NOT NULL DEFAULT '0;0;0;0',
        `keychain`        VARCHAR(32)  NOT NULL DEFAULT '',
        `tag`             VARCHAR(20)  NOT NULL DEFAULT '',
        PRIMARY KEY (`id`),
        KEY `idx_collection` (`collection_id`),
        UNIQUE KEY `uq_skin` (`collection_id`, `server_id`, `team`, `weapon_index`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

      "CREATE TABLE IF NOT EXISTS `lvl_web_skins_collection_items` (
        `id`            INT     NOT NULL AUTO_INCREMENT,
        `collection_id` INT     NOT NULL DEFAULT 0,
        `server_id`     INT     NOT NULL DEFAULT 0,
        `team`          TINYINT NOT NULL DEFAULT 0,
        `knife`         INT     NOT NULL DEFAULT 0,
        `glove`         INT     NOT NULL DEFAULT 0,
        `agent`         INT     NOT NULL DEFAULT 0,
        `music`         INT     NOT NULL DEFAULT 0,
        `coin`          INT     NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `idx_collection` (`collection_id`),
        UNIQUE KEY `uq_item` (`collection_id`, `server_id`, `team`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

      "CREATE TABLE IF NOT EXISTS `lvl_web_skins_collection_likes` (
        `id`            INT         NOT NULL AUTO_INCREMENT,
        `collection_id` INT         NOT NULL,
        `steamid`       VARCHAR(64) NOT NULL DEFAULT '',
        `created_at`    INT         NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `idx_collection` (`collection_id`),
        UNIQUE KEY `uq_like` (`collection_id`, `steamid`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

      "CREATE TABLE IF NOT EXISTS `lvl_web_skins_presets` (
        `id`         INT         NOT NULL AUTO_INCREMENT,
        `name`       VARCHAR(64) NOT NULL DEFAULT '',
        `sort_order` INT         NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

      "CREATE TABLE IF NOT EXISTS `lvl_web_skins_preset_skins` (
        `id`              INT         NOT NULL AUTO_INCREMENT,
        `preset_id`       INT         NOT NULL DEFAULT 0,
        `server_id`       INT         NOT NULL DEFAULT 0,
        `team`            TINYINT     NOT NULL DEFAULT 0,
        `weapon_index`    INT         NOT NULL DEFAULT 0,
        `skin`            VARCHAR(32) NOT NULL DEFAULT '0;0;0.0',
        `stattrack`       TINYINT(1)  NOT NULL DEFAULT 0,
        `stattrack_count` INT         NOT NULL DEFAULT 0,
        `stickers`        VARCHAR(64) NOT NULL DEFAULT '0;0;0;0',
        `keychain`        VARCHAR(32) NOT NULL DEFAULT '',
        `tag`             VARCHAR(20) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`),
        KEY `idx_preset` (`preset_id`),
        UNIQUE KEY `uq_preset_skin` (`preset_id`, `server_id`, `team`, `weapon_index`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

      "CREATE TABLE IF NOT EXISTS `lvl_web_skins_preset_items` (
        `id`        INT     NOT NULL AUTO_INCREMENT,
        `preset_id` INT     NOT NULL DEFAULT 0,
        `server_id` INT     NOT NULL DEFAULT 0,
        `team`      TINYINT NOT NULL DEFAULT 0,
        `knife`     INT     NOT NULL DEFAULT 0,
        `glove`     INT     NOT NULL DEFAULT 0,
        `agent`     INT     NOT NULL DEFAULT 0,
        `music`     INT     NOT NULL DEFAULT 0,
        `coin`      INT     NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `idx_preset` (`preset_id`),
        UNIQUE KEY `uq_preset_item` (`preset_id`, `server_id`, `team`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    ];
    foreach ($sqls as $sql) {
      $Db->query('Skins', 0, 0, $sql, []);
    }
    $this->json(['success' => true]);
  }

  private function actionCheckTablesInstalled(): void
  {
    $this->requireAdmin();
    $this->json(['success' => true, 'installed' => $this->tablesInstalled()]);
  }

  private function actionGetCollectionSettings(): void
  {
    $this->requireAdmin();
    $this->json(['success' => true, 'data' => $this->SkinchangerController->getCollectionSettings()]);
  }

  private function actionGetSettingsInitData(): void
  {
    $this->requireAdmin();

    $tablesInstalled = $this->tablesInstalled();
    $language = strtolower($_SESSION['language'] ?? 'en');
    $collections = $this->SkinchangerController->getCollections($language);

    $this->json([
      'success'             => true,
      'tables_installed'    => $tablesInstalled,
      'cache_info'          => $this->buildCacheInfo(),
      'default_collections' => $tablesInstalled ? $this->SkinchangerController->getDefaultCollections($language) : [],
      'collection_settings' => $this->SkinchangerController->getCollectionSettings(),
      'collections'         => $collections,
    ]);
  }

  private function tablesInstalled(): bool
  {
    static $checked = null;
    if ($checked !== null) return $checked;
    $tables = ['lvl_web_skins_collections', 'lvl_web_skins_collection_skins', 'lvl_web_skins_collection_items', 'lvl_web_skins_collection_likes', 'lvl_web_skins_presets', 'lvl_web_skins_preset_skins', 'lvl_web_skins_preset_items'];
    foreach ($tables as $table) {
      if (!$this->Db->mysql_table_search('Skins', 0, 0, $table)) {
        return $checked = false;
      }
    }
    return $checked = true;
  }

  private function actionGetInitData(): void
  {
    $this->requireUser();
    if (!$this->tablesInstalled()) {
      $this->json(['success' => true, 'data' => [
        'tables_installed'    => false,
        'collections'         => null,
        'skins'               => [],
        'categories'          => [],
        'default_collections' => [],
      ]]);
      return;
    }
    $data = $this->SkinchangerController->getInitData($this->language);
    $skins = $data['skins'] ?? [];
    $logLine = date('[Y-m-d H:i:s]') . ' GET /player/init skins (' . count($skins) . '): ' . implode(', ', array_map(fn($s) => ($s['weapon_name'] ?? $s['weapon_index']) . ':' . ($s['side'] ?? '?') . '(wi=' . $s['weapon_index'] . ')', $skins)) . "\n---\n";
    $this->json(['success' => true, 'data' => $data]);
  }

  private function sideToTeams(string $side): array
  {
    if ($side === 't')  return [0];
    if ($side === 'ct') return [1];
    return [0, 1];
  }

  private function respondWithSkins(int $collectionId, int $editMode): void
  {
    if ($collectionId) $this->SkinchangerController->saveSnapshot($collectionId);
    $skins = $this->SkinchangerController->getPlayerSkins($this->language) ?: [];
    $placeholders = $editMode ? $this->SkinchangerController->getPlaceholders() : null;
    $skinsCount = $collectionId ? $this->SkinchangerController->getCollectionSkinsCount($collectionId) : null;

    $logLine = date('[Y-m-d H:i:s]') . ' RESPONSE skins (' . count($skins) . '): ' . implode(', ', array_map(fn($s) => ($s['weapon_name'] ?? $s['weapon_index']) . ':' . ($s['side'] ?? '?') . '(wi=' . $s['weapon_index'] . ')', $skins)) . "\n---\n";

    $this->json([
      'success'      => true,
      'skins'        => $skins,
      'placeholders' => $placeholders,
      'skins_count'  => $skinsCount
    ]);
  }

  private function actionAssignSkinFull(): void
  {
    $this->requireUser();
    $side         = $this->input('side', 'alpha', 'both');
    $weaponIndex  = $this->input('weapon_index', 'int', 0);
    $skinId       = $this->input('skin_id', 'int', 0);
    $collectionId = $this->input('collection_id', 'int', 0);
    $editMode     = $this->input('edit_mode', 'int', 0);
    $this->SkinchangerController->setSkinForSide($this->language, $side, $weaponIndex, $skinId);
    $this->respondWithSkins($collectionId, $editMode);
  }

  private function actionUpdateSkinSettingsFull(): void
  {
    $this->requireUser();
    $side           = $this->input('side', 'alpha', 'both');
    $weaponIndex    = $this->input('weapon_index', 'int', 0);
    $float          = $this->input('float', 'float', null);
    $pattern        = $this->input('pattern', 'int', null);
    $stattrack      = $this->input('stattrack', 'int', null);
    $stattrackCount = $this->input('stattrack_count', 'int', null);
    $rawTag         = $this->input('tag', 'string', null);
    $tag            = $rawTag !== null ? $this->removeZalgo(mb_substr(trim($rawTag), 0, 20)) : null;
    $stickersJson   = $this->input('stickers', 'string', '');
    $keychainId     = $this->input('keychain_id', 'int', null);
    $collectionId   = $this->input('collection_id', 'int', 0);

    $teams               = $this->sideToTeams($side);
    $originalSide        = $this->input('original_side', 'alpha', $side);
    $oppositeWeaponIndex = $this->input('opposite_weapon_index', 'int', 0);
    $isKnifeOrGlove      = $this->SkinchangerController->isKnifeOrGlove($weaponIndex);
    $sideChanged         = $side !== $originalSide;
    $opposite            = ($sideChanged && $isKnifeOrGlove && $side !== 'both') ? ($side === 't' ? [1] : [0]) : [];

    $stickers = null;
    if ($stickersJson) {
      $parsed = json_decode($stickersJson, true);
      if (is_array($parsed)) {
        $stickers = [];
        for ($slot = 0; $slot < 4; $slot++) {
          $stickers[$slot] = isset($parsed[$slot]) ? (int)$parsed[$slot] : 0;
        }
      }
    }

    if ($sideChanged && $isKnifeOrGlove && $side === 'both') {
      $oppositeTeam = ($originalSide === 't') ? 1 : 0;
      $this->SkinchangerController->resetSkin($oppositeTeam, $weaponIndex);
      if ($oppositeWeaponIndex > 0 && $oppositeWeaponIndex !== $weaponIndex) {
        $this->SkinchangerController->resetSkin($oppositeTeam, $oppositeWeaponIndex);
      }
    }
    foreach ($teams as $team) {
      $this->SkinchangerController->updateSkinSettings($team, $weaponIndex, $float, $pattern, $stattrack, $stattrackCount, $tag, $stickers, $keychainId);
    }
    foreach ($opposite as $team) {
      $this->SkinchangerController->resetSkin($team, $weaponIndex);
    }

    if ($collectionId) $this->SkinchangerController->saveSnapshot($collectionId);

    $this->json(['success' => true]);
  }

  private function actionRemoveSkinFull(): void
  {
    $this->requireUser();
    $side         = $this->input('side', 'alpha', 'both');
    $weaponIndex  = $this->input('weapon_index', 'int', 0);
    $collectionId = $this->input('collection_id', 'int', 0);
    $editMode     = $this->input('edit_mode', 'int', 0);
    foreach ($this->sideToTeams($side) as $team) {
      $this->SkinchangerController->resetSkin($team, $weaponIndex);
    }
    $this->respondWithSkins($collectionId, $editMode);
  }

  private function actionRemoveCheckedSkins(): void
  {
    $this->requireUser();
    $collectionId = $this->input('collection_id', 'int', 0);
    $editMode     = $this->input('edit_mode', 'int', 0);
    $skinsRaw     = $this->input('skins', 'string', '');
    $skins        = is_array($skinsRaw) ? $skinsRaw : (is_string($skinsRaw) ? json_decode($skinsRaw, true) : []);
    if (!is_array($skins)) {
      $this->json(['success' => false, 'error' => 'invalid_data'], 400);
      return;
    }
    $allowed = ['t', 'ct', 'both'];
    $itemFieldMap = [
      'agent_ct' => 'agent',
      'agent_t'  => 'agent',
      'record'   => 'music',
      'coin'     => 'coin',
    ];
    $pairs = [];
    $pairMap = [];
    foreach ($skins as $item) {
      $weaponIndex = (int)($item['weapon_index'] ?? 0);
      $side        = in_array($item['side'] ?? '', $allowed, true) ? $item['side'] : 'both';
      $nameId      = $item['name_id'] ?? '';
      if ($weaponIndex <= 0 && !isset($itemFieldMap[$nameId])) continue;

      if (isset($itemFieldMap[$nameId])) {
        foreach ($this->sideToTeams($side) as $team) {
          $this->SkinchangerController->removeItem($team, $itemFieldMap[$nameId]);
        }
      } else {
        foreach ($this->sideToTeams($side) as $team) {
          $key = $team . ':' . $weaponIndex;
          if (isset($pairMap[$key])) continue;
          $pairMap[$key] = true;
          $pairs[] = ['team' => $team, 'weapon_index' => $weaponIndex];
        }
      }
    }
    if (!empty($pairs)) {
      $this->SkinchangerController->resetSkins($pairs);
    }
    $this->respondWithSkins($collectionId, $editMode);
  }

  private function actionAssignItemFull(): void
  {
    $this->requireUser();
    $side         = $this->input('side', 'alpha', 'both');
    $field        = $this->input('field', 'alpha', '');
    $weaponIndex  = $this->input('weapon_index', 'int', 0);
    $collectionId = $this->input('collection_id', 'int', 0);
    $editMode     = $this->input('edit_mode', 'int', 0);
    foreach ($this->sideToTeams($side) as $team) {
      $this->SkinchangerController->assignItem($team, $field, $weaponIndex);
    }
    $this->respondWithSkins($collectionId, $editMode);
  }

  private function actionRemoveItemFull(): void
  {
    $this->requireUser();
    $side         = $this->input('side', 'alpha', 'both');
    $field        = $this->input('field', 'alpha', '');
    $collectionId = $this->input('collection_id', 'int', 0);
    $editMode     = $this->input('edit_mode', 'int', 0);
    foreach ($this->sideToTeams($side) as $team) {
      $this->SkinchangerController->removeItem($team, $field);
    }
    $this->respondWithSkins($collectionId, $editMode);
  }
}
