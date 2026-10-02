<?php

namespace app\modules\module_page_results\ext\Service;

use app\modules\module_page_results\ext\Repository\WarnRepository;

class WarnService
{
  private $warnRepository;
  private $General;
  private $Translate;
  private $Modules;

  public function __construct($General, $Translate, $Modules, $Db)
  {
    $this->warnRepository = new WarnRepository($Db);
    $this->General = $General;
    $this->Translate = $Translate;
    $this->Modules = $Modules;
  }

  public function renderWarns(int $page = 1): array
  {
    $warns = $this->warnRepository->getAll();

    $perPage = 12;
    $total = count($warns);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    $pagedWarns = array_slice($warns, $offset, $perPage);

    $data = [];
    foreach ($pagedWarns as $warn) {
      $steamid = $warn['steamid'] ?? $warn['steam'] ?? '';
      $data[] = [
        'name' => $this->General->checkName($steamid),
        'steamid' => $steamid,
        'checked_avatar' => $this->General->checkAvatar($steamid),
        'reason' => $warn['reason'] ?? '',
        'createtime' => isset($warn['createtime']) ? date('d.m.Y H:i', $warn['createtime']) : '',
        'time' => ($warn['time'] ?? 0) < time()
          ? $this->Translate->get_translate_module_phrase('module_page_results', '_Expired')
          : $this->Modules->action_time_exchange_exact(($warn['time'] ?? 0) - time()),
      ];
    }

    return [
      'data' => $data,
      'page_max' => $totalPages,
    ];
  }

  public function giveWarnManual(array $data): array
  {
    $steamid = $data['steamid'] ?? '';
    $reason = trim($data['reason'] ?? '');
    $duration = (int)($data['duration'] ?? 0);

    if (empty($reason) || $duration <= 0) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_SpecifyReasonAndDuration')];
    }

    $this->warnRepository->add($steamid, $reason, $duration * 86400);
    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_WarnGiven') . $duration . ' ' . $this->Translate->get_translate_module_phrase('module_page_results', '_days')];
  }
}
