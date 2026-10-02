<?php


namespace app\modules\module_page_mon_settings\ext;

class Monitoring
{
  public $Db;
  public $General;
  public $Translate;
  public $Modules;
  public $Router;
  public $Notifications;

  public $Settings;

  public $ModSettings;

  public function __construct($Db, $General, $Translate, $Modules, $Router, $Notifications)
  {
    $this->Db = $Db;
    $this->General = $General;
    $this->Translate = $Translate;
    $this->Modules = $Modules;
    $this->Router = $Router;
    $this->Notifications = $Notifications;
    $this->Settings = $this->getSettings();
  }

  public function tableSearch()
  {
    $status = false;
    if ($this->Db->mysql_table_search('Core', 0, 0, "lvl_web_monitoring_access") && $this->Db->mysql_table_search('Core', 0, 0, "lvl_web_monitoring_access")) {
      $status = true;
    }
    return $status;
  }

  public function getLogs()
  {
    return $this->Db->queryAll("Core", 0, 0, "SELECT * FROM lvl_web_monitoring_logs ORDER BY `id` DESC");
  }

  public function getServerWithId($id)
  {
    $key = array_search($id, array_column($this->General->server_list, 'id'));
    return $this->General->server_list[$key] ?? [];
  }

  public function getLogsWithServerId($server)
  {
    return $this->Db->queryAll("Core", 0, 0, "SELECT * FROM lvl_web_monitoring_logs WHERE `server` = :server ORDER BY `id` DESC", ["server" => $server]);
  }

  public function getAccess()
  {
    return $this->Db->queryAll("Core", 0, 0, "SELECT * FROM lvl_web_monitoring_access");
  }

  public function getSettings()
  {
    return file_exists(MODULES . 'module_page_mon_settings/settings.php') ? require MODULES . 'module_page_mon_settings/settings.php' : [];
  }

  private function modsJsonPath()
  {
    return MODULES . 'module_page_mon_settings/mods.json';
  }

  public function getMods()
  {
    $path = $this->modsJsonPath();
    if (file_exists($path)) {
      $data = json_decode(file_get_contents($path), true);
      if (is_array($data)) return $this->normalizeMods($data);
    }
    $legacy = MODULES . 'module_page_mon_settings/mods.php';
    if (file_exists($legacy)) {
      $arr = require $legacy;
      if (is_array($arr)) {
        $normalized = $this->normalizeMods($arr);
        $this->saveMods($normalized);
        return $normalized;
      }
    }
    $this->saveMods([]);
    return [];
  }

  private function normalizeMods($mods)
  {
    $valid_server_mods = array_map('strval', array_column($this->General->server_list, 'server_mod'));
    $serversByMod = [];
    foreach ($this->General->server_list as $srv) {
      $sm = (string)($srv['server_mod'] ?? '');
      if ($sm === '') continue;
      $serversByMod[$sm][(string)$srv['id']] = true;
    }

    $out = [];
    foreach ($mods as $m) {
      if (!is_array($m) || empty($m['name'])) continue;
      $mode = [
        'name'    => $m['name'],
        'image_1' => $m['image_1'] ?? '',
        'image_2' => $m['image_2'] ?? '',
        'sort'    => isset($m['sort']) ? (int)$m['sort'] : 0,
      ];
      if (!empty($m['video'])) $mode['video'] = $m['video'];
      if (!empty($m['description'])) $mode['description'] = $m['description'];

      if (!empty($m['servers']) && is_array($m['servers'])) {
        $mode['servers'] = array_values(array_map('strval', $m['servers']));
      }

      $modeServers = $serversByMod[$m['name']] ?? [];

      $submods = [];
      if (!empty($m['submods']) && is_array($m['submods'])) {
        foreach ($m['submods'] as $sm) {
          if (!is_array($sm)) continue;
          $sub = [
            'id'    => isset($sm['id']) ? (int)$sm['id'] : 0,
            'title' => $sm['title'] ?? '',
            'sort'  => isset($sm['sort']) ? (int)$sm['sort'] : 0,
          ];
          $servers = [];
          if (!empty($sm['servers']) && is_array($sm['servers'])) {
            foreach ($sm['servers'] as $sid) {
              $sid = (string)$sid;
              if (isset($modeServers[$sid])) $servers[] = $sid;
            }
          }
          if ($servers) $sub['servers'] = $servers;
          $submods[] = $sub;
        }
        usort($submods, function ($a, $b) {
          return ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0);
        });
      }
      if ($submods) $mode['submods'] = $submods;
      $out[] = $mode;
    }
    usort($out, function ($a, $b) {
      return ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0);
    });
    unset($valid_server_mods);
    return $out;
  }

  private function saveMods(array $mods)
  {
    $path = $this->modsJsonPath();
    $dir = dirname($path);
    if (is_dir($dir)) @chmod($dir, 0777);
    if (file_exists($path)) @chmod($path, 0666);
    return file_put_contents($path, json_encode($mods, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
  }

  private function findModeIndex(array &$mods, $name)
  {
    foreach ($mods as $i => $m) if (($m['name'] ?? '') === (string)$name) return $i;
    return -1;
  }

  private function findSubmodLocation(array &$mods, $submodId)
  {
    $submodId = (int)$submodId;
    foreach ($mods as $i => $m) {
      if (!isset($m['submods'])) continue;
      foreach ($m['submods'] as $j => $sm) {
        if ((int)($sm['id'] ?? 0) === $submodId) return [$i, $j];
      }
    }
    return null;
  }

  private function nextSubmodId(array $mods)
  {
    $max = 0;
    foreach ($mods as $m) {
      foreach ($m['submods'] ?? [] as $sm) {
        $id = (int)($sm['id'] ?? 0);
        if ($id > $max) $max = $id;
      }
    }
    return $max + 1;
  }

  private function getAssignedServerIds(array $mods)
  {
    $ids = [];
    foreach ($mods as $m) {
      foreach ($m['submods'] ?? [] as $sm) foreach ($sm['servers'] ?? [] as $sid) $ids[(string)$sid] = true;
    }
    return $ids;
  }

  public function getServersForMode($modeName)
  {
    $out = [];
    foreach ($this->General->server_list as $srv) {
      if ((string)($srv['server_mod'] ?? '') === (string)$modeName) $out[] = $srv;
    }
    return $out;
  }

  public function getRootServersForMode(array $mode)
  {
    $assigned = [];
    foreach ($mode['submods'] ?? [] as $sm) foreach ($sm['servers'] ?? [] as $sid) $assigned[(string)$sid] = true;

    $allByMode = [];
    foreach ($this->getServersForMode($mode['name']) as $srv) {
      if (!isset($assigned[(string)$srv['id']])) $allByMode[(string)$srv['id']] = $srv;
    }

    $out = [];
    if (!empty($mode['servers']) && is_array($mode['servers'])) {
      foreach ($mode['servers'] as $sid) {
        $sid = (string)$sid;
        if (isset($allByMode[$sid])) {
          $out[] = $allByMode[$sid];
          unset($allByMode[$sid]);
        }
      }
    }
    foreach ($allByMode as $srv) $out[] = $srv;
    return $out;
  }

  public function getModsList()
  {
    $mods = $this->getMods();
    $mods_list = [];
    foreach ($mods as $value) {
      $mods_list[] = [
        'name' => $value['name'],
        'description' => $this->Translate->get_translate_phrase($this->General->mods[$value['name']] ?? '') ?: 'Описание отсутствует',
        'image_1' => $value['image_1'],
        'image_2' => $value['image_2'],
        'sort' => $value['sort'],
      ];
    }
    return $mods_list;
  }

  public function getServerName($id)
  {
    foreach ($this->General->server_list as $srv) {
      if ((string)$srv['id'] === (string)$id) return $srv['name'] ?? ('#' . $id);
    }
    return '#' . $id;
  }
  public function getAccessBySteam($steamid)
  {
    return $this->Db->query("Core", 0, 0, "SELECT * FROM lvl_web_monitoring_access WHERE `steamid` = :steamid LIMIT 1", ["steamid" => $steamid]);
  }
  public function getLogsRender($page, $limit, $server)
  {
    $html = "";
    if ($server == 'all') {
      $logs = $this->getLogs();
    } else {
      $logs = $this->getLogsWithServerId($server);
    }
    $count = count($logs);
    $offset = ($page - 1) * $limit;
    $logsPage = array_slice($logs, $offset, $limit);

    foreach ($logsPage as $key) {
      $date =  date('d.m.Y H:i', strtotime($key['date']));
      $admin = $key['admin'];
      $admin_name = $this->General->checkName($key['admin']);
      $steam = $key['steamid'];
      $steam_name = $this->General->checkName($key['steamid']);
      $server = $this->getServerWithId($key['server'])['name'];
      $duration = $key['duration'] == 0 ? $this->Translate->get_translate_phrase('_Forever') : $this->Modules->action_time_exchange_exact($key['duration']);
      $reason = $key['reason'];

      switch ($key['type']) {
        case 'mute':
          $type = $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_muted');
          break;
        case 'ban':
          $type = $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_banned');
          break;
        case 'kick':
          $type = $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_kicked');
          break;
        default:
          $type = $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_punished');
      }

      $detail = '';
      if (isset($key['duration']) && isset($key['reason'])) {
        $mute_html = '';
        if (!empty($key['mute_type'])) {
          switch ($key['mute_type']) {
            case 'full':
              $mute_type = $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_muteTypeBoth');
              break;
            case 'gag':
              $mute_type = $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_muteTypeGag');
              break;
            case 'mute':
              $mute_type = $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_muteTypeMute');
              break;
            default:
              $mute_type = '';
          }
          $mute_html = '<span>' . $this->Translate->get_translate_phrase('_Type') . ': ' . $mute_type . '</span>';
        }

        $detail .= <<<HTML
                <span>{$this->Translate->get_translate_module_phrase('module_page_mon_settings', '_term')}: {$duration}</span>
                <span>{$this->Translate->get_translate_module_phrase('module_page_mon_settings', '_reason')}: {$reason}</span>
                {$mute_html}
            HTML;
      }

      $html .= <<<HTML
            <div class="mon__logs-block">
                <span class="mon__logs-time">{$date}</span>
                <span class="mon__logs-title">{$this->Translate->get_translate_module_phrase('module_page_mon_settings', '_details')}</span>
                <div class="mon__logs-details">
                    <span>{$this->Translate->get_translate_phrase('_Admin')} <a href="/profiles/{$admin}/?search=1">{$admin_name}</a> {$type} {$this->Translate->get_translate_module_phrase('module_page_mon_settings', '_playerOf')} <a href="/profiles/{$steam}/?search=1">{$steam_name}</a></span>
                    <span>{$this->Translate->get_translate_phrase('_Server')}: {$server}</span>
                    {$detail}
                </div>
            </div>
        HTML;
    }
    $max_pages = ceil($count / $limit);
    $pagination = Pagination($max_pages, $page, 1);

    return [
      'status' => 'success',
      'html' => $html,
      'pagination' => $pagination,
      'count' => $count
    ];
  }


  public function getAccessRender()
  {
    $html = "";
    $access = $this->getAccess();
    foreach ($access as $key) {
      $avatar = $this->General->getAvatar($key['steamid'], 1);
      $name = $this->General->checkName($key['steamid']);
      $html .= <<<HTML
        <li class="mon__access-block">
          <img src="{$avatar}" alt="">
          <div class="mon__access-details">
            <a href="/profiles/{$key['steamid']}/?search=1" target="_blank" class="mon__access-nick">{$name} (IDs: {$key['servers']})</a>
            <span>{$key['steamid']}</span>
          </div>
          <button class="button-delete mon__access-delete" id="access_del" id_del="{$key['steamid']}">
            <svg>
                <use href="/resources/img/sprite.svg#trash"></use>
            </svg>
          </button>
        </li>
      HTML;
    }
    return ['status' => 'success', "html" => $html];
  }

  public function addAccess($post)
  {
    $steam = $post['steam_mon'];
    if (!preg_match('/^7656119\d{10}$/', $steam)) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_incorrectSteam')];
    } elseif (empty($steam)) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_emptySteam')];
    } elseif (count($post['servers_access']) < 1) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_selectServer')];
    } elseif (in_array($steam, array_column($this->getAccess(), 'steamid'), true)) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_existSteam')];
    }
    $servers = implode(',', $post['servers_access']);
    $this->Db->query("Core", 0, 0, "INSERT INTO `lvl_web_monitoring_access` (`steamid`, `servers`) VALUES (:steam, :servers);", ['steam' => $steam, 'servers' => $servers]);
    $avatar = $this->General->getAvatar($steam, 1);
    $name = $this->General->checkName($steam);
    $html = '';
    $html .= <<<HTML
      <li class="mon__access-block">
        <img src="{$avatar}" alt="">
        <div class="mon__access-details">
          <a href="/profiles/{$steam}/?search=1" target="_blank" class="mon__access-nick">{$name} (IDs: {$servers})</a>
          <span>{$steam}</span>
        </div>
        <button class="button-delete mon__access-delete" id="access_del" id_del="{$steam}">
          <svg>
              <use href="/resources/img/sprite.svg#trash"></use>
          </svg>
        </button>
      </li>
    HTML;
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully'), 'html' => $html];
  }

  public function delAccess($steamid)
  {
    $this->Db->query("Core", 0, 0, "DELETE FROM `lvl_web_monitoring_access` WHERE `steamid` = :steamid", ['steamid' => $steamid]);
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully')];
  }

  public function editSettings($post)
  {
    chmod(MODULES . 'module_page_mon_settings/settings.php', 0777);
    $settings = $this->getSettings();
    $settings['prime'] = empty($post['mon_prime']) ? 0 : 1;
    $settings['vip'] = empty($post['mon_vip']) ? 0 : 1;
    $settings['admin'] = empty($post['mon_admin']) ? 0 : 1;
    $settings['panel'] = empty($post['mon_panel']) ? 0 : 1;
    $settings['type'] = empty($post['mon_type']) ? 0 : 1;
    $settings['time'] = empty($post['mon_time']) ? 30 : $post['mon_time'];
    $settings['password'] = empty($post['mon_password']) ? '' : $post['mon_password'];
    $settings['debug'] = empty($post['mon_debug']) ? 0 : 1;
    $settings['plugs'] = empty($post['mon_plugs']) ? 0 : 1;
    $settings['filter_modes'] = empty($post['mon_filter_modes']) ? 0 : 1;
    $settings['server_card_count'] = empty($post['mon_server_card_count']) ? 3 : $post['mon_server_card_count'];
    $settings['punish_all_server'] = empty($post['mon_punish_all_server']) ? 0 : 1;
    $settings['server_table_count'] = empty($post['mon_server_table_count']) ? 1 : $post['mon_server_table_count'];
    $settings['show_ping'] = empty($post['mon_show_ping']) ? 0 : 1;
    $settings['show_online_line'] = empty($post['mon_show_online_line']) ? 0 : 1;
    file_put_contents(MODULES . 'module_page_mon_settings/settings.php', '<?php return ' . var_export($settings, true) . ';');
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_dataChanged')];
  }

  public function editModSettings($post)
  {
    chmod(MODULES . 'module_page_mon_settings/settings.php', 0777);
    $settings = $this->getSettings();
    $settings['enable_mods'] = empty($post['mon_mods']) ? 0 : 1;
    $settings['enable_emptys'] = empty($post['mon_emptys']) ? 0 : 1;
    $settings['centered_mods'] = empty($post['mon_centered_mods']) ? 0 : 1;
    $settings['server_counter'] = empty($post['mon_server_counter']) ? 0 : 1;
    $settings['mon_button'] = empty($post['mon_button']) ? 0 : 1;
    $settings['grid_counts'] =  empty($post['mods_grid']) ? '6' : $post['mods_grid'];
    file_put_contents(MODULES . 'module_page_mon_settings/settings.php', '<?php return ' . var_export($settings, true) . ';');
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_dataChanged')];
  }

  public function addMods($post)
  {
    if (empty($post['mod_name'])) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];

    $mods = $this->getMods();
    $global = $this->General->getMods();
    $code = isset($global[$post['mod_name']]) ? $post['mod_name'] : '';
    if (!$code) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];

    foreach ($mods as $m) {
      if (($m['name'] ?? '') === $code) {
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_existMode')];
      }
    }

    $hasFirst = !empty($_FILES['mod_image_1']) && $_FILES['mod_image_1']['error'] !== UPLOAD_ERR_NO_FILE;
    if (!$hasFirst || $_FILES['mod_image_1']['error'] !== UPLOAD_ERR_OK)
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_emptyImages')];

    $hasSecond = !empty($_FILES['mod_image_2']) && $_FILES['mod_image_2']['error'] !== UPLOAD_ERR_NO_FILE;
    if ($hasSecond && $_FILES['mod_image_2']['error'] !== UPLOAD_ERR_OK)
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $uploadDir = MODULES . 'module_block_main_servers/assets/img/mods/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true))
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];

    $ext1 = strtolower(pathinfo($_FILES['mod_image_1']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext1, $allowed, true))
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
    $fileName1 = $code . '_1.' . $ext1;
    $full1 = $uploadDir . $fileName1;
    if (!move_uploaded_file($_FILES['mod_image_1']['tmp_name'], $full1))
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];

    $fileName2 = '';
    $full2 = '';
    if ($hasSecond) {
      $ext2 = strtolower(pathinfo($_FILES['mod_image_2']['name'], PATHINFO_EXTENSION));
      if (!in_array($ext2, $allowed, true)) {
        @unlink($full1);
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
      }
      $fileName2 = $code . '_2.' . $ext2;
      $full2 = $uploadDir . $fileName2;
      if (!move_uploaded_file($_FILES['mod_image_2']['tmp_name'], $full2)) {
        @unlink($full1);
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
      }
    }

    $videoName = '';
    $hasVideo = !empty($_FILES['mod_video']) && $_FILES['mod_video']['error'] !== UPLOAD_ERR_NO_FILE;
    if ($hasVideo) {
      if ($_FILES['mod_video']['error'] !== UPLOAD_ERR_OK) {
        @unlink($full1);
        if ($fileName2 && $full2) @unlink($full2);
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
      }
      $allowedVideo = ['mp4', 'webm', 'ogg', 'mov', 'mkv'];
      $vext = strtolower(pathinfo($_FILES['mod_video']['name'], PATHINFO_EXTENSION));
      if (!in_array($vext, $allowedVideo, true)) {
        @unlink($full1);
        if ($fileName2 && $full2) @unlink($full2);
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
      }
      $videoName = $code . '_video.' . $vext;
      $videoFull = $uploadDir . $videoName;
      if (!move_uploaded_file($_FILES['mod_video']['tmp_name'], $videoFull)) {
        @unlink($full1);
        if ($fileName2 && $full2) @unlink($full2);
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
      }
    }

    $nextSort = 0;
    foreach ($mods as $m) if (($m['sort'] ?? 0) > $nextSort) $nextSort = (int)$m['sort'];
    $nextSort++;

    $entry = [
      'name'    => $code,
      'image_1' => $fileName1,
      'image_2' => $fileName2,
      'sort'    => $nextSort,
    ];
    if ($videoName) $entry['video'] = $videoName;
    $description = trim($post['mod-description'] ?? $post['description'] ?? '');
    if ($description !== '') $entry['description'] = $description;
    $mods[] = $entry;

    if (!$this->saveMods($mods)) {
      @unlink($full1);
      if ($hasSecond && !empty($fileName2) && file_exists($full2)) @unlink($full2);
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
    }

    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully')];
  }

  public function editMode($post)
  {
    $mods = $this->getMods();
    $idx = $this->findModeIndex($mods, $post['mode_id'] ?? '');
    if ($idx === -1) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_notExistMode')];

    $global = $this->General->getMods();
    if (!empty($post['mod_name']) && isset($global[$post['mod_name']])) {
      $newName = $post['mod_name'];
      foreach ($mods as $i => $m) {
        if ($i !== $idx && ($m['name'] ?? '') === $newName) {
          return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_existMode')];
        }
      }
      $mods[$idx]['name'] = $newName;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $uploadDir = MODULES . 'module_block_main_servers/assets/img/mods/';
    $code = $mods[$idx]['name'];

    if (!empty($_FILES['mod_image_1']) && $_FILES['mod_image_1']['error'] !== UPLOAD_ERR_NO_FILE) {
      if ($_FILES['mod_image_1']['error'] !== UPLOAD_ERR_OK) {
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
      }
      $ext1 = strtolower(pathinfo($_FILES['mod_image_1']['name'], PATHINFO_EXTENSION));
      if (!in_array($ext1, $allowed, true)) {
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
      }
      $name1 = $code . '_1.' . $ext1;
      if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);
      if (!move_uploaded_file($_FILES['mod_image_1']['tmp_name'], $uploadDir . $name1)) {
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
      }
      $old1 = $mods[$idx]['image_1'] ?? '';
      if ($old1 && $old1 !== $name1 && file_exists($uploadDir . $old1)) @unlink($uploadDir . $old1);
      $mods[$idx]['image_1'] = $name1;
    }

    if (!empty($_FILES['mod_image_2']) && $_FILES['mod_image_2']['error'] !== UPLOAD_ERR_NO_FILE) {
      if ($_FILES['mod_image_2']['error'] === UPLOAD_ERR_OK) {
        $ext2 = strtolower(pathinfo($_FILES['mod_image_2']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext2, $allowed, true)) {
          return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
        }
        $name2 = $code . '_2.' . $ext2;
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);
        if (!move_uploaded_file($_FILES['mod_image_2']['tmp_name'], $uploadDir . $name2)) {
          return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
        }
        $old2 = $mods[$idx]['image_2'] ?? '';
        if ($old2 && $old2 !== $name2 && file_exists($uploadDir . $old2)) @unlink($uploadDir . $old2);
        $mods[$idx]['image_2'] = $name2;
      }
    }

    if (!empty($_FILES['mod_video']) && $_FILES['mod_video']['error'] === UPLOAD_ERR_OK) {
      $allowedVideo = ['mp4', 'webm', 'ogg', 'mov', 'mkv'];
      $vext = strtolower(pathinfo($_FILES['mod_video']['name'], PATHINFO_EXTENSION));
      if (!in_array($vext, $allowedVideo, true)) {
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
      }
      $videoName = $code . '_video.' . $vext;
      if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);
      if (!move_uploaded_file($_FILES['mod_video']['tmp_name'], $uploadDir . $videoName)) {
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
      }
      $old = $mods[$idx]['video'] ?? '';
      if ($old && $old !== $videoName && file_exists($uploadDir . $old)) @unlink($uploadDir . $old);
      $mods[$idx]['video'] = $videoName;
    }

    $description = trim($post['description'] ?? '');
    $mods[$idx]['description'] = $description;

    if (!$this->saveMods($mods)) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
    }
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_dataChanged')];
  }

  public function addSubmod($post)
  {
    $modeName = $post['mode_id'] ?? '';
    $title    = trim($post['title'] ?? '');
    if ($title === '') return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_emptySubModTitle')];
    $mods = $this->getMods();
    $idx = $this->findModeIndex($mods, $modeName);
    if ($idx === -1) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_notExistMode')];
    $submods = $mods[$idx]['submods'] ?? [];
    $nextSort = 0;
    foreach ($submods as $sm) if (($sm['sort'] ?? 0) > $nextSort) $nextSort = (int)$sm['sort'];
    $nextSort++;
    $submods[] = [
      'id'    => $this->nextSubmodId($mods),
      'title' => $title,
      'sort'  => $nextSort,
    ];
    $mods[$idx]['submods'] = $submods;
    if (!$this->saveMods($mods)) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully')];
  }

  public function editSubmod($post)
  {
    $submodId = (int)($post['submod_id'] ?? 0);
    $title    = trim($post['title'] ?? '');
    if ($title === '') return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_emptySubModTitle')];
    $mods = $this->getMods();
    $loc = $this->findSubmodLocation($mods, $submodId);
    if (!$loc) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_notExistMode')];
    [$i, $j] = $loc;
    $mods[$i]['submods'][$j]['title'] = $title;
    if (!$this->saveMods($mods)) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_dataChanged')];
  }

  public function delSubmod($submodId)
  {
    $mods = $this->getMods();
    $loc = $this->findSubmodLocation($mods, $submodId);
    if (!$loc) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_notExistMode')];
    [$i, $j] = $loc;
    array_splice($mods[$i]['submods'], $j, 1);
    if (empty($mods[$i]['submods'])) unset($mods[$i]['submods']);
    if (!$this->saveMods($mods)) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorDeleteMode')];
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully')];
  }

  public function SortMods($post)
  {
    $tree = $post['tree'] ?? null;
    if (is_string($tree)) $tree = json_decode($tree, true);
    if (!is_array($tree)) return ['error' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_sortingError')];

    $mods = $this->getMods();
    if (!$mods) return ['error' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_sortingError')];

    $modeByName = [];
    foreach ($mods as $m) $modeByName[$m['name']] = $m;

    $submodById = [];
    foreach ($mods as $m) foreach ($m['submods'] ?? [] as $sm) $submodById[(int)$sm['id']] = ['mode' => $m['name'], 'data' => $sm];

    $serversByMode = [];
    foreach ($this->General->server_list as $srv) {
      $mname = (string)($srv['server_mod'] ?? '');
      if ($mname === '') continue;
      $serversByMode[$mname][(string)$srv['id']] = true;
    }

    $newMods = [];
    $modeSort = 0;

    foreach ($tree as $node) {
      $mname = $node['id'] ?? '';
      if (!isset($modeByName[$mname])) continue;
      $mode = $modeByName[$mname];
      $mode['sort'] = ++$modeSort;
      unset($mode['submods']);

      $allowed = $serversByMode[$mname] ?? [];
      $usedInMode = [];
      $submods = [];
      $smSort = 0;
      $rootServers = [];

      foreach ($node['children'] ?? [] as $child) {
        $type = $child['type'] ?? '';
        $cid  = $child['id'] ?? '';
        if ($type === 'submod') {
          $sid = (int)$cid;
          if (!isset($submodById[$sid]) || $submodById[$sid]['mode'] !== $mname) continue;
          $sm = $submodById[$sid]['data'];
          $sm['sort'] = ++$smSort;
          unset($sm['servers']);
          $smServers = [];
          foreach ($child['children'] ?? [] as $serverNode) {
            if (($serverNode['type'] ?? '') !== 'server') continue;
            $sidv = (string)($serverNode['id'] ?? '');
            if ($sidv === '' || isset($usedInMode[$sidv]) || !isset($allowed[$sidv])) continue;
            $smServers[] = $sidv;
            $usedInMode[$sidv] = true;
          }
          if ($smServers) $sm['servers'] = $smServers;
          $submods[] = $sm;
        } elseif ($type === 'server') {
          $sidv = (string)$cid;
          if ($sidv !== '' && !isset($usedInMode[$sidv]) && isset($allowed[$sidv])) {
            $rootServers[] = $sidv;
            $usedInMode[$sidv] = true;
          }
        }
      }

      if ($submods) $mode['submods'] = $submods;
      if ($rootServers) $mode['servers'] = $rootServers;
      else unset($mode['servers']);
      $newMods[] = $mode;
    }

    if (!$newMods) return ['error' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_sortingError')];
    if (!$this->saveMods($newMods)) return ['error' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_sortingError')];
    return ['success' => $this->Translate->get_translate_phrase('_successfully')];
  }

  public function delMod($modeName)
  {
    $mods = $this->getMods();
    $idx = $this->findModeIndex($mods, $modeName);
    if ($idx === -1) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_notExistMode')];

    $modData = $mods[$idx];
    $imgDir = MODULES . 'module_block_main_servers/assets/img/mods/';
    foreach (['image_1', 'image_2', 'video'] as $k) {
      if (!empty($modData[$k]) && file_exists($imgDir . $modData[$k])) @unlink($imgDir . $modData[$k]);
    }

    array_splice($mods, $idx, 1);

    if (!$this->saveMods($mods)) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorDeleteMode')];
    }
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully')];
  }

  public function deleteModFile($post)
  {
    $modeId = $post['mode_id'] ?? '';
    $field  = $post['field']   ?? '';
    if (!in_array($field, ['image_1', 'image_2', 'video'], true)) {
      return ['status' => 'error', 'text' => 'Invalid field'];
    }
    $mods = $this->getMods();
    $idx  = $this->findModeIndex($mods, $modeId);
    if ($idx === -1) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_notExistMode')];

    $uploadDir = MODULES . 'module_block_main_servers/assets/img/mods/';
    $old = $mods[$idx][$field] ?? '';
    if ($old && file_exists($uploadDir . $old)) @unlink($uploadDir . $old);
    unset($mods[$idx][$field]);

    if (!$this->saveMods($mods)) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorUpload')];
    }
    return ['status' => 'success', 'text' => ''];
  }


  public function SortServers($post)
  {
    $order = $post['order'] ?? null;
    if (is_string($order)) $order = json_decode($order, true);
    if (!is_array($order)) return ['error' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_sortingError')];

    foreach ($order as $item) {
      $id   = (int)($item['id']   ?? 0);
      $sort = (int)($item['sort'] ?? 0);
      if (!$id) continue;
      $this->Db->query("Core", 0, 0, "UPDATE `lvl_web_servers` SET `sort` = :sort WHERE `id` = :id", ['sort' => $sort, 'id' => $id]);
    }
    return ['success' => $this->Translate->get_translate_phrase('_successfully')];
  }

  public function editServer($post)
  {
    $id = (int)($post['server_id'] ?? 0);
    if (!$id) return ['status' => 'error', 'text' => 'Invalid server ID'];

    $name    = trim($post['server_name']    ?? '');
    $bage    = trim($post['server_bage']    ?? '');
    $ip      = trim($post['server_ip_port'] ?? '');
    $rcon    = trim($post['server_rcon']    ?? '');
    $mod     = trim($post['server_mod']     ?? '');
    $game    = in_array($post['game'] ?? '', ['cs2', 'csgo'], true) ? $post['game'] : 'cs2';
    $status  = in_array((int)($post['server_status'] ?? 1), [0, 1, 2], true) ? (int)$post['server_status'] : 1;
    $stats   = trim($post['server_stats']   ?? '');
    $vip     = trim($post['server_vip']     ?? '');
    $vip_id  = (int)($post['server_vip_id'] ?? 0);
    $sb      = trim($post['server_sb']      ?? '');
    $sb_id   = trim($post['server_sb_id']   ?? '');

    $params = [
      'name' => $name,
      'bage' => $bage,
      'ip' => $ip,
      'mod' => $mod,
      'game' => $game,
      'status' => $status,
      'stats' => $stats,
      'vip' => $vip,
      'vip_id' => $vip_id,
      'sb' => $sb,
      'sb_id' => $sb_id,
      'id' => $id,
    ];
    $sql = "UPDATE `lvl_web_servers` SET
      `name_custom` = :name, `server_bage` = :bage, `ip` = :ip, `server_mod` = :mod, `server_game` = :game,
      `server_status` = :status, `server_stats` = :stats,
      `server_vip` = :vip, `server_vip_id` = :vip_id,
      `server_sb` = :sb, `server_sb_id` = :sb_id
      WHERE `id` = :id";
    if ($rcon !== '') {
      $sql = "UPDATE `lvl_web_servers` SET
        `name_custom` = :name, `server_bage` = :bage, `ip` = :ip, `rcon` = :rcon, `server_mod` = :mod, `server_game` = :game,
        `server_status` = :status, `server_stats` = :stats,
        `server_vip` = :vip, `server_vip_id` = :vip_id,
        `server_sb` = :sb, `server_sb_id` = :sb_id
        WHERE `id` = :id";
      $params['rcon'] = $rcon;
    }
    $this->Db->query("Core", 0, 0, $sql, $params);
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_dataChanged')];
  }

  public function delServer($id)
  {
    $id = (int)$id;
    if (!$id) return ['status' => 'error', 'text' => 'Invalid server ID'];
    $this->Db->query("Core", 0, 0, "DELETE FROM `lvl_web_servers` WHERE `id` = :id", ['id' => $id]);
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully')];
  }

  public function bulkChangeStatus($post)
  {
    $ids    = $post['ids'] ?? [];
    $status = isset($post['status']) ? (int)$post['status'] : -1;

    if (!is_array($ids) || empty($ids)) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_emptyData') ?: 'Нет серверов'];
    }
    if (!in_array($status, [0, 1, 2], true)) {
      return ['status' => 'error', 'text' => 'Неверный статус'];
    }

    $safeIds = array_map('intval', $ids);
    $safeIds = array_filter($safeIds);
    if (empty($safeIds)) {
      return ['status' => 'error', 'text' => 'Неверные ID серверов'];
    }

    $placeholders = implode(',', $safeIds);
    $this->Db->query(
      "Core",
      0,
      0,
      "UPDATE `lvl_web_servers` SET `server_status` = :status WHERE `id` IN ({$placeholders})",
      ['status' => $status]
    );

    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_dataChanged')];
  }

  public function installTables()
  {
    $this->Db->query("Core", 0, 0, "CREATE TABLE IF NOT EXISTS `lvl_web_monitoring_access` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `steamid` varchar(255) NULL DEFAULT NULL,
      `servers` varchar(255) NULL DEFAULT NULL,
      PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC");

    $this->Db->query("Core", 0, 0, "CREATE TABLE IF NOT EXISTS `lvl_web_monitoring_logs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `steamid` varchar(255) NULL DEFAULT NULL,
      `admin` varchar(255) NULL DEFAULT NULL,
      `type` varchar(11) NULL DEFAULT NULL,
      `duration` varchar(255) NULL DEFAULT NULL,
      `reason` varchar(255) NULL DEFAULT NULL,
      `mute_type` varchar(255) NULL DEFAULT NULL,
      `date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      `server` varchar(255) NULL DEFAULT NULL,
      PRIMARY KEY (`id`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC");

    if ($this->tableSearch()) {
      return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_installed')];
    } else {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_mon_settings', '_errorInstall')];
    }
  }

  public function editPanelAccess($post)
  {
    chmod(MODULES . 'module_page_mon_settings/settings.php', 0777);
    $settings = $this->getSettings();
    $settings['panel'] = empty($post['mon_panel']) ? 0 : 1;
    $cs2 = $post['excluded_admins_cs2'] ?? [];
    $csgo = $post['excluded_admins_csgo'] ?? [];
    $settings['excluded_admins'] = [
      'cs2'  => array_values(array_filter(array_map('strval', is_array($cs2)  ? $cs2  : []))),
      'csgo' => array_values(array_filter(array_map('strval', is_array($csgo) ? $csgo : []))),
    ];
    file_put_contents(MODULES . 'module_page_mon_settings/settings.php', '<?php return ' . var_export($settings, true) . ';');
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_dataChanged')];
  }

  public function getAdminGroups()
  {
    $groups = ['cs2' => [], 'csgo' => []];
    if (!empty($this->Db->db_data['AdminSystem'])) {
      $rows = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT `id`, `name`, `flags`, `immunity` FROM `" . $this->Db->db_data['AdminSystem'][0]['Table'] . "groups`");
      if (is_array($rows)) $groups['cs2'] = $rows;
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      $rows = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT `id`, `name`, `flags`, `immunity` FROM `" . $this->Db->db_data['IksAdminNew'][0]['Table'] . "groups`");
      if (is_array($rows)) $groups['cs2'] = $rows;
    } elseif (!empty($this->Db->db_data['IksAdmin'])) {
      $rows = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT `id`, `name`, `flags`, `immunity` FROM `" . $this->Db->db_data['IksAdmin'][0]['Table'] . "groups`");
      if (is_array($rows)) $groups['cs2'] = $rows;
    }
    if (!empty($this->Db->db_data['SourceBans'])) {
      $rows = $this->Db->queryAll('SourceBans', 0, 0, "SELECT `id`, `name`, `flags`, `immunity` FROM `" . $this->Db->db_data['SourceBans'][0]['Table'] . "srvgroups`");
      if (is_array($rows)) $groups['csgo'] = $rows;
    }
    return $groups;
  }
}
