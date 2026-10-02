<?php

namespace app\modules\module_page_cards\ext\Controllers;

use app\modules\module_page_cards\ext\Services\CardsStatsService;

class CardsStatsController
{
    protected $css;

    public function __construct($Db)
    {
        $this->css = new CardsStatsService($Db);
    }

    public function getCountOpenCards(): array
    {
        return $this->css->getCountOpenCards();
    }

    public function getCountMoney(): array
    {
        return $this->css->getCountMoney();
    }

    public function getTopUsersOpens(): array
    {
        return $this->css->getTopUsersOpens();
    }

    public function getAllUsersStreak(): array
    {
        return $this->css->getAllUsersStreak();
    }
}
