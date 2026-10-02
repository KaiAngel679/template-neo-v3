<?php

namespace app\modules\module_page_results\ext\Service;

use app\modules\module_page_results\ext\Repository\ServerRepository;

class ServerService
{
  private $serverRepository;
  private $Translate;

  public function __construct($Translate)
  {
    $this->serverRepository = new ServerRepository();
    $this->Translate = $Translate;
  }

  public function renderServers(): array
  {
    return ['data' => $this->serverRepository->getAll()];
  }

  public function addServer(array $data): array
  {
    $name = trim($data['name'] ?? '');
    $sid = (int)($data['sid'] ?? 0);
    $rid = (int)($data['rid'] ?? 0);

    if (empty($name)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_EmptyServerName')];
    }

    $this->serverRepository->add(['name' => $name, 'sid' => $sid, 'rid' => $rid]);
    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_ServerAdded')];
  }

  public function deleteServer(array $data): array
  {
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_InvalidData')];
    }

    $this->serverRepository->delete($id);
    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_ServerDeleted')];
  }

  public function getAll(): array
  {
    return $this->serverRepository->getAll();
  }

  public function getById(int $id): array
  {
    return $this->serverRepository->getById($id);
  }

  public function getBySid(int $sid): array
  {
    return $this->serverRepository->getBySid($sid);
  }

  public function getByRid(int $rid): array
  {
    return $this->serverRepository->getByRid($rid);
  }

  public function getIdMap(): array
  {
    return $this->serverRepository->getIdMap();
  }
  
  public function getSidMap(): array
  {
    return $this->serverRepository->getSidMap();
  }

  public function getRidMap(): array
  {
    return $this->serverRepository->getRidMap();
  }
}
