<?php

if ($_POST["function"] == 'options' && isset($_POST["setup"])):
    session_start();
    
    if (!isset($_SESSION['user_admin'])):
        http_response_code(403);
        exit;
    endif;
    
    $options = require '../../storage/cache/sessions/options.php';
    echo json_encode($options[$_POST["setup"]]);
    exit;
endif;

if ($_POST["function"] == 'set' & isset($_POST["option"])) {
    session_start();

    if (isset($_SESSION['user_admin'])):
        require '../../app/includes/functions.php';

        $options = require '../../storage/cache/sessions/options.php';

        if (empty($_POST["data"]) || is_numeric($_POST["data"])):
            $options[$_POST["option"]] = (int) $options[$_POST["option"]] == 0 ? 1 : 0;
        else:
            $options[$_POST["option"]] = $_POST["data"];
        endif;

        file_put_contents('../../storage/cache/sessions/options.php', '<?php return ' . var_export_min($options) . ';');
        exit;
    else:
        http_response_code(403);
        exit;
    endif;
}

if ($_POST["function"] == 'delete' && (isset($_POST["server"]) || isset($_POST["table"]))):
    session_start();

    if (isset($_SESSION['user_admin'])):
        require '../../app/includes/functions.php';

        if (isset($_POST["table"])):
            $db = require '../../storage/cache/sessions/db.php';

            $del = explode(";", $_POST["table"]);

            if (sizeof($del) > 1):
                if (sizeof($db[$del[0]]) == 1 && sizeof($db[$del[0]][$del[1]]['DB']) == 1 && sizeof($db[$del[0]][$del[1]]['DB'][$del[2]]['Prefix']) == 1):
                    unset($db[$del[0]]);
                elseif (sizeof($db[$del[0]][$del[1]]['DB'][$del[2]]['Prefix']) > 1):
                    unset($db[$del[0]][$del[1]]['DB'][$del[2]]['Prefix'][$del[3]]);
                    rsort($db[$del[0]][$del[1]]['DB'][$del[2]]['Prefix']);
                elseif (sizeof($db[$del[0]][$del[1]]['DB']) > 1):
                    unset($db[$del[0]][$del[1]]['DB'][$del[2]]);
                    rsort($db[$del[0]][$del[1]]['DB']);
                elseif (sizeof($db[$del[0]]) > 1):
                    unset($db[$del[0]][$del[1]]);
                    rsort($db[$del[0]]);
                endif;
            else:
                unset($db[$del[0]]);
            endif;

            file_put_contents('../../storage/cache/sessions/db.php', '<?php return ' . var_export_min($db) . ';');
            exit;
        endif;
    else:
        http_response_code(403);
        exit;
    endif;
endif;

if ($_POST["function"] == 'sessions' & isset($_POST["data"])) {
    session_start();

    echo (int) $_SESSION[$_POST["data"]];
    exit;
}

if ($_POST["function"] == 'translate') {
    session_start();

    $translate = file_exists('../../storage/cache/sessions/translator_cache.php') ? require '../../storage/cache/sessions/translator_cache.php' : [];
    header('Content-Type: application/json');
    echo json_encode(empty($translate) ? 'No Translation' : $translate);
    exit;
}

if ($_POST["function"] == 'avatars') {
    set_time_limit(160);

    if (isset($_POST['data']) && is_array($_POST['data'])) {
        $inputIds = array_unique($_POST['data']);
        
        if (count($inputIds) > 30) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Maximum 30 Steam IDs allowed per request']);
            exit;
        }
        
        $settings = require '../../storage/cache/sessions/options.php';

        if (empty($settings['web_key'])) {
            http_response_code(500);
            exit;
        }

        $expired = time() - $settings['avatars_cache_time'];
        $cacheDir = "../../storage/cache/img/avatars/";
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $results = [];
        $toRequest = [];
        
        $rateLimitFile = '../../storage/cache/limits/rate_limit_avatars.json';
        $rateLimitDir = dirname($rateLimitFile);
        if (!is_dir($rateLimitDir)) {
            mkdir($rateLimitDir, 0777, true);
        }
        $now = microtime(true);
        $rateLimitData = file_exists($rateLimitFile) ? json_decode(file_get_contents($rateLimitFile), true) : ['requests' => [], 'last_cleanup' => $now];
        
        $rateLimitData['requests'] = array_filter($rateLimitData['requests'], function($timestamp) use ($now) {
            return ($now - $timestamp) < 1.0;
        });
        
        if (count($rateLimitData['requests']) >= 5) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Rate limit exceeded. Maximum 5 requests per second']);
            exit;
        }
        
        $rateLimitData['requests'][] = $now;
        $rateLimitData['last_cleanup'] = $now;
        file_put_contents($rateLimitFile, json_encode($rateLimitData));

        foreach ($inputIds as $steamid) {
            $cacheFile = $cacheDir . "{$steamid}.json";
            if (file_exists($cacheFile) && filemtime($cacheFile) > $expired) {
                $json = json_decode(file_get_contents($cacheFile), true);
                if (!empty($json['avatar']) || !empty($json['animated'])) {
                    $results[$steamid] = [
                        'avatar' => $json['animated'] ?: $json['avatar'],
                        'name' => $json['name'] ?? 'Unnamed',
                        'frame' => $settings['site'] . 'storage/cache/img/avatars/1_frame.png',
                        'background' => $json['background'] ?? $settings['site'] . 'storage/cache/img/avatars/1_background.webm'
                    ];
                    continue;
                }
            }
            $toRequest[] = $steamid;
        }

        if (!empty($toRequest)) {
            $mh = curl_multi_init();
            $handles = [];
            $players = [];
            $playerData = [];
            $batchSize = 20;
            foreach (array_chunk($toRequest, $batchSize) as $chunk) {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . $settings['web_key'] . '&steamids=' . implode(",", $chunk),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[] = ['ch' => $ch, 'type' => 'summary', 'ids' => $chunk];
            }

            do {
                curl_multi_exec($mh, $running);
                while (($info = curl_multi_info_read($mh)) !== false) {
                    if ($info['msg'] == CURLMSG_DONE) {
                        foreach ($handles as $i => $handle) {
                            if ($handle['ch'] === $info['handle']) {
                                $response = curl_multi_getcontent($handle['ch']);
                                if ($response) {
                                    $decoded = json_decode($response, true);
                                    if (!empty($decoded['response']['players'])) {
                                        $players = array_merge($players, $decoded['response']['players']);
                                    }
                                }
                                curl_multi_remove_handle($mh, $handle['ch']);
                                curl_close($handle['ch']);
                                unset($handles[$i]);
                                break;
                            }
                        }
                    }
                }
                if ($running) {
                    curl_multi_select($mh);
                }
            } while ($running > 0);

            $handles = [];

            foreach ($players as $player) {
                $steamid = $player['steamid'];
                $playerData[$steamid] = [
                    'basic' => $player,
                    'avatar' => null,
                    'background' => null
                ];

                $chAvatar = curl_init();
                curl_setopt_array($chAvatar, [
                    CURLOPT_URL => "https://api.steampowered.com/IPlayerService/GetAnimatedAvatar/v1/?key={$settings['web_key']}&steamid={$steamid}",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 7,
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                curl_multi_add_handle($mh, $chAvatar);
                $handles[] = ['ch' => $chAvatar, 'type' => 'avatar', 'steamid' => $steamid];

                $chBackground = curl_init();
                curl_setopt_array($chBackground, [
                    CURLOPT_URL => "https://api.steampowered.com/IPlayerService/GetProfileBackground/v1/?key={$settings['web_key']}&steamid={$steamid}",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 7,
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                curl_multi_add_handle($mh, $chBackground);
                $handles[] = ['ch' => $chBackground, 'type' => 'background', 'steamid' => $steamid];
            }

            do {
                curl_multi_exec($mh, $running);
                while (($info = curl_multi_info_read($mh)) !== false) {
                    if ($info['msg'] == CURLMSG_DONE) {
                        foreach ($handles as $i => $handle) {
                            if ($handle['ch'] === $info['handle']) {
                                $response = curl_multi_getcontent($handle['ch']);
                                if ($response !== false) {
                                    $decoded = json_decode($response, true);
                                    $steamid = $handle['steamid'];

                                    if ($handle['type'] === 'avatar') {
                                        $playerData[$steamid]['avatar'] = $decoded;
                                    } else {
                                        $playerData[$steamid]['background'] = $decoded;
                                    }
                                }

                                curl_multi_remove_handle($mh, $handle['ch']);
                                curl_close($handle['ch']);
                                unset($handles[$i]);
                            }
                        }
                    }
                }
                if ($running) {
                    curl_multi_select($mh);
                }
            } while ($running > 0);

            foreach ($playerData as $steamid => $data) {
                $player = $data['basic'];
                $animatedPath = '';

                $avatarData = isset($data['avatar']) && is_array($data['avatar']) ? $data['avatar'] : [];
                $avatarResponse = isset($avatarData['response']) && is_array($avatarData['response']) ? $avatarData['response'] : [];
                $avatarInfo = isset($avatarResponse['avatar']) && is_array($avatarResponse['avatar']) ? $avatarResponse['avatar'] : [];

                if (!empty($avatarInfo['image_small'])) {
                    $animatedPath = 'https://cdn.akamai.steamstatic.com/steamcommunity/public/images/' . $avatarInfo['image_small'];
                }

                $backgroundPath = '';

                $bgData = isset($data['background']) && is_array($data['background']) ? $data['background'] : [];
                $bgResponse = isset($bgData['response']) && is_array($bgData['response']) ? $bgData['response'] : [];
                $bg = isset($bgResponse['profile_background']) && is_array($bgResponse['profile_background']) ? $bgResponse['profile_background'] : [];

                if (!empty($bg['movie_webm'])) {
                    $backgroundPath = 'https://cdn.akamai.steamstatic.com/steamcommunity/public/images/' . $bg['movie_webm'];
                } elseif (!empty($bg['image_large'])) {
                    $backgroundPath = 'https://cdn.akamai.steamstatic.com/steamcommunity/public/images/' . $bg['image_large'];
                }

                $json = [
                    'avatar' => $player['avatarfull'] ?? '',
                    'name' => $player['personaname'] ?? 'Unnamed',
                    'slim' => $player['avatar'] ?? '',
                    'animated' => $animatedPath,
                    'background' => $backgroundPath
                ];

                if (!empty($json['avatar']) || !empty($json['animated'])) {
                    file_put_contents($cacheDir . "{$steamid}.json", json_encode($json));
                    $results[$steamid] = [
                        'avatar' => $json['animated'] ?: $json['avatar'],
                        'name' => $json['name'],
                        'frame' => $settings['site'] . 'storage/cache/img/avatars/1_frame.png',
                        'background' => $json['background'] ?: $settings['site'] . 'storage/cache/img/avatars/1_background.webm'
                    ];
                }
            }

            curl_multi_close($mh);
        }

        $output = [];
        foreach ($inputIds as $steamid) {
            $output[$steamid] = $results[$steamid] ?? null;
        }

        header('Content-Type: application/json');
        echo json_encode($output);
        exit;
    }
}

if ($_POST["function"] == 'faceit') {
    set_time_limit(160);
    if (!isset($_POST['data']) || !is_array($_POST['data'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input data']);
        exit;
    }

    $inputIds = array_unique($_POST['data']);
    
    if (count($inputIds) > 20) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Maximum 20 Steam IDs allowed per request']);
        exit;
    }
    
    $settings = require '../../storage/cache/sessions/options.php';

    if (empty($settings['faceit_key'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Faceit API key not configured']);
        exit;
    }

    $cacheDir = "../../storage/cache/faceit/";
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }

    $cacheTime = isset($settings['faceit_cache_time']) ? (int)$settings['faceit_cache_time'] : 3600;
    $expired = time() - $cacheTime;
    $results = [];
    $toRequest = [];
    
    $rateLimitFile = '../../storage/cache/limits/rate_limit_faceit.json';
    $rateLimitDir = dirname($rateLimitFile);
    if (!is_dir($rateLimitDir)) {
        mkdir($rateLimitDir, 0777, true);
    }
    $now = microtime(true);
    $rateLimitData = file_exists($rateLimitFile) ? json_decode(file_get_contents($rateLimitFile), true) : ['requests' => [], 'last_cleanup' => $now];
    
    $rateLimitData['requests'] = array_filter($rateLimitData['requests'], function($timestamp) use ($now) {
        return ($now - $timestamp) < 1.0;
    });
    
    if (count($rateLimitData['requests']) >= 5) {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Rate limit exceeded. Maximum 5 requests per second']);
        exit;
    }
    
    $rateLimitData['requests'][] = $now;
    $rateLimitData['last_cleanup'] = $now;
    file_put_contents($rateLimitFile, json_encode($rateLimitData));

    foreach ($inputIds as $steam64) {
        $cacheFile = $cacheDir . "{$steam64}.json";
        if (file_exists($cacheFile) && filemtime($cacheFile) > $expired) {
            $json = json_decode(file_get_contents($cacheFile), true);
            if (!empty($json)) {
                $results[$steam64] = $json;
                continue;
            }
        }
        $toRequest[] = $steam64;
    }

    if (!function_exists('faceit_http_get_json')) {
        function faceit_http_get_json($url, $headers = [], $timeout = 10) {
            $headerStr = implode("\r\n", $headers);
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => $headerStr,
                    'timeout' => $timeout,
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            $body = @file_get_contents($url, false, $context);
            if ($body === false) return null;
            $decoded = json_decode($body, true);
            return is_array($decoded) ? $decoded : null;
        }
    }

    if (!empty($toRequest)) {
        $headers = [
            "Authorization: Bearer {$settings['faceit_key']}",
            'Accept: application/json'
        ];

        $playerData = [];
        $faceitIds = [];

        foreach ($toRequest as $steam64) {
            $url = "https://open.faceit.com/data/v4/players?game=cs2&game_player_id={$steam64}";
            $decoded = faceit_http_get_json($url, $headers, 10);
            if (!empty($decoded) && !empty($decoded['player_id'])) {
                $faceitIds[$steam64] = $decoded['player_id'];
                $playerData[$steam64] = [
                    'basic' => $decoded,
                    'stats' => null,
                    'matches' => null
                ];
            } else {
                $results[$steam64] = [
                    'status' => 'error',
                    'message' => $decoded['message'] ?? 'Player not found',
                    'raw' => $decoded
                ];
            }
        }

        foreach ($faceitIds as $steam64 => $faceitId) {
            $statsUrl = "https://open.faceit.com/data/v4/players/{$faceitId}/stats/cs2";
            $statsDecoded = faceit_http_get_json($statsUrl, $headers, 10);
            if ($statsDecoded !== null) {
                $playerData[$steam64]['stats'] = $statsDecoded;
            }

            $matchesUrl = "https://open.faceit.com/data/v4/players/{$faceitId}/history?game=cs2&offset=0&limit=20";
            $matchesDecoded = faceit_http_get_json($matchesUrl, $headers, 10);
            if ($matchesDecoded !== null) {
                $playerData[$steam64]['matches'] = $matchesDecoded;
            }
        }

        foreach ($playerData as $steam64 => $data) {
            if (empty($data['basic'])) continue;

            $basicData = $data['basic'];
            $statsData = $data['stats'] ?? ['error' => true];
            $matchesData = $data['matches'] ?? ['error' => true];

            $result = [
                'nickname' => $basicData['nickname'] ?? null,
                'url' => isset($basicData['faceit_url'], $basicData['settings']['language']) ? str_replace('{lang}', $basicData['settings']['language'], $basicData['faceit_url']) : null,
                'elo' => isset($basicData['games']['cs2']['faceit_elo']) ? ($basicData['games']['cs2']['faceit_elo'] . ' ELO') : '0 ELO',
                'faceit_level' => $basicData['games']['cs2']['skill_level'] ?? null,
                'faceit_level_img' => empty($basicData['games']['cs2']['skill_level']) ? '/storage/cache/img/faceit/none.svg' : '/storage/cache/img/faceit/' . $basicData['games']['cs2']['skill_level'] . '.svg',
                'kd_ratio' => null,
                'win_rate' => null,
                'headshot_percent' => null,
                'total_matches' => null,
                'last_5_matches' => [],
                'max_win_streak' => 0,
                'status' => 'ok'
            ];

            if (!isset($statsData['error'])) {
                $result['kd_ratio'] = $statsData['lifetime']['Average K/D Ratio'] ?? null;
                $result['win_rate'] = isset($statsData['lifetime']['Win Rate %']) ? round($statsData['lifetime']['Win Rate %'], 2) : null;
                $result['headshot_percent'] = isset($statsData['lifetime']['Average Headshots %']) ? round($statsData['lifetime']['Average Headshots %'], 2) : null;
                $result['total_matches'] = $statsData['lifetime']['Matches'] ?? null;
            }

            if (isset($matchesData['items']) && is_array($matchesData['items'])) {
                $currentStreak = 0;
                $maxStreak = 0;
                foreach ($matchesData['items'] as $match) {
                    $playerTeam = null;
                    if (isset($match['teams']) && is_array($match['teams'])) {
                        foreach ($match['teams'] as $teamId => $team) {
                            if (isset($team['players']) && is_array($team['players'])) {
                                foreach ($team['players'] as $player) {
                                    if (isset($player['game_player_id']) && $player['game_player_id'] == $steam64) {
                                        $playerTeam = $teamId;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                    if ($playerTeam && isset($match['results']['winner'])) {
                        $isWin = $match['results']['winner'] === $playerTeam;
                        $result['last_5_matches'][] = $isWin ? 'W' : 'L';
                        if ($isWin) {
                            $currentStreak++;
                            $maxStreak = max($maxStreak, $currentStreak);
                        } else {
                            $currentStreak = 0;
                        }
                    } else {
                        $result['last_5_matches'][] = '?';
                        $currentStreak = 0;
                    }
                }
                $result['max_win_streak'] = $maxStreak;
                $result['last_5_matches'] = array_slice($result['last_5_matches'], 0, 5);
            }

            file_put_contents($cacheDir . "{$steam64}.json", json_encode($result));
            $results[$steam64] = $result;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}
