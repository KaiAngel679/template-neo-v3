<?php

namespace app\modules\module_page_bonuses\ext\Controllers;
use app\modules\module_page_bonuses\ext\Services\VkService;

class VkController
{
    protected $vs;

    public function __construct($Db, $Translate) {
        $this->vs = new VkService($Db, $Translate);
    }

    public function authVk(): array {
        return $this->vs->authVk();
    }
}
