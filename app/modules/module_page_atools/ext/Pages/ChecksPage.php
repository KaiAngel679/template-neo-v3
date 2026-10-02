<?php

namespace app\modules\module_page_atools\ext\Pages;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\ModuleContainer;

final class ChecksPage extends AbstractPage
{
    public function isAccessible(AccessController $access, string $section): bool
    {
        return $access->checkPermission('checks.view');
    }

    protected function actions(): array
    {
        return [
            'get_checks_list' => [
                'handler' => static fn(ModuleContainer $c) => $c->check()->getChecksList(
                    (int) ($_POST['admin'] ?? -1),
                    (array) ($_POST['servers'] ?? []),
                    (string) ($_POST['verdict'] ?? 'all'),
                    (string) ($_POST['date_from'] ?? ''),
                    (string) ($_POST['date_to'] ?? ''),
                    (int) ($_POST['limit'] ?? 10),
                    (int) ($_POST['offset'] ?? 0),
                    (string) ($_POST['search'] ?? ''),
                ),
            ],
            'delete_checks' => [
                'permission' => 'checks.delete',
                'handler' => static fn(ModuleContainer $c) => $c->check()->deleteChecks(
                    (array) ($_POST['check_ids'] ?? []),
                ),
            ],
        ];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        $renders = $container->renders();

        return [
            'verdicts' => $renders->renderVerdicts(),
            'admins' => $renders->renderAdmins('cs2'),
            'servers' => $container->servers()->getServers('cs2'),
        ];
    }
}
