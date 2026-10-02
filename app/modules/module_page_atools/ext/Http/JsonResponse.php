<?php

namespace app\modules\module_page_atools\ext\Http;

final class JsonResponse
{
    public static function send($data): void
    {
        exit(json_encode($data, true));
    }

    public static function forbidden(): void
    {
        self::send(['status' => 'error', 'message' => 'Forbidden']);
    }
}