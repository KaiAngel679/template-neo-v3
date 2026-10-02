<?php

namespace app\modules\module_page_request\ext;

use app\modules\module_page_request\ext\bb\BBCode;

class Requests
{
	public $Modules;
	public $Translate;
	public $General;
	public $Db;
	public $Notifications;
	public $Auth;
	public $status;
	public $access;

	public function __construct($Translate, $Notifications, $General, $Modules, $Db, $Auth)
	{
		$this->Modules = $Modules;
		$this->Translate = $Translate;
		$this->General = $General;
		$this->Db = $Db;
		$this->Notifications = $Notifications;
		$this->Auth = $Auth;
		$this->access = 0;
		$this->status = [
			"0" => $this->Translate->get_translate_module_phrase('module_page_request', '_AwaitingReview'),
			"1" => $this->Translate->get_translate_module_phrase('module_page_request', '_UnderСonsideration'),
			"2" => $this->Translate->get_translate_module_phrase('module_page_request', '_Revieweda'),
			"3" => $this->Translate->get_translate_module_phrase('module_page_request', '_Rejecteda'),
			"4" => $this->Translate->get_translate_module_phrase('module_page_request', '_Closed')
		];
		$this->access();
	}

	public function OpenBB()
	{
		$bbcode = new BBCode();
		$bbcode->addParser(
			'center',
			'/\[center\](.*?)\[\/center\]/s',
			'<center>$1</center>',
			'$2'
		);
		return $bbcode;
	}

	public function RequestsSettings()
	{
		$DiscordData = $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_settings`");
		return $DiscordData[0];
	}

	public function Settings()
	{
		if ($this->access < 10) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		empty($_POST['webhoock_offon']) ? $auth = 0 : $auth = 1;
		$param = ['url' => $_POST['webhoock_url'], 'auth' => $auth];
		$this->Db->query('request', 0, 0, "UPDATE `lvl_web_request_settings` SET `url`=:url, `auth`=:auth", $param);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_Savea')];
	}

	public function access()
	{
		if (isset($_SESSION['steamid'])):
			if (!empty($this->Db->db_data['request'])):
				if ($this->Db->mysql_table_search('request', 0, 0, "lvl_web_request_perm")):
					$result = $this->Db->query('request', 0, 0, "SELECT `request`, `review` FROM `lvl_web_request_perm` WHERE `steamid`= :steamid LIMIT 1", [
						"steamid" => $_SESSION["steamid64"]
					]);
					if (isset($_SESSION['user_admin'])) {
						$this->access += 10;
					}
					if (!empty($result)):
						if ($result['request'] > 0) {
							$this->access += 5;
						}
						if ($result['review'] > 0) {
							$this->access += 3;
						}
					endif;
				endif;
			endif;
		endif;
	}

	public function UploadMSGPhoto($file, $allowed_types = ['image/png', 'image/x-png', 'image/jpeg', 'image/webp', 'image/gif'])
	{
		$upload_dir = realpath(MODULES) . '/module_page_request/cacheimg/';
		$max_filesize = 8 * 1024 * 1024;

		if (!isset($file['error']) || is_array($file['error'])) {
			return ['error' => $this->Translate->get_translate_phrase('_fileUploadError')];
		}
		if ($file['error'] !== UPLOAD_ERR_OK) {
			return ['error' => $this->Translate->get_translate_phrase('_errorCode') . $file['error']];
		}
		$finfo = new \finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($file['tmp_name']);
		if (!in_array($mime, $allowed_types)) {
			return ['error' => $this->Translate->get_translate_phrase('_fileNotSupported')];
		}
		if (filesize($file['tmp_name']) > $max_filesize) {
			return ['error' => $this->Translate->get_translate_phrase('_fileMaxSize')];
		}
		$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		$blacklist = ['php', 'phtml', 'php3', 'php4', 'js', 'html', 'htm', 'svg'];
		if (in_array($extension, $blacklist)) {
			return ['error' => $this->Translate->get_translate_phrase('_fileDanger')];
		}
		$safe_name = bin2hex(random_bytes(8)) . '.' . $extension;
		$target_path = $upload_dir . $safe_name;
		if (!move_uploaded_file($file['tmp_name'], $target_path)) {
			return ['error' => $this->Translate->get_translate_phrase('_fileSomeError')];
		}
		if (!getimagesize($target_path)) {
			unlink($target_path);
			return ['error' => $this->Translate->get_translate_phrase('_fileNotImage')];
		}
		return [
			'status' => 1,
			'image_link' => htmlspecialchars('/app/modules/module_page_request/cacheimg/' . $safe_name, ENT_QUOTES, 'UTF-8')
		];
	}


	public function getAdmins()
	{
		$perm = $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_perm`");
		return $perm;
	}

	public function getAdmin($aid)
	{
		$data = ['aid' => $aid];
		$perm = $this->Db->query('request', 0, 0, "SELECT * FROM `lvl_web_request_perm` WHERE `aid` = :aid", $data);
		return $perm;
	}

	public function AddAdmin()
	{
		if ($this->access < 7) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		if (empty($_POST['steamid']))
			return ['error' => 'SteamID' . $this->Translate->get_translate_module_phrase('module_page_request', '_notspecified')];
		empty($_POST['request']) ? $request = 0 : $request = 1;
		empty($_POST['review']) ? $review = 0 : $review = 1;
		$data = [
			'steamid' => $_POST['steamid'],
			'request' => $request,
			'review' => $review
		];
		$this->Db->query('request', 0, 0, "INSERT INTO `lvl_web_request_perm`(`steamid`, `request`, `review`) VALUES(:steamid, :request, :review)", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_AdminAdded')];
	}

	public function editAdmin()
	{
		if ($this->access < 7) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$aid = $this->getAdmin($_POST['admin_id_edit']);
		if (empty($aid))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_AdminNotFound')];
		if (!preg_match('/^(7656119)([0-9]{10})$/', $_POST['steamid_edit']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_invalid_steam')];
		empty($_POST['request_edit']) ? $request = 0 : $request = 1;
		empty($_POST['review_edit']) ? $review = 0 : $review = 1;
		$data = [
			'aid' => $_POST['admin_id_edit'],
			'steamid' => $_POST['steamid_edit'],
			'request' => $request,
			'review' => $review
		];
		$this->Db->query('request', 0, 0, "UPDATE `lvl_web_request_perm` SET `steamid`=:steamid, `request`=:request, `review`=:review WHERE `aid`=:aid", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_AdminChanged')];
	}

	public function deletAdmin()
	{
		if ($this->access < 7) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$data = ['aid' => $_POST['param1']];
		$this->Db->query('request', 0, 0, "DELETE FROM `lvl_web_request_perm` WHERE aid = :aid", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_AdminRemoved'), 'location' => $this->General->arr_general['site'] . 'request/?page=perm'];
	}

	public function getListRequests()
	{
		$ListRequest = $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_requests` ORDER BY `sort` ASC");
		return $ListRequest;
	}

	public function getRequests()
	{
		$ListRequest = $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_requests` WHERE `status` = 1 ORDER BY `sort` ASC");
		return $ListRequest;
	}



	public function getQuestions($id)
	{
		if (!is_numeric($id))
			return;
		$data = ['request_id' => $id];
		$question = $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_question` WHERE `request_id` = :request_id ORDER BY `sort` ASC", $data);
		return $question;
	}

	public function getRequest($id)
	{
		if (!is_numeric($id))
			return;
		$data = ['id' => $id];
		$info = $this->Db->query('request', 0, 0, "SELECT * FROM `lvl_web_requests` WHERE `id` = :id", $data);
		return $info;
	}



	public function getList($id)
	{
		if (!is_numeric($id))
			return;
		$data = ['id' => $id];
		$info = $this->Db->query('request', 0, 0, "SELECT * FROM `lvl_web_request_list` WHERE `id` = :id", $data);
		return $info;
	}

	public function getCountList($id)
	{
		if (!is_numeric($id))
			return;
		$data = ['id' => $id];
		return $this->Db->queryNum('request', 0, 0, "SELECT COUNT(*) FROM `lvl_web_request_list` WHERE `rid` = :id", $data);
	}

	public function getAnswer($id)
	{
		if (!is_numeric($id))
			return;
		$data = ['id' => $id];
		$info = $this->Db->query('request', 0, 0, "SELECT * FROM `lvl_web_request_review` WHERE `id` = :id", $data);
		return $info;
	}

	public function getReview($rid)
	{
		if (!preg_match('/^[0-9]{1,5}$/', $rid))
			return;
		$data = ['rid' => $rid];
		$info = $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_review` WHERE `rid` = :rid", $data);
		return $info;
	}

	public function DelList()
	{
		if ($this->access < 5) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$data = ['id' => $_POST['param1']];
		$this->Db->query('request', 0, 0, "DELETE FROM `lvl_web_request_list` WHERE id = :id", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationDeleted'), 'location' => $this->General->arr_general['site'] . 'request/?page=list'];
	}

	public function getMyReview($rid)
	{
		if (!preg_match('/^[0-9]{1,5}$/', $rid))
			return;
		$data = ['rid' => $rid];
		$info = $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_review` WHERE `rid` = :rid AND `type` = 0", $data);
		return $info;
	}

	public function answer_admin()
	{
		if ($this->access < 3) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$bbcode = $this->OpenBB();
		if (empty($_POST['message']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_AnswerEmpty')];
		$message = $bbcode->convertToHtml(htmlentities($_POST['message']));
		$status = $this->getList($_POST['review_id'])['status'];
		$list = $this->getList($_POST['review_id']);
		if ($status >= '2')
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationClosed')];
		$data = [
			'rid' => $_POST['review_id'],
			'rqid' => $list['rid'],
			'steamid' => $_SESSION['steamid32'],
			'text' => $message,
			'date' => time(),
			'admin' => 1,
			'type' => 0
		];
		$this->Db->query('request', 0, 0, "INSERT INTO `lvl_web_request_review`(`rid`, `rqid`, `steamid`, `text`, `date`, `admin`, `type`) VALUES(:rid, :rqid, :steamid, :text, :date, :admin, :type)", $data);
		$this->Notifications->SendNotification(
			$list['steamid'],
			'_Applications1',
			'_NewAnswer',
			['name' => $this->General->checkName(con_steam64($list['steamid'])), 'text' => $_POST['message'], 'module_translation' => 'module_page_request'],
			$this->General->arr_general['site'] . 'request/?page=my&rid=' . $_POST['review_id'],
			'request',
			'_ToAnswer'
		);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_ResponseAdded')];
	}

	public function answer()
	{
		$bbcode = $this->OpenBB();
		if (empty($_POST['message']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_AnswerEmpty')];
		$message = $bbcode->convertToHtml(htmlentities($_POST['message']));
		$status = $this->getMyListId($_POST['review_id'])['status'];
		$list = $this->getMyListId($_POST['review_id']);
		if (empty($list))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationNotFound')];
		if ($status >= '2')
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationClosed')];
		$data = [
			'rid' => $_POST['review_id'],
			'rqid' => $list['rid'],
			'steamid' => $_SESSION['steamid32'],
			'text' => $message,
			'date' => time(),
			'admin' => 0,
			'type' => 0
		];
		$this->Db->query('request', 0, 0, "INSERT INTO `lvl_web_request_review`(`rid`, `rqid`, `steamid`, `text`, `date`, `admin`, `type`) VALUES(:rid, :rqid, :steamid, :text, :date, :admin, :type)", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_ResponseAdded')];
	}

	public function del_answer()
	{
		$data = ['id' => $_POST['param1']];
		$answer = $this->getAnswer($_POST['param1']);
		if (empty($answer))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_MsgNotFound')];
		if ($this->access < 5) {
			if ($answer['steamid'] == $_SESSION['steamid32']) {
				$this->Db->query('request', 0, 0, "DELETE FROM `lvl_web_request_review` WHERE id = :id", $data);
				return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_DelMessage')];
			} else {
				return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_own_message')];
			}
		} else {
			$this->Db->query('request', 0, 0, "DELETE FROM `lvl_web_request_review` WHERE id = :id", $data);
			return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_DelMessage')];
		}
	}

	public function status()
	{
		if ($this->access < 3) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		if (isset($_POST['param1'])) {
			$param = [
				'status' => $_POST['param1'],
				'id' => $_POST['param2']
			];
			$this->Db->query('request', 0, 0, "UPDATE lvl_web_request_list SET `status` =:status WHERE `id` = :id", $param);
			$list = $this->getList($_POST['param2']);
			$review = [
				'rid' => $_POST['param2'],
				'rqid' => $list['rid'],
				'steamid' => $_SESSION['steamid32'],
				'text' => $this->Translate->get_translate_module_phrase('module_page_request', '_ChangedTheStatus') . '"' . $this->status[$_POST['param1']] . '"',
				'date' => time(),
				'admin' => 1,
				'type' => 1
			];
			$this->Db->query('request', 0, 0, "INSERT INTO `lvl_web_request_review`(`rid`, `rqid`, `steamid`, `text`, `date`, `admin`, `type`) VALUES(:rid, :rqid, :steamid, :text, :date, :admin, :type)", $review);
			$this->Notifications->SendNotification(
				$list['steamid'],
				'_Applications1',
				'_NewStatus',
				['status' => $this->status[$_POST['param1']], 'module_translation' => 'module_page_request'],
				'',
				'request',
				'_ToConsider'
			);
			return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_StatusChangedTo') . '"' . $this->status[$_POST['param1']] . '"'];
		}
	}

	public function getMaxSort()
	{
		return $this->Db->query('request', 0, 0, "SELECT MAX(sort) as sort FROM `lvl_web_requests`")['sort'] ?? 1;
	}

	public function createRequest()
	{
		if ($this->access < 5) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$bbcode = $this->OpenBB();
		if (empty($_POST['title']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_EnterTheApplication')];
		empty($_POST['request_offon']) ? $status = 0 : $status = 1;
		empty($_POST['vk_offon']) ? $vk = 0 : $vk = 1;
		empty($_POST['discord_offon']) ? $discord = 0 : $discord = 1;
		empty($_POST['telegram_offon']) ? $telegram = 0 : $telegram = 1;
		empty($_POST['rules_offon']) ? $rules = 0 : $rules = 1;
		empty($_POST['criteria_offon']) ? $criteria = 0 : $criteria = 1;
		empty($_POST['age_offon']) ? $age_act = 0 : $age_act = 1;
		empty($_POST['age']) ? $age = 0 : $age = $_POST['age'];
		empty($_POST['hours_act']) ? $hours_act = 0 : $hours_act = 1;
		empty($_POST['hours']) ? $hours = 0 : $hours = $_POST['hours'];
		empty($_POST['server_offon']) ? $server_act = 0 : $server_act = 1;
		empty($_POST['default_server']) ? $default_server = NULL : $default_server = $_POST['default_server'];
		empty($_POST['time']) ? $time = 0 : $time = $_POST['time'];
		if (empty($_POST['ignore_server']))
			$ignore_server = NULL;
		else {
			$ignore_server = implode(";", $_POST['ignore_server']);
			$ignore_servers = explode(';', $ignore_server);
			if (in_array($_POST['default_server'], $ignore_servers))
				return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_TheServerIgnoredList')];
		}
		if (($server_act == 1) && empty($_POST['default_server']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_TheServerNotSelected')];
		$message = $bbcode->convertToHtml(htmlentities($_POST['message']));
		$data = [
			'title' => $_POST['title'],
			'sort' => $this->getMaxSort() + 1,
			'vk' => $vk,
			'discord' => $discord,
			'telegram' => $telegram,
			'rules' => $rules,
			'criteria' => $criteria,
			'time' => $time,
			'age_act' => $age_act,
			'age' => $age,
			'hours_act' => $hours_act,
			'hours' => $hours,
			'server' => $server_act,
			'default_server' => $default_server,
			'ignore_servers' => $ignore_server,
			'text' => $message,
			'status' => $status
		];
		$this->Db->query('request', 0, 0, "INSERT INTO `lvl_web_requests`( `title`, `sort`, `vk`, `discord`, `telegram`, `rules`, `criteria`, `time`, `age_act`, `age`, `hours_act`, `hours`, `server`, `default_server`, `ignore_servers`, `text`, `status`) 
																															VALUES ( :title, :sort, :vk, :discord, :telegram, :rules, :criteria, :time, :age, :age_act, :hours_act, :hours, :server, :default_server, :ignore_servers, :text, :status)", $data);
		$id = $this->Db->lastInsertId('request', 0, 0);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationAdded'), 'location' => $this->General->arr_general['site'] . 'request/?page=question&qid=' . $id];
	}

	public function editRequest()
	{
		if ($this->access < 5) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$bbcode = $this->OpenBB();
		$request = $this->getRequest($_POST['request_id_edit']);
		if (empty($request))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationNotFound')];
		else if (empty($_POST['title_edit']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_EnterTheApplication')];
		empty($_POST['request_offon_edit']) ? $status = 0 : $status = 1;
		empty($_POST['vk_offon_edit']) ? $vk = 0 : $vk = 1;
		empty($_POST['discord_offon_edit']) ? $discord = 0 : $discord = 1;
		empty($_POST['telegram_offon_edit']) ? $telegram = 0 : $telegram = 1;
		empty($_POST['rules_offon_edit']) ? $rules = 0 : $rules = 1;
		empty($_POST['criteria_offon_edit']) ? $criteria = 0 : $criteria = 1;
		empty($_POST['age_offon_edit']) ? $age_act = 0 : $age_act = 1;
		empty($_POST['age_edit']) ? $age = 0 : $age = $_POST['age_edit'];
		empty($_POST['hours_act_edit']) ? $hours_act = 0 : $hours_act = 1;
		empty($_POST['hours_edit']) ? $hours = 0 : $hours = $_POST['hours_edit'];
		empty($_POST['server_offon_edit']) ? $server = 0 : $server = 1;
		empty($_POST['time_edit']) ? $time_edit = 0 : $time_edit = $_POST['time_edit'];

		if ($server == 0) {
			$ignore_server = NULL;
			$default_server = NULL;
		} else {
			$default_server = $_POST['default_server_edit'];
			$ignore_server = implode(";", $_POST['ignore_server_edit']);
			$ignore_servers = explode(';', $ignore_server);
			if (in_array($_POST['default_server_edit'], $ignore_servers))
				return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_TheServerIgnoredList')];
		}
		$message_edit = $bbcode->convertToHtml(htmlentities($_POST['message_edit']));
		$data = [
			'title' => $_POST['title_edit'],
			'vk' => $vk,
			'discord' => $discord,
			'telegram' => $telegram,
			'server' => $server,
			'rules' => $rules,
			'criteria' => $criteria,
			'time' => $time_edit,
			'age_act' => $age_act,
			'age' => $age,
			'hours_act' => $hours_act,
			'hours' => $hours,
			'default_server' => $default_server,
			'ignore_servers' => $ignore_server,
			'text' => $message_edit,
			'status' => $status,
			'id' => $_POST['request_id_edit']
		];
		$this->Db->query('request', 0, 0, "UPDATE lvl_web_requests SET 
			`title` =:title,
			`vk` =:vk, 
			`discord` =:discord, 
			`telegram` =:telegram, 
			`server` =:server, 
			`rules` =:rules, 
			`criteria` =:criteria,
			`time` =:time, 
			`age_act` =:age_act, 
			`age` =:age, 
			`hours_act` =:hours_act, 
			`hours` =:hours, 
			`server` =:server, 
			`default_server` =:default_server, 
			`ignore_servers` =:ignore_servers, 
			`text` =:text, 
			`status` =:status 
			WHERE `id` = :id", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationChanged')];
	}

	public function changeSort($data, $type, $rid = '')
	{
		$order = json_decode($data, true);

		if (!is_array($order) || empty($order)) {
			return ['error' =>  $this->Translate->get_translate_module_phrase('module_page_request', '_errorIziToast')];
		}


		foreach ($order as $item) {
			$id = (int) ($item['id'] ?? 0);
			$position = (int) ($item['position'] ?? 0);

			if (!$id || !$position) {
				continue;
			}

			if ($type === 'request') {
				$this->Db->query(
					'request',
					0,
					0,
					"UPDATE `lvl_web_requests` SET `sort` = :sort WHERE `id` = :id",
					['sort' => $position, 'id' => $id]
				);
			} elseif ($type === 'question') {
				$this->Db->query(
					'request',
					0,
					0,
					"UPDATE `lvl_web_request_question` SET `sort` = :sort WHERE `id` = :id AND `request_id` = :rid",
					['sort' => $position, 'id' => $id, 'rid' => (int) $rid]
				);
			}
		}

		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_sorted')];
	}

	public function deletRequest()
	{
		if ($this->access < 5) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$request = $this->getRequest($_POST['param1']);
		if (empty($request))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationNotFound')];
		$data = ['id' => $_POST['param1']];
		$this->Db->query('request', 0, 0, "DELETE FROM `lvl_web_requests` WHERE `id` = :id", $data);
		$this->Db->query('request', 0, 0, "DELETE FROM `lvl_web_request_question` WHERE `request_id` = :id", $data);
		$this->Db->query('request', 0, 0, "DELETE FROM `lvl_web_request_list` WHERE `rid` = :id", $data);
		$this->Db->query('request', 0, 0, "DELETE FROM `lvl_web_request_review` WHERE `rqid` = :id", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationDeleted'), 'location' => $this->General->arr_general['site'] . 'request/?page=admin'];
	}

	public function getRequestQuestionAdmin($id)
	{
		if ($this->access < 5) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$data = ['request_id' => $id];
		$question = $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_question` WHERE `request_id` = :request_id ORDER BY `sort` ASC", $data);
		return $question;
	}

	public function getQuestionData($id)
	{
		if (!is_numeric($id))
			return;
		$data = ['id' => $id];
		$question = $this->Db->query('request', 0, 0, "SELECT * FROM `lvl_web_request_question` WHERE `id` = :id ORDER BY `sort` ASC", $data);
		return $question;
	}

	public function createQuestion()
	{
		if ($this->access < 5) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$data = [
			'request_id' => $_POST['request_id_question'],
			'question' => $_POST['question'],
			'desc' => $_POST['desc'],
			'clue' => $_POST['clue'],
			'sort' => 0
		];
		$this->Db->query('request', 0, 0, "INSERT INTO `lvl_web_request_question`(`request_id`, `question`, `desc`, `clue`, `sort`) VALUES(:request_id, :question, :desc, :clue, :sort)", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_QuestionAdded')];
	}

	public function editQuestion()
	{
		if ($this->access < 5) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$question = $this->getQuestionData($_POST['question_id_edit']);
		if (empty($question))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_QuestionNotFound')];
		$data = [
			'id' => $_POST['question_id_edit'],
			'request_id' => $_POST['request_id_edit'],
			'question' => $_POST['question_edit'],
			'desc' => $_POST['desc_edit'],
			'clue' => $_POST['clue_edit']
		];
		$this->Db->query('request', 0, 0, "UPDATE `lvl_web_request_question` SET `question`=:question, `request_id`=:request_id, `desc`=:desc, `clue`=:clue WHERE `id`=:id", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_QuestionChanged')];
	}

	public function deletQuestion()
	{
		if ($this->access < 5) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$question = $this->getQuestionData($_POST['param1']);
		if (empty($question))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_QuestionNotFound')];
		$data = ['id' => $_POST['param1']];
		$this->Db->query('request', 0, 0, "DELETE FROM `lvl_web_request_question` WHERE id = :id", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_QuestionDeleted'), 'location' => $this->General->arr_general['site'] . 'request/?page=question&qid=' . $_POST['param2']];
	}

	public function getTimeRequest($steam, $rid)
	{
		$data = [
			'steamid' => $steam,
			'rid' => $rid,
		];
		$time = $this->Db->query('request', 0, 0, "SELECT `date` FROM `lvl_web_request_list` WHERE `steamid`=:steamid AND `rid`=:rid ORDER BY `date` DESC LIMIT 1", $data);
		if ($time)
			return $time;
	}

	public function ServerName($sid)
	{
		$server = $this->Db->query('Core', 0, 0, "SELECT `name` FROM `lvl_web_servers` WHERE `id` = $sid");
		return $server['name'];
	}

	public function RequestsServer($sid)
	{
		return $this->Db->query('Core', 0, 0, "SELECT `id`, `name`, `server_stats` FROM `lvl_web_servers` WHERE `id` = $sid");
	}

	public function AllPlayTime($steam)
	{
		$total_time = 0;
		for ($i = 0; $i < $this->Db->table_count['LevelsRanks']; $i++) {
			$stats = $this->Db->queryAll('LevelsRanks', $this->Db->db_data['LevelsRanks'][$i]['USER_ID'], $this->Db->db_data['LevelsRanks'][$i]['DB_num'], "SELECT `steam`, `playtime` FROM `" . $this->Db->db_data['LevelsRanks'][$i]['Table'] . "` WHERE `steam`= '" . $steam . "' LIMIT 1");
			$total_time += $stats[0]['playtime'];
		}
		return floor($total_time / 3600);
	}

	public function PlayTime($steam, $sid)
	{
		$server = $this->RequestsServer($sid);
		$play_time = 0;
		$server_stats = explode(";", $server['server_stats']);
		$stats = $this->Db->query($server_stats[0], $server_stats[1], $server_stats[2], "SELECT `steam`, `playtime` FROM `" . $server_stats[3] . "` WHERE `steam`= '" . $steam . "' LIMIT 1");
		$play_time = $stats['playtime'];
		return floor($play_time / 3600);
	}


	public function getAllList($type = 0, $status = 0)
	{
		$sql_status = $status - 1;
		if (empty($type) && empty($status)) {
			return $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_list` ORDER BY date DESC");
		} elseif (!empty($type) && !empty($status)) {
			return $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_list` WHERE `rid` = $type AND `status` = $sql_status ORDER BY date DESC");
		} elseif (!empty($type)) {
			return $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_list` WHERE `rid` = $type ORDER BY date DESC");
		} elseif (!empty($status)) {
			return $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_list` WHERE `status` = $sql_status ORDER BY date DESC");
		}
	}

	public function getAllListPagination($min, $count, $type = 0, $status = 0)
	{
		$sql_status = $status - 1;
		if (empty($type) && empty($status)) {
			return $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_list` ORDER BY date DESC LIMIT " . ($min) . "," . $count . "");
		} elseif (!empty($type) && !empty($status)) {
			return $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_list` WHERE `rid` = $type AND `status` = $sql_status ORDER BY date DESC LIMIT " . ($min) . "," . $count . "");
		} elseif (!empty($type)) {
			return $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_list` WHERE `rid` = $type ORDER BY date DESC LIMIT " . ($min) . "," . $count . "");
		} elseif (!empty($status)) {
			return $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_list` WHERE `status` = $sql_status ORDER BY date DESC LIMIT " . ($min) . "," . $count . "");
		}
	}

	public function getMyList()
	{
		$data = [
			'steamid' => $_SESSION['steamid32']
		];
		return $this->Db->queryAll('request', 0, 0, "SELECT * FROM `lvl_web_request_list` WHERE `steamid` = :steamid ORDER BY date DESC", $data);
	}

	public function getMyListId($id)
	{
		$data = [
			'steamid' => $_SESSION['steamid32'],
			'id' => $id,
		];
		return $this->Db->query('request', 0, 0, "SELECT * FROM `lvl_web_request_list` WHERE `steamid` = :steamid AND `id` = :id ORDER BY date DESC", $data);
	}

	public function SendRequest()
	{
		if (empty($_SESSION['steamid32'])) {
			header('Location: ' . $this->General->arr_general['site'] . 'request');
			exit;
		}
		$settings = $this->RequestsSettings();
		$r_id = $_POST['request_id'];
		$request = $this->getRequest($r_id);
		if (empty($request))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationNotFound')];
		$questions = $this->getQuestions($r_id);
		$time = $this->getTimeRequest($_SESSION['steamid32'], $r_id);
		$requertDate = $request['time'] + (empty($time['date']) ? 0 : $time['date']);
		if ($requertDate > time()) {
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_NextApplication') . date('d.m.Y H:i', $requertDate)];
		} else {
			if ($request['age_act']) {
				if (!preg_match('/^[0-9]{1,2}$/', $_POST['age']))
					return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_AgeSpecifiedIncorrectly')];
				if ($request['age'] > $_POST['age'])
					return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_YourAgeYoung')];
			}
			$PlayTime = $this->AllPlayTime($_SESSION['steamid32']);
			if ($request['hours_act']) {
				if ($request['hours'] > $PlayTime)
					return ['error' => $this->Translate->get_translate_module_phrase('module_page_request', '_TooFewHours')];
			}
			$data = '';
			$server = $this->Translate->get_translate_module_phrase('module_page_request', '_Notspecified');
			foreach ($_POST as $key => $value) {
				if ($key == "discord") {
					$data .= "<span>Discord:</span> " . htmlentities($value) . " \n";
				}
				if ($key == "telegram") {
					if (empty($value)) {
						$data .= "<span>Telegram:</span> " . $this->Translate->get_translate_module_phrase('module_page_request', '_notspecified') . " \n";
					} else {
						$data .= "<span>Telegram:</span> https://t.me/" . htmlentities($value) . " \n";
					}
				}
				if ($key == "vk") {
					if (empty($value)) {
						$data .= "VK: " . $this->Translate->get_translate_module_phrase('module_page_request', '_notspecified') . " \n";
					} else {
						$data .= "<span>VK:</span> https://vk.com/" . htmlentities($value) . " \n";
					}
				}
				foreach ($questions as $q) {
					if ($key == "question" . $q['id']) {
						$data .= "<span>" . $q['question'] . ":</span> " . htmlentities($value) . " \n";
					}
				}
				if ($key == "age") {
					$data .= "<span>" . $this->Translate->get_translate_module_phrase('module_page_request', '_Age') . ":</span> $value \n";
				}
				if ($key == "server") {
					$data .= "<span>" . $this->Translate->get_translate_module_phrase('module_page_request', '_GameServer') . ":</span> " . $this->ServerName($value) . " \n";
					$server = $value;
				}
				if ($key == "rules") {
					$data .= "<span>" . $this->Translate->get_translate_module_phrase('module_page_request', '_Rules') . ":</span> $value \n";
				}
				if ($key == "criteria") {
					$data .= "<span>" . $this->Translate->get_translate_module_phrase('module_page_request', '_Criteria') . ":</span> $value \n";
				}
			}
			$data = rtrim($data, '\n');
			$dataRequert = [
				'rid' => $r_id,
				'steamid' => $_SESSION['steamid32'],
				'text' => $data,
				'server' => $server,
				'playtime' => $PlayTime,
				'date' => time(),
				'status' => 0
			];
			$this->Db->query('request', 0, 0, "INSERT INTO `lvl_web_request_list`(`rid`, `steamid`, `text`, `server`, `playtime`, `date`, `status`) VALUES (:rid, :steamid, :text, :server, :playtime, :date, :status)", $dataRequert);
			$id = $this->Db->lastInsertId('request', 0, 0);
			if ($settings['auth']) {
				$this->DiscordMsg($id, $_SESSION['steamid64'], $request['title'], $server);
			}
			$result = $this->Db->queryAll('request', 0, 0, "SELECT `steamid` FROM `lvl_web_request_perm` UNION SELECT `steamid` FROM `lvl_web_admins`");
			foreach ($result as $key) {
				$this->Notifications->SendNotification(
					$key['steamid'],
					'_Applications1',
					'_NewAnswer1',
					['name' => $this->General->checkName(con_steam64($dataRequert['steamid'])), 'number' => $id, 'module_translation' => 'module_page_request'],
					$this->General->arr_general['site'] . 'request/?page=review&rid=' . $id,
					'request',
					'_ToConsider'
				);
			}
			return ['success' => $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationSent')];
		}
	}

	protected function DiscordMsg($rid, $steam, $title, $server)
	{
		if (isset($_SESSION['steamid32'])) {
			$json = json_encode([
				"file" => "content",
				"embeds" =>
				[
					[
						"color" => hexdec("#f6b949"),
						"title" => ":new: " . $this->Translate->get_translate_module_phrase('module_page_request', '_NewApplicationWebsite'),
						"description" => ":arrow_forward: [**" . $this->Translate->get_translate_module_phrase('module_page_request', '_GoToReview') . "**](http:" . $this->General->arr_general['site'] . "request/?page=review&rid=$rid)",
						"type" => "content",
						"footer" =>
						[
							"text" => $this->General->arr_general['full_name'] . ' ' . date('d.m.Y H:i:s')
						],
						"image" => [
							"url" => "http:" . $this->General->arr_general['site'] . "/app/modules/module_page_request/assets/img/banner_req.png"
						],
						"author" =>
						[
							"name" => $this->Translate->get_translate_module_phrase('module_page_request', '_RequestUser1') . $this->General->checkName($steam),
							"url" => 'http:' . $this->General->arr_general['site'] . "profiles/$steam/?search=1",
							"icon_url" => $this->General->getAvatar($steam, 2)
						],
						"fields" =>
						[
							[
								"name" => ":hash: " . $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationNumber'),
								"value" => "```$rid```",
								"inline" => true
							],
							[
								"name" => ":clock5: " . $this->Translate->get_translate_module_phrase('module_page_request', '_Time'),
								"value" => '```' . date('d.m.Y H:i:s') . '```',
								"inline" => true
							],
							[
								"name" => ":pencil: " . $this->Translate->get_translate_module_phrase('module_page_request', '_ApplicationName'),
								"value" => "```$title```"
							],
							[
								"name" => ":desktop: " . $this->Translate->get_translate_module_phrase('module_page_request', '_GameServer'),
								"value" => is_numeric($server) ? "```" . $this->ServerName($server) . "```" : "```" . $server . "```"
							]
						]
					]
				]
			]);
			$cl = curl_init($this->RequestsSettings()['url']);
			curl_setopt($cl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
			curl_setopt($cl, CURLOPT_POST, 1);
			curl_setopt($cl, CURLOPT_POSTFIELDS, $json);
			curl_exec($cl);
		}
	}

	public function changeCategory($id, $rid)
	{
		$this->Db->query('request', 0, 0, "UPDATE `lvl_web_request_list` SET `rid` = :data WHERE `id` = :data1", ['data' => $id, 'data1' => $rid]);
		return ['status' => 'success', 'text' => $rid . $id];
	}
}
