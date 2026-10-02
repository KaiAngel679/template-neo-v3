<?php


use app\modules\module_page_punishment\ext\Punishment;

$Router->map('GET|POST', 'punishment/[:game]/', 'punishment');
$Router->map('GET|POST', 'punishment/[:game]/[i:sid]/', 'punishment');
$Map = $Router->match();
if (!empty($Db->db_data['IksAdmin']) && !empty($Db->db_data['AdminSystem']) && !empty($Db->db_data['IksAdminNew'])) {
    $id = 'cs2';
} elseif (!empty($Db->db_data['SourceBans'])) {
    $id = 'csgo';
} else {
    $id = 'cs2';
}
$game = $Map['params']['game'] ?? $id;
$server_id = $Map['params']['sid'] ?? 'all';

if (empty($Db->db_data['IksAdmin']) && empty($Db->db_data['AdminSystem']) && empty($Db->db_data['IksAdminNew']) && empty($Db->db_data['SourceBans'])) {
    get_iframe(503, $Translate->get_translate_module_phrase('module_page_punishment', '_connectMod')) && die();
}

$Punishment = new Punishment($Db, $General, $Translate, $Modules, $Notifications, $server_id, $game);

if (isset($_POST['list'])) {
    exit(json_encode($Punishment->RenderingPageList($_POST['type'], $_POST['page']), true));
} elseif (isset($_POST['modal'])) {
    exit(json_encode($Punishment->RenderingModalWindow($_POST['type'], $_POST['id']), true));
} elseif (isset($_POST['btn_unban'])) {
    exit(json_encode($Punishment->PunishmentUnban($_POST['idpunish'], $_POST['page'], $_POST['type'], $_POST['sid']), true));
} elseif (isset($_POST['search_ban']) || isset($_POST['search_mute']) || isset($_POST['search_admin'])) {
    exit(json_encode($Punishment->Search()->SearchPost($_POST['search_ban'], $_POST['search_mute'], $_POST['search_admin'])));
} elseif (isset($_POST['admin_vote'])) {
    exit(json_encode($Punishment->VoteAdmin($_POST)));
}

if (isset($_SESSION['user_admin'])) {
    if (isset($_POST['installTable'])) {
        exit(json_encode($Punishment->installTables()));
    }
}

$Modules->set_page_title("{$Translate->get_translate_module_phrase('module_page_punishment', '_punishment')} | {$General->arr_general['short_name']}");
$Modules->set_page_description("{$Translate->get_translate_module_phrase('module_page_punishment', '_punishment')} | {$General->arr_general['short_name']}");
