<?php

namespace app\modules\module_page_reviews\ext\Controllers;

use app\modules\module_page_reviews\ext\Repositories\FileRepository;
use app\modules\module_page_reviews\ext\Services\LegacyMigrationService;
use app\modules\module_page_reviews\ext\Services\SettingsService;

class SettingsController
{
    private $service;
    private $Db;
    private $Translate;

    public function __construct(?object $Db = null, ?object $Translate = null)
    {
        $this->Db = $Db;
        $this->Translate = $Translate;
        $this->service = new SettingsService(new FileRepository(), $Translate);
    }

    public function get(): array
    {
        return $this->service->get();
    }

    public function save(): array
    {
        return $this->service->save([
            'reward_enabled' => $_POST['reward_enabled'] ?? 0,
            'reward_amount' => $_POST['reward_amount'] ?? 0,
            'min_hours_enabled' => $_POST['min_hours_enabled'] ?? 0,
            'min_hours' => $_POST['min_hours'] ?? 0,
            'fields_mode' => $_POST['fields_mode'] ?? 'all',
            'server_select_enabled' => $_POST['server_select_enabled'] ?? 1,
            'list_columns' => $_POST['list_columns'] ?? 1,
            'blacklist_enabled' => $_POST['blacklist_enabled'] ?? 0,
            'blacklist_words' => $_POST['blacklist_words'] ?? '',
            'blacklist_mode' => $_POST['blacklist_mode'] ?? 'censor',
            'blacklist_style' => $_POST['blacklist_style'] ?? 'hearts',
            'rating_icon' => $_POST['rating_icon'] ?? 'star-fill',
            'discord_webhook_url' => $_POST['discord_webhook_url'] ?? '',
            'discord_webhook_image' => $_POST['discord_webhook_image'] ?? '',
            'discord_webhook_color' => $_POST['discord_webhook_color'] ?? '#5865F2',
        ]);
    }

    public function migrateLegacy(): array
    {
        if ($this->Db === null) {
            return ['status' => 'error', 'message' => 'Database unavailable'];
        }

        return (new LegacyMigrationService($this->Db, null, $this->Translate))->run();
    }

    public function canMigrateLegacy(): bool
    {
        if ($this->Db === null) {
            return false;
        }

        return (new LegacyMigrationService($this->Db, null, $this->Translate))->isAvailable();
    }

    public function service(): SettingsService
    {
        return $this->service;
    }
}
