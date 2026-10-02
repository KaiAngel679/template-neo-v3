<?php

use app\modules\module_page_mon_settings\ext\Monitoring;

$Router->map('GET|POST', 'monitoring/[settings|mods|logs_access|server_list:page]/', 'monitoring');
$Map = $Router->match();
$page = $Map['params']['page'] ?? 'settings';

if (!in_array($page, ['settings', 'mods', 'logs_access', 'server_list'])) {
    get_iframe('404', $Translate->get_translate_phrase('_Page_not_found')) && die();
}


$Mon = new Monitoring($Db, $General, $Translate, $Modules, $Router, $Notifications);
if (isset($_SESSION['user_admin'])) {
  if (isset($_POST['getLogsList'])) {
    exit(json_encode($Mon->getLogsRender($_POST['page'], $_POST['limit'], $_POST['server']), true));
  }
  if (isset($_POST['getAccessList'])) {
    exit(json_encode($Mon->getAccessRender(), true));
  }
  if (isset($_POST['servers_access'])) {
    exit(json_encode($Mon->addAccess($_POST), true));
  }
  if (isset($_POST['mon_access_del'])) {
    exit(json_encode($Mon->delAccess($_POST['steamid']), true));
  }
  if (isset($_POST['mon_settings'])) {
    exit(json_encode($Mon->editSettings($_POST), true));
  }
  if (isset($_POST['installTables'])) {
    exit(json_encode($Mon->installTables(), true));
  }
  if (isset($_POST['mods_grid'])) {
    exit(json_encode($Mon->editModSettings($_POST), true));
  }
  if (isset($_POST['mod_name']) && empty($_POST['mode_id'])) {
    exit(json_encode($Mon->AddMods($_POST), true));
  }
  if (isset($_POST['mode_edit'])) {
    exit(json_encode($Mon->editMode($_POST), true));
  }
  if (isset($_POST['submod_add'])) {
    exit(json_encode($Mon->addSubmod($_POST), true));
  }
  if (isset($_POST['submod_edit'])) {
    exit(json_encode($Mon->editSubmod($_POST), true));
  }
  if (isset($_POST['submod_del'])) {
    exit(json_encode($Mon->delSubmod($_POST['submod_id']), true));
  }
  if(isset($_POST['changeSort'])) {
    if (isset($_POST['order'])) {
      exit(json_encode($Mon->SortServers($_POST), true));
    }
    exit(json_encode($Mon->SortMods($_POST), true));
  }
  if (isset($_POST['mon_mod_del'])) {
    exit(json_encode($Mon->delMod($_POST['mod']), true));
  }
  if (isset($_POST['del_mod_file'])) {
    exit(json_encode($Mon->deleteModFile($_POST), true));
  }
  if (isset($_POST['edit_server'])) {
    exit(json_encode($Mon->editServer($_POST), true));
  }
  if (isset($_POST['del_server'])) {
    exit(json_encode($Mon->delServer($_POST['server_id']), true));
  }
  if (isset($_POST['bulk_status'])) {
    exit(json_encode($Mon->bulkChangeStatus($_POST), true));
  }
  if (isset($_POST['panel_access_settings'])) {
    exit(json_encode($Mon->editPanelAccess($_POST), true));
  }
} else {
  get_iframe('503', $Translate->get_translate_phrase('_accessDenied')) && die();
}


