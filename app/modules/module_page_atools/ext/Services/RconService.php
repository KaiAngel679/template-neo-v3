<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Rcon;
use app\modules\module_page_atools\ext\Services\ServersService;

class RconService extends Rcon
{
    private $ServersService;

    public function __construct(object $General)
    {
        $this->ServersService = new ServersService($General);
    }

    public function sendCommand(array $serverIds, string $command, ?string $game = null): array
    {
        $serverIds = array_values(array_unique(array_map('intval', $serverIds)));

        if (in_array(-1, $serverIds, true)) {
            $serverIds = array_column($this->ServersService->getServers($game), 'id');
        }
        $serverIds = array_values(array_unique($serverIds));

        $servers = $this->ServersService->returnServersByIds($serverIds);
        if ($servers === []) {
            return ['status' => 'error', 'message' => 'Servers not found'];
        }

        $results = [];
        foreach ($servers as $server) {
            $results[] = $this->executeRconOnServer($server, $command);
        }

        return ['status' => 'success', 'servers' => $results];
    }

    private function serverRconResult(array $server, string $status, ?string $message = null, $response = null): array
    {
        $row = [
            'server_id' => $server['id'],
            'name' => $server['name_custom'],
            'status' => $status,
        ];
        if ($message !== null) {
            $row['message'] = $message;
        }
        if ($response !== null) {
            $row['response'] = $response;
        }
        return $row;
    }

    private function executeRconOnServer(array $server, string $command): array
    {
        $addressParts = explode(':', $server['ip'], 2);
        $host = $addressParts[0] ?? '';
        $port = isset($addressParts[1]) ? (int) $addressParts[1] : 0;

        if ($host === '' || $port <= 0 || empty($server['rcon'])) {
            return $this->serverRconResult($server, 'error', 'Invalid server data');
        }

        $rcon = new Rcon($host, $port);
        if (!$rcon->Connect()) {
            return $this->serverRconResult($server, 'error', 'Connection failed');
        }

        if ($rcon->RconPass($server['rcon']) === false) {
            $rcon->Disconnect();
            return $this->serverRconResult($server, 'error', 'RCON auth failed');
        }

        $response = $rcon->Command($command);
        $rcon->Disconnect();

        return $this->serverRconResult($server, 'success', null, $response);
    }
}
