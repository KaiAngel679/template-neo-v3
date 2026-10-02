<?php

namespace app\modules\module_page_tickets\ext\Repositories;

use app\modules\module_page_tickets\ext\Repositories\JsonRepository;

class SettingsRepository
{
    protected $jr;

    public function __construct()
    {
        $this->jr = new JsonRepository;
    }

    public function createNewAnswer($answer, $button)
    {
        $file = MODULES . 'module_page_tickets/assets/cache/answers.json';
        $data = [];
        if (file_exists($file)) {
            $fileContent = file_get_contents($file);
            $data = json_decode($fileContent, true) ?? [];
        }
        $newId = !empty($data) ? max(array_keys($data)) + 1 : 1;
        $newAnswer = [
            'text_button' => $button,
            'text_answer' => $answer
        ];
        return $this->jr->putCache($newAnswer, 'answers', $newId);
    }

    public function putSettings($noty, $url, $color, $img)
    {
        $settings = [
            'noty' => $noty,
            'url' => $url,
            'color' => $color,
            'img' => $img
        ];
        return $this->jr->putCache($settings, 'noti');
    }

    public function putSettingsNew($slow, $slow_time, $auto_close, $duration)
    {
        $settings = [
            'slow' => $slow,
            'slow_time' => $slow_time,
            'auto_close' => $auto_close,
            'duration' => $duration
        ];
        return $this->jr->putCache($settings, 'settings');
    }

    public function deleteAnswer($id)
    {
        $jsonData = $this->jr->getCache('answers');
        if (isset($jsonData[$id])) {
            unset($jsonData[$id]);
            return $this->jr->putCache($jsonData, 'answers');
        }
        return ['status' => 'error'];
    }

    public function getAnswerId($id)
    {
        $jsonData = $this->jr->getCache('answers');
        if (is_string($jsonData)) {
            $jsonData = json_decode($jsonData, true);
        }
        if (isset($jsonData[$id])) {
            return ['status' => 'success', 'data' => $jsonData[$id]];
        }

        return ['status' => 'error'];
    }

    public function editAnswer($id, $text_button, $text_answer)
    {
        $jsonData = $this->jr->getCache('answers');
        $jsonData[$id] = [
            'text_button' => $text_button,
            'text_answer' => $text_answer
        ];
        return $this->jr->putCache($jsonData, 'answers');
    }
}
