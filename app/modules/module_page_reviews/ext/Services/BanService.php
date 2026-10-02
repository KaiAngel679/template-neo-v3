<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Repositories\BanRepository;
use app\modules\module_page_reviews\ext\Repositories\CommentRepository;
use app\modules\module_page_reviews\ext\Repositories\ReviewRepository;

class BanService
{
    public const SCOPES = ['review', 'comment', 'reply', 'vote', 'all'];
    private const MAX_BANS = 500;

    private $bans;
    private $Db;
    private $General;
    private $settings;
    private $reviews;
    private $comments;
    private $Translate;

    public function __construct(
        object $Db,
        ?object $General = null,
        ?SettingsService $settings = null,
        ?object $Translate = null
    ) {
        $this->Db = $Db;
        $this->bans = new BanRepository($Db);
        $this->General = $General;
        $this->settings = $settings;
        $this->reviews = new ReviewRepository($Db);
        $this->comments = new CommentRepository($Db);
        $this->Translate = $Translate;
    }

    public function list(): array
    {
        return [
            'status' => 'success',
            'bans' => array_map([$this, 'format'], $this->bans->listAll()),
        ];
    }

    public function create(array $input): array
    {
        $steamid = ModuleHelper::toSteam64(trim((string) ($input['steamid'] ?? '')));
        if ($steamid !== '' && !preg_match('/^7656119\d{10}$/', $steamid)) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_invalidSteam'),
            ];
        }

        $ip = self::sanitizeIp((string) ($input['ip'] ?? ''));
        if ($steamid === '' && $ip === '') {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_banNeedTarget'),
            ];
        }

        $scope = self::sanitizeScope((string) ($input['scope'] ?? 'all'));
        $reason = trim((string) ($input['reason'] ?? ''));
        if (mb_strlen($reason) > 200) {
            $reason = mb_substr($reason, 0, 200);
        }

        if ($this->bans->count() >= self::MAX_BANS) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_tooManyBans'),
            ];
        }

        $createdAt = time();
        $createdBy = ModuleHelper::toSteam64(ModuleHelper::sessionSteam());
        $id = $this->bans->create([
            'steamid' => $steamid,
            'ip' => $ip,
            'scope' => $scope,
            'reason' => $reason,
            'created_at' => $createdAt,
            'created_by' => $createdBy,
        ]);

        $ban = $this->format($this->bans->getById($id));

        $deleted = ['reviews' => 0, 'comments' => 0];
        if (
            $steamid !== ''
            && $this->settings !== null
            && $this->settings->isBanDeleteContentEnabled()
        ) {
            $deleted = $this->purgeByScope($steamid, $scope);
        }

        if ($deleted['reviews'] > 0 || $deleted['comments'] > 0) {
            $message = ModuleHelper::phrase($this->Translate, '_rv_banAddedWithDelete', [
                '%reviews%' => (string) $deleted['reviews'],
                '%comments%' => (string) $deleted['comments'],
            ]);
        } else {
            $message = ModuleHelper::phrase($this->Translate, '_rv_banAdded');
        }

        if ($deleted['reviews'] > 0) {
            $this->refreshHomeBlockCache();
        }

        return [
            'status' => 'success',
            'message' => $message,
            'ban' => $ban,
            'deleted' => $deleted,
            'bans' => $this->list()['bans'],
        ];
    }

    public function delete(string $id): array
    {
        $id = (int) preg_replace('/\D+/', '', $id);
        if ($id <= 0 || !$this->bans->delete($id)) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_banNotFound'),
            ];
        }

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_banRemoved'),
            'bans' => $this->list()['bans'],
        ];
    }

    public function findActiveBan(string $scope, ?string $steamid = null, ?string $ip = null): ?array
    {
        $scope = self::sanitizeScope($scope);
        $steamid = ModuleHelper::toSteam64((string) ($steamid ?? ModuleHelper::sessionSteam()));
        $ip = self::sanitizeIp((string) ($ip ?? $this->clientIp()));

        $row = $this->bans->findMatch($scope, $steamid, $ip);
        if ($row === null) {
            return null;
        }

        $ban = $this->format($row);

        return [
            'id' => (string) ($ban['id'] ?? ''),
            'scope' => (string) ($ban['scope'] ?? 'all'),
            'reason' => (string) ($ban['reason'] ?? ''),
        ];
    }

    public function rejectIfBanned(string $scope, ?string $steamid = null, ?string $ip = null): ?array
    {
        $ban = $this->findActiveBan($scope, $steamid, $ip);
        if ($ban === null) {
            return null;
        }

        $messages = [
            'review' => ModuleHelper::phrase($this->Translate, '_rv_banRejectReview'),
            'comment' => ModuleHelper::phrase($this->Translate, '_rv_banRejectComment'),
            'reply' => ModuleHelper::phrase($this->Translate, '_rv_banRejectReply'),
            'vote' => ModuleHelper::phrase($this->Translate, '_rv_banRejectVote'),
            'all' => ModuleHelper::phrase($this->Translate, '_rv_banRejectAll'),
        ];
        $actionScope = self::sanitizeScope($scope);
        $message = $messages[$actionScope] ?? $messages['all'];
        if ($ban['reason'] !== '') {
            $message .= ': ' . $ban['reason'];
        }

        return [
            'status' => 'error',
            'message' => $message,
            'banned' => true,
            'scope' => $ban['scope'],
            'reason' => $ban['reason'],
        ];
    }

    public function clientIp(): string
    {
        if ($this->General !== null && method_exists($this->General, 'get_client_ip_cdn')) {
            return self::sanitizeIp((string) $this->General->get_client_ip_cdn());
        }

        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return self::sanitizeIp((string) $_SERVER['HTTP_CF_CONNECTING_IP']);
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);

            return self::sanitizeIp(trim((string) ($parts[0] ?? '')));
        }

        return self::sanitizeIp((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    }

    public static function sanitizeScope(string $scope): string
    {
        $scope = strtolower(trim($scope));

        return in_array($scope, self::SCOPES, true) ? $scope : 'all';
    }

    public static function sanitizeIp(string $ip): string
    {
        $ip = trim($ip);
        if ($ip === '') {
            return '';
        }
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return '';
        }

        return $ip;
    }

    private function purgeByScope(string $steamid, string $scope): array
    {
        $deleted = ['reviews' => 0, 'comments' => 0];
        $scope = self::sanitizeScope($scope);

        if ($scope === 'review' || $scope === 'all') {
            foreach ($this->reviews->listIdsBySteam($steamid) as $reviewId) {
                $this->reviews->delete($reviewId);
                $deleted['reviews']++;
            }
        }

        if ($scope === 'comment' || $scope === 'all') {
            foreach ($this->comments->listIdsBySteam($steamid, 'comment') as $commentId) {
                $deleted['comments'] += $this->comments->deleteCascade($commentId);
            }
        }

        if ($scope === 'reply' || $scope === 'all') {
            foreach ($this->comments->listIdsBySteam($steamid, 'reply') as $replyId) {
                $this->comments->deleteVotes($replyId);
                $this->comments->deleteById($replyId);
                $deleted['comments']++;
            }
        }

        return $deleted;
    }

    private function format(?array $row): ?array
    {
        if ($row === null || $row === []) {
            return null;
        }

        $steamid = ModuleHelper::toSteam64((string) ($row['steamid'] ?? ''));
        $ip = self::sanitizeIp((string) ($row['ip'] ?? ''));

        return [
            'id' => (string) ((int) ($row['id'] ?? 0)),
            'steamid' => $steamid,
            'ip' => $ip,
            'scope' => self::sanitizeScope((string) ($row['scope'] ?? 'all')),
            'reason' => mb_substr(trim((string) ($row['reason'] ?? '')), 0, 200),
            'created_at' => (int) ($row['created_at'] ?? 0),
            'created_by' => ModuleHelper::toSteam64((string) ($row['created_by'] ?? '')),
        ];
    }

    private function refreshHomeBlockCache(): void
    {
        if ($this->General === null) {
            return;
        }

        $class = '\\app\\modules\\module_block_main_reviews\\ext\\ReviewsBlockService';
        if (!class_exists($class)) {
            return;
        }

        try {
            (new $class($this->Db, $this->General))->refreshCache(10);
        } catch (\Throwable $e) {
        }
    }
}
