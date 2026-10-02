<?php



namespace app\modules\module_page_pay\ext;

use app\modules\module_page_pay\ext\Basefunction;

class Freekassa extends Basefunction
{

	public function FKCheckIP()
	{
		if (
			!in_array($this->getIP(), array(
				'168.119.157.136',
				'168.119.60.227',
				'138.201.88.124',
				'178.154.197.79',

			))
		) {
			$this->LkAddLog('_DeniedIP', ['gateway' => 'FreeKassa', 'ip' => $this->getIP()]);
			die('Request from Denied IP');
		}
	}

	public function FKCheckSignature($post)
	{
		$us = $this->Decoder($post['us_sign']);
		$this->decod = explode(',', $us);
		$BChekGateway = $this->BChekGateway('FreeKassa');
		if (empty($BChekGateway))
			die('Gatewqy Freekassa not Exist.');
		$sign = md5($this->kassa[0]['shop_id'] . ':' . $post['AMOUNT'] . ':' . trim($this->kassa[0]['secret_key_2']) . ':' . $post['MERCHANT_ORDER_ID']);
		if ($sign != $post['SIGN']) {
			$this->LkAddLog('_NOTSIGN', ['gateway' => 'FreeKassa']);
			die('Invalid digital signature.');
		}
	}

	public function FKProcessPay($post)
	{
		$BCheckPay = $this->BCheckPay('FreeKassa');
		if (empty($BCheckPay))
			die('Pay not found');
		if ($this->decod[2] != $post['AMOUNT']) {
			$this->LkAddLog('_NoValidSumm', ['gateway' => 'FreeKassa', 'amount' => $this->decod[2] . '/' . $post['AMOUNT']]);
			die("Amount does't match");
		}
		$this->BCheckPlayer();
		$this->BCheckPromo('FreeKassa');
		$this->BUpdateBalancePlayer($this->decod[3], $post['AMOUNT']);
		$this->BUpdatePay();
		$this->BNotificationDiscord('FreeKassa');
		$this->LkAddLog('_NewDonat', ['gateway' => 'FreeKassa', 'order' => $this->decod[1], 'course' => $this->General->currency, 'amount' => $this->decod[2], 'steam' => $this->decod[3]]);
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
			$this->General->arr_general['site'] . 'pay/?section=payments#p'.$this->decod[1],
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

