<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\LogsListService;

class LogsController
{
    private $service;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->service = new LogsListService($Db, $General, $Translate, $Modules);
    }

    public function getList(string $mySteamid, string $category, string $sort, string $dateFrom, string $dateTo, string $search, int $limit, int $offset): array
    {
        return $this->service->getList($mySteamid, $category, $sort, $dateFrom, $dateTo, $search, $limit, $offset);
    }

    public function deleteEntry(string $fileDate, int $index, string $mySteamid): array
    {
        return $this->service->deleteEntry($fileDate, $index, $mySteamid);
    }
}
