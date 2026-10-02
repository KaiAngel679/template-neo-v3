<?php

namespace app\modules\module_page_bonuses\ext\Repositories;

class TelegramRepository extends CacheRepository
{
    private const AUTH_MAX_AGE = 86400;

    private const CHAT_MEMBER_OK = ['creator', 'administrator', 'member', 'restricted'];

    public function hashDecode(string $hash): array
    {
        $hash = trim($hash);
        if ($hash === '') {
            return ['status' => 'error', 'message' => 'noHash'];
        }

        $raw = base64_decode(strtr($hash, '-_', '+/'), true);
        if ($raw === false) {
            return ['status' => 'error', 'message' => 'noHash'];
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['hash'])) {
            return ['status' => 'error', 'message' => 'noHash'];
        }

        return ['status' => 'success', 'data' => $data];
    }

    public function authTelegram(array $data): array
    {
        $botToken = trim((string) ($this->getCache('settings')['bot_key_tg'] ?? ''));
        if ($botToken === '') {
            return ['status' => 'error', 'message' => 'noBotToken'];
        }

        if (!empty($data['auth_date']) && (time() - (int) $data['auth_date']) > self::AUTH_MAX_AGE) {
            return ['status' => 'error', 'message' => 'authExpired'];
        }

        $check_hash = $data['hash'];
        unset($data['hash']);
        $data_check_arr = [];
        foreach ($data as $key => $value) {
            $data_check_arr[] = $key . '=' . $value;
        }
        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $botToken, true);
        $calc = hash_hmac('sha256', $data_check_string, $secret_key);

        if (hash_equals($calc, $check_hash)) {
            return ['status' => 'success'];
        }

        return ['status' => 'error', 'message' => 'badHash'];
    }

    public function checkSubscriptionTelegram(array $result): array
    {
        $settings = $this->getCache('settings');
        $botToken = trim((string) ($settings['bot_key_tg'] ?? ''));
        $chatId = $this->normalizeTelegramChatId((string) ($settings['url_tg'] ?? ''));
        if ($botToken === '' || $chatId === null) {
            return ['status' => 'error', 'message' => 'badConfig'];
        }

        $userId = (int) ($result['id'] ?? 0);
        if ($userId <= 0) {
            return ['status' => 'error', 'message' => 'noUser'];
        }

        $url = 'https://api.telegram.org/bot' . $botToken
            . '/getChatMember?chat_id=' . rawurlencode($chatId)
            . '&user_id=' . $userId;
        $response = @file_get_contents($url);
        if ($response === false) {
            return ['status' => 'error', 'message' => 'requestFailed'];
        }

        $data = json_decode($response, true);
        if (!is_array($data) || empty($data['ok'])) {
            return ['status' => 'error', 'message' => 'apiError'];
        }

        $status = $data['result']['status'] ?? '';
        if (in_array($status, self::CHAT_MEMBER_OK, true)) {
            return ['status' => 'success'];
        }

        return ['status' => 'error', 'message' => 'notMember'];
    }

    private function normalizeTelegramChatId(string $urlTg): ?string
    {
        $urlTg = trim($urlTg);
        if ($urlTg === '') {
            return null;
        }

        if (preg_match('/^-?\d+$/', $urlTg)) {
            return $urlTg;
        }

        $path = preg_replace('#^https?://(www\.)?(t\.me|telegram\.me)/#i', '', $urlTg);
        $path = trim($path, '/');
        $path = preg_replace('/\?.*$/', '', $path);
        if ($path === '' || (isset($path[0]) && $path[0] === '+')) {
            return null;
        }

        if (preg_match('/^-?\d+$/', $path)) {
            return $path;
        }

        return '@' . ltrim($path, '@');
    }
}
