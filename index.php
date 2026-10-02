<?php
header('Content-Type: text/html; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('opcache.revalidate_freq', 0);

set_time_limit(4);

define('IN_LR', true);

define('VERSION', '3.8.1');

define('APP', 'app/');

define('RESOURCES', 'resources/');

define('STORAGE', 'storage/');

define('PAGE', APP . 'page/general/');

define('PAGE_CUSTOM', APP . 'page/custom/');

define('MODULES', APP . 'modules/');

define('TEMPLATES', APP . 'templates/');

define('INCLUDES', APP . 'includes/');

define('CACHE', STORAGE . 'cache/');

define('MODULESCACHE', STORAGE . 'modules_cache/');

define('ASSETS', STORAGE . 'assets/');

define('SESSIONS', CACHE . 'sessions/');

define('LOGS', CACHE . 'logs/');

define('IMG', CACHE . 'img/');

define('ASSETS_CSS', ASSETS . 'css/');

define('ASSETS_JS', ASSETS . 'js/');

define('RANKS_PACK', IMG . 'ranks/');

define('MINUTE_IN_SECONDS', 60);

define('HOUR_IN_SECONDS', 3600);

define('DAY_IN_SECONDS', 86400);

define('WEEK_IN_SECONDS', 604800);

define('MONTH_IN_SECONDS', 2592000);

define('YEAR_IN_SECONDS', 31536000);

require INCLUDES . 'functions.php';

session_set_cookie_params([
    'lifetime' => MONTH_IN_SECONDS,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax'
]);
ini_set('session.gc_maxlifetime', MONTH_IN_SECONDS);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

session_start();

unset($_SESSION['iframe']);

unset($_SESSION['metatag_index']);

ob_start();

use app\ext\Translate;

use app\ext\Db;

use app\ext\General;

use app\ext\Modules;

use app\ext\Notifications;

use app\ext\Auth;

use app\ext\Graphics;

use app\ext\AltoRouter;

use app\ext\ErrorsHandler;

use app\ext\RateLimiter;

spl_autoload_register(function ($class) {
    $path = str_replace('\\', '/', $class . '.php');
    file_exists($path) && require $path;
});

$ErrorsHandler = new ErrorsHandler;

$ErrorsHandler->setErrors();

$RateLimiter = new RateLimiter(100, 10); 

$Translate = new Translate;

$Db = new Db;

$Notifications = new Notifications($Translate, $Db);

$General = new General($Db);

if (!$RateLimiter->isAllowed()) {
    get_iframe(429, $Translate->get_translate_phrase('_suspectActivity')) && die();
}

$blocksFile = SESSIONS . '/blockedusers.json';
if (file_exists($blocksFile)) {
    $json_blocks = file_get_contents($blocksFile);
    $blocked_users = json_decode($json_blocks, true);
} else {
    $blocked_users = [];
}

$steamid = isset($_SESSION['steamid64']) ? $_SESSION['steamid64'] : null;
foreach ($blocked_users as $blocked_user) {
    if (($blocked_user['ip'] == $General->get_client_ip_cdn()) || ($steamid && $blocked_user['steam'] == $steamid)) {
        get_iframe(403, $Translate->get_translate_phrase('_youHaveBlocked')) && die();
    }
}
if (!isset($_SESSION['user_admin']) && !empty($General->arr_general['antivpn'])) {
    if ($General->check_vpn($General->get_client_ip_cdn())) {
        get_iframe(403, $Translate->get_translate_phrase('_detectedVpn')) && die();
    }
}

if (!isset($_SESSION['user_admin']) && !empty($General->arr_general['thoseworks'])) {
    get_iframe(503, $Translate->get_translate_phrase('_techWork')) && die();
}

if (version_compare(phpversion(), '7.4.0', '<') || version_compare(phpversion(), '8.0.0', '>=')) {
    get_iframe(500, $Translate->get_translate_phrase('_phpVersionNotSupport')) && die();
}

$Router = new AltoRouter;

empty($General->arr_general['site']) && $General->arr_general['site'] = '//' . preg_replace('/^(https?:)?(\/\/)?(www\.)?/', '', $_SERVER['HTTP_REFERER']);

$Router->setBasePath(parse_url($General->arr_general['site'], PHP_URL_PATH));

$Modules = new Modules($General, $Translate, $Notifications, $Router);

$Auth = new Auth($General, $Db);

$Graphics = new Graphics($Translate, $General, $Modules, $Db, $Auth, $Notifications, $Router);

$General->online_stats();