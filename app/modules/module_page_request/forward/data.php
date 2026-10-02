<?php

use app\modules\module_page_request\ext\Requests;

$RQ = new Requests($Translate, $Notifications, $General, $Modules, $Db, $Auth);

$request_id = (int) intval(get_section('id', '0'));

define('PLAYERS_ON_PAGE', '15');
$page_max = 0;

$page_num = (int) intval(get_section('num', '1'));
$page_num <= 0 && get_iframe(404, $Translate->get_translate_phrase('_pageNotFound')) && die();

$page_num_min = ($page_num - 1) * PLAYERS_ON_PAGE;

if (get_url(2) != $General->arr_general['site'] . 'request/') {
    header('Location: ' . $General->arr_general['site'] . 'request/');
};

if (isset($_POST) && !empty($_FILES["img"])) {
    exit(json_encode($RQ->UploadMSGPhoto($_FILES['img']), true));
}

if (!empty($Db->db_data['request'])) {
    $data = $RQ->RequestsSettings();
    $requests = $RQ->getRequests();
    if (!empty($requests)) {
        if (!in_array($request_id, array_keys($requests))) {
            get_iframe(404, $Translate->get_translate_phrase('_pageNotFound')) && die();
        }
        $Question = $RQ->getQuestions($requests[$request_id]['id']);
        $ignore_servers = explode(';', $requests[$request_id]['ignore_servers']);
    }
    if (isset($_SESSION['steamid'])):
        $myList = $RQ->getMyList();
    endif;
}

if (isset($_SESSION['steamid32']) && isset($_GET['page'])) {
    switch (strip_tags($_GET['page'])) {
        case 'my':
            if (!empty($myList)) {
                if (isset($_GET['rid'])) {
                    $List = $RQ->getMyListId($_GET['rid']);
                    if (empty($List)) {
                        header('Location: ' . $General->arr_general['site'] . 'request/?page=my');
                        exit;
                    }
                    $Request = $RQ->getRequest($List['rid']);
                    $Review = $RQ->getMyReview($_GET['rid']);
                }
            }
            break;
        case 'admin':
            if ($RQ->access >= 5) {
                $List = $RQ->getListRequests();
                if (isset($_POST['changeSort'])) {
                    exit(json_encode($RQ->changeSort($_POST['order'], $_POST['type']), true));
                }
            }
            break;
        case 'list':
            if ($RQ->access >= 3) {
                $page_max = ceil(count($RQ->getAllList(strip_tags($_GET['type']), strip_tags($_GET['status']))) / PLAYERS_ON_PAGE);
                $List = $RQ->getAllListPagination($page_num_min, PLAYERS_ON_PAGE, strip_tags($_GET['type']), strip_tags($_GET['status']));
                break;
            }
        case 'question':
            if ($RQ->access >= 5) {
                if (isset($_POST['changeSort'])) {
                    exit(json_encode($RQ->changeSort($_POST['order'], $_POST['type'], $_POST['request']), true));
                }
            }
            break;
        case 'review':
            if ($RQ->access >= 3) {
                $List = $RQ->getList($_GET['rid']);
                $Request = $RQ->getRequest($List['rid']);
                $Review = $RQ->getReview($_GET['rid']);
                if (isset($_POST['changeCategory'])) {
                    exit(json_encode($RQ->changeCategory($_POST['category_id'], $_GET['rid']), true));
                }
            }
            break;
        case 'perm':
            if ($RQ->access >= 8) {
                $Admins = $RQ->getAdmins();
            }
            break;
        default:
            exit;
    }
}

$Modules->set_page_title($Translate->get_translate_module_phrase('module_page_request', '_PlayersRequests'));

$Modules->set_page_description($Translate->get_translate_module_phrase('module_page_request', '_acceptingRequests'));