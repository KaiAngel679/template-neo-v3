<?php

namespace app\modules\module_page_results\ext\Repository;

class SettingsRepository extends BaseRepository
{
  public function getSettings(): array
  {
    return $this->loadFromJson('main', 'settings') ?: [];
  }

  public function saveSettings(array $settings): bool
  {
    return $this->saveToJson('main', 'settings', $settings);
  }
}
