<?php

namespace app\modules\module_page_tickets\ext\Repositories;

use app\modules\module_page_tickets\ext\Repositories\FilepondRepository;

class JsonRepository
{
    protected $fr;

    public function __construct()
    {
        $this->fr = new FilepondRepository;
    }

    public function getCache($name)
    {
        $file = MODULES . "module_page_tickets/assets/cache/$name.json";
        if (file_exists($file)) {
            $json = json_decode(file_get_contents($file), true);
        }

        return $json;
    }

    public function putCache($data, $name, $id = null)
    {
        $file = MODULES . "module_page_tickets/assets/cache/$name.json";
        $jsonData = [];
        if ($id !== null && file_exists($file)) {
            $jsonData = json_decode(file_get_contents($file), true) ?? [];
        }
        if ($id !== null) {
            $jsonData[$id] = $data;
        } else {
            $jsonData = $data;
        }
        $json = json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return file_put_contents($file, $json) !== false;
    }

    public function deleteTicketImages($id)
    {
        $filename = MODULES . "module_page_tickets/temp/$id.json";

        if (file_exists($filename)) {
            $json = file_get_contents($filename);
            $chatData = json_decode($json, true);
            foreach ($chatData['messages'] as $message) {
                if (isset($message['img']) && !empty($message['img'])) {
                    $images = explode(';', $message['img']);
                    foreach ($images as $image) {
                        if (!empty($image)) {
                            $this->fr->deletePhoto($image);
                        }
                    }
                }
            }
        }

        return ['status' => 'success'];
    }

    public function deleteTicketJSON($id)
    {
        $filename = MODULES . "module_page_tickets/temp/$id.json";
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
}
