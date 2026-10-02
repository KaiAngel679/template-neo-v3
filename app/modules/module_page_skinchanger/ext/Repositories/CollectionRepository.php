<?php

namespace app\modules\module_page_skinchanger\ext\Repositories;

class CollectionRepository
{
  protected $Db, $DID, $UID;

  public function __construct(object $Db, int $DID = 0, int $UID = 0)
  {
    $this->Db = $Db;
    $this->DID = $DID;
    $this->UID = $UID;
  }

  private function getSkinsCountSubquery(): string
  {
    return "(SELECT COUNT(DISTINCT cs.`weapon_index`, cs.`skin`)
          FROM `lvl_web_skins_collection_skins` cs
          WHERE cs.`collection_id` = c.`id`
          AND NOT EXISTS (
            SELECT 1 FROM `lvl_web_skins_collection_items` ci
            WHERE ci.`collection_id` = c.`id`
            AND (ci.`knife` = cs.`weapon_index` OR ci.`glove` = cs.`weapon_index`)
          ))
         + (SELECT COUNT(DISTINCT ci.`knife`) FROM `lvl_web_skins_collection_items` ci WHERE ci.`collection_id` = c.`id` AND ci.`knife` > 0)
         + (SELECT COUNT(DISTINCT ci.`glove`) FROM `lvl_web_skins_collection_items` ci WHERE ci.`collection_id` = c.`id` AND ci.`glove` > 0)
         + (SELECT COUNT(DISTINCT ci.`agent`) FROM `lvl_web_skins_collection_items` ci WHERE ci.`collection_id` = c.`id` AND ci.`agent` > 0)
         + (SELECT COUNT(DISTINCT ci.`music`) FROM `lvl_web_skins_collection_items` ci WHERE ci.`collection_id` = c.`id` AND ci.`music` > 0)
         + (SELECT COUNT(DISTINCT ci.`coin`) FROM `lvl_web_skins_collection_items` ci WHERE ci.`collection_id` = c.`id` AND ci.`coin` > 0)";
  }

  public function getCollections(string $steamid)
  {
    $skinsCountSql = $this->getSkinsCountSubquery();
    $result = $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT c.*,
         {$skinsCountSql} AS skins_count,
         (SELECT COUNT(*) FROM `lvl_web_skins_collection_likes` l WHERE l.`collection_id` = c.`id`) AS likes_count,
         (SELECT cs2.`weapon_index` FROM `lvl_web_skins_collection_skins` cs2 WHERE cs2.`collection_id` = c.`id` ORDER BY cs2.`id` ASC LIMIT 1) AS cover_weapon_index,
         (SELECT cs2.`skin` FROM `lvl_web_skins_collection_skins` cs2 WHERE cs2.`collection_id` = c.`id` ORDER BY cs2.`id` ASC LIMIT 1) AS cover_skin,
         (SELECT ci.`knife` FROM `lvl_web_skins_collection_items` ci WHERE ci.`collection_id` = c.`id` AND ci.`knife` > 0 LIMIT 1) AS cover_knife_index
       FROM `lvl_web_skins_collections` c
       WHERE c.`steamid` = :steamid
       ORDER BY c.`id` ASC;",
      ['steamid' => $steamid]
    );
    return $result;
  }

  public function getCollection(int $collection_id)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "SELECT * FROM `lvl_web_skins_collections` WHERE `id` = :id;", ['id' => $collection_id]);
  }

  public function getActiveCollection(string $steamid)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "SELECT * FROM `lvl_web_skins_collections` WHERE `steamid` = :steamid AND `is_active` = 1 LIMIT 1;", ['steamid' => $steamid]);
  }

  public function countCollections(string $steamid)
  {
    $row = $this->Db->queryNum('Skins', $this->DID, $this->UID, "SELECT COUNT(*) FROM `lvl_web_skins_collections` WHERE `steamid` = :steamid;", ['steamid' => $steamid]);
    return (int)($row[0] ?? 0);
  }

  public function createCollection(string $steamid, string $name, bool $is_public = false)
  {
    $now = time();
    $pub = $is_public ? 1 : 0;
    $this->Db->query('Skins', $this->DID, $this->UID, "INSERT INTO `lvl_web_skins_collections` (`steamid`, `name`, `is_active`, `is_public`, `installs_count`, `created_at`, `updated_at`, `published_at`) VALUES (:steamid, :name, 0, :is_public, 0, :created_at, :updated_at, 0);", ['steamid' => $steamid, 'name' => $name, 'is_public' => $pub, 'created_at' => $now, 'updated_at' => $now]);
    return (int)$this->Db->lastInsertId('Skins', $this->DID, $this->UID);
  }

  public function renameCollection(int $collection_id, string $name)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "UPDATE `lvl_web_skins_collections` SET `name` = :name, `updated_at` = :updated_at WHERE `id` = :id;", ['name' => $name, 'updated_at' => time(), 'id' => $collection_id]);
  }

  public function setCollectionPublic(int $collection_id, bool $is_public)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "UPDATE `lvl_web_skins_collections` SET `is_public` = :is_public, `published_at` = IF(:is_public2 = 1, :now, 0) WHERE `id` = :id;", ['is_public' => (int)(bool)$is_public, 'is_public2' => (int)(bool)$is_public, 'now' => time(), 'id' => $collection_id]);
  }

  public function touchCollection(int $collection_id): void
  {
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "UPDATE `lvl_web_skins_collections` SET `updated_at` = :now WHERE `id` = :id;",
      ['now' => time(), 'id' => $collection_id]
    );
  }

  public function deleteCollection(int $collection_id)
  {
    $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `lvl_web_skins_collection_skins` WHERE `collection_id` = :id;", ['id' => $collection_id]);
    $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `lvl_web_skins_collection_items` WHERE `collection_id` = :id;", ['id' => $collection_id]);
    $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `lvl_web_skins_collection_likes` WHERE `collection_id` = :id;", ['id' => $collection_id]);
    return $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `lvl_web_skins_collections` WHERE `id` = :id;", ['id' => $collection_id]);
  }

  public function setActiveCollection(string $steamid, int $collection_id)
  {
    $this->Db->query('Skins', $this->DID, $this->UID, "UPDATE `lvl_web_skins_collections` SET `is_active` = 0 WHERE `steamid` = :steamid;", ['steamid' => $steamid]);
    return $this->Db->query('Skins', $this->DID, $this->UID, "UPDATE `lvl_web_skins_collections` SET `is_active` = 1 WHERE `id` = :id;", ['id' => $collection_id]);
  }

  public function incrementInstalls(int $collection_id)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "UPDATE `lvl_web_skins_collections` SET `installs_count` = `installs_count` + 1 WHERE `id` = :id;", ['id' => $collection_id]);
  }

  public function getLike(int $collection_id, string $steamid)
  {
    $row = $this->Db->query('Skins', $this->DID, $this->UID, "SELECT `id` FROM `lvl_web_skins_collection_likes` WHERE `collection_id` = :cid AND `steamid` = :steamid;", ['cid' => $collection_id, 'steamid' => $steamid]);
    return !empty($row);
  }

  public function addLike(int $collection_id, string $steamid)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "INSERT IGNORE INTO `lvl_web_skins_collection_likes` (`collection_id`, `steamid`) VALUES (:cid, :steamid);", ['cid' => $collection_id, 'steamid' => $steamid]);
  }

  public function removeLike(int $collection_id, string $steamid)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `lvl_web_skins_collection_likes` WHERE `collection_id` = :cid AND `steamid` = :steamid;", ['cid' => $collection_id, 'steamid' => $steamid]);
  }

  public function countLikes(int $collection_id)
  {
    $row = $this->Db->queryNum('Skins', $this->DID, $this->UID, "SELECT COUNT(*) FROM `lvl_web_skins_collection_likes` WHERE `collection_id` = :cid;", ['cid' => $collection_id]);
    return (int)($row[0] ?? 0);
  }

  public function getPublicCollections(int $limit, int $offset, string $order = 'likes', array $filters = [])
  {
    $orderSql = ($order === 'installs') ? 'c.`installs_count` DESC, c.`id` ASC' : 'likes_count DESC, c.`id` ASC';
    $where = ['c.`is_public` = 1', 'EXISTS (SELECT 1 FROM `lvl_web_skins_collection_skins` cs0 WHERE cs0.`collection_id` = c.`id`)'];
    $params = ['limit' => (int)$limit, 'offset' => (int)$offset];

    $sort = $filters['sort'] ?? '';
    if ($sort === 'popular')     $orderSql = 'c.`installs_count` DESC, c.`id` ASC';
    elseif ($sort === 'liked')   $orderSql = 'likes_count DESC, c.`id` ASC';
    elseif ($sort === 'new')     $orderSql = 'c.`created_at` DESC, c.`id` ASC';
    elseif ($sort === 'last7d') {
      $where[] = 'c.`created_at` >= :ts7d';
      $params['ts7d'] = time() - 7 * 86400;
      $orderSql = 'likes_count DESC, c.`id` ASC';
    }

    if (!empty($filters['search'])) {
      $where[] = 'c.`name` LIKE :search';
      $params['search'] = '%' . $filters['search'] . '%';
    }

    if (!empty($filters['with_downloads'])) {
      $where[] = 'c.`installs_count` > 0';
    }

    if (!empty($filters['liked_me']) && !empty($filters['my_steamid'])) {
      $where[] = 'EXISTS (SELECT 1 FROM `lvl_web_skins_collection_likes` fl WHERE fl.`collection_id` = c.`id` AND fl.`steamid` = :filter_steamid)';
      $params['filter_steamid'] = $filters['my_steamid'];
    }

    if (!empty($filters['my_only']) && !empty($filters['my_steamid'])) {
      $where[] = 'c.`steamid` = :owner_steamid';
      $params['owner_steamid'] = $filters['my_steamid'];
    }

    $whereSql = implode(' AND ', $where);

    $mySteamid = $filters['my_steamid'] ?? '';
    $params['ml_steamid'] = $mySteamid;

    $result = $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT c.*, c.`steamid`,
          (SELECT COUNT(*) FROM `lvl_web_skins_collection_likes` l WHERE l.`collection_id` = c.`id`) AS likes_count,
          (SELECT 1 FROM `lvl_web_skins_collection_likes` ml WHERE ml.`collection_id` = c.`id` AND ml.`steamid` = :ml_steamid LIMIT 1) AS my_liked
        FROM `lvl_web_skins_collections` c
        WHERE {$whereSql}
        ORDER BY {$orderSql}
        LIMIT :limit OFFSET :offset;",
      $params
    );

    return $result;
  }

  public function countPublicCollections(array $filters = [])
  {
    $where = ['c.`is_public` = 1', 'EXISTS (SELECT 1 FROM `lvl_web_skins_collection_skins` cs0 WHERE cs0.`collection_id` = c.`id`)'];
    $params = [];

    $sort = $filters['sort'] ?? '';
    if ($sort === 'last7d') {
      $where[] = 'c.`created_at` >= :ts7d';
      $params['ts7d'] = time() - 7 * 86400;
    }

    if (!empty($filters['search'])) {
      $where[] = 'c.`name` LIKE :search';
      $params['search'] = '%' . $filters['search'] . '%';
    }
    if (!empty($filters['with_downloads'])) {
      $where[] = 'c.`installs_count` > 0';
    }
    if (!empty($filters['liked_me']) && !empty($filters['my_steamid'])) {
      $where[] = 'EXISTS (SELECT 1 FROM `lvl_web_skins_collection_likes` fl WHERE fl.`collection_id` = c.`id` AND fl.`steamid` = :filter_steamid)';
      $params['filter_steamid'] = $filters['my_steamid'];
    }
    if (!empty($filters['my_only']) && !empty($filters['my_steamid'])) {
      $where[] = 'c.`steamid` = :owner_steamid';
      $params['owner_steamid'] = $filters['my_steamid'];
    }

    $whereSql = implode(' AND ', $where);

    $row = $this->Db->queryNum('Skins', $this->DID, $this->UID, "SELECT COUNT(*) FROM `lvl_web_skins_collections` c WHERE {$whereSql};", $params);
    return (int)($row[0] ?? 0);
  }

  public function getDefaultCollections()
  {
    return $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT c.*,
          (SELECT COUNT(*) FROM `lvl_web_skins_collection_likes` l WHERE l.`collection_id` = c.`id`) AS likes_count
        FROM `lvl_web_skins_collections` c
        WHERE c.`is_default` = 1
        ORDER BY c.`created_at` ASC;",
      []
    );
  }

  public function setCollectionDefault(int $collection_id, bool $is_default)
  {
    return $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "UPDATE `lvl_web_skins_collections` SET `is_default` = :is_default WHERE `id` = :id;",
      ['is_default' => (int)$is_default, 'id' => $collection_id]
    );
  }

  public function getCollectionSkins(int $collection_id)
  {
    return $this->Db->queryAll('Skins', $this->DID, $this->UID, "SELECT * FROM `lvl_web_skins_collection_skins` WHERE `collection_id` = :collection_id;", ['collection_id' => $collection_id]);
  }

  public function countCollectionSkinsDistinct(int $collection_id): int
  {
    $row = $this->Db->queryNum(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT COUNT(DISTINCT `weapon_index`, `skin`) FROM `lvl_web_skins_collection_skins` WHERE `collection_id` = :collection_id;",
      ['collection_id' => $collection_id]
    );
    return (int)($row[0] ?? 0);
  }

  public function clearCollectionSkins(int $collection_id)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `lvl_web_skins_collection_skins` WHERE `collection_id` = :collection_id;", ['collection_id' => $collection_id]);
  }

  public function deleteCollectionSkinsBatchByServer(int $collection_id, array $rows): void
  {
    if (empty($rows)) return;
    $conditions = [];
    $params = ['collection_id' => $collection_id];
    foreach ($rows as $i => $row) {
      $sk = 'sid' . $i;
      $tk = 'team' . $i;
      $wk = 'wi' . $i;
      $conditions[] = "(`server_id` = :{$sk} AND `team` = :{$tk} AND `weapon_index` = :{$wk})";
      $params[$sk] = (int)$row['server_id'];
      $params[$tk] = (int)$row['team'];
      $params[$wk] = (int)$row['weapon_index'];
    }
    $sql = "DELETE FROM `lvl_web_skins_collection_skins` WHERE `collection_id` = :collection_id AND (" . implode(' OR ', $conditions) . ");";
    $this->Db->query('Skins', $this->DID, $this->UID, $sql, $params);
  }

  public function bulkInsertCollectionSkins(int $collection_id, array $skins): void
  {
    if (empty($skins)) return;
    $placeholders = [];
    $params = [];
    foreach ($skins as $s) {
      $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
      $params[] = $collection_id;
      $params[] = (int)$s['server_id'];
      $params[] = (int)$s['team'];
      $params[] = (int)$s['weapon_index'];
      $params[] = $s['skin'] ?? '0;0;0.0';
      $params[] = (int)($s['stattrack'] ?? 0);
      $params[] = (int)($s['stattrack_count'] ?? 0);
      $params[] = $s['stickers'] ?? '0;0;0;0';
      $params[] = $s['keychain'] ?? '';
      $params[] = $s['tag'] ?? '';
    }
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "INSERT INTO `lvl_web_skins_collection_skins` (`collection_id`, `server_id`, `team`, `weapon_index`, `skin`, `stattrack`, `stattrack_count`, `stickers`, `keychain`, `tag`) VALUES " . implode(', ', $placeholders) . " ON DUPLICATE KEY UPDATE `skin` = VALUES(`skin`), `stattrack` = VALUES(`stattrack`), `stattrack_count` = VALUES(`stattrack_count`), `stickers` = VALUES(`stickers`), `keychain` = VALUES(`keychain`), `tag` = VALUES(`tag`);",
      $params
    );
  }

  public function getCollectionItems(int $collection_id)
  {
    return $this->Db->queryAll('Skins', $this->DID, $this->UID, "SELECT * FROM `lvl_web_skins_collection_items` WHERE `collection_id` = :collection_id;", ['collection_id' => $collection_id]);
  }

  public function getCollectionsSkinsBatch(array $ids): array
  {
    if (empty($ids)) return [];
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $rows = $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT * FROM `lvl_web_skins_collection_skins` WHERE `collection_id` IN ({$ph}) ORDER BY `id` ASC;",
      array_map('intval', array_values($ids))
    );
    $result = [];
    foreach ($rows ?: [] as $row) {
      $result[(int)$row['collection_id']][] = $row;
    }
    return $result;
  }

  public function getCollectionsItemsBatch(array $ids): array
  {
    if (empty($ids)) return [];
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $rows = $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT * FROM `lvl_web_skins_collection_items` WHERE `collection_id` IN ({$ph});",
      array_map('intval', array_values($ids))
    );
    $result = [];
    foreach ($rows ?: [] as $row) {
      $result[(int)$row['collection_id']][] = $row;
    }
    return $result;
  }

  public function clearCollectionItems(int $collection_id)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `lvl_web_skins_collection_items` WHERE `collection_id` = :collection_id;", ['collection_id' => $collection_id]);
  }

  public function bulkInsertCollectionItems(int $collection_id, array $items): void
  {
    if (empty($items)) return;
    $placeholders = [];
    $params = [];
    foreach ($items as $it) {
      $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?)';
      $params[] = $collection_id;
      $params[] = (int)$it['server_id'];
      $params[] = (int)$it['team'];
      $params[] = (int)($it['knife'] ?? 0);
      $params[] = (int)($it['glove'] ?? 0);
      $params[] = (int)($it['agent'] ?? 0);
      $params[] = (int)($it['music'] ?? 0);
      $params[] = (int)($it['coin'] ?? 0);
    }
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "INSERT INTO `lvl_web_skins_collection_items` (`collection_id`, `server_id`, `team`, `knife`, `glove`, `agent`, `music`, `coin`) VALUES " . implode(', ', $placeholders) . " ON DUPLICATE KEY UPDATE `knife` = VALUES(`knife`), `glove` = VALUES(`glove`), `agent` = VALUES(`agent`), `music` = VALUES(`music`), `coin` = VALUES(`coin`);",
      $params
    );
  }

  public function getPresets(): array
  {
    return $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT p.*,
         (SELECT ps2.`weapon_index` FROM `lvl_web_skins_preset_skins` ps2 WHERE ps2.`preset_id` = p.`id` ORDER BY ps2.`id` ASC LIMIT 1) AS cover_weapon_index,
         (SELECT ps2.`skin` FROM `lvl_web_skins_preset_skins` ps2 WHERE ps2.`preset_id` = p.`id` ORDER BY ps2.`id` ASC LIMIT 1) AS cover_skin,
         (SELECT pi.`knife` FROM `lvl_web_skins_preset_items` pi WHERE pi.`preset_id` = p.`id` AND pi.`knife` > 0 LIMIT 1) AS cover_knife_index
       FROM `lvl_web_skins_presets` p
       ORDER BY p.`sort_order` ASC, p.`id` ASC;",
      []
    ) ?: [];
  }

  public function getPreset(int $id)
  {
    $rows = $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT * FROM `lvl_web_skins_presets` WHERE `id` = :id LIMIT 1;",
      ['id' => $id]
    );
    return $rows[0] ?? false;
  }

  public function getPresetMaxSortOrder(): int
  {
    $row = $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT MAX(`sort_order`) AS m FROM `lvl_web_skins_presets`;",
      []
    );
    return (int)(($row[0]['m'] ?? 0));
  }

  public function createPreset(string $name, int $sort_order = 0): int
  {
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "INSERT INTO `lvl_web_skins_presets` (`name`, `sort_order`) VALUES (:name, :sort_order);",
      ['name' => $name, 'sort_order' => $sort_order]
    );
    return (int)$this->Db->lastInsertId('Skins', $this->DID, $this->UID);
  }

  public function updatePresetSort(int $id, int $sort_order): void
  {
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "UPDATE `lvl_web_skins_presets` SET `sort_order` = :sort_order WHERE `id` = :id;",
      ['sort_order' => $sort_order, 'id' => $id]
    );
  }

  public function deletePreset(int $preset_id): void
  {
    $this->clearPresetSkins($preset_id);
    $this->clearPresetItems($preset_id);
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "DELETE FROM `lvl_web_skins_presets` WHERE `id` = :id;",
      ['id' => $preset_id]
    );
  }

  public function getPresetSkins(int $preset_id): array
  {
    return $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT * FROM `lvl_web_skins_preset_skins` WHERE `preset_id` = :preset_id;",
      ['preset_id' => $preset_id]
    ) ?: [];
  }

  public function getPresetSkinsBatch(array $ids): array
  {
    if (empty($ids)) return [];
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $rows = $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT * FROM `lvl_web_skins_preset_skins` WHERE `preset_id` IN ({$ph}) ORDER BY `id` ASC;",
      array_map('intval', array_values($ids))
    );
    $result = [];
    foreach ($rows ?: [] as $row) {
      $result[(int)$row['preset_id']][] = $row;
    }
    return $result;
  }

  public function clearPresetSkins(int $preset_id): void
  {
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "DELETE FROM `lvl_web_skins_preset_skins` WHERE `preset_id` = :preset_id;",
      ['preset_id' => $preset_id]
    );
  }

  public function bulkInsertPresetSkins(int $preset_id, array $skins): void
  {
    if (empty($skins)) return;
    $placeholders = [];
    $params = [];
    foreach ($skins as $s) {
      $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
      $params[] = $preset_id;
      $params[] = (int)$s['server_id'];
      $params[] = (int)$s['team'];
      $params[] = (int)$s['weapon_index'];
      $params[] = $s['skin'] ?? '0;0;0.0';
      $params[] = (int)($s['stattrack'] ?? 0);
      $params[] = (int)($s['stattrack_count'] ?? 0);
      $params[] = $s['stickers'] ?? '0;0;0;0';
      $params[] = $s['keychain'] ?? '';
      $params[] = $s['tag'] ?? '';
    }
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "INSERT INTO `lvl_web_skins_preset_skins` (`preset_id`, `server_id`, `team`, `weapon_index`, `skin`, `stattrack`, `stattrack_count`, `stickers`, `keychain`, `tag`) VALUES " . implode(', ', $placeholders) . ";",
      $params
    );
  }

  public function getPresetItems(int $preset_id): array
  {
    return $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT * FROM `lvl_web_skins_preset_items` WHERE `preset_id` = :preset_id;",
      ['preset_id' => $preset_id]
    ) ?: [];
  }

  public function getPresetItemsBatch(array $ids): array
  {
    if (empty($ids)) return [];
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $rows = $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT * FROM `lvl_web_skins_preset_items` WHERE `preset_id` IN ({$ph});",
      array_map('intval', array_values($ids))
    );
    $result = [];
    foreach ($rows ?: [] as $row) {
      $result[(int)$row['preset_id']][] = $row;
    }
    return $result;
  }

  public function clearPresetItems(int $preset_id): void
  {
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "DELETE FROM `lvl_web_skins_preset_items` WHERE `preset_id` = :preset_id;",
      ['preset_id' => $preset_id]
    );
  }

  public function bulkInsertPresetItems(int $preset_id, array $items): void
  {
    if (empty($items)) return;
    $placeholders = [];
    $params = [];
    foreach ($items as $it) {
      $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?)';
      $params[] = $preset_id;
      $params[] = (int)$it['server_id'];
      $params[] = (int)$it['team'];
      $params[] = (int)($it['knife'] ?? 0);
      $params[] = (int)($it['glove'] ?? 0);
      $params[] = (int)($it['agent'] ?? 0);
      $params[] = (int)($it['music'] ?? 0);
      $params[] = (int)($it['coin'] ?? 0);
    }
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "INSERT INTO `lvl_web_skins_preset_items` (`preset_id`, `server_id`, `team`, `knife`, `glove`, `agent`, `music`, `coin`) VALUES " . implode(', ', $placeholders) . ";",
      $params
    );
  }
}
