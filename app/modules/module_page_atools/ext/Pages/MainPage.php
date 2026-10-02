<?php

namespace app\modules\module_page_atools\ext\Pages;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\ModuleContainer;

final class MainPage extends AbstractPage
{
    public function isAccessible(AccessController $access, string $section): bool
    {
        return $access->hasAnyPermission();
    }

    protected function actions(): array
    {
        return [
            'get_main_charts' => [
                'permission' => 'punishments.view',
                'denied' => 'forbid',
                'handler' => static fn(ModuleContainer $c) => $c->main()->getCharts(
                    (int) ($_POST['days'] ?? 7),
                    (string) ($_POST['chart'] ?? 'bans'),
                ),
            ],
        ];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        $access = $container->access();
        $main = $container->main();
        $dash = $main->getPageStats();

        $topAllowed = [];
        if ($access->checkPermission('punishments.view')) {
            $topAllowed[] = 'ban';
            $topAllowed[] = 'mute';
        }
        if ($access->checkPermission('checks.view') && $main->hasChecksBackend()) {
            $topAllowed[] = 'check';
        }
        if (!empty($dash['reports_available'])) {
            $topAllowed[] = 'report';
        }

        $topMetrics = $main->getTopMetrics($topAllowed);
        $topAdminsByMetric = [];
        foreach ($topMetrics as $meta) {
            $topAdminsByMetric[$meta['metric']] = $main->getTopAdmins($meta['metric'])['data'] ?? [];
        }

        return compact('dash', 'topMetrics', 'topAdminsByMetric');
    }
}
