<?php

namespace app\modules\module_page_tickets\ext\Repositories;

use app\modules\module_page_tickets\ext\Repositories\JsonRepository;

class DiscordRepository
{
    protected $jr, $General;

    public function __construct($General)
    {
        $this->jr = new JsonRepository;
        $this->General = $General;
    }

    public function sendDiscordWebhook($embed)
    {
        $data = [
            'embeds' => [$embed],
        ];

        $options = [
            'http' => [
                'header' => 'Content-type: application/json',
                'method' => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($this->jr->getCache('noti')['url'], false, $context);

        return $result !== false;
    }

    public function buildEmbed($title, $description)
    {
        return [
            "title" => $title,
            "description" => $description,
            "color" => hexdec(preg_replace('/#/', '', $this->jr->getCache('noti')['color'])),
            "image" => [
                "url" => $this->jr->getCache('noti')['img']
            ],
            "thumbnail" => [
                "url" => $this->General->getAvatar($_SESSION['steamid64'], 1)
            ]
        ];
    }
}
