<?php

use app\modules\module_page_results\ext\Results;

$Results = new Results($Db, $General, $Translate, $Modules);

$Results->processCronSession();
