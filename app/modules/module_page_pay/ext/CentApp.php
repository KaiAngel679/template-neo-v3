<?php



namespace app\modules\module_page_pay\ext;

use app\modules\module_page_pay\ext\Basefunction;

class CentApp extends Basefunction
{

	public function CACheckIP()
	{
		if (!in_array($this->getIP(), array('65.108.45.39', '213.136.70.221'))) {
			$this->LkAddLog('_DeniedIP', ['gateway' => 'CentApp', 'ip' => $this->getIP()]);
			die('Request from Denied IP');
		}
	}

	public function CACheckSignature($post)
	{
		$us = $this->Decoder($post['custom']);
		$this->decod = explode(',', $us);
		$BChekGateway = $this->BChekGateway('CentApp');
		if (empty($BChekGateway))
			die('Gatewqy Freekassa not Exist.');
		$sign = strtoupper(md5($post['OutSum'] . ":" . $post['InvId'] . ":" . $this->kassa[0]['secret_key_2']));
		if ($sign != $post['SignatureValue']) {
			$this->LkAddLog('_NOTSIGN', ['gateway' => 'CentApp']);
			die('Invalid digital signature.');
		}
	}

	public function CAProcessPay($post)
	{
		$BCheckPay = $this->BCheckPay('CentApp');
		if (empty($BCheckPay))
			die('Pay not found');
		if ($post['Status'] != "SUCCESS") {
			$this->LkAddLog('_NOTSIGN', ['gateway' => 'CentApp']);
			die('Invalid digital signature.');
		}
		if ($this->decod[2] != $post['BalanceAmount']) {
			$this->LkAddLog('_NoValidSumm', ['gateway' => 'CentApp', 'amount' => $this->decod[2] . '/' . $post['BalanceAmount']]);
			die("Amount does't match");
		}

		$this->BCheckPlayer();
		$this->BCheckPromo('CentApp');
		$this->BUpdateBalancePlayer($this->decod[3], $post['BalanceAmount']);
		$this->BUpdatePay();
		$this->BNotificationDiscord('CentApp');
		$this->LkAddLog('_NewDonat', ['gateway' => 'CentApp', 'order' => $this->decod[1], 'course' => $this->General->currency, 'amount' => $this->decod[2], 'steam' => $this->decod[3]]);
		$admins = $this->Db->queryAll('Core', 0, 0, "SELECT * FROM lvl_web_admins WHERE flags = 'z' ");
		foreach ($admins as $key) {
			$this->Notifications->SendNotification(
				$key['steamid'],
				'_LK',
				'_GetDonat',
				['amount' => $this->decod[2], 'course' => $this->General->currency, 'module_translation' => 'module_page_pay'],
				$this->General->arr_general['site'] . 'pay/?section=payments#p' . $this->decod[1],
				'pay',
				'_Go'
			);
		}
		$this->Notifications->SendNotification(
			$this->decod[3],
			'_LK',
			'_YouPay',
			['amount' => $this->decod[2], 'course' => $this->General->currency, 'module_translation' => 'module_page_pay'],
			$this->General->arr_general['site'] . 'pay/?section=payments#p' . $this->decod[1],
			'pay',
			'_Go'
		);
		die('YES');
	}

	protected function getIP()
	{
		return $this->General->get_client_ip_cdn();
	}
}
