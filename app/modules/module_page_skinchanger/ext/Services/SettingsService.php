<?php

namespace app\modules\module_page_skinchanger\ext\Services;

use app\modules\module_page_skinchanger\ext\Repositories\{
  SettingsRepository
};

class SettingsService
{
  protected $Translate, $SettingsRepository;

  public function __construct(object $Translate)
  {
    $this->Translate     = $Translate;
    $this->SettingsRepository = new SettingsRepository();
  }

  public function getSettings()
  {
    return $this->SettingsRepository->getSettings();
  }

  public function saveSettings(array $settings): bool
  {
    return $this->SettingsRepository->saveSettings($settings);
  }
}
