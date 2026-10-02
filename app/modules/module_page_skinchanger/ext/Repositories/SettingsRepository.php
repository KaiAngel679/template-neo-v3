<?php

namespace app\modules\module_page_skinchanger\ext\Repositories;

class SettingsRepository extends BaseRepository
{
  private function getSettingsPath(): string
  {
    return STORAGE . 'modules_cache/module_page_skinchanger/settings.json';
  }

  public function getSettings(): array
  {
    $path = $this->getSettingsPath();
    if (!file_exists($path)) {
      return $this->getDefaults();
    }
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? array_merge($this->getDefaults(), $data) : $this->getDefaults();
  }

  public function saveSettings(array $settings): bool
  {
    $path = $this->getSettingsPath();
    $dir = dirname($path);
    if (!is_dir($dir)) {
      mkdir($dir, 0755, true);
    }
    return file_put_contents($path, json_encode(array_merge($this->getDefaults(), $settings), JSON_UNESCAPED_UNICODE)) !== false;
  }

  private function getDefaults(): array
  {
    return [
      'collections_limit'  => 5,
      'plugin'             => 'pisex',
      'vip_groups'         => [],
      'vip_access_groups'  => '',
      'new_collections'    => [],
    ];
  }
}
