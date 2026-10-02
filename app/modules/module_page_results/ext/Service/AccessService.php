<?php

namespace app\modules\module_page_results\ext\Service;

use app\modules\module_page_results\ext\Repository\AccessRepository;

class AccessService
{
  private $Translate;
  private $accessRepository;

  public function __construct($Translate, $Db)
  {
    $this->accessRepository = new AccessRepository( $Db);
    $this->Translate = $Translate;
  }

  public function getAccesses(): array
  {
    return $this->accessRepository->getAll();
  }

  public function addAccess(array $data): array
  {
    $steamid = con_steam64($data['steamid'] ?? '');
    if (empty($steamid)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_InvalidData')];
    }

    if ($this->accessRepository->existsBySteamId($steamid)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_AccessAlreadyExists')];
    }

    $this->accessRepository->add([
      'steamid' => $steamid,
      'awardwarns' => !empty($data['awardwarns']),
      'results' => !empty($data['results']),
      'warns' => !empty($data['warns']),
      'admins' => !empty($data['admins']),
      'full' => !empty($data['full']),
      'date_added' => time(),
      'added_by' => $_SESSION['steamid64'] ?? '',
    ]);

    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_AccessAdded')];
  }

  public function deleteAccess(array $data): array
  {
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_InvalidData')];
    }

    $this->accessRepository->delete($id);
    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_AccessDeleted')];
  }

  public function checkAccess(string $steamid, array $permissions = []): ?array
  {
    return $this->accessRepository->checkAccess($steamid, $permissions);
  }
}
