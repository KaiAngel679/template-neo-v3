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
!defined('TIME') && define('TIME', 15); 

require '../../../includes/functions.php';
require_once '../../../ext/Db.php';
require_once '../../../ext/General.php';
require_once '../../../ext/Translate.php';
require __DIR__ . '../../../../ext/SourceQuery/bootstrap.php';

use xPaw\SourceQuery\SourceQuery;

$Query = new SourceQuery();
$Db    = new \app\ext\Db();
$Translate    = new \app\ext\Translate();
$General = new \app\ext\General($Db);

$return = [];

$servers = $General->server_list;

$cache = get_module_cache();
if (empty($cache) || empty($cache['servers']) ||  (!empty($cache['time']) && time() > $cache['time'])) {
    foreach ($servers as $i => $server) {
        $server_stats = explode(";", $server['server_stats']);
        $server_info = explode(":", $server['ip']);
        $server_ip = $server_info[0];
        $server_port = $server_info[1];
        $fake_ip = $server['fakeip'];
        $server_name = $server['name_custom'];
        $server_mod = $server['server_mod'];
        $server_country = mb_strtolower($server['server_country']);
        $server_city = $server['server_city'];
        $server_bage = $server['server_bage'];
        try {
            $Query->Connect($server_ip, $server_port, 1, SourceQuery::SOURCE);
            $info = $Query->GetInfo();
            $players = $Query->GetPlayers();

            $map_name = array_reverse(explode("/", $info['Map']))[0];
            $map_image_path = CACHE . 'img/maps/' . $info['GameID'] . '/' . $map_name . '.webp';
            $map_image = file_exists($map_image_path) ? $map_name : '-';

            $cache['servers'][$i] = $return['servers'][$i] = [
                'ip' => $fake_ip,
                'HostName' => $server_name,
                'Map' => $map_name,
                'Map_image' => $map_image,
                'Players' => $info['Players'],
                'MaxPlayers' => $info['MaxPlayers'],
                'Mod' => $info['GameID'],
                'GameMode' => $server_mod,
                'City' => $server_city,
                'Country' => $server_country,
                'Bage' => $server_bage,
                'players' => $players
            ];
        } catch (Exception $e) {
            $cache['servers'][$i] = $return['servers'][$i] = [
                'ip' => $fake_ip,
                'HostName' => $Translate->get_translate_module_phrase('module_block_main_servers', '_serverOff'),
                'Map' => '-',
                'Map_image' => '-',
                'Players' => 0,
                'MaxPlayers' => 0,
                'Mod' => '730',
                'GameMode' => $server_mod,
                'City' => '',
                'Country' => '',
                'Bage' => $Translate->get_translate_module_phrase('module_block_main_servers', '_serverUnavailable'),
                'players' => []
            ];
        } finally {
            $Query->Disconnect();
        }
    }
    $cache['time'] = time() + TIME;
    set_module_cache($cache);
} else {
    $seconds = $cache['time'] - time();
    $return['servers'] = get_module_cache()['servers'];
    $return['message'] = $Translate->get_translate_module_phrase('module_block_main_servers', '_dataUpdate') . $seconds . $Translate->get_translate_module_phrase('module_block_main_servers', '_dataUpdateSec');
}

echo json_encode($return, JSON_UNESCAPED_UNICODE);
exit();

function get_module_cache()
{
    $cache_file = '../temp/cache.json';

    if (file_exists($cache_file)) {
        $json_data = file_get_contents($cache_file);
        return json_decode($json_data, true);
    } else {
        !file_exists('../temp') && mkdir('../temp', 0777, true);
        file_put_contents($cache_file, json_encode([]));
        return [];
    }
}

function set_module_cache($data)
{
    !file_exists('../temp') && mkdir('../temp', 0777, true);
    $cache_file = '../temp/cache.json';
    file_put_contents($cache_file, json_encode($data, JSON_PRETTY_PRINT));
}
