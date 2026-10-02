<?php

namespace app\modules\module_page_bonuses\ext\Controllers;
use app\modules\module_page_bonuses\ext\Services\DiscordService;

class DiscordController
{
    protected $ds;

    public function __construct($Db, $General, $Translate) {
        $this->ds = new DiscordService($Db, $General, $Translate);
    }

    public function authDiscord(string $code): array {
        return $this->ds->authDiscord($code);
    }
}
