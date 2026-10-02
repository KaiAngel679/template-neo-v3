<?php

namespace app\modules\module_page_faq\ext\Services;
use app\modules\module_page_faq\ext\Repositories\FileRepository;

class FaqService {
    protected $fr, $Translate;

    public function __construct($Translate) {
        $this->fr = new FileRepository;
        $this->Translate = $Translate;
    }    

    public function created($title, $text) 
    {
        if (!$title && !$text) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_faq', '_filFaq')];
        }
        return $this->fr->created($title, $text);
    }

    public function delete($id) 
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_faq', '_idFaq')];
        }
        $return = $this->fr->delete($id);
        if ($return['status'] == 'success') {
            return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_faq', '_deletedFaq')];
        } else {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_faq', '_idNotFound')];
        }
    }

    public function modal($id) 
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_faq', '_idFaq')];
        }
        return $this->fr->modal($id);
    }

    public function edit($id, $title, $text) 
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_faq', '_idFaq')];
        }
        $return = $this->fr->edit($id, $title, $text);
        if ($return['status'] == 'success') {
            return ['status' => 'success', 'url' => 'reload'];
        } else {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_faq', '_idNotFound')];
        }
    }
}