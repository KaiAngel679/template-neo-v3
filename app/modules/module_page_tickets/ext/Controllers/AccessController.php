<?php

namespace app\modules\module_page_tickets\ext\Controllers;

use app\modules\module_page_tickets\ext\Services\DatabaseService;
use app\modules\module_page_tickets\ext\Repositories\AdditionalRepository;

class AccessController
{
    protected $ds, $ar, $Translate;

    public function __construct($Db, $Translate)
    {
        $this->ds = new DatabaseService($Db);
        $this->ar = new AdditionalRepository($Translate);
        $this->Translate = $Translate;
    }

    public function createNewAccess($steam, $add_access, $add_block, $add_delete, $add_settings, $general, $category)
    {
        if (!$this->ar->isValidSteamId($steam)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', empty($steam) ? '_forgotSteam' : '_didntSteam')];
        }
        if (empty($category)) {
            $category = null;
        }
        $this->ds->createNewAccess($steam, $add_access, $add_block, $add_delete, $add_settings, $general, $category);
        return ['status' => 'success', 'url' => 'reload'];
    }

    public function deleteAccess($steam)
    {
        if (!$this->ar->isValidSteamId($steam)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', empty($steam) ? '_forgotSteam' : '_didntSteam')];
        }
        $this->ds->deleteAccess($steam);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_accessRemoved')];
    }

    public function getAccessUser($steam)
    {
        if (!$this->ar->isValidSteamId($steam)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', empty($steam) ? '_forgotSteam' : '_didntSteam')];
        }
        $info = $this->ds->getAccessFullCheck($steam);
        return ['status' => 'success', 'access' => $info['access'], 'delete' => $info['add_delete'], 'block' => $info['add_block'], 'add_access' => $info['add_access'], 'settings' => $info['add_settings'], 'general' => $info['general'], 'category' => $this->ds->getCategoriesTabs(), 'checked' => $info['category']];
    }

    public function editAcceessUser($steam, $delete, $block, $add_access, $settings, $general, $category)
    {
        if (empty($category)) {
            $category = null;
        }
        $this->ds->editAccess($steam, $delete, $block, $add_access, $settings, $general, $category);
        return ['status' => 'success', 'url' => 'reload'];
    }
}
