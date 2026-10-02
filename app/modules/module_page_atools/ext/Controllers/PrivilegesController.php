<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\PrivilegesService;

class PrivilegesController
{
    private $service;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->service = new PrivilegesService($Db, $General, $Translate, $Modules);
    }

    public function createPrivilege(string $steamid, string $group, $expire, array $servers): array
    {
        return $this->service->createPrivilege($steamid, $group, $expire, $servers);
    }

    public function getPrivilegesList(array $servers, string $group, int $limit, int $offset, string $search, string $expireFilter = 'all'): array
    {
        return $this->service->getPrivilegesList($servers, $group, $limit, $offset, $search, $expireFilter);
    }

    public function deletePrivileges(array $privilegeIds): array
    {
        return $this->service->deletePrivileges($privilegeIds);
    }

    public function updatePrivilege(string $privilegeId, string $group, $expire, array $servers): array
    {
        return $this->service->updatePrivilege($privilegeId, $group, $expire, $servers);
    }
}
