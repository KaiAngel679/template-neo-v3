<?php



namespace app\modules\module_page_pay\ext;

use app\modules\module_page_pay\ext\Basefunction;

class AnyPay extends Basefunction{

	public function APCheckIP(){
		if(!in_array($this->General->get_client_ip_cdn(), array('185.162.128.38', '185.162.128.39', '185.162.128.88'))){
				$this->LkAddLog('_DeniedIP', ['gateway' =>'AnyPay', 'ip'=>$this->General->get_client_ip_cdn()]);
				die('Request from Denied IP');
		}
	}

	public function APCheckSignature($post){
		$us = $this->Decoder($post['us_sign']);
		$this->decod = explode(',', $us);
		$BChekGateway = $this->BChekGateway('AnyPay');
		if(empty($BChekGateway))
			die('Gatewqy AnyPay not Exist.');
		$sign = hash('md5', implode(':', array($this->kassa[0]['shop_id'], $post['amount'], $post['pay_id'], trim($this->kassa[0]['secret_key_2']))));
		if($sign != $post['sign']){
			$this->LkAddLog('_NOTSIGN', ['gateway'=>'AnyPay']);
			die('Invalid digital signature.');
		}
	}

	public function APProcessPay($post){
		if ($post['status'] == 'paid') {
			$BCheckPay = $this->BCheckPay('AnyPay');
			if(empty($BCheckPay))die('Pay not found');
			if($this->decod[2] != $post['amount']){
				$this->LkAddLog('_NoValidSumm', ['gateway'=>'AnyPay','amount' => $this->decod[2].'/'.$post['amount']]);
				die("Amount does't match");
			}
			$this->BCheckPlayer();
			$this->BCheckPromo('AnyPay');
			$this->BUpdateBalancePlayer($this->decod[3],$post['amount']);
			$this->BUpdatePay();
			$this->BNotificationDiscord('AnyPay');
			$this->LkAddLog('_NewDonat', ['gateway'=>'AnyPay','order'=>$this->decod[1], 'course'=>$this->General->currency, 'amount' => $this->decod[2], 'steam'=>$this->decod[3]]);
			$admins = $this->db->queryAll( 'Core', 0, 0, "SELECT * FROM lvl_web_admins WHERE flags = 'z' ");
			foreach( $admins as $key ){
				$this->Notifications->SendNotification(
					$key['steamid'],
					'_LK',
					'_GetDonat',
					['amount' => $this->decod[2], 'course' => $this->General->currency, 'module_translation' => 'module_page_pay'],
					$this->General->arr_general['site'] . 'pay/?section=payments#p'.$this->decod[1],
					'pay',
					'_Go'
				);
			}
			$this->Notifications->SendNotification(
				$this->decod[3],
				'_LK',
				'_YouPay',
				['amount' => $this->decod[2], 'course' => $this->General->currency, 'module_translation' => 'module_page_pay'],
				$this->General->arr_general['site'] . 'pay/?section=payments#p'.$this->decod[1],
				'pay',
				'_Go'
			);
			die('YES');
		}
	}
}