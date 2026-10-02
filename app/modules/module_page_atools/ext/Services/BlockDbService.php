<?php

namespace app\modules\module_page_atools\ext\Services;

use app\modules\module_page_atools\ext\Repositories\FileRepository;

final class BlockDbService
{
    private const API_BASE = 'https://api.blockdb.net';

    private $FileRepository;
    private $LogsService;

    public function __construct()
    {
        $this->FileRepository = new FileRepository();
        $this->LogsService = new LogsService();
    }

    public function isEnabled(): bool
    {
        return $this->getApiKey() !== '';
    }

    public function createBan(string $adminSteamid64, string $adminName, string $offenderSteamid64, string $offenderName, ?string $ip, int $durationSeconds, string $reason): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $adminSteamid64 = con_steam64($adminSteamid64);
        $offenderSteamid64 = con_steam64($offenderSteamid64);

        if ($adminSteamid64 === '' || $offenderSteamid64 === '') {
            return;
        }

        $this->request('POST', '/v1/public/bans', [
            'admin' => [
                'name' => $adminName !== '' ? $adminName : 'admin',
                'steamid64' => $adminSteamid64,
            ],
            'offender' => [
                'ip' => trim((string) $ip),
                'steam' => [
                    'steamid64' => $offenderSteamid64,
                    'name' => $offenderName !== '' ? $offenderName : 'player',
                ],
            ],
            'duration' => max(0, $durationSeconds),
            'reason' => $reason,
        ], 'blockdb_create_ban');
    }

    public function unban(string $offenderSteamid64): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $offenderSteamid64 = con_steam64($offenderSteamid64);
        if ($offenderSteamid64 === '') {
            return;
        }

        $this->request('POST', '/v1/public/bans/unban', [
            'steamid64' => $offenderSteamid64,
        ], 'blockdb_unban');
    }

    private function getApiKey(): string
    {
        $settings = $this->FileRepository->get('settings');

        return trim((string) ($settings['blockdb_api_key'] ?? ''));
    }

    private function request(string $method, string $path, array $payload, string $logAction): void
    {
        $apiKey = $this->getApiKey();
        if ($apiKey === '') {
            return;
        }

        $url = self::API_BASE . $path;
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);

        if ($body === false) {
            return;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError !== '') {
            $this->logFailure($logAction, $httpCode, $curlError, $payload);

            return;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->logFailure($logAction, $httpCode, (string) $response, $payload);
        }
    }

    private function logFailure(string $action, int $httpCode, string $detail, array $payload): void
    {
        if (empty($this->FileRepository->get('settings')['debug_logs'])) {
            return;
        }

        $this->LogsService->addError([
            'kind' => 'blockdb',
            'action' => $action,
            'message' => 'BlockDB request failed',
            'context' => [
                'http_code' => $httpCode,
                'detail' => $detail,
                'steamid64' => $payload['offender']['steam']['steamid64']
                    ?? $payload['steamid64']
                    ?? null,
            ],
        ]);
    }
}
