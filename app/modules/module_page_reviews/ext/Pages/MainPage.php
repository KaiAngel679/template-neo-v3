<?php

namespace app\modules\module_page_reviews\ext\Pages;

use app\modules\module_page_reviews\ext\ModuleContainer;
use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Services\PlaytimeService;

final class MainPage extends AbstractPage
{
    public function isAccessible(string $section): bool
    {
        return true;
    }

    protected function actions(): array
    {
        return [
            'get_reviews' => [
                'handler' => static fn(ModuleContainer $c) => $c->reviews()->list(),
            ],
            'get_review' => [
                'auth' => true,
                'handler' => static fn(ModuleContainer $c) => $c->reviews()->getForEdit(),
            ],
            'get_review_view' => [
                'handler' => static fn(ModuleContainer $c) => $c->reviews()->getView(),
            ],
            'get_review_thread' => [
                'handler' => static fn(ModuleContainer $c) => $c->comments()->thread(),
            ],
            'create_review' => [
                'auth' => true,
                'handler' => static fn(ModuleContainer $c) => $c->reviews()->create(),
            ],
            'update_review' => [
                'auth' => true,
                'handler' => static fn(ModuleContainer $c) => $c->reviews()->update(),
            ],
            'delete_review' => [
                'auth' => true,
                'handler' => static fn(ModuleContainer $c) => $c->reviews()->delete(),
            ],
            'vote_review' => [
                'auth' => true,
                'handler' => static fn(ModuleContainer $c) => $c->reviews()->vote(),
            ],
            'create_comment' => [
                'auth' => true,
                'handler' => static fn(ModuleContainer $c) => $c->comments()->create(),
            ],
            'update_comment' => [
                'auth' => true,
                'handler' => static fn(ModuleContainer $c) => $c->comments()->update(),
            ],
            'delete_comment' => [
                'auth' => true,
                'handler' => static fn(ModuleContainer $c) => $c->comments()->delete(),
            ],
            'vote_comment' => [
                'auth' => true,
                'handler' => static fn(ModuleContainer $c) => $c->comments()->vote(),
            ],
        ];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        $criteria = $container->criteria()->listActive();
        $settings = $container->settings()->get();
        $minHours = $container->settings()->service()->getMinHours();
        $sessionSteam = ModuleHelper::sessionSteam();
        $viewerHours = $sessionSteam !== ''
            ? (new PlaytimeService($container->Db))->getHoursForSteam($sessionSteam)
            : 0;
        $bans = $container->bans()->service();
        $reviewBan = $bans->findActiveBan('review');
        $commentBan = $bans->findActiveBan('comment');
        $replyBan = $bans->findActiveBan('reply');
        $voteBan = $bans->findActiveBan('vote');
        $translate = $container->Translate;

        $ratingIcon = action_text_clear((string) $container->settings()->service()->getRatingIcon());
        $ratingUse = '/resources/img/sprite.svg#' . $ratingIcon;

        $summary = $container->summary()->get();
        $total = (int) ($summary['total'] ?? 0);
        $avg = (float) ($summary['avg'] ?? 0);
        $dist = $summary['distribution'] ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $bars = [];
        $barBase = max(1, $total);
        for ($star = 5; $star >= 1; $star--) {
            $count = (int) ($dist[$star] ?? 0);
            $bars[] = [
                'star' => $star,
                'count' => $count,
                'pct' => round($count * 100 / $barBase, 1),
            ];
        }

        $attrs = $summary['attrs'] ?? [];
        if ($attrs === [] && $criteria !== []) {
            foreach ($criteria as $c) {
                $attrs[] = [
                    'id' => (int) ($c['id'] ?? 0),
                    'name' => $c['name'],
                    'avg' => 0,
                ];
            }
        }
        foreach ($attrs as &$attr) {
            $attr['avg_label'] = number_format((float) ($attr['avg'] ?? 0), 1, '.', '');
        }
        unset($attr);

        $summary['avg_label'] = number_format($avg, 1, '.', '');
        $summary['total_label'] = number_format($total, 0, '.', ' ');
        $summary['bars'] = $bars;
        $summary['attrs'] = $attrs;

        $servers = [];
        foreach ($container->servers()->getServers() as $srv) {
            $game = strtolower((string) ($srv['server_game'] ?? ''));
            $servers[] = [
                'id' => (int) $srv['id'],
                'name_custom' => $srv['name_custom'],
                'badge' => $game === 'csgo' ? 'csgo' : 'cs2',
                'badge_text' => $game === 'csgo' ? 'CS:GO' : 'CS2',
            ];
        }

        $reviewBanReason = trim((string) ($reviewBan['reason'] ?? ''));
        $reviewBanText = $reviewBanReason !== ''
            ? str_replace(
                '%reason%',
                action_text_clear($reviewBanReason),
                $translate->get_translate_module_phrase('module_page_reviews', '_rv_bannedReviewReason')
            )
            : $translate->get_translate_module_phrase('module_page_reviews', '_rv_bannedReviewButton');

        $hoursLeft = max(0, (int) $minHours - (int) $viewerHours);

        $rewardAmount = (int) ($settings['reward_amount'] ?? 0);
        $rewardCurrency = (string) ($container->General->currency ?? '₽');
        $showRewardBanner = !empty($settings['reward_enabled'])
            && $rewardAmount > 0
            && !empty($container->Db->db_data['lk']);
        $rewardBannerText = $showRewardBanner
            ? ModuleHelper::phrase($translate, '_rv_exchangeText', [
                '%amount%' => (string) $rewardAmount,
                '%currency%' => $rewardCurrency,
            ])
            : '';

        $fieldsFlags = $container->settings()->service()->getFieldsFlags();
        $showProsCons = !empty($fieldsFlags['show_pros_cons']);
        $showComment = !empty($fieldsFlags['show_comment']);

        return [
            'servers' => $servers,
            'summary' => $summary,
            'criteria' => $criteria,
            'settings' => $settings,
            'minHours' => $minHours,
            'viewerHours' => $viewerHours,
            'canReviewByHours' => $minHours <= 0 || $viewerHours >= $minHours,
            'hoursLeft' => $hoursLeft,
            'reviewBan' => $reviewBan,
            'reviewBanText' => $reviewBanText,
            'commentBan' => $commentBan,
            'replyBan' => $replyBan,
            'voteBan' => $voteBan,
            'ratingIcon' => $ratingIcon,
            'ratingUse' => $ratingUse,
            'hasCriteria' => $criteria !== [],
            'viewerAvatar' => $sessionSteam !== ''
                ? $container->General->getAvatar($sessionSteam, 3)
                : '',
            'showRewardBanner' => $showRewardBanner,
            'rewardBannerText' => $rewardBannerText,
            'fieldsMode' => (string) ($fieldsFlags['mode'] ?? 'all'),
            'showProsCons' => $showProsCons,
            'showComment' => $showComment,
            'showServerSelect' => $container->settings()->service()->isServerSelectEnabled(),
            'listColumns' => $container->settings()->service()->getListColumns(),
        ];
    }
}
