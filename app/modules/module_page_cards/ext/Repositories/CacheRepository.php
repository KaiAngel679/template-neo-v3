<?php

namespace app\modules\module_page_cards\ext\Repositories;

class CacheRepository
{
    private const CACHE_DIR = MODULES . "module_page_cards/assets/cache/";
    public function getCache(string $name): array
    {
        $file = self::CACHE_DIR . "$name.json";

        if (!file_exists($file)) {
            return ['status' => 'error', 'message' => 'not'];
        }

        if (!is_readable($file)) {
            return ['status' => 'error', 'message' => 'perm'];
        }

        return json_decode(file_get_contents($file), true);
    }

    public function putCache(array $data, string $name): array
    {
        $file = self::CACHE_DIR . "$name.json";

        if (!file_exists($file)) {
            return ['status' => 'error', 'message' => 'not'];
        }

        if (!is_writable($file)) {
            return ['status'  => 'error', 'message' => 'perm'];
        }

        if (file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) === false) {
            return ['status' => 'error', 'message' => 'fake'];
        }

        return ['status' => 'success'];
    }
}
