<?php

namespace app\modules\module_page_reviews\ext\Controllers;

use app\modules\module_page_reviews\ext\Repositories\DatabaseRepository;

class DatabaseController
{
    private $repo;

    public function __construct(object $Db)
    {
        $this->repo = new DatabaseRepository($Db);
    }

    public function ensureSchema(): void
    {
        $this->repo->createTables();
    }
}
