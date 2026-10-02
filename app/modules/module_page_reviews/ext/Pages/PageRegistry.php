<?php

namespace app\modules\module_page_reviews\ext\Pages;

final class PageRegistry
{
    private const MAP = [
        'main' => MainPage::class,
        'settings' => SettingsPage::class,
    ];

    public static function resolve(string $view): ?AbstractPage
    {
        $class = self::MAP[$view] ?? null;

        return $class !== null ? new $class() : null;
    }
}
