<?php

namespace app\modules\module_page_bonuses\ext\Services;
use app\modules\module_page_bonuses\ext\Repositories\DatabaseRepository;
use app\modules\module_page_bonuses\ext\Repositories\CacheRepository;

class BalanceService {
    protected $dr, $cr;

    public function __construct($Db) {
        $this->dr = new DatabaseRepository($Db);
        $this->cr = new CacheRepository;
    }

    public function addBalance(string $steam, string $type, string $status): void
    {
        $isset = $this->dr->checkLKTable($steam);
        if (empty($isset)) {
            $this->dr->createdUserLKTable($steam);
        }
        $this->dr->updateBalanceUser($this->cr->getCache('settings')['money_' . $type], $steam);
        $this->dr->logBalanceUser($this->cr->getCache('settings')['money_' . $type], $steam, $status);
    }
}
