<?php

namespace app\ext;

class Auth
{
    public    $General;
    public    $Db;
    protected $token_length = 32;
    protected $cookie_days = 30;
    function __construct($General, $Db)
    {

        defined('IN_LR') != true && die();

        $this->General = $General;

        $this->Db = $Db;

        !isset($_SESSION["steamid"]) && $this->authByCookie();

        if (isset($_SESSION['steamid'])):
            $this->check_session_admin();
        endif;

        if (isset($_GET["auth"])) {
            if ($_GET["auth"] == 'login')
                require 'app/includes/auth/steam.php';
        }

        isset($_GET["auth"]) && $_GET["auth"] == 'logout' && require 'app/includes/auth/steam.php';
    }

    protected function cookieEnabled(): bool
    {
        return (bool) $this->General->arr_general['auth_cock'];
    }

    public function getUserToken(string $token)
    {
        if ($this->cookieEnabled())
            return $this->Db->query("Core", 0, 0, "SELECT * FROM `lr_web_cookie_tokens` WHERE `cookie_token` = :token", [
                "token" => $token
            ]);

        return [];
    }

    public function authByCookie()
    {
        $this->clearOldTokens();

        if (isset($_COOKIE["cookie_token"])) {
            if (!empty($user = $this->getUserToken(htmlentities($_COOKIE["cookie_token"])))) {
                if ($user["cookie_expire"] > time()) {
                    $steam32 = con_steam64to32($user["steam"]);

                    $_SESSION = [
                        "steamid"           => $user["steam"],
                        "steamid64"         => $user["steam"],
                        "steamid32"         => $steam32,
                        "steamid32_short"   => substr($steam32, 8),
                        "USER_AGENT"        => $_SERVER['HTTP_USER_AGENT'],
                        "REMOTE_ADDR"       => $this->General->get_client_ip_cdn()
                    ];

                    if (!empty($this->Db->db_data['lk'])) {
                        $param = ['auth' => '%' . $steam32 . '%'];
                        $player = $this->Db->query('lk', 0, 0, "SELECT * FROM lk WHERE auth LIKE :auth LIMIT 1", $param);
                        if (empty($player)) {
                            $params = [
                                'auth' => $steam32,
                                'name' => $this->General->checkName($user["steam"])
                            ];
                            $this->Db->query('lk', 0, 0, "INSERT INTO lk(auth, name, cash, all_cash) VALUES (:auth,:name,0,0)", $params);
                        }
                    }

                    $this->Db->query('Core', 0, 0, "INSERT INTO `lvl_web_profiles` (`auth`) VALUES (:auth)", ['auth' => $user["steam"]]);

                    header('Location: ' . $this->General->arr_general['site']);
                }
            }
        }
    }

    public function clearOldTokens()
    {
        $this->Db->query("Core", 0, 0, "DELETE FROM `lr_web_cookie_tokens` WHERE `cookie_expire` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $this->cookie_days . " DAY))");
    }

    public function generateToken()
    {
        if ($this->cookieEnabled()) {
            $token = bin2hex(random_bytes($this->token_length));
            $this->setUserToken($token, $_SESSION["steamid64"]);

            setcookie("cookie_token", $token, [
                'expires' => strtotime("+" . $this->cookie_days . " days"),
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }

    protected function setUserToken(string $token, int $steamid64)
    {
        if ($this->cookieEnabled()) {
            if (!empty($this->Db->query("Core", 0, 0, "SELECT * FROM `lr_web_cookie_tokens` WHERE `steam` = :steam", ["steam" => $steamid64]))) {
                $this->Db->query("Core", 0, 0, "UPDATE `lr_web_cookie_tokens` SET `cookie_token` = :token, `cookie_expire` = :expire WHERE `steam` = :steam", [
                    "steam" => $steamid64,
                    "token" => $token,
                    "expire" => strtotime("+" . $this->cookie_days . " days")
                ]);
            } else {
                $this->Db->query("Core", 0, 0, "INSERT INTO `lr_web_cookie_tokens`(`steam`, `cookie_token`, `cookie_expire`) VALUES (:steam, :token, :expire)", [
                    "steam" => $steamid64,
                    "token" => $token,
                    "expire" => strtotime("+" . $this->cookie_days . " days")
                ]);
            }
        }
    }

    public function delToken(string $steam)
    {
        $this->Db->query("Core", 0, 0, "DELETE FROM `lr_web_cookie_tokens` WHERE `steam` = :steam", [
            "steam" => (int) $steam
        ]);
    }
    
    public function check_session_admin()
    {
        $result = $this->Db->query('Core', 0, 0, "SELECT `steamid`, `group`, `flags`, `access` FROM `lvl_web_admins` WHERE `steamid`= :steamid LIMIT 1", [
            "steamid" => $_SESSION["steamid64"]
        ]);
        if (! empty($result)):
            $_SESSION['user_admin'] = 1;
            $_SESSION['user_group'] = $result['group'];
            $_SESSION['user_access'] = $result['access'];
            $_SESSION['user_flags'] = $result['flags'];
        else:
            unset($_SESSION['user_admin'], $_SESSION['user_group'], $_SESSION['user_access'], $_SESSION['user_flags']);
        endif;
    }
}
