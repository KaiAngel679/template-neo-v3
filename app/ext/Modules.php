<?php

namespace app\ext;

class Modules
{
    public $array_modules = [];
    public $array_modules_count = 0;
    public $array_templates_count = 0;
    public $arr_module_init = [];
    public $arr_module_init_page_count = 0;
    public $arr_templates = [];
    public $arr_user_info = [];
    public $scan_modules = [];
    public $scan_templates = [];
    public $css_library = [];
    public $js_library = [];
    public $page_title = '';
    public $page_description = '';
    public $page_canonical = '';
    public $page_image = '';
    public $General;
    public $Translate;
    public $Notifications;
    public $Router;
    public $route;

    function __construct($General, $Translate, $Notifications, $Router)
    {
        defined('IN_LR') != true && die();

        $this->General = $General;

        $this->Translate = $Translate;

        $this->Notifications = $Notifications;

        $this->Router = $Router;

        $this->array_modules = $this->get_arr_modules();

        $this->array_modules_count = sizeof($this->array_modules);

        $this->arr_module_init = $this->get_module_init();

        $this->arr_module_init_page_count = sizeof($this->arr_module_init['page']);

        $this->arr_templates = $this->get_templates_init();

        $this->scan_templates = array_diff(scandir(TEMPLATES, 1), array('..', '.', 'disabled'));

        $this->array_templates_count = sizeof($this->scan_templates);

        $this->AddRoutes();

        $match = $Router->match()['target'] ?? $Router->SearchRoute();

        $this->route = (strpos($match, "?language=") !== false) ? "home" : $match;

        $basename = parse_url($General->arr_general['site'], PHP_URL_PATH);

        (empty($basename) && empty($this->route) || $_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == $basename) && $this->route = 'home';

        isset($_SESSION['user_admin']) && $this->check_actual_modules_list();

        $this->check_generated_js();

        $this->check_generated_style();

        $this->check_actual_templates();

        !empty($checkroute) && empty($this->arr_module_init['page'][$this->route]) && get_iframe(404, 'Данная страница не существует') && die();

        $_SESSION['page_redirect'] = $this->route;
    }

    public function get_templates_init()
    {
        if (!file_exists(SESSIONS . 'templates_cache.php')) {
            $scan = array_diff(scandir(TEMPLATES, 1), array('..', '.', 'disabled'));
            if (sizeof($scan) != 0) {
                foreach ($scan as $key => $val) {
                    $result[$scan[$key]] = json_decode(file_get_contents(TEMPLATES . $scan[$key] . '/description.json'), true);
                }
            }

            file_put_contents(SESSIONS . 'templates_cache.php', '<?php return ' . var_export_min($result) . ";");

            return $result;
        }
        return require SESSIONS . 'templates_cache.php';
    }

    public function AddRoutes()
    {
        foreach ($this->arr_module_init['page'] as $key => $val)
            $this->Router->map('GET', '/' . $key . '/', $key, $key);

        return;
    }

    public function check_actual_templates()
    {
        $keys = array_keys($this->arr_templates);

        $zalupka = array_values(array_diff($keys, $this->scan_templates));
        $zalupka2 = array_values(array_diff($this->scan_templates, $keys));

        if (!empty($zalupka) || !empty($zalupka2)) {
            foreach (array_keys($this->arr_templates) as $val) {
                $search = array_search($val, $this->scan_templates);
                if ($this->scan_templates[$search] == $val) {
                    $templates[] = $val;
                }
            }

            foreach ($this->scan_templates as $val) {
                $search = array_search($val, array_keys($this->arr_templates));
                if (array_keys($this->arr_templates)[$search] != $val) {
                    $templates[] = $val;
                }
            }

            for ($i = 0; $i < sizeof($templates); $i++)
                $result[$templates[$i]] = json_decode(file_get_contents(TEMPLATES . $templates[$i] . '/description.json'), true);

            file_put_contents(SESSIONS . 'templates_cache.php', '<?php return ' . var_export_min($result) . ";");

            unlink(SESSIONS . 'templates_modules_cache.php');

            header("Refresh:3");
        }
    }

    public function check_actual_modules_list()
    {
        $scan_modules = array_diff(scandir(MODULES, 1), array('..', '.', 'disabled'));

        $keys = array_keys($this->array_modules);
        $zalupka = array_values(array_diff($scan_modules, $keys));
        $zalupka2 = array_values(array_diff($keys, $scan_modules));

        if (!empty($zalupka) || !empty($zalupka2)):
            foreach (array_keys($this->array_modules) as $val) {
                $search = array_search($val, $scan_modules);
                if ($scan_modules[$search] == $val) {
                    $modules[] = $val;
                }
            }

            foreach ($scan_modules as $val) {
                $search = array_search($val, array_keys($this->array_modules));
                if (array_keys($this->array_modules)[$search] != $val) {
                    $modules[] = $val;
                }
            }

            for ($i = 0, $c = sizeof($modules); $i < $c; $i++):

                $modules_desc[$modules[$i]] = json_decode(file_get_contents(MODULES . $modules[$i] . '/description.json'), true);

                if ($modules_desc[$modules[$i]]['setting']['status'] == 1 && $modules_desc[$modules[$i]]['page'] != 'all'):
                    if (!empty($modules_desc[$modules[$i]]['setting']['interface']) && $modules_desc[$modules[$i]]['setting']['interface'] == 1):
                        $result['page'][$modules_desc[$modules[$i]]['page']]['interface'][empty($modules_desc[$modules[$i]]['setting']['interface_adjacent']) ? 'afternavbar' : $modules_desc[$modules[$i]]['setting']['interface_adjacent']][] = $modules[$i];
                    endif;
                    if (!empty($modules_desc[$modules[$i]]['setting']['interface_always']) && $modules_desc[$modules[$i]]['setting']['interface_always'] == 1):
                        $result['interface_always'][empty($modules_desc[$modules[$i]]['setting']['interface_always_adjacent']) ? 'afternavbar' : $modules_desc[$modules[$i]]['setting']['interface_always_adjacent']][] = ['name' => $modules[$i]];
                    endif;
                    !empty($modules_desc[$modules[$i]]['setting']['data']) && $modules_desc[$modules[$i]]['setting']['data'] == 1 && $result['page'][$modules_desc[$modules[$i]]['page']]['data'][] = $modules[$i];
                    !empty($modules_desc[$modules[$i]]['setting']['data_always']) && $modules_desc[$modules[$i]]['setting']['data_always'] == 1 && $result['data_always'][] = $modules[$i];
                    !empty($modules_desc[$modules[$i]]['setting']['js_always']) && $modules_desc[$modules[$i]]['setting']['js_always'] == 1 && $result['js_always'][] = $modules[$i];
                    !empty($modules_desc[$modules[$i]]['setting']['css_always']) && $modules_desc[$modules[$i]]['setting']['css_always'] == 1 && $result['css_always'][] = $modules[$i];
                    !empty($modules_desc[$modules[$i]]['setting']['js']) && $modules_desc[$modules[$i]]['setting']['js'] == 1 && $result['page'][$modules_desc[$modules[$i]]['page']]['js'][] = ['name' => $modules[$i], 'type' => $modules_desc[$modules[$i]]['setting']['type']];
                    !empty($modules_desc[$modules[$i]]['setting']['css']) && $modules_desc[$modules[$i]]['setting']['css'] == 1 && $result['page'][$modules_desc[$modules[$i]]['page']]['css'][] = ['name' => $modules[$i], 'type' => $modules_desc[$modules[$i]]['setting']['type']];
                    !empty($modules_desc[$modules[$i]]['sidebar']) && $result['sidebar'][] = $modules[$i];
                endif;

            endfor;

            if (file_exists(SESSIONS . 'modules_initialization.php')) {
                $cache = require SESSIONS . 'modules_initialization.php';

                if (!function_exists("array_key_last")) {
                    function array_key_last($array)
                    {
                        if (!is_array($array) || empty($array)) {
                            return NULL;
                        }
                        return array_keys($array)[count($array) - 1];
                    }
                }
                foreach ($result['page']['home']['interface']['afternavbar'] as $key => $val) {
                    $search = array_search($val, $cache['page']['home']['interface']['afternavbar']);
                    if ($cache['page']['home']['interface']['afternavbar'][$search] == $val)
                        $restwo[$search] = $val;
                    else
                        $restwo[array_key_last($result['page']['home']['interface']['afternavbar']) + sizeof($restwo) ?? 1] = $val;
                }

                ksort($restwo);

                $result['page']['home']['interface']['afternavbar'] = array_values($restwo);
            }

            file_put_contents(SESSIONS . 'modules_initialization.php', '<?php return ' . var_export_min($result) . ";\n");

            file_put_contents(SESSIONS . 'modules_cache.php', '<?php return ' . var_export_min($modules_desc) . ";");

            unlink(SESSIONS . 'translator.json');

            unlink(ASSETS_JS . 'translations.js');

            header("Refresh:3");
        endif;
    }

    public function get_module_init()
    {

        if (!file_exists(SESSIONS . 'modules_initialization.php')):

            $result = [];

            for ($i = 0; $i < $this->array_modules_count; $i++):

                $module = array_keys($this->array_modules)[$i];
                if (
                    $this->array_modules[$module]['setting']['status'] == 1
                    && $this->array_modules[$module]['page'] != 'all'
                ):
                    if (!empty($this->array_modules[$module]['setting']['interface']) && $this->array_modules[$module]['setting']['interface'] == 1):
                        $result['page'][$this->array_modules[$module]['page']]['interface'][empty($this->array_modules[$module]['setting']['interface_adjacent']) ? 'afternavbar' : $this->array_modules[$module]['setting']['interface_adjacent']][] = $module;
                    endif;
                    if (!empty($this->array_modules[$module]['setting']['interface_always']) && $this->array_modules[$module]['setting']['interface_always'] == 1):
                        $result['interface_always'][empty($this->array_modules[$module]['setting']['interface_always_adjacent']) ? 'afternavbar' : $this->array_modules[$module]['setting']['interface_always_adjacent']][] = ['name' => $module];
                    endif;
                    !empty($this->array_modules[$module]['setting']['data']) && $this->array_modules[$module]['setting']['data'] == 1 && $result['page'][$this->array_modules[$module]['page']]['data'][] = $module;
                    !empty($this->array_modules[$module]['setting']['data_always']) && $this->array_modules[$module]['setting']['data_always'] == 1 && $result['data_always'][] = $module;
                    !empty($this->array_modules[$module]['setting']['js_always']) && $this->array_modules[$module]['setting']['js_always'] == 1 && $result['js_always'][] = $module;
                    !empty($this->array_modules[$module]['setting']['css_always']) && $this->array_modules[$module]['setting']['css_always'] == 1 && $result['css_always'][] = $module;
                    !empty($this->array_modules[$module]['setting']['js']) && $this->array_modules[$module]['setting']['js'] == 1 && $result['page'][$this->array_modules[$module]['page']]['js'][] = ['name' => $module, 'type' => $this->array_modules[$module]['setting']['type']];
                    !empty($this->array_modules[$module]['setting']['css']) && $this->array_modules[$module]['setting']['css'] == 1 && $result['page'][$this->array_modules[$module]['page']]['css'][] = ['name' => $module, 'type' => $this->array_modules[$module]['setting']['type']];
                    !empty($this->array_modules[$module]['sidebar']) && $result['sidebar'][] = $module;
                endif;
            endfor;

            for ($i2 = 0; $i2 < $c = sizeof($result['page']); $i2++):

                $page = array_keys($result['page'])[$i2];

                for ($i = 0; $i < $this->array_modules_count; $i++):

                    $module = array_keys($this->array_modules)[$i];

                    if (
                        $this->array_modules[$module]['setting']['status'] == 1
                        && $this->array_modules[$module]['page'] == 'all'
                    ):
                        if (!empty($this->array_modules[$module]['setting']['interface']) && $this->array_modules[$module]['setting']['interface'] == 1):
                            $result['page'][$page]['interface'][empty($this->array_modules[$module]['setting']['interface_adjacent']) ? 'afternavbar' : $this->array_modules[$module]['setting']['interface_adjacent']][] = $module;
                        endif;
                        if (!empty($this->array_modules[$module]['setting']['interface_always']) && $this->array_modules[$module]['setting']['interface_always'] == 1):
                            $result['interface_always'][empty($this->array_modules[$module]['setting']['interface_always_adjacent']) ? 'afternavbar' : $this->array_modules[$module]['setting']['interface_always_adjacent']][] = ['name' => $module];
                        endif;
                        !empty($this->array_modules[$module]['setting']['data']) && $this->array_modules[$module]['setting']['data'] == 1 && $result['page'][$page]['data'][] = $module;
                        !empty($this->array_modules[$module]['setting']['data_always']) && $this->array_modules[$module]['setting']['data_always'] == 1 && $result['data_always'][] = $module;
                        !empty($this->array_modules[$module]['setting']['js_always']) && $this->array_modules[$module]['setting']['js_always'] == 1 && $result['js_always'][] = $module;
                        !empty($this->array_modules[$module]['setting']['css_always']) && $this->array_modules[$module]['setting']['css_always'] == 1 && $result['css_always'][] = $module;
                        !empty($this->array_modules[$module]['setting']['js']) && $this->array_modules[$module]['setting']['js'] == 1 && $result['page'][$page]['js'][] = ['name' => $module, 'type' => $this->array_modules[$module]['setting']['type']];
                        !empty($this->array_modules[$module]['setting']['css']) && $this->array_modules[$module]['setting']['css'] == 1 && $result['page'][$page]['css'][] = ['name' => $module, 'type' => $this->array_modules[$module]['setting']['type']];
                        !empty($this->array_modules[$module]['sidebar']) && $result['sidebar'][] = $module;
                    endif;
                endfor;
            endfor;

            file_put_contents(SESSIONS . 'modules_initialization.php', '<?php return ' . var_export_min($result) . ";\n");
        endif;
        return require SESSIONS . 'modules_initialization.php';
    }

    public function get_module_cache($module, $name = 'cache')
    {
        if (file_exists(MODULES . $module . '/temp/' . $name . '.php')):
            return require MODULES . $module . '/temp/' . $name . '.php';
        else:
            !file_exists(MODULES . $module . '/temp') && mkdir(MODULES . $module . '/temp', 0777, true);
            file_put_contents(MODULES . $module . '/temp/' . $name . '.php', '<?php return [];');
            return [];
        endif;
    }

    public function set_module_cache($module, $data, $name = 'cache')
    {
        !file_exists(MODULES . $module . '/temp') && mkdir(MODULES . $module . '/temp', 0777, true);
        file_put_contents(MODULES . $module . '/temp/' . $name . '.php', '<?php return ' . var_export_min($data) . ";");
        $this->General->Db->queryFirst('module_page_adminpanel', 'stats');
    }

    public function get_arr_modules()
    {
        $result = [];

        if (!file_exists(SESSIONS . 'modules_cache.php')) {
            $this->scan_modules = array_diff(scandir(MODULES, 1), array('..', '.', 'disabled'));

            $this->array_modules_count = sizeof($this->scan_modules);

            if ($this->array_modules_count != 0) {
                for ($i = 0; $i < $this->array_modules_count; $i++) {
                    $result[$this->scan_modules[$i]] = json_decode(file_get_contents(MODULES . $this->scan_modules[$i] . '/description.json'), true);
                }
            }

            file_put_contents(SESSIONS . 'modules_cache.php', '<?php return ' . var_export_min($result) . ";");
        }
        return require SESSIONS . 'modules_cache.php';
    }

    public function check_generated_style()
    {
        $this->css_library[] = ASSETS_CSS . 'style.css';
        $this->css_library[] = TEMPLATES . $this->General->arr_general['theme'] . '/assets/css/style.css';
    }

    public function check_generated_js()
    {
        $this->js_library[] = ASSETS_JS . 'app.js';
        $this->js_library[] = TEMPLATES . $this->General->arr_general['theme'] . '/assets/js/app.js';
    }

    public function set_page_title($text)
    {
        $this->page_title = $text;
    }

    public function get_page_title()
    {
        return empty($this->page_title) ? $this->General->arr_general['full_name'] : $this->page_title;
    }

    public function set_page_description($text)
    {
        $this->page_description = $text;
    }

    public function set_page_canonical($url)
    {
        $this->page_canonical = $url;
    }

    public function get_page_description()
    {
        return empty($this->page_description) ? $this->General->arr_general['info'] : $this->page_description;
    }

    public function get_page_canonical()
    {
        return empty($this->page_canonical) ? '' : $this->page_canonical;
    }

    public function set_page_image($text)
    {
        $this->page_image = $text;
    }

    public function get_page_image()
    {
        if (empty($this->page_image)):
            return file_exists(CACHE . '/img/global/bar_logo.jpg') ? $this->General->arr_general['site'] . 'storage/cache/img/global/bar_logo.jpg' : copy(CACHE . '/img/global/default_bar_logo.jpg', CACHE . '/img/global/bar_logo.jpg') && $this->General->arr_general['site'] . 'storage/cache/img/global/bar_logo.jpg';
        else:
            return $this->General->arr_general['site'] . $this->page_image;
        endif;
    }

    function action_time_exchange($seconds, $type = 0)
    {
        if (floor($seconds / 60 / 60 / 24 / 30) != 0 && ($type == 0 || $type == 5)) {
            $month = floor($seconds / 60 / 60 / 24 / 30);
            return $month > 1 ? $month . ' ' . $this->Translate->get_translate_phrase('_Months') : $month . ' ' . $this->Translate->get_translate_phrase('_Month');
        } elseif (floor($seconds / 60 / 60 / 24 / 7) != 0 && ($type == 0 || $type == 4)) {
            $week = floor($seconds / 60 / 60 / 24 / 7);
            return $week > 1 ? $week . ' ' . $this->Translate->get_translate_phrase('_Weeks') : $week . ' ' . $this->Translate->get_translate_phrase('_Week');
        } elseif (floor($seconds / 60 / 60 / 24) != 0 && ($type == 0 || $type == 3)) {
            $day = floor($seconds / 60 / 60 / 24);
            return $day > 1 ? $day . ' ' . $this->Translate->get_translate_phrase('_Days') : $day . ' ' . $this->Translate->get_translate_phrase('_Day');
        } elseif (floor($seconds / 60 / 60) != 0 && ($type == 0 || $type == 2)) {
            $hour = floor($seconds / 60 / 60);
            return $hour > 1 ? $hour . ' ' . $this->Translate->get_translate_phrase('_Hour') : $hour . ' ' . $this->Translate->get_translate_phrase('_Hour');
        } elseif (floor($seconds / 60) != 0 && ($type == 0 || $type == 1)) {
            $min = floor($seconds / 60);
            return $min > 1 ? $min . ' ' . $this->Translate->get_translate_phrase('_Minute') : $min . ' ' . $this->Translate->get_translate_phrase('_Minute');
        } else {
            return $seconds . ' ' . $this->Translate->get_translate_phrase('_Second');
        }
    }

    public function action_time_exchange_exact($seconds)
    {
        $div = array(2592000, 604800, 86400, 3600, 60, 1);
        $desc = array('мес.', 'нед.', 'дн.', 'ч.', 'мин.', 'сек.');
        $ret = array();
        foreach ($div as $index => $value) {
            $quotent = floor($seconds / $value);
            if ($quotent > 0) {
                $ret[$desc[$index]] = $quotent;
                $seconds %= $value;
            }
        }
        if (isset($ret['мес.'])) {
            $result = array();
            foreach (array('мес.', 'нед.') as $unit) {
                if (isset($ret[$unit])) {
                    $result[] = $ret[$unit] . " $unit";
                }
            }
            return implode(' ', $result);
        } elseif (isset($ret['нед.'])) {
            $result = array();
            foreach (array('нед.', 'дн.') as $unit) {
                if (isset($ret[$unit])) {
                    $result[] = $ret[$unit] . " $unit";
                }
            }
            return implode(' ', $result);
        } elseif (isset($ret['дн.'])) {
            $result = array();
            foreach (array('дн.', 'ч.') as $unit) {
                if (isset($ret[$unit])) {
                    $result[] = $ret[$unit] . " $unit";
                }
            }
            return implode(' ', $result);
        } elseif (isset($ret['ч.'])) {
            $result = array();
            foreach (array('ч.', 'мин.') as $unit) {
                if (isset($ret[$unit])) {
                    $result[] = $ret[$unit] . " $unit";
                }
            }
            return implode(' ', $result);
        } elseif (isset($ret['мин.'])) {
            $result = array();
            foreach (array('мин.', 'сек.') as $unit) {
                if (isset($ret[$unit])) {
                    $result[] = $ret[$unit] . " $unit";
                }
            }
            return implode(' ', $result);
        } elseif (isset($ret['сек.'])) {
            return $ret['сек.'] . ' сек.';
        }
    }
    
    public function get_balance()
    {
        if (isset($_SESSION['steamid32']) && isset($this->General->Db->db_data['Core'])) {
            preg_match('/:[0-9]{1}:\d+/i', $_SESSION['steamid32'], $auth);
            $param = ['auth' => '%' . $auth[0] . '%'];
            $infoUser = $this->General->Db->queryAll('lk', 0, 0, "SELECT `cash` FROM `lk` WHERE `auth` LIKE :auth LIMIT 1", $param);
            $cash = 'cash';
            return number_format($infoUser[0][$cash], 0, ' ', ' ');
        }
        return false;
    }

    public function get_settings_modules($name, $file)
    {
        return file_exists(MODULESCACHE . $name . '/' . $file . '.php') ? require MODULESCACHE . $name . '/' . $file . '.php' : [];
    }

    public function put_settings_modules($name, $file, $data)
    {
        $dir = MODULESCACHE . $name . '/';
        $path = $dir . $file . '.php';

        if (!is_dir($dir)) mkdir($dir, 0777, true);
        if (!is_writable($dir)) chmod($dir, 0777);

        return file_put_contents($path, '<?php return ' . var_export($data, true) . ';');
    }

    public function time_to_hours($seconds)
    {
        if ($seconds >= 3600) {
            $hours = floor($seconds / 3600);
            return $hours . $this->Translate->get_translate_phrase('_Hour');
        } elseif ($seconds >= 60) {
            $minutes = floor($seconds / 60);
            return $minutes . $this->Translate->get_translate_phrase('_Minute');
        } else {
            return $seconds . $this->Translate->get_translate_phrase('_Second');
        }
    }
}
