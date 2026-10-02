<?php

namespace app\modules\module_page_bonuses\ext\Controllers;
use app\modules\module_page_bonuses\ext\Services\TelegramService;

class TelegramController
{
    protected $ts;

    public function __construct($Db, $Translate) {
        $this->ts = new TelegramService($Db, $Translate);
    }

    public function authTelegram(string $hash): array {
        return $this->ts->authTelegram($hash);
    }
}
