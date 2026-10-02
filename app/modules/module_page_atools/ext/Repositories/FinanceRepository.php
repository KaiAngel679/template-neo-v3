<?php

namespace app\modules\module_page_atools\ext\Repositories;

class FinanceRepository
{
    protected $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function isLkConnected(): bool
    {
        return !empty($this->Db->db_data['lk']);
    }

    public function sumRevenueBetween(int $from, int $to): float
    {
        if (!$this->isLkConnected() || $from >= $to) {
            return 0.0;
        }

        $fromStr = date('Y-m-d H:i:s', $from);
        $toStr = date('Y-m-d H:i:s', $to);

        $row = $this->Db->query(
            'lk',
            0,
            0,
            "SELECT COALESCE(SUM(`pay_summ`), 0) AS total
             FROM `lk_pays`
             WHERE `pay_status` = 1
               AND `pay_summ` > 0
               AND `pay_system` != 'admin'
               AND COALESCE(
                   STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s'),
                   STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i'),
                   STR_TO_DATE(`pay_data`, '%d.%m.%Y')
               ) >= ?
               AND COALESCE(
                   STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s'),
                   STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i'),
                   STR_TO_DATE(`pay_data`, '%d.%m.%Y')
               ) < ?",
            [$fromStr, $toStr]
        );

        return (float) ($row['total'] ?? 0);
    }

    public function getUserByAuth(string $auth): array
    {
        if (!$this->isLkConnected() || $auth === '') {
            return [];
        }

        $row = $this->Db->query('lk', 0, 0, 'SELECT * FROM `lk` WHERE `auth` = ? LIMIT 1', [$auth]);

        return is_array($row) ? $row : [];
    }

    public function createUser(string $auth, string $name = ''): void
    {
        if (!$this->isLkConnected() || $auth === '') {
            return;
        }

        $this->Db->query(
            'lk',
            0,
            0,
            'INSERT INTO `lk` (`auth`, `name`, `cash`, `all_cash`) VALUES (?, ?, 0, 0)',
            [$auth, $name]
        );
    }

    public function countFinances(string $searchAuth, string $searchName, string $balanceFilter = 'all'): int
    {
        if (!$this->isLkConnected()) {
            return 0;
        }

        [$whereSql, $params] = $this->buildFinancesWhere($searchAuth, $searchName, $balanceFilter);

        $sql = 'SELECT COUNT(*) AS total FROM `lk`';
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }

        $row = $this->Db->query('lk', 0, 0, $sql, $params);

        return (int) ($row['total'] ?? 0);
    }

    public function fetchFinances(string $searchAuth, string $searchName, string $sort, int $limit, int $offset, string $balanceFilter = 'all'): array
    {
        if (!$this->isLkConnected()) {
            return [];
        }

        [$whereSql, $params] = $this->buildFinancesWhere($searchAuth, $searchName, $balanceFilter);

        $order = $sort === 'up' ? 'ASC' : 'DESC';
        $sql = 'SELECT * FROM `lk`';
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }
        $sql .= " ORDER BY `cash` {$order}, `auth` ASC LIMIT {$limit} OFFSET {$offset}";

        $rows = $this->Db->queryAll('lk', 0, 0, $sql, $params);

        return is_array($rows) ? $rows : [];
    }

    private function buildFinancesWhere(string $searchAuth, string $searchName, string $balanceFilter): array
    {
        $searchParts = [];
        $params = [];

        if ($searchAuth !== '') {
            $searchParts[] = '`auth` LIKE ?';
            $params[] = '%' . $searchAuth . '%';
        }
        if ($searchName !== '') {
            $searchParts[] = '`name` LIKE ?';
            $params[] = '%' . $searchName . '%';
        }

        $whereParts = [];
        if ($searchParts !== []) {
            $whereParts[] = '(' . implode(' OR ', $searchParts) . ')';
        }

        $balanceFilter = $this->normalizeBalanceFilter($balanceFilter);
        if ($balanceFilter === 'with_balance') {
            $whereParts[] = '`cash` > 0';
        } elseif ($balanceFilter === 'empty') {
            $whereParts[] = '`cash` <= 0';
        }

        return [implode(' AND ', $whereParts), $params];
    }

    private function normalizeBalanceFilter(string $balanceFilter): string
    {
        $balanceFilter = strtolower(trim($balanceFilter));

        return in_array($balanceFilter, ['with_balance', 'empty'], true) ? $balanceFilter : 'all';
    }

    public function fetchLastDeposits(array $auths): array
    {
        if (!$this->isLkConnected() || $auths === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($auths), '?'));
        $sql = "SELECT p.`pay_auth`, p.`pay_summ`, p.`pay_data`
                FROM `lk_pays` p
                INNER JOIN (
                    SELECT `pay_auth`, MAX(`pay_id`) AS max_id
                    FROM `lk_pays`
                    WHERE `pay_status` = 1 AND `pay_auth` IN ({$placeholders})
                    GROUP BY `pay_auth`
                ) latest ON latest.max_id = p.`pay_id`";

        $rows = $this->Db->queryAll('lk', 0, 0, $sql, $auths);
        $map = [];
        foreach ($rows as $row) {
            $auth = (string) ($row['pay_auth'] ?? '');
            if ($auth !== '') {
                $map[$auth] = $row;
            }
        }

        return $map;
    }

    public function addBalance(string $auth, float $amount): void
    {
        $this->Db->query(
            'lk',
            0,
            0,
            'UPDATE `lk` SET `cash` = `cash` + ?, `all_cash` = `all_cash` + ? WHERE `auth` = ?',
            [$amount, $amount, $auth]
        );
    }

    public function setBalance(string $auth, float $cash): void
    {
        $this->Db->query(
            'lk',
            0,
            0,
            'UPDATE `lk` SET `cash` = ? WHERE `auth` = ?',
            [$cash, $auth]
        );
    }

    public function resetBalance(string $auth): void
    {
        $this->Db->query(
            'lk',
            0,
            0,
            'UPDATE `lk` SET `cash` = 0 WHERE `auth` = ?',
            [$auth]
        );
    }

    public function deletePlayersWithoutDonation(): int
    {
        if (!$this->isLkConnected()) {
            return 0;
        }

        $row = $this->Db->query(
            'lk',
            0,
            0,
            'SELECT COUNT(*) AS total FROM `lk` WHERE !`cash` AND `all_cash` = 0',
            []
        );
        $count = (int) ($row['total'] ?? 0);

        if ($count === 0) {
            return 0;
        }

        $this->Db->query('lk', 0, 0, 'DELETE FROM `lk` WHERE !`cash` AND `all_cash` = 0', []);

        return $count;
    }

    public function logPayment(string $auth, float $summ, string $status = 'atools'): void
    {
        $this->Db->query(
            'lk',
            0,
            0,
            'INSERT INTO `lk_pays` (`pay_order`, `pay_auth`, `pay_summ`, `pay_data`, `pay_system`, `pay_promo`, `pay_status`)
             VALUES (?, ?, ?, ?, ?, ?, 1)',
            [time() % 100000, $auth, $summ, date('d.m.Y H:i:s'), 'admin', $status]
        );
    }
}
