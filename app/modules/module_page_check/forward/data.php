<?php

use app\modules\module_page_check\ext\Check;

$Check = new Check($Db, $General, $Translate, $Modules, $Router, $Notifications);
if (!$Check->IsAdmin()) {
	header('Location: ' . $General->arr_general['site']);
	exit;
};
$Router->map('GET|POST', 'check/[:page]/', 'main');

$Map = $Router->match();
$page = $Map['params']['page'] ?? 'main';

if (!in_array($page, ['main', 'settings'], true)) {
	header('Location: ' . $General->arr_general['site'] . 'check');
	exit;
}

$servers = $Check->getServers();
$reasons = $Check->getReason();


if ($Check->IsAdmin()) {
	if($_POST['getCheckList']){
		exit(json_encode($Check->Render($_POST['page'], $_POST['server'] ?? 1, $_POST['verdict'] ?? 'all', $_POST['search'] ?? '')));
	}
	if (isset($_SESSION['user_admin'])) {
		if (isset($_POST['reason']) && !empty($_POST['reason'])) {
			exit(json_encode($Check->saveReason($_POST['reason'])));
		} elseif (isset($_POST['admin'])) {
			exit(json_encode($Check->AddAccess($_POST['admin'])));
		} elseif (isset($_POST['accessdel']) && isset($_POST['id_del'])) {
			exit(json_encode($Check->DelAccess($_POST['id_del'])));
		} elseif (isset($_POST['changeSettings'])) {
			exit(json_encode($Check->saveSettings($_POST['name'])));
		}
	}
}

$Modules->set_page_title( $Translate->get_translate_module_phrase('module_page_check', '_checkTitle') . ' | ' . $General->arr_general['short_name']);