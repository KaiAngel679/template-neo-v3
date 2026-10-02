<?php

namespace app\modules\module_page_atools\ext\Controllers;

use app\modules\module_page_atools\ext\Services\FileService;

class FileController
{
    private $service;

    public function __construct()
    {
        $this->service = new FileService();
    }

    public function get(string $file): array
    {
        return $this->service->get($file);
    }

    public function update(string $file, callable $callback): void
    {
        $this->service->update($file, $callback);
    }
    public function delete(string $file): void
    {
        $this->service->delete($file);
    }
}
