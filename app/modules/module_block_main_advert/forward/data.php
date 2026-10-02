<?php

use app\modules\module_block_main_advert\ext\Advert;

$adv = new Advert($Db, $General, $Translate, $Modules, $Router, $Notifications);

$banners = $adv->getBanners();
if(isset($_SESSION['user_admin'])){
    if(isset($_POST['text_main']) && !isset($_POST['editBanner'])){
        exit(json_encode($adv->addBanner($_POST), true));
    } elseif(isset($_POST['editBanner'])){
        exit(json_encode($adv->updateBanner($_POST['editBanner'],$_POST), true));
    } elseif(isset($_POST['del_banner'])){
        exit(json_encode($adv->deleteBanner($_POST['id_del']), true));
    } elseif(isset($_POST['sortBanners'])){
        exit(json_encode($adv->sortBanners($_POST['order']), true));
    }
}