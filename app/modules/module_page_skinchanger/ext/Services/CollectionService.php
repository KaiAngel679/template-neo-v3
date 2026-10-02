<?php

namespace app\modules\module_page_skinchanger\ext\Services;

use app\modules\module_page_skinchanger\ext\Repositories\{
  CategoryRepository,
  CollectionRepository,
  SettingsRepository,
  SkinRepository
};
use app\modules\module_page_skinchanger\ext\Repositories\Plugins\PluginFactory;

class CollectionService
{
  protected $Translate, $CollectionRepository, $PluginRepo, $SettingsRepository, $SkinRepository, $CategoryRepository, $Db, $General, $languages;
  private $cachedFirstServerId = null;

  public function __construct(object $Translate, ?object $Db = null, ?object $General = null)
  {
    $this->Translate            = $Translate;
    $this->Db                   = $Db;
    $this->General              = $General;
    $this->PluginRepo           = PluginFactory::create($Db);
    $this->CollectionRepository = new CollectionRepository($Db);
    $this->SettingsRepository   = new SettingsRepository();
    $this->SkinRepository       = new SkinRepository();
    $this->CategoryRepository   = new CategoryRepository();
  }

  private function resolveServerId(int $serverId = 0): int
  {
    if ($serverId > 0) return $serverId;
    if ($this->cachedFirstServerId === null) {
      $this->cachedFirstServerId = $this->PluginRepo->getFirstServerId();
    }
    return $this->cachedFirstServerId;
  }

  private function resolvePlayerId(string $steamid)
  {
    return $this->PluginRepo->resolveIdentifier($steamid);
  }

  private function resolveCoverImg(?int $weaponIndex, ?string $skinField = null): string
  {
    if (!$weaponIndex) return '';
    $lang = $this->languages ?? 'en';
    $weapon = $this->SkinRepository->getById($lang, $weaponIndex);
    if (!$weapon) return '';

    if ($skinField) {
      $parts = explode(';', $skinField);
      $skinId = (int)($parts[0] ?? 0);
      if ($skinId > 0 && !empty($weapon['skins'])) {
        foreach ($weapon['skins'] as $sk) {
          if ((int)$sk['id_skin'] === $skinId) {
            return $sk['image'] ?? ($weapon['img'] ?? '');
          }
        }
      }
    }

    return $weapon['img'] ?? '';
  }

  private function findSkinByWeaponIndex(array $skins, int $weaponIndex): ?string
  {
    foreach ($skins as $s) {
      if ((int)($s['weapon_index'] ?? 0) === $weaponIndex) {
        return $s['skin'] ?? null;
      }
    }
    return null;
  }

  private function computeSkinsCount(array $skins, array $items): int
  {
    $itemWeapons = [];
    $distinctKnives = [];
    $distinctGloves = [];
    $distinctAgents = [];
    $distinctMusic  = [];
    $distinctCoins  = [];
    foreach ($items as $item) {
      $k = (int)($item['knife'] ?? 0);
      if ($k > 0) {
        $itemWeapons[$k] = true;
        $distinctKnives[$k] = true;
      }
      $g = (int)($item['glove'] ?? 0);
      if ($g > 0) {
        $itemWeapons[$g] = true;
        $distinctGloves[$g] = true;
      }
      $a = (int)($item['agent'] ?? 0);
      if ($a > 0) $distinctAgents[$a] = true;
      $m = (int)($item['music'] ?? 0);
      if ($m > 0) $distinctMusic[$m]  = true;
      $c = (int)($item['coin']  ?? 0);
      if ($c > 0) $distinctCoins[$c]  = true;
    }
    $skinPairs = [];
    foreach ($skins as $s) {
      $wi = (int)$s['weapon_index'];
      if (isset($itemWeapons[$wi])) continue;
      $skinPairs[$wi . ':' . ($s['skin'] ?? '')] = true;
    }
    return count($skinPairs) + count($distinctKnives) + count($distinctGloves) + count($distinctAgents) + count($distinctMusic) + count($distinctCoins);
  }

  private function findKnifeSkin(int $collectionId, int $knifeIndex): ?string
  {
    $skins = $this->CollectionRepository->getCollectionSkins($collectionId);
    return $skins ? $this->findSkinByWeaponIndex($skins, $knifeIndex) : null;
  }

  private function getPlayerVipGroups(string $steamid): array
  {
    if (empty($this->Db) || empty($this->Db->db_data['Vips'])) {
      return [];
    }

    $steam3  = con_steam64to3_int($steamid);
    $vipRows = [];
    for ($i = 0; $i < $this->Db->table_count['Vips']; $i++) {
      $rows = $this->Db->queryAll(
        'Vips',
        $this->Db->db_data['Vips'][$i]['USER_ID'],
        $this->Db->db_data['Vips'][$i]['DB_num'],
        "SELECT `group` FROM `vip_users` WHERE (`expires` = 0 OR `expires` > UNIX_TIMESTAMP()) AND `account_id` LIKE :steam",
        ['steam' => '%' . $steam3 . '%']
      );
      if (!empty($rows)) $vipRows = array_merge($vipRows, $rows);
    }

    return array_column($vipRows, 'group');
  }

  public function getSettings(): array
  {
    return $this->SettingsRepository->getSettings();
  }

  public function saveSettings(array $settings): bool
  {
    return $this->SettingsRepository->saveSettings($settings);
  }

  public function getCollectionLimit(string $steamid): int
  {
    $settings = $this->SettingsRepository->getSettings();
    $base     = (int)($settings['collections_limit'] ?? 5);
    $groups   = $settings['vip_groups'] ?? [];

    if (empty($groups)) {
      return $base;
    }

    $playerGroups = $this->getPlayerVipGroups($steamid);
    if (empty($playerGroups)) return $base;

    $bonus = 0;
    foreach ($groups as $entry) {
      $name  = trim($entry['group'] ?? '');
      $slots = (int)($entry['slots'] ?? 0);
      if ($name !== '' && $slots > $bonus && in_array($name, $playerGroups, true)) {
        $bonus = $slots;
      }
    }
    return $base + $bonus;
  }

  public function hasVipAccess(string $steamid): bool
  {
    $settings         = $this->SettingsRepository->getSettings();
    $vipAccessGroups  = trim($settings['vip_access_groups'] ?? '');

    if ($vipAccessGroups === '') {
      return true;
    }

    $requiredGroups = array_filter(array_map('trim', explode(';', $vipAccessGroups)));
    if (empty($requiredGroups)) {
      return true;
    }

    $playerGroups = $this->getPlayerVipGroups($steamid);
    return !empty(array_intersect($requiredGroups, $playerGroups));
  }

  public function getPlayerCollections(string $steamid): array
  {
    try {
      $player = $this->PluginRepo->getPlayer($steamid);
      if (!$player) return [];
      $playerId = $this->resolvePlayerId($steamid);
      if (!$playerId) return [];
      $serverId = $this->resolveServerId(0);

      $collections = $this->CollectionRepository->getCollections($steamid) ?: [];
      $limit       = $this->getCollectionLimit($steamid);

      if (count($collections) === 1) {
        foreach ($collections as $col) {
          if ((int)($col['is_active'] ?? 0) === 1 && (int)($col['skins_count'] ?? 0) === 0) {
            $rawSkins = $this->PluginRepo->getSkins($playerId);
            if (!empty($rawSkins)) {
              $this->snapshotPlayerToCollection($steamid, (int)$col['id'], $serverId);
              $collections = $this->CollectionRepository->getCollections($steamid) ?: [];
            }
            break;
          }
        }
      }

      $playerItems = null;
      $playerSkins = null;
      $hasActiveCollection = false;

      foreach ($collections as $col) {
        if ((int)($col['is_active'] ?? 0) === 1) {
          $hasActiveCollection = true;
          break;
        }
      }

      if ($hasActiveCollection) {
        $playerItems = $this->PluginRepo->getItems($playerId);
        $playerSkins = $this->PluginRepo->getSkins($playerId);
      }

      foreach ($collections as &$col) {
        $col['player_liked'] = false;

        if ((int)($col['is_active'] ?? 0) === 1) {
          $knifeIdx = 0;
          $weaponIdx = null;
          $skinField = null;

          if ($playerItems) {
            foreach ($playerItems as $it) {
              if ((int)($it['knife'] ?? 0) > 0) {
                $knifeIdx = (int)$it['knife'];
                break;
              }
            }
          }

          if (!$knifeIdx && $playerSkins) {
            $anyWeapon = null;
            foreach ($playerSkins as $s) {
              $wi = (int)($s['weapon_index'] ?? 0);
              if ($wi <= 0) continue;
              if (($s['skin'] ?? '0;0;0.0') !== '0;0;0.0') {
                $weaponIdx = $wi;
                $skinField = $s['skin'];
                break;
              }
              if ($anyWeapon === null) $anyWeapon = $s;
            }
            if (!$weaponIdx && $anyWeapon) {
              $weaponIdx = (int)$anyWeapon['weapon_index'];
              $skinField = $anyWeapon['skin'] ?? null;
            }
          }

          if (!$knifeIdx && !$weaponIdx) {
            $knifeIdx  = isset($col['cover_knife_index']) ? (int)$col['cover_knife_index'] : 0;
            $weaponIdx = isset($col['cover_weapon_index']) ? (int)$col['cover_weapon_index'] : null;
            $skinField = $col['cover_skin'] ?? null;
            if ($knifeIdx > 0) {
              $col['cover_img'] = $this->resolveCoverImg($knifeIdx, $this->findKnifeSkin((int)$col['id'], $knifeIdx));
            } else {
              $col['cover_img'] = $this->resolveCoverImg($weaponIdx, $skinField);
            }
            continue;
          }

          if ($playerSkins) {
            $uniqueSkins = [];
            foreach ($playerSkins as $s) {
              $skin = $s['skin'] ?? '0;0;0.0';
              if ($skin !== '0;0;0.0') {
                $weaponIndex = (int)($s['weapon_index'] ?? 0);
                $key = $weaponIndex . '_' . $skin;
                $uniqueSkins[$key] = true;
              }
            }
            $liveCount = count($uniqueSkins);
            $col['skins_count'] = max((int)$col['skins_count'], $liveCount);
          }

          if ($knifeIdx > 0) {
            $col['cover_img'] = $this->resolveCoverImg($knifeIdx, $this->findSkinByWeaponIndex($playerSkins ?: [], $knifeIdx));
          } else {
            $col['cover_img'] = $this->resolveCoverImg($weaponIdx, $skinField);
          }
        } else {
          $knifeIdx  = isset($col['cover_knife_index']) ? (int)$col['cover_knife_index'] : 0;
          $weaponIdx = isset($col['cover_weapon_index']) ? (int)$col['cover_weapon_index'] : null;
          $skinField = $col['cover_skin'] ?? null;

          if ($knifeIdx > 0) {
            $col['cover_img'] = $this->resolveCoverImg($knifeIdx, $this->findKnifeSkin((int)$col['id'], $knifeIdx));
          } else {
            $col['cover_img'] = $this->resolveCoverImg($weaponIdx, $skinField);
          }
        }
      }

      return [
        'collections' => $collections,
        'count'       => count($collections),
        'limit'       => $limit,
      ];
    } catch (\Exception $e) {
      return [
        'collections' => [],
        'count'       => 0,
        'limit'       => 5,
      ];
    }
  }

  public function createCollection(string $steamid, string $name, bool $isPublic = false): array
  {
    try {
      $name = mb_substr(trim($name), 0, 32);
      if ($name === '') return ['success' => false, 'error' => 'empty_name'];

      $player = $this->PluginRepo->getPlayer($steamid);
      if (!$player) return ['success' => false, 'error' => 'player_not_found'];

      $playerId = $this->resolvePlayerId($steamid);
      if (!$playerId) return ['success' => false, 'error' => 'player_not_found'];

      $limit    = $this->getCollectionLimit($steamid);
      $currentCount = $this->CollectionRepository->countCollections($steamid);

      if ($currentCount >= $limit) {
        return ['success' => false, 'error' => 'limit_reached'];
      }

      $id = $this->CollectionRepository->createCollection($steamid, $name, $isPublic);
      if ($currentCount === 0) {
        $rawSkins = $this->PluginRepo->getSkins($playerId);
        if (!empty($rawSkins)) $this->CollectionRepository->bulkInsertCollectionSkins((int)$id, $rawSkins);
        $rawItems = $this->PluginRepo->getItems($playerId);
        if (!empty($rawItems)) $this->CollectionRepository->bulkInsertCollectionItems((int)$id, $rawItems);
      }

      return ['success' => true, 'id' => $id, 'name' => $name];
    } catch (\Exception $e) {
      return ['success' => false, 'error' => 'database_error'];
    }
  }

  public function createMainCollection(string $steamid): array
  {
    $name = $this->Translate->get_translate_module_phrase('module_page_skinchanger', '_main_collection');
    return $this->createCollection($steamid, $name, false);
  }

  public function renameCollection(string $steamid, int $collectionId, string $name): array
  {
    try {
      $name = mb_substr(trim($name), 0, 32);
      if ($name === '') return ['success' => false, 'error' => 'empty_name'];

      $player = $this->PluginRepo->getPlayer($steamid);
      if (!$player) return ['success' => false, 'error' => 'player_not_found'];

      $col = $this->CollectionRepository->getCollection($collectionId);
      if (!$col || $col['steamid'] !== $steamid) {
        return ['success' => false, 'error' => 'not_found'];
      }

      $this->CollectionRepository->renameCollection($collectionId, $name);
      return ['success' => true];
    } catch (\Exception $e) {
      return ['success' => false, 'error' => 'database_error'];
    }
  }

  public function deleteCollection(string $steamid, int $collectionId): array
  {
    $player = $this->PluginRepo->getPlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];

    $col = $this->CollectionRepository->getCollection($collectionId);
    if (!$col) return ['success' => false, 'error' => 'not_found'];

    $isAdmin  = !empty($_SESSION['user_admin']);
    $isOwner  = $col['steamid'] === $steamid;
    $isDefault = !empty($col['is_default']);

    if (!$isOwner && !($isAdmin && $isDefault)) {
      return ['success' => false, 'error' => 'not_found'];
    }

    $wasActive = !empty($col['is_active']);
    $this->CollectionRepository->deleteCollection($collectionId);
    return ['success' => true, 'was_active' => $wasActive];
  }

  public function activateCollection(string $steamid, int $collectionId, int $serverId = 0): array
  {
    $serverId = $this->resolveServerId($serverId);
    $player = $this->PluginRepo->getPlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];

    $playerId = $this->resolvePlayerId($steamid);
    if (!$playerId) return ['success' => false, 'error' => 'player_not_found'];

    $col = $this->CollectionRepository->getCollection($collectionId);
    if (!$col) return ['success' => false, 'error' => 'not_found'];
    $isOwner = $col['steamid'] === $steamid;
    $isDefault = !empty($col['is_default']);
    if (!$isOwner && !$isDefault) {
      return ['success' => false, 'error' => 'not_found'];
    }

    $this->PluginRepo->deleteAllSkins($playerId);
    $this->PluginRepo->deleteAllItems($playerId);

    $collSkins = $this->CollectionRepository->getCollectionSkins($collectionId);
    $collItems = $this->CollectionRepository->getCollectionItems($collectionId);

    $skinsForServer = array_values(array_filter($collSkins ?: [], fn($s) => (int)$s['server_id'] === $serverId));
    if ($skinsForServer) $this->PluginRepo->bulkInsertSkins($playerId, $skinsForServer);

    $itemsForServer = array_values(array_filter($collItems ?: [], fn($it) => (int)$it['server_id'] === $serverId));
    if ($itemsForServer) $this->PluginRepo->bulkInsertItems($playerId, $itemsForServer);

    $this->CollectionRepository->setActiveCollection($steamid, $collectionId);
    return ['success' => true];
  }

  public function installCollection(string $steamid, int $collectionId, string $customName = '', int $serverId = 0): array
  {
    $serverId = $this->resolveServerId($serverId);
    $player = $this->PluginRepo->getPlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];

    $playerId = $this->resolvePlayerId($steamid);
    if (!$playerId) return ['success' => false, 'error' => 'player_not_found'];

    $col = $this->CollectionRepository->getCollection($collectionId);
    if (!$col) return ['success' => false, 'error' => 'not_found'];

    $limit    = $this->getCollectionLimit($steamid);
    if ($this->CollectionRepository->countCollections($steamid) >= $limit) {
      return ['success' => false, 'error' => 'limit_reached'];
    }

    $active = $this->CollectionRepository->getActiveCollection($steamid);
    if ($active) {
      $this->snapshotPlayerToCollection($steamid, (int)$active['id'], $serverId);
    }

    $newName = mb_substr(trim($customName ?: $col['name'] ?? ''), 0, 32) ?: 'Collection';
    $newId = $this->CollectionRepository->createCollection($steamid, $newName);

    $collSkins = $this->CollectionRepository->getCollectionSkins($collectionId);
    $collItems = $this->CollectionRepository->getCollectionItems($collectionId);

    if ($collSkins) $this->CollectionRepository->bulkInsertCollectionSkins($newId, $collSkins);
    if ($collItems) $this->CollectionRepository->bulkInsertCollectionItems($newId, $collItems);

    $this->PluginRepo->deleteAllSkins($playerId);
    $this->PluginRepo->deleteAllItems($playerId);

    $skinsForServer = array_values(array_filter($collSkins ?: [], fn($s) => (int)$s['server_id'] === $serverId));
    if ($skinsForServer) $this->PluginRepo->bulkInsertSkins($playerId, $skinsForServer);

    $itemsForServer = array_values(array_filter($collItems ?: [], fn($it) => (int)$it['server_id'] === $serverId));
    if ($itemsForServer) $this->PluginRepo->bulkInsertItems($playerId, $itemsForServer);

    $this->CollectionRepository->setActiveCollection($steamid, $newId);
    $this->CollectionRepository->incrementInstalls($collectionId);

    return ['success' => true];
  }

  private function snapshotPlayerToCollection(string $steamid, int $collectionId, int $serverId): void
  {
    $this->CollectionRepository->clearCollectionSkins($collectionId);
    $this->CollectionRepository->clearCollectionItems($collectionId);

    $playerId = $this->resolvePlayerId($steamid);
    if (!$playerId) return;

    $rawSkins = $this->PluginRepo->getSkins($playerId);
    $skinsForServer = array_values(array_filter($rawSkins ?: [], fn($s) => (int)$s['server_id'] === $serverId));
    if ($skinsForServer) $this->CollectionRepository->bulkInsertCollectionSkins($collectionId, $skinsForServer);

    $rawItems = $this->PluginRepo->getItems($playerId);
    $itemsForServer = array_values(array_filter($rawItems ?: [], fn($it) => (int)$it['server_id'] === $serverId));
    if ($itemsForServer) $this->CollectionRepository->bulkInsertCollectionItems($collectionId, $itemsForServer);

    $this->CollectionRepository->touchCollection($collectionId);
  }

  public function saveSnapshot(string $steamid, int $collectionId, int $serverId = 0): array
  {
    $serverId = $this->resolveServerId($serverId);
    $player = $this->PluginRepo->getPlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];

    $col = $this->CollectionRepository->getCollection($collectionId);
    if (!$col || $col['steamid'] !== $steamid) {
      return ['success' => false, 'error' => 'not_found'];
    }

    $this->snapshotPlayerToCollection($steamid, $collectionId, $serverId);

    return ['success' => true];
  }

  public function getCollectionSkinsCount(int $collectionId): int
  {
    return $this->CollectionRepository->countCollectionSkinsDistinct($collectionId);
  }

  public function setPublic(string $steamid, int $collectionId, bool $isPublic): array
  {
    $player = $this->PluginRepo->getPlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];

    $col = $this->CollectionRepository->getCollection($collectionId);
    if (!$col || $col['steamid'] !== $steamid) {
      return ['success' => false, 'error' => 'not_found'];
    }

    $this->CollectionRepository->setCollectionPublic($collectionId, $isPublic);
    return ['success' => true, 'is_public' => (int)$isPublic];
  }

  public function toggleLike(string $steamid, int $collectionId): array
  {
    $player = $this->PluginRepo->getPlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];

    $col = $this->CollectionRepository->getCollection($collectionId);
    if (!$col) return ['success' => false, 'error' => 'not_found'];

    $existing = $this->CollectionRepository->getLike($collectionId, $steamid);
    if ($existing) {
      $this->CollectionRepository->removeLike($collectionId, $steamid);
      $liked = false;
    } else {
      $this->CollectionRepository->addLike($collectionId, $steamid);
      $liked = true;
    }

    $total = $this->CollectionRepository->countLikes($collectionId);
    return ['success' => true, 'liked' => $liked, 'count' => $total];
  }

  public function getDefaultCollections(string $lang, string $steamid): array
  {
    try {
      $items = $this->CollectionRepository->getPresets() ?: [];

      $presetIds = array_map('intval', array_column($items, 'id'));
      $allPresetSkins = $this->CollectionRepository->getPresetSkinsBatch($presetIds);
      $allPresetItems = $this->CollectionRepository->getPresetItemsBatch($presetIds);

      foreach ($items as &$col) {
        $presetId = (int)$col['id'];
        $presetSkins = $allPresetSkins[$presetId] ?? [];
        $presetItems = $allPresetItems[$presetId] ?? [];

        $col['player_liked'] = false;
        $col['skins_count'] = $this->computeSkinsCount($presetSkins, $presetItems);

        $knifeIdx = 0;
        foreach ($presetItems as $item) {
          if ((int)($item['knife'] ?? 0) > 0) {
            $knifeIdx = (int)$item['knife'];
            break;
          }
        }
        $firstSkin = $presetSkins[0] ?? null;
        $weaponIdx = $firstSkin ? (int)$firstSkin['weapon_index'] : null;
        $skinField = $firstSkin ? ($firstSkin['skin'] ?? null) : null;

        $knifeSkin = null;
        if ($knifeIdx > 0) {
          $knifeSkin = $this->findSkinByWeaponIndex($presetSkins, $knifeIdx);
        }

        $col['cover_img'] = $knifeIdx > 0
          ? $this->resolveCoverImg($knifeIdx, $knifeSkin)
          : $this->resolveCoverImg($weaponIdx, $skinField);

        $col['skins_preview'] = $this->buildSkinsPreviewFromData(
          $presetSkins,
          $presetItems,
          $lang,
          9
        );
      }
      unset($col);

      return ['collections' => $items];
    } catch (\Exception $e) {
      return ['collections' => []];
    }
  }

  public function activateDefaultCollection(string $steamid, int $presetId, int $serverId = 0): array
  {
    $serverId = $this->resolveServerId($serverId);
    $preset = $this->CollectionRepository->getPreset($presetId);
    if (!$preset) return ['success' => false, 'error' => 'not_found'];

    $player = $this->PluginRepo->getPlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];

    $limit    = $this->getCollectionLimit($steamid);
    if ($this->CollectionRepository->countCollections($steamid) >= $limit) {
      return ['success' => false, 'error' => 'limit_reached'];
    }

    $active = $this->CollectionRepository->getActiveCollection($steamid);
    if ($active) {
      $this->snapshotPlayerToCollection($steamid, (int)$active['id'], $serverId);
    }

    $newName = mb_substr(trim($preset['name'] ?? ''), 0, 32) ?: 'Collection';
    $newId   = $this->CollectionRepository->createCollection($steamid, $newName);

    $presetSkins = $this->CollectionRepository->getPresetSkins($presetId);
    $presetItems = $this->CollectionRepository->getPresetItems($presetId);

    if ($presetSkins) $this->CollectionRepository->bulkInsertCollectionSkins($newId, $presetSkins);
    if ($presetItems) $this->CollectionRepository->bulkInsertCollectionItems($newId, $presetItems);

    $this->PluginRepo->deleteAllSkins($steamid);
    $this->PluginRepo->deleteAllItems($steamid);

    $skinsForServer = array_values(array_filter($presetSkins, fn($s) => (int)$s['server_id'] === $serverId));
    if ($skinsForServer) $this->PluginRepo->bulkInsertSkins($steamid, $skinsForServer);

    $itemsForServer = array_values(array_filter($presetItems, fn($it) => (int)$it['server_id'] === $serverId));
    if ($itemsForServer) $this->PluginRepo->bulkInsertItems($steamid, $itemsForServer);

    $this->CollectionRepository->setActiveCollection($steamid, $newId);
    return ['success' => true];
  }

  public function setDefault(string $steamid, int $collectionId, bool $isDefault): array
  {
    $col = $this->CollectionRepository->getCollection($collectionId);
    if (!$col) return ['success' => false, 'error' => 'not_found'];

    $this->CollectionRepository->setCollectionDefault($collectionId, $isDefault);
    return ['success' => true, 'is_default' => (int)$isDefault];
  }

  public function copyAsDefault(int $sourceId, string $customName, int $serverId = 0): array
  {
    $serverId = $this->resolveServerId($serverId);
    $col = $this->CollectionRepository->getCollection($sourceId);
    if (!$col) return ['success' => false, 'error' => 'not_found'];

    $newName  = mb_substr(trim($customName) ?: trim($col['name'] ?? ''), 0, 32) ?: 'Collection';
    $maxOrder = $this->CollectionRepository->getPresetMaxSortOrder();
    $newId    = $this->CollectionRepository->createPreset($newName, $maxOrder + 1);

    $collSkins = $this->CollectionRepository->getCollectionSkins($sourceId);
    $skinsForServer = array_values(array_filter($collSkins ?: [], fn($s) => (int)$s['server_id'] === $serverId));
    if ($skinsForServer) $this->CollectionRepository->bulkInsertPresetSkins($newId, $skinsForServer);

    $collItems = $this->CollectionRepository->getCollectionItems($sourceId);
    $itemsForServer = array_values(array_filter($collItems ?: [], fn($it) => (int)$it['server_id'] === $serverId));
    if ($itemsForServer) $this->CollectionRepository->bulkInsertPresetItems($newId, $itemsForServer);

    return ['success' => true, 'id' => $newId];
  }

  public function getPresetDetail(string $lang, int $presetId): array
  {
    $preset = $this->CollectionRepository->getPreset($presetId);
    if (!$preset) return ['success' => false, 'error' => 'not_found'];

    return [
      'success' => true,
      'data' => [
        'id'             => (int)$preset['id'],
        'name'           => $preset['name'] ?? '',
        'skins_preview'  => $this->resolvePresetSkinsPreview($presetId, $lang, null),
        'player_name'    => '',
        'player_avatar'  => '',
        'steamid'        => '',
        'created_at'     => '',
        'updated_at'     => '',
        'installs_count' => 0,
        'likes_count'    => 0,
        'player_liked'   => false,
      ],
    ];
  }

  public function deletePreset(int $presetId): array
  {
    $preset = $this->CollectionRepository->getPreset($presetId);
    if (!$preset) return ['success' => false, 'error' => 'not_found'];
    $this->CollectionRepository->deletePreset($presetId);
    return ['success' => true];
  }

  public function reorderPresets(array $ids): array
  {
    foreach ($ids as $pos => $id) {
      $this->CollectionRepository->updatePresetSort((int)$id, (int)$pos);
    }
    return ['success' => true];
  }

  public function getPublicCollections(string $lang, string $steamid, int $page = 1, int $perPage = 10, string $order = 'likes', array $filters = []): array
  {
    $player = $this->PluginRepo->getPlayer($steamid);
    $mySteamid = $player ? $steamid : '';

    $filters['my_steamid'] = $mySteamid;

    $offset  = ($page - 1) * $perPage;
    $items   = $this->CollectionRepository->getPublicCollections($perPage, $offset, $order, $filters) ?: [];
    $total   = $this->CollectionRepository->countPublicCollections($filters);

    if (empty($items)) {
      return [
        'collections' => [],
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'pages'       => (int)ceil($total / $perPage),
      ];
    }

    $ids = array_map('intval', array_column($items, 'id'));
    $allSkins = $this->CollectionRepository->getCollectionsSkinsBatch($ids);
    $allItems = $this->CollectionRepository->getCollectionsItemsBatch($ids);

    foreach ($items as &$col) {
      $cid = (int)$col['id'];
      $colSkins = $allSkins[$cid] ?? [];
      $colItems = $allItems[$cid] ?? [];

      $this->cleanOrphanKnivesAndGlovesForCollection($cid, $colItems, $colSkins);

      $knifeIdx = 0;
      foreach ($colItems as $item) {
        if ((int)($item['knife'] ?? 0) > 0) {
          $knifeIdx = (int)$item['knife'];
          break;
        }
      }
      $firstSkin = $colSkins[0] ?? null;
      $coverWeaponIdx = $firstSkin ? (int)$firstSkin['weapon_index'] : null;
      $coverSkinField = $firstSkin ? ($firstSkin['skin'] ?? null) : null;

      $colSteamid = $col['steamid'] ?? '';

      $col = [
        'id'             => $cid,
        'name'           => $col['name'] ?? '',
        'installs_count' => (int)($col['installs_count'] ?? 0),
        'likes_count'    => (int)($col['likes_count'] ?? 0),
        'skins_count'    => $this->computeSkinsCount($colSkins, $colItems),
        'player_liked'   => !empty($col['my_liked']),
        'cover_img'      => $knifeIdx > 0
          ? $this->resolveCoverImg($knifeIdx, $this->findSkinByWeaponIndex($colSkins, $knifeIdx))
          : $this->resolveCoverImg($coverWeaponIdx, $coverSkinField),
        'skins_preview'  => $this->buildSkinsPreviewFromData($colSkins, $colItems, $lang),
        'steamid'        => $colSteamid,
        'player_name'    => $this->General->checkName($colSteamid),
        'player_avatar'  => $this->General->getAvatar($colSteamid, 3),
        'checked_avatar' => $this->General->checkAvatar($colSteamid),
      ];
    }

    return [
      'collections' => $items,
      'total'       => $total,
      'page'        => $page,
      'per_page'    => $perPage,
      'pages'       => (int)ceil($total / $perPage),
    ];
  }

  public function getCollectionDetail(string $lang, string $steamid, int $collectionId): array
  {
    $col = $this->CollectionRepository->getCollection($collectionId);

    if (!$col || (!$col['is_public'] && !$col['is_default'])) return ['success' => false, 'error' => 'not_found'];

    $colSteamid = $col['steamid'] ?? '';

    $currentPlayer = !empty($steamid) ? $this->PluginRepo->getPlayer($steamid) : null;
    $playerLiked = $currentPlayer && (bool)$this->CollectionRepository->getLike((int)$col['id'], $steamid);

    return [
      'success' => true,
      'data' => [
        'id'             => (int)$col['id'],
        'name'           => $col['name'] ?? '',
        'skins_preview'  => $this->resolveSkinsPreview((int)$col['id'], $lang, null),
        'player_name'    => $this->General->checkName($colSteamid),
        'player_avatar'  => $this->General->getAvatar($colSteamid, 3),
        'checked_avatar' => $this->General->checkAvatar($colSteamid),
        'steamid'        => $colSteamid,
        'created_at'     => (int)($col['created_at'] ?? 0),
        'updated_at'     => (int)($col['updated_at'] ?? 0),
        'published_at'   => (int)($col['published_at'] ?? 0),
        'installs_count' => (int)($col['installs_count'] ?? 0),
        'likes_count'    => (int)($col['likes_count'] ?? 0),
        'player_liked'   => $playerLiked,
      ],
    ];
  }

  private function resolveSkinsPreview(int $collectionId, string $lang, ?int $limit = 9): array
  {
    $skins = $this->CollectionRepository->getCollectionSkins($collectionId);
    $items = $this->CollectionRepository->getCollectionItems($collectionId);
    $this->cleanOrphanKnivesAndGlovesForCollection($collectionId, $items ?: [], $skins);
    return $this->buildSkinsPreviewFromData($skins ?: [], $items ?: [], $lang, $limit);
  }

  private function resolvePresetSkinsPreview(int $presetId, string $lang, ?int $limit = 9): array
  {
    $skins = $this->CollectionRepository->getPresetSkins($presetId);
    $items = $this->CollectionRepository->getPresetItems($presetId);
    $this->cleanOrphanKnivesAndGlovesForCollection($presetId, $items, $skins);
    return $this->buildSkinsPreviewFromData($skins, $items, $lang, $limit);
  }

  private function buildSkinsPreviewFromData(array $skins, array $items, string $lang, ?int $limit = 9): array
  {
    if (!$skins && !$items) return [];

    $stickersCache  = $this->SkinRepository->getStickers($lang);
    $keychainsCache = $this->SkinRepository->getKeychains($lang);

    $placeholders = $this->SkinRepository->getPlaceholders();
    $orderMap = [];
    foreach ($placeholders as $pos => $ph) {
      $phId = (int)($ph['id'] ?? 0);
      if ($phId > 0) $orderMap[$phId] = $pos;
    }
    $fieldOrder = ['knife' => 0, 'glove' => 1, 'agent_ct' => 37, 'agent_t' => 38, 'music' => 39, 'coin' => 40];
    $sideWeight = [0 => 0, 2 => 1, 1 => 2];

    $result = [];

    if ($skins) {
      $grouped = [];
      foreach ($skins as $s) {
        $wi = (int)$s['weapon_index'];
        $skinVal = $s['skin'] ?? '0;0;0.0';
        if (!isset($grouped[$wi])) {
          $grouped[$wi] = ['teams' => [], 'skins' => [], 'data' => []];
        }
        $tm = (int)$s['team'];
        $grouped[$wi]['teams'][$tm] = $tm;
        $grouped[$wi]['skins'][$tm] = $skinVal;
        $grouped[$wi]['data'][$tm] = $s;
      }

      foreach ($grouped as $wi => $entry) {
        $teams = array_values($entry['teams']);
        $skinValues = array_values(array_unique($entry['skins']));

        $weaponType = $this->SkinRepository->getById($lang, $wi)['type'] ?? '';
        $wiOrder = $orderMap[$wi] ?? null;
        if ($wiOrder === null) {
          if ($weaponType === 'Knife') $wiOrder = -2;
          elseif ($weaponType === 'Gloves') $wiOrder = -1;
          else $wiOrder = 999;
        }

        if (count($skinValues) > 1 && count($teams) > 1) {
          foreach ($teams as $tm) {
            $s = $entry['data'][$tm];
            $preview = $this->buildPreviewEntry($wi, $s, $tm, $stickersCache, $keychainsCache, $lang);
            $preview['_order'] = $wiOrder;
            $preview['_side'] = $sideWeight[$tm] ?? 1;
            $result[] = $preview;
          }
        } else {
          $team = count($teams) >= 2 ? 2 : $teams[0];
          $s = $entry['data'][$teams[0]];
          $preview = $this->buildPreviewEntry($wi, $s, $team, $stickersCache, $keychainsCache, $lang);
          $preview['_order'] = $wiOrder;
          $preview['_side'] = $sideWeight[$team] ?? 1;
          $result[] = $preview;
        }
      }
    }

    if ($items) {
      $agentsCache       = $this->SkinRepository->getAgents($lang);
      $musicCache        = $this->SkinRepository->getMusic($lang);
      $collectiblesCache = $this->SkinRepository->getCollectibles($lang);

      $itemFields = ['knife', 'glove', 'agent', 'music', 'coin'];
      $handledWi = [];

      foreach ($skins ?? [] as $s) {
        $handledWi[(int)$s['weapon_index']] = true;
      }

      $itemGrouped = [];
      foreach ($items as $row) {
        $tm = (int)$row['team'];
        foreach ($itemFields as $field) {
          $wi = (int)($row[$field] ?? 0);
          if ($wi <= 0) continue;
          $key = $field . ':' . $wi;
          if (!isset($itemGrouped[$key])) {
            $itemGrouped[$key] = ['field' => $field, 'wi' => $wi, 'teams' => []];
          }
          $itemGrouped[$key]['teams'][] = $tm;
        }
      }

      foreach ($itemGrouped as $entry) {
        $field = $entry['field'];
        $wi    = $entry['wi'];
        $teams = array_unique($entry['teams']);
        sort($teams);

        if (in_array($field, ['knife', 'glove'], true) && isset($handledWi[$wi])) continue;

        $team = count($teams) >= 2 ? 2 : $teams[0];

        $img = '';
        $rarity = '';
        $skinName = '';
        $weaponName = '';
        $itemOrderField = $field;

        if ($field === 'agent') {
          $data = $agentsCache[(string)$wi] ?? null;
          if ($data) {
            $skinName = $data['name'] ?? '';
            $img = $data['image'] ?? '';
            $rarity = $data['id_rarity'] ?? '';
          }
          $itemOrderField = $team === 1 ? 'agent_ct' : 'agent_t';
        } elseif ($field === 'music') {
          $data = $musicCache[(string)$wi] ?? null;
          if ($data) {
            $skinName = $data['name'] ?? '';
            $img = $data['image'] ?? '';
            $rarity = $data['id_rarity'] ?? '';
          }
        } elseif ($field === 'coin') {
          $data = $collectiblesCache[(string)$wi] ?? null;
          if ($data) {
            $skinName = $data['name'] ?? '';
            $img = $data['image'] ?? '';
            $rarity = $data['id_rarity'] ?? '';
          }
        } else {
          $weaponData = $this->SkinRepository->getById($lang, $wi);
          if ($weaponData) {
            $img = $weaponData['img'] ?? '';
            $weaponName = $weaponData['name'] ?? '';
          }
        }

        if (!$img && !$skinName) continue;

        [$weaponName, $skinName] = $this->formatNames($weaponName, $skinName);

        $result[] = [
          'img'             => $img,
          'rarity'          => $rarity,
          'name'            => $skinName,
          'weapon_name'     => $weaponName,
          'team'            => $team,
          'float_label'     => '',
          'pattern'         => 0,
          'stattrack'       => 0,
          'stattrack_count' => 0,
          'tag'             => '',
          'stickers'        => [],
          'keychain'        => null,
          '_order'          => $fieldOrder[$itemOrderField] ?? 999,
          '_side'           => $sideWeight[$team] ?? 1,
        ];
      }
    }

    usort($result, function ($a, $b) {
      $cmp = ($a['_order'] ?? 999) - ($b['_order'] ?? 999);
      return $cmp !== 0 ? $cmp : (($a['_side'] ?? 1) - ($b['_side'] ?? 1));
    });
    foreach ($result as &$r2) {
      unset($r2['_order'], $r2['_side']);
    }

    return $limit ? array_slice($result, 0, $limit) : $result;
  }

  private function buildPreviewEntry(int $wi, array $s, int $team, array $stickersCache, array $keychainsCache, string $lang): array
  {
    $parts   = explode(';', $s['skin'] ?? '0;0;0.0');
    $skinId  = (int)($parts[0] ?? 0);
    $pattern = (int)($parts[1] ?? 0);
    $float   = (float)($parts[2] ?? 0.0);

    $weaponData = $this->SkinRepository->getById($lang, $wi);
    $img = '';
    $rarity = '';
    $skinName = '';
    $weaponName = '';

    if ($weaponData) {
      $img        = $weaponData['img'] ?? '';
      $weaponName = $weaponData['name'] ?? '';
      if ($skinId > 0 && !empty($weaponData['skins'])) {
        foreach ($weaponData['skins'] as $sk) {
          if ((int)$sk['id_skin'] === $skinId) {
            $img      = $sk['image'] ?? $img;
            $rarity   = $sk['id_rarity'] ?? '';
            $skinName = $sk['name'] ?? '';
            break;
          }
        }
      }
    }

    [$weaponName, $skinName] = $this->formatNames($weaponName, $skinName);

    $stickerIds = explode(';', $s['stickers'] ?? '0;0;0;0');
    $stickers = [];
    foreach ($stickerIds as $sid) {
      $sid = trim($sid);
      if ($sid !== '' && $sid !== '0' && isset($stickersCache[$sid])) {
        $stickers[] = ['image' => $stickersCache[$sid]['image'] ?? '', 'name' => $stickersCache[$sid]['name'] ?? ''];
      }
    }

    $keychainParts = explode(';', $s['keychain'] ?? '');
    $keychainId    = trim($keychainParts[0] ?? '');
    $keychain      = null;
    if ($keychainId !== '' && $keychainId !== '0' && isset($keychainsCache[$keychainId])) {
      $keychain = ['image' => $keychainsCache[$keychainId]['image'] ?? '', 'name' => $keychainsCache[$keychainId]['name'] ?? ''];
    }

    return [
      'img'             => $img,
      'rarity'          => $rarity,
      'name'            => $skinName,
      'weapon_name'     => $weaponName,
      'team'            => $team,
      'float_label'     => $float > 0 ? $this->floatLabel($float) . ' • ' . number_format($float, 4) : '',
      'pattern'         => $pattern,
      'stattrack'       => (int)($s['stattrack'] ?? 0),
      'stattrack_count' => (int)($s['stattrack_count'] ?? 0),
      'tag'             => $s['tag'] ?? '',
      'stickers'        => $stickers ?: [],
      'keychain'        => $keychain,
    ];
  }

  private function formatNames(string $weaponName, string $skinName): array
  {
    if (mb_strpos($skinName, ' | ') !== false) {
      [$weaponName, $skinName] = explode(' | ', $skinName, 2);
    }
    $star = '';
    if (mb_strpos($weaponName, '★') !== false) {
      $star = '★ ';
      $weaponName = trim(str_replace('★', '', $weaponName));
    }
    return [$star . $weaponName, $skinName];
  }

  private function floatLabel(float $float): string
  {
    if ($float <= 0.0699) return 'FN';
    if ($float <= 0.1499) return 'MW';
    if ($float <= 0.3799) return 'FT';
    if ($float <= 0.4499) return 'WW';
    return 'BS';
  }

  private function cleanOrphanKnivesAndGlovesForCollection(int $collectionId, array $items, ?array &$rawSkins): void
  {
    if (!$rawSkins) return;

    $serverId = $this->resolveServerId();

    $categories = $this->CategoryRepository->getCategories();
    $knifeIds   = array_flip(array_column($categories['knives']['list'] ?? [], 'id'));
    $gloveIds   = array_flip(array_column($categories['gloves']['list'] ?? [], 'id'));

    if (empty($knifeIds) && empty($gloveIds)) return;

    $allowedKnives = [];
    $allowedGloves = [];
    foreach ($items as $item) {
      $team = (int)$item['team'];
      $knifeWi = (int)($item['knife'] ?? 0);
      $gloveWi = (int)($item['glove'] ?? 0);
      if ($knifeWi > 0) $allowedKnives[$team] = $knifeWi;
      if ($gloveWi > 0) $allowedGloves[$team] = $gloveWi;
    }

    $toDelete = [];
    $deleteKeys = [];
    foreach ($rawSkins as $skin) {
      $wi       = (int)$skin['weapon_index'];
      $team     = (int)$skin['team'];
      $skinServerId = (int)$skin['server_id'];

      if ($skinServerId !== $serverId) continue;

      $isKnife = isset($knifeIds[$wi]);
      $isGlove = isset($gloveIds[$wi]);

      if (!$isKnife && !$isGlove) continue;

      $allowedWi = $isKnife ? ($allowedKnives[$team] ?? 0) : ($allowedGloves[$team] ?? 0);
      if ($allowedWi !== $wi) {
        $key = $serverId . ':' . $team . ':' . $wi;
        if (isset($deleteKeys[$key])) continue;
        $deleteKeys[$key] = true;
        $toDelete[] = ['server_id' => $serverId, 'team' => $team, 'weapon_index' => $wi];
      }
    }

    if (!empty($toDelete)) {
      $this->CollectionRepository->deleteCollectionSkinsBatchByServer($collectionId, $toDelete);
      $rawSkins = array_values(array_filter($rawSkins, function ($skin) use ($deleteKeys) {
        $key = (int)$skin['server_id'] . ':' . (int)$skin['team'] . ':' . (int)$skin['weapon_index'];
        return !isset($deleteKeys[$key]);
      }));
    }
  }
}
