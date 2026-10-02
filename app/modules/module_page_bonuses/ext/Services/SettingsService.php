<?php

namespace app\modules\module_page_bonuses\ext\Services;

use app\modules\module_page_bonuses\ext\Repositories\CacheRepository;

class SettingsService
{
    protected $cr, $Translate;

    private const SECTIONS = [
        'tg' => ['enabled_tg', 'text_tg', 'money_tg', 'url_tg', 'bot_key_tg', 'bot_id_tg'],
        'ds' => ['enabled_ds', 'text_ds', 'money_ds', 'url_ds', 'guild_id_ds', 'client_id_ds', 'secret_id_ds'],
        'vk' => ['enabled_vk', 'text_vk', 'money_vk', 'url_vk'],
    ];

    private const CACHE_ERRORS = [
        'not' => '_cacheFileNotFound',
        'perm' => '_noPermissionToWrite',
        'fake' => '_errorWritingCache',
    ];

    public function __construct($Translate)
    {
        $this->cr = new CacheRepository;
        $this->Translate = $Translate;
    }

    public function saveSettings(string $section, array $values): array
    {
        if (!isset(self::SECTIONS[$section])) {
            return ['status' => 'error', 'message' => $this->translate('_unknownErrorSavingParameters')];
        }

        $data = [];
        foreach (self::SECTIONS[$section] as $key) {
            if (array_key_exists($key, $values)) {
                $data[$key] = $values[$key];
            }
        }

        $result = $this->cr->putCache($data, $section, 'settings');
        if ($result['status'] === 'error') {
            $phrase = self::CACHE_ERRORS[$result['message']] ?? '_unknownErrorSavingParameters';
            return ['status' => 'error', 'message' => $this->translate($phrase)];
        }

        return ['status' => 'success', 'message' => $this->translate('_settingsSavedSuccessfully')];
    }

    public function saveSettingsTg($money_tg, $text_tg, $enabled_tg, $url_tg, $bot_key_tg, $bot_id_tg): array
    {
        return $this->saveSettings('tg', [
            'money_tg' => $money_tg,
            'text_tg' => $text_tg,
            'enabled_tg' => $enabled_tg,
            'url_tg' => $url_tg,
            'bot_key_tg' => $bot_key_tg,
            'bot_id_tg' => $bot_id_tg,
        ]);
    }

    public function saveSettingsDs($money_ds, $text_ds, $enabled_ds, $url_ds, $guild_id_ds, $client_id_ds, $secret_id_ds): array
    {
        return $this->saveSettings('ds', [
            'money_ds' => $money_ds,
            'text_ds' => $text_ds,
            'enabled_ds' => $enabled_ds,
            'url_ds' => $url_ds,
            'guild_id_ds' => $guild_id_ds,
            'client_id_ds' => $client_id_ds,
            'secret_id_ds' => $secret_id_ds,
        ]);
    }

    public function saveSettingsVk($money_vk, $text_vk, $enabled_vk, $url_vk): array
    {
        return $this->saveSettings('vk', [
            'money_vk' => $money_vk,
            'text_vk' => $text_vk,
            'enabled_vk' => $enabled_vk,
            'url_vk' => $url_vk,
        ]);
    }

    private function translate(string $phrase): string
    {
        return $this->Translate->get_translate_module_phrase('module_page_bonuses', $phrase);
    }
}
