<?php

function file_get_contents_fix($file)
{
    return file_get_contents($file, false, stream_context_create(array("ssl" => array("verify_peer" => false, "verify_peer_name" => false))));
}

function header_fix($url)
{
    if (!headers_sent()) {
        echo '<script type="text/javascript">window.location.href="' . $url . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . $url . '" /></noscript>';
    } else {
        header("Location: " . $url);
    }
}

function refresh()
{
    return header("Refresh: 0");
}

function empty_check_out($i, $d = 0, $a = 0)
{
    $x = empty($d) ? $i : $d;
    return empty($i) ? $a : $x;
}

function var_export_min($var, $return = true)
{
    if (is_array($var)) {
        $toImplode = array();
        foreach ($var as $key => $value) {
            $toImplode[] = var_export($key, true) . '=>' . var_export_min($value, true);
        }
        $code = 'array(' . implode(',', $toImplode) . ')';
        if ($return)
            return $code;
        else
            echo $code;
    } else {
        return var_export($var, $return);
    }
}

function var_export_opt($var, $return = true)
{
    return var_export($var, $return);
}

function get_section($section, $default)
{
    return isset($_GET[$section]) ? action_text_clear($_GET[$section]) : $default;
}

function get_arr_size($arr)
{
    return is_array($arr) ? sizeof($arr) : 0;
}

function get_url($type)
{
    $url_clear = action_text_clear($_SERVER["REQUEST_URI"]);
    switch ($type) {
        case 1:
            return '//' . $_SERVER["SERVER_NAME"] . $url_clear;
            break;
        case 2:
            return '//' . $_SERVER['HTTP_HOST'] . explode('?', $url_clear, 2)[0];
            break;
    }
}

function set_url_section($url, $command, $change)
{
    $query = $_GET;
    $query[$command] = $change;
    $finally = urldecode(http_build_query($query));
    return $url . '?' . $finally;
}

function get_iframe($code, $description, $die = true)
{
    $_SESSION['iframe_code'] = $code;
    $_SESSION['iframe_description'] = $description;
    $_SESSION['iframe'] = $die;
    $_SESSION['metatag_index'] = true;
    $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
    header($protocol . ' ' . $code . ' ' . get_status_text($code));
}

function get_status_text($code)
{
    static $texts = [
        200 => 'OK',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];

    return $texts[$code] ?? 'Unknown Status';
}

function action_text_trim($text, $max = 18)
{
    for ($i = 0, $symbols = 0, $iLen = strlen($text); $i < $iLen; $i += GetCharBytes($text[$i]), $symbols++) {
        if ($symbols > $max) {
            return substr($text, 0, $i) . '...';
        }
    }

    return $text;
}

function GetCharBytes($symbol)
{
    $charnum = ord($symbol);

    if ($charnum & (1 << 7)) {
        if ($charnum & (1 << 5)) {
            if ($charnum & (1 << 4)) {
                return 4;
            }

            return 3;
        }

        return 2;
    }

    return 1;
}

function action_text_clear($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
}

function action_text_clear_characters($text)
{
    return preg_replace('/[^А-Яа-яA-Za-z0-9]/', '', $text);
}

function action_int_percent_of_all($int = 0, $all = 0)
{
    if ($int == 0 || $all == 0) {
        $res = 0;
    } else {
        $res = floor(100 * $int / $all);
    }
    return is_nan($res) ? 0 : $res;
}

function action_text_clear_before_slash($text)
{
    return array_reverse(explode("/", $text))[0];
}

function check_duplicate_files($file, $file_2)
{
    return (file_exists($file) && file_exists($file_2) && filesize($file) === filesize($file_2)) ? true : false;
}

function con_steam32to64($id)
{
    if ($id[0] == 'S') {
        $arr = explode(":", $id);
        if (!empty($arr[2])):
            return bcadd(bcmul((int) $arr[2], 2), bcadd((int) $arr[1], '76561197960265728'), 0);
        endif;
    } else {
        return is_numeric($id) ? $id : false;
    }
}

function con_steam32to3_int($steamid32)
{
    if (preg_match('/^STEAM_/', $steamid32)) {
        $split = explode(':', $steamid32);
        return $split[2] << 1 | $split[1];
    }

    return $steamid32;
}

function con_steam64to3_int($steamid64)
{
    if (preg_match('/^765/', $steamid64) && strlen($steamid64) > 15) {
        return bcsub($steamid64, '76561197960265728');
    } else {
        return $steamid64;
    }
}

function con_steam3to64_int($steamid3)
{
    if (is_numeric($steamid3)):
        $a = $steamid3 % 2;
        $b = intval($steamid3 / 2);
        $c = con_steam32to64('STEAM_1:' . $a . ':' . $b);
        return $c;
    else:
        return '';
    endif;
}

function con_steam3to32_int($steamid3, $else = 0)
{
    if (is_numeric($steamid3)):
        $a = $steamid3 % 2;
        $b = intval($steamid3 / 2);
        return 'STEAM_1:' . $a . ':' . $b;
    elseif ($else === 1):
        return $steamid3[0] == 'S' ? con_steam32to64($steamid3) : $steamid3;
    else:
        return $steamid3;
    endif;
}

function con_steam64to32($steamid64)
{
    if (preg_match('/^(7656119)([0-9]{10})$/', $steamid64, $match)) {
        $const1 = 7960265728;
        $steam32 = '';
        if ($const1 <= $match[2]) {
            $a = ($match[2] - $const1) % 2;
            $b = ($match[2] - $const1 - $a) / 2;
            $steam32 = 'STEAM_1:' . $a . ':' . $b;
        }
    }
    if (is_numeric($steamid64) && empty($steam32)) {
        $z = bcdiv(bcsub($steamid64, '76561197960265728'), '2');
        $y = bcmod($steamid64, '2');
        return 'STEAM_1:' . $y . ':' . floor($z);
    } elseif (!empty($steam32)) {
        return $steam32;
    } elseif (!is_numeric($steamid64)) {
        return false;
    } else {
        return false;
    }
}

function LangValReplace($phares, $values = [])
{
    $replace = $phares;
    for ($i = 0; $i < sizeof($values); $i++) {
        foreach ($values as $key => $val) {
            $replace = str_replace('%' . $key . '%', $val, $replace);
        }
    }
    return $replace;
}

function action_array_keep_keys($array, $keys)
{
    $result = [];

    $keys = array_flip($keys);

    for ($i = 0, $c = sizeof($array); $i < $c; $i++) {
        $result[] = array_intersect_key($array[$i], $keys);
    }
    return $result;
}

function substr_unicode($str, $s, $l = null)
{
    return join("", array_slice(preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $s, $l));
}

function dirToArray($dir)
{

    $result = array();

    $cdir = scandir($dir);
    foreach ($cdir as $key => $value) {
        if (!in_array($value, array(".", ".."))) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
            } else {
                $result[] = $value;
            }
        }
    }
    return $result;
}

function con_steam64($steam64)
{
    $options = require __DIR__ . '../../../storage/cache/sessions/options.php';
    switch (true):
        case (preg_match('/^(7656119)([0-9]{10})/', $steam64)):
            return $steam64;
        case (preg_match('/^STEAM_[01]:[01]:[0-9]{2,12}$/', $steam64)):
            return con_steam32to64($steam64);
        case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/', $steam64)):
            $search_id = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/", '$3', $steam64), "/");
            $getsearch = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key={$options['web_key']}&vanityurl={$search_id}"), true)['response']['steamid'];
            return $getsearch;
        case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/', $steam64)):
            $search_steam = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/", '$3', $steam64), "/");
            return $search_steam;
        case (preg_match('/^\[U:(.*)\:(.*)\]/', $steam64)):
            return con_steam3to64_int(str_replace(array('[U:1:', '[U:0:', ']'), '', $steam64));
        default:
            return false;
    endswitch;
}

function con_steam32($steam32)
{
    $options = require __DIR__ . '../../../storage/cache/sessions/options.php';
    switch (true):
        case (preg_match('/^STEAM_[01]:[01]:[0-9]{2,12}$/', $steam32)):
            return $steam32;
        case (preg_match('/^(7656119)([0-9]{10})/', $steam32)):
            return con_steam64to32($steam32);
        case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/', $steam32)):
            $search_id = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/", '$3', $steam32), "/");
            $getsearch = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key={$options['web_key']}&vanityurl={$search_id}"), true)['response']['steamid'];
            return con_steam64to32($getsearch);
        case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/', $steam32)):
            $search_steam = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/", '$3', $steam32), "/");
            return con_steam64to32($search_steam);
        case (preg_match('/^\[U:(.*)\:(.*)\]/', $steam32)):
            return con_steam3to32_int(str_replace(array('[U:1:', '[U:0:', ']'), '', $steam32));
        default:
            return false;
    endswitch;
}

function con_steam3($steam3)
{
    $options = require __DIR__ . '../../../storage/cache/sessions/options.php';
    switch (true):
        case (preg_match('/^\[U:(.*)\:(.*)\]/', $steam3)):
            return str_replace(array('[U:1:', '[U:0:', ']'), '', $steam3);
        case (preg_match('/^STEAM_[01]:[01]:[0-9]{2,12}$/', $steam3)):
            return con_steam32to3_int($steam3);
        case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/', $steam3)):
            $search_id = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(id)\/(\S{1,})/", '$3', $steam3), "/");
            $getsearch = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key={$options['web_key']}&vanityurl={$search_id}"), true)['response']['steamid'];
            return con_steam64to3_int($getsearch);
        case (preg_match('/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/', $steam3)):
            $search_steam = rtrim(preg_replace("/^\w{1,}:\/\/(steamcommunity.com)\/(profiles)\/(7656119[0-9]{10})(\/|)/", '$3', $steam3), "/");
            return con_steam64to3_int($search_steam);
        case (preg_match('/^(7656119)([0-9]{10})/', $steam3)):
            return con_steam64to3_int($steam3);
        default:
            return false;
    endswitch;
}

function Pagination($pages, $page, $type = 0)
{
    if ($pages <= 1)
        return '';
    $html = '<div class="pagination">';
    $startPage = 1;
    if ($pages > 5) {
        if ($page <= 3) {
            $startPage = 1;
        } elseif ($page >= $pages - 2) {
            $startPage = $pages - 4;
        } else {
            $startPage = $page - 2;
        }
    }
    if ($startPage > 1) {
        $url = $type == 0 ? 'href="' . set_url_section(get_url(2), 'num', 1) . '"' : 'data-page="1"';
        $html .= <<<HTML
            <a class="button_pagination" {$url}><svg><use href="/resources/img/sprite.svg#double-chevrone-left"></use></svg></a>
        HTML;
    }
    if ($page > 1) {
        $url = $type == 0 ? 'href="' . set_url_section(get_url(2), 'num', ($page - 1)) . '"' : 'data-page="' . ($page - 1) . '"';
        $html .= <<<HTML
            <a class="button_pagination" {$url}><svg><use href="/resources/img/sprite.svg#single-chevrone-left"></use></svg></a>
        HTML;
    }
    for ($i = $startPage; $i < $startPage + 5 && $i <= $pages; $i++) {
        $active = ($i == $page) ? ' active' : '';
        $url = $type == 0 ? 'href="' . set_url_section(get_url(2), 'num', $i) . '"' : 'data-page="' . $i . '"';
        $html .= <<<HTML
            <a class="button_pagination{$active}" {$url}>{$i}</a>
        HTML;
    }
    if ($page < $pages) {
        $url = $type == 0 ? 'href="' . set_url_section(get_url(2), 'num', ($page + 1)) . '"' : 'data-page="' . ($page + 1) . '"';
        $html .= <<<HTML
            <a class="button_pagination" {$url}><svg><use href="/resources/img/sprite.svg#single-chevrone-right"></use></svg></a>
        HTML;
    }
    if ($startPage + 4 < $pages) {
        $url = $type == 0 ? 'href="' . set_url_section(get_url(2), 'num', $pages) . '"' : 'data-page="' . $pages . '"';
        $html .= <<<HTML
            <a class="button_pagination" {$url}><svg><use href="/resources/img/sprite.svg#double-chevrone-right"></use></svg></a>
        HTML;
    }

    $html .= '</div>';
    return $html;
}

function getRankImage($rank, $value, $rank_pack = 'default', $class = '')
{
    $options = require __DIR__ . '../../../storage/cache/sessions/options.php';
    $image_format = $options['rank_pack'] ?? 'png';
    if (isset($options['premier_ranks']) && $options['premier_ranks']) {
        if ($value <= 0) {
            $tier = 0;
            $first_number = '— — —';
            $second_number = '';
        } else {
            $main = floor($value / 1000);
            $decimal = $value % 1000;
            $first_number = number_format($main, 0, '', ',') . ',';
            $second_number = str_pad($decimal, 3, '0', STR_PAD_LEFT);
            if ($value < 5000) {
                $tier = 0;
            } elseif ($value < 10000) {
                $tier = 1;
            } elseif ($value < 15000) {
                $tier = 2;
            } elseif ($value < 20000) {
                $tier = 3;
            } elseif ($value < 25000) {
                $tier = 4;
            } elseif ($value < 30000) {
                $tier = 5;
            } else {
                $tier = 6;
            }
            if ($value > 999999) {
                $overlevel = 'over-1m';
            } elseif ($value > 99999) {
                $overlevel = 'over-100k';
            } else {
                $overlevel = '';
            }
        }
        return <<<HTML
            <div class="rank tier-{$tier}">
                <div class="rank__double-lines">
                    <div class="rank__double-line"></div>
                    <div class="rank__double-line"></div>
                </div>
                <div class="rank__ghost-lines">
                    <div class="rank__ghost-line-1"></div>
                    <div class="rank__ghost-line-2"></div>
                    <div class="rank__ghost-line-3"></div>
                    <div class="rank__ghost-line-4"></div>
                </div>
                <div class="rank__text {$overlevel}">
                    $first_number
                    <small>$second_number</small>
                </div>
            </div>
        HTML;
    } else {
        return <<<HTML
            <img class="custom-rank {$class}" src="/storage/cache/img/ranks/{$rank_pack}/{$rank}.{$image_format}" alt="" title="">
        HTML;
    }
}