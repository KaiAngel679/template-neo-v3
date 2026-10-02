<?php

use app\modules\module_page_pay\ext\Lk_module;

if (IN_LR != true) {
    header('Location: ' . $General->arr_general['site']);
    exit;
}

$LK = new Lk_module($Translate, $Notifications, $General, $Modules, $Db);

if (isset($_POST['steam'])) {
    $LK->LkOnPayment($_POST);
    exit;
} else if (!empty($_POST['promocode']) && !empty($_POST['amount']) && !empty($_POST['steamid'])) {
    $LK->LkCalculatePromo($_POST['promocode'], $_POST['steamid'], $_POST['amount']);
    exit;
} else if (isset($_POST['steamidload'])) {
    $LK->LkLoadPlayerProfile($_POST['steamidload']);
    exit;
}
