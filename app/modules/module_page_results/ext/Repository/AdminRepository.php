<?php

namespace app\modules\module_page_results\ext\Repository;



class AdminRepository extends BaseRepository
{

  public $Db;
  public function __construct($Db)
  {
    $this->Db = $Db;
  }

  public function getAdmins()
  {
    if (!empty($this->Db->db_data['AdminSystem'])) {
      return $this->Db->queryAll(
        'AdminSystem',
        0,
        0,
        "SELECT 
          `as_admins`.`steamid`, 
          `as_admins_servers`.`group_id` AS `group`,
          GROUP_CONCAT(DISTINCT `as_admins_servers`.`server_id`) AS `server_id`
        FROM `as_admins`
        JOIN `as_admins_servers` ON `as_admins`.`id` = `as_admins_servers`.`admin_id`
        WHERE (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
        GROUP BY `as_admins`.`id`, `as_admins_servers`.`group_id`"
      );
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      return $this->Db->queryAll(
        'IksAdminNew',
        0,
        0,
        "SELECT 
          `iks_admins`.`steam_id` AS `steamid`, 
          `iks_admins`.`group_id` AS `group`,
          GROUP_CONCAT(DISTINCT `iks_admin_to_server`.`server_id`) AS `server_id`
        FROM `iks_admins`
        LEFT JOIN `iks_admin_to_server` ON `iks_admins`.`id` = `iks_admin_to_server`.`admin_id`
        WHERE (`iks_admins`.`end_at` > UNIX_TIMESTAMP() OR `iks_admins`.`end_at` IS NULL) 
          AND `iks_admins`.`is_disabled` = 0 
          AND `iks_admins`.`steam_id` != 'CONSOLE' 
          AND `iks_admins`.`deleted_at` IS NULL
        GROUP BY `iks_admins`.`id`"
      );
    } else {
      return [];
    }
  }
  public function getAll(): array
  {
    return $this->loadFromJson('main', 'admins') ?: [];
  }

  public function getById(int $id): array
  {
    $admins = $this->getAll();
    foreach ($admins as $admin) {
      if ((int)($admin['id'] ?? 0) === $id) {
        return $admin;
      }
    }
    return [];
  }

  public function getBySteam(string $steam): array
  {
    $admins = $this->getAll();
    foreach ($admins as $admin) {
      if (($admin['steam'] ?? '') === $steam) {
        return $admin;
      }
    }
    return [];
  }

  public function getBySteamArray(string $steam): array
  {
    $admins = $this->getAll();
    foreach ($admins as $admin) {
      if (($admin['steam'] ?? '') === $steam) {
        return [$admin];
      }
    }
    return [];
  }

  public function add(array $data): bool
  {
    $admins = $this->getAll();
    $data['id'] = $this->getNextId($admins);
    $data['date_added'] = time();
    $data['date_deleted'] = 0;
    $admins[] = $data;
    return $this->saveToJson('main', 'admins', $admins);
  }

  public function update(int $id, array $data): bool
  {
    $admins = $this->getAll();
    foreach ($admins as &$admin) {
      if ((int)($admin['id'] ?? 0) === $id) {
        $admin = array_merge($admin, $data);
        return $this->saveToJson('main', 'admins', $admins);
      }
    }
    return false;
  }

  public function delete(int $id): bool
  {
    $admins = $this->getAll();
    $found = false;
    foreach ($admins as $key => $admin) {
      if ((int)($admin['id'] ?? 0) === $id) {
        unset($admins[$key]);
        $found = true;
        break;
      }
    }
    if (!$found) {
      return false;
    }
    return $this->saveToJson('main', 'admins', array_values($admins));
  }

  public function exists(int $id): bool
  {
    return !empty($this->getById($id));
  }

  public function existsBySteam(string $steam): bool
  {
    $admins = $this->getAll();
    foreach ($admins as $admin) {
      if (($admin['steam'] ?? '') === $steam) {
        return true;
      }
    }
    return false;
  }

  public function getGroups(): array
  {
    if (!empty($this->Db->db_data['AdminSystem'])) {
      return $this->Db->queryAll('AdminSystem', 0, 0, 'SELECT * FROM `as_groups` ORDER BY immunity ASC') ?: [];
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      return $this->Db->queryAll('IksAdminNew', 0, 0, 'SELECT * FROM `iks_groups` ORDER BY immunity ASC') ?: [];
    }
    return [];
  }
}
