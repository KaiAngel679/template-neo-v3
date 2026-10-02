<?php

namespace app\ext;

class Notifications
{
    protected $Db, $Translate;

    function __construct($Translate, $Db)
    {
        $this->Translate = $Translate;
        $this->Db = $Db;
        $this->NotificationsRender();
    }

    public function SendNotification($steam, $title, $text, $values_insert, $url, $icon, $button)
    {
        $param = [
            'steam' => con_steam64($steam),
            'title' => $title,
            'text' => $text,
            'values_insert' => json_encode($values_insert, JSON_UNESCAPED_UNICODE),
            'url' => $url,
            'icon' => $icon,
            'button' => $button,
        ];
        $this->Db->query('Core', 0, 0, "INSERT INTO `lr_web_notifications`(`steam`, `title`, `text`, `values_insert`, `url`, `icon`, `button`, `seen`, `status`) VALUES (:steam, :title, :text, :values_insert, :url, :icon, :button, 0, 0)", $param);
    }

    public function NotificationsRender()
    {
        if (!empty($_SESSION['steamid']) && !empty($_POST['entryid'])) {
            $unread = $this->NotificationsEach(true);
            $unread_count = count($unread);
            $count = 0;
            foreach ($unread as $notification) {
                if (!empty($notification['url'])) {
                    $notyurl = '<div class="notify_btn" onclick="location.href=\'' . $notification['url'] . '\'">' . $notification['button'] . '</div>';
                } else {
                    $notyurl = '';
                }
                $notifications[] = array(
                    'id' => $notification['id'],
                    'seen' => $notification['seen'],
                    'html' => '<div class="notify_body">
                                    <div class="notify_content">
                                        <div class="notify_image">
                                            ' . $notification['icon'] . '
                                        </div>
                                        <div class="notify_text">
                                            <div class="notify_title">' . $notification['title'] . '</div>
                                            <div class="notify_message">' . $notification['text'] . '</div>
                                            ' . $notyurl . '
                                        </div>
                                    </div>
                                    <div class="notify_closethis" id="main_notifications_del" id_del="' . $notification['id'] . '">
                                        <svg viewBox="0 0 320 512">
                                            <path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"></path>
                                        </svg>
                                    </div>
                                </div>',
                );
                ++$count;
            }
            if (!empty($notifications)) {
                echo json_encode(array('count' => $unread_count, 'no_notifications' => '<div class="no_notify">' . $this->Translate->get_translate_phrase('_No_Notifications') . '</div>', 'notifications' => array_reverse($notifications)));
                exit;
            } else {
                echo json_encode(array('count' => $unread_count, 'no_notifications' => '<div class="no_notify">' . $this->Translate->get_translate_phrase('_No_Notifications') . '</div>', 'notifications' => null));
                exit;
            }
        }
        if (isset($_POST['main_notifications_del'])) exit(json_encode($this->DelNotifications($_POST['id']), true));
        if (isset($_POST['main_notifications_all_del'])) exit(json_encode($this->DelAllNotifications(), true));
    }

    public function NotificationsEach($view)
    {
        $param = ['steam' => $_SESSION['steamid']];
        $NotificationsEach = $this->Db->queryAll('Core', 0, 0, "SELECT * FROM `lr_web_notifications` WHERE `status` = 0 AND `steam` = :steam ORDER BY `id` DESC", $param);
        $deliver = [];
        $NotificationsSVG = [
            "store"      => '<svg viewBox="0 0 28 28"><g><switch><g><path d="M10 9c.55 0 1-.45 1-1V7c0-1.66 1.34-3 3-3s3 1.34 3 3v1c0 .55.45 1 1 1s1-.45 1-1V7c0-2.76-2.24-5-5-5S9 4.24 9 7v1c0 .55.45 1 1 1zm4 8c-2.76 0-5-2.24-5-5 0-.55.45-1 1-1s1 .45 1 1c0 1.66 1.34 3 3 3s3-1.34 3-3c0-.55.45-1 1-1s1 .45 1 1c0 2.76-2.24 5-5 5zm11 2.9L24 10c0-1.66-1.34-3-3-3H7a2.989 2.989 0 0 0-3 2.9L3 20c0 3.31 2.69 6 6 6h10c3.33-.01 6.01-2.72 6-6.05v-.05z"></path></g></switch></g></svg>',
            "pay"        => '<svg viewBox="0 0 32 32"><g><path d="M10.533 7.397c1.115-.342 2.276-.536 3.475-.536s2.36.194 3.475.536a4.936 4.936 0 0 0 2.042-3.991v-.624A.783.783 0 0 0 18.742 2H9.273a.782.782 0 0 0-.782.782v.625c0 1.643.81 3.089 2.042 3.99zM23.01 4.796a.9.9 0 0 0-.9.9c0 .974-.792 1.765-1.766 1.765h-.778a6.629 6.629 0 0 1-.507.554c.69.328 1.356.715 1.987 1.176a3.57 3.57 0 0 0 2.864-3.494.9.9 0 0 0-.9-.901zM29.538 20.959c0 .738-1 1.385-2.506 1.741a12.07 12.07 0 0 1-2.832.317c-1.034 0-2.006-.116-2.825-.317-1.506-.356-2.506-1.003-2.506-1.741 0-1.139 2.388-2.065 5.332-2.065 2.949 0 5.337.926 5.337 2.065zM27.398 27.646c-.95.238-2.06.365-3.199.365-1.143 0-2.251-.128-3.207-.369-.92-.223-1.606-.515-2.124-.837v1.13c0 .738.993 1.379 2.492 1.747.819.201 1.798.318 2.839.318 1.048 0 2.02-.117 2.846-.317 1.5-.369 2.492-1.01 2.492-1.747v-1.132c-.52.324-1.211.618-2.139.842zM27.376 24.159c-.948.232-2.051.357-3.177.357-1.122 0-2.223-.125-3.183-.36-.936-.221-1.628-.519-2.148-.847v1.137c0 .738.993 1.379 2.492 1.741.819.207 1.798.324 2.839.324 1.048 0 2.02-.116 2.846-.324 1.5-.362 2.492-1.003 2.492-1.741v-1.14c-.522.331-1.218.631-2.161.853z"></path><path d="M17.368 27.936V20.592c.311-2.216 3.597-3.197 6.832-3.197.255 0 .509.008.762.021-1.425-4.782-5.37-9.054-10.954-9.054-8.013 0-12.668 8.796-11.312 15.276C3.79 28.864 8.496 30 14.008 30c1.447 0 2.831-.088 4.115-.3-.615-.637-.755-1.301-.755-1.764z"></path></g></svg>',
            "request"    => '<svg viewBox="0 0 384 512"><path d="M192 0c-41.8 0-77.4 26.7-90.5 64H64C28.7 64 0 92.7 0 128V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V128c0-35.3-28.7-64-64-64H282.5C269.4 26.7 233.8 0 192 0zm0 64a32 32 0 1 1 0 64 32 32 0 1 1 0-64zM72 272a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zm104-16H304c8.8 0 16 7.2 16 16s-7.2 16-16 16H176c-8.8 0-16-7.2-16-16s7.2-16 16-16zM72 368a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zm88 0c0-8.8 7.2-16 16-16H304c8.8 0 16 7.2 16 16s-7.2 16-16 16H176c-8.8 0-16-7.2-16-16z"></path></svg>',
            "info"       => '<svg viewBox="0 0 20 20"><g><path d="M10 1a8.987 8.987 0 0 0-7.921 13.257l-1.037 3.455A1 1 0 0 0 2 19a1.019 1.019 0 0 0 .288-.042l3.455-1.037A9 9 0 1 0 10 1zm0 4a1 1 0 1 1-1 1 1 1 0 0 1 1-1zm1 10h-1a1 1 0 0 1-1-1v-4a1 1 0 0 1 0-2h1a1 1 0 0 1 1 1v4a1 1 0 0 1 0 2z"></path></g></svg>',
            "ms"         => '<svg viewBox="0 0 24 24"><g><path fill-rule="evenodd" d="M12.824 2.196a3 3 0 0 0-1.648 0l-5 1.428A3 3 0 0 0 4 6.51v5.374a9 9 0 0 0 3.769 7.324l2.487 1.776a3 3 0 0 0 3.488 0l2.487-1.776A9 9 0 0 0 20 11.883V6.51a3 3 0 0 0-2.176-2.885zm2.86 6.074a1 1 0 0 1 .046 1.414l-3.75 4a1 1 0 0 1-1.359.094l-2.25-1.818a1 1 0 1 1 1.258-1.556l1.527 1.234 3.114-3.322a1 1 0 0 1 1.414-.046z" clip-rule="evenodd"></path></g></svg>',
            "punish"     => '<svg viewBox="0 0 20 20" fill-rule="evenodd"><g><path d="M5 8.165V6a4 4 0 0 1 4-4c-.178 0 0 0 0 0a4 4 0 0 1 4 4 1 1 0 0 1-2 0 2 2 0 1 0-4 0v2h5.018A2.982 2.982 0 0 1 15 10.982v4.036A2.983 2.983 0 0 1 12.018 18H7a1 1 0 0 1-1-1 2 2 0 0 0-2-2 1 1 0 0 1-1-1v-3.018c0-1.303.835-2.411 2-2.817zM5 17a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3-5v2a1 1 0 0 0 2 0v-2a1 1 0 0 0-2 0zm7-4h1a1 1 0 0 0 0-2h-1a1 1 0 0 0 0 2zm.707-3.293 1-1a1 1 0 0 0-1.414-1.414l-1 1a1 1 0 0 0 1.414 1.414z"></path></g></svg>',
            "profile"    => '<svg viewBox="0 0 512 512"><g><g data-name="Layer 9"><path d="M501.09 58.55c-9.12-2.07-18.88-4-29-5.87a16.1 16.1 0 0 1-12.81-12.81c-1.88-10.15-3.81-19.9-5.87-29-2.46-11.29-20.24-14.36-30-6.68-20.91 17-47.45 42.54-65.49 62.44-3.48 3.83-4.9 9.59-3.95 15.31 2.42 14.34 4.54 28.74 6.58 42.64L242.57 242.5a19 19 0 0 0 26.87 26.87l117.94-117.94c13.9 2 28.29 4.15 42.64 6.57 5.71.95 11.48-.47 15.31-3.94 19.89-18 45.42-44.58 62.43-65.49 7.68-9.78 4.62-27.57-6.67-30.02z"></path><path d="M457.42 167.39a34.89 34.89 0 0 1-20.15 8.71A197.67 197.67 0 1 1 335.84 74.7a35.24 35.24 0 0 1 8.7-20.18c7.66-8.45 16.82-17.88 26.45-27.29A255 255 0 0 0 256 0C114.62 0 0 114.62 0 256s114.62 256 256 256 256-114.62 256-256a255 255 0 0 0-27.28-115.08c-9.41 9.64-18.85 18.81-27.3 26.47z"></path><path d="M333 231.22a81 81 0 1 1-52.37-52.28l43.83-43.83a138.91 138.91 0 1 0 52.34 52.31z"></path></g></g></svg>',
            "reports"    => '<svg viewBox="0 0 24 24" xml:space="preserve"><g><g><path d="M11.976 5.745a22.634 22.634 0 0 1-3.287 1.31 25.96 25.96 0 0 1-2.332.611l.03 3.287A7.031 7.031 0 0 0 10 17.097l2.02 1.122 1.978-1.122a7.031 7.031 0 0 0 3.614-6.144V7.624a17.233 17.233 0 0 1-2.301-.57 25.694 25.694 0 0 1-3.336-1.309z"></path><path d="M20.357 4.106a34.245 34.245 0 0 1-3.132-.822 34.62 34.62 0 0 1-4.48-1.757 1.717 1.717 0 0 0-1.487 0 34.621 34.621 0 0 1-4.483 1.757 34.301 34.301 0 0 1-3.131.822A1.739 1.739 0 0 0 2.25 5.817v4.71A11.756 11.756 0 0 0 8.294 20.8l2.855 1.587a1.734 1.734 0 0 0 1.7 0l2.857-1.587a11.756 11.756 0 0 0 6.044-10.272v-4.71a1.739 1.739 0 0 0-1.393-1.71zm-1.244 6.847a8.53 8.53 0 0 1-4.385 7.454l-2.02 1.122a1.45 1.45 0 0 1-1.414.001l-2.022-1.123a8.53 8.53 0 0 1-4.385-7.454V7.624a1.448 1.448 0 0 1 1.16-1.426c.73-.154 1.468-.348 2.194-.575a24.187 24.187 0 0 0 3.139-1.231 1.447 1.447 0 0 1 1.238-.002 24.014 24.014 0 0 0 3.142 1.233c.725.227 1.464.42 2.195.576a1.446 1.446 0 0 1 1.158 1.425z"></path></g></g></svg>',
            "challenges" => '<svg viewBox="0 0 32 32" xml:space="preserve" fill-rule="evenodd"><g><path d="M5.828 13.444 16.435 2.837a3 3 0 0 1 4.243 0l8.485 8.485a3 3 0 0 1 0 4.243L18.556 26.172l-2.121-2.122a1 1 0 0 0-1.414 1.414l2.121 2.122-1.503 1.503a3 3 0 0 1-4.243 0l-2.382-2.382a.999.999 0 0 1 0-1.414l.631-.631a1.53 1.53 0 0 0 0-2.164l-.001-.002a1.531 1.531 0 0 0-2.164 0l-.632.631a.999.999 0 0 1-1.414 0l-2.523-2.523a3 3 0 0 1 0-4.243l1.503-1.503 2.121 2.121a1 1 0 0 0 1.415-1.414zm11.8-2.323 1.945-1.399a.999.999 0 0 1 1.584.807l.011 2.395 1.931 1.417a1 1 0 0 1-.278 1.756l-2.275.751-.751 2.275a1 1 0 0 1-1.755.278l-1.418-1.932-2.395-.011a1 1 0 0 1-.807-1.584l1.399-1.944-.73-2.282a1 1 0 0 1 1.257-1.257z"></path></g></svg>'
        ];
        foreach ($NotificationsEach as $notification) {
            $values = json_decode($notification['values_insert']);
            if (!empty($values->module_translation)) {
                $text = $this->Translate->get_translate_module_phrase($values->module_translation, $notification['text']);
                $title = $this->Translate->get_translate_module_phrase($values->module_translation, $notification['title']);
                $button = $this->Translate->get_translate_module_phrase($values->module_translation, $notification['button']);
            } else {
                $text = $this->Translate->get_translate_phrase($notification['text']);
                $title = $this->Translate->get_translate_phrase($notification['title']);
                $button = $this->Translate->get_translate_phrase($notification['button']);
            }
            if (!$values) {
                $values = [];
            }
            foreach ($values as $key => $val) {
                $text = str_replace('%' . $key . '%', $val, $text);
                $title = str_replace('%' . $key . '%', $val, $title);
                $button = str_replace('%' . $key . '%', $val, $button);
            }
            $icon = isset($NotificationsSVG[$notification['icon']]) ? $NotificationsSVG[$notification['icon']] : 'info';
            $deliver[] = array('id' => $notification['id'], 'title' => $title, 'text' => $text, 'seen' => $notification['seen'], 'url' => $notification['url'], 'icon' => $icon, 'button' => $button);
            if ($view && !$notification['seen']) {
                $this->Db->query('Core', 0, 0, "UPDATE `lr_web_notifications` SET `seen` = 1 WHERE `steam` = :steam", $param);
            }
        }
        return $deliver;
    }

    public function DelNotifications($id)
    {
        $param = ['steam' => $_SESSION['steamid'], 'id' => $id];
        $this->Db->query('Core', 0, 0, "UPDATE `lr_web_notifications` SET `seen` = 1, `status` = 1 WHERE `steam` = :steam AND `id` = :id LIMIT 1", $param);
        return array('status' => 'success', 'text' =>  $this->Translate->get_translate_phrase('_clearNoty'));
    }

    public function DelAllNotifications()
    {
        $param = ['steam' => $_SESSION['steamid']];
        $this->Db->queryAll('Core', 0, 0, "UPDATE `lr_web_notifications` SET `seen` = 1, `status` = 1 WHERE `steam` = :steam", $param);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_phrase('_clearAllNoty')];
    }
}
