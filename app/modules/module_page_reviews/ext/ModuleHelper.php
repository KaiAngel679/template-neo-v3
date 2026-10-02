<?php

namespace app\modules\module_page_reviews\ext;

final class ModuleHelper
{
    public const TEXT_MIN = 10;
    public const TEXT_MAX = 2000;
    public const SEARCH_MAX = 100;

    private const MAX_COMBINING_MARKS = 2;

    public static function sanitizeUserText(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;

        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_C);
            if (is_string($normalized)) {
                $text = $normalized;
            }
        }

        $max = self::MAX_COMBINING_MARKS;
        $text = preg_replace('/(\p{M}{' . $max . '})\p{M}+/u', '$1', $text) ?? $text;

        $text = preg_replace('/[\x{00AD}\x{200B}\x{200C}\x{2060}\x{FEFF}]+/u', '', $text) ?? $text;

        return $text;
    }

    public static function phrase(?object $translate, string $key, array $replace = []): string
    {
        $text = '';
        if ($translate !== null && method_exists($translate, 'get_translate_module_phrase')) {
            $text = (string) $translate->get_translate_module_phrase('module_page_reviews', $key);
        }
        if ($text === '' || $text === $key) {
            $text = $key;
        }
        foreach ($replace as $search => $value) {
            $text = str_replace((string) $search, (string) $value, $text);
        }

        return $text;
    }

    public static function toSteam64(string $value): string
    {
        $converted = con_steam64($value);

        return ($converted !== false && $converted !== '' && $converted !== '0') ? (string) $converted : $value;
    }

    public static function validateOptionalText(string $text, string $label = 'Текст', ?object $translate = null): ?string
    {
        $len = mb_strlen($text);
        if ($len === 0) {
            return null;
        }
        if ($len < self::TEXT_MIN) {
            return self::phrase($translate, '_rv_textMin', [
                '%label%' => $label,
                '%min%' => (string) self::TEXT_MIN,
            ]);
        }
        if ($len > self::TEXT_MAX) {
            return self::phrase($translate, '_rv_textMax', [
                '%label%' => $label,
                '%max%' => (string) self::TEXT_MAX,
            ]);
        }

        return null;
    }

    public static function validateRequiredText(string $text, string $label = 'Текст', ?object $translate = null): ?string
    {
        $len = mb_strlen($text);
        if ($len < self::TEXT_MIN) {
            return self::phrase($translate, '_rv_textMin', [
                '%label%' => $label,
                '%min%' => (string) self::TEXT_MIN,
            ]);
        }
        if ($len > self::TEXT_MAX) {
            return self::phrase($translate, '_rv_textMax', [
                '%label%' => $label,
                '%max%' => (string) self::TEXT_MAX,
            ]);
        }

        return null;
    }

    public static function clampSearch(string $search): string
    {
        $search = trim($search);
        if ($search === '') {
            return '';
        }
        if (mb_strlen($search) > self::SEARCH_MAX) {
            return mb_substr($search, 0, self::SEARCH_MAX);
        }

        return $search;
    }

    public static function toSteam32(string $value): string
    {
        $converted = con_steam32($value);

        return ($converted !== false && $converted !== '' && $converted !== '0') ? (string) $converted : $value;
    }

    public static function resolveDisplayName(object $General, string $steamid, ?string $fallbackName = null): string
    {
        $name = $General->checkName($steamid);
        if ($name == 'Unnamed' && $fallbackName !== null && $fallbackName !== '') {
            $name = action_text_clear($fallbackName);
        }

        return $name;
    }

    public static function isAdmin(): bool
    {
        return isset($_SESSION['user_admin']);
    }

    public static function sessionSteam(): string
    {
        return isset($_SESSION['steamid']) ? (string) $_SESSION['steamid'] : '';
    }

    public static function normalizeI18nName(array $name): array
    {
        $out = [];
        foreach ($name as $lang => $value) {
            if (!is_string($lang) && !is_int($lang)) {
                continue;
            }
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            $out[strtolower((string) $lang)] = mb_substr($value, 0, 64);
        }

        return $out;
    }

    public static function parseI18nName($stored): array
    {
        if (is_array($stored)) {
            return self::normalizeI18nName($stored);
        }

        $raw = trim((string) $stored);
        if ($raw === '') {
            return [];
        }

        if ($raw[0] === '{' || $raw[0] === '[') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return self::normalizeI18nName($decoded);
            }
        }

        return ['ru' => mb_substr($raw, 0, 64)];
    }

    public static function resolveI18nName($stored, ?string $fallback = null): string
    {
        $map = self::parseI18nName($stored);
        if ($map === []) {
            return $fallback !== null ? (string) $fallback : '';
        }

        $lang = strtolower((string) ($_SESSION['language'] ?? 'ru'));
        if (isset($map[$lang]) && $map[$lang] !== '') {
            return $map[$lang];
        }
        if (isset($map['ru']) && $map['ru'] !== '') {
            return $map['ru'];
        }

        return (string) reset($map);
    }

    public static function encodeI18nName(array $map): string
    {
        $map = self::normalizeI18nName($map);
        if ($map === []) {
            return '';
        }

        return (string) json_encode($map, JSON_UNESCAPED_UNICODE);
    }
}
