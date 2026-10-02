<?php
if ($_POST['online_stats']) {
    !defined('IN_LR') && define('IN_LR', true);
    !defined('APP') && define('APP', '../../../../app/');
    !defined('STORAGE') && define('STORAGE', '../../../../storage/');
    !defined('CACHE') && define('CACHE', STORAGE . 'cache/');
    !defined('MODULES') && define('MODULES', APP . 'modules/');
    !defined('SESSIONS') && define('SESSIONS', CACHE . 'sessions/');

    session_start();

    require '../../../ext/Db.php';
    $Db = new app\ext\Db;


    $mods = file_exists(SESSIONS . 'mods.php') ? require SESSIONS . 'mods.php' : [];
    $cache_path = MODULES . 'module_block_main_servers/temp/cache.json';
    $server_cache = file_exists($cache_path) ? json_decode(file_get_contents($cache_path, true), true) : ['servers' => []];
    $data = [];
    $data['site'] = $Db->query('Core', 0, 0, "SELECT COUNT(*) as `count` FROM `lr_web_online` LIMIT 1")['count'] ?? 0;
    $data['servers'] = 0;
    $data['server_mods'] = [];

    if (isset($server_cache['servers'])) {
        foreach ($server_cache['servers'] as $server) {
            $players = (int)($server['Players'] ?? 0);
            $data['servers'] += $players;

            $gm = $server['GameMode'] ?? 'PUBLIC';
            if (!isset($mods[$gm])) {
                $gm = 'PUBLIC';
            }

            if (!isset($data['server_mods'][$gm])) {
                $data['server_mods'][$gm] = 0;
            }
            $data['server_mods'][$gm] += $players;
        }
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
} else {
    exit();
}
