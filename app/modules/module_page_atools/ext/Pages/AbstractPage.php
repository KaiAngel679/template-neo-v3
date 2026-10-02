<?php

namespace app\modules\module_page_atools\ext\Pages;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\Http\JsonResponse;
use app\modules\module_page_atools\ext\ModuleContainer;

abstract class AbstractPage
{
    abstract public function isAccessible(AccessController $access, string $section): bool;

    abstract protected function actions(): array;

    abstract public function context(ModuleContainer $container, string $section): array;

    public function dispatch(ModuleContainer $container): void
    {
        foreach ($this->actions() as $postKey => $action) {
            if (!isset($_POST[$postKey])) {
                continue;
            }

            $permission = $action['permission'] ?? null;
            if ($permission !== null) {
                $perm = is_callable($permission) ? $permission($container) : $permission;
                if ($perm !== null && !$container->access()->checkPermission($perm)) {
                    if (($action['denied'] ?? 'ignore') === 'forbid') {
                        JsonResponse::forbidden();
                    }
                    continue;
                }
            }

            JsonResponse::send(($action['handler'])($container));
        }
    }
}