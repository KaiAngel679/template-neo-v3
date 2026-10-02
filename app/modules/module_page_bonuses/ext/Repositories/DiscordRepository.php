<?php

namespace app\modules\module_page_bonuses\ext\Repositories;

class DiscordRepository extends CacheRepository
{
    protected $General;

    public function __construct($General)
    {
        $this->General = $General;
    }

    public function getDiscordUser(string $code): array
    {
        if (!$code) {
            return ['status' => 'error', 'message' => 'noCode'];
        }
        $data = [
            'client_id'     => $this->getCache('settings')['client_id_ds'],
            'client_secret' => $this->getCache('settings')['secret_id_ds'],
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => 'https:' . $this->General->arr_general['site'] . 'bonuses',
        ];
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded",
                'content' => http_build_query($data)
            ]
        ];
        $response = file_get_contents("https://discord.com/api/oauth2/token", false, stream_context_create($opts));
        $tokenData = json_decode($response, true);
        if (!isset($tokenData['access_token'])) {
            return ['status' => 'error', 'message' => 'noAccess'];
        }
        $accessToken = $tokenData['access_token'];
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer " . $accessToken
            ]
        ];
        $response = file_get_contents("https://discord.com/api/users/@me", false, stream_context_create($opts));
        $userData = json_decode($response, true);
        if (!isset($userData['id'])) {
            return ['status' => 'error', 'message' => 'noId'];
        }
        $userData['access_token'] = $accessToken;
        return ['status' => 'success', 'data' => $userData];
    }

    public function checkSubscriptionDiscord(array $userData): array
    {
        if (empty($userData['access_token'])) {
            return ['status' => 'error', 'message' => 'noAccess'];
        }
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer " . $userData['access_token']
            ]
        ];
        $response = file_get_contents("https://discord.com/api/users/@me/guilds", false, stream_context_create($opts));
        $guilds = json_decode($response, true);
        $isMember = 'error';
        if (is_array($guilds)) {
            foreach ($guilds as $guild) {
                if ($guild['id'] == $this->getCache('settings')['guild_id_ds']) {
                    $isMember = 'success';
                    break;
                }
            }
        }
        return ['status' => $isMember];
    }
}
