<?php

namespace app\modules\module_page_skinchanger\ext\Controllers;

use app\modules\module_page_skinchanger\ext\Services\{
  SettingsService
};

class SettingsController
{
  public $SettingsService;

  public function __construct(object $Db, object $Translate, object $General)
  {
    $this->SettingsService = new SettingsService($Translate);
  }

  public function getSettings()
  {
    return $this->SettingsService->getSettings();
  }

  public function saveSettings(array $settings): bool
  {
    return $this->SettingsService->saveSettings($settings);
  }
}
