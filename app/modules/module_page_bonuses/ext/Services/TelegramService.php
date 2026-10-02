<?php

namespace app\modules\module_page_bonuses\ext\Services;

use app\modules\module_page_bonuses\ext\Repositories\TelegramRepository;
use app\modules\module_page_bonuses\ext\Repositories\DatabaseRepository;
use app\modules\module_page_bonuses\ext\Services\BalanceService;

class TelegramService
{
    protected $tr, $dr, $bs, $Translate;

    public function __construct($Db, $Translate)
    {
        $this->tr = new TelegramRepository();
        $this->dr = new DatabaseRepository($Db);
        $this->bs = new BalanceService($Db);
        $this->Translate = $Translate;
    }

    public function authTelegram(string $hash): array
    {
        $result = $this->tr->hashDecode($hash);
        if ($result['status'] != 'success') {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_failedGetHash')];
        }

        $auth = $this->tr->authTelegram($result['data']);
        if ($auth['status'] != 'success') {
            $code = isset($auth['message']) ? $auth['message'] : '';
            if ($code === 'noBotToken') {
                $msg = $this->Translate->get_translate_module_phrase('module_page_bonuses', '_tgBotTokenMissing');
            } elseif ($code === 'authExpired') {
                $msg = $this->Translate->get_translate_module_phrase('module_page_bonuses', '_tgAuthExpired');
            } else {
                $msg = $this->Translate->get_translate_module_phrase('module_page_bonuses', '_hashMismatch');
            }

            return ['status' => 'error', 'message' => $msg];
        }

        $sub = $this->tr->checkSubscriptionTelegram($result['data']);
        if ($sub['status'] != 'success') {
            $code = isset($sub['message']) ? $sub['message'] : '';
            if ($code === 'badConfig') {
                $msg = $this->Translate->get_translate_module_phrase('module_page_bonuses', '_tgChannelOrBotNotConfigured');
            } elseif ($code === 'notMember') {
                $msg = $this->Translate->get_translate_module_phrase('module_page_bonuses', '_notSubscribed');
            } else {
                $msg = $this->Translate->get_translate_module_phrase('module_page_bonuses', '_notSubscribed');
            }

            return ['status' => 'error', 'message' => $msg];
        }

        $rewardRow = $this->dr->checkReward($_SESSION['steamid64']);
        if (!empty($rewardRow['tg_reward'])) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_youAlreadyReceivedReward')];
        }

        $this->dr->createdUserReward($_SESSION['steamid64']);
        $this->dr->updateUserRewardTg($_SESSION['steamid64']);
        $this->bs->addBalance($_SESSION['steamid32'], 'tg', $this->Translate->get_translate_module_phrase('module_page_bonuses', '_issuedForTelegramSubscription'));

        return ['status' => 'success', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_bonusCredited')];
    }
}
