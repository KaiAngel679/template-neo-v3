<?php

namespace app\modules\module_page_steamfinder\ext\Services;

use app\modules\module_page_steamfinder\ext\Repositories\CacheRepository;

class FaceitApiService
{
    protected $General, $CacheRepository, $api_key, $Translate, $Modules;

    public function __construct($General, $Translate, $Modules)
    {
        $this->General = $General;
        $this->Translate = $Translate;
        $this->Modules = $Modules;
        $this->api_key = $this->General->get_default_options()['faceit_key'];
        $this->CacheRepository = new CacheRepository();
    }

    public function getFaceitStats($steam64)
    {
        $cached = $this->CacheRepository->get($steam64);
        if ($cached !== null && isset($cached['faceit_stats'])) {
            return $cached['faceit_stats'];
        }

        $result = [
            'nickname' => null,
            'elo' => null,
            'faceit_level' => null,
            'kd_ratio' => null,
            'win_rate' => null,
            'headshot_percent' => null,
            'total_matches' => null,
            'last_5_matches' => [],
            'max_win_streak' => 0
        ];

        $playerUrl = "https://open.faceit.com/data/v4/players?game=cs2&game_player_id={$steam64}";
        $playerData = $this->makeFaceitApiRequest($playerUrl);

        if (isset($playerData['error'])) {
            return $result;
        }

        if (empty($playerData['player_id'])) {
            return $result;
        }

        $faceitId = $playerData['player_id'];
        $result['nickname'] = $playerData['nickname'] ?? null;
        $result['faceit_level'] = $playerData['games']['cs2']['skill_level'] ?? null;
        $result['elo'] = $playerData['games']['cs2']['faceit_elo'] ?? null;

        $statsUrl = "https://open.faceit.com/data/v4/players/{$faceitId}/stats/cs2";
        $statsData = $this->makeFaceitApiRequest($statsUrl);

        if (!isset($statsData['error'])) {
            $result['kd_ratio'] = $statsData['lifetime']['Average K/D Ratio'] ?? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown');
            $result['win_rate'] = isset($statsData['lifetime']['Win Rate %']) ? round($statsData['lifetime']['Win Rate %'], 2) : $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown');
            $result['headshot_percent'] = isset($statsData['lifetime']['Average Headshots %']) ? round($statsData['lifetime']['Average Headshots %'], 2) : $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown');
            $result['total_matches'] = $statsData['lifetime']['Matches'] ?? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown');
        }

        $matchesUrl = "https://open.faceit.com/data/v4/players/{$faceitId}/history?game=cs2&offset=0&limit={$result['total_matches']}";
        $matchesData = $this->makeFaceitApiRequest($matchesUrl);

        if (!isset($matchesData['error'])) {
            $currentStreak = 0;
            $maxStreak = 0;

            foreach ($matchesData['items'] as $match) {
                $playerTeam = null;
                foreach ($match['teams'] as $teamId => $team) {
                    foreach ($team['players'] as $player) {
                        if ($player['game_player_id'] == $steam64) {
                            $playerTeam = $teamId;
                            break 2;
                        }
                    }
                }

                if ($playerTeam && isset($match['results']['winner'])) {
                    $isWin = $match['results']['winner'] == $playerTeam;
                    $result['last_5_matches'][] = $isWin ? 'W' : 'L';

                    if ($isWin) {
                        $currentStreak++;
                        if ($currentStreak > $maxStreak) {
                            $maxStreak = $currentStreak;
                        }
                    } else {
                        $currentStreak = 0;
                    }
                } else {
                    $result['last_5_matches'][] = '?';
                    $currentStreak = 0;
                }
            }
        }

        $result['max_win_streak'] = $maxStreak;

        $result['last_5_matches'] = array_slice($result['last_5_matches'], 0, 5);

        $this->CacheRepository->set($steam64, 'faceit_stats', $result);

        return $result;
    }

    private function makeFaceitApiRequest($url)
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer {$this->api_key}\r\n" .
                    "Accept: application/json\r\n"
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        return json_decode($response, true);
    }
}
