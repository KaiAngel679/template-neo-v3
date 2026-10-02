<?php

use app\modules\module_page_admintime\ext\AdminTimeCore;

$AdminTimeCore = new AdminTimeCore($Db,  $General, $Translate, $Modules);
$hasAccess_admintime = $AdminTimeCore->Access->checkAccess();

