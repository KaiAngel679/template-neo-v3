<?php

namespace app\modules\module_page_atools\ext\Pages;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\ModuleContainer;

final class LogsPage extends AbstractPage
{
    public function isAccessible(AccessController $access, string $section): bool
    {
        return $access->checkPermission('logs.view');
    }

    protected function actions(): array
    {
        return [
            'get_logs_list' => [
                'handler' => static fn(ModuleContainer $c) => $c->logs()->getList(
                    (string) $_SESSION['steamid'],
                    (string) ($_POST['category'] ?? 'all'),
                    (string) ($_POST['sort'] ?? 'new'),
                    (string) ($_POST['date_from'] ?? ''),
                    (string) ($_POST['date_to'] ?? ''),
                    (string) ($_POST['search'] ?? ''),
                    (int) ($_POST['limit'] ?? 10),
                    (int) ($_POST['offset'] ?? 0),
                ),
            ],
            'delete_log' => [
                'handler' => static fn(ModuleContainer $c) => $c->logs()->deleteEntry(
                    (string) ($_POST['file_date'] ?? ''),
                    (int) ($_POST['index'] ?? -1),
                    (string) $_SESSION['steamid'],
                ),
            ],
        ];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        return [];
    }
}
