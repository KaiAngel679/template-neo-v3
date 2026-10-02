<?php

namespace app\modules\module_page_reviews\ext\Controllers;

use app\modules\module_page_reviews\ext\Repositories\FileRepository;
use app\modules\module_page_reviews\ext\Services\BanService;
use app\modules\module_page_reviews\ext\Services\SettingsService;

class BanController
{
    private $service;
    private $settings;

    public function __construct(?object $General = null, ?object $Db = null, ?object $Translate = null)
    {
        $files = new FileRepository();
        $this->settings = new SettingsService($files, $Translate);
        $this->service = new BanService($Db, $General, $this->settings, $Translate);
    }

    public function list(): array
    {
        return $this->service->list();
    }

    public function create(): array
    {
        return $this->service->create([
            'steamid' => $_POST['steamid'] ?? '',
            'ip' => $_POST['ip'] ?? '',
            'scope' => $_POST['scope'] ?? 'all',
            'reason' => $_POST['reason'] ?? '',
        ]);
    }

    public function delete(): array
    {
        return $this->service->delete((string) ($_POST['id'] ?? ''));
    }

    public function saveOptions(): array
    {
        return $this->settings->saveBanDeleteContent(!empty($_POST['ban_delete_content']));
    }

    public function service(): BanService
    {
        return $this->service;
    }
}
