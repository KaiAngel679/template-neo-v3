<?php

namespace app\modules\module_page_skinchanger\ext\Repositories;

class SkinRepository extends BaseRepository
{
  private $allCache = [];

  public function getAll(string $lang)
  {
    if (isset($this->allCache[$lang])) {
      return $this->allCache[$lang];
    }

    $skins = $this->loadFromJson($lang, 'skins');

    $settingsRepo = new SettingsRepository();
    $settings = $settingsRepo->getSettings();
    $selectedCollectionIds = $settings['new_collections'] ?? [];

    if (!empty($selectedCollectionIds) && is_array($skins)) {
      $allCollections = $this->getCollections($lang);
      $selectedCrateIds = [];

      foreach ($allCollections as $collection) {
        if (in_array($collection['id'], $selectedCollectionIds, true)) {
          $selectedCrateIds = array_merge($selectedCrateIds, $collection['crates'] ?? []);
        }
      }

      $selectedCrateIds = array_unique($selectedCrateIds);

      foreach ($skins as &$weapon) {
        if (!empty($weapon['skins']) && is_array($weapon['skins'])) {
          foreach ($weapon['skins'] as &$skin) {
            $skinCollections = $skin['collections'] ?? [];
            $skinCrates = $skin['crates'] ?? [];

            $hasCollectionMatch = !empty(array_intersect($skinCollections, $selectedCollectionIds));
            $hasCrateMatch = !empty($selectedCrateIds) && !empty(array_intersect($skinCrates, $selectedCrateIds));

            $skin['new'] = $hasCollectionMatch || $hasCrateMatch;
          }
          unset($skin);
        }
      }
      unset($weapon);
    }

    $this->allCache[$lang] = $skins;
    return $skins;
  }

  public function getById(string $lang, int $id): ?array
  {
    $skins = $this->getAll($lang);
    foreach ($skins as $skin) {
      if ($skin['id'] == $id) {
        return $skin;
      }
    }
    return null;
  }

  private function rarityOrder(): array
  {
    return [
      'rarity_contraband_weapon' => 0,
      'rarity_ancient_weapon'    => 1,
      'rarity_legendary_weapon'  => 2,
      'rarity_mythical_weapon'   => 3,
      'rarity_rare_weapon'       => 4,
      'rarity_uncommon_weapon'   => 5,
      'rarity_common_weapon'     => 6,
    ];
  }

  public function getByIdSorted(string $lang, int $id)
  {
    $skins = $this->getAll($lang);
    foreach ($skins as $skin) {
      if ($skin['id'] == $id) {
        if (!empty($skin['skins']) && is_array($skin['skins'])) {
          $order = $this->rarityOrder();
          uasort($skin['skins'], function ($a, $b) use ($order) {
            $ra = $order[$a['id_rarity']] ?? 99;
            $rb = $order[$b['id_rarity']] ?? 99;
            return $ra - $rb;
          });
          $skin['skins'] = array_values($skin['skins']);
        }
        return $skin;
      }
    }
    return null;
  }

  public function getPlaceholders()
  {
    $placeholders = $this->loadFromJson('placeholders');
    return is_array($placeholders) ? $placeholders : [];
  }

  public function getStickers(string $lang)
  {
    return $this->loadFromJson($lang, 'stickers') ?? [];
  }

  public function getKeychains(string $lang)
  {
    return $this->loadFromJson($lang, 'keychains') ?? [];
  }

  public function getAgents(string $lang)
  {
    $list = $this->loadFromJson($lang, 'agents') ?? [];
    $map = [];
    foreach ($list as $a) $map[$a['id']] = $a;
    return $map;
  }

  public function getMusic(string $lang)
  {
    $list = $this->loadFromJson($lang, 'music') ?? [];
    $map = [];
    foreach ($list as $m) $map[$m['id']] = $m;
    return $map;
  }

  public function getCollectibles(string $lang)
  {
    $list = $this->loadFromJson($lang, 'collectibles') ?? [];
    $map = [];
    foreach ($list as $c) $map[$c['id']] = $c;
    return $map;
  }

  public function getCollections(string $lang)
  {
    return $this->loadFromJson($lang, 'collections') ?? [];
  }

  public function searchSkins(string $lang, string $query, int $limit = 30): array
  {
    $all = $this->getAll($lang);
    if (!is_array($all) || empty($all)) {
      $all = $this->getAll('en');
    }
    if (!is_array($all)) {
      $all = [];
    }

    $enAll = ($lang !== 'en') ? $this->getAll('en') : [];
    $enIndex = [];
    foreach ($enAll as $weapon) {
      foreach ($weapon['skins'] ?? [] as $skin) {
        $enIndex[$weapon['id'] . '_' . $skin['id_skin']] = mb_strtolower($skin['name'] ?? '');
      }
    }

    $queryLower = mb_strtolower($query);
    $results = [];
    $seen = [];
    $order = $this->rarityOrder();

    foreach ($all as $weapon) {
      foreach ($weapon['skins'] ?? [] as $skin) {
        $name = mb_strtolower($skin['name'] ?? '');
        $enName = $enIndex[$weapon['id'] . '_' . $skin['id_skin']] ?? '';
        if (mb_strpos($name, $queryLower) !== false || ($enName && mb_strpos($enName, $queryLower) !== false)) {
          $key = $weapon['id'] . '_' . $skin['id_skin'];
          if (isset($seen[$key])) continue;
          $seen[$key] = true;
          $results[] = [
            'weapon_id' => $weapon['id'],
            'weapon_name' => $weapon['name'] ?? '',
            'id_skin' => $skin['id_skin'],
            'name' => $skin['name'],
            'image' => $skin['image'] ?? '',
            'id_rarity' => $skin['id_rarity'] ?? '',
            '_rarity_order' => $order[$skin['id_rarity']] ?? 99,
          ];
        }
      }
    }

    $agents = array_values($this->getAgents($lang));
    $enAgents = ($lang !== 'en') ? array_values($this->getAgents('en')) : [];
    $enAgentIndex = [];
    foreach ($enAgents as $a) {
      $enAgentIndex[$a['id']] = mb_strtolower($a['name'] ?? '');
    }
    foreach ($agents as $agent) {
      $name = mb_strtolower($agent['name'] ?? '');
      $enName = $enAgentIndex[$agent['id']] ?? '';
      if (mb_strpos($name, $queryLower) !== false || ($enName && mb_strpos($enName, $queryLower) !== false)) {
        $key = 'agent_' . $agent['id'];
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $team = (int)($agent['team'] ?? 0);
        $results[] = [
          'weapon_id' => $team === 1 ? 'agent_ct' : 'agent_t',
          'weapon_name' => '',
          'id_skin' => $agent['id'],
          'name' => $agent['name'],
          'image' => $agent['image'] ?? '',
          'id_rarity' => $agent['id_rarity'] ?? '',
          '_rarity_order' => 50,
        ];
      }
    }

    $music = array_values($this->getMusic($lang));
    $enMusic = ($lang !== 'en') ? array_values($this->getMusic('en')) : [];
    $enMusicIndex = [];
    foreach ($enMusic as $m) {
      $enMusicIndex[$m['id']] = mb_strtolower($m['name'] ?? '');
    }
    foreach ($music as $item) {
      $name = mb_strtolower($item['name'] ?? '');
      $enName = $enMusicIndex[$item['id']] ?? '';
      if (mb_strpos($name, $queryLower) !== false || ($enName && mb_strpos($enName, $queryLower) !== false)) {
        $key = 'record_' . $item['id'];
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $results[] = [
          'weapon_id' => 'record',
          'weapon_name' => '',
          'id_skin' => $item['id'],
          'name' => $item['name'],
          'image' => $item['image'] ?? '',
          'id_rarity' => $item['id_rarity'] ?? '',
          '_rarity_order' => 50,
        ];
      }
    }

    $collectibles = array_values($this->getCollectibles($lang));
    $enCollectibles = ($lang !== 'en') ? array_values($this->getCollectibles('en')) : [];
    $enCollectiblesIndex = [];
    foreach ($enCollectibles as $c) {
      $enCollectiblesIndex[$c['id']] = mb_strtolower($c['name'] ?? '');
    }
    foreach ($collectibles as $item) {
      $name = mb_strtolower($item['name'] ?? '');
      $enName = $enCollectiblesIndex[$item['id']] ?? '';
      if (mb_strpos($name, $queryLower) !== false || ($enName && mb_strpos($enName, $queryLower) !== false)) {
        $key = 'coin_' . $item['id'];
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $results[] = [
          'weapon_id' => 'coin',
          'weapon_name' => '',
          'id_skin' => $item['id'],
          'name' => $item['name'],
          'image' => $item['image'] ?? '',
          'id_rarity' => $item['id_rarity'] ?? '',
          '_rarity_order' => 50,
        ];
      }
    }

    usort($results, fn($a, $b) => $a['_rarity_order'] - $b['_rarity_order']);
    foreach ($results as &$r) unset($r['_rarity_order']);
    return array_slice($results, 0, $limit);
  }
}
