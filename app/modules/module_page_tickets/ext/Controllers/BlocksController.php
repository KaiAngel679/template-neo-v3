<?php

namespace app\modules\module_page_tickets\ext\Controllers;

use app\modules\module_page_tickets\ext\Services\DatabaseService;
use app\modules\module_page_tickets\ext\Repositories\AdditionalRepository;

class BlocksController
{
    protected $ds, $ar, $Translate;

    public function __construct($Db, $Translate)
    {
        $this->ds = new DatabaseService($Db);
        $this->ar = new AdditionalRepository($Translate);
        $this->Translate = $Translate;
    }

    public function createNewBlock($steam, $reason, $time)
    {
        if (!$this->ar->isValidSteamId($steam)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', empty($steam) ? '_forgotSteam' : '_didntSteam')];
        }
        if (empty($reason)) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_notReason')];
        if ($time == '' || $time == null) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_notTime')];
        $this->ds->createNewBlock($steam, $reason, $time);
        foreach ($this->ds->getTicketsListUser($steam) as $key) {
            $this->ds->closeTicketAuto($key['id']);
        }
        return ['status' => 'success', 'url' => 'reload'];
    }

    public function deleteBlock($steam)
    {
        if (!is_numeric($steam)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_notReveivedSteam')];
        }
        $this->ds->deleteBlock($steam);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_blockDelete')];
    }
}
