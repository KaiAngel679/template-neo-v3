<?php

namespace app\modules\module_page_atools\ext\Pages;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\ModuleContainer;

final class PrivilegesPage extends AbstractPage
{
    public function isAccessible(AccessController $access, string $section): bool
    {
        return $access->checkPermission('privileges.view');
    }

    protected function actions(): array
    {
        return [
            'get_catalog' => [
                'handler' => static fn(ModuleContainer $c) => [
                    'status' => 'success',
                    'data' => $c->catalog()->privileges($c->servers(), $c->renders()),
                ],
            ],
            'create_privilege' => [
                'permission' => 'privileges.create',
                'handler' => static fn(ModuleContainer $c) => $c->privileges()->createPrivilege(
                    (string) ($_POST['steamid'] ?? ''),
                    (string) ($_POST['group'] ?? ''),
                    $_POST['expire'] ?? '',
                    (array) ($_POST['servers'] ?? []),
                ),
            ],
            'get_privileges_list' => [
                'handler' => static fn(ModuleContainer $c) => $c->privileges()->getPrivilegesList(
                    (array) ($_POST['servers'] ?? []),
                    (string) ($_POST['group'] ?? '-1'),
                    (int) ($_POST['limit'] ?? 10),
                    (int) ($_POST['offset'] ?? 0),
                    (string) ($_POST['search'] ?? ''),
                    (string) ($_POST['expire_filter'] ?? 'all'),
                ),
            ],
            'delete_privileges' => [
                'permission' => 'privileges.delete',
                'handler' => static fn(ModuleContainer $c) => $c->privileges()->deletePrivileges(
                    (array) ($_POST['privilege_ids'] ?? []),
                ),
            ],
            'update_privilege' => [
                'permission' => 'privileges.update',
                'handler' => static fn(ModuleContainer $c) => $c->privileges()->updatePrivilege(
                    (string) ($_POST['privilege_id'] ?? ''),
                    (string) ($_POST['group'] ?? ''),
                    $_POST['expire'] ?? '',
                    (array) ($_POST['servers'] ?? []),
                ),
            ],
        ];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        $renders = $container->renders();

        return [
            'terms' => $renders->renderTerms('vips'),
            'groups' => $renders->renderVipGroups(),
            'filterGroups' => $renders->renderVipGroups(true),
            'servers' => $container->servers()->getServers(),
            'defaultAllServers' => $container->defaultAllServers(),
        ];
    }
}
