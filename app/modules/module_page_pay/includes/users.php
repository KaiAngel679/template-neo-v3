<?php

if (!isset($_SESSION['user_admin']) || IN_LR != true) {
    header('Location: ' . $General->arr_general['site']);
    exit;
}
?>
<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <div class="badge">
                <?= $Translate->get_translate_module_phrase('module_page_pay', '_UsersList') ?>
            </div>
        </div>
        <div class="card-container">
            <div class="modern_table">
                <div class="mt_header_1">
                    <li>
                        <span class="none_span"><?= $Translate->get_translate_module_phrase('module_page_pay', '_LK_avatar') ?></span>
                        <span><?= $Translate->get_translate_phrase('_Player') ?></span>
                        <span><?= $Translate->get_translate_module_phrase('module_page_pay', '_Balance') ?></span>
                        <span class="none_span"><?= $Translate->get_translate_module_phrase('module_page_pay', '_BalanceAllTime') ?></span>
                        <span><?= $Translate->get_translate_phrase('_Current_Action') ?></span>
                    </li>
                </div>
                <div class="mt_content_1 mb20 no-scrollbar">
                    <?php foreach ($playersAll as $key) : ?>
                        <li>
                            <span class="none_span">
                                <?php $General->get_js_relevance_avatar($key['auth']) ?>
                                <img class="rounded-circle" src="<?= $General->getAvatar(con_steam64($key['auth']), 2); ?>" id="avatar" avatarid="<?= con_steam64($key['auth']) ?>">
                            </span>
                            <span onclick="location.href = '<?= $General->arr_general['site'] ?>profiles/<?= $key['auth'] ?>?search=1'">
                                <a href="<?= $General->arr_general['site'] ?>profiles/<?= con_steam64($key['auth']) ?>?search=1"><?= action_text_clear($General->checkName(con_steam64($key['auth']))) ?></a>
                            </span>
                            <span><?= $General->currency ?> <?= $key['cash'] ?></span>
                            <span class="none_span"><?= $General->currency ?> <?= $key['all_cash'] ?></span>
                            <span>
                                <a title="" href="<?= set_url_section(get_url(2), 'user_edit', $key['auth']) ?>">
                                    <?= $Translate->get_translate_phrase('_Change') ?>
                                </a>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </div>
            </div>
            <?= Pagination($page_max, $page_num)?>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <div class="badge" style="justify-content: space-between;">
                <?= $Translate->get_translate_module_phrase('module_page_pay', '_Options') ?>
                <form data-get="user_edit" id="users_clean" data-default="true" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="users_clean">
                </form>
                <button class="secondary_btn btn_delete" type="submit" form="users_clean" style="position: absolute; right: 20px;"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ClearZero') ?></button>
            </div>
        </div>
        <div class="card-container">
            <div>
                <form id="search_users" data-default="true" enctype="multipart/form-data" method="post">
                    <div class="input-form">
                        <input name="search_users" placeholder="STEAM_1:1:390... / 7656119803... / [U:1:1234234] / https://steamcommunity.com/profiles/... ">
                    </div>
                </form>
                <button class="secondary_btn" type="submit" form="search_users"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Search') ?></button>
            </div>
        </div>
    </div>
</div>
<?php if (!empty($_GET['user_edit'])) : $user = $LK->LkGetUserData($_GET['user_edit']);
    $pays = $LK->LkGetUserPays($_GET['user_edit']); ?>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Information') ?> - <?= $General->checkName(con_steam64($user[0]['auth'])) ?><a data-del="delete" data-get="user_edit" class="close_settings"><?= $Translate->get_translate_phrase('_Close') ?></a></h5>
            </div>
            <div class="card-container module_block">
                <form id="user_edit" data-default="true" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="user" value="<?= $_GET['user_edit'] ?>">
                    <div class="input-form">
                        <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_Balance') ?></label>
                        <input type="hidden" name="old_balance" value="<?= $user[0]['cash'] ?>">
                        <input name="new_balance" value="<?= $user[0]['cash'] ?>">
                    </div>
                </form>
                <div style="display:flex; gap:1rem;">
                    <input class="secondary_btn" type="submit" form="user_edit" value="<?= $Translate->get_translate_module_phrase('module_page_pay', '_Save') ?>">
                    <button class="secondary_btn btn_delete" id="del_lk_user" id_del="<?= $user[0]['auth'] ?>"><?= $Translate->get_translate_module_phrase('module_page_pay', '_removeFromDatabase') ?></button>
                </div>
                
                <div class="user_pays">
                    <?php if (!empty($pays)) : ?>
                        <div class="modern_table">
                            <div class="mt_header_2">
                                <li>
                                    <span class="none_span">#</span>
                                    <span><?= $Translate->get_translate_module_phrase('module_page_pay', '_Date') ?></span>
                                    <span class="none_span"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Gateways') ?></span>
                                    <span><?= $Translate->get_translate_module_phrase('module_page_pay', '_Amount') ?></span>
                                    <span class="none_span"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Promo') ?></span>
                                    <span><?= $Translate->get_translate_module_phrase('module_page_pay', '_Status') ?></span>
                                </li>
                            </div>
                            <div class="mt_content_2 no-scrollbar">
                                <?php foreach ($pays as $key) : ?>
                                    <li>
                                        <span class="none_span"><?= $key['pay_order'] ?></span>
                                        <span><?= $key['pay_data'] ?></span>
                                        <span class="none_span"><img src="<?= $General->arr_general['site'] ?>app/modules/module_page_pay/assets/gateways/<?= mb_strtolower($key['pay_system']) ?>.svg"></span>
                                        <span><?= $key['pay_summ'] ?></span>
                                        <span class="none_span"><?= $key['pay_promo'] ?></span>
                                        <span><?= $LK->status($key['pay_status']) ?></span>
                                    </li>
                                <?php endforeach ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="block_last_donate_nope">
                            <svg viewBox="0 0 512 512">
                                <path d="M0 256C0 397.4 114.6 512 256 512s256-114.6 256-256S397.4 0 256 0S0 114.6 0 256zm240 80c0-8.8 7.2-16 16-16c45 0 85.6 20.5 115.7 53.1c6 6.5 5.6 16.6-.9 22.6s-16.6 5.6-22.6-.9c-25-27.1-57.4-42.9-92.3-42.9c-8.8 0-16-7.2-16-16zm-80 80c-26.5 0-48-21-48-47c0-20 28.6-60.4 41.6-77.7c3.2-4.4 9.6-4.4 12.8 0C179.6 308.6 208 349 208 369c0 26-21.5 47-48 47zM303.6 208c0-17.7 14.3-32 32-32s32 14.3 32 32s-14.3 32-32 32s-32-14.3-32-32zm-128 32c-17.7 0-32-14.3-32-32s14.3-32 32-32s32 14.3 32 32s-14.3 32-32 32z" />
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_page_pay', '_NotPays') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>