<?php

namespace app\modules\module_page_skinchanger\ext\Services;

use app\modules\module_page_skinchanger\ext\Repositories\{
  SkinRepository,
  CategoryRepository
};
use app\modules\module_page_skinchanger\ext\Repositories\Plugins\{
  PluginFactory
};

class SkinsService
{
  protected $Db, $Translate, $languages, $SkinRepository, $PluginRepository, $CategoryRepository;
  public function __construct(object $Db, object $Translate)
  {
    $this->Db = $Db;
    $this->Translate = $Translate;
    $this->SkinRepository = new SkinRepository();
    $this->CategoryRepository = new CategoryRepository();
    $this->PluginRepository = PluginFactory::create($this->Db);
  }

  public function getAll(string $lang)
  {
    return $this->SkinRepository->getAll($lang);
  }

  public function getById(string $lang, int $id)
  {
    return $this->SkinRepository->getById($lang, $id);
  }

  public function getByIdSorted(string $lang, int $id)
  {
    return $this->SkinRepository->getByIdSorted($lang, $id);
  }

  public function getStickers(string $lang)
  {
    return $this->SkinRepository->getStickers($lang);
  }

  public function getKeychains(string $lang)
  {
    return $this->SkinRepository->getKeychains($lang);
  }

  public function getAgents(string $lang)
  {
    return $this->SkinRepository->getAgents($lang);
  }

  public function getMusic(string $lang)
  {
    return $this->SkinRepository->getMusic($lang);
  }

  public function getCoins(string $lang)
  {
    return $this->SkinRepository->getCollectibles($lang);
  }

  public function getCollections(string $lang)
  {
    return $this->SkinRepository->getCollections($lang);
  }

  public function searchSkins(string $lang, string $query): array
  {
    return $this->SkinRepository->searchSkins($lang, $query);
  }

  public function getPlaceholders()
  {
    $placeholders = $this->SkinRepository->getPlaceholders();
    foreach ($placeholders as &$placeholder) {
      $placeholder['name'] = (isset($placeholder['name'][0]) && $placeholder['name'][0] === '_' ? $this->Translate->get_translate_module_phrase('module_page_skinchanger', $placeholder['name']) : $placeholder['name']);
    }
    return $placeholders;
  }

  private function floatLabel(float $float): string
  {
    if ($float <= 0.0699) return 'FN';
    if ($float <= 0.1499) return 'MW';
    if ($float <= 0.3799) return 'FT';
    if ($float <= 0.4499) return 'WW';
    return 'BS';
  }

  private function resolveStickers(string $stickerIds, array $allStickers): array
  {
    $ids = explode(';', $stickerIds);
    while (count($ids) < 5) $ids[] = '0';
    $result = [];
    foreach ($ids as $id) {
      $id = trim($id);
      if ($id === '' || $id === '0') {
        $result[] = null;
      } elseif (isset($allStickers[$id]) && !empty($allStickers[$id]['image'])) {
        $result[] = ['id' => (int)$id, 'image' => $allStickers[$id]['image']];
      } else {
        $result[] = null;
      }
    }
    return $result;
  }

  private function resolveKeychain(string $keychainData, array $allKeychains): ?array
  {
    $parts = explode(';', $keychainData);
    $id = trim($parts[0] ?? '');
    if ($id === '' || $id === '0') return null;
    $image = $allKeychains[$id]['image'] ?? null;
    return $image ? ['id' => (int)$id, 'image' => $image] : null;
  }

  public function getPlayerSkins(string $lang, string $steamid)
  {
    $player = $this->PluginRepository->getPlayer($steamid);
    if (!$player) return null;

    $items = $this->PluginRepository->getItems($player['id']) ?: [];
    $rawSkins = $this->PluginRepository->getSkins($player['id']);
    if (!$items && !$rawSkins) return [];

    $this->cleanOrphanKnivesAndGloves($player['id'], $items, $rawSkins);

    $sideMap = ['0' => 't', '1' => 'ct'];
    $itemGroups = $this->buildItemGroups($items);
    $skinsMap = $this->buildSkinsMap($rawSkins);
    [$orderMap, $fieldOrder] = $this->buildOrderMap();

    $caches = [
      'stickers'     => $this->SkinRepository->getStickers($lang),
      'keychains'    => $this->SkinRepository->getKeychains($lang),
      'agents'       => $this->SkinRepository->getAgents($lang),
      'music'        => $this->SkinRepository->getMusic($lang),
      'collectibles' => $this->SkinRepository->getCollectibles($lang),
    ];

    $handledKeys = [];
    $result = $this->processItems($lang, $itemGroups, $skinsMap, $sideMap, $orderMap, $fieldOrder, $caches, $handledKeys);
    $result = array_merge($result, $this->processWeaponSkins($lang, $rawSkins, $handledKeys, $skinsMap, $sideMap, $orderMap, $fieldOrder, $caches));

    $sideWeight = ['t' => 0, 'both' => 1, 'ct' => 2];

    usort($result, function ($a, $b) use ($sideWeight) {
      $cmp = $a['_order'] - $b['_order'];
      if ($cmp !== 0) return $cmp;
      return ($sideWeight[$a['side']] ?? 1) - ($sideWeight[$b['side']] ?? 1);
    });
    foreach ($result as &$r) unset($r['_order']);

    return $result;
  }

  private function cleanOrphanKnivesAndGloves(int $playerId, array $items, ?array &$rawSkins): void
  {
    if (!$rawSkins) return;

    $serverId = $this->PluginRepository->getFirstServerId();

    [$knifeIds, $gloveIds] = $this->getKnifeGloveIds();

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
      $this->PluginRepository->deleteSkinsBatchByServer($playerId, $toDelete);
      $rawSkins = array_values(array_filter($rawSkins, function ($skin) use ($deleteKeys) {
        $key = (int)$skin['server_id'] . ':' . (int)$skin['team'] . ':' . (int)$skin['weapon_index'];
        return !isset($deleteKeys[$key]);
      }));
    }
  }

  private function buildItemGroups(array $items): array
  {
    $fields = ['knife', 'glove', 'agent', 'music', 'coin'];
    $groups = [];
    foreach ($items as $item) {
      $team = (string)$item['team'];
      foreach ($fields as $field) {
        $wi = (int)($item[$field] ?? 0);
        if ($wi > 0) {
          $gkey = $field . ':' . $wi;
          if (!isset($groups[$gkey])) $groups[$gkey] = ['field' => $field, 'wi' => $wi, 'teams' => []];
          $groups[$gkey]['teams'][] = $team;
        }
      }
    }
    return $groups;
  }

  private function buildSkinsMap(?array $rawSkins): array
  {
    $map = [];
    if ($rawSkins) {
      foreach ($rawSkins as $skin) {
        $map[$skin['team'] . ':' . $skin['weapon_index']] = $skin;
      }
    }
    return $map;
  }

  private function buildOrderMap(): array
  {
    $placeholders = $this->SkinRepository->getPlaceholders();
    $orderMap = [];
    foreach ($placeholders as $pos => $ph) {
      $phId = (int)($ph['id'] ?? 0);
      if ($phId > 0) $orderMap[$phId] = $pos;
    }
    $fieldOrder = ['knife' => 0, 'glove' => 1, 'music' => 39, 'coin' => 40];
    return [$orderMap, $fieldOrder];
  }

  private function parseSkinField(?array $skin): array
  {
    if (!$skin) return [0, 0, 0.0];
    $parts = explode(';', $skin['skin'] ?? '0;0;0.0');
    return [(int)($parts[0] ?? 0), (int)($parts[1] ?? 0), (float)($parts[2] ?? 0.0)];
  }

  private function resolveItemCacheData(string $lang, string $field, int $wi, int $skinId, ?array $skin, array $caches): array
  {
    $weaponName = '';
    $skinName = '';
    $img = '';
    $idRarity = '';
    $stickers = [];
    $keychain = null;

    if ($field === 'agent') {
      $data = $caches['agents'][(string)$wi] ?? null;
      if ($data) {
        $skinName = $data['name'] ?? '';
        $img = $data['image'] ?? '';
        $idRarity = $data['id_rarity'] ?? '';
      }
    } elseif ($field === 'music') {
      $data = $caches['music'][(string)$wi] ?? null;
      if ($data) {
        $skinName = $data['name'] ?? '';
        $img = $data['image'] ?? '';
        $idRarity = $data['id_rarity'] ?? '';
      }
    } elseif ($field === 'coin') {
      $data = $caches['collectibles'][(string)$wi] ?? null;
      if ($data) {
        $skinName = $data['name'] ?? '';
        $img = $data['image'] ?? '';
        $idRarity = $data['id_rarity'] ?? '';
      }
    } else {
      $weaponData = $this->SkinRepository->getById($lang, $wi);
      $skinData = $this->findSkinData($weaponData, $skinId);
      $weaponName = $weaponData['name'] ?? '';
      $skinName   = $skinData['name'] ?? '';
      $img        = $skinData['image'] ?? ($weaponData['img'] ?? '');
      $idRarity   = $skinData['id_rarity'] ?? '';
      $stickers   = $skin ? $this->resolveStickers($skin['stickers'] ?? '', $caches['stickers']) : [];
      $keychain   = $skin && !empty($skin['keychain']) ? $this->resolveKeychain($skin['keychain'], $caches['keychains']) : null;
    }

    return compact('weaponName', 'skinName', 'img', 'idRarity', 'stickers', 'keychain');
  }

  private function findSkinData(?array $weaponData, int $skinId): ?array
  {
    if (!$weaponData || empty($weaponData['skins']) || !$skinId) return null;
    foreach ($weaponData['skins'] as $s) {
      if ((int)$s['id_skin'] === $skinId) return $s;
    }
    return null;
  }

  private function buildEntry(?array $skin, int $wi, string $side, int $skinId, int $pattern, float $float, array $cached, int $order, ?string $nameId = null): array
  {
    $weaponName = $cached['weaponName'] ?? '';
    $skinNameRaw = $cached['skinName'] ?? '';
    $weaponNameFinal = $weaponName;
    $skinNameFinal = $skinNameRaw;

    if (mb_strpos($skinNameRaw, ' | ') !== false) {
      [$weaponNameFinal, $skinNameFinal] = explode(' | ', $skinNameRaw, 2);
    }

    $star = '';
    if (mb_strpos($weaponNameFinal, '★') !== false) {
      $star = '★';
      $weaponNameFinal = trim(str_replace('★', '', $weaponNameFinal));
    }
    $weaponNameFull = ($star ? $star . ' ' : '') . trim($weaponNameFinal);

    return [
      'id'              => $skin['id'] ?? null,
      'weapon_index'    => $wi,
      'name_id'         => $nameId,
      'side'            => $side,
      'stattrack'       => $skin ? (bool)(int)$skin['stattrack'] : false,
      'stattrack_count' => $skin ? (int)$skin['stattrack_count'] : 0,
      'float'           => $float,
      'float_label'     => $float > 0 ? $this->floatLabel($float) . ' • ' . number_format($float, 4) : '',
      'pattern'         => $pattern,
      'skin_id'         => $skinId,
      'weapon_name'     => $weaponNameFull,
      'skin_name'       => $skinNameFinal,
      'img'             => $cached['img'],
      'id_rarity'       => $cached['idRarity'],
      'stickers'        => $cached['stickers'],
      'keychain'        => $cached['keychain'],
      'tag'             => $skin['tag'] ?? '',
      '_order'          => $order,
    ];
  }

  private function processItems(string $lang, array $itemGroups, array $skinsMap, array $sideMap, array $orderMap, array $fieldOrder, array $caches, array &$handledKeys): array
  {
    $result = [];
    $skinFields = ['knife', 'glove'];
    $nameIdMap = ['knife' => 'knife', 'glove' => 'gloves', 'music' => 'record', 'coin' => 'coin'];

    foreach ($itemGroups as $g) {
      $field = $g['field'];
      $wi    = $g['wi'];
      $teams = array_unique($g['teams']);
      $hasSkinEntry = in_array($field, $skinFields, true);

      if ($hasSkinEntry) {
        foreach ($teams as $tm) $handledKeys[$tm . ':' . $wi] = true;
      }

      $order = $fieldOrder[$field] ?? $orderMap[$wi] ?? 999;

      if ($field === 'agent') {
        $nameId = null;
      } else {
        $nameId = $nameIdMap[$field] ?? null;
      }

      if ($hasSkinEntry && count($teams) > 1) {
        $skinA = $skinsMap[$teams[0] . ':' . $wi] ?? null;
        $skinB = $skinsMap[$teams[1] . ':' . $wi] ?? null;
        $valA = $skinA['skin'] ?? '';
        $valB = $skinB['skin'] ?? '';

        if ($valA !== $valB) {
          foreach ($teams as $tm) {
            $skin = $skinsMap[$tm . ':' . $wi] ?? null;
            [$skinId, $pattern, $float] = $this->parseSkinField($skin);
            $cached = $this->resolveItemCacheData($lang, $field, $wi, $skinId, $skin, $caches);
            $tmSide = $sideMap[$tm] ?? 'both';
            $result[] = $this->buildEntry($skin, $wi, $tmSide, $skinId, $pattern, $float, $cached, $order, $nameId);
          }
          continue;
        }
      }

      $side = (count($teams) > 1) ? 'both' : ($sideMap[$teams[0]] ?? 'both');
      $skin = $hasSkinEntry ? ($skinsMap[$teams[0] . ':' . $wi] ?? null) : null;
      [$skinId, $pattern, $float] = $this->parseSkinField($skin);
      $cached = $this->resolveItemCacheData($lang, $field, $wi, $skinId, $skin, $caches);

      if ($field === 'agent') {
        $order = ($side === 'ct' || (count($teams) === 1 && $teams[0] === '1')) ? 37 : 38;
        $nameId = ($side === 'ct' || (count($teams) === 1 && $teams[0] === '1')) ? 'agent_ct' : 'agent_t';
      }

      $result[] = $this->buildEntry($skin, $wi, $side, $skinId, $pattern, $float, $cached, $order, $nameId);
    }

    return $result;
  }

  private function processWeaponSkins(string $lang, ?array $rawSkins, array $handledKeys, array $skinsMap, array $sideMap, array $orderMap, array $fieldOrder, array $caches): array
  {
    if (!$rawSkins) return [];

    $skinGroups = [];
    foreach ($rawSkins as $skin) {
      $key = $skin['team'] . ':' . $skin['weapon_index'];
      if (isset($handledKeys[$key])) continue;
      $skinGroups[(int)$skin['weapon_index']][] = $skin;
    }

    $result = [];
    foreach ($skinGroups as $wi => $skins) {
      $weaponData = $this->SkinRepository->getById($lang, $wi);
      if (!$weaponData) continue;

      $byTeam = [];
      foreach ($skins as $s) $byTeam[$s['team']] = $s;

      $entries = [];
      if (count($byTeam) > 1 && ($byTeam[array_key_first($byTeam)]['skin'] ?? '') === ($byTeam[array_key_last($byTeam)]['skin'] ?? '')) {
        $entries[] = ['skin' => reset($byTeam), 'side' => 'both'];
      } else {
        foreach ($byTeam as $tm => $s) {
          $entries[] = ['skin' => $s, 'side' => $sideMap[(string)$tm] ?? 'both'];
        }
      }

      foreach ($entries as $entry) {
        $skin = $entry['skin'];
        [$skinId, $pattern, $float] = $this->parseSkinField($skin);
        $skinData = $this->findSkinData($weaponData, $skinId);

        $cached = [
          'weaponName' => $weaponData['name'] ?? '',
          'skinName'   => $skinData['name'] ?? '',
          'img'        => $skinData['image'] ?? ($weaponData['img'] ?? ''),
          'idRarity'   => $skinData['id_rarity'] ?? '',
          'stickers'   => $this->resolveStickers($skin['stickers'] ?? '', $caches['stickers']),
          'keychain'   => !empty($skin['keychain']) ? $this->resolveKeychain($skin['keychain'], $caches['keychains']) : null,
        ];

        $order = $orderMap[$wi] ?? null;
        if ($order === null) {
          $order = $this->resolveWeaponOrder($wi, $fieldOrder);
        }
        $result[] = $this->buildEntry($skin, $wi, $entry['side'], $skinId, $pattern, $float, $cached, $order);
      }
    }

    return $result;
  }

  private const KEYCHAIN_POSITIONS = [
    1 => [8.0, 0.0, 3.0],
    2 => [10.0, 0.0, 9.2],
    3 => [8.0, 0.0, 1.0],
    4 => [5.7, 0.0, 2.2],
    7 => [6.0, 0.0, 2.0],
    8 => [6.7, 5.0, 3.2],
    9 => [10.0, 0.0, 8.0],
    10 => [7.0, 0.0, 3.0],
    11 => [20.0, 0.0, 5.6],
    13 => [8.5, 1.0, 3.0],
    14 => [4.2, 0.0, 4.8],
    16 => [18.6, 7.0, 12.4],
    17 => [9.0, 0.0, 5.6],
    19 => [8.7, 0.0, 5.9],
    23 => [5.5, 0.0, 3.7],
    24 => [9.0, 0.0, 3.8],
    25 => [4.5, 0.0, 3.7],
    26 => [2.4, 0.0, 2.7],
    27 => [3.8, 0.0, 8.7],
    28 => [1.2, 0.0, 3.8],
    29 => [5.2, 0.0, 0.8],
    30 => [10.1, 5.0, 7.0],
    31 => [5.9, 5.0, 5.1],
    32 => [9.0, 0.0, 2.5],
    33 => [-3.0, 0.0, 2.5],
    34 => [9.0, 0.0, 5.6],
    35 => [6.5, 0.0, 3.7],
    36 => [8.0, 0.0, 1.0],
    38 => [20.0, 0.0, 12.2],
    39 => [7.5, 0.0, 4.0],
    40 => [10.0, 5.0, 3.8],
    60 => [6.0, 0.0, 4.5],
    61 => [7.0, 0.0, 4.0],
    63 => [6.8, 0.0, 4.7],
    64 => [9.0, 0.0, 4.0],
  ];

  private function resolveWeaponOrder(int $wi, array $fieldOrder): int
  {
    [$knifeIds, $gloveIds] = $this->getKnifeGloveIds();
    if (isset($knifeIds[$wi])) return $fieldOrder['knife'] ?? 0;
    if (isset($gloveIds[$wi])) return $fieldOrder['glove'] ?? 1;
    return 999;
  }

  private function resolvePlayer(string $steamid): ?array
  {
    return $this->PluginRepository->getPlayer($steamid);
  }

  private function resolvePlayerId(string $steamid)
  {
    return $this->PluginRepository->resolveIdentifier($steamid);
  }

  private function resolveServerId(int $serverId = 0): int
  {
    return $serverId > 0 ? $serverId : $this->PluginRepository->getFirstServerId();
  }

  private function ensureItem(int $playerId, int $serverId, int $team): void
  {
    $item = $this->PluginRepository->getItem($playerId, $serverId, $team);
    if (!$item) {
      $this->PluginRepository->insertItem($playerId, $serverId, $team);
    }
  }

  private function ensureSkin(int $playerId, int $serverId, int $team, int $weaponIndex): array
  {
    $existing = $this->PluginRepository->getSkin($playerId, $serverId, $team, $weaponIndex);
    if (!$existing) {
      $this->PluginRepository->insertSkinDefault($playerId, $serverId, $team, $weaponIndex);
      $existing = $this->PluginRepository->getSkin($playerId, $serverId, $team, $weaponIndex);
    }
    return $existing;
  }

  public function resetAll(string $steamid): array
  {
    $player = $this->resolvePlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];

    $this->PluginRepository->deleteAllSkins($player['id']);
    $this->PluginRepository->deleteAllItems($player['id']);
    return ['success' => true];
  }

  public function assignItem(string $steamid, int $team, string $field, int $weaponIndex, int $serverId = 0): array
  {
    $serverId = $this->resolveServerId($serverId);
    $player = $this->resolvePlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];

    $playerId = $this->resolvePlayerId($steamid);
    if (!$playerId) return ['success' => false, 'error' => 'player_not_found'];

    $allowed = ['knife', 'glove', 'agent', 'music', 'coin'];
    if (!in_array($field, $allowed, true)) return ['success' => false, 'error' => 'invalid_field'];
    if (!in_array($team, [0, 1], true)) return ['success' => false, 'error' => 'invalid_team'];

    $this->ensureItem($playerId, $serverId, $team);

    $this->PluginRepository->updateItemField($playerId, $serverId, $team, $field, $weaponIndex);
    return ['success' => true];
  }

  public function removeItem(string $steamid, int $team, string $field, int $serverId = 0): array
  {
    $serverId = $this->resolveServerId($serverId);
    $player = $this->resolvePlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];

    $playerId = $this->resolvePlayerId($steamid);
    if (!$playerId) return ['success' => false, 'error' => 'player_not_found'];

    $allowed = ['knife', 'glove', 'agent', 'music', 'coin'];
    if (!in_array($field, $allowed, true)) return ['success' => false, 'error' => 'invalid_field'];
    if (!in_array($team, [0, 1], true)) return ['success' => false, 'error' => 'invalid_team'];

    if (in_array($field, ['knife', 'glove'], true)) {
      $item = $this->PluginRepository->getItem($playerId, $serverId, $team);
      if ($item) {
        $wi = (int)($item[$field] ?? 0);
        if ($wi > 0) {
          $this->PluginRepository->deleteSkin($playerId, $serverId, $team, $wi);
        }
      }
    }

    $this->ensureItem($playerId, $serverId, $team);
    $this->PluginRepository->updateItemField($playerId, $serverId, $team, $field, 0);
    return ['success' => true];
  }

  public function setSkinForSide(string $lang, string $steamid, string $side, int $weaponIndex, int $skinId, int $serverId = 0): array
  {
    $serverId = $this->resolveServerId($serverId);
    $teams = $side === 't' ? [0] : ($side === 'ct' ? [1] : [0, 1]);
    foreach ($teams as $team) {
      $result = $this->setSkin($lang, $steamid, $team, $weaponIndex, $skinId, $serverId);
      if (!$result['success']) return $result;
    }

    if ($side !== 'both') {
      $player = $this->resolvePlayer($steamid);
      if ($player) {
        $oppositeTeam = $side === 't' ? 1 : 0;
        $oppSkin = $this->PluginRepository->getSkin($player['id'], $serverId, $oppositeTeam, $weaponIndex);
        if ($oppSkin) {
          $parts = explode(';', $oppSkin['skin'] ?? '0;0;0.0');
          if ((int)($parts[0] ?? 0) === $skinId) {
            $this->resetSkin($steamid, $oppositeTeam, $weaponIndex, $serverId);
          }
        }
      }
    }

    return ['success' => true];
  }

  public function setSkin(string $lang, string $steamid, int $team, int $weaponIndex, int $skinId, int $serverId = 0): array
  {
    $serverId = $this->resolveServerId($serverId);
    $player = $this->resolvePlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];
    if (!in_array($team, [0, 1], true)) return ['success' => false, 'error' => 'invalid_team'];
    if ($weaponIndex <= 0) return ['success' => false, 'error' => 'invalid_weapon_index'];

    $weaponData = $this->SkinRepository->getById($lang, $weaponIndex);
    if (!$weaponData) return ['success' => false, 'error' => 'weapon_not_found'];
    if ($skinId > 0 && !$this->findSkinData($weaponData, $skinId)) return ['success' => false, 'error' => 'skin_not_found'];

    [$knifeIds, $gloveIds] = $this->getKnifeGloveIds();
    $isRealKnife = isset($knifeIds[$weaponIndex]);
    $isRealGlove = isset($gloveIds[$weaponIndex]);
    if ($isRealKnife || $isRealGlove) {
      $field = $isRealKnife ? 'knife' : 'glove';
      $item = $this->PluginRepository->getItem($player['id'], $serverId, $team);
      if ($item) {
        $oldWi = (int)($item[$field] ?? 0);
        if ($oldWi > 0 && $oldWi !== $weaponIndex) {
          $this->PluginRepository->deleteSkin($player['id'], $serverId, $team, $oldWi);
        }
      }
      $this->assignItem($steamid, $team, $field, $weaponIndex, $serverId);
    }

    $skin = "{$skinId};0;0.0";
    $existing = $this->PluginRepository->getSkin($player['id'], $serverId, $team, $weaponIndex);
    if ($existing) {
      $this->PluginRepository->updateSkinData($player['id'], $serverId, $team, $weaponIndex, ['skin' => $skin]);
    } else {
      $this->PluginRepository->insertSkinDefault($player['id'], $serverId, $team, $weaponIndex);
      $this->PluginRepository->updateSkinData($player['id'], $serverId, $team, $weaponIndex, ['skin' => $skin]);
    }

    return ['success' => true];
  }

  private function getKnifeGloveIds(): array
  {
    static $knifeIds = null, $gloveIds = null;
    if ($knifeIds === null) {
      $categories = $this->CategoryRepository->getCategories();
      $knifeIds = array_flip(array_column($categories['knives']['list'] ?? [], 'id'));
      $gloveIds = array_flip(array_column($categories['gloves']['list'] ?? [], 'id'));
    }
    return [$knifeIds, $gloveIds];
  }

  public function isKnifeOrGlove(int $weaponIndex): bool
  {
    [$knifeIds, $gloveIds] = $this->getKnifeGloveIds();
    return isset($knifeIds[$weaponIndex]) || isset($gloveIds[$weaponIndex]);
  }

  public function resetSkin(string $steamid, int $team, int $weaponIndex, int $serverId = 0): array
  {
    $serverId = $this->resolveServerId($serverId);
    $player = $this->resolvePlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];
    if (!in_array($team, [0, 1], true)) return ['success' => false, 'error' => 'invalid_team'];

    [$knifeIds, $gloveIds] = $this->getKnifeGloveIds();
    $isRealKnife = isset($knifeIds[$weaponIndex]);
    $isRealGlove = isset($gloveIds[$weaponIndex]);
    if ($isRealKnife || $isRealGlove) {
      $field = $isRealKnife ? 'knife' : 'glove';
      $item = $this->PluginRepository->getItem($player['id'], $serverId, $team);
      if ($item && (int)($item[$field] ?? 0) === $weaponIndex) {
        $this->ensureItem($player['id'], $serverId, $team);
        $this->PluginRepository->updateItemField($player['id'], $serverId, $team, $field, 0);
      }
    }

    $this->PluginRepository->deleteSkin($player['id'], $serverId, $team, $weaponIndex);
    return ['success' => true];
  }

  public function resetSkins(string $steamid, array $items, int $serverId = 0): array
  {
    if (empty($items)) return ['success' => true];

    $player = $this->resolvePlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];

    $serverId = $this->resolveServerId($serverId);

    [$knifeIds, $gloveIds] = $this->getKnifeGloveIds();
    $pairs = [];
    $pairMap = [];
    foreach ($items as $item) {
      $team        = (int)($item['team'] ?? -1);
      $weaponIndex = (int)($item['weapon_index'] ?? 0);
      if (!in_array($team, [0, 1], true) || $weaponIndex <= 0) continue;

      $pairKey = $team . ':' . $weaponIndex;
      if (isset($pairMap[$pairKey])) continue;
      $pairMap[$pairKey] = true;

      $isRealKnife = isset($knifeIds[$weaponIndex]);
      $isRealGlove = isset($gloveIds[$weaponIndex]);
      if ($isRealKnife || $isRealGlove) {
        $field = $isRealKnife ? 'knife' : 'glove';
        $this->ensureItem($player['id'], $serverId, $team);
        $this->PluginRepository->updateItemField($player['id'], $serverId, $team, $field, 0);
      }

      $pairs[] = ['team' => $team, 'weapon_index' => $weaponIndex];
    }

    $this->PluginRepository->deleteSkinsBatch($player['id'], $serverId, $pairs);
    return ['success' => true];
  }

  public function updateSkinSettings(string $steamid, int $team, int $weaponIndex, ?float $float, ?int $pattern, ?int $stattrack, ?int $stattrackCount, ?string $tag, ?array $stickers = null, ?int $keychainId = null, int $serverId = 0): array
  {
    $serverId = $this->resolveServerId($serverId);
    $player = $this->resolvePlayer($steamid);
    if (!$player) return ['success' => false, 'error' => 'player_not_found'];
    if (!in_array($team, [0, 1], true)) return ['success' => false, 'error' => 'invalid_team'];

    if ($float !== null && ($float < 0 || $float > 0.9999)) return ['success' => false, 'error' => 'invalid_float'];
    if ($pattern !== null && ($pattern < 0 || $pattern > 999)) return ['success' => false, 'error' => 'invalid_pattern'];
    if ($stattrackCount !== null && ($stattrackCount < 0 || $stattrackCount > 999999)) return ['success' => false, 'error' => 'invalid_stattrack_count'];
    if ($tag !== null && mb_strlen($tag) > 20) return ['success' => false, 'error' => 'invalid_tag'];

    $existing = $this->ensureSkin($player['id'], $serverId, $team, $weaponIndex);

    $parts = explode(';', $existing['skin'] ?? '0;0;0.0');
    $curSkinId = $parts[0] ?? '0';
    $curPattern = (int)($parts[1] ?? 0);
    $curFloat   = (float)($parts[2] ?? 0.0);

    if ((int)$curSkinId === 0) {
      $otherTeam = $team === 0 ? 1 : 0;
      $otherSkin = $this->PluginRepository->getSkin($player['id'], $serverId, $otherTeam, $weaponIndex);
      if ($otherSkin) {
        $otherParts = explode(';', $otherSkin['skin'] ?? '0;0;0.0');
        $curSkinId = $otherParts[0] ?? '0';
      }
    }

    [$knifeIds, $gloveIds] = $this->getKnifeGloveIds();
    if (isset($knifeIds[$weaponIndex])) {
      $this->assignItem($steamid, $team, 'knife', $weaponIndex, $serverId);
    } elseif (isset($gloveIds[$weaponIndex])) {
      $this->assignItem($steamid, $team, 'glove', $weaponIndex, $serverId);
    }

    $newPattern = $pattern ?? $curPattern;
    $newFloat   = $float ?? $curFloat;
    $skinValue  = "{$curSkinId};{$newPattern};{$newFloat}";

    $data = ['skin' => $skinValue];
    if ($stattrack !== null) $data['stattrack'] = $stattrack;
    if ($stattrackCount !== null) $data['stattrack_count'] = $stattrackCount;
    if ($tag !== null) $data['tag'] = $tag;

    if ($stickers !== null && is_array($stickers)) {
      $stickerArray = [];
      for ($slot = 0; $slot < 4; $slot++) {
        $stickerArray[] = isset($stickers[$slot]) ? (string)(int)$stickers[$slot] : '0';
      }
      $data['stickers'] = implode(';', $stickerArray);
    } else {
      $existingStickers = explode(';', $existing['stickers'] ?? '0;0;0;0');
      $existingStickers = array_slice($existingStickers, 0, 4);
      while (count($existingStickers) < 4) $existingStickers[] = '0';
      $data['stickers'] = implode(';', $existingStickers);
    }

    if ($keychainId !== null) {
      if ($keychainId === 0) {
        $data['keychain'] = '';
      } else {
        $pos = self::KEYCHAIN_POSITIONS[$weaponIndex] ?? [8.0, 0.0, 3.0];
        $data['keychain'] = "{$keychainId};{$pos[0]};{$pos[1]};{$pos[2]}";
      }
    } else {
      $data['keychain'] = '0;0;0;0;0';
    }

    $this->PluginRepository->updateSkinData($player['id'], $serverId, $team, $weaponIndex, $data);
    return ['success' => true];
  }
}
