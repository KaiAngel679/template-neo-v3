<?php

namespace app\modules\module_page_reviews\ext\Repositories;

class DatabaseRepository
{
    private $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function createTables(): void
    {
        $this->Db->query('Core', 0, 0, 'CREATE TABLE IF NOT EXISTS `neo_reviews` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `steamid` BIGINT(17) NOT NULL,
            `server_id` INT NOT NULL DEFAULT -1,
            `server_name` VARCHAR(128) NOT NULL DEFAULT \'\',
            `game` VARCHAR(16) NOT NULL DEFAULT \'\',
            `overall` DECIMAL(3,1) NOT NULL DEFAULT 0,
            `hours` INT UNSIGNED NOT NULL DEFAULT 0,
            `pros` TEXT NOT NULL,
            `cons` TEXT NOT NULL,
            `comment` TEXT NOT NULL,
            `score` INT NOT NULL DEFAULT 0,
            `created_at` INT UNSIGNED NOT NULL,
            `updated_at` INT UNSIGNED NOT NULL,
            KEY `idx_steamid` (`steamid`),
            KEY `idx_server_id` (`server_id`),
            KEY `idx_overall` (`overall`),
            KEY `idx_score` (`score`),
            KEY `idx_created_at` (`created_at`),
            UNIQUE KEY `uq_steam_server` (`steamid`, `server_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

        $this->Db->query('Core', 0, 0, 'CREATE TABLE IF NOT EXISTS `neo_review_attrs` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `review_id` INT UNSIGNED NOT NULL,
            `attr` VARCHAR(32) NOT NULL,
            `value` TINYINT UNSIGNED NOT NULL,
            UNIQUE KEY `uq_review_attr` (`review_id`, `attr`),
            KEY `idx_review_id` (`review_id`),
            KEY `idx_attr` (`attr`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

        $this->Db->query('Core', 0, 0, 'CREATE TABLE IF NOT EXISTS `neo_review_comments` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `review_id` INT UNSIGNED NOT NULL,
            `parent_id` INT UNSIGNED NULL DEFAULT NULL,
            `steamid` BIGINT(17) NOT NULL,
            `text` TEXT NOT NULL,
            `score` INT NOT NULL DEFAULT 0,
            `created_at` INT UNSIGNED NOT NULL,
            KEY `idx_review_id` (`review_id`),
            KEY `idx_parent_id` (`parent_id`),
            KEY `idx_steamid` (`steamid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

        $this->Db->query('Core', 0, 0, 'CREATE TABLE IF NOT EXISTS `neo_review_votes` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `review_id` INT UNSIGNED NOT NULL,
            `steamid` BIGINT(17) NOT NULL,
            `value` TINYINT NOT NULL,
            `created_at` INT UNSIGNED NOT NULL,
            UNIQUE KEY `uq_review_vote` (`review_id`, `steamid`),
            KEY `idx_review_id` (`review_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

        $this->Db->query('Core', 0, 0, 'CREATE TABLE IF NOT EXISTS `neo_review_comment_votes` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `comment_id` INT UNSIGNED NOT NULL,
            `steamid` BIGINT(17) NOT NULL,
            `value` TINYINT NOT NULL,
            `created_at` INT UNSIGNED NOT NULL,
            UNIQUE KEY `uq_comment_vote` (`comment_id`, `steamid`),
            KEY `idx_comment_id` (`comment_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

        $this->Db->query('Core', 0, 0, 'CREATE TABLE IF NOT EXISTS `neo_review_rewards` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `steamid` BIGINT(17) NOT NULL,
            `amount` INT UNSIGNED NOT NULL DEFAULT 0,
            `created_at` INT UNSIGNED NOT NULL,
            UNIQUE KEY `uq_steamid` (`steamid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

        $this->Db->query('Core', 0, 0, 'CREATE TABLE IF NOT EXISTS `neo_review_bans` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `steamid` VARCHAR(32) NOT NULL DEFAULT \'\',
            `ip` VARCHAR(45) NOT NULL DEFAULT \'\',
            `scope` VARCHAR(16) NOT NULL DEFAULT \'all\',
            `reason` VARCHAR(200) NOT NULL DEFAULT \'\',
            `created_at` INT UNSIGNED NOT NULL,
            `created_by` VARCHAR(32) NOT NULL DEFAULT \'\',
            KEY `idx_steamid` (`steamid`),
            KEY `idx_ip` (`ip`),
            KEY `idx_scope` (`scope`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
    }
}
