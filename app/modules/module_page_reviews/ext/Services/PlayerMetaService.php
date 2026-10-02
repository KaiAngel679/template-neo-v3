<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\ModuleHelper;

class PlayerMetaService
{
    private $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function badgesForSteamids(array $steamids64): array
    {
        $ids = [];
        foreach ($steamids64 as $steamid) {
            $id = ModuleHelper::toSteam64((string) $steamid);
            if ($id !== '' && $id !== '0') {
                $ids[$id] = true;
            }
        }
        $ids = array_keys($ids);
        $out = [];
        foreach ($ids as $id) {
            $out[$id] = [
                'site_admin' => false,
                'admin' => false,
                'admin_group' => '',
                'vip' => false,
                'vip_group' => '',
            ];
        }
        if ($ids === []) {
            return $out;
        }

        $this->fillSiteAdmins($out, $ids);
        $this->fillServerAdmins($out, $ids);
        $this->fillVips($out, $ids);

        return $out;
    }

    public function badgesForSteam(string $steamid64): array
    {
        $map = $this->badgesForSteamids([$steamid64]);
        $id = ModuleHelper::toSteam64($steamid64);

        return $map[$id] ?? [
            'site_admin' => false,
            'admin' => false,
            'admin_group' => '',
            'vip' => false,
            'vip_group' => '',
        ];
    }

    private function markAdmin(array &$out, string $steamid64, string $groupName = ''): void
    {
        if (!isset($out[$steamid64])) {
            return;
        }
        $out[$steamid64]['admin'] = true;
        $groupName = trim($groupName);
        if ($groupName !== '' && $out[$steamid64]['admin_group'] === '') {
            $out[$steamid64]['admin_group'] = action_text_clear($groupName);
        }
    }

    private function fillSiteAdmins(array &$out, array $ids): void
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT `steamid` FROM `lvl_web_admins` WHERE `steamid` IN (' . $placeholders . ')',
            $ids
        );
        foreach ((array) $rows as $row) {
            $id = ModuleHelper::toSteam64((string) ($row['steamid'] ?? ''));
            if (isset($out[$id])) {
                $out[$id]['site_admin'] = true;
            }
        }
    }

    private function fillServerAdmins(array &$out, array $ids): void
    {
        if (!empty($this->Db->db_data['AdminSystem'])) {
            $this->fillAdminSystem($out, $ids);
        }

        if (!empty($this->Db->db_data['IksAdminNew'])) {
            $this->fillIksAdminNew($out, $ids);
        } elseif (!empty($this->Db->db_data['IksAdmin'])) {
            $this->fillIksAdmin($out, $ids);
        }

        if (!empty($this->Db->db_data['SourceBans'])) {
            $this->fillSourceBans($out, $ids);
        }
    }

    private function fillAdminSystem(array &$out, array $ids): void
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->Db->queryAll(
            'AdminSystem',
            0,
            0,
            'SELECT a.`steamid`, g.`name` AS `group_name`
             FROM `as_admins` a
             INNER JOIN `as_admins_servers` s ON a.`id` = s.`admin_id`
             LEFT JOIN `as_groups` g ON s.`group_id` = g.`id`
             WHERE a.`steamid` IN (' . $placeholders . ')
               AND (s.`expires` > UNIX_TIMESTAMP() OR s.`expires` = 0)',
            $ids
        );
        foreach ((array) $rows as $row) {
            $id = ModuleHelper::toSteam64((string) ($row['steamid'] ?? ''));
            $this->markAdmin($out, $id, (string) ($row['group_name'] ?? ''));
        }
    }

    private function fillIksAdminNew(array &$out, array $ids): void
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->Db->queryAll(
            'IksAdminNew',
            0,
            0,
            'SELECT a.`steam_id` AS `steamid`, g.`name` AS `group_name`
             FROM `iks_admins` a
             LEFT JOIN `iks_groups` g ON a.`group_id` = g.`id`
             WHERE a.`steam_id` IN (' . $placeholders . ')
               AND a.`is_disabled` = 0
               AND (a.`end_at` > UNIX_TIMESTAMP() OR a.`end_at` IS NULL)',
            $ids
        );
        foreach ((array) $rows as $row) {
            $id = ModuleHelper::toSteam64((string) ($row['steamid'] ?? ''));
            $this->markAdmin($out, $id, (string) ($row['group_name'] ?? ''));
        }
    }

    private function fillIksAdmin(array &$out, array $ids): void
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->Db->queryAll(
            'IksAdmin',
            0,
            0,
            'SELECT a.`sid` AS `steamid`, g.`name` AS `group_name`
             FROM `iks_admins` a
             LEFT JOIN `iks_groups` g ON a.`group_id` = g.`id`
             WHERE a.`sid` IN (' . $placeholders . ')
               AND (a.`end` > UNIX_TIMESTAMP() OR a.`end` = 0)',
            $ids
        );
        foreach ((array) $rows as $row) {
            $id = ModuleHelper::toSteam64((string) ($row['steamid'] ?? ''));
            $this->markAdmin($out, $id, (string) ($row['group_name'] ?? ''));
        }
    }

    private function fillSourceBans(array &$out, array $ids): void
    {
        $shortMap = [];
        foreach ($ids as $id) {
            $short = $this->steam32Short($id);
            if ($short !== '') {
                $shortMap[$short] = $id;
            }
        }
        if ($shortMap === []) {
            return;
        }

        $rows = $this->Db->queryAll(
            'SourceBans',
            0,
            0,
            'SELECT a.`authid`, a.`srv_group`
             FROM `sb_admins` a
             INNER JOIN `sb_admins_servers_groups` asg ON a.`aid` = asg.`admin_id`
             WHERE (a.`expired` > UNIX_TIMESTAMP() OR a.`expired` = 0)
             GROUP BY a.`aid`, a.`authid`, a.`srv_group`'
        );

        foreach ((array) $rows as $row) {
            $authid = (string) ($row['authid'] ?? '');
            if ($authid === '') {
                continue;
            }
            foreach ($shortMap as $short => $steamid64) {
                if (strpos($authid, $short) !== false) {
                    $this->markAdmin($out, $steamid64, (string) ($row['srv_group'] ?? ''));
                    break;
                }
            }
        }
    }

    private function steam32Short(string $steamid64): string
    {
        $steam32 = ModuleHelper::toSteam32($steamid64);
        if (preg_match('/:([01]):(\d+)$/', $steam32, $m)) {
            return $m[1] . ':' . $m[2];
        }

        return '';
    }

    private function fillVips(array &$out, array $ids): void
    {
        if (empty($this->Db->db_data['Vips'])) {
            return;
        }

        $accountMap = [];
        foreach ($ids as $id) {
            $acc = function_exists('con_steam3') ? con_steam3($id) : false;
            if ($acc === false || $acc === '' || $acc === null) {
                continue;
            }
            $accountMap[(string) $acc] = $id;
        }
        if ($accountMap === []) {
            return;
        }

        foreach ($this->Db->db_data['Vips'] as $entry) {
            $prefix = (string) ($entry['Table'] ?? 'vip_');
            if ($prefix === '') {
                $prefix = 'vip_';
            }
            $table = (substr($prefix, -5) === 'users') ? $prefix : ($prefix . 'users');

            $accountIds = array_keys($accountMap);
            $placeholders = implode(',', array_fill(0, count($accountIds), '?'));
            $rows = $this->Db->queryAll(
                'Vips',
                (int) $entry['USER_ID'],
                (int) $entry['DB_num'],
                'SELECT `account_id`, `group` FROM `' . $table . '`
                 WHERE (`expires` = 0 OR `expires` > UNIX_TIMESTAMP())
                   AND CAST(`account_id` AS CHAR) IN (' . $placeholders . ')',
                $accountIds
            );

            if (empty($rows)) {
                $likeParts = [];
                $params = [];
                foreach ($accountIds as $acc) {
                    $likeParts[] = 'CAST(`account_id` AS CHAR) LIKE ?';
                    $params[] = '%' . $acc . '%';
                }
                $rows = $this->Db->queryAll(
                    'Vips',
                    (int) $entry['USER_ID'],
                    (int) $entry['DB_num'],
                    'SELECT `account_id`, `group` FROM `' . $table . '`
                     WHERE (`expires` = 0 OR `expires` > UNIX_TIMESTAMP())
                       AND (' . implode(' OR ', $likeParts) . ')',
                    $params
                );
            }

            foreach ((array) $rows as $row) {
                $accountId = (string) ($row['account_id'] ?? '');
                if ($accountId === '') {
                    continue;
                }
                foreach ($accountMap as $needle => $steamid64) {
                    if ($needle !== '' && ($accountId === (string) $needle || strpos($accountId, (string) $needle) !== false)) {
                        $out[$steamid64]['vip'] = true;
                        if ($out[$steamid64]['vip_group'] === '' && !empty($row['group'])) {
                            $out[$steamid64]['vip_group'] = action_text_clear((string) $row['group']);
                        }
                        break;
                    }
                }
            }
        }
    }
}
