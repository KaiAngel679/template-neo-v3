<?php
define('PLAYERS_ON_PAGE', '10');
$page_max = 0;

$page_num = (int) intval(get_section('num', '1'));
$page_num <= 0 && get_iframe('R2', 'Данная страница не существует') && die();

$page_num_min = ($page_num - 1) * PLAYERS_ON_PAGE;


if (!empty($_POST['case_id'])) {
    $CASES->loadRoulette(strip_tags($_POST['case_id']));
    exit;
} else if (!empty($_POST['liveLoad'])) {
    $CASES->liveLoad();
    exit;
}

if (isset($_SESSION['steamid32'])) {
    $CASES->BCheckPlayer();
    if (!empty($_POST['case_id_open'])) {
        $CASES->openCase(strip_tags($_POST['case_id_open']));
        exit;
    } else if (!empty($_POST['live'])) {
        $CASES->liveUpload(strip_tags($_POST['live']));
        exit;
    } else if (!empty($_POST['sale'])) {
        $CASES->saleSubject(strip_tags($_POST['sale']));
        exit;
    } else if (isset($_POST['up'])) {
        $CASES->upSubject($_POST);
        exit;
    } else if (!empty($_POST['wins'])) {
        $CASES->winSubject(strip_tags($_POST['wins']));
        exit;
    } else if (!empty($_POST['win_up_confirm'])) {
        $_JSON = json_decode(base64_decode(strip_tags($_POST['win_up_confirm'])), true);
        if (!empty($_JSON['w_confirm']) && !empty($_JSON['up'])) {
            $CASES->upSubject($_JSON);
            exit;
        } else exit;
    } else if (!empty($_POST['win_up_server'])) {
        $_JSON = json_decode(base64_decode(strip_tags($_POST['win_up_server'])), true);
        if (!empty($_JSON['sid']) && !empty($_JSON['up'])) {
            $CASES->upSubject($_JSON);
            exit;
        } else exit;
    }
}
if (isset($_SESSION['user_admin'])  && isset($_GET['section'])) {
    switch (strip_tags($_GET['section'])) {
        case 'admin':
            $CasesList = $CASES->getCasesAdmin();
            if ($_POST['button'] == 'add_case_btn') {
                exit(json_encode(['btn' => "CasesAjax('add_case', '', 'case_form')", 'title' => $Translate->get_translate_module_phrase('module_page_open_case', '_AddCase')], true));
            } else if ($_POST['button'] == 'add_case') {
                exit(json_encode($CASES->createCase($_POST), true));
            } else if ($_POST['button'] == 'edit_case_btn') {
                exit(json_encode($CASES->getCaseData($_POST), true));
            } else if ($_POST['button'] == 'edit_case') {
                exit(json_encode($CASES->editCase($_POST), true));
            } else if ($_POST['button'] == 'del_case_btn') {
                exit(json_encode(['btn' => 'CasesAjax("del_case", ' . $_POST['param'] . ')', 'content' => $Translate->get_translate_module_phrase('module_page_open_case', '_delete_a_case')], true));
            } else if ($_POST['button'] == 'del_case') {
                exit(json_encode($CASES->deletCase($_POST), true));
            }if ($_POST['button'] == 'add_cat_btn') {
                exit(json_encode(['btn' => "CasesAjax('add_cat', '', 'cat_form')", 'title' => $Translate->get_translate_module_phrase('module_page_open_case', '_AddCat')], true));
            } else if ($_POST['button'] == 'add_cat') {
                exit(json_encode($CASES->createCategory($_POST), true));
            } else if ($_POST['button'] == 'edit_cat_btn') {
                exit(json_encode($CASES->getCatData($_POST), true));
            } else if ($_POST['button'] == 'edit_cat') {
                exit(json_encode($CASES->editCat($_POST), true));
            } else if ($_POST['button'] == 'del_cat_btn') {
                exit(json_encode(['btn' => 'CasesAjax("del_cat", ' . $_POST['param'] . ')', 'content' => $Translate->get_translate_module_phrase('module_page_open_case', '_delete_a_category')], true));
            } else if ($_POST['button'] == 'del_cat') {
                exit(json_encode($CASES->deletCat($_POST), true));
            } else if ($_POST['button'] == 'clear_list_btn') {
                exit(json_encode(['btn' => 'CasesAjax("clear_list")', 'content' => 'Вы действительно хотите очистить список открытых кейсов?'], true));
            } else if ($_POST['button'] == 'clear_list') {
                exit(json_encode($CASES->ClearList(), true));
            } else if ($_POST['button'] == 'clear_gifts_btn') {
                exit(json_encode(['btn' => 'CasesAjax("clear_gifts")', 'content' => 'Вы действительно хотите очистить забранные/проданные призы?'], true));
            } else if ($_POST['button'] == 'clear_gifts') {
                exit(json_encode($CASES->ClearGifts(), true));
            } else if ($_POST['button'] == 'clear_live_btn') {
                exit(json_encode(['btn' => 'CasesAjax("clear_live")', 'content' => 'Вы действительно хотите очистить лайв ленту?'], true));
            } else if ($_POST['button'] == 'clear_live') {
                exit(json_encode($CASES->ClearLive(), true));
            } else if ($_POST['button'] == 'case_settings') {
                exit(json_encode($CASES->EditSettings($_POST), true));
            }
            break;
        case 'case':
            $subjects = $CASES->getCaseSubjectsAdmin($_GET['id']);
            if ($_POST['button'] == 'add_subject_btn') {
                exit(json_encode(['btn' => "CasesAjax('add_subject', '', 'subject_form')", 'title' => $Translate->get_translate_module_phrase('module_page_open_case', '_AddItem')], true));
            } else if ($_POST['button'] == 'add_subject') {
                exit(json_encode($CASES->createSubject($_POST), true));
            } else if ($_POST['button'] == 'edit_subject_btn') {
                exit(json_encode($CASES->getSubjectInfo($_POST), true));
            } else if ($_POST['button'] == 'edit_subject') {
                exit(json_encode($CASES->editSubject($_POST), true));
            } else if ($_POST['button'] == 'del_subject_btn') {
                exit(json_encode(['btn' => 'CasesAjax("del_subject", $("#modal_delete").attr("delete"))', 'content' => 'Вы действительно хотите удалить предмет?'], true));
            } else if ($_POST['button'] == 'del_subject') {
                exit(json_encode($CASES->deletSubject($_POST), true));
            }
            break;
        case 'cases_list':
            $page_max = ceil($CASES->openCasesListCount() / PLAYERS_ON_PAGE);
            $List = $CASES->openCasesListPagination($page_num_min, PLAYERS_ON_PAGE);
            $page_num > $page_max && get_iframe('R2', 'Данная страница не существует') && die();
            break;
        case 'wins_list':
            $page_max = ceil($CASES->WinsListCount() / PLAYERS_ON_PAGE);
            $Wins = $CASES->WinsListPagination($page_num_min, PLAYERS_ON_PAGE);
            $page_num > $page_max && get_iframe('R2', 'Данная страница не существует') && die();
            break;

        default:
            exit;
            break;
    }
}
