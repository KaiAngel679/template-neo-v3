<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Repositories\CommentRepository;
use app\modules\module_page_reviews\ext\Repositories\ReviewRepository;

class CommentService
{
    private $comments;
    private $reviews;
    private $meta;
    private $General;
    private $settings;
    private $bans;
    private $Translate;

    public function __construct(
        CommentRepository $comments,
        ReviewRepository $reviews,
        PlayerMetaService $meta,
        object $General,
        ?SettingsService $settings = null,
        ?BanService $bans = null,
        ?object $Translate = null
    ) {
        $this->comments = $comments;
        $this->reviews = $reviews;
        $this->meta = $meta;
        $this->General = $General;
        $this->settings = $settings;
        $this->bans = $bans;
        $this->Translate = $Translate;
    }

    public function thread(int $reviewId): array
    {
        $review = $this->reviews->getById($reviewId);
        if ($review === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewNotFound'),
            ];
        }

        $viewer = ModuleHelper::toSteam64(ModuleHelper::sessionSteam());
        $rows = $this->comments->listByReview($reviewId);
        $steamids = [];
        foreach ($rows as $row) {
            $steamids[] = (string) ($row['steamid'] ?? '');
        }
        $badgesMap = $this->meta->badgesForSteamids($steamids);

        $roots = [];
        $replies = [];

        foreach ($rows as $row) {
            $item = $this->formatComment($row, $viewer, $badgesMap);
            if ($item === null) {
                continue;
            }
            if ($item['parent_id'] === null) {
                $item['replies'] = [];
                $roots[$item['id']] = $item;
            } else {
                $replies[] = $item;
            }
        }

        foreach ($replies as $reply) {
            $parentId = $reply['parent_id'];
            if (isset($roots[$parentId])) {
                $roots[$parentId]['replies'][] = $reply;
            }
        }

        return [
            'status' => 'success',
            'comments' => array_values($roots),
            'total' => count($rows),
        ];
    }

    public function create(int $reviewId, string $text, ?int $parentId): array
    {
        $steamid = ModuleHelper::toSteam64(ModuleHelper::sessionSteam());
        if ($steamid === '') {
            return ['status' => 'error', 'message' => 'Unauthorized'];
        }

        $scope = $parentId !== null ? 'reply' : 'comment';
        if ($this->bans !== null) {
            $banned = $this->bans->rejectIfBanned($scope, $steamid);
            if ($banned !== null) {
                return $banned;
            }
        }

        $review = $this->reviews->getById($reviewId);
        if ($review === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewNotFound'),
            ];
        }

        $text = ModuleHelper::sanitizeUserText(trim($text));
        $error = ModuleHelper::validateRequiredText(
            $text,
            ModuleHelper::phrase($this->Translate, '_rv_comment'),
            $this->Translate
        );
        if ($error !== null) {
            return ['status' => 'error', 'message' => $error];
        }

        if ($this->settings !== null) {
            $blocked = $this->settings->rejectIfBlocked($text);
            if ($blocked !== null) {
                return $blocked;
            }
        }

        if ($parentId !== null) {
            $parent = $this->comments->getById($parentId);
            if ($parent === null || (int) $parent['review_id'] !== $reviewId) {
                return [
                    'status' => 'error',
                    'message' => ModuleHelper::phrase($this->Translate, '_rv_parentCommentNotFound'),
                ];
            }
            if ($parent['parent_id'] !== null) {
                return [
                    'status' => 'error',
                    'message' => ModuleHelper::phrase($this->Translate, '_rv_nestedReplyForbidden'),
                ];
            }
        }

        $id = $this->comments->create($reviewId, $parentId, $steamid, $text);
        $row = $this->comments->getById($id);

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_commentAdded'),
            'comment' => $this->formatComment($row, $steamid),
        ];
    }

    public function update(int $id, string $text): array
    {
        $steamid = ModuleHelper::toSteam64(ModuleHelper::sessionSteam());
        $row = $this->comments->getById($id);
        if ($row === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_commentNotFound'),
            ];
        }

        if (!$this->canEdit($row, $steamid)) {
            return ['status' => 'error', 'message' => 'Forbidden'];
        }

        $scope = $row['parent_id'] !== null ? 'reply' : 'comment';
        if ($this->bans !== null) {
            $banned = $this->bans->rejectIfBanned($scope, $steamid);
            if ($banned !== null) {
                return $banned;
            }
        }

        $text = ModuleHelper::sanitizeUserText(trim($text));
        $error = ModuleHelper::validateRequiredText(
            $text,
            ModuleHelper::phrase($this->Translate, '_rv_comment'),
            $this->Translate
        );
        if ($error !== null) {
            return ['status' => 'error', 'message' => $error];
        }

        if ($this->settings !== null) {
            $blocked = $this->settings->rejectIfBlocked($text);
            if ($blocked !== null) {
                return $blocked;
            }
        }

        $this->comments->update($id, $text);
        $updated = $this->comments->getById($id);

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_commentUpdated'),
            'comment' => $this->formatComment($updated, $steamid),
        ];
    }

    public function delete(int $id): array
    {
        $steamid = ModuleHelper::toSteam64(ModuleHelper::sessionSteam());
        $row = $this->comments->getById($id);
        if ($row === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_commentNotFound'),
            ];
        }

        if (!$this->canEdit($row, $steamid)) {
            return ['status' => 'error', 'message' => 'Forbidden'];
        }

        $reviewId = (int) $row['review_id'];
        $removed = $this->comments->deleteCascade($id);

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_commentDeleted'),
            'removed' => $removed,
            'review_id' => $reviewId,
            'comments_count' => $this->comments->countByReview($reviewId),
        ];
    }

    public function vote(int $commentId, int $value): array
    {
        $steamid = ModuleHelper::toSteam64(ModuleHelper::sessionSteam());
        if ($steamid === '') {
            return ['status' => 'error', 'message' => 'Unauthorized'];
        }
        if ($value !== 1 && $value !== -1) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_invalidVote'),
            ];
        }

        if ($this->bans !== null) {
            $banned = $this->bans->rejectIfBanned('vote', $steamid);
            if ($banned !== null) {
                return $banned;
            }
        }

        $row = $this->comments->getById($commentId);
        if ($row === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_commentNotFound'),
            ];
        }

        $this->comments->setVote($commentId, $steamid, $value);
        $updated = $this->comments->getById($commentId);

        return [
            'status' => 'success',
            'score' => (int) ($updated['score'] ?? 0),
            'my_vote' => $this->comments->getVote($commentId, $steamid),
        ];
    }

    private function canEdit(array $row, string $steamid): bool
    {
        $steamid = ModuleHelper::toSteam64($steamid);
        if ($steamid === '') {
            return false;
        }

        if (ModuleHelper::isAdmin()) {
            return true;
        }

        return ModuleHelper::toSteam64((string) ($row['steamid'] ?? '')) === $steamid;
    }

    private function formatComment(?array $row, string $viewerSteam = '', ?array $badgesMap = null): ?array
    {
        if ($row === null || $row === []) {
            return null;
        }

        $id = (int) $row['id'];
        $steamid = ModuleHelper::toSteam64((string) $row['steamid']);
        $viewer64 = $viewerSteam !== '' ? ModuleHelper::toSteam64($viewerSteam) : '';
        $canEdit = $this->canEdit($row, $viewer64);
        $rawText = ModuleHelper::sanitizeUserText((string) $row['text']);

        return [
            'id' => $id,
            'review_id' => (int) $row['review_id'],
            'parent_id' => $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
            'steamid' => $steamid,
            'name' => ModuleHelper::resolveDisplayName($this->General, $steamid),
            'avatar' => $this->General->getAvatar($steamid, 3),
            'checked_avatar' => $this->General->checkAvatar($steamid),
            'badges' => $badgesMap[$steamid] ?? $this->meta->badgesForSteam($steamid),
            'text' => $this->settings !== null
                ? $this->settings->censor($rawText)
                : $rawText,
            'text_edit' => $canEdit ? $rawText : null,
            'score' => (int) $row['score'],
            'my_vote' => $viewer64 !== '' ? $this->comments->getVote($id, $viewer64) : null,
            'created_at' => (int) $row['created_at'],
            'is_owner' => $viewer64 !== '' && $viewer64 === $steamid,
            'can_edit' => $canEdit,
            'can_delete' => $canEdit,
        ];
    }
}
