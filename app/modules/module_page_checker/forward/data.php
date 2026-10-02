<?php

use app\modules\module_page_checker\ext\Repositories\FileRepository;
use app\modules\module_page_checker\ext\Repositories\TextareaRepository;
use app\modules\module_page_checker\ext\Controllers\SettingsController;
use app\modules\module_page_checker\ext\Repositories\FilepondRepository;

$Router->map('GET|POST|DELETE', 'checker/[:page]/', 'checker');
$Map = $Router->match();
$page = $Map['params']['page'] ?? 'main';

$fileRepository = new FileRepository();
$textareaRepository = new TextareaRepository();
$settings = $fileRepository->getCache('settings');
$img = explode(';', $settings['img'] ?? '');

if (!in_array($page, ['main', 'settings'])) {
    get_iframe(404, $Translate->get_translate_phrase('_Page_not_found')) && die();
}

if (isset($_POST['get_links'])) {
    exit(json_encode(['status' => 'success', 'links' => ['site' => '/' . MODULES . 'module_page_checker/assets/download/download_ready/' . rawurlencode($settings['file']), 'share' => $settings['url_ft']]], true));
}

if ($page == 'settings') {
    $sc = new SettingsController($Translate);
    $filepondRepository = new FilepondRepository();
    if (isset($_SESSION['steamid64'])) {
        if (isset($_SESSION['user_admin'])) {
            if (isset($_POST['save_one'])) {
                exit(json_encode($sc->saveOne($_POST['auth'], $_POST['url_vt'], $_POST['url_ft'], $_POST['name_checker'], $_POST['description_checker'], $_POST['file']), true));
            } elseif (isset($_POST['send_filepond_file'])) {
                exit(json_encode($filepondRepository->send($_FILES['filepond'], $_POST['type']), true));
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                parse_str(file_get_contents("php://input"), $data);
                exit(json_encode($filepondRepository->remove($data['file'], $data['type'], 'tmp'), true));
            } elseif ($_POST['save_two']) {
                exit(json_encode($sc->saveTwo($_POST['slider'], $_POST['paragraphs'], $_POST['img']), true));
            } elseif ($_POST['delete']) {
                exit(json_encode($sc->delete($_POST['type']), true));
            }
        } else {
            get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die();
        }
    } else {
        get_iframe(401, 'Вы забыли авторизоваться') && die();
    }
}

require MODULES . 'module_page_checker/forward/vendors.php';
