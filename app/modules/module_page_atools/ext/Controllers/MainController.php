<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\MainService;

class MainController
{
    private $service;

    public function __construct(object $Db, object $General, object $Translate)
    {
        $this->service = new MainService($Db, $General, $Translate);
    }

    public function getPageStats(): array
    {
        return $this->service->getPageStats();
    }

    public function getCharts(int $days, string $chart): array
    {
        return $this->service->getCharts($days, $chart);
    }

    public function getTopAdmins(string $metric): array
    {
        return $this->service->getTopAdmins($metric);
    }

    public function getTopMetrics(array $allowed): array
    {
        return $this->service->getTopMetrics($allowed);
    }

    public function hasReportsAccess(): bool
    {
        return $this->service->hasReportsAccess();
    }

    public function hasChecksBackend(): bool
    {
        return $this->service->hasChecksBackend();
    }
}
