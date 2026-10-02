<?php

namespace app\modules\module_page_skinchanger\ext\Repositories\Plugins;

use app\modules\module_page_skinchanger\ext\Repositories\SettingsRepository;

class PluginFactory
{
  private static ?string $pluginType = null;

  public static function create(object $Db, int $DID = 0, int $UID = 0): PisexRepository
  {
    if (self::$pluginType === null) {
      $settings = (new SettingsRepository())->getSettings();
      self::$pluginType = $settings['plugin'] ?? 'pisex';
    }

    switch (self::$pluginType) {
      case 'weaponpaints':
        return new WeaponPaintsRepository($Db, $DID, $UID);
      default:
        return new PisexRepository($Db, $DID, $UID);
    }
  }

  public static function getPluginType(): string
  {
    if (self::$pluginType === null) {
      $settings = (new SettingsRepository())->getSettings();
      self::$pluginType = $settings['plugin'] ?? 'pisex';
    }
    return self::$pluginType;
  }
}
