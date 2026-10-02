<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Helpers\GameBackendResolver;
use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\AdminRepository;
use app\modules\module_page_atools\ext\Repositories\DashboardRepository;
use app\modules\module_page_atools\ext\Repositories\FinanceRepository;
use app\modules\module_page_atools\ext\Repositories\PlayerSearchRepository;

class PlayerSearchService
{
    private const PREVIEW_LIMIT = 2;
    private const PUNISHMENT_FETCH_LIMIT = 5;

    private $PlayerSearchRepository;
    private $AdminRepository;
    private $FinanceRepository;
    private $DashboardRepository;
    private $AccessService;
    private $PunishmentService;
    private $AdminService;
    private $PrivilegesService;
    private $FinanceService;
    private $CheckService;
    private $ExperienceService;
    private $GameBackendResolver;
    private $General;
    private $Translate;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->PlayerSearchRepository = new PlayerSearchRepository($Db);
        $this->AdminRepository = new AdminRepository($Db);
        $this->FinanceRepository = new FinanceRepository($Db);
        $this->DashboardRepository = new DashboardRepository($Db);
        $this->AccessService = new AccessService($Db);
        $this->PunishmentService = new PunishmentService($Db, $General, $Translate, $Modules);
        $this->AdminService = new AdminService($Db, $General, $Translate, $Modules);
        $this->PrivilegesService = new PrivilegesService($Db, $General, $Translate, $Modules);
        $this->FinanceService = new FinanceService($Db, $General, $Translate);
        $this->CheckService = new CheckService($Db, $General, $Translate);
        $this->ExperienceService = new ExperienceService($Db, $General, $Translate, $Modules);
        $this->GameBackendResolver = new GameBackendResolver($Db);
        $this->General = $General;
        $this->Translate = $Translate;
    }

    public function search(string $query, string $mySteamid, array $permissions): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_searchEnterQuery')];
        }

        $steam64 = $this->PlayerSearchRepository->resolveSteam64($query);
        if ($steam64 === '') {
            $candidates = $this->PlayerSearchRepository->findCandidatesByNickname($query);
            if ($candidates === []) {
                return [
                    'status' => 'success',
                    'mode' => 'not_found',
                    'message' => ModuleHelper::phrase($this->Translate, '_at_searchNotFound'),
                ];
            }

            if (count($candidates) > 1) {
                return [
                    'status' => 'success',
                    'mode' => 'candidates',
                    'candidates' => $this->formatCandidates($candidates),
                ];
            }

            $steam64 = (string) ($candidates[0]['steamid'] ?? '');
        }

        if ($steam64 === '' || !preg_match('/^7656119\d{10}$/', $steam64)) {
            return [
                'status' => 'success',
                'mode' => 'not_found',
                'message' => ModuleHelper::phrase($this->Translate, '_at_searchNotFound'),
            ];
        }

        $searchSteam = $steam64;
        $searchSteam32 = ModuleHelper::toSteam32($steam64);

        return [
            'status' => 'success',
            'mode' => 'player',
            'player' => $this->buildPlayerCard($steam64),
            'sections' => $this->buildSections($steam64, $searchSteam, $searchSteam32, $mySteamid, $permissions),
        ];
    }

    private function buildPlayerCard(string $steam64): array
    {
        return [
            'steamid' => $steam64,
            'name' => ModuleHelper::resolveDisplayName($this->General, $steam64),
            'avatar' => $this->General->getAvatar($steam64, 3),
            'checked_avatar' => $this->General->checkAvatar($steam64),
            'profile_url' => '/profiles/' . $steam64 . '/?search=1',
        ];
    }

    private function formatCandidates(array $candidates): array
    {
        $out = [];
        foreach ($candidates as $candidate) {
            $steam64 = (string) ($candidate['steamid'] ?? '');
            if ($steam64 === '') {
                continue;
            }

            $name = trim((string) ($candidate['name'] ?? ''));
            if ($name === '') {
                $name = ModuleHelper::resolveDisplayName($this->General, $steam64);
            }

            $out[] = [
                'steamid' => $steam64,
                'name' => $name,
                'avatar' => $this->General->getAvatar($steam64, 3),
                'checked_avatar' => $this->General->checkAvatar($steam64),
            ];
        }

        return $out;
    }

    private function buildSections(
        string $steam64,
        string $searchSteam,
        string $searchSteam32,
        string $mySteamid,
        array $permissions
    ): array {
        $sections = [];

        if ($this->hasPermission($permissions, 'punishments.view')) {
            $sections['punishments'] = $this->buildPunishmentsSection($searchSteam, $mySteamid);
        }

        if ($this->hasPermission($permissions, 'checks.view')
            && $this->AdminRepository->isChecksBackendAvailable()) {
            $sections['checks'] = $this->buildChecksSection($searchSteam, $mySteamid);
        }

        if ($this->DashboardRepository->hasReportsAccess($mySteamid) && $this->PlayerSearchRepository->isReportsConnected()) {
            $sections['reports'] = $this->buildReportsSection($steam64);
        }

        if ($this->hasPermission($permissions, 'admins.view')) {
            $sections['admins'] = $this->buildAdminsSection($searchSteam, $mySteamid);
        }

        if ($this->hasPermission($permissions, 'privileges.view')) {
            $sections['vip'] = $this->buildVipSection($searchSteam);
        }

        if ($this->hasPermission($permissions, 'finances.view') && $this->FinanceRepository->isLkConnected()) {
            $sections['finances'] = $this->buildFinancesSection($searchSteam32, $mySteamid);
        }

        if ($this->hasPermission($permissions, 'experience.view')) {
            $experience = $this->buildExperienceSection($searchSteam32, $mySteamid);
            if ($experience !== null) {
                $sections['experience'] = $experience;
            }
        }

        return $sections;
    }

    private function mapPunishmentPreviewRow(array $row): array
    {
        return [
            'punish_type' => (int) ($row['punish_type'] ?? 0),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'created_time' => (string) ($row['created_time'] ?? ''),
            'offender_steamid' => (string) ($row['offender_steamid'] ?? ''),
            'offender_name' => (string) ($row['offender_name'] ?? ''),
            'offender_avatar' => (string) ($row['offender_avatar'] ?? ''),
            'offender_checked_avatar' => (int) ($row['offender_checked_avatar'] ?? 0),
            'reason' => (string) ($row['reason'] ?? ''),
            'admin_steamid' => (string) ($row['admin_steamid'] ?? ''),
            'admin_name' => (string) ($row['admin_name'] ?? ''),
            'admin_avatar' => (string) ($row['admin_avatar'] ?? ''),
            'admin_checked_avatar' => (int) ($row['admin_checked_avatar'] ?? 0),
            'servers' => is_array($row['servers'] ?? null) ? $row['servers'] : [],
            'duration_text' => (string) ($row['duration_text'] ?? ''),
            'expire_class' => (string) ($row['expire_class'] ?? ''),
            'expire_text' => (string) ($row['expire_text'] ?? ''),
        ];
    }

    private function mapCheckPreviewRow(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'player_steamid' => (string) ($row['player_steamid'] ?? ''),
            'player_name' => (string) ($row['player_name'] ?? ''),
            'player_avatar' => (string) ($row['player_avatar'] ?? ''),
            'player_checked_avatar' => (int) ($row['player_checked_avatar'] ?? 0),
            'admin_steamid' => (string) ($row['admin_steamid'] ?? ''),
            'admin_name' => (string) ($row['admin_name'] ?? ''),
            'admin_avatar' => (string) ($row['admin_avatar'] ?? ''),
            'admin_checked_avatar' => (int) ($row['admin_checked_avatar'] ?? 0),
            'datestart' => (string) ($row['datestart'] ?? ''),
            'date_end' => (string) ($row['date_end'] ?? ''),
            'verdict' => (string) ($row['verdict'] ?? ''),
            'contact' => (string) ($row['contact'] ?? ''),
        ];
    }

    private function mapAdminPreviewRow(array $row): array
    {
        $servers = [];
        foreach (($row['servers'] ?? []) as $server) {
            if (!is_array($server)) {
                continue;
            }
            $servers[] = ['name' => (string) ($server['name'] ?? '')];
        }

        return [
            'steamid' => (string) ($row['steamid'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'avatar' => (string) ($row['avatar'] ?? ''),
            'checked_avatar' => (int) ($row['checked_avatar'] ?? 0),
            'group_name' => (string) ($row['group_name'] ?? ''),
            'servers' => $servers,
            'expires_text' => (string) ($row['expires_text'] ?? ''),
        ];
    }

    private function mapVipPreviewRow(array $row): array
    {
        return [
            'steamid' => (string) ($row['steamid'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'avatar' => (string) ($row['avatar'] ?? ''),
            'checked_avatar' => (int) ($row['checked_avatar'] ?? 0),
            'group_name' => (string) ($row['group_name'] ?? ''),
            'server_tooltip' => (string) ($row['server_tooltip'] ?? ''),
            'expires_text' => (string) ($row['expires_text'] ?? ''),
            'remaining_text' => (string) ($row['remaining_text'] ?? ''),
        ];
    }

    private function mapFinancePreviewRow(array $row): array
    {
        return [
            'steamid' => (string) ($row['steamid'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'avatar' => (string) ($row['avatar'] ?? ''),
            'checked_avatar' => (int) ($row['checked_avatar'] ?? 0),
            'cash' => (float) ($row['cash'] ?? 0),
            'all_cash' => (float) ($row['all_cash'] ?? 0),
            'last_deposit_summ' => (float) ($row['last_deposit_summ'] ?? 0),
            'last_deposit' => (string) ($row['last_deposit'] ?? ''),
        ];
    }

    private function mapExperiencePreviewRow(array $row): array
    {
        return [
            'steamid' => (string) ($row['steamid'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'avatar' => (string) ($row['avatar'] ?? ''),
            'checked_avatar' => (int) ($row['checked_avatar'] ?? 0),
            'value' => (int) ($row['value'] ?? 0),
            'kills' => (int) ($row['kills'] ?? 0),
            'deaths' => (int) ($row['deaths'] ?? 0),
            'shoots' => (int) ($row['shoots'] ?? 0),
            'hits' => (int) ($row['hits'] ?? 0),
            'headshots' => (int) ($row['headshots'] ?? 0),
            'playtime' => (string) ($row['playtime'] ?? ''),
            'server_tooltip' => (string) ($row['server_tooltip'] ?? ''),
        ];
    }

    private function mapReportPreviewRow(array $row): array
    {
        return [
            'time' => (string) ($row['time'] ?? ''),
            'reason' => (string) ($row['reason'] ?? ''),
            'kd' => (string) ($row['kd'] ?? ''),
            'status_label' => (string) ($row['status_label'] ?? ''),
            'verdict' => (string) ($row['verdict'] ?? ''),
            'player_name' => (string) ($row['player_name'] ?? ''),
            'player_steamid' => (string) ($row['player_steamid'] ?? ''),
            'player_avatar' => (string) ($row['player_avatar'] ?? ''),
            'player_checked_avatar' => (int) ($row['player_checked_avatar'] ?? 0),
            'admin_name' => (string) ($row['admin_name'] ?? ''),
            'admin_steamid' => (string) ($row['admin_steamid'] ?? ''),
            'admin_avatar' => (string) ($row['admin_avatar'] ?? ''),
            'admin_checked_avatar' => (int) ($row['admin_checked_avatar'] ?? 0),
        ];
    }

    private function buildPunishmentsSection(string $searchSteam, string $mySteamid): array
    {
        $allRows = [];
        $banTotal = 0;
        $muteTotal = 0;

        foreach ([GameBackendResolver::GAME_CS2, GameBackendResolver::GAME_CSGO] as $game) {
            if (!$this->GameBackendResolver->isGameConnected($game)) {
                continue;
            }

            $banResult = $this->PunishmentService->getPunishmentsList(
                $game,
                'ban',
                -1,
                [-1],
                '',
                '',
                'all',
                self::PUNISHMENT_FETCH_LIMIT,
                0,
                $mySteamid,
                $searchSteam
            );
            $muteResult = $this->PunishmentService->getPunishmentsList(
                $game,
                'mute',
                -1,
                [-1],
                '',
                '',
                'all',
                self::PUNISHMENT_FETCH_LIMIT,
                0,
                $mySteamid,
                $searchSteam
            );

            foreach (($banResult['data'] ?? []) as $row) {
                $allRows[] = $row;
            }
            foreach (($muteResult['data'] ?? []) as $row) {
                $allRows[] = $row;
            }

            $banTotal += (int) ($banResult['total'] ?? 0);
            $muteTotal += (int) ($muteResult['total'] ?? 0);
        }

        usort($allRows, static function (array $a, array $b): int {
            return (int) ($b['created'] ?? 0) <=> (int) ($a['created'] ?? 0);
        });

        $items = [];
        foreach (array_slice($allRows, 0, self::PREVIEW_LIMIT) as $row) {
            $items[] = $this->mapPunishmentPreviewRow($row);
        }

        return [
            'items' => $items,
            'total' => $banTotal + $muteTotal,
            'section_url' => '/atools/punishments/',
        ];
    }

    private function buildAdminsSection(string $searchSteam, string $mySteamid): array
    {
        $items = [];
        $total = 0;

        foreach ([GameBackendResolver::GAME_CS2, GameBackendResolver::GAME_CSGO] as $game) {
            if (!$this->GameBackendResolver->isGameConnected($game)) {
                continue;
            }

            $result = $this->AdminService->getAdminsList($game, [-1], -1, $mySteamid, self::PREVIEW_LIMIT, 0, $searchSteam);
            foreach (($result['data'] ?? []) as $row) {
                $items[] = $this->mapAdminPreviewRow($row);
            }
            $total += (int) ($result['total'] ?? 0);
        }

        return [
            'items' => array_slice($items, 0, self::PREVIEW_LIMIT),
            'total' => $total,
            'section_url' => '/atools/admins/',
        ];
    }

    private function buildVipSection(string $searchSteam): array
    {
        $result = $this->PrivilegesService->getPrivilegesList([-1], '-1', self::PREVIEW_LIMIT, 0, $searchSteam, 'all');
        $items = [];
        foreach (($result['data'] ?? []) as $row) {
            $items[] = $this->mapVipPreviewRow($row);
        }

        return [
            'items' => $items,
            'total' => (int) ($result['total'] ?? 0),
            'section_url' => '/atools/privileges/',
        ];
    }

    private function buildFinancesSection(string $searchSteam32, string $mySteamid): array
    {
        $result = $this->FinanceService->getFinancesList('down', 1, 0, $searchSteam32, $mySteamid);
        $items = [];
        foreach (($result['data'] ?? []) as $row) {
            $items[] = $this->mapFinancePreviewRow($row);
        }

        return [
            'items' => $items,
            'total' => (int) ($result['total'] ?? 0),
            'currency' => (string) ($result['currency'] ?? $this->General->currency),
            'section_url' => '/atools/finances/',
        ];
    }

    private function buildChecksSection(string $searchSteam, string $mySteamid): array
    {
        $result = $this->CheckService->getChecksList(-1, [-1], 'all', '', '', self::PREVIEW_LIMIT, 0, $mySteamid, $searchSteam);
        $items = [];
        foreach (($result['data'] ?? []) as $row) {
            $items[] = $this->mapCheckPreviewRow($row);
        }

        return [
            'items' => $items,
            'total' => (int) ($result['total'] ?? 0),
            'section_url' => '/atools/checks/',
        ];
    }

    private function buildExperienceSection(string $searchSteam32, string $mySteamid): ?array
    {
        $countResult = $this->ExperienceService->getExperienceList([-1], 'down', 1, 0, $searchSteam32, $mySteamid);
        if (($countResult['status'] ?? '') !== 'success') {
            return null;
        }

        $total = (int) ($countResult['total'] ?? 0);
        if ($total === 0) {
            return [
                'items' => [],
                'total' => 0,
                'section_url' => '/atools/experience/',
            ];
        }

        $result = $this->ExperienceService->getExperienceList([-1], 'down', $total, 0, $searchSteam32, $mySteamid);
        if (($result['status'] ?? '') !== 'success') {
            return null;
        }

        $items = [];
        foreach (($result['data'] ?? []) as $row) {
            $items[] = $this->mapExperiencePreviewRow($row);
        }

        return [
            'items' => $items,
            'total' => $total,
            'section_url' => '/atools/experience/',
        ];
    }

    private function buildReportsSection(string $steam64): array
    {
        $rows = $this->PlayerSearchRepository->fetchReportsForPlayer($steam64, self::PREVIEW_LIMIT);
        $items = [];

        foreach ($rows as $row) {
            $status = (int) ($row['status'] ?? 0);
            $kills = (int) ($row['kills'] ?? 0);
            $deaths = (int) ($row['deaths'] ?? 0);
            $intruderSteam = (string) ($row['steamid_intruder'] ?? '');
            $intruderName = trim((string) ($row['name_intruder'] ?? ''));
            if ($intruderName === '' && $intruderSteam !== '') {
                $intruderName = ModuleHelper::resolveDisplayName($this->General, $intruderSteam);
            }

            $adminSteam = (string) ($row['steamid_admin_verdict'] ?? '');
            $adminName = trim((string) ($row['name_admin_verdict'] ?? ''));
            if ($adminName === '' && $adminSteam !== '') {
                $adminName = ModuleHelper::resolveDisplayName($this->General, $adminSteam);
            }

            $items[] = $this->mapReportPreviewRow([
                'time' => !empty($row['time']) ? date('d.m.Y, H:i', (int) $row['time']) : '',
                'reason' => (string) ($row['reason'] ?? ''),
                'kd' => $this->formatKd($kills, $deaths),
                'status_label' => $status === 1
                    ? ModuleHelper::phrase($this->Translate, '_at_searchReportClosed')
                    : ModuleHelper::phrase($this->Translate, '_at_searchReportOpen'),
                'verdict' => (string) ($row['verdict'] ?? ''),
                'player_name' => $intruderName,
                'player_steamid' => $intruderSteam,
                'player_avatar' => $intruderSteam !== '' ? $this->General->getAvatar($intruderSteam, 3) : '',
                'player_checked_avatar' => $intruderSteam !== '' ? $this->General->checkAvatar($intruderSteam) : 0,
                'admin_name' => $adminName,
                'admin_steamid' => $adminSteam,
                'admin_avatar' => $adminSteam !== '' ? $this->General->getAvatar($adminSteam, 3) : '',
                'admin_checked_avatar' => $adminSteam !== '' ? $this->General->checkAvatar($adminSteam) : 0,
            ]);
        }

        return [
            'items' => $items,
            'total' => $this->PlayerSearchRepository->countReportsForPlayer($steam64),
            'section_url' => '/reports/list/0/1/',
        ];
    }

    private function formatKd(int $kills, int $deaths): string
    {
        if ($deaths <= 0) {
            return (string) $kills;
        }

        return rtrim(rtrim(number_format($kills / $deaths, 2, '.', ''), '0'), '.');
    }

    private function hasPermission(array $permissions, string $permission): bool
    {
        return in_array($permission, $permissions, true);
    }
}
