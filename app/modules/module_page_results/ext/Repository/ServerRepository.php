<?php

namespace app\modules\module_page_results\ext\Repository;

class ServerRepository extends BaseRepository
{
  public function getAll(): array
  {
    return $this->loadFromJson('main', 'servers') ?: [];
  }

  public function getById(int $id): array
  {
    $servers = $this->getAll();
    foreach ($servers as $server) {
      if ((int)($server['id'] ?? 0) === $id) {
        return $server;
      }
    }
    return [];
  }

  public function getBySid(int $sid): array
  {
    $servers = $this->getAll();
    foreach ($servers as $server) {
      if ((int)($server['sid'] ?? 0) === $sid) {
        return $server;
      }
    }
    return [];
  }

  public function getByRid(int $rid): array
  {
    $servers = $this->getAll();
    foreach ($servers as $server) {
      if ((int)($server['rid'] ?? 0) === $rid) {
        return $server;
      }
    }
    return [];
  }

  public function getSidMap(): array
  {
    $servers = $this->getAll();
    $map = [];
    foreach ($servers as $server) {
      $map[(int)($server['sid'] ?? 0)] = $server['name'] ?? '';
    }
    return $map;
  }

  public function getRidMap(): array
  {
    $servers = $this->getAll();
    $map = [];
    foreach ($servers as $server) {
      $map[(int)($server['rid'] ?? 0)] = $server['name'] ?? '';
    }
    return $map;
  }

  public function getIdMap(): array
  {
    $servers = $this->getAll();
    $map = [];
    foreach ($servers as $server) {
      $map[(int)($server['id'] ?? 0)] = $server;
    }
    return $map;
  }

  public function getFilteredSids(array $serverFilter): array
  {
    $sids = [];
    $showAll = empty($serverFilter) || (isset($serverFilter[0]) && $serverFilter[0] === '0');
    $servers = $this->getAll();
    foreach ($servers as $s) {
      if ($showAll || in_array((string)($s['id'] ?? 0), $serverFilter, true)) {
        $sids[] = (int)($s['sid'] ?? 0);
      }
    }
    return array_filter($sids);
  }

  public function getFilteredRids(array $serverFilter): array
  {
    $rids = [];
    $showAll = empty($serverFilter) || (isset($serverFilter[0]) && $serverFilter[0] === '0');
    $servers = $this->getAll();
    foreach ($servers as $s) {
      if ($showAll || in_array((string)($s['id'] ?? 0), $serverFilter, true)) {
        $rids[] = (int)($s['rid'] ?? 0);
      }
    }
    return array_filter($rids);
  }

  public function add(array $data): bool
  {
    $servers = $this->getAll();
    $newServer = [
      'id' => $this->getNextId($servers),
      'name' => trim($data['name'] ?? ''),
      'sid' => (int)($data['sid'] ?? 0),
      'rid' => (int)($data['rid'] ?? 0),
    ];
    $servers[] = $newServer;
    return $this->saveToJson('main', 'servers', $servers);
  }

  public function delete(int $id): bool
  {
    $servers = $this->getAll();
    $updatedServers = array_filter($servers, function ($server) use ($id) {
      return (int)($server['id'] ?? 0) !== $id;
    });
    return $this->saveToJson('main', 'servers', array_values($updatedServers));
  }

  public function exists(int $id): bool
  {
    return !empty($this->getById($id));
  }
}
