<?php

if (!isset($_SESSION['user_admin']) || IN_LR != true) {
  header('Location: ' . $General->arr_general['site']);
  exit;
}

$checkTables =  array(
  'lvl_web_help_content',
  'lvl_web_help_category',
  'lvl_web_help_images'
);

$absentTables = [];
foreach ($checkTables as $table) {
  if (!$Db->mysql_table_search('Core', 0, 0, $table)) {
    $absentTables[$table] = 1;
  }
}

$queryies = [];
if (!empty($absentTables) || 1) {
  $queryies = [
    "CREATE TABLE IF NOT EXISTS `lvl_web_help_content` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `category` int(11) NULL DEFAULT NULL,
        `title` text NOT NULL,
        `svg` text NULL DEFAULT NULL,
        `content` longtext NOT NULL,
        `sort` int(11) NOT NULL DEFAULT 0,
        `created` int(11) NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE = InnoDB CHARSET = utf8mb4 COLLATE utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `lvl_web_help_category` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `svg` text NOT NULL,
        `sort` int(11) NOT NULL DEFAULT 0,
        `created` int(11) NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE = InnoDB CHARSET = utf8mb4 COLLATE utf8mb4_general_ci;",

    "CREATE TABLE IF NOT EXISTS `lvl_web_help_images` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `content_id` int(11) NULL DEFAULT NULL,
        `file_name` varchar(255) NOT NULL,
        `created` int(11) NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE = InnoDB CHARSET = utf8mb4 COLLATE utf8mb4_general_ci;"
  ];
}

foreach ($queryies as $query) {
  $Db->query('Core', 0, 0, $query);
}


header("Location: " . $General->arr_general['site']."help");
