<?php

use app\modules\module_page_bonuses\ext\Controllers\BonusesController;

if ($Modules->route != 'bonuses') {
    if (isset($_SESSION['steamid64'])) {
        $bcBonuses = new BonusesController($Db);
        $modalCheck = $bcBonuses->checkModal($_SESSION['steamid64'])['modal_show'];
        if (!$modalCheck) {
            $bcBonuses->createdUserModal($_SESSION['steamid64']);
            $modalShow = 0;
        } else {
            $modalShow = $modalCheck;
        }
        if (isset($_POST['hide_modal'])) {
            $bcBonuses->updateUserModal($_SESSION['steamid64']);
        }
    }
}
