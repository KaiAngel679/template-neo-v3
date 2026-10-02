<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Formatters\ChecksVerdictFormatter;
use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\FileRepository;
use app\modules\module_page_atools\ext\Repositories\LanguageRepository;
use app\modules\module_page_atools\ext\Repositories\AdminRepository;

class RendersService
{
    private $FileRepository, $LanguageRepository, $AdminRepository, $General, $Translate;

    public function __construct(object $Db, object $General, object $Translate)
    {
        $this->FileRepository = new FileRepository();
        $this->LanguageRepository = new LanguageRepository();
        $this->AdminRepository = new AdminRepository($Db);
        $this->General = $General;
        $this->Translate = $Translate;
    }

    public function renderTerms(?string $scope = null): array
    {
        $raw = $this->FileRepository->get('terms');
        $out = [];

        foreach ($raw as $id => $row) {
            if ($scope && ($row['type'] ?? '') !== $scope) {
                continue;
            }

            $out[] = [
                'id' => (int) $id,
                'name' => $this->LanguageRepository->translate($row['name']),
                'name_raw' => $row['name'],
                'type' => $row['type'],
                'time' => (int) $row['time'],
            ];
        }

        return $out;
    }

    public function renderReasons(): array
    {
        $raw = $this->FileRepository->get('reasons');
        $out = [];

        foreach ($raw as $id => $row) {
            $out[] = [
                'id' => (int) $id,
                'name' => $this->LanguageRepository->translate($row['name']),
                'name_raw' => $row['name'],
                'type' => $row['type'],
            ];
        }

        return $out;
    }

    public function hiddenVipTestGroupIni(): ?string
    {
        $settings = $this->FileRepository->get('settings');
        if (empty($settings['hide_vip_test'])) {
            return null;
        }

        $ini = trim((string) ($settings['vip_test_group'] ?? ''));

        return $ini != '' ? $ini : null;
    }

    public function renderVipGroups(bool $excludeHiddenTest = false): array
    {
        $raw = $this->FileRepository->get('vip_groups');
        $hidden = $excludeHiddenTest ? $this->hiddenVipTestGroupIni() : null;
        $out = [];

        foreach ($raw as $id => $row) {
            $ini = (string) ($row['ini'] ?? '');
            if ($hidden != null && $ini === $hidden) {
                continue;
            }

            $displayRaw = $row['display'] ?? [];
            if (is_string($displayRaw)) {
                $displayRaw = ['ru' => $displayRaw];
            }

            $out[] = [
                'id' => (int) $id,
                'ini' => $ini,
                'name' => $this->LanguageRepository->translate($displayRaw),
                'name_raw' => $displayRaw,
            ];
        }

        return $out;
    }

    public function renderGroups(): array
    {
        $raw = $this->FileRepository->get('groups');
        $out = [];
        foreach ($raw as $id => $row) {
            $out[] = [
                'id' => (int) $id,
                'name' => $this->LanguageRepository->translate($row['name']),
                'name_raw' => $row['name'],
                'permissions' => $row['permissions'],
            ];
        }
        return $out;
    }

    public function renderAdmins(string $type): array
    {
        $result = $this->AdminRepository->getListAdmins($type);
        $out = [];
        foreach ($result as $admin) {
            $steamid = (string) ($admin['steamid'] ?? '');
            if ($steamid === '' || $steamid === '0' || $steamid === 'STEAM_ID_SERVER') {
                continue;
            }
            $steamid64 = (string) con_steam64($steamid);
            $name = ModuleHelper::resolveDisplayName($this->General, $steamid64 !== '' && $steamid64 !== '0' ? $steamid64 : $steamid, $admin['name'] ?? null);
            $out[] = [
                'id' => $admin['id'],
                'name' => $name,
            ];
        }
        return $out;
    }

    public function renderVerdicts(): array
    {
        if (!$this->AdminRepository->isChecksBackendAvailable()) {
            return [];
        }

        $isIks = $this->AdminRepository->isChecksIksBackend();
        $out = [];
        $seen = [];

        foreach ($this->AdminRepository->getChecksVerdicts() as $row) {
            $entry = ChecksVerdictFormatter::dropdownEntry($row, $isIks, $this->Translate);
            if ($entry === null) {
                continue;
            }

            if (isset($seen[$entry['id']])) {
                continue;
            }

            $seen[$entry['id']] = true;
            $out[] = $entry;
        }

        usort($out, static fn(array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $out;
    }
}
