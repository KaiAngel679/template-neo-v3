<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Helpers\RconResultHelper;
use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\VipRepository;

class PrivilegesService
{
    private $VipRepository, $ServersService, $RconService, $LogsService, $RendersService, $AccessService, $General, $Translate, $Db, $Modules;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->VipRepository = new VipRepository($Db);
        $this->ServersService = new ServersService($General);
        $this->RconService = new RconService($General);
        $this->LogsService = new LogsService();
        $this->RendersService = new RendersService($Db, $General, $Translate);
        $this->AccessService = new AccessService($Db);
        $this->Db = $Db;
        $this->General = $General;
        $this->Translate = $Translate;
        $this->Modules = $Modules;
    }

    public function getPrivilegesList(array $servers, string $group, int $limit, int $offset, string $search, string $expireFilter = 'all'): array
    {
        if (!$this->VipRepository->isConnected()) {
            return ['status' => 'success', 'data' => [], 'total' => 0, 'my_data' => $this->AccessService->buildListMyData($_SESSION['steamid'])];
        }

        $targets = $this->resolveVipTargets($servers);
        if ($targets == []) {
            return ['status' => 'success', 'data' => [], 'total' => 0, 'my_data' => $this->AccessService->buildListMyData($_SESSION['steamid'])];
        }

        $groupIni = trim($group) != '' ? trim($group) : '-1';
        $expireFilter = in_array($expireFilter, ['all', 'forever', 'temporary', 'expired'], true) ? $expireFilter : 'all';
        [$accountId, $searchName] = $this->resolveVipSearch($search);
        $excludeGroup = $this->RendersService->hiddenVipTestGroupIni();
        $groupNames = $this->buildVipGroupNamesMap();
        $rows = [];

        foreach ($targets as $target) {
            $serverVip = (string) $target['server_vip'];
            $sid = (int) $target['sid'];
            $serverTooltip = $this->resolveVipServerTooltip($serverVip, $sid);

            foreach (
                $this->VipRepository->fetchUsers(
                    $serverVip,
                    $sid,
                    $groupIni,
                    $accountId,
                    $searchName,
                    $excludeGroup,
                    $expireFilter
                ) as $row
            ) {
                $account = (int) ($row['account_id'] ?? 0);
                if ($account <= 0) {
                    continue;
                }

                $steamid64 = con_steam3to64_int($account);
                $expires = (int) ($row['expires'] ?? 0);
                $groupKey = (string) ($row['group'] ?? '');
                $remainingText = $this->formatVipExpireDisplay($expires);
                $rows[] = [
                    'id' => $account . ':' . $sid,
                    'steamid' => $steamid64,
                    'name' => ModuleHelper::resolveDisplayName($this->General, $steamid64, $row['name'] ?? null),
                    'avatar' => $this->General->getAvatar($steamid64, 3),
                    'checked_avatar' => $this->General->checkAvatar($steamid64),
                    'group_name' => $groupNames[$groupKey] ?? $groupKey,
                    'group' => $groupKey,
                    'sid' => $sid,
                    'server_panel_ids' => $this->resolveVipPanelIds($serverVip, $sid),
                    'server_tooltip' => $serverTooltip,
                    'expires_text' => $expires == 0 ? $this->Translate->get_translate_phrase('_Forever') : date('d.m.Y, H:i', $expires),
                    'remaining_text' => $remainingText,
                    'expires' => $expires,
                    'lastvisit' => (int) ($row['lastvisit'] ?? 0),
                ];
            }
        }

        usort($rows, static function (array $a, array $b): int {
            $aExp = $a['expires'] == 0 ? PHP_INT_MAX : $a['expires'];
            $bExp = $b['expires'] == 0 ? PHP_INT_MAX : $b['expires'];
            if ($aExp != $bExp) {
                return $bExp <=> $aExp;
            }

            return $b['lastvisit'] <=> $a['lastvisit'];
        });

        $total = count($rows);
        $limit = max(1, $limit);
        $offset = max(0, $offset);

        return [
            'status' => 'success',
            'data' => array_slice($rows, $offset, $limit),
            'total' => $total,
            'my_data' => $this->AccessService->buildListMyData($_SESSION['steamid']),
        ];
    }

    public function deletePrivileges(array $privilegeIds): array
    {
        $ids = $this->normalizePrivilegeIds($privilegeIds);
        if ($ids == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPrivilegesNotSelected')];
        }

        $deleted = 0;
        $deletedDetails = [];
        $steamidsForRcon = [];
        $panelIdsForRcon = [];

        foreach ($ids as $id) {
            $target = $this->resolveVipTargetByPrivilegeId($id);
            if ($target == null) {
                continue;
            }

            if (!$this->VipRepository->privilegeExists($target['server_vip'], $target['sid'], $target['account_id'])) {
                continue;
            }

            $this->VipRepository->deletePrivilege($target['server_vip'], $target['sid'], $target['account_id']);
            $deleted++;
            $deletedDetails[] = [
                'privilege_id' => $id,
                'steamid' => $target['steamid64'],
                'sid' => $target['sid'],
            ];
            $steamidsForRcon[$target['steamid64']] = true;
            $panelIdsForRcon = array_merge($panelIdsForRcon, $target['panel_ids']);
        }

        if ($deleted == 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPrivilegesNotFound')];
        }

        $panelIdsForRcon = array_values(array_unique(array_map('intval', $panelIdsForRcon)));
        $partial = $this->reloadVipRcon('delete_privilege', array_keys($steamidsForRcon), $panelIdsForRcon);

        $steamids64 = array_values(array_unique(array_filter(array_map(
            static fn(array $row): string => (string) ($row['steamid'] ?? ''),
            $deletedDetails
        ))));

        $this->LogsService->add([
            'type' => 'delete_privilege',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => $this->LogsService->buildTargetSteamids($steamids64),
            'details' => [
                'servers' => $panelIdsForRcon,
            ],
        ]);

        return $partial
            ? ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgVipDeletedPartial')]
            : ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgVipDeleted')];
    }

    public function updatePrivilege(string $privilegeId, string $group, $expire, array $panelServerIds): array
    {
        if (!$this->VipRepository->isConnected()) {
            $this->LogsService->addCriticalError('update_privilege', 'VIP backend not connected');
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgVipDbNotConnected')];
        }

        if ($group == '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyVipGroup')];
        }
        if ($expire == '' || $expire == null || $expire < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyExpire')];
        }
        if ($panelServerIds == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyServers')];
        }

        $original = $this->resolveVipTargetByPrivilegeId($privilegeId);
        if ($original == null) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPrivilegeNotFound')];
        }

        if (!$this->VipRepository->privilegeExists($original['server_vip'], $original['sid'], $original['account_id'])) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPrivilegeNotFound')];
        }

        $newTargets = $this->resolveVipTargets($panelServerIds);
        if ($newTargets == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgServersNotLinkedVip')];
        }

        $expiresAt = $expire == 0 ? 0 : time() + (int) $expire;
        $accountId = $original['account_id'];
        $steamid64 = $original['steamid64'];
        $oldSid = $original['sid'];
        $playerName = $this->General->checkName($steamid64);
        $newSids = [];
        $panelIdsForRcon = [];
        $updatedSids = [];

        foreach ($newTargets as $target) {
            $sid = (int) $target['sid'];
            $newSids[] = $sid;
            $panelIdsForRcon = array_merge($panelIdsForRcon, $target['panel_ids']);

            if ($this->VipRepository->privilegeExists($target['server_vip'], $sid, $accountId)) {
                $this->VipRepository->updatePrivilege(
                    $target['server_vip'],
                    $sid,
                    $accountId,
                    $group,
                    $expiresAt
                );
            } else {
                $this->VipRepository->insertPrivilege(
                    $target['server_vip'],
                    $sid,
                    $accountId,
                    $playerName,
                    $group,
                    $expiresAt
                );
            }

            $updatedSids[] = $sid;
        }

        if (!in_array($oldSid, $newSids, true)) {
            $this->VipRepository->deletePrivilege($original['server_vip'], $oldSid, $accountId);
            $panelIdsForRcon = array_merge($panelIdsForRcon, $original['panel_ids']);
        }

        $panelIdsForRcon = array_values(array_unique(array_map('intval', $panelIdsForRcon)));
        $partial = $this->reloadVipRcon('update_privilege', [$steamid64], $panelIdsForRcon);

        $this->LogsService->add([
            'type' => 'update_privilege',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => ['steamid' => $steamid64],
            'details' => [
                'privilege_id' => $privilegeId,
                'group' => $group,
                'expire' => $expire,
                'old_sid' => $oldSid,
                'sids' => array_values(array_unique($updatedSids)),
                'servers' => $panelIdsForRcon,
            ],
        ]);

        return $partial
            ? ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgVipUpdatedPartial')]
            : ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgVipUpdated')];
    }

    public function createPrivilege(string $steamid, string $group, $expire, array $panelServerIds): array
    {
        if (!$this->VipRepository->isConnected()) {
            $this->LogsService->addCriticalError('create_privilege', 'VIP backend not connected');
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgVipDbNotConnected')];
        }

        if ($steamid == '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifySteamId')];
        }
        if ($group == '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyVipGroup')];
        }
        if ($expire == '' || $expire == null || $expire < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyExpire')];
        }
        if ($panelServerIds == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyServers')];
        }

        $steamid64 = con_steam64($steamid);
        $targets = $this->resolveVipTargets($panelServerIds);

        if ($targets == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgServersNotLinkedVip')];
        }

        $expiresAt = $expire == 0 ? 0 : time() + $expire;
        $accountId = con_steam64to3_int($steamid64);
        $insertedSids = [];
        $panelIdsForRcon = [];

        foreach ($targets as $target) {
            if ($this->VipRepository->privilegeExists($target['server_vip'], $target['sid'], $accountId)) {
                return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgVipAlreadyExists')];
            }

            $this->VipRepository->insertPrivilege(
                $target['server_vip'],
                $target['sid'],
                $accountId,
                $this->General->checkName($steamid64),
                $group,
                $expiresAt
            );

            $insertedSids[] = $target['sid'];
            $panelIdsForRcon = array_merge($panelIdsForRcon, $target['panel_ids']);
        }

        $panelIdsForRcon = array_values(array_unique(array_map('intval', $panelIdsForRcon)));
        $partial = $this->reloadVipRcon('create_privilege', [$steamid64], $panelIdsForRcon);

        $this->LogsService->add([
            'type' => 'create_privilege',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => ['steamid' => $steamid64],
            'details' => [
                'group' => $group,
                'expire' => $expire,
                'sids' => $insertedSids,
                'servers' => $panelIdsForRcon,
            ],
        ]);

        return $partial
            ? ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgVipGrantedPartial')]
            : ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgVipGranted')];
    }

    private function resolveVipTargets(array $panelServerIds): array
    {
        $panelServerIds = array_values(array_unique(array_map('intval', $panelServerIds)));

        if (in_array(-1, $panelServerIds, true) || $panelServerIds == []) {
            $panelServerIds = array_column($this->ServersService->getServers(), 'id');
        }

        $targets = [];
        foreach ($this->ServersService->returnServersByIds($panelServerIds) as $server) {
            $serverVip = trim((string) ($server['server_vip'] ?? ''));
            $sid = (int) ($server['server_vip_id'] ?? 0);
            if ($serverVip == '' || $sid <= 0) {
                continue;
            }

            $key = $serverVip . '|' . $sid;
            if (!isset($targets[$key])) {
                $targets[$key] = [
                    'server_vip' => $serverVip,
                    'sid' => $sid,
                    'panel_ids' => [],
                ];
            }
            $targets[$key]['panel_ids'][] = (int) $server['id'];
        }

        return array_values($targets);
    }

    private function normalizePrivilegeIds(array $ids): array
    {
        $out = [];
        foreach ($ids as $id) {
            $id = trim((string) $id);
            if ($id != '' && preg_match('/^\d+:\d+$/', $id)) {
                $out[$id] = true;
            }
        }

        return array_keys($out);
    }

    private function resolveVipTargetByPrivilegeId(string $privilegeId): ?array
    {
        $parts = explode(':', $privilegeId, 2);
        if (count($parts) != 2) {
            return null;
        }

        $accountId = (int) $parts[0];
        $sid = (int) $parts[1];
        if ($accountId <= 0 || $sid <= 0) {
            return null;
        }

        $serverVip = null;
        $panelIds = [];
        foreach ($this->General->server_list as $server) {
            if ((int) ($server['server_vip_id'] ?? 0) != $sid) {
                continue;
            }

            $vip = trim((string) ($server['server_vip'] ?? ''));
            if ($vip == '') {
                continue;
            }

            if ($serverVip == null) {
                $serverVip = $vip;
            } elseif ($serverVip != $vip) {
                continue;
            }

            $panelIds[] = (int) $server['id'];
        }

        if ($serverVip == null) {
            return null;
        }

        return [
            'account_id' => $accountId,
            'sid' => $sid,
            'server_vip' => $serverVip,
            'panel_ids' => array_values(array_unique($panelIds)),
            'steamid64' => con_steam3to64_int($accountId),
        ];
    }

    private function resolveVipSearch(string $search): array
    {
        $search = trim($search);
        if ($search == '') {
            return [0, ''];
        }

        $steamSearch = ModuleHelper::toSteam64($search);
        if (preg_match('/^(7656119)([0-9]{10})$/', $steamSearch)) {
            return [(int) con_steam64to3_int($steamSearch), ''];
        }

        return [0, $search];
    }

    private function buildVipGroupNamesMap(): array
    {
        $map = [];
        foreach ($this->RendersService->renderVipGroups() as $group) {
            $ini = (string) ($group['ini'] ?? '');
            if ($ini == '') {
                continue;
            }
            $map[$ini] = (string) ($group['name'] ?? $ini);
        }

        return $map;
    }

    private function resolveVipServerTooltip(string $serverVip, int $sid): string
    {
        $names = [];
        foreach ($this->General->server_list as $server) {
            if (trim((string) ($server['server_vip'] ?? '')) != $serverVip) {
                continue;
            }
            if ((int) ($server['server_vip_id'] ?? 0) != $sid) {
                continue;
            }
            $names[] = (string) $server['name_custom'];
        }

        $names = array_values(array_unique($names));

        return $names != [] ? implode(', ', $names) : ('#' . $sid);
    }

    private function resolveVipPanelIds(string $serverVip, int $sid): array
    {
        $panelIds = [];
        foreach ($this->General->server_list as $server) {
            if (trim((string) ($server['server_vip'] ?? '')) != $serverVip) {
                continue;
            }
            if ((int) ($server['server_vip_id'] ?? 0) != $sid) {
                continue;
            }
            $panelIds[] = (int) $server['id'];
        }

        return array_values(array_unique($panelIds));
    }

    private function reloadVipRcon(string $action, array $steamids64, array $panelIds): bool
    {
        $partial = false;

        foreach ($steamids64 as $steamid64) {
            $cmd = "css_reload_vip_player {$steamid64}; mm_reload_vip {$steamid64}";
            $partial = RconResultHelper::sendAndLog(
                $this->RconService,
                $this->LogsService,
                $action,
                $cmd,
                (string) $steamid64,
                $panelIds,
                null
            ) || $partial;
        }

        return $partial;
    }

    private function formatVipExpireDisplay(int $expires): string
    {
        if ($expires === 0) {
            return $this->Translate->get_translate_phrase('_Forever');
        }

        if ($expires <= time()) {
            return ModuleHelper::phrase($this->Translate, '_at_expired');
        }

        return $this->Modules->action_time_exchange_exact($expires - time());
    }
}
