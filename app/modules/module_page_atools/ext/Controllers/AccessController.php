<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\AccessService;

class AccessController
{
    private $service;

    public function __construct(object $Db)
    {
        $this->service = new AccessService($Db);
    }

    public function checkPermission(string $permission): bool
    {
        return $this->service->hasPermission($_SESSION['steamid'], $permission);
    }

    public function hasAnyPermission(): bool
    {
        return $this->service->hasAnyPermission($_SESSION['steamid']);
    }
}
