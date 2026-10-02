<?php

namespace app\modules\module_page_cards\ext\Repositories;

use app\modules\module_page_cards\ext\Rcon;

class RconRepository extends Rcon
{
    protected $General;

    public function __construct($General)
    {
        $this->General = $General;
    }

    public function sendCommand(string $command, array $ids): array
    {
        foreach ($ids as $serverId) {
            $server = null;
            foreach ($this->General->server_list as $srv) {
                if ($srv['id'] == $serverId) {
                    $server = $srv;
                    break;
                }
            }

            $ip = explode(':', $server['ip']);
            $rcon = new Rcon($ip[0], $ip[1]);

            if ($rcon->Connect()) {
                if (!empty($server['rcon'])) {
                    $rcon->RconPass($server['rcon']);
                    $rcon->Command($command);
                    $rcon->Disconnect();
                } else {
                    return ["status" => "error"];
                }
            } else {
                return ["status" => "error"];
            }
        }

        return ["status" => "success"];
    }
}
