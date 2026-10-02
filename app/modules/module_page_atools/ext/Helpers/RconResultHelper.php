<?php

namespace app\modules\module_page_atools\ext\Helpers;

use app\modules\module_page_atools\ext\Services\LogsService;
use app\modules\module_page_atools\ext\Services\RconService;

final class RconResultHelper
{
    public static function hasFailures(array $result): bool
    {
        if (($result['status'] ?? '') === 'error') {
            return true;
        }

        foreach ($result['servers'] ?? [] as $server) {
            if (($server['status'] ?? '') === 'error') {
                return true;
            }
        }

        return false;
    }

    public static function sendAndLog(RconService $rcon, LogsService $logs, string $action, string $command, string $targetSteamid64, array $serverIds, ?string $game = null): bool
    {
        $result = $rcon->sendCommand($serverIds, $command, $game);
        $logs->addRconError($action, $command, $targetSteamid64, $serverIds, $result);

        return self::hasFailures($result);
    }
}
