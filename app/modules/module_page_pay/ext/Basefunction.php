<?php



namespace app\modules\module_page_pay\ext;

use app\ext\Modules;

use app\ext\General;

use app\ext\Db;

use app\ext\Notifications;

use app\ext\Translate;

use app\ext\AltoRouter;

class Basefunction{

	public $kassa;
	public $decod;
	public $pay;
	public $summ;
	public $bonus;
	public $Db;
	public $General;
	public $Modules;
	public $Notifications;
	public $Translate;
	public $Router;

	public function __construct() {
		$this->Db =  new Db;
		$this->Translate = new Translate;
		$this->Notifications = new Notifications( $this->Translate, $this->Db );
		$this->General = new General( $this->Db );
		$this->Router = new AltoRouter;
		empty( $this->General->arr_general['site'] ) && $this->General->arr_general['site'] = '//' . preg_replace('/^(https?:)?(\/\/)?(www\.)?/', '', $_SERVER['HTTP_REFERER']);
		$this->Modules = new Modules( $this->General, $this->Translate, $this->Notifications, $this->Router );
	}
	public function BChekGateway($gateway){
		$param = ['id' => $this->decod[0]];
		$this->kassa = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_pay_service WHERE id = :id", $param);
		if(empty($this->kassa[0]['status'])){
			$this->LkAddLog('_Foff', ['gateway' =>$gateway]);
				return false;
		}else return true;
	}
	public function BCheckPay($gateway){
		preg_match('/:[0-9]{1}:\d+/i', $this->decod[3], $auth);
		$params = [
			'order' 	=> $this->decod[1],
			'auth'		=> '%'.$auth[0].'%',
		];
		$this->pay = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_pays WHERE pay_order = :order AND pay_auth LIKE :auth AND pay_status = 0", $params);
		if(empty($this->pay)){
				$this->LkAddLog('_PayNotExist', ['course'=>$this->General->currency,'numberpay' => $this->decod[1], 'steam'=>$this->decod[3],'amount'=>$this->decod[2],'gateway' =>$gateway]);
					return false;
		}else return true;
	}

	public function BCheckPlayer(){
		preg_match('/:[0-9]{1}:\d+/i', $this->decod[3], $auth);
		$param = ['auth'=>'%'.$auth[0].'%'];
		$player = $this->Db->query('lk', 0, 0, "SELECT * FROM lk WHERE auth LIKE :auth LIMIT 1", $param);
		if(empty($player)){
			$params = [
				'auth' 		=> $this->decod[3],
				'name'		=> $this->General->checkName(con_steam32to64($this->decod[3])) ?? 'Unnamed'
			];
			$this->Db->query('lk', 0, 0, "INSERT INTO lk(auth, name, cash, all_cash) VALUES (:auth,:name,0,0)", $params);
		}
	}
	public function BCheckPromo($gateway){
		$param = ['code' => $this->pay[0]['pay_promo']];
		$promoCode = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_promocodes WHERE code = :code",$param);
		if(empty($promoCode)){
			$this->summ = $this->decod[2];
		}
		else{
			$this->Db->query('lk', 0, 0, "UPDATE lk_promocodes SET attempts = attempts - 1 WHERE code = :code",$param);
			$this->bonus = ($this->decod[2]/100)*$promoCode[0]['percent'];
			$this->summ = $this->bonus+$this->decod[2];

			$this->LkAddLog('_SetPromo',['course'=>$this->General->currency,'numberpay' => $this->decod[1], 'promocode'=>$this->pay[0]['pay_promo'],'amount'=>$this->bonus,'gateway' =>$gateway]);
		}
	}
	public function BNotificationDiscord($kassa){
		$ds = $this->Db->queryAll('lk', 0, 0, "SELECT `url`, `auth` FROM `lk_discord` LIMIT 1");
		$summ = $this->Db->queryAll('lk', 0, 0, "SELECT `all_cash` FROM `lk` WHERE `auth` = :auth LIMIT 1", ["auth" => $this->decod[3]]);
		$summ_put = number_format($this->decod[2], 0, '.', ' ') . " " .  html_entity_decode($this->General->currency);
		$summ_all = $summ[0]['all_cash'] . " " . html_entity_decode($this->General->currency);
		if (!empty($ds[0]['auth'])) {
			$steam64 = con_steam32to64($this->decod[3]);
			$json = json_encode([
				"embeds" =>
				[
					[
						"title" => $this->Translate->get_translate_module_phrase('module_page_pay', '_newPay'),
						"color"	=> hexdec("5FB36C"),
						"description" => "[{$this->Translate->get_translate_module_phrase('module_page_pay', '_openPayLogs')}](https:{$this->General->arr_general['site']}adminpanel/?section=logslk)",
						"author" => [
							"name" => $this->Translate->get_translate_module_phrase('module_page_pay', '_openProfile') . $this->General->checkName($steam64),
							"url" => "https:{$this->General->arr_general['site']}profiles/{$steam64}/?search=1",
							"icon_url" => $this->General->getAvatar($steam64, 1)
						],
						"image" => [
							"url" => "https:{$this->General->arr_general['site']}app/modules/module_page_pay/assets/gateways_discord/pay_image.png"
						],
						"thumbnail"	=> [
							"url" => "https:{$this->General->arr_general['site']}app/modules/module_page_pay/assets/gateways_discord/" . mb_strtolower($kassa) . ".png",
						],
						"fields" =>
						[
							[
								"name" => $this->Translate->get_translate_module_phrase('module_page_pay', '_payAmount'),
								"value" => '```' . $summ_put . '```',
								"inline" => false,
							],
							[
								"name" => $this->Translate->get_translate_module_phrase('module_page_pay', '_payId'),
								"value" => '```' . $this->decod[1] . '```',
								"inline" => true,
							],
							[
								"name" => $this->Translate->get_translate_module_phrase('module_page_pay', '_totalDonate'),
								"value" => '```' . $summ_all . '```',
								"inline" => true,
							],
						],
						"footer"=>
						[
							"text" => $this->Translate->get_translate_module_phrase('module_page_pay', '_purchaiseTime'),
						],
						"timestamp" => date("c"),
						],
				],
			]);
			$cl = curl_init($ds[0]['url']);
			curl_setopt($cl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
			curl_setopt($cl, CURLOPT_POST, 1);
			curl_setopt($cl, CURLOPT_POSTFIELDS, $json);
			curl_exec($cl);
		}
	}
	public function BUpdateBalancePlayer($steam,$summ){
		preg_match('/:[0-9]{1}:\d+/i', $steam, $auth);

		 $params = [
				'auth' 		=> '%'.$auth[0].'%',
				'cash'		=> $this->summ,
				'all_cash'	=> $summ,
			];
		$this->Db->query('lk', 0, 0, "UPDATE lk SET `cash` = `cash` + :cash, `all_cash` = `all_cash` + :all_cash WHERE auth LIKE :auth LIMIT 1", $params);
	}

	public function BUpdatePay(){
		 $params = [
				'auth' 		=> $this->decod[3],
				'summ' 		=> $this->decod[2],
				'order'		=> $this->decod[1],
			];
		$this->Db->query('lk', 0, 0, "UPDATE lk_pays SET `pay_status` = 1, `pay_summ` = :summ WHERE pay_auth = :auth AND pay_order = :order LIMIT 1", $params);
	}
	public function Decoder($string){
			$decod = base64_decode(base64_decode($string));
			return $decod;
	}
	public function LkAddLog($act, $log_value = []){
			$params = [
			'log_name' 		=> date('d_m_Y'),
			'log_value' 	=> json_encode($log_value),//Формируем Json
			'log_time'		=> date('_H:i:s: '),
			'log_content'	=> $act
		];
		$this->Db->query('lk', 0, 0, "INSERT INTO lk_logs(log_name, log_value, log_time, log_content) VALUES (:log_name,:log_value,:log_time,:log_content)",$params);
	}
}
