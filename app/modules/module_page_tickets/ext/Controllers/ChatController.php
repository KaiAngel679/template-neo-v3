<?php

namespace app\modules\module_page_tickets\ext\Controllers;

use app\modules\module_page_tickets\ext\Services\DatabaseService;
use app\modules\module_page_tickets\ext\Repositories\ChatRepository;
use app\modules\module_page_tickets\ext\Repositories\FilepondRepository;
use app\modules\module_page_tickets\ext\Repositories\JsonRepository;
use app\modules\module_page_tickets\ext\Repositories\DiscordRepository;
use app\modules\module_page_tickets\ext\Services\BalanceService;

class ChatController
{
    protected $ds, $fr, $cr, $jr, $dr, $bs, $id, $Translate, $Notifications, $General;

    public function __construct($Db, $Translate, $Notifications, $General, $id)
    {
        $this->ds = new DatabaseService($Db);
        $this->cr = new ChatRepository;
        $this->fr = new FilepondRepository;
        $this->jr = new JsonRepository;
        $this->dr = new DiscordRepository($General);
        $this->bs = new BalanceService($Db, $Translate);
        $this->Translate = $Translate;
        $this->Notifications = $Notifications;
        $this->General = $General;
        $this->id = $id;
    }

    public function getMessages($lastId)
    {
        $messages = $this->cr->getMessagesChat($this->id, $lastId)['messages'];
        $access = $this->ds->getAccessFullCheck($_SESSION['steamid64']);
        $admin = !empty($access['access']);

        foreach ($messages as &$key) {
            $key['date'] = date('d.m.Y, H:i', $key['created_at']);
            $key['time'] = date('H:i', $key['created_at']);
            $key['name'] = $this->General->checkName($key['steamid']);
        }
        unset($key);

        return ['status' => 'success', 'messages' => $messages, 'admin' => $admin, 'steamid' => $_SESSION['steamid64']];
    }

    public function sendMessage($message, $hide, $file)
    {
        $chatInfo = $this->ds->getInfoChat($this->id);
        $status = $chatInfo['status'] ?? null;

        if (!$status) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketHasBeenDeleted'), 'url' => 'back'];
        }
        if ($status == 2) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketHasBeenClosed'), 'url' => 'reload'];
        }
        if ($this->ds->checkBlock($_SESSION['steamid64'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_block')];
        }
        if (!trim($message) && !$file) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_writeMsgAttachFile')];
        }
        if (mb_strlen($message, 'UTF-8') > 1000) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketMess1000')];
        }
        $photoArray = $file ? explode(';', $file) : [];
        $access = $this->ds->getAccessFullCheck($_SESSION['steamid64']);
        $slow = $this->cr->getSlowMode($this->id);
        if (!$access['access']) {
            if ($slow && $this->jr->getCache('settings')['slow_time'] > time() - end($this->cr->getMessagesChat($this->id)['messages'])['created_at']) {
                return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketTimeout')];
            }
        }
        $admin = !empty($access['access']);
        $messageJSON = [
            "message_id" => uniqid("msg-$this->id-"),
            "created_at" => time(),
            "steamid" => $_SESSION['steamid64'],
            "text" => $message,
            "img" => $file,
            "admin" => $admin ? 1 : 0,
            "hide" => $admin ? (int) $hide : 0,
        ];
        $this->cr->saveMessagesChat($this->id, $messageJSON);
        if (!empty($file)) {
            $this->fr->transferPhoto($file);
        }
        $notiCache = $this->jr->getCache('noti');
        if (!empty($notiCache['url']) && !$admin) {
            $topic = $this->ds->getTopic($this->id);
            $embed = $this->dr->buildEmbed($this->Translate->get_translate_module_phrase('module_page_tickets', '_newAnswerInTicket') . $this->id, "-# " . $this->Translate->get_translate_module_phrase('module_page_tickets', '_fromUser') . htmlspecialchars($this->General->checkName($_SESSION['steamid64']), ENT_QUOTES, 'UTF-8', false) . "**](http:" . $this->General->arr_general['site'] . "profiles/" . $_SESSION['steamid64'] . "/?search=1)\n# [" . $this->Translate->get_translate_module_phrase('module_page_tickets', '_goToTicket') . "](http:" . $this->General->arr_general['site'] . "tickets/chat/$this->id/)\n" . $this->Translate->get_translate_module_phrase('module_page_tickets', '_subject') . "**$topic**\n```$message```\n-# " . $this->Translate->get_translate_module_phrase('module_page_tickets', '_attached') . count($photoArray));
            $this->dr->sendDiscordWebhook($embed);
        }
        if (!empty($notiCache['noty'])) {
            if ($admin) {
                $this->Notifications->SendNotification($chatInfo['steamid'], '_ticket', '_ticketNotiUser', ['number' => $this->id, 'module_translation' => 'module_page_tickets'], '/tickets/chat/' . $this->id, 'request', '_open');
            } else {
                $allAdmins = $this->ds->getAccessAll();
                $allMessages = $this->cr->getMessagesChat($this->id)['messages'];
                $adminWhoWrote = [];
                foreach ($allMessages as $msg) {
                    if ($msg['admin'] == 1) {
                        $adminWhoWrote[] = $msg['steamid'];
                    }
                }
                $adminWhoWrote = array_unique($adminWhoWrote);
                $ticketCategoryId = $chatInfo['category_id'];
                foreach ($allAdmins as $adminUser) {
                    if (!in_array($adminUser['steamid'], $adminWhoWrote)) {
                        continue;
                    }
                    if (!$adminUser['category']) {
                        $this->Notifications->SendNotification($adminUser['steamid'], '_ticket', '_ticketNotiAdminSend', ['number' => $this->id, 'module_translation' => 'module_page_tickets'], '/tickets/chat/' . $this->id, 'request', '_open');
                    } else {
                        $allowedCategories = array_map('trim', explode(';', $adminUser['category']));
                        if (in_array($ticketCategoryId, $allowedCategories)) {
                            $this->Notifications->SendNotification($adminUser['steamid'], '_ticket', '_ticketNotiAdminSend', ['number' => $this->id, 'module_translation' => 'module_page_tickets'], '/tickets/chat/' . $this->id, 'request', '_open');
                        }
                    }
                }
            }
        }
        return ['status' => 'success', 'admin' => $admin ? 1 : 0, 'slow' => $slow];
    }

    public function slowMode()
    {
        if (!is_numeric($this->id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketNotNumber')];
        }
        $this->cr->slowMode($this->id);
        return ['status' => 'success'];
    }

    public function closeTicket()
    {
        if (!is_numeric($this->id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketNotNumber')];
        }
        if ($this->ds->getAccessFullCheck($_SESSION['steamid64'])['access']) {
            $summ = $this->ds->getCategoryFull($this->ds->getInfoChat($this->id)['category_id'])['amount_money'];
            $summ ? $this->bs->addBalance($_SESSION['steamid32'], $summ) : null;
        }
        $this->ds->closeTicket($this->id, $_SESSION['steamid64']);
        if ($this->jr->getCache('noti')['url']) {
            $topic = $this->ds->getTopic($this->id);
            $embed = $this->dr->buildEmbed($this->Translate->get_translate_module_phrase('module_page_tickets', '_ticket') . " №" . $this->id . " " . mb_strtolower($this->Translate->get_translate_module_phrase('module_page_tickets', '_closed')), "-# " . $this->Translate->get_translate_module_phrase('module_page_tickets', '_byUser') . htmlspecialchars($this->General->checkName($_SESSION['steamid64'])) . "**](http:" . $this->General->arr_general['site'] . "profiles/" . $_SESSION['steamid64'] . "/?search=1)\n# [" . $this->Translate->get_translate_module_phrase('module_page_tickets', '_goToTicket') . "](http:" . $this->General->arr_general['site'] . "tickets/chat/$this->id/)\n" . $this->Translate->get_translate_module_phrase('module_page_tickets', '_subject') . " **$topic**");
            $this->dr->sendDiscordWebhook($embed);
        }
        return ['status' => 'success', 'url' => 'reload'];
    }

    public function deleteMessage($messageId)
    {
        $filename = MODULES . "module_page_tickets/temp/{$this->id}.json";

        if (!file_exists($filename)) {
            return ['status' => 'error'];
        }

        $json = file_get_contents($filename);
        $chatData = json_decode($json, true);

        if ($chatData === null || !isset($chatData['messages'])) {
            return ['status' => 'error'];
        }

        $messages = $chatData['messages'];
        $access = $this->ds->getAccessFullCheck($_SESSION['steamid64']);
        $isAdmin = !empty($access['access']);

        $found = false;
        foreach ($messages as $i => $message) {
            if ($message['message_id'] === $messageId) {
                if ($isAdmin || $message['steamid'] === $_SESSION['steamid64']) {
                    unset($messages[$i]);
                    $found = true;
                    break;
                } else {
                    return ['status' => 'error'];
                }
            }
        }

        if (!$found) {
            return ['status' => 'error'];
        }

        $chatData['messages'] = array_values($messages);
        file_put_contents($filename, json_encode($chatData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return ['status' => 'success'];
    }

    public function getPunish($punishType, $dateType)
    {
        $steam = $this->ds->getInfoUserSteam($this->id);
        if ($dateType == 'active') {
            $result = $this->ds->getActivePunish($steam);
            if ($punishType == 'ban') {
                return $result[0];
            } elseif ($punishType == 'mute') {
                return $result[1];
            }
        } elseif ($dateType == 'expired') {
            $result = $this->ds->getExpiredPunish($steam);
            if ($punishType == 'ban') {
                return $result[0];
            } elseif ($punishType == 'mute') {
                return $result[1];
            }
        }
    }

    public function getLkHistory()
    {
        return $this->ds->getLkHistory(con_steam32($this->ds->getInfoUserSteam($this->id)));
    }

    public function getStoreHistory()
    {
        return $this->ds->getStoreHistory(con_steam32($this->ds->getInfoUserSteam($this->id)));
    }
}
