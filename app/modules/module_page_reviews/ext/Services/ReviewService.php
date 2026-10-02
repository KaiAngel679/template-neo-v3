<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Repositories\CommentRepository;
use app\modules\module_page_reviews\ext\Repositories\CriteriaRepository;
use app\modules\module_page_reviews\ext\Repositories\ReviewRepository;

class ReviewService
{
    private $Db;
    private $reviews;
    private $comments;
    private $playtime;
    private $servers;
    private $criteria;
    private $meta;
    private $General;
    private $reward;
    private $settings;
    private $discord;
    private $bans;
    private $Translate;

    public function __construct(
        object $Db,
        ReviewRepository $reviews,
        CommentRepository $comments,
        PlaytimeService $playtime,
        ServersService $servers,
        CriteriaRepository $criteria,
        PlayerMetaService $meta,
        object $General,
        ?RewardService $reward = null,
        ?SettingsService $settings = null,
        ?DiscordNotifyService $discord = null,
        ?BanService $bans = null,
        ?object $Translate = null
    ) {
        $this->Db = $Db;
        $this->reviews = $reviews;
        $this->comments = $comments;
        $this->playtime = $playtime;
        $this->servers = $servers;
        $this->criteria = $criteria;
        $this->meta = $meta;
        $this->General = $General;
        $this->reward = $reward;
        $this->settings = $settings;
        $this->discord = $discord;
        $this->bans = $bans;
        $this->Translate = $Translate;
    }

    public function list(array $filters, string $sort, int $limit, int $offset): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);
        $sort = in_array($sort, ['date', 'popular', 'rating'], true) ? $sort : 'date';

        $rows = $this->reviews->list($filters, $sort, $limit, $offset);
        $total = $this->reviews->count($filters);
        $viewer = ModuleHelper::sessionSteam();
        $activeCriteria = $this->criteria->listActive();

        $steamids = [];
        foreach ($rows as $row) {
            $steamids[] = (string) ($row['steamid'] ?? '');
        }
        $badgesMap = $this->meta->badgesForSteamids($steamids);

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->formatReview($row, $viewer, true, $activeCriteria, $badgesMap);
        }

        return [
            'status' => 'success',
            'reviews' => $items,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'summary' => $this->reviews->getSummary($activeCriteria),
            'criteria' => array_map(static function ($row) {
                return [
                    'id' => (int) $row['id'],
                    'name' => ModuleHelper::resolveI18nName($row['name'] ?? '', (string) ($row['id'] ?? '')),
                ];
            }, $activeCriteria),
        ];
    }

    public function create(array $input): array
    {
        $steamid = ModuleHelper::toSteam64(ModuleHelper::sessionSteam());
        if ($steamid === '') {
            return ['status' => 'error', 'message' => 'Unauthorized'];
        }

        if ($this->bans !== null) {
            $banned = $this->bans->rejectIfBanned('review', $steamid);
            if ($banned !== null) {
                return $banned;
            }
        }

        $hours = $this->playtime->getHoursForSteam($steamid);
        $minHours = $this->settings !== null ? $this->settings->getMinHours() : 0;
        if ($minHours > 0 && $hours < $minHours) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_notEnoughHours', [
                    '%min%' => (string) $minHours,
                    '%hours%' => (string) $hours,
                ]),
                'hours' => $hours,
                'min_hours' => $minHours,
            ];
        }

        $parsed = $this->parseInput($input);
        if (isset($parsed['error'])) {
            return ['status' => 'error', 'message' => $parsed['error']];
        }

        if ($this->settings !== null) {
            $blocked = $this->settings->rejectIfBlocked(
                (string) $parsed['pros'],
                (string) $parsed['cons'],
                (string) $parsed['comment']
            );
            if ($blocked !== null) {
                return $blocked;
            }
        }

        $existing = $this->reviews->findBySteamAndServer($steamid, $parsed['server_id']);
        if ($existing !== null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewExists'),
            ];
        }

        $mayReward = $this->reviews->countBySteam($steamid) === 0
            && ($this->reward === null || !$this->reward->hasReceived($steamid));

        $parsed['steamid'] = $steamid;
        $parsed['hours'] = $hours;

        $id = $this->reviews->create($parsed, $parsed['attrs']);
        $row = $this->reviews->getById($id);

        $reward = ['given' => false];
        if ($mayReward && $this->reward !== null) {
            try {
                $reward = $this->reward->tryRewardFirstReview($steamid);
            } catch (\Throwable $e) {
                $reward = ['given' => false, 'reason' => 'error'];
            }
        }

        if ($this->discord !== null && is_array($row)) {
            try {
                $this->discord->notifyNewReview($row);
            } catch (\Throwable $e) {
            }
        }

        $this->refreshHomeBlockCache();

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewAdded'),
            'review' => $this->formatReview($row, $steamid, true),
            'reward' => $reward,
        ];
    }

    public function update(int $id, array $input): array
    {
        $steamid = ModuleHelper::toSteam64(ModuleHelper::sessionSteam());
        $row = $this->reviews->getById($id);
        if ($row === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewNotFound'),
            ];
        }

        if (!$this->canEdit($row, $steamid)) {
            return ['status' => 'error', 'message' => 'Forbidden'];
        }

        if ($this->bans !== null) {
            $banned = $this->bans->rejectIfBanned('review', $steamid);
            if ($banned !== null) {
                return $banned;
            }
        }

        $parsed = $this->parseInput($input);
        if (isset($parsed['error'])) {
            return ['status' => 'error', 'message' => $parsed['error']];
        }

        if ($this->settings !== null) {
            $blocked = $this->settings->rejectIfBlocked(
                (string) $parsed['pros'],
                (string) $parsed['cons'],
                (string) $parsed['comment']
            );
            if ($blocked !== null) {
                return $blocked;
            }
        }

        $duplicate = $this->reviews->findBySteamAndServer((string) $row['steamid'], $parsed['server_id']);
        if ($duplicate !== null && (int) $duplicate['id'] !== $id) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewExists'),
            ];
        }

        $parsed['hours'] = $this->playtime->getHoursForSteam((string) $row['steamid']);
        $this->reviews->update($id, $parsed, $parsed['attrs']);
        $updated = $this->reviews->getById($id);

        $this->refreshHomeBlockCache();

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewUpdated'),
            'review' => $this->formatReview($updated, $steamid, true),
        ];
    }

    public function delete(int $id): array
    {
        $steamid = ModuleHelper::toSteam64(ModuleHelper::sessionSteam());
        $row = $this->reviews->getById($id);
        if ($row === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewNotFound'),
            ];
        }

        if (!$this->canEdit($row, $steamid)) {
            return ['status' => 'error', 'message' => 'Forbidden'];
        }

        $this->reviews->delete($id);
        $this->refreshHomeBlockCache();

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewDeleted'),
        ];
    }

    public function vote(int $id, int $value): array
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

        $row = $this->reviews->getById($id);
        if ($row === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewNotFound'),
            ];
        }

        $this->reviews->setVote($id, $steamid, $value);
        $updated = $this->reviews->getById($id);

        return [
            'status' => 'success',
            'score' => (int) ($updated['score'] ?? 0),
            'my_vote' => $this->reviews->getVote($id, $steamid),
        ];
    }

    public function getForEdit(int $id): array
    {
        $steamid = ModuleHelper::toSteam64(ModuleHelper::sessionSteam());
        $row = $this->reviews->getById($id);
        if ($row === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewNotFound'),
            ];
        }

        if (!$this->canEdit($row, $steamid)) {
            return ['status' => 'error', 'message' => 'Forbidden'];
        }

        return [
            'status' => 'success',
            'review' => $this->formatReview($row, $steamid, false, null, null, false),
        ];
    }

    public function getView(int $id, array $filters = [], string $sort = 'date', int $limit = 20): array
    {
        $row = $this->reviews->getById($id);
        if ($row === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_reviewNotFound'),
            ];
        }

        $viewer = ModuleHelper::toSteam64(ModuleHelper::sessionSteam());
        $limit = max(1, min(50, $limit));
        $index = $this->reviews->findListIndex($id, $filters, $sort);
        $page = $index === null ? 1 : (int) floor($index / $limit) + 1;

        return [
            'status' => 'success',
            'review' => $this->formatReview($row, $viewer, true),
            'index' => $index,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    public function formatReview(
        ?array $row,
        string $viewerSteam = '',
        bool $withCommentCount = false,
        ?array $activeCriteria = null,
        ?array $badgesMap = null,
        bool $censor = true
    ): ?array {
        if ($row === null || $row === []) {
            return null;
        }

        $id = (int) $row['id'];
        $steamid = ModuleHelper::toSteam64((string) $row['steamid']);
        $attrs = $this->reviews->getAttrs($id);
        $criteria = $activeCriteria ?? $this->criteria->listActive();
        $attrList = [];
        foreach ($criteria as $criterion) {
            $key = (string) (int) ($criterion['id'] ?? 0);
            if ($key === '0') {
                continue;
            }
            $attrList[] = [
                'key' => $key,
                'label' => ModuleHelper::resolveI18nName($criterion['name'] ?? '', $key),
                'value' => (int) ($attrs[$key] ?? 0),
            ];
        }

        $myVote = $viewerSteam !== '' ? $this->reviews->getVote($id, $viewerSteam) : null;
        $badges = $badgesMap[$steamid] ?? $this->meta->badgesForSteam($steamid);

        $pros = ModuleHelper::sanitizeUserText((string) $row['pros']);
        $cons = ModuleHelper::sanitizeUserText((string) $row['cons']);
        $comment = ModuleHelper::sanitizeUserText((string) $row['comment']);
        if ($censor && $this->settings !== null) {
            $pros = $this->settings->censor($pros);
            $cons = $this->settings->censor($cons);
            $comment = $this->settings->censor($comment);
        }

        $item = [
            'id' => $id,
            'steamid' => $steamid,
            'name' => ModuleHelper::resolveDisplayName($this->General, $steamid),
            'avatar' => $this->General->getAvatar($steamid, 3),
            'checked_avatar' => $this->General->checkAvatar($steamid),
            'badges' => $badges,
            'server_id' => (int) $row['server_id'],
            'server_name' => (string) $row['server_name'],
            'game' => (string) $row['game'],
            'overall' => round((float) $row['overall'], 1),
            'hours' => (int) $row['hours'],
            'pros' => $pros,
            'cons' => $cons,
            'comment' => $comment,
            'score' => (int) $row['score'],
            'my_vote' => $myVote,
            'attrs' => $attrList,
            'attrs_map' => $attrs,
            'created_at' => (int) $row['created_at'],
            'updated_at' => (int) $row['updated_at'],
            'is_owner' => $viewerSteam !== '' && ModuleHelper::toSteam64((string) $viewerSteam) === $steamid,
            'can_edit' => $this->canEdit($row, $viewerSteam),
            'can_delete' => $this->canEdit($row, $viewerSteam),
        ];

        if ($withCommentCount) {
            $item['comments_count'] = $this->comments->countByReview($id);
        }

        return $item;
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

    private function parseInput(array $input): array
    {
        $serverId = (int) ($input['server_id'] ?? -1);
        if ($this->settings !== null && !$this->settings->isServerSelectEnabled()) {
            $serverId = -1;
        }
        $server = $this->servers->getServerById($serverId);
        if ($server === null) {
            return ['error' => ModuleHelper::phrase($this->Translate, '_rv_serverNotFound')];
        }

        $activeCriteria = $this->criteria->listActive();
        $attrs = [];
        $overall = 0.0;

        if ($activeCriteria === []) {
            $overall = (int) ($input['overall'] ?? 0);
            if ($overall < 1 || $overall > 5) {
                return ['error' => ModuleHelper::phrase($this->Translate, '_rv_rateOverallRange')];
            }
            $overall = (float) $overall;
        } else {
            $sum = 0;
            foreach ($activeCriteria as $criterion) {
                $key = (string) (int) ($criterion['id'] ?? 0);
                $val = (int) ($input['attrs'][$key] ?? $input['attr_' . $key] ?? 0);
                if ($val < 1 || $val > 5) {
                    return ['error' => ModuleHelper::phrase($this->Translate, '_rv_rateCriteriaRange')];
                }
                $attrs[$key] = $val;
                $sum += $val;
            }
            $overall = round($sum / count($activeCriteria), 1);
        }

        $pros = ModuleHelper::sanitizeUserText(trim((string) ($input['pros'] ?? '')));
        $cons = ModuleHelper::sanitizeUserText(trim((string) ($input['cons'] ?? '')));
        $comment = ModuleHelper::sanitizeUserText(trim((string) ($input['comment'] ?? '')));

        $fieldsMode = $this->settings !== null ? $this->settings->getFieldsMode() : 'all';
        if ($fieldsMode === 'comment') {
            $pros = '';
            $cons = '';
        } elseif ($fieldsMode === 'pros_cons') {
            $comment = '';
        }

        $fieldsToValidate = [];
        if ($fieldsMode === 'comment' || $fieldsMode === 'all') {
            $fieldsToValidate[] = [$comment, ModuleHelper::phrase($this->Translate, '_rv_comment')];
        }
        if ($fieldsMode === 'pros_cons' || $fieldsMode === 'all') {
            $fieldsToValidate[] = [$pros, ModuleHelper::phrase($this->Translate, '_rv_pros')];
            $fieldsToValidate[] = [$cons, ModuleHelper::phrase($this->Translate, '_rv_cons')];
        }

        foreach ($fieldsToValidate as [$field, $label]) {
            if ($fieldsMode === 'comment') {
                $error = ModuleHelper::validateRequiredText($field, $label, $this->Translate);
            } else {
                $error = ModuleHelper::validateOptionalText($field, $label, $this->Translate);
            }
            if ($error !== null) {
                return ['error' => $error];
            }
        }

        if ($fieldsMode === 'pros_cons') {
            if ($pros === '' && $cons === '') {
                return [
                    'error' => ModuleHelper::phrase($this->Translate, '_rv_writeAtLeastOne', [
                        '%min%' => (string) ModuleHelper::TEXT_MIN,
                        '%max%' => (string) ModuleHelper::TEXT_MAX,
                    ]),
                ];
            }
        } elseif ($fieldsMode === 'all') {
            if ($pros === '' && $cons === '' && $comment === '') {
                return [
                    'error' => ModuleHelper::phrase($this->Translate, '_rv_writeAtLeastOne', [
                        '%min%' => (string) ModuleHelper::TEXT_MIN,
                        '%max%' => (string) ModuleHelper::TEXT_MAX,
                    ]),
                ];
            }
        }

        return [
            'server_id' => $serverId,
            'server_name' => (string) $server['name_custom'],
            'game' => (string) $server['server_game'],
            'overall' => $overall,
            'pros' => $pros,
            'cons' => $cons,
            'comment' => $comment,
            'attrs' => $attrs,
        ];
    }

    private function refreshHomeBlockCache(): void
    {
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
