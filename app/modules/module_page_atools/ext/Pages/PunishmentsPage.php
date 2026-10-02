<?php

namespace app\modules\module_page_atools\ext\Pages;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\ModuleContainer;

final class PunishmentsPage extends AbstractPage
{
    public function isAccessible(AccessController $access, string $section): bool
    {
        return $access->checkPermission('punishments.view');
    }

    protected function actions(): array
    {
        return [
            'get_catalog' => [
                'handler' => static fn(ModuleContainer $c) => [
                    'status' => 'success',
                    'data' => $c->catalog()->punishments($c->servers(), $c->renders(), $c->General),
                ],
            ],
            'create_punishment' => [
                'permission' => static fn() => (int) ($_POST['punish_type'] ?? 0) === 0 ? 'bans.create' : 'mutes.create',
                'denied' => 'forbid',
                'handler' => static fn(ModuleContainer $c) => $c->punishment()->createPunishment(
                    (string) ($_POST['steamid'] ?? ''),
                    (string) ($_POST['ip'] ?? ''),
                    (int) ($_POST['punish_type'] ?? 0),
                    (string) ($_POST['reason'] ?? ''),
                    (int) ($_POST['expire'] ?? 0),
                    (array) ($_POST['servers'] ?? []),
                ),
            ],
            'get_punishments_list' => [
                'handler' => static fn(ModuleContainer $c) => $c->punishment()->getPunishmentsList(
                    (string) ($_POST['type'] ?? ''),
                    (string) ($_POST['punish_type'] ?? 'ban'),
                    (int) ($_POST['admin'] ?? -1),
                    (array) ($_POST['servers'] ?? []),
                    (string) ($_POST['date_from'] ?? ''),
                    (string) ($_POST['date_to'] ?? ''),
                    (string) ($_POST['expire_filter'] ?? 'all'),
                    (int) ($_POST['limit'] ?? 10),
                    (int) ($_POST['offset'] ?? 0),
                    (string) ($_POST['search'] ?? ''),
                ),
            ],
            'remove_punishments' => [
                'permission' => static fn() => (string) ($_POST['punish_type'] ?? 'ban') === 'mute' ? 'mutes.unmute' : 'bans.unban',
                'denied' => 'forbid',
                'handler' => static fn(ModuleContainer $c) => $c->punishment()->removePunishments(
                    (string) ($_POST['type'] ?? ''),
                    (array) ($_POST['punish_ids'] ?? []),
                    (string) ($_POST['punish_type'] ?? 'ban'),
                ),
            ],
            'delete_punishments' => [
                'permission' => static fn() => (string) ($_POST['punish_type'] ?? 'ban') === 'mute' ? 'mutes.delete' : 'bans.delete',
                'denied' => 'forbid',
                'handler' => static fn(ModuleContainer $c) => $c->punishment()->deletePunishments(
                    (string) ($_POST['type'] ?? ''),
                    (array) ($_POST['punish_ids'] ?? []),
                ),
            ],
            'update_punishment' => [
                'permission' => static fn() => (string) ($_POST['filter_punish_type'] ?? 'ban') === 'mute' ? 'mutes.update' : 'bans.update',
                'denied' => 'forbid',
                'handler' => static fn(ModuleContainer $c) => $c->punishment()->updatePunishment(
                    (string) ($_POST['type'] ?? ''),
                    (string) ($_POST['filter_punish_type'] ?? 'ban'),
                    (int) ($_POST['punish_id'] ?? 0),
                    isset($_POST['ip']) ? (string) $_POST['ip'] : null,
                    (int) ($_POST['punish_type'] ?? 0),
                    (string) ($_POST['reason'] ?? ''),
                    (int) ($_POST['expire'] ?? 0),
                    (array) ($_POST['servers'] ?? []),
                ),
            ],
        ];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        return [
            'terms' => $container->renders()->renderTerms('punishments'),
            'servers' => $container->servers()->getServers(),
            'defaultAllServers' => $container->defaultAllServers(),
        ];
    }
}
