<?php

if (IN_LR != true) {
    header('Location: ' . $General->arr_general['site']);
    exit;
}
if (isset($_SESSION['user_admin'])) :
    $pays = $LK->LkGetAllPays($page_num_min_pays, PAYS_COUNT); ?>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_pay', '_PaymentsList') ?>
                    <button class="secondary_btn btn_delete ml-auto" id="del_check"><?= $Translate->get_translate_module_phrase('module_page_pay', '_deleteUnpaid') ?></button>
                </div>
            </div>
            <div class="card-container">
                <?php if (!empty($pays)) : ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-left">#</th>
                                    <th class="text-left">Steam</th>
                                    <th class="text-left"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Date') ?></th>
                                    <th class="text-left"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Gateways') ?></th>
                                    <th class="text-left"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Amount') ?></th>
                                    <th class="text-left"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Promo') ?></th>
                                    <th class="text-left"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Status') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pays as $key) : ?>
                                    <tr id="p<?= $key['pay_order'] ?>">
                                        <td class="text-left"><?= $key['pay_order'] ?></td>
                                        <?php $General->get_js_relevance_avatar(con_steam32to64($key['pay_auth'])) ?>
                                        <td class="text-left">
                                            <div class="pay__user-info">
                                                <div class="pay__user-avatar-block">
                                                    <img class="pay__user-avatar" src="<?= $General->getAvatar(con_steam32to64($key['pay_auth']), 3) ?>" id="avatar" avatarid="<?= con_steam32to64($key['pay_auth']) ?>" alt="" title="">
                                                </div>
                                                <a href="/profiles/<?= con_steam32to64($key['pay_auth']) ?>/?search=1" target="_blank" id="name" nameid="<?= con_steam32to64($key['pay_auth']) ?>"><?= $General->checkName(con_steam32to64($key['pay_auth'])) ?></a>
                                            </div>
                                        </td>
                                        <td class="text-left"><?= $key['pay_data'] ?></td>
                                        <td class="text-left"><img class="payment-image" src="<?= $General->arr_general['site'] ?>app/modules/module_page_pay/assets/gateways/<?= mb_strtolower($key['pay_system']) ?>.svg"></td>
                                        <td class="text-left"><?= $key['pay_summ'] ?></td>
                                        <td class="text-left"><?= $key['pay_promo'] ?></td>
                                        <td class="text-left"><?= $LK->status($key['pay_status']) ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <?= $Translate->get_translate_module_phrase('module_page_pay', '_NotPays') ?>
                <?php endif; ?>
                <?= Pagination($page_max_pays, $page_num)?>
            </div>
        </div>
    </div>
<?php elseif (isset($_SESSION['steamid32']) && !isset($_SESSION['user_admin'])) :
    $pays = $LK->LkGetUserPays($_SESSION['steamid32']); ?>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_PaymentsList') ?></h5>
            </div>
            <div class="card-container module_block">
                <?php if (!empty($pays)) : ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-left">#</th>
                                <th class="text-left"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Date') ?></th>
                                <th class="text-left"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Gateways') ?></th>
                                <th class="text-left"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Amount') ?></th>
                                <th class="text-left"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Promo') ?></th>
                                <th class="text-left"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pays as $key) : ?>
                                <tr id="p<?= $key['pay_order'] ?>">
                                    <th class="text-left"></a><?= $key['pay_order'] ?></th>
                                    <th class="text-left"><?= $key['pay_data'] ?></th>
                                    <th class="text-left"><img src="<?= $General->arr_general['site'] ?>app/modules/module_page_pay/assets/gateways/<?= mb_strtolower($key['pay_system']) ?>.svg"></th>
                                    <th class="text-left"><?= $key['pay_summ'] ?></th>
                                    <th class="text-left"><?= $key['pay_promo'] ?></th>
                                    <th class="text-left"><?= $LK->status($key['pay_status']) ?></th>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <?= $Translate->get_translate_module_phrase('module_page_pay', '_haventTopup') ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?><style type="text/css">
    .table-hover tr:target {
        background-color: rgba(242, 123, 38, 0.16);
    }
</style>