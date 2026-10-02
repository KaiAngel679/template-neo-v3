<?php

namespace app\modules\module_page_atools\ext;

final class ModuleHelper
{
    public static function phrase(object $translate, string $key): string
    {
        return $translate->get_translate_module_phrase('module_page_atools', $key);
    }

    public static function toSteam64(string $value): string
    {
        $converted = con_steam64($value);

        return ($converted !== false && $converted !== '' && $converted !== '0') ? (string) $converted : $value;
    }

    public static function toSteam32(string $value): string
    {
        $converted = con_steam32($value);

        return ($converted !== false && $converted !== '' && $converted !== '0') ? (string) $converted : $value;
    }

    public static function toSteam3(string $value): string
    {
        $converted = con_steam3($value);

        return ($converted !== false && $converted !== '') ? (string) $converted : $value;
    }

    public static function resolveDisplayName(object $General, string $steamid, ?string $fallbackName = null): string
    {
        $name = $General->checkName($steamid);
        if ($name == 'Unnamed' && $fallbackName !== null && $fallbackName !== '') {
            $name = action_text_clear($fallbackName);
        }

        return $name;
    }
}
