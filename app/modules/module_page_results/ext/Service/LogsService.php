<?php

namespace app\modules\module_page_results\ext\Service;

use app\modules\module_page_results\ext\Service\SettingsService;
use app\modules\module_page_results\ext\Repository\LogsRepository;

class LogsService
{
  private $logsRepository;
  private $settingsService;
  private $General;
  private $Translate;
  public function __construct($Translate, $General)
  {
    $this->settingsService = new SettingsService($Translate);
    $this->logsRepository = new LogsRepository();
    $this->Translate = $Translate;
    $this->General = $General;
    
  }
  public function writeLog(string $level, string $message): array
  {
    $this->logsRepository->writeLog($level, $message);
    return ['success' => 'Log entry added'];
  }

  public function getLogs($page, $date): array
  {
    $this->logsRepository->getLogs($page, $date);
    return ['success' => 'Logs retrieved'];
  }

  public function renderLogs($page, $date): array
  {
    return $this->logsRepository->getLogs($page, $date);
  }

  public function getDatesWithLogs(): array
  {
    return $this->logsRepository->getDatesWithLogs();
  }

  public function clearLogs(): array
  {
    $this->logsRepository->clearLogs();
    return ['success' => 'Logs cleared'];
  }

  private function DiscordWebhook($payload): void
  {
    $settings = $this->settingsService->getSettings();
    if ($settings['enable_webhook'] ?? false) {
      $webhookUrl = $settings['webhook_url'] ?? '';
      if (filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
        if (!is_array($payload)) {
          $payload = ['content' => (string) $payload];
        }
        $payload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
      }
    }
  }

  public function sendGenerateMessage($periodId, $period, $serversReport): void
  {
    $serverLines = [];
    foreach ($serversReport as $server) {
      $admins = (int) ($server['admins'] ?? 0);
      $name = (string) ($server['name'] ?? '');
      $serverLines[] = sprintf($this->Translate->get_translate_module_phrase('module_page_results', '_serversandadmins'), $admins, $name);
    }
    $resultsUrl = 'https:' . $this->General->arr_general['site'] . 'results/?rid=' . $periodId;
    $settings = $this->settingsService->getSettings();
    $imageUrl = (string) ($settings['image_url'] ?? 'https://i.ibb.co/QFHPhh9J/simplified-the-cs2-background-on-their-website-for-v0-hjrzmal4xgpa1.png');
    $descriptionLines = ["# [" . $this->Translate->get_translate_module_phrase('module_page_results', '_ViewReport') . "]({$resultsUrl})"];
    $descriptionLines[] = "ㅤ";
    if (!empty($serverLines)) {
      $descriptionLines[] = implode("\nㅤ\n", $serverLines);
    }
    $description = implode("\n", $descriptionLines);

    $embed = [
      'title' => sprintf($this->Translate->get_translate_module_phrase('module_page_results', '_ReportGeneratedForPeriod'), $period),
      'description' => $description,
      'fields' => [],
      'image' => [
        'url' => $imageUrl
      ]
    ];

    $content = [
      'embeds' => [$embed]
    ];

    $this->DiscordWebhook($content);
  }

  public function sendGiveAwardMessage(int $resultId, int $serverId, string $adminName, string $serverName, string $period): void
  {
    $resultsUrl = 'https:' . $this->General->arr_general['site'] . "results/?rid={$resultId}&server={$serverId}";
    $settings = $this->settingsService->getSettings();
    $imageUrl = (string) ($settings['image_url'] ?? 'https://i.ibb.co/QFHPhh9J/simplified-the-cs2-background-on-their-website-for-v0-hjrzmal4xgpa1.png');

    $description = sprintf(
      $this->Translate->get_translate_module_phrase('module_page_results', '_AwardsGivenToAdmin'),
      $adminName,
      $resultsUrl,
      $serverName,
      $period
    );

    $embed = [
      'description' => $description,
      'image' => [
        'url' => $imageUrl
      ]
    ];

    $content = [
      'embeds' => [$embed]
    ];

    $this->DiscordWebhook($content);
  }
}
