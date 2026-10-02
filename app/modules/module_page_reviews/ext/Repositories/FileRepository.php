<?php

namespace app\modules\module_page_reviews\ext\Repositories;

class FileRepository
{
    private const MODULE_CACHE = 'module_page_reviews';

    private function moduleCacheDir(): string
    {
        return STORAGE . 'modules_cache/' . self::MODULE_CACHE . '/';
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        if (is_dir($dir)) {
            @chmod($dir, 0777);
        }
    }

    private function filePath(string $file): string
    {
        return $this->moduleCacheDir() . $file . '.json';
    }

    public function exists(string $file): bool
    {
        return is_file($this->filePath($file));
    }

    private function ensureFile(string $file): string
    {
        $baseDir = $this->moduleCacheDir();
        $this->ensureDir($baseDir);

        $path = $this->filePath($file);
        if (!file_exists($path)) {
            if (@file_put_contents($path, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
                @chmod($path, 0777);
            }
        }

        return $path;
    }

    public function get(string $file): array
    {
        $path = $this->ensureFile($file);
        $content = @file_get_contents($path);
        if ($content === false || $content === '') {
            return [];
        }

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    public function update(string $file, callable $callback): void
    {
        $data = $this->get($file);
        $newData = $callback($data);
        if (!is_array($newData)) {
            return;
        }

        $path = $this->ensureFile($file);
        $json = json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return;
        }

        $tmp = $path . '.tmp';
        if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
            return;
        }
        @chmod($tmp, 0777);
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            return;
        }
        @chmod($path, 0777);
    }
}
