<?php if (!isset($_SESSION['user_admin']) || IN_LR != true) {
    header('Location: ' . $General->arr_general['site']);
    exit;
} ?>
<div class="col-md-7">
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SettingsPromo') ?></h5>
        </div>
        <div class="card-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_Promo') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_BonusPromo') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_LimitUsePromo') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_Snap') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_hidePromo') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_forNew') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($LK->LkPromocodes() as $key) : ?>
                            <tr>
                                <td><?= $key['code'] ?></td>
                                <td><?= $key['percent'] ?></td>
                                <td><?= $key['attempts'] ?></td>
                                <td><?php !empty($key['auth1']) ? print '+' : print '-'; ?></td>
                                <td><?php !empty($key['hide']) ? print '+' : print '-'; ?></td>
                                <td><?php !empty($key['new']) ? print '+' : print '-'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a class="button" href="<?= set_url_section(get_url(2), 'promocode_edit', $key['id']) ?>"><?= $Translate->get_translate_phrase('_Change') ?></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="col-md-5">
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Options') ?></h5>
        </div>
        <div class="card-container">
            <a class="button width-100" href="<?= set_url_section(get_url(2), 'promocode_add', 'promocodes') ?>"><?= $Translate->get_translate_module_phrase('module_page_pay', '_AddPromocode') ?></a>
        </div>
    </div>
</div>
<?php if (!empty($_GET['promocode_edit'])) : $promo = $LK->LkPromoCode($_GET['promocode_edit']); ?>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_EditPromo') ?> - <?= $promo[0]['code'] ?>
                    <svg data-del="delete" data-get="promocode_edit" class="close_settings"><use href="/resources/img/sprite.svg#x"></use></svg>
                </h5>
            </div>
            <div class="card-container module_block">
                <form id="promocode_edit" data-default="true" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="editid" value="<?= $_GET['promocode_edit'] ?>">
                    <div class="flex-inline">
                        <div class="inputs-inline">
                            <label for="editpromo"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Name') ?>:</label>
                            <input id="editpromo" name="editpromo" value="<?= $promo[0]['code'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label for="editlimit"><?= $Translate->get_translate_module_phrase('module_page_pay', '_LimitUsePromo') ?>:</label>
                            <input id="editlimit" name="editlimit" value="<?= $promo[0]['attempts'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label for="editbonuspecent"><?= $Translate->get_translate_module_phrase('module_page_pay', '_BonusPromo') ?>:</label>
                            <input id="editbonuspecent" name="editbonuspecent" value="<?= $promo[0]['percent'] ?>">
                        </div>
                    </div>
                    
                    <div class="inputs-inline">
                        <input class="switch" type="checkbox" name="status" id="status" <?php $promo[0]['auth1'] && print 'checked'; ?>>
                        <label for="status"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SnapSID') ?></label>
                    </div>
                    <div class="inputs-inline">
                        <input class="switch" type="checkbox" name="hide" id="hide" <?php $promo[0]['hide'] && print 'checked'; ?>>
                        <label for="hide"><?= $Translate->get_translate_module_phrase('module_page_pay', '_isHidden') ?></label>
                    </div>
                    <div class="inputs-inline">
                        <input class="switch" type="checkbox" name="new_players" id="new" <?php $promo[0]['new'] && print 'checked'; ?>>
                        <label for="new"><?= $Translate->get_translate_module_phrase('module_page_pay', '_fornewPlayers') ?></label>
                    </div>
                </form>
                <div class="kasses-buttons">
                    <button class="width-100" type="submit" form="promocode_edit"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Save') ?></button>
                    <button class="button-delete" type="submit" form="promocode_delete"><?= $Translate->get_translate_phrase('_Delete_Action') ?></button>
                </div>
                <form data-del="delete" data-get="promocode_edit" id="promocode_delete" data-default="true" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="promocode_delete" value="<?= $_GET['promocode_edit'] ?>">
                </form>
                <div class="user_pays">
                    <?php $usage = $LK->LkUsagePromo($promo[0]['code']);
                    if (!empty($usage)) : ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><?= $Translate->get_translate_phrase('_Player') ?></th>
                                        <th><?= $Translate->get_translate_phrase('_Date') ?></th>
                                        <th><?= $Translate->get_translate_module_phrase('module_page_pay', '_payment') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usage as $key) : ?>
                                        <?php $General->get_js_relevance_avatar(con_steam32to64($key['pay_auth']));?>
                                        <tr>
                                            <td id="name" nameid="<?=con_steam32to64($key['pay_auth'])?>"><?= $General->checkName(con_steam32to64($key['pay_auth'])) ?></td>
                                            <td><?= $key['pay_data'] ?></td>
                                            <td><img style="width: 4rem; height: 2rem" src="/app/modules/module_page_pay/assets/gateways/<?= mb_strtolower($key['pay_system']) ?>.svg" alt=""></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="no-data">
                            <?= $Translate->get_translate_module_phrase('module_page_pay', '_PromoNotUse') ?>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>
<?php if (!empty($_GET['promocode_add'])) : ?>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_AddPromocode') ?>
                    <svg data-del="delete" data-get="promocode_add" class="close_settings"><use href="/resources/img/sprite.svg#x"></use></svg>
                </h5>
            </div>
            <div class="card-container module_block">
                <form id="promocode_add" data-default="true" enctype="multipart/form-data" method="post">
                    <div class="flex-inline">
                        <div class="inputs-inline">
                            <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_AddPromoName') ?></label>
                            <input name="addpromo">
                        </div>
                        <div class="inputs-inline">
                            <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_LimitUsePromo') ?>:</label>
                            <input name="limit">
                        </div>
                        <div class="inputs-inline">
                            <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_BonusPromo') ?>:</label>
                            <input name="bonuspecent">
                        </div>
                    </div>
                    <div class="inputs-inline">
                        <input class="switch" type="checkbox" name="auth" id="status_add">
                        <label for="status_add"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SnapSID') ?></label>
                    </div>
                    <div class="inputs-inline">
                        <input class="switch" type="checkbox" name="hide" id="hide_add">
                        <label for="hide_add"><?= $Translate->get_translate_module_phrase('module_page_pay', '_isHidden') ?></label>
                    </div>
                    <div class="inputs-inline">
                        <input class="switch" type="checkbox" name="new_players" id="new_add">
                        <label for="new_add"><?= $Translate->get_translate_module_phrase('module_page_pay', '_fornewPlayers') ?></label>
                    </div>
                </form>
                <button class="width-100" name="promocode_add" type="submit" form="promocode_add"><?= $Translate->get_translate_module_phrase('module_page_pay', '_AddPromocode') ?></button>
            </div>
        </div>
    </div>
<?php endif ?>