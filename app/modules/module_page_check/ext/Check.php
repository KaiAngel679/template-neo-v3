<?php

namespace app\modules\module_page_check\ext;

class Check
{
	public $Db;
	public $General;
	public $Translate;
	public $Modules;
	public $Router;
	public $Notifications;
	public $settings;


	public function __construct($Db, $General, $Translate, $Modules, $Router, $Notifications)
	{
		$this->Db = $Db;
		$this->General = $General;
		$this->Translate = $Translate;
		$this->Modules = $Modules;
		$this->Router = $Router;
		$this->Notifications = $Notifications;
		$this->settings = $this->getSettings();
	}

	public function getAllListPaginated(int $server, $type, $search, int $page_min, int $limit)
	{
		$sql = '';
		$params = ['server' => $server];
		if ($type !== 'all') {
			if (!empty($this->Db->db_data['IksAdminNew'])) {
				$sql = " AND `check_result` = :type";
			} else {
				$sql = " AND `verdict` = :type";
				$type = $this->getReasonById($type);
			}
			$params['type'] = $type;
		}
		if (!empty($search)) {
			$search = '%' . $search . '%';
			if (!empty($this->Db->db_data['IksAdminNew'])) {
				$sql .= " AND (iks_check_results.steam_id LIKE :search OR iks_check_results.name LIKE :search OR iks_admins.steam_id LIKE :search OR iks_admins.name LIKE :search)";
			} else {
				$sql .= " AND (`player_steamid` LIKE :search OR `player_name` LIKE :search OR `admin_steamid` LIKE :search OR `admin_name` LIKE :search)";
			}
			$params['search'] = $search;
		}
		if (!empty($this->Db->db_data['AdminSystem'])) {
			$array = $this->Db->queryAll('AdminSystem', 0, 0, "SELECT * FROM `checkcheats_stats` WHERE `server_id` = :server $sql ORDER BY id DESC LIMIT $page_min, $limit", $params);
			$count = $this->Db->query('AdminSystem', 0, 0, "SELECT COUNT(*) as count FROM `checkcheats_stats` WHERE `server_id` = :server $sql", $params)['count'];
			return [
				'data' => $array,
				'count' => $count,
			];
		} elseif (!empty($this->Db->db_data['IksAdminNew'])) {
			$array = $this->Db->queryAll(
				'IksAdminNew',
				0,
				0,
				"SELECT
					iks_check_results.*,
					iks_check_results.steam_id AS player_steamid,
					iks_check_results.name AS player_name,
					iks_check_results.created_at AS datestart,
					iks_check_results.result_reason AS verdict,
					iks_admins.steam_id AS admin_steamid,
					iks_admins.name AS admin_name
				FROM iks_check_results
				JOIN iks_admins ON iks_check_results.admin_id = iks_admins.id
				WHERE iks_check_results.server_id = :server $sql
				ORDER BY iks_check_results.id DESC
				LIMIT $page_min, $limit",
				$params
			);
			$count = $this->Db->query(
				'IksAdminNew',
				0,
				0,
				"SELECT COUNT(*) AS count
				 FROM iks_check_results cr
				 WHERE cr.server_id = :server $sql",
				$params
			)['count'];
			return [
				'data' => $array,
				'count' => $count,
			];
		} elseif (!empty($this->Db->db_data['IksAdmin'])) {
			$array = $this->Db->queryAll('IksAdmin', 0, 0, "SELECT * FROM `checkcheats_stats` WHERE `server_id` = :server $sql ORDER BY id DESC LIMIT $page_min, $limit", $params);
			$count = $this->Db->query('IksAdmin', 0, 0, "SELECT COUNT(*) as count FROM `checkcheats_stats` WHERE `server_id` = :server $sql", $params)['count'];
			return [
				'data' => $array,
				'count' => $count,
			];
		} else {
			return [
				'data' => [],
				'count' => 0,
			];
		}
	}

	public function getCountList($server)
	{
		if (!empty($this->Db->db_data['AdminSystem'])) {
			return $this->Db->query('AdminSystem', 0, 0, "SELECT COUNT(*) as count FROM `checkcheats_stats` WHERE `server_id` = :server", ['server' => $server])['count'];
		} elseif (!empty($this->Db->db_data['IksAdminNew'])) {
			return $this->Db->query('IksAdminNew', 0, 0, "SELECT COUNT(*) as count FROM `iks_check_results` WHERE `server_id` = :server", ['server' => $server])['count'];
		} elseif (!empty($this->Db->db_data['IksAdmin'])) {
			return $this->Db->query('IksAdmin', 0, 0, "SELECT COUNT(*) as count FROM `checkcheats_stats` WHERE `server_id` = :server", ['server' => $server])['count'];
		} else {
			return 0;
		}
	}

	public function getServerSbid($server)
	{
		if (is_numeric($server)) {
			$index = array_search($server, array_column($this->General->server_list, 'id'));
			return $index !== false ? ($this->General->server_list[$index]['server_sb_id'] ?? 1) : 1;
		}
		return 1;
	}

	public function getServerIksAdminNew()
	{
		return $this->Db->queryAll('IksAdminNew', 0, 0, "SELECT * FROM iks_servers WHERE `deleted_at` IS NULL");
	}

	public function getServers()
	{

		$servers = [];
		if (!empty($this->Db->db_data['IksAdminNew'])) {
			$iks_servers = $this->getServerIksAdminNew();
			foreach ($iks_servers as $server) {
				$servers[] = [
					'id' => $server['id'],
					'name' => $server['name'],
				];
			}
			return $servers;
		} else {
			foreach ($this->General->server_list as $server) {
				$servers[] = [
					'id' => $server['id'],
					'name' => $server['name'],
				];
			}
		}
		return $servers;
	}

	public function IsAdmin()
	{
		if (isset($_SESSION['steamid32'])) {
			$steam64 = $_SESSION['steamid64'];
			$access = $this->getAccess();
			if (in_array($steam64, $access)) {
				return true;
			}
			if (isset($_SESSION['user_admin'])) {
				return true;
			}
			if (isset($this->settings['giveAccess']) && $this->settings['giveAccess'] === 1) {
				if (!empty($this->Db->db_data['AdminSystem'])):
					$admin = $this->Db->query(
						'AdminSystem',
						0,
						0,
						"SELECT 
						`as_admins`.`id`,
						`as_admins`.`steamid`,
						GROUP_CONCAT(DISTINCT `as_admins_servers`.`server_id`) AS `server_id`
					FROM 
						`as_admins`
					JOIN 
						`as_admins_servers` 
					ON 
						`as_admins`.`id` = `as_admins_servers`.`admin_id` 
					WHERE `as_admins`.`steamid` = :id
					AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)
					GROUP BY `as_admins`.`id` LIMIT 1",
						['id' => $steam64]
					);
				elseif (!empty($this->Db->db_data['IksAdminNew'])):
					$admin = $this->Db->query(
						'IksAdminNew',
						0,
						0,
						"SELECT 
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
						a.steam_id = :id
						AND a.is_disabled = 0
						AND (a.end_at > UNIX_TIMESTAMP() OR a.end_at IS NULL)
					GROUP BY a.steam_id;",
						['id' => $steam64]
					);
				elseif (!empty($this->Db->db_data['IksAdmin'])):
					$admin = $this->Db->query(
						'IksAdmin',
						0,
						0,
						"SELECT 
						a.sid AS steamid,
						GROUP_CONCAT(DISTINCT g.name ORDER BY g.immunity DESC SEPARATOR ', ') AS group_names,
						a.server_id AS servers
					FROM 
						iks_admins a
					JOIN 
						iks_groups g ON a.group_id = g.id
					WHERE 
						a.sid = :id
						AND (a.end = 0 OR a.end > UNIX_TIMESTAMP())
					GROUP BY a.sid;",
						['id' => $steam64]
					);
				endif;
				if (!empty($admin['steamid'])) {
					return true;
				}
			}
			return false;
		}
	}

	public function getReason()
	{
		if (!empty($this->Db->db_data['IksAdminNew'])) {
			$reasons = [
				0 => $this->Translate->get_translate_module_phrase('module_page_check', '_reason0IksAdminNew'),
				1 => $this->Translate->get_translate_module_phrase('module_page_check', '_reason1IksAdminNew'),
				2 => $this->Translate->get_translate_module_phrase('module_page_check', '_reason2IksAdminNew'),
				3 => $this->Translate->get_translate_module_phrase('module_page_check', '_reason3IksAdminNew'),
				4 => $this->Translate->get_translate_module_phrase('module_page_check', '_reason4IksAdminNew'),
				5 => $this->Translate->get_translate_module_phrase('module_page_check', '_reason5IksAdminNew'),
			];
		} else {
			$reasons = $this->Modules->get_settings_modules('module_page_check', 'reasons');
		}
		if (empty($reasons)) {
			return [];
		}
		return $reasons;
	}

	public function getReasonFile()
	{
		$reasons = $this->Modules->get_settings_modules('module_page_check', 'reasons');
		if (empty($reasons)) {
			return [];
		}
		return $reasons;
	}

	public function getReasonById($id)
	{
		$reasons = $this->getReason();
		if (isset($reasons[$id])) {
			return $reasons[$id];
		}
		return '';
	}

	public function saveReason($reasons)
	{
		if (empty($reasons)) {
			return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_incorrectData')];
		}

		$reasons = array_filter($reasons, function ($reason) {
			return !empty(trim($reason));
		});

		$this->Modules->put_settings_modules('module_page_check', 'reasons', $reasons);

		return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully')];
	}

	public function getAccess()
	{
		$access = $this->Modules->get_settings_modules('module_page_check', 'access');
		if (empty($access)) {
			return [];
		}
		return $access;
	}

	public function AddAccess($steamid)
	{
		if (empty($steamid)) {
			return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_incorrectData')];
		}

		if (!preg_match('/^(7656119)([0-9]{10})/', $steamid)) {
			return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_check', '_incorrectSteamData')];
		}

		$access = $this->getAccess();
		if (!in_array($steamid, $access)) {
			$access[] = $steamid;
			$this->Modules->put_settings_modules('module_page_check', 'access', $access);
			return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully')];
		} else {
			return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_check', '_alreadyExists')];
		}
	}

	public function delAccess($steamid)
	{
		if (empty($steamid)) {
			return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_incorrectData')];
		}

		$access = $this->getAccess();
		if (($key = array_search($steamid, $access)) !== false) {
			unset($access[$key]);
			$this->Modules->put_settings_modules('module_page_check', 'access', $access);
			return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully')];
		} else {
			return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_check', '_notFound')];
		}
	}

	public function getSettings()
	{
		$settings = $this->Modules->get_settings_modules('module_page_check', 'settings');
		if (empty($settings)) {
			return [];
		}
		return $settings;
	}

	public function saveSettings($name)
	{
		$settings = $this->getSettings();

		$settings[$name] = (int) $settings[$name] == 0 ? 1 : 0;

		$this->Modules->put_settings_modules('module_page_check', 'settings', $settings);

		return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successfully')];
	}

	public function Render($page, $server, $type, $search)
	{
		$page = (int)$page;
		$limit = 20;
		$page_min = ($page - 1) * $limit;
		$page_max = 0;
		$array = $this->getAllListPaginated($this->getServerSbid($server), $type, $search, $page_min, $limit);
		$data = $array['data'];
		$count = $array['count'];
		$render = [];
		$steamids = [];
		$page_max = ceil($count / $limit);
		foreach ($data as $key => $value) {
			$player_steamid = $value['player_steamid'];
			$player_name = $this->General->checkName($player_steamid);
			$admin_steamid = $value['admin_steamid'];
			$admin_name = $this->General->checkName($admin_steamid);
			if (!empty($this->Db->db_data['IksAdminNew'])) {
				if ($value['check_result'] == 4 || $value['check_result'] == 5) {
					$verdict = $value['verdict'];
				} else {
					$verdict = $this->getReasonById($value['check_result']);
				}
				if (isset($value['discord']) && !empty($value['discord'])) {
					$suspect_discord = $value['discord'];
				} else {
					$suspect_discord = $this->Translate->get_translate_phrase('_absent');
				}
			} else {
				$verdict = $value['verdict'];
				if (!empty($value['suspect_discord'])) {
					$suspect_discord = $value['suspect_discord'];
				} else {
					$suspect_discord = $this->Translate->get_translate_phrase('_absent');
				}
			}
			$render[] = [
				'id' => $value['id'],
				'player_steamid' => $player_steamid,
				'player_name' => $player_name,
				'player_checked_avatar' => $this->General->checkAvatar($player_steamid),
				'admin_steamid' => $admin_steamid,
				'admin_name' => $admin_name,
				'admin_checked_avatar' => $this->General->checkAvatar($admin_steamid),
				'datestart' => !empty($value['datestart']) ? date('d.m.Y, H:i:s', $value['datestart']) : $this->Translate->get_translate_phrase('_absent'),
				'date_end' => !empty($value['date_end']) ? date('d.m.Y, H:i:s', $value['date_end']) : $this->Translate->get_translate_phrase('_absent'),
				'verdict' => $verdict,
				'suspect_discord' => $suspect_discord,
			];
			$steamids[] = $player_steamid;
			$steamids[] = $admin_steamid;
		}
		$steamids = array_unique($steamids);
		return [
			'checks' => $render,
			'steamids' => $steamids,
			'max_pages' => $page_max
		];
	}
}
