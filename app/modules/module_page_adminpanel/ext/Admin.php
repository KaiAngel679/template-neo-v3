<?php



namespace app\modules\module_page_adminpanel\ext;

use mysqli;

class Admin
{
    public $General;
    public $Modules;
    public $Db;
    public $Translate;

    function __construct($General, $Modules, $Db, $Translate)
    {

        defined('IN_LR') != true && die();

        (empty($_SESSION['steamid32']) || !isset($_SESSION['user_admin'])) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die();

        $this->General = $General;

        $this->Modules = $Modules;
        $this->Db = $Db;

        $this->Translate = $Translate;
    }

    function ReloadPage()
    {
        header("Location: ?" . $_SERVER['QUERY_STRING']);
    }

    public function GetArrModules()
    {
        $result = [];

        $modules_list = array_diff(scandir(MODULES, 1), array('..', '.', 'disabled'));

        $modules_count = sizeof($modules_list);

        if ($modules_count != 0) {
            for ($i = 0; $i < $modules_count; $i++) {
                $result[$modules_list[$i]] = json_decode(file_get_contents(MODULES . $modules_list[$i] . '/description.json'), true);
            }
        }
        return $result;
    }

    function arr_k_last($array)
    {
        if (!is_array($array) || empty($array)) {
            return NULL;
        }

        return array_keys($array)[count($array) - 1];
    }

    function action_clear_modules_initialization()
    {
        $array_modules = $this->GetArrModules();
        $count_modules = sizeof($array_modules);

        for ($i = 0; $i < $count_modules; $i++):
            $module = array_keys($array_modules)[$i];
            if ($array_modules[$module]['setting']['status'] == 1 && $array_modules[$module]['page'] != 'all'):
                if (!empty($array_modules[$module]['setting']['interface']) && $array_modules[$module]['setting']['interface'] == 1):
                    $result['page'][$array_modules[$module]['page']]['interface'][empty($array_modules[$module]['setting']['interface_adjacent']) ? 'afternavbar' : $array_modules[$module]['setting']['interface_adjacent']][] = $module;
                endif;
                if (!empty($array_modules[$module]['setting']['interface_always']) && $array_modules[$module]['setting']['interface_always'] == 1):
                    $result['interface_always'][empty($array_modules[$module]['setting']['interface_always_adjacent']) ? 'afternavbar' : $array_modules[$module]['setting']['interface_always_adjacent']][] = ['name' => $module];
                endif;
                !empty($array_modules[$module]['setting']['data']) && $array_modules[$module]['setting']['data'] == 1 && $result['page'][$array_modules[$module]['page']]['data'][] = $module;
                !empty($array_modules[$module]['setting']['data_always']) && $array_modules[$module]['setting']['data_always'] == 1 && $result['data_always'][] = $module;
                !empty($array_modules[$module]['setting']['js_always']) && $array_modules[$module]['setting']['js_always'] == 1 && $result['js_always'][] = $module;
                !empty($array_modules[$module]['setting']['css_always']) && $array_modules[$module]['setting']['css_always'] == 1 && $result['css_always'][] = $module;
                !empty($array_modules[$module]['setting']['js']) && $array_modules[$module]['setting']['js'] == 1 && $result['page'][$array_modules[$module]['page']]['js'][] = ['name' => $module, 'type' => $array_modules[$module]['setting']['type']];
                !empty($array_modules[$module]['setting']['css']) && $array_modules[$module]['setting']['css'] == 1 && $result['page'][$array_modules[$module]['page']]['css'][] = ['name' => $module, 'type' => $array_modules[$module]['setting']['type']];
                !empty($array_modules[$module]['sidebar']) && $result['sidebar'][] = $module;
            endif;
        endfor;

        if (file_exists(SESSIONS . 'modules_initialization.php')) {
            $cache = require SESSIONS . 'modules_initialization.php';

            foreach ($result['page']['home']['interface']['afternavbar'] as $key => $val) {
                $search = array_search($val, $cache['page']['home']['interface']['afternavbar']);
                if ($cache['page']['home']['interface']['afternavbar'][$search] == $val)
                    $restwo[$search] = $val;
                else
                    $restwo[$this->arr_k_last($result['page']['home']['interface']['afternavbar'])] = $val;
            }

            ksort($restwo);

            $result['page']['home']['interface']['afternavbar'] = array_values($restwo);
        }

        file_put_contents(SESSIONS . 'modules_initialization.php', '<?php return ' . var_export_min($result, true) . ";");

        $cache_files = [
            'modules_cache' => SESSIONS . 'modules_cache.php',
            'translator_cache' => SESSIONS . 'translator.json',
            'translator_js_cache' => ASSETS_JS . 'translations.js',
        ];

        for ($i = 0; $i < $this->Modules->array_modules_count; $i++):
            $module = array_keys($this->Modules->array_modules)[$i];

            file_exists(MODULES . $module . '/temp/cache.php') && unlink(MODULES . $module . '/temp/cache.php');
        endfor;

        file_exists($cache_files['modules_cache']) && unlink($cache_files['modules_cache']);

        file_exists($cache_files['translator_cache']) && unlink($cache_files['translator_cache']);

        file_exists($cache_files['translator_js_cache']) && unlink($cache_files['translator_js_cache']);

        file_exists(MODULES . 'module_page_adminpanel/temp/stats.php') && unlink(MODULES . 'module_page_adminpanel/temp/stats.php');

        return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_successClearCache')];
    }

    function edit_modules_initialization()
    {
        $array = $this->Modules->arr_module_init;

        $data = json_decode($_POST['data'], true);

        for ($i2 = 0, $c = sizeof($data); $i2 < $c; $i2++) {
            $_data[] = $data[$i2]['id'];
        }

        get_section('module_page', 'home') == 'sidebar' ? $array['sidebar'] = $_data : $array['page'][get_section('module_page', 'home')]['interface'][get_section('module_interface_adjacent', 'afternavbar')] = $_data;

        file_put_contents(SESSIONS . 'modules_initialization.php', '<?php return ' . var_export_min($array, true) . ";");
    }

    function edit_options()
    {

        $arr = $this->General->arr_general;

        $option = [
            'full_name' => $_POST['full_name'],
            'short_name' => $_POST['short_name'],
            'info' => $_POST['info'],
            'keywords' => $_POST['keywords'],
            'language' => $_POST['language'],
            'currency' => $_POST['currency'],
            'web_key' => $_POST['web_key'],
            'avatars_cache_time' => (int) $_POST['avatars_cache_time'],
            'faceit_key' => $_POST['faceit_key'],
            'faceit_cache_time' => (int) $_POST['faceit_cache_time']
        ];

        file_put_contents(SESSIONS . 'options.php', '<?php return ' . var_export_min(array_replace($arr, $option), true) . ";");

        return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_dataSaved')];
    }

    function get_social()
    {
        return file_exists(MODULES . 'module_page_adminpanel/settings.php') ? require MODULES . 'module_page_adminpanel/settings.php' : [];
    }

    function edit_social()
    {
        $arr = $this->get_social();
        $option = [
            'discord' => [
                'botToken' => $_POST['botToken'],
                'guildId' => $_POST['guildId'],
            ],
            'vk' => [
                'vkToken' => $_POST['vkToken'],
                'groupId' => $_POST['groupId'],
            ],
            'telegram' => [
                'telegramToken' => $_POST['telegramToken'],
                'chatId' => $_POST['chatId'],
            ],
        ];
        file_put_contents(MODULES . 'module_page_adminpanel/settings.php', '<?php return ' . var_export_min(array_replace($arr, $option), true) . ";");
        return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_dataSaved')];
    }

    function action_db_add_mods()
    {

        $db = require SESSIONS . '/db.php';

        $db += [$_POST['mod'] => []];
        file_put_contents(SESSIONS . 'db.php', '<?php return ' . var_export_opt($db, true) . ";");

        $this->ReloadPage();
    }

    function EditServer($post)
    {
        $db = require SESSIONS . '/db.php';
        $edit_info = explode(";", $post['mod_info_edit']);
        $db[$edit_info[0]][$edit_info[1]]['HOST'] = $post['host_edit'];
        $db[$edit_info[0]][$edit_info[1]]['PORT'] = $post['port_edit'] ?? '3306';
        $db[$edit_info[0]][$edit_info[1]]['USER'] = $post['username_edit'];
        $db[$edit_info[0]][$edit_info[1]]['PASS'] = $post['password_edit'];
        $db[$edit_info[0]][$edit_info[1]]['DB'][$edit_info[2]]['DB'] = $post['db_name_edit'];
        $db[$edit_info[0]][$edit_info[1]]['DB'][$edit_info[2]]['Prefix'][$edit_info[3]]['table'] = $post['table_name_edit'] ?? '';
        if (isset($db[$edit_info[0]][$edit_info[1]]['DB'][$edit_info[2]]['Prefix'][$edit_info[3]]['ranks_pack'])) {
            $db[$edit_info[0]][$edit_info[1]]['DB'][$edit_info[2]]['Prefix'][$edit_info[3]]['ranks_pack'] = $post['rank_pack_edit'];
        }
        $db[$edit_info[0]][$edit_info[1]]['DB'][$edit_info[2]]['Prefix'][$edit_info[3]]['name'] = $post['server_name_edit'] ?? '';
        file_put_contents(SESSIONS . 'db.php', '<?php return ' . var_export_opt($db, true) . ";");
        return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_dataChanged')];
    }

    function action_db_add_connection()
    {
        $db = require SESSIONS . '/db.php';
        if ($_POST['function'] == 'add_conection') {
            $mod = $_POST['mod'];
            if (empty($_POST['host']))
                return exit(json_encode(['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_error_host')]));
            else
                $host = $_POST['host'];
            if (empty($_POST['db_name']))
                return exit(json_encode(['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_error_db')]));
            else
                $db_name = $_POST['db_name'];
            if (empty($_POST['password']))
                return exit(json_encode(['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_error_password')]));
            else
                $password = $_POST['password'];
            if (empty($_POST['username']))
                return exit(json_encode(['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_error_username')]));
            else
                $username = $_POST['username'];
            if (empty($_POST['port']))
                return exit(json_encode(['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_error_port')]));
            else
                $port = $_POST['port'];
            if (empty($_POST['table_name']))
                return exit(json_encode(['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_error_name_table')]));
            else
                $table_name = $_POST['table_name'];
            $server_name = empty($_POST['server_name']) ? '' : $_POST['server_name'];
            $steam_mod = 1;
            $game_mod = 730;
            if ($mod == 'LevelsRanks' && empty($_POST['rank_pack'])) {
                return exit(json_encode(['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_error_rank_pack')]));
            } else
                $rank_pack = $_POST['rank_pack'];

            $query = ['HOST' => $host, 'PORT' => $port, 'USER' => $username, 'PASS' => $password, 'DB' => [0 => ['DB' => $db_name, 'Prefix' => [0 => ['table' => $table_name, 'name' => $server_name, 'mod' => $game_mod, 'steam' => $steam_mod]]]]];
            if ($mod == 'LevelsRanks') {
                $query['DB'][0]['Prefix'][0]['ranks_pack'] = $rank_pack;
            }

            $mysqli = new mysqli($host, $username, $password, $db_name, $port);

            if ($mysqli->connect_error)
                return exit(json_encode(['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_error_con_db')]));

            $mysqli->close();

            if (empty($db[$mod])) {
                $db[$mod] = [0 => $query];
                file_put_contents(SESSIONS . 'db.php', '<?php return ' . var_export_opt($db, true) . ";");
                return exit(json_encode(['success' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_success_mod_created')]));
            } else {
                $db[$mod][] = $query;
                file_put_contents(SESSIONS . 'db.php', '<?php return ' . var_export_opt($db, true) . ";");
                return exit(json_encode(['success' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_success_db_created')]));
            }
        }

        if ($_POST['function'] == 'delete') {
            if (!empty($_POST['table'])) {
                $db_info = explode(";", $_POST['table']);
                if (!empty($db[$db_info[0]])) {
                    if (count($db[$db_info[0]]) == 1) {
                        if (count($db[$db_info[0]][$db_info[1]]['DB']) == 1) {
                            unset($db[$db_info[0]]);
                        } else {
                            unset($db[$db_info[0]][$db_info[1]]['DB'][$db_info[2]]);
                        }
                    } else {
                        unset($db[$db_info[0]][$db_info[1]]);
                    }
                }
                unset($db[$_POST['table']]);
                file_put_contents(SESSIONS . 'db.php', '<?php return ' . var_export_opt($db, true) . ";");
                return;
            }
        }
        return exit(json_encode(['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_error_not_found')]));
    }

    function action_get_server($id)
    {
        if (!preg_match('/^[0-9]{1,3}$/', $id))
            return;
        $params = ['id' => $id];
        $server = $this->Db->query('Core', 0, 0, "SELECT * FROM lvl_web_servers WHERE id = :id", $params);
        return $server;
    }
    function action_add_server()
    {
        $ip_port_array = explode(':', $_POST['server_ip_port']);
        $url = "https://ipinfo.io/{$ip_port_array[0]}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        $city = $data['city'] ?? '';
        $country = $data['country'] ?? '';

        $params = [
            'server_ip_port' => $_POST['server_ip_port'] ?? 0,
            'server_ip_port_fake' => $_POST['server_ip_port_fake'] ?? 0,
            'server_name' => $_POST['server_name'] ?? 0,
            'server_name_custom' => $_POST['server_name_custom'] ?? 0,
            'server_rcon' => $_POST['server_rcon'] ?? 0,
            'server_stats' => $_POST['server_stats'] ?? 0,
            'server_vip' => $_POST['server_vip'] ?? 0,
            'server_vip_id' => $_POST['server_vip_id'] ?? 0,
            'server_sb' => $_POST['server_sb'] ?? 0,
            'server_sb_id' => $_POST['server_sb_id'] ?? 0,
            'server_shop' => $_POST['server_shop'] ?? 0,
            'server_lk' => $_POST['server_lk'] ?? 0,
            'server_mod' => $_POST['server_mod'],
            'server_country' => $country ?? '',
            'server_city' => $city ?? '',
            'server_bage' => $_POST['server_bage'] ?? 0,
            'server_game' => $_POST['server_game'] ?? 'cs2',
            'server_status' => $_POST['server_status'] ?? 1
        ];

        $this->Db->query('Core', 0, 0, "INSERT INTO `lvl_web_servers` (
            `ip`,
            `fakeip`,
            `name`,
            `name_custom`,
            `rcon`,
            `server_stats`,
            `server_vip`,
            `server_vip_id`,
            `server_sb`,
            `server_sb_id`,
            `server_shop`,
            `server_lk`,
            `server_mod`,
            `server_country`,
            `server_city`,
            `server_bage`,
            `server_status`,
            `server_game`)
            VALUES (
                :server_ip_port,
                :server_ip_port_fake,
                :server_name,
                :server_name_custom,
                :server_rcon,
                :server_stats,
                :server_vip,
                :server_vip_id,
                :server_sb,
                :server_sb_id,
                :server_shop,
                :server_lk,
                :server_mod,
                :server_country,
                :server_city,
                :server_bage,
                :server_status,
                :server_game
            );", $params);

        $this->ReloadPage();
    }

    function action_edit_server()
    {
        $ip_port_array = explode(':', $_POST['server_ip_port_edit']);
        $url = "https://ipinfo.io/{$ip_port_array[0]}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        $city = $data['city'] ?? '';
        $country = $data['country'] ?? '';

        $params = [
            'server_id' => $_POST['server_id_edit'] ?? 0,
            'server_ip_port' => $_POST['server_ip_port_edit'] ?? 0,
            'server_ip_port_fake' => $_POST['server_ip_port_fake_edit'] ?? 0,
            'server_name' => $_POST['server_name_edit'] ?? 0,
            'server_name_custom' => $_POST['server_name_custom_edit'] ?? 0,
            'server_rcon' => $_POST['server_rcon_edit'] ?? 0,
            'server_stats' => $_POST['server_stats_edit'] ?? 0,
            'server_vip' => $_POST['server_vip_edit'] ?? 0,
            'server_vip_id' => $_POST['server_vip_id_edit'] ?? 0,
            'server_sb' => $_POST['server_sb_edit'] ?? 0,
            'server_sb_id' => $_POST['server_sb_id_edit'] ?? 0,
            'server_shop' => $_POST['server_shop_edit'] ?? 0,
            'server_lk' => $_POST['server_lk_edit'] ?? 0,
            'server_mod' => $_POST['server_mod_edit'],
            'server_country' => $country,
            'server_city' => $city,
            'server_bage' => $_POST['server_bage_edit'],
            'server_game' => $_POST['server_game_edit'],
            'server_status' => $_POST['server_status_edit'] ?? 1
        ];

        $this->Db->query('Core', 0, 0, "UPDATE lvl_web_servers SET `ip` = :server_ip_port,
                                                                    `fakeip` = :server_ip_port_fake,
                                                                    `name` = :server_name,
                                                                    `name_custom` = :server_name_custom,
                                                                    `rcon` = :server_rcon,
                                                                    `server_stats` = :server_stats,
                                                                    `server_vip` = :server_vip,
                                                                    `server_vip_id` = :server_vip_id,
                                                                    `server_sb` = :server_sb,
                                                                    `server_sb_id` = :server_sb_id,
                                                                    `server_shop` = :server_shop,
                                                                    `server_lk` = :server_lk,
                                                                    `server_mod` = :server_mod,
                                                                    `server_country` = :server_country,
                                                                    `server_city` = :server_city,
                                                                    `server_bage` = :server_bage,
                                                                    `server_game` = :server_game,
                                                                    `server_status` = :server_status
                                                                    WHERE `id` = :server_id", $params);

        $this->ReloadPage();
    }

    function action_del_server()
    {
        $params = ['id' => $_POST['del_server']];

        $this->Db->query('Core', 0, 0, 'DELETE FROM `lvl_web_servers` WHERE `id` = :id', $params);
    }

    public function info_modules_settings($id)
    {
        $settings = $this->Modules->get_settings_modules('module_block_main_banner_slider', 'settings');

        foreach ($settings['slides'] as $key_id => $key) {
            if (isset($id) && $id == $key_id) {
                return $key;
            }
        }
        return null;
    }

    function edit_module($POST, $GET, $FILES)
    {
        if ($GET['options'] == 'module_block_main_banner_slider' && (!isset($GET['baner_edit']) || $GET['baner_edit'] === '')) {
            chmod(MODULES . 'module_block_main_banner_slider/settings.php', 0777);
            if (isset($FILES['file']) && $FILES['file']['error'] == UPLOAD_ERR_OK) {
                if (exif_imagetype($FILES['file']['tmp_name']) !== false) {
                    $extension = pathinfo($FILES['file']['name'], PATHINFO_EXTENSION);
                    $newFileName = uniqid() . '.' . $extension;
                    if (move_uploaded_file($FILES['file']['tmp_name'], MODULES . 'module_block_main_banner_slider/assets/img/' . $newFileName)) {
                        $newSlide = [
                            'img' => $newFileName,
                            'title' => $POST['title'],
                            'description' => $POST['description'],
                            'button_text' => $POST['button_text'],
                            'button_url' => $POST['button_url']
                        ];
                        $settings = $this->Modules->get_settings_modules($GET['options'], 'settings');
                        $settings['slides'][] = $newSlide;
                        $this->Modules->put_settings_modules($GET['options'], 'settings', $settings);
                        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_newSlideCreated')];
                    } else {
                        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorMooveInFile')];
                    }
                } else {
                    return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_fileNotImage')];
                }
            } else {
                return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorLoadFile')];
            }
        }
        if ($GET['options'] == 'module_block_main_banner_slider' && (isset($GET['baner_edit']) || $GET['baner_edit'] !== '')) {
            chmod(MODULES . 'module_block_main_banner_slider/settings.php', 0777);
            if (!empty($FILES['file']['name'])) {
                if (isset($FILES['file']) && $FILES['file']['error'] == UPLOAD_ERR_OK) {
                    if (exif_imagetype($FILES['file']['tmp_name']) !== false) {
                        $extension = pathinfo($FILES['file']['name'], PATHINFO_EXTENSION);
                        $newFileName = uniqid() . '.' . $extension;
                        if (move_uploaded_file($FILES['file']['tmp_name'], MODULES . 'module_block_main_banner_slider/assets/img/' . $newFileName)) {
                            $settings = $this->Modules->get_settings_modules($GET['options'], 'settings');
                            foreach ($settings['slides'] as $id => $key) {
                                if ($id == $GET['baner_edit']) {
                                    $EditSlide = [
                                        'img' => $newFileName,
                                        'title' => $POST['title'],
                                        'description' => $POST['description'],
                                        'button_text' => $POST['button_text'],
                                        'button_url' => $POST['button_url']
                                    ];
                                    $settings['slides'][$id] = $EditSlide;
                                }
                            }
                            $this->Modules->put_settings_modules($GET['options'], 'settings', $settings);
                            return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_slideEdited')];
                        } else {
                            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorMooveInFile')];
                        }
                    } else {
                        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_fileNotImage')];
                    }
                } else {
                    return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorLoadFile')];
                }
            } else {
                chmod(MODULES . 'module_block_main_banner_slider/settings.php', 0777);
                $settings = $this->Modules->get_settings_modules($GET['options'], 'settings');
                foreach ($settings['slides'] as $id => $key) {
                    if ($id == $GET['baner_edit']) {
                        $EditSlide = [
                            'img' => $key['img'],
                            'title' => $POST['title'],
                            'description' => $POST['description'],
                            'button_text' => $POST['button_text'],
                            'button_url' => $POST['button_url']
                        ];
                        $settings['slides'][$id] = $EditSlide;
                    }
                }
                $this->Modules->put_settings_modules($GET['options'], 'settings', $settings);
                return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_slideEdited')];
            }
        }
        if ($GET['options'] == 'module_page_profiles') {
            chmod(MODULES . 'module_page_profiles/settings.php', 0777);
            $options = $this->Modules->get_settings_modules($GET['options'], 'settings');

            $options['faceit_api_key'] = $POST['faceit_api_key'];
            $options['use_all_vips_servers_in_one_table'] = $POST['use_all_vips_servers_in_one_table'] == 'on' ? 1 : 0;
            $options['punishment_all_servers'] = $POST['punishment_all_servers'] == 'on' ? 1 : 0;

            $this->Modules->put_settings_modules($GET['options'], 'settings', $options);
            return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_settingsSaved')];
        }
        if ($GET['options'] == 'module_page_punishment') {
            chmod(MODULES . 'module_page_punishment/settings.php', 0777);
            $options = $this->Modules->get_settings_modules($GET['options'], 'settings');

            $options['iks_db_new'] = $POST['iks_db_new'];
            $options['punishment_all_servers'] = $POST['punishment_all_servers'] == 'on' ? 1 : 0;
            $options['func_unban'] = $POST['func_unban'] == 'on' ? 1 : 0;
            $options['price_unban'] = $POST['price_unban'];
            $options['func_unmute'] = $POST['func_unmute'] == 'on' ? 1 : 0;
            $options['price_unmute'] = $POST['price_unmute'];
            $options['func_like'] = $POST['func_like'] == 'on' ? 1 : 0;
            $options['hoursToLike'] = $POST['hoursToLike'];

            $this->Modules->put_settings_modules($GET['options'], 'settings', $options);
            return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_settingsSaved')];
        }
    }

    public function clearStats()
    {
        if (isset($_SESSION['user_admin'])) {
            for ($d = 0; $d < $this->Db->table_count['LevelsRanks']; $d++) {
                $this->Db->queryAll('LevelsRanks', $this->Db->db_data['LevelsRanks'][$d]['USER_ID'], $this->Db->db_data['LevelsRanks'][$d]['DB_num'], "UPDATE " . $this->Db->db_data['LevelsRanks'][$d]['Table'] . " SET `value` = 0, `rank` = 0, `kills` = 0, `deaths` = 0, `shoots` = 0, `hits` = 0, `headshots` = 0, `assists` = 0, `round_win` = 0, `round_lose` = 0");
            }
            return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_statsClear')];
        } else {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_accessDenied')];
        }
    }

    public function clearEmptyPlayer()
    {
        if (isset($_SESSION['user_admin'])) {
            for ($d = 0; $d < $this->Db->table_count['LevelsRanks']; $d++) {
                $this->Db->queryAll('LevelsRanks', $this->Db->db_data['LevelsRanks'][$d]['USER_ID'], $this->Db->db_data['LevelsRanks'][$d]['DB_num'], "DELETE FROM " . $this->Db->db_data['LevelsRanks'][$d]['Table'] . " WHERE `value` = 0 AND `rank` = 0 AND `kills` = 0 AND `deaths` = 0 AND `hits` = 0 AND `headshots` = 0 AND `assists` = 0 AND `round_win` = 0 AND `round_lose` = 0 AND `playtime` = 0;");
            }
            return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_emptyPLayersClreared')];
        } else {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_accessDenied')];
        }
    }

    public function clearUnactivePlayer()
    {
        if (isset($_SESSION['user_admin'])) {
            for ($d = 0; $d < $this->Db->table_count['LevelsRanks']; $d++) {
                $this->Db->queryAll('LevelsRanks', $this->Db->db_data['LevelsRanks'][$d]['USER_ID'], $this->Db->db_data['LevelsRanks'][$d]['DB_num'], "DELETE FROM " . $this->Db->db_data['LevelsRanks'][$d]['Table'] . " WHERE FROM_UNIXTIME(`lastconnect`) < DATE_SUB(NOW(), INTERVAL 3 MONTH)");
            }
            return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_inactivePlayersCleared')];
        } else {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_accessDenied')];
        }
    }

    function edit_module_core($POST, $GET)
    {
        $Module_data = $this->Modules->array_modules[$GET['options']];
        $Module_data['setting']['type'] = (int) $POST['module_type'] ?? 0;
        file_put_contents(MODULES . $GET['options'] . '/description.json', json_encode($Module_data, JSON_UNESCAPED_UNICODE));
        $modules_init = $this->Modules->arr_module_init;
        if (!empty($Module_data['sidebar']) && !in_array($GET['options'], $modules_init['sidebar'])) {
            $modules_init['sidebar'] = +$GET['options'];
        }
        file_put_contents(SESSIONS . '/modules_initialization.php', '<?php return ' . var_export($modules_init, true) . ";");
        $this->action_clear_modules_initialization();
        return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_settingsSaved')];
    }

    public function DelSettingsBaner($POST)
    {
        chmod(MODULES . 'module_block_main_banner_slider/settings.php', 0777);
        $options = $this->Modules->get_settings_modules('module_block_main_banner_slider', 'settings');

        $indexToDelete = null;
        foreach ($options['slides'] as $index => $item) {
            if ($index == $POST['id_del']) {
                $indexToDelete = $index;
                $imgdel = $item['img'];
                break;
            }
        }

        if ($indexToDelete !== null) {
            unlink('module_block_main_banner_slider/assets/img/' . $imgdel);
            unset($options['slides'][$indexToDelete]);
            $this->Modules->put_settings_modules('module_block_main_banner_slider', 'settings', $options);
            return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_bannerRemoved')];
        } else {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_bannedIDNotFound')];
        }
    }

    public function GetMenuCategories()
    {
        $jsonFile = MODULESCACHE . 'template_neo/menu.json';

        $menuJson = @file_get_contents($jsonFile);
        if ($menuJson === false) {
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_failedToReadFile')];
        }

        $menu = json_decode($menuJson, true);
        if ($menu === null && json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorDecoding') . json_last_error_msg()];
        }

        $categories = [];
        foreach ($menu as $item) {
            if (isset($item['type']) && $item['type'] === 'category') {
                $categories[] = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                ];
            }
        }

        return $categories;
    }

    private function decodeJsonArray(string $jsonData)
    {
        $arr = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($arr)) {
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_invalidJson') . json_last_error_msg()];
        }
        return $arr;
    }

    private function readJsonArrayFile(string $file)
    {
        if (!file_exists($file)) {
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_menuFileNotfound')];
        }
        $json = @file_get_contents($file);
        if ($json === false) {
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_failedToReadFile')];
        }
        $arr = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($arr)) {
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorDecoding') . json_last_error_msg()];
        }
        return $arr;
    }

    private function writeJsonArrayFile(string $file, array $data)
    {
        $saved = file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($saved === false) {
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorWriteingFile')];
        }
        return ['success' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_menuSaved')];
    }

    private function sortMenuFlat(string $jsonData, string $file)
    {
        @chmod($file, 0777);
        $tree = $this->decodeJsonArray($jsonData);
        if (isset($tree['error'])) return $tree;

        $order = [];
        foreach ($tree as $node) {
            $id = is_array($node) ? (int)($node['menu_id'] ?? $node['id'] ?? 0) : (int)$node;
            if ($id > 0) $order[] = $id;
        }

        $items = $this->readJsonArrayFile($file);
        if (isset($items['error'])) return $items;

        $byId = [];
        $leftover = [];
        foreach ($items as $it) {
            $itId = (int)($it['id'] ?? 0);
            if ($itId > 0) $byId[$itId] = $it;
            else $leftover[] = $it;
        }

        $newItems = [];
        $seen = [];
        foreach ($order as $itId) {
            if (isset($seen[$itId])) continue;
            $seen[$itId] = 1;
            if (isset($byId[$itId])) $newItems[] = $byId[$itId];
        }
        foreach ($items as $it) {
            $itId = (int)($it['id'] ?? 0);
            if ($itId > 0 && !isset($seen[$itId])) $newItems[] = $it;
        }
        foreach ($leftover as $it) $newItems[] = $it;

        return $this->writeJsonArrayFile($file, $newItems);
    }

    private function sortMenuNavTree(string $jsonData, string $file)
    {
        @chmod($file, 0777);
        $tree = $this->decodeJsonArray($jsonData);
        if (isset($tree['error'])) return $tree;

        $menu = $this->readJsonArrayFile($file);
        if (isset($menu['error'])) return $menu;

        $uidSeq = 0;
        $makeUid = function () use (&$uidSeq) {
            $uidSeq++;
            return $uidSeq;
        };

        $catStacks = [];
        $catByUid  = [];
        $pointStacks = [];
        $pointByUid  = [];

        foreach ($menu as $it) {
            $type = (($it['type'] ?? '') === 'category') ? 'category' : 'point';
            $id = (int)($it['id'] ?? 0);
            if ($type === 'category') {
                $catUid = $makeUid();
                $catByUid[$catUid] = $it;
                $catStacks[$id][] = $catUid;
                foreach ((array)($it['children'] ?? []) as $ch) {
                    $pid = (int)($ch['id'] ?? 0);
                    $pUid = $makeUid();
                    $pointByUid[$pUid] = ['item' => $ch, 'originCatUid' => $catUid];
                    $pointStacks[$pid][] = $pUid;
                }
            } else {
                $pUid = $makeUid();
                $pointByUid[$pUid] = ['item' => $it, 'originCatUid' => null];
                $pointStacks[$id][] = $pUid;
            }
        }

        $catsUsed = [];
        $pointsUsed = [];
        $nextCat = function (int $cid) use (&$catStacks, &$catsUsed, &$catByUid) {
            if (empty($catStacks[$cid])) return null;
            foreach ($catStacks[$cid] as $cu) {
                if (!isset($catsUsed[$cu])) {
                    return ['uid' => $cu, 'item' => $catByUid[$cu]];
                }
            }
            return null;
        };
        $nextPoint = function (int $pid) use (&$pointStacks, &$pointsUsed, &$pointByUid) {
            if (empty($pointStacks[$pid])) return null;
            foreach ($pointStacks[$pid] as $pu) {
                if (!isset($pointsUsed[$pu])) {
                    return ['uid' => $pu, 'item' => $pointByUid[$pu]['item']];
                }
            }
            return null;
        };

        $out = [];
        foreach ($tree as $node) {
            $t = (string)($node['type'] ?? '');
            if ($t === 'category') {
                $cid = (int)($node['menu_id'] ?? 0);
                $catInst = $nextCat($cid);
                if (!$cid || $catInst === null) continue;
                $children = [];
                foreach ((array)($node['children'] ?? []) as $chNode) {
                    $pid = (int)($chNode['point_id'] ?? $chNode['menu_id'] ?? 0);
                    $ptInst = $nextPoint($pid);
                    if (!$pid || $ptInst === null) continue;
                    $children[] = $ptInst['item'];
                    $pointsUsed[$ptInst['uid']] = 1;
                }
                $catItem = $catInst['item'];
                $catItem['children'] = $children;
                $out[] = $catItem;
                $catsUsed[$catInst['uid']] = 1;
            } elseif ($t === 'point') {
                $pid = (int)($node['point_id'] ?? $node['menu_id'] ?? 0);
                $ptInst = $nextPoint($pid);
                if (!$pid || $ptInst === null) continue;
                $out[] = $ptInst['item'];
                $pointsUsed[$ptInst['uid']] = 1;
            }
        }

        foreach ($catByUid as $cu => $catItem) {
            if (isset($catsUsed[$cu])) continue;
            $children = [];
            foreach ($pointByUid as $pu => $pinfo) {
                if (($pinfo['originCatUid'] ?? null) === $cu && !isset($pointsUsed[$pu])) {
                    $children[] = $pinfo['item'];
                    $pointsUsed[$pu] = 1;
                }
            }
            $catItem['children'] = $children;
            $out[] = $catItem;
            $catsUsed[$cu] = 1;
        }
        foreach ($pointByUid as $pu => $pinfo) {
            if (($pinfo['originCatUid'] ?? null) === null && !isset($pointsUsed[$pu])) {
                $out[] = $pinfo['item'];
                $pointsUsed[$pu] = 1;
            }
        }

        $maxId = 0;
        foreach ($out as $it) {
            $maxId = max($maxId, (int)($it['id'] ?? 0));
            if ((($it['type'] ?? '') === 'category')) {
                foreach ((array)($it['children'] ?? []) as $ch) {
                    $maxId = max($maxId, (int)($ch['id'] ?? 0));
                }
            }
        }
        $used = [];
        $alloc = function () use (&$used, &$maxId) {
            do {
                $maxId++;
            } while (isset($used[$maxId]));
            return $maxId;
        };
        $uni = function (&$id) use (&$used, $alloc) {
            $id = (int)($id ?? 0);
            if ($id <= 0 || isset($used[$id])) $id = $alloc();
            $used[$id] = 1;
        };

        foreach ($out as &$it) {
            $it['type'] = (($it['type'] ?? '') === 'category') ? 'category' : 'point';
            if ($it['type'] === 'category') {
                $it['children'] = is_array($it['children'] ?? null) ? $it['children'] : [];
            } else {
                unset($it['children']);
            }
            $uni($it['id']);
            if ($it['type'] === 'category') {
                foreach ($it['children'] as &$ch) {
                    $ch['type'] = 'point';
                    $uni($ch['id']);
                }
                unset($ch);
                $it['children'] = array_values($it['children']);
            }
        }
        unset($it);

        return $this->writeJsonArrayFile($file, $out);
    }

    public function sortMenu($jsonData, $rootContainer = 'nested-nav')
    {
        $rootContainer = (string)($rootContainer ?? 'nested-nav');
        $jsonData = (string)($jsonData ?? '[]');

        switch ($rootContainer) {
            case 'nested-userbar':
                return $this->sortMenuFlat($jsonData, MODULESCACHE . 'template_neo/userbar.json');
            case 'nested-footer':
                return $this->sortMenuFlat($jsonData, MODULESCACHE . 'template_neo/footer.json');
            case 'nested-nav':
            default:
                return $this->sortMenuNavTree($jsonData, MODULESCACHE . 'template_neo/menu.json');
        }
    }

    public function AddMenuPoint($POST)
    {
        $jsonFile = MODULESCACHE . 'template_neo/menu.json';

        if (!file_exists($jsonFile)) {
            @file_put_contents($jsonFile, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        chmod($jsonFile, 0777);

        if (empty($POST['title']))
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_forgotSpecify')];
        if (empty($POST['link']) && empty($POST['module']))
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_forgotLink')];
        if (empty($POST['svg']))
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_forgotSVG')];
        if (stripos(trim($POST['svg']), '<svg') !== 0)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_tagSVG')];

        $blank = !empty($POST['blank']);
        $onlyAdmin = !empty($POST['only_admin']);
        $onlyAdminSite = !empty($POST['only_admin_site']);
        $onlyAdminServer = !empty($POST['only_admin_server']);
        $onlyAuth = !empty($POST['only_auth']);
        $description = isset($POST['description']) ? trim($POST['description']) : '';

        $menuJson = @file_get_contents($jsonFile);
        if ($menuJson === false)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_failedToReadFile')];
        $menu = json_decode($menuJson, true);
        if ($menu === null && json_last_error() !== JSON_ERROR_NONE)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorDecoding') . json_last_error_msg()];

        $generateUniqueId = function (array $items) {
            $ids = [];
            foreach ($items as $item) {
                $ids[] = $item['id'];
                if (isset($item['children']) && is_array($item['children'])) {
                    foreach ($item['children'] as $child) {
                        $ids[] = $child['id'];
                    }
                }
            }
            return $ids ? max($ids) + 1 : 1;
        };

        $newPoint = [
            'id' => 0,
            'type' => 'point',
            'title' => $POST['title'],
            'description' => $description,
            'onlyAuth' => $onlyAuth,
            'onlyAdmin' => $onlyAdmin,
            'onlyAdminSite' => $onlyAdminSite,
            'onlyAdminServer' => $onlyAdminServer,
            'icon' => $POST['svg'],
            'url' => $POST['link'] ?? '',
            'module' => $POST['module'] ?? '',
            'blank' => $blank,
        ];

        $newPoint['id'] = $generateUniqueId($menu);
        $menu[] = $newPoint;
        if (file_put_contents($jsonFile, json_encode($menu, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_failedUpdateMenu')];
        return ['success' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_itemAdd')];
    }

    public function AddMenuCategory($POST)
    {
        $jsonFile = MODULESCACHE . 'template_neo/menu.json';
        if (!file_exists($jsonFile)) {
            @file_put_contents($jsonFile, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
        chmod($jsonFile, 0777);
        if (empty($POST['title']))
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_forgotCat')];
        if (empty($POST['svg']))
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_forgotSVG')];
        $svg = trim($POST['svg']);
        if (stripos($svg, '<svg') !== 0 || stripos($svg, '</svg>') === false)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_validSVG')];
        $onlyAdmin = !empty($POST['only_admin']);
        $onlyAdminSite = !empty($POST['only_admin_site']);
        $onlyAdminServer = !empty($POST['only_admin_server']);
        $onlyAuth = !empty($POST['only_auth']);
        $menuJson = @file_get_contents($jsonFile);
        if ($menuJson === false)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_failedToReadFile')];
        $menu = json_decode($menuJson, true);
        if ($menu === null && json_last_error() !== JSON_ERROR_NONE)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorDecoding') . json_last_error_msg()];
        $existingIds = array_column($menu, 'id');
        $newId = $existingIds ? max($existingIds) + 1 : 1;
        $menu[] = [
            'id' => $newId,
            'type' => 'category',
            'title' => $POST['title'],
            'icon' => $svg,
            'onlyAuth' => $onlyAuth,
            'onlyAdmin' => $onlyAdmin,
            'onlyAdminSite' => $onlyAdminSite,
            'onlyAdminServer' => $onlyAdminServer,
            'children' => []
        ];
        if (file_put_contents($jsonFile, json_encode($menu, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false)
            return ['error' => $this->Translate->get_translate_phrase('_errorIziToast')];
        return ['success' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_added')];
    }

    public function AddUserBar($POST)
    {
        $jsonFile = MODULESCACHE . 'template_neo/userbar.json';

        if (!file_exists($jsonFile)) {
            @file_put_contents($jsonFile, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        chmod($jsonFile, 0777);

        if (empty($POST['title']))
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_forgotSpecify')];
        if (empty($POST['link']) && empty($POST['module']))
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_forgotLink')];
        if (empty($POST['svg']))
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_forgotSVG')];
        if (stripos(trim($POST['svg']), '<svg') !== 0)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_tagSVG')];

        $blank = !empty($POST['blank']);
        $onlyAdmin = !empty($POST['only_admin']);
        $onlyAdminSite = !empty($POST['only_admin_site']);
        $onlyAdminServer = !empty($POST['only_admin_server']);
        $description = isset($POST['description']) ? trim($POST['description']) : '';

        $menuJson = @file_get_contents($jsonFile);
        if ($menuJson === false)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_failedToReadFile')];
        $menu = json_decode($menuJson, true);
        if ($menu === null && json_last_error() !== JSON_ERROR_NONE)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorDecoding') . json_last_error_msg()];

        $generateUniqueId = function (array $items) {
            $ids = [];
            foreach ($items as $item) {
                $ids[] = $item['id'];
            }
            return $ids ? max($ids) + 1 : 1;
        };

        $newPoint = [
            'id' => 0,
            'type' => 'point',
            'title' => $POST['title'],
            'description' => $description,
            'onlyAdmin' => $onlyAdmin,
            'onlyAdminSite' => $onlyAdminSite,
            'onlyAdminServer' => $onlyAdminServer,
            'icon' => $POST['svg'],
            'url' => $POST['link'] ?? '',
            'module' => $POST['module'] ?? '',
            'blank' => $blank,
        ];

        $newPoint['id'] = $generateUniqueId($menu);
        $menu[] = $newPoint;
        if (file_put_contents($jsonFile, json_encode($menu, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) {
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_failedUpdateMenu')];
        }
        return ['success' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_itemAdd')];
    }

    public function AddFooter($POST)
    {
        $jsonFile = MODULESCACHE . 'template_neo/footer.json';

        if (!file_exists($jsonFile)) {
            @file_put_contents($jsonFile, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        chmod($jsonFile, 0777);

        if (empty($POST['title']))
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_forgotSpecify')];
        if (empty($POST['link']) && empty($POST['module']))
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_forgotLink')];

        $blank = !empty($POST['blank']);
        $onlyAdmin = !empty($POST['only_admin']);
        $onlyAdminSite = !empty($POST['only_admin_site']);
        $onlyAdminServer = !empty($POST['only_admin_server']);
        $onlyAuth = !empty($POST['only_auth']);
        $description = isset($POST['description']) ? trim($POST['description']) : '';

        $menuJson = @file_get_contents($jsonFile);
        if ($menuJson === false)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_failedToReadFile')];
        $menu = json_decode($menuJson, true);
        if ($menu === null && json_last_error() !== JSON_ERROR_NONE)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorDecoding') . json_last_error_msg()];

        $generateUniqueId = function (array $items) {
            $ids = [];
            foreach ($items as $item) {
                $ids[] = $item['id'];
            }
            return $ids ? max($ids) + 1 : 1;
        };

        $newPoint = [
            'id' => 0,
            'type' => 'point',
            'title' => $POST['title'],
            'description' => $description,
            'onlyAuth' => $onlyAuth,
            'onlyAdmin' => $onlyAdmin,
            'onlyAdminSite' => $onlyAdminSite,
            'onlyAdminServer' => $onlyAdminServer,
            'url' => $POST['link'] ?? '',
            'module' => $POST['module'] ?? '',
            'blank' => $blank,
        ];

        $newPoint['id'] = $generateUniqueId($menu);
        $menu[] = $newPoint;
        if (file_put_contents($jsonFile, json_encode($menu, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) {
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_failedUpdateMenu')];
        }
        return ['success' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_itemAdd')];
    }

    public function loadMenuData($id, $type, $point = '')
    {
        $id = (int) ($id ?? 0);
        $type = (string) ($type ?? '');
        $pointId = ($point !== '' ? (int) $point : 0);

        if ($id <= 0 || $type === '') {
            return ['error' => $this->Translate->get_translate_phrase('_incorrectData')];
        }

        $readJsonArray = function (string $jsonFile) {
            if (!file_exists($jsonFile)) {
                return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_menuFileNotfound')];
            }

            $jsonContent = @file_get_contents($jsonFile);
            if ($jsonContent === false) {
                return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorReading')];
            }

            $data = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_invalidJson') . json_last_error_msg()];
            }

            return $data;
        };

        $buildResponse = function (array $item, string $scope, array $extra = []) {
            $res = [
                'success' => true,
                'title' => $item['title'] ?? '',
                'icon' => $item['icon'] ?? '',
                'only_auth' => (bool) ($item['onlyAuth'] ?? false),
                'only_admin' => (bool) ($item['onlyAdmin'] ?? false),
                'only_admin_site' => (bool) ($item['onlyAdminSite'] ?? false),
                'only_admin_server' => (bool) ($item['onlyAdminServer'] ?? false),
                'link' => $item['url'] ?? '',
                'module' => $item['module'] ?? '',
                'description' => $item['description'] ?? '',
                'blank' => (bool) ($item['blank'] ?? false),
            ];
            return array_merge($res, $extra);
        };

        switch ($type) {
            case 'userbar':
            case 'footer': {
                    $jsonFile = MODULESCACHE . 'template_neo/' . ($type === 'userbar' ? 'userbar.json' : 'footer.json');
                    $items = $readJsonArray($jsonFile);
                    if (isset($items['error'])) {
                        return $items;
                    }

                    foreach ($items as $item) {
                        if ((int) ($item['id'] ?? 0) === $id) {
                            return $buildResponse($item, $type);
                        }
                    }

                    return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_elementNotfound')];
                }

            case 'category':
            case 'point': {
                    $jsonFile = MODULESCACHE . 'template_neo/menu.json';
                    $menu = $readJsonArray($jsonFile);
                    if (isset($menu['error'])) {
                        return $menu;
                    }

                    if ($type === 'category') {
                        foreach ($menu as $category) {
                            if ((int) ($category['id'] ?? 0) === $id && ($category['type'] ?? '') === 'category') {
                                return $buildResponse($category, 'menu', [
                                    'category_id' => null,
                                    'point_id' => null,
                                ]);
                            }
                        }
                        return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_notFoundCat')];
                    }

                    if ($pointId > 0) {
                        foreach ($menu as $category) {
                            if ((int) ($category['id'] ?? 0) !== $id || ($category['type'] ?? '') !== 'category') {
                                continue;
                            }
                            foreach (($category['children'] ?? []) as $subpoint) {
                                if ((int) ($subpoint['id'] ?? 0) === $pointId && ($subpoint['type'] ?? '') === 'point') {
                                    return $buildResponse($subpoint, 'menu', [
                                        'category_id' => $id,
                                        'point_id' => $pointId,
                                    ]);
                                }
                            }
                            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_subitemNotFound')];
                        }
                        return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_catNotFoundInSubitem')];
                    }

                    foreach ($menu as $item) {
                        if ((int) ($item['id'] ?? 0) === $id && ($item['type'] ?? '') === 'point') {
                            return $buildResponse($item, 'menu', [
                                'category_id' => null,
                                'point_id' => null,
                            ]);
                        }
                    }
                    return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_itemNotFound')];
                }

            default:
                return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_invalidType')];
        }
    }

    public function updateMenuItem($postData)
    {
        $id = (int) ($postData['id'] ?? 0);
        $point = $postData['point'] ?? '';
        $type = $postData['type'] ?? '';
        $title = trim($postData['title'] ?? '');
        $icon = trim($postData['icon'] ?? '');
        $onlyAuth = !empty($postData['onlyAuth']);
        $onlyAdmin = !empty($postData['onlyAdmin']);
        $onlyAdminSite = !empty($postData['onlyAdminSite']);
        $onlyAdminServer = !empty($postData['onlyAdminServer']);
        $link = trim($postData['link'] ?? '');
        $module = trim($postData['module'] ?? '');
        $description = trim($postData['description'] ?? '');
        $blank = !empty($postData['blank']);
        $category_id = isset($postData['category_id']) ? (int) $postData['category_id'] : 0;

        if (!$id || !$type || !$title) {
            return ['error' => $this->Translate->get_translate_phrase('_incorrectData')];
        }

        if ($type === 'userbar' || $type === 'footer') {
            $file = MODULESCACHE . 'template_neo/' . ($type === 'userbar' ? 'userbar.json' : 'footer.json');
            if (!file_exists($file)) {
                return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_menuFileNotfound')];
            }

            $items = json_decode(file_get_contents($file), true);
            if (!is_array($items)) {
                return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_parsingErrorJson')];
            }
            if (empty($link) && empty($module)) {
                return ['error' => $this->Translate->get_translate_phrase('_incorrectData')];
            }

            $updated = false;

            foreach ($items as &$item) {
                if (!isset($item['id']) || (int) $item['id'] !== $id) {
                    continue;
                }

                $item['title'] = $title;
                $item['onlyAuth'] = $onlyAuth;
                $item['onlyAdmin'] = $onlyAdmin;
                $item['onlyAdminSite'] = $onlyAdminSite;
                $item['onlyAdminServer'] = $onlyAdminServer;
                $item['url'] = $link;
                $item['module'] = $module;
                $item['blank'] = $blank;

                if ($description !== '') {
                    $item['description'] = $description;
                }

                if ($type === 'userbar') {
                    if ($icon !== '') {
                        $item['icon'] = $icon;
                    }
                } elseif ($type === 'footer') {
                    if ($icon !== '') {
                        $item['icon'] = $icon;
                    }
                }

                $updated = true;
                break;
            }

            if (!$updated) {
                return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_elementNotfound')];
            }

            if (file_put_contents($file, json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
                return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorSavingFile')];
            }

            return ['success' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_elementUpdated')];
        }

        $file = MODULESCACHE . 'template_neo/menu.json';
        if (!file_exists($file))
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_menuFileNotfound')];
        $menu = json_decode(file_get_contents($file), true);
        if (!$menu)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_parsingErrorJson')];

        $updated = false;

        foreach ($menu as $k => &$item) {
            if ($type === 'category' && $item['type'] === 'category' && $item['id'] === $id) {
                $item['title'] = $title;
                $item['icon'] = $icon;
                $item['onlyAuth'] = $onlyAuth;
                $item['onlyAdmin'] = $onlyAdmin;
                $item['onlyAdminSite'] = $onlyAdminSite;
                $item['onlyAdminServer'] = $onlyAdminServer;
                $updated = true;
                break;
            }

            if ($type === 'point') {
                if ($point !== '') {
                    if ($item['type'] === 'category' && $item['id'] === $id && !empty($item['children'])) {
                        foreach ($item['children'] as &$child) {
                            if ($child['id'] == $point && $child['type'] === 'point') {
                                $child['title'] = $title;
                                $child['icon'] = $icon;
                                $child['onlyAuth'] = $onlyAuth;
                                $child['onlyAdmin'] = $onlyAdmin;
                                $child['onlyAdminSite'] = $onlyAdminSite;
                                $child['onlyAdminServer'] = $onlyAdminServer;
                                $child['url'] = $link;
                                $child['module'] = $module;
                                $child['description'] = $description;
                                $child['blank'] = $blank;
                                $updated = true;
                                break 2;
                            }
                        }
                    }
                } else {
                    if ($item['type'] === 'point' && $item['id'] === $id) {
                        $item['title'] = $title;
                        $item['icon'] = $icon;
                        $item['onlyAuth'] = $onlyAuth;
                        $item['onlyAdmin'] = $onlyAdmin;
                        $item['onlyAdminSite'] = $onlyAdminSite;
                        $item['onlyAdminServer'] = $onlyAdminServer;
                        $item['url'] = $link;
                        $item['module'] = $module;
                        $item['description'] = $description;
                        $item['blank'] = $blank;

                        if ($category_id) {
                            $moved = $item;
                            unset($menu[$k]);
                            $menu = array_values($menu);
                            $used = [];
                            $maxId = 0;
                            foreach ($menu as $_it) {
                                $iid = (int)($_it['id'] ?? 0);
                                if ($iid > 0) {
                                    $used[$iid] = 1;
                                    $maxId = max($maxId, $iid);
                                }
                                if (($_it['type'] ?? '') === 'category' && is_array($_it['children'] ?? null)) {
                                    foreach ($_it['children'] as $_ch) {
                                        $cid = (int)($_ch['id'] ?? 0);
                                        if ($cid > 0) {
                                            $used[$cid] = 1;
                                            $maxId = max($maxId, $cid);
                                        }
                                    }
                                }
                            }
                            $alloc = function () use (&$used, &$maxId) {
                                do {
                                    $maxId++;
                                } while (isset($used[$maxId]));
                                return $maxId;
                            };
                            foreach ($menu as &$cat) {
                                if ($cat['type'] === 'category' && $cat['id'] === $category_id) {
                                    if (!isset($cat['children']) || !is_array($cat['children'])) $cat['children'] = [];
                                    $nid = (int)($moved['id'] ?? 0);
                                    if ($nid <= 0 || isset($used[$nid])) {
                                        $nid = $alloc();
                                    }
                                    $moved['id'] = $nid;
                                    $moved['type'] = 'point';
                                    $cat['children'][] = $moved;
                                    break;
                                }
                            }
                            unset($cat);
                        }

                        $updated = true;
                        break;
                    }
                }
            }
        }

        if (!$updated)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_elementNotfound')];
        if (file_put_contents($file, json_encode(array_values($menu), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false)
            return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorSavingFile')];

        return ['success' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_elementUpdated')];
    }

    public function DeleteMenu($id, $type, $point = '')
    {
        $id = (int)($id ?? 0);
        $type = (string)($type ?? '');
        $pointId = ($point !== '' ? (int)$point : 0);

        if ($id <= 0) {
            return ['error' => $this->Translate->get_translate_phrase('_incorrectData')];
        }

        $write = function (string $file, array $data) {
            $saved = file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            if ($saved === false) {
                return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorWriteingFile')];
            }
            return ['success' => $this->Translate->get_translate_phrase('_Saved')];
        };

        switch ($type) {
            case 'userbar':
            case 'footer': {
                    $file = MODULESCACHE . 'template_neo/' . ($type === 'userbar' ? 'userbar.json' : 'footer.json');
                    if (!file_exists($file)) {
                        @file_put_contents($file, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                    }

                    $items = $this->readJsonArrayFile($file);
                    if (isset($items['error'])) {
                        return $items;
                    }

                    $newItems = [];
                    $deleted = false;
                    foreach ($items as $it) {
                        if ((int)($it['id'] ?? 0) === $id) {
                            $deleted = true;
                            continue;
                        }
                        $newItems[] = $it;
                    }

                    if (!$deleted) {
                        return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_elementNotfound')];
                    }

                    return $write($file, $newItems);
                }

            case 'subpoint': {
                    if ($pointId <= 0) {
                        return ['error' => $this->Translate->get_translate_phrase('_incorrectData')];
                    }

                    $file = MODULESCACHE . 'template_neo/menu.json';
                    $menu = $this->readJsonArrayFile($file);
                    if (isset($menu['error'])) {
                        return $menu;
                    }

                    $deleted = false;
                    foreach ($menu as &$item) {
                        if ((int)($item['id'] ?? 0) !== $id) {
                            continue;
                        }
                        if (!isset($item['children']) || !is_array($item['children'])) {
                            break;
                        }
                        foreach ($item['children'] as $childKey => $child) {
                            if ((int)($child['id'] ?? 0) === $pointId) {
                                unset($item['children'][$childKey]);
                                $deleted = true;
                            }
                        }
                        $item['children'] = array_values($item['children']);
                        break;
                    }
                    unset($item);

                    if (!$deleted) {
                        return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_elementNotfound')];
                    }

                    return $write($file, $menu);
                }

            case 'menu':
            default: {
                    $file = MODULESCACHE . 'template_neo/menu.json';
                    $menu = $this->readJsonArrayFile($file);
                    if (isset($menu['error'])) {
                        return $menu;
                    }

                    $newMenu = [];
                    $deleted = false;
                    foreach ($menu as $it) {
                        if ((int)($it['id'] ?? 0) === $id) {
                            $deleted = true;
                            continue;
                        }
                        $newMenu[] = $it;
                    }

                    if (!$deleted) {
                        return ['error' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_elementNotfound')];
                    }

                    return $write($file, $newMenu);
                }
        }
    }

    public function editTemplateInfo($data, $type)
    {
        $option = $this->General->get_neo_options() ?? [];
        switch ($type) {
            case 'social':
                $option['VK'] = $data['social_vk'];
                $option['TG'] = $data['social_tg'];
                $option['DS'] = $data['social_ds'];
                $option['Steam'] = $data['social_steam'];
                $option['YT'] = $data['social_yt'];
                $option['TT'] = $data['social_tt'];
                break;
            case 'info':
                $option['SiteName'] = $data['site_name'];
                $option['typeLofo'] = $data['typeLofo'] ?? 1;
                if (isset($data['filepond']) && strpos($data['filepond'], 'tmp_logo.') === 0) {
                    $directory = IMG . 'global/';
                    if (file_exists($directory)) {
                        foreach (glob($directory . 'tmp_logo.*') as $tmpFile) {
                            $extension = pathinfo($tmpFile, PATHINFO_EXTENSION);
                            $logoFile = $directory . 'logo.' . $extension;
                            if (file_exists($logoFile)) unlink($logoFile);
                            rename($tmpFile, $logoFile);
                        }
                    }
                    $option['Logo'] = 'logo.' . $extension;
                }
                break;
            case 'other':
                $option['SupportLink'] = $data['support_link'];
                $option['ContactEmail'] = $data['contact_email'];
                break;
            case 'load_logo':
                $directory = IMG . 'global/';
                if (!file_exists($directory)) mkdir($directory, 0777, true);
                if (!is_writable($directory)) {
                    exit(json_encode(['status' => 'error', 'text' => 'Папка не доступна для записи']));
                }
                $extension = pathinfo($data['name'], PATHINFO_EXTENSION);
                $fileName = 'tmp_logo.' . $extension;
                $filePath = $directory . $fileName;
                if (file_exists($filePath)) unlink($filePath);
                if (move_uploaded_file($data['tmp_name'], $filePath)) {
                    exit(json_encode(['status' => 'success', 'file' => $fileName]));
                } else {
                    exit(json_encode(['status' => 'error', 'text' => 'Не удалось переместить файл']));
                }
            case 'del_logo':
                $input = json_decode($data, true);
                if (!$input || !isset($input['file'])) {
                    exit(json_encode(['status' => 'error', 'text' => 'Нет данных для удаления']));
                }
                $directory = IMG . 'global/';
                $filePath = $directory . $input['file'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                    exit(json_encode(['status' => 'success']));
                } else {
                    exit(json_encode(['status' => 'error', 'text' => 'Файл не найден']));
                }
            case 'remove_logo':
                if (!empty($option['Logo'])) {
                    $directory = IMG . 'global/';
                    $filePath = $directory . $option['Logo'];
                    unlink($filePath);
                    $option['Logo'] = '';
                }
            case 'link_footer':

            case 'link_userbar':
        }
        file_put_contents(MODULESCACHE . 'template_neo/settings_neo.php', '<?php return ' . var_export($option, true) . ';');
        return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_saveInfo')];
    }

    public function CreateTable()
    {
        $hasMod = $this->Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_mod');
        $hasCountry = $this->Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_country');
        $hasCity = $this->Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_city');
        $hasSbId = $this->Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_sb_id');
        $hasBage = $this->Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_bage');
        if ($hasMod && $hasCountry && $hasCity && $hasSbId && $hasBage) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_tableUpdated')];
        } else {
            if (!$hasMod) $this->Db->query('Core', 0, 0, "ALTER TABLE `lvl_web_servers` ADD COLUMN `server_mod` VARCHAR(255)");
            if (!$hasCountry) $this->Db->query('Core', 0, 0, "ALTER TABLE `lvl_web_servers` ADD COLUMN `server_country` VARCHAR(255)");
            if (!$hasCity) $this->Db->query('Core', 0, 0, "ALTER TABLE `lvl_web_servers` ADD COLUMN `server_city` VARCHAR(255)");
            if (!$hasBage) $this->Db->query('Core', 0, 0, "ALTER TABLE `lvl_web_servers` ADD COLUMN `server_bage` VARCHAR(255)");
            if (!$hasSbId) $this->Db->query('Core', 0, 0, "ALTER TABLE `lvl_web_servers` ADD COLUMN `server_sb_id` VARCHAR(255) AFTER `server_sb`");
            if (!$hasMod) $this->Db->queryAll('Core', 0, 0, "UPDATE `lvl_web_servers` SET server_mod = '" . $this->Translate->get_translate_phrase('_editServer') . "'");
            return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_tableCreated')];
        }
    }

    public function CreateTableNeo3_7()
    {
        $hasGame = $this->Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_game');
        $hasStatus = $this->Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_status');
        $hasSort = $this->Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'sort');
        $hasWarnSystem = $this->Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_warnsystem');
        if ($hasGame && $hasStatus && $hasSort && !$hasWarnSystem) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_tableUpdated')];
        } else {
            if (!$hasStatus) $this->Db->query('Core', 0, 0, "ALTER TABLE `lvl_web_servers` ADD COLUMN `server_status` INT(11) NOT NULL DEFAULT '1'");
            if (!$hasGame) $this->Db->query('Core', 0, 0, "ALTER TABLE `lvl_web_servers` ADD COLUMN `server_game` VARCHAR(255) NOT NULL DEFAULT 'cs2' AFTER `server_status`");
            if (!$hasSort) $this->Db->query('Core', 0, 0, "ALTER TABLE `lvl_web_servers` ADD COLUMN `sort` INT(11) NOT NULL DEFAULT '0'");
            if ($hasWarnSystem) $this->Db->query('Core', 0, 0, "ALTER TABLE `lvl_web_servers` DROP COLUMN `server_warnsystem`");
            return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_tableCreated')];
        }
    }

    public function sortServers($order)
    {
        $servers = json_decode($order, true);
        if (!is_array($servers)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_phrase('_incorrectData')];
        }
        foreach ($servers as $item) {
            $this->Db->query('Core', 0, 0, "UPDATE `lvl_web_servers` SET `sort` = :sort WHERE `id` = :id", [
                'sort' => (int)$item['position'],
                'id' => (int)$item['id']
            ]);
        }
        return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_changeApplied')];
    }

    public function SettingsSaveHide($POST)
    {
        chmod(MODULESCACHE . 'template_neo/settings_neo.php', 0777);
        $option = require MODULESCACHE . 'template_neo/settings_neo.php';
        empty($POST['hide_filter']) ? $hide = 0 : $hide = 1;
        $option['hide_filter'] = $hide;
        file_put_contents(MODULESCACHE . 'template_neo/settings_neo.php', '<?php return ' . var_export($option, true) . ';');
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_changeApplied')];
    }

    public function SettingsSaveStretch($POST)
    {
        chmod(MODULESCACHE . 'template_neo/settings_neo.php', 0777);
        $option = require MODULESCACHE . 'template_neo/settings_neo.php';
        empty($POST['stretch_filter']) ? $stretch = 0 : $stretch = 1;
        $option['stretch_filter'] = $stretch;
        file_put_contents(MODULESCACHE . 'template_neo/settings_neo.php', '<?php return ' . var_export($option, true) . ';');
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_changeApplied')];
    }

    public function SettingsSaveHideCity($POST)
    {
        chmod(MODULESCACHE . 'template_neo/settings_neo.php', 0777);
        $option = require MODULESCACHE . 'template_neo/settings_neo.php';
        empty($POST['hide_city']) ? $stretch = 0 : $stretch = 1;
        $option['hide_city'] = $stretch;
        file_put_contents(MODULESCACHE . 'template_neo/settings_neo.php', '<?php return ' . var_export($option, true) . ';');
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_changeApplied')];
    }

    public function SettingsSaveHideCountry($POST)
    {
        chmod(MODULESCACHE . 'template_neo/settings_neo.php', 0777);
        $option = require MODULESCACHE . 'template_neo/settings_neo.php';
        empty($POST['hide_country']) ? $stretch = 0 : $stretch = 1;
        $option['hide_country'] = $stretch;
        file_put_contents(MODULESCACHE . 'template_neo/settings_neo.php', '<?php return ' . var_export($option, true) . ';');
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_changeApplied')];
    }

    private function getUsersCount()
    {
        $settings = $this->get_social();

        if (!$settings) {
            return [];
        }

        $botToken = $settings['discord']['botToken'];
        $guildId = $settings['discord']['guildId'];
        $vkToken = $settings['vk']['vkToken'];
        $groupId = $settings['vk']['groupId'];
        $telegramToken = $settings['telegram']['telegramToken'];
        $chatId = $settings['telegram']['chatId'];

        $multiCurl = curl_multi_init();
        $curlHandles = [];

        if (!empty($botToken) && !empty($guildId)) {
            $urlDiscord = "https://discord.com/api/v10/guilds/$guildId?with_counts=true";
            $chDiscord = curl_init($urlDiscord);
            curl_setopt($chDiscord, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chDiscord, CURLOPT_HTTPHEADER, [
                "Authorization: Bot $botToken",
            ]);
            curl_multi_add_handle($multiCurl, $chDiscord);
            $curlHandles['discord'] = $chDiscord;
        }

        if (!empty($vkToken) && !empty($groupId)) {
            $urlVk = "https://api.vk.com/method/groups.getById?group_id=$groupId&fields=members_count&access_token=$vkToken&v=5.131";
            $chVk = curl_init($urlVk);
            curl_setopt($chVk, CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($multiCurl, $chVk);
            $curlHandles['vk'] = $chVk;
        }

        if (!empty($telegramToken) && !empty($chatId)) {
            $urlTelegram = "https://api.telegram.org/bot$telegramToken/getChatMembersCount?chat_id=$chatId";
            $chTelegram = curl_init($urlTelegram);
            curl_setopt($chTelegram, CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($multiCurl, $chTelegram);
            $curlHandles['telegram'] = $chTelegram;
        }

        if (!empty($curlHandles)) {
            do {
                curl_multi_exec($multiCurl, $active);
                curl_multi_select($multiCurl);
            } while ($active);

            foreach ($curlHandles as $platform => $ch) {
                $response = curl_multi_getcontent($ch);
                $data = json_decode($response, true);

                switch ($platform) {
                    case 'discord':
                        $memberCount = $data['approximate_member_count'] ?? 0;
                        break;
                    case 'vk':
                        $memberCount = $data['response'][0]['members_count'] ?? 0;
                        break;
                    case 'telegram':
                        $memberCount = isset($data['ok']) && $data['ok'] === true ? $data['result'] : 0;
                        break;
                    default:
                        $memberCount = 0;
                        break;
                }

                $result[$platform] = $memberCount;
                curl_multi_remove_handle($multiCurl, $ch);
            }
        }
        curl_multi_close($multiCurl);

        return $result;
    }

    public function InfoStatsCached()
    {
        $stats = [
            'CountPlayers' => 0,
            'CountAdmins' => 0,
            'CountBans' => 0,
            'CountBans7d' => 0,
            'CountMutes' => 0,
            'CountMutes7d' => 0,
            'CountVip' => 0,
            'Money' => 0,
            'MoneyMonth' => [],
            'CountReports' => 0,
            'CountReports7d' => 0,
            'CountVerifications' => 0,
            'CountRequests' => 0,
            'CountRequests7d' => 0,
            'CountCheckCheats' => 0,
            'CountCheckCheats7d' => 0,
            'CountDiscord' => 0,
            'CountVk' => 0,
            'CountTelegram' => 0,
            'Time' => time() + 10800,
        ];

        $data = $this->Modules->get_module_cache('module_page_adminpanel', 'stats');
        if (time() > ($data['Time'] ?? 0)) {
            for ($i = 0; $i < $this->Db->table_count['LevelsRanks']; $i++) {
                $stats['CountPlayers'] += $this->Db->queryNum('LevelsRanks', $this->Db->db_data['LevelsRanks'][$i]['USER_ID'], $this->Db->db_data['LevelsRanks'][$i]['DB_num'], 'SELECT COUNT(*) FROM ' . $this->Db->db_data['LevelsRanks'][$i]['Table'] . ' LIMIT 1')[0];
            }
            if (!empty($this->Db->db_data['lk'])) {
                $stats['Money'] = $this->Db->query('lk', 0, 0, "SELECT SUM(`pay_summ`) AS `cash_summ` FROM `lk_pays` WHERE `pay_status` = 1 AND `pay_system` != 'admin'");
                $stats['MoneyMonth'] = $this->Db->query('lk', 0, 0, "SELECT
                    SUM(CASE
                            WHEN MONTH(STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s')) = MONTH(NOW())
                                AND YEAR(STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s')) = YEAR(NOW())
                                AND `pay_status` = 1 AND `pay_system` != 'admin'
                            THEN `pay_summ`
                            ELSE 0
                        END) AS `current_month`,
                    SUM(CASE
                            WHEN (MONTH(STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s')) = MONTH(NOW()) - 1
                                AND (YEAR(STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s')) = YEAR(NOW())) OR (MONTH(NOW()) = 1 AND MONTH(STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s')) = 12)
                                AND YEAR(STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s')) = YEAR(NOW()) - 1)
                            AND `pay_status` = 1 AND `pay_system` != 'admin'
                        THEN `pay_summ`
                        ELSE 0
                    END) AS `prev_month`,
                    SUM(CASE
                            WHEN STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s') BETWEEN DATE_SUB(NOW(), INTERVAL 3 MONTH) AND NOW()
                                AND `pay_status` = 1 AND `pay_system` != 'admin'
                        THEN `pay_summ`
                        ELSE 0
                    END) AS `3months`,
                    SUM(CASE
                            WHEN STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s') BETWEEN DATE_SUB(NOW(), INTERVAL 6 MONTH) AND NOW()
                                AND `pay_status` = 1 AND `pay_system` != 'admin'
                        THEN `pay_summ`
                        ELSE 0
                    END) AS `6months`
                FROM `lk_pays`
                ");
            }
            if (!empty($this->Db->db_data['IksAdmin'])) {
                for ($i = 0; $i < $this->Db->table_count['IksAdmin']; $i++) {
                    $countBanMutes = $this->Db->query(
                        'IksAdmin',
                        $this->Db->db_data['IksAdmin'][$i]['USER_ID'],
                        $this->Db->db_data['IksAdmin'][$i]['DB_num'],
                        "SELECT
                            (SELECT COUNT(*) FROM `iks_admins` LIMIT 1) AS count_admins,
                            (SELECT COUNT(*) FROM `iks_mutes` LIMIT 1) AS count_mutes,
                            (SELECT COUNT(*) FROM `iks_gags` LIMIT 1) AS count_gags,
                            (SELECT COUNT(*) FROM `iks_mutes` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1) AS count_mutes_7d,
                            (SELECT COUNT(*) FROM `iks_gags` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1) AS count_gags_7d,
                            (SELECT COUNT(*) FROM `iks_bans` LIMIT 1) AS count_bans,
                            (SELECT COUNT(*) FROM `iks_bans` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1) AS count_bans_7d"
                    );
                    $stats['CountMutes'] += $countBanMutes['count_mutes'] + $countBanMutes['count_gags'];
                    $stats['CountMutes7d'] += $countBanMutes['count_mutes_7d'] + $countBanMutes['count_gags_7d'];
                    $stats['CountAdmins'] += $countBanMutes['count_admins'];
                    $stats['CountBans'] += $countBanMutes['count_bans'];
                    $stats['CountBans7d'] += $countBanMutes['count_bans_7d'];
                }
            }
            if (!empty($this->Db->db_data['IksAdminNew'])) {
                for ($i = 0; $i < $this->Db->table_count['IksAdminNew']; $i++) {
                    $countBanMutes = $this->Db->query(
                        'IksAdminNew',
                        $this->Db->db_data['IksAdminNew'][$i]['USER_ID'],
                        $this->Db->db_data['IksAdminNew'][$i]['DB_num'],
                        "SELECT
                            (SELECT COUNT(DISTINCT a.steam_id) FROM `iks_admins` a JOIN `iks_admin_to_server` s ON a.`id` = s.`admin_id` WHERE (a.`end_at` > UNIX_TIMESTAMP() OR a.`end_at` IS NULL) AND a.`is_disabled` = 0 AND a.`id` != 1) AS count_admins,
                            (SELECT COUNT(*) FROM `iks_comms` WHERE (`mute_type` = 0 OR `mute_type` = 2) LIMIT 1) AS count_mutes,
                            (SELECT COUNT(*) FROM `iks_comms` WHERE (`mute_type` = 1 OR `mute_type` = 2) LIMIT 1) AS count_gags,
                            (SELECT COUNT(*) FROM `iks_bans` LIMIT 1) AS count_bans,
                            (SELECT COUNT(*) FROM `iks_comms` WHERE (`mute_type` = 0 OR `mute_type` = 2) AND `created_at` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1) AS count_mutes_7d,
                            (SELECT COUNT(*) FROM `iks_comms` WHERE (`mute_type` = 1 OR `mute_type` = 2) AND `created_at` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1) AS count_gags_7d,
                            (SELECT COUNT(*) FROM `iks_bans` WHERE `created_at` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1) AS count_bans_7d"
                    );
                    $stats['CountMutes'] += $countBanMutes['count_mutes'] + $countBanMutes['count_gags'];
                    $stats['CountAdmins'] += $countBanMutes['count_admins'];
                    $stats['CountBans'] += $countBanMutes['count_bans'];
                    $stats['CountMutes7d'] += $countBanMutes['count_mutes_7d'] + $countBanMutes['count_gags_7d'];
                    $stats['CountBans7d'] += $countBanMutes['count_bans_7d'];
                }
            }
            if (!empty($this->Db->db_data['AdminSystem'])) {
                for ($i = 0; $i < $this->Db->table_count['AdminSystem']; $i++) {
                    $countBanMutes = $this->Db->query(
                        'AdminSystem',
                        $this->Db->db_data['AdminSystem'][$i]['USER_ID'],
                        $this->Db->db_data['AdminSystem'][$i]['DB_num'],
                        "SELECT
                            (SELECT COUNT(DISTINCT a.steamid) FROM `as_admins` a JOIN `as_admins_servers` s ON a.`id` = s.`admin_id` WHERE (s.`expires` > UNIX_TIMESTAMP() OR s.`expires` = 0) AND a.`id` != 1 ) AS count_admins,
                            (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = '1' LIMIT 1) AS count_mutes,
                            (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = '2' LIMIT 1) AS count_gags,
                            (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = '3' LIMIT 1) AS count_silence,
                            (SELECT COUNT(*) FROM `as_punishments` WHERE `punish_type` = '0' LIMIT 1) AS count_bans,
                            (SELECT COUNT(*) FROM `as_punishments` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) AND `punish_type` = '1' LIMIT 1) AS count_mutes_7d,
                            (SELECT COUNT(*) FROM `as_punishments` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) AND `punish_type` = '2' LIMIT 1) AS count_gags_7d,
                            (SELECT COUNT(*) FROM `as_punishments` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) AND `punish_type` = '3' LIMIT 1) AS count_silence_7d,
                            (SELECT COUNT(*) FROM `as_punishments` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) AND `punish_type` = '0' LIMIT 1) AS count_bans_7d"
                    );
                    $stats['CountMutes'] += $countBanMutes['count_mutes'] + $countBanMutes['count_gags'] + $countBanMutes['count_silence'];
                    $stats['CountMutes7d'] += $countBanMutes['count_mutes_7d'] + $countBanMutes['count_gags_7d'] + $countBanMutes['count_silence_7d'];
                    $stats['CountAdmins'] += $countBanMutes['count_admins'];
                    $stats['CountBans'] += $countBanMutes['count_bans'];
                    $stats['CountBans7d'] += $countBanMutes['count_bans_7d'];
                }
            }
            if (!empty($this->Db->db_data['SourceBans'])) {
                for ($i = 0; $i < $this->Db->table_count['SourceBans']; $i++) {
                    $countBanMutes = $this->Db->query(
                        'SourceBans',
                        $this->Db->db_data['SourceBans'][$i]['USER_ID'],
                        $this->Db->db_data['SourceBans'][$i]['DB_num'],
                        "SELECT
                            (SELECT COUNT(DISTINCT a.authid) FROM `sb_admins` a JOIN `sb_admins_servers_groups` s ON a.`aid` = s.`admin_id` WHERE (a.`expired` > UNIX_TIMESTAMP() OR a.`expired` = 0) AND a.`aid` != 0 ) AS count_admins,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `type` = '1' LIMIT 1) AS count_mutes,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `type` = '2' LIMIT 1) AS count_gags,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `type` = '3' LIMIT 1) AS count_silence,
                            (SELECT COUNT(*) FROM `sb_bans` LIMIT 1) AS count_bans,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) AND `type` = '1' LIMIT 1) AS count_mutes_7d,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) AND `type` = '2' LIMIT 1) AS count_gags_7d,
                            (SELECT COUNT(*) FROM `sb_comms` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) AND `type` = '3' LIMIT 1) AS count_silence_7d,
                            (SELECT COUNT(*) FROM `sb_bans` WHERE `created` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1) AS count_bans_7d"
                    );
                    $stats['CountMutes'] += $countBanMutes['count_mutes'] + $countBanMutes['count_gags'] + $countBanMutes['count_silence'];
                    $stats['CountMutes7d'] += $countBanMutes['count_mutes_7d'] + $countBanMutes['count_gags_7d'] + $countBanMutes['count_silence_7d'];
                    $stats['CountAdmins'] += $countBanMutes['count_admins'];
                    $stats['CountBans'] += $countBanMutes['count_bans'];
                    $stats['CountBans7d'] += $countBanMutes['count_bans_7d'];
                }
            }
            if (!empty($this->Db->db_data['Vips'])) {
                for ($i = 0; $i < $this->Db->table_count['Vips']; $i++) {
                    $stats['CountVip'] += $this->Db->queryNum('Vips', $this->Db->db_data['Vips'][$i]['USER_ID'], $this->Db->db_data['Vips'][$i]['DB_num'], "SELECT COUNT(*) FROM `vip_users` LIMIT 1")[0];
                }
            }
            if (!empty($this->Db->db_data['Reports'])) {
                $stats['CountReports'] += $this->Db->queryNum('Reports', 0, 0, "SELECT COUNT(*) FROM `rs_reports` LIMIT 1")[0];
                $stats['CountReports7d'] += $this->Db->queryNum('Reports', 0, 0, "SELECT COUNT(*) FROM `rs_reports` WHERE `time` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1")[0];
            }
            if ($this->Db->mysql_table_search('Core', 0, 0, 'verification_users')) {
                $stats['CountVerifications'] += $this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `verification_users` LIMIT 1")[0];
            }
            if (!empty($this->Db->db_data['Check'])) {
                $stats['CountCheckCheats'] += $this->Db->queryNum('Check', 0, 0, "SELECT COUNT(*) FROM `checkcheats_stats` LIMIT 1")[0];
                $stats['CountCheckCheats7d'] += $this->Db->queryNum('Check', 0, 0, "SELECT COUNT(*) FROM `checkcheats_stats` WHERE `datestart` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1")[0];
            }
            if (!empty($this->Db->db_data['request'])) {
                $stats['CountRequests'] += $this->Db->queryNum('request', 0, 0, "SELECT COUNT(*) FROM `lvl_web_request_list` LIMIT 1")[0];
                $stats['CountRequests7d'] += $this->Db->queryNum('request', 0, 0, "SELECT COUNT(*) FROM `lvl_web_request_list` WHERE `date` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1")[0];
            }
            if (file_exists(MODULES . 'module_page_tickets/description.json')) {
                $stats['CountTickets'] += $this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `neo_tickets_list` LIMIT 1")[0];
                $stats['CountTickets7d'] += $this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `neo_tickets_list` WHERE `created_at` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1")[0];
            }
            $userCounts = $this->getUsersCount();
            $stats['CountDiscord'] = $userCounts['discord'] ?? 0;
            $stats['CountVk'] = $userCounts['vk'] ?? 0;
            $stats['CountTelegram'] = $userCounts['telegram'] ?? 0;

            $this->Modules->set_module_cache('module_page_adminpanel', $stats, 'stats');
        }

        return $data;
    }

    public function InfoStats()
    {
        $stats = [
            'CountPlayers24' => 0,
            'CountPlayers7d' => 0,
            'CountNewPlayers' => 0,
            'CountNewPlayers7d' => 0,
            'LastPay' => [],
            'CountVisit' => 0,
            'LastShopPay' => [],
            'VisitsUsers' => [],
            'VisitsUsersCount' => 0,
        ];

        for ($i = 0; $i < $this->Db->table_count['LevelsRanks']; $i++) {
            $stats['CountPlayers24'] += $this->Db->queryNum('LevelsRanks', $this->Db->db_data['LevelsRanks'][$i]['USER_ID'], $this->Db->db_data['LevelsRanks'][$i]['DB_num'], 'SELECT COUNT(*) FROM ' . $this->Db->db_data['LevelsRanks'][$i]['Table'] . ' WHERE `lastconnect` >= UNIX_TIMESTAMP(CURDATE()) LIMIT 1')[0];
        }
        for ($i = 0; $i < $this->Db->table_count['LevelsRanks']; $i++) {
            $stats['CountPlayers7d'] += $this->Db->queryNum('LevelsRanks', $this->Db->db_data['LevelsRanks'][$i]['USER_ID'], $this->Db->db_data['LevelsRanks'][$i]['DB_num'], 'SELECT COUNT(*) FROM ' . $this->Db->db_data['LevelsRanks'][$i]['Table'] . ' WHERE `lastconnect` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1')[0];
        }
        if (!empty($this->Db->db_data['lk'])) {
            $stats['LastPay'] = $this->Db->queryAll('lk', 0, 0, "SELECT `pay_order`, `pay_auth`, `pay_summ`, `pay_data`, pay_system, `pay_status` FROM `lk_pays` WHERE `pay_status` = 1 AND NOT `pay_system` = 'admin' ORDER BY `pay_id` DESC LIMIT 20");
        }
        if (file_exists(MODULES . 'module_page_store/description.json')) {
            $shop = [];
            if ($this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_logs") && $this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_cw_logs") && $this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_models_logs")):
                $shop = $this->Db->queryAll('Core', 0, 0, "SELECT `id`, `steam`, `title`, `date`, `status`
                FROM `lvl_web_shop_logs`
                WHERE `status` = 1
                UNION ALL
                SELECT `id`, `steam`, `title`, `date`, `status`
                FROM `lvl_web_shop_cw_logs`
                WHERE `status` = 1
                UNION ALL
                SELECT `id`, `steam`, `title`, `date`, `status`
                FROM `lvl_web_shop_models_logs`
                WHERE `status` = 1
                ORDER BY `date` DESC LIMIT 20;");
            elseif ($this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_logs") && $this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_cw_logs")):
                $shop = $this->Db->queryAll('Core', 0, 0, "SELECT `id`, `steam`, `title`, `date`, `status`
                FROM `lvl_web_shop_logs`
                WHERE `status` = 1
                UNION ALL
                SELECT `id`, `steam`, `title`, `date`, `status`
                FROM `lvl_web_shop_cw_logs`
                WHERE `status` = 1 ORDER BY date DESC LIMIT 20;");
            elseif ($this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_logs")):
                $shop = $this->Db->queryAll('Core', 0, 0, "SELECT `id`, `steam`, `title`, `date`, `status`
                    FROM `lvl_web_shop_logs`
                    WHERE `status` = 1 ORDER BY date DESC LIMIT 20");
            endif;
            $stats['LastShopPay'] = $shop;
        }
        $stats['CountVisit'] = $this->Db->query('Core', 0, 0, "SELECT * FROM `lr_web_attendance` WHERE `date` = " . date('m.Y', time()) . "");
        if (file_exists(MODULES . 'module_page_store/cache/logs_cache.php')) {
            $stats['LastShopPay'] = array_reverse(array_slice(require MODULES . 'module_page_store/cache/logs_cache.php', -10, 10));
        }
        $stats['VisitsUsers'] = $this->Db->queryAll('Core', 0, 0, "SELECT `user`, `ip`, `time` FROM `lr_web_online` ORDER BY CASE WHEN `user` != 'guest' THEN 0 ELSE 1 END");
        $stats['VisitsUsersCount'] = $this->Db->queryNum('Core', 0, 0, "SELECT COUNT(*) FROM `lr_web_online` LIMIT 1")[0];
        if (!empty($this->Db->db_data['NewPlayers'])) {
            $stats['CountNewPlayers'] += $this->Db->queryNum('NewPlayers', 0, 0, "SELECT COUNT(*) FROM `new_players` WHERE `connect` >= UNIX_TIMESTAMP(CURDATE()) LIMIT 1")[0];
            $stats['CountNewPlayers7d'] += $this->Db->queryNum('NewPlayers', 0, 0, "SELECT COUNT(*) FROM `new_players` WHERE `connect` >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY) LIMIT 1")[0];
        }
        return $stats;
    }

    private function checkUser($user)
    {
        switch ($user) {
            case 'guest':
                $html = <<<HTML
                    <span class="visit-users__line"><svg><use href="/resources/img/sprite.svg#steam"></use></svg>STEAMID: {$this->Translate->get_translate_phrase('_Unknown')}</span>
                HTML;
                $name = $this->Translate->get_translate_phrase('_guest');
                $type = $this->Translate->get_translate_phrase('_guest');
                return [$name, $type, $html];
            case 'Bot':
                $html = <<<HTML
                    <span class="visit-users__line"><svg><use href="/resources/img/sprite.svg#steam"></use></svg>STEAMID: {$this->Translate->get_translate_phrase('_Unknown')}</span>
                HTML;
                $name = $this->Translate->get_translate_phrase('_searchBot');
                $type = $this->Translate->get_translate_phrase('_searchBot');
                return [$name, $type, $html];
            default:
                $html = <<<HTML
                    <span class="visit-users__line"><svg><use href="/resources/img/sprite.svg#steam"></use></svg>STEAMID: {$user} <svg class="svg-hover copy-btn" data-clipboard-text="{$user}"><use href="/resources/img/sprite.svg#copy"></use></svg></span>
                HTML;
                $name = $this->General->checkName($user);
                $type = $this->Translate->get_translate_phrase('_Player');
                return [$name, $type, $html];
        }
    }

    private function checkOS($user_agent)
    {
        $user_agent = strtolower($user_agent);
        if (strpos($user_agent, 'iphone') !== false || strpos($user_agent, 'ipad') !== false) {
            return 'iOS';
        } elseif (strpos($user_agent, 'windows') !== false) {
            return 'Windows';
        } elseif (strpos($user_agent, 'mac os') !== false || strpos($user_agent, 'os x') !== false) {
            return 'macOS';
        } elseif (strpos($user_agent, 'linux') !== false) {
            return 'Linux';
        } elseif (strpos($user_agent, 'android') !== false) {
            return 'Android';
        } else {
            return 'Unknown';
        }
    }

    private function checkBrowser($user_agent)
    {
        $user_agent = strtolower($user_agent);

        if (strpos($user_agent, 'edg/') !== false || strpos($user_agent, 'edge') !== false) {
            return 'Edge';
        } elseif (strpos($user_agent, 'opr/') !== false || strpos($user_agent, 'opera') !== false) {
            return 'Opera';
        } elseif (strpos($user_agent, 'yabrowser') !== false) {
            return 'Yandex Browser';
        } elseif (strpos($user_agent, 'firefox') !== false) {
            return 'Firefox';
        } elseif ((strpos($user_agent, 'safari') !== false) && (strpos($user_agent, 'chrome') === false) && (strpos($user_agent, 'chromium') === false)) {
            return 'Safari';
        } elseif (strpos($user_agent, 'msie') !== false || strpos($user_agent, 'trident') !== false) {
            return 'Internet Explorer';
        } elseif (strpos($user_agent, 'chrome') !== false || strpos($user_agent, 'chromium') !== false) {
            return 'Chrome';
        } else {
            return 'Unknown';
        }
    }

    public function updateOnline()
    {
        $online = $this->Db->queryAll('Core', 0, 0, "SELECT * FROM `lr_web_online` ORDER BY CASE WHEN `user` NOT IN ('Bot', 'guest') THEN 0 ELSE 1 END");
        $users = '';
        foreach ($online as $key) {
            $steam = $key['user'];
            $avatar = $this->General->getAvatar($steam, 3);
            $check = $this->checkUser($key['user']);
            $name = $check[0];
            $type = $check[1];
            $html = $check[2];
            $ip = action_text_clear($key['ip']);
            $date = date('H:i, d.m.Y ', strtotime($key['time']));
            $os = $this->checkOS($key['user_agent']);
            $browser = $this->checkBrowser($key['user_agent']);
            $users .= <<<HTML
                <div class="visit-users__card" style="height: 39px;">
                    <header class="visit-users__header">
                        <img src="{$avatar}" id="avatar" avatarid="{$steam}">
                        <h4 class="visit-users__nick" id="name" nameid="{$steam}">{$name}</h4>
                        <button class="visit-users__button"><svg><use href="/resources/img/sprite.svg#plus"></use></svg></button>
                    </header>
                    <div class="visit-users__content">
                        <span class="visit-users__line"><svg><use href="/resources/img/sprite.svg#geo"></use></svg>IP: <span Class="hide-hover">{$ip}</span> <svg class="svg-hover copy-btn" data-clipboard-text="{$ip}"><use href="/resources/img/sprite.svg#copy"></use></svg></span>
                        {$html}
                        <span class="visit-users__line"><svg><use href="/resources/img/sprite.svg#time"></use></svg>{$this->Translate->get_translate_phrase('_lstActivity')} {$date}</span>
                        <span class="visit-users__line"><svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>{$this->Translate->get_translate_phrase('_Type')}: {$type}</span>
                        <span class="visit-users__line"><svg><use href="/resources/img/sprite.svg#browser"></use></svg>{$this->Translate->get_translate_phrase('_browser')}: {$browser}, OS: {$os}</span>
                    </div>
                </div>
            HTML;
        }

        return ['online_site' => count($online), 'online_users' => $users];
    }

    public function DelAllLogsWeb()
    {
        $baseDir = realpath(__DIR__ . '/../../../logs');
        if ($baseDir === false || !is_dir($baseDir)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_folderNotExist')];
        }
        @chmod($baseDir, 0777);

        $files = glob($baseDir . DIRECTORY_SEPARATOR . '*');
        if ($files === false) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_failedToGetLogs')];
        }

        $deleted = 0;
        foreach ($files as $file) {
            $name = basename($file);
            if (is_file($file) && preg_match('/^\d{2}-\d{2}-\d{4}\.(txt|log)$/i', $name)) {
                @unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_allLogsDeleted')];
        }
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_failedToGetLogs')];
    }

    public function DelLogWeb($POST)
    {
        $baseDir = realpath(__DIR__ . '/../../../logs');
        if ($baseDir === false || !is_dir($baseDir)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_folderNotExist')];
        }
        @chmod($baseDir, 0777);

        if (!isset($POST['id'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_fileNameNotReceived')];
        }

        $id = trim((string)$POST['id']);
        if ($id === '' || strpos($id, '/') !== false || strpos($id, '\\') !== false || strpos($id, '..') !== false) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_fileNameNotReceived')];
        }
        if (!preg_match('/^\d{2}-\d{2}-\d{4}\.(txt|log)$/i', $id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorDeletingLog')];
        }

        $target = $baseDir . DIRECTORY_SEPARATOR . $id;
        $targetReal = realpath($target);
        if ($targetReal === false || !is_file($targetReal) || strncmp($targetReal, $baseDir . DIRECTORY_SEPARATOR, strlen($baseDir) + 1) !== 0) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorDeletingLog')];
        }

        if (@unlink($targetReal)) {
            return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_log') . $id . $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_successfullyDeletedLog')];
        }
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_errorDeletingLog') . $id];
    }

    public function ShopLogs()
    {
        if ($this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_logs") && $this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_cw_logs") && $this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_models_logs")):
            return $this->Db->queryAll('Core', 0, 0, "SELECT `id`, `steam`, `title`, `date`, `status`
            FROM `lvl_web_shop_logs`
            WHERE `status` = 1
            UNION ALL
            SELECT `id`, `steam`, `title`, `date`, `status`
            FROM `lvl_web_shop_cw_logs`
            WHERE `status` = 1
            UNION ALL
            SELECT `id`, `steam`, `title`, `date`, `status`
            FROM `lvl_web_shop_models_logs`
            WHERE `status` = 1
            ORDER BY `date` DESC;");
        elseif ($this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_logs") && $this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_cw_logs")):
            return $this->Db->queryAll('Core', 0, 0, "SELECT `id`, `steam`, `title`, `date`, `status`
            FROM `lvl_web_shop_logs`
            WHERE `status` = 1
            UNION ALL
            SELECT `id`, `steam`, `title`, `date`, `status`
            FROM `lvl_web_shop_cw_logs`
            WHERE `status` = 1 ORDER BY date DESC;");
        elseif ($this->Db->mysql_table_search('Core', 0, 0, "lvl_web_shop_logs")):
            return $this->Db->queryAll('Core', 0, 0, "SELECT `id`, `steam`, `title`, `date`, `status`
                FROM `lvl_web_shop_logs`
                WHERE `status` = 1");
        endif;
    }

    public function LkLogs()
    {
        $alllogs = $this->Db->queryAll('lk', 0, 0, "SELECT DISTINCT log_name FROM lk_logs");
        return array_reverse($alllogs);
    }

    public function LkLogContent($log)
    {
        if (!preg_match('/^[0-9]{2}\_[0-9]{2}\_[0-9]{4}+$/i', $log)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_fileNameNotReceived')];
        }
        $param = ['log_name' => $log];
        $contentLog = $this->Db->queryAll('lk', 0, 0, "SELECT * FROM lk_logs WHERE log_name = :log_name", $param);
        if (!empty($contentLog)) {
            return $contentLog;
        } else {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_logNotFound')];
        }
    }

    public function LkLogdelete($log)
    {
        if (!preg_match('/^[0-9]{2}\_[0-9]{2}\_[0-9]{4}+$/i', $log['id'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_fileNameNotReceived')];
        }
        $param = ['log_name' => $log['id']];
        $this->Db->query('lk', 0, 0, "DELETE FROM lk_logs WHERE log_name = :log_name", $param);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_log') . $log['id'] . $this->Translate->get_translate_phrase('_successfullyDeleted')];
    }

    public function LkCleanLogs()
    {
        $this->Db->query('lk', 0, 0, "DELETE FROM lk_logs WHERE log_name");
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_allLogsDeleted')];
    }

    public function getMonthName($Month)
    {
        switch ($Month) {
            case 1:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_Jan');
            case 2:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_Feb');
            case 3:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_Mar');
            case 4:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_Apr');
            case 5:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_May');
            case 6:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_Jun');
            case 7:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_Jul');
            case 8:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_Aug');
            case 9:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_Sep');
            case 10:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_Oct');
            case 11:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_Nov');
            case 12:
                return $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_Dec');
            default:
        }
    }

    function getAdmins()
    {
        return $this->Db->queryAll('Core', 0, 0, "SELECT * FROM `lvl_web_admins`");
    }

    function getAdmin($steam)
    {
        return $this->Db->query('Core', 0, 0, "SELECT * FROM `lvl_web_admins` WHERE `steamid` = :steamid", ['steamid' => $steam]);
    }

    function addAdmin($post)
    {
        if (empty($post['admin_name'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_usernameEmpty')];
        } else if (empty($post['admin_steamid64'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_emptySteamId')];
        } else if (empty($post['admin_access'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_accessEmpty')];
        } else if (empty($post['admin_flags'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_flagsEmpty')];
        } else if (isset($this->getAdmin($post['admin_steamid64'])['steamid'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_adminAlreadyExist')];
        }

        $this->Db->query('Core', 0, 0, "INSERT INTO `lvl_web_admins` ( `steamid`, `group`, `access`, `flags`) VALUES ( :steamid, 1, :access, :flags)", [
            'steamid' => $post['admin_steamid64'],
            'access' => $post['admin_access'],
            'flags' => $post['admin_flags']
        ]);

        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_adminAdded')];
    }

    function delAdmin($steamid)
    {
        $this->Db->query('Core', 0, 0, "DELETE FROM `lvl_web_admins` WHERE `steamid` = :steamid", [
            'steamid' => $steamid
        ]);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_adminDeleted')];
    }

    function getRoleIndex($array, $index)
    {
        $ids = array_column($array, 'id');
        $index = array_search($index, $ids);
        return $index;
    }

    function getRoles()
    {
        $jsonFile = SESSIONS . '/roles.json';

        if (file_exists($jsonFile)) {
            $json_data = file_get_contents($jsonFile);
            return json_decode($json_data, true);
        } else {
            return [];
        }
    }

    function addRole($post)
    {
        $jsonFile = SESSIONS . '/roles.json';
        $roles = $this->getRoles();
        $newId = 1;
        if (!empty($roles)) {
            $ids = array_column($roles, 'id');
            $newId = max($ids) + 1;
        }

        if (empty($post['add_role'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_TitleEmpty')];
        } else if (empty($post['add_role_color'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_emptyColor')];
        }

        $newRole = [
            'id' => $newId,
            'role' => $post['add_role'],
            'color' => $post['add_role_color'],
            'users' => []
        ];

        $roles[] = $newRole;
        $jsonData = json_encode($roles, JSON_PRETTY_PRINT);
        file_put_contents($jsonFile, $jsonData);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_roleAdded')];
    }

    function addUserRole($post)
    {
        $jsonFile = SESSIONS . '/roles.json';
        $roles = $this->getRoles();

        if (empty($post['steamid64'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_emptySteamId')];
        } else if (empty($post['role_id'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_emptyRole')];
        }

        $role_index = $this->getRoleIndex($roles, $post['role_id']);

        if (in_array($post['steamid64'], $roles[$role_index]['users'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_alreadyHasRole')];
        }

        $roles[$role_index]['users'][] = $post['steamid64'];
        $jsonData = json_encode($roles, JSON_PRETTY_PRINT);
        file_put_contents($jsonFile, $jsonData);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_roleWasIssued')];
    }

    function delRole($roleId)
    {
        $jsonFile = SESSIONS . '/roles.json';
        $roles = $this->getRoles();

        $found = false;
        foreach ($roles as $index => $role) {
            if ($role['id'] == $roleId) {
                array_splice($roles, $index, 1);
                $found = true;
                break;
            }
        }

        if (!$found) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_roleNotFound')];
        }

        $jsonData = json_encode($roles, JSON_PRETTY_PRINT);
        file_put_contents($jsonFile, $jsonData);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_roleDeleted')];
    }

    function delUserRole($steam, $roleId)
    {
        $jsonFile = SESSIONS . '/roles.json';
        $roles = $this->getRoles();

        foreach ($roles as &$role) {
            if ($role['id'] == $roleId) {
                $key = array_search($steam, $role['users']);
                if ($key !== false) {
                    unset($role['users'][$key]);
                    $role['users'] = array_values($role['users']);
                    $jsonData = json_encode($roles, JSON_PRETTY_PRINT);
                    file_put_contents($jsonFile, $jsonData);
                    return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_roleForUser') . $steam . $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_roleSuccessfullRemoved')];
                } else {
                    return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_userSteamId') . $steam . $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_notHaveSuchRole')];
                }
            }
        }
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_roleNotFound')];
    }

    function getBlocks()
    {
        $jsonFile = SESSIONS . '/blockedusers.json';

        if (file_exists($jsonFile)) {
            $json_data = file_get_contents($jsonFile);
            return json_decode($json_data, true);
        } else {
            file_put_contents($jsonFile, json_encode([]));
            return [];
        }
    }

    function addBlock($post)
    {
        $jsonFile = SESSIONS . '/blockedusers.json';
        $blocks = $this->getBlocks();
        $newId = 1;
        if (!empty($blocks)) {
            $ids = array_column($blocks, 'id');
            $newId = max($ids) + 1;
        }

        if (empty($post['ban_steam'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_emptySteamId')];
        } else if (empty($post['ban_reason'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_emptyReason')];
        }

        foreach ($blocks as $block) {
            if ($block['steam'] == $post['ban_steam']) {
                return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_userAlreadyBanned')];
            }
        }

        $newBan = [
            'id' => $newId,
            'ip' => $post['ban_ip'],
            'steam' => $post['ban_steam'],
            'reason' => $post['ban_reason']
        ];

        $blocks[] = $newBan;
        $jsonData = json_encode($blocks, JSON_PRETTY_PRINT);
        file_put_contents($jsonFile, $jsonData);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_userAlreadyBanned')];
    }

    function delBlock($id)
    {
        $jsonFile = SESSIONS . '/blockedusers.json';
        $blocks = $this->getBlocks();

        foreach ($blocks as $index => $block) {
            if ($block['id'] == $id) {
                unset($blocks[$index]);
                $jsonData = json_encode($blocks, JSON_PRETTY_PRINT);
                file_put_contents($jsonFile, $jsonData);
                return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_userUnbanned')];
            }
        }
        return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_adminpanel', '_unserNotBanned')];
    }
    public function getSprites()
    {
        $sprites = RESOURCES . '/img/sprite.svg';
        $iconIds = [];
        if (file_exists($sprites)) {
            $svgContent = @file_get_contents($sprites);
            if ($svgContent !== false) {
                if (preg_match_all('/<symbol\s+[^>]*id="([^"]+)"/i', $svgContent, $match)) {
                    $iconIds = array_map(function ($id) {
                        return htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
                    }, $match[1]);
                }
            }
        }
        sort($iconIds, SORT_NATURAL | SORT_FLAG_CASE);
        return $iconIds;
    }
}
