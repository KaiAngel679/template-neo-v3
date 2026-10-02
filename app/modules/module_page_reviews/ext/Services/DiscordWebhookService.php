<?php

namespace app\modules\module_page_reviews\ext\Services;

class DiscordWebhookService
{
    public function send(array $embed, string $webhookUrl): bool
    {
        $webhookUrl = trim($webhookUrl);
        if ($webhookUrl === '') {
            return false;
        }

        $payload = json_encode(['embeds' => [$embed]], JSON_UNESCAPED_UNICODE);
        if ($payload === false) {
            return false;
        }

        $context = stream_context_create([
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => $payload,
                'timeout' => 5,
            ],
        ]);

        $result = @file_get_contents($webhookUrl, false, $context);

        return $result !== false;
    }
}
