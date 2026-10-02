<?php

namespace app\modules\module_page_cards\ext\Services;

use app\modules\module_page_cards\ext\Repositories\CacheRepository;
use app\modules\module_page_cards\ext\Repositories\DatabaseRepository;

class CardsSettingsService
{
    protected $cr, $dr, $Translate;

    public function __construct($Db, $Translate)
    {
        $this->cr = new CacheRepository();
        $this->dr = new DatabaseRepository($Db);
        $this->Translate = $Translate;
    }

    public function saveGeneralSettings(int $price, string $currency, array $ids, string $svg): array
    {
        if (!$svg) return ['status' => 'error', 'message' => 'Вы не указали SVG'];
        $put = $this->cr->putCache(['price' => $price, 'currency' => $currency, 'ids' => $ids, 'svg' => $svg], 'settings');
        if ($put['status'] === 'error') {
            switch ($put['message']) {
                case 'not':
                    return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_cacheFileNotFound')];
                case 'perm':
                    return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_noPermissionToWrite')];
                case 'fake':
                    return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_errorWritingCache')];
                default:
                    return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_unknownErrorSavingParameters')];
            }
        }

        return ['status' => 'success', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_settingsSavedSuccessfully')];
    }

    public function getCacheSettings(): array
    {
        return $this->cr->getCache('settings');
    }

    public function createTables(): void
    {
        $this->dr->createTables();
    }

    public function savePrizesSettings(array $prizes): array
    {
        foreach ($prizes as $prize) {
            $type = $prize['type'] ?? null;
            $count = (int)($prize['count'] ?? 0);
            $rare = (int)($prize['rare'] ?? 0);
            $chance = (int)($prize['chance'] ?? 0);

            if (!$type || !in_array($type, ['money', 'credits'])) {
                return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_incorrectTypeaward')];
            }

            if ($count <= 0) {
                return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_countOfMoneyNeededNotMoreZero')];
            }

            if ($chance < 0 || $chance > 100) {
                return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_chanceNeededFrom1To100')];
            }

            $this->dr->addReward($type, $count, $rare, $chance);
        }

        return ['status' => 'success'];
    }

    public function getRewards(): array
    {
        return $this->dr->getRewards();
    }

    public function deleteReward(int $id): array
    {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_incorrectIDaward')];
        }
        $this->dr->deleteReward($id);
        return ['status' => 'success', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_awardSuccesfullyDeleted')];
    }

    public function updateReward(int $id, string $type, int $count, int $rare, int $chance): array
    {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_incorrectIDaward')];
        }

        if (!$type || !in_array($type, ['money', 'credits'])) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_incorrectTypeaward')];
        }

        if ($count <= 0) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_countNeededMoreZero')];
        }

        if ($chance < 0 || $chance > 100) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_chanceNeededFrom1To100')];
        }

        $this->dr->updateReward($id, $type, $count, $rare, $chance);
        return ['status' => 'success'];
    }

    public function getReward(int $id): array
    {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_incorrectIDaward')];
        }
        $reward = $this->dr->getReward($id);
        if (empty($reward)) {
            return ['status' => 'error', 'message' => $this->Translate->get_translate_module_phrase('module_page_cards', '_awardNotFound')];
        }
        return ['status' => 'success', 'data' => $reward];
    }
}
