<?php

namespace app\modules\module_page_bonuses\ext\Repositories;

class CacheRepository
{
    private const CACHE_DIR = MODULES . 'module_page_bonuses/assets/cache/';

    private const DEFAULT_SETTINGS = [
        'enabled_tg' => 0,
        'text_tg' => '',
        'money_tg' => 0,
        'url_tg' => '',
        'bot_key_tg' => '',
        'bot_id_tg' => '',
        'enabled_ds' => 0,
        'text_ds' => '',
        'money_ds' => 0,
        'url_ds' => '',
        'guild_id_ds' => 0,
        'client_id_ds' => '',
        'secret_id_ds' => '',
        'enabled_vk' => 0,
        'text_vk' => '',
        'money_vk' => 0,
        'url_vk' => '',
    ];

    public function getCache(string $name): array
    {
        if ($name !== 'settings') {
            return ['status' => 'error', 'message' => 'not'];
        }

        $php = $this->pathPhp($name);
        $json = $this->pathJson($name);

        if (!file_exists($php) && file_exists($json)) {
            $this->migrateJsonToPhp($json, $php);
        }

        if (!file_exists($php)) {
            return self::DEFAULT_SETTINGS;
        }

        if (!is_readable($php)) {
            return ['status' => 'error', 'message' => 'perm'];
        }

        $data = require $php;

        return is_array($data) ? array_merge(self::DEFAULT_SETTINGS, $data) : self::DEFAULT_SETTINGS;
    }

    public function putCache(array $data, string $section, string $name): array
    {
        if ($name !== 'settings') {
            return ['status' => 'error', 'message' => 'not'];
        }

        $php = $this->pathPhp($name);
        $json = $this->pathJson($name);

        if (!file_exists($php) && file_exists($json)) {
            $this->migrateJsonToPhp($json, $php);
        }

        if (!file_exists($php)) {
            $this->writeSettingsPhp($php, self::DEFAULT_SETTINGS);
        }

        if (!is_writable($php)) {
            return ['status' => 'error', 'message' => 'perm'];
        }

        $current = $this->readSettingsPhpRaw($php);
        if (!is_array($current)) {
            $current = self::DEFAULT_SETTINGS;
        }
        $current = array_merge(self::DEFAULT_SETTINGS, $current);

        foreach ($data as $key => $value) {
            if (strpos((string) $key, "_{$section}") !== false) {
                $current[$key] = $value;
            }
        }

        if (!$this->writeSettingsPhp($php, $current)) {
            return ['status' => 'error', 'message' => 'fake'];
        }

        return ['status' => 'success'];
    }

    private function pathPhp(string $name): string
    {
        return self::CACHE_DIR . "{$name}.php";
    }

    private function pathJson(string $name): string
    {
        return self::CACHE_DIR . "{$name}.json";
    }

    private function migrateJsonToPhp(string $jsonPath, string $phpPath): void
    {
        if (!is_readable($jsonPath)) {
            return;
        }

        $decoded = json_decode((string) file_get_contents($jsonPath), true);
        if (!is_array($decoded)) {
            return;
        }

        $merged = array_merge(self::DEFAULT_SETTINGS, $decoded);
        $this->writeSettingsPhp($phpPath, $merged);
    }

    private function readSettingsPhpRaw(string $phpPath): ?array
    {
        if (!is_readable($phpPath)) {
            return null;
        }

        $data = require $phpPath;

        return is_array($data) ? $data : null;
    }

    private function writeSettingsPhp(string $phpPath, array $data): bool
    {
        $dir = dirname($phpPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $export = var_export($data, true);
        $body = "<?php\n\nreturn {$export};\n";
        $tmp = $phpPath . '.tmp';

        if (file_put_contents($tmp, $body, LOCK_EX) === false) {
            return false;
        }

        if (!rename($tmp, $phpPath)) {
            @unlink($tmp);

            return false;
        }

        return true;
    }
}
