<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\FinanceService;

class FinanceController
{
    private $service;

    public function __construct(object $Db, object $General, object $Translate)
    {
        $this->service = new FinanceService($Db, $General, $Translate);
    }

    public function getFinancesList(string $sort, int $limit, int $offset, string $search, string $mySteamid, string $balanceFilter = 'all'): array
    {
        return $this->service->getFinancesList($sort, $limit, $offset, $search, $mySteamid, $balanceFilter);
    }

    public function addBalance(string $steamid, $amount): array
    {
        return $this->service->addBalance($steamid, $amount);
    }

    public function updateBalance(string $auth, $newCash, $oldCash): array
    {
        return $this->service->updateBalance($auth, $newCash, $oldCash);
    }

    public function resetBalances(array $authList): array
    {
        return $this->service->resetBalances($authList);
    }

    public function deletePlayersWithoutDonation(): array
    {
        return $this->service->deletePlayersWithoutDonation();
    }
}
