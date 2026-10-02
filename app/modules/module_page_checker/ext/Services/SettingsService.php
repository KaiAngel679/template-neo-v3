<?php

namespace app\modules\module_page_checker\ext\Services;

use app\modules\module_page_checker\ext\Repositories\SettingsRepository;

class SettingsService
{
    protected $sr, $Translate;

    public function __construct($Translate)
    {
        $this->Translate = $Translate;
        $this->sr = new SettingsRepository;
    }

    public function saveOne($auth, $vt, $ft, $name, $description, $file)
    {
        if (!$name) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_checker', '_noName')];
        if (!$ft || !$file) ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_checker', '_noChecker')];
        $this->sr->saveOne($auth, $vt, $ft, $name, $description, $file);
        return ['status' => 'success', 'url' => 'reload'];
    }

    public function saveTwo($slider, $paragraphs, $img)
    {
        if ($img !== '') {
            $imgsArr = array_filter(explode(';', $img));
            if (count($imgsArr) < 4) {
                return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_checker', '_fourFiles')];
            }
        }
        $this->sr->saveTwo($slider, $paragraphs, $img);
        return ['status' => 'success', 'url' => 'reload'];
    }

    public function delete($type)
    {
        $this->sr->delete($type);
        return ['status' => 'success', 'url' => 'reload'];
    }
}
