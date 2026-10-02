<?php

namespace app\modules\module_page_skinchanger\ext\Repositories\Plugins;

class WeaponPaintsRepository extends PisexRepository
{
  private const WP_TEAM_T  = 2;
  private const WP_TEAM_CT = 3;

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

  public function resolveIdentifier(string $steamid)
  {
    return $steamid;
  }

  private const KNIFE_NAME_TO_ID = [
    'weapon_knife_karambit' => 507,
    'weapon_knife_m9_bayonet' => 508,
    'weapon_knife_butterfly' => 515,
    'weapon_bayonet' => 500,
    'weapon_knife_tactical' => 509,
    'weapon_knife_stiletto' => 522,
    'weapon_knife_widowmaker' => 523,
    'weapon_knife_skeleton' => 525,
    'weapon_knife_ursus' => 519,
    'weapon_knife_css' => 503,
    'weapon_knife_push' => 516,
    'weapon_knife_survival_bowie' => 514,
    'weapon_knife_falchion' => 512,
    'weapon_knife_gypsy_jackknife' => 520,
    'weapon_knife_flip' => 505,
    'weapon_knife_cord' => 517,
    'weapon_knife_gut' => 506,
    'weapon_knife_outdoor' => 521,
    'weapon_knife_canis' => 518,
    'weapon_knife_kukri' => 526,
  ];

  private function convertTeamToWeaponPaints(int $team): int
  {
    return $team === 1 ? self::WP_TEAM_CT : self::WP_TEAM_T;
  }

  private function convertTeamFromWeaponPaints(int $wpTeam): int
  {
    return $wpTeam === self::WP_TEAM_CT ? 1 : 0;
  }

  private function knifeNameToId(string $name): int
  {
    return self::KNIFE_NAME_TO_ID[$name] ?? 0;
  }

  private function knifeIdToName(int $id): string
  {
    static $idToName = null;
    if ($idToName === null) {
      $idToName = array_flip(self::KNIFE_NAME_TO_ID);
    }
    return $idToName[$id] ?? '';
  }

  private function loadAgentsCache(): array
  {
    static $cache = null;
    if ($cache === null) {
      $jsonPath = MODULES . 'module_page_skinchanger/cache/agents/ru.json';
      $data = @file_get_contents($jsonPath);
      $cache = ['idToModel' => [], 'modelToId' => []];
      if ($data) {
        $agents = json_decode($data, true) ?: [];
        foreach ($agents as $agent) {
          if (isset($agent['id'], $agent['model'])) {
            $cache['idToModel'][(int)$agent['id']] = $agent['model'];
            $cache['modelToId'][$agent['model']] = (int)$agent['id'];
          }
        }
      }
    }
    return $cache;
  }

  private function agentIdToModel(string $value): string
  {
    if (!is_numeric($value)) {
      return (string)$value;
    }
    $cache = $this->loadAgentsCache();
    return $cache['idToModel'][(int)$value] ?? (string)$value;
  }

  private function agentModelToId(string $value): int
  {
    if (is_numeric($value)) {
      return (int)$value;
    }
    $cache = $this->loadAgentsCache();
    return $cache['modelToId'][(string)$value] ?? 0;
  }

  public function getPlayer(string $steamid)
  {
    return ['id' => $steamid, 'steamid' => $steamid, 'name' => ''];
  }

  public function getFirstServerId(): int
  {
    return 1;
  }

  private function convertStickersFromWeaponPaints(array $row): string
  {
    $ids = [];
    for ($i = 0; $i < 5; $i++) {
      $val = $row["weapon_sticker_{$i}"] ?? '0;0;0;0;0;0;0';
      $parts = explode(';', $val);
      $ids[] = $parts[0] ?? '0';
    }
    return implode(';', $ids);
  }

  private function convertKeychainFromWeaponPaints(string $keychain): string
  {
    if ($keychain === '' || $keychain === '0;0;0;0;0') return '';
    $parts = explode(';', $keychain);
    if (count($parts) >= 4) {
      return $parts[0] . ';' . $parts[1] . ';' . $parts[2] . ';' . $parts[3];
    }
    return $keychain;
  }

  private function convertStickersToWeaponPaints(string $stickers): array
  {
    $ids = explode(';', $stickers);
    while (count($ids) < 5) $ids[] = '0';
    $result = [];
    $schemas = [1, 2, 3, 4, 5];
    for ($i = 0; $i < 5; $i++) {
      $id = $ids[$i] ?? '0';
      $schema = $schemas[$i];
      $result["weapon_sticker_{$i}"] = "{$id};{$schema};0;0;0;1;0";
    }
    return $result;
  }

  private function convertKeychainToWeaponPaints(string $keychain, int $weaponIndex): string
  {
    if ($keychain === '') return '0;0;0;0;0';

    $parts = explode(';', $keychain);
    $keychainId = $parts[0] ?? '0';

    if ($keychainId === '0') return '0;0;0;0;0';

    $pos = self::KEYCHAIN_POSITIONS[$weaponIndex] ?? [8.0, 0.0, 3.0];
    return "{$keychainId};{$pos[0]};{$pos[1]};{$pos[2]};0";
  }

  private function mapWpSkinRow(array $row, string $steamid, int $server_id = 1, string $team = null): array
  {
    return [
      'id'              => null,
      'player_id'       => $steamid,
      'server_id'       => $server_id,
      'team'            => $team ?? $this->convertTeamFromWeaponPaints((int)$row['weapon_team']),
      'weapon_index'    => (int)$row['weapon_defindex'],
      'skin'            => $row['weapon_paint_id'] . ';' . $row['weapon_seed'] . ';' . $row['weapon_wear'],
      'stattrack'       => (int)$row['weapon_stattrak'],
      'stattrack_count' => (int)$row['weapon_stattrak_count'],
      'stickers'        => $this->convertStickersFromWeaponPaints($row),
      'keychain'        => $this->convertKeychainFromWeaponPaints($row['weapon_keychain'] ?? ''),
      'tag'             => $row['weapon_nametag'] ?? '',
    ];
  }

  public function getSkins(string $player_id)
  {
    $steamid = $player_id;

    $rows = $this->Db->queryAll(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT * FROM `wp_player_skins` WHERE `steamid` = :sid;",
      ['sid' => $steamid]
    );

    if (!$rows) return null;

    $result = [];
    foreach ($rows as $row) {
      $result[] = $this->mapWpSkinRow($row, $steamid);
    }
    return $result;
  }

  public function getSkin(string $player_id, int $server_id, string $team, int $weapon_index)
  {
    $steamid = $player_id;

    $row = $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "SELECT * FROM `wp_player_skins` WHERE `steamid` = :sid AND `weapon_team` = :team AND `weapon_defindex` = :wi;",
      ['sid' => $steamid, 'team' => $this->convertTeamToWeaponPaints($team), 'wi' => $weapon_index]
    );

    if (!$row) return null;
    return $this->mapWpSkinRow($row, $steamid, $server_id, $team);
  }

  public function insertSkinDefault(string $player_id, int $server_id, string $team, int $weapon_index)
  {
    $steamid = $player_id;

    return $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "INSERT IGNORE INTO `wp_player_skins`
        (`steamid`, `weapon_team`, `weapon_defindex`, `weapon_paint_id`, `weapon_wear`, `weapon_seed`,
          `weapon_nametag`, `weapon_stattrak`, `weapon_stattrak_count`,
          `weapon_sticker_0`, `weapon_sticker_1`, `weapon_sticker_2`, `weapon_sticker_3`, `weapon_sticker_4`,
          `weapon_keychain`)
        VALUES (:sid, :team, :wi, 0, 0.000001, 0, NULL, 0, 0,
          '0;0;0;0;0;0;0','0;0;0;0;0;0;0','0;0;0;0;0;0;0','0;0;0;0;0;0;0','0;0;0;0;0;0;0','0;0;0;0;0');",
      ['sid' => $steamid, 'team' => $this->convertTeamToWeaponPaints($team), 'wi' => $weapon_index]
    );
  }

  public function updateSkinData(string $player_id, int $server_id, string $team, int $weapon_index, array $data)
  {
    $steamid = $player_id;

    $wpTeam = $this->convertTeamToWeaponPaints($team);
    $sets   = [];
    $params = ['sid' => $steamid, 'team' => $wpTeam, 'wi' => $weapon_index];

    if (isset($data['skin'])) {
      $parts = explode(';', $data['skin']);
      $sets[] = '`weapon_paint_id` = :paint_id';
      $sets[] = '`weapon_seed` = :seed';
      $sets[] = '`weapon_wear` = :wear';
      $params['paint_id'] = (int)($parts[0] ?? 0);
      $params['seed']     = (int)($parts[1] ?? 0);
      $params['wear']     = (float)($parts[2] ?? 0.000001);
    }

    if (isset($data['stattrack'])) {
      $sets[] = '`weapon_stattrak` = :st';
      $params['st'] = (int)$data['stattrack'];
    }

    if (isset($data['stattrack_count'])) {
      $sets[] = '`weapon_stattrak_count` = :stc';
      $params['stc'] = (int)$data['stattrack_count'];
    }

    if (isset($data['stickers'])) {
      $cols = $this->convertStickersToWeaponPaints($data['stickers']);
      for ($i = 0; $i < 5; $i++) {
        $sets[] = "`weapon_sticker_{$i}` = :stk{$i}";
        $params["stk{$i}"] = $cols["weapon_sticker_{$i}"];
      }
    }

    if (isset($data['keychain'])) {
      $sets[] = '`weapon_keychain` = :kc';
      $params['kc'] = $this->convertKeychainToWeaponPaints($data['keychain'], $weapon_index);
    }

    if (isset($data['tag'])) {
      $sets[] = '`weapon_nametag` = :tag';
      $params['tag'] = $data['tag'] !== '' ? $data['tag'] : null;
    }

    if (empty($sets)) return false;

    return $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "UPDATE `wp_player_skins` SET " . implode(', ', $sets) .
        " WHERE `steamid` = :sid AND `weapon_team` = :team AND `weapon_defindex` = :wi;",
      $params
    );
  }

  public function deleteSkin(string $player_id, int $server_id, string $team, int $weapon_index)
  {
    $steamid = $player_id;

    return $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "DELETE FROM `wp_player_skins` WHERE `steamid` = :sid AND `weapon_team` = :team AND `weapon_defindex` = :wi;",
      ['sid' => $steamid, 'team' => $this->convertTeamToWeaponPaints($team), 'wi' => $weapon_index]
    );
  }

  public function deleteAllSkins(string $player_id)
  {
    $steamid = $player_id;

    return $this->Db->query(
      'Skins',
      $this->DID,
      $this->UID,
      "DELETE FROM `wp_player_skins` WHERE `steamid` = :sid;",
      ['sid' => $steamid]
    );
  }

  public function getItems(string $player_id)
  {
    $steamid = $player_id;

    $knives    = $this->Db->queryAll('Skins', $this->DID, $this->UID, "SELECT * FROM `wp_player_knife`  WHERE `steamid` = :sid;", ['sid' => $steamid]) ?: [];
    $gloves    = $this->Db->queryAll('Skins', $this->DID, $this->UID, "SELECT * FROM `wp_player_gloves` WHERE `steamid` = :sid;", ['sid' => $steamid]) ?: [];
    $agents    = $this->Db->query('Skins', $this->DID, $this->UID,    "SELECT * FROM `wp_player_agents` WHERE `steamid` = :sid;", ['sid' => $steamid]);
    $musicRows = $this->Db->queryAll('Skins', $this->DID, $this->UID, "SELECT * FROM `wp_player_music`  WHERE `steamid` = :sid;", ['sid' => $steamid]) ?: [];
    $pinRows   = $this->Db->queryAll('Skins', $this->DID, $this->UID, "SELECT * FROM `wp_player_pins`   WHERE `steamid` = :sid;", ['sid' => $steamid]) ?: [];

    if (!$knives && !$gloves && !$agents && !$musicRows && !$pinRows) return null;

    $teamData = [];

    foreach ($knives as $k) {
      $t = $this->convertTeamFromWeaponPaints((int)$k['weapon_team']);
      $teamData[$t]['knife'] = $this->knifeNameToId($k['knife']);
    }
    foreach ($gloves as $g) {
      $t = $this->convertTeamFromWeaponPaints((int)$g['weapon_team']);
      $teamData[$t]['glove'] = (int)$g['weapon_defindex'];
    }
    if ($agents) {
      if (!empty($agents['agent_ct'])) {
        $teamData[1]['agent'] = $this->agentModelToId($agents['agent_ct']);
      }
      if (!empty($agents['agent_t'])) {
        $teamData[0]['agent'] = $this->agentModelToId($agents['agent_t']);
      }
    }
    foreach ($musicRows as $m) {
      $t = $this->convertTeamFromWeaponPaints((int)$m['weapon_team']);
      $teamData[$t]['music'] = (int)$m['music_id'];
    }
    foreach ($pinRows as $p) {
      $t = $this->convertTeamFromWeaponPaints((int)$p['weapon_team']);
      $teamData[$t]['coin'] = (int)$p['id'];
    }

    if (empty($teamData)) return null;

    $result = [];
    foreach ($teamData as $team => $data) {
      $result[] = [
        'player_id' => $steamid,
        'server_id' => 1,
        'team'      => $team,
        'knife'     => $data['knife'] ?? 0,
        'glove'     => $data['glove'] ?? 0,
        'agent'     => $data['agent'] ?? 0,
        'music'     => $data['music'] ?? 0,
        'coin'      => $data['coin']  ?? 0,
      ];
    }
    return $result;
  }

  public function getItem(string $player_id, int $server_id, string $team)
  {
    $steamid = $player_id;

    $wpTeam = $this->convertTeamToWeaponPaints($team);

    $knife  = $this->Db->query('Skins', $this->DID, $this->UID, "SELECT `knife` FROM `wp_player_knife`            WHERE `steamid` = :sid AND `weapon_team` = :team;", ['sid' => $steamid, 'team' => $wpTeam]);
    $glove  = $this->Db->query('Skins', $this->DID, $this->UID, "SELECT `weapon_defindex` FROM `wp_player_gloves` WHERE `steamid` = :sid AND `weapon_team` = :team;", ['sid' => $steamid, 'team' => $wpTeam]);
    $agents = $this->Db->query('Skins', $this->DID, $this->UID, "SELECT * FROM `wp_player_agents`                 WHERE `steamid` = :sid;", ['sid' => $steamid]);
    $music  = $this->Db->query('Skins', $this->DID, $this->UID, "SELECT `music_id` FROM `wp_player_music`          WHERE `steamid` = :sid AND `weapon_team` = :team;", ['sid' => $steamid, 'team' => $wpTeam]);
    $pin    = $this->Db->query('Skins', $this->DID, $this->UID, "SELECT `id` FROM `wp_player_pins`                 WHERE `steamid` = :sid AND `weapon_team` = :team;", ['sid' => $steamid, 'team' => $wpTeam]);

    if (!$knife && !$glove && !$agents && !$music && !$pin) return null;

    $agentField = ($team == 1) ? 'agent_ct' : 'agent_t';
    $agentValue = 0;
    if ($agents && !empty($agents[$agentField])) {
      $agentValue = $this->agentModelToId($agents[$agentField]);
    }

    return [
      'player_id' => $steamid,
      'server_id' => $server_id,
      'team'      => $team,
      'knife'     => $knife  ? $this->knifeNameToId($knife['knife']) : 0,
      'glove'     => $glove  ? (int)$glove['weapon_defindex'] : 0,
      'agent'     => $agentValue,
      'music'     => $music  ? (int)$music['music_id'] : 0,
      'coin'      => $pin    ? (int)$pin['id'] : 0,
    ];
  }

  public function insertItem(string $player_id, int $server_id, string $team)
  {
    return true;
  }

  public function updateItemField(string $player_id, int $server_id, string $team, string $field, string $value)
  {
    $allowed = ['knife', 'glove', 'agent', 'music', 'coin'];
    if (!in_array($field, $allowed, true)) return false;

    $steamid = $player_id;

    $wpTeam = $this->convertTeamToWeaponPaints($team);

    switch ($field) {
      case 'knife':
        if ($value === 0 || $value === '0') {
          return $this->Db->query(
            'Skins',
            $this->DID,
            $this->UID,
            "DELETE FROM `wp_player_knife` WHERE `steamid` = :sid AND `weapon_team` = :team;",
            ['sid' => $steamid, 'team' => $wpTeam]
          );
        }
        $knifeName = $this->knifeIdToName((int)$value);
        if (!$knifeName) return false;
        return $this->Db->query(
          'Skins',
          $this->DID,
          $this->UID,
          "INSERT INTO `wp_player_knife` (`steamid`, `weapon_team`, `knife`) VALUES (:sid, :team, :val)
            ON DUPLICATE KEY UPDATE `knife` = :val2;",
          ['sid' => $steamid, 'team' => $wpTeam, 'val' => $knifeName, 'val2' => $knifeName]
        );

      case 'glove':
        if ($value === 0 || $value === '0') {
          return $this->Db->query(
            'Skins',
            $this->DID,
            $this->UID,
            "DELETE FROM `wp_player_gloves` WHERE `steamid` = :sid AND `weapon_team` = :team;",
            ['sid' => $steamid, 'team' => $wpTeam]
          );
        }
        return $this->Db->query(
          'Skins',
          $this->DID,
          $this->UID,
          "INSERT INTO `wp_player_gloves` (`steamid`, `weapon_team`, `weapon_defindex`) VALUES (:sid, :team, :val)
            ON DUPLICATE KEY UPDATE `weapon_defindex` = :val2;",
          ['sid' => $steamid, 'team' => $wpTeam, 'val' => (int)$value, 'val2' => (int)$value]
        );

      case 'agent':
        $col = ($team == 1) ? 'agent_ct' : 'agent_t';
        if ($value === 0 || $value === '0') {
          $sql = ($col === 'agent_ct')
            ? "UPDATE `wp_player_agents` SET `agent_ct` = NULL WHERE `steamid` = :sid;"
            : "UPDATE `wp_player_agents` SET `agent_t` = NULL WHERE `steamid` = :sid;";
          return $this->Db->query('Skins', $this->DID, $this->UID, $sql, ['sid' => $steamid]);
        }
        $agentModel = $this->agentIdToModel($value);
        $sql = ($col === 'agent_ct')
          ? "INSERT INTO `wp_player_agents` (`steamid`, `agent_ct`) VALUES (:sid, :val) ON DUPLICATE KEY UPDATE `agent_ct` = :val2;"
          : "INSERT INTO `wp_player_agents` (`steamid`, `agent_t`) VALUES (:sid, :val) ON DUPLICATE KEY UPDATE `agent_t` = :val2;";
        return $this->Db->query('Skins', $this->DID, $this->UID, $sql, ['sid' => $steamid, 'val' => $agentModel, 'val2' => $agentModel]);

      case 'music':
        if ($value === 0 || $value === '0') {
          return $this->Db->query(
            'Skins',
            $this->DID,
            $this->UID,
            "DELETE FROM `wp_player_music` WHERE `steamid` = :sid AND `weapon_team` = :team;",
            ['sid' => $steamid, 'team' => $wpTeam]
          );
        }
        return $this->Db->query(
          'Skins',
          $this->DID,
          $this->UID,
          "INSERT INTO `wp_player_music` (`steamid`, `weapon_team`, `music_id`) VALUES (:sid, :team, :val)
            ON DUPLICATE KEY UPDATE `music_id` = :val2;",
          ['sid' => $steamid, 'team' => $wpTeam, 'val' => (int)$value, 'val2' => (int)$value]
        );

      case 'coin':
        if ($value === 0 || $value === '0') {
          return $this->Db->query(
            'Skins',
            $this->DID,
            $this->UID,
            "DELETE FROM `wp_player_pins` WHERE `steamid` = :sid AND `weapon_team` = :team;",
            ['sid' => $steamid, 'team' => $wpTeam]
          );
        }
        return $this->Db->query(
          'Skins',
          $this->DID,
          $this->UID,
          "INSERT INTO `wp_player_pins` (`steamid`, `weapon_team`, `id`) VALUES (:sid, :team, :val)
            ON DUPLICATE KEY UPDATE `id` = :val2;",
          ['sid' => $steamid, 'team' => $wpTeam, 'val' => (int)$value, 'val2' => (int)$value]
        );
    }

    return false;
  }

  public function deleteAllItems(string $player_id)
  {
    $steamid = $player_id;

    $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `wp_player_knife`  WHERE `steamid` = :sid;", ['sid' => $steamid]);
    $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `wp_player_gloves` WHERE `steamid` = :sid;", ['sid' => $steamid]);
    $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `wp_player_agents` WHERE `steamid` = :sid;", ['sid' => $steamid]);
    $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `wp_player_music`  WHERE `steamid` = :sid;", ['sid' => $steamid]);
    return $this->Db->query('Skins', $this->DID, $this->UID, "DELETE FROM `wp_player_pins`   WHERE `steamid` = :sid;", ['sid' => $steamid]);
  }

  public function bulkInsertSkins(string $player_id, array $skins): void
  {
    if (empty($skins)) return;
    $steamid = $player_id;

    foreach ($skins as $s) {
      $wpTeam = $this->convertTeamToWeaponPaints((int)$s['team']);
      $weaponIndex = (int)$s['weapon_index'];
      $parts = explode(';', $s['skin'] ?? '0;0;0.0');
      $paintId = (int)($parts[0] ?? 0);
      $seed = (int)($parts[1] ?? 0);
      $wear = (float)($parts[2] ?? 0.000001);
      $stickers = $this->convertStickersToWeaponPaints($s['stickers'] ?? '0;0;0;0');
      $keychain = $this->convertKeychainToWeaponPaints($s['keychain'] ?? '', $weaponIndex);
      $nametag = ($s['tag'] ?? '') !== '' ? $s['tag'] : null;
      $stattrack = (int)($s['stattrack'] ?? 0);
      $stattrackCount = (int)($s['stattrack_count'] ?? 0);

      $this->Db->query(
        'Skins',
        $this->DID,
        $this->UID,
        "INSERT INTO `wp_player_skins`
          (`steamid`, `weapon_team`, `weapon_defindex`, `weapon_paint_id`, `weapon_wear`, `weapon_seed`,
            `weapon_nametag`, `weapon_stattrak`, `weapon_stattrak_count`,
            `weapon_sticker_0`, `weapon_sticker_1`, `weapon_sticker_2`, `weapon_sticker_3`, `weapon_sticker_4`,
            `weapon_keychain`)
          VALUES (:sid, :team, :wi, :paint, :wear, :seed, :tag, :st, :stc, :stk0, :stk1, :stk2, :stk3, :stk4, :kc)
          ON DUPLICATE KEY UPDATE
            `weapon_paint_id` = VALUES(`weapon_paint_id`),
            `weapon_wear` = VALUES(`weapon_wear`),
            `weapon_seed` = VALUES(`weapon_seed`),
            `weapon_nametag` = VALUES(`weapon_nametag`),
            `weapon_stattrak` = VALUES(`weapon_stattrak`),
            `weapon_stattrak_count` = VALUES(`weapon_stattrak_count`),
            `weapon_sticker_0` = VALUES(`weapon_sticker_0`),
            `weapon_sticker_1` = VALUES(`weapon_sticker_1`),
            `weapon_sticker_2` = VALUES(`weapon_sticker_2`),
            `weapon_sticker_3` = VALUES(`weapon_sticker_3`),
            `weapon_sticker_4` = VALUES(`weapon_sticker_4`),
            `weapon_keychain` = VALUES(`weapon_keychain`);",
        [
          'sid' => $steamid,
          'team' => $wpTeam,
          'wi' => $weaponIndex,
          'paint' => $paintId,
          'wear' => $wear,
          'seed' => $seed,
          'tag' => $nametag,
          'st' => $stattrack,
          'stc' => $stattrackCount,
          'stk0' => $stickers['weapon_sticker_0'],
          'stk1' => $stickers['weapon_sticker_1'],
          'stk2' => $stickers['weapon_sticker_2'],
          'stk3' => $stickers['weapon_sticker_3'],
          'stk4' => $stickers['weapon_sticker_4'],
          'kc' => $keychain,
        ]
      );
    }
  }

  public function bulkInsertItems(string $player_id, array $items): void
  {
    if (empty($items)) return;
    $steamid = $player_id;

    foreach ($items as $it) {
      $wpTeam = $this->convertTeamToWeaponPaints((int)$it['team']);
      $team = (int)$it['team'];

      $knife = (int)($it['knife'] ?? 0);
      if ($knife > 0) {
        $knifeName = $this->knifeIdToName($knife);
        if ($knifeName) {
          $this->Db->query(
            'Skins',
            $this->DID,
            $this->UID,
            "INSERT INTO `wp_player_knife` (`steamid`, `weapon_team`, `knife`) VALUES (:sid, :team, :val)
              ON DUPLICATE KEY UPDATE `knife` = VALUES(`knife`);",
            ['sid' => $steamid, 'team' => $wpTeam, 'val' => $knifeName]
          );
        }
      }

      $glove = (int)($it['glove'] ?? 0);
      if ($glove > 0) {
        $this->Db->query(
          'Skins',
          $this->DID,
          $this->UID,
          "INSERT INTO `wp_player_gloves` (`steamid`, `weapon_team`, `weapon_defindex`) VALUES (:sid, :team, :val)
            ON DUPLICATE KEY UPDATE `weapon_defindex` = VALUES(`weapon_defindex`);",
          ['sid' => $steamid, 'team' => $wpTeam, 'val' => $glove]
        );
      }

      $agent = $it['agent'] ?? 0;
      if ($agent !== 0 && $agent !== '0' && $agent !== '') {
        $agentModel = $this->agentIdToModel($agent);
        $col = ($team == 1) ? 'agent_ct' : 'agent_t';
        $sql = ($col === 'agent_ct')
          ? "INSERT INTO `wp_player_agents` (`steamid`, `agent_ct`) VALUES (:sid, :val) ON DUPLICATE KEY UPDATE `agent_ct` = VALUES(`agent_ct`);"
          : "INSERT INTO `wp_player_agents` (`steamid`, `agent_t`) VALUES (:sid, :val) ON DUPLICATE KEY UPDATE `agent_t` = VALUES(`agent_t`);";
        $this->Db->query('Skins', $this->DID, $this->UID, $sql, ['sid' => $steamid, 'val' => $agentModel]);
      }

      $music = (int)($it['music'] ?? 0);
      if ($music > 0) {
        $this->Db->query(
          'Skins',
          $this->DID,
          $this->UID,
          "INSERT INTO `wp_player_music` (`steamid`, `weapon_team`, `music_id`) VALUES (:sid, :team, :val)
            ON DUPLICATE KEY UPDATE `music_id` = VALUES(`music_id`);",
          ['sid' => $steamid, 'team' => $wpTeam, 'val' => $music]
        );
      }

      $coin = (int)($it['coin'] ?? 0);
      if ($coin > 0) {
        $this->Db->query(
          'Skins',
          $this->DID,
          $this->UID,
          "INSERT INTO `wp_player_pins` (`steamid`, `weapon_team`, `id`) VALUES (:sid, :team, :val)
            ON DUPLICATE KEY UPDATE `id` = VALUES(`id`);",
          ['sid' => $steamid, 'team' => $wpTeam, 'val' => $coin]
        );
      }
    }
  }

  public function deleteSkinsBatch(string $player_id, int $server_id, array $pairs): void
  {
    if (empty($pairs)) return;
    $steamid = $player_id;

    foreach ($pairs as $pair) {
      $wpTeam = $this->convertTeamToWeaponPaints((int)$pair['team']);
      $weaponIndex = (int)$pair['weapon_index'];
      $this->Db->query(
        'Skins',
        $this->DID,
        $this->UID,
        "DELETE FROM `wp_player_skins` WHERE `steamid` = :sid AND `weapon_team` = :team AND `weapon_defindex` = :wi;",
        ['sid' => $steamid, 'team' => $wpTeam, 'wi' => $weaponIndex]
      );
    }
  }

  public function deleteSkinsBatchByServer(string $player_id, array $rows): void
  {
    if (empty($rows)) return;
    $steamid = $player_id;

    foreach ($rows as $row) {
      $wpTeam = $this->convertTeamToWeaponPaints((int)$row['team']);
      $weaponIndex = (int)$row['weapon_index'];
      $this->Db->query(
        'Skins',
        $this->DID,
        $this->UID,
        "DELETE FROM `wp_player_skins` WHERE `steamid` = :sid AND `weapon_team` = :team AND `weapon_defindex` = :wi;",
        ['sid' => $steamid, 'team' => $wpTeam, 'wi' => $weaponIndex]
      );
    }
  }
}
