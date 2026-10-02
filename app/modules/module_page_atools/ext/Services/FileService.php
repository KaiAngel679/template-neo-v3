<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Repositories\FileRepository;

class FileService
{
    private $repository;

    public function __construct()
    {
        $this->repository = new FileRepository();
    }

    public function get(string $file): array
    {
        return $this->repository->get($file);
    }

    public function update(string $file, callable $callback): void
    {
        $this->repository->update($file, $callback);
    }
    public function delete(string $file): void
    {
        $this->repository->delete($file);
    }
}
