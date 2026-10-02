<?php

namespace app\modules\module_page_atools\ext;

use app\modules\module_page_atools\ext\Http\JsonResponse;
use app\modules\module_page_atools\ext\Http\PageHandleResult;
use app\modules\module_page_atools\ext\Pages\PageRegistry;

final class ModuleKernel
{

    private $Db, $General, $Translate, $Modules;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->Db = $Db;
        $this->General = $General;
        $this->Translate = $Translate;
        $this->Modules = $Modules;
    }

    public function handle(object $Router): PageHandleResult
    {
        $Router->map('GET|POST', 'atools/[:page]/[:section]?/', 'main');
        $Map = $Router->match();

        $view = $Map['params']['page'] ?? 'main';
        $section = $Map['params']['section'] ?? 'general';

        if (empty($_SESSION['steamid'])) {
            return PageHandleResult::iframe(401, 'Not authorized');
        }

        $container = new ModuleContainer($this->Db, $this->General, $this->Translate, $this->Modules);

        if (!empty($_POST['global_search'])) {
            if (!$container->access()->hasAnyPermission()) {
                JsonResponse::forbidden();
            }

            JsonResponse::send($container->playerSearch()->search((string) ($_POST['search'] ?? '')));
        }

        $page = PageRegistry::resolve($view);
        if ($page === null) {
            return PageHandleResult::iframe(404, 'Page not found');
        }

        $access = $container->access();

        if (!$page->isAccessible($access, $section)) {
            return PageHandleResult::iframe(403, 'Forbidden');
        }

        $page->dispatch($container);

        return PageHandleResult::ok(array_merge(
            [
                'view' => $view,
                'section' => $section,
                'AccessController' => $access,
            ],
            $page->context($container, $section)
        ));
    }
}
