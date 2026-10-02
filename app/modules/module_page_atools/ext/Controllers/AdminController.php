<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\AdminService;

class AdminController
{
    private $service;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->service = new AdminService($Db, $General, $Translate, $Modules);
    }

    public function getGroupsGame(string $type): ?array
    {
        return $this->service->getGroupsGame($type);
    }

    public function createAdmin(string $steamid, string $type, string $group, string $expire, array $servers, array $permissions, string $vipGroup = ''): array
    {
        return $this->service->createAdmin($steamid, $type, $group, $expire, $servers, $permissions, $vipGroup);
    }

    public function getAdminsList(string $type, array $servers, int $group, int $limit = 10, int $offset = 0, string $search = '', array $accessPermissions = []): array
    {
        return $this->service->getAdminsList($type, $servers, $group, $_SESSION['steamid'], $limit, $offset, $search, $accessPermissions);
    }

    public function deleteAdmin(int $adminId, string $type): array
    {
        return $this->service->deleteAdmin($adminId, $type);
    }

    public function updateAdmin(int $adminId, string $type, string $group, string $expire, array $servers, array $permissions): array
    {
        return $this->service->updateAdmin($adminId, $type, $group, $expire, $servers, $permissions);
    }

    public function giveWarn(string $targetSteamid, string $reason, int $expireRaw, int $adminId, string $type): array
    {
        return $this->service->giveWarn($targetSteamid, $reason, $expireRaw, $_SESSION['steamid'], $adminId, $type);
    }

    public function removeWarns(string $targetSteamid, array $warnIds): array
    {
        return $this->service->removeWarns($targetSteamid, $warnIds, $_SESSION['steamid']);
    }

    public function deleteWarns(string $targetSteamid, array $warnIds): array
    {
        return $this->service->deleteWarns($targetSteamid, $warnIds, $_SESSION['steamid']);
    }

    public function updateWarn(string $targetSteamid, int $warnId, string $reason, string $expireRaw): array
    {
        return $this->service->updateWarn($targetSteamid, $warnId, $reason, $expireRaw, $_SESSION['steamid']);
    }
}
