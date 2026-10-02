<?php

namespace app\modules\module_page_reviews\ext\Pages;

use app\modules\module_page_reviews\ext\Http\JsonResponse;
use app\modules\module_page_reviews\ext\ModuleContainer;
use app\modules\module_page_reviews\ext\ModuleHelper;

abstract class AbstractPage
{
    abstract public function isAccessible(string $section): bool;

    abstract protected function actions(): array;

    abstract public function context(ModuleContainer $container, string $section): array;

    public function dispatch(ModuleContainer $container): void
    {
        foreach ($this->actions() as $postKey => $action) {
            if (!isset($_POST[$postKey])) {
                continue;
            }

            if (!empty($action['auth']) && ModuleHelper::sessionSteam() === '') {
                JsonResponse::unauthorized();
            }

            if (!empty($action['admin']) && !ModuleHelper::isAdmin()) {
                JsonResponse::forbidden();
            }

            JsonResponse::send(($action['handler'])($container));
        }
    }
}
