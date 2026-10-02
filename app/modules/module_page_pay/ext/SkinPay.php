<?php




namespace app\modules\module_page_pay\ext;

use app\modules\module_page_pay\ext\Basefunction;

class SkinPay extends Basefunction {
	public function SPCheckSignature($post){
        $lk_sign = ('3,' . $post['orderid'] . ',' .  round($post['amount_real']/100) . ',' . con_steam32($post['userid']));
        $this->decod = explode(',', $lk_sign);
        $BChekGateway = $this->BChekGateway('SkinPay');
        if(empty($BChekGateway))
            die('Gatewqy SkinPay not Exist.');
        $sign = $post["sign"];
        if($post['status'] != "success")
            die('Payment status: ' . $post['status']);
        function skinPaySign($q) {
            $paramsString = '';
            ksort($q);
            foreach ($q as $key => $value) {
                if($key == 'sign') continue;
                $paramsString .= $key .':'. $value .';';
            }
            return $paramsString;
        }
        $newsign = hash_hmac('sha1', skinPaySign($post), trim($this->kassa[0]['secret_key_2']));
        if($newsign != $sign){
			$this->LkAddLog('_NOTSIGN', ['gateway'=>'SkinPay']);
			die('Invalid digital signature.');
        }
	}

	public function SPProcessPay($post){
		$BCheckPay = $this->BCheckPay('SkinPay');
        if(empty($BCheckPay)) die('Pay not found');

		$this->BCheckPlayer();
		$this->BCheckPromo('SkinPay');
		$this->BUpdateBalancePlayer($this->decod[3], $this->decod[2]);
		$this->BUpdatePay();
		$this->BNotificationDiscord('SkinPay');
        $this->LkAddLog('_NewDonat', ['gateway'=>'SkinPay','order'=>$this->decod[1], 'course'=>$this->General->currency, 'amount' => $this->decod[2], 'steam'=>$this->decod[3]]);
        $admins = $this->Db->queryAll( 'Core', 0, 0, "SELECT * FROM lvl_web_admins WHERE flags = 'z' ");
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