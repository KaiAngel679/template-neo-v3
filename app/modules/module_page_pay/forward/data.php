<?php

use app\modules\module_page_pay\ext\Lk_module;

if (IN_LR != true) {
    header('Location: ' . $General->arr_general['site']);
    exit;
}

$LK = new Lk_module($Translate, $Notifications, $General, $Modules, $Db);

define('PLAYERS_ON_PAGE', '10');
define('PAYS_COUNT', '20');
$page_num = get_section('num', '1');
$page_max = $LK->UsersPageMax(PLAYERS_ON_PAGE);
$page_max_pays = $LK->PaysPageMax(PAYS_COUNT);
$page_num_min = ($page_num - 1) * PLAYERS_ON_PAGE;
$page_num_min_pays = ($page_num - 1) * PAYS_COUNT;
$playersAll = $LK->LkGetAllPlayers($page_num_min, PLAYERS_ON_PAGE);

if (isset($_SESSION['user_admin'])) {
    if (!empty($_POST['del_lk_user'])) {
        $LK->DelLKUser($_POST);
        exit;
    }
    if (isset($_POST['user'])) {
        $LK->LkUpdateBalance($_POST);
        exit;
    } else if (isset($_POST['users_clean'])) {
        $LK->LkDelUsers();
        exit;
    } else if (isset($_POST['search_users'])) {
        $LK->SearchUser($_POST['search_users']);
    }
}
if (isset($_SESSION['user_admin'])  && isset($_GET['section'])) {
    switch ($_GET['section']) {
        case 'gateways':
            if (!empty($_POST['gateway'])) {
                $LK->LkAddGateway($_POST);
                exit;
            } else if (isset($_POST['gateway_edit'])) {
                $LK->LkEditGateway($_POST);
                exit;
            } else if (isset($_POST['gateway_delete'])) {
                $LK->LkDeleteGateway($_POST);
                exit;
            } else if (isset($_POST['webhoock_url'])) {
                $LK->LkAddDiscord($_POST);
                exit;
            }
            break;
        case 'promocodes':
            if (isset($_POST['addpromo'])) {
                $LK->LkAddPromocode($_POST);
                exit;
            } else if (isset($_POST['editid'])) {
                $LK->LkEditPromocode($_POST);
                exit;
            } else if (isset($_POST['promocode_delete'])) {
                $LK->LkDeletePromocode($_POST);
                exit;
            }
            break;
        case 'search':
            if (isset($_POST['search_users'])) {
                $LK->SearchUser($_POST['search_users']);
            } else  if (isset($_POST['user'])) {
                $LK->LkUpdateBalance($_POST);
                exit;
            }
            break;
        case 'payments':
            if (isset($_POST['del_check'])) {
                exit(json_encode($LK->delGateWayError(), true));
            }
            break;
    }
}

if (!empty($_GET['gateway'])) {
    require MODULES . 'module_page_pay/includes/result.php';
    exit;
}

$Modules->set_page_title($Translate->get_translate_module_phrase('module_page_pay', '_LK') . ' | ' . $General->arr_general['short_name']);