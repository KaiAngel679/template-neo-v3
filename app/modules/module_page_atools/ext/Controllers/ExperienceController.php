<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\ExperienceService;

class ExperienceController
{
    private $service;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->service = new ExperienceService($Db, $General, $Translate, $Modules);
    }

    public function getExperienceList(array $serverIds, string $sort, int $limit, int $offset, string $search, string $mySteamid): array
    {
        return $this->service->getExperienceList($serverIds, $sort, $limit, $offset, $search, $mySteamid);
    }

    public function addExperience(string $steamid, $amount, array $serverIds): array
    {
        return $this->service->addExperience($steamid, $amount, $serverIds);
    }

    public function updateExperience(string $statsKey, string $steam, $newValue, $oldValue): array
    {
        return $this->service->updateExperience($statsKey, $steam, $newValue, $oldValue);
    }

    public function resetExperiences(array $playerList): array
    {
        return $this->service->resetExperiences($playerList);
    }

    public function wipeStats(array $serverIds): array
    {
        return $this->service->wipeStats($serverIds);
    }

    public function deleteEmptyPlayers(array $serverIds): array
    {
        return $this->service->deleteEmptyPlayers($serverIds);
    }
}
