<?php
use app\modules\module_page_open_case\ext\Open_case;

$CASES = new Open_case($Translate, $Notifications, $General, $Modules, $Db, $Auth);

require MODULES . 'module_page_open_case/forward/routes.php';

$settings = $CASES->CaseSettings();
$cats = $CASES->CaseCategory();


$Router->map('GET|POST', 'cases/[:page]/', 'cases');

$Map = $Router->match();
$page = $Map['params']['page'] ?? '';

if(isset($_SESSION['user_admin'])){
    if($page == 'install'){
        require MODULES . 'module_page_open_case/includes/pages/install.php';
    }
}


