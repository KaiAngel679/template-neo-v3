<?php

namespace app\modules\module_page_atools\ext\Pages;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\ModuleContainer;

final class AdminsPage extends AbstractPage
{
    public function isAccessible(AccessController $access, string $section): bool
    {
        return $access->checkPermission('admins.view');
    }

    protected function actions(): array
    {
        return [
            'get_catalog' => [
                'handler' => static fn(ModuleContainer $c) => [
                    'status' => 'success',
                    'data' => $c->catalog()->admins($c->database(), $c->servers(), $c->renders(), $c->General),
                ],
            ],
            'create_admin' => [
                'permission' => 'admins.create',
                'handler' => static fn(ModuleContainer $c) => $c->admin()->createAdmin(
                    (string) ($_POST['steamid'] ?? ''),
                    (string) ($_POST['group_type'] ?? ''),
                    (string) ($_POST['group'] ?? ''),
                    (string) ($_POST['expire'] ?? ''),
                    (array) ($_POST['servers'] ?? []),
                    (array) ($_POST['permissions'] ?? []),
                    (string) ($_POST['vip_group'] ?? ''),
                ),
            ],
            'delete_admin' => [
                'permission' => 'admins.delete',
                'handler' => static fn(ModuleContainer $c) => $c->admin()->deleteAdmin(
                    (int) ($_POST['admin_id'] ?? 0),
                    (string) ($_POST['type'] ?? ''),
                ),
            ],
            'update_admin' => [
                'permission' => 'admins.update',
                'handler' => static fn(ModuleContainer $c) => $c->admin()->updateAdmin(
                    (int) ($_POST['admin_id'] ?? 0),
                    (string) ($_POST['type'] ?? ''),
                    (string) ($_POST['group'] ?? ''),
                    (string) ($_POST['expire'] ?? ''),
                    (array) ($_POST['servers'] ?? []),
                    (array) ($_POST['permissions'] ?? []),
                ),
            ],
            'give_warn' => [
                'permission' => 'admins.warn.give',
                'handler' => static fn(ModuleContainer $c) => $c->admin()->giveWarn(
                    (string) ($_POST['target_steamid'] ?? ''),
                    (string) ($_POST['reason'] ?? ''),
                    (int) ($_POST['expire'] ?? 0),
                    (int) ($_POST['admin_id'] ?? 0),
                    (string) ($_POST['type'] ?? ''),
                ),
            ],
            'remove_warns' => [
                'permission' => 'admins.warn.remove',
                'handler' => static fn(ModuleContainer $c) => $c->admin()->removeWarns(
                    (string) ($_POST['target_steamid'] ?? ''),
                    (array) ($_POST['warn_ids'] ?? []),
                ),
            ],
            'delete_warns' => [
                'permission' => 'admins.warn.delete',
                'handler' => static fn(ModuleContainer $c) => $c->admin()->deleteWarns(
                    (string) ($_POST['target_steamid'] ?? ''),
                    (array) ($_POST['warn_ids'] ?? []),
                ),
            ],
            'update_warn' => [
                'permission' => 'admins.warn.update',
                'handler' => static fn(ModuleContainer $c) => $c->admin()->updateWarn(
                    (string) ($_POST['target_steamid'] ?? ''),
                    (int) ($_POST['warn_id'] ?? 0),
                    (string) ($_POST['reason'] ?? ''),
                    (string) ($_POST['expire'] ?? ''),
                ),
            ],
            'get_admins_list' => [
                'handler' => static fn(ModuleContainer $c) => $c->admin()->getAdminsList(
                    (string) ($_POST['type'] ?? ''),
                    (array) ($_POST['servers'] ?? []),
                    (int) ($_POST['group'] ?? -1),
                    (int) ($_POST['limit'] ?? 10),
                    (int) ($_POST['offset'] ?? 0),
                    (string) ($_POST['search'] ?? ''),
                    (array) ($_POST['access_permissions'] ?? []),
                ),
            ],
        ];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        $database = $container->database();
        $renders = $container->renders();

        return [
            'csgo' => $database->getGroupsGame('csgo'),
            'cs2' => $database->getGroupsGame('cs2'),
            'terms' => $renders->renderTerms('admins'),
            'groups' => $renders->renderGroups(),
            'vipGroups' => $renders->renderVipGroups(),
            'defaultAllServers' => $container->defaultAllServers(),
        ];
    }
}
