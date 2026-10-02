<?php

namespace app\modules\module_page_results\ext\Repository;

class StatsRepository extends BaseRepository
{
  public $Db;
  public $Translate;
  public function __construct($Db, $Translate)
  {
    $this->Db = $Db;
    $this->Translate = $Translate;
  }

  public function getAdminInfo(string $steamid, $server_id, int $timeStart, int $timeEnd): array
  {

    if (!empty($this->Db->db_data['AdminSystem'])) {
      return $this->Db->query(
        'AdminSystem',
        0,
        0,
        "SELECT 
          `as_admins`.`id` AS `admin_id`,
          `as_admins`.`name`,
          `as_admins`.`steamid`,
          GROUP_CONCAT(DISTINCT `as_admins_servers`.`server_id`) AS `server_id`,
          (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins`.`id` AND `server_id` IN (-1, {$server_id}) AND `punish_type` = 0 AND `created` >= :time_start AND `created` < :time_end) AS `bans_count`,
          (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins`.`id` AND `server_id` IN (-1, {$server_id}) AND `punish_type` IN (1, 3) AND `created` >= :time_start AND `created` < :time_end) AS `mutes_count`,
          (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins`.`id` AND `server_id` IN (-1, {$server_id}) AND `punish_type` IN (2, 3) AND `created` >= :time_start AND `created` < :time_end) AS `gags_count`
        FROM 
          `as_admins`
        JOIN 
          `as_admins_servers` 
        ON 
          `as_admins`.`id` = `as_admins_servers`.`admin_id`
        WHERE 
          `as_admins`.`steamid` = :steam
          AND `as_admins_servers`.`server_id` IN (-1, {$server_id})
          AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
        GROUP BY
          `as_admins`.`id`, `as_admins_servers`.`group_id`;",
        [
          'steam' => $steamid,
          'time_start' => $timeStart,
          'time_end' => $timeEnd
        ]
      );
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      return $this->Db->query(
        'IksAdminNew',
        0,
        0,
        "SELECT 
          `iks_admins`.`id`,
          `iks_admins`.`name`,
          `iks_admins`.`steam_id` AS `steamid`,
          `iks_admins`.`flags`,
          `iks_admins`.`immunity`,
          `iks_admins`.`end_at` AS `end`,
          `iks_admins`.`group_id`,
          GROUP_CONCAT(DISTINCT `iks_admin_to_server`.`server_id`) AS `server_id`,
          (SELECT COUNT(1) FROM `iks_bans` WHERE `admin_id` = `iks_admins`.`id`  AND (`iks_bans`.`server_id` IN ({$server_id}) OR `iks_bans`.`server_id` IS NULL) AND `created_at` >= :time_start AND `created_at` < :time_end) AS `bans_count`,
          (SELECT COUNT(1) FROM `iks_comms` WHERE `admin_id` = `iks_admins`.`id` AND (`iks_comms`.`server_id` IN ({$server_id}) OR `iks_comms`.`server_id` IS NULL) AND `mute_type` IN (0, 2) AND `created_at` >= :time_start AND `created_at` < :time_end) AS `mutes_count`,
          (SELECT COUNT(1) FROM `iks_comms` WHERE `admin_id` = `iks_admins`.`id` AND (`iks_comms`.`server_id` IN ({$server_id}) OR `iks_comms`.`server_id` IS NULL) AND `mute_type` IN (1, 2) AND `created_at` >= :time_start AND `created_at` < :time_end) AS `gags_count`
        FROM 
          `iks_admins`
        JOIN 
          `iks_admin_to_server` 
        ON 
          `iks_admins`.`id` = `iks_admin_to_server`.`admin_id`
        WHERE
          `iks_admins`.`steam_id` = :steam
          AND (`iks_admin_to_server`.`server_id` IN ({$server_id}) OR `iks_admin_to_server`.`server_id` IS NULL)
          AND (`iks_admins`.`end_at` > UNIX_TIMESTAMP() OR `iks_admins`.`end_at` IS NULL) AND `iks_admins`.`is_disabled` = 0
        GROUP BY
          `iks_admins`.`id`, `iks_admin_to_server`.`id`;",
        [
          'steam' => $steamid,
          'time_start' => $timeStart,
          'time_end' => $timeEnd
        ]
      );
    } else {
      return [];
    }
  }

  public function getAdminPlayedTime(string $steamid, $server_id, int $timeStart, int $timeEnd): array
  {
    if (!empty($this->Db->db_data['AdminSystem'])) {
      return $this->Db->query(
        'AdminSystem',
        0,
        0,
        "SELECT 
          SUM(`played_time`) AS `total_played`,
          COUNT(1) AS `count_sessions`
        FROM 
          `as_admin_time`
        WHERE 
          `admin_id` = :steam
          AND `server_id` IN ({$server_id})
          AND `connect_time` >= :time_start
          AND `connect_time` <= :time_end",
        ['steam' => $steamid, 'time_start' => $timeStart, 'time_end' => $timeEnd]
      );
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      return $this->Db->query(
        'IksAdminNew',
        0,
        0,
        "SELECT 
          SUM(`played_time`) AS `total_played`,
          COUNT(1) AS `count_sessions`
        FROM 
          `iks_rewards`
        WHERE 
          `admin_id` = :steam
          AND `server_id` IN ({$server_id})
          AND `connect_time` >= :time_start
          AND `connect_time` <= :time_end",
        ['steam' => $steamid, 'time_start' => $timeStart, 'time_end' => $timeEnd]
      );
    } else {
      return ['total_played' => 0, 'count_sessions' => 0];
    }
  }

  public function getAdminSessions(string $steamid, $server_id, int $timeStart, int $timeEnd): array
  {

    if (!empty($this->Db->db_data['AdminSystem'])) {
      return $this->Db->queryAll(
        'AdminSystem',
        0,
        0,
        "SELECT 
          `server_id`,
          `connect_time`,
          FROM_UNIXTIME(`connect_time`, '%d.%m.%Y') AS `date`,
          `disconnect_time`,
          `played_time`
        FROM 
          `as_admin_time`
        WHERE 
          `admin_id` = :steam
          AND `server_id` IN ({$server_id})
          AND `connect_time` >= :time_start
          AND `connect_time` <= :time_end
        ORDER BY `connect_time` DESC",
        [
          'steam' => $steamid,
          'time_start' => $timeStart,
          'time_end' => $timeEnd
        ]
      );
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      return $this->Db->queryAll(
        'IksAdminNew',
        0,
        0,
        "SELECT 
          `server_id`,
          `connect_time`,
          FROM_UNIXTIME(`connect_time`, '%d.%m.%Y') AS `date`,
          `disconnect_time`,
          `played_time`
        FROM 
          `iks_rewards`
        WHERE 
          `admin_id` = :steam
          AND `server_id` IN ({$server_id})
          AND `connect_time` >= :time_start
          AND `connect_time` <= :time_end
        ORDER BY `connect_time` DESC",
        [
          'steam' => $steamid,
          'time_start' => $timeStart,
          'time_end' => $timeEnd
        ]
      );
    } else {
      return [];
    }
  }

  public function getReportsCount(string $steamid, $server_rid, int $timeStart, int $timeEnd): int
  {

    if (!$this->Db || empty($this->Db->db_data['Reports'])) {
      return 0;
    }
    $count = $this->Db->queryNum(
      'Reports',
      0,
      0,
      "SELECT COUNT(1) FROM `rs_reports` WHERE `steamid_admin_verdict` = :steam AND `sid` IN ({$server_rid}) AND `time` >= :time_start AND `time` < :time_end",
      [
        'steam' => $steamid,
        'time_start' => $timeStart,
        'time_end' => $timeEnd
      ]
    );

    return (int)($count[0] ?? 0);
  }

  public function getCheckCount(string $steamid, $server_id, int $timeStart, int $timeEnd): int
  {
    if (!empty($this->Db->db_data['Check'])) {
      if ($this->Db->db_data['Check'][0]['Table'] == 'checkcheats_stats' || $this->Db->db_data['Check'][0]['Table'] == 'meowcheckcheats_stats') {
        $count = $this->Db->queryNum(
          'Check',
          0,
          0,
          "SELECT COUNT(1) FROM `{$this->Db->db_data['Check'][0]['Table']}` WHERE `admin_steamid` = :steam AND `server_id` IN ({$server_id}) AND `datestart` >= :time_start AND `datestart` < :time_end",
          [
            'steam' => $steamid,
            'time_start' => $timeStart,
            'time_end' => $timeEnd
          ]
        );
        return (int)($count[0] ?? 0);
      } elseif ($this->Db->db_data['Check'][0]['Table'] == 'iks_check_results') {
        $count = $this->Db->queryNum(
          'Check',
          0,
          0,
          "SELECT COUNT(1) FROM `iks_check_results`
          JOIN `iks_admins` ON `iks_check_results`.`admin_id` = `iks_admins`.`id`
          WHERE `iks_admins`.`steam_id` = :steam AND `iks_check_results`.`server_id` IN ({$server_id}) AND `iks_check_results`.`created_at` >= :time_start AND `iks_check_results`.`created_at` < :time_end",
          [
            'steam' => $steamid,
            'time_start' => $timeStart,
            'time_end' => $timeEnd
          ]
        );
        return (int)($count[0] ?? 0);
      }
    }
    return 0;
  }

  public function getPlaytimeByDays(string $steamid, string $serverIds, int $start, int $end): array
  {
    if (!empty($this->Db->db_data['AdminSystem'])) {
      return $this->Db->queryAll(
        'AdminSystem',
        0,
        0,
        "SELECT DATE(FROM_UNIXTIME(connect_time)) as day, SUM(played_time) as total 
          FROM as_admin_time WHERE admin_id = :steam AND server_id IN ({$serverIds}) 
          AND connect_time >= :start AND connect_time <= :end GROUP BY day",
        [
          'steam' => $steamid,
          'start' => $start,
          'end' => $end
        ]
      );
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      return $this->Db->queryAll(
        'IksAdminNew',
        0,
        0,
        "SELECT DATE(FROM_UNIXTIME(connect_time)) as day, SUM(played_time) as total 
          FROM iks_rewards WHERE admin_id = :steam AND server_id IN ({$serverIds}) 
          AND connect_time >= :start AND connect_time <= :end GROUP BY day",
        [
          'steam' => $steamid,
          'start' => $start,
          'end' => $end
        ]
      );
    } else {
      return [];
    }
  }

  public function getPunishmentsByDays(string $steamid, string $serverIds, int $start, int $end): array
  {
    if (!empty($this->Db->db_data['AdminSystem'])) {
      return $this->Db->queryAll(
        'AdminSystem',
        0,
        0,
        "SELECT DATE(FROM_UNIXTIME(p.created)) as day,
          SUM(p.punish_type = 0) as bans, 
          SUM(p.punish_type IN (1,3)) as mutes, 
          SUM(p.punish_type IN (2,3)) as gags
          FROM as_punishments p
          JOIN as_admins a ON p.admin_id = a.id
          WHERE a.steamid = :steam 
          AND p.server_id IN (-1, {$serverIds})
          AND p.created >= :start AND p.created < :end 
          GROUP BY day",
        [
          'steam' => $steamid,
          'start' => $start,
          'end' => $end
        ]
      );
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      $bans = $this->Db->queryAll(
        'IksAdminNew',
        0,
        0,
        "SELECT DATE(FROM_UNIXTIME(b.created_at)) as day, COUNT(1) as bans
          FROM iks_bans b
          JOIN iks_admins a ON b.admin_id = a.id
          WHERE a.steam_id = :steam 
          AND (b.server_id IN ({$serverIds}) OR b.server_id IS NULL)
          AND b.created_at >= :start AND b.created_at < :end
          GROUP BY day",
        ['steam' => $steamid, 'start' => $start, 'end' => $end]
      );

      $comms = $this->Db->queryAll(
        'IksAdminNew',
        0,
        0,
        "SELECT DATE(FROM_UNIXTIME(c.created_at)) as day,
          SUM(c.mute_type IN (0, 2)) as mutes,
          SUM(c.mute_type IN (1, 2)) as gags
          FROM iks_comms c
          JOIN iks_admins a ON c.admin_id = a.id
          WHERE a.steam_id = :steam 
          AND (c.server_id IN ({$serverIds}) OR c.server_id IS NULL)
          AND c.created_at >= :start AND c.created_at < :end
          GROUP BY day",
        ['steam' => $steamid, 'start' => $start, 'end' => $end]
      );

      $result = [];
      foreach ($bans as $row) {
        $result[$row['day']]['day'] = $row['day'];
        $result[$row['day']]['bans'] = $row['bans'];
      }
      foreach ($comms as $row) {
        $result[$row['day']]['day'] = $row['day'];
        $result[$row['day']]['mutes'] = $row['mutes'];
        $result[$row['day']]['gags'] = $row['gags'];
      }

      return array_values($result);
    } else {
      return [];
    }
  }

  public function getReportsByDays(string $steamid, string $serverRids, int $start, int $end): array
  {

    if (!$this->Db || empty($this->Db->db_data['Reports'])) {
      return [];
    }

    return $this->Db->queryAll(
      'Reports',
      0,
      0,
      "SELECT DATE(FROM_UNIXTIME(time)) as day, COUNT(1) as cnt FROM rs_reports WHERE steamid_admin_verdict = :steam AND `sid` IN ({$serverRids}) AND time >= :start AND time < :end GROUP BY day",
      [
        'steam' => $steamid,
        'start' => $start,
        'end' => $end
      ]
    );
  }

  public function getChecksByDays(string $steamid, string $serverIds, int $start, int $end): array
  {
    if (!empty($this->Db->db_data['Check'])) {
      if ($this->Db->db_data['Check'][0]['Table'] == 'checkcheats_stats' || $this->Db->db_data['Check'][0]['Table'] == 'meowcheckcheats_stats') {
        return $this->Db->queryAll(
          'Check',
          0,
          0,
          "SELECT DATE(FROM_UNIXTIME(datestart)) as day, COUNT(1) as cnt FROM {$this->Db->db_data['Check'][0]['Table']} 
        WHERE admin_steamid = :steam AND server_id IN ({$serverIds}) AND datestart >= :start AND datestart < :end GROUP BY day",
          ['steam' => $steamid, 'start' => $start, 'end' => $end]
        );
      } elseif ($this->Db->db_data['Check'][0]['Table'] == 'iks_check_results') {
        return $this->Db->queryAll(
          'Check',
          0,
          0,
          "SELECT DATE(FROM_UNIXTIME(iks_check_results.created_at)) as day, COUNT(1) as cnt 
          FROM iks_check_results
          JOIN iks_admins ON iks_check_results.admin_id = iks_admins.id
          WHERE iks_admins.steam_id = :steam AND iks_check_results.server_id IN ({$serverIds}) AND iks_check_results.created_at >= :start AND iks_check_results.created_at < :end 
          GROUP BY day",
          ['steam' => $steamid, 'start' => $start, 'end' => $end]
        );
      } else {
        return [];
      }
    } else {
      return [];
    }
  }

  public function giveBalance(string $steamid, float $amount, string $reason): bool
  {
    if (!$this->Db) return false;

    $steam = con_steam64to32($steamid);
    $status = $this->Translate->get_translate_module_phrase('module_page_results', '_AwardForNorm') . " " . $reason;

    $this->Db->query(
      'lk',
      0,
      0,
      "UPDATE `lk` SET `cash` = `cash` + :amount WHERE `auth` = :steamid;",
      ['steamid' => $steam, 'amount' => $amount]
    );

    $this->Db->query(
      'lk',
      0,
      0,
      "INSERT INTO `lk_pays` (`pay_order`, `pay_auth`, `pay_summ`, `pay_data`, `pay_system`, `pay_promo`, `pay_status`) VALUES (?, ?, ?, ?, 'admin', ?, 1)",
      [time() % 100000, $steam, $amount, date('d.m.Y H:i:s'), $status]
    );

    return true;
  }

  public function getAdminInfoBatch(array $steamids, $server_id, int $timeStart, int $timeEnd): array
  {
    if (empty($steamids)) {
      return [];
    }

    $params = ['time_start' => $timeStart, 'time_end' => $timeEnd];
    $steamids_str = implode(',', $steamids);
    $result = [];

    if (!empty($this->Db->db_data['AdminSystem'])) {
      $data = $this->Db->queryAll(
        'AdminSystem',
        0,
        0,
        "SELECT 
          `as_admins`.`steamid`,
          (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins`.`id` AND `server_id` IN (-1, {$server_id}) AND `punish_type` = 0 AND `created` >= :time_start AND `created` < :time_end) AS `bans_count`,
          (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins`.`id` AND `server_id` IN (-1, {$server_id}) AND `punish_type` IN (1, 3) AND `created` >= :time_start AND `created` < :time_end) AS `mutes_count`,
          (SELECT COUNT(1) FROM `as_punishments` WHERE `admin_id` = `as_admins`.`id` AND `server_id` IN (-1, {$server_id}) AND `punish_type` IN (2, 3) AND `created` >= :time_start AND `created` < :time_end) AS `gags_count`
        FROM `as_admins`
        WHERE `as_admins`.`steamid` IN ({$steamids_str})",
        $params
      );

      foreach ($data as $row) {
        $result[$row['steamid']] = $row;
      }
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      $data = $this->Db->queryAll(
        'IksAdminNew',
        0,
        0,
        "SELECT 
          `iks_admins`.`steam_id` AS `steamid`,
          (SELECT COUNT(1) FROM `iks_bans` WHERE `admin_id` = `iks_admins`.`id` AND (`iks_bans`.`server_id` IN ({$server_id}) OR `iks_bans`.`server_id` IS NULL) AND `created_at` >= :time_start AND `created_at` < :time_end) AS `bans_count`,
          (SELECT COUNT(1) FROM `iks_comms` WHERE `admin_id` = `iks_admins`.`id` AND (`iks_comms`.`server_id` IN ({$server_id}) OR `iks_comms`.`server_id` IS NULL) AND `mute_type` IN (0, 2) AND `created_at` >= :time_start AND `created_at` < :time_end) AS `mutes_count`,
          (SELECT COUNT(1) FROM `iks_comms` WHERE `admin_id` = `iks_admins`.`id` AND (`iks_comms`.`server_id` IN ({$server_id}) OR `iks_comms`.`server_id` IS NULL) AND `mute_type` IN (1, 2) AND `created_at` >= :time_start AND `created_at` < :time_end) AS `gags_count`
        FROM `iks_admins`
        WHERE `iks_admins`.`steam_id` IN ({$steamids_str})",
        $params
      );

      foreach ($data as $row) {
        $result[$row['steamid']] = $row;
      }
    }

    foreach ($steamids as $steamid) {
      if (!isset($result[$steamid])) {
        $result[$steamid] = ['bans_count' => 0, 'mutes_count' => 0, 'gags_count' => 0];
      }
    }

    return $result;
  }

  public function getAdminPlayedTimeBatch(array $steamids, $server_id, int $timeStart, int $timeEnd): array
  {
    if (empty($steamids)) {
      return [];
    }

    $params = ['time_start' => $timeStart, 'time_end' => $timeEnd];
    $steamids_str = implode(',', $steamids);
    $result = [];

    if (!empty($this->Db->db_data['AdminSystem'])) {
      $data = $this->Db->queryAll(
        'AdminSystem',
        0,
        0,
        "SELECT 
          `admin_id`,
          SUM(`played_time`) AS `total_played`,
          COUNT(1) AS `sessions_count`
        FROM `as_admin_time`
        WHERE `admin_id` IN ({$steamids_str})
          AND `server_id` IN ({$server_id})
          AND `connect_time` >= :time_start
          AND `connect_time` <= :time_end
        GROUP BY `admin_id`",
        $params
      );

      foreach ($data as $row) {
        $result[$row['admin_id']] = [
          'total_played' => (int)($row['total_played'] ?? 0),
          'sessions_count' => (int)($row['sessions_count'] ?? 0)
        ];
      }
    } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
      $data = $this->Db->queryAll(
        'IksAdminNew',
        0,
        0,
        "SELECT 
          `admin_id`,
          SUM(`played_time`) AS `total_played`,
          COUNT(1) AS `sessions_count`
        FROM `iks_rewards`
        WHERE `admin_id` IN ({$steamids_str})
          AND `server_id` IN ({$server_id})
          AND `connect_time` >= :time_start
          AND `connect_time` <= :time_end
        GROUP BY `admin_id`",
        $params
      );

      foreach ($data as $row) {
        $result[$row['admin_id']] = [
          'total_played' => (int)($row['total_played'] ?? 0),
          'sessions_count' => (int)($row['sessions_count'] ?? 0)
        ];
      }
    }

    foreach ($steamids as $steamid) {
      if (!isset($result[$steamid])) {
        $result[$steamid] = ['total_played' => 0, 'sessions_count' => 0];
      }
    }

    return $result;
  }

  public function getReportsCountBatch(array $steamids, $server_rid, int $timeStart, int $timeEnd): array
  {
    if (empty($steamids) || !$this->Db || empty($this->Db->db_data['Reports'])) {
      return array_fill_keys($steamids, 0);
    }

    $params = ['time_start' => $timeStart, 'time_end' => $timeEnd];
    $steamids_str = implode(',', $steamids);
    $result = [];

    $data = $this->Db->queryAll(
      'Reports',
      0,
      0,
      "SELECT 
        `steamid_admin_verdict`,
        COUNT(1) AS `count`
      FROM `rs_reports`
      WHERE `steamid_admin_verdict` IN ({$steamids_str})
        AND `sid` IN ({$server_rid})
        AND `time` >= :time_start
        AND `time` < :time_end
      GROUP BY `steamid_admin_verdict`",
      $params
    );

    foreach ($data as $row) {
      $result[$row['steamid_admin_verdict']] = (int)$row['count'];
    }

    foreach ($steamids as $steamid) {
      if (!isset($result[$steamid])) {
        $result[$steamid] = 0;
      }
    }

    return $result;
  }

  public function getCheckCountBatch(array $steamids, $server_id, int $timeStart, int $timeEnd): array
  {
    if (empty($steamids)) {
      return array_fill_keys($steamids, 0);
    }

    $result = array_fill_keys($steamids, 0);

    if (!empty($this->Db->db_data['Check'])) {
      $params = ['time_start' => $timeStart, 'time_end' => $timeEnd];
      $steamids_str = implode(',', $steamids);

      if ($this->Db->db_data['Check'][0]['Table'] == 'checkcheats_stats' || $this->Db->db_data['Check'][0]['Table'] == 'meowcheckcheats_stats') {
        $data = $this->Db->queryAll(
          'Check',
          0,
          0,
          "SELECT 
            `admin_steamid`,
            COUNT(1) AS `count`
          FROM `{$this->Db->db_data['Check'][0]['Table']}`
          WHERE `admin_steamid` IN ({$steamids_str})
            AND `server_id` IN ({$server_id})
            AND `datestart` >= :time_start
            AND `datestart` < :time_end
          GROUP BY `admin_steamid`",
          $params
        );

        foreach ($data as $row) {
          $result[$row['admin_steamid']] = (int)$row['count'];
        }
      } elseif ($this->Db->db_data['Check'][0]['Table'] == 'iks_check_results') {
        $data = $this->Db->queryAll(
          'Check',
          0,
          0,
          "SELECT 
            `iks_admins`.`steam_id`,
            COUNT(1) AS `count`
          FROM `iks_check_results`
          JOIN `iks_admins` ON `iks_check_results`.`admin_id` = `iks_admins`.`id`
          WHERE `iks_admins`.`steam_id` IN ({$steamids_str})
            AND `iks_check_results`.`server_id` IN ({$server_id})
            AND `iks_check_results`.`created_at` >= :time_start
            AND `iks_check_results`.`created_at` < :time_end
          GROUP BY `iks_admins`.`steam_id`",
          $params
        );

        foreach ($data as $row) {
          $result[$row['steam_id']] = (int)$row['count'];
        }
      }
    }

    return $result;
  }
}
