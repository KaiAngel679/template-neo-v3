<?php

use app\modules\module_page_cards\ext\Controllers\CardsStatsController;
use app\modules\module_page_cards\ext\Controllers\CardsSettingsController;

$Router->map('GET|POST', 'cards/[:views]/', 'cards');
$Map = $Router->match();
$view = $Map['params']['views'] ?? 'settings';

if (isset($_SESSION['user_admin'])) {
    $csc = new CardsStatsController($Db);
    $csсс = new CardsSettingsController($Db, $Translate);
    $csсс->createTables();
    if (isset($_POST['save_general_settings'])) {
        exit(json_encode($csсс->saveGeneralSettings((int)$_POST['price'], (string)$_POST['currency'], (array)$_POST['ids'], (string)$_POST['svg']), true));
    } elseif (isset($_POST['save_rewards'])) {
        exit(json_encode($csсс->savePrizesSettings((array)$_POST['prizes']), true));
    } elseif (isset($_POST['delete_reward'])) {
        exit(json_encode($csсс->deleteReward((int)$_POST['id']), true));
    } elseif (isset($_POST['update_reward'])) {
        exit(json_encode($csсс->updateReward((int)$_POST['id'], (string)$_POST['type'], (int)$_POST['count'], (int)$_POST['rare'], (int)$_POST['chance']), true));
    } elseif (isset($_POST['get_reward'])) {
        exit(json_encode($csсс->getReward((int)$_POST['id']), true));
    }
} else {
    get_iframe(401, 'The page is for admin only');
}
