<?php

namespace app\modules\module_page_atools\ext\Formatters;

use app\modules\module_page_atools\ext\Repositories\FileRepository;
use app\modules\module_page_atools\ext\Repositories\LanguageRepository;

final class PanelPermissionFormatter
{
    private const PHRASE_MAP = [
        'admins.view' => '_at_watchAdmins',
        'admins.create' => '_at_addingAdmins',
        'admins.delete' => '_at_deletingAdmins',
        'admins.update' => '_at_changingAdmins',
        'admins.warn.give' => '_at_addingWarns',
        'admins.warn.remove' => '_at_removingWarns',
        'admins.warn.delete' => '_at_deletingWarns',
        'admins.warn.update' => '_at_changingWarns',
        'punishments.view' => '_at_watchPunishments',
        'bans.create' => '_at_addingBans',
        'bans.unban' => '_at_removingBans',
        'bans.delete' => '_at_deletingBans',
        'bans.update' => '_at_changingBans',
        'mutes.create' => '_at_addingMutes',
        'mutes.unmute' => '_at_removingMutes',
        'mutes.delete' => '_at_deletingMutes',
        'mutes.update' => '_at_changingMutes',
        'checks.view' => '_at_watchChecks',
        'checks.delete' => '_at_deletingChecks',
        'finances.view' => '_at_watchFinances',
        'finances.update' => '_at_changingFinances',
        'finances.reset' => '_at_resettingFinances',
        'privileges.view' => '_at_watchVIPs',
        'privileges.create' => '_at_addingVIPs',
        'privileges.delete' => '_at_deletingVIPs',
        'privileges.update' => '_at_changingVIPs',
        'credits.view' => '_at_watchCredits',
        'credits.update' => '_at_changingCredits',
        'credits.reset' => '_at_viperResettingCredits',
        'experience.view' => '_at_watchExperience',
        'experience.update' => '_at_changingExperience',
        'experience.reset' => '_at_viperResettingExperience',
        'logs.view' => '_at_watchLogs',
        'logs.delete' => '_at_deletingLogs',
    ];

    private $FileRepository;
    private $LanguageRepository;
    private $Translate;

    public function __construct(object $Translate, ?FileRepository $fileRepository = null, ?LanguageRepository $languageRepository = null)
    {
        $this->Translate = $Translate;
        $this->FileRepository = $fileRepository ?? new FileRepository();
        $this->LanguageRepository = $languageRepository ?? new LanguageRepository();
    }

    public function resolveKeys(array $permissions): array
    {
        if (isset($permissions['group']) && $permissions['group'] !== '' && $permissions['group'] !== null) {
            $groups = $this->FileRepository->get('groups');
            $groupId = (string) $permissions['group'];

            return $groups[$groupId]['permissions'] ?? [];
        }

        if (!empty($permissions['flags']) && is_array($permissions['flags'])) {
            return array_values($permissions['flags']);
        }

        return [];
    }

    public function resolveAccessGroupName(array $permissions): ?string
    {
        if (!isset($permissions['group']) || $permissions['group'] === '' || $permissions['group'] === null) {
            return null;
        }

        $groups = $this->FileRepository->get('groups');
        $groupId = (string) $permissions['group'];
        if (empty($groups[$groupId]['name'])) {
            return null;
        }

        return $this->LanguageRepository->translate($groups[$groupId]['name']);
    }

    public function resolveAccessGroupId(array $permissionKeys): ?string
    {
        $normalized = $this->normalizePermissionKeys($permissionKeys);
        if ($normalized === []) {
            return null;
        }

        foreach ($this->FileRepository->get('groups') as $id => $group) {
            $groupPerms = $this->normalizePermissionKeys($group['permissions'] ?? []);
            if ($groupPerms === $normalized) {
                return (string) $id;
            }
        }

        return null;
    }

    public function labelsForLog(array $permissions): array
    {
        $labels = [];
        foreach ($this->resolveKeys($permissions) as $permission) {
            $label = $this->label((string) $permission);
            if ($label !== '') {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    public function label(string $permission): string
    {
        if (!isset(self::PHRASE_MAP[$permission])) {
            return $permission;
        }

        return $this->Translate->get_translate_module_phrase('module_page_atools', self::PHRASE_MAP[$permission]);
    }

    public function isValidPermission(string $permission): bool
    {
        return isset(self::PHRASE_MAP[$permission]);
    }

    private function normalizePermissionKeys(array $keys): array
    {
        $keys = array_values(array_unique(array_map('strval', $keys)));
        sort($keys);

        return $keys;
    }
}
