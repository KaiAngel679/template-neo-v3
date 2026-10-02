<?php

namespace app\modules\module_page_atools\ext\Services;

final class OnlinePlayersService
{
    public function getOnlinePlayers(object $General): array
    {
        $cacheDir = MODULES . 'module_block_main_servers/servers/';
        if (!is_dir($cacheDir)) {
            return [];
        }

        $out = [];
        $seen = [];

        foreach ((array) ($General->server_list ?? []) as $server) {
            $ipPort = (string) ($server['ip'] ?? '');
            if ($ipPort === '') {
                continue;
            }

            $cacheKey = str_replace('.', '_', $ipPort);
            $file = $cacheDir . $cacheKey . '.json';
            if (!is_file($file)) {
                continue;
            }

            $json = @file_get_contents($file);
            if ($json === false || $json === '') {
                continue;
            }

            $data = json_decode($json, true);
            if (!is_array($data)) {
                continue;
            }

            $players = $data['data']['players'] ?? [];
            if (!is_array($players) || $players === []) {
                continue;
            }

            $serverId = (int) ($server['id'] ?? 0);

            foreach ($players as $p) {
                $steamid = isset($p['steamid']) ? (string) $p['steamid'] : '';
                $name = isset($p['name']) ? (string) $p['name'] : '';

                if ($steamid === '' || $steamid === '0') {
                    continue;
                }
                if ($name === '') {
                    $name = $steamid;
                }

                $uniq = $steamid . '@' . $serverId;
                if (isset($seen[$uniq])) {
                    continue;
                }
                $seen[$uniq] = true;

                $out[] = [
                    'steamid' => $steamid,
                    'name' => $name,
                ];
            }
        }

        usort($out, static function (array $a, array $b): int {
            $na = (string) ($a['name'] ?? '');
            $nb = (string) ($b['name'] ?? '');
            return strcmp($na, $nb);
        });

        return $out;
    }
}
