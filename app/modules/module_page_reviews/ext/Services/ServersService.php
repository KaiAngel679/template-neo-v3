<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\ModuleHelper;

class ServersService
{
    private $General;
    private $Translate;

    public function __construct(object $General, ?object $Translate = null)
    {
        $this->General = $General;
        $this->Translate = $Translate;
    }

    public function getServers(): array
    {
        $out = [];
        foreach ($this->General->server_list as $server) {
            $out[] = [
                'id' => (int) $server['id'],
                'name_custom' => $server['name_custom'],
                'server_game' => $this->formatServerGame($server),
            ];
        }

        return $out;
    }

    public function getServerById(int $id): ?array
    {
        if ($id === -1) {
            return [
                'id' => -1,
                'name_custom' => ModuleHelper::phrase($this->Translate, '_rv_allServers'),
                'server_game' => '',
            ];
        }

        foreach ($this->General->server_list as $server) {
            if ((int) $server['id'] !== $id) {
                continue;
            }

            return [
                'id' => (int) $server['id'],
                'name_custom' => $server['name_custom'],
                'server_game' => $this->formatServerGame($server),
            ];
        }

        return null;
    }

    private function formatServerGame(array $server): string
    {
        return mb_strtoupper((string) ($server['server_game'] ?? ''), 'UTF-8');
    }
}
