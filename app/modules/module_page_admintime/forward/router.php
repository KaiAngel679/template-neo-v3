<?php
if (isset($_POST['getAdminTimeList'])) {
  exit(json_encode($AdminTimeCore->render($_POST['page'], $_POST['steamid'], $_POST['server'], $_POST['date_start'], $_POST['date_end']), true));
} elseif (isset($_POST['AdminSessionModal'])) {
  exit(json_encode($AdminTimeCore->renderSession($_POST['steamid'], $_POST['page'], $_POST['server'],  $_POST['date_start'], $_POST['date_end']), true));
} elseif (isset($_POST['AdminChartsModal'])) {
  exit(json_encode($AdminTimeCore->renderCharts($_POST['steamid'], $_POST['server'],  $_POST['date_start'], $_POST['date_end']), true));
}

if (isset($_SESSION['user_admin'])) {
  if (isset($_POST['addServer'])) {
    exit(json_encode($AdminTimeCore->Settings->addServer($_POST['server_name'], $_POST['server_id']), true));
  } elseif (isset($_POST['deleteServer'])) {
    exit(json_encode($AdminTimeCore->Settings->deleteServer($_POST['server_id']), true));
  } elseif (isset($_POST['addSettings'])) {
    exit(json_encode($AdminTimeCore->Settings->saveSettings($_POST), true));
  } elseif (isset($_POST['addAccess'])) {
    exit(json_encode($AdminTimeCore->Access->addAccess($_POST['steamid']), true));
  } elseif (isset($_POST['delAccess'])) {
    exit(json_encode($AdminTimeCore->Access->delAccess($_POST['steamid']), true));
  }
}
