<?php

namespace app\modules\module_page_bonuses\ext\Repositories;

class DatabaseRepository
{
    protected $Db;

    public function __construct($Db)
    {
        $this->Db = $Db;
    }

    public function createTables(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `neo_bonuses_reward` (
                    `steamid` bigint(17) NOT NULL UNIQUE,
                    `tg_reward` tinyint(1) DEFAULT 0,
                    `ds_reward` tinyint(1) DEFAULT 0,
                    `vk_reward` tinyint(1) DEFAULT 0,
                    PRIMARY KEY (`steamid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
                CREATE TABLE `neo_bonuses_user_modal` (
                    `steamid` bigint(17) NOT NULL UNIQUE,
                    `modal_show` tinyint(1) DEFAULT 0,
                    PRIMARY KEY (`steamid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

        $this->Db->query('Core', 0, 0, $sql);
    }

    public function checkModal(string $steam): array
    {
        return $this->Db->query('Core', 0, 0, "SELECT `modal_show` FROM `neo_bonuses_user_modal` WHERE `steamid` = ?", [$steam]);
    }

    public function createdUserModal(string $steam)
    {
        return $this->Db->query('Core', 0, 0, "INSERT IGNORE INTO `neo_bonuses_user_modal` (`steamid`, `modal_show`) VALUES (?, 0)", [$steam]);
    }

    public function updateUserModal(string $steam)
    {
        return $this->Db->query('Core', 0, 0, "UPDATE `neo_bonuses_user_modal` SET `modal_show` = 1 WHERE `steamid` = ?", [$steam]);
    }

    public function checkReward(string $steam): array
    {
        return $this->Db->query('Core', 0, 0, "SELECT `tg_reward`, `ds_reward`, `vk_reward` FROM `neo_bonuses_reward` WHERE `steamid` = ?", [$steam]);
    }

    public function createdUserReward(string $steam)
    {
        return $this->Db->query('Core', 0, 0, "INSERT IGNORE INTO `neo_bonuses_reward` (`steamid`, `tg_reward`, `ds_reward`, `vk_reward`) VALUES (?, 0, 0, 0)", [$steam]);
    }

    public function updateUserRewardTg(string $steam)
    {
        return $this->Db->query('Core', 0, 0, "UPDATE `neo_bonuses_reward` SET `tg_reward` = 1 WHERE `steamid` = ?", [$steam]);
    }

    public function updateUserRewardDs(string $steam)
    {
        return $this->Db->query('Core', 0, 0, "UPDATE `neo_bonuses_reward` SET `ds_reward` = 1 WHERE `steamid` = ?", [$steam]);
    }

    public function updateUserRewardVk(string $steam)
    {
        return $this->Db->query('Core', 0, 0, "UPDATE `neo_bonuses_reward` SET `vk_reward` = 1 WHERE `steamid` = ?", [$steam]);
    }

    public function checkLKTable(string $steam): array
    {
        return $this->Db->query('lk', 0, 0, "SELECT `auth` FROM `lk` WHERE `auth` = ?", [$steam]);
    }

    public function createdUserLKTable(string $steam)
    {
        return $this->Db->query('lk', 0, 0, "INSERT INTO `lk` SET `auth` = ?, `cash` = 0, `all_cash` = 0", [$steam]);
    }

    public function updateBalanceUser(int $summ, string $steam)
    {
        return $this->Db->query('lk', 0, 0, "UPDATE `lk` SET `cash` = `cash` + ? WHERE `auth` = ?", [$summ, $steam]);
    }

    public function logBalanceUser(int $summ, string $steam, string $status): array
    {
        return $this->Db->query('lk', 0, 0, "INSERT INTO `lk_pays` (`pay_order`, `pay_auth`, `pay_summ`, `pay_data`, `pay_system`, `pay_promo`, `pay_status`) VALUES (?, ?, ?, ?, 'admin', ?, 1)", [time() % 100000, $steam, $summ, date('d.m.Y H:i:s'), $status]);
    }
}
