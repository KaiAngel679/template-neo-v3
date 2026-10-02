<?php

namespace app\modules\module_page_reviews\ext;

use app\modules\module_page_reviews\ext\Controllers\BanController;
use app\modules\module_page_reviews\ext\Controllers\CommentController;
use app\modules\module_page_reviews\ext\Controllers\CriteriaController;
use app\modules\module_page_reviews\ext\Controllers\DatabaseController;
use app\modules\module_page_reviews\ext\Controllers\ReviewController;
use app\modules\module_page_reviews\ext\Controllers\ServersController;
use app\modules\module_page_reviews\ext\Controllers\SettingsController;
use app\modules\module_page_reviews\ext\Controllers\SummaryController;
use app\modules\module_page_reviews\ext\Repositories\RewardRepository;
use app\modules\module_page_reviews\ext\Services\RewardService;
use app\modules\module_page_reviews\ext\Services\SettingsService;
use app\modules\module_page_reviews\ext\Repositories\FileRepository;

final class ModuleContainer
{
    public $Db;
    public $General;
    public $Translate;
    public $Modules;

    private $database;
    private $servers;
    private $reviews;
    private $comments;
    private $summary;
    private $criteria;
    private $settings;
    private $reward;
    private $bans;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->Db = $Db;
        $this->General = $General;
        $this->Translate = $Translate;
        $this->Modules = $Modules;
        $this->database = null;
        $this->servers = null;
        $this->reviews = null;
        $this->comments = null;
        $this->summary = null;
        $this->criteria = null;
        $this->settings = null;
        $this->reward = null;
        $this->bans = null;
    }

    public function database(): DatabaseController
    {
        if ($this->database === null) {
            $this->database = new DatabaseController($this->Db);
        }

        return $this->database;
    }

    public function servers(): ServersController
    {
        if ($this->servers === null) {
            $this->servers = new ServersController($this->General, $this->Translate);
        }

        return $this->servers;
    }

    public function reviews(): ReviewController
    {
        if ($this->reviews === null) {
            $this->reviews = new ReviewController(
                $this->Db,
                $this->General,
                $this->reward(),
                $this->settings()->service(),
                $this->Translate,
                $this->bans()->service()
            );
        }

        return $this->reviews;
    }

    public function comments(): CommentController
    {
        if ($this->comments === null) {
            $this->comments = new CommentController(
                $this->Db,
                $this->General,
                $this->bans()->service(),
                $this->Translate
            );
        }

        return $this->comments;
    }

    public function summary(): SummaryController
    {
        if ($this->summary === null) {
            $this->summary = new SummaryController($this->Db);
        }

        return $this->summary;
    }

    public function criteria(): CriteriaController
    {
        if ($this->criteria === null) {
            $this->criteria = new CriteriaController($this->Db, $this->Translate);
        }

        return $this->criteria;
    }

    public function settings(): SettingsController
    {
        if ($this->settings === null) {
            $this->settings = new SettingsController($this->Db, $this->Translate);
        }

        return $this->settings;
    }

    public function bans(): BanController
    {
        if ($this->bans === null) {
            $this->bans = new BanController($this->General, $this->Db, $this->Translate);
        }

        return $this->bans;
    }

    public function reward(): RewardService
    {
        if ($this->reward === null) {
            $this->reward = new RewardService(
                new RewardRepository($this->Db),
                new SettingsService(new FileRepository(), $this->Translate),
                $this->Translate
            );
        }

        return $this->reward;
    }
}
