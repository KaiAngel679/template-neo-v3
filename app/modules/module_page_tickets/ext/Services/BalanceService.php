<?php

namespace app\modules\module_page_tickets\ext\Services;

use app\modules\module_page_tickets\ext\Services\DatabaseService;

class BalanceService
{
    protected $ds, $Translate;

    public function __construct($Db, $Translate)
    {
        $this->ds = new DatabaseService($Db);
        $this->Translate = $Translate;
    }

    public function addBalance($steam, $summ)
    {
        $isset = $this->ds->checkLKTable($steam);
        if (empty($isset)) {
            $this->ds->createdUserLKTable($steam);
        }
        $this->ds->updateBalanceUser($summ, $steam);
        $this->ds->logBalanceUser($summ, $steam, $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketReview'));
    }
}
