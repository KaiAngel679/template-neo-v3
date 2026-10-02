<?php

namespace app\modules\module_page_results\ext\Service;

use app\modules\module_page_results\ext\Repository\AdminRepository;
use app\modules\module_page_results\ext\Repository\ServerRepository;

class AdminService
{
  private $adminRepository;
  private $serverRepository;
  private $General;
  private $Translate;

  public function __construct(
    $General,
    $Translate,
    $Db
  ) {
    $this->adminRepository = new AdminRepository($Db);
    $this->serverRepository = new ServerRepository();
    $this->General = $General;
    $this->Translate = $Translate;
  }

  public function renderAdmins(array $postData): array
  {
    $perPage = 10;
    $page = (int)($postData['page'] ?? 1);
    $server = Helper::normalizeArrayFilter($postData['server'] ?? []);
    $group = Helper::normalizeArrayFilter($postData['group'] ?? []);
    $searchAdmin = trim($postData['searchAdmin'] ?? '');

    $admins = $this->adminRepository->getAll();
    $groups = $this->adminRepository->getGroups();

    $groupsMap = [];
    foreach ($groups as $g) {
      $groupsMap[(string)$g['id']] = $g['name'];
    }

    $serversIdMap = $this->serverRepository->getIdMap();

    $filtered = [];
    foreach ($admins as $admin) {
      if (!Helper::matchesArrayFilter($server, explode(';', $admin['server'] ?? ''))) continue;
      if (!Helper::matchesArrayFilter($group, [$admin['group'] ?? ''])) continue;

      if ($searchAdmin !== '') {
        $name = $this->General->checkName($admin['steam']);
        if (stripos($name, $searchAdmin) === false && stripos($admin['steam'], $searchAdmin) === false) continue;
      }

      $adminServers = array_filter(array_map('trim', explode(';', (string)($admin['server'] ?? ''))));
      $serverNames = [];
      foreach ($adminServers as $adminServerId) {
        $serverData = $serversIdMap[(int)$adminServerId] ?? null;
        if ($serverData) {
          $serverNames[] = $serverData['name'] ?? '';
        }
      }

      $filtered[] = [
        'id' => $admin['id'],
        'steam' => $admin['steam'],
        'name' => $this->General->checkName($admin['steam']),
        'checked_avatar' => $this->General->checkAvatar($admin['steam']),
        'discord' => $admin['discord'] ?? '',
        'telegram' => $admin['telegram'] ?? '',
        'group' => $groupsMap[(string)($admin['group'] ?? '')] ?? '',
        'server' => implode(', ', $serverNames),
        'date_added' => isset($admin['date_added']) ? date('d.m.Y H:i', $admin['date_added']) : '',
        'added_by' => $admin['added_by'] ?? '',
        'added_by_name' => $this->General->checkName($admin['added_by'] ?? ''),
      ];
    }

    $total = count($filtered);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));

    return [
      'data' => array_slice($filtered, ($page - 1) * $perPage, $perPage),
      'page_max' => $totalPages,
    ];
  }

  public function renderAdmin(int $id): array
  {
    if ($id <= 0) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_adminNotFound')];
    }

    $admin = $this->adminRepository->getById($id);
    if (empty($admin)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_adminNotFound')];
    }

    return [
      'id' => $admin['id'],
      'steam' => $admin['steam'],
      'discord' => $admin['discord'] ?? '',
      'telegram' => $admin['telegram'] ?? '',
      'group' => $admin['group'],
      'server' => explode(';', $admin['server'] ?? ''),
    ];
  }

  public function addAdmin(array $data): array
  {
    $steam = trim(con_steam64($data['steam'] ?? ''));
    $group = $data['group'] ?? '';
    $serverRaw = $data['server'] ?? '';
    $server = is_array($serverRaw) ? implode(';', $serverRaw) : (string)$serverRaw;
    $discord = trim($data['discord'] ?? '');
    $telegram = trim($data['telegram'] ?? '');

    if (empty($steam) || empty($group) || empty($server)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_fillAllFields')];
    }

    if ($this->adminRepository->existsBySteam($steam)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_AdminAlreadyExists')];
    }

    $this->adminRepository->add([
      'steam' => $steam,
      'group' => $group,
      'server' => $server,
      'discord' => $discord,
      'telegram' => $telegram,
      'added_by' => $_SESSION['steamid64'] ?? '',
      'date_added' => time()
    ]);

    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_AdminAdded')];
  }

  public function editAdmin(array $data): array
  {
    $id = (int)($data['id'] ?? 0);
    $steam = trim(con_steam64($data['steam'] ?? ''));
    $group = $data['group'] ?? '';
    $serverRaw = $data['server'] ?? '';
    $server = is_array($serverRaw) ? implode(';', $serverRaw) : (string)$serverRaw;
    $discord = trim($data['discord'] ?? '');
    $telegram = trim($data['telegram'] ?? '');

    if ($id <= 0 || empty($steam)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_AdminNotFound')];
    }

    $this->adminRepository->update($id, [
      'steam' => $steam,
      'group' => $group,
      'server' => $server,
      'discord' => $discord,
      'telegram' => $telegram,
    ]);

    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_AdminUpdated')];
  }

  public function deleteAdmin(array $data): array
  {
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_InvalidData')];
    }

    $this->adminRepository->delete($id);
    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_AdminDeleted')];
  }

  public function importAdmins($post): array
  {
    $dbAdmins = $this->adminRepository->getAdmins();

    $uniqueAdmins = [];
    $seenSteamIds = [];
    foreach ($dbAdmins as $admin) {
      $steamId = $admin['steamid'] ?? '';
      if (!isset($seenSteamIds[$steamId])) {
        $uniqueAdmins[] = $admin;
        $seenSteamIds[$steamId] = true;
      }
    }
    $dbAdmins = $uniqueAdmins;

    $existingAdmins = $this->adminRepository->getAll();
    $skipGroups = $post['group'] ?? [];
    $ignoreIssetAdmins = !empty($post['ignoreIssetAdmins']);

    $existingSteamMap = [];
    $existingAdminsFull = [];
    foreach ($existingAdmins as $admin) {
      $existingSteamMap[$admin['steam']] = $admin['id'];
      $existingAdminsFull[$admin['steam']] = $admin;
    }

    $servers = $this->serverRepository->getAll();
    if (empty($servers)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_noServersForImport')];
    }
    $allServerIds = [];
    $sidMap = [];
    foreach ($servers as $server) {
      $allServerIds[] = (string)$server['id'];
      $sidMap[(string)($server['sid'] ?? $server['id'])] = (string)$server['id'];
    }
    $allServersStr = implode(';', $allServerIds);

    $imported = [];
    $skipped = 0;
    $skipReasons = [];
    $processedSteam = [];

    foreach ($dbAdmins as $admin) {
      $steamId = $admin['steamid'] ?? '';
      $group = $admin['group'] ?? '1';

      if (isset($processedSteam[$steamId])) {
        $skipped++;
        $skipReasons[] = "Duplicate: $steamId (group: $group)";
        continue;
      }

      if (!empty($skipGroups) && in_array($group, (array)$skipGroups, true)) {
        $skipped++;
        $skipReasons[] = "Excluded group: $steamId (group: $group)";
        continue;
      }

      $isExisting = isset($existingSteamMap[$steamId]);
      if ($isExisting && !$ignoreIssetAdmins) {
        $skipped++;
        $skipReasons[] = "Already exists: $steamId (group: $group)";
        continue;
      }

      $processedSteam[$steamId] = true;

      $serverIds = $admin['server_id'] ?? '';
      if (empty($serverIds) || $serverIds === '-1') {
        $serverIds = $allServersStr;
      } else {
        $dbServerIds = array_filter(array_map('trim', explode(',', (string)$serverIds)));
        $mappedServerIds = [];
        foreach ($dbServerIds as $dbSid) {
          if (isset($sidMap[$dbSid])) {
            $mappedServerIds[] = $sidMap[$dbSid];
          }
        }
        $serverIds = !empty($mappedServerIds) ? implode(';', $mappedServerIds) : $allServersStr;
      }

      if ($isExisting && $ignoreIssetAdmins) {
        $adminId = $existingSteamMap[$steamId];
        $existingAdmin = $existingAdminsFull[$steamId];

        $updateData = [
          'steam' => $steamId,
          'group' => $group,
          'server' => $serverIds,
          'discord' => $existingAdmin['discord'] ?? '',
          'telegram' => $existingAdmin['telegram'] ?? '',
          'date_updated' => time(),
        ];
        $this->adminRepository->update($adminId, $updateData);
        $imported[] = $updateData;
      } else {
        $adminData = [
          'steam' => $steamId,
          'group' => $group,
          'server' => $serverIds,
          'discord' => '',
          'telegram' => '',
          'added_by' => $_SESSION['steamid64'] ?? '',
          'date_added' => time()
        ];
        $this->adminRepository->add($adminData);
        $imported[] = $adminData;
      }
    }

    return [
      'success' => sprintf(
        $this->Translate->get_translate_module_phrase('module_page_results', '_importMessage'),
        count($imported),
        $skipped
      ),
      'skip_reasons' => $skipReasons
    ];
  }

  public function getGroups(): array
  {
    return $this->adminRepository->getGroups();
  }
}
