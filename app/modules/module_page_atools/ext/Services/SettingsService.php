<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\FileRepository;

class SettingsService
{
    private $FileRepository, $Translate;

    public function __construct(object $Translate)
    {
        $this->FileRepository = new FileRepository();
        $this->Translate = $Translate;
    }

    public function createGroup(array $name, array $permissions): array
    {
        $name = $this->normalizeName($name);
        $permissions = array_values(array_filter(
            $permissions,
            static fn($permission): bool => is_string($permission) && $permission !== ''
        ));

        if ($name == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyGroupName')];
        }

        if ($permissions == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSelectAccessFlag')];
        }

        $this->FileRepository->update('groups', function (array $data) use ($name, $permissions): array {
            $data[$this->nextId($data)] = [
                'name' => $name,
                'permissions' => $permissions,
            ];

            return $data;
        });

        return ['status' => 'success'];
    }

    public function createReason(array $name, string $type): array
    {
        $name = $this->normalizeName($name);
        $type = strtolower(trim($type));

        if ($name == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyReasonName')];
        }

        if (!in_array($type, ['ban', 'mute'], true)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSelectPunishType')];
        }

        $this->FileRepository->update('reasons', function (array $data) use ($name, $type): array {
            $data[$this->nextId($data)] = [
                'name' => $name,
                'type' => $type,
            ];

            return $data;
        });

        return ['status' => 'success'];
    }

    public function updateReason(int $id, array $name, string $type): array
    {
        $reasons = $this->FileRepository->get('reasons');

        if (!isset($reasons[$id])) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgReasonNotFound')];
        }

        $name = $this->normalizeName($name);
        $type = strtolower(trim($type));

        if ($name == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyReasonName')];
        }

        if (!in_array($type, ['ban', 'mute'], true)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSelectPunishType')];
        }

        $this->FileRepository->update('reasons', function (array $data) use ($id, $name, $type): array {
            $data[$id]['name'] = $name;
            $data[$id]['type'] = $type;

            return $data;
        });

        return ['status' => 'success'];
    }

    public function deleteReason(int $id): array
    {
        return $this->deleteById('reasons', $id, '_at_msgReasonNotFound', '_at_msgReasonDeleted');
    }

    public function createTerm(array $name, int $time, string $type): array
    {
        $name = $this->normalizeName($name);
        $type = strtolower(trim($type));

        if ($name == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyTermName')];
        }

        if (!in_array($type, ['admins', 'punishments', 'vips'], true)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSelectTermType')];
        }

        if ($time < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgTermNegative')];
        }

        $this->FileRepository->update('terms', function (array $data) use ($name, $time, $type): array {
            $data[$this->nextId($data)] = [
                'name' => $name,
                'time' => $time,
                'type' => $type,
            ];

            return $data;
        });

        return ['status' => 'success'];
    }

    public function updateTerm(int $id, array $name, int $time, string $type): array
    {
        $terms = $this->FileRepository->get('terms');

        if (!isset($terms[$id])) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgTermNotFound')];
        }

        $name = $this->normalizeName($name);
        $type = strtolower(trim($type));

        if ($name == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyTermName')];
        }

        if (!in_array($type, ['admins', 'punishments', 'vips'], true)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSelectTermType')];
        }

        if ($time < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgTermNegative')];
        }

        $this->FileRepository->update('terms', function (array $data) use ($id, $name, $time, $type): array {
            $data[$id]['name'] = $name;
            $data[$id]['time'] = $time;
            $data[$id]['type'] = $type;

            return $data;
        });

        return ['status' => 'success'];
    }

    public function deleteTerm(int $id): array
    {
        return $this->deleteById('terms', $id, '_at_msgTermNotFound', '_at_msgTermDeleted');
    }

    public function createVipGroup(string $ini, array $display): array
    {
        $ini = trim($ini);
        $display = $this->normalizeName($display);

        if ($ini === '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyVipIni')];
        }

        if ($display == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyVipDisplay')];
        }

        $this->FileRepository->update('vip_groups', function (array $data) use ($ini, $display): array {
            $data[$this->nextId($data)] = [
                'ini' => $ini,
                'display' => $display,
            ];

            return $data;
        });

        return ['status' => 'success'];
    }

    public function updateVipGroup(int $id, string $ini, array $display): array
    {
        $groups = $this->FileRepository->get('vip_groups');

        if (!isset($groups[$id])) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgVipGroupNotFound')];
        }

        $ini = trim($ini);
        $display = $this->normalizeName($display);

        if ($ini === '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyVipIni')];
        }

        if ($display == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyVipDisplay')];
        }

        $this->FileRepository->update('vip_groups', function (array $data) use ($id, $ini, $display): array {
            $data[$id]['ini'] = $ini;
            $data[$id]['display'] = $display;

            return $data;
        });

        return ['status' => 'success'];
    }

    public function deleteVipGroup(int $id): array
    {
        return $this->deleteById('vip_groups', $id, '_at_msgVipGroupNotFound', '_at_msgVipGroupDeleted');
    }

    public function updateSettings(
        int $maxWarns,
        int $autoDeleteAdminMaxWarns,
        int $debugLogs,
        int $hideVipTest,
        string $vipTestGroup,
        string $blockdbApiKey,
        int $defaultAllServers
    ): array {
        $current = $this->FileRepository->get('settings');

        if ($maxWarns < 1) {
            $maxWarns = (int) ($current['max_warns'] ?? 0);
        }

        if ($maxWarns < 1) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgMaxWarnsMin')];
        }

        $this->FileRepository->update('settings', function (array $data) use (
            $maxWarns,
            $autoDeleteAdminMaxWarns,
            $debugLogs,
            $hideVipTest,
            $vipTestGroup,
            $blockdbApiKey,
            $defaultAllServers
        ): array {
            $data['max_warns'] = $maxWarns;
            $data['auto_delete_admin_max_warns'] = $autoDeleteAdminMaxWarns ? 1 : 0;
            $data['debug_logs'] = $debugLogs ? 1 : 0;
            $data['hide_vip_test'] = $hideVipTest ? 1 : 0;
            $data['vip_test_group'] = trim($vipTestGroup);
            $data['blockdb_api_key'] = trim($blockdbApiKey);
            $data['default_all_servers'] = $defaultAllServers ? 1 : 0;

            return $data;
        });

        return ['status' => 'success'];
    }

    public function deleteGroup(int $id): array
    {
        return $this->deleteById('groups', $id, '_at_msgGroupNotFound', '_at_msgGroupDeleted');
    }

    public function updateGroup(int $id, array $name, array $permissions): array
    {
        $groups = $this->FileRepository->get('groups');

        if (!isset($groups[$id])) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgGroupNotFound')];
        }

        $name = $this->normalizeName($name);
        $permissions = array_values(array_filter(
            $permissions,
            static fn($permission): bool => is_string($permission) && $permission !== ''
        ));

        if ($name == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyGroupName')];
        }

        if ($permissions == []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSelectAccessFlag')];
        }

        $this->FileRepository->update('groups', function (array $data) use ($id, $name, $permissions): array {
            $data[$id]['name'] = $name;
            $data[$id]['permissions'] = $permissions;

            return $data;
        });

        return ['status' => 'success'];
    }

    private function normalizeName(array $name): array
    {
        $out = [];

        foreach ($name as $lang => $value) {
            if (!is_string($lang)) {
                continue;
            }

            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            $out[strtolower($lang)] = $value;
        }

        return $out;
    }

    private function nextId(array $data): string
    {
        $max = -1;

        foreach (array_keys($data) as $key) {
            if (is_numeric($key) && (int) $key > $max) {
                $max = (int) $key;
            }
        }

        return (string) ($max + 1);
    }

    private function deleteById(string $fileKey, int $id, string $notFoundKey, string $successKey): array
    {
        $items = $this->FileRepository->get($fileKey);

        if (!isset($items[$id])) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, $notFoundKey)];
        }

        $this->FileRepository->update($fileKey, function (array $data) use ($id): array {
            unset($data[$id]);

            return $data;
        });

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, $successKey)];
    }

    public function importFromManagerSystem(object $General): array
    {
        if (!file_exists(MODULES . 'module_page_managersystem/description.json')) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgMsNotFound')];
        }

        $language = strtolower((string) ($General->arr_general['language'] ?? 'RU'));
        if ($language === '') {
            $language = 'ru';
        }

        $localized = static function (string $value) use ($language): array {
            $value = trim($value);
            if ($value === '') {
                return [];
            }

            return [$language => $value];
        };

        $msSettings = $this->loadManagerSystemCache('settings');
        $currentSettings = $this->FileRepository->get('settings');
        $vipTestGroup = trim((string) ($msSettings['group_test'] ?? ''));

        $settings = [
            'max_warns' => max(1, (int) ($msSettings['count_warn'] ?? ($currentSettings['max_warns'] ?? 3))),
            'auto_delete_admin_max_warns' => (int) ($msSettings['warn_auto_del'] ?? 0) ? 1 : 0,
            'debug_logs' => (int) ($currentSettings['debug_logs'] ?? 0),
            'hide_vip_test' => $vipTestGroup !== '' ? 1 : 0,
            'vip_test_group' => $vipTestGroup,
            'blockdb_api_key' => trim((string) ($currentSettings['blockdb_api_key'] ?? '')),
            'default_all_servers' => (int) ($msSettings['add_punishment_all'] ?? ($currentSettings['default_all_servers'] ?? 0)) ? 1 : 0,
        ];

        $reasons = [];
        $reasonId = 0;
        foreach ($this->loadManagerSystemCache('reasonban') as $item) {
            $name = $localized((string) ($item['reason_name'] ?? ''));
            if ($name === []) {
                continue;
            }

            $reasons[(string) ++$reasonId] = [
                'name' => $name,
                'type' => 'ban',
            ];
        }
        foreach ($this->loadManagerSystemCache('reasonmute') as $item) {
            $name = $localized((string) ($item['reason_name'] ?? ''));
            if ($name === []) {
                continue;
            }

            $reasons[(string) ++$reasonId] = [
                'name' => $name,
                'type' => 'mute',
            ];
        }

        $terms = [];
        $termId = 0;
        foreach ($this->loadManagerSystemCache('punishmenttime') as $item) {
            $name = $localized((string) ($item['name_time'] ?? ''));
            if ($name === []) {
                continue;
            }

            $terms[(string) ++$termId] = [
                'name' => $name,
                'time' => (int) ($item['duration'] ?? 0),
                'type' => 'punishments',
            ];
        }
        foreach ($this->loadManagerSystemCache('privilegestime') as $item) {
            $name = $localized((string) ($item['name_time'] ?? ''));
            if ($name === []) {
                continue;
            }

            $time = (int) ($item['duration'] ?? 0);
            foreach (['admins', 'vips'] as $type) {
                $terms[(string) ++$termId] = [
                    'name' => $name,
                    'time' => $time,
                    'type' => $type,
                ];
            }
        }

        $vipGroups = [];
        $vipGroupId = 0;
        foreach ($this->loadManagerSystemCache('vipgroup') as $item) {
            $ini = trim((string) ($item['name_group'] ?? ''));
            if ($ini === '') {
                continue;
            }

            $vipGroups[(string) ++$vipGroupId] = [
                'ini' => $ini,
                'display' => $localized($ini),
            ];
        }

        $this->FileRepository->update('settings', static fn(): array => $settings);
        $this->FileRepository->update('reasons', static fn(): array => $reasons);
        $this->FileRepository->update('terms', static fn(): array => $terms);
        $this->FileRepository->update('vip_groups', static fn(): array => $vipGroups);

        return [
            'status' => 'success',
            'message' => sprintf(
                ModuleHelper::phrase($this->Translate, '_at_msgImportDone'),
                count($reasons),
                count($terms),
                count($vipGroups),
                strtoupper($language)
            ),
        ];
    }

    private function loadManagerSystemCache(string $file): array
    {
        $path = MODULES . 'module_page_managersystem/assets/cache/' . $file . '.php';
        if (!file_exists($path)) {
            return [];
        }

        $data = require $path;

        return is_array($data) ? $data : [];
    }
}
