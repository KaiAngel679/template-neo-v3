<?php

namespace app\modules\module_page_bonuses\ext\Services;

use app\modules\module_page_bonuses\ext\Repositories\DatabaseRepository;
use app\modules\module_page_bonuses\ext\Services\BalanceService;

class VkService
{
    protected $dr, $bs, $Translate;

    public function __construct($Db, $Translate)
    {
        $this->dr = new DatabaseRepository($Db);
        $this->bs = new BalanceService($Db);
        $this->Translate = $Translate;
    }

    public function authVk(): array
    {
        if (!$this->dr->checkReward($_SESSION['steamid64'])['vk_reward']) {
            $roll = random_int(1, 100);
            if ($roll <= 30) {
                $this->dr->createdUserReward($_SESSION['steamid64']);
                $this->dr->updateUserRewardVk($_SESSION['steamid64']);
                $this->bs->addBalance($_SESSION['steamid32'], 'vk', $this->Translate->get_translate_module_phrase('module_page_bonuses', '_issuedForVkSubscription'));
                return ['status' => 'success', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_bonusCredited')];
            }
        } else {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_youAlreadyReceivedReward')];
        }

        return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_youNotSubscribed')];
    }
}
