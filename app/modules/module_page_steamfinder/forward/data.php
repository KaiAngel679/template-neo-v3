<?php

use app\modules\module_page_steamfinder\ext\Controllers\SteamFinderController;

$steamFinderController = new SteamFinderController($Db, $General, $Translate, $Modules);

require MODULES . "module_page_steamfinder/forward/requests.php";

$Modules->set_page_title("Steam Finder | {$General->arr_general['short_name']}");
$Modules->set_page_description($Translate->get_translate_module_phrase('module_page_steamfinder', '_descriptionPage'));
