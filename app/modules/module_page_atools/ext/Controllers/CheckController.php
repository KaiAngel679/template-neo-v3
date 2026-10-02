<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\CheckService;

class CheckController
{
    private $service;

    public function __construct(object $Db, object $General, object $Translate)
    {
        $this->service = new CheckService($Db, $General, $Translate);
    }

    public function getChecksList(int $admin, array $servers, string $verdict, string $dateFrom, string $dateTo, int $limit, int $offset, string $search): array
    {
        return $this->service->getChecksList($admin, $servers, $verdict, $dateFrom, $dateTo, $limit, $offset, $_SESSION['steamid64'], $search);
    }

    public function deleteChecks(array $checkIds): array
    {
        return $this->service->deleteChecks($checkIds, $_SESSION['steamid64']);
    }
}
