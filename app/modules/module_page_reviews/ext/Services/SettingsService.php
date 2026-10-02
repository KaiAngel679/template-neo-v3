<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Repositories\FileRepository;

class SettingsService
{
    public const DEFAULTS = [
        'reward_enabled' => 0,
        'reward_amount' => 0,
        'min_hours_enabled' => 0,
        'min_hours' => 0,
        'fields_mode' => 'all',
        'server_select_enabled' => 1,
        'list_columns' => 1,
        'blacklist_enabled' => 0,
        'blacklist_words' => '',
        'blacklist_mode' => 'censor',
        'blacklist_style' => 'hearts',
        'rating_icon' => 'star-fill',
        'discord_webhook_url' => '',
        'discord_webhook_image' => '',
        'discord_webhook_color' => '#5865F2',
        'ban_delete_content' => 0,
    ];

    public const FIELDS_MODES = ['comment', 'pros_cons', 'all'];

    private $files;
    private $Translate;

    public function __construct(FileRepository $files, ?object $Translate = null)
    {
        $this->files = $files;
        $this->Translate = $Translate;
    }

    public static function listSpriteIcons(): array
    {
        static $cache = null;
        if (is_array($cache)) {
            return $cache;
        }

        $paths = [];
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $paths[] = rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') . '/resources/img/sprite.svg';
        }
        if (defined('MODULES')) {
            $paths[] = dirname((string) MODULES) . '/../resources/img/sprite.svg';
            $paths[] = rtrim((string) MODULES, '/') . '/../../resources/img/sprite.svg';
        }

        $svg = '';
        foreach ($paths as $path) {
            if (is_file($path)) {
                $svg = (string) file_get_contents($path);
                break;
            }
        }

        $icons = [];
        if ($svg !== '' && preg_match_all('/\bid="([^"]+)"/', $svg, $matches)) {
            foreach ($matches[1] as $id) {
                $id = self::sanitizeRatingIcon((string) $id);
                if ($id !== '' && !in_array($id, $icons, true)) {
                    $icons[] = $id;
                }
            }
        }

        if ($icons === []) {
            $icons = ['star-fill', 'heart', 'like', 'diamond', 'trophy', 'sparkles', 'badge-star'];
        }

        sort($icons, SORT_STRING);
        $cache = $icons;

        return $cache;
    }

    public function get(): array
    {
        $data = $this->files->get('settings');
        $merged = array_merge(self::DEFAULTS, is_array($data) ? $data : []);
        $merged['blacklist_mode'] = self::sanitizeBlacklistMode((string) ($merged['blacklist_mode'] ?? 'censor'));
        $merged['fields_mode'] = self::sanitizeFieldsMode((string) ($merged['fields_mode'] ?? 'all'));
        $merged['server_select_enabled'] = !empty($merged['server_select_enabled']) ? 1 : 0;
        $merged['list_columns'] = self::sanitizeListColumns($merged['list_columns'] ?? 1);
        $merged['rating_icon'] = self::sanitizeRatingIcon((string) ($merged['rating_icon'] ?? 'star-fill'));
        $merged['discord_webhook_url'] = self::sanitizeWebhookUrl((string) ($merged['discord_webhook_url'] ?? ''));
        $merged['discord_webhook_image'] = self::sanitizeImageUrl((string) ($merged['discord_webhook_image'] ?? ''));
        $merged['discord_webhook_color'] = self::sanitizeEmbedColor((string) ($merged['discord_webhook_color'] ?? '#5865F2'));

        return $merged;
    }

    public static function sanitizeFieldsMode(string $mode): string
    {
        $mode = strtolower(trim($mode));

        return in_array($mode, self::FIELDS_MODES, true) ? $mode : 'all';
    }

    public function getFieldsMode(): string
    {
        return self::sanitizeFieldsMode((string) ($this->get()['fields_mode'] ?? 'all'));
    }

    public function getFieldsFlags(): array
    {
        $mode = $this->getFieldsMode();

        return [
            'mode' => $mode,
            'show_pros_cons' => $mode === 'all' || $mode === 'pros_cons',
            'show_comment' => $mode === 'all' || $mode === 'comment',
        ];
    }

    public function isServerSelectEnabled(): bool
    {
        return !empty($this->get()['server_select_enabled']);
    }

    public static function sanitizeListColumns($columns): int
    {
        return (int) $columns === 2 ? 2 : 1;
    }

    public function getListColumns(): int
    {
        return self::sanitizeListColumns($this->get()['list_columns'] ?? 1);
    }

    public static function sanitizeBlacklistMode(string $mode): string
    {
        return $mode === 'block' ? 'block' : 'censor';
    }

    public function getBlacklistMode(): string
    {
        return self::sanitizeBlacklistMode((string) ($this->get()['blacklist_mode'] ?? 'censor'));
    }

    public function isBlacklistEnabled(): bool
    {
        return !empty($this->get()['blacklist_enabled']);
    }

    public function rejectIfBlocked(string ...$texts): ?array
    {
        if (!$this->isBlacklistEnabled() || $this->getBlacklistMode() !== 'block') {
            return null;
        }

        foreach ($texts as $text) {
            $word = $this->findForbiddenWord($text);
            if ($word !== null) {
                return [
                    'status' => 'error',
                    'message' => ModuleHelper::phrase($this->Translate, '_rv_forbiddenWord'),
                ];
            }
        }

        return null;
    }

    public function findForbiddenWord(string $text): ?string
    {
        $words = $this->getBlacklistWords();
        if ($words === [] || $text === '') {
            return null;
        }

        usort($words, static function (string $a, string $b): int {
            return mb_strlen($b) <=> mb_strlen($a);
        });

        foreach ($words as $word) {
            $quoted = preg_quote($word, '/');
            $pattern = '/(?<![\p{L}\p{N}_])' . $quoted . '(?![\p{L}\p{N}_])/iu';
            if (preg_match($pattern, $text) === 1) {
                return $word;
            }
        }

        return null;
    }

    public function save(array $input): array
    {
        $enabled = !empty($input['reward_enabled']) ? 1 : 0;
        $amount = (int) ($input['reward_amount'] ?? 0);
        if ($amount < 0) {
            $amount = 0;
        }

        $minHoursEnabled = !empty($input['min_hours_enabled']) ? 1 : 0;
        $minHours = (int) ($input['min_hours'] ?? 0);
        if ($minHours < 0) {
            $minHours = 0;
        }

        $blacklistEnabled = !empty($input['blacklist_enabled']) ? 1 : 0;
        $blacklistWords = $this->normalizeBlacklistWords((string) ($input['blacklist_words'] ?? ''));
        $blacklistMode = self::sanitizeBlacklistMode((string) ($input['blacklist_mode'] ?? 'censor'));
        $blacklistStyle = ((string) ($input['blacklist_style'] ?? 'hearts')) === 'stars' ? 'stars' : 'hearts';
        $fieldsMode = self::sanitizeFieldsMode((string) ($input['fields_mode'] ?? 'all'));
        $serverSelectEnabled = !empty($input['server_select_enabled']) ? 1 : 0;
        $listColumns = self::sanitizeListColumns($input['list_columns'] ?? 1);
        $ratingIcon = self::sanitizeRatingIcon((string) ($input['rating_icon'] ?? 'star-fill'));
        $discordWebhookUrl = self::sanitizeWebhookUrl((string) ($input['discord_webhook_url'] ?? ''));
        $discordWebhookImage = self::sanitizeImageUrl((string) ($input['discord_webhook_image'] ?? ''));
        $discordWebhookColor = self::sanitizeEmbedColor((string) ($input['discord_webhook_color'] ?? '#5865F2'));
        $hasBanDelete = array_key_exists('ban_delete_content', $input);
        $banDeleteContent = $hasBanDelete ? (!empty($input['ban_delete_content']) ? 1 : 0) : null;

        $this->files->update('settings', static function (array $current) use (
            $enabled,
            $amount,
            $minHoursEnabled,
            $minHours,
            $fieldsMode,
            $serverSelectEnabled,
            $listColumns,
            $blacklistEnabled,
            $blacklistWords,
            $blacklistMode,
            $blacklistStyle,
            $ratingIcon,
            $discordWebhookUrl,
            $discordWebhookImage,
            $discordWebhookColor,
            $hasBanDelete,
            $banDeleteContent
        ) {
            $next = array_merge($current, [
                'reward_enabled' => $enabled,
                'reward_amount' => $amount,
                'min_hours_enabled' => $minHoursEnabled,
                'min_hours' => $minHours,
                'fields_mode' => $fieldsMode,
                'server_select_enabled' => $serverSelectEnabled,
                'list_columns' => $listColumns,
                'blacklist_enabled' => $blacklistEnabled,
                'blacklist_words' => $blacklistWords,
                'blacklist_mode' => $blacklistMode,
                'blacklist_style' => $blacklistStyle,
                'rating_icon' => $ratingIcon,
                'discord_webhook_url' => $discordWebhookUrl,
                'discord_webhook_image' => $discordWebhookImage,
                'discord_webhook_color' => $discordWebhookColor,
            ]);
            if ($hasBanDelete) {
                $next['ban_delete_content'] = $banDeleteContent;
            }

            return $next;
        });

        return [
            'status' => 'success',
            'message' => 'ok',
            'settings' => $this->get(),
        ];
    }

    public function getMinHours(): int
    {
        $settings = $this->get();
        if (empty($settings['min_hours_enabled'])) {
            return 0;
        }

        return max(0, (int) ($settings['min_hours'] ?? 0));
    }

    public function getRatingIcon(): string
    {
        return self::sanitizeRatingIcon((string) ($this->get()['rating_icon'] ?? 'star-fill'));
    }

    public function isBanDeleteContentEnabled(): bool
    {
        return !empty($this->get()['ban_delete_content']);
    }

    public function saveBanDeleteContent(bool $enabled): array
    {
        $value = $enabled ? 1 : 0;
        $this->files->update('settings', static function (array $current) use ($value) {
            return array_merge($current, ['ban_delete_content' => $value]);
        });

        return [
            'status' => 'success',
            'message' => 'ok',
            'settings' => $this->get(),
        ];
    }

    public static function sanitizeRatingIcon(string $icon): string
    {
        $icon = trim($icon);
        $icon = preg_replace('/[^a-zA-Z0-9_-]/', '', $icon) ?? '';
        if ($icon === '' || strlen($icon) > 64) {
            return 'star-fill';
        }

        return $icon;
    }

    public static function sanitizeWebhookUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (strlen($url) > 300) {
            return '';
        }
        if (!preg_match('#^https://(?:canary\.|ptb\.)?discord(?:app)?\.com/api/webhooks/\d+/[\w-]+$#i', $url)) {
            return '';
        }

        return $url;
    }

    public static function sanitizeImageUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (strlen($url) > 500) {
            return '';
        }
        if (!preg_match('#^https?://[^\s]+$#i', $url)) {
            return '';
        }

        return $url;
    }

    public static function sanitizeEmbedColor(string $color): string
    {
        $color = trim($color);
        if ($color === '') {
            return '#5865F2';
        }

        if (preg_match('/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})/i', $color, $m)) {
            $r = max(0, min(255, (int) $m[1]));
            $g = max(0, min(255, (int) $m[2]));
            $b = max(0, min(255, (int) $m[3]));

            return sprintf('#%02X%02X%02X', $r, $g, $b);
        }

        if ($color[0] !== '#') {
            $color = '#' . $color;
        }
        if (preg_match('/^#([0-9A-Fa-f]{3})$/', $color, $m)) {
            $h = $m[1];

            return strtoupper('#' . $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2]);
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return '#5865F2';
        }

        return strtoupper($color);
    }

    public static function embedColorToInt(string $color): int
    {
        $color = self::sanitizeEmbedColor($color);

        return (int) hexdec(substr($color, 1));
    }

    public function censor(string $text): string
    {
        if (!$this->isBlacklistEnabled() || $this->getBlacklistMode() !== 'censor') {
            return $text;
        }

        $words = $this->getBlacklistWords();
        if ($words === []) {
            return $text;
        }

        $char = ((string) ($this->get()['blacklist_style'] ?? 'hearts')) === 'stars' ? '★' : '❤';

        usort($words, static function (string $a, string $b): int {
            return mb_strlen($b) <=> mb_strlen($a);
        });

        foreach ($words as $word) {
            $quoted = preg_quote($word, '/');
            $pattern = '/(?<![\p{L}\p{N}_])' . $quoted . '(?![\p{L}\p{N}_])/iu';
            $text = (string) preg_replace_callback($pattern, static function (array $m) use ($char): string {
                $len = max(1, mb_strlen($m[0]));

                return str_repeat($char, $len);
            }, $text);
        }

        return $text;
    }

    private function getBlacklistWords(): array
    {
        return $this->parseBlacklistWords((string) ($this->get()['blacklist_words'] ?? ''));
    }

    private function normalizeBlacklistWords(string $raw): string
    {
        return implode(', ', $this->parseBlacklistWords($raw));
    }

    private function parseBlacklistWords(string $raw): array
    {
        $parts = preg_split('/[,;\n]+/u', $raw) ?: [];
        $out = [];
        $seen = [];

        foreach ($parts as $part) {
            $word = trim((string) $part);
            if ($word === '') {
                continue;
            }
            $word = mb_substr($word, 0, 64);
            $key = mb_strtolower($word);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $word;
        }

        return $out;
    }
}
