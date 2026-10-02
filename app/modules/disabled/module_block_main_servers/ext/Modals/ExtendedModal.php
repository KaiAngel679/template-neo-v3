<?php

namespace app\modules\module_block_main_servers\ext\Modals;

class ExtendedModal
{
    public object $General;
    private object $Db;
    private array $Settings;
    private object $Translate;

    public function __construct(object $General, object $Db, object $Translate)
    {
        $this->General = $General;
        $this->Db = $Db;
        $this->Settings = $this->getSettings();
        $this->Translate = $Translate;
    }

    private function getSettings(): array
    {
        return file_exists($_SERVER['DOCUMENT_ROOT'] . '/app/modules/module_page_mon_settings/settings.php') ? require $_SERVER['DOCUMENT_ROOT'] . '/app/modules/module_page_mon_settings/settings.php' : [];
    }

    public function Render(array $data, array $server)
    {
        $db_info = explode(';', $server['server_stats']);
        if (empty($db_info) || !isset($db_info[0]) || !isset($db_info[1])) {
            $rank_pack = 'default';
        } else {
            $rank_pack = $this->Db->db_data[$db_info[0]][$db_info[1]]['ranks_pack'];
        }
        $game = $server['server_game'] ?? 'cs2';
        if (empty($data)) {
            $data = $this->Empty();
        } else {
            $data = $this->Data($data, $server, $rank_pack, $game);
        }

        return $data;
    }

    private function Empty()
    {
        return [
            'admins' => 0,
            'winteam' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_unknown'),
            'score_ct' => 0,
            'score_t' => 0,
            'players' => []
        ];
    }

    private function Data(array $data, array $server, string $rank_pack, string $game)
    {
        $data['winteam'] = $this->WinTeam($data['score_ct'], $data['score_t']);

        if (empty($data['players'])) {
            $data['admins'] = 0;
            $data['players'] = [];
        } else {
            $sql_vip_ids = [];
            $sql_admin_ids = [];
            $sql_admin_ids32 = [];

            foreach ($data['players'] as $player) {
                $sql_vip_ids[] = con_steam64to3_int($player['steamid']);
                $sql_admin_ids[] = $player['steamid'];
                $sql_admin_ids32[] = con_steam32($player['steamid']);
            }

            $ids64 = implode(', ', $sql_admin_ids);
            $ids32 = implode(', ', array_map(function ($id) {
                return "'" . addslashes(substr($id, 8)) . "'";
            }, $sql_admin_ids32));

            $vip_data = $this->getVips(explode(';', $server['server_vip']), $server['server_vip_id'], implode(', ', $sql_vip_ids));
            $admin_data = $this->getAdmins(explode(';', $server['server_sb']), $server['server_sb_id'], $ids64, $ids32, $game);
            $data['players'] = $this->Table($data['players'], $rank_pack, $vip_data, $admin_data, $game);
            $data['admins'] = count($admin_data);
            $data['game'] = $game;
        }

        return $data;
    }

    private function WinTeam(int $ct, int $t)
    {
        if (!is_numeric($ct) || !is_numeric($t)) {
            return $this->Translate->get_translate_module_phrase('module_block_main_servers', '_scoreLoad');
        }
        if ($ct > $t) {
            return $this->Translate->get_translate_module_phrase('module_block_main_servers', '_ctWin');
        } elseif ($ct < $t) {
            return $this->Translate->get_translate_module_phrase('module_block_main_servers', '_tWin');
        } elseif ($ct == $t) {
            return $this->Translate->get_translate_module_phrase('module_block_main_servers', '_draw');
        }
        return $this->Translate->get_translate_module_phrase('module_block_main_servers', '_scoreLoad');
    }

    private function Table(array $players, string $rank_pack, array $vip_data, array $admin_data, string $game)
    {
        usort($players, fn($a, $b) => $b['kills'] - $a['kills']);
        $rows = [];

        foreach ($players as $player) {
            $rows[] = $this->Player($player, $rank_pack, $vip_data, $admin_data, $game);
        }

        return $rows;
    }

    private function Player(array $player, string $rank_pack, array $vip_data, array $admin_data, string $game)
    {
        switch ($player['team']) {
            case 2:
                $team = 'class="t-side"';
                break;
            case 3:
                $team = 'class="ct-side"';
                break;
            default:
                $team = '';
                break;
        }
        $faceit = empty($player['faceit_level']) ? '/storage/cache/img/faceit/none.svg' : '/storage/cache/img/faceit/' . $player['faceit_level'] . '.svg';
        $prime = $this->Prime($player);
        $admin = $this->Admin($player, $admin_data, $game);
        $vip = $this->Vip($player, $vip_data);
        $kd = empty($player['death']) ? $player['kills'] : round($player['kills'] / $player['death'], 2);
        $hs = ($player['kills'] == 0 || $player['headshots'] == 0) ? 0 : round(($player['headshots'] / $player['kills']) * 100, 0);
        $name = action_text_clear($player['name']);
        $rank = getRankImage($player['rank'], $player['rank'], $rank_pack, 'modal-card__body-rang');
        return [
            'team' => $team,
            'id' => $player['userid'],
            'steamid' => $player['steamid'],
            'faceit' => $faceit,
            'prime' => $prime,
            'admin' => $admin,
            'vip' => $vip,
            'kills' => $player['kills'],
            'kd' => $kd,
            'hs' => $hs,
            'name' => $name,
            'rank' => $rank,
            'playtime' => $this->convertSeconds($player['playtime']),
            'ping' => $player['ping'],
        ];
    }

    private function Prime(array $player): bool
    {
        if (!$this->Settings['prime'])
            return false;
        $status = isset($player['prime']) && $player['prime'] ? true : false;
        return $status;
    }

    private function getVips(array $data, string $id, string $ids): array
    {
        if (!empty($this->Db->db_data['Vips'])) {
            return $this->Db->queryAll($data[0], $data[1], $data[2], "SELECT * FROM `" . $data[3] . "users` WHERE `account_id` IN (" . $ids . ") AND `sid` = '" . $id . "'");
        } else {
            return [];
        }
    }

    private function getAdmins(array $data, string $id, string $ids64, string $ids32, string $game = 'cs2')
    {
        if ($game == 'cs2') {


            if (!empty($this->Db->db_data['AdminSystem']) && $data[0] == 'AdminSystem'):
                return $this->Db->queryAll($data[0], $data[1], $data[2], "SELECT 
                a.steamid,
                GROUP_CONCAT(DISTINCT g.name ORDER BY g.immunity DESC SEPARATOR ', ') AS group_names
            FROM 
                as_admins a
            JOIN 
                as_admins_servers s ON a.id = s.admin_id
            JOIN 
                as_groups g ON s.group_id = g.id
            WHERE 
                a.steamid IN (" . $ids64 . ")
                AND (s.expires > UNIX_TIMESTAMP() OR s.expires = 0)
                AND (s.server_id = '" . $id . "' OR s.server_id = -1)
            GROUP BY  a.steamid ORDER BY MAX(g.immunity) DESC;");
            elseif (!empty($this->Db->db_data['IksAdminNew']) && $data[0] == 'IksAdminNew'):
                return $this->Db->queryAll($data[0], $data[1], $data[2], "SELECT 
                    a.steam_id AS steamid,
                    GROUP_CONCAT(DISTINCT g.name ORDER BY g.immunity DESC SEPARATOR ', ') AS group_names,
                    s.server_id AS server
                FROM 
                    iks_admins a
                JOIN 
                    iks_admin_to_server s ON a.id = s.admin_id
                JOIN 
                    iks_groups g ON a.group_id = g.id
                WHERE
                    a.steam_id IN (" . $ids64 . ")
                    AND a.is_disabled = 0
                    AND (s.server_id = '" . $id . "' OR s.server_id IS NULL)
                    AND (a.end_at > UNIX_TIMESTAMP() OR a.end_at IS NULL)
                GROUP BY a.steam_id, s.server_id;");
            elseif (!empty($this->Db->db_data['IksAdmin']) && $data[0] == 'IksAdmin'):
                return $this->Db->queryAll($data[0], $data[1], $data[2], "SELECT 
                    a.sid AS steamid,
                    GROUP_CONCAT(DISTINCT g.name ORDER BY g.immunity DESC SEPARATOR ', ') AS group_names,
                    GROUP_CONCAT(DISTINCT a.server_id SEPARATOR ', ') AS servers
                FROM 
                    iks_admins a
                JOIN 
                    iks_groups g ON a.group_id = g.id
                WHERE 
                    a.sid IN (" . $ids64 . ")
                    AND (a.end = 0 OR a.end > UNIX_TIMESTAMP())
                    AND (
                        a.server_id LIKE '%" . $id . "%' 
                        OR a.server_id = ''
                    )
                GROUP BY a.sid;");
            endif;
        } elseif ($game == 'csgo') {
            if (!empty($this->Db->db_data['SourceBans']) && $data[0] == 'SourceBans'):
                return $this->Db->queryAll($data[0], $data[1], $data[2], "SELECT 
                    a.authid as steamid,
                    a.srv_group AS group_names
                FROM 
                    sb_admins a
                JOIN 
                    sb_admins_servers_groups s ON a.aid = s.admin_id
                WHERE 
                    SUBSTRING(a.authid, 9) IN (" . $ids32 . ")
                    AND (a.expired > UNIX_TIMESTAMP() OR a.expired = 0)
                    AND (s.server_id = '{$id}' OR s.server_id = -1)
                GROUP BY  a.authid ORDER BY MAX(a.immunity) DESC;");
            endif;
        } else {
            return [];
        }
        return [];
    }

    private function Admin(array $player, array $array, string $game): string
    {
        $searchId = $player['steamid'];
        $found = array_filter($array, function ($row) use ($searchId, $game) {
            if ($game == 'cs2') {
                return $row['steamid'] === $searchId;
            } elseif ($game == 'csgo') {
                return substr($row['steamid'], 8) === substr(con_steam32($searchId), 8);
            }
        });
        $group = array_values($found)[0]['group_names'] ?? '';
        if ($this->Settings['admin'] != 1 || empty($group)) {
            return '';
        }
        return $group;
    }

    private function Vip(array $player, array $array): string
    {
        $searchId = con_steam64to3_int($player['steamid']);
        $found = array_filter($array, function ($row) use ($searchId) {
            return $row['account_id'] === $searchId;
        });
        $group = array_values($found)[0]['group'] ?? '';

        if ($this->Settings['vip'] != 1 || empty($group)) {
            return '';
        }
        return $group;
    }

    private function convertSeconds(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
    }
}
