<?php



namespace app\modules\module_page_pay\ext;

class Lk_module
{
	public $name;
	public $Modules;
	public $General;
	public $Db;
	public $Notifications;
	public $Translate;

	public function __construct($Translate, $Notifications, $General, $Modules, $Db)
	{
		$this->Modules = $Modules;
		$this->Translate = $Translate;
		$this->General = $General;
		$this->Db = $Db;
		$this->Notifications = $Notifications;
	}

	public function LkBalancePlayer()
	{
		if (isset($_SESSION['steamid32'])) {
			preg_match('/:[0-9]{1}:\d+/i', $_SESSION['steamid32'], $auth);
			$param = ['auth' => '%' . $auth[0] . '%'];
			$infoUser = $this->Db->queryAll('lk', 0, 0, "SELECT cash FROM lk WHERE auth LIKE :auth LIMIT 1", $param);
			$cash = 'cash';
			$this->Modules->set_user_info_text(
				$this->Translate->get_translate_module_phrase('module_page_pay', '_Balance') .
					': ' .
					$this->General->currency .
					' <b class="material-balance">' .
					number_format($infoUser[0][$cash], 0, ' ', ' ') .
					'</b>'
			);
			return number_format($infoUser[0][$cash], 0, ' ', ' ');
		}
	}

	public function LkAllDonats()
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true) {
			exit();
		}
		$allDonat = $this->Db->queryNum('lk', 0, 0, "SELECT SUM(all_cash) FROM lk");
		return number_format($allDonat[0], 0, ' ', ' ');
	}

	public function LkAllDonatsToPayGateway($system)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true) {
			exit();
		}
		$params = ['name' => $system];
		$cashSYS = $this->Db->queryNum('lk', 0, 0, "SELECT SUM(pay_summ) FROM lk_pays WHERE pay_system = :name AND pay_status = 1", $params);
		if (empty($cashSYS)) {
			return false;
		}
		return number_format($cashSYS[0], 0, ' ', ' ');
	}

	public function LkPromocodes()
	{
		$allcodes = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_promocodes");
		return $allcodes;
	}

	public function DelLKUser($post)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true)
			exit;
		if (empty($post['id']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_Error'), 'error');
		$param = ['id' => $post['id']];
		$user = $this->Db->query('lk', 0, 0, "SELECT * FROM `lk` WHERE `auth` = :id LIMIT 1", $param);
		if (empty($user))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_playerNotFound'), 'error');
		$this->Db->query('lk', 0, 0, "DELETE FROM `lk` WHERE `auth` = :id", $param);
		$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_userDeleted'), 'success');
	}

	public function LkPromocodesVisible()
	{
		$allcodes = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_promocodes WHERE hide != 1");
		return $allcodes;
	}

	public function LkPromoCode($id)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true) {
			exit();
		}
		$param = ['id' => $id];
		$code = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_promocodes WHERE id = :id", $param);
		return $code;
	}

	public function LkDiscordData()
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true) {
			exit();
		}
		$DiscordData = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_discord");
		return $DiscordData[0];
	}

	public function LkGetAllGateways()
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true) {
			exit();
		}
		$allGateways = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_pay_service");
		return $allGateways;
	}

	public function LkGetGateway($gateway)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true) {
			exit();
		}
		$param = ['id' => $this->LkConvertGatewayId($gateway)];
		$Gateway = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_pay_service WHERE id = :id", $param);
		return $Gateway;
	}

	public function LkGetGatewaysOn()
	{
		$allKass = $this->Db->queryAll('lk', 0, 0, "SELECT id, name_kassa FROM lk_pay_service WHERE status = 1 ORDER BY id ASC");
		return $allKass;
	}

	public function LkGetGatewayOn($gateway)
	{
		$param = ['id' => $this->LkConvertGatewayId($gateway)];
		$gatewayExist = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_pay_service WHERE status = 1 AND id = :id", $param);
		return $gatewayExist;
	}

	public function LkGetUserData($user)
	{
		if (!preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $user))
			return false;
		$param = ['auth' => $user];
		$userdata = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk WHERE auth  = :auth", $param);
		return $userdata;
	}

	public function LkGetUserPays($user)
	{
		if (!preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $user))
			return false;
		$param = ['auth' => $user];
		$userdata = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_pays WHERE pay_auth  = :auth  ORDER BY pay_id DESC", $param);
		return $userdata;
	}

	public function LkGetAllPays(int $min, int $max)
	{

		$pays = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_pays ORDER BY pay_id DESC LIMIT $min, $max");
		return $pays;
	}

	public function LkGetPays()
	{
		$pays = $this->Db->queryAll('lk', 0, 0, "SELECT `pay_order`, `pay_auth`, `pay_summ`, `pay_data`, pay_system, `pay_status` FROM `lk_pays` WHERE `pay_status` = 1 AND NOT `pay_system` = 'admin' ORDER BY `pay_id` DESC LIMIT 4");
		return $pays;
	}

	public function LkGetAllPlayers(int $min, int $max)
	{
		return $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk ORDER BY all_cash DESC LIMIT $min, $max");
	}

	public function UsersPageMax($max)
	{
		$param = ['max' => $max];
		return ceil($this->Db->queryNum('lk', 0, 0, "SELECT COUNT(*) FROM lk")[0] / $max);
	}

	public function PaysPageMax($max)
	{
		$param = ['max' => $max];
		return ceil($this->Db->queryNum('lk', 0, 0, "SELECT COUNT(*) FROM lk_pays")[0] / $max);
	}

	public function delGateWayError()
	{
		$this->Db->query('lk', 0, 0, "DELETE FROM `lk_pays` WHERE `pay_status` = 0");
		return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successIziToast')];
	}

	public function LkUsagePromo($promo)
	{
		if (!preg_match('/^[A-z-0-9]{5,15}$/', $promo))
			return false;
		$param = ['pay_promo' => $promo];
		$promoData = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_pays WHERE pay_promo  = :pay_promo AND pay_status = 1  ORDER BY pay_id DESC", $param);
		return $promoData;
	}

	public function LkAddPromocode($post)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true)
			exit;
		if (empty($post['addpromo']))
			$promo = substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, rand(5, 15));
		else
			$promo = $post['addpromo'];
		if (!preg_match('/^[A-z-0-9]{5,15}$/', $promo))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ErrorNamePromo'), 'error');
		else if (empty($post['limit']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_LimitPromo'), 'error');
		else if (!preg_match('/^\d+$/', $post['limit']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_LimitField'), 'error');
		else if (empty($post['bonuspecent']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_EnterBonus'), 'error');
		else if (!preg_match('/^[0-9\.]+$/', $post['bonuspecent']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_NotCorBonus'), 'error');
		$param = ['code' => $promo];
		$expromo = $this->Db->queryAll('lk', 0, 0, "SELECT code FROM lk_promocodes WHERE code = :code", $param);
		if (!empty($expromo))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ExistPromo'), 'error');
		empty($post['status']) ? $auth = 0 :  $auth = 1;
		empty($post['hide']) ? $hide = 0 :  $hide = 1;
		empty($post['new_players']) ? $new = 0 :  $new = 1;
		$params = [
			'code' => $promo,
			'attempts' => $post['limit'],
			'percent' => $post['bonuspecent'],
			'auth' => $auth,
			'hide' => $hide,
			'new' => $new
		];
		$this->Db->query('lk', 0, 0, "INSERT INTO lk_promocodes(code, percent, attempts, auth1, hide, new) VALUES(:code, :percent, :attempts, :auth, :hide, :new)", $params);
		$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_AddedPromo'), ['namepromo' => $promo]), 'success');
	}

	public function LkEditPromocode($post)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true)
			exit;
		if (empty($post['editid']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_Error'), 'error');
		else if (!preg_match('/^\d+$/', $post['editid']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_Error'), 'error');
		else if (empty($post['editpromo']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_EditPromoName'), 'error');
		else if (!preg_match('/^[A-z-0-9]{5,15}$/', $post['editpromo']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ErrorNamePromo'), 'error');
		else if (empty($post['editlimit']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_LimitPromo'), 'error');
		else if (!preg_match('/^\d+$/', $post['editlimit']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_LimitField'), 'error');
		else if (empty($post['editbonuspecent']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_EnterBonus'), 'error');
		else if (!preg_match('/^[0-9\.]+$/', $post['editbonuspecent']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_NotCorBonus'), 'error');
		empty($post['status']) ? $auth = 0 :  $auth = 1;
		empty($post['hide']) ? $hide = 0 :  $hide = 1;
		empty($post['new_players']) ? $new = 0 :  $new = 1;
		$param = ['id' => $post['editid']];
		$expromo = $this->Db->queryAll('lk', 0, 0, "SELECT id FROM lk_promocodes WHERE id = :id", $param);
		if (empty($expromo))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_Error'), 'error');
		$params = [
			'id' => $post['editid'],
			'code' => $post['editpromo'],
			'attempts' => $post['editlimit'],
			'percent' => $post['editbonuspecent'],
			'auth' => $auth,
			'hide' => $hide,
			'new' => $new
		];
		$this->Db->query('lk', 0, 0, "UPDATE lk_promocodes SET code=:code, percent=:percent, attempts=:attempts, auth1=:auth, hide=:hide, new=:new WHERE id=:id", $params);
		$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_EditedPromo'), ['namepromo' => $post['editpromo']]), 'success');
	}

	public function LkDeletePromocode($post)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true)
			exit;
		if (empty($post['promocode_delete']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_Error'), 'error');
		else if (!preg_match('/^\d+$/', $post['promocode_delete']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_Error'), 'error');
		$param = ['id' => $post['promocode_delete']];
		$expromo = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_promocodes WHERE id = :id", $param);
		if (empty($expromo))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_Error'), 'error');
		$this->Db->query('lk', 0, 0, "DELETE FROM lk_promocodes WHERE id = :id", $param);
		$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_DeletedPromo'), 'success');
	}

	public function LkAddDiscord($post)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true)
			exit;
		if (empty($post['webhoock_url_offon']))
			$auth = 0;
		else {
			if (empty($post['webhoock_url']))
				$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_EnterWebhoockUrl'), 'error');
			$auth = 1;
		}
		$param = ['url' => $post['webhoock_url'], 'auth' => $auth];
		$allGateways = $this->Db->query('lk', 0, 0, "UPDATE lk_discord SET url=:url, auth=:auth", $param);
		$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_Saved'), 'success');
	}

	public function LkAddGateway($post)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true)
			exit;
		$this->LkExistGatewayAdd($post);
		$this->LKvalidateGatewayData($post['gateway'], $post);
		$params = [
			'id' => $this->LkConvertGatewayId($post['gateway']),
			'name' => $this->name,
			'shopid' => $post['shopid'],
			'secret1' => $post['secret1'] ?? '',
			'secret2' => $post['secret2'] ?? ''
		];
		$this->Db->query('lk', 0, 0, "INSERT INTO lk_pay_service VALUES(:id, :name, :shopid, :secret1, :secret2, 1)", $params);
		$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_AddGateway'), ['name' => $this->name]), 'success');
	}

	public function LkEditGateway($post)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true)
			exit;
		if (isset($_POST['status']))
			$status = 1;
		else
			$status = 0;
		$this->LkNotExistGateway($post['gateway_edit']);
		$this->LKvalidateGatewayData($post['gateway_edit'], $post);
		$params = [
			'id' => $this->LkConvertGatewayId($post['gateway_edit']),
			'status' => $status,
			'shopid' => $post['shopid'],
			'secret1' => $post['secret1'] ?? '',
			'secret2' => $post['secret2'] ?? ''
		];
		$this->Db->query('lk', 0, 0, "UPDATE lk_pay_service SET shop_id = :shopid, secret_key_1 = :secret1, secret_key_2 = :secret2, status = :status WHERE id = :id", $params);
		$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_DataChangesGateway'), ['gateway' => $this->name]), 'success');
	}

	public function LkDeleteGateway($post)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true)
			exit;
		if (!preg_match('/^\d+$/i', $post['gateway_delete']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_Error'), 'error');
		$this->LkNotExistGateway($this->LkConvertGatewayString($post['gateway_delete']));
		$param = ['id' => $post['gateway_delete']];
		$this->Db->query('lk', 0, 0, "DELETE FROM lk_pay_service WHERE id = :id", $param);
		$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_DeletedGateway'), 'success');
	}

	public function LkNotExistGateway($gateway)
	{
		$param = ['id' => $this->LkConvertGatewayId($gateway)];
		$gateway = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_pay_service WHERE id = :id", $param);
		if (empty($gateway))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_GetwNoExist'), 'error');
	}

	protected function LkExistGatewayAdd($post)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true)
			exit;
		$param = ['id' => $this->LkConvertGatewayId($post['gateway'])];
		$gateway = $this->Db->queryAll('lk', 0, 0, "SELECT id FROM lk_pay_service WHERE id = :id", $param);
		if (!empty($gateway))
			$this->message($this->name . $this->Translate->get_translate_module_phrase('module_page_pay', '_GetwExist'), 'error');
	}

	protected function LKvalidateGatewayData($gateway, $post)
	{
		switch ($gateway) {
			case 'freekassa':
				if (empty($post['shopid']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISID'), 'error');
				else if (empty($post['secret1']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC') . ' #1', 'error');
				else if (empty($post['secret2']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC') . ' #2', 'error');
				$this->name = 'FreeKassa';
				break;
			case 'paypalych':
				if (empty($post['shopid']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_IPURSE'), 'error');
				else if (empty($post['secret2']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC'), 'error');
				$this->name = 'PayPalych';
				break;
			case 'centapp':
				if (empty($post['shopid']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_IPURSE'), 'error');
				else if (empty($post['secret2']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC'), 'error');
				$this->name = 'centapp';
				break;
			case 'yoomoney':
				if (empty($post['shopid']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_IPURSE'), 'error');
				else if (empty($post['secret2']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC'), 'error');
				$this->name = 'YooMoney';
				break;
			case 'webmoney':
				if (empty($post['shopid']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_IPURSE'), 'error');
				else if (empty($post['secret2']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC'), 'error');
				$this->name = 'WebMoney';
				break;
			case 'anypay':
				if (empty($post['shopid']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_IPURSE'), 'error');
				else if (empty($post['secret2']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC'), 'error');
				$this->name = 'AnyPay';
				break;
			case 'cshost':
				if (empty($post['shopid']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISID'), 'error');
				else if (empty($post['secret2']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC'), 'error');
				$this->name = 'Cshost';
				break;
			case 'aaio':
				if (empty($post['shopid']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISID'), 'error');
				else if (empty($post['secret1']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC') . ' #1', 'error');
				else if (empty($post['secret2']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC') . ' #2', 'error');
				$this->name = 'Aaio';
				break;
			case 'lava':
				if (empty($post['shopid']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISID'), 'error');
				else if (empty($post['secret1']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC') . ' #1', 'error');
				else if (empty($post['secret2']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC') . ' #2', 'error');
				$this->name = 'Lava';
				break;
			case 'skinpay':
				if (empty($post['shopid']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_IPURSE'), 'error');
				else if (empty($post['secret2']))
					$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ISEC'), 'error');
				$this->name = 'SkinPay';
				break;
			default:
				$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_NINT'), 'error');
				break;
		}
	}

	public function status($status)
	{
		if (empty($status))
			$return = '<svg class="red"><use href="/resources/img/sprite.svg#x"></use></svg>';
		else
			$return = '<svg class="green"><use href="/resources/img/sprite.svg#check"></use></svg>';
		return $return;
	}

	public function LkDelUsers()
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true)
			exit;
		$this->Db->query('lk', 0, 0, "DELETE FROM lk WHERE !cash AND all_cash = 0");
		$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_UsersDelete'), 'success');
	}

	public function LkUpdateBalance($post)
	{
		if (!isset($_SESSION['user_admin']) || IN_LR != true)
			exit;
		if (!preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $post['user']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_SteamError'), 'error');
		if (!preg_match('/^[0-9]{1,10}.[0-9]{1,2}$/', $this->WM($post['new_balance'])))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_AmountError'), 'error');
		$new_balance = $post['new_balance'] - $post['old_balance'];
		if ($new_balance != 0) {
			$params = [
				'order' => time() % 100000,
				'auth' => $post['user'],
				'summ' => $new_balance,
				'data' => date('d.m.Y H:i:s'),
				'system' => 'admin'
			];
			$this->Db->query('lk', 0, 0, "INSERT INTO `lk_pays` (`pay_order`, `pay_auth`, `pay_summ`, `pay_data`, `pay_system`, `pay_promo`, `pay_status`) VALUES($params[order],'$params[auth]',$params[summ],'$params[data]','$params[system]',' ',1)");
			$this->Notifications->SendNotification(
				$post['user'],
				'_LK',
				'_AdminPay',
				['amount' => $new_balance, 'course' => $this->General->currency, 'module_translation' => 'module_page_pay'],
				'',
				'pay',
				'_Go'
			);
		}
		$params = [
			'auth' => $post['user'],
			'cash' => $post['new_balance'],
		];
		$this->Db->query('lk', 0, 0, "UPDATE lk SET cash = :cash WHERE auth = :auth", $params);
		$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_NewBalanceUser'), ['user' => $post['user']]), 'success');
	}

	public function LkLoadPlayerProfile($link, $type = 0)
	{
		$_SAPIKEY = $this->General->arr_general['web_key'];
		$match = explode('/', $link);
		if (!empty($match[4])) {
			if (preg_match('/^(7656119)([0-9]{10})$/', $match[4])) {
				$get = $this->CurlSend("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $_SAPIKEY . "&steamids=" . $match[4]);
				$content = json_decode($get, true);
			} else {
				$get = $this->CurlSend("http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key=" . $_SAPIKEY . "&vanityurl=" . $match[4]);
				$castomName = json_decode($get, true);
				$get = $this->CurlSend("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $_SAPIKEY . "&steamids=" . $castomName['response']['steamid']);
				$content = json_decode($get, true);
			}
		} else if (preg_match('/^(7656119)([0-9]{10})$/', $link)) {
			$get = $this->CurlSend("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $_SAPIKEY . "&steamids=" . $link);
			$content = json_decode($get, true);
		} else if (preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $link)) {
			$ex = explode(":", $link);
			$_s64 = $ex[2] * 2 + $ex[1] + '76561197960265728';
			$get = $this->CurlSend("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $_SAPIKEY . "&steamids=" . $_s64);
			$content = json_decode($get, true);
		} else if (preg_match('/^\[U:(.*)\:(.*)\]$/', $link, $match)) {
			if (!empty($match[2])) {
				$_s64 = $match[2] + '76561197960265728';
				$get = $this->CurlSend("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $_SAPIKEY . "&steamids=" . $_s64);
				$content = json_decode($get, true);
			} else
				return $link;
		}
		if (!empty($content)) {
			if (!empty($type))
				return con_steam64to32($content['response']['players'][0]['steamid']);
			else
				exit(trim(json_encode(
					array(
						'img' => $content['response']['players'][0]['avatarfull'],
						'name' => $content['response']['players'][0]['personaname'],
					)
				)));
		} else {
			if (!empty($type))
				return $link;
			else
				return false;
		}
	}

	public function LkOnPayment($post)
	{
		if (empty($post['gatewayPay']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_ChangeGateway'), 'error');
		$Gateway = $this->LkGetGatewayOn($post['gatewayPay']);
		$post['steam'] = $this->LkLoadPlayerProfile($post['steam'], 2);
		if (empty($Gateway))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_GatewayOnNotEzist'), 'error');
		else if (empty($post['steam']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_EnterSteam'), 'error');
		if (!preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $post['steam']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_SteamError'), 'error');
		else if (empty($post['amount']))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_EnterAmount'), 'error');
		else if (!preg_match('/^[0-9]{1,10}.[0-9]{1,2}$/', $this->WM($post['amount'])))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_AmountError'), 'error');
		else if (!empty($post['promocode'])) {
			if (!preg_match('/^[A-z-0-9]{5,15}$/', $post['promocode']))
				$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_Error'), 'error');
			$this->checkPromo($post['promocode'], $post['steam']);
		}
		$this->LkNotExistGateway($post['gatewayPay']);
		$this->setPay($post);
	}

	protected function setPay($post)
	{
		$data = $this->LkGetGatewayOn($post['gatewayPay']);
		$order = time() % 100000;
		$desc = $this->General->arr_general['short_name'] . " " . $this->Translate->get_translate_module_phrase('module_page_pay', '_OnPayUserDesc') . con_steam64($post['steam']);
		$lk_sign = $this->Encoder($data[0]['id'] . ',' . $order . ',' . $post['amount'] . ',' . $post['steam']);
		switch ($post['gatewayPay']) {
			case 'freekassa':
				if (empty($data[0]['status'])) {
					$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_GatwayOff'), ['name' => 'FreeKassa']), 'error');
				}
				$this->LKRegPay($order, $post, 'Freekassa');
				$sign = md5($data[0]['shop_id'] . ':' . $post['amount'] . ':' . $data[0]['secret_key_1'] . ':RUB:' . $order);
				$this->location('https://pay.freekassa.com/?' . http_build_query([
					'm' => $data[0]['shop_id'],
					'oa' => $post['amount'],
					'o' => $order,
					's' => $sign,
					'currency' => 'RUB',
					'us_sign' => $lk_sign
				]));
				break;
			case 'paypalych':
				if (empty($data[0]['status'])) {
					$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_GatwayOff'), ['name' => 'PayPalych']), 'error');
				}
				$this->LKRegPay($order, $post, 'PayPalych');
				$pay_data = [
					'amount' => $post['amount'],
					'type' => 'normal',
					'shop_id' => $data[0]['shop_id'],
					'order_id' => $order,
					'currency_in' => 'RUB',
					'custom' => $lk_sign,
					'payer_pays_commission' => 1,
				];

				$api_key = $data[0]['secret_key_2'];

				$ch = curl_init('https://pal24.pro/api/v1/bill/create');
				curl_setopt($ch, CURLOPT_HTTPHEADER, [
					"Authorization: Bearer $api_key",
				]);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($pay_data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				$json = curl_exec($ch);
				curl_close($ch);

				$out_data = json_decode($json, true);

				if (isset($out_data['success']) && $out_data['success'] == true && isset($out_data['link_page_url'])) {
					$this->location($out_data['link_page_url']);
				}
				break;
			case 'centapp':
				if (empty($data[0]['status'])) {
					$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_GatwayOff'), ['name' => 'PayPalych']), 'error');
				}
				$this->LKRegPay($order, $post, 'CentApp');
				$pay_data = [
					'amount' => $post['amount'],
					'type' => 'normal',
					'shop_id' => $data[0]['shop_id'],
					'order_id' => $order,
					'currency_in' => 'RUB',
					'custom' => $lk_sign,
					'payer_pays_commission' => 1,
				];

				$api_key = $data[0]['secret_key_2'];

				$ch = curl_init('https://cent.app/api/v1/bill/create');
				curl_setopt($ch, CURLOPT_HTTPHEADER, [
					"Authorization: Bearer $api_key",
				]);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($pay_data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				$json = curl_exec($ch);
				curl_close($ch);

				$out_data = json_decode($json, true);

				if (isset($out_data['success']) && $out_data['success'] == true && isset($out_data['link_page_url'])) {
					$this->location($out_data['link_page_url']);
				}
				break;
			case 'yoomoney':
				if (empty($data[0]['status']))
					$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_GatwayOff'), ['name' => 'YooMoney']), 'error');
				$this->LKRegPay($order, $post, 'YooMoney');
				$this->message('<form method="POST" action="https://yoomoney.ru/quickpay/confirm.xml">
    				<input name="receiver" value="' . $data[0]['shop_id'] . '"><input name="quickpay-form" value="shop"><input name="targets" value="' . $desc . '">
    				<input name="paymentType" value="PC"><input name="label" value="' . $lk_sign . '">
    				<input name="sum" value="' . $post['amount'] . '"><input id="punsh" type="submit"></form>', '');
				break;
			case 'webmoney':
				if (empty($data[0]['status']))
					$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_GatwayOff'), ['name' => 'WebMoney']), 'error');
				$this->LKRegPay($order, $post, 'WebMoney');
				$this->message('<form action="https://merchant.webmoney.ru/lmi/payment.asp" method="POST">
 					<input name="lk_sign" value="' . $lk_sign . '"><input name="LMI_PAYMENT_DESC_BASE64" value="' . base64_encode($desc) . '"><input name="LMI_PAYMENT_NO" value="' . $order . '">
 					<input name="LMI_PAYEE_PURSE" value="' . $data[0]['shop_id'] . '"><input name="LMI_PAYMENT_AMOUNT" value="' . $post['amount'] . '"><input id="punsh" type="submit"></form>', '');
				break;
			case 'anypay':
				if (empty($data[0]['status'])) {
					$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_GatwayOff'), ['name' => 'AnyPay']), 'error');
				}
				$this->LKRegPay($order, $post, 'AnyPay');
				$pay_id = $data[0]['id'] . $order . $post['amount'];
				$sign = hash('md5', implode(':', array('RUB', $post['amount'], $data[0]['secret_key_2'], $data[0]['shop_id'], $pay_id)));
				$this->message('<form method="POST" action="https://anypay.io/merchant">
					<input name="merchant_id" value="' . $data[0]['shop_id'] . '"><input name="pay_id" value="' . $pay_id . '"><input name="sign" value="' . $sign . '">
					<input name="amount" value="' . $post['amount'] . '"><input name="currency" value="RUB"><input name="desc" value="' . $desc . '"><input name="us_sign" value="' . $lk_sign . '"><input id="punsh" type="submit"></form>', '');
				break;
			case 'cshost':
				if (empty($data[0]['status'])) {
					$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_GatwayOff'), ['name' => 'Cshost']), 'error');
				}
				$lk_sign_cshost = base64_encode($post['amount'] . ',' . $post['steam']);
				$timerCH = substr(time(), -4);
				$this->LKRegPay($order, $post, 'Cshost');
				$sign = hash('sha256', $lk_sign_cshost . '|' . $data[0]['shop_id'] . '|' . $order . '|' . $data[0]['secret_key_2']);
				$this->message('<form  method="post" action="https://cshost.com.ua/cassa">
					<input name="userdata" value="' . $lk_sign_cshost . '"><input name="sum" value="' . $post['amount'] . '"><input name="timer" value="' . $timerCH . '"><input name="ps" value="p2p">
					<input name="idpay" value="' . $order . '"><input name="idcassa" value="' . $data[0]['shop_id'] . '"><input name="sign" value="' . $sign . '"><input id="punsh" type="submit"></form>', '');
				break;
			case 'aaio':
				if (empty($data[0]['status'])) {
					$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_GatwayOff'), ['name' => 'Aaio']), 'error');
				}
				$this->LKRegPay($order, $post, 'Aaio');
				$pay_id = $data[0]['id'] . $order . $post['amount'];
				$sign = hash('sha256', implode(':', array($data[0]['shop_id'], $post['amount'], 'RUB', $data[0]['secret_key_1'], $pay_id)));
				$this->message('<form method="POST" action="https://aaio.so/merchant/pay">
					<input name="merchant_id" value="' . $data[0]['shop_id'] . '"><input name="amount" value="' . $post['amount'] . '"><input name="currency" value="RUB"><input name="order_id" value="' . $pay_id . '">
					<input name="sign" value="' . $sign . '"><input name="desc" value="' . $desc . '"><input name="us_key" value="' . $lk_sign . '"><input id="punsh" type="submit"></form>', '');
				break;
			case 'lava':
				if (empty($data[0]['status'])) {
					$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_GatwayOff'), ['name' => 'Lava']), 'error');
				}
				$this->LKRegPay($order, $post, 'Lava');
				$desc = $this->General->arr_general['short_name'] . " " . $this->Translate->get_translate_module_phrase('module_page_pay', '_OnPayUserDesc') . con_steam64($post['steam']);
				$payload = [
					"shopId" => $data[0]['shop_id'],
					"orderId" => (string)$order,
					"sum" => (float)$post['amount'],
					"comment" => $desc,
					"customFields" => json_encode([
						'lk_sign' => $this->Encoder($data[0]['id'] . ',' . $order . ',' . $post['amount'] . ',' . $post['steam'])
					])
				];
				ksort($payload);
				$json = json_encode($payload, JSON_THROW_ON_ERROR);
				$signature = hash_hmac("sha256", $json, $data[0]['secret_key_1']);
				$ch = curl_init('https://api.lava.ru/business/invoice/create');
				curl_setopt($ch, CURLOPT_HTTPHEADER, [
					'Content-Type: application/json',
					'Signature: ' . $signature
				]);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ch);
				curl_close($ch);
				$result = json_decode($response, true);
				if (!empty($result['data']['url'])) {
					$this->location($result['data']['url']);
				}
				break;
			case 'skinpay':
				if (empty($data[0]['status']))
					$this->message(LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_GatwayOff'), ['name' => 'SkinPay']), 'error');
				$this->LKRegPay($order, $post, 'SkinPay');
				function skinPaySign($q)
				{
					$paramsString = '';
					ksort($q);
					foreach ($q as $key => $value) {
						if ($key == 'sign')
							continue;
						$paramsString .= $key . ':' . $value . ';';
					}
					return $paramsString;
				}
				$query = [
					'orderid' => $order,
					'key' => $data[0]['shop_id'],
					'userid' => con_steam64($post['steam']),
				];
				$query['sign'] = hash_hmac('sha1', skinPaySign($query), $data[0]['secret_key_2']);
				$this->location('https://skinpay.com/deposit?' . http_build_query($query));
				break;
			default:
				$this->message('Error', 'error');
				break;
		}
	}

	protected function checkPromo($promo, $sid)
	{
		$param = ['code' => $promo];
		$codeInfo = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_promocodes WHERE code = :code", $param);
		if (empty($codeInfo))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_NotFoundPromo'), 'error');
		else if ($codeInfo[0]['attempts'] <= 0)
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_NoLimitPromo'), 'error');
		else if ($codeInfo[0]['auth1']) {
			preg_match('/:[0-9]{1}:\d+/i', $sid, $auth);
			$params = ['code' => $promo, 'auth' => '%' . $auth[0] . '%'];
			$userPromo = $this->Db->queryAll('lk', 0, 0, "SELECT pay_promo FROM lk_pays WHERE pay_promo = :code AND pay_status = 1 AND pay_auth LIKE :auth", $params);
			if ($userPromo)
				$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_YouUsePromo'), 'error');
		} else if ($codeInfo[0]['new']) {
			preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $sid, $auth);
			$time = $this->AllPlayTime($auth[0]);
			if ($time > 5) {
				$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_promoOnlyForNewPlayers'), 'error');
			}
		}
	}

	public function AllPlayTime($steam)
	{
		$total_time = 0;
		$servers = $this->General->server_list;
		foreach ($servers as $table) {
			$server_stats = explode(";", $table['server_stats']);
			$stats = $this->Db->queryAll($server_stats[0], $server_stats[1], $server_stats[2], "SELECT `steam`, `playtime` FROM `" . $server_stats[3] . "` WHERE `steam` = '" . $steam . "' LIMIT 1");
			$total_time += $stats[0]['playtime'];
		}
		return floor($total_time / 3600);
	}

	public function LkCalculatePromo($promo, $steam, $amount)
	{
		$steam = $this->LkLoadPlayerProfile($steam, 2);
		if ($amount < 0.1)
			exit(trim(json_encode(
				array(
					'result' => '<div class="code_error">' . LangValReplace($this->Translate->get_translate_module_phrase('module_page_pay', '_MinAmount'), ['course' => $this->General->currency]) . '</div>'
				)
			)));
		else if (!preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $steam))
			exit(trim(json_encode(
				array(
					'result' => $this->Translate->get_translate_module_phrase('module_page_pay', '_SteamError')
				)
			)));
		else if ($amount >= 10 && !empty($steam)) {
			$param = ['code' => $promo];
			$codeInfo = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_promocodes WHERE code = :code", $param);
			if (empty($codeInfo))
				exit(trim(json_encode(
					array(
						'result' => $this->Translate->get_translate_module_phrase('module_page_pay', '_NotFoundPromo')
					)
				)));
			else if ($codeInfo[0]['attempts'] <= 0)
				exit(trim(json_encode(
					array(
						'result' => $this->Translate->get_translate_module_phrase('module_page_pay', '_NoLimitPromo')
					)
				)));
			else if ($codeInfo[0]['auth1']) {
				preg_match('/:[0-9]{1}:\d+/i', $steam, $auth);
				$params = ['code' => $promo, 'auth' => '%' . $auth[0] . '%'];
				$userPromo = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_pays WHERE pay_promo = :code AND pay_status = 1 AND pay_auth LIKE :auth LIMIT 1", $params);
				if ($userPromo)
					exit(trim(json_encode(
						array(
							'result' => $this->Translate->get_translate_module_phrase('module_page_pay', '_YouUsePromo')
						)
					)));
			} else if ($codeInfo[0]['new']) {
				preg_match('/^STEAM_[0-9]{1,2}:[0-1]:\d+$/', $steam, $auth);
				$time = $this->AllPlayTime($auth[0]);
				if ($time > 5) {
					exit(trim(json_encode(
						array(
							'result' => "$this->Translate->get_translate_module_phrase('module_page_pay', '_promoOnlyForNewPlayers')"
						)
					)));
				}
			}
			$bonus = ($amount / 100) * $codeInfo[0]['percent'];
			$newAmount = $bonus + $amount;
			exit(trim(json_encode(
				array(
					'result' => LangValReplace(
						$this->Translate->get_translate_module_phrase('module_page_pay', '_BonusPromoUse'),
						['newamount' => $newAmount, 'percent' => $codeInfo[0]['percent']]
					),
					'result_skin' => LangValReplace(
						$this->Translate->get_translate_module_phrase('module_page_pay', '_BonusPromoUseSkin'),
						['newamount' => $newAmount, 'percent' => $codeInfo[0]['percent']]
					)
				)
			)));
		}
	}

	public function https()
	{
		if (!empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']))
			return 'https:';
		else
			return 'http:';
	}

	protected function LKRegPay($order, $post, $system)
	{
		$params = [
			'order' => $order,
			'auth' => $post['steam'],
			'summ' => $post['amount'],
			'data' => date('d.m.Y H:i:s'),
			'system' => $system,
			'promo' => $post['promocode'],
		];
		$this->Db->query('lk', 0, 0, "INSERT INTO lk_pays(pay_order, pay_auth, pay_summ, pay_data, pay_system, pay_promo, pay_status) VALUES(:order, :auth, :summ, :data, :system, :promo, 0)", $params);
	}

	protected function Encoder($string)
	{
		$return = base64_encode(base64_encode($string));
		return $return;
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

	protected function LkConvertGatewayId($gateway)
	{
		$array = [
			'cshost' => 1,
			'aaio' => 2,
			'skinpay' => 3,
			'yoomoney' => 5,
			'webmoney' => 6,
			'anypay' => 7,
			'freekassa' => 8,
			'paypalych' => 9,
			'lava' => 10,
			'centapp' => 11,
		];
		return $array[$gateway];
	}

	protected function LkConvertGatewayString($id)
	{
		$array = [
			1 => 'cshost',
			2 => 'aaio',
			3 => 'skinpay',
			5 => 'yoomoney',
			6 => 'webmoney',
			7 => 'anypay',
			8 => 'freekassa',
			9 => 'paypalych',
			10 => 'lava',
			11 => 'centapp'

		];
		return $array[$id];
	}

	protected function message($text, $status)
	{
		exit(trim(json_encode(
			array(
				'text' => $text,
				'status' => $status,
			)
		)));
	}

	protected function location($url)
	{
		exit(trim(json_encode(
			array(
				'location' => $url,
			)
		)));
	}

	public function CurlSend($url)
	{
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$url = curl_exec($c);
		return $url;
	}

	function con_steam32($steam32)
	{
		switch (true):
			case (preg_match('/^STEAM_[01]:[01]:[0-9]{2,12}$/', $steam32)):
				return $steam32;
			case (preg_match('/^(7656119)([0-9]{10})/', $steam32)):
				return con_steam64to32($steam32);
			case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/', $steam32)):
				$search_id = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/", '$3', $steam32), "/");
				$getsearch = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key={$this->General->arr_general['web_key']}&vanityurl={$search_id}"), true)['response']['steamid'];
				return con_steam64to32($getsearch);
			case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/', $steam32)):
				$search_steam = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/", '$3', $steam32), "/");
				return con_steam64to32($search_steam);
			case (preg_match('/^\[U:(.*)\:(.*)\]/', $steam32)):
				return con_steam3to32_int(str_replace(array('[U:1:', '[U:0:', ']'), '', $steam32));
			default:
				return $steam32;
		endswitch;
	}

	public function SearchUser($post)
	{
		$Steam = trim($this->con_steam32($post));
		if (empty($Steam))
			$this->message($this->Translate->get_translate_module_phrase('module_page_pay', '_emptyString'), 'error');
		$param = ['search' => "%$Steam%"];
		$infoUser = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk WHERE auth LIKE :search ORDER BY all_cash DESC LIMIT 0,20", $param);
		if (empty($infoUser))
			$infoUser = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk WHERE name LIKE :search ORDER BY all_cash  DESC LIMIT 0,20", $param);

		$_SESSION['search'] = $infoUser;
		$this->location('?section=search');
	}

	private function buildPaymentsQuery($paySystem, $withPromo, $searchUser)
	{
		$where = [];
		$params = [];

		if ($paySystem !== 'all' && !empty($paySystem)) {
			$where[] = "pay_system = :pay_system";
			$params['pay_system'] = $paySystem;
		}

		if (!empty($withPromo)) {
			$where[] = "pay_promo != ''";
		}

		if (!empty($searchUser)) {
			$where[] = "pay_auth LIKE :search_user";
			$params['search_user'] = '%' . $searchUser . '%';
		}

		$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
		return ['where' => $whereClause, 'params' => $params];
	}

	private function getPaymentsCount($paySystem, $withPromo, $searchUser)
	{
		$queryParams = $this->buildPaymentsQuery($paySystem, $withPromo, $searchUser);
		$sql = "SELECT COUNT(*) as total FROM lk_pays " . $queryParams['where'];
		$result = $this->Db->queryAll('lk', 0, 0, $sql, $queryParams['params']);
		return $result[0]['total'] ?? 0;
	}

	private function getPayments($page, $paySystem, $withPromo, $searchUser, $limit = 13)
	{
		$queryParams = $this->buildPaymentsQuery($paySystem, $withPromo, $searchUser);
		$offset = ($page - 1) * $limit;
		$sql = "SELECT * FROM lk_pays " . $queryParams['where'] . " ORDER BY pay_id DESC LIMIT $limit OFFSET $offset";
		return $this->Db->queryAll('lk', 0, 0, $sql, $queryParams['params']);
	}

	public function renderPayments(int $page = 1, string $paySystem = 'all', string $withPromo = '', string $searchUser = '')
	{
		$limit = 13;
		$page = max(1, intval($page));
		$searchUser = $this->con_steam32($searchUser);
		$totalCount = $this->getPaymentsCount($paySystem, $withPromo, $searchUser);
		$pays = $this->getPayments($page, $paySystem, $withPromo, $searchUser, $limit);
		$pageMax = ceil($totalCount / $limit);
		$data = [];
		foreach ($pays as $pay) {
			$steam = con_steam64($pay['pay_auth']);
			$data[] = [
				'steam' => con_steam64($steam),
				'name' => $this->General->checkName($steam),
				'avatar' => $this->General->getAvatar($steam, 1),
				'checked_avatar' => $this->General->checkAvatar($steam),
				'pay_order' => $pay['pay_order'],
				'pay_summ' => $pay['pay_summ'],
				'pay_data' => $pay['pay_data'],
				'pay_system' => $pay['pay_system'],
				'pay_promo' => $pay['pay_promo'],
				'pay_status' => $this->status($pay['pay_status']),
			];
		}
		return ['payments' => $data, 'page_max' => $pageMax];
	}
}
