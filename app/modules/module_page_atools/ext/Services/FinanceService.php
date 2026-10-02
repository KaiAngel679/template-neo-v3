<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\ModuleHelper;
use app\modules\module_page_atools\ext\Repositories\FinanceRepository;

class FinanceService
{
    private $FinanceRepository, $AccessService, $LogsService, $General, $Translate;

    public function __construct(object $Db, object $General, object $Translate)
    {
        $this->FinanceRepository = new FinanceRepository($Db);
        $this->AccessService = new AccessService($Db);
        $this->LogsService = new LogsService();
        $this->General = $General;
        $this->Translate = $Translate;
    }

    public function getFinancesList(string $sort, int $limit, int $offset, string $search, string $mySteamid, string $balanceFilter = 'all'): array
    {
        if (!$this->FinanceRepository->isLkConnected()) {
            return [
                'status' => 'success',
                'data' => [],
                'total' => 0,
                'currency' => $this->General->currency,
                'my_data' => $this->AccessService->buildListMyData($mySteamid),
            ];
        }

        [$searchAuth, $searchName] = $this->resolveSearch($search);
        $limit = max(1, $limit);
        $offset = max(0, $offset);
        $sort = $sort === 'up' ? 'up' : 'down';
        $balanceFilter = in_array($balanceFilter, ['with_balance', 'empty'], true) ? $balanceFilter : 'all';

        $total = $this->FinanceRepository->countFinances($searchAuth, $searchName, $balanceFilter);
        $rows = $this->FinanceRepository->fetchFinances($searchAuth, $searchName, $sort, $limit, $offset, $balanceFilter);
        $auths = array_values(array_filter(array_map(
            static fn(array $row): string => (string) ($row['auth'] ?? ''),
            $rows
        )));
        $lastDeposits = $this->FinanceRepository->fetchLastDeposits($auths);

        $data = [];
        foreach ($rows as $row) {
            $auth = (string) ($row['auth'] ?? '');
            if ($auth === '') {
                continue;
            }

            $steamid64 = con_steam64($auth);
            $deposit = $lastDeposits[$auth] ?? null;

            $data[] = [
                'auth' => $auth,
                'steamid' => $steamid64,
                'name' => ModuleHelper::resolveDisplayName($this->General, $steamid64, $row['name'] ?? null),
                'avatar' => $this->General->getAvatar($steamid64, 3),
                'checked_avatar' => $this->General->checkAvatar($steamid64),
                'cash' => (float) ($row['cash'] ?? 0),
                'all_cash' => (float) ($row['all_cash'] ?? 0),
                'last_deposit' => $deposit ? $this->formatDepositDate((string) ($deposit['pay_data'] ?? '')) : '',
                'last_deposit_summ' => $deposit ? (float) ($deposit['pay_summ'] ?? 0) : 0,
            ];
        }

        return [
            'status' => 'success',
            'data' => $data,
            'total' => $total,
            'currency' => $this->General->currency,
            'my_data' => $this->AccessService->buildListMyData($mySteamid),
        ];
    }

    public function addBalance(string $steamInput, $amountInput): array
    {
        if (!$this->FinanceRepository->isLkConnected()) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgLkNotConnected')];
        }

        $auth = con_steam32(trim($steamInput));
        if (!$auth) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifySteamId')];
        }

        $amount = $this->normalizeAmount($amountInput);
        if ($amount === null || $amount <= 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifyBalanceAmount')];
        }

        $user = $this->FinanceRepository->getUserByAuth($auth);
        if ($user === []) {
            $steamid64 = con_steam64($auth);
            $name = $this->General->checkName($steamid64);
            $this->FinanceRepository->createUser($auth, $name !== 'Unnamed' ? $name : '');
        }

        $this->FinanceRepository->addBalance($auth, $amount);
        $this->FinanceRepository->logPayment($auth, $amount, 'atools_add');

        $steamid64 = con_steam64($auth);
        $this->LogsService->add([
            'type' => 'add_balance',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => ['steamid' => $steamid64],
            'details' => ['amount' => $amount],
        ]);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgBalanceAdded')];
    }

    public function updateBalance(string $authInput, $newCashInput, $oldCashInput): array
    {
        if (!$this->FinanceRepository->isLkConnected()) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgLkNotConnected')];
        }

        $auth = con_steam32(trim($authInput));
        if (!$auth) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgSpecifySteamId')];
        }

        $user = $this->FinanceRepository->getUserByAuth($auth);
        if ($user === []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgFinanceNotFound')];
        }

        $newCash = $this->normalizeAmount($newCashInput);
        if ($newCash === null || $newCash < 0) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgInvalidBalanceAmount')];
        }

        $oldCash = $this->normalizeAmount($oldCashInput);
        if ($oldCash === null) {
            $oldCash = (float) ($user['cash'] ?? 0);
        }

        $diff = round($newCash - $oldCash, 2);
        if ($diff != 0) {
            $this->FinanceRepository->logPayment($auth, $diff, 'atools_update');
        }

        $this->FinanceRepository->setBalance($auth, $newCash);

        $steamid64 = con_steam64($auth);
        $this->LogsService->add([
            'type' => 'update_balance',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => ['steamid' => $steamid64],
            'details' => [
                'old_cash' => $oldCash,
                'new_cash' => $newCash,
            ],
        ]);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgBalanceUpdated')];
    }

    public function resetBalances(array $authList): array
    {
        if (!$this->FinanceRepository->isLkConnected()) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgLkNotConnected')];
        }

        $auths = [];
        foreach ($authList as $auth) {
            $auth = trim((string) $auth);
            if ($auth !== '') {
                $auths[$auth] = true;
            }
        }
        $auths = array_keys($auths);

        if ($auths === []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgFinancesNotSelected')];
        }

        $steamids64 = [];
        $clearedCash = 0.0;
        foreach ($auths as $auth) {
            $user = $this->FinanceRepository->getUserByAuth($auth);
            if ($user === []) {
                continue;
            }

            $oldCash = (float) ($user['cash'] ?? 0);
            if ($oldCash == 0) {
                continue;
            }

            $this->FinanceRepository->resetBalance($auth);
            $this->FinanceRepository->logPayment($auth, -$oldCash, 'atools_reset');
            $steamids64[] = con_steam64($auth);
            $clearedCash += $oldCash;
        }

        if ($steamids64 === []) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgFinanceNotFound')];
        }

        $this->LogsService->add([
            'type' => 'reset_balance',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => $this->LogsService->buildTargetSteamids($steamids64),
            'details' => ['cleared_cash' => round($clearedCash, 2)],
        ]);

        $message = count($steamids64) > 1
            ? ModuleHelper::phrase($this->Translate, '_at_msgBalancesReset')
            : ModuleHelper::phrase($this->Translate, '_at_msgBalanceReset');

        return ['status' => 'success', 'message' => $message];
    }

    public function deletePlayersWithoutDonation(): array
    {
        if (!$this->FinanceRepository->isLkConnected()) {
            return ['status' => 'error', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgLkNotConnected')];
        }

        $deleted = $this->FinanceRepository->deletePlayersWithoutDonation();

        $this->LogsService->add([
            'type' => 'delete_finances_without_donation',
            'admin' => ['steamid' => $_SESSION['steamid']],
            'target' => [],
            'details' => ['deleted_count' => $deleted],
        ]);

        return ['status' => 'success', 'message' => ModuleHelper::phrase($this->Translate, '_at_msgFinancePlayersWithoutDonationDeleted')];
    }

    private function resolveSearch(string $search): array
    {
        $search = trim($search);
        if ($search === '') {
            return ['', ''];
        }

        $steam32 = ModuleHelper::toSteam32($search);
        if (preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $steam32)) {
            return [$steam32, ''];
        }

        $steam64 = ModuleHelper::toSteam64($search);
        if ($steam64 !== $search && preg_match('/^7656119\d{10}$/', $steam64)) {
            $authFrom64 = ModuleHelper::toSteam32($steam64);
            if (preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $authFrom64)) {
                return [$authFrom64, ''];
            }
        }

        return ['', $search];
    }

    private function normalizeAmount($value): ?float
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return round((float) $value, 2);
    }

    private function formatDepositDate(string $value): string
    {
        if ($value === '') {
            return '';
        }

        foreach (['d.m.Y H:i:s', 'd.m.Y, H:i:s', 'd.m.Y H:i', 'd.m.Y, H:i'] as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date instanceof \DateTime) {
                return $date->format('d.m.Y, H:i');
            }
        }

        return preg_replace('/(\d{2}:\d{2}):\d{2}/', '$1', $value) ?? $value;
    }
}
