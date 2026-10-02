<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Formatters\ChecksVerdictFormatter;
use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\AdminRepository;

class CheckService
{
    private $AdminRepository, $ServersService, $AccessService, $LogsService, $General, $Translate;

    public function __construct(object $Db, object $General, object $Translate)
    {
        $this->AdminRepository = new AdminRepository($Db);
        $this->ServersService = new ServersService($General);
        $this->AccessService = new AccessService($Db);
        $this->LogsService = new LogsService();
        $this->General = $General;
        $this->Translate = $Translate;
    }

    public function getChecksList(int $admin, array $servers, string $verdict, string $dateFrom, string $dateTo, int $limit, int $offset, string $mySteamid, string $search): array
    {
        if (!$this->AdminRepository->isChecksBackendAvailable()) {
            return [
                'status' => 'success',
                'data' => [],
                'total' => 0,
                'my_data' => $this->AccessService->buildListMyData($mySteamid),
            ];
        }

        $filters = [
            'admin' => $admin,
            'admin_steamid' => $this->AdminRepository->resolveCheckAdminSteamid($admin),
            'servers' => $this->ServersService->buildServersBindingForList($servers),
            'verdict' => $verdict,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'limit' => $limit,
            'offset' => $offset,
            'search' => $search,
        ];

        $rows = $this->AdminRepository->getChecksList($filters);
        $data = [];
        $isIks = $this->AdminRepository->isChecksIksBackend();

        foreach ($rows as $row) {
            $data[] = $this->formatCheckRow($row, $isIks);
        }

        return [
            'status' => 'success',
            'data' => $data,
            'total' => $this->AdminRepository->getChecksCount($filters),
            'my_data' => $this->AccessService->buildListMyData($mySteamid),
        ];
    }

    public function deleteChecks(array $checkIds, string $issuerSteamid64): array
    {
        $ids = array_values(array_unique(array_map('intval', $checkIds)));
        if ($ids == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgChecksNotSelected')];
        }

        if (!$this->AdminRepository->isChecksBackendAvailable()) {
            $this->LogsService->addCriticalError('delete_checks', 'Checks backend not connected', ['check_ids' => $ids]);

            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgChecksBackendMissing')];
        }

        $rows = $this->AdminRepository->getChecksByIds($ids);
        $isIks = $this->AdminRepository->isChecksIksBackend();

        if ($error = $this->validateNotOwnChecks($rows, $issuerSteamid64)) {
            return $error;
        }

        $logChecks = [];
        $playerSteamids64 = [];

        foreach ($rows as $row) {
            $formatted = $this->formatCheckRow($row, $isIks);
            $logChecks[] = [
                'player_steamid' => con_steam64($formatted['player_steamid']),
                'player_name' => $formatted['player_name'],
                'admin_steamid' => con_steam64($formatted['admin_steamid']),
                'admin_name' => $formatted['admin_name'],
            ];

            $playerSteamid64 = con_steam64($formatted['player_steamid']);
            $playerSteamids64[$playerSteamid64] = true;
        }

        $this->AdminRepository->deleteChecksByIds($ids);

        $this->LogsService->add([
            'type' => 'delete_checks',
            'admin' => ['steamid' => $issuerSteamid64],
            'target' => $this->LogsService->buildTargetSteamids(array_keys($playerSteamids64)),
            'details' => [
                'checks' => $logChecks,
            ],
        ]);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgChecksDeleted')];
    }

    private function validateNotOwnChecks(array $rows, string $issuerSteamid64): ?array
    {
        $mySteamid = con_steam64($issuerSteamid64);

        foreach ($rows as $row) {
            $adminSteamid = con_steam64($row['admin_steamid'] ?? '');
            if ($adminSteamid == $mySteamid) {
                return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgCannotDeleteOwnCheck')];
            }
        }

        return null;
    }

    private function formatCheckRow(array $row, bool $isIks): array
    {
        $playerSteamid = $row['player_steamid'];
        $playerName = ModuleHelper::resolveDisplayName($this->General, $playerSteamid, $row['player_name'] ?? null);

        $adminSteamid = $row['admin_steamid'];
        $adminName = ModuleHelper::resolveDisplayName($this->General, $adminSteamid, $row['admin_name'] ?? null);

        if ($isIks) {
            $verdict = ChecksVerdictFormatter::label($row, true, $this->Translate);
            $contact = !empty($row['discord'])
                ? $row['discord']
                : $this->Translate->get_translate_phrase('_absent');
        } else {
            $verdict = ChecksVerdictFormatter::label($row, false, $this->Translate);
            $contact = !empty($row['suspect_discord'])
                ? $row['suspect_discord']
                : $this->Translate->get_translate_phrase('_absent');
        }

        $dateStart = !empty($row['datestart'])
            ? date('d.m.Y, H:i:s', (int) $row['datestart'])
            : $this->Translate->get_translate_phrase('_absent');
        $dateEnd = !empty($row['date_end'])
            ? date('d.m.Y, H:i:s', (int) $row['date_end'])
            : $this->Translate->get_translate_phrase('_absent');

        return [
            'id' => (int) ($row['id']),
            'player_steamid' => $playerSteamid,
            'player_name' => $playerName,
            'player_avatar' => $this->General->getAvatar($playerSteamid, 3),
            'player_checked_avatar' => $this->General->checkAvatar($playerSteamid),
            'admin_steamid' => $adminSteamid,
            'admin_name' => $adminName,
            'admin_avatar' => $this->General->getAvatar($adminSteamid, 3),
            'admin_checked_avatar' => $this->General->checkAvatar($adminSteamid),
            'datestart' => $dateStart,
            'date_end' => $dateEnd,
            'verdict' => $verdict,
            'contact' => $contact,
        ];
    }
}
