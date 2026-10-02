<?php

use app\modules\module_page_admintime\ext\AdminTimeCore;

$Router->map('GET|POST', 'admintime/[home|settings:page]/', 'admintime');
$Map = $Router->match();
$page = $Map['params']['page'] ?? 'home';

$AdminTimeCore = new AdminTimeCore($Db,  $General, $Translate, $Modules);
$servers = $AdminTimeCore->Settings->getServers();
$settings = $AdminTimeCore->Settings->getSettings();
$accesses = $AdminTimeCore->Access->getAccesses();
$hasAccess = $AdminTimeCore->Access->checkAccess();

if($hasAccess){
  if(!isset($_SESSION['user_admin']) && $page == 'settings') {
    exit(get_iframe(401, $Translate->get_translate_phrase('_accessDenied')));
  }
  require_once MODULES . 'module_page_admintime/forward/router.php';
} else {
  exit(get_iframe(401, $Translate->get_translate_phrase('_accessDenied')));
}
