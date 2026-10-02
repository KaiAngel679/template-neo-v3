<?php

namespace app\modules\module_page_open_case\ext;

use app\modules\module_page_open_case\ext\Rcon;

class Open_case
{
	public $Modules;
	public $Translate;
	public $General;
	public $Db;
	public $Notifications;
	public $Auth;

	public function __construct($Translate, $Notifications, $General, $Modules, $Db, $Auth)
	{
		$this->Modules = $Modules;
		$this->Translate = $Translate;
		$this->General = $General;
		$this->Db = $Db;
		$this->Notifications = $Notifications;
		$this->Auth = $Auth;
	}

	public function BCheckPlayer()
	{
		$param = ['auth' => $_SESSION['steamid32']];
		$player = $this->Db->query('lk', 0, 0, "SELECT * FROM `lk` WHERE `auth` LIKE :auth LIMIT 1", $param);
		if (empty($player)) {
			$params = [
				'auth' 		=> $_SESSION['steamid32'],
				'name'		=> action_text_clear($this->General->checkName($_SESSION['steamid64']))
			];
			$this->Db->query('lk', 0, 0, "INSERT INTO lk(auth, name, cash, all_cash) VALUES (:auth,:name,0,0)", $params);
		}
	}

	public function Balance()
	{
		$result = $this->Db->query('lk', 0, 0, "SELECT * FROM lk WHERE auth = '" . $_SESSION['steamid32'] . "' LIMIT 1");
		$cash = 0;
		if (!empty($result['cash'])) {
			$cash = $result['cash'];
		}
		return $cash;
	}

	public function CaseSettings()
	{
		return $this->Db->query('Core', 0, 0, "SELECT * FROM `cases_settings`");
	}

	public function CaseCategory()
	{
		return $this->Db->queryAll('Core', 0, 0, "SELECT * FROM `cases_category` ORDER BY sort ASC");
	}

	public function EditSettings($post)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true) exit;
		empty($post['webhook_offon']) ? $webhook_offon = 0 : $webhook_offon = 1;
		$param = ['webhook' => $post['webhook'], 'webhook_offon' => $webhook_offon, 'speed' => $post['speed'], 'course' => $post['course']];
		$this->Db->query('Core', 0, 0, "UPDATE `cases_settings` SET `webhook`=:webhook, `webhook_offon`=:webhook_offon, `speed`=:speed, `course`=:course", $param);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_Saved')];
	}

	public function Speed($param = 1)
	{
		switch ($param) {
			case '1':
				$speed = 7;
				break;
			case '2':
				$speed = 20;
				break;
			case '3':
				$speed = 35;
				break;
		}
		return $speed;
	}

	public function createCase($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		if (empty($post['case_name']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterNameCase')];
		else if (empty($post['case_sort']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSortCase')];
		else if (!isset($post['case_cat']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterCatCase')];
		if (empty($post['case_price']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterAmountCase')];
		else if (!preg_match('/^[0-9]{1,1000}.[0-9]{1,2}$/', $this->WM($post['case_price'])))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_FormatAmountCase')];
		else if (empty($_FILES['case_img']['tmp_name']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_ChangeCasePNG')];
		$size = getimagesize($_FILES['case_img']['tmp_name']);
		if ($size[2] != IMAGETYPE_PNG)
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_OnlyPNG')];
		else if ($size[0] > 1500 || $size[1] > 1500)
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_SizePNG')];
		if (!file_exists(MODULES . 'module_page_open_case/assets/cases')) mkdir(MODULES . 'module_page_open_case/assets/cases', 0777);
		$apend = MODULES . 'module_page_open_case/assets/cases/' . date('YmdHis') . rand(100, 1000) . '.png';
		move_uploaded_file($_FILES['case_img']['tmp_name'], $apend);
		$data = ['case_name' => $post['case_name'], 'case_type' => $post['case_type'], 'case_cat' => $post['case_cat'], 'case_sort' => $post['case_sort'], 'case_price' => $post['case_price'], 'case_img' => $apend];
		$this->Db->query('Core', 0, 0, "INSERT INTO cases(case_name, case_type, case_cat, case_sort, case_price, case_img) VALUES (:case_name, :case_type, :case_cat, :case_sort, :case_price, :case_img)", $data);
		$id = $this->Db->lastInsertId('Core', 0, 0);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_Saved'), 'location' => $this->General->arr_general['site'] . 'cases/?section=case&id=' . $id];
	}

	public function getCaseData($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$img = '';
		$sql_data = [
			'id' => $post['param']
		];
		$case_data = $this->Db->query('Core', 0, 0, "SELECT * FROM cases WHERE id = :id", $sql_data);
		if (!empty($case_data)) {
			if (isset($case_data['case_img'])) {
				$data_img = $this->General->arr_general['site'] . $case_data['case_img'];
				$img = <<<HTML
				<img width="100" src="{$data_img}">
			HTML;
			}
			return ['case' => $case_data, 'title' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EditCase') . ' - ' . $case_data['case_name'], 'img' => $img, 'btn' => 'CasesAjax("edit_case", $("#modal_case").attr("case"), "case_form")'];
		} else {
			return ['error' => 'Данные не найдены'];
		}
	}

	public function getSubjectInfo($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$img = '';
		$sql_data = [
			'id' => $post['param']
		];
		$subject_data = $this->Db->query('Core', 0, 0, "SELECT * FROM cases_subjects WHERE id = :id", $sql_data);
		if (!empty($subject_data)) {
			if (isset($subject_data['subject_img'])) {
				$data_img = $this->General->arr_general['site'] . $subject_data['subject_img'];
				$img = <<<HTML
				<img width="100" src="{$data_img}">
			HTML;
			}
			$subject_data['subject_class'] = $this->bg($subject_data['subject_class']);
			return ['subject' => $subject_data, 'title' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EditItem') . ' - ' . $subject_data['subject_name'], 'img' => $img, 'btn' => 'CasesAjax("edit_subject", $("#modal_subject").attr("subject"), "subject_form")'];
		} else {
			return ['error' => 'Данные не найдены'];
		}
	}

	public function editCase($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$case = $this->getPriceCase($post['param']);
		if (empty($case)) return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_CaseNotFound')];
		else if (empty($post['case_name']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterNameCase')];
		else if (empty($post['case_sort']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSortCase')];
		else if (!isset($post['case_cat']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterCatCase')];
		if (empty($post['case_price']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterAmountCase')];
		else if (!preg_match('/^[0-9]{1,1000}.[0-9]{1,2}$/', $this->WM($post['case_price'])))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_FormatAmountCase')];
		$caseImg = $case['case_img'];
		if (!empty($_FILES['case_img']['tmp_name'])) {
			$size = getimagesize($_FILES['case_img']['tmp_name']);
			if ($size[2] != IMAGETYPE_PNG)
				return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_OnlyPNG')];
			else if ($size[0] > 1500 || $size[1] > 1500)
				return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_SizePNG')];
			unlink($case['case_img']);
			$apend = MODULES . 'module_page_open_case/assets/cases/' . date('YmdHis') . rand(100, 1000) . '.png';
			move_uploaded_file($_FILES['case_img']['tmp_name'], $apend);
			$caseImg = $apend;
		}
		$data = ['case_name' => $post['case_name'], 'case_type' => $post['case_type'], 'case_cat' => $post['case_cat'], 'case_sort' => $post['case_sort'], 'case_price' => $post['case_price'], 'case_img' => $caseImg, 'id' => $post['param']];
		$this->Db->query('Core', 0, 0, "UPDATE cases SET case_name =:case_name, case_type =:case_type, case_cat =:case_cat, case_sort =:case_sort, case_price=:case_price, case_img=:case_img WHERE id = :id", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_Saved')];
	}

	public function deletCase($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$case = $this->getPriceCase($post['param']);
		if (empty($case)) return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_CaseNotFound')];
		if (file_exists($case['case_img']))
			unlink($case['case_img']);
		$data = ['id' => $post['param']];
		$this->Db->query('Core', 0, 0, "DELETE FROM `cases` WHERE id = :id", $data);
		$this->Db->query('Core', 0, 0, "DELETE FROM `cases_subjects` WHERE case_id = :id", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_CaseDeleted')];
	}

	public function getCatData($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$sql_data = [
			'id' => $post['param']
		];
		$cat_data = $this->Db->query('Core', 0, 0, "SELECT * FROM `cases_category` WHERE id = :id", $sql_data);
		if (!empty($cat_data)) {
			return ['cat' => $cat_data, 'title' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EditCat') . ' - ' . $cat_data['name'], 'btn' => 'CasesAjax("edit_cat", $("#modal_cat").attr("cat"), "cat_form")'];
		} else {
			return ['error' => 'Данные не найдены'];
		}
	}

	public function createCategory($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		if (empty($post['cat_name']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterNameCase')];
		else if (empty($post['cat_sort']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSortCase')];
		$data = ['cat_name' => $post['cat_name'], 'cat_sort' => $post['cat_sort']];
		$this->Db->query('Core', 0, 0, "INSERT INTO cases_category(name, sort) VALUES (:cat_name, :cat_sort)", $data);
		return ['success' => 'Категория добавлена!'];
	}

	public function editCat($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$cat = $this->getCatData($post['param']);
		if (empty($cat)) return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_CatNotFound')];
		else if (empty($post['cat_name']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterNameCase')];
		else if (empty($post['cat_sort']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSortCase')];
		$data = ['cat_name' => $post['cat_name'], 'cat_sort' => $post['cat_sort'], 'id' => $post['param']];
		$this->Db->query('Core', 0, 0, "UPDATE cases_category SET name =:cat_name, sort =:cat_sort WHERE id = :id", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_Saved')];
	}

	public function deletCat($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$cat = $this->getCatData($post['param']);
		if (empty($cat)) return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_CatNotFound')];
		$data = ['id' => $post['param']];
		$this->Db->query('Core', 0, 0, "DELETE FROM `cases_category` WHERE id = :id", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_CatDeleted')];
	}

	public function createSubject($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		if (empty($post['subject_name']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSubjectName')];
		else if (empty($post['subject_desc']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSubjectShortDesc')];
		else if (empty($post['subject_sort']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSubjectSort')];
		else if (empty($post['subject_content']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSubjectPrize')];
		else if (empty($_FILES['subject_img']['tmp_name']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_ChangeSubjectPNG')];
		$size = getimagesize($_FILES['subject_img']['tmp_name']);
		if ($size[2] != IMAGETYPE_PNG)
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_OnlyPNG')];
		else if ($size[0] > 1500 || $size[1] > 1500)
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_SizePNG')];
		if (!file_exists(MODULES . 'module_page_open_case/assets/cases')) mkdir(MODULES . 'module_page_open_case/assets/cases', 0777);
		$apend = MODULES . 'module_page_open_case/assets/cases/' . date('YmdHis') . rand(100, 1000) . '.png';
		move_uploaded_file($_FILES['subject_img']['tmp_name'], $apend);
		if (empty($post['subject_sale'])) $sale = 0;
		else $sale = $post['subject_sale'];
		$data = [
			'server_id' => $post['subject_server'],
			'case_id' => $post['case_id_subject'],
			'subject_name' => $post['subject_name'],
			'subject_desc' => $post['subject_desc'],
			'subject_class' => $this->bgReturn($post['subject_class']),
			'subject_img' => $apend,
			'subject_type' => $post['subject_type'],
			'subject_content' => $post['subject_content'],
			'subject_chance' => $post['subject_chance'],
			'subject_sort' => $post['subject_sort'],
			'subject_sale' => $sale
		];
		$this->Db->query('Core', 0, 0, "INSERT INTO cases_subjects(server_id, case_id, subject_name, subject_desc, subject_class, subject_img, subject_type, subject_content, subject_chance, subject_sale, subject_sort) VALUES(:server_id, :case_id, :subject_name, :subject_desc, :subject_class, :subject_img, :subject_type, :subject_content, :subject_chance, :subject_sale, :subject_sort)", $data);
		return ['success' => 'Предмет ' . $post['subject_name'] . ' добавлен!'];
	}

	public function editSubject($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$subject = $this->getSubjectData($post['param']);
		if (empty($subject)) return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_SubjectNotFound')];
		if (empty($post['subject_name']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSubjectName')];
		else if (empty($post['subject_desc']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSubjectShortDesc')];
		else if (empty($post['subject_sort']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSubjectSort')];
		else if (empty($post['subject_content']))
			return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_EnterSubjectPrize')];
		$img = $subject['subject_img'];
		if (!empty($_FILES['subject_img']['tmp_name'])) {
			unlink($img);
			$size = getimagesize($_FILES['subject_img']['tmp_name']);
			if ($size[2] != IMAGETYPE_PNG)
				return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_OnlyPNG')];
			else if ($size[0] > 1500 || $size[1] > 1500)
				return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_SizePNG')];
			$apend = MODULES . 'module_page_open_case/assets/cases/' . date('YmdHis') . rand(100, 1000) . '.png';
			move_uploaded_file($_FILES['subject_img']['tmp_name'], $apend);
			$img = $apend;
		}
		if (empty($post['subject_sale'])) $sale = 0;
		else $sale = $post['subject_sale'];
		$data = [
			'id' => $post['param'],
			'server_id' => $post['subject_server'],
			'subject_name' => $post['subject_name'],
			'subject_desc' => $post['subject_desc'],
			'subject_class' => $this->bgReturn($post['subject_class']),
			'subject_img' => $img,
			'subject_type' => $post['subject_type'],
			'subject_content' => $post['subject_content'],
			'subject_chance' => $post['subject_chance'],
			'subject_sort' => $post['subject_sort'],
			'subject_sale' => $sale
		];
		$this->Db->query('Core', 0, 0, "UPDATE cases_subjects SET server_id=:server_id, subject_name=:subject_name, subject_desc=:subject_desc, subject_class=:subject_class, subject_img=:subject_img, subject_type=:subject_type, subject_content=:subject_content, subject_chance=:subject_chance, subject_sale=:subject_sale, subject_sort=:subject_sort WHERE id=:id", $data);
		return ['success' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_Saved')];
	}

	public function deletSubject($post)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$subject = $this->getSubjectData($post['param']);
		if (empty($subject)) return ['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_SubjectNotFound')];
		if (file_exists($subject['subject_img']))
			unlink($subject['subject_img']);
		$data = ['id' => $post['param']];
		$this->Db->query('Core', 0, 0, "DELETE FROM `cases_subjects` WHERE id = :id", $data);
		return ['success' => 'Предмет удален!'];
	}
	public function getCases()
	{
		return $this->Db->queryAll('Core', 0, 0, "SELECT * FROM cases WHERE id IN (SELECT case_id FROM cases_subjects  GROUP BY case_id HAVING COUNT(case_id) >= 3) ORDER BY case_sort ASC");
	}

	public function getOpens($id)
	{
		$data = ['case_id' => $id];
		$opens = $this->Db->queryAll('Core', 0, 0, "SELECT COUNT(case_id) FROM cases_open WHERE case_id=:case_id", $data);
		return $opens[0]['COUNT(case_id)'];
	}

	public function getCasesAdmin()
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		return $this->Db->queryAll('Core', 0, 0, "SELECT * FROM cases");
	}

	public function getCaseSubjectsAdmin($id)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$data = ['case_id' => $id];
		$subjects =  $this->Db->queryAll('Core', 0, 0, "SELECT * FROM cases_subjects WHERE case_id = :case_id", $data);
		return $subjects;
	}

	public function getWins()
	{
		if (empty($_SESSION['steamid32']) || IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$data = ['steam_id' => $_SESSION['steamid32'],];
		return $this->Db->queryAll('Core', 0, 0, "SELECT * FROM cases_wins WHERE steam_id = :steam_id ORDER BY id DESC", $data);
	}
	public function getWinsData($id)
	{
		$data = ['steam_id' => $_SESSION['steamid32'], 'id' => $id];
		return $this->Db->query('Core', 0, 0, "SELECT * FROM cases_wins WHERE steam_id = :steam_id AND id =:id", $data);
	}

	public function getCaseSubjects($id)
	{
		if (!preg_match('/^[0-9]{1,3}$/', $id)) return;
		$data = ['case_id' => $id];
		$subjects =  $this->Db->queryAll('Core', 0, 0, "SELECT * FROM cases_subjects WHERE case_id = :case_id ORDER BY subject_sort ASC", $data);
		return $subjects;
	}

	public function getSubjectData($id)
	{
		if (!preg_match('/^[0-9]{1,3}$/', $id)) return;
		$data = ['id' => $id];
		return $this->Db->query('Core', 0, 0, "SELECT * FROM cases_subjects WHERE id = :id", $data);
	}

	public function getCaseDatabyID($id)
	{
		if (!preg_match('/^[0-9]{1,3}$/', $id)) return;
		$data = ['id' => $id];
		return $this->Db->query('Core', 0, 0, "SELECT * FROM cases WHERE id = :id", $data);
	}

	public function getPriceCase($id)
	{
		if (!preg_match('/^[0-9]{1,3}$/', $id)) return;
		$data = ['id' => $id];
		$price = $this->Db->queryAll('Core', 0, 0, "SELECT * FROM cases WHERE id = :id", $data);
		return $price[0];
	}

	public function openCasesListCount()
	{
		$cases =  $this->Db->query('Core', 0, 0, "SELECT count(*) as count FROM cases_open LIMIT 1");
		return isset($cases['count']) ? (int)$cases['count'] : 0;
	}

	public function openCasesList()
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$cases =  $this->Db->queryAll('Core', 0, 0, "SELECT * FROM cases_open ORDER BY date DESC");
		if (!empty($cases)) {
			$array = [];
			foreach ($cases as $key) {
				$caseInfo = $this->getPriceCase($key['case_id']);
				$subJson = json_decode($key['wins'], true);
				array_push($array, [
					'steam_id' => $key['steam_id'],
					'case_id' => $key['case_id'],
					'case_img' => $this->ImgLoad($caseInfo['case_img']),
					'case_name' => $caseInfo['case_name'],
					'subject_img' => $this->ImgLoad($subJson['subject_img']),
					'subject_name' => $subJson['subject_name'],
					'date' => date('m.d.Y H:i:s', $key['date']),

				]);
			}
			return $array;
		}
		return false;
	}

	public function openCasesListPagination($min, $count)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$min = (int)$min;
		$count = (int)$count;
		$cases =  $this->Db->queryAll('Core', 0, 0, "SELECT * FROM cases_open ORDER BY date DESC LIMIT " . ($min) . "," . $count . "");
		if (!empty($cases)) {
			$array = [];
			foreach ($cases as $key) {
				$caseInfo = $this->getPriceCase($key['case_id']);
				$subJson = json_decode($key['wins'], true);
				array_push($array, [
					'steam_id' => $key['steam_id'],
					'case_id' => $key['case_id'],
					'case_img' => $this->ImgLoad($caseInfo['case_img']),
					'case_name' => $caseInfo['case_name'],
					'subject_img' => $this->ImgLoad($subJson['subject_img']),
					'subject_name' => $subJson['subject_name'],
					'date' => date('m.d.Y H:i:s', $key['date']),

				]);
			}
			return $array;
		}
		return false;
	}

	public function WinsList()
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		return $this->Db->queryAll('Core', 0, 0, "SELECT * FROM cases_wins ORDER BY id DESC");
	}

	public function WinsListCount()
	{
		$wins = $this->Db->query('Core', 0, 0, "SELECT COUNT(*) as count FROM cases_wins LIMIT 1");
		return isset($wins['count']) ? $wins['count'] : 0;
	}

	public function WinsListPagination($min, $count)
	{
		if (!isset($_SESSION['user_admin']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$min = (int)$min;
		$count = (int)$count;
		return $this->Db->queryAll('Core', 0, 0, "SELECT * FROM cases_wins ORDER BY id DESC LIMIT " . ($min) . "," . $count . "");
	}

	public function getTimeFreeOpen($session, $id)
	{
		$data = ['steam_id' => $session, 'case_id' => $id];
		$free = $this->Db->queryAll('Core', 0, 0, "SELECT date FROM cases_open WHERE steam_id=:steam_id AND case_id=:case_id ORDER BY date DESC LIMIT 1", $data);
		if ($free)
			return $free[0];
	}

	public function loadRoulette($id)
	{
		if (!preg_match('/^[0-9]{1,3}$/', $id)) return;
		$subjects = $this->getCaseSubjects($id);
		shuffle($subjects);
		$return = array();
		$count = 0;
		$count2 = 0;
		unset($_SESSION['cases']);
		foreach ($subjects as $key) {
			$_SESSION['cases'][$key['id']] = $count2++;
			array_push($return, array(
				'style' 	=> $key['subject_class'],
				'data' 		=> $count++,
				'img' 		=> $key['subject_img'],
				'desc'		=> $key['subject_desc'],
				'name' 		=> $key['subject_name']
			));
		}
		exit(json_encode(array_reverse($return)));
	}

	public function openCase($id)
	{
		if (empty($_SESSION['steamid32']) && IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		if (!preg_match('/^[0-9]{1,3}$/', strip_tags($id))) return;
		$subjects = $this->getCaseSubjects($id);
		$casePrice = $this->getPriceCase($id);
		if (empty($subjects) || empty($casePrice)) exit(json_encode(['error' => $this->Translate->get_translate_module_phrase('module_page_open_case', '_Error')]));
		$data = ['auth' => $_SESSION['steamid32']];
		$balance = $this->Balance();
		if ($casePrice['case_type'] == 1) {
			if ($balance < $casePrice['case_price'])
				exit(json_encode(['style' => 'transparent', 'message' => '
				                	<div class="bonuses-title">Ошибка</div>
									<div style="margin: 30px;text-align: center;color: var(--custom-text-color);font-size: 18px;font-weight: 500;letter-spacing: 1px;">
									' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_NoMoney') . '
									</div>']));
			$balance = $balance - $casePrice['case_price'];
			$data = ['auth' => $_SESSION['steamid32'], 'cash' => $balance];
			$this->Db->query('lk', 0, 0, "UPDATE lk SET cash =:cash WHERE auth=:auth", $data);
		} else if ($casePrice['case_type'] == 2) {
			$free = $this->getTimeFreeOpen($_SESSION['steamid32'], $id);
			$openDate = $casePrice['case_price'] + (empty($free['date']) ? 0 : $free['date']);
			if ($openDate > time()) {
				$openDate - time();
				exit(json_encode(['date' => $openDate, 'style' => 'transparent', 'message' => '
				                	<div class="bonuses-title">Ошибка</div>
									<div style="text-align: center;color: var(--custom-text-color);font-size: 18px;font-weight: 500;letter-spacing: 1px;">
									' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_WillAvailable') . '<br>
										<div class="eTimer"></div>
										</div>']));
			}
		}
		foreach ($subjects as $key) {
			$subjectsCount[$key['id']] = $key['subject_chance'];
		}
		$subjectId = $this->roulette($subjectsCount);
		$randomWin = $_SESSION['cases'][$subjectId];
		$subjectInfo = $this->getSubjectData($subjectId);
		if (empty($subjectInfo)) exit(json_encode(['error' => 'Error']));
		switch ($subjectInfo['subject_class']) {
			case 'gold':
				$color = '725a39';
				break;
			case 'red':
				$color = 'ec8492';
				break;
			case 'pink':
				$color = 'df0117';
				break;
			case 'purple':
				$color = 'c555ff';
				break;
			case 'blue':
				$color = '5655d3';
				break;
			case 'turquoise':
				$color = '2afdf4';
				break;
			case 'grey':
				$color = '3e3e3e';
				break;
			default:
				$color = 'cacaca';
				break;
		}
		$dataOpen = [
			'steam_id'			=> $_SESSION['steamid32'],
			'case_id'				=> $id,
			'wins'					=> json_encode([
				'subject_name' 	=> $subjectInfo['subject_name'],
				'subject_desc' => $subjectInfo['subject_desc'],
				'subject_class' => $subjectInfo['subject_class'],
				'subject_img' => $subjectInfo['subject_img'],
				'subject_sale' 	=> $subjectInfo['subject_sale']
			]),
			'date'		=> time()
		];
		$this->Db->query('Core', 0, 0, "INSERT INTO cases_open(steam_id, case_id, wins, date) VALUES (:steam_id, :case_id, :wins, :date)", $dataOpen);
		$data = [
			'subject_id'		=> $subjectId,
			'subject_name'	=> $subjectInfo['subject_name'],
			'subject_desc'	=> $subjectInfo['subject_desc'],
			'subject_style'	=> $subjectInfo['subject_class'],
			'subject_img'		=> $subjectInfo['subject_img'],
			'steam_id'			=> $_SESSION['steamid32'],
			'sale'					=> $subjectInfo['subject_sale'],
			'up' 						=> 0,
			'sell' 					=> 0
		];
		$this->Db->query('Core', 0, 0, 'INSERT INTO cases_wins(subject_id, subject_name, subject_desc, subject_style, subject_img, steam_id, sale, up, sell) VALUES(:subject_id, :subject_name, :subject_desc, :subject_style, :subject_img, :steam_id, :sale, :up, :sell)', $data);
		$sales = $this->Db->lastInsertId('Core', 0, 0);
		switch ($subjectInfo['subject_type']) {
			case 1:
				$data = ['steam_id' => $_SESSION['steamid32'], 'sell' => 1, 'up' => 1, 'id' => $sales];
				$this->Db->query('Core', 0, 0, 'UPDATE cases_wins SET sell =:sell, up =:up WHERE id=:id AND steam_id=:steam_id', $data);
				$wincash = $balance + $subjectInfo['subject_content'];
				$data = ['auth' => $_SESSION['steamid32'], 'cash' => $wincash];
				$this->Db->query('lk', 0, 0, 'UPDATE lk SET cash =:cash WHERE auth=:auth', $data);
				$html = '<div class="bonuses-title"' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
							  	<img class="subject-image" style="width: 15rem; height: 15rem;" src="' . $this->General->arr_general['site'] . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '">
									<div><span class="cases__yourwin" style="color:' . $color . ';text-overflow: ellipsis;font-size: 35px;font-weight: 700;letter-spacing: 1px;">' . $subjectInfo['subject_name'] . '</span><br>
									<span style="color: #fff;text-overflow: ellipsis;font-size: 25px;">' . $subjectInfo['subject_desc'] . '</span>
								</div>';
				$return = array(
					'style'		=> 'transparent',
					'ubal' 		=> number_format($balance, 0, ' ', ' ') . ' ' . $this->CaseSettings()['course'],
					'wcash' 	=> number_format($wincash, 0, ' ', ' ') . ' ' . $this->CaseSettings()['course'],
					'live' 		=> $subjectId,
					'win' 		=> $randomWin,
					'html' 		=> $html,
					'jopa' 		=> $_SESSION['cases']
				);
				break;
			default:
				$sale_html = ($subjectInfo['subject_sale'] != 0) ? '<a class="button width-100" onclick="to_sale(' . $sales . ')">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Sell') . ' ' . $subjectInfo['subject_sale'] . $this->CaseSettings()['course'] . '</a>' : '';
				$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
							  <img class="subject-image" style="width: 15rem; height: 15rem;" src="/' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '">
								<div><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 35px;font-weight: 700;letter-spacing: 1px;">' . $subjectInfo['subject_name'] . '</span><br>
								<span class="cases__yourwin" style="color: #ffc100;font-weight: 700;text-overflow: ellipsis;font-size: 22px;">' . $subjectInfo['subject_desc'] . '</span></div>
								<div class="bonuses-but"><a class="button width-100" onclick="Swal.close()">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Take') . '</a>' . $sale_html . '</div>';
				$return = array(
					'style' 	=> 'transparent',
					'ubal' 		=> number_format($balance, 0, ' ', ' ') . ' ' . $this->CaseSettings()['course'],
					'live' 		=> $subjectId,
					'win' 		=> $randomWin,
					'html' 		=> $html,
					'jopa' 		=> $_SESSION['cases']
				);
				break;
		}
		$this->DiscordMsg($casePrice, $subjectInfo);
		exit(json_encode($return));
	}

	public function saleSubject($id)
	{
		if (empty($_SESSION['steamid32']) || IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$winsInfo = $this->getWinsData($id);
		if (empty($winsInfo)) {
			$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Error') . ' #C1</div>
						<div style="margin-top:10px;">
							<span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
										  #C1: ' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_ErrorAdminSend') . '
								  	</span></div>';
			$return = array(
				'style' 	=> 'transparent',
				'html' 		=> $html
			);
			exit(json_encode($return));
		}
		$subjectInfo = $this->getSubjectData($winsInfo['subject_id']);
		if (empty($subjectInfo)) {
			$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Error') . ' #C2</div>
						<div style="margin-top:10px;">
							<span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
									#C2: ' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_ErrorAdminSend') . '
								  	</span></div>';
			$return = array(
				'style' 	=> 'transparent',
				'html' 		=> $html
			);
			exit(json_encode($return));
		}
		if ($subjectInfo['subject_type'] != 1 && empty($winsInfo['up']) && empty($winsInfo['sell'])) {
			$data = ['auth' => $_SESSION['steamid32']];
			$balance = $this->Balance();;
			$balance = $balance + $subjectInfo['subject_sale'];
			$data = ['auth' => $_SESSION['steamid32'], 'cash' => $balance];
			$this->Db->query('lk', 0, 0, 'UPDATE lk SET cash =:cash WHERE auth=:auth', $data);
			$data = ['steam_id' => $_SESSION['steamid32'], 'sell' => 1, 'id' => $id];
			$this->Db->query('Core', 0, 0, 'UPDATE cases_wins SET sell =:sell WHERE id=:id AND steam_id=:steam_id', $data);
			$return = array(
				'bal' => number_format($balance, 0, ' ', ' ') . ' ' . $this->CaseSettings()['course'],
			);
			exit(json_encode($return));
		}
	}

	public function upSubject($post)
	{
		if (empty($_SESSION['steamid32'])  || IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$winsInfo = $this->getWinsData(strip_tags($post['up']));
		if (empty($winsInfo)) {
			$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Error') . ' #C1</div>
						<div style="margin-top:10px;">
							<span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 900;">
										  #C1: ' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_ErrorAdminSend') . '
								  	</span></div>';
			$return = array(
				'style' 	=> 'transparent',
				'html' 		=> $html
			);
			exit(json_encode($return));
		}
		$subjectInfo = $this->getSubjectData($winsInfo['subject_id']);
		if (empty($subjectInfo)) {
			$html = '<script>setTimeout(function(){Swal.close()},2000);</script><div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Error') . ' #C2</div>
						<div style="margin-top:10px;">
							<span style="color:red;text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
									#C2: ' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_ErrorAdminSend') . '
								  	</span></div>';
			$return = array(
				'style' 	=> 'transparent',
				'html' 		=> $html
			);
			exit(json_encode($return));
		}
		if ($subjectInfo['subject_type'] != 1 && empty($winsInfo['up']) && empty($winsInfo['sell'])) {
			if ($subjectInfo['server_id']  == -1) {
				if (!empty($post['sid'])) {
					$server_id = strip_tags($post['sid']);
				} else {
					$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_SetUserServer') . '</div>';

					foreach ($this->General->server_list as $key) {
						$_Options[] = '<option value="' . base64_encode(json_encode(['sid' => $key['id'], 'up' => strip_tags($post['up'])])) . '">' . $key['name'] . '</option>';
					}
					$html .= '<div class="input-form"><select name="wins_to_server" class="wins-select">' . implode("\n", $_Options) . '</select></div>';
					$html .= '<div class="bonuses-but"><a class="width-100 button" onclick="Swal.close();window.location.reload();">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_No2') . '</a>
											<a class="button width-100" onclick="pick_up_wins_to_server()">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Yes2') . '</a></div>';
					$return = array(
						'allow'		=> 'true',
						'style'		=> 'transparent',
						'html' 		=> $html
					);
					exit(json_encode($return));
				}
			} else $server_id = $subjectInfo['server_id'];

			$server = $this->Get_Server_Info($server_id);

			switch ($subjectInfo['subject_class']) {
				case 'gold':
					$color = '#c58b1c78';
					break;
				case 'red':
					$color = '#c5301c3d';
					break;
				case 'pink':
					$color = '#d138663d';
					break;
				case 'purple':
					$color = '#7d2ecc3d';
					break;
				case 'blue':
					$color = '#5269e03d';
					break;
				case 'turquoise':
					$color = '#52acc038';
					break;
				case 'grey':
					$color = '#828a963d';
					break;
				default:
					$color = '#fff';
					break;
			}
			$steam3 = $this->st32to3($_SESSION['steamid32']);
			switch ($subjectInfo['subject_type']) {
				case 2: 
					$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
							  	<div style="margin-top:10px;">
							  		<span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">' . $subjectInfo['subject_content'] . '</span>
							  	</div>';
					$return = array(
						'style' 	=> 'transparent',
						'html' 		=> $html
					);
					break;
				case 3: 
					$dataMysql = explode(';', $server['server_vip']);
					if (!empty($dataMysql)) {
						$vipINFO = explode(':', $subjectInfo['subject_content']);
						$pos = strripos($vipINFO[1], '-');
						if ($pos === false) {
							$time = $vipINFO[1];
						} else {
							$getTimke = explode('-',  $vipINFO[1]);
							$time = rand($getTimke[0], $getTimke[1]);
						}

						$vipNewParam = [
							'account_id' => $this->st32to3($_SESSION['steamid32']),
							'sid' => $server['server_vip_id']
						];
						$steam3 = $this->st32to3($_SESSION['steamid32']);
						$vipNew = $this->Db->queryAll($dataMysql[0], $dataMysql[1], $dataMysql[2], "SELECT * FROM {$dataMysql[3]}users WHERE account_id = :account_id AND sid = :sid", $vipNewParam);
						if (empty($vipNew[0]['account_id'])) {
							$insertparams = [
								'account_id'	=> $this->st32to3($_SESSION['steamid32']),
								'name'			=> action_text_clear(action_text_trim($this->General->checkName($_SESSION['steamid64'])), 13),
								'lastvisit'		=> time(),
								'sid'			=> $server['server_vip_id'],
								'group'			=> $vipINFO[0],
								'expires'		=> $this->GetTimeVip($time)
							];
							$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "INSERT INTO {$dataMysql[3]}users VALUES (:account_id, :name, :lastvisit, :sid, :group, :expires)", $insertparams);
							$this->RconComand($server['ip'], $server['rcon'], 'sm_refresh_vips;mm_reload_vip ' . $steam3 . ';css_reload_vip_player ' . $steam3);
							$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
											  	<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
													  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
													  	Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeVip($time)) . '<br>
															<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
													  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
													  	IP: ' . $server['ip'] . '
											  	</span></div>';
							$return = array(
								'style' 	=> 'transparent',
								'html' 		=> $html
							);
						} else if ($vipINFO[0] == $vipNew[0]['group']) {
							if (empty($vipNew[0]['expires'])) {
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Error') . '</div>
											  	<div><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
											  		' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_HavePrivelege') . $subjectInfo['subject_sale'] . $this->CaseSettings()['course'] . '
											  	</span></div>
											  	<div class="bonuses-but" style="position: absolute;top: initial; bottom: 40px;right: 128px;"><button class="width-100" onclick="to_sale_wins(' . $winsInfo['id'] . ')">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Sell') . ' ' . $subjectInfo['subject_sale'] . $this->CaseSettings()['course'] . '</button></div>';
								$return = array(
									'style' 	=> '',
									'html' 		=> $html
								);
								exit(json_encode($return));
							} else {
								$insertparams = [
									'account_id'	=>	$this->st32to3($_SESSION['steamid32']),
									'lastvisit'		=>	time(),
									'sid'			=>	$server['server_vip_id'],
									'expires'		=>	$time
								];
								$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "UPDATE {$dataMysql[3]}users SET lastvisit = :lastvisit, expires = expires + :expires WHERE account_id = :account_id AND sid = :sid", $insertparams);
								$this->RconComand($server['ip'], $server['rcon'], 'sm_refresh_vips;mm_reload_vip ' . $steam3 . ';css_reload_vip_player ' . $steam3);
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
										  	<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
												  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
												  	Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeVip($time)) . '<br>
														<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
												  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
												  	IP: ' . $server['ip'] . '
										  	</span></div>';
								$return = array(
									'style' 	=> 'transparent',
									'html' 		=> $html
								);
							}
						} else {

							if (!empty($post['w_confirm'])) {
								$insertparams = [
									'account_id'	=> $this->st32to3($_SESSION['steamid32']),
									'lastvisit'		=> time(),
									'sid'			=> $server['server_vip_id'],
									'group'			=> $vipINFO[0],
									'expires'		=> $this->GetTimeVip($time)
								];

								$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "UPDATE {$dataMysql[3]}users SET lastvisit = :lastvisit, expires = :expires, `group`=:group WHERE account_id = :account_id AND sid = :sid", $insertparams);
								$this->RconComand($server['ip'], $server['rcon'], 'sm_refresh_vips;mm_reload_vip ' . $steam3 . ';css_reload_vip_player ' . $steam3);
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
											  	<div style="margin-top:10px;"><span class="cases__yourwin" style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
													  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
													  	Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeVip($time)) . '<br>
															<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
													  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
													  	IP: ' . $server['ip'] . '11
											  	</span></div>';
								$return = array(
									'style'		=> 'transparent',
									'html' 		=> $html
								);
							} else {

								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_VoteHavePrivelege') . '</div>
										<br><br>
										<div class="bonuses-but"><a class="width-100 button" onclick="Swal.close();window.location.reload();">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_No') . '</a>
										<a class="button width-100" onclick=pick_up_wins_accept("' . base64_encode(json_encode(['w_confirm' => 'true', 'up' => strip_tags($post['up']), 'sid' => $server_id])) . '")>' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Yes') . '</a></div>';
								$return = array(
									'allow'		=> 'true',
									'style'		=> 'transparent',
									'html' 		=> $html
								);
								exit(json_encode($return));
							}
						}
					} else {
						$return = array(
							'style' 	=> 'transparent',
							'html' 		=> 'Error'
						);
					}
					break;
				case 4: 
					$dataMysql = explode(';', $server['server_sb']);
					if (!empty($dataMysql)) {
						$IksINFO = explode(':', $subjectInfo['subject_content']);
						$IksGroupID = $this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "SELECT * FROM {$dataMysql[3]}groups WHERE name = '{$IksINFO[0]}'");
						$pos = strripos($IksINFO[1], '-');
						if ($pos === false) {
							$time = $IksINFO[1];
						} else {
							$getTimke = explode('-',  $IksINFO[1]);
							$time = rand($getTimke[0], $getTimke[1]);
						}

						$IksNewParam = [
							'sid' => $_SESSION['steamid64']
						];

						$IksNew = $this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "SELECT * FROM {$dataMysql[3]}admins WHERE sid = :sid", $IksNewParam);

						if (empty($IksNew['sid'])) {
							$insertparams = [
								'sid'	=> $_SESSION['steamid64'],
								'name'			=> action_text_clear(action_text_trim($this->General->checkName($_SESSION['steamid64'])), 13),
								'flags'			=> '',
								'immunity'			=> '-1',
								'group_id'			=> $IksGroupID['id'],
								'end'		=> $this->GetTimeIks($time),
								'server_id'		=> $server['server_sb_id']
							];
							$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "INSERT INTO {$dataMysql[3]}admins(sid, name, flags, immunity, group_id, end, server_id) VALUES (:sid, :name, :flags, :immunity, :group_id, :end, :server_id)", $insertparams);
							$this->RconComand($server['ip'], $server['rcon'], 'css_reload_admins');
							$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
														<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
																Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeIks($time)) . '<br>
																<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
																IP: ' . $server['ip'] . '
														</span></div>';
							$return = array(
								'style' 	=> 'transparent',
								'html' 		=> $html
							);
						} else if ($IksGroupID == $IksNew['group_id']) {
							if (empty($IksNew['end'])) {
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Error') . '</div>
														<div><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
															' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_HavePrivelege') . $subjectInfo['subject_sale'] . $this->CaseSettings()['course'] . '
														</span></div>
														<div class="bonuses-but" style="position: absolute;top: initial; bottom: 40px;right: 128px;"><button class="width-100" onclick="to_sale_wins(' . $winsInfo['id'] . ')">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Sell') . ' ' . $subjectInfo['subject_sale'] . $this->CaseSettings()['course'] . '</button></div>';
								$return = array(
									'style' 	=> '',
									'html' 		=> $html
								);
								exit(json_encode($return));
							} else {
								$iksSids = explode(';', $IksNew['group_id']);
								if (!in_array($server['server_sb_id'], $iksSids)) {
									$iksSids[] += $server['server_sb_id'];
								}

								$insertparams = [
									'sid'	=> $_SESSION['steamid64'],
									'name'			=> action_text_clear(action_text_trim($this->General->checkName($_SESSION['steamid64'])), 13),
									'flags'			=> '',
									'immunity'			=> '-1',
									'group_id'			=> $IksGroupID['id'],
									'end'		=> $this->GetTimeIks($time),
									'server_id'		=> $iksSids
								];
								$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "UPDATE {$dataMysql[3]}admins SET end = end + :end WHERE sid = :sid", $insertparams);
								$this->RconComand($server['ip'], $server['rcon'], 'css_reload_admins');
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
													<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
															' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
															Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeIks($time)) . '<br>
															<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
															' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
															IP: ' . $server['ip'] . '
													</span></div>';
								$return = array(
									'style' 	=> 'transparent',
									'html' 		=> $html
								);
							}
						} else {
							if (!empty($post['w_confirm'])) {
								$insertparams = [
									'sid'	=> $_SESSION['steamid64'],
									'flags'			=> '',
									'immunity'			=> '-1',
									'group_id'			=> $IksGroupID['id'],
									'end'		=> $this->GetTimeIks($time),
									'server_id'		=> $server['server_sb_id']
								];

								$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "UPDATE {$dataMysql[3]}admins SET flags = :flags, immunity = :immunity, `group_id`=:group_id, `end`=:end, `server_id`=:server_id WHERE sid = :sid", $insertparams);
								$this->RconComand($server['ip'], $server['rcon'], 'css_reload_admins');
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
														<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
																Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeIks($time)) . '<br>
																<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
																IP: ' . $server['ip'] . '11
														</span></div>';
								$return = array(
									'style'		=> 'transparent',
									'html' 		=> $html
								);
							} else {

								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_VoteHavePrivelege') . '</div>
											<br><br>
											<div class="bonuses-but"><a class="width-100 button" onclick="Swal.close();window.location.reload();">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_No') . '</a>
											<a class="button width-100" onclick=pick_up_wins_accept("' . base64_encode(json_encode(['w_confirm' => 'true', 'up' => strip_tags($post['up']), 'sid' => $server_id])) . '")>' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Yes') . '</a></div>';
								$return = array(
									'allow'		=> 'true',
									'style'		=> 'transparent',
									'html' 		=> $html
								);
								exit(json_encode($return));
							}
						}
					} else {
						$return = array(
							'style' 	=> 'transparent',
							'html' 		=> 'Error'
						);
					}
					break;
				case 5: 
					$dataMysql = explode(';', $server['server_sb']);
					if (!empty($dataMysql)) {
						$sbINFO = explode(':', $subjectInfo['subject_content']);
						$pos = strripos($sbINFO[1], '-');
						if ($pos === false) {
							$time = $sbINFO[1];
						} else {
							$getTimke = explode('-',  $sbINFO[1]);
							$time = rand($getTimke[0], $getTimke[1]);
						}
						$Param = ['authid' => $_SESSION['steamid32']];
						$userSb = $this->Db->queryAll($dataMysql[0], $dataMysql[1], $dataMysql[2], "SELECT * FROM {$dataMysql[3]}admins WHERE authid = :authid", $Param);

						$sb_group_param = ['name' => $sbINFO[0]];
						$sb_group = $this->Db->queryAll($dataMysql[0], $dataMysql[1], $dataMysql[2], "SELECT * FROM {$dataMysql[3]}srvgroups WHERE name = :name", $sb_group_param);

						$_Server_IP = explode(':', $server['ip']);
						$sb_server_param = ['ip' => $_Server_IP[0], 'port' => $_Server_IP[1]];
						$sb_server = $this->Db->queryAll($dataMysql[0], $dataMysql[1], $dataMysql[2], "SELECT * FROM {$dataMysql[3]}servers WHERE ip = :ip AND port = :port", $sb_server_param);

						if (empty($userSb[0]['authid'])) {
							$userLoginSB = action_text_clear(action_text_trim($this->General->checkName($_SESSION['steamid64'])), 13);
							if (strlen($userLoginSB) != strlen(utf8_decode($userLoginSB))) {
								$userLoginSB = '';
							}
							if (empty($userLoginSB)) {
								$userLoginSB = $this->random_str(8);
							}
							$userPassSB = $this->random_str(15);
							$userGenPass = $this->sbpasswd($userPassSB);
							$insAdminParams = [
								'user'		=> $userLoginSB,
								'authid'	=> $_SESSION['steamid32'],
								'password'	=> $userGenPass,
								'email'		=> '',
								'srv_group' => $sb_group[0]['name'],
								'expired'	=> $this->GetTimeVip($time)
							];
							$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "INSERT INTO {$dataMysql[3]}admins(aid, user, authid, password, gid, email, validate, extraflags, immunity, srv_group, srv_flags, srv_password, lastvisit, expired, skype, comment, vk, support) VALUES (NULL,:user,:authid,:password,-1,:email,NULL,0,50,:srv_group,NULL,NULL,NULL,:expired,NULL, NULL, NULL, 0)", $insAdminParams);

							$groupsParams = [
								'admin_id'		=> intval($this->Db->lastInsertId($dataMysql[0], $dataMysql[1], $dataMysql[2])),
								'group_id'		=> $sb_group[0]['id'],
								'srv_group_id'	=> -1,
								'server_id'		=> $sb_server[0]['sid']
							];
							$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "INSERT INTO {$dataMysql[3]}admins_servers_groups VALUES(:admin_id, :group_id, :srv_group_id, :server_id)", $groupsParams);

							$this->RconComand($server['ip'], $server['rcon'], 'sm_reloadadmins');
							$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
												  	<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
														  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
														  	Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeVip($time)) . '<br>
																<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
														  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
														  	IP: ' . $server['ip'] . '
												  	</span></div>';
							$return = array(
								'style' 	=> 'transparent',
								'html' 		=> $html
							);
						} else {
							$sb_group_param_admin = ['admin_id' => $userSb[0]['aid']];
							$sb_group_admin = $this->Db->queryAll($dataMysql[0], $dataMysql[1], $dataMysql[2], "SELECT * FROM {$dataMysql[3]}admins_servers_groups WHERE admin_id =:admin_id", $sb_group_param_admin);
							if ($userSb[0]['expired'] == 0 && $sb_group_admin[0]['server_id'] == $sb_server[0]['sid']) {
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Error') . '</div>
											  	<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
													  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_HavePrivelege') . $subjectInfo['subject_sale'] . $this->CaseSettings()['course'] . '
											  	</span></div>
											  	<div class="bonuses-but" style="position: absolute;top: initial;bottom: 40px;right: 128px;"><button class="width-100" onclick="to_sale_wins(' . $winsInfo['id'] . ')">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Sell') . ' ' . $subjectInfo['subject_sale'] . $this->CaseSettings()['course'] . '</button></div>';
								$return = array(
									'style' 	=> 'transparent',
									'html' 		=> $html
								);
								exit(json_encode($return));
							} else if ($sb_group_admin[0]['server_id'] != $sb_server[0]['sid']) {
								$groupsParams = [
									'admin_id'		=> $userSb[0]['aid'],
									'group_id'		=> $sb_group[0]['id'],
									'srv_group_id'	=> -1,
									'server_id'		=> $sb_server[0]['sid']
								];
								$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "INSERT INTO {$dataMysql[3]}admins_servers_groups VALUES(:admin_id, :group_id, :srv_group_id, :server_id)", $groupsParams);
								$this->RconComand($server['ip'], $server['rcon'], 'sm_reloadadmins');
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
												  <div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
														  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
														  	Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeVip($time)) . '<br>
																<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
														  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
														  	IP: ' . $server['ip'] . '
												  	</span></div>';
								$return = array(
									'style' 	=> 'transparent',
									'html' 		=> $html
								);
							} else {
								if ($userSb[0]['expired'] < time()) {
									$g_time = time() + $time;
								} else {
									$g_time = $userSb[0]['expired'] + $time;
								}
								$insertparams = [
									'authid'		=>	$_SESSION['steamid32'],
									'lastvisit'		=>	time(),
									'expired'		=>	$g_time
								];
								$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "UPDATE {$dataMysql[3]}admins SET expired = :expired, lastvisit = :lastvisit WHERE authid = :authid", $insertparams);
								$this->RconComand($server['ip'], $server['rcon'], 'sm_reloadadmins');
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
												  	<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
														  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
														  	Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeVip($time)) . '<br>
														  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
																<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
														  	IP: ' . $server['ip'] . '
												  	</span></div>';
								$return = array(
									'style' 	=> 'transparent',
									'html' 		=> $html
								);
							}
						}
					}
					break;
				case 6: 
					$this->RconComand($server['ip'], $server['rcon'], 'sm_shop_givecredits "' . $_SESSION['steamid32'] . '" "' . $subjectInfo['subject_content'] . '"; css_add_credits  "' . $_SESSION['steamid32'] . '" "' . $subjectInfo['subject_content'] . '"');
					$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
								  	<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
										  	Вам выдано ' . $subjectInfo['subject_content'] . ' кредитов!<br>
										  	' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
												<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
										  	IP - ' . $server['ip'] . '!
								  	</span></div>';
					$return = array(
						'style' 	=> 'transparent',
						'html' 		=> $html
					);
					break;
				case 7: 
					$this->RconComand($server['ip'], $server['rcon'], 'sm_shop_givecredits "' . $_SESSION['steamid32'] . '" "' . $subjectInfo['subject_content'] . '"');
					$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
											<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
													Вам выдано ' . $subjectInfo['subject_content'] . ' кредитов!<br>
													' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
													<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
													IP - ' . $server['ip'] . '!
											</span></div>';
					$return = array(
						'style' 	=> 'transparent',
						'html' 		=> $html
					);
					break;
				case 8: 
					$this->RconComand($server['ip'], $server['rcon'], 'lr_giveexp "' . $_SESSION['steamid32'] . '" "' . $subjectInfo['subject_content'] . '";css_lvl_giveexp ' . $_SESSION['steamid64'] . ' ' . $subjectInfo['subject_content'] . ';css_lr_giveexp ' . $_SESSION['steamid64'] . ' ' . $subjectInfo['subject_content']);
					$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
					<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
							Вам выдано ' . $subjectInfo['subject_content'] . ' опыта!<br>
							<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
							' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
							IP - ' . $server['ip'] . '!
					</span></div>';
					$return = array(
						'style' 	=> 'transparent',
						'html' 		=> $html
					);
					break;
				case 9: 
					$steam3 = con_steam32to3_int($_SESSION['steamid32']);
					$steam64 = con_steam32to64($_SESSION['steamid32']);
					$steam32_0 = $_SESSION['steamid32'];
					$steam32_0[6] = 0;
					$steam32_1 = $_SESSION['steamid32'];
					$steam32_1[6] = 1;
					$command = str_replace("%s0",  $steam32_0, $subjectInfo['subject_content']);
					$command = str_replace("%s1",  $steam32_1, $subjectInfo['subject_content']);
					$command = str_replace("%s3",  $steam3,    $subjectInfo['subject_content']);
					$command = str_replace("%s64", $steam64,   $subjectInfo['subject_content']);
					$this->RconComand($server['ip'], $server['rcon'], $command);
					$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
					<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 900;">
							' . $subjectInfo['subject_name'] . '<br>
							<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '">
							<div><span class="cases__yourwin" style="color:' . $color . ';text-overflow: ellipsis;font-size: 35px;font-weight: 900;">' . $subjectInfo['subject_name'] . '</span><br><br>
							' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
							IP - ' . $server['ip'] . '!
					</span></div>';
					$return = array(
						'style' 	=> 'transparent',
						'html' 		=> $html
					);
					break;
				case 10: 
					$dataMysql = explode(';', $server['server_sb']);
					if (!empty($dataMysql)) {
						$IksINFO = explode(':', $subjectInfo['subject_content']);
						$IksGroupID = $this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "SELECT * FROM {$dataMysql[3]}groups WHERE name = '{$IksINFO[0]}'");
						$pos = strripos($IksINFO[1], '-');
						if ($pos === false) {
							$time = $IksINFO[1];
						} else {
							$getTimke = explode('-',  $IksINFO[1]);
							$time = rand($getTimke[0], $getTimke[1]);
						}

						$IksNewParam = [
							'sid' => $_SESSION['steamid64']
						];

						$IksNew = $this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "SELECT * FROM {$dataMysql[3]}admins WHERE sid = :sid", $IksNewParam);

						if (empty($IksNew['sid'])) {
							$insertparams = [
								'sid'	=> $_SESSION['steamid64'],
								'name'			=> action_text_clear(action_text_trim($this->General->checkName($_SESSION['steamid64'])), 13),
								'flags'			=> '',
								'immunity'			=> '-1',
								'group_id'			=> $IksGroupID['id'],
								'end'		=> $this->GetTimeIks($time),
								'server_id'		=> $server['server_sb_id']
							];
							$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "INSERT INTO {$dataMysql[3]}admins(sid, name, flags, immunity, group_id, end, server_id) VALUES (:sid, :name, :flags, :immunity, :group_id, :end, :server_id)", $insertparams);
							$this->RconComand($server['ip'], $server['rcon'], 'css_reload_admins');
							$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
														<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
																Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeIks($time)) . '<br>
																<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
																IP: ' . $server['ip'] . '
														</span></div>';
							$return = array(
								'style' 	=> 'transparent',
								'html' 		=> $html
							);
						} else if ($IksGroupID == $IksNew['group_id']) {
							if (empty($IksNew['end'])) {
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Error') . '</div>
														<div><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
															' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_HavePrivelege') . $subjectInfo['subject_sale'] . $this->CaseSettings()['course'] . '
														</span></div>
														<div class="bonuses-but" style="position: absolute;top: initial; bottom: 40px;right: 128px;"><button class="width-100" onclick="to_sale_wins(' . $winsInfo['id'] . ')">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Sell') . ' ' . $subjectInfo['subject_sale'] . $this->CaseSettings()['course'] . '</button></div>';
								$return = array(
									'style' 	=> '',
									'html' 		=> $html
								);
								exit(json_encode($return));
							} else {
								$iksSids = explode(';', $IksNew['group_id']);
								if (!in_array($server['server_sb_id'], $iksSids)) {
									$iksSids[] += $server['server_sb_id'];
								}

								$insertparams = [
									'sid'	=> $_SESSION['steamid64'],
									'name'			=> action_text_clear(action_text_trim($this->General->checkName($_SESSION['steamid64'])), 13),
									'flags'			=> '',
									'immunity'			=> '-1',
									'group_id'			=> $IksGroupID['id'],
									'end'		=> $this->GetTimeIks($time),
									'server_id'		=> $iksSids
								];
								$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "UPDATE {$dataMysql[3]}admins SET end = end + :end WHERE sid = :sid", $insertparams);
								$this->RconComand($server['ip'], $server['rcon'], 'css_reload_admins');
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
													<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
															' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
															Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeIks($time)) . '<br>
															<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
															' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
															IP: ' . $server['ip'] . '
													</span></div>';
								$return = array(
									'style' 	=> 'transparent',
									'html' 		=> $html
								);
							}
						} else {
							if (!empty($post['w_confirm'])) {
								$insertparams = [
									'sid'	=> $_SESSION['steamid64'],
									'flags'			=> '',
									'immunity'			=> '-1',
									'group_id'			=> $IksGroupID['id'],
									'end'		=> $this->GetTimeIks($time),
									'server_id'		=> $server['server_sb_id']
								];

								$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "UPDATE {$dataMysql[3]}admins SET flags = :flags, immunity = :immunity, `group_id`=:group_id, `end`=:end, `server_id`=:server_id WHERE sid = :sid", $insertparams);
								$this->RconComand($server['ip'], $server['rcon'], 'css_reload_admins');
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
														<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
																Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeIks($time)) . '<br>
																<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
																IP: ' . $server['ip'] . '11
														</span></div>';
								$return = array(
									'style'		=> 'transparent',
									'html' 		=> $html
								);
							} else {

								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_VoteHavePrivelege') . '</div>
											<br><br>
											<div class="bonuses-but"><a class="width-100 button" onclick="Swal.close();window.location.reload();">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_No') . '</a>
											<a class="button width-100" onclick=pick_up_wins_accept("' . base64_encode(json_encode(['w_confirm' => 'true', 'up' => strip_tags($post['up']), 'sid' => $server_id])) . '")>' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Yes') . '</a></div>';
								$return = array(
									'allow'		=> 'true',
									'style'		=> 'transparent',
									'html' 		=> $html
								);
								exit(json_encode($return));
							}
						}
					} else {
						$return = array(
							'style' 	=> 'transparent',
							'html' 		=> 'Error'
						);
					}
					break;
				case 11: 
					$dataMysql = explode(';', $server['server_sb']);
					if (!empty($dataMysql)) {
						$IksINFO = explode(':', $subjectInfo['subject_content']);
						$IksGroupID = $this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "SELECT * FROM {$dataMysql[3]}groups WHERE name = '{$IksINFO[0]}'");
						$pos = strripos($IksINFO[1], '-');
						if ($pos === false) {
							$time = $IksINFO[1];
						} else {
							$getTimke = explode('-',  $IksINFO[1]);
							$time = rand($getTimke[0], $getTimke[1]);
						}

						$IksNewParam = [
							'sid' => $_SESSION['steamid64']
						];

						$IksNew = $this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "SELECT * FROM {$dataMysql[3]}admins WHERE sid = :sid", $IksNewParam);

						if (empty($IksNew['sid'])) {
							$insertparams = [
								'sid'	=> $_SESSION['steamid64'],
								'name'			=> action_text_clear(action_text_trim($this->General->checkName($_SESSION['steamid64'])), 13),
								'flags'			=> '',
								'immunity'			=> '-1',
								'group_id'			=> $IksGroupID['id'],
								'end'		=> $this->GetTimeIks($time),
								'server_id'		=> $server['server_sb_id']
							];
							$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "INSERT INTO {$dataMysql[3]}admins(sid, name, flags, immunity, group_id, end, server_id) VALUES (:sid, :name, :flags, :immunity, :group_id, :end, :server_id)", $insertparams);
							$this->RconComand($server['ip'], $server['rcon'], 'css_reload_admins');
							$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
														<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
																Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeIks($time)) . '<br>
																<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
																IP: ' . $server['ip'] . '
														</span></div>';
							$return = array(
								'style' 	=> 'transparent',
								'html' 		=> $html
							);
						} else if ($IksGroupID == $IksNew['group_id']) {
							if (empty($IksNew['end'])) {
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Error') . '</div>
														<div><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
															' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_HavePrivelege') . $subjectInfo['subject_sale'] . $this->CaseSettings()['course'] . '
														</span></div>
														<div class="bonuses-but" style="position: absolute;top: initial; bottom: 40px;right: 128px;"><button class="width-100" onclick="to_sale_wins(' . $winsInfo['id'] . ')">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Sell') . ' ' . $subjectInfo['subject_sale'] . $this->CaseSettings()['course'] . '</button></div>';
								$return = array(
									'style' 	=> '',
									'html' 		=> $html
								);
								exit(json_encode($return));
							} else {
								$iksSids = explode(';', $IksNew['group_id']);
								if (!in_array($server['server_sb_id'], $iksSids)) {
									$iksSids[] += $server['server_sb_id'];
								}

								$insertparams = [
									'sid'	=> $_SESSION['steamid64'],
									'name'			=> action_text_clear(action_text_trim($this->General->checkName($_SESSION['steamid64'])), 13),
									'flags'			=> '',
									'immunity'			=> '-1',
									'group_id'			=> $IksGroupID['id'],
									'end'		=> $this->GetTimeIks($time),
									'server_id'		=> $iksSids
								];
								$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "UPDATE {$dataMysql[3]}admins SET end = end + :end WHERE sid = :sid", $insertparams);
								$this->RconComand($server['ip'], $server['rcon'], 'css_reload_admins');
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
													<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
															' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
															Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeIks($time)) . '<br>
															<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
															' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
															IP: ' . $server['ip'] . '
													</span></div>';
								$return = array(
									'style' 	=> 'transparent',
									'html' 		=> $html
								);
							}
						} else {
							if (!empty($post['w_confirm'])) {
								$insertparams = [
									'sid'	=> $_SESSION['steamid64'],
									'flags'			=> '',
									'immunity'			=> '-1',
									'group_id'			=> $IksGroupID['id'],
									'end'		=> $this->GetTimeIks($time),
									'server_id'		=> $server['server_sb_id']
								];

								$this->Db->query($dataMysql[0], $dataMysql[1], $dataMysql[2], "UPDATE {$dataMysql[3]}admins SET flags = :flags, immunity = :immunity, `group_id`=:group_id, `end`=:end, `server_id`=:server_id WHERE sid = :sid", $insertparams);
								$this->RconComand($server['ip'], $server['rcon'], 'css_reload_admins');
								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
														<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Privilege') . ': ' . $subjectInfo['subject_name'] . '<br>
																Срок: до ' . date('d-m-Y H:i:s', $this->GetTimeIks($time)) . '<br>
																<img class="subject-image" style="width: 15rem; height: 15rem;" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . ' ' . $subjectInfo['subject_desc'] . '"><br>
																' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Server') . ':' . $server['name'] . '<br>
																IP: ' . $server['ip'] . '11
														</span></div>';
								$return = array(
									'style'		=> 'transparent',
									'html' 		=> $html
								);
							} else {

								$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_VoteHavePrivelege') . '</div>
											<br><br>
											<div class="bonuses-but"><a class="width-100 button" onclick="Swal.close();window.location.reload();">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_No') . '</a>
											<a class="button width-100" onclick=pick_up_wins_accept("' . base64_encode(json_encode(['w_confirm' => 'true', 'up' => strip_tags($post['up']), 'sid' => $server_id])) . '")>' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Yes') . '</a></div>';
								$return = array(
									'allow'		=> 'true',
									'style'		=> 'transparent',
									'html' 		=> $html
								);
								exit(json_encode($return));
							}
						}
					} else {
						$return = array(
							'style' 	=> 'transparent',
							'html' 		=> 'Error'
						);
					}
					break;
				default:
					$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_Error') . ' #C3</div>
								  	<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">
										  	#C3: ' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_ErrorAdminSend') . '
								  	</span></div>';
					$return = array(
						'style' 	=> 'transparent',
						'html' 		=> $html
					);
					exit(json_encode($return));
					break;
			}
			$data = ['steam_id' => $_SESSION['steamid32'], 'id' => strip_tags($post['up'])];
			$this->Db->query('Core', 0, 0, 'UPDATE cases_wins SET up =1 WHERE id=:id AND steam_id=:steam_id', $data);
			exit(json_encode($return));
		}
	}

	public function winSubject($id)
	{
		if (empty($_SESSION['steamid32']) || IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$winsInfo = $this->getWinsData(strip_tags($id));
		if (empty($winsInfo)) return;
		$subjectInfo = $this->getSubjectData($winsInfo['subject_id']);
		if (empty($subjectInfo)) return;
		if ($subjectInfo['subject_type'] != 1 && !empty($winsInfo['up']) && empty($winsInfo['sell'])) {
			switch ($subjectInfo['subject_class']) {
				case 'gold':
					$color = '725a39';
					break;
				case 'red':
					$color = 'ec8492';
					break;
				case 'pink':
					$color = 'df0117';
					break;
				case 'purple':
					$color = 'c555ff';
					break;
				case 'blue':
					$color = '5655d3';
					break;
				case 'turquoise':
					$color = '2afdf4';
					break;
				case 'grey':
					$color = '3e3e3e';
					break;
				default:
					$color = 'cacaca';
					break;
			}
			switch ($subjectInfo['subject_type']) {
				case 2:
					$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
							<div style="margin-top:10px;">
								<span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">' . $subjectInfo['subject_content'] . '</span>
							</div>';
					$return = array(
						'style' 	=> 'transparent',
						'html' 		=> $html
					);
					exit(json_encode($return));
					break;
				default:
					$html = '<div class="bonuses-title">' . $this->Translate->get_translate_module_phrase('module_page_open_case', '_YourWin') . '</div>
								  	<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 700;letter-spacing: 1px;">Вам был выдан данный выигрыш</span></div>
										<div style="margin-top:10px;"><span style="color:' . $color . ';text-overflow: ellipsis;font-size: 20px;font-weight: 900;">' . $subjectInfo['subject_name'] . '</span></div>
										<img class="subject-image" src="../' . $subjectInfo['subject_img'] . '" alt="' . $subjectInfo['subject_name'] . '">';
					$return = array(
						'style' 	=> 'transparent',
						'html' 		=> $html
					);
					exit(json_encode($return));
					break;
			}
		}
	}

	public function liveUpload($id)
	{
		if (empty($_SESSION['steamid32']) || IN_LR != true) {
			header('Location: ' . $this->General->arr_general['site']);
			exit;
		}
		$subjects = $this->getSubjectData($id);
		if (empty($subjects)) return;
		$case = $this->getPriceCase($subjects['case_id']);
		if (empty($case)) return;
		$data = ['auth' => $_SESSION['steamid32']];
		$user = action_text_clear($this->General->checkName($_SESSION['steamid64']));
		$data = ['case_id' => $case['id'], 'case_name' => $case['case_name'], 'subject_name' => $subjects['subject_name'], 'user_name' => $user, 'steam_id' => $_SESSION['steamid32'], 'subject_img' => $subjects['subject_img'], 'case_img' => $case['case_img'], 'live_style' => $subjects['subject_class']];
		$this->Db->query('Core', 0, 0, 'INSERT INTO cases_live(case_id, case_name, subject_name, user_name, steam_id, subject_img, case_img, live_style) VALUES (:case_id, :case_name, :subject_name, :user_name, :steam_id, :subject_img, :case_img, :live_style)', $data);
		$this->Db->query('Core', 0, 0, 'DELETE FROM cases_live WHERE id NOT IN (SELECT id FROM (SELECT id FROM cases_live ORDER BY id DESC LIMIT 15) x)');
	}

	public function liveLoad()
	{
		$result = $this->Db->queryAll('Core', 0, 0, "SELECT * FROM cases_live ORDER BY id DESC LIMIT 15");
		$lifelines = array();
		foreach ($result as $entry) {
			array_push($lifelines, array(
				'liveid'	=> $entry['id'],
				'id' 		=> $entry['case_id'],
				'cname' 	=> $entry['case_name'],
				'sname' 	=> $entry['subject_name'],
				'uname'		=> $entry['user_name'],
				'simg' 		=> $this->ImgLoad($entry['subject_img']),
				'cimg' 		=> $this->ImgLoad($entry['case_img']),
				'style' 	=> $entry['live_style'],
			));
		}
		exit(json_encode(array_reverse($lifelines)));
	}

	public function Get_Server_Info($id)
	{
		$param = ['id' => $id];
		$server = $this->Db->queryAll('Core', 0, 0, 'SELECT * FROM lvl_web_servers WHERE id = :id', $param);
		return $server[0];
	}

	protected function WM($summ)
	{
		$ita = explode('.', $summ);
		if (COUNT($ita) == 1) {
			$summa = $ita[0] . '.00';
		} else {
			$summa = $summ;
		}
		return $summa;
	}

	protected function numberOfDecimals($value)
	{
		if ((int)$value == $value) return 0;
		else if (!is_numeric($value)) return false;
		return strlen($value) - strrpos($value, '.') - 1;
	}

	protected function roulette($items)
	{
		$sumOfPercents = 0;
		foreach ($items as $itemsPercent) {
			$sumOfPercents += $itemsPercent;
		}

		$decimals = $this->numberOfDecimals($sumOfPercents);
		$multiplier = 1;
		for ($i = 0; $i < $decimals; $i++) {
			$multiplier *= 10;
		}

		$sumOfPercents *= $multiplier;
		$rand = rand(1, $sumOfPercents);
		$rangeStart = 1;
		foreach ($items as $itemKey => $itemsPercent) {
			$rangeFinish = $rangeStart + ($itemsPercent * $multiplier);
			if ($rand >= $rangeStart && $rand <= $rangeFinish) {
				return $itemKey;
			}
			$rangeStart = $rangeFinish + 1;
		}
	}

	protected function RconComand($ip, $rcons, $comands)
	{
		$ip = explode(':', $ip);
		$rcon = new Rcon($ip[0], $ip[1]);
		if ($rcon->Connect()) {
			$rcon->RconPass($rcons);
			$rcon->Command($comands);
			$rcon->Disconnect();
		}
	}

	public function bg($bg)
	{
		$array = [
			'gold'		=> 1,
			'red'		=> 2,
			'pink'		=> 3,
			'purple'	=> 4,
			'blue'		=> 5,
			'turquoise'	=> 6,
			'grey'		=> 7
		];
		return $array[$bg];
	}
	public function bgReturn($bg)
	{
		$array = [
			1	=> 'gold',
			2	=> 'red',
			3	=> 'pink',
			4	=> 'purple',
			5	=> 'blue',
			6	=> 'turquoise',
			7	=> 'grey'
		];
		return $array[$bg];
	}

	public function bg2($bg)
	{
		$array = [
			'gold'		=> $this->Translate->get_translate_module_phrase('module_page_open_case', '_Gold'),
			'red'		=> $this->Translate->get_translate_module_phrase('module_page_open_case', '_Red'),
			'pink'		=> $this->Translate->get_translate_module_phrase('module_page_open_case', '_Pink'),
			'purple'	=> $this->Translate->get_translate_module_phrase('module_page_open_case', '_Purple'),
			'blue'		=> $this->Translate->get_translate_module_phrase('module_page_open_case', '_Blue'),
			'turquoise'	=> $this->Translate->get_translate_module_phrase('module_page_open_case', '_Turquoise'),
			'grey'		=> $this->Translate->get_translate_module_phrase('module_page_open_case', '_Grey')
		];
		return $array[$bg];
	}

	protected function GetTimeVip($time)
	{
		if (empty($time)) {
			$time = '0';
			return $time;
		} else return time() + $time;
	}

	protected function GetTimeIks($time)
	{
		if (empty($time)) {
			$time = '0';
			return $time;
		} else return time() + $time;
	}

	protected function random_str($num = 30)
	{
		return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $num);
	}

	protected function sbpasswd($password, $salt = 'SourceBans')
	{
		return sha1(sha1($salt . $password));
	}

	protected function DiscordMsg($case, $subject)
	{
		$settings = $this->CaseSettings();
		switch ($subject['subject_class']) {
			case 'gold':
				$color = '725a39';
				break;
			case 'red':
				$color = 'ec8492';
				break;
			case 'pink':
				$color = 'df0117';
				break;
			case 'purple':
				$color = 'c555ff';
				break;
			case 'blue':
				$color = '5655d3';
				break;
			case 'turquoise':
				$color = '2afdf4';
				break;
			case 'grey':
				$color = '3e3e3e';
				break;
			default:
				$color = 'cacaca';
				break;
		}
		if ($case['case_type'] == 1)
			$price = " за " . $case['case_price'] . " " . $this->CaseSettings()['course'];
		else $price = " бесплатно";
		if (!empty($settings['webhook_offon'])) {
			if (action_text_clear(action_text_trim($this->General->checkName($_SESSION['steamid64'])), 13)) {
				$json = json_encode([
					"username" 		=> action_text_clear(action_text_trim($this->General->checkName($_SESSION['steamid64'])), 13),
					"avatar_url" 	=> $this->General->getAvatar(con_steam32to64($_SESSION['steamid32']), 2),
					"file" => "content",
					"embeds" =>
					[
						[
							"color"		=> hexdec($color),
							"title" 	=> "Открыл кейс " . $case['case_name'] . $price,
							"description" => $subject['subject_desc'],
							"type" 		=> "content",
							"url" 		=> 'https:' . $this->General->arr_general['site'] . "cases/?case=" . $case['id'],
							"thumbnail" =>
							[
								"url" => 'http:' . $this->General->arr_general['site'] . $subject['subject_img']
							],
							"footer" =>
							[
								"text" => $this->General->arr_general['full_name'] . ' ' . date('d.m.Y H:i:s'),
								"icon_url"	=> 'http:' . $this->General->arr_general['site'] . $this->ImgLoad('0')
							],
							"fields" =>
							[
								[
									"name" 		=> $this->Translate->get_translate_module_phrase('module_page_open_case', '_Wins'),
									"value" 	=> $subject['subject_name'],
									"inline" 	=> true
								],
								[
									"name" 		=> $this->Translate->get_translate_module_phrase('module_page_open_case', '_Case'),
									"value" 	=> $case['case_name'],
									"inline" 	=> true
								]
							]
						]
					]
				]);
				$cl = curl_init($settings['webhook']);
				curl_setopt($cl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
				curl_setopt($cl, CURLOPT_POST, 1);
				curl_setopt($cl, CURLOPT_POSTFIELDS, $json);
				curl_exec($cl);
			}
		}
	}

	public function st32to3($steamid32)
	{
		if (preg_match('/^STEAM_[0-1]\:(.*)\:(.*)$/', $steamid32, $res)) {
			return $res[2] * 2 + $res[1];
		}
		return false;
	}

	public function ClearGifts()
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true) exit;
		$this->Db->query('Core', 0, 0, "DELETE FROM `cases_wins` WHERE `up` = 1 OR `sell` = 1");
		return ['success' => 'Успешно очищено!'];
	}

	public function ClearList()
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true) exit;
		$this->Db->query('Core', 0, 0, "DELETE FROM `cases_open`");
		return ['success' => 'Успешно очищено!'];
	}

	public function ClearLive()
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true) exit;
		$this->Db->query('Core', 0, 0, "DELETE FROM `cases_live`");
		return ['success' => 'Успешно очищено!'];
	}

	public function ImgLoad($link)
	{
		if (!file_exists($link))
			$link = 'app/modules/module_page_open_case/assets/img/no_case_img.png';
		return $link;
	}
}
