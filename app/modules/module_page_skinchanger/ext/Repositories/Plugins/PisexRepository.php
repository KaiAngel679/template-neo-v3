<?php

namespace app\modules\module_page_skinchanger\ext\Repositories\Plugins;

class PisexRepository
{
  protected object $Db;
  protected int $DID;
  protected int $UID;
  public function __construct(object $Db, int $DID, int $UID)
  {
    $this->Db = $Db;
    $this->DID = $DID;
    $this->UID = $UID;
  }

  public function resolveIdentifier(string $steamid)
  {
    $player = $this->getPlayer($steamid);
    return $player['id'] ?? null;
  }

  public function getPlayer(string $steamid)
  {
    $player = $this->Db->query('Skins', $this->DID, $this->UID, "SELECT * FROM `sc_player` WHERE `steamid` = :steamid;", ['steamid' => $steamid]);
    if (!empty($player)) {
      return $player;
    }
    $this->Db->query('Skins', $this->DID, $this->UID, "INSERT INTO `sc_player` (`name`, `steamid`) VALUES (:name, :steamid);", [
      'name'    => 'Unknown',
      'steamid' => $steamid,
    ]);
    return $this->Db->query('Skins', $this->DID, $this->UID, "SELECT * FROM `sc_player` WHERE `steamid` = :steamid;", ['steamid' => $steamid]);
  }

  public function getFirstServerId(): int
  {
    $row = $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT `id` FROM `sc_servers` ORDER BY `id` ASC LIMIT 1;",
      []
    );
    if (!empty($row['id'])) {
      return (int)$row['id'];
    }
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "INSERT INTO `sc_servers` (`name`, `ip_address`, `port`) VALUES (:name, :ip, :port);",
      ['name' => 'All', 'ip' => '127.0.0.1', 'port' => 27015]
    );
    $inserted = $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT `id` FROM `sc_servers` ORDER BY `id` ASC LIMIT 1;",
      []
    );
    return (int)($inserted['id'] ?? 1);
  }

  public function getItems(string $player_id)
  {
    return $this->Db->queryAll('Skins', $this->DID, $this->UID, "SELECT * FROM `sc_items` WHERE `player_id` = :player_id;", ['player_id' => $player_id]);
  }

  public function getItem(string $player_id, int $server_id, string $team)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "SELECT * FROM `sc_items` WHERE `player_id` = :player_id AND `server_id` = :server_id AND `team` = :team;", ['player_id' => $player_id, 'server_id' => $server_id, 'team' => $team]);
  }

  public function insertItem(string $player_id, int $server_id, string $team)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "INSERT INTO `sc_items` (`player_id`, `server_id`, `team`, `agent`, `music`, `coin`, `knife`, `glove`) VALUES (:player_id, :server_id, :team, 0, 0, 0, 0, 0);", ['player_id' => $player_id, 'server_id' => $server_id, 'team' => $team]);
  }

  public function updateItemField(string $player_id, int $server_id, string $team, string $field, string $value)
  {
    $fieldMap = [
      'knife' => 'knife',
      'glove' => 'glove',
      'agent' => 'agent',
      'music' => 'music',
      'coin'  => 'coin',
    ];

    if (!isset($fieldMap[$field])) return false;
    $safeField = $fieldMap[$field];

    return $this->Db->query('Skins', $this->DID, $this->UID, "UPDATE `sc_items` SET `{$safeField}` = :value WHERE `player_id` = :player_id AND `server_id` = :server_id AND `team` = :team;", ['value' => $value, 'player_id' => $player_id, 'server_id' => $server_id, 'team' => $team]);
  }

  public function getSkins(string $player_id)
  {
    return $this->Db->queryAll('Skins', $this->DID, $this->UID, "SELECT * FROM `sc_skins` WHERE `player_id` = :player_id;", ['player_id' => $player_id]);
  }

  public function getSkin(string $player_id, int $server_id, string $team, int $weapon_index)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "SELECT * FROM `sc_skins` WHERE `player_id` = :player_id AND `server_id` = :server_id AND `team` = :team AND `weapon_index` = :weapon_index;", ['player_id' => $player_id, 'server_id' => $server_id, 'team' => $team, 'weapon_index' => $weapon_index]);
  }

  public function insertSkinDefault(string $player_id, int $server_id, string $team, int $weapon_index)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "INSERT INTO `sc_skins` (`player_id`, `server_id`, `team`, `weapon_index`, `stattrack`, `stattrack_count`, `stickers`, `skin`, `tag`) VALUES (:player_id, :server_id, :team, :weapon_index, 0, 0, '0;0;0;0', '0;0;0.0', '');", ['player_id' => $player_id, 'server_id' => $server_id, 'team' => $team, 'weapon_index' => $weapon_index]);
  }

  public function updateSkinData(string $player_id, int $server_id, string $team, int $weapon_index, array $data)
  {
    $allowed = ['skin', 'stattrack', 'stattrack_count', 'stickers', 'tag', 'keychain'];
    $sets = [];
    $params = ['player_id' => $player_id, 'server_id' => $server_id, 'team' => $team, 'weapon_index' => $weapon_index];
    foreach ($data as $col => $val) {
      if (!in_array($col, $allowed, true)) continue;
      $sets[] = "`{$col}` = :{$col}";
      $params[$col] = $val;
    }
    if (empty($sets)) return false;
    return $this->Db->query('Skins', $this->DID, $this->UID, "UPDATE `sc_skins` SET " . implode(', ', $sets) . " WHERE `player_id` = :player_id AND `server_id` = :server_id AND `team` = :team AND `weapon_index` = :weapon_index;", $params);
  }

  public function deleteSkin(string $player_id, int $server_id, string $team, int $weapon_index)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `sc_skins` WHERE `player_id` = :player_id AND `server_id` = :server_id AND `team` = :team AND `weapon_index` = :weapon_index;", ['player_id' => $player_id, 'server_id' => $server_id, 'team' => $team, 'weapon_index' => $weapon_index]);
  }

  public function deleteSkinsBatch(string $player_id, int $server_id, array $pairs): void
  {
    if (empty($pairs)) return;
    $conditions = [];
    $params = ['player_id' => $player_id, 'server_id' => $server_id];
    foreach ($pairs as $i => $pair) {
      $tk = 'team' . $i;
      $wk = 'wi' . $i;
      $conditions[] = "(`team` = :{$tk} AND `weapon_index` = :{$wk})";
      $params[$tk] = (int)$pair['team'];
      $params[$wk] = (int)$pair['weapon_index'];
    }
    $sql = "DELETE FROM `sc_skins` WHERE `player_id` = :player_id AND `server_id` = :server_id AND (" . implode(' OR ', $conditions) . ");";
    $this->Db->query('Skins', $this->DID, $this->UID, $sql, $params);
  }

  public function deleteSkinsBatchByServer(string $player_id, array $rows): void
  {
    if (empty($rows)) return;
    $conditions = [];
    $params = ['player_id' => $player_id];
    foreach ($rows as $i => $row) {
      $sk = 'sid' . $i;
      $tk = 'team' . $i;
      $wk = 'wi' . $i;
      $conditions[] = "(`server_id` = :{$sk} AND `team` = :{$tk} AND `weapon_index` = :{$wk})";
      $params[$sk] = (int)$row['server_id'];
      $params[$tk] = (int)$row['team'];
      $params[$wk] = (int)$row['weapon_index'];
    }
    $sql = "DELETE FROM `sc_skins` WHERE `player_id` = :player_id AND (" . implode(' OR ', $conditions) . ");";
    $this->Db->query('Skins', $this->DID, $this->UID, $sql, $params);
  }

  public function deleteAllSkins(string $player_id)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `sc_skins` WHERE `player_id` = :player_id;", ['player_id' => $player_id]);
  }

  public function deleteAllItems(string $player_id)
  {
    return $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `sc_items` WHERE `player_id` = :player_id;", ['player_id' => $player_id]);
  }

  public function bulkInsertSkins(string $player_id, array $skins): void
  {
    if (empty($skins)) return;
    $placeholders = [];
    $params = [];
    foreach ($skins as $s) {
      $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
      $params[] = $player_id;
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
      "INSERT INTO `sc_skins` (`player_id`, `server_id`, `team`, `weapon_index`, `skin`, `stattrack`, `stattrack_count`, `stickers`, `keychain`, `tag`) VALUES " . implode(', ', $placeholders) . ";",
      $params
    );
  }

  public function bulkInsertItems(string $player_id, array $items): void
  {
    if (empty($items)) return;
    $placeholders = [];
    $params = [];
    foreach ($items as $it) {
      $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?)';
      $params[] = $player_id;
      $params[] = (int)$it['server_id'];
      $params[] = (int)$it['team'];
      $params[] = (int)($it['agent'] ?? 0);
      $params[] = (int)($it['music'] ?? 0);
      $params[] = (int)($it['coin'] ?? 0);
      $params[] = (int)($it['knife'] ?? 0);
      $params[] = (int)($it['glove'] ?? 0);
    }
    $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "INSERT INTO `sc_items` (`player_id`, `server_id`, `team`, `agent`, `music`, `coin`, `knife`, `glove`) VALUES " . implode(', ', $placeholders) . ";",
      $params
    );
  }

  public function getSteamIdByPlayerId(string $player_id): ?string
  {
    $row = $this->Db->query('Skins', $this->DID, $this->UID, "SELECT `steamid` FROM `sc_player` WHERE `id` = :player_id;", ['player_id' => $player_id]);
    return $row['steamid'] ?? null;
  }

  public function getPlayerIdBySteamId(string $steamid): ?int
  {
    $row = $this->Db->query('Skins', $this->DID, $this->UID, "SELECT `id` FROM `sc_player` WHERE `steamid` = :steamid;", ['steamid' => $steamid]);
    return isset($row['id']) ? (int)$row['id'] : null;
  }
}
