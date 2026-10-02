<?php

namespace app\modules\module_page_steamfinder\ext\Repositories;

class CacheRepository
{
    private $cacheDir;

    public function __construct()
    {
        $this->cacheDir = $cacheDir ?? MODULES . 'module_page_steamfinder/temp/';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function get($steam64)
    {
        $filename = $this->getFilename($steam64);

        if (!file_exists($filename)) {
            return null;
        }

        $data = json_decode(file_get_contents($filename), true);

        $currentTime = time();
        $updated = false;

        foreach ($data as $type => $cached) {
            $ttl = $this->getTtlForType($type);
            if ($currentTime - $cached['timestamp'] > $ttl) {
                unset($data[$type]);
                $updated = true;
            }
        }

        if ($updated) {
            if (empty($data)) {
                unlink($filename);
                return null;
            }
            file_put_contents($filename, json_encode($data));
        }

        $result = [];
        foreach ($data as $type => $cached) {
            $result[$type] = $cached['content'];
        }

        return $result;
    }

    public function set($steam64, $type, $content)
    {
        $filename = $this->getFilename($steam64);
        $data = [];

        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename), true);
        }

        $data[$type] = [
            'timestamp' => time(),
            'content' => $content
        ];

        file_put_contents($filename, json_encode($data));
    }

    protected function getTtlForType($type)
    {
        $ttlMap = [
            'steam_bans' => 604800,
            'steam_profile' => 172800,
            'faceit_stats' => 86400,
            'server_stats' => 86400,
            'steam_games' => 604800,
            'steam_cs2_stats' => 86400
        ];

        return $ttlMap[$type] ?? 3600;
    }

    protected function getFilename($key)
    {
        return $this->cacheDir . $key . '.json';
    }
}
