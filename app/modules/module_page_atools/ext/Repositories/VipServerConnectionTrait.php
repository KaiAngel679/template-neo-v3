<?php

namespace app\modules\module_page_atools\ext\Repositories;

trait VipServerConnectionTrait
{
    private function parseVipServerConnection(string $serverVip): ?array
    {
        $serverVip = trim($serverVip);
        if ($serverVip === '') {
            return null;
        }

        $parts = explode(';', $serverVip, 4);
        if (count($parts) < 4 || $parts[0] === '' || $parts[3] === '') {
            return null;
        }

        return [
            'mod' => $parts[0],
            'user_id' => (int) $parts[1],
            'db_num' => (int) $parts[2],
            'table' => $parts[3],
        ];
    }
}
