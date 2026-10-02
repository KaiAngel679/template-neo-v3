<?php if (!isset($_SESSION['user_admin']) || IN_LR != true) {
    header('Location: ' . $General->arr_general['site']);
    exit;
}

$GatewaysArrayList = [
    'freekassa' => 'FreeKassa MULTI',
    'webmoney' => 'WebMoney MULTI',
    'yoomoney' => 'YooMoney',
    'anypay' => 'AnyPay',
    'paypalych' => 'PayPalych',
    'centapp' => 'CentApp',
    'cshost' => 'Cshost',
    'aaio' => 'Aaio',
    'skinpay' => 'SkinPay',
    'lava' => 'Lava'
]; ?>
<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SettingsGateways') ?>
            </h5>
        </div>
        <div class="card-container">
            <di class="kassa_block">
                <div class="for_all_time">
                    <?= $Translate->get_translate_module_phrase('module_page_pay', '_BalanceAllTime') ?>:
                    <?= $General->currency ?>
                    <?= $LK->LkAllDonats() ?></div>
                <ul class="kassa_list no-scrollbar">
                    <?php foreach ($LK->LkGetAllGateways() as $key):
                        $GatewaysExist[mb_strtolower($key['name_kassa'])] = 1; ?>
                        <li class="kassa_line">
                            <div>
                                <?= $key['name_kassa']; ?>
                                <span><?= $General->currency ?>
                                    <?= $LK->LkAllDonatsToPayGateway(mb_strtolower($key['name_kassa'])); ?></span>
                            </div>
                            <a class="button margin-left-auto"
                                href="<?= set_url_section(get_url(2), 'geteway_edit', mb_strtolower($key['name_kassa'])) ?>"><?= $Translate->get_translate_module_phrase('module_page_pay', '_tune') ?></a>
                        </li>
                    <?php endforeach ?>
                </ul>
            </di>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="card height-100">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Options') ?></h5>
        </div>
        <div class="card-container">
            <div class="select-panel">
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="option-gateway_add-select">
                        <?php foreach ($GatewaysArrayList as $url => $name):
                        if (empty($GatewaysExist[$url])): ?>
                            <li>
                                <label class="adaptive-select__label" for="for_gateway_add_<?= $name ?>">
                                    <div class="adaptive-select__label-text"><?= $name ?></div>
                                    <input class="hide-input" id="for_gateway_add_<?= $name ?>" name="gateway_add" type="radio" value="<?= set_url_section(get_url(2), 'gateway_add', $url) ?>" onclick="window.location.href=this.value">
                                </label>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="option-gateway_add-select">
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_pay', '_AddGateways') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            <form id="webhook_discord" data-default="true" enctype="multipart/form-data" method="post">
                <div class="inputs-inline">
                    <label for="discordwebhook">Discord Webhook URL:</label>
                    <div class="number">
                        <input id="discordwebhook" name="webhoock_url"
                            value="<?php $LK->LkDiscordData()['url'] && print $LK->LkDiscordData()['url']; ?>"
                            type="password">
                        <div class="eye-password" id="show_pass"><svg>
                                <use href="/resources/img/sprite.svg#eye"></use>
                            </svg></div>
                    </div>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="webhoock_url_offon" id="webhoock_url_offon" <?php $LK->LkDiscordData()['auth'] && print 'checked'; ?>>
                    <label for="webhoock_url_offon"><?= $Translate->get_translate_module_phrase('module_page_pay', '_sendLogsDS') ?></label>
                </div>
            </form>
        </div>
        <div class="card-bottom margin-top-auto">
            <button class="width-100" type="submit"
                form="webhook_discord"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Save') ?></button>
        </div>
    </div>
</div>
<?php if (!empty($_GET['geteway_edit'])):
    $Gateway = $LK->LkGetGateway($_GET['geteway_edit']); ?>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SetGateways') ?> -
                    <?= ucfirst($_GET['geteway_edit']) ?>
                    <svg data-del="delete" data-get="geteway_edit" class="close_settings">
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </h5>
            </div>
            <div class="card-container module_block">
                <form id="gateway_edit" data-default="true" enctype="multipart/form-data" method="post">
                    <?php switch ($_GET['geteway_edit']):
                        case 'webmoney': ?>
                            <input type="hidden" name="gateway_edit" value="<?= $_GET['geteway_edit'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="webmoney"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Purse') ?></label>
                                    <input id="webmoney" name="shopid" value="<?= $Gateway[0]['shop_id'] ?>">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secretKey2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?>:</label>
                                    <input id="secretKey2" name="secret2" value="<?= $Gateway[0]['secret_key_2'] ?>">
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=webmoney"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=webmoney"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <input class="switch" type="checkbox" name="status" id="status" <?php $Gateway[0]['status'] && print 'checked'; ?>>
                                <label
                                    for="status"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ActGateways') ?></label>
                            </div>
                        <?php break;
                        case 'yoomoney': ?>
                            <input type="hidden" name="gateway_edit" value="<?= $_GET['geteway_edit'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="yoomoney"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Purse') ?></label>
                                    <input id="yoomoney" name="shopid" value="<?= $Gateway[0]['shop_id'] ?>">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secretKey2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?></label>
                                    <div class="number">
                                        <input id="secretKey2" name="secret2" value="<?= $Gateway[0]['secret_key_2'] ?>"
                                            type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label
                                    for="resultUrl"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=yoomoney"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=yoomoney"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <input class="switch" type="checkbox" name="status" id="status" <?php $Gateway[0]['status'] && print 'checked'; ?>>
                                <label
                                    for="status"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ActGateways') ?></label>
                            </div>
                        <?php break;
                        case 'freekassa': ?>
                            <input type="hidden" name="gateway_edit" value="<?= $_GET['geteway_edit'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="freekassa"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="freekassa" name="shopid" value="<?= $Gateway[0]['shop_id'] ?>" type="text">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret1"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Password') ?>
                                        #1</label>
                                    <div class="number">
                                        <input id="secret1" name="secret1" value="<?= $Gateway[0]['secret_key_1'] ?>" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Password') ?>
                                        #2</label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" value="<?= $Gateway[0]['secret_key_2'] ?>" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>

                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=freekassa"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=freekassa"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <input class="switch" type="checkbox" name="status" id="status" <?php $Gateway[0]['status'] && print 'checked'; ?>>
                                <label
                                    for="status"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ActGateways') ?></label>
                            </div>
                        <?php break;
                        case 'paypalych': ?>
                            <input type="hidden" name="gateway_edit" value="<?= $_GET['geteway_edit'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="paypalych"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="paypalych" name="shopid" value="<?= $Gateway[0]['shop_id'] ?>">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?></label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" value="<?= $Gateway[0]['secret_key_2'] ?>" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>

                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=paypalych"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=paypalych"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <input class="switch" type="checkbox" name="status" id="status" <?php $Gateway[0]['status'] && print 'checked'; ?>>
                                <label
                                    for="status"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ActGateways') ?></label>
                            </div>
                        <?php break;
                        case 'centapp': ?>
                            <input type="hidden" name="gateway_edit" value="<?= $_GET['geteway_edit'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="centapp"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="centapp" name="shopid" value="<?= $Gateway[0]['shop_id'] ?>">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?></label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" value="<?= $Gateway[0]['secret_key_2'] ?>" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>

                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=centapp"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=centapp"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <input class="switch" type="checkbox" name="status" id="status" <?php $Gateway[0]['status'] && print 'checked'; ?>>
                                <label
                                    for="status"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ActGateways') ?></label>
                            </div>
                        <?php break;
                        case 'anypay': ?>
                            <input type="hidden" name="gateway_edit" value="<?= $_GET['geteway_edit'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="anypay"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="anypay" name="shopid" value="<?= $Gateway[0]['shop_id'] ?>">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?></label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" value="<?= $Gateway[0]['secret_key_2'] ?>" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=anypay"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=anypay"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <input class="switch" type="checkbox" name="status" id="status" <?php $Gateway[0]['status'] && print 'checked'; ?>>
                                <label
                                    for="status"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ActGateways') ?></label>
                            </div>
                        <?php break;
                        case 'cshost': ?>
                            <input type="hidden" name="gateway_edit" value="<?= $_GET['geteway_edit'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="cshost"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="cshost" name="shopid" value="<?= $Gateway[0]['shop_id'] ?>">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?></label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" value="<?= $Gateway[0]['secret_key_2'] ?>" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=cshost"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=cshost"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <input class="switch" type="checkbox" name="status" id="status" <?php $Gateway[0]['status'] && print 'checked'; ?>>
                                <label
                                    for="status"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ActGateways') ?></label>
                            </div>
                        <?php break;
                        case 'aaio': ?>
                            <input type="hidden" name="gateway_edit" value="<?= $_GET['geteway_edit'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="aaio"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="aaio" name="shopid" value="<?= $Gateway[0]['shop_id'] ?>">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret1"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?>
                                        #1</label>
                                    <div class="number">
                                        <input id="secret1" name="secret1" value="<?= $Gateway[0]['secret_key_1'] ?>" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?>
                                        #2</label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" value="<?= $Gateway[0]['secret_key_2'] ?>" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=aaio"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=aaio"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <input class="switch" type="checkbox" name="status" id="status" <?php $Gateway[0]['status'] && print 'checked'; ?>>
                                <label
                                    for="status"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ActGateways') ?></label>
                            </div>
                        <?php break;
                        case 'lava': ?>
                            <input type="hidden" name="gateway_edit" value="<?= $_GET['geteway_edit'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="lava"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="lava" name="shopid" value="<?= $Gateway[0]['shop_id'] ?>">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret1"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?>
                                        #1</label>
                                    <div class="number">
                                        <input id="secret1" name="secret1" value="<?= $Gateway[0]['secret_key_1'] ?>" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?>
                                        #2</label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" value="<?= $Gateway[0]['secret_key_2'] ?>" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=lava"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=lava"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <input class="switch" type="checkbox" name="status" id="status" <?php $Gateway[0]['status'] && print 'checked'; ?>>
                                <label
                                    for="status"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ActGateways') ?></label>
                            </div>
                        <?php break;
                        case 'skinpay': ?>
                            <input type="hidden" name="gateway_edit" value="<?= $_GET['geteway_edit'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label for="publicKey">Public key</label>
                                    <input id="publicKey" name="shopid" value="<?= $Gateway[0]['shop_id'] ?>" type="password">
                                </div>
                                <div class="inputs-inline">
                                    <label for="privateKey">Private key</label>
                                    <div class="number">
                                        <input id="privateKey" name="secret2" value="<?= $Gateway[0]['secret_key_2'] ?>"
                                            type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=skinpay"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=skinpay"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <input class="switch" type="checkbox" name="status" id="status" <?php $Gateway[0]['status'] && print 'checked'; ?>>
                                <label
                                    for="status"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ActGateways') ?></label>
                            </div>
                    <?php break;
                    endswitch ?>
                </form>
                <div class="kasses-buttons">
                    <button class="width-100" type="submit"
                        form="gateway_edit"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Save') ?></button>
                    <button class="button-delete" type="submit" form="gateway_delete"
                        title=""><?= $Translate->get_translate_phrase('_Delete_Action') ?></button>
                </div>
                <form data-get="geteway_edit" id="gateway_delete" data-default="true" enctype="multipart/form-data"
                    method="post">
                    <input type="hidden" name="gateway_delete" value="<?= $Gateway[0]['id'] ?>">
                </form>
            </div>
        </div>
    </div>
<?php endif ?>
<?php if (!empty($_GET['gateway_add'])): ?>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_AddGateways') ?> -
                    <?= ucfirst($_GET['gateway_add']) ?>
                    <svg data-del="delete" data-get="gateway_add" class="close_settings">
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </h5>
            </div>
            <div class="card-container module_block">
                <form id="gateway_add" data-default="true" data-get="gateway_add" enctype="multipart/form-data"
                    method="post">
                    <?php switch ($_GET['gateway_add']):
                        case 'webmoney': ?>
                            <input type="hidden" name="gateway" value="<?= $_GET['gateway_add'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="webmoney"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Purse') ?></label>
                                    <input id="webmoney" name="shopid">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?>:</label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=webmoney"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=webmoney"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                        <?php break;
                        case 'yoomoney': ?>
                            <input type="hidden" name="gateway" value="<?= $_GET['gateway_add'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="yoomoney"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Purse') ?></label>
                                    <input id="yoomoney" name="shopid">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Password') ?></label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=yoomoney"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=yoomoney"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                        <?php break;
                        case 'freekassa': ?>
                            <input type="hidden" name="gateway" value="<?= $_GET['gateway_add'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="freekassa"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="freekassa" name="shopid">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret1"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Password') ?>
                                        #1</label>
                                    <div class="number">
                                        <input id="secret1" name="secret1" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Password') ?>
                                        #2</label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=freekassa"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=freekassa"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                        <?php break;
                        case 'paypalych': ?>
                            <input type="hidden" name="gateway" value="<?= $_GET['gateway_add'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="paypalych"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="paypalych" name="shopid">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?></label>
                                    <input id="secret2" name="secret2">
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=paypalych"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=paypalych"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                        <?php break;
                        case 'centapp': ?>
                            <input type="hidden" name="gateway" value="<?= $_GET['gateway_add'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="centapp"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="centapp" name="shopid">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?></label>
                                    <input id="secret2" name="secret2">
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=centapp"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=centapp"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                        <?php break;
                        case 'anypay': ?>
                            <input type="hidden" name="gateway" value="<?= $_GET['gateway_add'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="anypay"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="anypay" name="shopid">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?></label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=anypay"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=anypay"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                        <?php break;
                        case 'cshost': ?>
                            <input type="hidden" name="gateway" value="<?= $_GET['gateway_add'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="cshost"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="cshost" name="shopid">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="cshost"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?></label>

                                    <div class="number">
                                        <input id="cshost" name="secret2" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=cshost"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=cshost"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                        <?php break;
                        case 'aaio': ?>
                            <input type="hidden" name="gateway" value="<?= $_GET['gateway_add'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="aaio"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="aaio" name="shopid">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret1"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?>
                                        #1</label>

                                    <div class="number">
                                        <input id="secret1" name="secret1" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?>
                                        #2</label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=aaio"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=aaio"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                        <?php break;
                        case 'lava': ?>
                            <input type="hidden" name="gateway" value="<?= $_GET['gateway_add'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label
                                        for="lava"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Indetificator') ?></label>
                                    <input id="lava" name="shopid">
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret1"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?>
                                        #1</label>
                                    <div class="number">
                                        <input id="secret1" name="secret1" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                                <div class="inputs-inline">
                                    <label
                                        for="secret2"><?= $Translate->get_translate_module_phrase('module_page_pay', '_SecretKey') ?>
                                        #2</label>
                                    <div class="number">
                                        <input id="secret2" name="secret2" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=lava"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=lava"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                        <?php break;
                        case 'skinpay': ?>
                            <input type="hidden" name="gateway" value="<?= $_GET['gateway_add'] ?>">
                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label for="publickKey">Public key</label>
                                    <div class="number">
                                        <input id="publickKey" name="shopid" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                                <div class="inputs-inline">
                                    <label for="privateKey">Private key</label>

                                    <div class="number">
                                        <input id="privateKey" name="secret2" type="password">
                                        <div class="eye-password" id="show_pass"><svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg></div>
                                    </div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label>
                                    <?= $Translate->get_translate_module_phrase('module_page_pay', '_ResultUrl') ?></label>
                                <div class="no_uppercase_url">
                                    <input id="resultUrl" type="text" value="<?= $LK->https() . get_url(2) ?>?gateway=skinpay"
                                        readonly>
                                    <a class="button copy-btn"
                                        data-clipboard-text="<?= $LK->https() . get_url(2) ?>?gateway=skinpay"><svg>
                                            <use href="/resources/img/sprite.svg#copy"></use>
                                        </svg><?= $Translate->get_translate_phrase('_Copytext') ?></a>
                                </div>
                            </div>
                    <?php break;
                    endswitch ?>
                </form>
                <button class="width-100" name="gateway_save" type="submit"
                    form="gateway_add"><?= $Translate->get_translate_module_phrase('module_page_pay', '_AddGateways') ?></button>
            </div>
        </div>
    </div>
<?php endif ?>