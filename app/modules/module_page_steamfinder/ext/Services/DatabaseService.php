<?php

namespace app\modules\module_page_steamfinder\ext\Services;

use app\modules\module_page_steamfinder\ext\Repositories\CacheRepository;

class DatabaseService
{
    protected $Db, $General, $CacheRepository;

    public function __construct($Db, $General)
    {
        $this->Db = $Db;
        $this->General = $General;
        $this->CacheRepository = new CacheRepository();
    }

    public function getInfoServers($steam, $steam64)
    {
        $cached = $this->CacheRepository->get($steam64);
        if ($cached !== null && isset($cached['server_stats'])) {
            return $cached['server_stats'];
        }

        $playTimes = [];

        foreach ($this->General->server_list as $server) {
            $server_stats = explode(";", $server['server_stats']);
            $stats = $this->Db->queryAll($server_stats[0], $server_stats[1], $server_stats[2],  "SELECT `steam`, `playtime` FROM `" . $server_stats[3] . "` WHERE `steam`= '" . $steam . "' LIMIT 1");

            $playtime = !empty($stats) ? floor($stats[0]['playtime'] / 3600) : 0;

            if ($playtime > 0) {
                $playTimes[] = [
                    'server_name' => $server['name_custom'],
                    'playtime_hours' => $playtime
                ];
            }
        }

        $this->CacheRepository->set($steam64, 'server_stats', $playTimes);

        return $playTimes;
    }
}
