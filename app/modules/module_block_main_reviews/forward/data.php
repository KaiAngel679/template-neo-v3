<?php

use app\modules\module_block_main_reviews\ext\ReviewsBlockService;

$mbrService = new ReviewsBlockService($Db, $General);
$mbrData = $mbrService->getHomeData(10);
$mbrSummary = $mbrData['summary'];
$mbrReviews = $mbrData['reviews'];
$mbrRatingIcon = action_text_clear((string) $mbrData['rating_icon']);
$mbrRatingUse = '/resources/img/sprite.svg#' . $mbrRatingIcon;