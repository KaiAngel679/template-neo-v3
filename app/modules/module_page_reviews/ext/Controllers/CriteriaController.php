<?php

namespace app\modules\module_page_reviews\ext\Controllers;

use app\modules\module_page_reviews\ext\Repositories\CriteriaRepository;
use app\modules\module_page_reviews\ext\Services\CriteriaService;

class CriteriaController
{
    private $service;

    public function __construct(object $Db, ?object $Translate = null)
    {
        $this->service = new CriteriaService(new CriteriaRepository($Db), $Translate);
    }

    public function list(): array
    {
        return $this->service->listAll();
    }

    public function listActive(): array
    {
        return $this->service->listActiveFormatted();
    }

    public function create(): array
    {
        $display = $_POST['display'] ?? [];
        if (!is_array($display)) {
            $display = [];
        }

        if ($display === [] && isset($_POST['name'])) {
            $display = ['ru' => (string) $_POST['name']];
        }

        return $this->service->create($display);
    }

    public function update(): array
    {
        $display = $_POST['display'] ?? [];
        if (!is_array($display)) {
            $display = [];
        }
        if ($display === [] && isset($_POST['name'])) {
            $display = ['ru' => (string) $_POST['name']];
        }

        return $this->service->update(
            (int) ($_POST['id'] ?? 0),
            $display
        );
    }

    public function delete(): array
    {
        return $this->service->delete((int) ($_POST['id'] ?? 0));
    }

    public function reorder(): array
    {
        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids)) {
            $ids = [];
        }

        return $this->service->reorder($ids);
    }

    public function service(): CriteriaService
    {
        return $this->service;
    }
}
