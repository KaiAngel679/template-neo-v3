<?php



namespace app\modules\module_page_pay\ext;

use app\modules\module_page_pay\ext\Basefunction;

class Webmoney extends Basefunction
{

	public function WBCheckPurse($post)
	{
		$us = $this->Decoder($post['lk_sign']);
		$this->decod = explode(',', $us);
		$BChekGateway = $this->BChekGateway('WebMoney');
		if (empty($BChekGateway)) exit;
		if ($post['LMI_PREREQUEST'] == 1) {
			if ($post['LMI_PAYEE_PURSE'] == trim($this->kassa[0]['shop_id'])) echo 'YES';
		}
	}

	public function WBCheckSignature($post)
	{
		$key = $post['LMI_PAYEE_PURSE'] . $post['LMI_PAYMENT_AMOUNT'] . $post['LMI_PAYMENT_NO'] . $post['LMI_MODE'] . $post['LMI_SYS_INVS_NO'] . $post['LMI_SYS_TRANS_NO'] . $post['LMI_SYS_TRANS_DATE'] . trim($this->kassa[0]['secret_key_2']) . $post['LMI_PAYER_PURSE'] . $post['LMI_PAYER_WM'];
		if (strtoupper(hash('sha256', $key)) != $post['LMI_HASH']) {
			$this->LkAddLog('_NOTSIGN', ['gateway' => 'WebMoney']);
			die('Invalid digital signature.');
		}
	}

	public function WBProcessPay($post)
	{
		$BCheckPay = $this->BCheckPay('WebMoney');
		if (empty($BCheckPay)) exit;
		if ($this->decod[2] != $post['LMI_PAYMENT_AMOUNT']) {
			$this->LkAddLog('_NoValidSumm', ['gateway' => 'WebMoney', 'amount' => $this->decod[2] . '/' . $post['LMI_PAYMENT_AMOUNT']]);
			die("Amount does't match");
		}
		$this->BCheckPlayer();
		$this->BCheckPromo('WebMoney');
		$this->BUpdateBalancePlayer($this->decod[3], $post['LMI_PAYMENT_AMOUNT']);
		$this->BUpdatePay();
		$this->BNotificationDiscord('WebMoney');
		$this->LkAddLog('_NewDonat', ['gateway' => 'WebMoney', 'order' => $this->decod[1], 'course' => $this->General->currency, 'amount' => $this->decod[2], 'steam' => $this->decod[3]]);
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
