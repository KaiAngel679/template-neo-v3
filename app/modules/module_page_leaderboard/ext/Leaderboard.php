<?php

namespace app\modules\module_page_leaderboard\ext;

class Leaderboard
{
  public $Db;
  public $General;
  public $Translate;
  public $Modules;
  public $Router;
  public $db_data;

  public function __construct($Db, $General, $Translate, $Modules, $Router)
  {
    $this->Db = $Db;
    $this->General = $General;
    $this->Translate = $Translate;
    $this->Modules = $Modules;
    $this->Router = $Router;
    $this->db_data = $this->DatabaseData();
  }

  private function DatabaseData()
  {
    $res_data = [];
    if (!empty($this->Db->db_data['LevelsRanks'])) {
      for ($d = 0; $d < $this->Db->table_count['LevelsRanks']; $d++) {
        $res_data[] =
          [
            'statistics'    => 'LevelsRanks',
            'name_servers'  => $this->Db->db_data['LevelsRanks'][$d]['name'],
            'mod'           => $this->Db->db_data['LevelsRanks'][$d]['mod'],
            'USER_ID'       => $this->Db->db_data['LevelsRanks'][$d]['USER_ID'],
            'data_db'       => $this->Db->db_data['LevelsRanks'][$d]['DB_num'],
            'data_servers'  => $this->Db->db_data['LevelsRanks'][$d]['Table'],
            'ranks_pack'    => $this->Db->db_data['LevelsRanks'][$d]['ranks_pack'],
          ];
      }
    }
    return $res_data;
  }

  public function getUserStats($server)
  {
    if (empty($_SESSION['steamid32'])) {
      return [];
    }
    $sql = "SELECT 
          s.`name`, s.`rank`, s.`steam`, s.`playtime`, s.`value`, s.`kills`, s.`assists`, s.`lastconnect`, s.`headshots`, s.`deaths`,
          COALESCE(TRUNCATE(s.`kills` / NULLIF(s.`deaths`, 0), 2), 0) AS `kd`,
          (SELECT COUNT(1) FROM {$this->db_data[$server]['data_servers']} t WHERE t.`value` >= s.`value` AND t.`lastconnect` > 0) AS `top`
        FROM {$this->db_data[$server]['data_servers']} s
        WHERE s.`steam` = :steam
        LIMIT 1";

    $user = $this->Db->query($this->db_data[$server]['statistics'], $this->db_data[$server]['USER_ID'], $this->db_data[$server]['data_db'], $sql, ["steam" => $_SESSION['steamid32']]);
    return $user;
  }

  private function getAllListPaginated(int $page, int $server, int $filter, int $limit = 20)
  {
    $page_min = ($page - 1) * $limit;
    switch ($filter) {
      case 1:
        $filter = 'kills';
        break;
      case 2:
        $filter = 'deaths';
        break;
      case 3:
        $filter = 'kd';
        break;
      case 4:
        $filter = 'headshots';
        break;
      case 5:
        $filter = 'playtime';
        break;
      case 6:
        $filter = 'lastconnect';
        break;
      default:
        $filter = 'value';
    }
    $page_max = ceil($this->Db->queryNum($this->db_data[$server]['statistics'], $this->db_data[$server]['USER_ID'], $this->db_data[$server]['data_db'], "SELECT COUNT(*) FROM " . $this->db_data[$server]['data_servers'] . " WHERE `lastconnect` > 0")[0] / $limit);
    $res = $this->Db->queryAll($this->db_data[$server]['statistics'], $this->db_data[$server]['USER_ID'], $this->db_data[$server]['data_db'], "SELECT *, `name`, `rank`, `steam`, `playtime`, `value`, `kills`, `assists`, `lastconnect`, `headshots`, `deaths`, CASE WHEN `deaths` = 0 THEN `deaths` = 1 END, TRUNCATE( `kills`/`deaths`, 2 ) AS `kd` FROM " . $this->db_data[$server]['data_servers'] . " WHERE `lastconnect` > 0 ORDER BY " . $filter . " DESC LIMIT " . ($page_min) . "," . $limit . "");
    return [
      'data' => $res,
      'ranks_pack' => $this->db_data[$server]['ranks_pack'],
      'page_max' => $page_max
    ];
  }

  public function getServer($server)
  {
    $servers = $this->General->server_list;
    $db_info = $this->db_data[$server]['statistics'] . ';' . $this->db_data[$server]['USER_ID'] . ';' . $this->db_data[$server]['data_db'] . ';' . $this->db_data[$server]['data_servers'];
    foreach ($servers as $server) {
      if ($server['server_stats'] == $db_info) {
        return $server;
      }
    }
    return [];
  }

  private function getBans(array $steams, string $ids32, int $sb_id, string $game)
  {
    $bans = [];
    if ($game == 'cs2') {
      if (!empty($this->Db->db_data['AdminSystem'])) {
        $bans = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT `created`, `steamid`, `expires`, `unpunish_admin_id` FROM `as_punishments` WHERE `steamid` IN (" . implode(', ', $steams) . ") AND `unpunish_admin_id` IS NULL AND (`expires` = 0 OR `expires` >= UNIX_TIMESTAMP()) AND (`server_id` = -1 OR  `server_id` = :sb_id) AND `punish_type` = 0 order by `created` desc", ['sb_id' => $sb_id]);
      } elseif (!empty($this->Db->db_data['IksAdmin'])) {
        $bans = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT `created`, `sid` as `steamid`, `end`, `Unbanned` FROM `iks_bans` WHERE `sid` IN (" . implode(', ', $steams) . ") AND (`server_id` IS NULL OR  `server_id` = :sb_id) AND `Unbanned` = 0 AND (`end` = 0 OR `end` >= UNIX_TIMESTAMP()) order by `created` desc", ['sb_id' => $sb_id]);
      } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
        $bans = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT `created_at`, `steam_id` as `steamid`, `end_at`, `unbanned_by` FROM `iks_bans` WHERE `steam_id` IN (" . implode(', ', $steams) . ") AND (`server_id` IS NULL OR  `server_id` = :sb_id) AND `unbanned_by` IS NULL AND (`end_at` = 0 OR `end_at` >= UNIX_TIMESTAMP()) order by `created_at` desc", ['sb_id' => $sb_id]);
      }
    } elseif ($game == 'csgo') {
      if (!empty($this->Db->db_data['SourceBans'])) {
        $bans = $this->Db->queryAll('SourceBans', 0, 0, "SELECT `created`, `authid` as `steamid`, `ends`, `RemovedBy` FROM `sb_bans` WHERE SUBSTRING(authid, 9) IN (" . $ids32 . ") AND (`sid` = :sb_id OR `sid` = 0) AND `RemovedBy` IS NULL AND (`length` = 0 OR `ends` >= UNIX_TIMESTAMP()) order by `created` desc", ['sb_id' => $sb_id]);
      }
    }
    return $bans;
  }

  private function getVips($data, $id, $ids)
  {
    if (!empty($this->Db->db_data['Vips'])) {
      return $this->Db->queryAll($data[0], $data[1], $data[2], "SELECT * FROM `" . $data[3] . "users` WHERE `account_id` IN (" . $ids . ") AND `sid` = '" . $id . "'");
    } else {
      return [];
    }
  }

  private function getAdmins($data, string $id, string $ids, string $ids32, string $game)
  {
    if ($game == 'cs2') {
      if (!empty($this->Db->db_data['AdminSystem']) && $data[0] == 'AdminSystem'):
        return $this->Db->queryAll($data[0], $data[1], $data[2], "SELECT 
                a.steamid,
                GROUP_CONCAT(DISTINCT g.name ORDER BY g.immunity DESC SEPARATOR ', ') AS group_names
            FROM 
                as_admins a
            JOIN 
                as_admins_servers s ON a.id = s.admin_id
            JOIN 
                as_groups g ON s.group_id = g.id
            WHERE 
                a.steamid IN (" . $ids . ")
                AND (s.expires > UNIX_TIMESTAMP() OR s.expires = 0)
                AND (s.server_id = '" . $id . "' OR s.server_id = -1)
            GROUP BY  a.steamid ORDER BY MAX(g.immunity) DESC;");
      elseif (!empty($this->Db->db_data['IksAdminNew']) && $data[0] == 'IksAdminNew'):
        return $this->Db->queryAll($data[0], $data[1], $data[2], "SELECT 
                    a.steam_id AS steamid,
                    GROUP_CONCAT(DISTINCT g.name ORDER BY g.immunity DESC SEPARATOR ', ') AS group_names,
                    s.server_id AS server
                FROM 
                    iks_admins a
                JOIN 
                    iks_admin_to_server s ON a.id = s.admin_id
                JOIN 
                    iks_groups g ON a.group_id = g.id
                WHERE
                    a.steam_id IN (" . $ids . ")
                    AND a.is_disabled = 0
                    AND (s.server_id = '" . $id . "' OR s.server_id IS NULL)
                    AND (a.end_at > UNIX_TIMESTAMP() OR a.end_at IS NULL)
                GROUP BY a.steam_id, s.server_id;");
      elseif (!empty($this->Db->db_data['IksAdmin']) && $data[0] == 'IksAdmin'):
        return $this->Db->queryAll($data[0], $data[1], $data[2], "SELECT 
                    a.sid AS steamid,
                    GROUP_CONCAT(DISTINCT g.name ORDER BY g.immunity DESC SEPARATOR ', ') AS group_names,
                    GROUP_CONCAT(DISTINCT a.server_id SEPARATOR ', ') AS servers
                FROM 
                    iks_admins a
                JOIN 
                    iks_groups g ON a.group_id = g.id
                WHERE 
                    a.sid IN (" . $ids . ")
                    AND (a.end = 0 OR a.end > UNIX_TIMESTAMP())
                    AND (
                        a.server_id LIKE '%" . $id . "%' 
                        OR a.server_id = ''
                    )
                GROUP BY a.sid;");
      endif;
    } elseif ($game == 'csgo') {
      if (!empty($this->Db->db_data['SourceBans']) && $data[0] == 'SourceBans'):
        return $this->Db->queryAll($data[0], $data[1], $data[2], "SELECT 
                a.authid as steamid,
                a.srv_group AS group_names
            FROM 
                sb_admins a
            JOIN 
                sb_admins_servers_groups s ON a.aid = s.admin_id
            WHERE 
                SUBSTRING(a.authid, 9) IN ({$ids32})
                AND (a.expired > UNIX_TIMESTAMP() OR a.expired = 0)
                AND (s.server_id = '{$id}' OR s.server_id = -1)
            GROUP BY  a.authid ORDER BY MAX(a.immunity) DESC;");
      endif;
    } else {
      return [];
    }
  }

  public function Render(int $page, int $server, int $filter, bool $clear_banned = false, int $limit = 20)
  {
    $limit = 13;
    $array = $this->getAllListPaginated($page, $server, $filter, $limit);
    $steams64 = [];
    $steams32 = [];
    $steams3 = [];
    $data = [];
    foreach ($array['data'] as $index => $key) {
      $steam32 = $key["steam"];
      $steam64 = con_steam32to64($key["steam"]);
      $steam3 = con_steam64to3_int($steam64);
      $avatar = $this->General->getAvatar($steam64, 3);
      $name = empty($this->General->checkName($steam64)) ? action_text_clear($key['name']) : action_text_clear($this->General->checkName($steam64));
      $faceit = $this->General->getFaceit($steam64, 'level_img');
      $rank = getRankImage($key['rank'] ?: 0, $key['value'] ?: 0, $array['ranks_pack'], 'leaderboard__table-rank');
      $steams64[] = $steam64;
      $steams32[] = $this->get_steam_32_short($steam32);
      $steams3[] = $steam3;
      $data[$index] = [
        'place'         => ($limit * ($page - 1)) + $index + 1,
        'name'          => $name,
        'rank'          => $rank,
        'steamid32'     => $steam32,
        'steamid'       => $steam64,
        'avatar'        => $avatar,
        'faceit'        => $faceit,
        'playtime'      => $this->Modules->time_to_hours($key['playtime']),
        'value'         => $key['value'],
        'kills'         => $key['kills'],
        'assists'       => $key['assists'],
        'lastconnect'   => $key['lastconnect'],
        'headshots'     => $key['headshots'],
        'deaths'        => $key['deaths'],
        'kd'            => $key['kd'],
        'banned'        => false,
        'online'        => ($key['online'] != 0 || ($this->General->checkOnline($steam64) == 1)) ? 'display: block;' : 'display: none;',
        'top_class'     => $page == 1 && $index < 3 && $filter == 0 ? $index + 1  : '',
        'link'          => "/profiles/" . $steam64 . "/" . $server,
        'checked_avatar' => $this->General->checkAvatar($steam64),
        'checked_faceit' => $this->General->checkFaceit($steam64),
      ];
    }

    $server = $this->getServer($server);
    $vip_data = $this->getVips(explode(';', $server['server_vip']), $server['server_vip_id'], implode(', ', $steams3));
    $admin_data = $this->getAdmins(explode(';', $server['server_sb']), $server['server_sb_id'], implode(', ', $steams64), implode(', ', array_map(fn($s) => "'" . addslashes($s) . "'", $steams32)), $server['server_game']);
    $bans = $this->getBans($steams64, implode(', ', array_map(fn($s) => "'" . addslashes($s) . "'", $steams32)), $server['server_sb_id'], $server['server_game']);
    foreach ($data as $index => $key) {
      foreach ($vip_data as $vip) {
        if (con_steam3to64_int($vip['account_id']) == $key['steamid']) {
          $data[$index]['vip'] = true;
          $data[$index]['vip_group'] = $vip['group'];
        }
      }
      if ($server['server_game'] == "cs2") {
        foreach ($admin_data as $admin) {
          if ($admin['steamid'] == $key['steamid']) {
            $data[$index]['admin'] = true;
            $data[$index]['admin_groups'] = $admin['group_names'];
          }
        }
      } elseif ($server['server_game'] == "csgo") {
        foreach ($admin_data as $admin) {
          if ($this->get_steam_32_short($admin['steamid']) == $this->get_steam_32_short($key['steamid32'])) {
            $data[$index]['admin'] = true;
            $data[$index]['admin_groups'] = $admin['group_names'];
          }
        }
      }
      foreach ($bans as $ban) {
        if ($server['server_game'] == "cs2") {
          if ($ban['steamid'] == $key['steamid']) {
            $data[$index]['banned'] = true;
          }
        } elseif ($server['server_game'] == "csgo") {
          if ($this->get_steam_32_short($ban['steamid']) == $this->get_steam_32_short($key['steamid32'])) {
            $data[$index]['banned'] = true;
          }
        }
      }
    }
    if ($clear_banned == 'true') {
      $data = array_values(array_filter($data, fn($item) => !$item['banned']));
    }
    return [
      'list' => $data,
      'premier_ranks' => isset($this->General->arr_general['premier_ranks']) && $this->General->arr_general['premier_ranks'] ? true : false,
      'page_max' => $array['page_max']
    ];
  }

  public function RenderUserStats(int $server)
  {
    if (empty($_SESSION['steamid32'])) {
      return [];
    }
    $user = $this->getUserStats($server);
    $data = [
      'link' => '',
      'name' => $this->Translate->get_translate_module_phrase('module_page_leaderboard', '_guest'),
      'steamid' => $_SESSION['steamid64'],
      'avatar' => '/storage/cache/img/avatars/1_avatar.jpg',
      'rank' => getRankImage(0, 0, $this->db_data[$server]['ranks_pack'], 'leaderboard__my-stat-rank'),
      'place' => 0,
      'value' => 0,
      'kd' => 0,
      'kills' => 0,
      'deaths' => 0,
      'headshots' => 0,
      'premier_rank' => isset($this->General->arr_general['premier_ranks']) && $this->General->arr_general['premier_ranks'] ? true : false,
      'havent_play' => true,
    ];
    if (!empty($user)) {
      $steam64 = con_steam32to64($user["steam"]);
      $data['link'] = "/profiles/" . $steam64 . "/" . $server;
      $data['name'] = empty($this->General->checkName($steam64)) ? action_text_clear($user['name']) : action_text_clear($this->General->checkName($steam64));
      $data['steamid'] = $steam64;
      $data['avatar'] = $this->General->getAvatar($steam64, 3);
      $data['checked_avatar'] = $this->General->checkAvatar($steam64);
      $data['faceit'] = $this->General->getFaceit($steam64, 'level_img');
      $data['checked_faceit'] = $this->General->checkFaceit($steam64);
      $data['rank'] = getRankImage($user['rank'] ?: 0, $user['value'] ?: 0, $this->db_data[$server]['ranks_pack'], 'leaderboard__my-stat-rank');
      $data['place'] = $user['top'];
      $data['value'] = $user['value'];
      $data['kd'] = $user['kd'];
      $data['kills'] = $user['kills'];
      $data['deaths'] = $user['deaths'];
      $data['headshots'] = $user['headshots'];
      $data['havent_play'] = false;
    }
    return $data;
  }
  public function get_steam_32_short($steam_32)
  {
    $type = "/[0-9a-zA-Z_]{7}:([0-9]{1}):([0-9]+)/u";
    preg_match_all($type, $steam_32, $arr, PREG_SET_ORDER);
    if (!empty($arr[0][2])):
      return $arr[0][1] . ':' . $arr[0][2];
    else:
      return false;
    endif;
  }
}
