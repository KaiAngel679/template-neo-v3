<?php
!defined('IN_LR') && define('IN_LR', true);
!defined('APP') && define('APP', '../../../../app/');
!defined('STORAGE') && define('STORAGE', '../../../../storage/');
!defined('ASSETS') && define('ASSETS', STORAGE . 'assets/');
!defined('ASSETS_JS') && define('ASSETS_JS', ASSETS . 'js/');
!defined('CACHE') && define('CACHE', STORAGE . 'cache/');
!defined('SESSIONS') && define('SESSIONS', CACHE . 'sessions/');
!defined('MODULES') && define('MODULES', APP . 'modules/');
!defined('MODULESCACHE') && define('MODULESCACHE', STORAGE . 'modules_cache/');
!defined('IMG') && define('IMG', CACHE . 'img/');
!defined('RANKS_PACK') && define('RANKS_PACK', IMG . 'ranks/');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

require '../../../includes/functions.php';
require_once '../../../ext/Db.php';
require_once '../../../ext/General.php';
require_once '../../../ext/Translate.php';
require_once __DIR__ . '/../ext/GeoLookup.php';
require_once __DIR__ . '/../ext/ServerInfoManager.php';

use app\modules\module_block_main_servers\ext\GeoLookup;
use app\modules\module_block_main_servers\ext\ServerInfoManager;

$Db = new \app\ext\Db();
$Translate = new \app\ext\Translate();
$General = new \app\ext\General($Db);

$return = [];
$servers = array_filter($General->server_list, function ($server) {
    return $server['server_status'] != 0;
});
$settings = getSettings();
$password = $settings['password'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'servers') {
    $server_cache = get_server_cache();
    $now = time();
    $cacheExpired = !empty($server_cache['time']) && $now > (int) $server_cache['time'];
    $needRebuild = empty($server_cache) || empty($server_cache['servers']) || $cacheExpired;
    if ($needRebuild) {
        if (empty($server_cache['servers'])) {
            $fresh = rebuild_servers_cache($General, $Translate, $Db, $servers, 60);
            if ($fresh !== null) {
                $server_cache = $fresh;
                $cacheExpired = !empty($server_cache['time']) && $now > (int) $server_cache['time'];
            }
        } else {
            $fresh = rebuild_servers_cache($General, $Translate, $Db, $servers, 3);
            if ($fresh !== null) {
                $server_cache = $fresh;
                $cacheExpired = !empty($server_cache['time']) && $now > (int) $server_cache['time'];
            }
        }
    }
    $return = [
        'servers' => $server_cache['servers'] ?? [],
        'mods' => $server_cache['mods'] ?? [],
        'time' => $server_cache['time'] ?? 0,
    ];
    $json = json_encode($return, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        http_response_code(500);
        exit;
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('CDN-Cache-Control: no-store');
    header('Surrogate-Control: no-store');
    header('Pragma: no-cache');
    if (!empty($server_cache['time'])) {
        header('X-Mon-Cache-Until: ' . (int) $server_cache['time']);
    }
    if ($cacheExpired) {
        header('X-Mon-Cache-Stale: 1');
    }

    echo $json;
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'modal' && isset($_GET['server'])) {
    $serverIdx = (int) $_GET['server'];
    if (!isset($servers[$serverIdx])) {
        http_response_code(404);
        exit;
    }
    $manager = new ServerInfoManager($General, $Translate, $Db, getCacheTime());
    $return = $manager->getServerData($servers[$serverIdx]);
} elseif (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === 0 && isset($_SERVER['HTTP_X_SERVER'])) {
    $serverIp = $_SERVER['HTTP_X_SERVER'];
    $authBearer = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    $password = $settings['password'] ?? null;
    if (empty($password)) {
        http_response_code(500);
        exit;
    }
    if (!$serverIp || !$authBearer) {
        http_response_code(401);
        exit;
    }
    if (!hash_equals('Bearer ' . $password, $authBearer)) {
        http_response_code(403);
        exit;
    }
    if (!in_array($serverIp, array_column($servers, 'ip'))) {
        http_response_code(404);
        exit;
    }
    $rawBody = file_get_contents('php://input');
    if (strlen($rawBody) > 5 * 1024 * 1024) {
        http_response_code(413);
        exit;
    }
    $rawData = json_decode($rawBody, true);
    if ($rawData === null) {
        http_response_code(400);
        exit;
    }
    $key = str_replace('.', '_', $serverIp);
    $tmpPath = '../servers/' . $key . '.tmp';
    !file_exists('../servers') && mkdir('../servers', 0777, true);
    file_put_contents($tmpPath, json_encode($rawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    echo json_encode(['success' => true]);
    exit;
}

function get_server_cache()
{
    $cache_file = '../temp/cache.json';
    if (file_exists($cache_file)) {
        $json_data = file_get_contents($cache_file);
        $data = json_decode($json_data, true);
        return is_array($data) ? $data : [];
    } else {
        !file_exists('../temp') && mkdir('../temp', 0777, true);
        file_put_contents($cache_file, json_encode([]), LOCK_EX);
        return [];
    }
}

function set_server_cache($data): void
{
    $dir = '../temp';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $cache_file = $dir . '/cache.json';
    $tmp_file = $cache_file . '.tmp';

    $json = json_encode($data, JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        return;
    }

    file_put_contents($tmp_file, $json, LOCK_EX);
    rename($tmp_file, $cache_file);
}

function getCacheTime(): int
{
    $settings = getSettings();
    return isset($settings['time']) ? (int) $settings['time'] : 30;
}

function getSettings(): array
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }
    if (file_exists(MODULES . 'module_page_mon_settings/settings.php')) {
        $settings = require MODULES . 'module_page_mon_settings/settings.php';
        return is_array($settings) ? $settings : [];
    }
    $settings = [];
    return $settings;
}

function rebuild_servers_cache(object $General, object $Translate, object $Db, array $servers, int $maxWaitSec = 0): ?array
{
    $dir = __DIR__ . '/../temp';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $lockFile = $dir . '/cache.rebuild.lock';
    $fp = @fopen($lockFile, 'c+');
    if ($fp === false) {
        return null;
    }

    $deadline = $maxWaitSec > 0 ? time() + $maxWaitSec : 0;
    while (!flock($fp, LOCK_EX | LOCK_NB)) {
        if ($deadline === 0 || time() >= $deadline) {
            fclose($fp);
            return read_fresh_server_cache(time());
        }
        usleep(100000);
    }

    try {
        $manager = new ServerInfoManager($General, $Translate, $Db, getCacheTime());
        $data = $manager->getServersData($servers, false);
        set_server_cache($data);
        return $data;
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
        @unlink($lockFile);
    }
}

function read_fresh_server_cache(int $now): ?array
{
    $cache = get_server_cache();
    if (empty($cache['servers']) || empty($cache['time']) || $now > (int) $cache['time']) {
        return null;
    }
    return $cache;
}

function getMods(): array
{
    static $mods = null;
    if ($mods !== null) {
        return $mods;
    }
    $jsonPath = MODULES . 'module_page_mon_settings/mods.json';
    if (file_exists($jsonPath)) {
        $data = json_decode(file_get_contents($jsonPath), true);
        if (is_array($data)) {
            $mods = $data;
            return $mods;
        }
    }
    if (file_exists(MODULES . 'module_page_mon_settings/mods.php')) {
        $mods = require MODULES . 'module_page_mon_settings/mods.php';
        return is_array($mods) ? $mods : [];
    }
    $mods = [];
    return $mods;
}

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($return, JSON_UNESCAPED_UNICODE);
}
exit;

function getUserGeo(): ?array
{
    $ip = '';
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }

    if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
        return null;
    }

    $cacheDir = __DIR__ . '/../temp/geo/';
    !is_dir($cacheDir) && mkdir($cacheDir, 0777, true);
    $cacheFile = $cacheDir . 'geo_user_' . md5($ip) . '.json';

    if (file_exists($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached) && isset($cached['expire']) && $cached['expire'] > time()) {
            return ['lat' => $cached['lat'], 'lon' => $cached['lon']];
        }
    }

    $coords = GeoLookup::fetchLatLon($ip);
    if ($coords === null) {
        return null;
    }

    $result = ['lat' => $coords['lat'], 'lon' => $coords['lon'], 'expire' => time() + 3600];
    file_put_contents($cacheFile, json_encode($result), LOCK_EX);
    return ['lat' => $result['lat'], 'lon' => $result['lon']];
}
