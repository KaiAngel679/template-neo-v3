<?php

namespace app\modules\module_page_atools\ext\Repositories\AdminDrivers;

interface AdminDriverInterface
{
    public function getGroups(): array;

    public function createAdminGroup(string $name, int $immunity, string $flags): void;

    public function updateAdminGroup(int $id, string $name, int $immunity, string $flags): void;

    public function deleteAdminGroup(int $id): void;

    public function issetAdmin(string $steamid): bool;

    public function createAdmin(string $steamid, string $name, int $group, int $expire, array $servers): void;

    public function getAdminsList(array $servers, int $group, int $limit, int $offset, string $search, ?array $allowedSteamids = null): array;

    public function getAdminsCount(array $servers, int $group, string $search, ?array $allowedSteamids = null): int;

    public function getAdminServers(int $adminId): array;

    public function deleteAdmin(int $adminId): void;

    public function getAdminById(int $adminId): ?array;

    public function updateAdmin(int $adminId, string $group, string $expire, array $servers): void;

    public function createPunishment(string $targetSteamid, string $targetName, ?string $ip, int $punishType, string $reason, int $durationSeconds, array $serverIds, string $issuerSteamid64): void;

    public function getListAdmins(): array;

    public function getPunishmentsList(array $filters): array;

    public function getPunishmentsCount(array $filters): int;

    public function filterRemovablePunishmentIds(array $ids): array;

    public function removePunishmentsByIds(array $ids, string $issuerSteamid64): void;

    public function deletePunishmentsByIds(array $ids): void;

    public function getPunishmentOffenderSteamidsByIds(array $ids): array;

    public function updatePunishmentById(int $id, ?string $ip, int $punishType, string $reason, int $durationSeconds, array $serverIds): bool;

    public function getChecksList(array $filters): array;

    public function getChecksCount(array $filters): int;

    public function getChecksByIds(array $ids): array;

    public function getChecksVerdicts(): array;

    public function deleteChecksByIds(array $ids): void;

    public function resolveCheckAdminSteamid(int $adminId): ?string;
}
