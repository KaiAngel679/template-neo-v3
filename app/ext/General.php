<?php

namespace app\ext;

class General
{
    public $arr_general = [];
    public $notes = [];
    public $server_list = [];
    public $server_list_count = 0;
    protected static $json_buff = [];

    public $Host;
    public $Version;

    public $Db;

    public $mods;

    public $currency;
    public $currencies;
    public $background;
    public $gradient;
    
    function __construct($Db)
    {

        defined('IN_LR') != true && die();

        $this->Db = $Db;

        $this->arr_general = $this->get_default_options();

        $this->server_list = $this->get_server_list();

        $this->server_list_count = get_arr_size($this->server_list);

        $this->mods = $this->getMods();

        $this->currencies = $this->getCurrencies();

        $this->currency = $this->currencies[$this->arr_general['currency']] ?? '₽';

        $this->background = $this->getBackgroundSettings();

        $this->gradient = $this->getBackgroundGradient();
        $this->get_default_url_section('language', $this->arr_general['language'], $this->get_arr_languages());
    }

    public function get_arr_languages()
    {
        return file_exists(SESSIONS . 'languages.json') ? json_decode(file_get_contents(SESSIONS . 'languages.json'), true) : ['EN', 'RU'];
    }

    public function get_default_url_section($section, $default, $arr_true)
    {
        !isset($_SESSION[$section]) && $_SESSION[$section] = $default;
        isset($_GET[$section]) && in_array($_GET[$section], $arr_true) && $_SESSION[$section] = $_GET[$section];
    }

    public function getAvatar($profile, $type)
    {
        if ($type == 1) {
            $avatarType = "avatar";
        } elseif ($type == 2) {
            $avatarType = "slim";
        } elseif ($type == 3) {
            $avatarType = "animated";
        } else {
            $avatarType = "avatar";
        }
        $profile = con_steam64($profile);
        $url = sprintf("%simg/avatars/%s.json", CACHE, $profile);
        if (file_exists($url)) {
            $avatar = json_decode(file_get_contents($url), true);
            self::$json_buff[$profile] = $avatar;
            if ($type == 3 && empty($avatar['animated'])) {
                return htmlentities($avatar['avatar']);
            }
            return htmlentities($avatar[$avatarType]);
        }
        return $this->arr_general['site'] . 'storage/cache/img/avatars/1_avatar.jpg';
    }

    public function getFrame($profile)
    {
        return $this->arr_general['site'] . 'storage/cache/img/avatars/1_frame.png';
    }

    public function getBackground($profile)
    {
        $profile = con_steam64($profile);
        $url = sprintf("%simg/avatars/%s.json", CACHE, $profile);
        if (file_exists($url)) {
            $json = json_decode(file_get_contents($url), true);
            self::$json_buff[$profile] = $json;
            if ($json['background']) {
                $return = htmlentities($json['background']);
            }
        }
        if (empty($json['background'])) {
            $return = $this->arr_general['site'] . 'storage/cache/img/avatars/1_background.webm';
        }
        if (preg_match('/\.webm$/', $return)) {
            $background_html = <<<HTML
                            <video preload="auto" class="back_video lazy" playsinline="" muted="" loop="">
                                <source data-src="{$return}" type="video/webm">
                            </video>
                        HTML;
        } else {
            $background_html = <<<HTML
                            <img class="lazy" data-src="{$return}" alt="">
                        HTML;
        }
        return $background_html;
    }

    public function checkAvatar($profile)
    {
        $profile = con_steam64($profile);
        $url = CACHE . 'img/avatars/' . $profile . '.json';
        if (file_exists($url)) {
            $cacheTime = $this->arr_general['avatars_cache_time'];
            if (time() >= filemtime($url) + $cacheTime) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }

    public function checkName($profile)
    {
        $profile = con_steam64($profile);
        !isset(self::$json_buff[$profile]) && $this->getAvatar($profile, 1);
        return action_text_clear(htmlentities(self::$json_buff[$profile]["name"])) ?? "Unnamed";
    }

    public function checkFaceit($profile)
    {
        $profile = con_steam64($profile);
        $url = CACHE . '/faceit/' . $profile . '.json';
        if (file_exists($url)) {
            $cacheTime = $this->arr_general['faceit_cache_time'];
            if (time() >= filemtime($url) + $cacheTime) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }

    public function getFaceit($profile, $type = 'full')
    {
        $profile = con_steam64($profile);
        $url = sprintf("%sfaceit/%s.json", CACHE, $profile);
        if (file_exists($url)) {
            $faceit = json_decode(file_get_contents($url), true);
            switch ($type) {
                case 'full':
                    return $faceit;
                case 'level':
                    return $faceit['faceit_level'] ?? 0;
                case 'level_img':
                    return $faceit['faceit_level_img'] ?? '/storage/cache/img/faceit/none.svg';
                case 'elo':
                    return $faceit['elo'] ?? '0 ELO';
                case 'url':
                    return $faceit['url'] ?? null;
                case 'nickname':
                    return $faceit['nickname'] ?? 'No Account';
            }
            return $faceit;
        }
        switch ($type) {
            case 'full':
                return [];
            case 'level_img':
                return '/storage/cache/img/faceit/none.svg';
            case 'elo':
                return '0 ELO';
            case 'url':
                return null;
            case 'nickname':
                return 'No Account';
        }
        return [];
    }

    public function checkOnline($profile)
    {
        $profile = con_steam64($profile);
        if (empty($this->Db->query('Core', 0, 0, "SELECT * FROM `lr_web_online` WHERE `user` = :steam", ['steam' => $profile]))):
            return 0;
        else:
            return 1;
        endif;
    }

    public function get_default_options()
    {
        $options = file_exists(SESSIONS . '/options.php') ? require SESSIONS . '/options.php' : null;
        return !isset($options['full_name']) ? exit(require 'app/page/custom/install/index.php') : $options;
    }

    public function get_neo_options()
    {
        return file_exists(MODULESCACHE . 'template_neo/settings_neo.php') ? require MODULESCACHE . 'template_neo/settings_neo.php' : null;
    }

    public function get_neo_menu()
    {
        $file = MODULESCACHE . 'template_neo/menu.json';
        return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
    }

    public function get_neo_userbar()
    {
        $file = MODULESCACHE . 'template_neo/userbar.json';
        return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
    }

    public function get_neo_footer()
    {
        $file = MODULESCACHE . 'template_neo/footer.json';
        return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
    }

    public function get_server_list()
    {
        return $this->Db->queryAll('Core', 0, 0, 'SELECT * FROM `lvl_web_servers` ORDER BY `sort`');
    }

    function get_icon($group, $name, $category = null)
    {
        return print $category == null ? $name : '<img src="' . $this->arr_general['site'] . CACHE . 'img/icons/' . $group . '/' . $category . '/' . $name . '.svg" class=svg>';
    }

    function get_js_relevance_avatar($id)
    {
        $steam = is_numeric($id) ? $id : con_steam64($id);
        $check = $this->checkAvatar($steam);
        echo "<script>CheckAvatar = {$check}; if (CheckAvatar == 1) { avatar.push(\"{$steam}\"); }</script>";
    }

    function get_js_relevance_faceit($id)
    {
        $steam = is_numeric($id) ? $id : con_steam64($id);
        $check = $this->checkFaceit($steam);
        echo "<script>CheckFaceit = {$check}; if (CheckFaceit == 1) { faceit.push(\"{$steam}\"); }</script>";
    }

    public function get_client_ip_cdn()
    {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ip_headers as $header) {
            if (isset($_SERVER[$header])) {
                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    $ips = explode(',', $_SERVER[$header]);
                    return trim($ips[0]);
                }
                return $_SERVER[$header];
            }
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    public function online_stats()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (isset($_SESSION['steamid64']))
            $User = $_SESSION['steamid64'];
        else
            $User = preg_match('/Bot/i', $agent) ? 'Bot' : 'guest';

        $client_ip = $this->get_client_ip_cdn();

        $param['ip'] = $client_ip;
        $Online = $this->Db->queryOneColumn('Core', 0, 0, "SELECT `user` FROM `lr_web_online` WHERE `ip` = :ip", $param);

        if (empty($Online)) {
            $params = [
                'user' => $User,
                'ip' => $client_ip,
                'user_agent' => $agent
            ];
            $this->Db->query('Core', 0, 0, "INSERT INTO `lr_web_online`(`id`, `user`, `ip`, `time`, `user_agent`) VALUES (NULL, :user, :ip, NOW(), :user_agent)", $params);

            $_Param['date'] = date('m.Y');
            $_Attendance_ID = $this->Db->queryOneColumn('Core', 0, 0, 'SELECT `id` FROM `lr_web_attendance` WHERE `date` = :date', $_Param);

            if ($_Attendance_ID) {
                $_ParamU['id'] = $_Attendance_ID[0];
                $this->Db->query('Core', 0, 0, "UPDATE `lr_web_attendance` SET `visits` = `visits` + 1 WHERE `id` = :id", $_ParamU);
            } else {
                $this->Db->query('Core', 0, 0, "INSERT INTO `lr_web_attendance`(`id`, `date`, `visits`) VALUES (NULL, :date, 1)", $_Param);
            }
        } else {
            if ($Online != $User) {
                $params = [
                    'user' => $User,
                    'ip' => $client_ip,
                    'user_agent' => $agent
                ];
                $this->Db->query('Core', 0, 0, 'UPDATE `lr_web_online` SET `time` = NOW(), `user` = :user, `user_agent` = :user_agent WHERE `ip` = :ip', $params);
            } else {
                $this->Db->query('Core', 0, 0, "UPDATE `lr_web_online` SET `time` = NOW(), `user_agent` = :user_agent WHERE  `ip` = :ip", $param);
            }
        }
        $this->Db->query('Core', 0, 0, "DELETE FROM `lr_web_online` WHERE `time` < SUBTIME(NOW(), '0 0:05:0')");
    }

    public function check_vpn(string $ip)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://blackbox.ipinfo.app/lookup/$ip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output == "Y" ? true : false;
    }

    public function getOnline()
    {
        return $this->Db->query('Core', 0, 0, "SELECT * FROM `lr_web_online`");
    }

    public function getOnlineCount()
    {
        return $this->Db->query('Core', 0, 0, "SELECT COUNT(*) as `count` FROM `lr_web_online` LIMIT 1")['count'];
    }

    public function getMods()
    {
        return file_exists(SESSIONS . 'mods.php') ? require SESSIONS . 'mods.php' : [];
    }

    public function getCurrencies()
    {
        return file_exists(SESSIONS . 'currencies.php') ? require SESSIONS . 'currencies.php' : [];
    }

    public function getOnlineSiteAndMods()
    {
        $cache_path = MODULES . 'module_block_main_servers/temp/cache.json';
        $server_cache = file_exists($cache_path) ? json_decode(file_get_contents($cache_path, true), true) : ['servers' => []];
        $data = [];
        $data['site'] = $this->getOnlineCount() ?? 0;
        $data['servers'] = 0;
        $data['server_mods'] = [];
        foreach ($server_cache['servers'] as $server) {
            $players = isset($server['Players']) ? (int)$server['Players'] : 0;
            $data['servers'] += $players;
            $gm = $server['GameMode'] ?? 'PUBLIC';
            if (!isset($data['server_mods'][$gm])) {
                $data['server_mods'][$gm] = 0;
            }
            $data['server_mods'][$gm] += $players;
        }
        return $data;
    }

    public function getBalance($steam)
    {
        $steam32 = con_steam32($steam);
        if (isset($steam32)) {
            return $this->Db->query('lk', 0, 0, "SELECT `cash`, `all_cash` FROM `lk` WHERE `auth` = :auth LIMIT 1", ['auth' => $steam32]);
        } else {
            return ['cash' => 0, 'all_cash' => 0];
        }
    }

    public function validatePOST(array $requiredFields = []): bool
    {
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                return false;
            }
        }
        return true;
    }

    public function getBackgroundSettings()
    {
        $jsonFile = MODULESCACHE . 'template_neo/background.json';
        if (!file_exists($jsonFile)) {
            return [];
        }
        return json_decode(file_get_contents($jsonFile), true);
    }

    public function getBackgroundGradient()
    {
        $background = $this->getBackgroundSettings();
        switch ($background['type']) {
            case 3:
                return $background['gradients'] ?? '1';
            case 5:
                return $background['gradients-stars'] ?? '1';
            default:
                return 1;
        }
    }

    public function checkAdminServerAccess($steamid)
    {
        if (isset($steamid)) {
            if (!empty($this->Db->db_data['IksAdmin'])) {
                $admin = $this->Db->query('IksAdmin', 0, 0, 'SELECT `sid` as steamid FROM `iks_admins` WHERE `sid` = :steamid AND (`iks_admins`.`end` > UNIX_TIMESTAMP() OR `iks_admins`.`end` = 0)', [
                    'steamid' => $steamid
                ]);
            } elseif (!empty($this->Db->db_data['IksAdminNew'])) {
                $admin = $this->Db->query('IksAdminNew', 0, 0, 'SELECT `steam_id` as steamid FROM `iks_admins` WHERE `steam_id` = :steamid AND `iks_admins`.`is_disabled` = 0 AND (`iks_admins`.`end_at` > UNIX_TIMESTAMP() OR `iks_admins`.`end_at` IS NULL)', [
                    'steamid' => $steamid
                ]);
            } elseif (!empty($this->Db->db_data['AdminSystem'])) {
                $admin = $this->Db->query('AdminSystem', 0, 0, 'SELECT `as_admins`.`steamid` as steamid FROM `as_admins` JOIN `as_admins_servers` ON `as_admins`.`id` = `as_admins_servers`.`admin_id` WHERE `steamid` = :steamid AND (`as_admins_servers`.`expires` > UNIX_TIMESTAMP() OR `as_admins_servers`.`expires` = 0)', [
                    'steamid' => $steamid
                ]);
            }
            if (!empty($admin['steamid'])) {
                return true;
            }
        }
        return false;
    }
}
