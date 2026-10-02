<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\SettingsService;

class SettingsController
{
    private $service;

    public function __construct(object $Translate)
    {
        $this->service = new SettingsService($Translate);
    }

    public function createGroup(array $name, array $permissions): array
    {
        return $this->service->createGroup($name, $permissions);
    }

    public function createReason(array $name, string $type): array
    {
        return $this->service->createReason($name, $type);
    }

    public function deleteGroup(int $id): array
    {
        return $this->service->deleteGroup($id);
    }

    public function updateSettings(
        int $maxWarns,
        int $autoDeleteAdminMaxWarns,
        int $debugLogs,
        int $hideVipTest,
        string $vipTestGroup,
        string $blockdbApiKey,
        int $defaultAllServers
    ): array {
        return $this->service->updateSettings(
            $maxWarns,
            $autoDeleteAdminMaxWarns,
            $debugLogs,
            $hideVipTest,
            $vipTestGroup,
            $blockdbApiKey,
            $defaultAllServers
        );
    }

    public function updateGroup(int $id, array $name, array $permissions): array
    {
        return $this->service->updateGroup($id, $name, $permissions);
    }

    public function updateReason(int $id, array $name, string $type): array
    {
        return $this->service->updateReason($id, $name, $type);
    }

    public function deleteReason(int $id): array
    {
        return $this->service->deleteReason($id);
    }

    public function createTerm(array $name, int $time, string $type): array
    {
        return $this->service->createTerm($name, $time, $type);
    }

    public function updateTerm(int $id, array $name, int $time, string $type): array
    {
        return $this->service->updateTerm($id, $name, $time, $type);
    }

    public function deleteTerm(int $id): array
    {
        return $this->service->deleteTerm($id);
    }

    public function createVipGroup(string $ini, array $display): array
    {
        return $this->service->createVipGroup($ini, $display);
    }

    public function updateVipGroup(int $id, string $ini, array $display): array
    {
        return $this->service->updateVipGroup($id, $ini, $display);
    }

    public function deleteVipGroup(int $id): array
    {
        return $this->service->deleteVipGroup($id);
    }

    public function importFromManagerSystem(object $General): array
    {
        return $this->service->importFromManagerSystem($General);
    }
}
