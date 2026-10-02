<?php

namespace app\modules\module_block_main_cards\ext\Services;

use app\modules\module_block_main_cards\ext\Repositories\DatabaseRepository;

class CardsService
{
    protected $dr;

    public function __construct($Db)
    {
        $this->dr = new DatabaseRepository($Db);
    }

    public function getLast10Type(): array
    {
        return $this->dr->getLast10Type();
    }

    public function getLast10TypeRare(): array
    {
        return $this->dr->getLast10TypeRare();
    }
}
