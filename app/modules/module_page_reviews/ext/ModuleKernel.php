<?php

namespace app\modules\module_page_reviews\ext;

use app\modules\module_page_reviews\ext\Http\PageHandleResult;
use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Pages\PageRegistry;

final class ModuleKernel
{
    private $Db;
    private $General;
    private $Translate;
    private $Modules;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->Db = $Db;
        $this->General = $General;
        $this->Translate = $Translate;
        $this->Modules = $Modules;
    }

    public function handle(object $Router): PageHandleResult
    {
        $Router->map('GET|POST', 'reviews/', 'main');
        $Router->map('GET|POST', 'reviews/main/', 'main');
        $Router->map('GET|POST', 'reviews/settings/[:section]?/', 'settings');
        $Map = $Router->match();

        $target = $Map['target'] ?? 'main';
        $view = $target === 'settings' ? 'settings' : 'main';
        if (($Map['params']['page'] ?? null) === 'settings') {
            $view = 'settings';
        }

        $section = $Map['params']['section'] ?? 'general';
        if ($section === '' || $section === null) {
            $section = 'general';
        }

        $container = new ModuleContainer($this->Db, $this->General, $this->Translate, $this->Modules);
        $container->database()->ensureSchema();

        $page = PageRegistry::resolve($view);
        if ($page === null) {
            return PageHandleResult::iframe(404, 'Page not found');
        }

        if (!$page->isAccessible($section)) {
            return PageHandleResult::iframe(403, 'Forbidden');
        }

        $page->dispatch($container);

        return PageHandleResult::ok(array_merge(
            [
                'view' => $view,
                'section' => $section,
                'isAdmin' => ModuleHelper::isAdmin(),
                'isAuth' => ModuleHelper::sessionSteam() !== '',
                'sessionSteam' => ModuleHelper::sessionSteam(),
            ],
            $page->context($container, $section)
        ));
    }
}
