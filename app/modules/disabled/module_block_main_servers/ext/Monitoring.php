<?php

namespace app\modules\module_block_main_servers\ext;

use app\modules\module_block_main_servers\ext\Rcon;

class Monitoring
{
  public object $Db;
  public object $General;
  public object $Translate;
  public object $Modules;
  public object $Router;
  public object $Notifications;
  public array $Settings;
  public object $Access;
  public int $Type;
  public array $servers;
  public int $servers_count;

  public function __construct(object $Db, object $General, object $Translate, object $Modules, object $Router, object $Notifications)
  {
    $this->Db = $Db;
    $this->General = $General;
    $this->Translate = $Translate;
    $this->Modules = $Modules;
    $this->Router = $Router;
    $this->Notifications = $Notifications;
    $this->servers = $this->getServers();
    $this->servers_count = $this->getServersCount();
    $this->Settings = $this->getSettings();
    $this->Type = $this->getModuleMod();
    $this->checkAccess();
  }

  public function getServers(): array
  {
    return array_filter($this->General->server_list, function ($server) {
      return $server['server_status'] != 0;
    });
  }

  public function getServersCount(): int
  {
    return count($this->getServers());
  }

  public function getUniqueServers(): array
  {
    $seen = [];
    $result = [];
    foreach ($this->getServers() as $server) {
      $ip = $server['ip'] ?? '';
      if ($ip === '' || isset($seen[$ip])) {
        continue;
      }
      $seen[$ip] = true;
      $result[] = $server;
    }
    return $result;
  }

  public function getUniqueServersCount(): int
  {
    return count($this->getUniqueServers());
  }

  public function getModuleMod(): int
  {
    if (isset($this->Modules->array_modules['module_page_mon_settings']['setting']['type'])) {
      return $this->Modules->array_modules['module_page_mon_settings']['setting']['type'];
    } else {
      return 1;
    }
  }

  public function getServerInfoById($id): array
  {
    return $this->General->server_list[$id] ?? [];
  }

  public function getSettings(): array
  {
    $settings = [];
    if (file_exists(MODULES . 'module_page_mon_settings/settings.php')) {
      $settings = require MODULES . 'module_page_mon_settings/settings.php';
    }
    return $settings;
  }

  public function getMods(): array
  {
    $mods = [];
    if (file_exists(MODULES . 'module_page_mon_settings/mods.json')) {
      $mods = json_decode(file_get_contents(MODULES . 'module_page_mon_settings/mods.json'), true) ?? [];
    }
    return $mods;
  }

  public function getModsList()
  {
    $mods = $this->getMods();
    $mods_list = [];
    foreach ($mods as $value) {
      $mods_list[] = [
        'name' => $value['name'],
        'description' => (isset($value['description']) && $value['description'][0] === '_' ? $this->Translate->get_translate_phrase($value['description']) : $value['description']),
        'image_1' => $value['image_1'],
        'image_2' => $value['image_2']
      ];
    }
    return $mods_list;
  }

  public function getModsConfigForJs(): array
  {
    $mods = $this->getMods();
    usort($mods, fn($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));
    return array_values(array_map(function ($m) {
      $submods = $m['submods'] ?? [];
      usort($submods, fn($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));
      return [
        'name' => $m['name'] ?? '',
        'description' => $m['description'] ?? '',
        'video' => $m['video'] ?? '',
        'servers' => array_map('strval', $m['servers'] ?? []),
        'submods' => array_map(fn($s) => [
          'title' => $s['title'] ?? '',
          'servers' => array_map('strval', $s['servers'] ?? []),
        ], $submods),
      ];
    }, $mods));
  }

  public function getUserGeo(): ?array
  {
    $ip = '';
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
      $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    } else {
      $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }

    if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
      return null;
    }

    $cacheDir = MODULES . 'module_block_main_servers/temp/geo/';
    !is_dir($cacheDir) && mkdir($cacheDir, 0777, true);
    $cacheFile = $cacheDir . 'geo_user_' . md5($ip) . '.json';

    if (file_exists($cacheFile)) {
      $cached = json_decode(file_get_contents($cacheFile), true);
      if (is_array($cached) && isset($cached['expire']) && $cached['expire'] > time()) {
        return ['lat' => $cached['lat'], 'lon' => $cached['lon']];
      }
    }

    $coords = GeoLookup::fetchLatLon($ip);
    if ($coords === null) {
      return null;
    }

    $result = ['lat' => $coords['lat'], 'lon' => $coords['lon'], 'expire' => time() + 3600];
    file_put_contents($cacheFile, json_encode($result), LOCK_EX);
    return ['lat' => $result['lat'], 'lon' => $result['lon']];
  }

  public function getAccess()
  {
    return $this->Db->query("Core", 0, 0, "SELECT * FROM lvl_web_monitoring_access WHERE `steamid` = :steam LIMIT 1", ['steam' => $_SESSION['steamid']]);
  }

  public function getPlugsCount(): int
  {
    if (!empty($this->Settings['plugs']) && $this->Settings['plugs']) {
      $count = $this->getUniqueServersCount();
      if (isset($this->Settings['type']) && $this->Settings['type'] == 0) {
        $cols = (int)($this->Settings['server_card_count'] ?? 3);
        if ($cols <= 0) {
          $cols = 3;
        }
        return ($cols - ($count % $cols)) % $cols;
      }
      $cols = (int)($this->Settings['server_table_count'] ?? 1);
      if ($cols <= 0) {
        $cols = 1;
      }
      return ($cols - ($count % $cols)) % $cols;
    }
    return 0;
  }

  public function getPlugsModsCount(): int
  {
    if (!empty($this->Settings['enable_emptys']) && $this->Settings['enable_emptys']) {
      return ($this->Settings['grid_counts'] - (count($this->getMods()) % $this->Settings['grid_counts'])) % $this->Settings['grid_counts'];
    } else {
      return 0;
    }
  }

  public function getFilterSubmods(): array
  {
    $result = [];
    foreach ($this->getMods() as $mod) {
      foreach ($mod['submods'] ?? [] as $sub) {
        if (!empty($sub['title'])) {
          $result[] = ['title' => $sub['title'], 'id' => $sub['id']];
        }
      }
    }
    return $result;
  }

  public function getFilterLocations(): array
  {
    $result = [];
    foreach ($this->General->server_list as $srv) {
      $city = trim($srv['server_city'] ?? '');
      $country = strtolower(trim($srv['server_country'] ?? ''));
      if ($city && !isset($result[$city])) {
        $result[$city] = $country;
      }
    }
    ksort($result);
    return $result;
  }

  public function getFilterMaps(): array
  {
    $maps = [];
    $cacheDir = dirname(__DIR__) . '/servers/';
    $indexFile = dirname(__DIR__) . '/temp/maps_index.json';
    $indexTtl = 120;

    if (file_exists($indexFile)) {
      $index = json_decode(file_get_contents($indexFile), true);
      if (is_array($index) && !empty($index['expire']) && $index['expire'] > time() && !empty($index['maps'])) {
        return $index['maps'];
      }
    }

    foreach (glob($cacheDir . '*.json') as $file) {
      if (strpos(basename($file), 'geo_') === 0) {
        continue;
      }
      $data = json_decode(file_get_contents($file), true);
      if (!empty($data['Map']) && $data['Map'] !== '-') {
        $maps[$data['Map']] = true;
      }
    }
    $mapList = array_keys($maps);
    sort($mapList);
    $tempDir = dirname(__DIR__) . '/temp';
    !is_dir($tempDir) && mkdir($tempDir, 0755, true);
    file_put_contents($indexFile, json_encode([
      'maps' => $mapList,
      'expire' => time() + $indexTtl,
    ], JSON_UNESCAPED_UNICODE), LOCK_EX);
    return $mapList;
  }

  private function sendRcon(string $ip, string $password, string $command): void
  {
    $_IP = explode(':', $ip);
    $_RCON = new Rcon($_IP[0], (int) $_IP[1]);
    if ($_RCON->Connect()) {
      $_RCON->RconPass($password);
      $_RCON->Command($command);
      $_RCON->Disconnect();
    }
  }

  public function MonAction(array $post): array
  {
    $action = $post['action'];
    if (!isset($post['server']) || $post['server'] == 'undefined') {
      return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_srvNotFound')];
    }
    $server_data = $this->getServerInfoById($post['server']);
    if (empty($server_data)) {
      return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_srvNotFound2')];
    }
    if (!isset($_SESSION['user_admin']) && !empty($this->getAccess())) {
      $servers = explode(';', $this->getAccess()['servers']);
      $server_key_id = isset($server_data['id']) ? $server_data['id'] : $post['server'];
      if (!in_array($server_key_id, $servers)) {
        return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_haventAccess') . $server_key_id];
      }
    }
    $game = $server_data['server_game'] ?? 'cs2';
    $admin = $this->getAdminInfoWithSid($server_data['server_sb_id'], $game);
    if (!isset($_SESSION['user_admin']) && $this->Settings['panel'] && !empty($admin['server_id'])) {
      if ($game == 'cs2') {
        if (!empty($this->Db->db_data['AdminSystem']) && explode(';', $server_data['server_sb'])[0] === 'AdminSystem') {
          if ($admin['server_id'] != '-1' && $admin['server_id'] != $server_data['server_sb_id']) {
            return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_haventAccess')];
          }
        } elseif (!empty($this->Db->db_data['IksAdminNew']) && explode(';', $server_data['server_sb'])[0] === 'IksAdminNew') {
          if (!empty($admin['server_id']) && $admin['server_id'] != $server_data['server_sb_id']) {
            return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_haventAccess')];
          }
        } elseif (!empty($this->Db->db_data['IksAdmin']) && explode(';', $server_data['server_sb'])[0] === 'IksAdmin') {
          if ($admin['server_id'] != '' && !in_array($server_data['server_sb_id'], explode(';', $admin['server_id']))) {
            return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_haventAccess')];
          }
        }
      } elseif ($game == 'csgo') {
        if (!empty($this->Db->db_data['SourceBans']) && explode(';', $server_data['server_sb'])[0] === 'SourceBans') {
          if ($admin['server_id'] != '-1' && $admin['server_id'] != $server_data['server_sb_id']) {
            return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_haventAccess')];
          }
        }
      }
    }
    if (!isset($post['time']) && in_array($action, ['ban', 'mute'])) {
      return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_specifyTime')];
    }
    $reason = '';
    switch ($action) {
      case 'ban':
        if (empty($post['ban_reason']) || $post['ban_reason'] == 'undefined') {
          return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_setReason')];
        }
        $reason = $post['ban_reason'];
        break;
      case 'mute':
        if (empty($post['mute_reason']) || $post['mute_reason'] == 'undefined') {
          return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_setReason')];
        }
        $reason = $post['mute_reason'];
        break;
    }
    $server_info = $this->getServerInfoById($post['server']);
    $password = $server_info['rcon'];
    $ip = $server_info['ip'];
    $duration = $post['time'] ?? '';
    $mute_type = $post['mute_type'] ?? '';
    $punish_reload_comand = '';
    $comand = '';
    foreach ($post['mon_action'] as $player) {
      $data = json_decode($player, true);
      $id = $data['id'] ?? NULL;
      $steam = $data['steam'] ?? NULL;
      if (!is_numeric($id) || !is_numeric($steam)) {
        return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_errorPunish')];
      }
      $id = (int) $id;
      $steam = (int) $steam;
      if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_errorPunish')];
      }
      if ($game == 'cs2') {
        if (!empty($this->Db->db_data['AdminSystem'])):
          if (!$this->Settings['punish_all_server']) {
            $server = $server_info['server_sb_id'] ?? '-1';
          } else {
            $server = '-1';
          }
          $punish_reload_comand = "mm_as_reload_punish {$steam}";
        elseif (!empty($this->Db->db_data['IksAdminNew'])):
          if (!$this->Settings['punish_all_server']) {
            $server = $server_info['server_sb_id'] ?? NULL;
          } else {
            $server = NULL;
          }
          $punish_reload_comand = "css_reload_infractions {$steam}";
        elseif (!empty($this->Db->db_data['IksAdmin'])):
          if (!$this->Settings['punish_all_server']) {
            $server = $server_info['server_sb_id'] ?? NULL;
          } else {
            $server = NULL;
          }
          $punish_reload_comand = "css_reload_infractions {$steam}";
        else:
          $punish_reload_comand = '';
        endif;
      } elseif ($game == 'csgo') {
        if (!empty($this->Db->db_data['SourceBans'])):
          if (!$this->Settings['punish_all_server']) {
            $server = $server_info['server_sb_id'] ?? '0';
          } else {
            $server = '0';
          }
        else:
          $server = '0';
        endif;
      }
      switch ($action) {
        case 'kick':
          $kick_comand = "kickid {$id};mm_kick {$steam};css_kick #{$steam} 'kick'";
          $this->sendRcon($ip, $password, $kick_comand);
          break;
        case 'ban':
          if ($game == 'cs2') {
            $this->addBan($steam, $duration, $reason, $server);
            if (!empty($punish_reload_comand)) {
              $this->sendRcon($ip, $password, $punish_reload_comand);
            }
          } elseif ($game == 'csgo') {
            $duration = round($duration / 60);
            $comand = "sm_ban #{$id} {$duration} {$reason};";
            $this->sendRcon($ip, $password, $comand);
          }

          break;
        case 'mute':
          if ($game == 'cs2') {
            $this->addMute($steam, $duration, $reason, $mute_type, $server);
            if (!empty($punish_reload_comand)) {
              $this->sendRcon($ip, $password, $punish_reload_comand);
            }
          } elseif ($game == 'csgo') {
            $duration = round($duration / 60);
            switch ($mute_type) {
              case 'mute':
                $comand = "sm_mute #{$id} {$duration} '{$reason};";
                break;
              case 'gag':
                $comand = "sm_gag #{$id} {$duration} {$reason};";
                break;
              case 'full':
                $comand = "sm_silence #{$id} {$duration} {$reason};";
                break;
            }
            if (!empty($comand)) {
              $this->sendRcon($ip, $password, $comand);
            }
          }
          break;
      }
      $this->addToLogs($steam, $action, $reason, $duration, $mute_type, $server_info['id']);
    }

    return ['success' => true, 'message' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_punishIssued')];
  }

  public function getBanReasonsForMs()
  {
    return file_exists(MODULES . 'module_page_managersystem/assets/cache/reasonban.php') ? require MODULES . 'module_page_managersystem/assets/cache/reasonban.php' : null;
  }
  public function getMuteReasonsForMs()
  {
    return file_exists(MODULES . 'module_page_managersystem/assets/cache/reasonmute.php') ? require MODULES . 'module_page_managersystem/assets/cache/reasonmute.php' : null;
  }
  public function getTimeForMs()
  {
    return file_exists(MODULES . 'module_page_managersystem/assets/cache/punishmenttime.php') ? require MODULES . 'module_page_managersystem/assets/cache/punishmenttime.php' : null;
  }

  public function getSettingsForMs()
  {
    return file_exists(MODULES . 'module_page_managersystem/assets/cache/settings.php') ? require MODULES . 'module_page_managersystem/assets/cache/settings.php' : null;
  }

  private function addBan(int $steam, int $time, string $reason, $server): void
  {
    if (!empty($this->Db->db_data['AdminSystem'])):
      if ($time == 0) {
        $end = 0;
      } else {
        $end = time() + $time;
      }
      $this->Db->query('AdminSystem', 0, 0, "INSERT INTO `as_punishments` (`name`, `steamid`, `ip`, `admin_id`, `created`, `expires`, `reason`, `unpunish_admin_id`, `server_id`, `punish_type`) VALUES (:name, :steam, '', :admin_id, UNIX_TIMESTAMP(), :time, :reason, NULL, :server, 0);", [
        'name' => $this->General->checkName($steam),
        'steam' => $steam,
        'admin_id' => $this->getAdminInfo($_SESSION['steamid'])['id'] ?? "1",
        'time' => $end,
        'reason' => $reason,
        'server' => $server
      ]);
    elseif (!empty($this->Db->db_data['IksAdminNew'])):
      if ($time == 0) {
        $end = 0;
      } else {
        $end = time() + $time;
      }
      if ($end != 0 && $end > time()) {
        $duration = $end - time();
      } else {
        $duration = 0;
      }
      $this->Db->query('IksAdminNew', 0, 0, "INSERT INTO `iks_bans` (`steam_id`, `ip`, `name`, `duration`, `reason`, `ban_type`, `server_id`, `admin_id`, `created_at`, `end_at`, `updated_at`) VALUES (:steam_id, :ip, :name, :duration, :reason, :ban_type, :server_id, :admin_id, :created_at, :end_at, :updated_at);", [
        "steam_id" => $steam,
        "ip" => NULL,
        "name" => empty($this->General->checkName($steam)) ? 'Unnamed' : $this->General->checkName($steam),
        "duration" => $duration,
        "reason" => $reason,
        "ban_type" => 0,
        "server_id" => $server,
        "admin_id" => $this->getAdminInfo($_SESSION['steamid64'])['id'] ?? '1',
        "created_at" => time(),
        "end_at" => $end,
        "updated_at" => time(),
      ]);
    elseif (!empty($this->Db->db_data['IksAdmin'])):
      if ($time == 0) {
        $end = 0;
      } else {
        $end = time() + $time;
      }
      if ($end != 0 && $end > time()) {
        $duration = $end - time();
      } else {
        $duration = 0;
      }
      $this->Db->query('IksAdmin', 0, 0, "INSERT INTO `iks_bans` (`name`, `sid`, `ip`, `adminsid`, `created`, `time`, `end`, `reason`, `BanType`, `Unbanned`, `server_id`, `adminName`) VALUES (:name, :steamid, :ip, :admin_steamid, :created, :duration, :end, :reason, :BanType, :Unbanned, :server_id, :adminName);", [
        "admin_steamid" => $_SESSION['steamid64'],
        "name" => empty($this->General->checkName($steam)) ? 'Unnamed' : $this->General->checkName($steam),
        "steamid" => $steam,
        "created" => time(),
        "end" => $end,
        "duration" => $time,
        "reason" => $reason,
        "ip" => 'Undefined',
        "BanType" => 0,
        "Unbanned" => 0,
        "server_id" => $server ?? NULL,
        "adminName" => empty($this->General->checkName($_SESSION['steamid64'])) ? 'Unnamed' : $this->General->checkName($_SESSION['steamid64']),
      ]);
    endif;
  }

  private function addMute(int $steam, int $time, string $reason, string $type, $server): void
  {
    if ($time == 0) {
      $end = 0;
    } else {
      $end = time() + $time;
    }
    $type_mute = 0;
    if (!empty($this->Db->db_data['AdminSystem'])):
      switch ($type) {
        case 'mute':
          $type_mute = 1;
          break;
        case 'gag':
          $type_mute = 2;
          break;
        case 'full':
          $type_mute = 3;
          break;
      }
      $this->Db->query('AdminSystem', 0, 0, "INSERT INTO `as_punishments` (`name`, `steamid`, `ip`, `admin_id`, `created`, `expires`, `reason`, `unpunish_admin_id`, `server_id`, `punish_type`) VALUES (:name, :steam, '', :admin_id, UNIX_TIMESTAMP(), :time, :reason, NULL, :server, :type);", [
        'name' => $this->General->checkName($steam),
        'steam' => $steam,
        'admin_id' => $this->getAdminInfo($_SESSION['steamid'])['id'] ?? "1",
        'time' => $end,
        'reason' => $reason,
        'server' => $server,
        'type' => $type_mute
      ]);
    elseif (!empty($this->Db->db_data['IksAdminNew'])):
      switch ($type) {
        case 'mute':
          $type_mute = 0;
          break;
        case 'gag':
          $type_mute = 1;
          break;
        case 'full':
          $type_mute = 2;
          break;
      }
      if ($end != 0 && $end > time()) {
        $duration = $end - time();
      } else {
        $duration = 0;
      }
      $this->Db->query('IksAdminNew', 0, 0, "INSERT INTO `iks_comms` (`steam_id`, `ip`, `name`, `duration`, `reason`, `mute_type`, `server_id`, `admin_id`, `created_at`, `end_at`, `updated_at`) VALUES (:steam_id, :ip, :name, :duration, :reason, :mute_type, :server_id, :admin_id, :created_at, :end_at, :updated_at);", [
        "steam_id" => $steam,
        "ip" => NULL,
        "name" => empty($this->General->checkName($steam)) ? 'Unnamed' : $this->General->checkName($steam),
        "duration" => $duration,
        "reason" => $reason,
        "server_id" => $server ?? NULL,
        "mute_type" => $type_mute,
        "admin_id" => $this->getAdminInfo($_SESSION['steamid64'])['id'] ?? '1',
        "created_at" => time(),
        "end_at" => $end,
        "updated_at" => time(),
      ]);
    elseif (!empty($this->Db->db_data['IksAdmin'])):
      if ($end != 0 && $end > time()) {
        $duration = $end - time();
      } else {
        $duration = 0;
      }
      $MuteAdd = [
        "admin_steamid" => $_SESSION['steamid64'],
        "name" => empty($this->General->checkName($steam)) ? 'Unnamed' : $this->General->checkName($steam),
        "steamid" => $steam,
        "created" => time(),
        "end" => $end,
        "duration" => $time,
        "reason" => $reason,
        "Unbanned" => 0,
        "server_id" => $server ?? '',
        "adminName" => empty($this->General->checkName($_SESSION['steamid64'])) ? 'Unnamed' : $this->General->checkName($_SESSION['steamid64']),
      ];
      switch ($type) {
        case 'mute':
          $this->Db->query('IksAdmin', 0, 0, "INSERT INTO `iks_mutes` (`name`, `sid`, `adminsid`, `created`, `time`, `end`, `reason`, `Unbanned`, `server_id`, `adminName`) VALUES (:name, :steamid, :admin_steamid, :created, :duration, :end, :reason, :Unbanned, :server_id, :adminName);", $MuteAdd);
          break;
        case 'gag':
          $this->Db->query('IksAdmin', 0, 0, "INSERT INTO `iks_gags` (`name`, `sid`, `adminsid`, `created`, `time`, `end`, `reason`, `Unbanned`, `server_id`, `adminName`) VALUES (:name, :steamid, :admin_steamid, :created, :duration, :end, :reason, :Unbanned, :server_id, :adminName);", $MuteAdd);
          break;
        case 'full':
          $this->Db->query('IksAdmin', 0, 0, "INSERT INTO `iks_mutes` (`name`, `sid`, `adminsid`, `created`, `time`, `end`, `reason`, `Unbanned`, `server_id`, `adminName`) VALUES (:name, :steamid, :admin_steamid, :created, :duration, :end, :reason, :Unbanned, :server_id, :adminName);", $MuteAdd);
          $this->Db->query('IksAdmin', 0, 0, "INSERT INTO `iks_gags` (`name`, `sid`, `adminsid`, `created`, `time`, `end`, `reason`, `Unbanned`, `server_id`, `adminName`) VALUES (:name, :steamid, :admin_steamid, :created, :duration, :end, :reason, :Unbanned, :server_id, :adminName);", $MuteAdd);
          break;
      }
    endif;
  }

  public function getAdminInfo($steam)
  {
    if (!empty($this->Db->db_data['AdminSystem'])):
      return $this->Db->query(
        'AdminSystem',
        0,
        0,
        "SELECT 
          `as_admins`.`id`,
          `as_admins`.`steamid`,
          GROUP_CONCAT(DISTINCT `as_admins_servers`.`server_id`) AS `server_id`
        FROM 
          `as_admins`
        JOIN 
          `as_admins_servers` 
        ON 
          `as_admins`.`id` = `as_admins_servers`.`admin_id` 
        WHERE `as_admins`.`steamid` = :id
        AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
        GROUP BY `as_admins`.`id` LIMIT 1",
        ['id' => $steam]
      );
    elseif (!empty($this->Db->db_data['IksAdminNew'])):
      return $this->Db->query(
        'IksAdminNew',
        0,
        0,
        "SELECT 
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
            a.steam_id = :id
            AND a.is_disabled = 0
            AND (a.end_at > UNIX_TIMESTAMP() OR a.end_at IS NULL)
          GROUP BY a.steam_id, s.server_id;",
        ['id' => $steam]
      );
    elseif (!empty($this->Db->db_data['IksAdmin'])):
      return $this->Db->query(
        'IksAdmin',
        0,
        0,
        "SELECT 
            a.sid AS steamid,
            GROUP_CONCAT(DISTINCT g.name ORDER BY g.immunity DESC SEPARATOR ', ') AS group_names,
            a.server_id AS servers
          FROM 
            iks_admins a
          JOIN 
            iks_groups g ON a.group_id = g.id
          WHERE 
            a.sid = :id
            AND (a.end = 0 OR a.end > UNIX_TIMESTAMP())
          GROUP BY a.sid;",
        ['id' => $steam]
      );
    endif;
    return [];
  }

  public function getAdminInfoWithSid($id, string $game)
  {
    if ($game == 'cs2') {
      $steam = $_SESSION['steamid'];
      if (!empty($this->Db->db_data['AdminSystem'])):
        return $this->Db->query(
          'AdminSystem',
          0,
          0,
          "SELECT 
          `as_admins`.`id`,
          `as_admins`.`steamid`,
          GROUP_CONCAT(DISTINCT `as_admins_servers`.`server_id`) AS `server_id`
        FROM 
          `as_admins`
        JOIN 
          `as_admins_servers` 
        ON 
          `as_admins`.`id` = `as_admins_servers`.`admin_id` 
        WHERE `as_admins`.`steamid` = :id
        AND (`as_admins_servers`.`server_id` = :server_id OR `as_admins_servers`.`server_id` = '-1')
        AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
        GROUP BY `as_admins`.`id` LIMIT 1",
          ['id' => $steam, 'server_id' => $id]
        );
      elseif (!empty($this->Db->db_data['IksAdminNew'])):
        return $this->Db->query(
          'IksAdminNew',
          0,
          0,
          "SELECT 
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
            a.steam_id = :id
            AND a.is_disabled = 0
            AND (`s`.`server_id` = :server_id OR `s`.`server_id` IS NULL)
            AND (a.end_at > UNIX_TIMESTAMP() OR a.end_at IS NULL)
          GROUP BY a.steam_id;",
          ['id' => $steam, 'server_id' => $id]
        );
      elseif (!empty($this->Db->db_data['IksAdmin'])):
        return $this->Db->query(
          'IksAdmin',
          0,
          0,
          "SELECT 
            a.sid AS steamid,
            GROUP_CONCAT(DISTINCT g.name ORDER BY g.immunity DESC SEPARATOR ', ') AS group_names,
            a.server_id AS servers
          FROM 
            iks_admins a
          JOIN 
            iks_groups g ON a.group_id = g.id
          WHERE 
            a.sid = :id AND a.server_id LIKE '%:server_id%'
            AND (a.end = 0 OR a.end > UNIX_TIMESTAMP())
          GROUP BY a.sid;",
          ['id' => $steam, 'server_id' => $id]
        );
      endif;
    } elseif ($game == 'csgo') {
      $steam = $_SESSION['steamid32_short'];
      if (!empty($this->Db->db_data['SourceBans'])):
        return $this->Db->query(
          'SourceBans',
          0,
          0,
          "SELECT 
            `sb_admins`.`aid` as id,
            `sb_admins`.`authid` as steamid,
            GROUP_CONCAT(DISTINCT `sb_admins_servers_groups`.`server_id`) AS `server_id`
          FROM 
            `sb_admins`
          JOIN 
            `sb_admins_servers_groups` 
          ON 
            `sb_admins`.`aid` = `sb_admins_servers_groups`.`admin_id` 
          WHERE SUBSTRING(`sb_admins`.`authid`, 9) = :id
          AND (`sb_admins_servers_groups`.`server_id` = :server_id OR `sb_admins_servers_groups`.`server_id` = '-1')
          AND (`sb_admins`.`expired` > UNIX_TIMESTAMP() OR `sb_admins`.`expired` = 0)
          GROUP BY `sb_admins`.`aid` LIMIT 1",
          ['id' => $steam, 'server_id' => $id]
        );
      endif;
    }
    return [];
  }

  public function checkAccess(): void
  {
    if (isset($_SESSION['user_admin'])) {
      $_SESSION['monAccess'] = true;
    } elseif (!empty($this->getAdminInfo($_SESSION['steamid'])['steamid']) && $this->Settings['panel']) {
      if (!$this->isAdminExcluded()) {
        $_SESSION['monAccess'] = true;
      } else {
        unset($_SESSION['monAccess']);
      }
    } elseif (!empty($this->getAccess())) {
      $_SESSION['monAccess'] = true;
    } else {
      unset($_SESSION['monAccess']);
    }
  }

  private function getAdminGroupNames(string $steam, string $game): array
  {
    $row = null;
    if ($game === 'cs2') {
      if (!empty($this->Db->db_data['AdminSystem'])) {
        $row = $this->Db->query(
          'AdminSystem',
          0,
          0,
          "SELECT GROUP_CONCAT(DISTINCT g.name) AS group_names
           FROM as_admins a
           JOIN as_admins_servers asg ON a.id = asg.admin_id
           LEFT JOIN as_groups g ON asg.group_id = g.id
           WHERE a.steamid = :id
           AND (asg.expires > UNIX_TIMESTAMP() OR asg.expires = 0)",
          ['id' => $steam]
        );
      } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
        $row = $this->Db->query(
          'IksAdminNew',
          0,
          0,
          "SELECT GROUP_CONCAT(DISTINCT g.name) AS group_names
           FROM iks_admins a
           JOIN iks_groups g ON a.group_id = g.id
           WHERE a.steam_id = :id AND a.is_disabled = 0
           AND (a.end_at > UNIX_TIMESTAMP() OR a.end_at IS NULL)",
          ['id' => $steam]
        );
      } elseif (!empty($this->Db->db_data['IksAdmin'])) {
        $row = $this->Db->query(
          'IksAdmin',
          0,
          0,
          "SELECT GROUP_CONCAT(DISTINCT g.name) AS group_names
           FROM iks_admins a
           JOIN iks_groups g ON a.group_id = g.id
           WHERE a.sid = :id
           AND (a.end = 0 OR a.end > UNIX_TIMESTAMP())",
          ['id' => $steam]
        );
      }
    } elseif ($game === 'csgo') {
      if (!empty($this->Db->db_data['SourceBans'])) {
        $row = $this->Db->query(
          'SourceBans',
          0,
          0,
          "SELECT GROUP_CONCAT(DISTINCT g.name) AS group_names
           FROM sb_admins a
           JOIN sb_admins_servers_groups asg ON a.aid = asg.admin_id
           LEFT JOIN sb_srvgroups g ON asg.group_id = g.grpId
           WHERE SUBSTRING(a.authid, 9) = :id
           AND (a.expired > UNIX_TIMESTAMP() OR a.expired = 0)",
          ['id' => $steam]
        );
      }
    }
    if (!empty($row['group_names'])) {
      return array_map('trim', explode(',', $row['group_names']));
    }
    return [];
  }

  private function isAdminExcluded(): bool
  {
    $excluded = $this->Settings['excluded_admins'] ?? [];
    if (empty($excluded))
      return false;

    if (!empty($excluded['cs2']) && !empty($_SESSION['steamid'])) {
      $groups = $this->getAdminGroupNames($_SESSION['steamid'], 'cs2');
      if (!empty(array_intersect($groups, $excluded['cs2'])))
        return true;
    }

    if (!empty($excluded['csgo']) && !empty($_SESSION['steamid32_short'])) {
      $groups = $this->getAdminGroupNames($_SESSION['steamid32_short'], 'csgo');
      if (!empty(array_intersect($groups, $excluded['csgo'])))
        return true;
    }

    return false;
  }

  public function addToLogs(int $steam, string $type, string $reason = '', $duration = '', string $mute_type = '', $server): void
  {
    $this->Db->query('Core', 0, 0, 'INSERT INTO `lvl_web_monitoring_logs` (`steamid`, `admin`, `type`, `duration`, `reason`, `mute_type`, `server`) VALUES (:steam, :admin, :type, :duration, :reason, :mute_type, :server);', [
      'steam' => $steam,
      'admin' => $_SESSION['steamid'],
      'type' => $type,
      'duration' => $duration,
      'reason' => $reason,
      'mute_type' => $mute_type,
      'server' => $server
    ]);
  }
}
