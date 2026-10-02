<?php

namespace app\modules\module_page_atools\ext\Pages;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\ModuleContainer;

final class CreditsPage extends AbstractPage
{
    public function isAccessible(AccessController $access, string $section): bool
    {
        return $access->checkPermission('credits.view');
    }

    protected function actions(): array
    {
        return [];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        return [];
    }
}
