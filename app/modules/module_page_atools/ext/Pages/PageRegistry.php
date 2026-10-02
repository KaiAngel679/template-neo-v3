<?php

namespace app\modules\module_page_atools\ext\Pages;

final class PageRegistry
{
    private const MAP = [
        'main' => MainPage::class,
        'admins' => AdminsPage::class,
        'punishments' => PunishmentsPage::class,
        'checks' => ChecksPage::class,
        'finances' => FinancesPage::class,
        'privileges' => PrivilegesPage::class,
        'credits' => CreditsPage::class,
        'experience' => ExperiencePage::class,
        'logs' => LogsPage::class,
        'settings' => SettingsPage::class,
    ];

    public static function resolve(string $view): ?AbstractPage
    {
        $class = self::MAP[$view] ?? null;

        return $class !== null ? new $class() : null;
    }
}
