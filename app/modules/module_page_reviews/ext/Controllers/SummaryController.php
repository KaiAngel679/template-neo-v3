<?php

namespace app\modules\module_page_reviews\ext\Controllers;

use app\modules\module_page_reviews\ext\Repositories\CriteriaRepository;
use app\modules\module_page_reviews\ext\Repositories\ReviewRepository;
use app\modules\module_page_reviews\ext\Services\SummaryService;

class SummaryController
{
    private $service;

    public function __construct(object $Db)
    {
        $this->service = new SummaryService(
            new ReviewRepository($Db),
            new CriteriaRepository($Db)
        );
    }

    public function get(): array
    {
        return $this->service->get();
    }
}
