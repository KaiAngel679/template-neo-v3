<?php



namespace app\modules\module_page_pay\ext;

use app\modules\module_page_pay\ext\Basefunction;

class Aaio extends Basefunction{

	public function AACheckIP(){
		if(!in_array($this->General->get_client_ip_cdn(), array('91.107.204.74'))){
				$this->LkAddLog('_DeniedIP', ['gateway' =>'Aaio', 'ip'=>$this->General->get_client_ip_cdn()]);
				die('Request from Denied IP');
		}
	}

	public function AACheckSignature($post){
		$us = $this->Decoder($post['us_key']);
		$this->decod = explode(',', $us);
		$BChekGateway = $this->BChekGateway('Aaio');
		if(empty($BChekGateway))
			die('Gatewqy Aaio not Exist.');
		$sign = hash('sha256', implode(':', array($this->kassa[0]['shop_id'], $post['amount'], $post['currency'], trim($this->kassa[0]['secret_key_2']), $post['order_id'])));
		if($sign != $post['sign']){
			$this->LkAddLog('_NOTSIGN', ['gateway'=>'Aaio']);
			die('Invalid digital signature.');
		}
	}

	public function AAProcessPay($post){
		$BCheckPay = $this->BCheckPay('Aaio');
		if(empty($BCheckPay))die('Pay not found');
		if($this->decod[2] != $post['amount']){
			$this->LkAddLog('_NoValidSumm', ['gateway'=>'Aaio','amount' => $this->decod[2].'/'.$post['amount']]);
			die("Amount does't match");
		}
		$this->BCheckPlayer();
		$this->BCheckPromo('Aaio');
		$this->BUpdateBalancePlayer($this->decod[3],$post['amount']);
		$this->BUpdatePay();
		$this->BNotificationDiscord('Aaio');
		$this->LkAddLog('_NewDonat', ['gateway'=>'Aaio','order'=>$this->decod[1], 'course'=>$this->General->currency, 'amount' => $this->decod[2], 'steam'=>$this->decod[3]]);
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