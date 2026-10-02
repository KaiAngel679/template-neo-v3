<?php

use app\modules\module_page_search\ext\Search;

$search = new Search( $Db, $General );

$search->returnJson();