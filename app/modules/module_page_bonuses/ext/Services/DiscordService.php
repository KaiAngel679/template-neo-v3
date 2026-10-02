<?php

namespace app\modules\module_page_bonuses\ext\Services;
use app\modules\module_page_bonuses\ext\Repositories\DiscordRepository;
use app\modules\module_page_bonuses\ext\Repositories\DatabaseRepository;
use app\modules\module_page_bonuses\ext\Services\BalanceService;

class DiscordService
{
    protected $drr, $dr, $bs, $Translate;

    public function __construct($Db, $General, $Translate) {
        $this->drr = new DiscordRepository($General);
        $this->dr = new DatabaseRepository($Db);
        $this->bs = new BalanceService($Db);
        $this->Translate = $Translate;
    }

    public function authDiscord(string $code): array
    {
        $userData = $this->drr->getDiscordUser($code);
        if ($userData['status'] == 'success') {
            $check = $this->drr->checkSubscriptionDiscord($userData['data']);
            if ($check['status'] == 'success') {
                if (!$this->dr->checkReward($_SESSION['steamid64'])['ds_reward']) {
                    $this->dr->createdUserReward($_SESSION['steamid64']);
                    $this->dr->updateUserRewardDs($_SESSION['steamid64']);
                    $this->bs->addBalance($_SESSION['steamid32'], 'ds', $this->Translate->get_translate_module_phrase('module_page_bonuses', '_issuedForDiscordSubscription'));
                    return ['status' => 'success', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_bonusCredited')];
                } else {
                    return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_youAlreadyReceivedReward')];
                }
            } else {
                switch($check['message']) {
                    case 'noAccess':
                        return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_failedGetAccessToken')];
                    default:
                        return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_notSubscribed')];
                }
            }
        } else {
            switch($userData['message']) {
                case 'noCode':
                    return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_failedGetCode')];
                case 'noAccess':
                    return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_failedGetAccessToken')];
                case 'noId':
                    return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_failedGetDiscordId')];
                default:
                    return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_bonuses', '_authorizationError')];
            }
        }
    }
}
