<?php


use app\modules\module_page_leaderboard\ext\Leaderboard;

$Leaderboard = new Leaderboard($Db, $General, $Translate, $Modules, $Router);

if (isset($_POST['getLeaderboardList'])) {
    exit(json_encode($Leaderboard->Render($_POST['page'], $_POST['server'] ?? 0, $_POST['filter'] ?? 0, $_POST['clear_banned'] ?? false)));
} elseif (isset($_POST['getUserStats'])) {
    exit(json_encode($Leaderboard->RenderUserStats($_POST['server'] ?? 0)));
}


$Modules->set_page_title($Translate->get_translate_phrase('_Stats_Sidebar') . ' | ' . $General->arr_general['short_name']);
$Modules->set_page_description($Translate->get_translate_phrase('_Statistics'));
