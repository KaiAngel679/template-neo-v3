<?php

namespace app\modules\module_page_cards\ext\Services;

use app\modules\module_page_cards\ext\Repositories\CacheRepository;
use app\modules\module_page_cards\ext\Repositories\CardsRepository;
use app\modules\module_page_cards\ext\Repositories\DatabaseRepository;
use app\modules\module_page_cards\ext\Repositories\RconRepository;

class CardsService
{
    private const BUY_DATE = '2000-01-01';

    protected $Translate, $dr, $General, $cr, $ccr, $rr;

    public function __construct($Db, $Translate, $General)
    {
        $this->dr = new DatabaseRepository($Db);
        $this->cr = new CacheRepository();
        $this->ccr = new CardsRepository();
        $this->rr = new RconRepository($General);
        $this->Translate = $Translate;
        $this->General = $General;
    }

    public function checkOpen(string $steamid): array
    {
        $progress = $this->dr->checkOpen($steamid);
        if (!$progress) {
            $this->dr->newUserProgress($steamid);
            return ['status' => 'success', 'data' => ['streak' => 0, 'price' => $this->cr->getCache('settings')['price'] . $this->General->currency, 'svg' => $this->cr->getCache('settings')['svg']]];
        }

        if ($progress['last_open_date'] != self::BUY_DATE && $progress['last_open_date'] != date("Y-m-d") && $progress['last_open_date'] != date("Y-m-d", strtotime("-1 day"))) {
            $progress['streak'] = 0;
            $this->dr->updateStreak($steamid, 0);
        }

        if ($progress['streak'] >= 7) {
            $progress['streak'] = 0;
            $this->dr->updateStreak($steamid, 0);
        }

        if ($progress['last_open_date'] == date("Y-m-d")) {
            return ["status" => 'error', 'data' => ['streak' => $progress['streak'], 'price' => $this->cr->getCache('settings')['price'] . $this->General->currency, 'svg' => $this->cr->getCache('settings')['svg']]];
        }

        return ['status' => 'success', 'data' => ['streak' => $progress['streak'], 'price' => $this->cr->getCache('settings')['price'] . $this->General->currency, 'svg' => $this->cr->getCache('settings')['svg']]];
    }

    public function openCard(string $steamid): array
    {
        $progress = $this->dr->checkOpen($steamid);
        if ($progress['last_open_date'] != self::BUY_DATE && $progress['last_open_date'] == date("Y-m-d")) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_cardAlready')];
        } else {
            $progress['streak']++;
            $this->dr->updateStreak($steamid, $progress['streak']);
        }

        $rewards = $this->dr->getRewards();
        if (!$rewards) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_noRewards')];
        }

        $filtered = $this->ccr->filteredRewards($progress['streak'], $rewards);
        if (!$filtered) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_noRewards')];
        }

        $reward = $this->ccr->random($filtered);

        $buy = $progress['last_open_date'] == self::BUY_DATE ? 1 : 0;
        $this->dr->addHistoryRecord($steamid, $reward['id'], $progress['streak'], $buy);
        $this->dr->updateUserProgress($steamid, $progress['streak']);

        switch ($reward['type']) {
            case 'money':
                return $this->giveMoneyReward($steamid, $reward['count'], $progress['streak']);
            case 'credits':
                return $this->giveCreditsReward($steamid, $reward['count'], $progress['streak']);
            default:
                return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_unknownTypeofPrize')];
        }
    }

    public function buyCard(string $steamid): array
    {
        $steamid32 = con_steam64to32($steamid);
        $isset = $this->dr->checkLKTable($steamid32);
        $this->ensureUserExists($steamid32);
        if ($isset['cash'] < $this->cr->getCache('settings')['price']) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_noMoney')];
        }
        $this->dr->updateUserProgressBuy($steamid);
        $this->dr->updateBalanceUser(-$this->cr->getCache('settings')['price'], $steamid32);
        $this->dr->logBalanceUser(-$this->cr->getCache('settings')['price'], $steamid32, $this->Translate->get_translate_module_phrase('module_page_cards', '_debitOpen'));
        return ['status' => 'success'];
    }

    private function giveMoneyReward(string $steamid, int $count, int $streak): array
    {
        $steamid32 = con_steam64to32($steamid);
        $this->ensureUserExists($steamid32);
        $this->dr->updateBalanceUser($count, $steamid32);
        $this->dr->logBalanceUser($count, $steamid32, $this->Translate->get_translate_module_phrase('module_page_cards', '_accrualOpen') . $streak);
        return ['status' => 'success', 'data' => ['reward' => $count . ' ' . $this->General->currency, 'description' => $this->Translate->get_translate_module_phrase('module_page_cards', '_toBalance'), 'streak' => $streak, 'price' => $this->cr->getCache('settings')['price'] . $this->General->currency]];
    }

    private function giveCreditsReward(string $steamid, int $count, int $streak): array
    {
        $currency = $this->cr->getCache('settings')['currency'];
        $this->rr->sendCommand("mm_shop_give_currency $steamid $currency $count", $this->cr->getCache('settings')['ids']);
        return ['status' => 'success', 'data' => ['reward' => $count . ' ' . $this->Translate->get_translate_module_phrase('module_page_cards', '_ofCredits'), 'description' => $this->Translate->get_translate_module_phrase('module_page_cards', '_onTheServer'), 'streak' => $streak, 'price' => $this->cr->getCache('settings')['price'] . $this->General->currency]];
    }

    private function ensureUserExists(string $steamid32): void
    {
        if (empty($this->dr->checkLKTable($steamid32))) {
            $this->dr->createdUserLKTable($steamid32);
        }
    }

    public function checkOpenDb(string $steamid): array
    {
        return $this->dr->checkOpen($steamid);
    }
}
