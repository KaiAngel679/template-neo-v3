<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\RendersService;

class RendersController
{
    private $service;

    public function __construct(object $Db, object $General, object $Translate)
    {
        $this->service = new RendersService($Db, $General, $Translate);
    }

    public function renderTerms(?string $scope = null): array
    {
        return ['status' => 'success', 'data' => $this->service->renderTerms($scope)];
    }

    public function renderReasons(): array
    {
        return ['status' => 'success', 'data' => $this->service->renderReasons()];
    }

    public function renderVipGroups(bool $excludeHiddenTest = false): array
    {
        return ['status' => 'success', 'data' => $this->service->renderVipGroups($excludeHiddenTest)];
    }

    public function renderGroups(): array
    {
        return ['status' => 'success', 'data' => $this->service->renderGroups()];
    }

    public function renderAdmins(string $type): array
    {
        return ['status' => 'success', 'data' => $this->service->renderAdmins($type)];
    }

    public function renderVerdicts(): array
    {
        return ['status' => 'success', 'data' => $this->service->renderVerdicts()];
    }
}
