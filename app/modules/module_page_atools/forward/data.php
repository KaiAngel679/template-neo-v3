<?php

define('MODULE_NAME', 'module_page_atools');

use app\modules\module_page_atools\ext\ModuleKernel;

$kernel = new ModuleKernel($Db, $General, $Translate, $Modules);
$result = $kernel->handle($Router);

if ($result->isOk()) {
    $Modules->set_page_title('ATools | ' . $General->arr_general['short_name']);
    extract($result->getContext());
} else {
    $result->renderIframe();
}
