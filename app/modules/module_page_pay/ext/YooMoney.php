<?php



namespace app\modules\module_page_pay\ext;

use app\modules\module_page_pay\ext\Basefunction;

class YooMoney extends Basefunction
{

	public function YMCheckSignature($post)
	{
		$us = $this->Decoder($post['label']);
		$this->decod = explode(',', $us);
		$BChekGateway = $this->BChekGateway('YooMoney');
		if (empty($BChekGateway))
			die('Gatewqy YooMoney not Exist.');
		$hash = sha1($post['notification_type'] . '&' . $post['operation_id'] . '&' . $post['amount'] . '&' . $post['currency'] . '&' . $post['datetime'] . '&' . $post['sender'] . '&' . $post['codepro'] . '&' . trim($this->kassa[0]['secret_key_2']) . '&' . $post['label']);
		if ($post['sha1_hash'] != $hash or $post['codepro'] === true or $post['unaccepted'] === true) {
			$this->LkAddLog('_NOTSIGN', ['gateway' => 'YooMoney']);
			die('Invalid digital signature.');
		}
	}

	public function YMProcessPay($post)
	{
		$BCheckPay = $this->BCheckPay('YooMoney');
		if (empty($BCheckPay)) die('Pay not found');
		if ($this->decod[2] != $post['withdraw_amount']) {
			$this->LkAddLog('_NoValidSumm', ['gateway' => 'YooMoney', 'amount' => $this->decod[2] . '/' . $post['withdraw_amount']]);
			die("Amount does't match");
		}
		$this->BCheckPlayer();
		$this->BCheckPromo('YooMoney');
		$this->BUpdateBalancePlayer($this->decod[3], $post['withdraw_amount']);
		$this->BUpdatePay();
		$this->BNotificationDiscord('YooMoney');
		$this->LkAddLog('_NewDonat', ['gateway' => 'YooMoney', 'order' => $this->decod[1], 'course' => $this->General->currency, 'amount' => $this->decod[2], 'steam' => $this->decod[3]]);
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
}
