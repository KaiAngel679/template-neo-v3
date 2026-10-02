<?php

namespace app\modules\module_page_results\ext\Repository;

class AccessRepository extends BaseRepository
{
  public $adminRepository;

  public $settingsRepository;

  public function __construct($Db)
  {
    $this->adminRepository = new AdminRepository($Db);
    $this->settingsRepository = new SettingsRepository();
  }
  public function getAll(): array
  {
    return $this->loadFromJson('main', 'accesses') ?: [];
  }

  public function getById(int $id): array
  {
    $accesses = $this->getAll();
    foreach ($accesses as $access) {
      if ((int)($access['id'] ?? 0) === $id) {
        return $access;
      }
    }
    return [];
  }

  public function getBySteamId(string $steamid): array
  {
    $accesses = $this->getAll();
    foreach ($accesses as $access) {
      if (($access['steamid'] ?? '') == $steamid) {
        return $access;
      }
    }
    return [];
  }

  public function add(array $data): bool
  {
    $accesses = $this->getAll();
    $data['id'] = $this->getNextId($accesses);
    $data['date_added'] = time();
    $accesses[] = $data;
    return $this->saveToJson('main', 'accesses', $accesses);
  }

  public function delete(int $id): bool
  {
    $accesses = $this->getAll();
    $accesses = array_filter($accesses, function ($access) use ($id) {
      return (int)($access['id'] ?? 0) !== $id;
    });
    return $this->saveToJson('main', 'accesses', array_values($accesses));
  }

  public function existsBySteamId(string $steamid): bool
  {
    return !empty($this->getBySteamId($steamid));
  }

  public function checkAccess(string $steamid, array $permissions = []): ?array
  {
    if (isset($_SESSION['user_admin'])) return ['full' => true];
    $access = $this->getBySteamId($steamid);
    $settings = $this->settingsRepository->getSettings();
    $admins = $this->adminRepository->getAll();
    $admin = null;
    $result = [];
    foreach ($admins as $a) {
      if (($a['steam'] ?? '') === $steamid) {
        $admin = $a;
        break;
      }
    }
    if (!empty($access)) {
      if (empty($permissions)) {
        $allPermissions = ['awardwarns', 'results', 'warns', 'admins', 'full'];
        foreach ($allPermissions as $permission) {
          if (!empty($access[$permission])) {
            $result[$permission] = true;
          }
        }
      } else {
        if (!empty($access['full'])) {
          $result['full'] = true;
        }
        foreach ($permissions as $permission) {
          if (!empty($access[$permission])) {
            $result[$permission] = true;
          }
        }
      }
    }

    if (!empty($settings['only_theirs']) && !empty($admin) && empty($result)) {
      $result['theirs'] = true;
    }

    return $result ?: null;
  }
}
