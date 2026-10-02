<?php

namespace app\modules\module_page_pay\ext;

use app\modules\module_page_pay\ext\Basefunction;

class Lava extends Basefunction
{
    public function LCheckSignature()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (empty($data))
            die('Empty payload');
        $custom = json_decode($data['custom_fields'] ?? '', true);
        $us = $this->Decoder($custom['lk_sign']);
        $this->decod = explode(',', $us);
        $BChekGateway = $this->BChekGateway('Lava');
        if (empty($BChekGateway))
            die('Gatewqy Lava not Exist.');
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        if (!$auth)
            die('No Authorization header');
        ksort($data);
        $expected = hash_hmac("sha256", json_encode($data), $this->kassa[0]['secret_key_2']);
        if ($expected !== $auth) {
            $this->LkAddLog('_NOTSIGN', ['gateway' => 'Lava']);
            die('Invalid digital signature');
        }

        return $data;
    }

    public function LProcessPay()
    {
        $data = $this->LCheckSignature();
        $BCheckPay = $this->BCheckPay('Lava');
        if (empty($BCheckPay)) die('Pay not found');
        if ($data['status'] != "success") {
            $this->LkAddLog('_NOTSIGN', ['gateway' => 'Lava']);
            die('Invalid digital signature.');
        }
        if ($this->decod[2] != $data['amount']) {
            $this->LkAddLog('_NoValidSumm', ['gateway' => 'Lava', 'amount' => $this->decod[2] . '/' . $data['amount']]);
            die("Amount mismatch");
        }
        $this->BCheckPlayer();
        $this->BCheckPromo('Lava');
        $this->BUpdateBalancePlayer($this->decod[3], $this->decod[2]);
        $this->BUpdatePay();
        $this->BNotificationDiscord('Lava');
        $this->LkAddLog('_NewDonat', ['gateway' => 'Lava', 'order' => $this->decod[1], 'course' => $this->General->currency, 'amount' => $this->decod[2], 'steam' => $this->decod[3]]);
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
        die('OK');
    }
}
