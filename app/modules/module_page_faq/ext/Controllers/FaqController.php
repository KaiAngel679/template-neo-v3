<?php

namespace app\modules\module_page_faq\ext\Controllers;
use app\modules\module_page_faq\ext\Services\FaqService;

class FaqController {
    protected $fs;

    public function __construct($Translate) {
        $this->fs = new FaqService($Translate);
    }

    public function created($title, $text) 
    {
        return $this->fs->created($title, $text);
    }

    public function delete($id) 
    {
        return $this->fs->delete($id);
    }

    public function modal($id) 
    {
        return $this->fs->modal($id);
    }

    public function edit($id, $title, $text) 
    {
        return $this->fs->edit($id, $title, $text);
    }
}