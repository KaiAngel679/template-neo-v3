<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\PunishmentService;

class PunishmentController
{
    private $service;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->service = new PunishmentService($Db, $General, $Translate, $Modules);
    }

    public function createPunishment(string $steamid, ?string $ip, int $type, string $reason, int $expire, array $servers): array
    {
        return $this->service->createPunishment($steamid, $ip, $type, $reason, $expire, $servers);
    }

    public function getPunishmentsList(string $type, string $punishType, int $admin, array $servers, string $dateFrom, string $dateTo, string $expireFilter, int $limit, int $offset, string $search): array
    {
        return $this->service->getPunishmentsList($type, $punishType, $admin, $servers, $dateFrom, $dateTo, $expireFilter, $limit, $offset, $_SESSION['steamid64'], $search);
    }

    public function removePunishments(string $type, array $ids, string $listPunishType = 'ban'): array
    {
        return $this->service->removePunishments($type, $ids, $_SESSION['steamid64'], $listPunishType);
    }

    public function deletePunishments(string $type, array $ids): array
    {
        return $this->service->deletePunishments($type, $ids, $_SESSION['steamid64']);
    }

    public function updatePunishment(string $type, string $listPunishType, int $punishId, ?string $ip, int $punishType, string $reason, int $expire, array $servers): array
    {
        return $this->service->updatePunishment($type, $listPunishType, $punishId, $ip, $punishType, $reason, $expire, $servers, $_SESSION['steamid64']);
    }
}
