<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Formatters\PanelPermissionFormatter;
use app\modules\module_page_atools\ext\Helpers\GameBackendResolver;
use app\modules\module_page_atools\ext\Helpers\RconResultHelper;
use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\AdminRepository;
use app\modules\module_page_atools\ext\Repositories\DatabaseRepository;
use app\modules\module_page_atools\ext\Repositories\FileRepository;
use app\modules\module_page_atools\ext\Services\AccessService;
use app\modules\module_page_atools\ext\Services\ServersService;
use app\modules\module_page_atools\ext\Services\RconService;
use app\modules\module_page_atools\ext\Services\LogsService;
use app\modules\module_page_atools\ext\Services\PrivilegesService;

class AdminService
{
    private $AccessService, $AdminRepository, $DatabaseRepository, $ServersService, $RconService, $LogsService, $PrivilegesService, $GameBackendResolver, $PanelPermissionFormatter, $FileRepository, $General, $Db, $Translate;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->AdminRepository = new AdminRepository($Db);
        $this->DatabaseRepository = new DatabaseRepository($Db);
        $this->AccessService = new AccessService($Db);
        $this->ServersService = new ServersService($General);
        $this->RconService = new RconService($General);
        $this->LogsService = new LogsService();
        $this->PrivilegesService = new PrivilegesService($Db, $General, $Translate, $Modules);
        $this->GameBackendResolver = new GameBackendResolver($Db);
        $this->PanelPermissionFormatter = new PanelPermissionFormatter($Translate);
        $this->FileRepository = new FileRepository();
        $this->Translate = $Translate;
        $this->General = $General;
        $this->Db = $Db;
    }

    public function getGroupsGame(string $type): ?array
    {
        if ($type == 'cs2' || $type == 'csgo') {
            return ['status' => 'success', 'data' => $this->AdminRepository->getGroupsGame($type)];
        }
        return [];
    }

    public function createAdmin(string $steamid, string $type, string $group, string $expire, array $servers, array $permissions, string $vipGroup = ''): array
    {
        $vipGroup = trim($vipGroup);
        if ($vipGroup !== '' && !$this->AccessService->hasPermission($_SESSION['steamid'], 'privileges.create')) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgVipPermissionRequired')];
        }

        $validate = $this->validateAdminMutationInput($steamid, $type, $group, $expire, $servers);
        if ($validate != null) {
            return $validate;
        }
        if ($this->AdminRepository->issetAdmin($steamid, $type)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminExists')];
        }

        $steamid64 = con_steam64($steamid);
        $steamid32 = con_steam32($steamid);
        $name = $this->General->checkName($steamid64);

        $panelPermissionsForLog = $permissions;
        if (!$this->AccessService->hasAnyPermission($steamid64)) {
            $this->AccessService->createAdminWeb($steamid64, $permissions);
        } else {
            $panelPermissionsForLog = $this->buildPanelPermissionsPayload(
                $this->AccessService->getAllPermissions($steamid64)
            );
        }

        [$serversIds, $serversRcon] = $this->resolveAdminServersBinding($servers, $type);

        $logDetails = $this->buildAdminLogDetails($type, $group, $expire, $serversIds, $panelPermissionsForLog);

        if ($type === GameBackendResolver::GAME_CS2 && $this->GameBackendResolver->isCs2Connected()) {
            $this->AdminRepository->createAdmin($steamid64, $type, $name, $group, $expire, $serversIds);

            return $this->maybeGrantVipWithAdmin(
                $this->adminRconFinish(
                    'create_admin',
                    $steamid64,
                    $serversRcon,
                    $this->GameBackendResolver->cs2AdminReloadCommand($steamid64),
                    'create',
                    GameBackendResolver::GAME_CS2,
                    $logDetails
                ),
                $steamid,
                $vipGroup,
                $expire,
                $servers
            );
        }

        if ($type === GameBackendResolver::GAME_CSGO && $this->GameBackendResolver->isCsgoConnected()) {
            $this->AdminRepository->createAdmin($steamid32, $type, $name, $group, $expire, $serversIds);

            return $this->maybeGrantVipWithAdmin(
                $this->adminRconFinish(
                    'create_admin',
                    $steamid64,
                    $serversRcon,
                    $this->GameBackendResolver->csgoAdminReloadCommand(),
                    'create',
                    GameBackendResolver::GAME_CSGO,
                    $logDetails
                ),
                $steamid,
                $vipGroup,
                $expire,
                $servers
            );
        }

        if ($type !== GameBackendResolver::GAME_CSGO && $type !== GameBackendResolver::GAME_CS2) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgInvalidGameType')];
        }

        return $this->logAdminBackendMissing('create_admin', $type, ['steamid' => $steamid64]);
    }

    public function getAdminsList(string $type, array $servers, int $group, string $mySteamid, int $limit = 10, int $offset = 0, string $search = '', array $accessPermissions = []): array
    {
        $serversBinding = $this->ServersService->buildServersBindingForList($servers, $type);
        $allowedSteamids = $this->resolveAllowedSteamidsForAccessPermissions($accessPermissions, $type);
        $admins = $this->AdminRepository->getAdminsList($type, $serversBinding, $group, $limit, $offset, $search, $allowedSteamids);
        $total = $this->AdminRepository->getAdminsCount($type, $serversBinding, $group, $search, $allowedSteamids);
        $myData = array_merge(
            $this->AccessService->buildListMyData($mySteamid),
            ['warn_settings' => $this->getWarnSettings()]
        );

        if ($admins == []) {
            return ['status' => 'success', 'data' => [], 'total' => (int) $total, 'my_data' => $myData];
        }

        $steamIds = [];
        foreach ($admins as $admin) {
            $steamIds[$admin['id']] = con_steam64($admin['steamid']);
        }

        $allPermissions = [];
        $this->AccessService->prefetchPermissions(array_values($steamIds));
        foreach ($steamIds as $adminId => $sid64) {
            $allPermissions[$adminId] = $this->AccessService->getAllPermissions($sid64);
        }

        $siteAdminSet = $this->DatabaseRepository->siteAdminSteamidsSet(array_values($steamIds));
        $warnByTarget = $this->DatabaseRepository->getWarningsForTargets(array_values($steamIds));

        $result = [];
        foreach ($admins as $admin) {
            $steamid64 = $steamIds[$admin['id']];
            $name = ModuleHelper::resolveDisplayName($this->General, $steamid64, $admin['name'] ?? null);
            $expires = (int) $admin['expires'];

            $adminPermissions = $allPermissions[$admin['id']] ?? [];

            $result[] = [
                'id' => (int) $admin['id'],
                'name' => $name,
                'steamid' => $steamid64,
                'avatar' => $this->General->getAvatar($steamid64, 3),
                'checked_avatar' => $this->General->checkAvatar($steamid64),
                'group_id' => (int) $admin['group_id'],
                'group_name' => action_text_clear($admin['group_name']) ?? 'Unknown',
                'expires' => $expires,
                'expires_text' => $expires == 0 ? $this->Translate->get_translate_phrase('_Forever') : date('d.m.y, H:i', $expires),
                'permissions' => $adminPermissions,
                'panel_access_group_id' => $this->PanelPermissionFormatter->resolveAccessGroupId($adminPermissions),
                'servers' => $this->buildAdminServersList($admin, $type),
                'is_site_admin' => isset($siteAdminSet[$steamid64]),
                'warnings' => $this->formatWarningsRows($warnByTarget[$steamid64] ?? []),
            ];
        }

        return ['status' => 'success', 'data' => $result, 'total' => (int) $total, 'my_data' => $myData];
    }

    public function updateAdmin(int $adminId, string $type, string $group, string $expire, array $servers, array $permissions): array
    {
        $admin = $this->AdminRepository->getAdminById($adminId, $type);
        if (empty($admin)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminNotFound')];
        }

        $steamid64 = con_steam64($admin['steamid']);
        if (!$this->issuerIsSiteAdmin()) {
            if ($steamid64 == $_SESSION['steamid64']) {
                return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgCannotUpdateOwnAccess')];
            }
            if ($this->DatabaseRepository->issetSiteAdmin($steamid64)) {
                return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgCannotChangeSiteAdmin')];
            }
        }

        $validate = $this->validateAdminMutationInput($steamid64, $type, $group, $expire, $servers);
        if ($validate != null) {
            return $validate;
        }

        [$serversIds, $serversRcon] = $this->resolveAdminServersBinding($servers, $type);

        $this->AccessService->createAdminWeb($steamid64, $permissions);

        $logDetails = $this->buildAdminLogDetails($type, $group, $expire, $serversIds, $permissions);

        if ($type === GameBackendResolver::GAME_CS2 && $this->GameBackendResolver->isCs2Connected()) {
            $this->AdminRepository->updateAdmin($admin['id'], $type, $group, $expire, $serversIds);

            return $this->adminRconFinish(
                'update_admin',
                $steamid64,
                $serversRcon,
                $this->GameBackendResolver->cs2AdminReloadCommand($steamid64),
                'update',
                GameBackendResolver::GAME_CS2,
                $logDetails
            );
        }

        if ($type === GameBackendResolver::GAME_CSGO && $this->GameBackendResolver->isCsgoConnected()) {
            $this->AdminRepository->updateAdmin($admin['id'], $type, $group, $expire, $serversIds);

            return $this->adminRconFinish(
                'update_admin',
                $steamid64,
                $serversRcon,
                $this->GameBackendResolver->csgoAdminReloadCommand(),
                'update',
                GameBackendResolver::GAME_CSGO,
                $logDetails
            );
        }

        if ($type !== GameBackendResolver::GAME_CSGO && $type !== GameBackendResolver::GAME_CS2) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgInvalidGameType')];
        }

        return $this->logAdminBackendMissing('update_admin', $type, ['admin_id' => $adminId]);
    }

    public function deleteAdmin(int $adminId, string $type, bool $writeLog = true): array
    {
        $admin = $this->AdminRepository->getAdminById($adminId, $type);
        $id = $admin['id'];
        if (empty($id)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminNotFound')];
        }

        $steamid64 = con_steam64($admin['steamid']);
        if (!$this->issuerIsSiteAdmin()) {
            if ($steamid64 == $_SESSION['steamid64']) {
                return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgCannotDeleteOwnAccess')];
            }
            if ($this->DatabaseRepository->issetSiteAdmin($steamid64)) {
                return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgCannotDeleteSiteAdmin')];
            }
        }

        $this->AccessService->deleteAdminWeb($steamid64);
        $this->AdminRepository->deleteAdmin($id, $type);
        if ($writeLog) {
            $this->LogsService->add([
                'type' => 'delete_admin',
                'admin' => ['steamid' => $_SESSION['steamid']],
                'target' => ['steamid' => $steamid64],
                'details' => ['type' => $type],
            ]);
        }

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminDeleted')];
    }

    public function giveWarn(string $targetSteamid, string $reason, int $expireRaw, string $adminSteamid, int $adminId, string $type): array
    {
        if ($targetSteamid == $adminSteamid) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgCannotWarnSelf')];
        }

        $settings = $this->getWarnSettings();
        $maxWarns = $settings['max_warns'];

        $reason = trim($reason);
        if ($reason == '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyWarnReason')];
        }
        if (mb_strlen($reason) > 255) {
            $reason = mb_substr($reason, 0, 255);
        }

        if ($this->DatabaseRepository->countActiveWarnings($targetSteamid) >= $maxWarns) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgMaxWarnsReached')];
        }

        if ($expireRaw < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyWarnExpire')];
        }

        $expiresAt = $expireRaw == 0 ? 0 : time() + $expireRaw;
        $this->DatabaseRepository->insertWarning($targetSteamid, $adminSteamid, $reason, $expireRaw, time(), time(), $expiresAt);

        [$resolvedAdminId, $resolvedType] = $this->resolveAdminForAutoDelete($targetSteamid, $adminId, $type);
        if ($resolvedType === GameBackendResolver::GAME_CSGO && $this->GameBackendResolver->isCsgoConnected()) {
            $this->AdminRepository->createSourceBansAdminWarn(
                $resolvedAdminId,
                $targetSteamid,
                $adminSteamid,
                $expiresAt,
                $reason
            );
        }

        $this->LogsService->add([
            'type' => 'give_warn',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => ['steamid' => $targetSteamid],
            'details' => ['reason' => $reason, 'expire' => $expireRaw],
        ]);

        $adminDeleted = $this->tryAutoDeleteAdminOnMaxWarns($targetSteamid, $resolvedAdminId, $resolvedType, $settings);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, $adminDeleted ? '_at_msgWarnGivenAdminRemoved' : '_at_msgWarnGiven')];
    }

    public function removeWarns(string $targetSteamid, array $warnIds, string $adminSteamid): array
    {
        return $this->mutateWarns(
            $targetSteamid,
            $warnIds,
            $adminSteamid,
            'remove_warn',
            '_at_msgCannotRemoveOwnWarns',
            '_at_msgWarnsRemoved',
            true
        );
    }

    public function deleteWarns(string $targetSteamid, array $warnIds, string $adminSteamid): array
    {
        return $this->mutateWarns(
            $targetSteamid,
            $warnIds,
            $adminSteamid,
            'delete_warn',
            '_at_msgCannotDeleteOwnWarns',
            '_at_msgWarnsDeleted',
            false
        );
    }

    public function updateWarn(string $targetSteamid, int $warnId, string $reason, string $expireRaw, string $adminSteamid): array
    {
        if ($targetSteamid == $adminSteamid) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgCannotUpdateOwnWarn')];
        }

        $row = $this->DatabaseRepository->getWarningByIdForTarget($warnId, $targetSteamid);
        if ($row == null) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgWarnNotFound')];
        }

        $reason = trim($reason);
        if ($reason == '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyWarnReason')];
        }
        if (mb_strlen($reason) > 255) {
            $reason = mb_substr($reason, 0, 255);
        }

        if ($expireRaw < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyWarnExpire')];
        }
        $expiresAt = $expireRaw == 0 ? 0 : time() + $expireRaw;
        $this->DatabaseRepository->updateWarning($warnId, $targetSteamid, $reason, $expireRaw, $expiresAt, time());

        $this->LogsService->add([
            'type' => 'update_warn',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => ['steamid' => $targetSteamid],
            'details' => ['reason' => $reason, 'expire' => $expireRaw, 'warn_id' => $warnId],
        ]);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgWarnUpdated')];
    }

    private function issuerIsSiteAdmin(): bool
    {
        return $this->DatabaseRepository->isCurrentSiteAdmin();
    }

    private function validateAdminMutationInput(string $steamid, string $type, string $group, string $expire, array $servers): ?array
    {
        if (!$steamid) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifySteamId')];
        }
        if (!$type) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyType')];
        }
        if ($group == '' || !isset($group)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyGroup')];
        }
        if ($expire < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyExpire')];
        }
        if ($servers == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyServers')];
        }

        return null;
    }

    private function logAdminBackendMissing(string $action, string $type, array $context = []): array
    {
        $this->LogsService->addCriticalError($action, 'Admin backend not connected', array_merge(['type' => $type], $context));

        return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminBackendMissing')];
    }

    private function resolveAdminServersBinding(array $panelServers, string $type): array
    {
        [$serversIds, $serversRcon] = $this->ServersService->resolvePanelServersToBinding($panelServers);

        if ($type !== GameBackendResolver::GAME_CSGO || !in_array(-1, $serversIds, true)) {
            return [$serversIds, $serversRcon];
        }

        $expanded = $this->ServersService->getSourceBansBindingIds(GameBackendResolver::GAME_CSGO);
        $panelIds = $this->ServersService->filterPanelServerIdsByGame($panelServers, GameBackendResolver::GAME_CSGO);

        return [
            $expanded !== [] ? $expanded : $serversIds,
            $panelIds !== [] ? $panelIds : $serversRcon,
        ];
    }

    private function adminRconFinish(string $logType, string $steamid64, array $serversRcon, string $cmd, string $createOrUpdate, string $game, array $logDetails): array
    {
        $partial = RconResultHelper::sendAndLog(
            $this->RconService,
            $this->LogsService,
            $logType,
            $cmd,
            $steamid64,
            $serversRcon,
            $game
        );

        $this->LogsService->add([
            'type' => $logType,
            'admin' => ['steamid' => $_SESSION['steamid64']],
            'target' => ['steamid' => $steamid64],
            'details' => $logDetails,
        ]);

        if ($createOrUpdate == 'update') {
            return $partial
                ? ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminUpdatedPartial')]
                : ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminUpdated')];
        }

        return $partial
            ? ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminCreatedPartial')]
            : ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminCreated')];
    }

    private function maybeGrantVipWithAdmin(array $adminResult, string $steamid, string $vipGroup, $expire, array $servers): array
    {
        if ($adminResult['status'] !== 'success' || $vipGroup === '') {
            return $adminResult;
        }

        $vipResult = $this->PrivilegesService->createPrivilege($steamid, $vipGroup, $expire, $servers);
        if ($vipResult['status'] === 'success') {
            return [
                'status' => 'success',
                'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminCreatedWithVip'),
            ];
        }

        return [
            'status' => 'success',
            'message' => ($adminResult['message'] ?? '') . '. ' . ($vipResult['message'] ?? ''),
        ];
    }

    private function buildAdminServersList(array $admin, string $type): array
    {
        $serverIds = explode(',', $admin['server_ids']);
        if (in_array('-1', $serverIds, true) || in_array('', $serverIds, true)) {
            return [
                [
                    'id' => -1,
                    'name' => ModuleHelper::phrase($this->Translate, '_at_allServers'),
                ]
            ];
        }
        $out = [];
        foreach ($serverIds as $rawSid) {
            $row = $this->ServersService->returnServerByServerId($type, $rawSid);
            if ($row != null) {
                $out[] = [
                    'id' => (int) $row['id'],
                    'name' => $row['name_custom'],
                ];
            }
        }

        return $out;
    }

    private function formatWarningsRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'id' => (int) $row['id'],
                'reason' => action_text_clear($row['reason']),
                'created_at_text' => date('d.m.y, H:i', $row['created_at']),
                'expires_at' => (int) $row['expires_at'],
                'expires_at_text' => $row['expires_at'] == 0 ? $this->Translate->get_translate_phrase('_Forever') : date('d.m.y, H:i', $row['expires_at']),
                'admin_name' => $this->General->checkName($row['admin_steamid']),
                'is_expired' => $row['expires_at'] > 0 && $row['expires_at'] <= time(),
            ];
        }

        return $out;
    }

    private function mutateWarns(
        string $targetSteamid,
        array $warnIds,
        string $adminSteamid,
        string $logType,
        string $selfActionErrorKey,
        string $successMessageKey,
        bool $expireOnly
    ): array {
        if ($targetSteamid == $adminSteamid) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, $selfActionErrorKey)];
        }

        $ids = $this->normalizeWarnIds($warnIds);
        if ($ids == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgWarnsNotSelected')];
        }

        $reasons = $this->DatabaseRepository->getWarningReasonsForTarget($targetSteamid, $ids);

        if ($expireOnly) {
            $this->DatabaseRepository->expireWarningsForTarget($targetSteamid, $ids);
        } else {
            $this->DatabaseRepository->deleteWarningsForTarget($targetSteamid, $ids);
        }

        $this->LogsService->add([
            'type' => $logType,
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => ['steamid' => $targetSteamid],
            'details' => ['reasons' => $reasons],
        ]);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, $successMessageKey)];
    }

    private function normalizeWarnIds(array $warnIds): array
    {
        $out = [];
        foreach ($warnIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $out[] = $id;
            }
        }

        return array_values(array_unique($out));
    }

    private function getWarnSettings(): array
    {
        $raw = $this->FileRepository->get('settings');

        return [
            'max_warns' => max(1, (int) ($raw['max_warns'] ?? 3)),
            'auto_delete_admin_max_warns' => !empty($raw['auto_delete_admin_max_warns']) ? 1 : 0,
        ];
    }

    private function tryAutoDeleteAdminOnMaxWarns(string $targetSteamid, int $adminId, string $type, array $settings): bool
    {
        if (empty($settings['auto_delete_admin_max_warns'])) {
            return false;
        }
        if ($adminId <= 0 || $type == '') {
            return false;
        }
        if ($this->DatabaseRepository->countActiveWarnings($targetSteamid) < $settings['max_warns']) {
            return false;
        }

        $result = $this->deleteAdmin($adminId, $type, false);
        if (($result['status'] ?? '') !== 'success') {
            return false;
        }

        $this->LogsService->add([
            'type' => 'auto_delete_admin_on_max_warns',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => ['steamid' => $targetSteamid],
            'details' => ['type' => $type],
        ]);

        return true;
    }

    private function resolveAdminForAutoDelete(string $targetSteamid, int $adminId, string $type): array
    {
        if ($adminId > 0 && $type !== '') {
            return [$adminId, $type];
        }

        $resolved = $this->AdminRepository->findAdminByTargetSteamid($targetSteamid, $type !== '' ? $type : null);
        if ($resolved === null) {
            return [$adminId, $type];
        }

        return [(int) $resolved['id'], (string) $resolved['type']];
    }

    private function buildAdminLogDetails(string $type, string $group, string $expire, array $serversIds, array $permissions): array
    {
        return [
            'type' => $type,
            'group' => $group,
            'expire' => $expire,
            'servers' => $serversIds,
            'panel_access_group' => $this->PanelPermissionFormatter->resolveAccessGroupName($permissions),
            'panel_permissions' => $this->PanelPermissionFormatter->labelsForLog($permissions),
        ];
    }

    private function buildPanelPermissionsPayload(array $permissionKeys): array
    {
        $groupId = $this->PanelPermissionFormatter->resolveAccessGroupId($permissionKeys);
        if ($groupId !== null) {
            return ['group' => $groupId];
        }

        return ['flags' => $permissionKeys];
    }

    private function resolveAllowedSteamidsForAccessPermissions(array $permissions, string $type): ?array
    {
        $permissions = array_values(array_unique(array_filter(array_map('strval', $permissions), function (string $permission): bool {
            return $this->PanelPermissionFormatter->isValidPermission($permission);
        })));

        if ($permissions === []) {
            return null;
        }

        $allowed = [];

        foreach ($this->DatabaseRepository->getAllAccessAdmins() as $steamid64 => $userPermissions) {
            foreach ($permissions as $permission) {
                if (in_array($permission, $userPermissions, true)) {
                    $allowed[] = $type === GameBackendResolver::GAME_CSGO
                        ? con_steam32($steamid64)
                        : (string) con_steam64($steamid64);
                    break;
                }
            }
        }

        return array_values(array_unique(array_filter($allowed)));
    }
}
