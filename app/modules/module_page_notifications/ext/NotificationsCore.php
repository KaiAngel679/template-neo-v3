<?php

namespace app\modules\module_page_notifications\ext;

use app\ext\Notifications;

class NotificationsCore
{
    protected $Notifications;
    protected $Db;
    protected $Translate;
    protected $translationsPath;
    protected $sessionsPath;
    protected $assetsJsPath;

    public function __construct($Db, $Translate)
    {
        $this->Db = $Db;
        $this->Translate = $Translate;
        $this->Notifications = new Notifications($Translate, $Db);
        $this->translationsPath = MODULES . 'module_page_notifications/translation.json';
        $this->sessionsPath = SESSIONS;
        $this->assetsJsPath = ASSETS_JS;
    }

    private function addTranslation($key, $ruText, $enText = '')
    {
        if (empty($enText)) {
            $enText = $ruText;
        }

        $translations = [];
        if (file_exists($this->translationsPath)) {
            $jsonContent = file_get_contents($this->translationsPath);
            $translations = json_decode($jsonContent, true);
            if ($translations === null) {
                $translations = [];
            }
        }

        if (!isset($translations[$key])) {
            $translations[$key] = [
                'EN' => $enText,
                'RU' => $ruText
            ];

            file_put_contents($this->translationsPath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->clearTranslationCache();
        }

        return $key;
    }

    private function clearTranslationCache()
    {

        $translatorCacheFile = $this->sessionsPath . 'translator.json';
        if (file_exists($translatorCacheFile)) {
            unlink($translatorCacheFile);
        }

        $translationsJsFile = $this->assetsJsPath . 'translations.js';
        if (file_exists($translationsJsFile)) {
            unlink($translationsJsFile);
        }

        $modulesCacheFile = $this->sessionsPath . 'modules_cache.php';
        if (file_exists($modulesCacheFile)) {

            touch($modulesCacheFile, time() - 3600);
        }

        $modulesInitFile = $this->sessionsPath . 'modules_initialization.php';
        if (file_exists($modulesInitFile)) {
            touch($modulesInitFile, time() - 3600);
        }

        if (method_exists($this->Translate, 'clear_cache')) {
            $this->Translate->clear_cache();
        }

        error_log("Translation cache cleared after adding new notification phrase");

        return true;
    }

    private function generateTranslationKey($text, $type = 'title')
    {

        $key = '_' . $type . '_' . md5($text);

        $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);

        return substr($key, 0, 50);
    }

    public function sendCustomNotification($steamid, $title, $text, $icon = 'info', $url = '', $button = '', $values = [])
    {

        if (empty($values) || !isset($values['module_translation'])) {
            $values['module_translation'] = 'module_page_notifications';
        }

        $titleKey = $this->generateTranslationKey($title, 'title');
        $textKey = $this->generateTranslationKey($text, 'text');
        $buttonKey = $button ? $this->generateTranslationKey($button, 'button') : '';

        $this->addTranslation($titleKey, $title);
        $this->addTranslation($textKey, $text);
        if ($button) {
            $this->addTranslation($buttonKey, $button);
        }

        $this->Notifications->SendNotification(
            $steamid,
            $titleKey,
            $textKey,
            $values,
            $url,
            $icon,
            $buttonKey
        );

        return true;
    }

    public function sendToMultiple($steamids, $title, $text, $icon = 'info', $url = '', $button = '', $values = [])
    {
        if (!is_array($steamids)) {
            $steamids = [$steamids];
        }

        $count = 0;
        $total = count($steamids);

        foreach ($steamids as $steamid) {
            $this->sendCustomNotification($steamid, $title, $text, $icon, $url, $button, $values);
            $count++;

            if ($count % 100 == 0) {
                error_log("Отправлено $count из $total уведомлений");
            }
        }

        return $count;
    }

    public function getOnlineUsers()
    {
        $users = [];

        try {
            $onlineUsers = $this->Db->queryAll('Core', 0, 0, 
                "SELECT DISTINCT user FROM lr_web_online WHERE user IS NOT NULL AND user != ''"
            );

            foreach ($onlineUsers as $user) {
                $steamid = $user['user'];
                if (!empty($steamid) && !in_array($steamid, $users)) {
                    $users[] = $steamid;
                }
            }
        } catch (\Exception $e) {

            error_log("Ошибка получения онлайн пользователей: " . $e->getMessage());
        }

        return $users;
    }

    public function getRecentUsers($limit = 1000, $days = 30)
    {
        $users = [];

        try {

            $daysAgo = time() - ($days * 24 * 60 * 60);

            $sql = "SELECT DISTINCT steam, lastconnect 
                    FROM lvl_base 
                    WHERE steam IS NOT NULL 
                    AND steam != '' 
                    AND lastconnect > $daysAgo
                    ORDER BY lastconnect DESC";

            if ($limit > 0) {
                $sql .= " LIMIT " . intval($limit);
            }

            $recentUsers = $this->Db->queryAll('Core', 0, 0, $sql);

            foreach ($recentUsers as $user) {
                $steamid = $user['steam'];
                if (!empty($steamid) && !in_array($steamid, $users)) {
                    $users[] = $steamid;
                }
            }

            $count = count($users);
            if ($limit > 0 && $count < $limit) {
                error_log("Найдено только $count пользователей за последние $days дней (лимит: $limit)");
            }

        } catch (\Exception $e) {
            error_log("Ошибка получения недавних пользователей: " . $e->getMessage());
        }

        return $users;
    }

    public function sendToRecentUsers($title, $text, $icon = 'info', $url = '', $button = '', $values = [], $limit = 1000, $days = 30)
    {
        $recentUsers = $this->getRecentUsers($limit, $days);
        return $this->sendToMultiple($recentUsers, $title, $text, $icon, $url, $button, $values);
    }

    public function getAllUsers($limit = 0)
    {
        $users = [];
        $offset = 0;
        $batchSize = 1000;

        try {
            do {
                $sql = "SELECT DISTINCT steam FROM lvl_base WHERE steam IS NOT NULL AND steam != ''";

                if ($limit > 0) {

                    $sql .= " LIMIT " . intval($batchSize) . " OFFSET " . intval($offset);
                    $allUsers = $this->Db->queryAll('Core', 0, 0, $sql);
                } else {

                    $sql .= " LIMIT " . intval($batchSize) . " OFFSET " . intval($offset);
                    $allUsers = $this->Db->queryAll('Core', 0, 0, $sql);
                }

                if (empty($allUsers)) {
                    break;
                }

                foreach ($allUsers as $user) {
                    $steamid = $user['steam'];
                    if (!empty($steamid) && !in_array($steamid, $users)) {
                        $users[] = $steamid;
                    }
                }

                $offset += $batchSize;

                if ($limit > 0 && count($users) >= $limit) {

                    $users = array_slice($users, 0, $limit);
                    break;
                }

                if (count($allUsers) == $batchSize) {
                    usleep(100000);
                }

            } while (count($allUsers) == $batchSize && ($limit == 0 || count($users) < $limit));

        } catch (\Exception $e) {
            error_log("Ошибка получения всех пользователей: " . $e->getMessage());
        }

        return $users;
    }

    public function sendToAllOnline($title, $text, $icon = 'info', $url = '', $button = '', $values = [])
    {
        $onlineUsers = $this->getOnlineUsers();
        return $this->sendToMultiple($onlineUsers, $title, $text, $icon, $url, $button, $values);
    }

    public function sendToAllRegistered($title, $text, $icon = 'info', $url = '', $button = '', $values = [], $limit = 0)
    {
        if ($limit > 0) {

            $allUsers = $this->getAllUsers($limit);
        } else {

            $allUsers = $this->getAllUsers(0);
        }

        return $this->sendToMultiple($allUsers, $title, $text, $icon, $url, $button, $values);
    }

    public function sendToAllRegisteredBatch($title, $text, $icon = 'info', $url = '', $button = '', $values = [], $batchSize = 500)
    {
        $totalCount = 0;
        $offset = 0;

        try {
            do {

                $sql = "SELECT DISTINCT steam FROM lvl_base WHERE steam IS NOT NULL AND steam != '' LIMIT " . 
                       intval($batchSize) . " OFFSET " . intval($offset);

                $usersBatch = $this->Db->queryAll('Core', 0, 0, $sql);

                if (empty($usersBatch)) {
                    break;
                }

                $steamids = [];
                foreach ($usersBatch as $user) {
                    $steamids[] = $user['steam'];
                }

                $batchCount = $this->sendToMultiple($steamids, $title, $text, $icon, $url, $button, $values);
                $totalCount += $batchCount;

                error_log("Отправлено пакетом: $batchCount, Всего: $totalCount");

                $offset += $batchSize;

                usleep(200000);

            } while (count($usersBatch) == $batchSize);

        } catch (\Exception $e) {
            error_log("Ошибка пакетной отправки: " . $e->getMessage());
        }

        return $totalCount;
    }

    public function getStats()
    {
        $stats = [
            'online' => 0,
            'total' => 0
        ];

        try {

            $onlineResult = $this->Db->query('Core', 0, 0, 
                "SELECT COUNT(DISTINCT user) as count FROM lr_web_online WHERE user IS NOT NULL"
            );
            if ($onlineResult && isset($onlineResult['count'])) {
                $stats['online'] = $onlineResult['count'];
            }

            $totalResult = $this->Db->query('Core', 0, 0, 
                "SELECT COUNT(DISTINCT steam) as count FROM lvl_base WHERE steam IS NOT NULL"
            );
            if ($totalResult && isset($totalResult['count'])) {
                $stats['total'] = $totalResult['count'];
            }
        } catch (\Exception $e) {
            error_log("Ошибка получения статистики: " . $e->getMessage());
        }

        return $stats;
    }

    public function getRecentStats($days = 30)
    {
        $stats = [
            'recent' => 0
        ];

        try {

            $daysAgo = time() - ($days * 24 * 60 * 60);

            $recentResult = $this->Db->query('Core', 0, 0, 
                "SELECT COUNT(DISTINCT steam) as count 
                 FROM lvl_base 
                 WHERE steam IS NOT NULL 
                 AND lastconnect > $daysAgo"
            );

            if ($recentResult && isset($recentResult['count'])) {
                $stats['recent'] = $recentResult['count'];
            }
        } catch (\Exception $e) {
            error_log("Ошибка получения статистики активных пользователей: " . $e->getMessage());
        }

        return $stats;
    }

    public function checkTranslationsFile()
    {
        if (!file_exists($this->translationsPath)) {

            $baseTranslations = [
                "_notifications" => [
                    "EN" => "Notifications",
                    "RU" => "Уведомления"
                ],
                "_Settings" => [
                    "EN" => "Settings",
                    "RU" => "Настройки"
                ],
                "_needAuth" => [
                    "EN" => "Authorization is required.",
                    "RU" => "Требуется авторизация."
                ],
                "_SendNotification" => [
                    "EN" => "Send Notification",
                    "RU" => "Отправить уведомление"
                ]
            ];

            file_put_contents(
                $this->translationsPath, 
                json_encode($baseTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $this->clearTranslationCache();
        }

        return file_exists($this->translationsPath);
    }

    public function forceReloadTranslations()
    {
        $this->clearTranslationCache();

        if (method_exists($this->Translate, 'rebuild_cache')) {
            $this->Translate->rebuild_cache();
        }

        return true;
    }

    public function getUsersCount($type = 'all')
    {
        if ($type == 'online') {
            $onlineUsers = $this->getOnlineUsers();
            return count($onlineUsers);
        } elseif ($type == 'recent') {
            $recentStats = $this->getRecentStats();
            return $recentStats['recent'] ?? 0;
        } else {

            try {
                $result = $this->Db->query('Core', 0, 0, 
                    "SELECT COUNT(DISTINCT steam) as count FROM lvl_base WHERE steam IS NOT NULL"
                );
                return $result && isset($result['count']) ? $result['count'] : 0;
            } catch (\Exception $e) {
                return 0;
            }
        }
    }
}