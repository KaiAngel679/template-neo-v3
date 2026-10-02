<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\DatabaseService;

class DatabaseController
{
    private $service;

    public function __construct(object $Db, object $Translate)
    {
        $this->service = new DatabaseService($Db, $Translate);
    }

    public function createTables(): void
    {
        $this->service->createTables($_SESSION['steamid']);
    }

    public function hasWelcomeModalSeen(int $steamid): bool
    {
        return $this->service->hasWelcomeModalSeen($steamid);
    }

    public function dismissWelcomeModal(int $steamid): array
    {
        $this->service->markWelcomeModalSeen($steamid);

        return ['status' => 'success'];
    }

    public function getGroupsGame(string $type): ?array
    {
        return $this->service->getGroupsGame($type);
    }

    public function listAdminGroups(): array
    {
        return ['status' => 'success', 'data' => $this->service->listAdminGroups()];
    }

    public function hasCsgoAdminBackend(): bool
    {
        return $this->service->hasCsgoAdminBackend();
    }

    public function hasCs2AdminBackend(): bool
    {
        return $this->service->hasCs2AdminBackend();
    }

    public function createAdminGroup(string $type, string $name, int $immunity, string $flags): array
    {
        return $this->service->createAdminGroup($type, $name, $immunity, $flags);
    }

    public function updateAdminGroup(string $type, int $id, string $name, int $immunity, string $flags): array
    {
        return $this->service->updateAdminGroup($type, $id, $name, $immunity, $flags);
    }

    public function deleteAdminGroup(string $type, int $id): array
    {
        return $this->service->deleteAdminGroup($type, $id);
    }

    public function getAdminsCount(string $type, array $servers, int $group, string $search = ''): int
    {
        return $this->service->getAdminsCount($type, $servers, $group, $search);
    }

    public function getPunishmentsCount(string $type, array $filters): int
    {
        return $this->service->getPunishmentsCount($type, $filters);
    }

    public function getChecksCount(array $filters): int
    {
        return $this->service->getChecksCount($filters);
    }
}
