<?php

namespace app\modules\module_page_atools\ext\Pages;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\ModuleContainer;

final class SettingsPage extends AbstractPage
{
    public function isAccessible(AccessController $access, string $section): bool
    {
        return !empty($_SESSION['user_admin']);
    }

    protected function actions(): array
    {
        return [
            'save_settings' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->updateSettings(
                    (int) ($_POST['max_warns'] ?? 1),
                    (int) ($_POST['auto_delete_admin_max_warns'] ?? 0),
                    (int) ($_POST['debug_logs'] ?? 0),
                    (int) ($_POST['hide_vip_test'] ?? 0),
                    (string) ($_POST['vip_test_group'] ?? ''),
                    (string) ($_POST['blockdb_api_key'] ?? ''),
                    (int) ($_POST['default_all_servers'] ?? 0),
                ),
            ],
            'create_access_group' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->createGroup(
                    (array) ($_POST['name'] ?? []),
                    (array) ($_POST['permissions'] ?? []),
                ),
            ],
            'delete_access_group' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->deleteGroup(
                    (int) ($_POST['group_id'] ?? 0),
                ),
            ],
            'update_access_group' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->updateGroup(
                    (int) ($_POST['group_id'] ?? 0),
                    (array) ($_POST['name'] ?? []),
                    (array) ($_POST['permissions'] ?? []),
                ),
            ],
            'create_reason' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->createReason(
                    (array) ($_POST['name'] ?? []),
                    (string) ($_POST['type'] ?? ''),
                ),
            ],
            'update_reason' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->updateReason(
                    (int) ($_POST['reason_id'] ?? 0),
                    (array) ($_POST['name'] ?? []),
                    (string) ($_POST['type'] ?? ''),
                ),
            ],
            'delete_reason' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->deleteReason(
                    (int) ($_POST['reason_id'] ?? 0),
                ),
            ],
            'create_term' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->createTerm(
                    (array) ($_POST['name'] ?? []),
                    (int) ($_POST['time'] ?? 0),
                    (string) ($_POST['type'] ?? ''),
                ),
            ],
            'update_term' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->updateTerm(
                    (int) ($_POST['term_id'] ?? 0),
                    (array) ($_POST['name'] ?? []),
                    (int) ($_POST['time'] ?? 0),
                    (string) ($_POST['type'] ?? ''),
                ),
            ],
            'delete_term' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->deleteTerm(
                    (int) ($_POST['term_id'] ?? 0),
                ),
            ],
            'create_vip_group' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->createVipGroup(
                    (string) ($_POST['ini'] ?? ''),
                    (array) ($_POST['display'] ?? []),
                ),
            ],
            'update_vip_group' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->updateVipGroup(
                    (int) ($_POST['vip_group_id'] ?? 0),
                    (string) ($_POST['ini'] ?? ''),
                    (array) ($_POST['display'] ?? []),
                ),
            ],
            'delete_vip_group' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->deleteVipGroup(
                    (int) ($_POST['vip_group_id'] ?? 0),
                ),
            ],
            'create_admin_server_group' => [
                'handler' => static fn(ModuleContainer $c) => $c->database()->createAdminGroup(
                    (string) ($_POST['type'] ?? ''),
                    (string) ($_POST['name'] ?? ''),
                    (int) ($_POST['immunity'] ?? 0),
                    (string) ($_POST['flags'] ?? ''),
                ),
            ],
            'update_admin_server_group' => [
                'handler' => static fn(ModuleContainer $c) => $c->database()->updateAdminGroup(
                    (string) ($_POST['type'] ?? ''),
                    (int) ($_POST['group_id'] ?? 0),
                    (string) ($_POST['name'] ?? ''),
                    (int) ($_POST['immunity'] ?? 0),
                    (string) ($_POST['flags'] ?? ''),
                ),
            ],
            'delete_admin_server_group' => [
                'handler' => static fn(ModuleContainer $c) => $c->database()->deleteAdminGroup(
                    (string) ($_POST['type'] ?? ''),
                    (int) ($_POST['group_id'] ?? 0),
                ),
            ],
            'import_managersystem' => [
                'handler' => static fn(ModuleContainer $c) => $c->settings()->importFromManagerSystem($c->General),
            ],
            'dismiss_welcome_modal' => [
                'handler' => static fn(ModuleContainer $c) => $c->database()->dismissWelcomeModal((int) ($_SESSION['steamid'] ?? 0)),
            ],
        ];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        $container->database()->createTables();

        $context = [
            'FileController' => $container->file(),
            'DatabaseController' => $container->database(),
            'RendersController' => $container->renders(),
        ];

        if ($section === 'general') {
            $context['showWelcomeModal'] = !$container->database()->hasWelcomeModalSeen((int) ($_SESSION['steamid'] ?? 0));
        }

        return $context;
    }
}
