<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Repositories\FileRepository;

class LogsService
{
    private $FileRepository;

    public function __construct()
    {
        $this->FileRepository = new FileRepository();
    }

    private function dailyLogFileKey(string $folder, ?string $date = null): string
    {
        return $folder . '/' . ($date ?? date('d-m-Y'));
    }

    public function addError(array $entry): void
    {
        if (!$this->isDebugLogsEnabled()) {
            return;
        }

        $row = array_merge([
            'timestamp' => time(),
            'admin_steamid' => $_SESSION['steamid'] ?? null,
        ], $entry);

        $this->FileRepository->update($this->dailyLogFileKey('error'), function ($data) use ($row) {
            if (!is_array($data)) {
                $data = [];
            }
            $data[] = $row;

            return $data;
        });
    }

    public function addCriticalError(string $action, string $message, array $context = []): void
    {
        $this->addError([
            'kind' => 'critical',
            'action' => $action,
            'message' => $message,
            'context' => $context,
        ]);
    }

    public function addRconError(string $businessAction, string $command, string $targetSteam64, array $serverIdsSent, array $result): void
    {
        $failures = [];
        if (($result['status'] ?? '') === 'error') {
            $failures[] = ['scope' => 'batch', 'message' => $result['message'] ?? 'unknown'];
        }
        foreach ($result['servers'] ?? [] as $row) {
            if (($row['status'] ?? '') === 'error') {
                $failures[] = [
                    'scope' => 'server',
                    'server_id' => $row['server_id'] ?? null,
                    'name' => $row['name'] ?? null,
                    'message' => $row['message'] ?? null,
                ];
            }
        }
        if ($failures === []) {
            return;
        }

        $this->addError([
            'kind' => 'rcon',
            'action' => $businessAction,
            'message' => 'RCON failure',
            'context' => [
                'command' => $command,
                'target_steamid' => $targetSteam64,
                'server_ids' => $serverIdsSent,
                'failures' => $failures,
            ],
        ]);
    }

    public function add(array $log): void
    {
        $file = $this->dailyLogFileKey('logs');
        $this->FileRepository->update($file, function ($data) use ($log) {

            $data[] = [
                'type' => $log['type'] ?? 'unknown',
                'timestamp' => time(),
                'admin' => $log['admin'] ?? [],
                'target' => $log['target'] ?? [],
                'details' => $log['details'] ?? []
            ];

            return $data;
        });
    }

    public function deleteEntry(string $fileDate, int $index): bool
    {
        if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $fileDate) || $index < 0) {
            return false;
        }

        $deleted = false;
        $fileKey = $this->dailyLogFileKey('logs', $fileDate);

        $this->FileRepository->update($fileKey, function (array $data) use ($index, &$deleted): array {
            if (!isset($data[$index])) {
                return $data;
            }

            unset($data[$index]);
            $deleted = true;

            return array_values($data);
        });

        return $deleted;
    }

    public function get(string $folder, ?string $date = null): array
    {
        return $this->FileRepository->get($this->dailyLogFileKey($folder, $date));
    }

    public function delete(string $folder, ?string $date = null): void
    {
        $this->FileRepository->delete($this->dailyLogFileKey($folder, $date));
    }

    public function getLogFiles(string $folder): array
    {
        return $this->FileRepository->getLogFiles($folder);
    }

    public function buildTargetSteamids(array $steamids64): array
    {
        if (count($steamids64) == 1) {
            return ['steamid' => $steamids64[0]];
        }

        return ['steamids' => $steamids64];
    }

    private function isDebugLogsEnabled(): bool
    {
        return !empty($this->FileRepository->get('settings')['debug_logs']);
    }
}
