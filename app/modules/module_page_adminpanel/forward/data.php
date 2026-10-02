<?php
(! isset($_SESSION['user_admin'])) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die();

use app\modules\module_page_adminpanel\ext\Admin;

$Admin = new Admin($General, $Modules, $Db, $Translate);

if (isset($_SESSION['user_admin'])) {
    if (function_exists("opcache_reset"))
        isset($_POST) && opcache_reset();

    isset($_POST['add_mods']) && $Admin->action_db_add_mods();

    isset($_POST['save_server']) && $Admin->action_add_server();

    isset($_POST['save_server_edit']) && $Admin->action_edit_server();

    isset($_POST['del_server']) && $Admin->action_del_server();

    isset($_POST['function']) && $Admin->action_db_add_connection();

    isset($_POST['data']) && $Admin->edit_modules_initialization();

    if (isset($_POST['changeSortNav'])) {
        $rootContainer = $_POST['rootContainer'] ?? 'nested-nav';
        exit(json_encode($Admin->sortMenu($_POST['data_sort'], $rootContainer), true));
    } elseif (isset($_POST['changeSortServer'])) {
        exit(json_encode($Admin->sortServers($_POST['order']), true));
    } elseif (isset($_POST['add_point'])) {
        exit(json_encode($Admin->AddMenuPoint($_POST), true));
    } elseif (isset($_POST['add_category'])) {
        exit(json_encode($Admin->AddMenuCategory($_POST), true));
    } elseif (isset($_POST['add_userbar'])) {
        exit(json_encode($Admin->AddUserBar($_POST), true));
    } elseif (isset($_POST['add_footer'])) {
        exit(json_encode($Admin->AddFooter($_POST), true));
    } elseif (isset($_POST['loadMenuData'])) {
        $id = $_POST['id'] ?? null;
        $type = $_POST['type'] ?? null;
        $point = $_POST['point'] ?? null;
        exit(json_encode($Admin->loadMenuData($id, $type, $point), true));
    } elseif (isset($_POST['menu_del'])) {
        $idPoint = $_POST['id_point'] ?? '';
        exit(json_encode($Admin->DeleteMenu($_POST['id_del'], $_POST['type'], $idPoint), true));
    } elseif (isset($_POST['form-edit-conection'])) {
        exit(json_encode($Admin->EditServer($_POST), true));
    } elseif (isset($_POST['updateMenuItem'])) {
        exit(json_encode($Admin->updateMenuItem($_POST), true));
    } elseif (isset($_POST['create_table'])) {
        exit(json_encode($Admin->CreateTable(), true));
    } elseif (isset($_POST['create_table_neo3_7'])) {
        exit(json_encode($Admin->CreateTableNeo3_7(), true));
    } elseif (isset($_POST['hide_filter_form'])) {
        exit(json_encode($Admin->SettingsSaveHide($_POST), true));
    } elseif (isset($_POST['stretch_filter_form'])) {
        exit(json_encode($Admin->SettingsSaveStretch($_POST), true));
    } elseif (isset($_POST['hide_city_form'])) {
        exit(json_encode($Admin->SettingsSaveHideCity($_POST), true));
    } elseif (isset($_POST['hide_country_form'])) {
        exit(json_encode($Admin->SettingsSaveHideCountry($_POST), true));
    } elseif (isset($_POST['all_del_logs'])) {
        exit(json_encode($Admin->DelAllLogsWeb(), true));
    } elseif (isset($_POST['log_del'])) {
        exit(json_encode($Admin->DelLogWeb($_POST), true));
    } elseif (isset($_POST['all_del_logs_lk'])) {
        exit(json_encode($Admin->LkCleanLogs(), true));
    } elseif (isset($_POST['log_del_lk'])) {
        exit(json_encode($Admin->LkLogdelete($_POST), true));
    } elseif (isset($_POST['settings_modules'])) {
        exit(json_encode($Admin->edit_module($_POST, $_GET, $_FILES), true));
    } elseif (isset($_POST['baner_del'])) {
        exit(json_encode($Admin->DelSettingsBaner($_POST), true));
    } elseif (isset($_POST['settings_modules_core'])) {
        exit(json_encode($Admin->edit_module_core($_POST, $_GET), true));
    } elseif (isset($_POST['clear_modules_initialization'])) {
        exit(json_encode($Admin->action_clear_modules_initialization(), true));
    } elseif (isset($_POST['options_one'])) {
        exit(json_encode($Admin->edit_options(), true));
    } elseif (isset($_POST['options_two'])) {
        exit(json_encode($Admin->edit_social(), true));
    } elseif (isset($_POST['addRoleForm'])) {
        exit(json_encode($Admin->addRole($_POST), true));
    } elseif (isset($_POST['del_role'])) {
        exit(json_encode($Admin->delRole($_POST['id']), true));
    } elseif (isset($_POST['addAdminForm'])) {
        exit(json_encode($Admin->addAdmin($_POST), true));
    } elseif (isset($_POST['del_admin'])) {
        exit(json_encode($Admin->delAdmin($_POST['id']), true));
    } elseif (isset($_POST['addUserRoleForm'])) {
        exit(json_encode($Admin->addUserRole($_POST), true));
    } elseif (isset($_POST['del_user_role'])) {
        exit(json_encode($Admin->delUserRole($_POST['user_steam'], $_POST['role_id']), true));
    } elseif (isset($_POST['addBlockForm'])) {
        exit(json_encode($Admin->addBlock($_POST), true));
    } elseif (isset($_POST['del_block'])) {
        exit(json_encode($Admin->delBlock($_POST['block_id']), true));
    } elseif (isset($_POST['admin_clear_empty_players'])) {
        exit(json_encode($Admin->clearEmptyPlayer(), true));
    } elseif (isset($_POST['admin_clear_stats'])) {
        exit(json_encode($Admin->clearStats(), true));
    } elseif (isset($_POST['admin_clear_unactive_players'])) {
        exit(json_encode($Admin->clearUnactivePlayer(), true));
    } elseif (isset($_POST['admin_update_online'])) {
        exit(json_encode($Admin->updateOnline(), true));
    } elseif (isset($_POST['edit_socials'])) {
        exit(json_encode($Admin->editTemplateInfo($_POST, 'social'), true));
    } elseif (isset($_POST['edit_info'])) {
        exit(json_encode($Admin->editTemplateInfo($_POST, 'info'), true));
    } elseif (isset($_POST['edit_other'])) {
        exit(json_encode($Admin->editTemplateInfo($_POST, 'other'), true));
    } elseif (isset($_POST['remove_logo'])) {
        exit(json_encode($Admin->editTemplateInfo($_POST, 'remove_logo'), true));
    } elseif (isset($_POST['send_filepond_file'])) {
        exit(json_encode($Admin->editTemplateInfo($_FILES['filepond'], 'load_logo'), true));
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = file_get_contents('php://input');
        exit(json_encode($Admin->editTemplateInfo($data, 'del_logo'), true));
    }
}

$roles = $Admin->getRoles();
$blocks = $Admin->getBlocks();
$admins = $Admin->getAdmins();

$Modules->set_page_title($Translate->get_translate_phrase('_Admin_panel') . ' | ' .  $General->arr_general['short_name']);
