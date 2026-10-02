<?php


namespace app\modules\module_page_punishment\ext;

use app\modules\module_page_punishment\ext\Punishment;

class Search extends Punishment
{
  protected $Db, $General, $Translate, $Modules, $server_id, $game;

  public function __construct($Db, $General, $Modules, $Translate, $server_id, $game)
  {
    $this->Db = $Db;
    $this->General = $General;
    $this->Translate = $Translate;
    $this->Modules = $Modules;
    $this->server_id = $server_id;
    $this->game = $game;
  }

  public function Steam64_Search($steam64)
  {
    switch (true):
      case (preg_match('/^(7656119)([0-9]{10})/', $steam64)):
        return $steam64;
      case (preg_match('/^STEAM_[01]:[01]:[0-9]{2,12}$/', $steam64)):
        return con_steam32to64($steam64);
      case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/', $steam64)):
        $search_id = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/", '$3', $steam64), "/");
        $getsearch = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key={$this->General->arr_general['web_key']}&vanityurl={$search_id}"), true)['response']['steamid'];
        return $getsearch;
      case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/', $steam64)):
        $search_steam = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/", '$3', $steam64), "/");
        return $search_steam;
      case (preg_match('/^\[U:(.*)\:(.*)\]/', $steam64)):
        return con_steam3to64_int(str_replace(array('[U:1:', '[U:0:', ']'), '', $steam64));
      default:
        return $steam64;
    endswitch;
  }

  public function Steam32_Search($steam32)
  {
    switch (true):
      case (preg_match('/^(7656119)([0-9]{10})/', $steam32)):
        return substr(con_steam64to32($steam32), 8);
      case (preg_match('/^STEAM_[01]:[01]:[0-9]{2,12}$/', $steam32)):
        return substr($steam32, 8);
      case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/', $steam32)):
        $search_id = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/", '$3', $steam32), "/");
        $getsearch = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key={$this->General->arr_general['web_key']}&vanityurl={$search_id}"), true)['response']['steamid'];
        return $getsearch;
      case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/', $steam32)):
        $search_steam = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/", '$3', $steam32), "/");
        return $search_steam;
      case (preg_match('/^\[U:(.*)\:(.*)\]/', $steam32)):
        return substr(con_steam3to32_int(str_replace(array('[U:1:', '[U:0:', ']'), '', $steam32)), 8);
      default:
        return substr($steam32, 8);
    endswitch;
  }

  public function getAdminFromAdminSystem($id)
  {
    return $this->Db->query('AdminSystem', 0, 0, "SELECT * FROM `as_admins` WHERE `id` = :id", ['id' => $id]);
  }

  public function SearchPost($search_ban = "", $search_mute = "", $search_admin = "")
  {

    if (!empty($search_ban)) {

      if ($this->game == 'cs2') {
        $result = $this->Steam64_Search($search_ban);
        if (!empty($this->Db->db_data['AdminSystem'])) {
          if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
            $query = "SELECT *, 
              (SELECT `name` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_name`, 
              (SELECT `steamid` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_steamid` 
            FROM `as_punishments`
            WHERE `punish_type` = 0 AND `steamid` LIKE :result 
              OR `name` LIKE :result 
              OR `ip` LIKE :result 
              AND (`as_punishments`.`server_id` = :server_id OR `as_punishments`.`server_id` = '-1')
            LIMIT 20";
          } else {
            $query = "SELECT *, 
              (SELECT `name` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_name`, 
              (SELECT `steamid` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_steamid` 
            FROM `as_punishments`
            WHERE `punish_type` = 0 AND `steamid` LIKE :result 
              OR `name` LIKE :result 
              OR `ip` LIKE :result
            LIMIT 20";
          }
          $params = ["result" => "%{$result}%", 'server_id' => $this->server_id];
          $search = $this->Db->queryAll('AdminSystem', 0, 0, $query, $params);
        } elseif (!empty($this->Db->db_data['IksAdmin'])) {
          $query = "SELECT * FROM `iks_bans` WHERE `sid` LIKE :result OR `name` LIKE :result OR `ip` LIKE :result OR `adminsid` LIKE :result LIMIT 20";
          $params = ["result" => "%{$result}%"];
          $search = $this->Db->queryAll('IksAdmin', 0, 0, $query, $params);
        } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
          if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
            $query = "SELECT *, 
                (SELECT `name` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_name`, 
                (SELECT `steam_id` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_steamid` 
              FROM `iks_bans` 
              WHERE `steam_id` LIKE :result 
                OR `name` LIKE :result 
                OR `ip` LIKE :result 
                AND (`iks_bans`.`server_id` = :server_id OR `iks_bans`.`server_id` IS NULL)
              LIMIT 20";
          } else {
            $query = "SELECT *, 
                (SELECT `name` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_name`, 
                (SELECT `steam_id` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_steamid` 
              FROM `iks_bans` 
              WHERE `steam_id` LIKE :result 
                OR `name` LIKE :result 
                OR `ip` LIKE :result 
              LIMIT 20";
          }
          $params = ["result" => "%{$result}%", 'server_id' => $this->server_id];
          $search = $this->Db->queryAll('IksAdminNew', 0, 0, $query, $params);
        }
      } elseif ($this->game == 'csgo') {
        $result = $this->Steam32_Search($search_ban);
        if (!empty($this->Db->db_data['SourceBans'])) {
          if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
            $query = "SELECT *, 
              (SELECT `user` as `name` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_bans`.`aid`) AS `admin_name`, 
              (SELECT `authid` as `steamid` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_bans`.`aid`) AS `admin_steamid` 
            FROM `sb_bans`
            WHERE `authid` LIKE :result 
              OR `name` LIKE :result 
              OR `ip` LIKE :result 
              AND (`sb_bans`.`server_id` = :server_id OR `sb_bans`.`server_id` = '0')
            LIMIT 20";
          } else {
            $query = "SELECT *, 
              (SELECT `user` as `name` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_bans`.`aid`) AS `admin_name`, 
              (SELECT `authid` as `steamid` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_bans`.`aid`) AS `admin_steamid` 
            FROM `sb_bans`
            WHERE `authid` LIKE :result 
              OR `name` LIKE :result 
              OR `ip` LIKE :result
            LIMIT 20";
          }
          $params = ["result" => "%{$result}%", 'server_id' => $this->server_id];
          $search = $this->Db->queryAll('SourceBans', 0, 0, $query, $params);
        }
      }
    } elseif (!empty($search_mute)) {
      if ($this->game == 'cs2') {
        $result = $this->Steam64_Search($search_mute);
        if (!empty($this->Db->db_data['AdminSystem'])) {
          if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
            $query = "SELECT *, 
              (SELECT `name` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_name`, 
              (SELECT `steamid` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_steamid` 
            FROM `as_punishments`
            WHERE `punish_type` != 0 AND `steamid` LIKE :result 
              OR `name` LIKE :result 
              OR `ip` LIKE :result 
              AND (`as_punishments`.`server_id` = :server_id OR `as_punishments`.`server_id` = '-1')
            LIMIT 20";
          } else {
            $query = "SELECT *, 
              (SELECT `name` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_name`, 
              (SELECT `steamid` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_steamid` 
            FROM `as_punishments`
            WHERE `punish_type` != 0 AND `steamid` LIKE :result 
              OR `name` LIKE :result 
              OR `ip` LIKE :result
            LIMIT 20";
          }
          $params = ["result" => "%{$result}%", 'server_id' => $this->server_id];
          $search = $this->Db->queryAll('AdminSystem', 0, 0, $query, $params);
        } elseif (!empty($this->Db->db_data['IksAdmin'])) {
          $query_voice = "SELECT * FROM `iks_mutes` WHERE `sid` LIKE :result OR `name` LIKE :result OR `adminsid` LIKE :result LIMIT 20";
          $query_gag = "SELECT * FROM `iks_gags` WHERE `sid` LIKE :result OR `name` LIKE :result OR `adminsid` LIKE :result LIMIT 20";
          $params = ["result" => "%{$result}%"];
          $voice = $this->Db->queryAll('IksAdmin', 0, 0, $query_voice, $params);
          $gag = $this->Db->queryAll('IksAdmin', 0, 0, $query_gag, $params);
          $uniquemute = [];
          foreach (array_merge($voice, $gag) as $item) {
            $steamid = $item['sid'];
            $created = $item['created'];
            $key = $steamid . '_' . $created;

            if (!isset($uniquemute[$key])) {
              $uniquemute[$key] = $item;
            }
          }
          usort($uniquemute, function ($a, $b) {
            return $b['created'] - $a['created'];
          });
          $search = array_values(array_slice($uniquemute, 0, 20));
        } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
          if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
            $query = "SELECT *, 
              (SELECT `name` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_name`, 
              (SELECT `steam_id` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_steamid` 
            FROM `iks_comms` 
            WHERE `steam_id` LIKE :result 
              OR `name` LIKE :result 
              OR `ip` LIKE :result 
              AND (`iks_comms`.`server_id` = :server_id OR `iks_comms`.`server_id` IS NULL)
            LIMIT 20";
          } else {
            $query = "SELECT *, 
              (SELECT `name` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_name`, 
              (SELECT `steam_id` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_steamid` 
            FROM `iks_comms` 
            WHERE `steam_id` LIKE :result 
              OR `name` LIKE :result 
              OR `ip` LIKE :result 
            LIMIT 20";
          }
          $params = ["result" => "%{$result}%", 'server_id' => $this->server_id];
          $search = $this->Db->queryAll('IksAdminNew', 0, 0, $query, $params);
        }
      } elseif ($this->game == 'csgo') {
        $result = $this->Steam32_Search($search_mute);
        if (!empty($this->Db->db_data['SourceBans'])) {
          if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
            $query = "SELECT *, 
              (SELECT `user` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_comms`.`aid`) AS `admin_name`, 
              (SELECT `authid` as `steamid` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_comms`.`aid`) AS `admin_steamid` 
            FROM `sb_comms`
            WHERE `authid` LIKE :result 
              OR `name` LIKE :result
              AND (`sb_comms`.`server_id` = :server_id OR `sb_comms`.`server_id` = '0')
            LIMIT 20";
          } else {
            $query = "SELECT *, 
              (SELECT `user` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_comms`.`aid`) AS `admin_name`, 
              (SELECT `authid` as `steamid` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_comms`.`aid`) AS `admin_steamid` 
            FROM `sb_comms`
            WHERE `authid` LIKE :result 
              OR `name` LIKE :result
            LIMIT 20";
          }
          $params = ["result" => "%{$result}%", 'server_id' => $this->server_id];
          $search = $this->Db->queryAll('SourceBans', 0, 0, $query, $params);
        }
      }
    } elseif (!empty($search_admin)) {
      if ($this->game == 'cs2') {
        $result = $this->Steam64_Search($search_admin);
        if (!empty($this->Db->db_data['AdminSystem'])) {
          if ($this->server_id != 'all') {
            $query = "SELECT 
                        `as_admins`.`id` AS `admin_id`,
                        `as_admins`.`name`,
                        `as_admins`.`steamid`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`expires`) AS `end`,
                        `as_groups`.`name` as `group`,
                        `as_groups`.`immunity` as `immunity`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`server_id`) AS `server_id`
                    FROM 
                        `as_admins`
                    JOIN 
                        `as_admins_servers` 
                    ON 
                        `as_admins`.`id` = `as_admins_servers`.`admin_id`
                    LEFT JOIN
                        `as_groups` 
                    ON 
                        `as_admins_servers`.`group_id` = `as_groups`.`id`
                    WHERE 
                        `as_admins`.`steamid` LIKE :result OR `as_admins`.`name` LIKE :result
                        AND (`as_admins_servers`.`server_id` = :server_id OR `as_admins_servers`.`server_id` = '-1')
                        AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
                    GROUP BY
                        `as_admins`.`id`, `as_admins_servers`.`group_id`
                        ORDER BY `as_groups`.`immunity` DESC LIMIT 20";
          } else {
            $query = "SELECT 
                        `as_admins`.`id` AS `admin_id`,
                        `as_admins`.`name`,
                        `as_admins`.`steamid`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`expires`) AS `end`,
                        `as_groups`.`name` as `group`,
                        `as_groups`.`immunity` as `immunity`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`server_id`) AS `server_id`
                    FROM 
                        `as_admins`
                    JOIN 
                        `as_admins_servers` 
                    ON 
                        `as_admins`.`id` = `as_admins_servers`.`admin_id`
                    LEFT JOIN
                        `as_groups` 
                    ON 
                        `as_admins_servers`.`group_id` = `as_groups`.`id`
                    WHERE 
                        `as_admins`.`steamid` LIKE :result OR `as_admins`.`name` LIKE :result
                        AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
                    GROUP BY
                        `as_admins`.`id`, `as_admins_servers`.`group_id`
                        ORDER BY `as_groups`.`immunity` DESC LIMIT 20";
          }
          $params = ["result" => "%{$result}%", 'server_id' => $this->server_id];
          $search = $this->Db->queryAll('AdminSystem', 0, 0, $query, $params);
        } elseif (!empty($this->Db->db_data['IksAdmin'])) {
          if ($this->server_id != 'all') {
            $query = "SELECT 
                `iks_admins`.`id` as `admin_id`, 
                `iks_admins`.`server_id`, 
                `iks_admins`.`sid` AS `steamid`, 
                `iks_admins`.`name`, 
                `iks_admins`.`group_id`, 
                `iks_admins`.`immunity`, 
                `iks_admins`.`end`,
                `iks_groups`.`name` AS `group`,
                (SELECT COUNT(*) FROM `iks_bans` WHERE `adminsid` = `iks_admins`.`sid` ) AS `bans_count`,
                (SELECT COUNT(*) FROM `iks_gags` WHERE `adminsid` = `iks_admins`.`sid`) AS `mutes_count`,
                (SELECT COUNT(*) FROM `iks_mutes` WHERE `adminsid` = `iks_admins`.`sid`) AS `gags_count`
                FROM `iks_admins`
                LEFT JOIN `iks_groups`
                ON `iks_admins`.`group_id` = `iks_groups`.`id`
                WHERE `sid` LIKE :result OR `name` LIKE :result AND 
                (FIND_IN_SET(:server_id, REPLACE(`iks_admins`.`server_id`, ';', ',')) > 0 OR `iks_admins`.`server_id` = '') 
                LIMIT 20";
          } else {
            $query = "SELECT 
                `iks_admins`.`id` as `admin_id`, 
                `iks_admins`.`server_id`, 
                `iks_admins`.`sid` AS `steamid`, 
                `iks_admins`.`name`, 
                `iks_admins`.`group_id`, 
                `iks_admins`.`immunity`, 
                `iks_admins`.`end`,
                `iks_groups`.`name` AS `group`,
                (SELECT COUNT(*) FROM `iks_bans` WHERE `adminsid` = `iks_admins`.`sid` ) AS `bans_count`,
                (SELECT COUNT(*) FROM `iks_gags` WHERE `adminsid` = `iks_admins`.`sid`) AS `mutes_count`,
                (SELECT COUNT(*) FROM `iks_mutes` WHERE `adminsid` = `iks_admins`.`sid`) AS `gags_count`
                FROM `iks_admins`
                LEFT JOIN `iks_groups`
                ON `iks_admins`.`group_id` = `iks_groups`.`id`
                WHERE `sid` LIKE :result OR `name` LIKE :result LIMIT 20";
          }
          $params = ["result" => "%{$result}%"];
          $search = $this->Db->queryAll('IksAdmin', 0, 0, $query, $params);
        } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
          if ($this->server_id != 'all') {
            $query = "SELECT 
                        `as_admins`.`id` AS `admin_id`,
                        `as_admins`.`name`,
                        `as_admins`.`steamid`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`expires`) AS `end`,
                        `as_groups`.`name` as `group`,
                        `as_groups`.`immunity` as `immunity`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`server_id`) AS `server_id`
                    FROM 
                        `as_admins`
                    JOIN 
                        `as_admins_servers` 
                    ON 
                        `as_admins`.`id` = `as_admins_servers`.`admin_id`
                    LEFT JOIN
                        `as_groups` 
                    ON 
                        `as_admins_servers`.`group_id` = `as_groups`.`id`
                    WHERE 
                        `as_admins`.`steamid` LIKE :result OR `as_admins`.`name` LIKE :result
                        AND (`as_admins_servers`.`server_id` = :server_id OR `as_admins_servers`.`server_id` = '-1')
                        AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
                    GROUP BY
                        `as_admins`.`id`, `as_admins_servers`.`group_id`
                        ORDER BY `as_groups`.`immunity` DESC LIMIT 20";
          } else {
            $query = "SELECT 
                        `as_admins`.`id` AS `admin_id`,
                        `as_admins`.`name`,
                        `as_admins`.`steamid`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`expires`) AS `end`,
                        `as_groups`.`name` as `group`,
                        `as_groups`.`immunity` as `immunity`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`server_id`) AS `server_id`
                    FROM 
                        `as_admins`
                    JOIN 
                        `as_admins_servers` 
                    ON 
                        `as_admins`.`id` = `as_admins_servers`.`admin_id`
                    LEFT JOIN
                        `as_groups` 
                    ON 
                        `as_admins_servers`.`group_id` = `as_groups`.`id`
                    WHERE 
                        `as_admins`.`steamid` LIKE :result OR `as_admins`.`name` LIKE :result
                        AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
                    GROUP BY
                        `as_admins`.`id`, `as_admins_servers`.`group_id`
                        ORDER BY `as_groups`.`immunity` DESC LIMIT 20";
          }
          $params = ["result" => "%{$result}%", 'server_id' => $this->server_id];
          $search = $this->Db->queryAll('IksAdminNew', 0, 0, $query, $params);
        }
      } elseif ($this->game == 'csgo') {
        $result = $this->Steam32_Search($search_admin);
        if (!empty($this->Db->db_data['SourceBans'])) {
          if ($this->server_id != 'all') {
            $query = "SELECT 
                        `sb_admins`.`aid` AS `admin_id`,
                        `sb_admins`.`user` as name,
                        `sb_admins`.`authid` `steamid`,
                        `sb_admins`.`expired` AS `end`,
                        `sb_admins`.`srv_group` as `group`,
                        `sb_admins`.`immunity` as `immunity`,
                        GROUP_CONCAT(DISTINCT `sb_admins_servers_groups`.`server_id`) AS `server_id`
                    FROM 
                        `sb_admins`
                    JOIN 
                        `sb_admins_servers_groups` 
                    ON 
                        `sb_admins`.`aid` = `sb_admins_servers_groups`.`admin_id`
                    WHERE 
                        `sb_admins`.`authid` LIKE :result OR `sb_admins`.`user` LIKE :result
                        AND (`sb_admins_servers_groups`.`server_id` = :server_id OR `sb_admins_servers_groups`.`server_id` = '-1')
                        AND (`sb_admins`.`expired` > UNIX_TIMESTAMP() OR `sb_admins`.`expired` = 0)
                    GROUP BY
                        `sb_admins`.`aid`
                        ORDER BY `sb_admins`.`immunity` DESC";
          } else {
            $query = "SELECT 
                        `sb_admins`.`aid` AS `admin_id`,
                        `sb_admins`.`user` as name,
                        `sb_admins`.`authid` `steamid`,
                        `sb_admins`.`expired` AS `end`,
                        `sb_admins`.`srv_group` as `group`,
                        `sb_admins`.`immunity` as `immunity`,
                        GROUP_CONCAT(DISTINCT `sb_admins_servers_groups`.`server_id`) AS `server_id`
                    FROM 
                        `sb_admins`
                    JOIN 
                        `sb_admins_servers_groups` 
                    ON 
                        `sb_admins`.`aid` = `sb_admins_servers_groups`.`admin_id`
                    WHERE 
                        `sb_admins`.`authid` LIKE :result OR `sb_admins`.`user` LIKE :result
                        AND (`sb_admins`.`expired` > UNIX_TIMESTAMP() OR `sb_admins`.`expired` = 0)
                    GROUP BY
                        `sb_admins`.`aid`
                        ORDER BY `sb_admins`.`immunity` DESC";
          }
          $params = ["result" => "%{$result}%", 'server_id' => $this->server_id];
          $search = $this->Db->queryAll('SourceBans', 0, 0, $query, $params);
        }
      }
    }
    if ($search) {
      if (!empty($search_ban)) {
        if ($this->game == 'cs2') {
          if (!empty($this->Db->db_data['AdminSystem'])) {
            foreach ($search as $key => $row) {
              $idban = $row['id'];
              $steam_player = $row['steamid'];
              $steam_admin = $row['admin_steamid'];
              $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : $this->General->checkName($steam_player);
              if (!empty($steam_admin)) {
                $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['admin_name']) : action_text_clear($this->General->checkName($steam_admin));
              } else {
                $name_admin = $this->Translate->get_translate_module_phrase('module_page_punishment', '_console');
              }
              if (!empty($row['unpunish_admin_id'])) {
                $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                $style_ban = 'remove_punish';
              } elseif ($row['expires'] == '0') {
                $end_ban = $this->Translate->get_translate_phrase('_Forever');
                $style_ban = 'permanent_punish';
              } elseif (time() > $row['expires']) {
                $end_ban = $this->Modules->action_time_exchange_exact($row['expires'] - $row['created']);
                $style_ban = 'expired_punish';
              } else {
                $end_ban = $this->Modules->action_time_exchange_exact($row['expires'] - time());
                $style_ban = 'current_punish';
              }
              $reason_ban = action_text_clear($row['reason']);
              $JSONSearch[$key]["sid"] = $steam_player;
              $JSONSearch[$key]["check_getavatar"] = $this->General->checkAvatar($steam_player) ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <li class="modal_open" page="bans" id="{$idban}">
                  <span>
                      <svg x="0" y="0" viewBox="0 0 24 24" xml:space="preserve">
                          <g>
                              <path d="M6 7.5a5.25 5.25 0 1 1 5.25 5.25A5.26 5.26 0 0 1 6 7.5zM21.92 17a4.68 4.68 0 0 1-1.47 3.42h-.05a4.7 4.7 0 0 1-3.22 1.28 4.73 4.73 0 0 1-3.51-7.92s0-.07.07-.1a4.7 4.7 0 0 1 3.4-1.46A4.75 4.75 0 0 1 21.92 17zm-3.16 2.82-4.41-4.4a3.22 3.22 0 0 0 2.82 4.83 3.18 3.18 0 0 0 1.59-.43zM20.42 17a3.25 3.25 0 0 0-5.06-2.7l4.51 4.51a3.22 3.22 0 0 0 .55-1.81zm-8.37-3.48a.71.71 0 0 0-.57-.27H8.87a6.92 6.92 0 0 0-6.62 5 2.76 2.76 0 0 0 2.65 3.5h7.22a.76.76 0 0 0 .56-.24 1.3 1.3 0 0 0 .1-.15 6.22 6.22 0 0 1-.73-7.84z"></path>
                          </g>
                      </svg>
                  </span>
                  <span class="none_span">
                    <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                  </span>
                  <span>{$name_player}</span>
                  <span>{$reason_ban}</span>
                  <span class="{$style_ban} none_span">{$end_ban}</span>
                  <span class="none_span">{$name_admin}</span>
              </li>
            HTML;
            }
          } elseif (!empty($this->Db->db_data['IksAdmin'])) {
            foreach ($search as $key => $row) {
              $idban = $row['id'];
              $steam_player = $row['sid'];
              $steam_admin = $row['adminsid'];
              $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : $this->General->checkName($steam_player);
              $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['adminName']) : $this->General->checkName($steam_admin);
              if ($row['Unbanned'] == 1) {
                $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                $style_ban = 'remove_punish';
              } elseif ($row['time'] == 0) {
                $end_ban = $this->Translate->get_translate_phrase('_Forever');
                $style_ban = 'permanent_punish';
              } elseif (time() > $row['created'] + $row['time']) {
                $end_ban = $this->Modules->action_time_exchange_exact($row['time']);
                $style_ban = 'expired_punish';
              } else {
                $end_ban = $this->Modules->action_time_exchange_exact($row['end'] - time());
                $style_ban = 'current_punish';
              }
              $reason_ban = action_text_clear($row['reason']);
              $JSONSearch[$key]["sid"] = $steam_player;
              $JSONSearch[$key]["check_getavatar"] = $this->General->checkAvatar($steam_player) ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <li class="modal_open" page="bans" id="{$idban}">
                  <span>
                      <svg x="0" y="0" viewBox="0 0 24 24" xml:space="preserve">
                          <g>
                              <path d="M6 7.5a5.25 5.25 0 1 1 5.25 5.25A5.26 5.26 0 0 1 6 7.5zM21.92 17a4.68 4.68 0 0 1-1.47 3.42h-.05a4.7 4.7 0 0 1-3.22 1.28 4.73 4.73 0 0 1-3.51-7.92s0-.07.07-.1a4.7 4.7 0 0 1 3.4-1.46A4.75 4.75 0 0 1 21.92 17zm-3.16 2.82-4.41-4.4a3.22 3.22 0 0 0 2.82 4.83 3.18 3.18 0 0 0 1.59-.43zM20.42 17a3.25 3.25 0 0 0-5.06-2.7l4.51 4.51a3.22 3.22 0 0 0 .55-1.81zm-8.37-3.48a.71.71 0 0 0-.57-.27H8.87a6.92 6.92 0 0 0-6.62 5 2.76 2.76 0 0 0 2.65 3.5h7.22a.76.76 0 0 0 .56-.24 1.3 1.3 0 0 0 .1-.15 6.22 6.22 0 0 1-.73-7.84z"></path>
                          </g>
                      </svg>
                  </span>
                  <span class="none_span">
                    <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                  </span>
                  <span>{$name_player}</span>
                  <span>{$reason_ban}</span>
                  <span class="{$style_ban} none_span">{$end_ban}</span>
                  <span class="none_span">{$name_admin}</span>
              </li>
            HTML;
            }
          } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
            foreach ($search as $key => $row) {
              $idban = $row['id'];
              $steam_player = $row['steam_id'];
              $steam_admin = $row['admin_steamid'];
              $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : $this->General->checkName($steam_player);
              $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['admin_name']) : $this->General->checkName($steam_admin);
              if (!empty($row['unbanned_by'])) {
                $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                $style_ban = 'remove_punish';
              } elseif ($row['end_at'] == '0') {
                $end_ban = $this->Translate->get_translate_phrase('_Forever');
                $style_ban = 'permanent_punish';
              } elseif (time() > $row['end_at']) {
                $end_ban = $this->Modules->action_time_exchange_exact($row['end_at'] - $row['created_at']);
                $style_ban = 'expired_punish';
              } else {
                $end_ban = $this->Modules->action_time_exchange_exact($row['end_at'] - time());
                $style_ban = 'current_punish';
              }
              $reason_ban = action_text_clear($row['reason']);
              $JSONSearch[$key]["sid"] = $steam_player;
              $JSONSearch[$key]["check_getavatar"] = $this->General->checkAvatar($steam_player) ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <li class="modal_open" page="bans" id="{$idban}">
                  <span>
                      <svg x="0" y="0" viewBox="0 0 24 24" xml:space="preserve">
                          <g>
                              <path d="M6 7.5a5.25 5.25 0 1 1 5.25 5.25A5.26 5.26 0 0 1 6 7.5zM21.92 17a4.68 4.68 0 0 1-1.47 3.42h-.05a4.7 4.7 0 0 1-3.22 1.28 4.73 4.73 0 0 1-3.51-7.92s0-.07.07-.1a4.7 4.7 0 0 1 3.4-1.46A4.75 4.75 0 0 1 21.92 17zm-3.16 2.82-4.41-4.4a3.22 3.22 0 0 0 2.82 4.83 3.18 3.18 0 0 0 1.59-.43zM20.42 17a3.25 3.25 0 0 0-5.06-2.7l4.51 4.51a3.22 3.22 0 0 0 .55-1.81zm-8.37-3.48a.71.71 0 0 0-.57-.27H8.87a6.92 6.92 0 0 0-6.62 5 2.76 2.76 0 0 0 2.65 3.5h7.22a.76.76 0 0 0 .56-.24 1.3 1.3 0 0 0 .1-.15 6.22 6.22 0 0 1-.73-7.84z"></path>
                          </g>
                      </svg>
                  </span>
                  <span class="none_span">
                    <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                  </span>
                  <span>{$name_player}</span>
                  <span>{$reason_ban}</span>
                  <span class="{$style_ban} none_span">{$end_ban}</span>
                  <span class="none_span">{$name_admin}</span>
              </li>
            HTML;
            }
          }
        } elseif ($this->game == 'csgo') {
          if (!empty($this->Db->db_data['AdminSystem'])) {
            foreach ($search as $key => $row) {
              $idban = $row['bid'];
              $steam_player = $row['authid'];
              $steam_admin = $row['admin_steamid'];
              $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : $this->General->checkName($steam_player);
              if (!empty($steam_admin)) {
                $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['admin_name']) : action_text_clear($this->General->checkName($steam_admin));
              } else {
                $name_admin = $this->Translate->get_translate_module_phrase('module_page_punishment', '_console');
              }
              if ($row['RemoveType'] == 'U') {
                $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                $style_ban = 'remove_punish';
              } elseif ($row['length'] == '0' && $row['RemoveType'] != 'U') {
                $end_ban = $this->Translate->get_translate_phrase('_Forever');
                $style_ban = 'permanent_punish';
              } elseif (time() >= $row['ends'] && $row['length'] != '0') {
                $end_ban = $this->Modules->action_time_exchange_exact($row['ends'] - $row['created']);
                $style_ban = 'expired_punish';
              } else {
                $end_ban = $this->Modules->action_time_exchange_exact($row['ends'] - time());
                $style_ban = 'current_punish';
              }
              $reason_ban = action_text_clear($row['reason']);
              $JSONSearch[$key]["sid"] = $steam_player;
              $JSONSearch[$key]["check_getavatar"] = $this->General->checkAvatar($steam_player) ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <li class="modal_open" page="bans" id="{$idban}">
                  <span>
                      <svg x="0" y="0" viewBox="0 0 24 24" xml:space="preserve">
                          <g>
                              <path d="M6 7.5a5.25 5.25 0 1 1 5.25 5.25A5.26 5.26 0 0 1 6 7.5zM21.92 17a4.68 4.68 0 0 1-1.47 3.42h-.05a4.7 4.7 0 0 1-3.22 1.28 4.73 4.73 0 0 1-3.51-7.92s0-.07.07-.1a4.7 4.7 0 0 1 3.4-1.46A4.75 4.75 0 0 1 21.92 17zm-3.16 2.82-4.41-4.4a3.22 3.22 0 0 0 2.82 4.83 3.18 3.18 0 0 0 1.59-.43zM20.42 17a3.25 3.25 0 0 0-5.06-2.7l4.51 4.51a3.22 3.22 0 0 0 .55-1.81zm-8.37-3.48a.71.71 0 0 0-.57-.27H8.87a6.92 6.92 0 0 0-6.62 5 2.76 2.76 0 0 0 2.65 3.5h7.22a.76.76 0 0 0 .56-.24 1.3 1.3 0 0 0 .1-.15 6.22 6.22 0 0 1-.73-7.84z"></path>
                          </g>
                      </svg>
                  </span>
                  <span class="none_span">
                    <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                  </span>
                  <span>{$name_player}</span>
                  <span>{$reason_ban}</span>
                  <span class="{$style_ban} none_span">{$end_ban}</span>
                  <span class="none_span">{$name_admin}</span>
              </li>
            HTML;
            }
          }
        }
      } elseif (!empty($search_mute)) {
        if ($this->game == 'cs2') {
          if (!empty($this->Db->db_data['AdminSystem'])) {
            foreach ($search as $key => $row) {
              $idban = $row['id'];
              $steam_player = $row['steamid'];
              $steam_admin = $this->getAdminFromAdminSystem($row['admin_id'])['steamid'];
              $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : $this->General->checkName($steam_player);
              $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($this->getAdminFromAdminSystem($row['admin_id'])['name']) : $this->General->checkName($steam_admin);
              if (!empty($row['unpunish_admin_id'])) {
                $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unmuted');
                $style_ban = 'remove_punish';
              } elseif ($row['expires'] == '0') {
                $end_ban = $this->Translate->get_translate_phrase('_Forever');
                $style_ban = 'permanent_punish';
              } elseif (time() > $row['expires']) {
                $end_ban = $this->Modules->action_time_exchange_exact($row['expires'] - $row['created']);
                $style_ban = 'expired_punish';
              } else {
                $end_ban = $this->Modules->action_time_exchange_exact($row['expires'] - time());
                $style_ban = 'current_punish';
              }
              $reason_ban = action_text_clear($row['reason']);
              if ($row['punish_type'] == 3) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#face-mute"></use></svg>';
              } elseif ($row['punish_type'] == 2) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#chat-slash"></use></svg>';
              } elseif ($row['punish_type'] == 1) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#micro-slash"></use></svg>';
              }
              $JSONSearch[$key]["sid"] = $steam_player;
              $JSONSearch[$key]["check_getavatar"] = $this->General->checkAvatar($steam_player) ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <li class="modal_open" page="comms" id="{$idban}">
                  <span>
                      {$punishmentType}
                  </span>
                  <span class="none_span">
                    <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                  </span>
                  <span>{$name_player}</span>
                  <span>{$reason_ban}</span>
                  <span class="{$style_ban} none_span">{$end_ban}</span>
                  <span class="none_span">{$name_admin}</span>
              </li>
            HTML;
            }
          } elseif (!empty($this->Db->db_data['IksAdmin'])) {
            foreach ($search as $key => $row) {
              $idban = $row['id'];
              $steam_player = $row['sid'];
              $steam_admin = $row['adminsid'];
              $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : $this->General->checkName($steam_player);
              $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['adminName']) : $this->General->checkName($steam_admin);
              if ($row['Unbanned'] == 1) {
                $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unmuted');
                $style_ban = 'remove_punish';
              } elseif ($row['time'] == 0) {
                $end_ban = $this->Translate->get_translate_phrase('_Forever');
                $style_ban = 'permanent_punish';
              } elseif (time() > $row['created'] + $row['time']) {
                $end_ban = $this->Modules->action_time_exchange_exact($row['time']);
                $style_ban = 'expired_punish';
              } else {
                $end_ban = $this->Modules->action_time_exchange_exact($row['end'] - time());
                $style_ban = 'current_punish';
              }
              $reason_ban = action_text_clear($row['reason']);
              $MuteType = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT COUNT(*) as count FROM `iks_mutes` WHERE `sid` = :sid AND `created` = :created;", ["sid" => $row['sid'], "created" => $row['created']]);
              $ChatType = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT COUNT(*) as count FROM `iks_gags` WHERE `sid` = :sid AND `created` = :created;", ["sid" => $row['sid'], "created" => $row['created']]);
              $MuteCount = $MuteType[0]['count'];
              $ChatCount = $ChatType[0]['count'];
              if ($MuteCount > 0 && $ChatCount > 0) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#face-mute"></use></svg>';
              } elseif ($MuteCount > 0) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#chat-slash"></use></svg>';
              } elseif ($ChatCount > 0) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#micro-slash"></use></svg>';
              }
              $JSONSearch[$key]["sid"] = $steam_player;
              $JSONSearch[$key]["check_getavatar"] = $this->General->checkAvatar($steam_player) ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <li class="modal_open" page="comms" id="{$idban}">
                  <span>
                      {$punishmentType}
                  </span>
                  <span class="none_span">
                    <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                  </span>
                  <span>{$name_player}</span>
                  <span>{$reason_ban}</span>
                  <span class="{$style_ban} none_span">{$end_ban}</span>
                  <span class="none_span">{$name_admin}</span>
              </li>
            HTML;
            }
          } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
            foreach ($search as $key => $row) {
              $idban = $row['id'];
              $steam_player = $row['steam_id'];
              $steam_admin = $row['admin_steamid'];
              $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : $this->General->checkName($steam_player);
              $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['admin_name']) : $this->General->checkName($steam_admin);
              if (!empty($row['unbanned_by'])) {
                $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                $style_ban = 'remove_punish';
              } elseif ($row['end_at'] == '0') {
                $end_ban = $this->Translate->get_translate_phrase('_Forever');
                $style_ban = 'permanent_punish';
              } elseif (time() > $row['end_at']) {
                $end_ban = $this->Modules->action_time_exchange_exact($row['end_at'] - $row['created_at']);
                $style_ban = 'expired_punish';
              } else {
                $end_ban = $this->Modules->action_time_exchange_exact($row['end_at'] - time());
                $style_ban = 'current_punish';
              }
              $reason_ban = action_text_clear($row['reason']);
              if ($row['mute_type'] = 2) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#face-mute"></use></svg>';
              } elseif ($row['mute_type'] = 1) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#chat-slash"></use></svg>';
              } elseif ($row['mute_type'] = 0) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#micro-slash"></use></svg>';
              }
              $JSONSearch[$key]["sid"] = $steam_player;
              $JSONSearch[$key]["check_getavatar"] = $this->General->checkAvatar($steam_player) ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <li class="modal_open" page="comms" id="{$idban}">
                  <span>
                      {$punishmentType}
                  </span>
                  <span class="none_span">
                    <img style="position: absolute; transform: scale(1.17);" src="{$this->General->getFrame($steam_player)}" id="frame" frameid="{$steam_player}">
                    <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                  </span>
                  <span>{$name_player}</span>
                  <span>{$reason_ban}</span>
                  <span class="{$style_ban} none_span">{$end_ban}</span>
                  <span class="none_span">{$name_admin}</span>
              </li>
            HTML;
            }
          }
        } elseif ($this->game == 'csgo') {
          $result = $this->Steam32_Search($search_ban);
          if (!empty($this->Db->db_data['SourceBans'])) {
            foreach ($search as $key => $row) {
              $idban = $row['bid'];
              $steam_player = con_steam64($row['authid']);
              $steam_admin = con_steam64($row['admin_steamid']);
              $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : $this->General->checkName($steam_player);
              $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['admin_name']) : $this->General->checkName($steam_admin);
              if ($row['RemoveType'] == 'U') {
                $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unmuted');
                $style_ban = 'remove_punish';
              } elseif ($row['length'] == '0' && $row['RemoveType'] != 'U') {
                $end_ban = $this->Translate->get_translate_phrase('_Forever');
                $style_ban = 'permanent_punish';
              } elseif (time() >= $row['ends'] && $row['length'] != '0') {
                $end_ban = $this->Modules->action_time_exchange_exact($row['ends'] - $row['created']);
                $style_ban = 'expired_punish';
              } else {
                $end_ban = $this->Modules->action_time_exchange_exact($row['ends'] - time());
                $style_ban = 'current_punish';
              }
              $reason_ban = action_text_clear($row['reason']);
              if ($row['ptype'] == 3) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#face-mute"></use></svg>';
              } elseif ($row['type'] == 2) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#chat-slash"></use></svg>';
              } elseif ($row['type'] == 1) {
                $punishmentType = '<svg><use href="/resources/img/sprite.svg#micro-slash"></use></svg>';
              }
              $JSONSearch[$key]["sid"] = $steam_player;
              $JSONSearch[$key]["check_getavatar"] = $this->General->checkAvatar($steam_player) ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <li class="modal_open" page="comms" id="{$idban}">
                  <span>
                      {$punishmentType}
                  </span>
                  <span class="none_span">
                    <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                  </span>
                  <span>{$name_player}</span>
                  <span>{$reason_ban}</span>
                  <span class="{$style_ban} none_span">{$end_ban}</span>
                  <span class="none_span">{$name_admin}</span>
              </li>
            HTML;
            }
          }
        }
      } else if (!empty($search_admin)) {
        if ($this->game == 'cs2') {
          if (!empty($this->Db->db_data['AdminSystem'])) {
            foreach ($search as $key => $row) {
              $id = $row['admin_id'];
              $steam = $row['steamid'];
              $name = empty($this->General->checkName($steam)) ? action_text_clear($row['name']) : $this->General->checkName($steam);
              $group = $row['group'];
              $online = ($this->General->checkOnline($steam)) ? 'online' : '';
              $background_html = $this->General->getBackground($steam);
              $raiting = $this->getAdminRaiting($steam);
              $likes = $raiting['likes'] ?? 0;
              $dislikes = $raiting['dislikes'] ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <div class="punishmen-admins__card">
                  <div class="punishmen-admins__card-header">
                      <div id="background" backgroundid="{$steam}">{$background_html}</div>
                      <div class="punishmen-admins__rating">
                          <div class="punishmen-admins__rating-like" data-type="like" data-steam="{$steam}" id="adminRating">
                              <svg>
                                  <use href="/resources/img/sprite.svg#like"></use>
                              </svg> {$likes}
                          </div>
                          <div class="punishmen-admins__rating-dislike" data-type="dislike" data-steam="{$steam}" id="adminRating">
                              <svg>
                                  <use href="/resources/img/sprite.svg#like"></use>
                              </svg> {$dislikes}
                          </div>
                      </div>
                      <div class="punishmen-admins__group">{$group}</div>
                  </div>
                  <div class="punishmen-admins__avatar">
                      <span class="punishmen-admins__status {$online}"></span>
                      <img src="{$this->General->getAvatar($steam, 3)}" id="avatar" avatarid="{$steam}" alt="">
                  </div>
                  <div class="punishmen-admins__steamid copy-btn" data-clipboard-text="{$steam}">
                      <svg>
                          <use href="/resources/img/sprite.svg#copy"></use>
                      </svg>
                      {$steam}
                  </div>
                  <a href="/profiles/{$steam}/?search=1" target="_blank" class="punishmen-admins__nickname"  id="name" nameid="{$steam}">{$name}</a>
                  <div class="punishmen-admins__button">
                      <button class="width-100 modal_open" page="admins" id="{$id}">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_viewInfo')}</button>
                  </div>
              </div>
            HTML;
              $JSONSearch[$key]["sid"] = $row['steamid'];
              $JSONSearch[$key]["CheckAvatar"] = $this->General->checkAvatar($row['steamid']);
            }
          } elseif (!empty($this->Db->db_data['IksAdmin'])) {
            foreach ($search as $key => $row) {
              $id = $row['admin_id'];
              $steam = $row['steamid'];
              $name = empty($this->General->checkName($steam)) ? action_text_clear($row['name']) : $this->General->checkName($steam);
              $group = $row['group'];
              $online = ($this->General->checkOnline($steam)) ? 'online' : '';
              $background_html = $this->General->getBackground($steam);
              $raiting = $this->getAdminRaiting($steam);
              $likes = $raiting['likes'] ?? 0;
              $dislikes = $raiting['dislikes'] ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <div class="punishmen-admins__card">
                  <div class="punishmen-admins__card-header">
                      <div id="background" backgroundid="{$steam}">{$background_html}</div>
                      <div class="punishmen-admins__rating">
                          <div class="punishmen-admins__rating-like" data-type="like" data-steam="{$steam}" id="adminRating">
                              <svg>
                                  <use href="/resources/img/sprite.svg#like"></use>
                              </svg> {$likes}
                          </div>
                          <div class="punishmen-admins__rating-dislike" data-type="dislike" data-steam="{$steam}" id="adminRating">
                              <svg>
                                  <use href="/resources/img/sprite.svg#like"></use>
                              </svg> {$dislikes}
                          </div>
                      </div>
                      <div class="punishmen-admins__group">{$group}</div>
                  </div>
                  <div class="punishmen-admins__avatar">
                      <span class="punishmen-admins__status {$online}"></span>
                      <img src="{$this->General->getAvatar($steam, 3)}" id="avatar" avatarid="{$steam}" alt="">
                  </div>
                  <div class="punishmen-admins__steamid copy-btn" data-clipboard-text="{$steam}">
                      <svg>
                          <use href="/resources/img/sprite.svg#copy"></use>
                      </svg>
                      {$steam}
                  </div>
                  <a href="/profiles/{$steam}/?search=1" target="_blank" class="punishmen-admins__nickname"  id="name" nameid="{$steam}">{$name}</a>
                  <div class="punishmen-admins__button">
                      <button class="width-100 modal_open" page="admins" id="{$id}">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_viewInfo')}</button>
                  </div>
              </div>
            HTML;
              $JSONSearch[$key]["sid"] = $row['steamid'];
              $JSONSearch[$key]["CheckAvatar"] = $this->General->checkAvatar($row['steamid']);
            }
          } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
            foreach ($search as $key => $row) {
              $id = $row['admin_id'];
              $steam = $row['steamid'];
              $name = empty($this->General->checkName($steam)) ? action_text_clear($row['name']) : $this->General->checkName($steam);
              $group = $row['group'];
              $online = ($this->General->checkOnline($steam)) ? 'online' : '';
              $background_html = $this->General->getBackground($steam);
              $raiting = $this->getAdminRaiting($steam);
              $likes = $raiting['likes'] ?? 0;
              $dislikes = $raiting['dislikes'] ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <div class="punishmen-admins__card">
                  <div class="punishmen-admins__card-header">
                      <div id="background" backgroundid="{$steam}">{$background_html}</div>
                      <div class="punishmen-admins__rating">
                          <div class="punishmen-admins__rating-like" data-type="like" data-steam="{$steam}" id="adminRating">
                              <svg>
                                  <use href="/resources/img/sprite.svg#like"></use>
                              </svg> {$likes}
                          </div>
                          <div class="punishmen-admins__rating-dislike" data-type="dislike" data-steam="{$steam}" id="adminRating">
                              <svg>
                                  <use href="/resources/img/sprite.svg#like"></use>
                              </svg> {$dislikes}
                          </div>
                      </div>
                      <div class="punishmen-admins__group">{$group}</div>
                  </div>
                  <div class="punishmen-admins__avatar">
                      <span class="punishmen-admins__status {$online}"></span>
                      <img src="{$this->General->getAvatar($steam, 3)}" id="avatar" avatarid="{$steam}" alt="">
                  </div>
                  <div class="punishmen-admins__steamid copy-btn" data-clipboard-text="{$steam}">
                      <svg>
                          <use href="/resources/img/sprite.svg#copy"></use>
                      </svg>
                      {$steam}
                  </div>
                  <a href="/profiles/{$steam}/?search=1" target="_blank" class="punishmen-admins__nickname"  id="name" nameid="{$steam}">{$name}</a>
                  <div class="punishmen-admins__button">
                      <button class="width-100 modal_open" page="admins" id="{$id}">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_viewInfo')}</button>
                  </div>
              </div>
            HTML;
              $JSONSearch[$key]["sid"] = $row['steamid'];
              $JSONSearch[$key]["CheckAvatar"] = $this->General->checkAvatar($row['steamid']);
            }
          }
        } elseif ($this->game == 'csgo') {
          if (!empty($this->Db->db_data['SourceBans'])) {
            foreach ($search as $key => $row) {
              $id = $row['admin_id'];
              $steam = con_steam64($row['steamid']);
              $name = empty($this->General->checkName($steam)) ? action_text_clear($row['name']) : $this->General->checkName($steam);
              $group = $row['group'];
              $online = ($this->General->checkOnline($steam)) ? 'online' : '';
              $background_html = $this->General->getBackground($steam);
              $raiting = $this->getAdminRaiting($steam);
              $likes = $raiting['likes'] ?? 0;
              $dislikes = $raiting['dislikes'] ?? 0;
              $JSONSearch[$key]["search_html"] = <<<HTML
              <div class="punishmen-admins__card">
                  <div class="punishmen-admins__card-header">
                      <div id="background" backgroundid="{$steam}">{$background_html}</div>
                      <div class="punishmen-admins__rating">
                          <div class="punishmen-admins__rating-like" data-type="like" data-steam="{$steam}" id="adminRating">
                              <svg>
                                  <use href="/resources/img/sprite.svg#like"></use>
                              </svg> {$likes}
                          </div>
                          <div class="punishmen-admins__rating-dislike" data-type="dislike" data-steam="{$steam}" id="adminRating">
                              <svg>
                                  <use href="/resources/img/sprite.svg#like"></use>
                              </svg> {$dislikes}
                          </div>
                      </div>
                      <div class="punishmen-admins__group">{$group}</div>
                  </div>
                  <div class="punishmen-admins__avatar">
                      <span class="punishmen-admins__status {$online}"></span>
                      <img src="{$this->General->getAvatar($steam, 3)}" id="avatar" avatarid="{$steam}" alt="">
                  </div>
                  <div class="punishmen-admins__steamid copy-btn" data-clipboard-text="{$steam}">
                      <svg>
                          <use href="/resources/img/sprite.svg#copy"></use>
                      </svg>
                      {$steam}
                  </div>
                  <a href="/profiles/{$steam}/?search=1" target="_blank" class="punishmen-admins__nickname"  id="name" nameid="{$steam}">{$name}</a>
                  <div class="punishmen-admins__button">
                      <button class="width-100 modal_open" page="admins" id="{$id}">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_viewInfo')}</button>
                  </div>
              </div>
            HTML;
              $JSONSearch[$key]["sid"] = $steam;
              $JSONSearch[$key]["CheckAvatar"] = $this->General->checkAvatar($steam);
            }
          }
        }
      }
    }
    if (!empty($search_mute) || !empty($search_ban)) {
      $page = 'punishment';
    } else if (!empty($search_admin)) {
      $page = 'admins';
    }
    return ["html" => $JSONSearch, "page" => $page];
  }
}
