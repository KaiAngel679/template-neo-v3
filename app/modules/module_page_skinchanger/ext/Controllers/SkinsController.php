<?php

namespace app\modules\module_page_skinchanger\ext\Controllers;

use app\modules\module_page_skinchanger\ext\Services\{
  SkinsService
};

class SkinsController
{
  public $SkinsService;

  public function __construct($Db, $Translate, $General)
  {
    $this->SkinsService = new SkinsService($Db, $Translate);
  }

  public function getAll()
  {
    return $this->SkinsService->getAll();
  }

  public function getById(string $lang, int $id)
  {
    return $this->SkinsService->getById($lang, $id);
  }

  public function getByIdSorted(string $lang, int $id)
  {
    return $this->SkinsService->getByIdSorted($lang, $id);
  }

  public function getStickers(string $lang)
  {
    return $this->SkinsService->getStickers($lang);
  }

  public function getKeychains(string $lang)
  {
    return $this->SkinsService->getKeychains($lang);
  }

  public function getAgents(string $lang)
  {
    return $this->SkinsService->getAgents($lang);
  }

  public function getMusic(string $lang)
  {
    return $this->SkinsService->getMusic($lang);
  }

  public function getCoins(string $lang)
  {
    return $this->SkinsService->getCoins($lang);
  }

  public function getCollections(string $lang)
  {
    return $this->SkinsService->getCollections($lang);
  }

  public function searchSkins(string $query): array
  {
    return $this->SkinsService->searchSkins($query);
  }

  public function getPlaceholders()
  {
    return $this->SkinsService->getPlaceholders();
  }

  public function getPlayerSkins(string $lang, string $steamid)
  {
    return $this->SkinsService->getPlayerSkins($lang, $steamid);
  }

  public function resetAll(string $steamid)
  {
    return $this->SkinsService->resetAll($steamid);
  }

  public function assignItem(string $steamid, string $team, string $field, int $weaponIndex)
  {
    return $this->SkinsService->assignItem($steamid, $team, $field, $weaponIndex);
  }

  public function removeItem(string $steamid, string $team, string $field)
  {
    return $this->SkinsService->removeItem($steamid, $team, $field);
  }

  public function setSkinForSide(string $lang, string $steamid, string $side, int $weaponIndex, int $skinId)
  {
    return $this->SkinsService->setSkinForSide($lang, $steamid, $side, $weaponIndex, $skinId);
  }

  public function setSkin(string $steamid, string $team, string $field, int $weaponIndex, int $skinId)
  {
    return $this->SkinsService->setSkin($steamid, $team, $weaponIndex, $skinId);
  }

  public function resetSkin(string $steamid, string $team, string $field, int $weaponIndex)
  {
    return $this->SkinsService->resetSkin($steamid, $team, $weaponIndex);
  }

  public function resetSkins(string $steamid, array $items)
  {
    return $this->SkinsService->resetSkins($steamid, $items);
  }

  public function updateSkinSettings(string $steamid, string $team, int $weaponIndex, float $float, string $pattern, bool $stattrack, int $stattrackCount, string $tag, array $stickers = null, int $keychainId = null)
  {
    return $this->SkinsService->updateSkinSettings($steamid, $team, $weaponIndex, $float, $pattern, $stattrack, $stattrackCount, $tag, $stickers, $keychainId);
  }

  public function isKnifeOrGlove(int $weaponIndex): bool
  {
    return $this->SkinsService->isKnifeOrGlove($weaponIndex);
  }
}
