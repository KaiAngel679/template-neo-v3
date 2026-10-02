<?php

use app\modules\module_block_main_servers\ext\Monitoring;

$Mon = new Monitoring($Db, $General, $Translate, $Modules, $Router, $Notifications);

if(isset($_SESSION['monAccess'])){
  if(isset($_POST['mon_action'])){
    exit(json_encode($Mon->MonAction($_POST), true));
  }
}

$monType     = (empty($Mon->Settings['type']) || $Mon->Settings['type'] == 0) ? 0 : 1;
$moncardSuffix  = (!empty($Mon->Settings['server_card_count']) && $Mon->Settings['server_card_count'] != '3') ? '-' . $Mon->Settings['server_card_count'] : '';
$mongridClass   = 'servers__card-wrapper' . $moncardSuffix;
$montableClass  = (!empty($Mon->Settings['server_table_count']) && $Mon->Settings['server_table_count'] == 1) ? 'table-mon__servers-wrap-single' : 'table-mon__servers-wrap';
$mondefaultView = ($monType == 0) ? 'grid' : 'table';
$monloaderTag    = ($mondefaultView === 'grid') ? 'cards-loader' : 'table-loader';
$monloaderCount  = $Mon->getUniqueServersCount();
$monplugsLoader  = ($mondefaultView === 'grid') ? $Mon->getPlugsCount() : 0;

$monSettings = [
    'updateTime' => !empty($Mon->Settings['time']) ? $Mon->Settings['time'] * 1000 : 30000,
    'enableMods' => !empty($Mon->Settings['enable_mods']) ? true : false,
    'filterModes' => !empty($Mon->Settings['filter_modes']) ? true : false,
    'plugs' => !empty($Mon->Settings['plugs']) ? true : false,
    'viewType' => $mondefaultView,
    'showPing' => $Mon->Settings['show_ping'] ?? 0,
    'showOnlineLine' => $Mon->Settings['show_online_line'] ?? 0,
];
