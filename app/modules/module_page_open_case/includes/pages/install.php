<?php
if (!isset($_SESSION['user_admin']) || IN_LR != true) {
    header('Location: ' . $General->arr_general['site']);
    exit;
}

$queryies = [
    "DROP TABLE `cases_discord`",
    "CREATE TABLE IF NOT EXISTS `cases` (`id` INT NOT NULL AUTO_INCREMENT, `case_name` VARCHAR(512) NOT NULL, `case_type` INT NOT NULL, `case_sort` INT NOT NULL, `case_price` VARCHAR(256) NOT NULL, `case_img` VARCHAR(512) NOT NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;",
    "CREATE TABLE IF NOT EXISTS `cases_category` ( `id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(512) NOT NULL , `sort` INT NOT NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;",
    "CREATE TABLE IF NOT EXISTS `cases_settings` ( `webhook` VARCHAR(512) NOT NULL , `webhook_offon` INT NOT NULL DEFAULT '0', `speed` VARCHAR(512) NOT NULL, `course` VARCHAR(512) NOT NULL) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;",
    "CREATE TABLE IF NOT EXISTS `cases_live` ( `id` INT NOT NULL AUTO_INCREMENT , `case_id` INT NOT NULL , `case_name` VARCHAR(256) NOT NULL , `subject_name` VARCHAR(256) NOT NULL , `user_name` VARCHAR(256) NOT NULL , `steam_id` VARCHAR(256) NOT NULL , `subject_img` VARCHAR(512) NOT NULL , `case_img` VARCHAR(512) NOT NULL , `live_style` VARCHAR(128) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;",
    "CREATE TABLE IF NOT EXISTS `cases_open` ( `steam_id` VARCHAR(256) NOT NULL , `case_id` INT NOT NULL , `wins` VARCHAR(512) NOT NULL , `date` INT NOT NULL ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;",
    "CREATE TABLE IF NOT EXISTS `cases_subjects` ( `id` INT NOT NULL AUTO_INCREMENT , `server_id` INT NOT NULL , `case_id` INT NOT NULL , `subject_name` VARCHAR(256) NOT NULL , `subject_desc` VARCHAR(256) NOT NULL , `subject_class` VARCHAR(128) NOT NULL , `subject_img` VARCHAR(512) NOT NULL , `subject_type` INT NOT NULL , `subject_content` VARCHAR(512) NOT NULL , `subject_chance` FLOAT NOT NULL , `subject_sale` FLOAT NOT NULL , `subject_sort` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;",
    "CREATE TABLE IF NOT EXISTS `cases_wins` ( `id` INT NOT NULL AUTO_INCREMENT , `subject_id` INT NOT NULL , `subject_name` VARCHAR(256) NOT NULL , `subject_desc` VARCHAR(256) NOT NULL , `subject_style` VARCHAR(128) NOT NULL , `subject_img` VARCHAR(512) NOT NULL , `steam_id` VARCHAR(256) NOT NULL , `sale` FLOAT NOT NULL , `up` INT NOT NULL DEFAULT '0' , `sell` INT NOT NULL DEFAULT '0' , PRIMARY KEY (`id`)) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;",

    "ALTER TABLE `cases` ADD `case_cat` INT NOT NULL AFTER `case_sort`;",

    "INSERT IGNORE INTO `cases_settings`(`webhook`, `webhook_offon`, `speed`, `course`) VALUES ('',0,2,'₽');",
];

try {
    foreach ($queryies as $query) {
        $Db->query('Core', 0, 0, $query);
    }
} catch (\Exception $e) {
    exit;
}

header('Location: ' . $General->arr_general['site'].'cases');
