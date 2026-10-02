<?php

use app\modules\module_page_check\ext\Check;

$ModuleCheckCore = new Check($Db, $General, $Translate, $Modules, $Router, $Notifications);
if (!empty($this->Db->db_data['Check'])) {
  $ModuleCheckAdminAccess = $ModuleCheckCore->IsAdmin();
}
