<?php

use app\modules\module_page_results\ext\Results;

$Results = new Results($Db, $General, $Translate, $Modules);
$Router->map('GET|POST', 'results/[results|admintime|adminsinfo|warns|settings:page]/', 'results');
$Map = $Router->match();
$page = $Map['params']['page'] ?? 'results';

$groups = $Results->getGroups();
$servers = $Results->getServers();
$access = $Results->checkAccess();
$settings = $Results->getSettings();

switch ($page) {
  case 'admintime':
    (empty($access['results']) && empty($access['theirs']) && empty($access['full'])) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die();
    break;
  case 'adminsinfo':
    (empty($access['admins']) && empty($access['full'])) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die();
    break;
  case 'warns':
    (empty($access['warns']) && empty($access['full'])) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die();
    break;
  case 'settings':
    empty($access['full']) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die();
    break;
  default:
    (empty($access['results']) && empty($access['theirs']) && empty($access['full'])) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die();
}

if (isset($_POST['action'])) {
  $action = $_POST['action'];
  $denied = ['error' => $Translate->get_translate_phrase('_accessDenied')];
  switch ($action) {
    case 'getAdminsTime':
      exit(json_encode($Results->renderAdminTime($_POST)));
    case 'getSessions':
      exit(json_encode($Results->renderSessions($_POST)));
    case 'getCharts':
      exit(json_encode($Results->renderCharts($_POST)));
    case 'getResults':
      exit(json_encode($Results->renderResults($_POST)));
    case 'getResultDetails':
      exit(json_encode($Results->renderResultDetails((int)($_POST['id']), (int)($_POST['server']))));
    case 'getAdmin':
    case 'getAdmins':
    case 'addAdmin':
    case 'editAdmin':
    case 'deleteAdmin':
    case 'importAdmins':
      (empty($access['admins']) && empty($access['full'])) && exit(json_encode($denied));
      switch ($action) {
        case 'getAdmin':
          exit(json_encode($Results->renderAdmin((int)($_POST['id']))));
        case 'getAdmins':
          exit(json_encode($Results->renderAdmins((int)($_POST['page'] ?? 1), (int)($_POST['server'] ?? 0), (int)($_POST['group'] ?? 0))));
        case 'addAdmin':
          exit(json_encode($Results->addAdmin($_POST)));
        case 'editAdmin':
          exit(json_encode($Results->editAdmin($_POST)));
        case 'deleteAdmin':
          exit(json_encode($Results->deleteAdmin((int)($_POST['id']))));
        case 'importAdmins':
          exit(json_encode($Results->importAdmins($_POST)));
      }
    case 'getWarns':
      (empty($access['warns']) && empty($access['full'])) && exit(json_encode($denied));
      switch ($action) {
        case 'getWarns':
          exit(json_encode($Results->renderWarns((int)($_POST['page'] ?? 1))));
      }
    case 'grantAwards':
    case 'giveAward':
    case 'giveWarn':
      (empty($access['awardwarns']) && empty($access['full'])) && exit(json_encode($denied));
      switch ($action) {
        case 'grantAwards':
          exit(json_encode($Results->grantAwards((int)($_POST['id']), (int)($_POST['server']))));
        case 'giveAward':
          exit(json_encode($Results->giveAwardManual($_POST)));
        case 'giveWarn':
          exit(json_encode($Results->giveWarnManual($_POST)));
      }
    case 'saveSettings':
    case 'saveDiscord':
    case 'manualGenerate':
    case 'getAwards':
    case 'getAward':
    case 'addAward':
    case 'editAward':
    case 'deleteAward':
    case 'addAccess':
    case 'deleteAccess':
    case 'addServer':
    case 'deleteServer':
    case 'getLogs':
      empty($access['full']) && exit(json_encode($denied));
      switch ($action) {
        case 'saveSettings':
          exit(json_encode($Results->saveSettings($_POST)));
        case 'saveDiscord':
          exit(json_encode($Results->saveDiscord($_POST)));
        case 'getAwards':
          exit(json_encode($Results->getAwards()));
        case 'getAward':
          exit(json_encode($Results->getAward((int)($_POST['id']))));
        case 'addAward':
          exit(json_encode($Results->addAward($_POST)));
        case 'editAward':
          exit(json_encode($Results->editAward($_POST)));
        case 'deleteAward':
          exit(json_encode($Results->deleteAward((int)($_POST['id']))));
        case 'addAccess':
          exit(json_encode($Results->addAccess($_POST)));
        case 'deleteAccess':
          exit(json_encode($Results->deleteAccess((int)($_POST['id']))));
        case 'addServer':
          exit(json_encode($Results->addServer($_POST)));
        case 'deleteServer':
          exit(json_encode($Results->deleteServer((int)($_POST['id']))));
        case 'getLogs':
          exit(json_encode($Results->renderLogs((int)($_POST['page'] ?? 1), $_POST['date'] ?? '')));
        case 'manualGenerate':
          exit(json_encode($Results->manualGenerate((int)($_POST['count_weeks'] ?? 1))));
      }
  }
}
