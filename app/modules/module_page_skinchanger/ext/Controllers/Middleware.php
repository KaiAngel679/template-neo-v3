<?php

namespace app\modules\module_page_skinchanger\ext\Controllers;

class Middleware
{
    public static function json(): void
    {
        header('Content-Type: application/json');
    }

    public static function rateLimit(string $group): void
    {
        if (!(new RateLimits())->check($group)) {
            self::abort(429, 'rate_limited');
        }
    }

    public static function auth(): void
    {
        if (empty($_SESSION['steamid'])) {
            self::abort(401, 'unauthorized');
        }
    }

    public static function admin(): void
    {
        self::auth();
        if (empty($_SESSION['user_admin'])) {
            self::abort(403, 'forbidden');
        }
    }

    public static function run(string $rateGroup, ?string $guard): void
    {
        self::rateLimit($rateGroup);

        if ($guard === 'auth')  self::auth();
        if ($guard === 'admin') self::admin();
    }

    public static function abort(int $code, string $error): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        exit(json_encode(['success' => false, 'error' => $error]));
    }
}
