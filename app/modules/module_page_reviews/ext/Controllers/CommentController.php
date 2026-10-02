<?php

namespace app\modules\module_page_reviews\ext\Controllers;

use app\modules\module_page_reviews\ext\Repositories\CommentRepository;
use app\modules\module_page_reviews\ext\Repositories\FileRepository;
use app\modules\module_page_reviews\ext\Repositories\ReviewRepository;
use app\modules\module_page_reviews\ext\Services\BanService;
use app\modules\module_page_reviews\ext\Services\CommentService;
use app\modules\module_page_reviews\ext\Services\PlayerMetaService;
use app\modules\module_page_reviews\ext\Services\SettingsService;

class CommentController
{
    private $service;

    public function __construct(object $Db, object $General, ?BanService $bans = null, ?object $Translate = null)
    {
        $this->service = new CommentService(
            new CommentRepository($Db),
            new ReviewRepository($Db),
            new PlayerMetaService($Db),
            $General,
            new SettingsService(new FileRepository(), $Translate),
            $bans,
            $Translate
        );
    }

    public function thread(): array
    {
        return $this->service->thread((int) ($_POST['review_id'] ?? 0));
    }

    public function create(): array
    {
        $parent = $_POST['parent_id'] ?? null;
        $parentId = ($parent === null || $parent === '' || (int) $parent === 0) ? null : (int) $parent;

        return $this->service->create(
            (int) ($_POST['review_id'] ?? 0),
            (string) ($_POST['text'] ?? ''),
            $parentId
        );
    }

    public function vote(): array
    {
        return $this->service->vote((int) ($_POST['id'] ?? 0), (int) ($_POST['value'] ?? 0));
    }

    public function update(): array
    {
        return $this->service->update((int) ($_POST['id'] ?? 0), (string) ($_POST['text'] ?? ''));
    }

    public function delete(): array
    {
        return $this->service->delete((int) ($_POST['id'] ?? 0));
    }
}
