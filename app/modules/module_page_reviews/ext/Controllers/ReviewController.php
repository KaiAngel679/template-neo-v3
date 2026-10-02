<?php

namespace app\modules\module_page_reviews\ext\Controllers;

use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Repositories\CommentRepository;
use app\modules\module_page_reviews\ext\Repositories\CriteriaRepository;
use app\modules\module_page_reviews\ext\Repositories\ReviewRepository;
use app\modules\module_page_reviews\ext\Services\BanService;
use app\modules\module_page_reviews\ext\Services\DiscordNotifyService;
use app\modules\module_page_reviews\ext\Services\PlaytimeService;
use app\modules\module_page_reviews\ext\Services\PlayerMetaService;
use app\modules\module_page_reviews\ext\Services\ReviewService;
use app\modules\module_page_reviews\ext\Services\RewardService;
use app\modules\module_page_reviews\ext\Services\ServersService;
use app\modules\module_page_reviews\ext\Services\SettingsService;

class ReviewController
{
    private $service;

    public function __construct(
        object $Db,
        object $General,
        ?RewardService $reward = null,
        ?SettingsService $settings = null,
        ?object $Translate = null,
        ?BanService $bans = null
    ) {
        $discord = ($settings !== null && $Translate !== null)
            ? new DiscordNotifyService($settings, $General, $Translate)
            : null;

        $this->service = new ReviewService(
            $Db,
            new ReviewRepository($Db),
            new CommentRepository($Db),
            new PlaytimeService($Db),
            new ServersService($General, $Translate),
            new CriteriaRepository($Db),
            new PlayerMetaService($Db),
            $General,
            $reward,
            $settings,
            $discord,
            $bans,
            $Translate
        );
    }

    public function list(): array
    {
        $stars = $_POST['stars'] ?? [];
        if (!is_array($stars)) {
            $stars = [];
        }

        return $this->service->list(
            [
                'search' => ModuleHelper::clampSearch((string) ($_POST['search'] ?? '')),
                'stars' => $stars,
            ],
            (string) ($_POST['sort'] ?? 'date'),
            (int) ($_POST['limit'] ?? 20),
            (int) ($_POST['offset'] ?? 0)
        );
    }

    public function create(): array
    {
        $attrs = $_POST['attrs'] ?? [];
        if (!is_array($attrs)) {
            $attrs = [];
        }

        return $this->service->create([
            'server_id' => $_POST['server_id'] ?? -1,
            'pros' => $_POST['pros'] ?? '',
            'cons' => $_POST['cons'] ?? '',
            'comment' => $_POST['comment'] ?? '',
            'attrs' => $attrs,
            'overall' => $_POST['overall'] ?? 0,
        ]);
    }

    public function update(): array
    {
        $attrs = $_POST['attrs'] ?? $_POST['edit_attrs'] ?? [];
        if (!is_array($attrs)) {
            $attrs = [];
        }

        return $this->service->update((int) ($_POST['id'] ?? 0), [
            'server_id' => $_POST['server_id'] ?? -1,
            'pros' => $_POST['pros'] ?? '',
            'cons' => $_POST['cons'] ?? '',
            'comment' => $_POST['comment'] ?? '',
            'attrs' => $attrs,
            'overall' => $_POST['overall'] ?? 0,
        ]);
    }

    public function delete(): array
    {
        return $this->service->delete((int) ($_POST['id'] ?? 0));
    }

    public function vote(): array
    {
        return $this->service->vote((int) ($_POST['id'] ?? 0), (int) ($_POST['value'] ?? 0));
    }

    public function getForEdit(): array
    {
        return $this->service->getForEdit((int) ($_POST['id'] ?? 0));
    }

    public function getView(): array
    {
        $stars = $_POST['stars'] ?? [];
        if (!is_array($stars)) {
            $stars = [];
        }

        return $this->service->getView(
            (int) ($_POST['id'] ?? 0),
            [
                'search' => ModuleHelper::clampSearch((string) ($_POST['search'] ?? '')),
                'stars' => $stars,
            ],
            (string) ($_POST['sort'] ?? 'date'),
            (int) ($_POST['limit'] ?? 20)
        );
    }

    public function service(): ReviewService
    {
        return $this->service;
    }
}
