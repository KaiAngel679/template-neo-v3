<?php

namespace app\modules\module_page_tickets\ext\Controllers;

use app\modules\module_page_tickets\ext\Services\DatabaseService;
use app\modules\module_page_tickets\ext\Repositories\SettingsRepository;

class SettingsController
{
    protected $ds, $sr, $Translate;

    public function __construct($Db, $Translate)
    {
        $this->ds = new DatabaseService($Db);
        $this->sr = new SettingsRepository;
        $this->Translate = $Translate;
    }

    public function createNewCategory($type, $title, $questions, $description, $servers, $server_on, $playtime, $response, $replay, $amount, $sort)
    {
        if (!$title) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_enterCatName')];
        }
        if (!$sort) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_specifySort')];
        }
        if (!$type) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_specifyType')];
        }
        if ($type == 1) {
            $questions == null;
        } else {
            if (!$questions) {
                return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_specifyQuestions')];
            }
        }
        foreach (['description', 'servers', 'playtime', 'response', 'replay', 'amount'] as $var) {
            if (empty($$var)) {
                $$var = null;
            }
        }
        $this->ds->createNewCategory($type, $title, $questions, $description, $servers, $server_on, $playtime, $response, $replay, $amount, $sort);
        return ['status' => 'success', 'url' => 'reload'];
    }

    public function createNewAnswer($answer, $button)
    {
        if (!$button) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_specifyButtonName')];
        }
        if (!$answer) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_specifyTexe')];
        }
        $this->sr->createNewAnswer($answer, $button);
        return ['status' => 'success', 'url' => 'reload'];
    }

    public function putSettings($noty, $url, $color, $img)
    {
        $this->sr->putSettings($noty, $url, $color, $img);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_settingsSave')];
    }

    public function putSettingsNew($slow, $slow_time, $auto_close, $duration)
    {
        $this->sr->putSettingsNew($slow, $slow_time, $auto_close, $duration);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_settingsSave')];
    }

    public function deleteCategory($id)
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_catNotNumber')];
        }
        $this->ds->deleteCategory($id);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_catRemoved')];
    }

    public function deleteAnswer($id)
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_answerNotNumber')];
        }
        $this->sr->deleteAnswer($id);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_answerRemoved')];
    }

    public function getAnswerId($id)
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_answerNotNumber')];
        }
        $result = $this->sr->getAnswerId($id)['data'];
        return ['status' => 'success', 'data' => $result];
    }

    public function editAnswer($id, $text_button, $text_answer)
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_answerNotNumber')];
        }
        $this->sr->editAnswer($id, $text_button, $text_answer);
        return ['status' => 'success', 'url' => 'reload'];
    }

    public function getCategory($id)
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_categoryNotNumber')];
        }
        $info = $this->ds->getCategoryFull($id);
        $description = action_text_clear($info['description'] ?? '');
        $playtime = action_text_clear($info['server_played'] ?? '');
        $response = action_text_clear($info['response_time'] ?? '');
        $replay = action_text_clear($info['replay_time'] ?? '');
        $amount = action_text_clear($info['amount_money'] ?? '');
        return ['status' => 'success', 'servers' => $this->ds->getServers(), 'select' => $info['servers'], 'title' => $info['title'], 'description' => $description, 'playtime' => $playtime, 'sort' => $info['sort_id'], 'server_on' => $info['server_on'], 'response' => $response, 'replay' => $replay, 'amount' => $amount, 'type' => $info['type'], 'questions' => $info['questions']];
    }

    public function editCategory($id, $title, $description, $server_on, $servers, $playtime, $response, $replay, $amount, $type, $questions, $sort)
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_categoryNotNumber')];
        }
        if (!$title) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_enterCatName')];
        }
        if (!$sort) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_specifySort')];
        }
        if ($type == 1) {
            $questions == null;
        } else {
            if (!$questions) {
                return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_specifyQuestions')];
            }
        }
        foreach (['description', 'servers', 'playtime', 'response', 'replay', 'amount'] as $var) {
            if (empty($$var)) {
                $$var = null;
            }
        }
        $this->ds->editCategory($id, $title, $description, $server_on, $servers, $playtime, $response, $replay, $amount, $type, $questions, $sort);
        return ['status' => 'success', 'url' => 'reload'];
    }
}
