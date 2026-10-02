<?php

namespace app\modules\module_page_atools\ext\Repositories;

use app\modules\module_page_atools\ext\Services\LogsService;

class FileRepository
{
    private function assetsCacheDir(): string
    {
        return MODULES . MODULE_NAME . '/assets/cache/';
    }

    private function moduleCacheDir(): string
    {
        return STORAGE . 'modules_cache/' . MODULE_NAME . '/';
    }

    private function baseDirForFile(string $file): string
    {
        static $settingsKeys = [
            'settings',
            'terms',
            'reasons',
            'groups',
            'vip_groups',
        ];

        if (in_array($file, $settingsKeys, true)) {
            return $this->moduleCacheDir();
        }

        return $this->assetsCacheDir();
    }

    private function logStorageFailure(string $operation, string $message, string $fileKey, ?string $path = null, ?string $detail = null): void
    {
        if (strpos($fileKey, 'error/') === 0) {
            return;
        }
        (new LogsService())->addError([
            'kind' => 'filesystem',
            'action' => $operation,
            'message' => $message,
            'context' => array_filter([
                'file_key' => $fileKey,
                'path' => $path,
                'detail' => $detail,
            ]),
        ]);
    }

    private function ensureFile(string $file): string
    {
        $baseDir = $this->baseDirForFile($file);
        $path = $baseDir . $file . '.json';
        $parent = dirname($path);
        if ($parent !== '' && $parent !== '.' && !is_dir($parent)) {
            @mkdir($parent, 0777, true);
            if (!is_dir($parent)) {
                $this->logStorageFailure('ensure_subdir', 'mkdir failed', $file, $parent);
            }
        }

        if (!is_dir($this->assetsCacheDir())) {
            @mkdir($this->assetsCacheDir(), 0777, true);
        }
        if (!is_dir($this->assetsCacheDir())) {
            $this->logStorageFailure('ensure_cache_dir', 'mkdir failed', $file, $this->assetsCacheDir());
        }

        if (!file_exists($path)) {
            if (@file_put_contents($path, json_encode([], JSON_PRETTY_PRINT)) === false) {
                $this->logStorageFailure('ensure_empty_json', 'Initial write failed', $file, $path);
            }
        }

        return $path;
    }

    public function get(string $file): array
    {
        try {
            $path = $this->ensureFile($file);

            $content = file_get_contents($path);

            if ($content === false || $content === '') {
                return [];
            }

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logStorageFailure('file_repository_get', 'JSON decode failed', $file, $path, json_last_error_msg());
                return [];
            }

            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            $this->logStorageFailure('file_repository_get', 'Exception', $file, $path ?? null, $e->getMessage());
            return [];
        }
    }

    public function update(string $file, callable $callback): void
    {
        try {
            $data = $this->get($file);

            $newData = $callback($data);

            if (!is_array($newData)) {
                $this->logStorageFailure('file_repository_update', 'Callback not array', $file, null, null);
                return;
            }

            if (isset($newData['status']) && isset($newData['message'])) {
                $this->logStorageFailure('file_repository_update', 'Invalid data structure', $file, null, null);
                return;
            }

            $this->write($file, $newData);
        } catch (\Throwable $e) {
            $this->logStorageFailure('file_repository_update', 'Exception', $file, null, $e->getMessage());
        }
    }

    private function write(string $file, array $data): void
    {
        $path = null;
        try {
            $path = $this->ensureFile($file);

            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            if ($json === false) {
                $this->logStorageFailure('file_repository_write', 'JSON encode failed', $file, $path, null);
                return;
            }

            $tmp = $path . '.tmp';

            if (file_put_contents($tmp, $json, LOCK_EX) === false) {
                $this->logStorageFailure('file_repository_write', 'File put contents tmp failed', $file, $tmp, null);
                return;
            }

            if (!rename($tmp, $path)) {
                $this->logStorageFailure('file_repository_write', 'Rename tmp to target failed', $file, $path, null);
            }
        } catch (\Throwable $e) {
            $this->logStorageFailure('file_repository_write', 'Exception', $file, $path, $e->getMessage());
        }
    }

    public function delete(string $file): void
    {
        try {
            $path = $this->assetsCacheDir() . $file . '.json';

            if (!file_exists($path)) {
                return;
            }

            if (!unlink($path)) {
                $this->logStorageFailure('file_repository_delete', 'Unlink failed', $file, $path, null);
            }
        } catch (\Throwable $e) {
            $this->logStorageFailure('file_repository_delete', 'Exception', $file, $path ?? null, $e->getMessage());
        }
    }

    public function getLogFiles(string $folder): array
    {
        $path = $this->assetsCacheDir() . $folder;

        if (!is_dir($path)) {
            return [];
        }

        $files = glob($path . '/*.json');

        if (!$files) {
            return [];
        }

        rsort($files);

        return array_map('basename', $files);
    }
}
