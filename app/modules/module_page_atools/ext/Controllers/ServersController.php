<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\ServersService;

class ServersController
{
    private $service;

    public function __construct(object $General)
    {
        $this->service = new ServersService($General);
    }

    public function getServers(?string $type = null): array
    {
        return ['status' => 'success', 'data' => $this->service->getServers($type)];
    }
}
