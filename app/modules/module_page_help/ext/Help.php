<?php


namespace app\modules\module_page_help\ext;

class Help
{
	public $Db;
	public $General;
	public $Translate;
	public $Modules;
	public $Router;
	public $Notifications;


	public function __construct($Db, $General, $Translate, $Modules, $Router, $Notifications)
	{
		$this->Db = $Db;
		$this->General = $General;
		$this->Translate = $Translate;
		$this->Modules = $Modules;
		$this->Router = $Router;
		$this->Notifications = $Notifications;
	}

	public function LoadContent()
	{
		$content = $this->Db->query('Core', 0, 0, "SELECT `id`, `category`, `title`, `svg`, `content`, `created` FROM `lvl_web_help_content`");

		return ['content' => $content];
	}

	public function GetCategory()
	{
		return $this->Db->queryAll('Core', 0, 0, "SELECT `id`, `title`, `svg`, `sort` FROM `lvl_web_help_category` ORDER BY `sort` ASC");
	}

	public function GetCategoryByID($id)
	{
		return $this->Db->query('Core', 0, 0, "SELECT `id`, `title`, `svg`, `sort` FROM `lvl_web_help_category` WHERE `id` = $id ORDER BY `sort` ASC");
	}

	public function GetContent()
	{
		return $this->Db->queryAll('Core', 0, 0, "SELECT `id`, `category`, `title`, `svg`, `content`, `sort`, `created` FROM `lvl_web_help_content` ORDER BY `sort` ASC");
	}

	public function GetContentByCatID($id)
	{
		return $this->Db->queryAll('Core', 0, 0, "SELECT `id`, `category`, `title`, `svg`, `sort` FROM `lvl_web_help_content` WHERE `category` = $id ORDER BY `sort` ASC");
	}

	public function GetContentFirstID()
	{
		$content = $this->GetContent();
		$categories = $this->GetCategory();

		$filteredContent = array_filter($content, function ($item) {
			return is_null($item['category']);
		});

		$combined = [];

		foreach ($filteredContent as $item) {
			$combined[] = [
				'id' => $item['id'],
				'title' => $item['title'],
				'svg' => $item['svg'],
				'sort' => $item['sort'],
			];
		}

		foreach ($categories as $item) {
			if (count($this->GetContentByCatID($item['id'])) != 0) {
				$combined[] = [
					'id' => $item['id'],
					'title' => $item['title'],
					'svg' => $item['svg'],
					'sort' => $item['sort'],
					'subItems' => $this->GetContentByCatID($item['id'])
				];
			}
		}

		usort($combined, function ($a, $b) {
			return $a['sort'] <=> $b['sort'];
		});

		if (isset($combined[0]['subItems'][0]['id'])) {
			$data = $combined[0]['subItems'][0]['id'] ?? 0;
		} else {
			$data = $combined[0]['id'] ?? 0;
		}

		return $data;
	}

	public function GetContentButtons()
	{
		$buttons = [];
		$content = $this->GetContent();
		$categories = $this->GetCategory();

		$filteredContent = array_filter($content, function ($item) {
			return is_null($item['category']);
		});

		$combined = [];

		foreach ($filteredContent as $item) {
			$combined[] = [
				'id' => $item['id'],
				'title' => $item['title'],
				'svg' => $item['svg'],
				'sort' => $item['sort'],
			];
		}

		foreach ($categories as $item) {
			if (count($this->GetContentByCatID($item['id'])) != 0 || isset($_SESSION['user_admin'])) {
				$combined[] = [
					'id' => $item['id'],
					'title' => $item['title'],
					'svg' => $item['svg'],
					'sort' => $item['sort'],
					'subItems' => $this->GetContentByCatID($item['id'])
				];
			}
		}

		usort($combined, function ($a, $b) {
			return $a['sort'] <=> $b['sort'];
		});

		$count_combined = 0;
		foreach ($combined as $item) {
			$count_combined += 1;
			$combinedId = $item['id'];
			$combinedSvg = $item['svg'];
			$combinedTitle = $item['title'];

			$subItemsHtml = [];
			if (isset($item['subItems'])) {
				$count_items = 0;
				foreach ($item['subItems'] as $subItem) {
					$count_items += 1;
					if ($count_combined == 1) {
						$help_active_small = ($count_items == 1) ? ' help_active_small' : '';
					} else {
						$help_active_small = '';
					}
					$subItemId = $subItem['id'];
					$subItemSvg = $subItem['svg'];
					$subItemTitle = $subItem['title'];
					$subItemsHtml[] = <<<HTML
							<div class="help_menu-block">
								<svg class="help_svg" viewBox="0 0 12 11" aria-hidden="true"><path d="M11 9H4C2.89543 9 2 8.10457 2 7V1C2 0.447715 1.55228 0 1 0C0.447715 0 0 0.447715 0 1V7C0 9.20914 1.79086 11 4 11H11C11.5523 11 12 10.5523 12 10C12 9.44771 11.5523 9 11 9Z"></path></svg>
								<div class="rules_menu_cat{$help_active_small}" onclick="Ajax('load', {$subItemId})" id="content_btn_{$subItemId}">
										<div class="svg">{$subItemSvg}{$subItemTitle}</div>
								</div>
							</div>
						HTML;
				}
			}
			$subItems = implode('', $subItemsHtml);

			if (count($subItemsHtml) != 0) {
				$subItemsArray = implode('', $subItemsHtml);
				$subItems = <<<HTML
									<div class='help_dropdown dropdown_list-{$combinedId}'>
											$subItemsArray
									</div>
					HTML;
			}
			if (isset($_SESSION['user_admin'])) {
				$category_btn = <<<HTML
				<div class="help_buttons-2">
					<div class="edit_content" onclick="Ajax('edit_category_btn', {$combinedId})">
						<svg viewBox="0 0 512 512">
							<path d="M362.7 19.3L314.3 67.7 444.3 197.7l48.4-48.4c25-25 25-65.5 0-90.5L453.3 19.3c-25-25-65.5-25-90.5 0zm-71 71L58.6 323.5c-10.4 10.4-18 23.3-22.2 37.4L1 481.2C-1.5 489.7 .8 498.8 7 505s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L421.7 220.3 291.7 90.3z"></path>
						</svg>
					</div>
					<div class="delete_content" onclick="Ajax('del_category', {$combinedId})">
						<svg viewBox="0 0 320 512">
							<path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"></path>
						</svg>
					</div>
				</div>
			HTML;
			} else {
				$category_btn = '';
			}

			$help_active = ($count_combined == 1) ? ' help_active' : '';

			if (!isset($item['subItems'])) {
				$buttons[] =  <<<HTML
					<div class="rules_menu{$help_active}" onclick="Ajax('load', {$combinedId})" id="content_{$combinedId}">
						<div class="svg">{$combinedSvg}{$combinedTitle}</div>
					</div>
				HTML;
			} else {
				$buttons[] =  <<<HTML
					<div class="rules_menu dropdown{$help_active}" id="category_id_{$combinedId}">
						<div class="svg">{$combinedSvg}{$combinedTitle}</div>
						$category_btn
					</div>
					$subItems
				HTML;
			}
		}

		return $buttons;
	}


	public function GetContentByID($id = '')
	{
		if (is_numeric($id)) {
			$id_content = $id;
		} else {
			$id_content = $this->GetContentFirstID();
		}

		$content = [];
		$no_category = [
			'id' => '',
			'title' => $this->Translate->get_translate_module_phrase('module_page_help', '_Uncategorized')
		];

		$categories = $this->GetCategory();
		$buttons = $this->GetContentButtons();

		$content = $this->Db->query('Core', 0, 0, "SELECT `id`, `category`, `title`, `svg`, `content`, `sort`, `created` FROM `lvl_web_help_content` WHERE id = $id_content");
		if (!empty($id)) {
			$data = ['content' => $content, 'category' => $categories, 'no_category' => $no_category, 'title' => "Изменение пункта"];
		} else {
			$data = ['content' => $content, 'category' => $categories, 'buttons' => $buttons, 'no_category' => $no_category, 'title' => "Изменение пункта"];
		}
		return $data;
	}

	public function AddContent($post)
	{
		$data = [
			'category' => !empty($post['category']) ? $post['category'] : null,
			'title' => $post['title'],
			'svg' => $post['svg'],
			'sort' => $post['sort'],
			'content' => $post['param1'],
			'created' => time()
		];

		$this->Db->query('Core', 0, 0, "INSERT INTO `lvl_web_help_content` (`category`, `title`, `svg`, `content`, `sort`, `created`) VALUES (:category, :title, :svg, :content, :sort, :created)", $data);
		return ['success' => 'Данные успешно добавлены'];
	}

	public function EditContent($post)
	{
		if (!is_numeric($post['param2'])) {
			return ['error' => 'Ошибка!'];
		}
		$data = [
			'id' => $post['param2'],
			'title' => $post['title'],
			'svg' => $post['svg'],
			'sort' => $post['sort'],
			'content' => $post['param1'],
			'created' => time()
		];
		$this->Db->query('Core', 0, 0, "UPDATE lvl_web_help_content SET `title` = :title, `svg` = :svg, `content` = :content, `sort` = :sort, `created` = :created WHERE id = :id", $data);

		return ['success' => 'Данные успешно изменены', 'id' => $data['id']];
	}

	public function DelContent($post)
	{
		if (!is_numeric($post['param1'])) {
			return ['error' => 'Ошибка!'];
		}
		$data = [
			'id' => $post['param1'],
		];
		$this->Db->query('Core', 0, 0, "DELETE FROM lvl_web_help_content WHERE id = :id", $data);
		return ['success' => 'Данные успешно удалены'];
	}

	public function AddCategory($post)
	{

		$data = [
			'title' => $post['title'],
			'svg' => $post['svg'],
			'sort' => $post['sort'],
			'created' => time()
		];
		$this->Db->query('Core', 0, 0, "INSERT INTO `lvl_web_help_category` (`title`, `svg`, `sort`, `created`) VALUES (:title, :svg, :sort, :created)", $data);
		return ['success' => 'Данные успешно добавлены'];
	}

	public function EditCategory($post)
	{
		if (!is_numeric($post['param1'])) {
			return ['error' => 'Ошибка!'];
		}
		$data = [
			'id' => $post['param1'],
			'title' => $post['title'],
			'svg' => $post['svg'],
			'sort' => $post['sort'],
			'created' => time()
		];
		$this->Db->query('Core', 0, 0, "UPDATE `lvl_web_help_category` SET `title` = :title, `svg` = :svg, `sort` = :sort, `created` = :created WHERE id = :id", $data);

		return ['success' => 'Данные успешно изменены', 'id' => $data['id']];
	}

	public function DelCategory($post)
	{
		if (!is_numeric($post['param1'])) {
			return ['error' => 'Ошибка!'];
		}
		$data = [
			'id' => $post['param1'],
		];
		$this->Db->query('Core', 0, 0, "DELETE FROM lvl_web_help_category WHERE id = :id", $data);
		$this->Db->query('Core', 0, 0, "DELETE FROM lvl_web_help_content WHERE category = :id", $data);
		return ['success' => 'Данные успешно удалены'];
	}

	public function UploadIMG($post, $files)
	{
		if (isset($_SESSION['user_admin']) && $post['action'] === 'upload_image') {
			$uploadDir = MODULES . 'module_page_help/cacheimg/';
			$fileName = basename($files['image']['name']);
			$fileTmpName = $files['image']['tmp_name'];
			$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
			$newFileName = date('YmdHis') . '.' . $fileExtension;
			$uploadPath = $uploadDir . $newFileName;
			$data = [
				'file_name' => $newFileName,
				'created' => time()
			];
			$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
			if (!in_array($fileExtension, $allowedExtensions)) {
				return json_encode(['status' => 'error', 'text' => 'Недопустимый тип файла. Допустимы только JPG, JPEG, PNG и GIF.']);
			}
			$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
			if (!in_array($files['image']['type'], $allowedMimeTypes)) {
				return json_encode(['status' => 'error', 'text' => 'Недопустимый тип MIME. Допустимы только JPG, JPEG, PNG и GIF.']);
			}
			if (move_uploaded_file($fileTmpName, $uploadPath)) {
				$this->Db->query('Core', 0, 0, "INSERT INTO `lvl_web_help_images` (`file_name`, `created`) VALUES (:file_name, :created)", $data);
				$publicUrl = $this->General->arr_general['site'] . 'app/modules/module_page_help/cacheimg/' . $newFileName;
				return json_encode(['status' => 'success', 'url' => $publicUrl]);
			} else {
				return json_encode(['status' => 'error', 'text' => 'Ошибка загрузки файла']);
			}
		}
	}
}
