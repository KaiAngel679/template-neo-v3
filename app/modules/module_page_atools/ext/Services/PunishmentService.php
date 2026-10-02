<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Helpers\GameBackendResolver;
use app\modules\module_page_atools\ext\Helpers\RconResultHelper;
use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\AdminRepository;
use app\modules\module_page_atools\ext\Repositories\DatabaseRepository;
use app\modules\module_page_atools\ext\Services\LogsService;
use app\modules\module_page_atools\ext\Services\RconService;
use app\modules\module_page_atools\ext\Services\ServersService;
use app\modules\module_page_atools\ext\Services\AccessService;
use app\modules\module_page_atools\ext\Services\BlockDbService;

class PunishmentService
{
    private const GAME_CS2 = GameBackendResolver::GAME_CS2;
    private const GAME_CSGO = GameBackendResolver::GAME_CSGO;

    private $AdminRepository, $DatabaseRepository, $ServersService, $RconService, $AccessService, $LogsService, $GameBackendResolver, $BlockDbService, $General, $Db, $Translate, $Modules;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->AdminRepository = new AdminRepository($Db);
        $this->DatabaseRepository = new DatabaseRepository($Db);
        $this->ServersService = new ServersService($General);
        $this->RconService = new RconService($General);
        $this->AccessService = new AccessService($Db);
        $this->LogsService = new LogsService();
        $this->BlockDbService = new BlockDbService();
        $this->GameBackendResolver = new GameBackendResolver($Db);
        $this->Translate = $Translate;
        $this->Modules = $Modules;
        $this->General = $General;
        $this->Db = $Db;
    }

    public function createPunishment(string $steamid, ?string $ip, int $type, string $reason, int $expire, array $servers): array
    {
        if ($error = $this->validatePunishInput($steamid, $type, $reason, $expire, $servers, true)) {
            return $error;
        }

        $steamid64 = con_steam64($steamid);
        $steamid32 = con_steam32($steamid);
        $name = $this->General->checkName($steamid64);
        $issuerAdmin = $this->getIssuerAdminByGame();

        if (
            !$this->ServersService->isGameInvolvedInServerSelection($servers, self::GAME_CS2)
            && !$this->ServersService->isGameInvolvedInServerSelection($servers, self::GAME_CSGO)
        ) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyServers')];
        }

        if (!$this->issuerCanApplyToPanels($servers, $issuerAdmin)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgNotAdminCannotPunish')];
        }

        $cs2Panels = $this->normalizePanelIdsForGame($servers, self::GAME_CS2);
        $csgoPanels = $this->normalizePanelIdsForGame($servers, self::GAME_CSGO);
        $partial = false;

        if ($this->ServersService->isGameInvolvedInServerSelection($servers, self::GAME_CS2) && $issuerAdmin['cs2']) {
            $partial = $this->createPunishmentInGame(self::GAME_CS2, $cs2Panels, $steamid64, $name, $ip, $type, $reason, $expire, $_SESSION['steamid64']) || $partial;
        }

        if ($this->ServersService->isGameInvolvedInServerSelection($servers, self::GAME_CSGO) && $issuerAdmin['csgo']) {
            $partial = $this->createPunishmentInGame(self::GAME_CSGO, $csgoPanels, $steamid32, $name, $ip, $type, $reason, $expire, con_steam32($_SESSION['steamid64'])) || $partial;
        }

        if ($type === 0 && $this->ServersService->isGameInvolvedInServerSelection($servers, self::GAME_CS2)) {
            $issuerName = $this->General->checkName($_SESSION['steamid64']);
            $this->BlockDbService->createBan(
                $_SESSION['steamid64'],
                $issuerName,
                $steamid64,
                $name,
                $ip,
                $expire,
                $reason
            );
        }

        $this->logPunishment('create_punishment', $_SESSION['steamid64'], [$steamid64], [
            'punish_type' => $type,
            'reason' => $reason,
            'expire' => $expire,
            'servers' => $servers,
        ]);

        return $this->resolveCreateSuccess($partial, $servers, $issuerAdmin);
    }

    public function updatePunishment(string $type, string $listPunishType, int $punishId, ?string $ip, int $punishType, string $reason, int $expire, array $servers, string $issuerSteamid64): array
    {
        if ($punishId <= 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPunishNotSelected')];
        }

        if ($error = $this->validatePunishInput(null, $punishType, $reason, $expire, $servers, false, $listPunishType)) {
            return $error;
        }

        if (!$this->issuerIsSiteAdmin()) {
            if ($error = $this->validateNotSelfPunishments($type, [$punishId], $issuerSteamid64, 'modify')) {
                return $error;
            }

            if ($error = $this->assertIssuerForGame($type)) {
                return $error;
            }
        }

        $panelIds = $this->normalizePanelIdsForGame($servers, $type);
        if ($panelIds == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyServers')];
        }

        [$serversIds, $serversRcon] = $this->ServersService->resolvePanelServersToBinding($panelIds);
        if (!$this->AdminRepository->updatePunishmentById($type, $punishId, $ip, $punishType, $reason, $expire, $serversIds)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgCannotModifyExpiredPunish')];
        }

        $targets = $this->resolvePunishmentTargetSteamids64($type, [$punishId]);
        $partial = $this->reloadPunishmentsRcon('update_punishment', $type, $targets, $serversRcon, $issuerSteamid64);

        $this->logPunishment('update_punishment', $issuerSteamid64, $targets, [
            'game' => $type,
            'punish_id' => $punishId,
            'punish_type' => $punishType,
            'reason' => $reason,
            'expire' => $expire,
            'servers' => $servers,
        ]);

        return $partial
            ? ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPunishUpdatedPartial')]
            : ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPunishUpdated')];
    }

    public function getPunishmentsList(string $type, string $punishType, int $admin, array $servers, string $dateFrom, string $dateTo, string $expireFilter, int $limit = 10, int $offset = 0, string $mySteamid = '', string $search = ''): array
    {
        $filters = [
            'punish_type' => $punishType,
            'admin' => $admin,
            'servers' => $this->ServersService->buildServersBindingForList($servers, $type),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'expire_filter' => $expireFilter,
            'limit' => $limit,
            'offset' => $offset,
            'search' => $search,
        ];

        $rows = $this->AdminRepository->getPunishmentsList($type, $filters);
        $serverNames = $this->buildServerNameMap();
        $data = [];

        foreach ($rows as $row) {
            $data[] = $this->formatPunishmentRow($row, $type, $serverNames);
        }

        return [
            'status' => 'success',
            'data' => $data,
            'total' => $this->AdminRepository->getPunishmentsCount($type, $filters),
            'my_data' => $this->AccessService->buildListMyData($mySteamid),
        ];
    }

    public function removePunishments(string $type, array $punishIds, string $issuerSteamid64, string $listPunishType = 'ban'): array
    {
        return $this->mutatePunishments($type, $punishIds, $issuerSteamid64, 'remove', 'remove_punishments', '_at_msgPunishmentsRemoved', true, true, $listPunishType);
    }

    public function deletePunishments(string $type, array $punishIds, string $issuerSteamid64): array
    {
        return $this->mutatePunishments($type, $punishIds, $issuerSteamid64, 'delete', 'delete_punishments', '_at_msgPunishmentsDeleted', false, false, 'ban');
    }

    private function mutatePunishments(string $type, array $punishIds, string $issuerSteamid64, string $selfAction, string $logType, string $successMessageKey, bool $checkRemovable, bool $isRemove, string $listPunishType = 'ban'): array
    {
        $ids = $this->normalizeIds($punishIds);
        if ($ids == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPunishmentsNotSelected')];
        }

        if (!$this->issuerIsSiteAdmin()) {
            if ($error = $this->validateNotSelfPunishments($type, $ids, $issuerSteamid64, $selfAction)) {
                return $error;
            }
        }

        if ($checkRemovable && ($error = $this->validateRemovablePunishments($type, $ids))) {
            return $error;
        }

        $issuerDbSteamid = $type === self::GAME_CS2
            ? con_steam64($issuerSteamid64)
            : con_steam32($issuerSteamid64);

        $targets = $this->resolvePunishmentTargetSteamids64($type, $ids);

        if ($isRemove) {
            $this->AdminRepository->removePunishmentsByIds($type, $ids, $issuerDbSteamid);
        } else {
            $this->AdminRepository->deletePunishmentsByIds($type, $ids);
        }

        if ($isRemove && $listPunishType === 'ban' && $type === self::GAME_CS2) {
            foreach ($targets as $targetSteamid64) {
                $this->BlockDbService->unban($targetSteamid64);
            }
        }

        $this->logPunishment($logType, $issuerSteamid64, $targets, [
            'game' => $type,
        ]);

        $csgoCmd = $isRemove ? 'ma_wb_unblock' : 'ma_wb_ban';
        $this->reloadPunishmentsRcon($logType, $type, $targets, [-1], $issuerSteamid64, $csgoCmd);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, $successMessageKey)];
    }

    private function createPunishmentInGame(string $game, array $panelIds, string $targetSteamid, string $name, ?string $ip, int $type, string $reason, int $expire, string $issuerSteamid): bool
    {
        if (!$this->GameBackendResolver->isGameConnected($game)) {
            $this->LogsService->addCriticalError('create_punishment', 'Game backend not connected', [
                'game' => $game,
                'target_steamid' => con_steam64($targetSteamid),
            ]);

            return false;
        }

        [$serversIds, $serversRcon] = $this->ServersService->resolvePanelServersToBinding($panelIds);
        $this->AdminRepository->createPunishment($game, $targetSteamid, $name, $ip, $type, $reason, $expire, $serversIds, $issuerSteamid);

        return $this->reloadPunishmentsRcon('create_punishment', $game, [con_steam64($targetSteamid)], $serversRcon, $_SESSION['steamid64']);
    }

    private function reloadPunishmentsRcon(string $logType, string $game, array $targetSteamids64, array $serversRcon, string $issuerSteamid64, string $csgoCommand = 'ma_wb_ban'): bool
    {
        if ($game == self::GAME_CSGO) {
            return $this->punishRconApply($logType, $targetSteamids64[0] ?? '', $serversRcon, $csgoCommand, self::GAME_CSGO);
        }

        if ($game != self::GAME_CS2 || !$this->GameBackendResolver->isCs2Connected()) {
            return false;
        }

        $partial = false;

        foreach ($targetSteamids64 as $steamid64) {
            $cmd = $this->GameBackendResolver->cs2PunishReloadCommand($steamid64);
            $partial = $this->punishRconApply($logType, $steamid64, $serversRcon, $cmd, self::GAME_CS2) || $partial;
        }

        return $partial;
    }

    private function issuerCanApplyToPanels(array $servers, array $issuerAdmin): bool
    {
        if ($this->ServersService->isGameInvolvedInServerSelection($servers, self::GAME_CS2) && !$issuerAdmin['cs2']) {
            return false;
        }

        if ($this->ServersService->isGameInvolvedInServerSelection($servers, self::GAME_CSGO) && !$issuerAdmin['csgo']) {
            return false;
        }

        return true;
    }

    private function assertIssuerForGame(string $game): ?array
    {
        $issuerAdmin = $this->getIssuerAdminByGame();

        if ($game == self::GAME_CS2 && !$issuerAdmin['cs2']) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgNotAdminCannotModifyPunish')];
        }

        if ($game == self::GAME_CSGO && !$issuerAdmin['csgo']) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgNotAdminCannotModifyPunish')];
        }

        return null;
    }

    private function resolveCreateSuccess(bool $partial, array $servers, array $issuerAdmin): array
    {
        $cs2Involved = $this->ServersService->isGameInvolvedInServerSelection($servers, self::GAME_CS2);
        $csgoInvolved = $this->ServersService->isGameInvolvedInServerSelection($servers, self::GAME_CSGO);

        if ($partial && (($cs2Involved && !$issuerAdmin['cs2']) || ($csgoInvolved && !$issuerAdmin['csgo']))) {
            return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPunishGrantedPartialNoAdmin')];
        }

        if (($cs2Involved && !$issuerAdmin['cs2']) || ($csgoInvolved && !$issuerAdmin['csgo'])) {
            return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPunishGrantedNoAdmin')];
        }

        if ($partial) {
            return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPunishGrantedPartial')];
        }

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgPunishGranted')];
    }

    private function issuerIsSiteAdmin(): bool
    {
        return $this->DatabaseRepository->isCurrentSiteAdmin();
    }

    private function validateRemovablePunishments(string $type, array $ids): ?array
    {
        $removable = $this->AdminRepository->filterRemovablePunishmentIds($type, $ids);
        if (count($removable) != count($ids)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgCannotRemoveExpiredPunish')];
        }

        return null;
    }

    private function validateNotSelfPunishments(string $type, array $ids, string $mySteamid64, string $action): ?array
    {
        $mySteamid = con_steam64($mySteamid64);

        foreach ($this->resolvePunishmentTargetSteamids64($type, $ids) as $steamid64) {
            if ($steamid64 == $mySteamid) {
                if ($action === 'remove') {
                    $key = '_at_msgCannotRemoveOwnPunish';
                } elseif ($action === 'delete') {
                    $key = '_at_msgCannotDeleteOwnPunish';
                } else {
                    $key = '_at_msgCannotModifyOwnPunish';
                }

                return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, $key)];
            }
        }

        return null;
    }

    private function validatePunishInput(?string $steamid, int $punishType, string $reason, int $expire, array $servers, bool $requireSteamid, ?string $listPunishType = null): ?array
    {
        if ($requireSteamid && $steamid == '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifySteamId')];
        }

        if ($listPunishType == 'ban' && $punishType != 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgInvalidPunishType')];
        }

        if ($listPunishType == 'mute' && !in_array($punishType, [1, 2, 3], true)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgInvalidPunishType')];
        }

        if (!in_array($punishType, [0, 1, 2, 3], true)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgInvalidPunishType')];
        }

        if ($reason == '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyReason')];
        }

        if ($expire < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyTerm')];
        }

        if ($servers == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyServers')];
        }

        return null;
    }

    private function normalizePanelIdsForGame(array $servers, string $game): array
    {
        $panelIds = array_values(array_unique(array_map('intval', $servers)));

        if (in_array(-1, $panelIds, true)) {
            return [-1];
        }

        return $this->ServersService->filterPanelServerIdsByGame($panelIds, $game);
    }

    private function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0)));
    }

    private function resolvePunishmentTargetSteamids64(string $type, array $ids): array
    {
        $steamids64 = [];

        foreach ($this->AdminRepository->getPunishmentOffenderSteamidsByIds($type, $ids) as $steamid) {
            $normalized = con_steam64($steamid);
            if ($normalized != '' && $normalized != '0') {
                $steamids64[$normalized] = true;
            }
        }

        return array_keys($steamids64);
    }

    private function logPunishment(string $type, string $issuerSteamid64, array $targetSteamids64, array $details): void
    {
        $this->LogsService->add([
            'type' => $type,
            'admin' => ['steamid' => $issuerSteamid64],
            'target' => $this->LogsService->buildTargetSteamids($targetSteamids64),
            'details' => $details,
        ]);
    }

    private function buildServerNameMap(): array
    {
        $map = [];
        foreach ($this->General->server_list as $server) {
            $sb = $server['server_sb_id'];
            if ($sb != 0) {
                $map[$sb] = $server['name_custom'];
            }
        }

        return $map;
    }

    private function formatPunishmentRow(array $row, string $type, array $serverNames): array
    {
        $offenderName = ModuleHelper::resolveDisplayName($this->General, con_steam64($row['steamid']), $row['name'] ?? null);

        $adminSteam = !empty($row['admin_steamid']) ? con_steam64($row['admin_steamid']) : '';
        $adminName = 'Console';
        if ($adminSteam != '') {
            $adminName = ModuleHelper::resolveDisplayName($this->General, $adminSteam, $row['admin_name'] ?? null);
        }

        $removed = !empty($row['unpunish_admin_id'])
            || (($row['RemoveType'] ?? '') == 'U')
            || !empty($row['unbanned_by']);
        $expiresAt = $row['expires'];
        $expired = !$removed && $expiresAt > 0 && $expiresAt <= time();
        $duration = $this->resolveDurationSeconds($row, $expiresAt);
        $status = $this->resolvePunishmentStatus($removed, $expiresAt, $duration);

        return [
            'id' => (int) $row['id'],
            'is_removed' => $removed,
            'is_expired' => $expired,
            'punish_type' => (int) ($row['punish_type'] ?? 0),
            'ip' => ($row['ip'] ?? ''),
            'expires' => $expiresAt,
            'created' => (int) $row['created'],
            'duration_seconds' => $duration,
            'server_panel_ids' => $this->mapBindingServerIdsToPanelIds($this->parseServerIdsFromRow($row), $type),
            'created_at' => $row['created'] > 0 ? date('d.m.y', $row['created']) : '',
            'created_time' => $row['created'] > 0 ? (ModuleHelper::phrase($this->Translate, '_at_createdTime') . ' ' . date('H:i', $row['created'])) : '',
            'offender_steamid' => con_steam64($row['steamid']),
            'offender_name' => $offenderName,
            'offender_avatar' => $this->General->getAvatar(con_steam64($row['steamid']), 3),
            'offender_checked_avatar' => $this->General->checkAvatar(con_steam64($row['steamid'])),
            'reason' => action_text_clear($row['reason']),
            'admin_steamid' => $adminSteam,
            'admin_name' => $adminName,
            'admin_avatar' => $adminSteam != '' ? $this->General->getAvatar($adminSteam, 3) : '',
            'admin_checked_avatar' => $adminSteam != '' ? $this->General->checkAvatar($adminSteam) : 0,
            'servers' => $this->resolveServerNames($this->parseServerIdsFromRow($row), $serverNames),
            'duration_text' => $duration == 0
                ? $this->Translate->get_translate_phrase('_Forever')
                : $this->Modules->action_time_exchange_exact($duration),
            'expire_class' => $status['class'],
            'expire_text' => $status['text'],
        ];
    }

    private function resolveDurationSeconds(array $row, int $expiresAt): int
    {
        $lengthStored = ($row['length'] ?? 0);
        if ($lengthStored > 0) {
            return $lengthStored;
        }

        if ($expiresAt > 0 && $row['created'] > 0) {
            return max(0, $expiresAt - $row['created']);
        }

        return 0;
    }

    private function resolvePunishmentStatus(bool $removed, int $expires, int $durationSec): array
    {
        if ($removed) {
            return ['class' => 'removed', 'text' => ModuleHelper::phrase($this->Translate, '_at_removed')];
        }

        if ($expires == 0) {
            return ['class' => 'permanent', 'text' => $this->Translate->get_translate_phrase('_Forever')];
        }

        if ($expires <= time()) {
            return ['class' => 'expiring', 'text' => ModuleHelper::phrase($this->Translate, '_at_expired')];
        }

        return [
            'class' => '',
            'text' => $this->Modules->action_time_exchange_exact(max(0, $expires - time())),
        ];
    }

    private function parseServerIdsFromRow(array $row): array
    {
        $raw = $row['server_ids'] ?? null;
        if ($raw == null || $raw == '') {
            $raw = isset($row['server_id']) ? $row['server_id'] : '';
        }

        if ($raw == '') {
            return [];
        }

        return array_map('intval', explode(',', $raw));
    }

    private function resolveServerNames(array $serverIds, array $serverNames): array
    {
        if ($serverIds == []) {
            return [];
        }

        foreach ($serverIds as $serverId) {
            if ($serverId == -1 || $serverId == 0) {
                return [ModuleHelper::phrase($this->Translate, '_at_allServers')];
            }
        }

        $names = [];
        foreach ($serverIds as $serverId) {
            if (isset($serverNames[$serverId])) {
                $names[] = $serverNames[$serverId];
            }
        }

        return array_values(array_unique($names));
    }

    private function mapBindingServerIdsToPanelIds(array $sbIds, string $type): array
    {
        if ($sbIds == []) {
            return [];
        }

        if (in_array(-1, $sbIds, true) || in_array(0, $sbIds, true)) {
            return [-1];
        }

        $gameUp = mb_strtoupper($type, 'UTF-8');
        $panel = [];

        foreach ($this->General->server_list as $server) {
            if (mb_strtoupper(($server['server_game'] ?? ''), 'UTF-8') != $gameUp) {
                continue;
            }
            $sb = ($server['server_sb_id'] ?? 0);
            if ($sb != 0 && in_array($sb, $sbIds, true)) {
                $panel[] = $server['id'];
            }
        }

        return array_values(array_unique($panel));
    }

    private function punishRconApply(string $logType, string $targetSteamid64, array $serversRcon, string $cmd, string $game): bool
    {
        return RconResultHelper::sendAndLog(
            $this->RconService,
            $this->LogsService,
            $logType,
            $cmd,
            $targetSteamid64,
            $serversRcon,
            $game
        );
    }

    private function getIssuerAdminByGame(): array
    {
        $admin = ['cs2' => false, 'csgo' => false];

        if ($this->GameBackendResolver->isCs2Connected()) {
            $admin['cs2'] = $this->AdminRepository->issetAdmin($_SESSION['steamid64'], self::GAME_CS2);
        }

        if ($this->GameBackendResolver->isCsgoConnected()) {
            $admin['csgo'] = $this->AdminRepository->issetAdmin($_SESSION['steamid32'], self::GAME_CSGO);
        }

        return $admin;
    }
}
