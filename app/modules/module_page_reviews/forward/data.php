<?php

define('MODULE_NAME', 'module_page_reviews');

use app\modules\module_page_reviews\ext\ModuleKernel;

$kernel = new ModuleKernel($Db, $General, $Translate, $Modules);
$result = $kernel->handle($Router);

if ($result->isOk()) {
    $Modules->set_page_title(
        $Translate->get_translate_module_phrase('module_page_reviews', '_rv_pageTitle')
        . ' | '
        . $General->arr_general['short_name']
    );
    extract($result->getContext());
} else {
    $result->renderIframe();
}
