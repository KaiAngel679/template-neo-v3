<?php

namespace app\modules\module_page_atools\ext\Services;

class ServersService
{
    private $General;

    public function __construct(object $General)
    {
        $this->General = $General;
    }

    public function getServers(?string $game = null): array
    {
        $out = [];
        foreach ($this->General->server_list as $server) {
            if ($game != null && $this->normalizeGameKey(($server['server_game'] ?? '')) != strtolower($game)) {
                continue;
            }
            $out[] = [
                'id' => (int) $server['id'],
                'name_custom' => $server['name_custom'],
                'server_game' => $this->formatServerGame($server),
            ];
        }
        return $out;
    }

    public function filterPanelServerIdsByGame(array $panelServerIds, string $game): array
    {
        $game = strtolower($game);
        $panelServerIds = array_values(array_unique(array_map('intval', $panelServerIds)));

        if (in_array(-1, $panelServerIds, true)) {
            $out = [];
            foreach ($this->General->server_list as $server) {
                if ($this->normalizeGameKey(($server['server_game'] ?? '')) === $game) {
                    $out[] = (int) $server['id'];
                }
            }

            return array_values(array_unique($out));
        }

        $out = [];
        foreach ($this->returnServersByIds($panelServerIds) as $server) {
            if ($this->normalizeGameKey($server['server_game']) == $game) {
                $out[] = (int) $server['id'];
            }
        }

        return array_values(array_unique($out));
    }

    public function isGameInvolvedInServerSelection(array $servers, string $game): bool
    {
        return $this->filterPanelServerIdsByGame(
            array_values(array_unique(array_map('intval', $servers))),
            $game
        ) !== [];
    }

    public function getSourceBansBindingIds(?string $game = 'csgo'): array
    {
        $panelIds = $this->filterPanelServerIdsByGame([-1], $game ?? 'csgo');
        $binding = [];

        foreach ($this->returnServersByIds($panelIds) as $server) {
            $sb = (int) ($server['server_sb_id'] ?? 0);
            if ($sb > 0) {
                $binding[] = $sb;
            }
        }

        return array_values(array_unique($binding));
    }

    public function resolvePanelServersToBinding(array $panelServerIds): array
    {
        $panelServerIds = array_values(array_unique(array_map('intval', $panelServerIds)));

        if (in_array(-1, $panelServerIds, true)) {
            return [[-1], [-1]];
        }

        $serversData = $this->returnServersByIds($panelServerIds);

        return [
            array_column($serversData, 'server_sb_id'),
            array_column($serversData, 'id'),
        ];
    }

    public function returnServersByIds(array $ids): array
    {
        if ($ids == []) {
            return [];
        }
        $want = array_flip(array_map('intval', $ids));
        $out = [];
        foreach ($this->General->server_list as $server) {
            if (!isset($want[$server['id']])) {
                continue;
            }
            $out[] = [
                'id' => (int) $server['id'],
                'ip' => $server['ip'],
                'name_custom' => $server['name_custom'],
                'server_game' => $this->formatServerGame($server),
                'server_stats' => $server['server_stats'],
                'server_sb_id' => (int) $server['server_sb_id'],
                'rcon' => $server['rcon'],
                'server_vip' => $server['server_vip'],
                'server_vip_id' => (int) $server['server_vip_id'],
                'server_shop' => $server['server_shop'],
            ];
        }
        return $out;
    }

    public function returnServerByServerId(string $game, int $sbId): ?array
    {
        foreach ($this->General->server_list as $server) {
            if ($server['server_game'] != $game) {
                continue;
            }
            if ($server['server_sb_id'] != $sbId) {
                continue;
            }
            $rows = $this->returnServersByIds([$server['id']]);

            return $rows[0] ?? null;
        }

        return null;
    }

    public function buildServersBindingForList(array $panelServers, ?string $game = null): array
    {
        $panelIds = array_values(array_unique(array_map('intval', $panelServers)));
        if ($panelIds == [] || in_array(-1, $panelIds, true)) {
            return [-1];
        }

        $gameUp = $game != null ? mb_strtoupper($game, 'UTF-8') : null;
        $binding = [];

        foreach ($this->returnServersByIds($panelIds) as $server) {
            if ($gameUp != null && ($server['server_game'] ?? '') != $gameUp) {
                continue;
            }

            $sb = (int) ($server['server_sb_id'] ?? 0);
            if ($sb != 0) {
                $binding[] = $sb;
            }
        }

        $binding = array_values(array_unique($binding));

        return $binding == [] ? [-1] : $binding;
    }

    private function formatServerGame(array $server): string
    {
        return mb_strtoupper($server['server_game'], 'UTF-8');
    }

    private function normalizeGameKey(string $game): ?string
    {
        $game = strtolower($game);
        if ($game == 'cs2' || $game == 'csgo') {
            return $game;
        }

        return null;
    }
}
