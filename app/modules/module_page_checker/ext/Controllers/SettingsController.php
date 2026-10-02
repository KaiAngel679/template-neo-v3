<?php

namespace app\modules\module_page_checker\ext\Controllers;

use app\modules\module_page_checker\ext\Services\SettingsService;

class SettingsController
{
    protected $ss;

    public function __construct($Translate)
    {
        $this->ss = new SettingsService($Translate);
    }

    public function saveOne($auth, $vt, $ft, $name, $description, $file)
    {
        return $this->ss->saveOne($auth, $vt, $ft, $name, $description, $file);
    }

    public function saveTwo($slider, $paragraphs, $img)
    {
        return $this->ss->saveTwo($slider, $paragraphs, $img);
    }

    public function delete($type)
    {
        return $this->ss->delete($type);
    }
}
