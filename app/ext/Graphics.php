<?php

namespace app\ext;

class Graphics
{
    public    $Translate;
    public    $General;
    public    $Modules;
    public    $Db;
    public    $Auth;
    public    $Notifications;
    public    $Router;

    function __construct($Translate, $General, $Modules, $Db, $Auth, $Notifications, $Router)
    {

        defined('IN_LR') != true && die();

        $Graphics            = $this;
        $this->Translate     = $Translate;
        $this->General       = $General;
        $this->Modules       = $Modules;
        $this->Db            = $Db;
        $this->Auth          = $Auth;
        $this->Notifications = $Notifications;
        $this->Router        = $Router;

        if (!empty($_SESSION['steamid']) || isset($_POST)) : (empty($Modules->arr_module_init['page'][$Modules->route]) && !isset($_GET['auth'])) && get_iframe(404, $this->Translate->get_translate_phrase('_Page_not_found')) && die();
            for ($module_id = 0, $c_mi = sizeof($Modules->arr_module_init['page'][$Modules->route]['data']); $module_id < $c_mi; $module_id++) :
                $file = MODULES . $Modules->arr_module_init['page'][$Modules->route]['data'][$module_id] . '/forward/data.php';
                file_exists($file) && require $file;
            endfor;

            if (!empty($Modules->arr_module_init['data_always'])) :
                for ($module_id = 0, $c_mi = sizeof($Modules->arr_module_init['data_always']); $module_id < $c_mi; $module_id++) :
                    $file = MODULES . $Modules->arr_module_init['data_always'][$module_id] . '/forward/data_always.php';
                    file_exists($file) && require $file;
                endfor;
            endif;
        endif;

        require PAGE . 'head.php';

        (file_exists(TEMPLATES . $General->arr_general['theme'] . '/interface/head.php')) && require TEMPLATES . $General->arr_general['theme'] . '/interface/head.php';

        (file_exists(TEMPLATES . $General->arr_general['theme'] . '/interface/sidebar.php')) && require TEMPLATES . $General->arr_general['theme'] . '/interface/sidebar.php';

        (file_exists(TEMPLATES . $General->arr_general['theme'] . '/interface/navbar.php')) && require TEMPLATES . $General->arr_general['theme'] . '/interface/navbar.php';

        (file_exists(TEMPLATES . $General->arr_general['theme'] . '/interface/container.php')) && require TEMPLATES . $General->arr_general['theme'] . '/interface/container.php';

        if (empty($_SESSION['iframe'])) {
            if (!empty($Modules->arr_module_init['interface_always']['afternavbar'])) :
                for ($module_id = 0, $c_mi = sizeof($Modules->arr_module_init['interface_always']['afternavbar']); $module_id < $c_mi; $module_id++) :
                    $file = MODULES . $Modules->arr_module_init['interface_always']['afternavbar'][$module_id]['name'] . '/forward/interface_always.php';
                    file_exists($file) && require $file;
                endfor;
            endif;

            if (!empty($Modules->arr_module_init['page'][$Modules->route]['interface']['afternavbar'])) :
                for ($module_id = 0, $c_mi = sizeof($Modules->arr_module_init['page'][$Modules->route]['interface']['afternavbar']); $module_id < $c_mi; $module_id++) :
                    $file = MODULES . $Modules->arr_module_init['page'][$Modules->route]['interface']['afternavbar'][$module_id] . '/forward/interface.php';
                    file_exists($file) && require $file;
                endfor;
            endif;
        } else {
            require PAGE . 'iframe.php';
        }

        require PAGE . 'footer.php';
    }
    
    public function get_css_color_palette()
    {
        return ':root' . str_replace('"', '', str_replace('",', ';', file_get_contents_fix('app/templates/' . $this->General->arr_general['theme'] . '/colors.json'))) .  ' ';
    }
}
