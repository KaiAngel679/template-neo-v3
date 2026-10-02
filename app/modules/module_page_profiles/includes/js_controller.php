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
!defined('MODULESCACHE') && define('MODULESCACHE', STORAGE . 'modules_cache/');
!defined('ASSETS') && define('ASSETS', STORAGE . 'assets/');
!defined('SESSIONS') && define('SESSIONS', CACHE . 'sessions/');
!defined('LOGS') && define('LOGS', CACHE . 'logs/');
!defined('IMG') && define('IMG', CACHE . 'img/');
!defined('ASSETS_CSS') && define('ASSETS_CSS', ASSETS . 'css/');
!defined('ASSETS_JS') && define('ASSETS_JS', ASSETS . 'js/');
!defined('THEMES') && define('THEMES', ASSETS_CSS . 'themes/');
!defined('RANKS_PACK') && define('RANKS_PACK', IMG . 'ranks/');
!defined('MINUTE_IN_SECONDS') && define('MINUTE_IN_SECONDS', 60);
!defined('HOUR_IN_SECONDS') && define('HOUR_IN_SECONDS', 3600);
!defined('DAY_IN_SECONDS') && define('DAY_IN_SECONDS', 86400);
!defined('WEEK_IN_SECONDS') && define('WEEK_IN_SECONDS', 604800);
!defined('MONTH_IN_SECONDS') && define('MONTH_IN_SECONDS', 2592000);
!defined('YEAR_IN_SECONDS') && define('YEAR_IN_SECONDS', 31536000);

session_start();

require '../../../includes/functions.php';
require_once '../../../ext/Db.php';
require_once '../../../ext/Translate.php';
require_once '../../../ext/General.php';
require __DIR__ . '../../../../ext/SourceQuery/bootstrap.php';

use xPaw\SourceQuery\SourceQuery;

$return = [];

$Translate      = new \app\ext\Translate;
$Db             = new \app\ext\Db();
$General        = new \app\ext\General($Db);
$Query          = new SourceQuery();

if (isset($_POST['online'])) {
  $servers = $General->server_list;
  $servers_count = sizeof($servers);
  for ($i_ser = 0; $i_ser < $servers_count; $i_ser++) :
    $server[] = explode(":", $servers[$i_ser]['ip']);
    $server_name[] = $servers[$i_ser]['name'];
  endfor;

  for ($i_server = 0; $i_server < $servers_count; $i_server++) :
    try {
      $Query->Connect($server[$i_server][0], $server[$i_server][1], 1, SourceQuery::SOURCE);
      $SQuery[$i_server]['ip'] = $server[$i_server][0] . ':' . $server[$i_server][1];
      $SQuery[$i_server]['server_name'] = $server_name[$i_server];
      $SQuery[$i_server]['players'] = $Query->GetPlayers();
    } catch (Exception $e) {
      $SQuery[$i_server]['ip'] = $server[$i_server][0] . ':' . $server[$i_server][1];
      $SQuery[$i_server]['server_name'] = $server_name[$i_server];
      $SQuery[$i_server]['players'] = [];
    } finally {
      $Query->Disconnect();
    }
  endfor;

  $lastconnect = false;

  foreach ($SQuery as $server_search) {
    foreach ($server_search["players"] as $player) {
      if (strcasecmp($player["Name"], $_POST['online']["name"]) == 0) {
        $return['online'] = $server_search['server_name'];
        $return['ip'] = $server_search['ip'];
        $return['text'] = $Translate->get_translate_module_phrase('module_page_profiles', '_Playing_on_the_server');
        $lastconnect = true;
      }
    }
  }

  if ($lastconnect == false) {
    $return['online'] = $_POST['online']["lastconnect"];
    $return['text'] = $Translate->get_translate_module_phrase('module_page_profiles', '_Last_connect');
  }
}

echo json_encode($return, JSON_UNESCAPED_UNICODE);
exit;
