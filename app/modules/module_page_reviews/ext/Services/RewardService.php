<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Repositories\RewardRepository;

class RewardService
{
    private $rewards;
    private $settings;
    private $Translate;

    public function __construct(RewardRepository $rewards, SettingsService $settings, object $Translate)
    {
        $this->rewards = $rewards;
        $this->settings = $settings;
        $this->Translate = $Translate;
    }

    public function hasReceived(string $steamid64): bool
    {
        $steamid64 = ModuleHelper::toSteam64($steamid64);
        if ($steamid64 === '') {
            return false;
        }

        return $this->rewards->hasReceived($steamid64);
    }

    public function tryRewardFirstReview(string $steamid64): array
    {
        $steamid64 = ModuleHelper::toSteam64($steamid64);
        if ($steamid64 === '' || !preg_match('/^7656119\d{10}$/', $steamid64)) {
            return ['given' => false, 'reason' => 'bad_steam'];
        }

        $cfg = $this->settings->get();
        if (empty($cfg['reward_enabled']) || (int) $cfg['reward_amount'] <= 0) {
            return ['given' => false, 'reason' => 'disabled'];
        }

        if (!$this->rewards->isLkConnected()) {
            return ['given' => false, 'reason' => 'no_lk'];
        }

        if ($this->rewards->hasReceived($steamid64)) {
            return ['given' => false, 'reason' => 'already'];
        }

        $amount = (int) $cfg['reward_amount'];
        $steam32 = ModuleHelper::toSteam32($steamid64);
        if ($steam32 === '' || $steam32 === '0') {
            return ['given' => false, 'reason' => 'bad_steam'];
        }

        $promo = method_exists($this->Translate, 'get_translate_module_phrase')
            ? (string) $this->Translate->get_translate_module_phrase('module_page_reviews', '_rv_rewardPayPromo')
            : 'Review reward';

        if (!$this->rewards->tryMarkReceived($steamid64, $amount)) {
            return ['given' => false, 'reason' => 'already'];
        }

        try {
            $this->rewards->ensureLkUser($steam32);
            $this->rewards->addCash($steam32, $amount);
            $this->rewards->logPay($steam32, $amount, $promo);
        } catch (\Throwable $e) {
            return ['given' => false, 'reason' => 'error'];
        }

        return [
            'given' => true,
            'amount' => $amount,
        ];
    }
}
