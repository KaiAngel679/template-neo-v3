<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

set_time_limit(4);

!defined('IN_LR') && define('IN_LR', true);
!defined('APP') && define('APP', '../../../../app/');
!defined('STORAGE') && define('STORAGE', '../../../../storage/');
!defined('PAGE') && define('PAGE', APP . 'page/general/');
!defined('PAGE_CUSTOM') && define('PAGE_CUSTOM', APP . 'page/custom/');
!defined('MODULES') && define('MODULES', APP . 'modules/');
!defined('INCLUDES') && define('INCLUDES', APP . 'includes/');
!defined('CACHE') && define('CACHE', STORAGE . 'cache/');
!defined('ASSETS') && define('ASSETS', STORAGE . 'assets/');
!defined('SESSIONS') && define('SESSIONS', CACHE . 'sessions/');
!defined('LOGS') && define('LOGS', CACHE . 'logs/');
!defined('IMG') && define('IMG', CACHE . 'img/');
!defined('ASSETS_CSS') && define('ASSETS_CSS', ASSETS . 'css/');
!defined('ASSETS_JS') && define('ASSETS_JS', ASSETS . 'js/');
!defined('THEMES') && define('THEMES', ASSETS_CSS . 'themes/');
!defined('TEMPLATES') && define('TEMPLATES', APP . 'templates/');
!defined('RANKS_PACK') && define('RANKS_PACK', IMG . 'ranks/');
!defined('MINUTE_IN_SECONDS') && define('MINUTE_IN_SECONDS', 60);
!defined('HOUR_IN_SECONDS') && define('HOUR_IN_SECONDS', 3600);
!defined('DAY_IN_SECONDS') && define('DAY_IN_SECONDS', 86400);
!defined('WEEK_IN_SECONDS') && define('WEEK_IN_SECONDS', 604800);
!defined('MONTH_IN_SECONDS') && define('MONTH_IN_SECONDS', 2592000);
!defined('YEAR_IN_SECONDS') && define('YEAR_IN_SECONDS', 31536000);

session_start();

ob_start();

require '../../../includes/functions.php';
require_once '../../../ext/Db.php';
require_once '../../../ext/Notifications.php';
require_once '../../../ext/Translate.php';
require_once '../../../ext/General.php';
require_once '../../../ext/Modules.php';
require_once '../../../ext/AltoRouter.php';
require_once '../../../ext/Auth.php';
require_once MODULES . 'module_page_request/ext/bb/BBCode.php';
require_once MODULES . 'module_page_request/ext/bb/Parser/Parser.php';
require_once MODULES . 'module_page_request/ext/bb/Parser/HTMLParser.php';
require_once MODULES . 'module_page_request/ext/bb/Parser/BBCodeParser.php';
require_once MODULES . 'module_page_request/ext/Requests.php';

$Translate      = new \app\ext\Translate;
$Db             = new \app\ext\Db();
$Notifications  = new \app\ext\Notifications($Translate, $Db);
$General        = new \app\ext\General($Db);
$Router         = new \app\ext\AltoRouter();
$Modules        = new \app\ext\Modules($General, $Translate, $Notifications, $Router);
$Auth           = new \app\ext\Auth($General, $Db);
$Requests = new \app\modules\module_page_request\ext\Requests($Translate, $Notifications, $General, $Modules, $Db, $Auth);

if (isset($_POST['function'])) {
    switch ($_POST['function']) {
        case 'request':
            echo json_encode($Requests->SendRequest());
        break;
        case 'settings':
            echo json_encode($Requests->Settings());
        break;
        case 'request_add':
            echo json_encode($Requests->createRequest());
        break;
        case 'request_edit':
            echo json_encode($Requests->editRequest());
        break;
        case 'request_del':
            echo json_encode($Requests->deletRequest());
        break;
        case 'status':
            echo json_encode($Requests->status());
        break;
        case 'answer_admin':
            echo json_encode($Requests->answer_admin());
        break;
        case 'answer':
            echo json_encode($Requests->answer());
        break;
        case 'del_list':
            echo json_encode($Requests->DelList());
        break;
        case 'del_answer':
            echo json_encode($Requests->del_answer());
        break;
        case 'question_add':
            echo json_encode($Requests->createQuestion());
        break;
        case 'question_edit':
            echo json_encode($Requests->editQuestion());
        break;
        case 'question_del':
            echo json_encode($Requests->deletQuestion());
        break;
        case 'perm_add':
            echo json_encode($Requests->AddAdmin());
        break;
        case 'perm_edit':
            echo json_encode($Requests->editAdmin());
        break;
        case 'perm_del':
            echo json_encode($Requests->deletAdmin());
        break;
    }
}
