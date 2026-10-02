<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\AdminRepository;
use app\modules\module_page_atools\ext\Repositories\FileRepository;
use app\modules\module_page_atools\ext\Repositories\LanguageRepository;

class LogsListService
{
    private const CATEGORY_TYPES = [
        'punish' => ['create_punishment', 'update_punishment', 'remove_punishments', 'delete_punishments'],
        'admins' => ['create_admin', 'update_admin', 'delete_admin', 'give_warn', 'remove_warn', 'delete_warn', 'update_warn', 'auto_delete_admin_on_max_warns'],
        'checks' => ['delete_checks'],
        'finances' => ['add_balance', 'update_balance', 'reset_balance', 'delete_finances_without_donation'],
        'privileges' => ['create_privilege', 'update_privilege', 'delete_privilege'],
        'credits' => [],
        'experience' => ['add_experience', 'update_experience', 'reset_experience', 'wipe_experience_stats', 'delete_empty_experience_players'],
    ];

    private const TYPE_META = [
        'create_punishment' => ['phrase' => '_at_logCreatePunishment', 'icon' => 'plus', 'class' => 'punishment'],
        'update_punishment' => ['phrase' => '_at_logUpdatePunishment', 'icon' => 'edit-pen', 'class' => 'punishment'],
        'remove_punishments' => ['phrase' => '_at_logRemovePunishments', 'icon' => 'unlock', 'class' => 'unpunishment'],
        'delete_punishments' => ['phrase' => '_at_logDeletePunishments', 'icon' => 'trash', 'class' => 'unpunishment'],
        'create_admin' => ['phrase' => '_at_logCreateAdmin', 'icon' => 'plus', 'class' => 'admins'],
        'update_admin' => ['phrase' => '_at_logUpdateAdmin', 'icon' => 'edit-pen', 'class' => 'admins'],
        'delete_admin' => ['phrase' => '_at_logDeleteAdmin', 'icon' => 'undo', 'class' => 'admins'],
        'auto_delete_admin_on_max_warns' => ['phrase' => '_at_logAutoDeleteAdmin', 'icon' => 'undo', 'class' => 'admins'],
        'give_warn' => ['phrase' => '_at_logGiveWarn', 'icon' => 'plus', 'class' => 'warn'],
        'remove_warn' => ['phrase' => '_at_logRemoveWarn', 'icon' => 'undo', 'class' => 'unwarn'],
        'delete_warn' => ['phrase' => '_at_logDeleteWarn', 'icon' => 'trash', 'class' => 'unwarn'],
        'update_warn' => ['phrase' => '_at_logUpdateWarn', 'icon' => 'edit-pen', 'class' => 'warn'],
        'delete_checks' => ['phrase' => '_at_logDeleteChecks', 'icon' => 'trash', 'class' => 'uncheck'],
        'add_balance' => ['phrase' => '_at_logAddBalance', 'icon' => 'plus', 'class' => 'money'],
        'update_balance' => ['phrase' => '_at_logUpdateBalance', 'icon' => 'edit-pen', 'class' => 'money'],
        'reset_balance' => ['phrase' => '_at_logResetBalance', 'icon' => 'broom', 'class' => 'money'],
        'delete_finances_without_donation' => ['phrase' => '_at_logDeleteFinancePlayersWithoutDonation', 'icon' => 'ghost', 'class' => 'money'],
        'create_privilege' => ['phrase' => '_at_logCreatePrivilege', 'icon' => 'plus', 'class' => 'vip'],
        'update_privilege' => ['phrase' => '_at_logUpdatePrivilege', 'icon' => 'edit-pen', 'class' => 'vip'],
        'delete_privilege' => ['phrase' => '_at_logDeletePrivilege', 'icon' => 'trash', 'class' => 'vip'],
        'add_experience' => ['phrase' => '_at_logAddExperience', 'icon' => 'plus', 'class' => 'experience'],
        'update_experience' => ['phrase' => '_at_logUpdateExperience', 'icon' => 'edit-pen', 'class' => 'experience'],
        'reset_experience' => ['phrase' => '_at_logResetExperience', 'icon' => 'broom', 'class' => 'experience'],
        'wipe_experience_stats' => ['phrase' => '_at_logWipeExperienceStats', 'icon' => 'broom', 'class' => 'experience'],
        'delete_empty_experience_players' => ['phrase' => '_at_logDeleteEmptyExperiencePlayers', 'icon' => 'ghost', 'class' => 'experience'],
    ];

    private $LogsService, $AccessService, $ServersService, $FileRepository, $LanguageRepository, $AdminRepository, $General, $Translate, $Modules;
    private $adminGroupNameCache = [];
    private $vipGroupNameCache = [];

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->LogsService = new LogsService();
        $this->AccessService = new AccessService($Db);
        $this->ServersService = new ServersService($General);
        $this->FileRepository = new FileRepository();
        $this->LanguageRepository = new LanguageRepository();
        $this->AdminRepository = new AdminRepository($Db);
        $this->General = $General;
        $this->Translate = $Translate;
        $this->Modules = $Modules;
    }

    public function getList(string $mySteamid, string $category, string $sort, string $dateFrom, string $dateTo, string $search, int $limit, int $offset): array
    {
        $category = $this->normalizeCategory($category);
        $sort = $sort === 'old' ? 'old' : 'new';
        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);
        $search = trim($search);

        $entries = $this->collectEntries($category, $dateFrom, $dateTo);
        if ($search !== '') {
            $entries = array_values(array_filter($entries, function (array $entry) use ($search): bool {
                return $this->matchesSearch($entry, $search);
            }));
        }

        usort($entries, static function (array $a, array $b) use ($sort): int {
            $cmp = ($a['timestamp'] ?? 0) <=> ($b['timestamp'] ?? 0);
            return $sort === 'old' ? $cmp : -$cmp;
        });

        $total = count($entries);
        $slice = array_slice($entries, $offset, $limit);
        $canDelete = $this->AccessService->hasPermission($mySteamid, 'logs.delete');

        return [
            'status' => 'success',
            'data' => array_map(function (array $entry) use ($canDelete): array {
                return $this->formatEntry($entry, $canDelete);
            }, $slice),
            'total' => $total,
            'my_data' => $this->AccessService->buildListMyData($mySteamid),
        ];
    }

    public function deleteEntry(string $fileDate, int $index, string $mySteamid): array
    {
        if (!$this->AccessService->hasPermission($mySteamid, 'logs.delete')) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgForbidden')];
        }

        if (!$this->isValidFileDate($fileDate) || $index < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgLogNotFound')];
        }

        if (!$this->LogsService->deleteEntry($fileDate, $index)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgLogNotFound')];
        }

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgLogDeleted')];
    }

    private function collectEntries(string $category, string $dateFrom, string $dateTo): array
    {
        $allowedTypes = $this->typesForCategory($category);
        $fromTs = $this->parseInputDate($dateFrom, false);
        $toTs = $this->parseInputDate($dateTo, true);
        $entries = [];

        foreach ($this->LogsService->getLogFiles('logs') as $fileName) {
            $fileDate = preg_replace('/\.json$/', '', $fileName);
            $fileTs = $this->parseFileDate($fileDate);
            if ($fileTs === null) {
                continue;
            }
            if ($fromTs !== null && $fileTs < $fromTs) {
                continue;
            }
            if ($toTs !== null && $fileTs > $toTs) {
                continue;
            }

            $rows = $this->LogsService->get('logs', $fileDate);
            if (!is_array($rows)) {
                continue;
            }

            foreach ($rows as $index => $row) {
                if (!is_array($row)) {
                    continue;
                }
                $type = (string) ($row['type'] ?? 'unknown');
                if ($allowedTypes !== null && !in_array($type, $allowedTypes, true)) {
                    continue;
                }

                $entries[] = array_merge($row, [
                    'file_date' => $fileDate,
                    'index' => (int) $index,
                ]);
            }
        }

        return $entries;
    }

    private function formatEntry(array $entry, bool $canDelete): array
    {
        $type = (string) ($entry['type'] ?? 'unknown');
        $meta = self::TYPE_META[$type] ?? ['phrase' => $type, 'icon' => 'info-circle', 'class' => 'admins'];
        $timestamp = (int) ($entry['timestamp'] ?? 0);
        $admin = $this->formatActor($entry['admin'] ?? []);
        $targets = $this->extractTargets($entry);

        return [
            'id' => ($entry['file_date'] ?? '') . ':' . ($entry['index'] ?? 0),
            'file_date' => (string) ($entry['file_date'] ?? ''),
            'index' => (int) ($entry['index'] ?? 0),
            'type' => $type,
            'type_label' => ModuleHelper::phrase($this->Translate, $meta['phrase']),
            'type_icon' => $meta['icon'],
            'details_class' => $meta['class'],
            'timestamp' => $timestamp,
            'date_label' => $this->formatLogDate($timestamp),
            'time_tooltip' => $timestamp > 0 ? (ModuleHelper::phrase($this->Translate, '_at_logTimeTooltip') . ' ' . date('H:i', $timestamp)) : '',
            'admin' => $admin,
            'targets' => $targets,
            'details' => $this->buildDetails($type, $entry['details'] ?? [], $timestamp),
            'can_delete' => $canDelete,
        ];
    }

    private function buildDetails(string $type, array $details, int $logTimestamp = 0): array
    {
        $rows = [];

        switch ($type) {
            case 'create_punishment':
            case 'update_punishment':
                $rows[] = $this->detailRow('layers', ModuleHelper::phrase($this->Translate, '_at_logPunishType') . ' ' . $this->punishTypeLabel((int) ($details['punish_type'] ?? 0)));
                if (!empty($details['reason'])) {
                    $rows[] = $this->detailRow('list-info', ModuleHelper::phrase($this->Translate, '_at_logReason') . ' ' . action_text_clear((string) $details['reason']));
                }
                $rows[] = $this->detailRow('time', ModuleHelper::phrase($this->Translate, '_at_logPunishExpire') . ' ' . $this->formatExpireValue($details['expire'] ?? 0));
                $rows = array_merge($rows, $this->serverDetailRows($details['servers'] ?? [], $details['game'] ?? null));
                break;

            case 'remove_punishments':
            case 'delete_punishments':
                if (!empty($details['game'])) {
                    $rows[] = $this->detailRow('gamepad', ModuleHelper::phrase($this->Translate, '_at_logGame') . ' ' . strtoupper((string) $details['game']));
                }
                break;

            case 'create_admin':
            case 'update_admin':
                if (!empty($details['type'])) {
                    $rows[] = $this->detailRow('gamepad', ModuleHelper::phrase($this->Translate, '_at_logGame') . ' ' . strtoupper((string) $details['type']));
                }
                if (isset($details['group']) && $details['group'] !== '') {
                    $rows[] = $this->detailRow('policeman', ModuleHelper::phrase($this->Translate, '_at_logGroup') . ' ' . $this->resolveAdminGroupName((string) ($details['type'] ?? ''), (string) $details['group']));
                }
                if (!empty($details['panel_access_group'])) {
                    $rows[] = $this->detailRow('lock', ModuleHelper::phrase($this->Translate, '_at_logAccessGroup') . ' ' . action_text_clear((string) $details['panel_access_group']));
                }
                $permissionLabels = array_values(array_filter(array_map(static function ($label): string {
                    return is_string($label) && $label !== '' ? action_text_clear($label) : '';
                }, (array) ($details['panel_permissions'] ?? []))));
                if ($permissionLabels !== []) {
                    $rows[] = $this->detailRow('key', ModuleHelper::phrase($this->Translate, '_at_logPermissions') . ' ' . implode(', ', $permissionLabels));
                }
                if (array_key_exists('expire', $details)) {
                    $rows[] = $this->detailRow('time-expired', ModuleHelper::phrase($this->Translate, '_at_logExpire') . ' ' . $this->formatExpireValue($details['expire']));
                }
                $rows = array_merge($rows, $this->serverDetailRows($details['servers'] ?? [], $details['type'] ?? null));
                break;

            case 'delete_admin':
            case 'auto_delete_admin_on_max_warns':
                if (!empty($details['type'])) {
                    $rows[] = $this->detailRow('gamepad', ModuleHelper::phrase($this->Translate, '_at_logGame') . ' ' . strtoupper((string) $details['type']));
                }
                if (isset($details['group']) && $details['group'] !== '') {
                    $rows[] = $this->detailRow('policeman', ModuleHelper::phrase($this->Translate, '_at_logGroup') . ' ' . $this->resolveAdminGroupName((string) ($details['type'] ?? ''), (string) $details['group']));
                }
                if (array_key_exists('expire', $details)) {
                    $rows[] = $this->detailRow('time-expired', ModuleHelper::phrase($this->Translate, '_at_logExpire') . ' ' . $this->formatExpireValue($details['expire']));
                }
                $rows = array_merge($rows, $this->serverDetailRows($details['servers'] ?? [], $details['type'] ?? null));
                break;

            case 'give_warn':
            case 'update_warn':
                if (!empty($details['reason'])) {
                    $rows[] = $this->detailRow('warning', ModuleHelper::phrase($this->Translate, '_at_logWarnReason') . ' ' . action_text_clear((string) $details['reason']));
                }
                if (array_key_exists('expire', $details)) {
                    $rows[] = $this->detailRow('time', ModuleHelper::phrase($this->Translate, '_at_logDuration') . ' ' . $this->formatExpireValue($details['expire']));
                    if ((int) $details['expire'] > 0 && $logTimestamp > 0) {
                        $rows[] = $this->detailRow('time-expired', ModuleHelper::phrase($this->Translate, '_at_logEndDate') . ' ' . $this->formatLogDate($logTimestamp + (int) $details['expire']));
                    }
                }
                break;

            case 'remove_warn':
            case 'delete_warn':
                $reasons = array_values(array_filter(array_map(static function ($reason): string {
                    return is_string($reason) && $reason !== '' ? action_text_clear($reason) : '';
                }, (array) ($details['reasons'] ?? []))));
                if ($reasons === [] && !empty($details['reason'])) {
                    $reasons = [action_text_clear((string) $details['reason'])];
                }
                if ($reasons !== []) {
                    $label = count($reasons) > 1
                        ? ModuleHelper::phrase($this->Translate, '_at_logWarnReasons')
                        : ModuleHelper::phrase($this->Translate, '_at_logWarnReasonSingle');
                    $rows[] = $this->detailRow('warning', $label . ': ' . implode(', ', $reasons));
                }
                break;

            case 'delete_checks':
                foreach ((array) ($details['checks'] ?? []) as $check) {
                    if (!is_array($check)) {
                        continue;
                    }

                    $playerSid = $this->normalizeSteamid($check['player_steamid'] ?? '');
                    $checkerSid = $this->normalizeSteamid($check['admin_steamid'] ?? '');
                    $playerName = $this->resolveLoggedPersonName($playerSid, $check['player_name'] ?? null);
                    $checkerName = $this->resolveLoggedPersonName($checkerSid, $check['admin_name'] ?? null);

                    if ($playerSid !== '' || $playerName !== '') {
                        $rows[] = $this->detailRow(
                            'user-solo',
                            '',
                            null,
                            ModuleHelper::phrase($this->Translate, '_at_logPlayer') . ' ' . $this->logProfileLink($playerSid, $playerName)
                        );
                    }
                    if ($checkerSid !== '' || $checkerName !== '') {
                        $rows[] = $this->detailRow(
                            'policeman',
                            '',
                            null,
                            ModuleHelper::phrase($this->Translate, '_at_logChecker') . ' ' . $this->logProfileLink($checkerSid, $checkerName)
                        );
                    }
                }
                break;

            case 'create_privilege':
            case 'update_privilege':
                if (!empty($details['group'])) {
                    $rows[] = $this->detailRow('diamond', ModuleHelper::phrase($this->Translate, '_at_logGroup') . ' ' . $this->resolveVipGroupName((string) $details['group']));
                }
                if (array_key_exists('expire', $details)) {
                    $rows[] = $this->detailRow('time-expired', ModuleHelper::phrase($this->Translate, '_at_logExpire') . ' ' . $this->formatExpireValue($details['expire']));
                }
                $rows = array_merge($rows, $this->serverDetailRows($details['servers'] ?? []));
                break;

            case 'delete_privilege':
                $rows = array_merge($rows, $this->serverDetailRows($details['servers'] ?? []));
                break;

            case 'add_balance':
                if (array_key_exists('amount', $details)) {
                    $rows[] = $this->detailRow(
                        'wallet',
                        ModuleHelper::phrase($this->Translate, '_at_logBalanceAmount') . ' ' . $this->formatBalanceAmount($details['amount'])
                    );
                }
                break;

            case 'update_balance':
                if (array_key_exists('old_cash', $details)) {
                    $rows[] = $this->detailRow(
                        'wallet',
                        ModuleHelper::phrase($this->Translate, '_at_logBalanceOld') . ' ' . $this->formatBalanceAmount($details['old_cash'])
                    );
                }
                if (array_key_exists('new_cash', $details)) {
                    $rows[] = $this->detailRow(
                        'wallet',
                        ModuleHelper::phrase($this->Translate, '_at_logBalanceNew') . ' ' . $this->formatBalanceAmount($details['new_cash'])
                    );
                }
                break;

            case 'reset_balance':
                if (array_key_exists('cleared_cash', $details)) {
                    $rows[] = $this->detailRow(
                        'wallet',
                        ModuleHelper::phrase($this->Translate, '_at_logBalanceCleared') . ' ' . $this->formatBalanceAmount($details['cleared_cash'])
                    );
                }
                break;

            case 'add_experience':
                if (array_key_exists('amount', $details)) {
                    $rows[] = $this->detailRow(
                        'star-fill',
                        ModuleHelper::phrase($this->Translate, '_at_logExperienceAmount') . ' ' . (int) $details['amount']
                    );
                }
                $rows = array_merge($rows, $this->serverDetailRows($details['servers'] ?? []));
                break;

            case 'update_experience':
                if (array_key_exists('old_value', $details)) {
                    $rows[] = $this->detailRow(
                        'star-fill',
                        ModuleHelper::phrase($this->Translate, '_at_logExperienceOld') . ' ' . (int) $details['old_value']
                    );
                }
                if (array_key_exists('new_value', $details)) {
                    $rows[] = $this->detailRow(
                        'star-fill',
                        ModuleHelper::phrase($this->Translate, '_at_logExperienceNew') . ' ' . (int) $details['new_value']
                    );
                }
                break;

            case 'reset_experience':
                if (array_key_exists('cleared_value', $details)) {
                    $rows[] = $this->detailRow(
                        'star-fill',
                        ModuleHelper::phrase($this->Translate, '_at_logExperienceCleared') . ' ' . (int) $details['cleared_value']
                    );
                }
                break;

            case 'wipe_experience_stats':
            case 'delete_empty_experience_players':
                $rows = array_merge($rows, $this->serverDetailRows($details['servers'] ?? []));
                break;

            default:
                foreach ($details as $key => $value) {
                    if (is_scalar($value) && (string) $value !== '') {
                        $rows[] = $this->detailRow('list-info', ucfirst((string) $key) . ': ' . action_text_clear((string) $value));
                    }
                }
                break;
        }

        return $rows;
    }

    private function detailRow(string $icon, string $text, ?string $tooltip = null, ?string $html = null): array
    {
        $row = [
            'icon' => $icon,
            'text' => $text,
        ];

        if ($html !== null && $html !== '') {
            $row['html'] = $html;
        }
        if ($tooltip !== null && $tooltip !== '') {
            $row['tooltip'] = $tooltip;
        }

        return $row;
    }

    private function resolveLoggedPersonName(string $steamid, $fallbackName = null): string
    {
        if ($steamid !== '') {
            $name = ModuleHelper::resolveDisplayName($this->General, $steamid);
            if ($name !== 'Unnamed') {
                return $name;
            }
        }

        $fallbackName = trim((string) $fallbackName);

        return $fallbackName !== '' ? action_text_clear($fallbackName) : ($steamid !== '' ? $steamid : '');
    }

    private function logProfileLink(string $steamid, string $name): string
    {
        $label = htmlspecialchars($name !== '' ? $name : $steamid, ENT_QUOTES, 'UTF-8');
        if ($steamid === '') {
            return $label;
        }

        return '<a href="/profiles/' . htmlspecialchars($steamid, ENT_QUOTES, 'UTF-8') . '/?search=1" target="_blank">' . $label . '</a>';
    }

    private function serverDetailRows(array $servers, ?string $game = null): array
    {
        $names = $this->resolveServerNames($servers, $game);
        if ($names === []) {
            return [];
        }

        $text = ModuleHelper::phrase($this->Translate, '_at_logServers') . ' ' . implode(', ', $names);
        $tooltip = count($names) > 2 ? implode(', ', $names) : null;

        return [$this->detailRow('servers', $text, $tooltip)];
    }

    private function resolveServerNames(array $servers, ?string $game = null): array
    {
        $servers = array_values(array_unique(array_map('intval', $servers)));
        if ($servers === [] || in_array(-1, $servers, true)) {
            return [ModuleHelper::phrase($this->Translate, '_at_logAllServers')];
        }

        $map = [];
        foreach ($this->ServersService->getServers($game) as $server) {
            $map[(int) $server['id']] = (string) $server['name_custom'];
        }

        $names = [];
        foreach ($servers as $id) {
            $names[] = $map[$id] ?? ('#' . $id);
        }

        return $names;
    }

    private function resolveAdminGroupName(string $game, string $groupId): string
    {
        $key = strtolower($game) . ':' . $groupId;
        if (isset($this->adminGroupNameCache[$key])) {
            return $this->adminGroupNameCache[$key];
        }

        $name = $groupId;
        $groups = $this->AdminRepository->getGroupsGame(strtolower($game)) ?? [];
        foreach ($groups as $group) {
            if ((string) ($group['id'] ?? '') === (string) $groupId) {
                $name = (string) ($group['name'] ?? $groupId);
                break;
            }
        }

        return $this->adminGroupNameCache[$key] = $name;
    }

    private function resolveVipGroupName(string $groupIni): string
    {
        if (isset($this->vipGroupNameCache[$groupIni])) {
            return $this->vipGroupNameCache[$groupIni];
        }

        foreach ($this->FileRepository->get('vip_groups') as $row) {
            if ((string) ($row['ini'] ?? '') === $groupIni) {
                $display = $row['display'] ?? [];
                if (is_string($display)) {
                    $display = ['ru' => $display];
                }

                return $this->vipGroupNameCache[$groupIni] = $this->LanguageRepository->translate($display);
            }
        }

        return $this->vipGroupNameCache[$groupIni] = $groupIni;
    }

    private function punishTypeLabel(int $type): string
    {
        return $type === 1
            ? ModuleHelper::phrase($this->Translate, '_at_logMuteGag')
            : ModuleHelper::phrase($this->Translate, '_at_logGameBan');
    }

    private function formatExpireValue($value): string
    {
        $seconds = (int) $value;
        if ($seconds <= 0) {
            return $this->Translate->get_translate_phrase('_Forever');
        }

        foreach ($this->FileRepository->get('terms') as $term) {
            if ((int) ($term['time'] ?? -1) == $seconds) {
                return $this->LanguageRepository->translate($term['name'] ?? []);
            }
        }

        return $this->Modules->action_time_exchange_exact($seconds);
    }

    private function formatBalanceAmount($value): string
    {
        if (!is_numeric($value)) {
            return '0' . ($this->General->currency ?? '');
        }

        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') . ($this->General->currency ?? '');
    }

    private function formatLogDate(int $timestamp): string
    {
        if ($timestamp <= 0) {
            return '—';
        }

        $month = ModuleHelper::phrase($this->Translate, '_at_month' . (int) date('n', $timestamp));

        return (int) date('j', $timestamp) . ' ' . $month . ' ' . date('Y', $timestamp) . ModuleHelper::phrase($this->Translate, '_at_yearSuffix');
    }

    private function formatActor(array $actor): array
    {
        $steamid = $this->normalizeSteamid($actor['steamid'] ?? '');
        if ($steamid === '') {
            return [
                'steamid' => '',
                'name' => '—',
                'avatar' => '',
                'checked_avatar' => 0,
            ];
        }

        return [
            'steamid' => $steamid,
            'name' => ModuleHelper::resolveDisplayName($this->General, $steamid),
            'avatar' => $this->General->getAvatar($steamid, 3),
            'checked_avatar' => $this->General->checkAvatar($steamid),
        ];
    }

    private function extractTargets(array $entry): array
    {
        $out = [];
        foreach ($this->extractTargetSteamids($entry) as $steamid) {
            $out[] = $this->formatActor(['steamid' => $steamid]);
        }

        return $out;
    }

    private function extractTargetSteamids(array $entry): array
    {
        $steamids = [];
        $target = $entry['target'] ?? [];

        if (!empty($target['steamid'])) {
            $steamids[] = $this->normalizeSteamid($target['steamid']);
        }
        if (!empty($target['steamids']) && is_array($target['steamids'])) {
            foreach ($target['steamids'] as $sid) {
                $steamids[] = $this->normalizeSteamid($sid);
            }
        }

        if ($steamids === [] && !empty($entry['details']['targets']) && is_array($entry['details']['targets'])) {
            foreach ($entry['details']['targets'] as $row) {
                if (!is_array($row) || empty($row['steamid'])) {
                    continue;
                }
                $steamids[] = $this->normalizeSteamid($row['steamid']);
            }
        }

        return array_values(array_unique(array_filter($steamids)));
    }

    private function matchesSearch(array $entry, string $search): bool
    {
        $needle = mb_strtolower(trim($search));

        $steamNeedle = $this->resolveSearchSteamid($search);
        $haystacks = [];

        $adminSid = $this->normalizeSteamid($entry['admin']['steamid'] ?? '');
        if ($adminSid !== '') {
            $haystacks[] = $adminSid;
            $haystacks[] = mb_strtolower(ModuleHelper::resolveDisplayName($this->General, $adminSid));
        }

        foreach ($this->extractTargetSteamids($entry) as $sid) {
            $haystacks[] = $sid;
            $haystacks[] = mb_strtolower(ModuleHelper::resolveDisplayName($this->General, $sid));
        }

        foreach ($haystacks as $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            if ($steamNeedle !== '' && $value === $steamNeedle) {
                return true;
            }
            if (mb_strpos($value, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function resolveSearchSteamid(string $search): string
    {
        $search = trim($search);
        if ($search === '') {
            return '';
        }

        if (preg_match('/^7656119\d{10}$/', $search)) {
            return $search;
        }

        if (
            preg_match('/^STEAM_/i', $search)
            || preg_match('/steamcommunity\.com/i', $search)
            || preg_match('/^\[U:/', $search)
        ) {
            $converted = ModuleHelper::toSteam64($search);
            if (preg_match('/^7656119\d{10}$/', (string) $converted)) {
                return (string) $converted;
            }
        }

        if (ctype_digit($search)) {
            $converted = $this->normalizeSteamid($search);
            if ($converted !== '') {
                return $converted;
            }
        }

        return '';
    }

    private function normalizeSteamid($steamid): string
    {
        if ($steamid === null || $steamid === '') {
            return '';
        }

        $normalized = con_steam64((string) $steamid);
        if ($normalized === false || $normalized === '' || $normalized === '0') {
            return '';
        }

        return (string) $normalized;
    }

    private function normalizeCategory(string $category): string
    {
        return array_key_exists($category, self::CATEGORY_TYPES) ? $category : 'all';
    }

    private function typesForCategory(string $category): ?array
    {
        if ($category === 'all') {
            return null;
        }

        return self::CATEGORY_TYPES[$category] ?? [];
    }

    private function parseInputDate(string $value, bool $endOfDay): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$dt) {
            return null;
        }

        if ($endOfDay) {
            $dt->setTime(23, 59, 59);
        } else {
            $dt->setTime(0, 0, 0);
        }

        return $dt->getTimestamp();
    }

    private function parseFileDate(string $fileDate): ?int
    {
        if (!$this->isValidFileDate($fileDate)) {
            return null;
        }

        $dt = \DateTime::createFromFormat('d-m-Y', $fileDate);

        return $dt ? $dt->setTime(0, 0, 0)->getTimestamp() : null;
    }

    private function isValidFileDate(string $fileDate): bool
    {
        return (bool) preg_match('/^\d{2}-\d{2}-\d{4}$/', $fileDate);
    }
}
