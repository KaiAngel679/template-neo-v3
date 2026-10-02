<?php
if (!isset($_SESSION['user_admin']) || IN_LR != true) {
    header('Location: ' . $General->arr_general['site']);
    exit;
} ?>
<div class="row">
    <div class="col-md-12">
        <div class="admin_nav">
            <button class="<?= (get_section('section', 'lk') == 'lk' || get_section('section', 'lk') == 'search') ? 'active' : ''; ?>" onclick="location.href = '/pay'">
                <svg><use href="/resources/img/sprite.svg#users-cards"></use></svg>
                <?= $Translate->get_translate_module_phrase('module_page_pay', '_UsersList') ?>
            </button>
            <button class="<?php get_section('section', 'lk') == 'gateways' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'gateways') ?>'">
                <svg><use href="/resources/img/sprite.svg#gateaway"></use></svg>
                <?= $Translate->get_translate_module_phrase('module_page_pay', '_SettingsGateways') ?>
            </button>
            <button class="<?php get_section('section', 'lk') == 'promocodes' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'promocodes') ?>'">
                <svg><use href="/resources/img/sprite.svg#ticket"></use></svg>
                <?= $Translate->get_translate_module_phrase('module_page_pay', '_Promo') ?>
            </button>
            <button class="<?php get_section('section', 'lk') == 'payments' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'payments') ?>'">
                <svg><use href="/resources/img/sprite.svg#log"></use></svg>
                <?= $Translate->get_translate_module_phrase('module_page_pay', '_PaymentsList') ?>
            </button>
        </div>
    </div>
</div>
<?php if (!empty($_GET['section']) && isset($_SESSION['steamid32'])) : ?>
    <div class="row">
        <?php switch ($_GET['section']):
            case $_GET['section']:
                require MODULES . 'module_page_pay/includes/' . $_GET['section'] . '.php';
                break;
        endswitch; ?>
    </div>
<?php else : ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_UsersList') ?></div>
                </div>
                <div class="card-container">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?= $Translate->get_translate_phrase('_Player') ?></th>
                                    <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_Balance') ?></th>
                                    <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_BalanceAllTime') ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($playersAll as $key) : ?>
                                    <tr>
                                        <td>
                                            <?php $General->get_js_relevance_avatar($key['auth']) ?>
                                            <a href="<?= $General->arr_general['site'] ?>profiles/<?= con_steam64($key['auth']) ?>?search=1">
                                                <img class="rounded-circle" src="<?= $General->getAvatar(con_steam64($key['auth']), 2); ?>" id="avatar" avatarid="<?= con_steam64($key['auth']) ?>">
                                                <?= action_text_clear($General->checkName(con_steam64($key['auth']))) ?>
                                            </a>
                                        </td>
                                        <td><?= $General->currency ?> <?= $key['cash'] ?></td>
                                        <td><?= $General->currency ?> <?= $key['all_cash'] ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a class="button" title="" href="<?= set_url_section(get_url(2), 'user_edit', $key['auth']) ?>"><?= $Translate->get_translate_phrase('_Change') ?></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?= Pagination($page_max, $page_num)?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="badge" style="justify-content: space-between;"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Options') ?></div>
                </div>
                <div class="card-container">
                    <div class="inputs-inline">
                        <form data-get="user_edit" id="users_clean" data-default="true" enctype="multipart/form-data" method="post">
                            <input type="hidden" name="users_clean">
                        </form>
                        <button class="button-delete width-100" type="submit" form="users_clean"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ClearZero') ?></button>
                    </div>
                    <div>
                        <form id="search_users" data-default="true" enctype="multipart/form-data" method="post">
                            <div class="inputs-inline">
                                <label for="searchUserBalance"><?= $Translate->get_translate_module_phrase('module_page_pay', '_findPLayer') ?></label>
                                <input id="searchUserBalance" name="search_users" placeholder="STEAM_1:1:390... / 7656119803... / [U:1:1234234] / https://steamcommunity.com/profiles/... ">
                            </div>
                        </form>
                        <button class="width-100" type="submit" form="search_users"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Search') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($_GET['user_edit'])) : $user = $LK->LkGetUserData($_GET['user_edit']);
        $pays = $LK->LkGetUserPays($_GET['user_edit']); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="badge">
                            <?= $Translate->get_translate_module_phrase('module_page_pay', '_Information') ?> - <?= $General->checkName(con_steam64($user[0]['auth'])) ?>
                            <svg data-del="delete" data-get="user_edit" class="close_settings"><use href="/resources/img/sprite.svg#x"></use></svg>
                        </h5>
                    </div>
                    <div class="card-container module_block">
                        <form id="user_edit" data-default="true" enctype="multipart/form-data" method="post">
                            <div class="flex-inline">
                                <div class="inputs-inline flex-1">
                                    <label for="userSteamId"><?= $Translate->get_translate_phrase('_Identifier') ?></label>
                                    <input id="userSteamId" type="text" name="user" value="<?= $_GET['user_edit'] ?>" readonly>
                                </div>
                                <div class="inputs-inline flex-1">
                                    <label for="newUsersBalance"><?= $Translate->get_translate_module_phrase('module_page_pay', '_newBalance') ?></label>
                                    <input id="newUsersBalance" name="new_balance" value="<?= $user[0]['cash'] ?>" placeholder="<?= $Translate->get_translate_module_phrase('module_page_pay', '_setNewBalance') ?>">
                                </div>
                                <div class="inputs-inline flex-1">
                                    <label for="oldUsersBalance"><?= $Translate->get_translate_module_phrase('module_page_pay', '_oldBalanceCurrent') ?></label>
                                    <input id="oldUsersBalance" type="text" name="old_balance" value="<?= $user[0]['cash'] ?>" readonly>
                                </div>
                                <div class="inputs-buttons-pay">
                                    <button class="flex-1" type="submit" form="user_edit"><?= $Translate->get_translate_module_phrase('module_page_pay', '_changeBalance') ?></button>
                                    <button class="button-delete flex-1" id="del_lk_user" id_del="<?= $user[0]['auth'] ?>"><?= $Translate->get_translate_module_phrase('module_page_pay', '_removeFromDatabase') ?></button>
                                </div>
                            </div>
                        </form>
                        <div class="user_pays">
                            <?php if (!empty($pays)) : ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_Date') ?></th>
                                                <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_Gateways') ?></th>
                                                <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_Amount') ?></th>
                                                <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_Promo') ?></th>
                                                <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_Status') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pays as $key) : ?>
                                                <tr>
                                                    <td><?= $key['pay_order'] ?></td>
                                                    <td><?= $key['pay_data'] ?></td>
                                                    <td><img style="width:4rem; height:2rem;" src="<?= $General->arr_general['site'] ?>app/modules/module_page_pay/assets/gateways/<?= mb_strtolower($key['pay_system']) ?>.svg"></td>
                                                    <td><?= $key['pay_summ'] ?></td>
                                                    <td><?= $key['pay_promo'] ?></td>
                                                    <td><?= $LK->status($key['pay_status']) ?></td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else : ?>
                                <div class="block_last_donate_nope">
                                    <svg><use href="/resources/img/sprite.svg#cry-emoji"></use></svg>
                                    <?= $Translate->get_translate_module_phrase('module_page_pay', '_NotPays') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
<?php endif; ?>