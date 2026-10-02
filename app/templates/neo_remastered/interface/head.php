<?php
if (isset($_SESSION['steamid64'])) {
    $msAccess = $Db->query('Core', 0, 0, "SELECT `steamid_access` FROM `lvl_web_managersystem_access` WHERE `steamid_access` = :steamid_access", ['steamid_access' => $_SESSION['steamid64']]);
    $serverAccess = $General->checkAdminServerAccess($_SESSION['steamid64']);
} else {
    $msAccess = [];
    $serverAccess = false;
}
$isAdmin = isset($_SESSION['user_admin']);
$isAuth = isset($_SESSION['steamid64']);
$hasAdminAccess = !empty($msAccess['steamid_access']);
$hasServerAccess = ($serverAccess === true);
?>