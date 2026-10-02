<?php

if (empty($_POST['action']) && empty($_SESSION['steamid64'])) {
    get_iframe(403, $Translate->get_translate_module_phrase('module_page_notifications', '_needAuth'));
}

$Router->map('GET|POST', 'notifications/[notifications|settings:page]/', 'notifications');
$Router->map('POST', 'notifications/send/', 'sendNotification');

$Map = $Router->match();
$page = $Map['params']['page'] ?? 'notifications';

if ($Map['target'] === 'sendNotification') {

    if (!isset($_SESSION['user_admin'])) {
        echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен']);
        exit;
    }

    require_once MODULES . 'module_page_notifications/ext/NotificationsCore.php';

    $NotificationsCore = new app\modules\module_page_notifications\ext\NotificationsCore($Db, $Translate);

    $send_type = $_POST['send_type'] ?? 'single';
    $steamid = $_POST['steamid'] ?? '';
    $title = $_POST['title'] ?? '';
    $text = $_POST['text'] ?? '';
    $icon = $_POST['icon'] ?? 'info';
    $url = $_POST['url'] ?? '';
    $button = $_POST['button'] ?? '';

    $variables = [];
    if (!empty($_POST['variables'])) {
        $variablesInput = $_POST['variables'];

        if (strpos($variablesInput, '{') === 0) {
            $variables = json_decode($variablesInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $variables = [];
            }
        } else {

            $lines = explode("\n", $variablesInput);
            foreach ($lines as $line) {
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $variables[trim($parts[0])] = trim($parts[1]);
                }
            }
        }
    }

    $variables['module_translation'] = 'module_page_notifications';

    if (empty($title) || empty($text)) {
        echo json_encode(['status' => 'error', 'message' => 'Заполните заголовок и текст']);
        exit;
    }

    try {
        $count = 0;
        $message = '';

        switch ($send_type) {
            case 'single':

                if (empty($steamid)) {
                    echo json_encode(['status' => 'error', 'message' => 'Введите SteamID получателя']);
                    exit;
                }

                $NotificationsCore->sendCustomNotification($steamid, $title, $text, $icon, $url, $button, $variables);
                $count = 1;
                $message = "Уведомление отправлено пользователю $steamid";
                break;

            case 'online':

                $count = $NotificationsCore->sendToAllOnline($title, $text, $icon, $url, $button, $variables);
                $message = "Уведомление отправлено $count онлайн-пользователям";
                break;

            case 'recent':

                $count = $NotificationsCore->sendToRecentUsers($title, $text, $icon, $url, $button, $variables, 1000, 30);
                $message = "Уведомление отправлено $count активным пользователям (за последний месяц)";
                break;

            case 'all':

                $count = $NotificationsCore->sendToAllRegistered($title, $text, $icon, $url, $button, $variables, 1000);
                $message = "Уведомление отправлено $count зарегистрированным пользователям";
                break;

            default:
                echo json_encode(['status' => 'error', 'message' => 'Неверный тип отправки']);
                exit;
        }

        echo json_encode([
            'status' => 'success', 
            'message' => $message,
            'count' => $count
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка: ' . $e->getMessage()]);
    }

    exit;
}
?>