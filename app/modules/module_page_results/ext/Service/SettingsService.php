<?php

namespace app\modules\module_page_results\ext\Service;

use app\modules\module_page_results\ext\Repository\SettingsRepository;

class SettingsService
{
  private $settingsRepository;
  private $Translate;

  public function __construct( $Translate)
  {
    $this->settingsRepository = new SettingsRepository();
    $this->Translate = $Translate;
  }

  public function renderSettings(): array
  {
    return ['data' => $this->settingsRepository->getSettings()];
  }

  public function saveSettings(array $data): array
  {
    $existing = $this->settingsRepository->getSettings();
    $settings = [
      'only_theirs' => !empty($data['only_theirs']),
      'allInOneReport' => !empty($data['allInOneReport']),
      'time' => (int)($data['time'] ?? 0),
      'add_warn' => !empty($data['add_warn']),
      'warn_time' => (int)($data['warn_time'] ?? 0),
    ];

    $this->settingsRepository->saveSettings(array_merge($existing, $settings));
    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_SettingsSaved')];
  }

  public function saveDiscord(array $data): array
  {
    $existing = $this->settingsRepository->getSettings();
    $webhook = trim($data['webhook_url'] ?? $data['webhook'] ?? $data['webhook_url'] ?? '');
    $imageUrl = trim($data['image_url'] ?? $data['image_url'] ?? '');
    $enableWebhook = !empty($data['enable_webhook']) || !empty($data['enable_webhook']);

    if ($webhook !== '' && !filter_var($webhook, FILTER_VALIDATE_URL)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_InvalidWebhookUrl')];
    }
    if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
      return ['error' => $this->Translate->get_translate_module_phrase('module_page_results', '_InvalidWebhookUrl')];
    }

    $settings = [
      'enable_webhook' => $enableWebhook,
      'webhook_url' => $webhook,
      'image_url' => $imageUrl
    ];

    $this->settingsRepository->saveSettings(array_merge($existing, $settings));
    return ['success' => $this->Translate->get_translate_module_phrase('module_page_results', '_SettingsSaved')];
  }

  public function getSettings(): array
  {
    return $this->settingsRepository->getSettings();
  }
}
