<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\AccessService;
use app\modules\module_page_atools\ext\Services\PlayerSearchService;

class PlayerSearchController
{
    private $service;
    private $AccessService;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->service = new PlayerSearchService($Db, $General, $Translate, $Modules);
        $this->AccessService = new AccessService($Db);
    }

    public function search(string $query): array
    {
        $mySteamid = (string) ($_SESSION['steamid64'] ?? $_SESSION['steamid'] ?? '');
        $permissions = $this->AccessService->getAllPermissions((string) ($_SESSION['steamid'] ?? ''));

        return $this->service->search($query, $mySteamid, $permissions);
    }
}
