<?php

namespace app\modules\module_page_cards\ext\Controllers;

use app\modules\module_page_cards\ext\Services\CardsSettingsService;

class CardsSettingsController
{
    protected $css;

    public function __construct($Db, $Translate)
    {
        $this->css = new CardsSettingsService($Db, $Translate);
    }

    public function saveGeneralSettings(int $price, string $currency, array $ids, string $svg): array
    {
        return $this->css->saveGeneralSettings($price, $currency, $ids, $svg);
    }

    public function getCacheSettings(): array
    {
        return $this->css->getCacheSettings();
    }

    public function createTables(): void
    {
        $this->css->createTables();
    }

    public function savePrizesSettings(array $prizes): array
    {
        return $this->css->savePrizesSettings($prizes);
    }

    public function getRewards(): array
    {
        return $this->css->getRewards();
    }

    public function deleteReward(int $id): array
    {
        return $this->css->deleteReward($id);
    }

    public function updateReward(int $id, string $type, int $count, int $rare, int $chance): array
    {
        return $this->css->updateReward($id, $type, $count, $rare, $chance);
    }

    public function getReward(int $id): array
    {
        return $this->css->getReward($id);
    }
}
