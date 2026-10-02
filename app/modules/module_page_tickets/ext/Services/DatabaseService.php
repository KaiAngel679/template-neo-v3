<?php

namespace app\modules\module_page_tickets\ext\Services;

class DatabaseService
{
    protected $Db;

    public function __construct($Db)
    {
        $this->Db = $Db;
    }

    public function checkTables()
    {
        $checkTable = ['neo_tickets_access', 'neo_tickets_blocks', 'neo_tickets_categories', 'neo_tickets_list'];
        foreach ($checkTable as $key) {
            $result = $this->Db->mysql_table_search('Core', 0, 0, $key);
            if ($result == 0) {
                return false;
            }
        }
        return true;
    }

    public function createTables()
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS `neo_tickets_access` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `steamid` BIGINT(17) NOT NULL,
                `access` INT(1) NOT NULL,
                `add_access` INT(1) NOT NULL,
                `add_block` INT(1) NOT NULL,
                `add_delete` INT(1) NOT NULL,
                `add_settings` INT(1) NOT NULL,
                `general` INT(1) NOT NULL,
                `category` VARCHAR(255),
                `edit_at` BIGINT NOT NULL,
                `created_at` BIGINT NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS `neo_tickets_blocks` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `steamid` BIGINT(17) NOT NULL,
                `reason` VARCHAR(255) NOT NULL,
                `duration` INT NOT NULL,
                `edit_at` BIGINT NOT NULL,
                `created_at` BIGINT NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS `neo_tickets_categories` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `type` INT(1) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `questions` TEXT,
                `description` TEXT,
                `server_on` INT(1) NOT NULL,
                `servers` VARCHAR(255),
                `server_played` INT,
                `response_time` INT,
                `replay_time` INT,
                `amount_money` INT,
                `sort_id` INT NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS `neo_tickets_list` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `category_id` INT NOT NULL,
                `steamid` BIGINT(17) NOT NULL,
                `topic` VARCHAR(255) NOT NULL,
                `state` INT(1) NOT NULL,
                `status` INT(1) NOT NULL,
                `server` INT,
                `steamid_close` BIGINT(17),
                `edit_at` BIGINT NOT NULL,
                `created_at` BIGINT NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        ];
        foreach ($tables as $sql) {
            $this->Db->query('Core', 0, 0, $sql);
        }
        $this->Db->query('Core', 0, 0, "INSERT INTO `neo_tickets_access` (`steamid`, `access`, `add_access`, `add_block`, `add_delete`, `add_settings`, `general`, `category`, `edit_at`, `created_at`) VALUES (?, 1, 1, 1, 1, 1, 1, null, ?, ?)", [$_SESSION['steamid64'], time(), time()]);
        return ['status' => 'success'];
    }

    public function getCategoriesTabs()
    {
        return $this->Db->queryAll('Core', 0, 0, 'SELECT `id`, `title`, `response_time` FROM `neo_tickets_categories` ORDER BY `sort_id`');
    }

    public function getCategoryTitle($id)
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `title` FROM `neo_tickets_categories`WHERE `id` = ? LIMIT 1', [$id])['title'];
    }

    public function getCategoriesInfo($id)
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `type`, `description`, `server_on`, `servers`, `questions` FROM `neo_tickets_categories` WHERE `id` = ? LIMIT 1', [$id]);
    }

    public function getCategoryFull($id)
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `id`, `type`, `title`, `description`, `server_on`, `servers`, `server_played`, `response_time`, `replay_time`, `amount_money`, `questions`, `sort_id` FROM `neo_tickets_categories` WHERE `id` = ? LIMIT 1', [$id]);
    }

    public function checkCategory($id)
    {
        return $this->Db->queryNum('Core', 0, 0, 'SELECT COUNT(*) FROM `neo_tickets_categories` WHERE `id` = ?', [$id])[0];
    }

    public function getCategorySend($id)
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `server_played`, `replay_time`, `type`, `questions` FROM `neo_tickets_categories` WHERE `id` = ? LIMIT 1', [$id]);
    }

    public function getCategoriesFull()
    {
        return $this->Db->queryAll('Core', 0, 0, 'SELECT `id`, `type`, `title`, `description`, `server_on`, `servers`, `server_played`, `sort_id` FROM `neo_tickets_categories` ORDER BY `sort_id`');
    }

    public function createNewCategory($type, $title, $questions, $description, $servers, $server_on, $playtime, $response, $replay, $amount, $sort)
    {
        return $this->Db->query('Core', 0, 0, 'INSERT INTO `neo_tickets_categories` (`type`, `title`, `questions`, `description`, `server_on`, `servers`, `server_played`, `response_time`, `replay_time`, `amount_money`, `sort_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$type, $title, $questions, $description, $server_on, $servers, $playtime, $response, $replay, $amount, $sort]);
    }

    public function deleteCategory($id)
    {
        return $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_tickets_categories` WHERE `id` = ?', [$id]);
    }

    public function editCategory($id, $title, $description, $server_on, $servers, $playtime, $response, $replay, $amount, $type, $questions, $sort)
    {
        return $this->Db->query('Core', 0, 0, 'UPDATE `neo_tickets_categories` SET `title` = ?, `description` = ?, `server_on` = ?, `servers` = ?, `server_played` = ?, `response_time` = ?, `replay_time` = ?, `amount_money` = ?, `questions` = ?, `type` = ?, `sort_id` = ? WHERE `id` = ?', [$title, $description, $server_on, $servers, $playtime, $response, $replay, $amount, $questions, $type, $sort, $id]);
    }

    public function getServers()
    {
        return $this->Db->queryAll('Core', 0, 0, 'SELECT `id`, `name_custom` FROM `lvl_web_servers`');
    }

    public function getServer($id)
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `server_stats` FROM `lvl_web_servers` WHERE `id` = ? LIMIT 1', [$id])['server_stats'];
    }

    public function getServerName($id)
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `name_custom` FROM `lvl_web_servers` WHERE `id` = ? LIMIT 1', [$id])['name_custom'];
    }

    public function getPlayTime($user, $db, $table, $steam)
    {
        $stats = $this->Db->query('LevelsRanks', $user, $db, 'SELECT `playtime` FROM `' . $table . '` WHERE `steam` LIKE ? LIMIT 1', [$steam])['playtime'];
        return floor($stats / 3600);
    }

    public function getPlayTimeAll($steam)
    {
        $total_time = 0;
        for ($i = 0; $i < $this->Db->table_count['LevelsRanks']; $i++) {
            $stats = $this->Db->queryAll('LevelsRanks', $this->Db->db_data['LevelsRanks'][$i]['USER_ID'], $this->Db->db_data['LevelsRanks'][$i]['DB_num'], 'SELECT `playtime` FROM `' . $this->Db->db_data['LevelsRanks'][$i]['Table'] . '` WHERE `steam` LIKE ? LIMIT 1', [$steam]);
            $total_time += $stats[0]['playtime'];
        }
        return floor($total_time / 3600);
    }

    public function createTicket($category, $steam, $topic, $server)
    {
        return $this->Db->query('Core', 0, 0, 'INSERT INTO `neo_tickets_list` (`category_id`, `steamid`, `topic`, `state`, `status`, `server`, `edit_at`, `created_at`) VALUES (?, ?, ?, 1, 1, ?, ' . time() . ', ' . time() . ')', [$category, $steam, $topic, $server]);
    }

    public function getLastId()
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `id` FROM `neo_tickets_list` ORDER BY `id` DESC LIMIT 1')['id'];
    }

    public function getTicketsListUser($steam)
    {
        return $this->Db->queryAll('Core', 0, 0, 'SELECT `id` FROM `neo_tickets_list` WHERE `steamid` = ? AND `status` = 1', [$steam]);
    }

    public function getTicketList($steam, $limit, $offset)
    {
        $tickets = $this->Db->queryAll('Core', 0, 0, 'SELECT `id`, `topic`, `created_at`, `steamid` FROM `neo_tickets_list` WHERE `steamid` = ? AND `status` = 1 ORDER BY `created_at` DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset, [$steam]);
        $total = $this->Db->query('Core', 0, 0, 'SELECT COUNT(*) as `total` FROM `neo_tickets_list` WHERE `steamid` = ? AND `status` = 1 LIMIT 1', [$steam])['total'];
        return ['tickets' => $tickets, 'total' => $total];
    }

    public function getTicketListAdmin($category, $server, $limit, $search, $offset)
    {
        $infodb = $this->getAccessFullCheck($_SESSION['steamid64'])['category'];
        $categories = $infodb ? array_map('trim', explode(';', $infodb)) : [];
        $query = 'SELECT `id`, `topic`, `created_at`, `steamid`, `category_id` FROM `neo_tickets_list` WHERE status = 1';
        $params = [];
        if ($category !== 'all') {
            $query .= ' AND `category_id` = ?';
            $params[] = $category;
        } else {
            if (!empty($categories)) {
                $placeholders = implode(',', array_fill(0, count($categories), '?'));
                $query .= " AND `category_id` IN ($placeholders)";
                $params = array_merge($params, $categories);
            }
        }
        if ($server !== 'all') {
            $query .= ' AND server = ?';
            $params[] = $server;
        }
        if (!empty($search)) {
            $query .= ' AND (steamid LIKE ? OR topic LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        $query .= ' ORDER BY `created_at` DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        $tickets = $this->Db->queryAll('Core', 0, 0, $query, $params);

        $countQuery = 'SELECT COUNT(*) as total FROM `neo_tickets_list` WHERE status = 1';
        $countParams = [];
        if ($category !== 'all') {
            $countQuery .= ' AND `category_id` = ?';
            $countParams[] = $category;
        } else {
            if (!empty($categories)) {
                $placeholders = implode(',', array_fill(0, count($categories), '?'));
                $countQuery .= " AND `category_id` IN ($placeholders)";
                $countParams = array_merge($countParams, $categories);
            }
        }
        if ($server !== 'all') {
            $countQuery .= ' AND server = ?';
            $countParams[] = $server;
        }
        if (!empty($search)) {
            $countQuery .= ' AND (`steamid` LIKE ? OR `topic` LIKE ?)';
            $countParams[] = '%' . $search . '%';
            $countParams[] = '%' . $search . '%';
        }
        $total = $this->Db->query('Core', 0, 0, $countQuery, $countParams)['total'];

        return ['tickets' => $tickets, 'total' => $total];
    }

    public function closeTicket($id, $steam)
    {
        return $this->Db->query('Core', 0, 0, 'UPDATE `neo_tickets_list` SET `status` = 2, `state` = 2, `steamid_close` = ?, `edit_at` = ? WHERE `id` = ?', [$steam, time(), $id]);
    }

    public function closeTicketAuto($id)
    {
        return $this->Db->query('Core', 0, 0, 'UPDATE `neo_tickets_list` SET `status` = 2, `state` = 3, `edit_at` = ? WHERE `id` = ?', [time(), $id]);
    }

    public function openTicket($id)
    {
        return $this->Db->query('Core', 0, 0, 'UPDATE `neo_tickets_list` SET `status` = 1, `state` = 1, `steamid_close` = null, `edit_at` = ? WHERE `id` = ?', [time(), $id]);
    }

    public function deleteTicket($id)
    {
        return $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_tickets_list` WHERE `id` = ?', [$id]);
    }

    public function moveTicket($id, $category)
    {
        return $this->Db->query('Core', 0, 0, 'UPDATE `neo_tickets_list` SET `category_id` = ?, `edit_at` = ? WHERE `id` = ?', [$category, time(), $id]);
    }

    public function getArchiveTicketCategoryId($id)
    {
        return $this->Db->queryAll('Core', 0, 0, 'SELECT `edit_at`, `created_at` FROM `neo_tickets_list` WHERE `category_id` = ? AND `status` = 2 ORDER BY `created_at` DESC LIMIT 50', [$id]);
    }

    public function getAccessFullCheck($steam)
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `access`, `add_access`, `add_block`, `add_delete`, `add_settings`, `category`, `general` FROM `neo_tickets_access` WHERE `steamid` = ? LIMIT 1', [$steam]);
    }

    public function getAccessAll()
    {
        return $this->Db->queryAll('Core', 0, 0, 'SELECT * FROM `neo_tickets_access`');
    }

    public function deleteAccess($steam)
    {
        return $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_tickets_access` WHERE `steamid` = :steam', ['steam' => $steam]);
    }

    public function editAccess($steam, $delete, $block, $add_access, $settings, $general, $category)
    {
        return $this->Db->query('Core', 0, 0, 'UPDATE `neo_tickets_access` SET `add_delete` = ?, `add_block` = ?, `add_access` = ?, `add_settings` = ?, `general` = ?, `category` = ?, `edit_at` = ? WHERE `steamid` = ?', [$delete, $block, $add_access, $settings, $general, $category, time(), $steam]);
    }

    public function createNewAccess($steam, $add_access, $add_block, $add_delete, $add_settings, $general, $category)
    {
        return $this->Db->query('Core', 0, 0, 'INSERT INTO `neo_tickets_access` (`steamid`, `access`, `add_access`, `add_block`, `add_delete`, `add_settings`, `general`, `category`, `edit_at`, `created_at`) VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, ?)', [$steam, $add_access, $add_block, $add_delete, $add_settings, $general, $category, time(), time()]);
    }

    public function getUserCheckChat($steam, $id)
    {
        return $this->Db->queryNum('Core', 0, 0, 'SELECT COUNT(*) FROM `neo_tickets_list` WHERE `steamid` = ? AND `id` = ? LIMIT 1', [$steam, $id])[0];
    }

    public function getInfoChat($id)
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `id`, `category_id`, `steamid`, `topic`, `state`, `status`, `server`, `steamid_close`, `edit_at`, `created_at` FROM `neo_tickets_list` WHERE `id` = ? LIMIT 1', [$id]);
    }

    public function getInfoUserSteam($id)
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `steamid` FROM `neo_tickets_list` WHERE `id` = ? LIMIT 1', [$id])['steamid'];
    }

    public function getTopic($id)
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `topic` FROM `neo_tickets_list` WHERE `id` = ? LIMIT 1', [$id])['topic'];
    }

    public function getTicketArchive($steam, $limit, $offset)
    {
        $tickets = $this->Db->queryAll('Core', 0, 0, 'SELECT * FROM `neo_tickets_list` WHERE `steamid` = ? AND `status` = 2 ORDER BY `created_at` DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset, [$steam]);
        $total = $this->Db->query('Core', 0, 0, 'SELECT COUNT(*) as `total` FROM `neo_tickets_list` WHERE `steamid` = ? AND `status` = 2 LIMIT 1', [$steam])['total'];
        return ['tickets' => $tickets, 'total' => $total];
    }

    public function getTicketArchiveAdmin($category, $server, $limit, $search, $offset)
    {
        $infodb = $this->getAccessFullCheck($_SESSION['steamid64'])['category'];
        $categories = $infodb ? array_map('trim', explode(';', $infodb)) : [];
        $query = 'SELECT * FROM `neo_tickets_list` WHERE status = 2';
        $params = [];
        if ($category !== 'all') {
            $query .= ' AND `category_id` = ?';
            $params[] = $category;
        } else {
            if (!empty($categories)) {
                $placeholders = implode(',', array_fill(0, count($categories), '?'));
                $query .= " AND `category_id` IN ($placeholders)";
                $params = array_merge($params, $categories);
            }
        }
        if ($server !== 'all') {
            $query .= ' AND server = ?';
            $params[] = $server;
        }
        if (!empty($search)) {
            $query .= ' AND (steamid LIKE ? OR topic LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        $query .= ' ORDER BY `created_at` DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        $tickets = $this->Db->queryAll('Core', 0, 0, $query, $params);

        $countQuery = 'SELECT COUNT(*) as total FROM `neo_tickets_list` WHERE status = 2';
        $countParams = [];
        if ($category !== 'all') {
            $countQuery .= ' AND `category_id` = ?';
            $countParams[] = $category;
        } else {
            if (!empty($categories)) {
                $placeholders = implode(',', array_fill(0, count($categories), '?'));
                $countQuery .= " AND `category_id` IN ($placeholders)";
                $countParams = array_merge($countParams, $categories);
            }
        }
        if ($server !== 'all') {
            $countQuery .= ' AND server = ?';
            $countParams[] = $server;
        }
        if (!empty($search)) {
            $countQuery .= ' AND (`steamid` LIKE ? OR `topic` LIKE ?)';
            $countParams[] = '%' . $search . '%';
            $countParams[] = '%' . $search . '%';
        }
        $total = $this->Db->query('Core', 0, 0, $countQuery, $countParams)['total'];

        return ['tickets' => $tickets, 'total' => $total];
    }

    public function getTickets()
    {
        return $this->Db->queryAll('Core', 0, 0, 'SELECT `id`, `edit_at` FROM `neo_tickets_list` WHERE `status` = 1');
    }

    public function getLastTicket($steam, $id)
    {
        return $this->Db->query('Core', 0, 0, 'SELECT `created_at` FROM `neo_tickets_list` WHERE `steamid` = ? AND `category_id` = ? ORDER BY `created_at` DESC LIMIT 1', [$steam, $id])['created_at'];
    }

    public function createNewBlock($steam, $reason, $time)
    {
        return $this->Db->query('Core', 0, 0, "INSERT INTO `neo_tickets_blocks` (`steamid`, `reason`, `duration`, `edit_at`, `created_at`) VALUES (?, ?, ?, ?, ?)", [$steam, $reason, $time, time(), time()]);
    }

    public function getBlocks()
    {
        return $this->Db->queryAll('Core', 0, 0, 'SELECT `steamid`, `reason`, `duration` FROM `neo_tickets_blocks`');
    }

    public function checkBlock($steam)
    {
        return $this->Db->queryNum('Core', 0, 0, 'SELECT COUNT(*) FROM `neo_tickets_blocks` WHERE `steamid` = ? LIMIT 1', [$steam])[0];
    }

    public function deleteBlock($steam)
    {
        return $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_tickets_blocks` WHERE `steamid` = ?', [$steam]);
    }

    public function checkLKTable($steam)
    {
        return $this->Db->query('lk', 0, 0, "SELECT `auth` FROM `lk` WHERE `auth` = ?", [$steam]);
    }

    public function createdUserLKTable($steam)
    {
        return $this->Db->query('lk', 0, 0, "INSERT INTO `lk` SET `auth` = ?, `cash` = 0, `all_cash` = 0", [$steam]);
    }

    public function updateBalanceUser($summ, $steam)
    {
        return $this->Db->query('lk', 0, 0, "UPDATE `lk` SET `cash` = `cash` + ? WHERE `auth` = ?", [$summ, $steam]);
    }

    public function logBalanceUser($summ, $steam, $log)
    {
        return $this->Db->query('lk', 0, 0, "INSERT INTO `lk_pays` (`pay_order`, `pay_auth`, `pay_summ`, `pay_data`, `pay_system`, `pay_promo`, `pay_status`) VALUES (?, ?, ?, ?, 'admin', ?, 1)", [time() % 100000, $steam, $summ, date('d.m.Y H:i:s'), $log]);
    }

    public function getAdminById($id)
    {
        if (!empty($this->Db->db_data['IksAdminNew'])) {
            return $this->Db->query('IksAdminNew', 0, 0, "SELECT `name`, `steam_id` as `steamid` FROM `iks_admins` WHERE `id` = ?", [$id]);
        } elseif (!empty($this->Db->db_data['AdminSystem'])) {
            return $this->Db->query('AdminSystem', 0, 0, "SELECT `name`, `steamid` FROM `as_admins` WHERE `id` = ?", [$id]);
        }
    }

    public function getActivePunish($steam)
    {
        if (!empty($this->Db->db_data['IksAdminNew'])) {
            $ban = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT `steam_id` as `steamid`, `name`, `duration`, `reason`, `admin_id` FROM `iks_bans` WHERE `steam_id` = ? AND (`end_at` = 0 OR `end_at` > UNIX_TIMESTAMP()) AND `unbanned_by` IS NULL", [$steam]);
            $mute = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT `steam_id` as `steamid`, `name`, `mute_type` as `punish_type`, `duration`, `reason`, `admin_id` FROM `iks_comms` WHERE `steam_id` = ? AND (`end_at` = 0 OR `end_at` > UNIX_TIMESTAMP()) AND `unbanned_by` IS NULL", [$steam]);
            foreach ($mute as &$m) {
                $m['unified_type'] = (int)$m['punish_type'];
            }
            unset($m);
        } elseif (!empty($this->Db->db_data['AdminSystem'])) {
            $ban = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT `steamid`, `name`, CASE WHEN `expires` = 0 THEN 0 ELSE (`expires` - `created`) END as `duration`, `reason`, `admin_id` FROM `as_punishments` WHERE `steamid` = ? AND (`expires` = 0 OR `expires` > UNIX_TIMESTAMP()) AND `unpunish_admin_id` IS NULL AND `punish_type` = 0", [$steam]);
            $mute = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT `steamid`, `name`, `punish_type`, CASE WHEN `expires` = 0 THEN 0 ELSE (`expires` - `created`) END as `duration`, `reason`, `admin_id` FROM `as_punishments` WHERE `steamid` = ? AND (`expires` = 0 OR `expires` > UNIX_TIMESTAMP()) AND `unpunish_admin_id` IS NULL AND (`punish_type` = 1 OR `punish_type` = 2 OR `punish_type` = 3)", [$steam]);
            foreach ($mute as &$m) {
                switch ((int)$m['punish_type']) {
                    case 1:
                        $m['unified_type'] = 0;
                        break;
                    case 2:
                        $m['unified_type'] = 1;
                        break;
                    case 3:
                        $m['unified_type'] = 2;
                        break;
                }
            }
            unset($m);
        }
        return [$ban, $mute];
    }

    public function getExpiredPunish($steam)
    {
        if (!empty($this->Db->db_data['IksAdminNew'])) {
            $ban = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT `steam_id` as `steamid`, `name`, `duration`, `reason`, `admin_id` FROM `iks_bans` WHERE `steam_id` = ? AND ((`end_at` > 0 AND `end_at` <= UNIX_TIMESTAMP()) OR `unbanned_by` IS NOT NULL)", [$steam]);
            $mute = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT `steam_id` as `steamid`, `name`, `mute_type` as `punish_type`, `duration`, `reason`, `admin_id` FROM `iks_comms` WHERE `steam_id` = ? AND ((`end_at` > 0 AND `end_at` <= UNIX_TIMESTAMP()) OR `unbanned_by` IS NOT NULL)", [$steam]);
            foreach ($mute as &$m) {
                $m['unified_type'] = (int)$m['punish_type'];
            }
            unset($m);
        } elseif (!empty($this->Db->db_data['AdminSystem'])) {
            $ban = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT `steamid`, `name`, CASE WHEN `expires` = 0 THEN 0 ELSE (`expires` - `created`) END as `duration`, `reason`, `admin_id` FROM `as_punishments` WHERE `steamid` = ? AND ((`expires` > 0 AND `expires` <= UNIX_TIMESTAMP()) OR `unpunish_admin_id` IS NOT NULL) AND `punish_type` = 0", [$steam]);
            $mute = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT `steamid`, `name`, `punish_type`, CASE WHEN `expires` = 0 THEN 0 ELSE (`expires` - `created`) END as `duration`, `reason`, `admin_id` FROM `as_punishments` WHERE `steamid` = ? AND ((`expires` > 0 AND `expires` <= UNIX_TIMESTAMP()) OR `unpunish_admin_id` IS NOT NULL) AND (`punish_type` = 1 OR `punish_type` = 2 OR `punish_type` = 3)", [$steam]);
            foreach ($mute as &$m) {
                switch ((int)$m['punish_type']) {
                    case 1:
                        $m['unified_type'] = 0;
                        break;
                    case 2:
                        $m['unified_type'] = 1;
                        break;
                    case 3:
                        $m['unified_type'] = 2;
                        break;
                }
            }
            unset($m);
        }
        return [$ban, $mute];
    }

    public function getLkHistory($steam)
    {
        return $this->Db->queryAll('lk', 0, 0, "SELECT `pay_summ`, `pay_data`, `pay_system`, `pay_promo`, `pay_status` FROM `lk_pays` WHERE `pay_auth` = ? ORDER BY `pay_id` DESC", [$steam]);
    }

    public function getStoreHistory($steam)
    {
        return $this->Db->queryAll('Core', 0, 0, "SELECT `steam`, `title`, `server`, `date` FROM `lvl_web_shop_logs` WHERE `steam` = ? ORDER BY `id` DESC", [$steam]);
    }
}
