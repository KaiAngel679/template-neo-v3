<?php

namespace app\modules\module_page_skinchanger\ext\Controllers;

use app\modules\module_page_skinchanger\ext\Services\{
  CollectionService
};

class CollectionController
{
  public $CollectionService;

  public function __construct(object $Db, object $Translate, object $General)
  {
    $this->CollectionService = new CollectionService($Translate, $Db, $General);
  }

  public function getSettings(): array
  {
    return $this->CollectionService->getSettings();
  }

  public function saveSettings(array $settings): bool
  {
    return $this->CollectionService->saveSettings($settings);
  }

  public function hasVipAccess(string $steamid): bool
  {
    return $this->CollectionService->hasVipAccess($steamid);
  }

  public function getPlayerCollections(string $steamid): array
  {
    return $this->CollectionService->getPlayerCollections($steamid);
  }

  public function createCollection(string $steamid, string $name, bool $isPublic = false): array
  {
    return $this->CollectionService->createCollection($steamid, $name, $isPublic);
  }

  public function createMainCollection(string $steamid): array
  {
    return $this->CollectionService->createMainCollection($steamid);
  }

  public function renameCollection(string $steamid, int $collectionId, string $name): array
  {
    return $this->CollectionService->renameCollection($steamid, $collectionId, $name);
  }

  public function deleteCollection(string $steamid, int $collectionId): array
  {
    return $this->CollectionService->deleteCollection($steamid, $collectionId);
  }

  public function activateCollection(string $steamid, int $collectionId): array
  {
    return $this->CollectionService->activateCollection($steamid, $collectionId);
  }

  public function installCollection(string $steamid, int $collectionId, string $customName = ''): array
  {
    return $this->CollectionService->installCollection($steamid, $collectionId, $customName);
  }

  public function saveSnapshot(string $steamid, int $collectionId): array
  {
    return $this->CollectionService->saveSnapshot($steamid, $collectionId);
  }

  public function getCollectionSkinsCount(int $collectionId): int
  {
    return $this->CollectionService->getCollectionSkinsCount($collectionId);
  }

  public function setPublic(string $steamid, int $collectionId, bool $isPublic): array
  {
    return $this->CollectionService->setPublic($steamid, $collectionId, $isPublic);
  }

  public function toggleLike(string $steamid, int $collectionId): array
  {
    return $this->CollectionService->toggleLike($steamid, $collectionId);
  }

  public function getPublicCollections(string $lang, string $steamid, int $page = 1, int $perPage = 10, string $order = 'likes', array $filters = []): array
  {
    return $this->CollectionService->getPublicCollections($lang, $steamid, $page, $perPage, $order, $filters);
  }

  public function getDefaultCollections(string $lang, string $steamid): array
  {
    return $this->CollectionService->getDefaultCollections($lang, $steamid);
  }

  public function activateDefaultCollection(string $steamid, int $presetId): array
  {
    return $this->CollectionService->activateDefaultCollection($steamid, $presetId);
  }

  public function getPresetDetail(string $lang, int $presetId): array
  {
    return $this->CollectionService->getPresetDetail($lang, $presetId);
  }

  public function deletePreset(int $presetId): array
  {
    return $this->CollectionService->deletePreset($presetId);
  }

  public function reorderPresets(array $ids): array
  {
    return $this->CollectionService->reorderPresets($ids);
  }

  public function getCollectionDetail(string $lang, string $steamid, int $collectionId): array
  {
    return $this->CollectionService->getCollectionDetail($lang, $steamid, $collectionId);
  }

  public function setDefault(string $steamid, int $collectionId, bool $isDefault): array
  {
    return $this->CollectionService->setDefault($steamid, $collectionId, $isDefault);
  }

  public function copyAsDefault(int $collectionId, string $name): array
  {
    return $this->CollectionService->copyAsDefault($collectionId, $name);
  }
}
