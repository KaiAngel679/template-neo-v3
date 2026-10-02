<?php




namespace app\modules\module_page_pay\ext;

use app\modules\module_page_pay\ext\Basefunction;

class Cshost extends Basefunction{
	public function CshostCheckSignature($post){
			$us = base64_decode($post['userdata']);
			$this->decod = explode(',', $us);

			array_unshift($this->decod, $post['idpay']);
			array_unshift($this->decod, 1);

			$BChekGateway = $this->BChekGateway('Cshost');
			if(empty($BChekGateway))
				die('Gateway Cshost not Exist.');

			$sign = hash('sha256', $post['userdata'].'|'.$this->kassa[0]['shop_id'].'|'.$post['idpay'].'|'.$this->kassa[0]['secret_key_2']);
			if($sign != $post['sign']){
				$this->LkAddLog('_NOTSIGN', ['gateway' => 'Cshost']);
				die('Invalid digital signature.');
			}
	}

	public function CshostProcessPay($post){
		$BCheckPay = $this->BCheckPay('Cshost');
		 if(empty($BCheckPay)) die('Pay not found');

		 if($this->decod[2] != $post['sum']){
			$this->LkAddLog('_NoValidSumm', ['gateway'=>'Cshost','amount' => $this->decod[2].'/'.$post['sum']]);
		 	die("Amount does't match");
		 }

		 if($post['status'] != 1){
			$this->LkAddLog('_Error', ['gateway' => 'Cshost','amount' => $this->decod[2], 'message' => "Payment not approved"]);
			die("State: Payment not approved");
		 }

		 $this->BCheckPlayer();
		 $this->BCheckPromo('Cshost');
		 $this->BUpdateBalancePlayer($this->decod[3],$this->decod[2]);
		 $this->BUpdatePay();
		 $this->BNotificationDiscord('Cshost');
		 $this->LkAddLog('_NewDonat', ['gateway' => 'Cshost','order'=>$post['idpay'], 'course'=> $this->General->currency, 'amount' => $this->decod[2], 'steam'=>$this->decod[3]]);
		 $admins = $this->Db->queryAll( 'Core', 0, 0, "SELECT * FROM lvl_web_admins WHERE flags = 'z' ");
		 foreach( $admins as $key ){
			$this->Notifications->SendNotification(
				$key['steamid'],
				'_LK',
				'_GetDonat',
				['amount' => $post['amount'], 'course' => $this->General->currency, 'module_translation' => 'module_page_pay'],
				$this->General->arr_general['site'] . 'pay/?section=payments#p'.$this->decod[1],
				'pay',
				'_Go'
			);
		}
		$this->Notifications->SendNotification(
			$this->decod[3],
			'_LK',
			'_YouPay',
			['amount' => $post['amount'], 'course' => $this->General->currency, 'module_translation' => 'module_page_pay'],
			$this->General->arr_general['site'] . 'pay/?section=payments#p'.$this->decod[1],
			'pay',
			'_Go'
		);
		 die('YES');
	}
}
