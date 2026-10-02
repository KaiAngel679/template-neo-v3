<?php

namespace app\modules\module_page_admintime\ext;

class AdminTimeSettings
{
  protected $Db, $General, $Translate, $Modules;
  public function __construct($Db, $General, $Translate, $Modules)
  {
    $this->Db = $Db;
    $this->General = $General;
    $this->Translate = $Translate;
    $this->Modules = $Modules;
  }

  public function getSettings()
  {
    return $this->Modules->get_settings_modules('module_page_admintime', 'settings');
  }

  public function getServers()
  {
    return $this->Modules->get_settings_modules('module_page_admintime', 'servers');
  }

  public function getServerById($server_id)
  {
    $servers = $this->getServers();
    foreach ($servers as $server) {
      if ($server['id'] == $server_id) {
        return $server;
      }
    }
    return null;
  }

  public function saveSettings($post)
  {
    $settings = $this->getSettings();
    isset($post['auto_add_access']) ? $settings['auto_add_access'] = 1 : $settings['auto_add_access'] = 0;
    isset($post['clean_groups']) ? $settings['clean_groups'] = $post['clean_groups'] : $settings['clean_groups'] = '';
    $this->Modules->put_settings_modules('module_page_admintime', 'settings', $settings);
    return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_settingsSaved')];
  }

  public function addServer($name, $server_id)
  {
    if (empty($name) && empty($server_id)) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_incorrectData')];
    }
    if(!is_numeric($server_id)) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_incorrectData')];
    }
    $servers = $this->getServers();
    $maxId = 0;
    foreach ($servers as $server) {
      if (isset($server['id']) && $server['id'] > $maxId) {
        $maxId = $server['id'];
      }
    }
    $newId = $maxId + 1;
    $servers[] = [
      'id' => $newId,
      'name' => $name,
      'server_id' => $server_id
    ];
    $this->Modules->put_settings_modules('module_page_admintime', 'servers', $servers);
    $server = [
      'id' => $newId,
      'name' => $name,
      'server_id' => $server_id
    ];
    return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_admintime', '_LINK_SERVER'), 'server' => $server];
  }

  public function deleteServer($server_id){
    if (empty($server_id) || !is_numeric($server_id)) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_incorrectData')];
    }
    $servers = $this->getServers();
    foreach ($servers as $key => $server) {
      if ($server['id'] == $server_id) {
        unset($servers[$key]);
        $this->Modules->put_settings_modules('module_page_admintime', 'servers', $servers);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_admintime', '_SERVER_DELETED')];
      }
    }
    return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_serverNotFound')];
  }
}
