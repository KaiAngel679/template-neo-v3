<?php

namespace app\modules\module_page_bonuses\ext\Services;

use app\modules\module_page_bonuses\ext\Repositories\CacheRepository;
use app\modules\module_page_bonuses\ext\Repositories\DatabaseRepository;

class BonusesService
{
    protected $dr, $cr;

    public function __construct($Db)
    {
        $this->dr = new DatabaseRepository($Db);
        $this->cr = new CacheRepository();
    }

    public function checkModal(string $steam): array
    {
        return $this->dr->checkModal($steam);
    }

    public function createdUserModal(string $steam)
    {
        return $this->dr->createdUserModal($steam);
    }

    public function updateUserModal(string $steam)
    {
        return $this->dr->updateUserModal($steam);
    }

    public function checkReward(string $steam): array
    {
        return $this->dr->checkReward($steam);
    }

    public function getCache(string $name): array {
        return $this->cr->getCache($name);
    }

    public function createTables(): void {
        $this->dr->createTables();
    }
}
