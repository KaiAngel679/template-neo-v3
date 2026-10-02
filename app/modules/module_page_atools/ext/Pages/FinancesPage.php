<?php

namespace app\modules\module_page_atools\ext\Pages;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\ModuleContainer;

final class FinancesPage extends AbstractPage
{
    public function isAccessible(AccessController $access, string $section): bool
    {
        return $access->checkPermission('finances.view');
    }

    protected function actions(): array
    {
        return [
            'get_finances_list' => [
                'handler' => static fn(ModuleContainer $c) => $c->finances()->getFinancesList(
                    (string) ($_POST['sort'] ?? 'down'),
                    (int) ($_POST['limit'] ?? 10),
                    (int) ($_POST['offset'] ?? 0),
                    (string) ($_POST['search'] ?? ''),
                    (string) $_SESSION['steamid'],
                    (string) ($_POST['balance_filter'] ?? 'all'),
                ),
            ],
            'add_balance' => [
                'permission' => 'finances.update',
                'handler' => static fn(ModuleContainer $c) => $c->finances()->addBalance(
                    (string) ($_POST['steamid'] ?? ''),
                    $_POST['amount'] ?? '',
                ),
            ],
            'update_balance' => [
                'permission' => 'finances.update',
                'handler' => static fn(ModuleContainer $c) => $c->finances()->updateBalance(
                    (string) ($_POST['auth'] ?? ''),
                    $_POST['cash'] ?? '',
                    $_POST['old_cash'] ?? '',
                ),
            ],
            'reset_balances' => [
                'permission' => 'finances.reset',
                'handler' => static fn(ModuleContainer $c) => $c->finances()->resetBalances(
                    (array) ($_POST['auth_list'] ?? []),
                ),
            ],
            'delete_finances_without_donation' => [
                'permission' => 'finances.reset',
                'handler' => static fn(ModuleContainer $c) => $c->finances()->deletePlayersWithoutDonation(),
            ],
        ];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        return [];
    }
}
