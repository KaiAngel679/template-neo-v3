<?php

namespace app\modules\module_block_main_reviews\ext;

use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Repositories\DatabaseRepository;
use app\modules\module_page_reviews\ext\Repositories\FileRepository;
use app\modules\module_page_reviews\ext\Repositories\ReviewRepository;
use app\modules\module_page_reviews\ext\Services\SettingsService;

class ReviewsBlockService
{
    private const CACHE_LIMIT = 10;
    private const CACHE_FILE = 'home.json';

    private $Db;
    private $General;

    public function __construct(object $Db, object $General)
    {
        $this->Db = $Db;
        $this->General = $General;
    }

    public function isAvailable(): bool
    {
        return class_exists(ReviewRepository::class)
            && class_exists(SettingsService::class)
            && class_exists(ModuleHelper::class);
    }

    public function getHomeData(int $limit = self::CACHE_LIMIT): array
    {
        $limit = max(1, min(12, $limit));
        $cached = $this->readCache($limit);
        if ($cached !== null) {
            return $cached;
        }

        return $this->buildAndStore($limit);
    }

    public function refreshCache(int $limit = self::CACHE_LIMIT): array
    {
        $this->clearCache();

        return $this->buildAndStore(max(1, min(12, $limit)));
    }

    public function clearCache(): void
    {
        $path = $this->cachePath();
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function buildAndStore(int $limit): array
    {
        $data = $this->buildHomeData($limit);
        $this->writeCache($data, $limit);

        return $data;
    }

    private function buildHomeData(int $limit): array
    {
        $empty = [
            'summary' => [
                'avg' => 0,
                'avg_label' => '0.0',
                'total' => 0,
                'total_label' => '0',
            ],
            'rating_icon' => 'star-fill',
            'reviews' => [],
        ];

        if (!$this->isAvailable()) {
            return $empty;
        }

        try {
            (new DatabaseRepository($this->Db))->createTables();
        } catch (\Throwable $e) {
        }

        $settings = new SettingsService(new FileRepository());
        $repo = new ReviewRepository($this->Db);
        $summary = $repo->getSummary();
        $rows = $repo->list([], 'date', $limit, 0);

        $reviews = [];
        foreach ($rows as $row) {
            $steamid = ModuleHelper::toSteam64((string) ($row['steamid'] ?? ''));
            if ($steamid === '') {
                continue;
            }

            $serverId = (int) ($row['server_id'] ?? -1);
            $serverName = trim((string) ($row['server_name'] ?? ''));

            $pros = $settings->censor(ModuleHelper::sanitizeUserText(trim((string) ($row['pros'] ?? ''))));
            $cons = $settings->censor(ModuleHelper::sanitizeUserText(trim((string) ($row['cons'] ?? ''))));
            $comment = $settings->censor(ModuleHelper::sanitizeUserText(trim((string) ($row['comment'] ?? ''))));

            $parts = [];
            if ($pros !== '') {
                $parts[] = $pros;
            }
            if ($cons !== '') {
                $parts[] = $cons;
            }
            if ($comment !== '') {
                $parts[] = $comment;
            }
            $text = $this->previewText(implode(' ', $parts), 220);

            $overall = round((float) ($row['overall'] ?? 0), 1);
            $createdAt = (int) ($row['created_at'] ?? 0);

            $reviews[] = [
                'id' => (int) ($row['id'] ?? 0),
                'steamid' => $steamid,
                'name' => ModuleHelper::resolveDisplayName($this->General, $steamid),
                'avatar' => $this->General->getAvatar($steamid, 3),
                'checked_avatar' => $this->General->checkAvatar($steamid),
                'overall' => $overall,
                'overall_label' => number_format($overall, 1, '.', ''),
                'tone' => $overall < 3 ? 'very-bad' : ($overall < 4 ? 'bad' : ''),
                'text' => $text,
                'server_id' => $serverId,
                'server_name' => $serverName,
                'created_at' => $createdAt,
                'date' => $createdAt > 0 ? date('d.m.Y', $createdAt) : '',
            ];
        }

        $avg = round((float) ($summary['avg'] ?? 0), 1);

        return [
            'summary' => [
                'avg' => $avg,
                'avg_label' => number_format($avg, 1, '.', ''),
                'total' => (int) ($summary['total'] ?? 0),
                'total_label' => number_format((int) ($summary['total'] ?? 0), 0, '.', ' '),
            ],
            'rating_icon' => $settings->getRatingIcon(),
            'reviews' => $reviews,
        ];
    }

    private function cacheDir(): string
    {
        return MODULES . 'module_block_main_reviews/assets/cache/';
    }

    private function cachePath(): string
    {
        return $this->cacheDir() . self::CACHE_FILE;
    }

    private function readCache(int $limit): ?array
    {
        $path = $this->cachePath();
        if (!is_file($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload) || !isset($payload['data']) || !is_array($payload['data'])) {
            return null;
        }

        if ((int) ($payload['limit'] ?? 0) !== $limit) {
            return null;
        }

        $data = $payload['data'];
        if (!isset($data['summary'], $data['reviews'], $data['rating_icon']) || !is_array($data['reviews'])) {
            return null;
        }

        foreach ($data['reviews'] as &$review) {
            if (!is_array($review)) {
                continue;
            }
            $review['text'] = ModuleHelper::sanitizeUserText((string) ($review['text'] ?? ''));
            $review['name'] = ModuleHelper::sanitizeUserText((string) ($review['name'] ?? ''));
            $review['server_name'] = ModuleHelper::sanitizeUserText((string) ($review['server_name'] ?? ''));
        }
        unset($review);

        return $data;
    }

    private function writeCache(array $data, int $limit): void
    {
        $dir = $this->cacheDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        if (is_dir($dir)) {
            @chmod($dir, 0777);
        }

        $payload = [
            'limit' => $limit,
            'updated_at' => time(),
            'data' => $data,
        ];
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return;
        }

        $path = $this->cachePath();
        $tmp = $path . '.tmp';
        if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
            if (@file_put_contents($path, $json, LOCK_EX) === false) {
                return;
            }
            @chmod($path, 0777);
            return;
        }
        @chmod($tmp, 0777);
        if (!@rename($tmp, $path)) {
            @copy($tmp, $path);
            @unlink($tmp);
        }
        if (is_file($path)) {
            @chmod($path, 0777);
        }
    }

    private function previewText(string $text, int $max = 220): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if ($text === '' || mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max);
        if (preg_match('/^(.*)\s\S*$/u', $cut, $m) && trim((string) $m[1]) !== '') {
            $cut = rtrim((string) $m[1]);
        }

        return $cut . '…';
    }
}
