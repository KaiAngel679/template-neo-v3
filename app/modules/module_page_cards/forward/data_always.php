<?php

use app\modules\module_page_cards\ext\Controllers\CardsController;

if (!empty($_SESSION['steamid64'])) {
    $ccCards = new CardsController($Db, $Translate, $General);
    $progressCards = $ccCards->checkOpenDb((string)$_SESSION['steamid64']);
    if (isset($_POST['check_open'])) {
        exit(json_encode($ccCards->checkOpen((string)$_SESSION['steamid64']), true));
    } elseif (isset($_POST['open_card'])) {
        exit(json_encode($ccCards->openCard((string)$_SESSION['steamid64']), true));
    } elseif (isset($_POST['buy_card'])) {
        exit(json_encode($ccCards->buyCard((string)$_SESSION['steamid64']), true));
    }

    require MODULES . "module_page_cards/forward/vendors.php";
}
