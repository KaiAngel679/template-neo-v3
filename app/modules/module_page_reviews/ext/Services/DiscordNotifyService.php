<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\ModuleHelper;

class DiscordNotifyService
{
    private $settings;
    private $General;
    private $Translate;
    private $webhook;

    public function __construct(SettingsService $settings, object $General, object $Translate)
    {
        $this->settings = $settings;
        $this->General = $General;
        $this->Translate = $Translate;
        $this->webhook = new DiscordWebhookService();
    }

    public function notifyNewReview(array $row): void
    {
        $cfg = $this->settings->get();
        $webhookUrl = trim((string) ($cfg['discord_webhook_url'] ?? ''));
        if ($webhookUrl === '') {
            return;
        }

        $steamid = ModuleHelper::toSteam64((string) ($row['steamid'] ?? ''));
        if ($steamid === '') {
            return;
        }

        $site = 'http:' . rtrim((string) ($this->General->arr_general['site'] ?? ''), '/');
        $name = ModuleHelper::resolveDisplayName($this->General, $steamid);
        $profile = $site . '/profiles/' . $steamid . '/?search=1';
        $overall = round((float) ($row['overall'] ?? 0), 1);
        $serverName = trim((string) ($row['server_name'] ?? ''));
        if ($serverName === '') {
            $serverName = $this->phrase('_rv_webhookAllServers');
        }

        $pros = $this->clip((string) ($row['pros'] ?? ''));
        $cons = $this->clip((string) ($row['cons'] ?? ''));
        $comment = $this->clip((string) ($row['comment'] ?? ''));

        $description = '**' . $this->phrase('_rv_webhookPlayer') . ':** [' . $name . '](' . $profile . ")\n"
            . '**' . $this->phrase('_rv_webhookServer') . ':** ' . $serverName . "\n"
            . '**' . $this->phrase('_rv_webhookRating') . ':** ' . $overall . ' / 5';

        if ($pros !== '') {
            $description .= "\n\n**" . $this->phrase('_rv_webhookPros') . ":**\n" . $pros;
        }
        if ($cons !== '') {
            $description .= "\n\n**" . $this->phrase('_rv_webhookCons') . ":**\n" . $cons;
        }
        if ($comment !== '') {
            $description .= "\n\n**" . $this->phrase('_rv_webhookComment') . ":**\n" . $comment;
        }

        $embed = [
            'title' => $this->phrase('_rv_webhookTitleNew'),
            'description' => $description,
            'color' => SettingsService::embedColorToInt((string) ($cfg['discord_webhook_color'] ?? '#5865F2')),
            'url' => $site . '/reviews/',
            'thumbnail' => [
                'url' => $this->General->getAvatar($steamid, 1),
            ],
            'timestamp' => gmdate('c'),
        ];

        $imageUrl = trim((string) ($cfg['discord_webhook_image'] ?? ''));
        if ($imageUrl !== '') {
            $embed['image'] = ['url' => $imageUrl];
        }

        $this->webhook->send($embed, $webhookUrl);
    }

    private function phrase(string $key): string
    {
        return (string) $this->Translate->get_translate_module_phrase('module_page_reviews', $key);
    }

    private function clip(string $text): string
    {
        $text = ModuleHelper::sanitizeUserText(trim($text));
        if ($text === '') {
            return '';
        }
        if (mb_strlen($text) > 400) {
            return mb_substr($text, 0, 397) . '...';
        }

        return $text;
    }
}
