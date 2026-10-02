<?php

namespace app\modules\module_page_bonuses\ext\Controllers;

use app\modules\module_page_bonuses\ext\Services\SettingsService;

class SettingsController {
    protected $ss;

    public function __construct($Translate) {
        $this->ss = new SettingsService($Translate);
    }

    public function saveSettingsTg($money_tg, $text_tg, $enabled_tg, $url_tg, $bot_key_tg, $bot_id_tg): array {
        return $this->ss->saveSettingsTg($money_tg, $text_tg, $enabled_tg, $url_tg, $bot_key_tg, $bot_id_tg);
    }

    public function saveSettingsDs($money_ds, $text_ds, $enabled_ds, $url_ds, $guild_id_ds, $client_id_ds, $secret_id_ds): array {
        return $this->ss->saveSettingsDs($money_ds, $text_ds, $enabled_ds, $url_ds, $guild_id_ds, $client_id_ds, $secret_id_ds);
    }

    public function saveSettingsVk($money_vk, $text_vk, $enabled_vk, $url_vk): array {
        return $this->ss->saveSettingsVk($money_vk, $text_vk, $enabled_vk, $url_vk);
    }
}
