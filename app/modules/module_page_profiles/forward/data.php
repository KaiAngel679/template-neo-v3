<?php

use app\modules\module_page_profiles\ext\Player;

$Router->map('GET|POST', 'profiles/[:id]/', 'profiles');
$Router->map('GET|POST', 'profiles/[:id]/[i:sid]/', 'profiles');
$Router->map('GET|POST', 'profiles/[:id]/[:page]/', 'profiles');
$Router->map('GET|POST', 'profiles/[:id]/[:page]/[i:sid]/', 'profiles');

$Map = $Router->match();
$server_id = $Map['params']['sid'] ?? 0;
$page = $Map['params']['page'] ?? 'info';
$profile = $Map['params']['id'] ?? (isset($_SESSION['steamid']) ? $_SESSION['steamid'] : '');
$search = intval($_GET['search'] ?? 0);

if (!preg_match('^(STEAM_[0-1]:[0-1]:(\d+))|(7656119[0-9]{10})^', $profile)) {
  get_iframe(404, $Translate->get_translate_phrase('_pageNotFound')) && die();
}

empty($profile) && get_iframe(404, $Translate->get_translate_phrase('_pageNotFound')) && die();

$Player = new Player($General, $Db, $Translate, $Modules, $profile, $server_id, $search);

$server_page = $Player->found[$Player->server_group]['server_group'];

switch ($page) {
  case 'info':
    $page_name = $Translate->get_translate_module_phrase('module_page_profiles', '_Info');
    break;
  case 'admin':
    $page_name = $Translate->get_translate_module_phrase('module_page_profiles', '_Admin');
    break;
  case 'stats':
    $page_name = $Translate->get_translate_module_phrase('module_page_profiles', '_Stats');
    break;
  case 'block':
    $page_name = $Translate->get_translate_module_phrase('module_page_profiles', '_Block');
    break;
  case 'friends':
    $page_name = $Translate->get_translate_module_phrase('module_page_profiles', '_Friends');
    break;
  case 'transaction':
    $page_name = $Translate->get_translate_module_phrase('module_page_profiles', '_Transaction');
    break;
}

$server_name = $Player->found[$Player->server_group]['name_servers'];

$Modules->set_page_title(empty($General->checkName($Player->get_steam_64())) ? action_text_clear($Player->get_name()) : $General->checkName($Player->get_steam_64()) . $Translate->get_translate_module_phrase('module_page_profiles', '_statsOnServ') . $server_name . ' | ' .  $General->arr_general['short_name']);
$Modules->set_page_description($Translate->get_translate_module_phrase('module_page_profiles', '_Stats') . ' ' .  empty($General->checkName($Player->get_steam_64())) ? action_text_clear($Player->get_name()) : $General->checkName($Player->get_steam_64()) . $Translate->get_translate_module_phrase('module_page_profiles', '_onServ') . $server_name . $Translate->get_translate_module_phrase('module_page_profiles', '_pageDesc') .  $General->arr_general['short_name'] . '!');
if (!$search) {
  $Modules->set_page_canonical($General->arr_general['site'] . 'profiles/' . $Player->get_steam_64() . '/?search=1');
}

$Admins = $Player->get_db_Admins();
$Groups = $Player->get_db_Groups();
$Vips = $Player->get_db_Vips();
$Bans = $Player->get_db_Bans();
$Settings = $Player->settings;
$Info = $Player->get_info();
$roles = $Player->getRoles();

if (isset($_SESSION["steamid64"]) && (($Player->get_steam_64() == $_SESSION['steamid64']) && isset($_POST['edit_info']))) {
  exit(json_encode($Player->edit_info(), true));
} elseif (isset($_POST['edit_info'])) {
  exit(json_encode(['error' => 'Access denied'], true));
}

switch ($page) {
  case 'info':
    break;
  case 'stats':
    break;
  case 'admin':
    if (empty($Admins)) :
      get_iframe(404, $Translate->get_translate_module_phrase('module_page_profiles', '_notAdmin')) && die();
    endif;
    $Warns = $Player->get_db_Warns();
    $Reports = $Player->get_db_RepCount();
    $CheckCheats = $Player->CheckCheatsCount();
    break;
  case 'block':
    $Comms = $Player->get_db_Comms();
    break;
  case 'friends':
    $friends = $Player->get_friends();
    break;
  case 'transaction':
    if (isset($_SESSION["steamid64"]) && (($Player->get_steam_64() == $_SESSION['steamid64']) || isset($_SESSION['user_admin']))) {
      $lk = $Player->get_db_lk();
      $web_shop = $Player->get_db_shop();
    } else {
      get_iframe(403, $Translate->get_translate_module_phrase('module_page_profiles', '_pageUnva')) && die();
    }
    break;
  default:
    get_iframe(403, $Translate->get_translate_module_phrase('module_page_profiles', '_pageUnva')) && die();
    break;
}
