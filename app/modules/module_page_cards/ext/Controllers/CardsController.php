<?php

namespace app\modules\module_page_cards\ext\Controllers;

use app\modules\module_page_cards\ext\Services\CardsService;

class CardsController
{
    protected $cs;

    public function __construct($Db, $Translate, $General)
    {
        $this->cs = new CardsService($Db, $Translate, $General);
    }

    public function checkOpen(string $steamid): array
    {
        return $this->cs->checkOpen($steamid);
    }

    public function openCard(string $steamid): array
    {
        return $this->cs->openCard($steamid);
    }

    public function buyCard(string $steamid): array
    {
        return $this->cs->buyCard($steamid);
    }

    public function checkOpenDb(string $steamid): array
    {
        return $this->cs->checkOpenDb($steamid);
    }
}
