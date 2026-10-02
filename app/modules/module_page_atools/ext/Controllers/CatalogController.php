<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\OnlinePlayersService;

class CatalogController
{
    public function admins(
        DatabaseController $databaseController,
        ServersController $serversController,
        RendersController $rendersController,
        object $General
    ): array {
        $onlinePlayers = (new OnlinePlayersService())->getOnlinePlayers($General);

        return [
            'groups' => [
                'csgo' => $databaseController->getGroupsGame('csgo') ?? [],
                'cs2' => $databaseController->getGroupsGame('cs2') ?? [],
            ],
            'servers' => [
                'csgo' => $serversController->getServers('csgo')['data'] ?? [],
                'cs2' => $serversController->getServers('cs2')['data'] ?? [],
            ],
            'terms' => [
                'admins' => $rendersController->renderTerms('admins')['data'] ?? [],
                'punishments' => $rendersController->renderTerms('punishments')['data'] ?? [],
            ],
            'accessGroups' => $rendersController->renderGroups()['data'] ?? [],
            'onlinePlayers' => $onlinePlayers,
        ];
    }

    public function punishments(
        ServersController $serversController,
        RendersController $rendersController,
        object $General
    ): array {
        $onlinePlayers = (new OnlinePlayersService())->getOnlinePlayers($General);

        return [
            'servers' => [
                'csgo' => $serversController->getServers('csgo')['data'] ?? [],
                'cs2' => $serversController->getServers('cs2')['data'] ?? [],
            ],
            'admins' => [
                'csgo' => $rendersController->renderAdmins('csgo')['data'] ?? [],
                'cs2' => $rendersController->renderAdmins('cs2')['data'] ?? [],
            ],
            'terms' => [
                'punishments' => $rendersController->renderTerms('punishments')['data'] ?? [],
            ],
            'reasons' => $rendersController->renderReasons()['data'] ?? [],
            'onlinePlayers' => $onlinePlayers,
        ];
    }

    public function privileges(
        ServersController $serversController,
        RendersController $rendersController
    ): array {
        return [
            'vipGroups' => $rendersController->renderVipGroups()['data'] ?? [],
            'servers' => $serversController->getServers()['data'] ?? [],
            'terms' => [
                'vips' => $rendersController->renderTerms('vips')['data'] ?? [],
            ],
        ];
    }
}
