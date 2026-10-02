<?php

use app\modules\module_block_main_lk_top\ext\LkTopExt;

$LkTop = new LkTopExt($Db, $General, $Modules, $Translate);

if(isset($_POST['getLkTop'])){
    exit(json_encode($LkTop->Render($_POST['type'])));
}

$modules = $Modules->arr_module_init['page']['home']['interface']['afternavbar'];
$index_banner = array_search('module_block_main_banner_slider', $modules);
$index_lk = array_search('module_block_main_lk_top', $modules);
$foundBoth = $index_banner !== false && $index_lk !== false;
$areAdjacent = $foundBoth && abs($index_banner - $index_lk) === 1;
$isLkFirst = $foundBoth && $index_lk < $index_banner;
$lk_col_class = $areAdjacent ? 'col-md-3' : 'col-md-12';
$lk_full = !$areAdjacent ? true : false;
$banner_col_class = $areAdjacent ? 'col-md-9' : 'col-md-12';
$lk_open_row = !$areAdjacent || ($areAdjacent && $isLkFirst);
$lk_close_row = !$areAdjacent || ($areAdjacent && !$isLkFirst);;
$banner_open_row = !$areAdjacent || ($areAdjacent && !$isLkFirst);
$banner_close_row = !$areAdjacent || ($areAdjacent && $isLkFirst);
