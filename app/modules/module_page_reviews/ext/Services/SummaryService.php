<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\Repositories\CriteriaRepository;
use app\modules\module_page_reviews\ext\Repositories\ReviewRepository;

class SummaryService
{
    private $reviews;
    private $criteria;

    public function __construct(ReviewRepository $reviews, CriteriaRepository $criteria)
    {
        $this->reviews = $reviews;
        $this->criteria = $criteria;
    }

    public function get(): array
    {
        return $this->reviews->getSummary($this->criteria->listActive());
    }
}
