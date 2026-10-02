<?php

if (isset($_POST['search'])) {
    exit(json_encode($steamFinderController->dataCollection($_POST['steamid']), true));
}
