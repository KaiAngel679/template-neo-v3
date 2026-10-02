<?php

namespace app\modules\module_page_atools\ext;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\Controllers\AdminController;
use app\modules\module_page_atools\ext\Controllers\CatalogController;
use app\modules\module_page_atools\ext\Controllers\CheckController;
use app\modules\module_page_atools\ext\Controllers\DatabaseController;
use app\modules\module_page_atools\ext\Controllers\FileController;
use app\modules\module_page_atools\ext\Controllers\ExperienceController;
use app\modules\module_page_atools\ext\Controllers\FinanceController;
use app\modules\module_page_atools\ext\Controllers\LogsController;
use app\modules\module_page_atools\ext\Controllers\MainController;
use app\modules\module_page_atools\ext\Controllers\PlayerSearchController;
use app\modules\module_page_atools\ext\Controllers\PrivilegesController;
use app\modules\module_page_atools\ext\Controllers\PunishmentController;
use app\modules\module_page_atools\ext\Controllers\RendersController;
use app\modules\module_page_atools\ext\Controllers\ServersController;
use app\modules\module_page_atools\ext\Controllers\SettingsController;

final class ModuleContainer
{
    public $Db, $General, $Translate, $Modules;
    private $access, $database, $renders, $servers, $catalog, $main, $admin, $punishment, $check, $privileges, $finances, $experience, $logs, $settings, $file, $playerSearch;

    public function __construct(object $Db, object $General, object $Translate, object $Modules)
    {
        $this->Db = $Db;
        $this->General = $General;
        $this->Translate = $Translate;
        $this->Modules = $Modules;
        $this->access = null;
        $this->database = null;
        $this->renders = null;
        $this->servers = null;
        $this->catalog = null;
        $this->main = null;
        $this->admin = null;
        $this->punishment = null;
        $this->check = null;
        $this->privileges = null;
        $this->finances = null;
        $this->experience = null;
        $this->logs = null;
        $this->settings = null;
        $this->file = null;
        $this->playerSearch = null;
    }

    public function access(): AccessController
    {
        if ($this->access === null) {
            $this->access = new AccessController($this->Db);
        }

        return $this->access;
    }

    public function database(): DatabaseController
    {
        if ($this->database === null) {
            $this->database = new DatabaseController($this->Db, $this->Translate);
        }

        return $this->database;
    }

    public function renders(): RendersController
    {
        if ($this->renders === null) {
            $this->renders = new RendersController($this->Db, $this->General, $this->Translate);
        }

        return $this->renders;
    }

    public function servers(): ServersController
    {
        if ($this->servers === null) {
            $this->servers = new ServersController($this->General);
        }

        return $this->servers;
    }

    public function catalog(): CatalogController
    {
        if ($this->catalog === null) {
            $this->catalog = new CatalogController();
        }

        return $this->catalog;
    }

    public function main(): MainController
    {
        if ($this->main === null) {
            $this->main = new MainController($this->Db, $this->General, $this->Translate);
        }

        return $this->main;
    }

    public function admin(): AdminController
    {
        if ($this->admin === null) {
            $this->admin = new AdminController($this->Db, $this->General, $this->Translate, $this->Modules);
        }

        return $this->admin;
    }

    public function punishment(): PunishmentController
    {
        if ($this->punishment === null) {
            $this->punishment = new PunishmentController($this->Db, $this->General, $this->Translate, $this->Modules);
        }

        return $this->punishment;
    }

    public function check(): CheckController
    {
        if ($this->check === null) {
            $this->check = new CheckController($this->Db, $this->General, $this->Translate);
        }

        return $this->check;
    }

    public function privileges(): PrivilegesController
    {
        if ($this->privileges === null) {
            $this->privileges = new PrivilegesController($this->Db, $this->General, $this->Translate, $this->Modules);
        }

        return $this->privileges;
    }

    public function finances(): FinanceController
    {
        if ($this->finances === null) {
            $this->finances = new FinanceController($this->Db, $this->General, $this->Translate);
        }

        return $this->finances;
    }

    public function experience(): ExperienceController
    {
        if ($this->experience === null) {
            $this->experience = new ExperienceController($this->Db, $this->General, $this->Translate, $this->Modules);
        }

        return $this->experience;
    }

    public function logs(): LogsController
    {
        if ($this->logs === null) {
            $this->logs = new LogsController($this->Db, $this->General, $this->Translate, $this->Modules);
        }

        return $this->logs;
    }

    public function settings(): SettingsController
    {
        if ($this->settings === null) {
            $this->settings = new SettingsController($this->Translate);
        }

        return $this->settings;
    }

    public function file(): FileController
    {
        if ($this->file === null) {
            $this->file = new FileController();
        }

        return $this->file;
    }

    public function defaultAllServers(): bool
    {
        return !empty($this->file()->get('settings')['default_all_servers']);
    }

    public function playerSearch(): PlayerSearchController
    {
        if ($this->playerSearch === null) {
            $this->playerSearch = new PlayerSearchController($this->Db, $this->General, $this->Translate, $this->Modules);
        }

        return $this->playerSearch;
    }
}
