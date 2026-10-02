<?php

namespace app\modules\module_page_tickets\ext\Repositories;

use app\modules\module_page_tickets\ext\Repositories\FilepondRepository;
use app\modules\module_page_tickets\ext\Repositories\JsonRepository;

class ChatRepository
{
    protected $fr, $jr;

    public function __construct()
    {
        $this->fr = new FilepondRepository;
        $this->jr = new JsonRepository;
    }

    public function saveMessagesChat($id, $message, $slow = 1)
    {
        $filename = MODULES . "module_page_tickets/temp/$id.json";
        if (file_exists($filename)) {
            $json = file_get_contents($filename);
            $chatData = json_decode($json, true);
        } else {
            $chatData = [
                "chat_id" => $id,
                "slow_mode" => $slow,
                "messages" => []
            ];
        }
        $chatData["messages"][] = $message;
        file_put_contents($filename, json_encode($chatData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function getMessagesChat($id, $lastMessage = null, $onlyCheckUser = false, $userSteamId = null)
    {
        $filename = MODULES . "module_page_tickets/temp/$id.json";

        if (!file_exists($filename)) {
            return ['status' => 'error'];
        }

        $json = file_get_contents($filename);
        $chatData = json_decode($json, true);

        if ($chatData === null) {
            return ['status' => 'error'];
        }

        $messages = $chatData["messages"] ?? [];

        if ($onlyCheckUser && $userSteamId) {
            foreach ($messages as $message) {
                if (!empty($message['steamid']) && $message['steamid'] == $userSteamId) {
                    return ['status' => 'success', 'hasUserMessage' => true];
                }
            }
            return ['status' => 'success', 'hasUserMessage' => false];
        }

        if ($lastMessage) {
            $newMessages = [];
            $found = false;
            foreach ($messages as $message) {
                if ($found) {
                    $newMessages[] = $message;
                }
                if ($message['message_id'] === $lastMessage) {
                    $found = true;
                }
            }
            return ['status' => 'success', 'messages' => $newMessages];
        }

        return ['status' => 'success', 'messages' => $messages];
    }

    public function slowMode($id)
    {
        $filename = MODULES . "module_page_tickets/temp/$id.json";
        if (!file_exists($filename)) {
            return ['status' => 'error'];
        }
        $json = file_get_contents($filename);
        $data = json_decode($json, true);
        if ($data === null) {
            return ['status' => 'error'];
        }
        if (isset($data['slow_mode'])) {
            $data['slow_mode'] = $data['slow_mode'] == 1 ? 0 : 1;
        }
        file_put_contents($filename, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return ['status' => 'success'];
    }

    public function getSlowMode($id)
    {
        $filename = MODULES . "module_page_tickets/temp/$id.json";
        if (!file_exists($filename)) {
            return ['status' => 'error'];
        }
        $json = file_get_contents($filename);
        $data = json_decode($json, true);
        if ($data === null) {
            return ['status' => 'error'];
        }
        if (!isset($data['slow_mode'])) {
            return ['status' => 'error'];
        }
        return $data['slow_mode'];
    }
}
