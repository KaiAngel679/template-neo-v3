<?php

namespace app\modules\module_block_main_cards\ext\Controllers;

use app\modules\module_block_main_cards\ext\Services\CardsService;

class CardsController
{
    protected $cs;

    public function __construct($Db)
    {
        $this->cs = new CardsService($Db);
    }

    public function getLast10Type(): array
    {
        return $this->cs->getLast10Type();
    }

    public function getLast10TypeRare(): array
    {
        return $this->cs->getLast10TypeRare();
    }
}
