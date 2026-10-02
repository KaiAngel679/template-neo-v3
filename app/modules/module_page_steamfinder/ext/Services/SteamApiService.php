<?php

namespace app\modules\module_page_steamfinder\ext\Services;

use app\modules\module_page_steamfinder\ext\Repositories\CacheRepository;

class SteamApiService
{
    protected $General, $CacheRepository, $api_key, $Translate;

    public function __construct($General, $Translate)
    {
        $this->General = $General;
        $this->api_key = $this->General->get_default_options()['web_key'];
        $this->CacheRepository = new CacheRepository();
        $this->Translate = $Translate;
    }

    public function getAllSteamFormats($input)
    {
        $result = [
            'steam3' => null,
            'steam32' => null,
            'steam64' => null,
            'profile_url' => null,
            'profile_url_username' => null
        ];

        $input = trim($input);
        $steam64 = null;

        if (filter_var($input, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($input);
            if (strpos($parsed['host'], 'steamcommunity.com') !== false) {
                $path = trim($parsed['path'], '/');
                $parts = explode('/', $path);

                if ($parts[0] === 'profiles' && isset($parts[1]) && is_numeric($parts[1])) {
                    $steam64 = $parts[1];
                } elseif ($parts[0] === 'id' && isset($parts[1])) {
                    $steam64 = $this->resolveVanityUrl($parts[1]);
                    if (!$steam64) {
                        $result['text'] = $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_nofoundUser');
                        return $result;
                    }
                }
            }
        } elseif (preg_match('/^STEAM_/', $input)) {
            $steam64 = con_steam32to64($input);
        } elseif (preg_match('/^765/', $input) && strlen($input) > 15) {
            $steam64 = $input;
        } elseif (preg_match('/^\[U:1:(\d+)\]$/', $input, $matches)) {
            $steam3 = $matches[1];
            $steam64 = con_steam3to64_int($steam3);
        }

        $result['steam64'] = $steam64;
        $result['steam32'] = con_steam64to32($steam64);
        $result['steam3'] = con_steam64to3_int($steam64);
        $result['profile_url'] = "https://steamcommunity.com/profiles/{$steam64}";
        $vanityUrl = $this->getVanityUrl($steam64);
        $result['profile_url_username'] = $vanityUrl ? "https://steamcommunity.com/id/{$vanityUrl}" : "https://steamcommunity.com/id/{$steam64}";

        return $result;
    }

    private function getVanityUrl($steam64)
    {
        $url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key={$this->api_key}&steamids={$steam64}";

        $response = file_get_contents($url);
        if ($response === false)
            return false;

        $data = json_decode($response, true);

        if (!empty($data['response']['players'][0]['profileurl'])) {
            $profileUrl = $data['response']['players'][0]['profileurl'];
            if (preg_match('/\/id\/([^\/]+)/', $profileUrl, $matches)) {
                return $matches[1];
            }
        }

        return false;
    }

    private function resolveVanityUrl($vanityUrl)
    {
        $url = "https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/?key={$this->api_key}&vanityurl={$vanityUrl}";

        $response = file_get_contents($url);
        if ($response === false)
            return false;

        $data = json_decode($response, true);

        return $data['response']['steamid'] ?? false;
    }

    public function getSteamProfileInfo($steam64)
    {
        $cached = $this->CacheRepository->get($steam64);
        if ($cached !== null && isset($cached['steam_profile'])) {
            return $cached['steam_profile'];
        }

        $result = [
            'nickname' => null,
            'profile_state' => null,
            'privacy_state' => null,
            'status' => null,
            'last_logoff' => null,
            'time_created' => null,
            'country' => null,
            'steam_level' => null,
            'friends_count' => null,
        ];

        $summariesUrl = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key={$this->api_key}&steamids={$steam64}";
        $summariesResponse = file_get_contents($summariesUrl);

        if ($summariesResponse === false) {
            return $result;
        }
        $summariesData = json_decode($summariesResponse, true);
        if (empty($summariesData['response']['players'][0])) {
            return $result;
        }

        $profile = $summariesData['response']['players'][0];

        $result['nickname'] = $profile['personaname'] ?? null;
        $result['profile_state'] = $profile['profilestate'] ?? 0;
        $result['status'] = $profile['personastate'] ?? 0;
        $result['last_logoff'] = $profile['lastlogoff'] ?? null;
        $result['time_created'] = $profile['timecreated'] ?? null;
        $result['country'] = $profile['loccountrycode'] ?? null;

        if (isset($profile['communityvisibilitystate'])) {
            $result['privacy_state'] = ($profile['communityvisibilitystate'] == 3) ? 'public' : 'private';
        }

        $levelUrl = "https://api.steampowered.com/IPlayerService/GetSteamLevel/v1/?key={$this->api_key}&steamid={$steam64}";
        $levelResponse = file_get_contents($levelUrl);

        if ($levelResponse !== false) {
            $levelData = json_decode($levelResponse, true);
            $result['steam_level'] = $levelData['response']['player_level'] ?? null;
        }

        $friendsUrl = "https://api.steampowered.com/ISteamUser/GetFriendList/v1/?key={$this->api_key}&steamid={$steam64}&relationship=friend";
        $friendsResponse = file_get_contents($friendsUrl);

        if ($friendsResponse !== false) {
            $friendsData = json_decode($friendsResponse, true);
            if (!empty($friendsData['friendslist']['friends'])) {
                $result['friends_count'] = count($friendsData['friendslist']['friends']);
            }
        }

        $this->CacheRepository->set($steam64, 'steam_profile', $result);

        return $result;
    }

    public function getSteamBanInfo($steam64)
    {
        $cached = $this->CacheRepository->get($steam64);
        if ($cached !== null && isset($cached['steam_bans'])) {
            return $cached['steam_bans'];
        }

        $result = [
            'community_ban' => false,
            'vac_status' => false,
            'vac_banned_games_count' => 0,
            'last_vac_ban_days_ago' => null,
            'game_bans' => 0,
            'economy_ban' => 'none',
            'trade_status' => 'enabled',
            'overall_status' => 'clean'
        ];

        $url = "https://api.steampowered.com/ISteamUser/GetPlayerBans/v1/?key={$this->api_key}&steamids={$steam64}";
        $response = file_get_contents($url);

        if ($response === false) {
            return $result;
        }

        $data = json_decode($response, true);

        if (empty($data['players'][0])) {
            return $result;
        }

        $banInfo = $data['players'][0];

        $result['community_ban'] = $banInfo['CommunityBanned'];
        $result['vac_status'] = $banInfo['VACBanned'];
        $result['game_bans'] = $banInfo['NumberOfGameBans'];
        $result['economy_ban'] = $banInfo['EconomyBan'] ?? 'none';

        if ($result['vac_status']) {
            $result['vac_banned_games_count'] = $banInfo['NumberOfVACBans'] ?? 0;
            $result['last_vac_ban_days_ago'] = $banInfo['DaysSinceLastBan'] ?? null;
        }

        if ($result['economy_ban'] !== 'none') {
            $result['trade_status'] = 'disabled';
        } else {
            $result['trade_status'] = $banInfo['DaysSinceLastBan'] > 0 ? 'probation' : 'enabled';
        }

        $result['overall_status'] = ($result['vac_status'] || $result['game_bans'] > 0 || $result['community_ban'] || $result['economy_ban'] !== 'none') ? 'banned' : 'clean';

        $this->CacheRepository->set($steam64, 'steam_bans', $result);

        return $result;
    }

    public function getSteamGamesInfo($steam64)
    {
        $cached = $this->CacheRepository->get($steam64);
        if ($cached !== null && isset($cached['steam_games'])) {
            return $cached['steam_games'];
        }
        $result = [
            'game_count' => 0,
            'total_playtime_hours' => 0,
            'cs2_playtime_hours' => 0,
            'average_playtime_hours' => 0,
            'top_games' => [],
        ];
        $url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?key={$this->api_key}&steamid={$steam64}&include_played_free_games=1&include_appinfo=1";
        $response = @file_get_contents($url);
        if ($response === false) {
            return $result;
        }
        $data = json_decode($response, true);
        if (empty($data['response'])) {
            return $result;
        }
        $result['game_count'] = $data['response']['game_count'] ?? 0;
        if (!empty($data['response']['games'])) {
            $games = $data['response']['games'];
            $playedGamesCount = 0;
            $totalMinutes = 0;

            foreach ($games as $g) {
                $playtime = (int)($g['playtime_forever'] ?? 0);
                if ($playtime > 0) {
                    $totalMinutes += $playtime;
                    $playedGamesCount++;
                }
                if (($g['appid'] ?? 0) == 730) {
                    $result['cs2_playtime_hours'] = round($playtime / 60);
                }
            }
            $result['total_playtime_hours'] = round($totalMinutes / 60);
            if ($playedGamesCount > 0) {
                $result['average_playtime_hours'] = round($result['total_playtime_hours'] / $playedGamesCount);
            }
            usort($games, function ($a, $b) {
                return ($b['playtime_forever'] ?? 0) <=> ($a['playtime_forever'] ?? 0);
            });
            $top = array_slice($games, 0, 15);
            foreach ($top as $tg) {
                $playtime = (int)($tg['playtime_forever'] ?? 0);
                $playtime_2weeks = (int)($tg['playtime_2weeks'] ?? 0);
                $result['top_games'][] = [
                    'appid' => $tg['appid'],
                    'name' => $tg['name'] ?? '',
                    'playtime_2weeks' => round($playtime_2weeks / 60),
                    'playtime_hours' => round($playtime / 60),
                ];
            }
        }

        $this->CacheRepository->set($steam64, 'steam_games', $result);
        return $result;
    }

    public function getCs2Stats($steam64)
    {
        $cached = $this->CacheRepository->get($steam64);
        if ($cached !== null && isset($cached['steam_cs2_stats'])) {
            return $cached['steam_cs2_stats'];
        }
        $result = [
            'kills' => 0,
            'deaths' => 0,
            'kd' => 0,
            'mvps' => 0,
            'wins' => 0,
            'matches_played' => 0,
            'shots_fired' => 0,
            'shots_hit' => 0,
            'accuracy_percent' => 0
        ];
        $url = "https://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v2/?appid=730&key={$this->api_key}&steamid={$steam64}";
        $response = @file_get_contents($url);
        if ($response === false) {
            return $result;
        }
        $data = json_decode($response, true);
        if (empty($data['playerstats']['stats'])) {
            return $result;
        }
        $stats = [];
        foreach ($data['playerstats']['stats'] as $item) {
            $stats[$item['name']] = $item['value'];
        }
        $result['kills'] = (int)($stats['total_kills'] ?? 0);
        $result['deaths'] = (int)($stats['total_deaths'] ?? 0);
        $result['mvps'] = (int)($stats['total_mvps'] ?? 0);
        $result['wins'] = (int)($stats['total_matches_won'] ?? 0);
        $result['matches_played'] = (int)($stats['total_matches_played'] ?? 0);
        $result['shots_fired'] = (int)($stats['total_shots_fired'] ?? 0);
        $result['shots_hit'] = (int)($stats['total_shots_hit'] ?? 0);
        $result['kd'] = $result['deaths'] > 0 ? round($result['kills'] / $result['deaths'], 2) : $result['kills'];
        $result['accuracy_percent'] = $result['shots_fired'] > 0 ? (int)round(($result['shots_hit'] / $result['shots_fired']) * 100) : 0;

        $this->CacheRepository->set($steam64, 'steam_cs2_stats', $result);
        return $result;
    }
}
