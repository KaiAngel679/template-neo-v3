<?php
if ($_POST['stats']) {
    !defined('IN_LR') && define('IN_LR', true);
    !defined('APP') && define('APP', '../../../../app/');
    !defined('STORAGE') && define('STORAGE', '../../../../storage/');
    !defined('CACHE') && define('CACHE', STORAGE . 'cache/');
    !defined('MODULES') && define('MODULES', APP . 'modules/');
    !defined('SESSIONS') && define('SESSIONS', CACHE . 'sessions/');

    session_start();

    require '../../../ext/Db.php';
    $Db = new app\ext\Db;

    function ensure_directory_exists($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }


    function set_module_cache($module, $data, $name)
    {
        $dir = MODULES . $module . '/temp';
        ensure_directory_exists($dir);
        file_put_contents($dir . '/' . $name . '.php', '<?php return ' . var_export($data, true) . ';');
    }

    function get_module_cache($module, $name)
    {
        $file = MODULES . $module . '/temp/' . $name . '.php';
        if (file_exists($file)) {
            return require $file;
        } else {
            $dir = MODULES . $module . '/temp';
            ensure_directory_exists($dir);
            file_put_contents($file, '<?php return [];');
            return [];
        }
    }

    $stats = [
        'CountPlayers' => 0,
        'CountAdmins' => 0,
        'CountBans' => 0,
        'CountMutes' => 0,
        'CountVip' => 0,
        'CountPlayers24' => 0,
        'CountPlayers7d' => 0,
        'Time' => time() + 30,
    ];

    $data = get_module_cache('module_block_main_statistics', 'stats');
    if (time() > ($data['Time'] ?? 0)) {
        for ($i = 0; $i < $Db->table_count['LevelsRanks']; $i++) {
            $stats['CountPlayers'] += $Db->queryNum('LevelsRanks', $Db->db_data['LevelsRanks'][$i]['USER_ID'], $Db->db_data['LevelsRanks'][$i]['DB_num'], 'SELECT COUNT(*) FROM ' . $Db->db_data['LevelsRanks'][$i]['Table'] . ' LIMIT 1')[0];
            $stats['CountPlayers24'] += $Db->queryNum('LevelsRanks', $Db->db_data['LevelsRanks'][$i]['USER_ID'], $Db->db_data['LevelsRanks'][$i]['DB_num'], 'SELECT COUNT(*) FROM ' . $Db->db_data['LevelsRanks'][$i]['Table'] . ' WHERE `lastconnect` >= UNIX_TIMESTAMP(CURDATE()) LIMIT 1')[0];
            $stats['CountPlayers7d'] += $Db->queryNum('LevelsRanks', $Db->db_data['LevelsRanks'][$i]['USER_ID'], $Db->db_data['LevelsRanks'][$i]['DB_num'], 'SELECT COUNT(*) FROM ' . $Db->db_data['LevelsRanks'][$i]['Table'] . ' WHERE `lastconnect` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1')[0];
        }
        if (!empty($Db->db_data['AdminSystem'])) {
            for ($i = 0; $i < $Db->table_count['AdminSystem']; $i++) {
                $countBanMutes = $Db->query(
                    'AdminSystem',
                    $Db->db_data['AdminSystem'][$i]['USER_ID'],
                    $Db->db_data['AdminSystem'][$i]['DB_num'],
                    "SELECT 
                        (SELECT COUNT(*) FROM `as_admins` WHERE `id` != 1 LIMIT 1) AS count_admins, 
                        (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = '1' LIMIT 1) AS count_mutes,
                        (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = '2' LIMIT 1) AS count_gags,
                        (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = '3' LIMIT 1) AS count_silence,
                        (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = '0' LIMIT 1) AS count_bans"
                );
                $stats['CountMutes'] += $countBanMutes['count_mutes'] + $countBanMutes['count_gags'] + $countBanMutes['count_silence'];
                $stats['CountAdmins'] += $countBanMutes['count_admins'];
                $stats['CountBans'] += $countBanMutes['count_bans'];
            }
        }
        if (!empty($Db->db_data['IksAdmin'])) {
            for ($i = 0; $i < $Db->table_count['IksAdmin']; $i++) {
                $countBanMutes = $Db->query(
                    'IksAdmin',
                    $Db->db_data['IksAdmin'][$i]['USER_ID'],
                    $Db->db_data['IksAdmin'][$i]['DB_num'],
                    "SELECT 
                        (SELECT COUNT(*) FROM `iks_admins` LIMIT 1) AS count_admins, 
                        (SELECT COUNT(*) FROM `iks_mutes` LIMIT 1) AS count_mutes,
                        (SELECT COUNT(*) FROM `iks_gags` LIMIT 1) AS count_gags,
                        (SELECT COUNT(*) FROM `iks_bans` LIMIT 1) AS count_bans"
                );
                $stats['CountMutes'] += $countBanMutes['count_mutes'] + $countBanMutes['count_gags'];
                $stats['CountAdmins'] += $countBanMutes['count_admins'];
                $stats['CountBans'] += $countBanMutes['count_bans'];
            }
        }
        if (!empty($Db->db_data['IksAdminNew'])) {
            for ($i = 0; $i < $Db->table_count['IksAdminNew']; $i++) {
                $countBanMutes = $Db->query(
                    'IksAdminNew',
                    $Db->db_data['IksAdminNew'][$i]['USER_ID'],
                    $Db->db_data['IksAdminNew'][$i]['DB_num'],
                    "SELECT 
                        (SELECT COUNT(*) FROM `iks_admins` WHERE `id` != 1 LIMIT 1) AS count_admins, 
                        (SELECT COUNT(*) FROM `iks_comms` WHERE `mute_type` = '0' LIMIT 1) AS count_mutes,
                        (SELECT COUNT(*) FROM `iks_comms` WHERE `mute_type` = '1' LIMIT 1) AS count_gags,
                        (SELECT COUNT(*) FROM `iks_comms` WHERE `mute_type` = '2' LIMIT 1) AS count_silence,
                        (SELECT COUNT(*) FROM `iks_bans` LIMIT 1) AS count_bans"
                );
                $stats['CountMutes'] += $countBanMutes['count_mutes'] + $countBanMutes['count_gags'] + $countBanMutes['count_silence'];
                $stats['CountAdmins'] += $countBanMutes['count_admins'];
                $stats['CountBans'] += $countBanMutes['count_bans'];
            }
        }
        if (!empty($Db->db_data['SourceBans'])) {
            for ($i = 0; $i < $Db->table_count['SourceBans']; $i++) {
                $countBanMutes = $Db->query(
                    'SourceBans',
                    $Db->db_data['SourceBans'][$i]['USER_ID'],
                    $Db->db_data['SourceBans'][$i]['DB_num'],
                    "SELECT 
                            (SELECT COUNT(DISTINCT a.authid) FROM `sb_admins` a JOIN `sb_admins_servers_groups` s ON a.`aid` = s.`admin_id` WHERE (a.`expired` > UNIX_TIMESTAMP() OR a.`expired` = 0) AND a.`aid` != 0 ) AS count_admins,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `type` = '1' LIMIT 1) AS count_mutes,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `type` = '2' LIMIT 1) AS count_gags,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `type` = '3' LIMIT 1) AS count_silence,
                            (SELECT COUNT(*) FROM `sb_bans` LIMIT 1) AS count_bans,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) AND `type` = '1' LIMIT 1) AS count_mutes_7d,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) AND `type` = '2' LIMIT 1) AS count_gags_7d,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) AND `type` = '3' LIMIT 1) AS count_silence_7d,
                            (SELECT COUNT(*) FROM `sb_bans` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1) AS count_bans_7d"
                );
                $stats['CountMutes'] += $countBanMutes['count_mutes'] + $countBanMutes['count_gags'] + $countBanMutes['count_silence'];
                $stats['CountAdmins'] += $countBanMutes['count_admins'];
                $stats['CountBans'] += $countBanMutes['count_bans'];
            }
        }
        if (!empty($Db->db_data['Vips'])) {
            for ($i = 0; $i < $Db->table_count['Vips']; $i++) {
                $stats['CountVip'] += $Db->queryNum('Vips', $Db->db_data['Vips'][$i]['USER_ID'], $Db->db_data['Vips'][$i]['DB_num'], "SELECT COUNT(*) FROM `vip_users` LIMIT 1")[0];
            }
        }
        set_module_cache('module_block_main_statistics', $stats, 'stats');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
} else {
    exit();
}
