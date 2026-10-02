<?php

namespace app\modules\module_page_cards\ext\Repositories;

class DatabaseRepository
{
    protected $Db;

    public function __construct($Db)
    {
        $this->Db = $Db;
    }

    public function createTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS `neo_cards_rewards` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `type` VARCHAR(100) NOT NULL,
                `count` INT NOT NULL,
                `rare` INT(1) NOT NULL,
                `chance` INT(3) NOT NULL,
                `updated_at` BIGINT NOT NULL,
                `created_at` BIGINT NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS neo_cards_user_progress (
                `id` INT NOT NULL AUTO_INCREMENT,
                `steamid` BIGINT(17) NOT NULL UNIQUE,
                `last_open_date` DATE NOT NULL,
                `streak` INT NOT NULL DEFAULT 0,
                `updated_at` BIGINT NOT NULL,
                `created_at` BIGINT NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS neo_cards_history (
                `id` INT NOT NULL AUTO_INCREMENT,
                `steamid` BIGINT(17) NOT NULL,
                `reward_id` INT NOT NULL,
                `streak` INT NOT NULL,
                `buy_open` BOOLEAN DEFAULT FALSE,
                `created_at` BIGINT NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        ];
        foreach ($tables as $sql) {
            $this->Db->query('Core', 0, 0, $sql);
        }
    }

    public function checkOpen(string $steamid): array
    {
        return $this->Db->query('Core', 0, 0, "SELECT * FROM neo_cards_user_progress WHERE steamid = ? LIMIT 1", [$steamid]);
    }

    public function newUserProgress(string $steamid)
    {
        return $this->Db->query('Core', 0, 0, "INSERT INTO neo_cards_user_progress (steamid, last_open_date, streak, updated_at, created_at) VALUES (?, ?, 0, ?, ?)", [$steamid, '1970-01-01', time(), time()]);
    }

    public function updateUserProgress(string $steamid, int $streak)
    {
        return $this->Db->query('Core', 0, 0, "UPDATE neo_cards_user_progress SET last_open_date = ?, streak = ?, updated_at = ? WHERE steamid = ?", [date("Y-m-d"), $streak, time(), $steamid]);
    }

    public function updateStreak(string $steamid, int $streak)
    {
        return $this->Db->query('Core', 0, 0, "UPDATE neo_cards_user_progress SET streak = ?, updated_at = ? WHERE steamid = ?", [$streak, time(), $steamid]);
    }

    public function updateUserProgressBuy(string $steamid): array
    {
        return $this->Db->query('Core', 0, 0, "UPDATE neo_cards_user_progress SET last_open_date = ?, updated_at = ? WHERE steamid = ?", ['2000-01-01', time(), $steamid]);
    }

    public function addHistoryRecord(string $steamid, int $reward_id, int $streak, int $buy): array
    {
        return $this->Db->query('Core', 0, 0, "INSERT INTO neo_cards_history (steamid, reward_id, streak, buy_open, created_at) VALUES (?, ?, ?, ?, ?)", [$steamid, $reward_id, $streak, $buy, time()]);
    }

    public function getRewards(): array
    {
        return $this->Db->queryAll('Core', 0, 0, "SELECT id, type, rare, count, chance FROM neo_cards_rewards ORDER BY rare ASC");
    }

    public function checkLKTable(string $steamid): array
    {
        return $this->Db->query('lk', 0, 0, "SELECT `auth`, `cash` FROM `lk` WHERE `auth` = ?", [$steamid]);
    }

    public function createdUserLKTable(string $steamid)
    {
        return $this->Db->query('lk', 0, 0, "INSERT INTO `lk` SET `auth` = ?, `cash` = 0, `all_cash` = 0", [$steamid]);
    }

    public function updateBalanceUser(int $summ, string $steamid)
    {
        return $this->Db->query('lk', 0, 0, "UPDATE `lk` SET `cash` = `cash` + ? WHERE `auth` = ?", [$summ, $steamid]);
    }

    public function logBalanceUser(int $summ, string $steamid, string $log)
    {
        return $this->Db->query('lk', 0, 0, "INSERT INTO `lk_pays` (`pay_order`, `pay_auth`, `pay_summ`, `pay_data`, `pay_system`, `pay_promo`, `pay_status`) VALUES (?, ?, ?, ?, 'admin', ?, 1)", [time() % 100000, $steamid, $summ, date('d.m.Y H:i:s'), $log]);
    }

    public function getCountOpenCards(): array
    {
        return $this->Db->query('Core', 0, 0, "SELECT
                COUNT(*) AS total_all_time,
                SUM(created_at >= UNIX_TIMESTAMP(CURDATE())) AS today,
                SUM(
                    created_at >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 DAY)
                    AND created_at < UNIX_TIMESTAMP(CURDATE())
                ) AS yesterday,
                SUM(created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY)) AS last_7_days,
                SUM(created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 30 DAY)) AS last_30_days,
                SUM(created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 90 DAY)) AS last_90_days,
                SUM(buy_open = 1) AS buy_open_total
            FROM neo_cards_history
        ");
    }

    public function getCountMoney(): array
    {
        return $this->Db->query('Core', 0, 0, "SELECT
                SUM(CASE WHEN r.type = 'money' THEN r.count ELSE 0 END) AS money_total,
                SUM(CASE WHEN r.type = 'credits' THEN r.count ELSE 0 END) AS credits_total
            FROM neo_cards_history h
            JOIN neo_cards_rewards r ON r.id = h.reward_id;
        ");
    }

    public function getAllUsersStreak(): array
    {
        $result = $this->Db->queryAll('Core', 0, 0, "SELECT streak, COUNT(*) as count FROM neo_cards_user_progress WHERE streak >= 1 AND streak <= 7 GROUP BY streak ORDER BY streak ASC");
        $streaks = [];
        for ($i = 1; $i <= 7; $i++) {
            $streaks[$i] = ['streak' => $i, 'count' => 0];
        }
        foreach ($result as $row) {
            $streaks[$row['streak']] = $row;
        }
        return array_values($streaks);
    }

    public function getTopUsersOpens(): array
    {
        return $this->Db->queryAll('Core', 0, 0, "SELECT steamid, COUNT(*) as opens FROM neo_cards_history GROUP BY steamid ORDER BY opens DESC LIMIT 100");
    }

    public function addReward(string $type, int $count, int $rare, int $chance): array
    {
        return $this->Db->query('Core', 0, 0, "INSERT INTO neo_cards_rewards (type, count, rare, chance, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?)", [$type, $count, $rare, $chance, time(), time()]);
    }

    public function updateReward(int $id, string $type, int $count, int $rare, int $chance): array
    {
        return $this->Db->query('Core', 0, 0, "UPDATE neo_cards_rewards SET type = ?, count = ?, rare = ?, chance = ?, updated_at = ? WHERE id = ?", [$type, $count, $rare, $chance, time(), $id]);
    }

    public function getReward(int $id): array
    {
        return $this->Db->query('Core', 0, 0, "SELECT id, type, rare, count, chance FROM neo_cards_rewards WHERE id = ? LIMIT 1", [$id]);
    }

    public function deleteReward(int $id): array
    {
        return $this->Db->query('Core', 0, 0, "DELETE FROM neo_cards_rewards WHERE id = ?", [$id]);
    }
}
