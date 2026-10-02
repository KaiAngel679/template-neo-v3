<?php

namespace app\modules\module_page_reviews\ext\Repositories;

class RewardRepository
{
    private $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function hasReceived(string $steamid64): bool
    {
        $row = $this->Db->query(
            'Core',
            0,
            0,
            'SELECT `id` FROM `neo_review_rewards` WHERE `steamid` = ? LIMIT 1',
            [$steamid64]
        );

        return is_array($row) && $row !== [];
    }

    public function markReceived(string $steamid64, int $amount): void
    {
        $this->Db->query(
            'Core',
            0,
            0,
            'INSERT INTO `neo_review_rewards` (`steamid`, `amount`, `created_at`) VALUES (?, ?, ?)',
            [$steamid64, $amount, time()]
        );
    }

    public function tryMarkReceived(string $steamid64, int $amount): bool
    {
        if ($this->hasReceived($steamid64)) {
            return false;
        }

        $stmt = $this->Db->inquiry(
            'Core',
            0,
            0,
            'INSERT INTO `neo_review_rewards` (`steamid`, `amount`, `created_at`) VALUES (?, ?, ?)',
            [$steamid64, $amount, time()]
        );

        if ($stmt === false) {
            return false;
        }

        return (int) $stmt->rowCount() === 1;
    }

    public function isLkConnected(): bool
    {
        return !empty($this->Db->db_data['lk']);
    }

    public function ensureLkUser(string $steam32): void
    {
        $row = $this->Db->query('lk', 0, 0, 'SELECT `auth` FROM `lk` WHERE `auth` = ? LIMIT 1', [$steam32]);
        if (is_array($row) && $row !== []) {
            return;
        }

        $this->Db->query(
            'lk',
            0,
            0,
            'INSERT INTO `lk` SET `auth` = ?, `cash` = 0, `all_cash` = 0',
            [$steam32]
        );
    }

    public function addCash(string $steam32, int $amount): void
    {
        $this->Db->query(
            'lk',
            0,
            0,
            'UPDATE `lk` SET `cash` = `cash` + ? WHERE `auth` = ?',
            [$amount, $steam32]
        );
    }

    public function logPay(string $steam32, int $amount, string $promo): void
    {
        $this->Db->query(
            'lk',
            0,
            0,
            'INSERT INTO `lk_pays` (`pay_order`, `pay_auth`, `pay_summ`, `pay_data`, `pay_system`, `pay_promo`, `pay_status`)
             VALUES (?, ?, ?, ?, \'admin\', ?, 1)',
            [time() % 100000, $steam32, $amount, date('d.m.Y H:i:s'), $promo]
        );
    }
}
