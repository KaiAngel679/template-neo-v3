<?php

use app\modules\module_page_bonuses\ext\Controllers\BonusesController;
use app\modules\module_page_bonuses\ext\Controllers\DiscordController;
use app\modules\module_page_bonuses\ext\Controllers\SettingsController;
use app\modules\module_page_bonuses\ext\Controllers\TelegramController;
use app\modules\module_page_bonuses\ext\Controllers\VkController;

$Router->map('GET|POST', 'bonuses/[:views]/', 'bonuses');
$Map = $Router->match();
$views = $Map['params']['views'] ?? 'main';

$tc = new TelegramController($Db, $Translate);
$dc = new DiscordController($Db, $General, $Translate);
$sc = new SettingsController($Translate);
$vc = new VkController($Db, $Translate);
$bs = new BonusesController($Db);

if (isset($_SESSION['steamid64'])) {
    if ($views == 'main') {
        if (isset($_POST['hash_tg'])) {
            exit(json_encode($tc->authTelegram($_POST['hash']), true));
        } elseif (isset($_POST['auth_ds'])) {
            exit(json_encode($dc->authDiscord($_POST['code']), true));
        } elseif (isset($_POST['auth_vk'])) {
            exit(json_encode($vc->authVk(), true));
        }
    } else {
        if (isset($_SESSION['user_admin'])) {
            $bs->createTables();
            if (isset($_POST['save_settings_tg'])) {
                exit(json_encode($sc->saveSettingsTg($_POST['money_tg'], $_POST['text_tg'], $_POST['enabled_tg'], $_POST['url_tg'], $_POST['bot_key_tg'], $_POST['bot_id_tg']), true));
            } elseif (isset($_POST['save_settings_ds'])) {
                exit(json_encode($sc->saveSettingsDs($_POST['money_ds'], $_POST['text_ds'], $_POST['enabled_ds'], $_POST['url_ds'], $_POST['guild_id_ds'], $_POST['client_id_ds'], $_POST['secret_id_ds']), true));
            } elseif (isset($_POST['save_settings_vk'])) {
                exit(json_encode($sc->saveSettingsVk($_POST['money_vk'], $_POST['text_vk'], $_POST['enabled_vk'], $_POST['url_vk']), true));
            }
        } else {
            get_iframe(401, $this->Translate->get_translate_phrase('_accessDenied'));
        }
    }
} else {
    get_iframe(401, $this->Translate->get_translate_module_phrase('module_page_bonuses', '_youNotAuth'));
}
