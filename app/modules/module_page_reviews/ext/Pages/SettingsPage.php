<?php

namespace app\modules\module_page_reviews\ext\Pages;

use app\modules\module_page_reviews\ext\ModuleContainer;
use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Services\SettingsService;

final class SettingsPage extends AbstractPage
{
    public function isAccessible(string $section): bool
    {
        return ModuleHelper::isAdmin();
    }

    protected function actions(): array
    {
        return [
            'save_settings' => [
                'admin' => true,
                'handler' => static fn(ModuleContainer $c) => $c->settings()->save(),
            ],
            'get_criteria' => [
                'admin' => true,
                'handler' => static fn(ModuleContainer $c) => $c->criteria()->list(),
            ],
            'create_criterion' => [
                'admin' => true,
                'handler' => static fn(ModuleContainer $c) => $c->criteria()->create(),
            ],
            'update_criterion' => [
                'admin' => true,
                'handler' => static fn(ModuleContainer $c) => $c->criteria()->update(),
            ],
            'delete_criterion' => [
                'admin' => true,
                'handler' => static fn(ModuleContainer $c) => $c->criteria()->delete(),
            ],
            'reorder_criteria' => [
                'admin' => true,
                'handler' => static fn(ModuleContainer $c) => $c->criteria()->reorder(),
            ],
            'migrate_legacy_reviews' => [
                'admin' => true,
                'handler' => static fn(ModuleContainer $c) => $c->settings()->migrateLegacy(),
            ],
            'list_bans' => [
                'admin' => true,
                'handler' => static fn(ModuleContainer $c) => $c->bans()->list(),
            ],
            'create_ban' => [
                'admin' => true,
                'handler' => static fn(ModuleContainer $c) => $c->bans()->create(),
            ],
            'delete_ban' => [
                'admin' => true,
                'handler' => static fn(ModuleContainer $c) => $c->bans()->delete(),
            ],
            'save_ban_options' => [
                'admin' => true,
                'handler' => static fn(ModuleContainer $c) => $c->bans()->saveOptions(),
            ],
        ];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        $allowed = ['general', 'criteria', 'bans'];
        if (!in_array($section, $allowed, true)) {
            $section = 'general';
        }

        $settings = $container->settings()->get();
        $translate = $container->Translate;

        $ratingIcon = SettingsService::sanitizeRatingIcon((string) ($settings['rating_icon'] ?? 'star-fill'));
        $fieldsMode = SettingsService::sanitizeFieldsMode((string) ($settings['fields_mode'] ?? 'all'));
        $fieldsModeLabel = [
            'comment' => '_rv_fieldsModeComment',
            'pros_cons' => '_rv_fieldsModeProsCons',
            'all' => '_rv_fieldsModeAll',
        ][$fieldsMode] ?? '_rv_fieldsModeAll';
        $fieldsModeIcon = [
            'comment' => 'chat',
            'pros_cons' => 'list',
            'all' => 'layers',
        ][$fieldsMode] ?? 'layers';
        $listColumns = SettingsService::sanitizeListColumns($settings['list_columns'] ?? 1);
        $listColumnsLabel = $listColumns === 2 ? '_rv_listColumnsTwo' : '_rv_listColumnsOne';
        $listColumnsIcon = $listColumns === 2 ? 'grid-elements' : 'list';
        $blacklistMode = ((string) ($settings['blacklist_mode'] ?? 'censor')) === 'block' ? 'block' : 'censor';
        $blacklistStyle = ((string) ($settings['blacklist_style'] ?? 'hearts')) === 'stars' ? 'stars' : 'hearts';
        $blacklistEnabled = !empty($settings['blacklist_enabled']);

        $languages = method_exists($container->General, 'get_arr_languages')
            ? (array) $container->General->get_arr_languages()
            : ['RU', 'EN', 'UA'];

        $banScopes = [
            ['key' => 'all', 'label' => $translate->get_translate_module_phrase('module_page_reviews', '_rv_banScopeAll'), 'icon' => 'block'],
            ['key' => 'review', 'label' => $translate->get_translate_module_phrase('module_page_reviews', '_rv_banScopeReview'), 'icon' => 'edit-pen'],
            ['key' => 'comment', 'label' => $translate->get_translate_module_phrase('module_page_reviews', '_rv_banScopeComment'), 'icon' => 'chat-slash'],
            ['key' => 'reply', 'label' => $translate->get_translate_module_phrase('module_page_reviews', '_rv_banScopeReply'), 'icon' => 'chat-slash'],
            ['key' => 'vote', 'label' => $translate->get_translate_module_phrase('module_page_reviews', '_rv_banScopeVote'), 'icon' => 'like'],
        ];

        $banScopeLabels = [];
        foreach ($banScopes as $scope) {
            $banScopeLabels[$scope['key']] = $scope['label'];
        }

        $bans = $container->bans()->list()['bans'] ?? [];
        foreach ($bans as &$ban) {
            $scope = (string) ($ban['scope'] ?? 'all');
            $ban['scope_label'] = $banScopeLabels[$scope] ?? $scope;
        }
        unset($ban);

        $criteria = $container->criteria()->list()['criteria'] ?? [];
        foreach ($criteria as &$item) {
            $item['payload'] = action_text_clear(json_encode([
                'id' => (int) $item['id'],
                'name' => (string) ($item['name'] ?? ''),
                'name_raw' => is_array($item['name_raw'] ?? null) ? $item['name_raw'] : [],
            ], JSON_UNESCAPED_UNICODE));
        }
        unset($item);

        $ratingIcons = SettingsService::listSpriteIcons();
        if (!in_array($ratingIcon, $ratingIcons, true)) {
            array_unshift($ratingIcons, $ratingIcon);
        }

        return [
            'section' => $section,
            'settings' => $settings,
            'rewardEnabled' => !empty($settings['reward_enabled']),
            'rewardAmount' => (int) ($settings['reward_amount'] ?? 0),
            'minHoursEnabled' => !empty($settings['min_hours_enabled']),
            'minHours' => (int) ($settings['min_hours'] ?? 0),
            'fieldsMode' => $fieldsMode,
            'fieldsModeLabel' => $translate->get_translate_module_phrase('module_page_reviews', $fieldsModeLabel),
            'fieldsModeIcon' => $fieldsModeIcon,
            'serverSelectEnabled' => !empty($settings['server_select_enabled']),
            'listColumns' => $listColumns,
            'listColumnsLabel' => $translate->get_translate_module_phrase('module_page_reviews', $listColumnsLabel),
            'listColumnsIcon' => $listColumnsIcon,
            'blacklistEnabled' => $blacklistEnabled,
            'blacklistWords' => (string) ($settings['blacklist_words'] ?? ''),
            'blacklistMode' => $blacklistMode,
            'blacklistStyle' => $blacklistStyle,
            'blacklistStyleEnabled' => $blacklistEnabled && $blacklistMode === 'censor',
            'ratingIcon' => $ratingIcon,
            'ratingIcons' => $ratingIcons,
            'discordWebhookUrl' => (string) ($settings['discord_webhook_url'] ?? ''),
            'discordWebhookImage' => (string) ($settings['discord_webhook_image'] ?? ''),
            'discordWebhookColor' => SettingsService::sanitizeEmbedColor((string) ($settings['discord_webhook_color'] ?? '#5865F2')),
            'criteria' => $criteria,
            'languages' => $languages,
            'bans' => $bans,
            'banScopes' => $banScopes,
            'lkConnected' => !empty($container->Db->db_data['lk']),
            'canMigrateLegacy' => $container->settings()->canMigrateLegacy(),
        ];
    }
}
