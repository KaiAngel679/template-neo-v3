<?php

namespace app\modules\module_page_reviews\ext\Controllers;

use app\modules\module_page_reviews\ext\Services\ServersService;

class ServersController
{
    private $service;

    public function __construct(object $General, ?object $Translate = null)
    {
        $this->service = new ServersService($General, $Translate);
    }

    public function getServers(): array
    {
        return $this->service->getServers();
    }

    public function service(): ServersService
    {
        return $this->service;
    }
}
