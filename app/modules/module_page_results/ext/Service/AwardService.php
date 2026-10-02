<?php

namespace app\modules\module_page_results\ext\Service;

use app\modules\module_page_results\ext\Repository\AwardRepository;
use app\modules\module_page_results\ext\Repository\ResultRepository;
use app\modules\module_page_results\ext\Repository\WarnRepository;
use app\modules\module_page_results\ext\Repository\StatsRepository;
use app\modules\module_page_results\ext\Repository\SettingsRepository;

class AwardService
{
  private $awardRepository;
  private $resultRepository;
  private $warnRepository;
  private $statsRepository;
  private $settingsRepository;
  private $logsService;
  private $Translate;
  private $General;

  public function __construct(
    $Translate,
    $General,
    $Db
  ) {
    $this->awardRepository = new AwardRepository();
    $this->resultRepository = new ResultRepository();
    $this->warnRepository = new WarnRepository($Db);
    $this->statsRepository = new StatsRepository($Db, $Translate);
    $this->settingsRepository = new SettingsRepository();
    $this->logsService = new LogsService($Translate, $General);
    $this->Translate = $Translate;
    $this->General = $General;
  }

  public function getAll(): array
  {
    return $this->awardRepository->getAll();
  }

  public function getById(int $id): ?array
  {
    return $this->awardRepository->getById($id);
  }

  public function grantAwards(int $resultId, int $serverId): array
  {
    $result = $this->resultRepository->getById($resultId);
    if (empty($result) || (int)($result['id'] ?? 0) !== $resultId) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_ResultNotFound')];
    }

    $servers = $result['servers'] ?? [];
    foreach ($servers as &$server) {
      if ((int)($server['server_id'] ?? 0) === $serverId) {
        if (!empty($server['award_taken']) && $server['award_taken'] === true) {
          return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_AwardsAlreadyGiven')];
        }

        $settings = $this->settingsRepository->getSettings();
        $admins = $server['admins'] ?? [];
        $awardedAdmins = [];
        $warnerAdmins = [];

        foreach ($admins as &$admin) {
          if ($settings['time'] > 0 && (int)($admin['played_time'] ?? 0) >= (int)($settings['time'] ?? 0) * 3600) {
            if (!empty($admin['award_sum']) && (float)($admin['award_sum']) > 0) {
              $awardedAdmins[] = $admin;
              $this->statsRepository->giveBalance($admin['steamid'], (float)($admin['award_sum']), $server['period_label'] ?? '');
            }
          } else {
            if (!empty($settings['add_warn']) && (int)($settings['warn_time'] ?? 0) > 0) {
              $warnerAdmins[] = $admin;
              $this->warnRepository->add(
                $admin['steamid'],
                $this->Translate->get_translate_module_phrase('module_page_results', '_NormNotCompletedForPeriod') . ($server['period_label'] ?? ''),
                (int)($settings['warn_time'] ?? 0)
              );
            }
          }
        }

        $warn_message = '';
        if (count($warnerAdmins) > 0) {
          $warn_message = $this->Translate->get_translate_module_phrase('module_page_results', '_AndWarnsGiven') . count($warnerAdmins) . ' ' . $this->Translate->get_translate_module_phrase('module_page_results', '_onAdmins') . '.';
        }

        $server['admins'] = $admins;
        $server['award_taken'] = true;
        $server['award_give_by'] = $_SESSION['steamid64'];
        $this->resultRepository->save($resultId, $server);
        $adminName = $this->General->checkName($_SESSION['steamid64'] ?? '');
        $this->logsService->sendGiveAwardMessage($resultId,  $serverId, $adminName, $server['server_name'], $server['period_label']);
        return [
          'success' => $this->Translate->get_translate_module_phrase('module_page_results', '_AwardsGiven') .
            count($awardedAdmins) .
            $this->Translate->get_translate_module_phrase('module_page_results', '_onAdmins') .
            $warn_message
        ];
      }
    }

    return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_ResultNotFound')];
  }

  public function giveAwardManual(array $data): array
  {
    $steamid = $data['steamid'] ?? '';
    $amount = (float)($data['amount'] ?? 0);
    $reason = trim($data['reason'] ?? '');

    if ($amount <= 0) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_SpecifyAmount')];
    }

    $this->statsRepository->giveBalance($steamid, $amount, $reason ?: $this->Translate->get_translate_module_phrase('module_page_results', '_ManualAward'));
    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_AwardGiven') . ' ' . $amount . ' ' . $this->General->currency];
  }

  public function renderAwards(): array
  {
    return ['data' => $this->awardRepository->getAll()];
  }

  public function addAward(array $data): array
  {
    $time = (int)($data['time'] ?? 0);
    $amount = trim($data['amount'] ?? '');

    if ($time <= 0 || empty($amount)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_InvalidData')];
    }

    $this->awardRepository->add(['time' => $time, 'amount' => $amount]);
    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_AwardAdded')];
  }

  public function editAward(array $data): array
  {
    $id = (int)($data['id'] ?? 0);
    $time = (int)($data['time'] ?? 0);
    $amount = trim($data['amount'] ?? '');

    if ($id <= 0 || $time <= 0 || empty($amount)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_InvalidData')];
    }

    $this->awardRepository->update($id, ['time' => $time, 'amount' => $amount]);
    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_AwardUpdated')];
  }

  public function deleteAward(array $data): array
  {
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_InvalidData')];
    }

    $this->awardRepository->delete($id);
    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_AwardDeleted')];
  }
}
