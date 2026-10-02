<?php

namespace app\modules\module_page_admintime\ext;

class AdminTimeAccess
{
  protected $Db, $General, $Translate, $Modules, $Settings;

  public function __construct($Db, $General, $Translate, $Modules)
  {
    $this->Db = $Db;
    $this->General = $General;
    $this->Translate = $Translate;
    $this->Modules = $Modules;
    $this->Settings = new AdminTimeSettings($Db, $General, $Translate, $Modules);
  }

  public function getAccesses()
  {
    return $this->Modules->get_settings_modules('module_page_admintime', 'access');
  }

  public function addAccess($steamid)
  {
    $access = $this->getAccesses();
    $steamid = con_steam64($steamid);
    if($steamid === false) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_admintime', '_INVALID_STEAMID')];
    }
    if (!in_array($steamid, $access)) {
      $access[] = $steamid;
      $this->Modules->put_settings_modules('module_page_admintime', 'access', $access);
      $admin = [
        'steamid' => $steamid,
        'name' =>  $this->General->checkName($steamid),
        'avatar' =>  $this->General->getAvatar($steamid, 3),
        'avatarCheck' => $this->General->checkAvatar($steamid)
      ];
      return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_admintime', '_ADD_ACCESS'), 'admin' => $admin];
    }
    return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_admintime', '_ACCESS_EXISTS')];
  }

  public function delAccess($steamid)
  {
    $access = $this->getAccesses();
    $access = array_filter($access, fn($id) => $id !== $steamid);
    $this->Modules->put_settings_modules('module_page_admintime', 'access', $access);
    return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_admintime', '_WITHDRAWN_ACCESS')];
  }

  public function checkAccess()
  {
    if (isset($_SESSION['user_admin'])) {
      return true;
    }
    $accesses = $this->getAccesses();
    if (in_array($_SESSION['steamid64'], $accesses)) {
      return true;
    }
    $settings = $this->Settings->getSettings();
    if ($settings['auto_add_access'] == 1) {
      if (!empty($this->Db->db_data['IksAdmin'])) {
        $admin = $this->Db->query('IksAdmin', 0, 0, 'SELECT `sid` FROM `iks_admins` WHERE `sid` = :steamid AND (`iks_admins`.`end` > UNIX_TIMESTAMP() OR `iks_admins`.`end` = 0)', [
          'steamid' => $_SESSION['steamid64']
        ]);
      } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
        $admin = $this->Db->query('IksAdminNew', 0, 0, 'SELECT `steam_id` FROM `iks_admins` WHERE `steam_id` = :steamid AND `iks_admins`.`is_disabled` = 0', [
          'steamid' => $_SESSION['steamid64']
        ]);
      } elseif (!empty($this->Db->db_data['AdminSystem'])) {
        $admin = $this->Db->query('AdminSystem', 0, 0, 'SELECT `as_admins`.`steamid` FROM `as_admins` JOIN `as_admins_servers` ON `as_admins`.`id` = `as_admins_servers`.`admin_id` WHERE `steamid` = :steamid AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)', [
          'steamid' => $_SESSION['steamid64']
        ]);
      }
      if (!empty($admin)) {
        return true;
      }
    }
    return false;
  }
}
