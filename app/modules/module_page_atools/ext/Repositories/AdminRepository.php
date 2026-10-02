<?php

namespace app\modules\module_page_atools\ext\Repositories;

use app\modules\module_page_atools\ext\Repositories\AdminDrivers\AdminDriverFactory;
use app\modules\module_page_atools\ext\Repositories\AdminDrivers\AdminDriverInterface;
use app\modules\module_page_atools\ext\Repositories\AdminDrivers\IksAdminDriver;
use app\modules\module_page_atools\ext\Repositories\AdminDrivers\SourceBansAdminDriver;

class AdminRepository
{
    private $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function getGroupsGame(string $type): ?array
    {
        if ($type !== 'cs2' && $type !== 'csgo') {
            return [];
        }

        $driver = $this->driver($type);

        return $driver ? $driver->getGroups() : [];
    }

    public function getAllAdminGroups(): array
    {
        $out = [];

        $cs2Driver = $this->driver('cs2');
        if ($cs2Driver !== null) {
            foreach ($cs2Driver->getGroups() as $row) {
                $out[] = $this->normalizeAdminGroupRow($row, 'cs2');
            }
        }

        if (!empty($this->Db->db_data['SourceBans'])) {
            $driver = $this->driver('csgo');
            if ($driver !== null) {
                foreach ($driver->getGroups() as $row) {
                    $out[] = $this->normalizeAdminGroupRow($row, 'csgo');
                }
            }
        }

        return $out;
    }

    public function hasCsgoAdminBackend(): bool
    {
        return !empty($this->Db->db_data['SourceBans']);
    }

    public function hasCs2AdminBackend(): bool
    {
        return $this->driver('cs2') !== null;
    }

    public function createAdminGroup(string $type, string $name, int $immunity, string $flags): void
    {
        $driver = $this->driver($type);
        if ($driver === null) {
            return;
        }

        $driver->createAdminGroup($name, $immunity, $this->normalizeAdminGroupFlags($flags));
    }

    public function updateAdminGroup(string $type, int $id, string $name, int $immunity, string $flags): void
    {
        $driver = $this->driver($type);
        if ($driver === null) {
            return;
        }

        $driver->updateAdminGroup($id, $name, $immunity, $this->normalizeAdminGroupFlags($flags));
    }

    public function deleteAdminGroup(string $type, int $id): void
    {
        $driver = $this->driver($type);
        if ($driver === null) {
            return;
        }

        $driver->deleteAdminGroup($id);
    }

    public function issetAdmin(string $steamid, string $type): bool
    {
        $driver = $this->driver($type);

        return $driver ? $driver->issetAdmin($steamid) : false;
    }

    public function createAdmin(string $steamid, string $type, string $name, int $group, int $expire, array $servers): void
    {
        $driver = $this->driver($type);
        if ($driver != null) {
            $driver->createAdmin($steamid, $name, $group, $expire, $servers);
        }
    }

    public function getAdminsList(string $type, array $servers, int $group, int $limit = 10, int $offset = 0, string $search = '', ?array $allowedSteamids = null): array
    {
        $driver = $this->driver($type);

        return $driver ? $driver->getAdminsList($servers, $group, $limit, $offset, $search, $allowedSteamids) : [];
    }

    public function getListAdmins($type)
    {
        $driver = $this->driver($type);

        return $driver ? $driver->getListAdmins() : [];
    }

    public function getAdminsCount(string $type, array $servers, int $group, string $search = '', ?array $allowedSteamids = null): int
    {
        $driver = $this->driver($type);

        return $driver ? $driver->getAdminsCount($servers, $group, $search, $allowedSteamids) : 0;
    }

    public function getAdminServers(int $adminId, string $type): array
    {
        $driver = $this->driver($type);

        return $driver ? $driver->getAdminServers($adminId) : [];
    }

    public function deleteAdmin(int $adminId, string $type): void
    {
        $driver = $this->driver($type);
        if ($driver !== null) {
            $driver->deleteAdmin($adminId);
        }
    }

    public function getAdminById(int $adminId, string $type): ?array
    {
        $driver = $this->driver($type);

        return $driver ? $driver->getAdminById($adminId) : null;
    }

    public function findAdminByTargetSteamid(string $steamid64, ?string $preferredType = null): ?array
    {
        $types = [];
        if ($preferredType === 'cs2' || $preferredType === 'csgo') {
            $types[] = $preferredType;
        }
        foreach (['cs2', 'csgo'] as $type) {
            if (!in_array($type, $types, true)) {
                $types[] = $type;
            }
        }

        $steamid64 = con_steam64($steamid64);

        foreach ($types as $type) {
            $driver = $this->driver($type);
            if ($driver === null) {
                continue;
            }

            $dbSteamid = $type === 'csgo' ? con_steam32($steamid64) : $steamid64;
            if (!$driver->issetAdmin($dbSteamid)) {
                continue;
            }

            $rows = $driver->getAdminsList([-1], -1, 1, 0, $steamid64);
            if (!empty($rows[0]['id'])) {
                return [
                    'id' => (int) $rows[0]['id'],
                    'type' => $type,
                ];
            }
        }

        return null;
    }

    public function updateAdmin(int $adminId, string $type, string $group, string $expire, array $servers): void
    {
        $driver = $this->driver($type);
        if ($driver != null) {
            $driver->updateAdmin($adminId, $group, $expire, $servers);
        }
    }

    public function createPunishment(string $game, string $steamid, string $name, ?string $ip, int $type, string $reason, int $duration, array $serverIds, string $adminSteamid): void
    {
        $driver = $this->driver($game);
        if ($driver != null) {
            $driver->createPunishment($steamid, $name, $ip, $type, $reason, $duration, $serverIds, $adminSteamid);
        }
    }

    public function getPunishmentsList(string $type, array $filters): array
    {
        $driver = $this->driver($type);

        return $driver ? $driver->getPunishmentsList($filters) : [];
    }

    public function getPunishmentsCount(string $type, array $filters): int
    {
        $driver = $this->driver($type);

        return $driver ? $driver->getPunishmentsCount($filters) : 0;
    }

    public function filterRemovablePunishmentIds(string $type, array $ids): array
    {
        $driver = $this->driver($type);

        return $driver ? $driver->filterRemovablePunishmentIds($ids) : [];
    }

    public function removePunishmentsByIds(string $type, array $ids, string $issuerSteamid64): void
    {
        $driver = $this->driver($type);

        if ($driver != null) {
            $driver->removePunishmentsByIds($ids, $issuerSteamid64);
        }
    }

    public function deletePunishmentsByIds(string $type, array $ids): void
    {
        $driver = $this->driver($type);

        if ($driver != null) {
            $driver->deletePunishmentsByIds($ids);
        }
    }

    public function getPunishmentOffenderSteamidsByIds(string $type, array $ids): array
    {
        $driver = $this->driver($type);

        return $driver ? $driver->getPunishmentOffenderSteamidsByIds($ids) : [];
    }

    public function updatePunishmentById(string $game, int $id, ?string $ip, int $punishType, string $reason, int $duration, array $serverIds): bool
    {
        $driver = $this->driver($game);

        return $driver ? $driver->updatePunishmentById($id, $ip, $punishType, $reason, $duration, $serverIds) : false;
    }

    public function isChecksBackendAvailable(): bool
    {
        return $this->checksDriver() !== null;
    }

    public function isChecksIksBackend(): bool
    {
        return $this->checksDriver() instanceof IksAdminDriver;
    }

    public function getChecksList(array $filters): array
    {
        $driver = $this->checksDriver();

        return $driver ? $driver->getChecksList($filters) : [];
    }

    public function getChecksCount(array $filters): int
    {
        $driver = $this->checksDriver();

        return $driver ? $driver->getChecksCount($filters) : 0;
    }

    public function getChecksByIds(array $ids): array
    {
        $driver = $this->checksDriver();

        return $driver ? $driver->getChecksByIds($ids) : [];
    }

    public function getChecksVerdicts(): array
    {
        $driver = $this->checksDriver();

        return $driver ? $driver->getChecksVerdicts() : [];
    }

    public function deleteChecksByIds(array $ids): void
    {
        $driver = $this->checksDriver();

        if ($driver != null) {
            $driver->deleteChecksByIds($ids);
        }
    }

    public function resolveCheckAdminSteamid(int $adminId): ?string
    {
        $driver = $this->checksDriver();

        return $driver ? $driver->resolveCheckAdminSteamid($adminId) : null;
    }

    public function createSourceBansAdminWarn(int $recipientAid, string $targetSteamid, string $issuerSteamid, int $expires, string $reason): bool
    {
        $driver = $this->driver('csgo');
        if (!$driver instanceof SourceBansAdminDriver) {
            return false;
        }

        return $driver->createAdminWarn($recipientAid, $targetSteamid, $issuerSteamid, $expires, $reason);
    }

    private function driver(string $type): ?AdminDriverInterface
    {
        return AdminDriverFactory::resolve($this->Db, $type);
    }

    private function checksDriver(): ?AdminDriverInterface
    {
        return AdminDriverFactory::resolveChecks($this->Db);
    }

    private function normalizeAdminGroupRow(array $row, string $type): array
    {
        $flags = strtolower(preg_replace('/[^a-z]/', '', (string) ($row['flags'] ?? '')));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'immunity' => (int) ($row['immunity'] ?? 0),
            'flags' => $flags,
            'type' => $type,
        ];
    }

    private function normalizeAdminGroupFlags(string $flags): string
    {
        $letters = [];
        $flags = strtolower($flags);

        for ($i = 0, $len = strlen($flags); $i < $len; $i++) {
            $char = $flags[$i];
            if ($char >= 'a' && $char <= 'z') {
                $letters[$char] = true;
            }
        }

        ksort($letters);

        return implode('', array_keys($letters));
    }
}
