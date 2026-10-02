<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Repositories\CriteriaRepository;

class LegacyMigrationService
{
    private const SERVER_ID = -1;

    private $Db;
    private $criteria;
    private $Translate;

    public function __construct(object $Db, ?CriteriaRepository $criteria = null, ?object $Translate = null)
    {
        $this->Db = $Db;
        $this->criteria = $criteria ?? new CriteriaRepository($Db);
        $this->Translate = $Translate;
    }

    public function isAvailable(): bool
    {
        return $this->tableExists('web_reviews');
    }

    public function run(): array
    {
        if (!$this->isAvailable()) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_migrationTableMissing'),
            ];
        }

        $reviews = $this->loadReviews();
        $likes = $this->loadLikes();

        if ($reviews === []) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_migrationNoData'),
            ];
        }

        $criteria = $this->criteria->listActive();
        $attrIds = [];
        foreach ($criteria as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $attrIds[] = (string) $id;
            }
        }

        $imported = 0;
        $skipped = 0;
        $likesImported = 0;
        $idMap = [];

        $bySteam = [];
        foreach ($reviews as $row) {
            $steam = ModuleHelper::toSteam64((string) ($row['steam'] ?? ''));
            if ($steam === '' || !ctype_digit($steam)) {
                $skipped++;
                continue;
            }
            $time = (int) ($row['time'] ?? 0);
            if (!isset($bySteam[$steam]) || $time >= (int) ($bySteam[$steam]['time'] ?? 0)) {
                $bySteam[$steam] = $row;
                $bySteam[$steam]['steam'] = $steam;
            } else {
                $skipped++;
            }
        }

        foreach ($bySteam as $row) {
            $steam = (string) $row['steam'];
            $oldId = (int) ($row['id'] ?? 0);

            $existing = $this->Db->query(
                'Core',
                0,
                0,
                'SELECT `id` FROM `neo_reviews` WHERE `steamid` = ? AND `server_id` = ? LIMIT 1',
                [$steam, self::SERVER_ID]
            );
            if (is_array($existing) && !empty($existing['id'])) {
                $idMap[$oldId] = (int) $existing['id'];
                $skipped++;
                continue;
            }

            $overall = max(1, min(5, (int) ($row['score'] ?? 5)));
            $comment = $this->cleanText((string) ($row['text'] ?? ''));
            if ($comment === '') {
                $comment = '—';
            }
            $created = max(0, (int) ($row['time'] ?? time()));
            $likeScore = max(0, (int) ($row['like'] ?? 0));

            try {
                $this->Db->query(
                    'Core',
                    0,
                    0,
                    'INSERT INTO `neo_reviews`
                    (`steamid`, `server_id`, `server_name`, `game`, `overall`, `hours`, `pros`, `cons`, `comment`, `score`, `created_at`, `updated_at`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $steam,
                        self::SERVER_ID,
                        '',
                        '',
                        number_format($overall, 1, '.', ''),
                        0,
                        '',
                        '',
                        $comment,
                        $likeScore,
                        $created,
                        $created,
                    ]
                );
                $newId = (int) $this->Db->lastInsertId('Core', 0, 0);
            } catch (\Throwable $e) {
                $skipped++;
                continue;
            }

            if ($newId <= 0) {
                $skipped++;
                continue;
            }

            $idMap[$oldId] = $newId;

            foreach ($attrIds as $attrId) {
                try {
                    $this->Db->query(
                        'Core',
                        0,
                        0,
                        'INSERT INTO `neo_review_attrs` (`review_id`, `attr`, `value`) VALUES (?, ?, ?)',
                        [$newId, $attrId, $overall]
                    );
                } catch (\Throwable $e) {
                }
            }

            $imported++;
        }

        $scoreByNewId = [];
        foreach ($likes as $like) {
            $oldRevId = (int) ($like['id_rev'] ?? 0);
            if ($oldRevId <= 0 || !isset($idMap[$oldRevId])) {
                continue;
            }
            $newId = $idMap[$oldRevId];
            $voter = ModuleHelper::toSteam64((string) ($like['steam'] ?? ''));
            if ($voter === '' || !ctype_digit($voter)) {
                continue;
            }
            $likedAt = max(0, (int) ($like['time'] ?? time()));

            try {
                $this->Db->query(
                    'Core',
                    0,
                    0,
                    'INSERT INTO `neo_review_votes` (`review_id`, `steamid`, `value`, `created_at`)
                     VALUES (?, ?, 1, ?)
                     ON DUPLICATE KEY UPDATE `value` = 1, `created_at` = VALUES(`created_at`)',
                    [$newId, $voter, $likedAt]
                );
                $likesImported++;
                $scoreByNewId[$newId] = ($scoreByNewId[$newId] ?? 0) + 1;
            } catch (\Throwable $e) {
            }
        }

        foreach ($scoreByNewId as $newId => $score) {
            try {
                $this->Db->query(
                    'Core',
                    0,
                    0,
                    'UPDATE `neo_reviews` SET `score` = ? WHERE `id` = ?',
                    [$score, $newId]
                );
            } catch (\Throwable $e) {
            }
        }

        $this->clearHomeBlockCache();

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_migrationDone'),
            'source' => 'db',
            'imported' => $imported,
            'skipped' => $skipped,
            'likes' => $likesImported,
        ];
    }

    private function clearHomeBlockCache(): void
    {
        $path = MODULES . 'module_block_main_reviews/assets/cache/home.json';
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function tableExists(string $table): bool
    {
        if (method_exists($this->Db, 'mysql_table_search')) {
            return (bool) $this->Db->mysql_table_search('Core', 0, 0, $table);
        }

        try {
            $row = $this->Db->query('Core', 0, 0, 'SHOW TABLES LIKE ?', [$table]);

            return is_array($row) && $row !== [];
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function loadReviews(): array
    {
        try {
            $rows = $this->Db->queryAll(
                'Core',
                0,
                0,
                'SELECT `id`, `steam`, `text`, `score`, `like`, `time` FROM `web_reviews` ORDER BY `time` ASC, `id` ASC'
            );
        } catch (\Throwable $e) {
            return [];
        }

        return is_array($rows) ? $rows : [];
    }

    private function loadLikes(): array
    {
        if (!$this->tableExists('web_reviews_user_like')) {
            return [];
        }

        try {
            $rows = $this->Db->queryAll(
                'Core',
                0,
                0,
                'SELECT `steam`, `id_rev`, `time` FROM `web_reviews_user_like`'
            );
        } catch (\Throwable $e) {
            return [];
        }

        return is_array($rows) ? $rows : [];
    }

    private function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return trim($text);
    }
}
