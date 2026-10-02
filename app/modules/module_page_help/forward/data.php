<?php

use app\modules\module_page_help\ext\Help;

$help = new Help($Db, $General, $Translate, $Modules, $Router, $Notifications);


$Router->map('GET|POST', 'help/[:page]/', 'help');

$Map = $Router->match();
$page = $Map['params']['page'] ?? '';

if($page == 'install'){
  require MODULES . 'module_page_help/includes/install.php';
}

require MODULES . 'module_page_help/forward/routes.php';


$Modules->set_page_title($Translate->get_translate_module_phrase('module_page_help', '_helpTitle') . ' | ' . $General->arr_general['short_name']);
$Modules->set_page_description($Translate->get_translate_module_phrase('module_page_help', '_helpTitle'));