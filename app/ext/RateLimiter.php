<?php
namespace app\ext;

class RateLimiter {
    protected $rateLimit;
    protected $timeWindow;

    public function __construct($rateLimit, $timeWindow) {
        $this->rateLimit = $rateLimit;
        $this->timeWindow = $timeWindow;
    }

    public function isAllowed() {
        $storageDir = CACHE . 'limits/';
        $storageFile = $storageDir . md5($_SERVER['REMOTE_ADDR']) . '.json';

        $this->cleanUpExpiredFiles($storageDir);

        $requestLog = [];

        if (file_exists($storageFile)) {
            $requestLog = json_decode(file_get_contents($storageFile), true) ?? [];
            $requestLog = array_filter($requestLog, fn($timestamp) => $timestamp > time() - $this->timeWindow);
        }

        if (count($requestLog) >= $this->rateLimit) {
            return false;
        }

        $requestLog[] = time();
        file_put_contents($storageFile, json_encode($requestLog));

        return true;
    }

    private function cleanUpExpiredFiles($directory) {
        if (!is_dir($directory)) {
            return;
        }

        $files = glob($directory . '*.json');
        $currentTime = time();

        foreach ($files as $file) {
            $requestLog = json_decode(file_get_contents($file), true);

            if (empty($requestLog) || max($requestLog) < $currentTime - $this->timeWindow) {
                unlink($file);
            }
        }
    }
}