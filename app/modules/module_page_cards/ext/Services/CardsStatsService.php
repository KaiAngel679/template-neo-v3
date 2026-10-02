<?php

namespace app\modules\module_page_cards\ext\Services;

use app\modules\module_page_cards\ext\Repositories\DatabaseRepository;

class CardsStatsService
{
    protected $dr;

    public function __construct($Db)
    {
        $this->dr = new DatabaseRepository($Db);
    }

    public function getCountOpenCards(): array
    {
        return $this->dr->getCountOpenCards();
    }

    public function getCountMoney(): array
    {
        return $this->dr->getCountMoney();
    }

    public function getTopUsersOpens(): array
    {
        return $this->dr->getTopUsersOpens();
    }

    public function getAllUsersStreak(): array
    {
        return $this->dr->getAllUsersStreak();
    }
}
