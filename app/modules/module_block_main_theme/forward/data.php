<?php

use app\modules\module_block_main_theme\ext\Theme;

$Theme = new Theme($Db, $General, $Modules, $Translate);

if (isset($_SESSION['user_admin'])) {
    if (isset($_POST['theme_change']) && !empty($_POST['theme'])) {
        exit(json_encode($Theme->changeTheme((int)$_POST['theme'], $_POST['type'] ?? 'default')));
    } elseif (isset($_POST['theme_reset'])) {
        exit(json_encode($Theme->restoreDefaultTheme()));
    } elseif (isset($_POST['save_theme']) && !empty($_POST['theme_colors'])) {
        exit(json_encode($Theme->changeColors($_POST['theme_colors'])));
    } elseif (isset($_POST['save_palette']) && !empty($_POST['theme_colors'])) {
        exit(json_encode($Theme->savePalette($_POST['theme_colors'])));
    } elseif (isset($_POST['theme_delete']) && isset($_POST['theme'])) {
        exit(json_encode($Theme->deleteTheme($_POST['theme'])));
    } elseif (isset($_POST['save_background'])) {
        exit(json_encode($Theme->saveBackground($_POST)));
    }
}

$colorTheme = $Theme->getCurrentColors();
$background = $Theme->getBackgroundSettings();
