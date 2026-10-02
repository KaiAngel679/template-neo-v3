<?php

namespace app\modules\module_page_skinchanger\ext\Controllers;

use app\modules\module_page_skinchanger\ext\Controllers\{
  CategoryController,
  SkinsController,
  CollectionController
};

use app\modules\module_page_skinchanger\ext\Services\{
  CacheService
};

class SkinchangerController
{
  public object $CacheService;
  public object $SkinsController;
  public object $CategoryController;
  public object $CollectionController;

  public function __construct(object $Db, object $Translate, object $General)
  {
    $this->CacheService = new CacheService($Translate);
    $this->SkinsController = new SkinsController($Db, $Translate, $General);
    $this->CategoryController = new CategoryController($Db, $Translate, $General);
    $this->CollectionController = new CollectionController($Db, $Translate, $General);
  }

  public function cacheUpdate($name = '')
  {
    return $this->CacheService->cacheUpdate($name);
  }

  public function getSkinByIdSorted(string $lang, int $id)
  {
    return $this->SkinsController->getByIdSorted($lang, $id);
  }

  public function getCategories(string $lang)
  {
    return $this->CategoryController->getCategories($lang);
  }

  public function getStickers(string $lang)
  {
    return $this->SkinsController->getStickers($lang);
  }

  public function getKeychains(string $lang)
  {
    return $this->SkinsController->getKeychains($lang);
  }

  public function getAgents(string $lang)
  {
    return $this->SkinsController->getAgents($lang);
  }

  public function getMusic(string $lang)
  {
    return $this->SkinsController->getMusic($lang);
  }

  public function getCoins(string $lang)
  {
    return $this->SkinsController->getCoins($lang);
  }

  public function getCollections(string $lang)
  {
    return $this->SkinsController->getCollections($lang);
  }

  public function searchSkins(string $query): array
  {
    return $this->SkinsController->searchSkins($query);
  }

  public function getPlaceholders()
  {
    return $this->SkinsController->getPlaceholders();
  }

  public function getPlayerSkins(string $lang)
  {
    return $this->SkinsController->getPlayerSkins($lang, $_SESSION['steamid']);
  }

  public function resetAll()
  {
    return $this->SkinsController->resetAll($_SESSION['steamid']);
  }

  public function setSkinForSide(string $lang, string $side, int $weaponIndex, int $skinId)
  {
    return $this->SkinsController->setSkinForSide($lang, $_SESSION['steamid'], $side, $weaponIndex, $skinId);
  }

  public function setSkin(string $team, int $weaponIndex, int $skinId)
  {
    return $this->SkinsController->setSkin($_SESSION['steamid'], $team, $weaponIndex, $skinId);
  }

  public function resetSkin(string $team, int $weaponIndex)
  {
    return $this->SkinsController->resetSkin($_SESSION['steamid'], $team, $weaponIndex);
  }

  public function resetSkins(array $items)
  {
    return $this->SkinsController->resetSkins($_SESSION['steamid'], $items);
  }

  public function updateSkinSettings(string $team, int $weaponIndex, float $float, string $pattern, bool $stattrack, int $stattrackCount, string $tag, array $stickers = null, int $keychainId = null)
  {
    return $this->SkinsController->updateSkinSettings($_SESSION['steamid'], $team, $weaponIndex, $float, $pattern, $stattrack, $stattrackCount, $tag, $stickers, $keychainId);
  }

  public function isKnifeOrGlove(int $weaponIndex): bool
  {
    return $this->SkinsController->isKnifeOrGlove($weaponIndex);
  }

  public function getCollectionSettings(): array
  {
    return $this->CollectionController->getSettings();
  }

  public function saveCollectionSettings(array $settings): bool
  {
    return $this->CollectionController->saveSettings($settings);
  }

  public function hasVipAccess(): bool
  {
    return $this->CollectionController->hasVipAccess($_SESSION['steamid'] ?? '');
  }

  public function getPlayerCollections(): array
  {
    $steamid = $_SESSION['steamid'];
    $data    = $this->CollectionController->getPlayerCollections($steamid);
    return $data;
  }

  public function createCollection(string $name, bool $isPublic = false): array
  {
    return $this->CollectionController->createCollection($_SESSION['steamid'], $name, $isPublic);
  }

  public function createMainCollection(): array
  {
    return $this->CollectionController->createMainCollection($_SESSION['steamid']);
  }

  public function renameCollection(int $collectionId, string $name): array
  {
    return $this->CollectionController->renameCollection($_SESSION['steamid'], $collectionId, $name);
  }

  public function deleteCollection(int $collectionId): array
  {
    return $this->CollectionController->deleteCollection($_SESSION['steamid'], $collectionId);
  }

  public function activateCollection(int $collectionId): array
  {
    return $this->CollectionController->activateCollection($_SESSION['steamid'], $collectionId);
  }

  public function installCollection(int $collectionId, string $customName = ''): array
  {
    return $this->CollectionController->installCollection($_SESSION['steamid'], $collectionId, $customName);
  }

  public function saveSnapshot(int $collectionId): array
  {
    return $this->CollectionController->saveSnapshot($_SESSION['steamid'], $collectionId);
  }

  public function getCollectionSkinsCount(int $collectionId): int
  {
    return $this->CollectionController->getCollectionSkinsCount($collectionId);
  }

  public function setCollectionPublic(int $collectionId, bool $isPublic): array
  {
    return $this->CollectionController->setPublic($_SESSION['steamid'], $collectionId, $isPublic);
  }

  public function toggleLike(int $collectionId): array
  {
    return $this->CollectionController->toggleLike($_SESSION['steamid'], $collectionId);
  }

  public function getPublicCollections(string $lang, int $page = 1, int $perPage = 10, string $order = 'likes', array $filters = []): array
  {
    return $this->CollectionController->getPublicCollections($lang, $_SESSION['steamid'], $page, $perPage, $order, $filters);
  }

  public function getCollectionDetail(string $lang, int $collectionId): array
  {
    return $this->CollectionController->getCollectionDetail($lang, $_SESSION['steamid'], $collectionId);
  }

  public function getInitData(string $lang): array
  {
    $steamid = $_SESSION['steamid'];
    return [
      'skins'               => $this->SkinsController->getPlayerSkins($lang, $steamid) ?: [],
      'collections'         => $this->CollectionController->getPlayerCollections($steamid),
      'categories'          => $this->CategoryController->getCategories($lang),
      'default_collections' => $this->CollectionController->getDefaultCollections($lang, $steamid),
    ];
  }

  public function getDefaultCollections(string $lang): array
  {
    return $this->CollectionController->getDefaultCollections($lang, $_SESSION['steamid']);
  }

  public function activateDefaultCollection(int $presetId): array
  {
    return $this->CollectionController->activateDefaultCollection($_SESSION['steamid'], $presetId);
  }

  public function getPresetDetail(string $lang, int $presetId): array
  {
    return $this->CollectionController->getPresetDetail($lang, $presetId);
  }

  public function deletePreset(int $presetId): array
  {
    return $this->CollectionController->deletePreset($presetId);
  }

  public function reorderPresets(array $ids): array
  {
    return $this->CollectionController->reorderPresets($ids);
  }

  public function setCollectionDefault(int $collectionId, bool $isDefault): array
  {
    return $this->CollectionController->setDefault($_SESSION['steamid'], $collectionId, $isDefault);
  }

  public function copyAsDefault(int $collectionId, string $name): array
  {
    return $this->CollectionController->copyAsDefault($collectionId, $name);
  }

  public function assignItem(string $team, string $field, int $weaponIndex)
  {
    return $this->SkinsController->assignItem($_SESSION['steamid'], $team, $field, $weaponIndex);
  }

  public function removeItem(string $team, string $field)
  {
    return $this->SkinsController->removeItem($_SESSION['steamid'], $team, $field);
  }
}
