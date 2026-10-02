<?php

namespace app\modules\module_page_bonuses\ext\Controllers;

use app\modules\module_page_bonuses\ext\Services\BonusesService;

class BonusesController
{
    protected $bs;

    public function __construct($Db)
    {
        $this->bs = new BonusesService($Db);
    }

    public function checkModal(string $steam): array
    {
        return $this->bs->checkModal($steam);
    }

    public function createdUserModal(string $steam)
    {
        return $this->bs->createdUserModal($steam);
    }

    public function updateUserModal(string $steam)
    {
        return $this->bs->updateUserModal($steam);
    }

    public function checkReward(string $steam): array
    {
        return $this->bs->checkReward($steam);
    }

    public function getCache(string $name): array
    {
        return $this->bs->getCache($name);
    }

    public function createTables(): void
    {
        $this->bs->createTables();
    }
}
