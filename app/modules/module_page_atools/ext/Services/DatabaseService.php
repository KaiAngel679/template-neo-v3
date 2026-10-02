<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\AdminRepository;
use app\modules\module_page_atools\ext\Repositories\DatabaseRepository;

class DatabaseService
{
    private $repository, $adminRepository, $Translate;

    public function __construct(object $Db, object $Translate)
    {
        $this->repository = new DatabaseRepository($Db);
        $this->adminRepository = new AdminRepository($Db);
        $this->Translate = $Translate;
    }

    public function createTables(int $steamid): void
    {
        $this->repository->createTables($steamid);
    }

    public function hasWelcomeModalSeen(int $steamid): bool
    {
        return $this->repository->hasWelcomeModalSeen($steamid);
    }

    public function markWelcomeModalSeen(int $steamid): void
    {
        $this->repository->markWelcomeModalSeen($steamid);
    }

    public function getGroupsGame(string $type): ?array
    {
        return $this->adminRepository->getGroupsGame($type);
    }

    public function listAdminGroups(): array
    {
        return $this->adminRepository->getAllAdminGroups();
    }

    public function hasCsgoAdminBackend(): bool
    {
        return $this->adminRepository->hasCsgoAdminBackend();
    }

    public function hasCs2AdminBackend(): bool
    {
        return $this->adminRepository->hasCs2AdminBackend();
    }

    public function createAdminGroup(string $type, string $name, int $immunity, string $flags): array
    {
        $name = trim($name);

        if ($name === '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyAdminGroupName')];
        }

        if ($immunity < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgImmunityNegative')];
        }

        if (!$this->hasAdminBackend($type)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminBackendNotConnected')];
        }

        $this->adminRepository->createAdminGroup($type, $name, $immunity, $flags);

        return ['status' => 'success'];
    }

    public function updateAdminGroup(string $type, int $id, string $name, int $immunity, string $flags): array
    {
        $name = trim($name);

        if ($id <= 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgGroupNotFound')];
        }

        if ($name === '') {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyAdminGroupName')];
        }

        if ($immunity < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgImmunityNegative')];
        }

        if (!$this->hasAdminBackend($type)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminBackendNotConnected')];
        }

        $this->adminRepository->updateAdminGroup($type, $id, $name, $immunity, $flags);

        return ['status' => 'success'];
    }

    public function deleteAdminGroup(string $type, int $id): array
    {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgGroupNotFound')];
        }

        if (!$this->hasAdminBackend($type)) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgAdminBackendNotConnected')];
        }

        $this->adminRepository->deleteAdminGroup($type, $id);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgGroupDeleted')];
    }

    private function hasAdminBackend(string $type): bool
    {
        if ($type === 'csgo') {
            return $this->adminRepository->hasCsgoAdminBackend();
        }

        if ($type === 'cs2') {
            return $this->adminRepository->hasCs2AdminBackend();
        }

        return false;
    }

    public function getAdminsCount(string $type, array $servers, int $group, string $search = ''): int
    {
        return $this->adminRepository->getAdminsCount($type, $servers, $group, $search);
    }

    public function getPunishmentsCount(string $type, array $filters): int
    {
        return $this->adminRepository->getPunishmentsCount($type, $filters);
    }

    public function getChecksCount(array $filters): int
    {
        return $this->adminRepository->getChecksCount($filters);
    }
}
