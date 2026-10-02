<?php

namespace app\modules\module_page_admintime\ext;

class AdminTimeCore
{
  protected $Db, $General, $Translate, $Modules;

  public $Settings, $Access;

  public function __construct($Db, $General, $Translate, $Modules)
  {
    $this->Db = $Db;
    $this->General = $General;
    $this->Translate = $Translate;
    $this->Modules = $Modules;
    $this->Settings = new AdminTimeSettings($Db, $General, $Translate, $Modules);
    $this->Access = new AdminTimeAccess($Db, $General, $Translate, $Modules);
  }

  public function render($page, $steamid = null, $server_id = null, $date_start = null, $date_end = null)
  {
    $page = (int)$page;
    $limit = 12;
    $page_min = ($page - 1) * $limit;
    $page_max = 0;
    if (!empty($date_end) && $date_start == $date_end) {
      $date_end = $date_end + 86400;
    }
    if ($server_id !== 'all') {
      $server_id = $this->Settings->getServerById($server_id)['server_id'] ?? null;
    }
    if ($steamid != null) {
      $steamid = con_steam64($steamid);
    }
    $admins_array = $this->getAdmins($steamid, $server_id);
    $admins_slised = array_slice($admins_array, $page_min, $limit);
    $admins = [];
    $page_max = ceil(count($admins_array) / $limit);
    foreach ($admins_slised as $admin) {
      $name = $this->General->checkName($admin['steamid']);
      $avatar = $this->General->getAvatar($admin['steamid'], 3);
      $background = $this->General->getBackground($admin['steamid']);
      $admin_time = $this->getAdminPlayedTime($admin['steamid'], $date_start, $date_end, $server_id);
      $admins[] = [
        'name' => $name,
        'avatar' => $avatar,
        'checked_avatar' => $this->General->checkAvatar($admin['steamid']),
        'background' => $background,
        'steamid' => $admin['steamid'],
        'total_time' => $admin_time
      ];
    }
    return [
      'admins' => $admins,
      'max_pages' => $page_max
    ];
  }

  public function renderSession($steamid, $page, $server_id = null, $date_start = null, $date_end = null)
  {
    $limit = 10;
    $page = (int)$page;
    $page_min = ($page - 1) * $limit;
    $page_max = 0;

    if (!empty($date_end) && $date_start == $date_end) {
      $date_end = $date_end + 86400;
    }

    $sessions_array = $this->getAdminSessionsLogs($steamid, $server_id, $date_start, $date_end);
    $sessions = array_slice($sessions_array, $page_min, $limit);

    foreach ($sessions as &$session) {
      $session['server_id'] = $this->getServerById($session['server_id'])['name'] ?? $this->Translate->get_translate_phrase('_Unknown');
    }

    $page_max = ceil(count($sessions_array) / $limit);

    return [
      'name' => $this->Translate->get_translate_module_phrase('module_page_admintime', '_adminSession') . ' ' . $this->General->checkName($steamid),
      'sessions' => $sessions,
      'max_pages' => $page_max
    ];
  }

  public function renderCharts($steamid, $server_id = null, $date_start = null, $date_end = null)
  {
    $sessions = $this->getAdminSessionsDaysLogs($steamid, $server_id, $date_start, $date_end);
    $categories = [];
    $time = [];
    foreach ($sessions as $session) {
      $categories[] = $session['date'];
      $time[] = $session['total_played_time'];
    }

    return [
      'name' => $this->General->checkName($steamid),
      'categories' => $categories,
      'time' => $time,
      'start' => $date_start,
      'end' => $date_end
    ];
  }

  public function getAdmins($steamid, $server_id)
  {
    if (!empty($this->Db->db_data['IksAdminNew'])) {
      $sql = 'SELECT DISTINCT 
            ads.*,
            ads.steam_id as steamid
            FROM iks_admins ads
            JOIN iks_rewards asr ON ads.steam_id = asr.admin_id
            JOIN iks_admin_to_server ass ON ads.id = ass.admin_id
            WHERE `steam_id` != "CONSOLE" AND (`end_at` > UNIX_TIMESTAMP() OR `end_at` IS NULL) AND `is_disabled` = 0';
      $params = [];
      if ($server_id !== 'all' && $server_id !== null) {
        $sql .= ' AND (ass.server_id = :serverId OR ass.server_id IS NULL)';
        $params['serverId'] = $server_id;
      }
      if (!empty($steamid) && $steamid !== null) {
        $sql .= ' AND ads.steam_id = :admin_id';
        $params['admin_id'] = $steamid;
      }
      $admins = $this->Db->queryAll('IksAdminNew', 0, 0, $sql, $params);
    } elseif (!empty($this->Db->db_data['IksAdmin'])) {
      $sql = 'SELECT DISTINCT 
            ads.*,
            ads.sid as steamid
            FROM iks_admins ads
            JOIN iks_rewards asr ON ads.sid = asr.admin_id
            WHERE 1=1';
      $params = [];
      if ($server_id !== 'all' && $server_id !== null) {
        $sql .= ' AND (
            ads.server_id = :serverId OR
            ads.server_id LIKE CONCAT(:serverId, \';%\') OR
            ads.server_id LIKE CONCAT(\'%;\', :serverId) OR
            ads.server_id LIKE CONCAT(\'%;\', :serverId, \';%\') OR
            ads.server_id IS NULL
        )';
        $params['serverId'] = $server_id;
      }
      if (!empty($steamid) && $steamid !== null) {
        $sql .= ' AND ads.sid = :admin_id';
        $params['admin_id'] = $steamid;
      }
      $admins = $this->Db->queryAll('IksAdmin', 0, 0, $sql, $params);
    } elseif (!empty($this->Db->db_data['AdminSystem'])) {
      $sql = 'SELECT DISTINCT ads.*,
                `ass`.`expires` AS `end`,
                `ag`.`name` AS `group`
                FROM as_admins ads
                JOIN as_admin_time asr ON ads.steamid = asr.admin_id
                JOIN as_admins_servers ass ON ads.id = ass.admin_id
                JOIN as_groups ag ON ass.group_id = ag.id
                WHERE (`ass`.`expires` > UNIX_TIMESTAMP() OR `ass`.`expires` = 0) AND `ads`.`steamid` != 0';
      $params = [];
      if ($server_id !== 'all' && $server_id !== null) {
        $sql .= ' AND (ass.server_id = :serverId OR ass.server_id = -1)';
        $params['serverId'] = $server_id;
      }
      if (!empty($steamid) && $steamid !== null) {
        $sql .= ' AND ads.steamid = :admin_id';
        $params['admin_id'] = $steamid;
      }
      $admins = $this->Db->queryAll('AdminSystem', 0, 0, $sql, $params);
      $settings = $this->Settings->getSettings();

      $cleanGroups = [];
      if (!empty($settings['clean_groups'])) {
        if (is_string($settings['clean_groups'])) {
          $cleanGroups = array_filter(array_map('trim', explode(';', $settings['clean_groups'])));
        } elseif (is_array($settings['clean_groups'])) {
          $cleanGroups = $settings['clean_groups'];
        }
      }

      if (!empty($cleanGroups)) {
        $admins = array_filter($admins, function ($admin) use ($cleanGroups) {
          return !in_array($admin['group'] ?? null, $cleanGroups, true);
        });
        $admins = array_values($admins);
      }
    }
    return $admins;
  }

  public function getAdminSessionsLogs($steamid, $server_id, $date_start, $date_end)
  {
    $sessionLogs = [];
    if (!empty($this->Db->db_data['IksAdmin'])) {
      $dbKey = 'IksAdmin';
      $table = 'iks_rewards';
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      $dbKey = 'IksAdminNew';
      $table = 'iks_rewards';
    } elseif (!empty($this->Db->db_data['AdminSystem'])) {
      $dbKey = 'AdminSystem';
      $table = 'as_admin_time';
    } else {
      return $sessionLogs;
    }

    $sql = "SELECT * FROM `$table` WHERE `admin_id` = :steamid64";
    $params['steamid64'] = $steamid;

    if (!empty($date_start)) {
      $sql .= ' AND `connect_time` >= :connect_time';
      $params['connect_time'] = $date_start;
    }
    if ($server_id !== 'all' && $server_id !== null) {
      $sql .= ' AND `server_id` = :serverId';
      $params['serverId'] = $server_id;
    }
    if (!empty($date_end)) {
      $sql .= ' AND `connect_time` <= :disconnect_time';
      $params['disconnect_time'] = $date_end;
    }
    $sql .= ' AND `connect_time` < `disconnect_time` ORDER BY `connect_time` DESC';
    $sessionLogs = $this->Db->queryAll($dbKey, 0, 0, $sql, $params);
    return $sessionLogs;
  }

  public function getAdminSessionsDaysLogs($steamid, $server_id = null, $date_start = null, $date_end = null)
  {
    $sessionLogs = [];
    if (!empty($this->Db->db_data['IksAdmin'])) {
      $dbKey = 'IksAdmin';
      $table = 'iks_rewards';
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      $dbKey = 'IksAdminNew';
      $table = 'iks_rewards';
    } elseif (!empty($this->Db->db_data['AdminSystem'])) {
      $dbKey = 'AdminSystem';
      $table = 'as_admin_time';
    } else {
      return $sessionLogs;
    }

    $params = [];
    $sql_params = '';

    if (empty($date_start) && empty($date_end)) {
      $date_end = time();
      $date_start = $date_end - 29 * 86400;
    }

    if (!empty($date_start)) {
      $sql_params .= ' AND `connect_time` >= :connect_time';
      $params['connect_time'] = $date_start;
    }
    if ($server_id !== 'all' && $server_id !== null) {
      $sql_params .= ' AND `server_id` = :serverId';
      $params['serverId'] = $server_id;
    }
    if (!empty($date_end)) {
      $sql_params .= ' AND `connect_time` <= :disconnect_time';
      $params['disconnect_time'] = $date_end;
    }
    $sql_params .= ' AND `connect_time` < `disconnect_time`';
    $sql = "SELECT 
        admin_id,
        admin_name,
        DATE_FORMAT(FROM_UNIXTIME(connect_time), '%d.%m') as date,
        SUM(played_time) as total_played_time
    FROM {$table} WHERE `admin_id` = :steamid64 {$sql_params}
    GROUP BY DATE(FROM_UNIXTIME(connect_time)), admin_id, admin_name
    ORDER BY connect_time ASC";
    $params['steamid64'] = $steamid;
    $sessionLogs = $this->Db->queryAll($dbKey, 0, 0, $sql, $params);
    return $sessionLogs;
  }

  public function getAdminPlayedTime($steamid, $date_start, $date_end, $server_id)
  {
    if (!empty($this->Db->db_data['IksAdminNew'])) {
      $base = 'IksAdminNew';
      $table = 'iks_rewards';
    } elseif (!empty($this->Db->db_data['IksAdmin'])) {
      $base = 'IksAdmin';
      $table = 'iks_rewards';
    } elseif (!empty($this->Db->db_data['AdminSystem'])) {
      $base = 'AdminSystem';
      $table = 'as_admin_time';
    } else {
      return 0;
    }
    $sql = "SELECT SUM(`played_time`) AS `total_played_time` FROM `$table` WHERE `admin_id` = :steamid64";
    $params = [];
    if (!empty($date_start) && $date_start !== null) {
      $sql .= " AND `connect_time` >= :connect_time";
      $params['connect_time'] = $date_start;
    }
    if ($server_id !== 'all' && $server_id !== null) {
      $sql .= " AND `server_id` = :serverId";
      $params['serverId'] = $server_id;
    }
    if (!empty($date_end) && $date_end !== null) {
      $sql .= " AND `connect_time` <= :disconnect_time";
      $params['disconnect_time'] = $date_end;
    }
    $params['steamid64'] = $steamid;
    $playedTime = $this->Db->query($base, 0, 0, $sql, $params);
    return $playedTime['total_played_time'] ?? 0;
  }

  public function getServerById($serverId)
  {
    $servers = $this->Modules->get_settings_modules('module_page_admintime', 'servers');
    $server = array_search($serverId, array_column($servers, 'id'));
    return $server !== false ? $servers[$server] : null;
  }
}
