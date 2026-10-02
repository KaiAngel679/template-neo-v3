<?php

namespace app\modules\module_page_tickets\ext\Controllers;

use app\modules\module_page_tickets\ext\Services\DatabaseService;
use app\modules\module_page_tickets\ext\Repositories\SendRepository;
use app\modules\module_page_tickets\ext\Repositories\AdditionalRepository;
use app\modules\module_page_tickets\ext\Repositories\ChatRepository;
use app\modules\module_page_tickets\ext\Repositories\FilepondRepository;
use app\modules\module_page_tickets\ext\Repositories\JsonRepository;
use app\modules\module_page_tickets\ext\Repositories\DiscordRepository;

class SendController
{
    protected $ds, $sr, $fr, $cr, $ar, $jr, $dr, $Translate, $Notifications, $General;

    public function __construct($Db, $Translate, $Notifications, $General)
    {
        $this->ds = new DatabaseService($Db);
        $this->sr = new SendRepository;
        $this->cr = new ChatRepository;
        $this->fr = new FilepondRepository;
        $this->jr = new JsonRepository;
        $this->dr = new DiscordRepository($General);
        $this->ar = new AdditionalRepository($Translate);
        $this->Translate = $Translate;
        $this->Notifications = $Notifications;
        $this->General = $General;

        foreach ($this->ds->getBlocks() as $key) {
            if ($key['duration'] == 0) {
                continue;
            }
            if (time() - $key['created_at'] > $key['duration']) {
                $this->ds->deleteBlock($key['steamid']);
            }
        }
    }

    public function constructFormCategory($id)
    {
        $info = $this->ds->getCategoriesInfo($id);
        $description = $this->sr->textareaConvert($info['description'] ?? '');
        $servers = null;
        if (!empty($info['server_on'])) {
            $serversAll = $this->ds->getServers();
            $servers = !empty($info['servers']) ? $this->sr->filterServersId($serversAll, $info['servers']) : $serversAll;
        }
        return ['status' => 'success', 'description' => $description, 'server_on' => (int) $info['server_on'], 'servers' => $servers, 'type' =>  (int) $info['type'], 'questions' => $info['questions']];
    }

    public function sendForm($category, $topic, $message, $questions, $file, $server)
    {
        if (!$_SESSION['steamid64']) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketLogin')];
        }
        if ($this->ds->checkBlock($_SESSION['steamid64'])) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_block')];
        }
        if ($this->ds->checkCategory($category) <= 0) {
            return ['status' => 'error', 'text' => 'No such category'];
        }
        $categorySend = $this->ds->getCategorySend($category);
        $lastTicketTime = $this->ds->getLastTicket($_SESSION['steamid64'], $category);
        $nextAllowedTime = $lastTicketTime + ($categorySend['replay_time'] ?? 0);
        if (!empty($categorySend['replay_time']) && time() < $nextAllowedTime) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_nextTicket') . date('d.m.Y, H:i', $nextAllowedTime)];
        }
        if (!$category) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_catNotSelected')];
        }
        if (!$topic || !trim($topic)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_catNotSpecified')];
        }
        if (mb_strlen($topic, 'UTF-8') > 100) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketTopic100')];
        }
        if ($categorySend['type'] == 1) {
            if (!$message || !trim($message)) {
                return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_haventMsg')];
            }
            if (mb_strlen($message, 'UTF-8') > 1000) {
                return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketMess1000')];
            }
        } else {
            $categoryQuestions = array_map('trim', explode(';', $categorySend['questions']));
            $userAnswers = array_map('trim', explode(';', $questions));
            foreach ($userAnswers as $answer) {
                if (trim($answer) === '') {
                    return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_haventQuestions')];
                }
                if (mb_strlen($answer, 'UTF-8') > 500) {
                    return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketMess500')];
                }
            }
            $message = '';
            foreach ($categoryQuestions as $idx => $question) {
                $message .= $question . "\n- " . $userAnswers[$idx] . "\n";
            }
        }
        if ($server === 'none') {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_haventSrv')];
        }
        $photoArray = $file ? explode(';', $file) : [];
        if (!empty($categorySend['server_played'])) {
            $hour = 0;
            if ($server !== 'none' && !empty($server)) {
                $stats = $this->ds->getServer($server);
                $parts = explode(';', $stats);
                $result = ['user' => $parts[1], 'db' => $parts[2], 'table' => $parts[3]];
                $hour = $this->ds->getPlayTime($result['user'], $result['db'], $result['table'], $_SESSION['steamid32']);
            } else {
                $hour = $this->ds->getPlayTimeAll($_SESSION['steamid32']);
            }
            if ($hour < $categorySend['server_played']) {
                $needed = $categorySend['server_played'] - $hour;
                return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_needTime') . $needed . $this->Translate->get_translate_module_phrase('module_page_tickets', '_hour')];
            }
        }
        $this->ds->createTicket($category, $_SESSION['steamid64'], $topic, $server);
        $id = $this->ds->getLastId();
        $messageJSON = [
            "message_id" => uniqid("msg-$id-"),
            "created_at" => time(),
            "steamid"   => $_SESSION['steamid64'],
            "text"      => $message,
            "img"       => $file,
            "admin"     => 0
        ];
        $this->cr->saveMessagesChat((int) $id, $messageJSON);
        if (!empty($file)) {
            $this->fr->transferPhoto($file);
        }
        $notiCache = $this->jr->getCache('noti');
        if ($notiCache['url']) {
            $embed = $this->dr->buildEmbed($this->Translate->get_translate_module_phrase('module_page_tickets', '_newTicket') . $id, "-# " . $this->Translate->get_translate_module_phrase('module_page_tickets', '_fromUser') . $this->General->checkName($_SESSION['steamid64']) . "**](http:" . $this->General->arr_general['site'] . "profiles/" . $_SESSION['steamid64'] . "/?search=1)\n# [" . $this->Translate->get_translate_module_phrase('module_page_tickets', '_goToTicket') . "](http:" . $this->General->arr_general['site'] . "tickets/chat/$id/)\n" . $this->Translate->get_translate_module_phrase('module_page_tickets', '_subject') . "**$topic**\n```$message```\n-# " . $this->Translate->get_translate_module_phrase('module_page_tickets', '_attached') . count($photoArray));
            $this->dr->sendDiscordWebhook($embed);
        }
        if ($notiCache['noty']) {
            $allAdmins = $this->ds->getAccessAll();
            foreach ($allAdmins as $key) {
                if (!$key['category']) {
                    $this->Notifications->SendNotification($key['steamid'], '_ticket', '_ticketNotiAdmin', ['name' => $this->General->checkName($_SESSION['steamid64']), 'number' => $id, 'module_translation' => 'module_page_tickets'], '/tickets/chat/' . $id, 'request', '_open');
                } else {
                    $allowedCategories = array_map('trim', explode(';', $key['category']));
                    if (in_array($category, $allowedCategories)) {
                        $this->Notifications->SendNotification($key['steamid'], '_ticket', '_ticketNotiAdmin', ['name' => $this->General->checkName($_SESSION['steamid64']), 'number' => $id, 'module_translation' => 'module_page_tickets'], '/tickets/chat/' . $id, 'request', '_open');
                    }
                }
            }
        }
        return ['status' => 'success', 'url' => "/tickets/chat/$id/"];
    }

    public function getTimeRespons($category)
    {
        $tickets = $this->ds->getArchiveTicketCategoryId($category);
        if (empty($tickets)) {
            return;
        }
        $totalDuration = 0;
        foreach ($tickets as $ticket) {
            $totalDuration += $ticket['edit_at'] - $ticket['created_at'];
        }
        return $totalDuration / count($tickets);
    }
}
