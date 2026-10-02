<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Repositories\DatabaseRepository;
use app\modules\module_page_atools\ext\Repositories\FileRepository;

class AccessService
{
    private $DatabaseRepository, $FileRepository;
    private $permissionsCache = [];

    public function __construct(object $Db)
    {
        $this->DatabaseRepository = new DatabaseRepository($Db);
        $this->FileRepository = new FileRepository();
    }

    private function getPermissionsList(string $steamid): array
    {
        if (!array_key_exists($steamid, $this->permissionsCache)) {
            $row = $this->DatabaseRepository->getAccessAdmin($steamid);
            if (!is_array($row) || !isset($row['permissions'])) {
                $this->permissionsCache[$steamid] = [];
            } else {
                $decoded = json_decode($row['permissions'], true);
                $this->permissionsCache[$steamid] = is_array($decoded) ? $decoded : [];
            }
        }
        return $this->permissionsCache[$steamid];
    }

    private function forgetPermissionsCache(string $steamid): void
    {
        if (function_exists("opcache_reset")) {
            opcache_reset();
        }
        unset($this->permissionsCache[$steamid]);
    }

    public function hasPermission(string $steamid, string $permission): bool
    {
        return in_array($permission, $this->getPermissionsList($steamid), true);
    }

    public function hasAnyPermission(string $steamid): bool
    {
        return $this->getPermissionsList($steamid) !== [];
    }

    public function getAllPermissions(string $steamid): array
    {
        return $this->getPermissionsList($steamid);
    }

    public function prefetchPermissions(array $steamids): void
    {
        $missing = [];
        foreach ($steamids as $steamid) {
            $steamid = trim((string) $steamid);
            if ($steamid != '' && !array_key_exists($steamid, $this->permissionsCache)) {
                $missing[$steamid] = true;
            }
        }
        if ($missing == []) {
            return;
        }

        foreach ($this->DatabaseRepository->getAccessAdminsBySteamids(array_keys($missing)) as $steamid => $permissions) {
            $this->permissionsCache[$steamid] = $permissions;
        }
        foreach (array_keys($missing) as $steamid) {
            if (!array_key_exists($steamid, $this->permissionsCache)) {
                $this->permissionsCache[$steamid] = [];
            }
        }
    }

    public function buildListMyData(string $steamid): array
    {
        $steamid64 = con_steam64((string) $steamid);
        if ($steamid64 === false || $steamid64 === '') {
            $steamid64 = (string) $steamid;
        }

        return [
            'steamid' => (string) $steamid64,
            'permissions' => $this->getAllPermissions($steamid),
            'is_site_admin' => $this->DatabaseRepository->isCurrentSiteAdmin(),
        ];
    }

    public function createAdminWeb(string $steamid64, array $permissions): array
    {
        if (isset($permissions['group']) && $permissions['group'] !== '' && $permissions['group'] !== null) {
            $groupsData = $this->FileRepository->get('groups');
            $groupId = (string) $permissions['group'];
            if (!empty($groupsData[$groupId]['permissions'])) {
                $this->DatabaseRepository->createAdminWeb($steamid64, $groupsData[$groupId]['permissions']);
            }
        } elseif (array_key_exists('flags', $permissions)) {
            $this->DatabaseRepository->createAdminWeb($steamid64, is_array($permissions['flags']) ? $permissions['flags'] : []);
        } elseif ($permissions === []) {
            return ['status' => 'success'];
        } else {
            $this->DatabaseRepository->createAdminWeb($steamid64, []);
        }

        $this->forgetPermissionsCache($steamid64);
        return ['status' => 'success'];
    }

    public function deleteAdminWeb(string $steamid64): array
    {
        $this->DatabaseRepository->deleteAdminWeb($steamid64);
        $this->forgetPermissionsCache($steamid64);
        return ['status' => 'success'];
    }
}
