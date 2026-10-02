<?php

$red = $_SESSION['rpage'] ?? '';
if (! empty($_GET["auth"]) && $_GET["auth"] == 'login') {
    require 'app/ext/LightOpenID.php';
    try {
        $openid = new LightOpenID("http:" . $this->General->arr_general['site']);
        if (! $openid->mode) {
            $openid->identity = 'https://steamcommunity.com/openid';
            header('Location: ' . $openid->authUrl());
            if (! headers_sent()) { ?>
                <script type="text/javascript">
                    window.location.href = "<?= $openid->authUrl() ?>";
                </script>
                <noscript>
                    <meta http-equiv="refresh" content="0;url=<?= $openid->authUrl() ?>" />
                </noscript>
        <?php exit;
            }
        } elseif ($openid->mode == 'cancel')
            echo 'User has canceled authentication!';
        else {
            if ($openid->validate()) {
                preg_match("/^https?:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/", $openid->identity, $matches);

                $steam32 = con_steam64to32($matches[1]);
                $steam64 = $matches[1];

                $_SESSION = [
                    "steamid"           => $steam64,
                    "steamid64"         => $steam64,
                    "steamid32"         => $steam32,
                    "steamid32_short"   => substr($steam32, 8),
                    "USER_AGENT"        => $_SERVER['HTTP_USER_AGENT'],
                    "REMOTE_ADDR"       => $this->General->get_client_ip_cdn()
                ];

                if (! empty($this->Db->db_data['LevelsRanks'])) {
                    try {
                        if (!empty($this->Db->db_data['LevelsRanks'])) {
                            $params = [
                                "steam" => $steam32,
                                "name" => $this->General->checkName($steam64) ?? 'Unnamed',
                                "lastconnect" => time()
                            ];
                            $this->Db->query('LevelsRanks', $this->Db->db_data['LevelsRanks'][0]['USER_ID'], $this->Db->db_data['LevelsRanks'][0]['DB_num'], "INSERT INTO " . $this->Db->db_data['LevelsRanks'][0]['Table'] . "(`steam`, `name`, `lastconnect`) VALUES (:steam, :name, :lastconnect)", $params);
                        }

                        if (!empty($this->Db->db_data['lk'])) {
                            $param = ['auth' => '%' . $steam32 . '%'];
                            $player = $this->Db->query('lk', 0, 0, "SELECT * FROM lk WHERE auth LIKE :auth LIMIT 1", $param);
                            if (empty($player)) {
                                $params = [
                                    'auth' => $steam32,
                                    'name' => $this->General->checkName($steam64)
                                ];
                                $this->Db->query('lk', 0, 0, "INSERT INTO `lk` (`auth`, `name`, `cash`, `all_cash`) VALUES (:auth,:name,0,0)", $params);
                            }
                        }
                        $this->Db->query('Core', 0, 0, "INSERT INTO `lvl_web_profiles` (`auth`) VALUES (:auth)", ['auth' => $steam64]);
                    } catch (Exception $e) {}
                }

                $this->generateToken();

                header('Location: ' . explode('?', $_SERVER['REQUEST_URI'])[0]);
            }
            header('Location: ' . $this->General->arr_general['site']);
        }
    } catch (ErrorException $e) {
        header('Location: ' . $this->General->arr_general['site']);
    }
};
if (! empty($_GET["auth"]) && $_GET["auth"] == 'logout') {
    $this->cookieEnabled() && $this->delToken($_SESSION["steamid64"]);

    session_unset();
    session_destroy();

    setcookie('cookie_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    header('Location: ' . $this->General->arr_general['site']);
    if (! headers_sent()) { ?>
        <script type="text/javascript">
            window.location.href = "<?= $this->General->arr_general['site'] ?>";
        </script>
        <noscript>
            <meta http-equiv="refresh" content="0;url=<?= $this->General->arr_general['site'] ?>" />
        </noscript>
<?php exit;
    }
}