<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Helpers\RconResultHelper;
use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\ExperienceRepository;

class ExperienceService
{
    private $ExperienceRepository, $AccessService, $LogsService, $ServersService, $RconService, $General, $Translate, $Modules;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->ExperienceRepository = new ExperienceRepository($Db);
        $this->AccessService = new AccessService($Db);
        $this->LogsService = new LogsService();
        $this->ServersService = new ServersService($General);
        $this->RconService = new RconService($General);
        $this->General = $General;
        $this->Translate = $Translate;
        $this->Modules = $Modules;
    }

    public function getExperienceList(array $serverIds, string $sort, int $limit, int $offset, string $search, string $mySteamid): array
    {
        if (!$this->ExperienceRepository->isConnected()) {
            return [
                'status' => 'success',
                'data' => [],
                'total' => 0,
                'my_data' => $this->AccessService->buildListMyData($mySteamid),
            ];
        }

        $tables = $this->resolveTablesByServers($serverIds);
        if ($tables === []) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperienceTableNotFound'),
            ];
        }

        [$searchSteam, $searchName] = $this->resolveSearch($search);
        $limit = max(1, $limit);
        $offset = max(0, $offset);
        $sort = $sort === 'up' ? 'up' : 'down';
        $serverNamesByStatsKey = $this->buildStatsKeyServerNames();

        $total = $this->ExperienceRepository->countPlayers($tables, $searchSteam, $searchName);
        $rows = $this->ExperienceRepository->fetchPlayers($tables, $searchSteam, $searchName, $sort, $limit, $offset);

        $data = [];
        foreach ($rows as $row) {
            $steam = (string) ($row['steam'] ?? '');
            if ($steam === '') {
                continue;
            }

            $steamid64 = con_steam64($steam);
            $statsKeyRow = (string) ($row['stats_key'] ?? '');
            $serverNames = $serverNamesByStatsKey[$statsKeyRow] ?? [];

            $data[] = [
                'id' => $statsKeyRow . '|' . $steam,
                'stats_key' => $statsKeyRow,
                'steam' => $steam,
                'steamid' => $steamid64,
                'name' => ModuleHelper::resolveDisplayName($this->General, $steamid64, $row['name'] ?? null),
                'avatar' => $this->General->getAvatar($steamid64, 3),
                'checked_avatar' => $this->General->checkAvatar($steamid64),
                'server_tooltip' => implode('<br>', array_map('action_text_clear', $serverNames)),
                'value' => (int) ($row['value'] ?? 0),
                'kills' => (int) ($row['kills'] ?? 0),
                'deaths' => (int) ($row['deaths'] ?? 0),
                'shoots' => (int) ($row['shoots'] ?? 0),
                'hits' => (int) ($row['hits'] ?? 0),
                'headshots' => (int) ($row['headshots'] ?? 0),
                'playtime' => $this->Modules->time_to_hours((int) ($row['playtime'] ?? 0)),
            ];
        }

        return [
            'status' => 'success',
            'data' => $data,
            'total' => $total,
            'my_data' => $this->AccessService->buildListMyData($mySteamid),
        ];
    }

    public function addExperience(string $steamInput, $amountInput, array $serverIds): array
    {
        if (!$this->ExperienceRepository->isConnected()) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperienceNotConnected')];
        }

        $steam = con_steam32(trim($steamInput));
        if (!$steam) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifySteamId')];
        }

        $amount = $this->normalizeAmount($amountInput);
        if ($amount === null || $amount <= 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyExperienceAmount')];
        }

        $serverIds = $this->resolveExperienceServerIds($serverIds);
        if ($serverIds === []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyServers')];
        }

        $command = 'lr_giveexp "' . $steam . '" ' . $amount;
        $steamid64 = con_steam64($steam);
        $hasFailures = RconResultHelper::sendAndLog(
            $this->RconService,
            $this->LogsService,
            'add_experience_rcon',
            $command,
            $steamid64,
            $serverIds
        );

        $this->LogsService->add([
            'type' => 'add_experience',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => ['steamid' => $steamid64],
            'details' => [
                'amount' => $amount,
                'servers' => $serverIds,
            ],
        ]);

        $message = $hasFailures
            ? ModuleHelper::phrase($this->Translate, '_at_msgExperienceAddedPartial')
            : ModuleHelper::phrase($this->Translate, '_at_msgExperienceAdded');

        return ['status' => 'success', 'message' => $message];
    }

    public function updateExperience(string $statsKey, string $steamInput, $newValueInput, $oldValueInput): array
    {
        if (!$this->ExperienceRepository->isConnected()) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperienceNotConnected')];
        }

        $steam = con_steam32(trim($steamInput));
        if (!$steam || $statsKey === '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperiencePlayerNotFound')];
        }

        $player = $this->ExperienceRepository->getPlayerByStatsKey($statsKey, $steam);
        if ($player === null) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperiencePlayerNotFound')];
        }

        $newValue = $this->normalizeAmount($newValueInput);
        if ($newValue === null || $newValue < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgInvalidExperienceAmount')];
        }

        $oldValue = $this->normalizeAmount($oldValueInput);
        if ($oldValue === null) {
            $oldValue = (int) ($player['value'] ?? 0);
        }

        $this->ExperienceRepository->updateValue($statsKey, $steam, (int) $newValue);

        $steamid64 = con_steam64($steam);
        $this->LogsService->add([
            'type' => 'update_experience',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => ['steamid' => $steamid64],
            'details' => [
                'old_value' => (int) $oldValue,
                'new_value' => (int) $newValue,
                'stats_key' => $statsKey,
            ],
        ]);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperienceUpdated')];
    }

    public function resetExperiences(array $playerList): array
    {
        if (!$this->ExperienceRepository->isConnected()) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperienceNotConnected')];
        }

        $players = $this->parsePlayerList($playerList);
        if ($players === []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperienceNotSelected')];
        }

        $steamids64 = [];
        $clearedValue = 0;
        foreach ($players as $player) {
            $row = $this->ExperienceRepository->getPlayerByStatsKey($player['stats_key'], $player['steam']);
            if ($row === null) {
                continue;
            }
            $clearedValue += (int) ($row['value'] ?? 0);
            $steamids64[] = con_steam64($player['steam']);
        }

        $affected = $this->ExperienceRepository->resetPlayers($players);
        if ($affected === 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperiencePlayerNotFound')];
        }

        $this->LogsService->add([
            'type' => 'reset_experience',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => $this->LogsService->buildTargetSteamids($steamids64),
            'details' => ['cleared_value' => $clearedValue],
        ]);

        $message = count($players) > 1
            ? ModuleHelper::phrase($this->Translate, '_at_msgExperiencesReset')
            : ModuleHelper::phrase($this->Translate, '_at_msgExperienceReset');

        return ['status' => 'success', 'message' => $message];
    }

    public function wipeStats(array $serverIds): array
    {
        if (!$this->ExperienceRepository->isConnected()) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperienceNotConnected')];
        }

        $tables = $this->resolveTablesByServers($serverIds);
        if ($tables === []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperienceTableNotFound')];
        }

        $this->ExperienceRepository->wipeStats($tables);

        $this->LogsService->add([
            'type' => 'wipe_experience_stats',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => [],
            'details' => ['servers' => $this->normalizeServerIds($serverIds)],
        ]);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperienceStatsWiped')];
    }

    public function deleteEmptyPlayers(array $serverIds): array
    {
        if (!$this->ExperienceRepository->isConnected()) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperienceNotConnected')];
        }

        $tables = $this->resolveTablesByServers($serverIds);
        if ($tables === []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgExperienceTableNotFound')];
        }

        $this->ExperienceRepository->deleteEmptyPlayersInTables($tables);

        $this->LogsService->add([
            'type' => 'delete_empty_experience_players',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => [],
            'details' => ['servers' => $this->normalizeServerIds($serverIds)],
        ]);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgEmptyExperiencePlayersDeleted')];
    }

    private function normalizeServerIds(array $serverIds): array
    {
        $serverIds = array_values(array_unique(array_map('intval', $serverIds)));

        return $serverIds === [] || in_array(-1, $serverIds, true) ? [-1] : $serverIds;
    }

    private function resolveTablesByServers(array $serverIds): array
    {
        $serverIds = array_values(array_unique(array_map('intval', $serverIds)));

        if ($serverIds === [] || in_array(-1, $serverIds, true)) {
            return $this->ExperienceRepository->resolveTables('-1');
        }

        $tables = [];
        foreach ($this->ServersService->returnServersByIds($serverIds) as $server) {
            $statsKey = (string) ($server['server_stats'] ?? '');
            if ($statsKey === '') {
                continue;
            }

            foreach ($this->ExperienceRepository->resolveTables($statsKey) as $entry) {
                $key = $this->ExperienceRepository->buildStatsKey($entry);
                $tables[$key] = $entry;
            }
        }

        return array_values($tables);
    }

    private function buildStatsKeyServerNames(): array
    {
        $map = [];
        foreach ($this->General->server_list as $server) {
            $statsKey = (string) ($server['server_stats'] ?? '');
            $name = (string) ($server['name_custom'] ?? '');
            if ($statsKey === '' || $name === '') {
                continue;
            }
            $map[$statsKey][] = $name;
        }

        foreach ($map as &$names) {
            $names = array_values(array_unique($names));
        }
        unset($names);

        return $map;
    }

    private function resolveExperienceServerIds(array $serverIds): array
    {
        $serverIds = array_values(array_unique(array_map('intval', $serverIds)));

        if (in_array(-1, $serverIds, true)) {
            return array_column($this->ServersService->getServers(), 'id');
        }

        return $serverIds;
    }

    private function parsePlayerList(array $playerList): array
    {
        $out = [];
        foreach ($playerList as $item) {
            $item = trim((string) $item);
            if ($item === '') {
                continue;
            }

            $parts = explode('|', $item, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $steam = con_steam32($parts[1]);
            if (!$steam) {
                continue;
            }

            $out[] = [
                'stats_key' => $parts[0],
                'steam' => $steam,
            ];
        }

        return $out;
    }

    private function resolveSearch(string $search): array
    {
        $search = trim($search);
        if ($search === '') {
            return ['', ''];
        }

        $steam32 = ModuleHelper::toSteam32($search);
        if (preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $steam32)) {
            return [$steam32, ''];
        }

        $steam64 = ModuleHelper::toSteam64($search);
        if ($steam64 !== $search && preg_match('/^7656119\d{10}$/', $steam64)) {
            $authFrom64 = ModuleHelper::toSteam32($steam64);
            if (preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $authFrom64)) {
                return [$authFrom64, ''];
            }
        }

        return ['', $search];
    }

    private function normalizeAmount($value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}