<?php


namespace app\modules\module_page_punishment\ext;

use app\modules\module_page_punishment\ext\Rcon;

class Punishment extends Rcon
{
    protected $Db, $General, $Translate, $Modules, $Search, $Notifications, $server_id, $game, $hasAdminAccess;

    public function __construct($Db, $General, $Translate, $Modules, $Notifications, $server_id, $game = 'cs2')
    {
        $this->Db = $Db;
        $this->General = $General;
        $this->Translate = $Translate;
        $this->Modules = $Modules;
        $this->Notifications = $Notifications;
        $this->Search = new Search($Db, $General, $Modules, $Translate, $server_id, $game);
        $this->server_id = $server_id;
        $this->game = $game;
        $this->hasAdminAccess = $this->hasAdminAccess();
    }

    public function Search()
    {
        return $this->Search;
    }

    public function GetSettings()
    {
        return $this->Modules->get_settings_modules('module_page_punishment', 'settings');
    }

    public function GetServerLR()
    {
        for ($d = 0; $d < $this->General->server_list_count; $d++) {
            if ($this->General->server_list[$d]['server_game'] != $this->game) continue;
            $r[] = array(
                "name" => $this->General->server_list[$d]['name_custom'],
                "server_sb" => explode(";", $this->General->server_list[$d]['server_sb']),
                "server_sb_id" => $this->General->server_list[$d]['server_sb_id']
            );
        }
        return $r;
    }

    public function Rcons($command)
    {
        $_Server_Info = $this->General->server_list;
        $success = true;
        foreach ($_Server_Info as $server) {
            $_IP = explode(':', $server['ip']);
            $_RCON = new Rcon($_IP[0], $_IP[1]);
            if ($_RCON->Connect()) {
                if (!empty($server['rcon'])) {
                    $_RCON->RconPass($server['rcon']);
                    $_RCON->Command($command);
                } else {
                    $success = false;
                }
                $_RCON->Disconnect();
            } else {
                $success = false;
            }
        }
        if ($success) {
            return "success";
        } else {
            return "error";
        }
    }

    public function GetBalance()
    {
        if (isset($_SESSION['steamid32'])) {
            preg_match('/:[0-9]{1}:\d+/i', $_SESSION['steamid32'], $auth);
            $param = [
                'auth' => '%' . $auth[0] . '%'
            ];
            $infoUser = $this->Db->queryAll('lk', 0, 0, "SELECT cash FROM lk WHERE auth LIKE :auth LIMIT 1", $param);
            $cash = 'cash';
            if (isset($infoUser[0]))
                return $infoUser[0][$cash];
            else
                return 0;
        }
    }

    public function UpdateBalance($steam, $price)
    {
        preg_match('/:[0-9]{1}:\d+/i', $steam, $auth);
        $param = ['auth' => '%' . $auth[0] . '%', 'price' => $price];
        $this->Db->queryAll('lk', 0, 0, "UPDATE lk SET cash = cash - :price WHERE auth LIKE :auth LIMIT 1", $param);
        return true;
    }

    public function GetInfoCount()
    {
        $infoCount = [
            'count_mutes' => 0,
            'count_gags' => 0,
            'count_bans' => 0,
            'count_bans_perm' => 0,
            'count_bans_activ' => 0,
            'my_count_bans' => 0,
            'my_count_mutes' => 0,
        ];
        if ($this->game == 'cs2') {
            if (!empty($this->Db->db_data['AdminSystem'])) {
                for ($i = 0; $i < $this->Db->table_count['AdminSystem']; $i++) {
                    $countBansMutesAdmins = $this->Db->query(
                        'AdminSystem',
                        0,
                        0,
                        "SELECT 
                        (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = 1 LIMIT 1) AS count_mutes,
                        (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = 2 LIMIT 1) AS count_gags,
                        (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = 0 LIMIT 1) AS count_bans,
                        (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = 0 AND `expires` = 0 LIMIT 1) AS count_bans_perm,
                        (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = 0 AND (`expires` = 0 OR `expires` > UNIX_TIMESTAMP()) AND `unpunish_admin_id` IS NULL LIMIT 1) AS count_bans_activ"
                    );
                    $myBanCount = ceil($this->Db->queryNum('AdminSystem', 0, 0, "SELECT COUNT(*) FROM `as_punishments` WHERE `steamid` = :steamid AND `punish_type` = 0 AND (`expires` = 0 OR `expires` > UNIX_TIMESTAMP()) AND `unpunish_admin_id` IS NULL", ['steamid' => $_SESSION['steamid64']])[0]);
                    $myVoiceCount = ceil($this->Db->queryNum('AdminSystem', 0, 0, "SELECT COUNT(*) FROM `as_punishments` WHERE `steamid` = :steamid AND `punish_type` = 1 AND (`expires` = 0 OR `expires` > UNIX_TIMESTAMP()) AND `unpunish_admin_id` IS NULL", ['steamid' => $_SESSION['steamid64']])[0]);
                    $myGagCount = ceil($this->Db->queryNum('AdminSystem', 0, 0, "SELECT COUNT(*) FROM `as_punishments` WHERE `steamid` = :steamid AND `punish_type` = 2 AND (`expires` = 0 OR `expires` > UNIX_TIMESTAMP()) AND `unpunish_admin_id` IS NULL", ['steamid' => $_SESSION['steamid64']])[0]);
                    $myMuteCount = $myVoiceCount + $myGagCount;
                    $infoCount['count_admins'] += $countBansMutesAdmins['count_admins'];
                    $infoCount['count_mutes'] += $countBansMutesAdmins['count_mutes'];
                    $infoCount['count_gags'] += $countBansMutesAdmins['count_gags'];
                    $infoCount['count_bans'] += $countBansMutesAdmins['count_bans'];
                    $infoCount['count_bans_perm'] += $countBansMutesAdmins['count_bans_perm'];
                    $infoCount['count_bans_activ'] += $countBansMutesAdmins['count_bans_activ'];
                    $infoCount['my_count_bans'] += $myBanCount;
                    $infoCount['my_count_mutes'] += $myMuteCount;
                }
            }
            if (!empty($this->Db->db_data['IksAdmin'])) {
                $countBansMutesAdmins = $this->Db->query(
                    'IksAdmin',
                    0,
                    0,
                    "SELECT 
                    (SELECT COUNT(*) FROM `iks_mutes` LIMIT 1) AS count_mutes,
                    (SELECT COUNT(*) FROM `iks_gags` LIMIT 1) AS count_gags,
                    (SELECT COUNT(*) FROM `iks_bans` LIMIT 1) AS count_bans,
                    (SELECT COUNT(*) FROM `iks_bans` WHERE `time` = 0 AND `Unbanned` = 0 LIMIT 1) AS count_bans_perm,
                    (SELECT COUNT(*) FROM `iks_bans` WHERE `time` = 0 OR `end` > UNIX_TIMESTAMP() AND `Unbanned` = 0 LIMIT 1) AS count_bans_activ"
                );
                $myBanCount = ceil($this->Db->queryNum('IksAdmin', 0, 0, "SELECT COUNT(*) FROM `iks_bans` WHERE `sid` = :sid AND (`time` = 0 OR `end` > UNIX_TIMESTAMP()) AND `Unbanned` = 0", ['sid' => $_SESSION['steamid64']])[0]);
                $myVoiceCount = ceil($this->Db->queryNum('IksAdmin', 0, 0, "SELECT COUNT(*) FROM `iks_mutes` WHERE `sid` = :sid AND (`time` = 0 OR `end` > UNIX_TIMESTAMP()) AND `Unbanned` = 0", ['sid' => $_SESSION['steamid64']])[0]);
                $myGagCount = ceil($this->Db->queryNum('IksAdmin', 0, 0, "SELECT COUNT(*) FROM `iks_gags` WHERE `sid` = :sid AND (`time` = 0 OR `end` > UNIX_TIMESTAMP()) AND `Unbanned` = 0", ['sid' => $_SESSION['steamid64']])[0]);
                $myMuteCount = $myVoiceCount + $myGagCount;
                $infoCount['count_admins'] = $countBansMutesAdmins['count_admins'];
                $infoCount['count_mutes'] = $countBansMutesAdmins['count_mutes'];
                $infoCount['count_gags'] = $countBansMutesAdmins['count_gags'];
                $infoCount['count_bans'] = $countBansMutesAdmins['count_bans'];
                $infoCount['count_bans_perm'] = $countBansMutesAdmins['count_bans_perm'];
                $infoCount['count_bans_activ'] = $countBansMutesAdmins['count_bans_activ'];
                $infoCount['my_count_bans'] = $myBanCount;
                $infoCount['my_count_mutes'] = $myMuteCount;
            }
            if (!empty($this->Db->db_data['IksAdminNew'])) {
                $countBansMutesAdmins = $this->Db->query(
                    'IksAdminNew',
                    0,
                    0,
                    "SELECT 
                    (SELECT COUNT(*) FROM `iks_bans` LIMIT 1) AS count_bans,
                    (SELECT COUNT(*) FROM `iks_comms` WHERE `mute_type` = 0 LIMIT 1) AS count_mutes,
                    (SELECT COUNT(*) FROM `iks_comms` WHERE (`mute_type` = 1 OR `mute_type` = 2) LIMIT 1) AS count_gags,
                    (SELECT COUNT(*) FROM `iks_bans` WHERE `end_at` = 0 LIMIT 1) AS count_bans_perm,
                    (SELECT COUNT(*) FROM `iks_bans` WHERE (`end_at` = 0 OR `end_at` > UNIX_TIMESTAMP()) AND `unbanned_by` IS NULL LIMIT 1) AS count_bans_activ"
                );
                $myBanCount = ceil($this->Db->queryNum('IksAdminNew', 0, 0, "SELECT COUNT(*) FROM `iks_bans` WHERE `steam_id` = :steam_id AND (`end_at` = 0 OR `end_at` > UNIX_TIMESTAMP()) AND `unbanned_by` IS NULL", ['steam_id' => $_SESSION['steamid64']])[0]);
                $myVoiceCount = ceil($this->Db->queryNum('IksAdminNew', 0, 0, "SELECT COUNT(*) FROM `iks_mutes` WHERE `steam_id` = :steam_id AND (`end_at` = 0 OR `end_at` > UNIX_TIMESTAMP()) AND `unbanned_by` IS NULL", ['steam_id' => $_SESSION['steamid64']])[0]);
                $myGagCount = ceil($this->Db->queryNum('IksAdminNew', 0, 0, "SELECT COUNT(*) FROM `iks_gags` WHERE `steam_id` = :steam_id AND (`end_at` = 0 OR `end_at` > UNIX_TIMESTAMP()) AND `unbanned_by` IS NULL", ['steam_id' => $_SESSION['steamid64']])[0]);
                $myMuteCount = $myVoiceCount + $myGagCount;
                $infoCount['count_admins'] = $countBansMutesAdmins['count_admins'];
                $infoCount['count_mutes'] = $countBansMutesAdmins['count_mutes'];
                $infoCount['count_gags'] = $countBansMutesAdmins['count_gags'];
                $infoCount['count_bans'] = $countBansMutesAdmins['count_bans'];
                $infoCount['count_bans_perm'] = $countBansMutesAdmins['count_bans_perm'];
                $infoCount['count_bans_activ'] = $countBansMutesAdmins['count_bans_activ'];
                $infoCount['my_count_bans'] = $myBanCount;
                $infoCount['my_count_mutes'] = $myMuteCount;
            }
        } elseif ($this->game == 'csgo') {
            if (!empty($this->Db->db_data['SourceBans'])) {
                for ($i = 0; $i < $this->Db->table_count['SourceBans']; $i++) {
                    $countBansMutesAdmins = $this->Db->query(
                        'SourceBans',
                        0,
                        0,
                        "SELECT 
                        (SELECT COUNT(*) FROM `sb_comms` WHERE `type` IN (1,3) LIMIT 1) AS count_mutes,
                        (SELECT COUNT(*) FROM `sb_comms` WHERE `type` IN (2,3) LIMIT 1) AS count_gags,
                        (SELECT COUNT(*) FROM `sb_bans` LIMIT 1) AS count_bans,
                        (SELECT COUNT(*) FROM `sb_bans` WHERE `length` = 0 LIMIT 1) AS count_bans_perm,
                        (SELECT COUNT(*) FROM `sb_bans` WHERE (`length` = 0 OR `ends` > UNIX_TIMESTAMP()) AND `RemovedBy` IS NULL LIMIT 1) AS count_bans_activ"
                    );
                    $myBanCount = ceil($this->Db->queryNum('SourceBans', 0, 0, "SELECT COUNT(*) FROM `sb_bans` WHERE SUBSTRING(authid, 9) = :steamid AND (`length` = 0 OR `ends` > UNIX_TIMESTAMP()) AND `RemovedBy` IS NULL", ['steamid' => $_SESSION['steamid32_short']])[0]);
                    $myVoiceCount = ceil($this->Db->queryNum('SourceBans', 0, 0, "SELECT COUNT(*) FROM `sb_comms` WHERE SUBSTRING(authid, 9) = :steamid AND `type` IN (1,3) AND (`length` = 0 OR `ends` > UNIX_TIMESTAMP()) AND `RemovedBy` IS NULL", ['steamid' => $_SESSION['steamid32_short']])[0]);
                    $myGagCount = ceil($this->Db->queryNum('SourceBans', 0, 0, "SELECT COUNT(*) FROM `sb_comms` WHERE SUBSTRING(authid, 9) = :steamid AND `type` IN (2,3) AND (`length` = 0 OR `ends` > UNIX_TIMESTAMP()) AND `RemovedBy` IS NULL", ['steamid' => $_SESSION['steamid32_short']])[0]);
                    $myMuteCount = $myVoiceCount + $myGagCount;
                    $infoCount['count_admins'] += $countBansMutesAdmins['count_admins'];
                    $infoCount['count_mutes'] += $countBansMutesAdmins['count_mutes'];
                    $infoCount['count_gags'] += $countBansMutesAdmins['count_gags'];
                    $infoCount['count_bans'] += $countBansMutesAdmins['count_bans'];
                    $infoCount['count_bans_perm'] += $countBansMutesAdmins['count_bans_perm'];
                    $infoCount['count_bans_activ'] += $countBansMutesAdmins['count_bans_activ'];
                    $infoCount['my_count_bans'] += $myBanCount;
                    $infoCount['my_count_mutes'] += $myMuteCount;
                }
            }
        }
        return $infoCount;
    }

    public function getAdminRaiting($steam)
    {
        return $this->Db->query('Core', 0, 0, "SELECT * FROM `lvl_web_admins_rating` WHERE `steamid` = :steamid", ['steamid' => $steam]);
    }

    public function RenderingPageList($type, $page)
    {
        $type = $type ?? 'bans';
        $limit = 20;
        $page_min = ($page - 1) * $limit;
        is_numeric($page) ? intval($page) : $page = 1;
        if ($this->game == 'cs2') {
            if (!empty($this->Db->db_data['AdminSystem'])) {
                if ($type == 'bans') {
                    if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
                        $count_list = $this->Db->queryNum('AdminSystem', 0, 0, "SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = 0 AND `server_id` = :server_id ORDER BY `created` DESC", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']])[0];
                        $list = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT *, (SELECT `name` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steamid` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_steamid`  FROM `as_punishments` WHERE `punish_type` = 0 AND `server_id` = :server_id ORDER BY `created` DESC LIMIT " . $page_min . ", " . $limit . "", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']]);
                    } else {
                        $count_list = $this->Db->queryNum('AdminSystem', 0, 0, "SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = 0 ORDER BY `created` DESC")[0];
                        $list = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT *, (SELECT `name` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steamid` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_steamid`  FROM `as_punishments` WHERE `punish_type` = 0 ORDER BY `created` DESC LIMIT " . $page_min . ", " . $limit . "");
                    }
                    $JSONSear = <<<HTML
                    <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchPlayers')}" id="search_ban">
                HTML;
                    foreach ($list as $key => $row) {
                        $idban = $row['id'];
                        $steam_player = $row['steamid'];
                        $steam_admin = $row['admin_steamid'];
                        $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam_player));
                        if (!empty($steam_admin)) {
                            $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['admin_name']) : action_text_clear($this->General->checkName($steam_admin));
                        } else {
                            $name_admin = $this->Translate->get_translate_module_phrase('module_page_punishment', '_console');
                        }

                        if (!empty($row['unpunish_admin_id'])) {
                            $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                            $style_ban = 'remove_punish';
                        } elseif ($row['expires'] == '0') {
                            $end_ban = $this->Translate->get_translate_phrase('_Forever');
                            $style_ban = 'permanent_punish';
                        } elseif (time() > $row['expires']) {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['expires'] - $row['created']);
                            $style_ban = 'expired_punish';
                        } else {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['expires'] - time());
                            $style_ban = 'current_punish';
                        }
                        $reason_ban = action_text_clear($row['reason']);
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <li class="modal_open" page="{$type}" id="{$idban}">
                            <span>
                                <svg><use href="/resources/img/sprite.svg#user-block"></use></svg>
                            </span>
                            <span class="none_span">
                                <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                            </span>
                            <span id="name" nameid="{$steam_player}">{$name_player}</span>
                            <span>{$reason_ban}</span>
                            <span class="{$style_ban} none_span">{$end_ban}</span>
                            <span class="none_span">{$name_admin}</span>
                        </li>
                    HTML;
                        $JSONResult[$key]["sid"] = $row['steamid'];
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($row['steamid']);
                    }
                } elseif ($type == 'comms') {
                    if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
                        $count_list = $this->Db->queryNum('AdminSystem', 0, 0, "SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` != 0 AND `server_id` = :server_id ORDER BY `created` DESC", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']])[0];
                        $list = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT *, (SELECT `name` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steamid` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_steamid` FROM `as_punishments` WHERE `punish_type` != 0 AND `server_id` = :server_id ORDER BY `created` DESC LIMIT " . $page_min . ", " . $limit . "", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']]);
                    } else {
                        $count_list = $this->Db->queryNum('AdminSystem', 0, 0, "SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` != 0 ORDER BY `created` DESC")[0];
                        $list = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT *, (SELECT `name` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steamid` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_steamid` FROM `as_punishments` WHERE `punish_type` != 0 ORDER BY `created` DESC LIMIT " . $page_min . ", " . $limit . "");
                    }
                    $JSONSear = <<<HTML
                    <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchPlayers')}" id="search_mute">
                HTML;
                    foreach ($list as $key => $row) {
                        $idban = $row['id'];
                        $steam_player = $row['steamid'];
                        $steam_admin = $row['admin_steamid'];
                        $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam_player));
                        if (!empty($steam_admin)) {
                            $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['admin_name']) : action_text_clear($this->General->checkName($steam_admin));
                        } else {
                            $name_admin = $this->Translate->get_translate_module_phrase('module_page_punishment', '_console');
                        }

                        if (!empty($row['unpunish_admin_id'])) {
                            $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unmuted');
                            $style_ban = 'remove_punish';
                        } elseif ($row['expires'] == '0') {
                            $end_ban = $this->Translate->get_translate_phrase('_Forever');
                            $style_ban = 'permanent_punish';
                        } elseif (time() > $row['expires']) {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['expires'] - $row['created']);
                            $style_ban = 'expired_punish';
                        } else {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['expires'] - time());
                            $style_ban = 'current_punish';
                        }
                        $reason_ban = action_text_clear($row['reason']);
                        if ($row['punish_type'] == 3) {
                            $punishmentType = '<svg><use href="/resources/img/sprite.svg#face-mute"></use></svg>';
                        } elseif ($row['punish_type'] == 2) {
                            $punishmentType = '<svg><use href="/resources/img/sprite.svg#chat-slash"></use></svg>';
                        } elseif ($row['punish_type'] == 1) {
                            $punishmentType = '<svg><use href="/resources/img/sprite.svg#micro-slash"></use></svg>';
                        }
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <li class="modal_open" page="{$type}" id="{$idban}">
                            <span>
                                {$punishmentType}
                            </span>
                            <span class="none_span">
                                <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                            </span>
                            <span id="name" nameid="{$steam_player}">{$name_player}</span>
                            <span>{$reason_ban}</span>
                            <span class="{$style_ban} none_span">{$end_ban}</span>
                            <span class="none_span">{$name_admin}</span>
                        </li>
                    HTML;
                        $JSONResult[$key]["sid"] = $row['steamid'];
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($row['steamid']);
                    }
                } elseif ($type == 'admins') {
                    if ($this->server_id != 'all') {
                        $array = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT 
                        `as_admins`.`id` AS `admin_id`,
                        `as_admins`.`name`,
                        `as_admins`.`steamid`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`expires`) AS `end`,
                        `as_groups`.`name` as `group`,
                        `as_groups`.`immunity` as `immunity`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`server_id`) AS `server_id`
                    FROM 
                        `as_admins`
                    JOIN 
                        `as_admins_servers` 
                    ON 
                        `as_admins`.`id` = `as_admins_servers`.`admin_id`
                    LEFT JOIN
                        `as_groups` 
                    ON 
                        `as_admins_servers`.`group_id` = `as_groups`.`id`
                    WHERE 
                        (`as_admins_servers`.`server_id` = :server_id OR `as_admins_servers`.`server_id` = '-1')
                        AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
                    GROUP BY
                        `as_admins`.`id`, `as_admins_servers`.`group_id`
                        ORDER BY `as_groups`.`immunity` DESC", ['server_id' => $this->server_id]);
                        $count_list = count($array);
                        $list = array_slice($array, $page_min, $limit);
                    } else {
                        $array = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT 
                        `as_admins`.`id` AS `admin_id`,
                        `as_admins`.`name`,
                        `as_admins`.`steamid`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`expires`) AS `end`,
                        `as_groups`.`name` as `group`,
                        `as_groups`.`immunity` as `immunity`,
                        GROUP_CONCAT(DISTINCT `as_admins_servers`.`server_id`) AS `server_id`
                    FROM 
                        `as_admins`
                    JOIN 
                        `as_admins_servers` 
                    ON 
                        `as_admins`.`id` = `as_admins_servers`.`admin_id`
                    LEFT JOIN
                        `as_groups` 
                    ON 
                        `as_admins_servers`.`group_id` = `as_groups`.`id`
                    WHERE 
                        (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
                    GROUP BY
                        `as_admins`.`id`, `as_admins_servers`.`group_id`
                        ORDER BY `as_groups`.`immunity` DESC");
                        $count_list = count($array);
                        $list = array_slice($array, $page_min, $limit);
                    }
                    $JSONSear = <<<HTML
                    <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchAdmin')}" id="search_admin">
                HTML;
                    foreach ($list as $key => $row) {
                        $id = $row['admin_id'];
                        $steam = $row['steamid'];
                        $name = empty($this->General->checkName($steam)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam));
                        $group = $row['group'];
                        $online = ($this->General->checkOnline($steam)) ? 'online' : '';
                        $background_html = $this->General->getBackground($steam);
                        $raiting = $this->getAdminRaiting($steam);
                        $likes = $raiting['likes'] ?? 0;
                        $dislikes = $raiting['dislikes'] ?? 0;
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <div class="punishmen-admins__card">
                            <div class="punishmen-admins__card-header">
                                <div id="background" backgroundid="{$steam}">{$background_html}</div>
                                <div class="punishmen-admins__rating">
                                    <div class="punishmen-admins__rating-like" data-type="like" data-steam="{$steam}" id="adminRating">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#like"></use>
                                        </svg> {$likes}
                                    </div>
                                    <div class="punishmen-admins__rating-dislike" data-type="dislike" data-steam="{$steam}" id="adminRating">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#like"></use>
                                        </svg> {$dislikes}
                                    </div>
                                </div>
                                <div class="punishmen-admins__group">{$group}</div>
                            </div>
                            <div class="punishmen-admins__avatar">
                                <span class="punishmen-admins__status {$online}"></span>
                                <img src="{$this->General->getAvatar($steam, 3)}" id="avatar" avatarid="{$steam}" alt="">
                            </div>
                            <div class="punishmen-admins__steamid copy-btn" data-clipboard-text="{$steam}">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy"></use>
                                </svg>
                                {$steam}
                            </div>
                            <a href="/profiles/{$steam}/?search=1" target="_blank" class="punishmen-admins__nickname"  id="name" nameid="{$steam}">{$name}</a>
                            <div class="punishmen-admins__button">
                                <button class="width-100 modal_open" page="{$type}" id="{$id}">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_viewInfo')}</button>
                            </div>
                        </div>
                    HTML;
                        $JSONResult[$key]["sid"] = $row['steamid'];
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($row['steamid']);
                    }
                }
            }
            if (!empty($this->Db->db_data['IksAdmin'])) {
                if ($type == 'bans') {
                    if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
                        $count_list = $this->Db->queryNum('IksAdmin', 0, 0, "SELECT COUNT(*) FROM `iks_bans` WHERE `server_id` = :server_id ORDER BY `created` DESC", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']])[0];
                        $list = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT * FROM `iks_bans` WHERE `server_id` = :server_id ORDER BY `created` DESC LIMIT " . $page_min . ", " . $limit . "", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']]);
                    } else {
                        $count_list = $this->Db->queryNum('IksAdmin', 0, 0, "SELECT COUNT(*) FROM `iks_bans` ORDER BY `created` DESC")[0];
                        $list = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT * FROM `iks_bans` ORDER BY `created` DESC LIMIT " . $page_min . ", " . $limit . "");
                    }
                    $JSONSear = <<<HTML
                    <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchPlayers')}" id="search_ban">
                HTML;
                    foreach ($list as $key => $row) {
                        $idban = $row['id'];
                        $steam_player = $row['sid'];
                        $steam_admin = $row['adminsid'];
                        $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam_player));
                        $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['adminName']) : action_text_clear($this->General->checkName($steam_admin));
                        if ($row['Unbanned'] == 1) {
                            $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                            $style_ban = 'remove_punish';
                        } elseif ($row['time'] == 0) {
                            $end_ban = $this->Translate->get_translate_phrase('_Forever');
                            $style_ban = 'permanent_punish';
                        } elseif (time() > $row['created'] + $row['time']) {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['time']);
                            $style_ban = 'expired_punish';
                        } else {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['end'] - time());
                            $style_ban = 'current_punish';
                        }
                        $reason_ban = action_text_clear($row['reason']);
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <li class="modal_open" page="{$type}" id="{$idban}">
                            <span>
                                <svg><use href="/resources/img/sprite.svg#user-block"></use></svg>
                            </span>
                            <span class="none_span">
                                <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                            </span>
                            <span>{$name_player}</span>
                            <span>{$reason_ban}</span>
                            <span class="{$style_ban} none_span">{$end_ban}</span>
                            <span class="none_span">{$name_admin}</span>
                        </li>
                    HTML;
                        $JSONResult[$key]["sid"] = $steam_player;
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($steam_player);
                    }
                } elseif ($type == 'comms') {
                    if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
                        $voice = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT * FROM `iks_mutes` WHERE `server_id` = '{$this->GetServerLR()[$this->server_id]['server_sb_id']}' ORDER BY `created` DESC");
                        $gags = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT * FROM `iks_gags` WHERE `server_id` = '{$this->GetServerLR()[$this->server_id]['server_sb_id']}' ORDER BY `created` DESC");
                    } else {
                        $voice = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT * FROM `iks_mutes` ORDER BY `created` DESC");
                        $gags = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT * FROM `iks_gags` ORDER BY `created` DESC");
                    }
                    $listarray = [];
                    foreach (array_merge($voice, $gags) as $item) {
                        $steamid = $item['sid'];
                        $created = $item['created'];
                        $key = $steamid . '_' . $created;

                        if (!isset($listarray[$key])) {
                            $listarray[$key] = $item;
                        }
                    }
                    usort($listarray, function ($a, $b) {
                        return $b['created'] - $a['created'];
                    });
                    $list = array_values(array_slice($listarray, $page_min, $limit));
                    $count_list = count($listarray);
                    $JSONSear = <<<HTML
                    <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchPlayers')}" id="search_mute">
                HTML;
                    foreach ($list as $key => $row) {
                        $idban = $row['id'];
                        $steam_player = $row['sid'];
                        $steam_admin = $row['adminsid'];
                        $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam_player));
                        $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['adminName']) : action_text_clear($this->General->checkName($steam_admin));
                        if ($row['Unbanned'] == 1) {
                            $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unmuted');
                            $style_ban = 'remove_punish';
                        } elseif ($row['time'] == 0) {
                            $end_ban = $this->Translate->get_translate_phrase('_Forever');
                            $style_ban = 'permanent_punish';
                        } elseif (time() > $row['created'] + $row['time']) {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['time']);
                            $style_ban = 'expired_punish';
                        } else {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['end'] - time());
                            $style_ban = 'current_punish';
                        }
                        $reason_ban = action_text_clear($row['reason']);
                        $MuteType = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT COUNT(*) as count FROM `iks_mutes` WHERE `sid` = " . $row['sid'] . " AND `created` = " . $row['created'] . ";");
                        $ChatType = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT COUNT(*) as count FROM `iks_gags` WHERE `sid` = " . $row['sid'] . " AND `created` = " . $row['created'] . ";");
                        $MuteCount = $MuteType[0]['count'];
                        $ChatCount = $ChatType[0]['count'];
                        if ($MuteCount > 0 && $ChatCount > 0) {
                            $punishmentType = '<svg><use href="/resources/img/sprite.svg#face-mute"></use></svg>';
                        } elseif ($MuteCount > 0) {
                            $punishmentType = '<svg><use href="/resources/img/sprite.svg#micro-slash"></use></svg>';
                        } elseif ($ChatCount > 0) {
                            $punishmentType = '<svg><use href="/resources/img/sprite.svg#chat-slash"></use></svg>';
                        }
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <li class="modal_open" page="{$type}" id="{$idban}">
                            <span>
                                {$punishmentType}
                            </span>
                            <span class="none_span">
                                <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                            </span>
                            <span>{$name_player}</span>
                            <span>{$reason_ban}</span>
                            <span class="{$style_ban} none_span">{$end_ban}</span>
                            <span class="none_span">{$name_admin}</span>
                        </li>
                    HTML;
                        $JSONResult[$key]["sid"] = $steam_player;
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($steam_player);
                    }
                } elseif ($type == 'admins') {
                    if ($this->server_id != 'all') {
                        $count_list = count($this->Db->queryAll('IksAdmin', 0, 0, "SELECT 
                            `iks_admins`.`id`
                        FROM 
                            `iks_admins`
                        WHERE
                            (
                                FIND_IN_SET(:server_id, REPLACE(`iks_admins`.`server_id`, ';', ',')) > 0 
                                OR `iks_admins`.`server_id` = ''
                            )
                        AND (`iks_admins`.`end` > UNIX_TIMESTAMP() OR `iks_admins`.`end` = 0)
                        GROUP BY 
                            `iks_admins`.`id`
                    ", ['server_id' => $this->server_id]));

                        $list = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT 
                            `iks_admins`.`id`,
                            `iks_admins`.`name`,
                            `iks_admins`.`sid` AS `steamid`,
                            `iks_admins`.`flags`,
                            `iks_groups`.`name` AS `group`,
                            CASE 
                                WHEN `iks_admins`.`group_id` IS NULL THEN NULL
                                ELSE COALESCE(`iks_groups`.`immunity`, `iks_admins`.`immunity`)
                            END AS `immunity`,
                            `iks_admins`.`end`,
                            `iks_admins`.`group_id`,
                            `iks_admins`.`server_id`
                        FROM 
                            `iks_admins`
                        LEFT JOIN
                            `iks_groups`
                        ON
                            `iks_admins`.`group_id` = `iks_groups`.`id`
                        WHERE
                            (
                                FIND_IN_SET(:server_id, REPLACE(`iks_admins`.`server_id`, ';', ',')) > 0 
                                OR `iks_admins`.`server_id` = ''
                            )
                            AND (`iks_admins`.`end` > UNIX_TIMESTAMP() OR `iks_admins`.`end` = 0)
                        GROUP BY 
                            `iks_admins`.`id`
                        ORDER BY 
                            `immunity` DESC
                        LIMIT " . $page_min . ", " . $limit, ['server_id' => $this->server_id]);
                    } else {
                        $count_list = count($this->Db->queryAll('IksAdmin', 0, 0, "SELECT 
                            `iks_admins`.`id`
                        FROM 
                            `iks_admins`
                        WHERE 
                            (`iks_admins`.`end` > UNIX_TIMESTAMP() OR `iks_admins`.`end` = 0)
                        GROUP BY 
                            `iks_admins`.`id`"));
                        $list = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT 
                            `iks_admins`.`id`,
                            `iks_admins`.`name`,
                            `iks_admins`.`sid` AS `steamid`,
                            `iks_admins`.`flags`,
                            `iks_groups`.`name` AS `group`,
                            CASE 
                                WHEN `iks_admins`.`group_id` IS NULL THEN NULL
                                ELSE COALESCE(`iks_groups`.`immunity`, `iks_admins`.`immunity`)
                            END AS `immunity`,
                            `iks_admins`.`end`,
                            `iks_admins`.`group_id`,
                            `iks_admins`.`server_id`
                        FROM 
                            `iks_admins`
                        
                        LEFT JOIN
                            `iks_groups`
                        ON
                            `iks_admins`.`group_id` = `iks_groups`.`id`
                        WHERE 
                            (`iks_admins`.`end` > UNIX_TIMESTAMP() OR `iks_admins`.`end` = 0)
                        GROUP BY 
                            `iks_admins`.`id`
                        ORDER BY 
                            `immunity` DESC
                        LIMIT " . $page_min . ", " . $limit);
                    }
                    $JSONSear = <<<HTML
                    <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchAdmin')}" id="search_admin">
                HTML;
                    foreach ($list as $key => $row) {
                        $id = $row['id'];
                        $steam = $row['steamid'];
                        $name = empty($this->General->checkName($steam)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam));
                        $group = $row['group'];
                        $online = ($this->General->checkOnline($steam)) ? 'online' : '';
                        $background_html = $this->General->getBackground($steam);
                        $raiting = $this->getAdminRaiting($steam);
                        $likes = $raiting['likes'] ?? 0;
                        $dislikes = $raiting['dislikes'] ?? 0;
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <div class="punishmen-admins__card">
                            <div class="punishmen-admins__card-header">
                                <div id="background" backgroundid="{$steam}">{$background_html}</div>
                                <div class="punishmen-admins__rating">
                                    <div class="punishmen-admins__rating-like" data-type="like"  data-steam="{$steam}" id="adminRating">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#like"></use>
                                        </svg>  {$likes}
                                    </div>
                                    <div class="punishmen-admins__rating-dislike" data-type="dislike"  data-steam="{$steam}" id="adminRating">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#like"></use>
                                        </svg>  {$dislikes}
                                    </div>
                                </div>
                                <div class="punishmen-admins__group">{$group}</div>
                            </div>
                            <div class="punishmen-admins__avatar">
                                <span class="punishmen-admins__status {$online}"></span>
                                <img src="{$this->General->getAvatar($steam, 3)}" id="avatar" avatarid="{$steam}" alt="">
                            </div>
                            <div class="punishmen-admins__steamid copy-btn" data-clipboard-text="{$steam}">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy"></use>
                                </svg>
                                {$steam}
                            </div>
                            <a href="/profiles/{$steam}/?search=1" target="_blank" class="punishmen-admins__nickname"  id="name" nameid="{$steam}">{$name}</a>
                            <div class="punishmen-admins__button">
                                <button class="width-100 modal_open" page="{$type}" id="{$id}">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_viewInfo')}</button>
                            </div>
                        </div>
                    HTML;
                        $JSONResult[$key]["sid"] = $row['steamid'];
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($row['steamid']);
                    }
                }
            }
            if (!empty($this->Db->db_data['IksAdminNew'])) {
                if ($type == 'bans') {
                    if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
                        $count_list = $this->Db->queryNum('IksAdminNew', 0, 0, "SELECT COUNT(*) FROM `iks_bans` WHERE `server_id` = :server_id", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']])[0];
                        $list = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT *, (SELECT `name` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steam_id` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_steamid` FROM `iks_bans` WHERE (`server_id` = :server_id OR `server_id` IS NULL) ORDER BY `created_at` DESC LIMIT " . $page_min . ", " . $limit . "", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']]);
                    } else {
                        $count_list = $this->Db->queryNum('IksAdminNew', 0, 0, "SELECT COUNT(*) FROM `iks_bans`")[0];
                        $list = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT *, (SELECT `name` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steam_id` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_steamid` FROM `iks_bans` ORDER BY `created_at` DESC LIMIT " . $page_min . ", " . $limit . "");
                    }
                    $JSONSear = <<<HTML
                    <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchPlayers')}" id="search_ban">
                HTML;
                    foreach ($list as $key => $row) {
                        $idban = $row['id'];
                        $steam_player = $row['steam_id'];
                        $steam_admin = $row['admin_steamid'];
                        $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam_player));
                        $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['admin_name']) : action_text_clear($this->General->checkName($steam_admin));
                        if (!empty($row['unbanned_by'])) {
                            $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                            $style_ban = 'remove_punish';
                        } elseif ($row['end_at'] == '0') {
                            $end_ban = $this->Translate->get_translate_phrase('_Forever');
                            $style_ban = 'permanent_punish';
                        } elseif (time() > $row['end_at']) {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['end_at'] - $row['created_at']);
                            $style_ban = 'expired_punish';
                        } else {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['end_at'] - time());
                            $style_ban = 'current_punish';
                        }
                        $reason_ban = action_text_clear($row['reason']);
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <li class="modal_open" page="{$type}" id="{$idban}">
                            <span>
                                <svg><use href="/resources/img/sprite.svg#user-block"></use></svg>
                            </span>
                            <span class="none_span">
                                <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                            </span>
                            <span id="name" nameid="{$steam_player}">{$name_player}</span>
                            <span>{$reason_ban}</span>
                            <span class="{$style_ban} none_span">{$end_ban}</span>
                            <span class="none_span">{$name_admin}</span>
                        </li>
                    HTML;
                        $JSONResult[$key]["sid"] = $steam_player;
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($steam_player);
                    }
                } elseif ($type == 'comms') {
                    if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
                        $count_list = $this->Db->queryNum('IksAdminNew', 0, 0, "SELECT COUNT(*) FROM `iks_comms` WHERE `server_id` = :server_id", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']])[0];
                        $list = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT *, (SELECT `name` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steam_id` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_steamid` FROM `iks_comms` WHERE `server_id` = :server_id ORDER BY `created_at` DESC LIMIT " . $page_min . ", " . $limit . "", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']]);
                    } else {
                        $count_list = $this->Db->queryNum('IksAdminNew', 0, 0, "SELECT COUNT(*) FROM `iks_comms`")[0];
                        $list = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT *, (SELECT `name` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steam_id` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_steamid` FROM `iks_comms`ORDER BY `created_at` DESC LIMIT " . $page_min . ", " . $limit . "");
                    }
                    $JSONSear = <<<HTML
                    <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchPlayers')}" id="search_mute">
                HTML;
                    foreach ($list as $key => $row) {
                        $idban = $row['id'];
                        $steam_player = $row['steam_id'];
                        $steam_admin = $row['admin_steamid'];
                        $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam_player));
                        $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['admin_name']) : action_text_clear($this->General->checkName($steam_admin));

                        if (!empty($row['unbanned_by'])) {
                            $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unmuted');
                            $style_ban = 'remove_punish';
                        } elseif ($row['end_at'] == '0') {
                            $end_ban = $this->Translate->get_translate_phrase('_Forever');
                            $style_ban = 'permanent_punish';
                        } elseif (time() > $row['end_at']) {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['end_at'] - $row['created_at']);
                            $style_ban = 'expired_punish';
                        } else {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['end_at'] - time());
                            $style_ban = 'current_punish';
                        }
                        $reason_ban = action_text_clear($row['reason']);
                        if ($row['mute_type'] == 2) {
                            $punishmentType = '<svg x="0" y="0" viewBox="0 0 24 24" xml:space="preserve"><g><path d="M8.5 11a1.5 1.5 0 1 1 .001-3.001A1.5 1.5 0 0 1 8.5 11zm7-3a1.5 1.5 0 1 0 .001 3.001A1.5 1.5 0 0 0 15.5 8zM12 0C5.383 0 0 5.383 0 12s5.383 12 12 12c2.388 0 4.61-.709 6.482-1.917-.104-.085-.215-.16-.311-.256-.3-.3-.58-.698-.863-1.367A9.927 9.927 0 0 1 12 22C6.486 22 2 17.514 2 12S6.486 2 12 2s10 4.486 10 10c0 .995-.151 1.955-.423 2.863.817.361 1.37.624 1.799.928A11.93 11.93 0 0 0 24 11.999C24 5.383 18.617 0 12 0zM6 18h2v-4H6zm5-4H9v4h2zm1 4h2v-4h-2zm3 0h2v-4h-2zm3.75-2a.75.75 0 0 0-.75.75c0 .088.609 2.674 1.587 3.665.387.392.902.585 1.414.585s1.024-.195 1.414-.585c.78-.78.78-2.048 0-2.828C21.599 16.876 19.327 16 18.75 16z"></path></g></svg>';
                        } elseif ($row['mute_type'] == 1) {
                            $punishmentType = '<svg x="0" y="0" viewBox="0 0 100 100" xml:space="preserve" fill-rule="evenodd"><g><path d="m19.665 18.164 56.25 56.25a3.126 3.126 0 0 0 4.42 0 3.127 3.127 0 0 0 0-4.419l-56.25-56.25a3.126 3.126 0 0 0-4.42 0 3.127 3.127 0 0 0 0 4.419zM52.635 87.5v-6.395a33.153 33.153 0 0 0 18.212-7.596l-4.441-4.44A26.932 26.932 0 0 1 49.51 75c-14.931 0-27.053-12.122-27.053-27.053a3.126 3.126 0 0 0-6.25 0c0 17.327 13.261 31.582 30.178 33.158V87.5H35.447a3.126 3.126 0 0 0 0 6.25h28.125a3.126 3.126 0 0 0 0-6.25zm21.4-28.127 4.645 4.645a33.13 33.13 0 0 0 4.133-16.071 3.126 3.126 0 0 0-6.25 0 26.94 26.94 0 0 1-2.528 11.426z"></path><path d="M28.117 30.78v18.64c0 12.073 9.802 21.875 21.875 21.875a21.785 21.785 0 0 0 13.764-4.877zm3.23-14.094 39.464 39.463a21.828 21.828 0 0 0 1.056-6.729V28.125c0-12.073-9.802-21.875-21.875-21.875-7.881 0-14.795 4.177-18.645 10.436z"></path></g></svg>';
                        } elseif ($row['mute_type'] == 0) {
                            $punishmentType = '<svg x="0" y="0" viewBox="0 0 32 32" style="enable-background:new 0 0 512 512" xml:space="preserve"><g><path d="M18.753 9.833a4.992 4.992 0 0 0-6.92 6.92zM13.247 18.167a4.992 4.992 0 0 0 6.92-6.92z"></path><path d="M23 3H9a5.006 5.006 0 0 0-5 5v12a5.006 5.006 0 0 0 5 5h1.219l.811 3.242a1 1 0 0 0 1.6.539L17.351 25H23a5.006 5.006 0 0 0 5-5V8a5.006 5.006 0 0 0-5-5zm-7 18a6.979 6.979 0 0 1-4.934-2.041s-.011 0-.015-.01-.006-.01-.01-.015a7 7 0 0 1 9.893-9.893s.011 0 .015.01.006.01.01.015A7 7 0 0 1 16 21z"></path></g></svg>';
                        }
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <li class="modal_open" page="{$type}" id="{$idban}">
                            <span>
                                {$punishmentType}
                            </span>
                            <span class="none_span">
                                <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                            </span>
                            <span id="name" nameid="{$steam_player}">{$name_player}</span>
                            <span>{$reason_ban}</span>
                            <span class="{$style_ban} none_span">{$end_ban}</span>
                            <span class="none_span">{$name_admin}</span>
                        </li>
                    HTML;
                        $JSONResult[$key]["sid"] = $steam_player;
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($steam_player);
                    }
                } elseif ($type == 'admins') {
                    if ($this->server_id != 'all') {
                        $count_list = count($this->Db->queryAll('IksAdminNew', 0, 0, "
                        SELECT 
                            `iks_admins`.`id`
                        FROM 
                            `iks_admins`
                        JOIN 
                            `iks_admin_to_server` 
                        ON 
                            `iks_admins`.`id` = `iks_admin_to_server`.`admin_id`
                        WHERE
                            (`iks_admin_to_server`.`server_id` = :server_id OR `iks_admin_to_server`.`server_id` IS NULL)
                            AND `iks_admins`.`is_disabled` = 0
                        GROUP BY 
                            `iks_admins`.`id`, `iks_admin_to_server`.`admin_id`
                    ", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']]));

                        $list = $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT 
                            `iks_admins`.`id`,
                            `iks_admins`.`name`,
                            `iks_admins`.`steam_id` AS `steamid`,
                            `iks_admins`.`flags`,
                            `iks_groups`.`name` AS `group`,
                            CASE 
                                WHEN `iks_admins`.`group_id` IS NULL THEN NULL
                                ELSE COALESCE(`iks_groups`.`immunity`, `iks_admins`.`immunity`)
                            END AS `immunity`,
                            `iks_admins`.`end_at` AS `end`,
                            `iks_admins`.`group_id`,
                            GROUP_CONCAT(DISTINCT `iks_admin_to_server`.`server_id`) AS `server_id`
                        FROM 
                            `iks_admins`
                        JOIN 
                            `iks_admin_to_server` 
                        ON 
                            `iks_admins`.`id` = `iks_admin_to_server`.`admin_id`
                        LEFT JOIN
                            `iks_groups`
                        ON
                            `iks_admins`.`group_id` = `iks_groups`.`id`
                        WHERE
                            (`iks_admin_to_server`.`server_id` = :server_id OR `iks_admin_to_server`.`server_id` IS NULL)
                            AND `iks_admins`.`is_disabled` = 0
                        GROUP BY 
                            `iks_admins`.`id`, `iks_admin_to_server`.`admin_id`
                        ORDER BY 
                            `immunity` DESC
                        LIMIT " . $page_min . ", " . $limit, ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']]);
                    } else {
                        $count_list = count($this->Db->queryAll('IksAdminNew', 0, 0, "
                        SELECT 
                            `iks_admins`.`id`
                        FROM 
                            `iks_admins`
                        JOIN 
                            `iks_admin_to_server` 
                        ON 
                            `iks_admins`.`id` = `iks_admin_to_server`.`admin_id`
                        WHERE 
                            `iks_admins`.`is_disabled` = 0
                        GROUP BY 
                            `iks_admins`.`id`, `iks_admin_to_server`.`admin_id`
                    "));

                        $list = $this->Db->queryAll('IksAdminNew', 0, 0, "
                        SELECT 
                            `iks_admins`.`id`,
                            `iks_admins`.`name`,
                            `iks_admins`.`steam_id` AS `steamid`,
                            `iks_admins`.`flags`,
                            `iks_groups`.`name` AS `group`,
                            CASE 
                                WHEN `iks_admins`.`group_id` IS NULL THEN NULL
                                ELSE COALESCE(`iks_groups`.`immunity`, `iks_admins`.`immunity`)
                            END AS `immunity`,
                            `iks_admins`.`end_at` AS `end`,
                            `iks_admins`.`group_id`,
                            GROUP_CONCAT(DISTINCT `iks_admin_to_server`.`server_id`) AS `server_id`
                        FROM 
                            `iks_admins`
                        JOIN 
                            `iks_admin_to_server` 
                        ON 
                            `iks_admins`.`id` = `iks_admin_to_server`.`admin_id`
                        LEFT JOIN
                            `iks_groups`
                        ON
                            `iks_admins`.`group_id` = `iks_groups`.`id`
                        WHERE 
                            `iks_admins`.`is_disabled` = 0
                        GROUP BY 
                            `iks_admins`.`id`, `iks_admin_to_server`.`admin_id`
                        ORDER BY 
                            `immunity` DESC
                        LIMIT " . $page_min . ", " . $limit);
                    }
                    $JSONSear = <<<HTML
                    <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchAdmin')}" id="search_admin">
                HTML;
                    foreach ($list as $key => $row) {
                        $id = $row['id'];
                        $steam = $row['steamid'];
                        $name = empty($this->General->checkName($steam)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam));
                        $group = $row['group'];
                        $online = ($this->General->checkOnline($steam)) ? 'online' : '';
                        $background_html = $this->General->getBackground($steam);
                        $raiting = $this->getAdminRaiting($steam);
                        $likes = $raiting['likes'] ?? 0;
                        $dislikes = $raiting['dislikes'] ?? 0;
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <div class="punishmen-admins__card">
                            <div class="punishmen-admins__card-header">
                                <div id="background" backgroundid="{$steam}">{$background_html}</div>
                                <div class="punishmen-admins__rating">
                                    <div class="punishmen-admins__rating-like" data-type="like" data-steam="{$steam}" id="adminRating">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#like"></use>
                                        </svg> {$likes}
                                    </div>
                                    <div class="punishmen-admins__rating-dislike" data-type="dislike" data-steam="{$steam}" id="adminRating">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#like"></use>
                                        </svg>  {$dislikes}
                                    </div>
                                </div>
                                <div class="punishmen-admins__group">{$group}</div>
                            </div>
                            <div class="punishmen-admins__avatar">
                                <span class="punishmen-admins__status {$online}"></span>
                                <img src="{$this->General->getAvatar($steam, 3)}" id="avatar" avatarid="{$steam}" alt="">
                            </div>
                            <div class="punishmen-admins__steamid copy-btn" data-clipboard-text="{$steam}">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy"></use>
                                </svg>
                                {$steam}
                            </div>
                            <a href="/profiles/{$steam}/?search=1" target="_blank" class="punishmen-admins__nickname"  id="name" nameid="{$steam}">{$name}</a>
                            <div class="punishmen-admins__button">
                                <button class="width-100 modal_open" page="{$type}" id="{$id}">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_viewInfo')}</button>
                            </div>
                        </div>
                    HTML;
                        $JSONResult[$key]["sid"] = $row['steamid'];
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($row['steamid']);
                    }
                }
            }
        } elseif ($this->game == 'csgo') {
            if (!empty($this->Db->db_data['SourceBans'])) {
                if ($type == 'bans') {
                    if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
                        $count_list = $this->Db->queryNum('SourceBans', 0, 0, "SELECT COUNT(*) FROM `sb_bans` WHERE (`sid` = :server_id OR `sid` = 0) ORDER BY `created` DESC", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']])[0];
                        $list = $this->Db->queryAll('SourceBans', 0, 0, "SELECT *, (SELECT `user` as name FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_bans`.`aid`) AS `admin_name`, (SELECT `authid` as steamid FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_bans`.`aid`) AS `admin_steamid`  FROM `sb_bans` WHERE `sid` = :server_id ORDER BY `created` DESC LIMIT " . $page_min . ", " . $limit . "", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']]);
                    } else {
                        $count_list = $this->Db->queryNum('SourceBans', 0, 0, "SELECT COUNT(*) FROM `sb_bans` ORDER BY `created` DESC")[0];
                        $list = $this->Db->queryAll('SourceBans', 0, 0, "SELECT *, (SELECT `user` as name FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_bans`.`aid`) AS `admin_name`, (SELECT `authid` as steamid FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_bans`.`aid`) AS `admin_steamid`  FROM `sb_bans` ORDER BY `created` DESC LIMIT " . $page_min . ", " . $limit . "");
                    }
                    $JSONSear = <<<HTML
                        <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchPlayers')}" id="search_ban">
                    HTML;
                    foreach ($list as $key => $row) {
                        $idban = $row['bid'];
                        $steam_player = con_steam64($row['authid']);
                        $steam_admin = con_steam64($row['admin_steamid']);
                        $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam_player));
                        if (!empty($steam_admin)) {
                            $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['admin_name']) : action_text_clear($this->General->checkName($steam_admin));
                        } else {
                            $name_admin = $this->Translate->get_translate_module_phrase('module_page_punishment', '_console');
                        }

                        if ($row['RemoveType'] == 'U') {
                            $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                            $style_ban = 'remove_punish';
                        } elseif ($row['length'] == '0' && $row['RemoveType'] != 'U') {
                            $end_ban = $this->Translate->get_translate_phrase('_Forever');
                            $style_ban = 'permanent_punish';
                        } elseif (time() >= $row['ends'] && $row['length'] != '0') {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['ends'] - $row['created']);
                            $style_ban = 'expired_punish';
                        } else {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['ends'] - time());
                            $style_ban = 'current_punish';
                        }
                        $reason_ban = action_text_clear($row['reason']);
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <li class="modal_open" page="{$type}" id="{$idban}">
                            <span>
                                <svg><use href="/resources/img/sprite.svg#user-block"></use></svg>
                            </span>
                            <span class="none_span">
                                <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                            </span>
                            <span id="name" nameid="{$steam_player}">{$name_player}</span>
                            <span>{$reason_ban}</span>
                            <span class="{$style_ban} none_span">{$end_ban}</span>
                            <span class="none_span">{$name_admin}</span>
                        </li>
                    HTML;
                        $JSONResult[$key]["sid"] = $steam_player;
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($steam_player);
                    }
                } elseif ($type == 'comms') {
                    if ($this->server_id != 'all' && $this->GetSettings()['punishment_all_servers'] == 0) {
                        $count_list = $this->Db->queryNum('SourceBans', 0, 0, "SELECT COUNT(*) FROM `sb_comms` AND `server_id` = :server_id ORDER BY `created` DESC", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']])[0];
                        $list = $this->Db->queryAll('SourceBans', 0, 0, "SELECT *, (SELECT `user` as name FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_comms`.`aid`) AS `admin_name`, (SELECT `authid` as steamid FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_comms`.`aid`) AS `admin_steamid` FROM `sb_comms` WHERE `sid` = :server_id ORDER BY `created` DESC LIMIT " . $page_min . ", " . $limit . "", ['server_id' => $this->GetServerLR()[$this->server_id]['server_sb_id']]);
                    } else {
                        $count_list = $this->Db->queryNum('SourceBans', 0, 0, "SELECT COUNT(*) FROM `sb_comms` ORDER BY `created` DESC")[0];
                        $list = $this->Db->queryAll('SourceBans', 0, 0, "SELECT *, (SELECT `user` as name FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_comms`.`aid`) AS `admin_name`, (SELECT `authid` as steamid FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_comms`.`aid`) AS `admin_steamid` FROM `sb_comms` ORDER BY `created` DESC LIMIT " . $page_min . ", " . $limit . "");
                    }
                    $JSONSear = <<<HTML
                        <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchPlayers')}" id="search_mute">
                    HTML;
                    foreach ($list as $key => $row) {
                        $idban = $row['bid'];
                        $steam_player = con_steam64($row['authid']);
                        $steam_admin = con_steam64($row['admin_steamid']);
                        $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam_player));
                        if (!empty($steam_admin)) {
                            $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($row['admin_name']) : action_text_clear($this->General->checkName($steam_admin));
                        } else {
                            $name_admin = $this->Translate->get_translate_module_phrase('module_page_punishment', '_console');
                        }

                        if ($row['RemoveType'] == 'U') {
                            $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                            $style_ban = 'remove_punish';
                        } elseif ($row['length'] == '0' && $row['RemoveType'] != 'U') {
                            $end_ban = $this->Translate->get_translate_phrase('_Forever');
                            $style_ban = 'permanent_punish';
                        } elseif (time() >= $row['ends'] && $row['length'] != '0') {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['ends'] - $row['created']);
                            $style_ban = 'expired_punish';
                        } else {
                            $end_ban = $this->Modules->action_time_exchange_exact($row['ends'] - time());
                            $style_ban = 'current_punish';
                        }
                        $reason_ban = action_text_clear($row['reason']);
                        if ($row['type'] == 3) {
                            $punishmentType = '<svg><use href="/resources/img/sprite.svg#face-mute"></use></svg>';
                        } elseif ($row['type'] == 2) {
                            $punishmentType = '<svg><use href="/resources/img/sprite.svg#chat-slash"></use></svg>';
                        } elseif ($row['type'] == 1) {
                            $punishmentType = '<svg><use href="/resources/img/sprite.svg#micro-slash"></use></svg>';
                        }
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <li class="modal_open" page="{$type}" id="{$idban}">
                            <span>
                                {$punishmentType}
                            </span>
                            <span class="none_span">
                                <img class="avatar_img" src="{$this->General->getAvatar($steam_player, 3)}" id="avatar" avatarid="{$steam_player}">
                            </span>
                            <span id="name" nameid="{$steam_player}">{$name_player}</span>
                            <span>{$reason_ban}</span>
                            <span class="{$style_ban} none_span">{$end_ban}</span>
                            <span class="none_span">{$name_admin}</span>
                        </li>
                    HTML;
                        $JSONResult[$key]["sid"] = $steam_player;
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($steam_player);
                    }
                } elseif ($type == 'admins') {
                    if ($this->server_id != 'all') {
                        $array = $this->Db->queryAll('SourceBans', 0, 0, "SELECT 
                        `sb_admins`.`aid` AS `admin_id`,
                        `sb_admins`.`user` as name,
                        `sb_admins`.`authid` `steamid`,
                        `sb_admins`.`expired` AS `end`,
                        `sb_admins`.`srv_group` as `group`,
                        `sb_admins`.`immunity` as `immunity`,
                        GROUP_CONCAT(DISTINCT `sb_admins_servers_groups`.`server_id`) AS `server_id`
                    FROM 
                        `sb_admins`
                    JOIN 
                        `sb_admins_servers_groups` 
                    ON 
                        `sb_admins`.`aid` = `sb_admins_servers_groups`.`admin_id`
                    WHERE 
                        (`sb_admins_servers_groups`.`server_id` = :server_id OR `sb_admins_servers_groups`.`server_id` = '-1')
                        AND (`sb_admins`.`expired` > UNIX_TIMESTAMP() OR `sb_admins`.`expired` = 0)
                    GROUP BY
                        `sb_admins`.`aid`
                        ORDER BY `sb_admins`.`immunity` DESC", ['server_id' => $this->server_id]);
                        $count_list = count($array);
                        $list = array_slice($array, $page_min, $limit);
                    } else {
                        $array = $this->Db->queryAll('SourceBans', 0, 0, "SELECT 
                        `sb_admins`.`aid` AS `admin_id`,
                        `sb_admins`.`user` as name,
                        `sb_admins`.`authid` `steamid`,
                        `sb_admins`.`expired` AS `end`,
                        `sb_admins`.`srv_group` as `group`,
                        `sb_admins`.`immunity` as `immunity`,
                        GROUP_CONCAT(DISTINCT `sb_admins_servers_groups`.`server_id`) AS `server_id`
                    FROM 
                        `sb_admins`
                    JOIN 
                        `sb_admins_servers_groups` 
                    ON 
                        `sb_admins`.`aid` = `sb_admins_servers_groups`.`admin_id`
                    WHERE 
                        (`sb_admins`.`expired` > UNIX_TIMESTAMP() OR `sb_admins`.`expired` = 0)
                    GROUP BY
                        `sb_admins`.`aid`
                    ORDER BY `sb_admins`.`immunity` DESC");
                        $count_list = count($array);
                        $list = array_slice($array, $page_min, $limit);
                    }
                    $JSONSear = <<<HTML
                    <input type="text" placeholder="{$this->Translate->get_translate_module_phrase('module_page_punishment', '_searchAdmin')}" id="search_admin">
                HTML;
                    foreach ($list as $key => $row) {
                        $id = $row['admin_id'];
                        $steam = con_steam64($row['steamid']);
                        $name = empty($this->General->checkName($steam)) ? action_text_clear($row['name']) : action_text_clear($this->General->checkName($steam));
                        $group = $row['group'];
                        $online = ($this->General->checkOnline($steam)) ? 'online' : '';
                        $background_html = $this->General->getBackground($steam);
                        $raiting = $this->getAdminRaiting($steam);
                        $likes = $raiting['likes'] ?? 0;
                        $dislikes = $raiting['dislikes'] ?? 0;
                        $JSONResult[$key]["html_list"] = <<<HTML
                        <div class="punishmen-admins__card">
                            <div class="punishmen-admins__card-header">
                                <div id="background" backgroundid="{$steam}">{$background_html}</div>
                                <div class="punishmen-admins__rating">
                                    <div class="punishmen-admins__rating-like" data-type="like" data-steam="{$steam}" id="adminRating">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#like"></use>
                                        </svg> {$likes}
                                    </div>
                                    <div class="punishmen-admins__rating-dislike" data-type="dislike" data-steam="{$steam}" id="adminRating">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#like"></use>
                                        </svg> {$dislikes}
                                    </div>
                                </div>
                                <div class="punishmen-admins__group">{$group}</div>
                            </div>
                            <div class="punishmen-admins__avatar">
                                <span class="punishmen-admins__status {$online}"></span>
                                <img src="{$this->General->getAvatar($steam, 3)}" id="avatar" avatarid="{$steam}" alt="">
                            </div>
                            <div class="punishmen-admins__steamid copy-btn" data-clipboard-text="{$steam}">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy"></use>
                                </svg>
                                {$steam}
                            </div>
                            <a href="/profiles/{$steam}/?search=1" target="_blank" class="punishmen-admins__nickname"  id="name" nameid="{$steam}">{$name}</a>
                            <div class="punishmen-admins__button">
                                <button class="width-100 modal_open" page="{$type}" id="{$id}">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_viewInfo')}</button>
                            </div>
                        </div>
                    HTML;
                        $JSONResult[$key]["sid"] = $steam;
                        $JSONResult[$key]["CheckAvatar"] = $this->General->checkAvatar($steam);
                    }
                }
            }
        }
        $max_pages = ceil($count_list / $limit);
        $pagination = Pagination($max_pages, $page, 1);
        return ["html" => $JSONResult, "pagination" => $pagination, "count" => $count_list, "search" => $JSONSear, "max_pages" => $max_pages, "page" => $page];
    }

    public function RenderingModalWindow($page, $id)
    {
        if ($this->game == 'cs2') {


            if (!empty($this->Db->db_data['AdminSystem'])) {
                if ($page == 'bans') {
                    $modal = $this->Db->query('AdminSystem', 0, 0, "SELECT *, (SELECT `name` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steamid` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_steamid`, (SELECT `name` FROM `as_admins` WHERE `id` = `unpunish_admin_id`) AS `unpunish_name`, (SELECT `steamid` FROM `as_admins` WHERE `id` = `unpunish_admin_id`) AS `unpunish_steamid` FROM `as_punishments` WHERE `id` = :id AND `punish_type` = 0 LIMIT 1", ['id' => $id]);
                    $steam_player = $modal['steamid'];
                    $steam_admin = $modal['admin_steamid'];
                    $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($modal['name']) : action_text_clear($this->General->checkName($steam_player));
                    if (!empty($steam_admin)) {
                        $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($modal['admin_name']) : action_text_clear($this->General->checkName($steam_admin));
                    } else {
                        $name_admin = $this->Translate->get_translate_module_phrase('module_page_punishment', '_console');
                    }
                    $unpunish = '';
                    $steam_unpunish = $modal['unpunish_steamid'];
                    $name_unpunish = empty($this->General->checkName($steam_unpunish)) ? action_text_clear($modal['unpunish_name']) : action_text_clear($this->General->checkName($steam_unpunish));
                    $JSONResult["modal"] = $modal;
                    if ($steam_unpunish != null) {
                        if (!empty($steam_unpunish)) {
                            $unpunish = <<<HTML
                            <div class="punish_take_admin">
                                {$this->Translate->get_translate_module_phrase('module_page_punishment', '_unpunish_By')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_unpunish}</a>
                            </div>
                        HTML;
                        } else {
                            $unpunish = <<<HTML
                            <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_unpunish_By')} - {$this->Translate->get_translate_module_phrase('module_page_punishment', '_console')}</div>
                        HTML;
                        }
                    }
                    if (!empty($steam_admin)) {
                        $IssuedBy = <<<HTML
                        <div class="punish_take_admin">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_admin}</a>
                        </div>
                    HTML;
                    } else {
                        $IssuedBy = <<<HTML
                        <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - {$this->Translate->get_translate_module_phrase('module_page_punishment', '_console')}</div>
                    HTML;
                    }
                    if (!empty($modal['unpunish_admin_id'])) {
                        $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                        $modal_unban = false;
                    } elseif ($modal['expires'] == '0') {
                        $end_ban = $this->Translate->get_translate_phrase('_Forever');
                        $modal_unban = true;
                    } elseif (time() > $modal['expires']) {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['expires'] - $modal['created']);
                        $modal_unban = false;
                    } else {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['expires'] - time());
                        $modal_unban = true;
                    }
                    $c_ban_time = date('d.m.Y H:i', $modal['created']);
                    $button_unabn_buy = '';
                    if ($this->GetSettings()['func_unban'] == 1 && $modal_unban) {
                        $button_unabn_buy = <<<HTML
                        <button class="punish_buy btn_unban" page="{$page}" idpunish="{$id}" sid="{$steam_player}" type="buy">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_BuyUnban')}
                            <span>{$this->GetSettings()['price_unban']} {$this->General->currency}</span>
                        </button>
                    HTML;
                    }
                    $button_unabn = '';
                    if ((file_exists(MODULES . 'module_page_managersystem/description.json') || isset($_SESSION['user_admin'])) && $modal_unban) {
                        if ($this->hasAdminAccess) {
                            $button_unabn = <<<HTML
                                <button class="width-100 btn_unban" page="{$page}" idpunish="{$id}" sid="{$steam_player}" type="admin">
                                    {$this->Translate->get_translate_module_phrase('module_page_punishment', '_RemovePunishButton')}
                                </button>
                            HTML;
                        }
                    }
                    if (!empty($button_unabn_buy) && !empty($button_unabn)) {
                        $div = <<<HTML
                        <div class="punish_buttons">
                    HTML;
                        $divc = <<<HTML
                        </div>
                    HTML;
                    }
                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        {$div}
                            {$button_unabn_buy}
                            {$button_unabn}
                        {$divc}
                        
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_PunishedPlayer')}</p>
                                    <span><a href="/profiles/{$steam_player}/?search=1" target="_blank">{$name_player}</a></span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#check-circle"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_SteamidPlayer')}</p>
                                    <span>{$steam_player}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#calendare"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_DatePunishment')}</p>
                                    <span>{$c_ban_time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#time-expired"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_TermModal')}</p>
                                    <span>{$end_ban}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        {$IssuedBy}
                        {$unpunish}
                    </div>
                HTML;
                } else if ($page == 'comms') {
                    $modal = $this->Db->query('AdminSystem', 0, 0, "SELECT *, (SELECT `name` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steamid` FROM `as_admins` WHERE `id` = `admin_id`) AS `admin_steamid`, (SELECT `name` FROM `as_admins` WHERE `id` = `unpunish_admin_id`) AS `unpunish_name`, (SELECT `steamid` FROM `as_admins` WHERE `id` = `unpunish_admin_id`) AS `unpunish_steamid` FROM `as_punishments` WHERE `id` = :id AND `punish_type` != 0 LIMIT 1", ['id' => $id]);
                    $steam_player = $modal['steamid'];
                    $steam_admin = $modal['admin_steamid'];
                    $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($modal['name']) : action_text_clear($this->General->checkName($steam_player));
                    if (!empty($steam_admin)) {
                        $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($modal['admin_name']) : action_text_clear($this->General->checkName($steam_admin));
                    } else {
                        $name_admin = $this->Translate->get_translate_module_phrase('module_page_punishment', '_console');
                    }
                    $unpunish = '';
                    $steam_unpunish = $modal['unpunish_steamid'];
                    $name_unpunish = empty($this->General->checkName($steam_unpunish)) ? action_text_clear($modal['unpunish_name']) : action_text_clear($this->General->checkName($steam_unpunish));
                    $JSONResult["modal"] = $modal;
                    if ($steam_unpunish != null) {
                        if (!empty($steam_unpunish)) {
                            $unpunish = <<<HTML
                            <div class="punish_take_admin">
                                {$this->Translate->get_translate_module_phrase('module_page_punishment', '_unpunish_By')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_unpunish}</a>
                            </div>
                        HTML;
                        } else {
                            $unpunish = <<<HTML
                            <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_unpunish_By')} - {$this->Translate->get_translate_module_phrase('module_page_punishment', '_console')}</div>
                        HTML;
                        }
                    }
                    if (!empty($steam_admin)) {
                        $IssuedBy = <<<HTML
                        <div class="punish_take_admin">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_admin}</a>
                        </div>
                    HTML;
                    } else {
                        $IssuedBy = <<<HTML
                        <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - {$this->Translate->get_translate_module_phrase('module_page_punishment', '_console')}</div>
                    HTML;
                    }
                    if (!empty($modal['unpunish_admin_id'])) {
                        $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unmuted');
                        $modal_unban = false;
                    } elseif ($modal['expires'] == '0') {
                        $end_ban = $this->Translate->get_translate_phrase('_Forever');
                        $modal_unban = true;
                    } elseif (time() > $modal['expires']) {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['expires'] - $modal['created']);
                        $modal_unban = false;
                    } else {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['expires'] - time());
                        $modal_unban = true;
                    }
                    $c_ban_time = date('d.m.Y H:i', $modal['created']);
                    $button_unabn_buy = '';
                    if ($this->GetSettings()['func_unmute'] == 1 && $modal_unban) {
                        $button_unabn_buy = <<<HTML
                        <button class="punish_buy btn_unban" page="{$page}" idpunish="{$id}" sid="{$steam_player}" type="buy">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_BuyUnban')}
                            <span>{$this->GetSettings()['price_unmute']} {$this->General->currency}</span>
                        </button>
                    HTML;
                    }
                    $button_unabn = '';
                    if ((file_exists(MODULES . 'module_page_managersystem/description.json') || isset($_SESSION['user_admin'])) && $modal_unban) {
                        if ($this->hasAdminAccess) {
                            $button_unabn = <<<HTML
                            <button class="width-100 btn_unban" page="{$page}" idpunish="{$id}" sid="{$steam_player}" type="admin">
                                {$this->Translate->get_translate_module_phrase('module_page_punishment', '_RemovePunishButton')}
                            </button>
                        HTML;
                        }
                    }
                    if (!empty($button_unabn_buy) && !empty($button_unabn)) {
                        $hr = <<<HTML
                        <hr>
                    HTML;
                    }
                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        <div class="punish_buttons">
                            {$button_unabn_buy}
                            {$button_unabn}
                        </div>
                        {$hr}
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_PunishedPlayer')}</p>
                                    <span><a href="/profiles/{$steam_player}/?search=1" target="_blank">{$name_player}</a></span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#check-circle"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_SteamidPlayer')}</p>
                                    <span>{$steam_player}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#calendare"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_DatePunishment')}</p>
                                    <span>{$c_ban_time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#time-expired"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_TermModal')}</p>
                                    <span>{$end_ban}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        {$IssuedBy}
                        {$unpunish}
                    </div>
                HTML;
                } else if ($page == 'admins') {
                    if ($this->server_id != 'all') {
                        $admin_info = $this->Db->query('AdminSystem', 0, 0, "SELECT 
                            `as_admins`.`steamid`,
                            `as_groups`.`name` as `group`,
                            `as_groups`.`immunity` as `immunity`,
                            GROUP_CONCAT(DISTINCT `as_admins_servers`.`expires`) AS `end`,
                            (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins_servers`.`admin_id` AND `punish_type` = 0) AS `bans_count`,
                            (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins_servers`.`admin_id` AND (`punish_type` = 1 OR `punish_type` = 3)) AS `mutes_count`,
                            (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins_servers`.`admin_id` AND (`punish_type` = 2 OR `punish_type` = 3)) AS `gags_count`
                        FROM `as_admins_servers`
                        JOIN
                            `as_groups` 
                        ON 
                            `as_admins_servers`.`group_id` = `as_groups`.`id`
                        INNER JOIN 
                            `as_admins`
                        ON 
                            `as_admins_servers`.`admin_id` = `as_admins`.`id`
                        WHERE `admin_id` = :admin_id
                        AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
                        AND (`as_admins_servers`.`server_id` = :server_id OR `as_admins_servers`.`server_id` = '-1')
                        GROUP BY `as_admins_servers`.`group_id`", ['admin_id' => $id, 'server_id' => $this->server_id]);
                    } else {
                        $admin_info = $this->Db->query('AdminSystem', 0, 0, "SELECT 
                            `as_admins`.`steamid`,
                            `as_groups`.`name` as `group`,
                            `as_groups`.`immunity` as `immunity`,
                            GROUP_CONCAT(DISTINCT `as_admins_servers`.`expires`) AS `end`,
                            (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins_servers`.`admin_id` AND `punish_type` = 0) AS `bans_count`,
                            (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins_servers`.`admin_id` AND (`punish_type` = 1 OR `punish_type` = 3)) AS `mutes_count`,
                            (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins_servers`.`admin_id` AND (`punish_type` = 2 OR `punish_type` = 3)) AS `gags_count`,
                            (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins_servers`.`admin_id` AND (`punish_type` = 2 OR `punish_type` = 3)) AS `gags_count`
                        FROM `as_admins_servers`
                        JOIN
                            `as_groups` 
                        ON 
                            `as_admins_servers`.`group_id` = `as_groups`.`id`
                        INNER JOIN 
                            `as_admins`
                        ON 
                            `as_admins_servers`.`admin_id` = `as_admins`.`id`
                        WHERE `admin_id` = :admin_id
                        AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
                        GROUP BY `as_admins_servers`.`group_id`", ['admin_id' => $id]);
                    }
                    $group = $admin_info['group'];
                    if ($admin_info['end'] == 0) {
                        $time = $this->Translate->get_translate_phrase('_Forever');
                    } else {
                        $time = date('d.m.Y H:i', $admin_info['end']);
                    }
                    $steam = $admin_info['steamid'];
                    $Warns = ceil($this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `lvl_web_managersystem_warn` WHERE `steamid` = :steam AND `time` > UNIX_TIMESTAMP()", ['steam' => $steam])[0]);
                    $Check = ceil($this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `checkcheats_stats` WHERE `admin_steamid` = :steam", ['steam' => $steam])[0]);
                    if (file_exists(MODULES . 'module_page_reports/description.json')) {
                        $Rep = ceil($this->Db->queryNum('Reports', 0, 0, "SELECT COUNT(*) FROM `rs_reports` WHERE `steamid_admin_verdict` = :steam", ['steam' => $steam])[0]);
                    } else {
                        $Rep = 0;
                    }

                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_group')}</p>
                                    <span>{$group}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#time-expired"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_term')}</p>
                                    <span>{$time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#warning"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_warns')}</p>
                                    <span>{$Warns}/3</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#block"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_bansIssued')}</p>
                                    <span>{$admin_info['bans_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#micro-slash"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_mutesIssued')}</p>
                                    <span>{$admin_info['mutes_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#chat-slash"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_gagsIssued')}</p>
                                    <span>{$admin_info['gags_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#check-circle"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_passedChecks')}</p>
                                    <span>{$Check}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#report-list"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_reportsReviewed')}</p>
                                    <span>{$Rep}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <button class="width-100" onClick="window.open('https://steamcommunity.com/profiles/{$steam}');">
                            <svg><use href="/resources/img/sprite.svg#steam"></use></svg>
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_openProfile')} Steam
                        </button>
                    </div>
                HTML;
                }
            }
            if (!empty($this->Db->db_data['IksAdmin'])) {
                if ($page == 'bans') {
                    $modal = $this->Db->query('IksAdmin', 0, 0, "SELECT * FROM `iks_bans` WHERE `id` = :id LIMIT 1", ['id' => $id]);
                    $steam_player = $modal['sid'];
                    $steam_admin = $modal['adminsid'];
                    $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($modal['name']) : $this->General->checkName($steam_player);
                    $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($modal['adminName']) : $this->General->checkName($steam_admin);

                    if ($modal['Unbanned'] == 1) {
                        $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                        $modal_unban = false;
                    } elseif ($modal['time'] == 0) {
                        $end_ban = $this->Translate->get_translate_phrase('_Forever');
                        $modal_unban = true;
                    } elseif (time() > $modal['created'] + $modal['time']) {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['time']);
                        $modal_unban = false;
                    } else {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['end'] - time());
                        $modal_unban = true;
                    }
                    $c_ban_time = date('d.m.Y H:i', $modal['created']);
                    $button_unabn_buy = '';
                    if ($this->GetSettings()['func_unban'] == 1 && $modal_unban) {
                        $button_unabn_buy = <<<HTML
                        <button class="punish_buy btn_unban" page="{$page}" idpunish="{$id}" sid="{$steam_player}" type="buy">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_BuyUnban')}
                            <span>{$this->GetSettings()['price_unban']} {$this->General->currency}</span>
                        </button>
                    HTML;
                    }
                    $button_unabn = '';
                    if ((file_exists(MODULES . 'module_page_managersystem/description.json') || isset($_SESSION['user_admin'])) && $modal_unban) {
                        if ($this->hasAdminAccess) {
                            $button_unabn = <<<HTML
                            <button class="width-100 btn_unban" page="{$page}" idpunish="{$id}" sid="{$steam_player}" type="admin">
                                {$this->Translate->get_translate_module_phrase('module_page_punishment', '_RemovePunishButton')}
                            </button>
                        HTML;
                        }
                    }
                    if (!empty($button_unabn_buy) && !empty($button_unabn)) {
                        $hr = <<<HTML
                        <hr>
                    HTML;
                        $div = <<<HTML
                        <div class="punish_buttons">
                    HTML;
                        $divc = <<<HTML
                        </div>
                    HTML;
                    }
                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        {$div}
                            {$button_unabn_buy}
                            {$button_unabn}
                        {$divc}
                        {$hr}
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_PunishedPlayer')}</p>
                                    <span><a href="/profiles/{$steam_player}/?search=1" target="_blank">{$name_player}</a></span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#check-circle"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_SteamidPlayer')}</p>
                                    <span>{$steam_player}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#calendare"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_DatePunishment')}</p>
                                    <span>{$c_ban_time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#time-expired"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_TermModal')}</p>
                                    <span>{$end_ban}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_admin}</a></div>
                    </div>
                HTML;
                } else if ($page == 'comms') {
                    $voice = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT * FROM `iks_mutes` WHERE `id` = :id LIMIT 1", ['id' => $id]);
                    $gags = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT * FROM `iks_gags` WHERE `id` = :id LIMIT 1", ['id' => $id]);
                    $modalarray = array_merge($voice, $gags);
                    usort($modalarray, function ($a, $b) {
                        return $b['created'] - $a['created'];
                    });
                    $modal = reset($modalarray);
                    $steam_player = $modal['sid'];
                    $steam_admin = $modal['adminsid'];
                    $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($modal['name']) : $this->General->checkName($steam_player);
                    $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($modal['adminName']) : $this->General->checkName($steam_admin);

                    if ($modal['Unbanned'] == 1) {
                        $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unmuted');
                        $modal_unban = false;
                    } elseif ($modal['time'] == 0) {
                        $end_ban = $this->Translate->get_translate_phrase('_Forever');
                        $modal_unban = true;
                    } elseif (time() > $modal['created'] + $modal['time']) {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['time']);
                        $modal_unban = false;
                    } else {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['end'] - time());
                        $modal_unban = true;
                    }
                    $c_ban_time = date('d.m.Y H:i', $modal['created']);
                    $button_unabn_buy = '';
                    if ($this->GetSettings()['func_unmute'] == 1 && $modal_unban) {
                        $button_unabn_buy = <<<HTML
                        <button class="punish_buy btn_unban" page="{$page}" sid="{$steam_player}" type="buy">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_BuyUnban')}
                            <span>{$this->GetSettings()['price_unmute']} {$this->General->currency}</span>
                        </button>
                    HTML;
                    }
                    $button_unabn = '';
                    if ((file_exists(MODULES . 'module_page_managersystem/description.json') || isset($_SESSION['user_admin'])) && $modal_unban) {
                        if ($this->hasAdminAccess) {
                            $button_unabn = <<<HTML
                            <button class="width-100 btn_unban" page="{$page}" sid="{$steam_player}" type="admin">
                                {$this->Translate->get_translate_module_phrase('module_page_punishment', '_RemovePunishButton')}
                            </button>
                        HTML;
                        }
                    }
                    if (!empty($button_unabn_buy) && !empty($button_unabn)) {
                        $hr = <<<HTML
                        <hr>
                    HTML;
                    }
                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        <div class="punish_buttons">
                            {$button_unabn_buy}
                            {$button_unabn}
                        </div>
                        {$hr}
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_PunishedPlayer')}</p>
                                    <span><a href="/profiles/{$steam_player}/?search=1" target="_blank">{$name_player}</a></span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#check-circle"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_SteamidPlayer')}</p>
                                    <span>{$steam_player}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#calendare"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_DatePunishment')}</p>
                                    <span>{$c_ban_time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#time-expired"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_TermModal')}</p>
                                    <span>{$end_ban}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_admin}</a></div>
                    </div>
                HTML;
                } else if ($page == 'admins') {
                    if ($this->server_id != 'all') {
                        $admin_info = $this->Db->query('IksAdmin', 0, 0, "SELECT 
                        `iks_admins`.`sid` AS `steamid`,
                        `iks_admins`.`immunity`,
                        `iks_admins`.`group_id`,
                        `iks_admins`.`end`,
                        `iks_groups`.`name` AS `group`,
                        `iks_admins`.`server_id`,
                        (SELECT COUNT(1) FROM `iks_bans` WHERE `adminsid` = `iks_admins`.`sid`) AS `bans_count`,
                        (SELECT COUNT(1) FROM `iks_mutes` WHERE `adminsid` = `iks_admins`.`sid`) AS `mutes_count`,
                        (SELECT COUNT(1) FROM `iks_gags` WHERE `adminsid` = `iks_admins`.`sid`) AS `gags_count`
                    FROM 
                        `iks_admins`
                    LEFT JOIN
                        `iks_groups` ON `iks_admins`.`group_id` = `iks_groups`.`id`
                    WHERE 
                        `iks_admins`.`id` = :admin_id
                        AND (`iks_admins`.`end` > UNIX_TIMESTAMP() OR `iks_admins`.`end` = 0)
                        AND (
                            FIND_IN_SET(:server_id, REPLACE(`iks_admins`.`server_id`, ';', ',')) > 0 
                            OR `iks_admins`.`server_id` = ''
                        )
                    GROUP BY `iks_admins`.`group_id`", ['admin_id' => $id, 'server_id' => $this->server_id]);
                    } else {
                        $admin_info = $this->Db->query('IksAdmin', 0, 0, "SELECT 
                        `iks_admins`.`sid` AS `steamid`,
                        `iks_admins`.`immunity`,
                        `iks_admins`.`group_id`,
                        `iks_admins`.`end`,
                        `iks_groups`.`name` AS `group`,
                        `iks_admins`.`server_id`,
                        (SELECT COUNT(1) FROM `iks_bans` WHERE `adminsid` = `iks_admins`.`sid`) AS `bans_count`,
                        (SELECT COUNT(1) FROM `iks_mutes` WHERE `adminsid` = `iks_admins`.`sid`) AS `mutes_count`,
                        (SELECT COUNT(1) FROM `iks_gags` WHERE `adminsid` = `iks_admins`.`sid`) AS `gags_count`
                    FROM 
                        `iks_admins`
                    LEFT JOIN
                        `iks_groups` ON `iks_admins`.`group_id` = `iks_groups`.`id`
                    WHERE 
                        `iks_admins`.`id` = :admin_id
                        AND (`iks_admins`.`end` > UNIX_TIMESTAMP() OR `iks_admins`.`end` = 0)
                    GROUP BY `iks_admins`.`group_id`
                    ", ['admin_id' => $id]);
                    }
                    $group = $admin_info['group'];
                    if ($admin_info['end'] == 0) {
                        $time = $this->Translate->get_translate_phrase('_Forever');
                    } else {
                        $time = date('d.m.Y H:i', $admin_info['end']);
                    }
                    $steam = $admin_info['steamid'];
                    $Warns = ceil($this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `lvl_web_managersystem_warn` WHERE `steamid` = :steam AND `time` > UNIX_TIMESTAMP()", ['steam' => $steam])[0]);
                    $Check = ceil($this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `checkcheats_stats` WHERE `admin_steamid` = :steam", ['steam' => $steam])[0]);
                    if (file_exists(MODULES . 'module_page_reports/description.json')) {
                        $Rep = ceil($this->Db->queryNum('Reports', 0, 0, "SELECT COUNT(*) FROM `rs_reports` WHERE `steamid_admin_verdict` = :steam", ['steam' => $steam])[0]);
                    } else {
                        $Rep = 0;
                    }
                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_group')}</p>
                                    <span>{$group}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#time-expired"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_term')}</p>
                                    <span>{$time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#warning"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_warns')}</p>
                                    <span>{$Warns}/3</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#block"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_bansIssued')}</p>
                                    <span>{$admin_info['bans_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#micro-slash"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_mutesIssued')}</p>
                                    <span>{$admin_info['mutes_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#chat-slash"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_gagsIssued')}</p>
                                    <span>{$admin_info['gags_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#check-circle"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_passedChecks')}</p>
                                    <span>{$Check}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#report-list"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_reportsReviewed')}</p>
                                    <span>{$Rep}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <button class="width-100" onClick="window.open('https://steamcommunity.com/profiles/{$steam}');">
                            <svg><use href="/resources/img/sprite.svg#steam"></use></svg>
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_openProfile')} Steam
                        </button>
                    </div>
                HTML;
                }
            }
            if (!empty($this->Db->db_data['IksAdminNew'])) {
                if ($page == 'bans') {
                    $modal = $this->Db->query('IksAdminNew', 0, 0, "SELECT *, (SELECT `name` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steam_id` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_steamid` FROM `iks_bans` WHERE `id` = :id LIMIT 1", ['id' => $id]);
                    $steam_player = $modal['steam_id'];
                    $steam_admin = $modal['admin_steamid'];
                    $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($modal['name']) : $this->General->checkName($steam_player);
                    $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($modal['admin_name']) : $this->General->checkName($steam_admin);
                    if (!empty($modal['unbanned_by'])) {
                        $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                        $modal_unban = false;
                    } elseif ($modal['end_at'] == '0') {
                        $end_ban = $this->Translate->get_translate_phrase('_Forever');
                        $modal_unban = true;
                    } elseif (time() > $modal['end_at']) {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['end_at'] - $modal['created_at']);
                        $modal_unban = false;
                    } else {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['end_at'] - time());
                        $modal_unban = true;
                    }
                    $c_ban_time = date('d.m.Y H:i', $modal['created_at']);
                    $button_unabn_buy = '';
                    if ($this->GetSettings()['func_unban'] == 1 && $modal_unban) {
                        $button_unabn_buy = <<<HTML
                        <button class="punish_buy btn_unban" page="{$page}" idpunish="{$id}" sid="{$steam_player}" type="buy">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_BuyUnban')}
                            <span>{$this->GetSettings()['price_unban']} {$this->General->currency}</span>
                        </button>
                    HTML;
                    }
                    $button_unabn = '';
                    if ((file_exists(MODULES . 'module_page_managersystem/description.json') || isset($_SESSION['user_admin'])) && $modal_unban) {
                        if ($this->hasAdminAccess) {
                            $button_unabn = <<<HTML
                            <button class="width-100 btn_unban" page="{$page}" idpunish="{$id}" sid="{$steam_player}" type="admin">
                                {$this->Translate->get_translate_module_phrase('module_page_punishment', '_RemovePunishButton')}
                            </button>
                        HTML;
                        }
                    }
                    if (!empty($button_unabn_buy) && !empty($button_unabn)) {
                        $hr = <<<HTML
                        <hr>
                    HTML;
                        $div = <<<HTML
                        <div class="punish_buttons">
                    HTML;
                        $divc = <<<HTML
                        </div>
                    HTML;
                    }
                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        {$div}
                            {$button_unabn_buy}
                            {$button_unabn}
                        {$divc}
                        {$hr}
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg x="0" y="0" viewBox="0 0 448 448" xml:space="preserve">
                                        <g>
                                            <path d="M352 128c0 70.692-57.308 128-128 128S96 198.692 96 128 153.308 0 224 0s128 57.308 128 128zm-44.48 117.12c-49.913 35.838-117.127 35.838-167.04 0C83.528 275.823 48.015 335.299 48 400c0 26.51 21.49 48 48 48h256c26.51 0 48-21.49 48-48-.015-64.701-35.528-124.177-92.48-154.88z"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_PunishedPlayer')}</p>
                                    <span><a href="/profiles/{$steam_player}/?search=1" target="_blank">{$name_player}</a></span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg x="0" y="0" viewBox="0 0 32 32" xml:space="preserve">
                                        <g>
                                            <path d="M30 22v3c0 2.757-2.243 5-5 5h-3a1 1 0 1 1 0-2h3c1.654 0 3-1.346 3-3v-3a1 1 0 1 1 2 0zM25 2h-3a1 1 0 1 0 0 2h3c1.654 0 3 1.346 3 3v3a1 1 0 1 0 2 0V7c0-2.757-2.243-5-5-5zM10 28H7c-1.654 0-3-1.346-3-3v-3a1 1 0 1 0-2 0v3c0 2.757 2.243 5 5 5h3a1 1 0 1 0 0-2zM3 11a1 1 0 0 0 1-1V7c0-1.654 1.346-3 3-3h3a1 1 0 1 0 0-2H7C4.243 2 2 4.243 2 7v3a1 1 0 0 0 1 1zm9.141 5.164a6.68 6.68 0 0 0-1.085.886A6.915 6.915 0 0 0 9 22a1 1 0 0 0 1 1h12.02a1 1 0 0 0 .977-1.215 6.9 6.9 0 0 0-2.044-4.726 6.736 6.736 0 0 0-1.094-.894A4.962 4.962 0 0 0 21 13c0-2.757-2.243-5-5-5s-5 2.243-5 5c0 1.178.416 2.284 1.141 3.164z"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_SteamidPlayer')}</p>
                                    <span>{$steam_player}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg x="0" y="0" viewBox="0 0 32 32" xml:space="preserve">
                                        <g>
                                            <path d="M30 6.93V11H2V6.93A2.969 2.969 0 0 1 5 4h2v2a3 3 0 0 0 6 0V4h6v2a3 3 0 0 0 6 0V4h2a2.969 2.969 0 0 1 3 2.93zM2 13v16.07A2.969 2.969 0 0 0 5 32h22a2.969 2.969 0 0 0 3-2.93V13zm9 14a1.003 1.003 0 0 1-1 1H8a1.003 1.003 0 0 1-1-1v-2a1.003 1.003 0 0 1 1-1h2a1.003 1.003 0 0 1 1 1zm0-7a1.003 1.003 0 0 1-1 1H8a1.003 1.003 0 0 1-1-1v-2a1.003 1.003 0 0 1 1-1h2a1.003 1.003 0 0 1 1 1zm7 7a1.003 1.003 0 0 1-1 1h-2a1.003 1.003 0 0 1-1-1v-2a1.003 1.003 0 0 1 1-1h2a1.003 1.003 0 0 1 1 1zm0-7a1.003 1.003 0 0 1-1 1h-2a1.003 1.003 0 0 1-1-1v-2a1.003 1.003 0 0 1 1-1h2a1.003 1.003 0 0 1 1 1zm7 0a1.003 1.003 0 0 1-1 1h-2a1.003 1.003 0 0 1-1-1v-2a1.003 1.003 0 0 1 1-1h2a1.003 1.003 0 0 1 1 1z"></path>
                                            <path d="M11 4v2a1 1 0 0 1-2 0V4a1 1 0 0 1 2 0zM23 4v2a1 1 0 0 1-2 0V4a1 1 0 0 1 2 0z"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_DatePunishment')}</p>
                                    <span>{$c_ban_time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg x="0" y="0" viewBox="0 0 512 512" xml:space="preserve">
                                        <g>
                                            <path d="M426.667 102.99V58.066C439.358 50.667 448 37.059 448 21.333V10.667A10.66 10.66 0 0 0 437.333 0H74.667A10.66 10.66 0 0 0 64 10.667v10.667c0 15.725 8.642 29.333 21.333 36.733v44.923c0 42.271 18.021 82.729 49.438 111L181.448 256l-46.677 42.01c-31.417 28.271-49.438 68.729-49.438 111v44.923C72.642 461.333 64 474.941 64 490.667v10.667A10.66 10.66 0 0 0 74.667 512h362.667a10.66 10.66 0 0 0 10.667-10.667v-10.667c0-15.725-8.642-29.333-21.333-36.733V409.01c0-42.271-18.021-82.729-49.438-111L330.552 256l46.677-42.01c31.417-28.271 49.438-68.73 49.438-111zm-77.979 79.291-64.292 57.865c-4.5 4.042-7.063 9.802-7.063 15.854s2.563 11.813 7.063 15.854l64.292 57.865C371.125 349.917 384 378.823 384 409.01V448h-26.672l-92.797-123.729c-4.021-5.375-13.042-5.375-17.063 0L154.672 448H128v-38.99c0-30.188 12.875-59.094 35.313-79.292l64.292-57.865c4.5-4.042 7.063-9.802 7.063-15.854s-2.563-11.813-7.063-15.854l-64.292-57.865C140.875 162.083 128 133.177 128 102.99V64h256v38.99c0 30.187-12.875 59.093-35.312 79.291z"></path>
                                            <path d="M329.521 149.333H182.469c-4.219 0-8.042 2.49-9.75 6.344a10.655 10.655 0 0 0 1.854 11.49l74.271 68.521c2.031 1.844 4.594 2.76 7.156 2.76s5.125-.917 7.156-2.76l74.26-68.521a10.654 10.654 0 0 0 1.854-11.49 10.665 10.665 0 0 0-9.749-6.344z"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_TermModal')}</p>
                                    <span>{$end_ban}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_admin}</a></div>
                    </div>
                HTML;
                } else if ($page == 'comms') {
                    $modal = $this->Db->query('IksAdminNew', 0, 0, "SELECT *, (SELECT `name` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_name`, (SELECT `steam_id` FROM `iks_admins` WHERE `id` = `admin_id`) AS `admin_steamid` FROM `iks_comms` WHERE `id` = :id LIMIT 1", ['id' => $id]);
                    $id = $modal['id'];
                    $steam_player = $modal['steam_id'];
                    $steam_admin = $modal['admin_steamid'];
                    $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($modal['name']) : $this->General->checkName($steam_player);
                    $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($modal['admin_name']) : $this->General->checkName($steam_admin);
                    if (!empty($key['unbanned_by'])) {
                        $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unmuted');
                        $modal_unban = false;
                    } elseif ($modal['end_at'] == '0') {
                        $end_ban = $this->Translate->get_translate_phrase('_Forever');
                        $modal_unban = true;
                    } elseif (time() > $modal['end_at']) {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['end_at'] - $modal['created_at']);
                        $modal_unban = false;
                    } else {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['end_at'] - time());
                        $modal_unban = true;
                    }
                    $c_ban_time = date('d.m.Y H:i', $modal['created_at']);
                    $button_unabn_buy = '';
                    if ($this->GetSettings()['func_unmute'] == 1 && $modal_unban) {
                        $button_unabn_buy = <<<HTML
                        <button class="punish_buy btn_unban" page="{$page}" idpunish="{$id}" sid="{$steam_player}" type="buy">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_BuyUnban')}
                            <span>{$this->GetSettings()['price_unmute']} {$this->General->currency}</span>
                        </button>
                    HTML;
                    }
                    $button_unabn = '';
                    if ((file_exists(MODULES . 'module_page_managersystem/description.json') || isset($_SESSION['user_admin'])) && $modal_unban) {
                        if ($this->hasAdminAccess) {
                            $button_unabn = <<<HTML
                            <button class="width-100 btn_unban" page="{$page}" idpunish="{$id}" sid="{$steam_player}" type="admin">
                                {$this->Translate->get_translate_module_phrase('module_page_punishment', '_RemovePunishButton')}
                            </button>
                        HTML;
                        }
                    }
                    if (!empty($button_unabn_buy) && !empty($button_unabn)) {
                        $hr = <<<HTML
                        <hr>
                    HTML;
                    }
                    $JSONResult["modal"] = $modal;
                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        <div class="punish_buttons">
                            {$button_unabn_buy}
                            {$button_unabn}
                        </div>
                        {$hr}
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg x="0" y="0" viewBox="0 0 448 448" xml:space="preserve">
                                        <g>
                                            <path d="M352 128c0 70.692-57.308 128-128 128S96 198.692 96 128 153.308 0 224 0s128 57.308 128 128zm-44.48 117.12c-49.913 35.838-117.127 35.838-167.04 0C83.528 275.823 48.015 335.299 48 400c0 26.51 21.49 48 48 48h256c26.51 0 48-21.49 48-48-.015-64.701-35.528-124.177-92.48-154.88z"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_PunishedPlayer')}</p>
                                    <span><a href="/profiles/{$steam_player}/?search=1" target="_blank">{$name_player}</a></span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg x="0" y="0" viewBox="0 0 32 32" xml:space="preserve">
                                        <g>
                                            <path d="M30 22v3c0 2.757-2.243 5-5 5h-3a1 1 0 1 1 0-2h3c1.654 0 3-1.346 3-3v-3a1 1 0 1 1 2 0zM25 2h-3a1 1 0 1 0 0 2h3c1.654 0 3 1.346 3 3v3a1 1 0 1 0 2 0V7c0-2.757-2.243-5-5-5zM10 28H7c-1.654 0-3-1.346-3-3v-3a1 1 0 1 0-2 0v3c0 2.757 2.243 5 5 5h3a1 1 0 1 0 0-2zM3 11a1 1 0 0 0 1-1V7c0-1.654 1.346-3 3-3h3a1 1 0 1 0 0-2H7C4.243 2 2 4.243 2 7v3a1 1 0 0 0 1 1zm9.141 5.164a6.68 6.68 0 0 0-1.085.886A6.915 6.915 0 0 0 9 22a1 1 0 0 0 1 1h12.02a1 1 0 0 0 .977-1.215 6.9 6.9 0 0 0-2.044-4.726 6.736 6.736 0 0 0-1.094-.894A4.962 4.962 0 0 0 21 13c0-2.757-2.243-5-5-5s-5 2.243-5 5c0 1.178.416 2.284 1.141 3.164z"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_SteamidPlayer')}</p>
                                    <span>{$steam_player}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg x="0" y="0" viewBox="0 0 32 32" xml:space="preserve">
                                        <g>
                                            <path d="M30 6.93V11H2V6.93A2.969 2.969 0 0 1 5 4h2v2a3 3 0 0 0 6 0V4h6v2a3 3 0 0 0 6 0V4h2a2.969 2.969 0 0 1 3 2.93zM2 13v16.07A2.969 2.969 0 0 0 5 32h22a2.969 2.969 0 0 0 3-2.93V13zm9 14a1.003 1.003 0 0 1-1 1H8a1.003 1.003 0 0 1-1-1v-2a1.003 1.003 0 0 1 1-1h2a1.003 1.003 0 0 1 1 1zm0-7a1.003 1.003 0 0 1-1 1H8a1.003 1.003 0 0 1-1-1v-2a1.003 1.003 0 0 1 1-1h2a1.003 1.003 0 0 1 1 1zm7 7a1.003 1.003 0 0 1-1 1h-2a1.003 1.003 0 0 1-1-1v-2a1.003 1.003 0 0 1 1-1h2a1.003 1.003 0 0 1 1 1zm0-7a1.003 1.003 0 0 1-1 1h-2a1.003 1.003 0 0 1-1-1v-2a1.003 1.003 0 0 1 1-1h2a1.003 1.003 0 0 1 1 1zm7 0a1.003 1.003 0 0 1-1 1h-2a1.003 1.003 0 0 1-1-1v-2a1.003 1.003 0 0 1 1-1h2a1.003 1.003 0 0 1 1 1z"></path>
                                            <path d="M11 4v2a1 1 0 0 1-2 0V4a1 1 0 0 1 2 0zM23 4v2a1 1 0 0 1-2 0V4a1 1 0 0 1 2 0z"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_DatePunishment')}</p>
                                    <span>{$c_ban_time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg x="0" y="0" viewBox="0 0 512 512" xml:space="preserve">
                                        <g>
                                            <path d="M426.667 102.99V58.066C439.358 50.667 448 37.059 448 21.333V10.667A10.66 10.66 0 0 0 437.333 0H74.667A10.66 10.66 0 0 0 64 10.667v10.667c0 15.725 8.642 29.333 21.333 36.733v44.923c0 42.271 18.021 82.729 49.438 111L181.448 256l-46.677 42.01c-31.417 28.271-49.438 68.729-49.438 111v44.923C72.642 461.333 64 474.941 64 490.667v10.667A10.66 10.66 0 0 0 74.667 512h362.667a10.66 10.66 0 0 0 10.667-10.667v-10.667c0-15.725-8.642-29.333-21.333-36.733V409.01c0-42.271-18.021-82.729-49.438-111L330.552 256l46.677-42.01c31.417-28.271 49.438-68.73 49.438-111zm-77.979 79.291-64.292 57.865c-4.5 4.042-7.063 9.802-7.063 15.854s2.563 11.813 7.063 15.854l64.292 57.865C371.125 349.917 384 378.823 384 409.01V448h-26.672l-92.797-123.729c-4.021-5.375-13.042-5.375-17.063 0L154.672 448H128v-38.99c0-30.188 12.875-59.094 35.313-79.292l64.292-57.865c4.5-4.042 7.063-9.802 7.063-15.854s-2.563-11.813-7.063-15.854l-64.292-57.865C140.875 162.083 128 133.177 128 102.99V64h256v38.99c0 30.187-12.875 59.093-35.312 79.291z"></path>
                                            <path d="M329.521 149.333H182.469c-4.219 0-8.042 2.49-9.75 6.344a10.655 10.655 0 0 0 1.854 11.49l74.271 68.521c2.031 1.844 4.594 2.76 7.156 2.76s5.125-.917 7.156-2.76l74.26-68.521a10.654 10.654 0 0 0 1.854-11.49 10.665 10.665 0 0 0-9.749-6.344z"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_TermModal')}</p>
                                    <span>{$end_ban}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_admin}</a></div>
                    </div>
                HTML;
                } else if ($page == 'admins') {
                    if ($this->server_id != 'all') {
                        $admin_info = $this->Db->query('IksAdminNew', 0, 0, "SELECT 
                            `iks_admins`.`steam_id` AS `steamid`,
                            `iks_admins`.`immunity`,
                            `iks_admins`.`group_id`,
                            `iks_admins`.`end_at` AS `end`,
                            `iks_groups`.`name` AS `group`,
                            GROUP_CONCAT(DISTINCT `iks_admin_to_server`.`server_id`) AS `server_id`,
                            (SELECT COUNT(1) FROM `iks_bans` WHERE `admin_id` = `iks_admins`.`id`) AS `bans_count`,
                            (SELECT COUNT(1) FROM `iks_comms` WHERE `admin_id` = `iks_admins`.`id` AND (`mute_type` = 0 OR `mute_type` = 2)) AS `mutes_count`,
                            (SELECT COUNT(1) FROM `iks_comms` WHERE `admin_id` = `iks_admins`.`id` AND (`mute_type` = 1 OR `mute_type` = 2)) AS `gags_count`
                        FROM 
                            `iks_admins`
                        JOIN 
                            `iks_admin_to_server` ON `iks_admins`.`id` = `iks_admin_to_server`.`admin_id`
                        LEFT JOIN
                            `iks_groups` ON `iks_admins`.`group_id` = `iks_groups`.`id`
                        WHERE 
                            `iks_admins`.`id` = :admin_id
                            AND (`iks_admins`.`end_at` > UNIX_TIMESTAMP() OR `iks_admins`.`end_at` IS NULL)
                            AND (`iks_admin_to_server`.`server_id` = :server_id OR `iks_admin_to_server`.`server_id` IS NULL)
                        GROUP BY `iks_admins`.`group_id`", ['admin_id' => $id, 'server_id' => $this->server_id]);
                    } else {
                        $admin_info = $this->Db->query('IksAdminNew', 0, 0, "SELECT 
                            `iks_admins`.`steam_id` AS `steamid`,
                            `iks_admins`.`immunity`,
                            `iks_admins`.`group_id`,
                            `iks_admins`.`end_at` AS `end`,
                            `iks_groups`.`name` AS `group`,
                            GROUP_CONCAT(DISTINCT `iks_admin_to_server`.`server_id`) AS `server_id`,
                            (SELECT COUNT(1) FROM `iks_bans` WHERE `admin_id` = `iks_admins`.`id`) AS `bans_count`,
                            (SELECT COUNT(1) FROM `iks_comms` WHERE `admin_id` = `iks_admins`.`id` AND (`mute_type` = 0 OR `mute_type` = 2)) AS `mutes_count`,
                            (SELECT COUNT(1) FROM `iks_comms` WHERE `admin_id` = `iks_admins`.`id` AND (`mute_type` = 1 OR `mute_type` = 2)) AS `gags_count`
                        FROM 
                            `iks_admins`
                        JOIN 
                            `iks_admin_to_server` ON `iks_admins`.`id` = `iks_admin_to_server`.`admin_id`
                        LEFT JOIN
                            `iks_groups` ON `iks_admins`.`group_id` = `iks_groups`.`id`
                        WHERE 
                            `iks_admins`.`id` = :admin_id AND (`iks_admins`.`end_at` > UNIX_TIMESTAMP() OR `iks_admins`.`end_at` IS NULL)
                        GROUP BY `iks_admins`.`group_id`
                    ", ['admin_id' => $id]);
                    }
                    $group = $admin_info['group'];
                    if ($admin_info['end'] == 0) {
                        $time = $this->Translate->get_translate_phrase('_Forever');
                    } else {
                        $time = date('d.m.Y H:i', $admin_info['end']);
                    }
                    $steam = $admin_info['steamid'];
                    $Warns = ceil($this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `lvl_web_managersystem_warn` WHERE `steamid` = :steam AND `time` > UNIX_TIMESTAMP()", ['steam' => $steam])[0]);
                    $Check = ceil($this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `checkcheats_stats` WHERE `admin_steamid` = :steam", ['steam' => $steam])[0]);
                    if (file_exists(MODULES . 'module_page_reports/description.json')) {
                        $Rep = ceil($this->Db->queryNum('Reports', 0, 0, "SELECT COUNT(*) FROM `rs_reports` WHERE `steamid_admin_verdict` = :steam", ['steam' => $steam])[0]);
                    } else {
                        $Rep = 0;
                    }
                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_group')}</p>
                                    <span>{$group}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#time-expired"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_term')}</p>
                                    <span>{$time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#warning"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_warns')}</p>
                                    <span>{$Warns}/3</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#block"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_bansIssued')}</p>
                                    <span>{$admin_info['bans_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#micro-slash"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_mutesIssued')}</p>
                                    <span>{$admin_info['mutes_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#chat-slash"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_gagsIssued')}</p>
                                    <span>{$admin_info['gags_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#check-circle"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_passedChecks')}</p>
                                    <span>{$Check}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#report-list"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_reportsReviewed')}</p>
                                    <span>{$Rep}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <button class="width-100" onClick="window.open('https://steamcommunity.com/profiles/{$steam}');">
                            <svg><use href="/resources/img/sprite.svg#steam"></use></svg>
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_openProfile')} Steam
                        </button>
                    </div>
                HTML;
                }
            }
        } elseif ($this->game == 'csgo') {
            if (!empty($this->Db->db_data['SourceBans'])) {
                if ($page == 'bans') {
                    $modal = $this->Db->query('SourceBans', 0, 0, "SELECT *, (SELECT `user` as `name` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_bans`.`aid`) AS `admin_name`, (SELECT `authid` as `steamid` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_bans`.`aid`) AS `admin_steamid`, (SELECT `user` as `name` FROM `sb_admins` WHERE `sb_admins`.`aid` = `RemovedBy`) AS `unpunish_name`, (SELECT `authid` as `steamid` FROM `sb_admins` WHERE `sb_admins`.`aid` = `RemovedBy`) AS `unpunish_steamid` FROM `sb_bans` WHERE `bid` = :id LIMIT 1", ['id' => $id]);
                    $steam_player = con_steam64($modal['authid']);
                    $steam_admin = con_steam64($modal['admin_steamid']);
                    $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($modal['name']) : action_text_clear($this->General->checkName($steam_player));
                    if (!empty($steam_admin)) {
                        $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($modal['admin_name']) : action_text_clear($this->General->checkName($steam_admin));
                    } else {
                        $name_admin = $this->Translate->get_translate_module_phrase('module_page_punishment', '_console');
                    }
                    $unpunish = '';
                    $steam_unpunish = $modal['unpunish_steamid'];
                    $name_unpunish = empty($this->General->checkName($steam_unpunish)) ? action_text_clear($modal['unpunish_name']) : action_text_clear($this->General->checkName($steam_unpunish));
                    $JSONResult["modal"] = $modal;
                    if ($steam_unpunish != null) {
                        if (!empty($steam_unpunish)) {
                            $unpunish = <<<HTML
                            <div class="punish_take_admin">
                                {$this->Translate->get_translate_module_phrase('module_page_punishment', '_unpunish_By')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_unpunish}</a>
                            </div>
                        HTML;
                        } else {
                            $unpunish = <<<HTML
                            <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_unpunish_By')} - {$this->Translate->get_translate_module_phrase('module_page_punishment', '_console')}</div>
                        HTML;
                        }
                    }
                    if (!empty($steam_admin)) {
                        $IssuedBy = <<<HTML
                        <div class="punish_take_admin">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_admin}</a>
                        </div>
                    HTML;
                    } else {
                        $IssuedBy = <<<HTML
                        <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - {$this->Translate->get_translate_module_phrase('module_page_punishment', '_console')}</div>
                    HTML;
                    }
                    if ($modal['RemoveType'] == 'U') {
                        $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unbanned');
                        $modal_unban = false;
                    } elseif ($modal['length'] == '0' && $modal['RemoveType'] != 'U') {
                        $end_ban = $this->Translate->get_translate_phrase('_Forever');
                        $modal_unban = true;
                    } elseif (time() >= $modal['ends'] && $modal['length'] != '0') {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['ends'] - $modal['created']);
                        $modal_unban = false;
                    } else {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['ends'] - time());
                        $modal_unban = true;
                    }
                    $c_ban_time = date('d.m.Y H:i', $modal['created']);
                    $button_unabn_buy = '';
                    if ($this->GetSettings()['func_unban'] == 1 && $modal_unban) {
                        $button_unabn_buy = <<<HTML
                        <button class="punish_buy btn_unban" page="{$page}" idpunish="{$id}" sid="{$modal['authid']}" type="buy">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_BuyUnban')}
                            <span>{$this->GetSettings()['price_unban']} {$this->General->currency}</span>
                        </button>
                    HTML;
                    }
                    $button_unabn = '';
                    if ((file_exists(MODULES . 'module_page_managersystem/description.json') || isset($_SESSION['user_admin'])) && $modal_unban) {
                        if ($this->hasAdminAccess) {
                            $button_unabn = <<<HTML
                                <button class="width-100 btn_unban" page="{$page}" idpunish="{$id}" sid="{$modal['authid']}" type="admin">
                                    {$this->Translate->get_translate_module_phrase('module_page_punishment', '_RemovePunishButton')}
                                </button>
                            HTML;
                        }
                    }
                    if (!empty($button_unabn_buy) && !empty($button_unabn)) {
                        $div = <<<HTML
                        <div class="punish_buttons">
                    HTML;
                        $divc = <<<HTML
                        </div>
                    HTML;
                    }
                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        {$div}
                            {$button_unabn_buy}
                            {$button_unabn}
                        {$divc}
                        
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_PunishedPlayer')}</p>
                                    <span><a href="/profiles/{$steam_player}/?search=1" target="_blank">{$name_player}</a></span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#check-circle"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_SteamidPlayer')}</p>
                                    <span>{$steam_player}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#calendare"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_DatePunishment')}</p>
                                    <span>{$c_ban_time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#time-expired"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_TermModal')}</p>
                                    <span>{$end_ban}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        {$IssuedBy}
                        {$unpunish}
                    </div>
                HTML;
                } else if ($page == 'comms') {
                    $modal = $this->Db->query('SourceBans', 0, 0, "SELECT *, (SELECT `user` as `name` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_comms`.`aid`) AS `admin_name`, (SELECT `authid` as `steamid` FROM `sb_admins` WHERE `sb_admins`.`aid` = `sb_comms`.`aid`) AS `admin_steamid`, (SELECT `user` as `name` FROM `sb_admins` WHERE `sb_admins`.`aid` = `RemovedBy`) AS `unpunish_name`, (SELECT `authid` as `steamid` FROM `sb_admins` WHERE `sb_admins`.`aid` = `RemovedBy`) AS `unpunish_steamid` FROM `sb_comms` WHERE `bid` = :id LIMIT 1", ['id' => $id]);
                    $steam_player = con_steam64($modal['authid']);
                    $steam_admin = con_steam64($modal['admin_steamid']);
                    $name_player = empty($this->General->checkName($steam_player)) ? action_text_clear($modal['name']) : action_text_clear($this->General->checkName($steam_player));
                    if (!empty($steam_admin)) {
                        $name_admin = empty($this->General->checkName($steam_admin)) ? action_text_clear($modal['admin_name']) : action_text_clear($this->General->checkName($steam_admin));
                    } else {
                        $name_admin = $this->Translate->get_translate_module_phrase('module_page_punishment', '_console');
                    }
                    $unpunish = '';
                    $steam_unpunish = $modal['unpunish_steamid'];
                    $name_unpunish = empty($this->General->checkName($steam_unpunish)) ? action_text_clear($modal['unpunish_name']) : action_text_clear($this->General->checkName($steam_unpunish));
                    $JSONResult["modal"] = $modal;
                    if ($steam_unpunish != null) {
                        if (!empty($steam_unpunish)) {
                            $unpunish = <<<HTML
                            <div class="punish_take_admin">
                                {$this->Translate->get_translate_module_phrase('module_page_punishment', '_unpunish_By')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_unpunish}</a>
                            </div>
                        HTML;
                        } else {
                            $unpunish = <<<HTML
                            <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_unpunish_By')} - {$this->Translate->get_translate_module_phrase('module_page_punishment', '_console')}</div>
                        HTML;
                        }
                    }
                    if (!empty($steam_admin)) {
                        $IssuedBy = <<<HTML
                        <div class="punish_take_admin">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - <a href="/profiles/{$steam_admin}/?search=1" target="_blank">{$name_admin}</a>
                        </div>
                    HTML;
                    } else {
                        $IssuedBy = <<<HTML
                        <div class="punish_take_admin">{$this->Translate->get_translate_module_phrase('module_page_punishment', '_IssuedBy')} - {$this->Translate->get_translate_module_phrase('module_page_punishment', '_console')}</div>
                    HTML;
                    }
                    if ($modal['RemoveType'] == 'U') {
                        $end_ban = $this->Translate->get_translate_module_phrase('module_page_punishment', '_Unmuted');
                        $modal_unban = false;
                    } elseif ($modal['length'] == '0' && $modal['RemoveType'] != 'U') {
                        $end_ban = $this->Translate->get_translate_phrase('_Forever');
                        $modal_unban = true;
                    } elseif (time() >= $modal['ends'] && $modal['length'] != '0') {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['ends'] - $modal['created']);
                        $modal_unban = false;
                    } else {
                        $end_ban = $this->Modules->action_time_exchange_exact($modal['ends'] - time());
                        $modal_unban = true;
                    }
                    $c_ban_time = date('d.m.Y H:i', $modal['created']);
                    $button_unabn_buy = '';
                    if ($this->GetSettings()['func_unmute'] == 1 && $modal_unban) {
                        $button_unabn_buy = <<<HTML
                        <button class="punish_buy btn_unban" page="{$page}" idpunish="{$id}" sid="{$modal['authid']}" type="buy">
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_BuyUnban')}
                            <span>{$this->GetSettings()['price_unmute']} {$this->General->currency}</span>
                        </button>
                    HTML;
                    }
                    $button_unabn = '';
                    if ((file_exists(MODULES . 'module_page_managersystem/description.json') || isset($_SESSION['user_admin'])) && $modal_unban) {
                        if ($this->hasAdminAccess) {
                            $button_unabn = <<<HTML
                            <button class="width-100 btn_unban" page="{$page}" idpunish="{$id}" sid="{$modal['authid']}" type="admin">
                                {$this->Translate->get_translate_module_phrase('module_page_punishment', '_RemovePunishButton')}
                            </button>
                        HTML;
                        }
                    }
                    if (!empty($button_unabn_buy) && !empty($button_unabn)) {
                        $hr = <<<HTML
                        <hr>
                    HTML;
                    }
                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        <div class="punish_buttons">
                            {$button_unabn_buy}
                            {$button_unabn}
                        </div>
                        {$hr}
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_PunishedPlayer')}</p>
                                    <span><a href="/profiles/{$steam_player}/?search=1" target="_blank">{$name_player}</a></span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#check-circle"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_SteamidPlayer')}</p>
                                    <span>{$steam_player}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#calendare"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_DatePunishment')}</p>
                                    <span>{$c_ban_time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#time-expired"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_TermModal')}</p>
                                    <span>{$end_ban}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        {$IssuedBy}
                        {$unpunish}
                    </div>
                HTML;
                } else if ($page == 'admins') {
                    if ($this->server_id != 'all') {
                        $admin_info = $this->Db->query('SourceBans', 0, 0, "SELECT 
                            `sb_admins`.`authid` as `steamid`,
                            `sb_admins`.`srv_group` as `group`,
                            `sb_admins`.`immunity` as `immunity`,
                            `sb_admins`.`expired` AS `end`,
                            (SELECT COUNT(1) FROM `sb_bans` WHERE `sb_bans`.`aid` = `sb_admins`.`aid`) AS `bans_count`,
                            (SELECT COUNT(1) FROM `sb_comms` WHERE `sb_comms`.`aid` = `sb_admins`.`aid`) AS `mutes_count`,
                            (SELECT COUNT(1) FROM `sb_comms` WHERE `sb_comms`.`aid` = `sb_admins`.`aid`) AS `gags_count`
                        FROM `sb_admins`
                        INNER JOIN 
                            `sb_admins_servers_groups`
                        ON 
                            `sb_admins_servers_groups`.`admin_id` = `sb_admins`.`aid`
                        WHERE `sb_admins`.`aid` = :admin_id
                        AND (`sb_admins_servers_groups`.`server_id` = :server_id OR `sb_admins_servers_groups`.`server_id` = '-1')
                        AND (`sb_admins`.`expired` > UNIX_TIMESTAMP() OR `sb_admins`.`expired` = 0)
                        GROUP BY `sb_admins`.`aid`", ['admin_id' => $id, 'server_id' => $this->server_id]);
                    } else {
                        $admin_info = $this->Db->query('SourceBans', 0, 0, "SELECT 
                            `sb_admins`.`authid` as `steamid`,
                            `sb_admins`.`srv_group` as `group`,
                            `sb_admins`.`immunity` as `immunity`,
                            `sb_admins`.`expired` AS `end`,
                            (SELECT COUNT(1) FROM `sb_bans` WHERE `sb_bans`.`aid` = `sb_admins`.`aid`) AS `bans_count`,
                            (SELECT COUNT(1) FROM `sb_comms` WHERE `sb_comms`.`aid` = `sb_admins`.`aid`) AS `mutes_count`,
                            (SELECT COUNT(1) FROM `sb_comms` WHERE `sb_comms`.`aid` = `sb_admins`.`aid`) AS `gags_count`
                        FROM `sb_admins`
                        INNER JOIN 
                            `sb_admins_servers_groups`
                        ON 
                            `sb_admins_servers_groups`.`admin_id` = `sb_admins`.`aid`
                        WHERE `sb_admins`.`aid` = :admin_id
                        AND (`sb_admins`.`expired` > UNIX_TIMESTAMP() OR `sb_admins`.`expired` = 0)
                        GROUP BY `sb_admins`.`aid`", ['admin_id' => $id]);
                    }
                    $group = $admin_info['group'];
                    if ($admin_info['end'] == 0) {
                        $time = $this->Translate->get_translate_phrase('_Forever');
                    } else {
                        $time = date('d.m.Y H:i', $admin_info['end']);
                    }
                    $steam = con_steam64($admin_info['steamid']);
                    $Warns = ceil($this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `lvl_web_managersystem_warn` WHERE `steamid` = :steam AND `time` > UNIX_TIMESTAMP()", ['steam' => $steam])[0]);
                    $Check = ceil($this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `checkcheats_stats` WHERE `admin_steamid` = :steam", ['steam' => $steam])[0]);
                    if (file_exists(MODULES . 'module_page_reports/description.json')) {
                        $Rep = ceil($this->Db->queryNum('Reports', 0, 0, "SELECT COUNT(*) FROM `rs_reports` WHERE `steamid_admin_verdict` = :steam", ['steam' => $steam])[0]);
                    } else {
                        $Rep = 0;
                    }
                    $JSONResult["html_modal"] = <<<HTML
                    <div class="punish_body">
                        <div class="punish_info">
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_group')}</p>
                                    <span>{$group}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#time-expired"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_term')}</p>
                                    <span>{$time}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#warning"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_warns')}</p>
                                    <span>{$Warns}/3</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#block"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_bansIssued')}</p>
                                    <span>{$admin_info['bans_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#micro-slash"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_mutesIssued')}</p>
                                    <span>{$admin_info['mutes_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#chat-slash"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_gagsIssued')}</p>
                                    <span>{$admin_info['gags_count']}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#check-circle"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_passedChecks')}</p>
                                    <span>{$Check}</span>
                                </div>
                            </div>
                            <div class="punish_subinfo">
                                <div class="icon_subinfo">
                                    <svg><use href="/resources/img/sprite.svg#report-list"></use></svg>
                                </div>
                                <div class="text_subinfo">
                                    <p>{$this->Translate->get_translate_module_phrase('module_page_punishment', '_reportsReviewed')}</p>
                                    <span>{$Rep}</span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <button class="width-100" onClick="window.open('https://steamcommunity.com/profiles/{$steam}');">
                            <svg><use href="/resources/img/sprite.svg#steam"></use></svg>
                            {$this->Translate->get_translate_module_phrase('module_page_punishment', '_openProfile')} Steam
                        </button>
                    </div>
                HTML;
                }
            }
        }
        return $JSONResult;
    }

    public function PunishmentUnban($idpunish = null, $page, $type, $sid = null)
    {
        if ($type == 'admin' && !$this->hasAdminAccess) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAdmin')];
        } elseif ($type == 'buy' && $page == 'bans' && !$this->GetSettings()['func_unban']) {
            return ['status' => 'error', 'text' => 'Poshel nahuy'];
        } elseif ($type == 'buy' && $page == 'comms' && !$this->GetSettings()['func_unmute']) {
            return ['status' => 'error', 'text' => 'Poshel nahuy'];
        }
        if ($this->game == 'cs2') {
            if (!empty($this->Db->db_data['AdminSystem'])) {
                if ($page == 'bans') {
                    if ($type == 'buy') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearch = $this->Db->query('AdminSystem', 0, 0, "SELECT * FROM `as_punishments` WHERE `id` = :id AND `punish_type` = 0 AND (`expires` = 0 OR `expires` > UNIX_TIMESTAMP()) LIMIT 1", ['id' => $idpunish]);

                        if (empty($usersearch))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        if ($this->GetSettings()['price_unban'] > $this->GetBalance())
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_haventEnouh')];
                        $this->Db->query('AdminSystem', 0, 0, "UPDATE `as_punishments` SET `unpunish_admin_id` = 1 WHERE `id` = :id LIMIT 1", ['id' => $idpunish]);
                        $this->UpdateBalance($_SESSION['steamid32'], $this->GetSettings()['price_unban']);
                        $this->Rcons("mm_as_reload_punish " . $usersearch['steamid']);
                        $this->Notifications->SendNotification($_SESSION['steamid'], '_punishment', '_UnBan', ['module_translation' => 'module_page_punishment'], '', 'punish', '_Go');
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_buyUnban')];
                    } elseif ($type == 'admin') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearch = $this->Db->query('AdminSystem', 0, 0, "SELECT * FROM `as_punishments` WHERE `id` = :id AND (`expires` = 0 OR `expires` > UNIX_TIMESTAMP()) LIMIT 1", ['id' => $idpunish]);
                        if (empty($usersearch))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        $this->Db->query('AdminSystem', 0, 0, "UPDATE `as_punishments` SET `unpunish_admin_id` = 1 WHERE `id` = :id LIMIT 1", ['id' => $idpunish]);
                        $this->Rcons("mm_as_reload_punish " . $usersearch['steamid']);
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_adminUnban')];
                    }
                } elseif ($page == 'comms') {
                    if ($type == 'buy') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearchmute = $this->Db->query('AdminSystem', 0, 0, "SELECT * FROM `as_punishments` WHERE `id` = :id AND (`expires` = 0 OR `expires` > UNIX_TIMESTAMP()) LIMIT 1", ['id' => $idpunish]);
                        if (empty($usersearchmute))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        if ($this->GetSettings()['price_unmute'] > $this->GetBalance())
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_haventEnouh')];
                        $this->Db->queryAll('AdminSystem', 0, 0, "UPDATE `as_punishments` SET `unpunish_admin_id` = 1 WHERE `id` = :id", ['id' => $idpunish]);
                        $this->UpdateBalance($_SESSION['steamid32'], $this->GetSettings()['price_unmute']);
                        $this->Rcons("mm_as_reload_punish " . $usersearchmute['steamid']);
                        $this->Notifications->SendNotification($_SESSION['steamid'], '_punishment', '_UnMute', ['module_translation' => 'module_page_punishment'], '', 'punish', '_Go');
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_buyUnmute')];
                    } elseif ($type == 'admin') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearchmute = $this->Db->query('AdminSystem', 0, 0, "SELECT * FROM `as_punishments` WHERE `id` = :id AND (`expires` = 0 OR `expires` > UNIX_TIMESTAMP()) LIMIT 1", ['id' => $idpunish]);
                        if (empty($usersearchmute))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        $this->Db->query('AdminSystem', 0, 0, "UPDATE `as_punishments` SET `unpunish_admin_id` = 1 WHERE `id` = :id", ['id' => $idpunish]);
                        $this->Rcons("mm_as_reload_punish " . $usersearchmute['steamid']);
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_adminUnmute')];
                    }
                }
            } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
                if ($page == 'bans') {
                    if ($type == 'buy') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearch = $this->Db->query('IksAdminNew', 0, 0, "SELECT * FROM `iks_bans` WHERE `id` = :id AND (`end_at` = 0 OR `end_at` > UNIX_TIMESTAMP() AND `unbanned_by` IS NULL) LIMIT 1", ['id' => $idpunish]);
                        if (empty($usersearch))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        if ($this->GetSettings()['price_unban'] > $this->GetBalance())
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_haventEnouh')];
                        $this->Db->query('IksAdminNew', 0, 0, "UPDATE `iks_bans` SET `unbanned_by` = 1 WHERE `id` = :id LIMIT 1", ['id' => $idpunish]);
                        $this->UpdateBalance($_SESSION['steamid32'], $this->GetSettings()['price_unban']);
                        $this->Rcons("css_reload_infractions {$sid}");
                        $this->Notifications->SendNotification($_SESSION['steamid'], '_punishment', '_UnBan', ['module_translation' => 'module_page_punishment'], '', 'punish', '_Go');
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_buyUnban')];
                    } elseif ($type == 'admin') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearch = $this->Db->query('IksAdminNew', 0, 0, "SELECT * FROM `iks_bans` WHERE `id` = :id AND (`end_at` = 0 OR `end_at` > UNIX_TIMESTAMP() AND `unbanned_by` IS NULL) LIMIT 1", ['id' => $idpunish]);
                        if (empty($usersearch))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        $this->Db->query('IksAdminNew', 0, 0, "UPDATE `iks_bans` SET `unbanned_by` = 1 WHERE `id` = :id LIMIT 1", ['id' => $idpunish]);
                        $this->Rcons("css_reload_infractions {$sid}");
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_adminUnban')];
                    }
                } elseif ($page == 'comms') {
                    if ($type == 'buy') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearchmute = $this->Db->query('IksAdminNew', 0, 0, "SELECT * FROM `iks_comms` WHERE `id` = :id AND (`end_at` = 0 OR `end_at` > UNIX_TIMESTAMP() AND `unbanned_by` IS NULL) LIMIT 1", ['id' => $idpunish]);
                        $user = reset($usersearchmute);
                        if (empty($usersearchmute))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        if ($this->GetSettings()['price_unmute'] > $this->GetBalance())
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_haventEnouh')];
                        $this->Db->query('IksAdminNew', 0, 0, "UPDATE `iks_comms` SET `unbanned_by` = 1 WHERE `id` = :id", ['id' => $idpunish]);
                        $this->UpdateBalance($_SESSION['steamid32'], $this->GetSettings()['price_unmute']);
                        $this->Rcons("css_reload_infractions " . $user['steam_id']);
                        $this->Notifications->SendNotification($_SESSION['steamid'], '_punishment', '_UnMute', ['module_translation' => 'module_page_punishment'], '', 'punish', '_Go');
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_buyUnmute')];
                    } elseif ($type == 'admin') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearchmute = $this->Db->query('IksAdminNew', 0, 0, "SELECT * FROM `iks_comms` WHERE `id` = :id AND (`end_at` = 0 OR `end_at` > UNIX_TIMESTAMP() AND `unbanned_by` IS NULL) LIMIT 1", ['id' => $idpunish]);
                        $user = reset($usersearchmute);
                        if (empty($usersearchmute))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        $this->Db->queryAll('IksAdminNew', 0, 0, "UPDATE `iks_comms` SET `unbanned_by` = 1 WHERE `id` = :id", ['id' => $idpunish]);
                        $this->Rcons("css_reload_infractions " . $user['steam_id']);
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_adminUnmute')];
                    }
                }
            } elseif (!empty($this->Db->db_data['IksAdmin'])) {
                if ($page == 'bans') {
                    if ($type == 'buy') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearch = $this->Db->query('IksAdmin', 0, 0, "SELECT * FROM `iks_bans` WHERE `id` = :id AND (`time` = 0 OR `end` > UNIX_TIMESTAMP() AND `Unbanned` = 0) LIMIT 1", ['id' => $idpunish]);
                        if (empty($usersearch))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        if ($this->GetSettings()['price_unban'] > $this->GetBalance())
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_haventEnouh')];
                        $this->Db->query('IksAdmin', 0, 0, "UPDATE `iks_bans` SET `Unbanned` = 1, `UnbannedBy` = :UnbannedBy WHERE `id` = :id LIMIT 1", ['id' => $idpunish, 'UnbannedBy' => $_SESSION['steamid64']]);
                        $this->UpdateBalance($_SESSION['steamid32'], $this->GetSettings()['price_unban']);
                        $this->Rcons("css_reload_infractions {$sid}");
                        $this->Notifications->SendNotification($_SESSION['steamid'], '_punishment', '_UnBan', ['module_translation' => 'module_page_punishment'], '', 'punish', '_Go');
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_buyUnban')];
                    } elseif ($type == 'admin') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearch = $this->Db->query('IksAdmin', 0, 0, "SELECT * FROM `iks_bans` WHERE `id` = :id AND (`time` = 0 OR `end` > UNIX_TIMESTAMP() AND `Unbanned` = 0) LIMIT 1", ['id' => $idpunish]);
                        if (empty($usersearch))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        $this->Db->query('IksAdmin', 0, 0, "UPDATE `iks_bans` SET `Unbanned` = 1, `UnbannedBy` = :UnbannedBy WHERE `id` = :id LIMIT 1", ['id' => $idpunish, 'UnbannedBy' => $_SESSION['steamid64']]);
                        $this->Rcons("css_reload_infractions {$sid}");
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_adminUnban')];
                    }
                } elseif ($page == 'comms') {
                    if ($type == 'buy') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearchmute = $this->Db->query('IksAdmin', 0, 0, "SELECT * FROM `iks_mutes` WHERE `sid` = :steam AND (`time` = 0 OR `end` > UNIX_TIMESTAMP() AND `Unbanned` = 0) LIMIT 1", ['steam' => $sid]);
                        $usersearchgag = $this->Db->query('IksAdmin', 0, 0, "SELECT * FROM `iks_gags` WHERE `sid` = :steam AND (`time` = 0 OR `end` > UNIX_TIMESTAMP() AND `Unbanned` = 0) LIMIT 1", ['steam' => $sid]);
                        if (empty($usersearchmute) && empty($usersearchgag))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        if ($this->GetSettings()['price_unmute'] > $this->GetBalance())
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_haventEnouh')];
                        $MuteType = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT COUNT(*) as count FROM `iks_mutes` WHERE `sid` = :steam", ['steam' => $sid]);
                        $ChatType = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT COUNT(*) as count FROM `iks_gags` WHERE `sid` = :steam", ['steam' => $sid]);

                        if ($MuteType[0]['count'] > 0 && $ChatType[0]['count'] > 0) {
                            $this->Db->queryAll('IksAdmin', 0, 0, "UPDATE `iks_mutes` SET `Unbanned` = 1, `UnbannedBy` = :UnbannedBy WHERE `sid` = :steam AND `end` = :end", ['steam' => $sid, 'end' => $usersearchmute['end'], 'UnbannedBy' => $_SESSION['steamid64']]);
                            $this->Db->queryAll('IksAdmin', 0, 0, "UPDATE `iks_gags` SET `Unbanned` = 1, `UnbannedBy` = :UnbannedBy WHERE `sid` = :steam AND `end` = :end", ['steam' => $sid, 'end' => $usersearchgag['end'], 'UnbannedBy' => $_SESSION['steamid64']]);
                            $this->UpdateBalance($_SESSION['steamid32'], $this->GetSettings()['price_unmute']);
                            $this->Rcons("css_reload_infractions {$sid}");
                        } elseif ($MuteType[0]['count'] > 0) {
                            $this->Db->query('IksAdmin', 0, 0, "UPDATE `iks_mutes` SET `Unbanned` = 1, `UnbannedBy` = :UnbannedBy WHERE `sid` = :steam AND `end` = :end", ['steam' => $sid, 'end' => $usersearchmute['end'], 'UnbannedBy' => $_SESSION['steamid64']]);
                            $this->UpdateBalance($_SESSION['steamid32'], $this->GetSettings()['price_unmute']);
                            $this->Rcons("css_reload_infractions {$sid}");
                        } elseif ($ChatType[0]['count'] > 0) {
                            $this->Db->query('IksAdmin', 0, 0, "UPDATE `iks_gags` SET `Unbanned` = 1, `UnbannedBy` = :UnbannedBy WHERE `sid` = :steam AND `end` = :end", ['steam' => $sid, 'end' => $usersearchgag['end'], 'UnbannedBy' => $_SESSION['steamid64']]);
                            $this->UpdateBalance($_SESSION['steamid32'], $this->GetSettings()['price_unmute']);
                            $this->Rcons("css_reload_infractions {$sid}");
                        }
                        $this->Notifications->SendNotification($_SESSION['steamid'], '_punishment', '_UnMute', ['module_translation' => 'module_page_punishment'], '', 'punish', '_Go');
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_buyUnmute')];
                    } elseif ($type == 'admin') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearchmute = $this->Db->query('IksAdmin', 0, 0, "SELECT * FROM `iks_mutes` WHERE `sid` = :steam AND (`time` = 0 OR `end` > UNIX_TIMESTAMP() AND `Unbanned` = 0) LIMIT 1", ['steam' => $sid]);
                        $usersearchgag = $this->Db->query('IksAdmin', 0, 0, "SELECT * FROM `iks_gags` WHERE `sid` = :steam AND (`time` = 0 OR `end` > UNIX_TIMESTAMP() AND `Unbanned` = 0) LIMIT 1", ['steam' => $sid]);
                        if (empty($usersearchmute) && empty($usersearchgag))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        $MuteType = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT COUNT(*) as count FROM `iks_mutes` WHERE `sid` = :steam", ['steam' => $sid]);
                        $ChatType = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT COUNT(*) as count FROM `iks_gags` WHERE `sid` = :steam", ['steam' => $sid]);

                        if ($MuteType[0]['count'] > 0 && $ChatType[0]['count'] > 0) {
                            $this->Db->queryAll('IksAdmin', 0, 0, "UPDATE `iks_mutes` SET `Unbanned` = 1, `UnbannedBy` = :UnbannedBy WHERE `sid` = :steam AND `end` = :end", ['steam' => $sid, 'end' => $usersearchmute['end'], 'UnbannedBy' => $_SESSION['steamid64']]);
                            $this->Db->queryAll('IksAdmin', 0, 0, "UPDATE `iks_gags` SET `Unbanned` = 1, `UnbannedBy` = :UnbannedBy WHERE `sid` = :steam AND `end` = :end", ['steam' => $sid, 'end' => $usersearchgag['end'], 'UnbannedBy' => $_SESSION['steamid64']]);
                            $this->Rcons("css_reload_infractions {$sid}");
                        } elseif ($MuteType[0]['count'] > 0) {
                            $this->Db->query('IksAdmin', 0, 0, "UPDATE `iks_mutes` SET `Unbanned` = 1, `UnbannedBy` = :UnbannedBy WHERE `sid` = :steam AND `end` = :end", ['steam' => $sid, 'end' => $usersearchmute['end'], 'UnbannedBy' => $_SESSION['steamid64']]);
                            $this->Rcons("css_reload_infractions {$sid}");
                        } elseif ($ChatType[0]['count'] > 0) {
                            $this->Db->query('IksAdmin', 0, 0, "UPDATE `iks_gags` SET `Unbanned` = 1, `UnbannedBy` = :UnbannedBy WHERE `sid` = :steam AND `end` = :end", ['steam' => $sid, 'end' => $usersearchgag['end'], 'UnbannedBy' => $_SESSION['steamid64']]);
                            $this->Rcons("css_reload_infractions {$sid}");
                        }
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_adminUnmute')];
                    }
                }
            }
        } elseif ($this->game == 'csgo') {
            if (!empty($this->Db->db_data['SourceBans'])) {
                if ($page == 'bans') {
                    if ($type == 'buy') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearch = $this->Db->query('SourceBans', 0, 0, "SELECT * FROM `sb_bans` WHERE `bid` = :id AND (`length` = 0 OR `ends` > UNIX_TIMESTAMP()) LIMIT 1", ['id' => $idpunish]);
                        if (empty($usersearch))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        if ($this->GetSettings()['price_unban'] > $this->GetBalance())
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_haventEnouh')];
                        $this->Db->query('SourceBans', 0, 0, "UPDATE `sb_bans` SET `RemovedBy` = 0, `RemoveType` = 'U' WHERE `bid` = :id LIMIT 1", ['id' => $idpunish]);
                        $this->UpdateBalance($_SESSION['steamid32'], $this->GetSettings()['price_unban']);
                        $this->Notifications->SendNotification($_SESSION['steamid'], '_punishment', '_UnBan', ['module_translation' => 'module_page_punishment'], '', 'punish', '_Go');
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_buyUnban')];
                    } elseif ($type == 'admin') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearch = $this->Db->query('SourceBans', 0, 0, "SELECT * FROM `sb_bans` WHERE `bid` = :id AND (`length` = 0 OR `ends` > UNIX_TIMESTAMP()) LIMIT 1", ['id' => $idpunish]);
                        if (empty($usersearch))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        $this->Db->query('SourceBans', 0, 0, "UPDATE `sb_bans` SET `RemovedBy` = 0, `RemoveType` = 'U' WHERE `bid` = :id LIMIT 1", ['id' => $idpunish]);
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_adminUnban')];
                    }
                } elseif ($page == 'comms') {
                    if ($type == 'buy') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearchmute = $this->Db->query('SourceBans', 0, 0, "SELECT * FROM `sb_comms` WHERE `bid` = :id AND (`length` = 0 OR `ends` > UNIX_TIMESTAMP()) LIMIT 1", ['id' => $idpunish]);
                        if (empty($usersearchmute))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        if ($this->GetSettings()['price_unmute'] > $this->GetBalance())
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_haventEnouh')];
                        $this->Db->queryAll('SourceBans', 0, 0, "UPDATE `sb_comms` SET `RemovedBy` = 0, `RemoveType` = 'U' WHERE `bid` = :id", ['id' => $idpunish]);
                        $this->UpdateBalance($_SESSION['steamid32'], $this->GetSettings()['price_unmute']);
                        $this->Notifications->SendNotification($_SESSION['steamid'], '_punishment', '_UnMute', ['module_translation' => 'module_page_punishment'], '', 'punish', '_Go');
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_buyUnmute')];
                    } elseif ($type == 'admin') {
                        if (empty($_SESSION['steamid64']))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notAuth')];
                        $usersearchmute = $this->Db->query('SourceBans', 0, 0, "SELECT * FROM `sb_comms` WHERE `bid` = :id AND (`length` = 0 OR `ends` > UNIX_TIMESTAMP()) LIMIT 1", ['id' => $idpunish]);
                        if (empty($usersearchmute))
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_notValid')];
                        $this->Db->query('SourceBans', 0, 0, "UPDATE `sb_comms` SET `RemovedBy` = 0, `RemoveType` = 'U' WHERE `bid` = :id", ['id' => $idpunish]);
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_adminUnmute')];
                    }
                }
            }
        }
    }

    public function hasAdminAccess()
    {
        $Access = $this->Db->query('Core', 0, 0, "SELECT `id`, `steamid_access`, `add_admin_access`, `add_ban_access`, `add_mute_access`, `add_vip_access`, `add_warn_access`, `add_timecheck_access`, `add_access` FROM `lvl_web_managersystem_access` WHERE `steamid_access` = :steamid", ['steamid' => $_SESSION['steamid64']]);
        $AdminAccess = false;
        if (
            isset($_SESSION['user_admin']) && !empty($_SESSION['user_admin'])
        ) {
            $AdminAccess = true;
        }

        if (
            isset($Access['steamid_access']) &&
            (
                (!empty($Access['add_ban_access']) && $Access['add_ban_access'] == 1) ||
                (!empty($Access['add_mute_access']) && $Access['add_mute_access'] == 1)
            )
        ) {
            $AdminAccess = true;
        }
        return $AdminAccess;
    }

    public function AllPlayTime($steam)
    {
        $total_time = 0;
        for ($i = 0; $i < $this->Db->table_count['LevelsRanks']; $i++) {
            $stats = $this->Db->queryAll('LevelsRanks', $this->Db->db_data['LevelsRanks'][$i]['USER_ID'], $this->Db->db_data['LevelsRanks'][$i]['DB_num'], "SELECT `steam`, `playtime` FROM `" . $this->Db->db_data['LevelsRanks'][$i]['Table'] . "` WHERE `steam`= :steam LIMIT 1", ['steam' => con_steam32($steam)]);
            $total_time += $stats[0]['playtime'];
        }
        return floor($total_time / 3600);
    }

    public function VoteAdmin($post)
    {
        $steamid = $_SESSION['steamid64'];
        $type = $post['type'];
        $admin_steam = $post['steamid'];

        if (isset($this->GetSettings()['func_like']) && $this->GetSettings()['func_like'] == 1) {
            if ($this->AllPlayTime($steamid) < $this->GetSettings()['hoursToLike']) {
                return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_fewHours')];
            }
        }

        if (!$steamid || !$admin_steam || !in_array($type, ['like', 'dislike'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_errorIziToast')];
        }

        $check = $this->Db->query('Core', 0, 0, "SELECT * FROM `lvl_web_admins_ratingvotes` WHERE `steamid` = :steamid AND `admin_steamid` = :admin", [
            'steamid' => $steamid,
            'admin' => $admin_steam
        ]);

        $exists = $this->Db->query('Core', 0, 0, "SELECT * FROM `lvl_web_admins_rating` WHERE `steamid` = :steamid", [
            'steamid' => $admin_steam
        ]);

        if ($check && $check['vote_type'] === $type) {
            $column = $type === 'like' ? 'likes' : 'dislikes';
            $this->Db->query('Core', 0, 0, "UPDATE `lvl_web_admins_rating` SET `$column` = GREATEST(`$column` - 1, 0) WHERE `steamid` = :steamid", [
                'steamid' => $admin_steam
            ]);
            $this->Db->query('Core', 0, 0, "DELETE FROM `lvl_web_admins_ratingvotes` WHERE `steamid` = :steamid AND `admin_steamid` = :admin", [
                'steamid' => $steamid,
                'admin' => $admin_steam
            ]);
            return ['status' => 'success', 'action' => 'removed', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_voteRemove')];
        }

        if ($check) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_votedAlready')];
        }

        if (!$exists) {
            $likes = $type === 'like' ? 1 : 0;
            $dislikes = $type === 'dislike' ? 1 : 0;
            $this->Db->query('Core', 0, 0, "INSERT INTO `lvl_web_admins_rating` (`steamid`, `likes`, `dislikes`) VALUES (:steamid, :likes, :dislikes)", [
                'steamid' => $admin_steam,
                'likes' => $likes,
                'dislikes' => $dislikes
            ]);
        } else {
            $column = $type === 'like' ? 'likes' : 'dislikes';
            $this->Db->query('Core', 0, 0, "UPDATE `lvl_web_admins_rating` SET `$column` = `$column` + 1 WHERE `steamid` = :steamid", [
                'steamid' => $admin_steam
            ]);
        }

        $this->Db->query('Core', 0, 0, "INSERT INTO `lvl_web_admins_ratingvotes` (`steamid`, `admin_steamid`, `vote_type`, `vote_time`) VALUES (:voter, :admin, :type, UNIX_TIMESTAMP())", [
            'voter' => $steamid,
            'admin' => $admin_steam,
            'type' => $type
        ]);

        return ['status' => 'success', 'action' => 'added', 'text' => $this->Translate->get_translate_module_phrase('module_page_punishment', '_voteCounted')];
    }


    public function installTables()
    {
        $this->Db->query('Core', 0, 0, "CREATE TABLE IF NOT EXISTS `lvl_web_admins_rating` (
            `steamid` VARCHAR(20) NOT NULL,
            `likes` INT UNSIGNED NOT NULL DEFAULT 0,
            `dislikes` INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`steamid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $this->Db->query('Core', 0, 0, "CREATE TABLE IF NOT EXISTS `lvl_web_admins_ratingvotes` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `steamid` VARCHAR(20) DEFAULT NULL,
            `admin_steamid` VARCHAR(20) DEFAULT NULL,
            `vote_type` ENUM('like','dislike') DEFAULT NULL,
            `vote_time` VARCHAR(20) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_vote` (`steamid`, `admin_steamid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;");
        return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully')];
    }
}
