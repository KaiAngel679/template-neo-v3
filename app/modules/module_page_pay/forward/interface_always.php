<div class="popup_modal" id="popupPay">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_pay', '_LK') ?>
            <?php if (!empty($_SESSION['steamid']) && isset($_SESSION['user_admin'])) : ?>
                <a class="popup_modal_close margin-left-auto" href="/pay" target="_blank"><svg><use href="/resources/img/sprite.svg#gear"></use></svg></a>
            <?php endif; ?>
            <span class="popup_modal_close"><svg><use href="/resources/img/sprite.svg#x"></use></svg></span>
        </div>
        <form id="pay" data-default="true" enctype="multipart/form-data" method="post" class="lk_window">
            <div class="popup_balance_content">
                <?php if (!empty($LK->LkGetGatewaysOn())) : ?>
                    <div class="popub_balance_first">
                        <div class="popup_column">
                            <div class="inputs-inline">
                                <label><svg><use href="/resources/img/sprite.svg#card"></use></svg><?= $Translate->get_translate_module_phrase('module_page_pay', '_ChangeGateway') ?></label>
                                <div class="popup_pay_area">
                                    <?php foreach ($LK->LkGetGatewaysOn() as $info) : ?>
                                        <input type="radio" name="gatewayPay" data-name="<?= $info['name_kassa']; ?>" value="<?= mb_strtolower($info['name_kassa']) ?>" id="Gateway<?= $info['id'] ?>" class="popup_gateways">
                                        <label for="Gateway<?= $info['id'] ?>" class="popup_gateways-label"><img src="<?= $General->arr_general['site'] . MODULES ?>module_page_pay/assets/gateways/<?= mb_strtolower($info['name_kassa']) ?>.svg" alt=""></label>
                                    <?php endforeach ?>
                                </div>
                            </div>
                            <?php if (isset($_SESSION['steamid32'])) : ?>
                                <input type="hidden" name="steam" value="<?= $_SESSION['steamid32'] ?>">
                            <?php else : ?>
                                <div class="inputs-inline">
                                    <label for="userSteamidPay"><svg><use href="/resources/img/sprite.svg#steam"></use></svg>STEAM ID:</label>
                                    <input id="userSteamidPay" name="steam" placeholder="STEAM_1:1:390... / 7656119803... / [U:1:1234234] / https://steamcommunity.com/profiles/... ">
                                </div>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="popup_middle_line">
                        <span class="popup_line_top"></span>
                        <span class="popup_arrow"></span>
                        <span class="popup_line_bottom"></span>
                    </div>
                <?php endif; ?>
                <div class="popub_balance_second">
                    <div class="popup_balance_second_flex">
                        <label><svg><use href="/resources/img/sprite.svg#gateaway"></use></svg><?= $Translate->get_translate_module_phrase('module_page_pay', '_ChoosenGateway') ?></label>
                        <div class="popup_pay_info">
                            <div class="popup_pay_info_left">
                                <span class="popup_title_text"><?= $Translate->get_translate_module_phrase('module_page_pay', '_ChoosenGateway') ?></span>
                                <span class="popup_pay_method" id="kass_name"><?= $Translate->get_translate_module_phrase('module_page_pay', '_notChoosen') ?></span>
                            </div>
                            <div id="kass_img"></div>
                        </div>
                        <div class="skintoggle" id="skinnone">
                            <label><svg><use href="/resources/img/sprite.svg#gateaway"></use></svg><?= $Translate->get_translate_module_phrase('module_page_pay', '_GetAmoumtYour') ?></label>
                            <div class="preset_buttons">
                                <button class="flex-auto preset-amount" type="button" data-amount="100">100</button>
                                <button class="flex-auto preset-amount" type="button" data-amount="250">250</button>
                                <button class="flex-auto preset-amount" type="button" data-amount="500">500</button>
                                <button class="flex-auto preset-amount" type="button" data-amount="1000">1000</button>
                                <button class="flex-auto preset-amount" type="button" data-amount="2000">2000</button>
                                <button class="flex-auto preset-amount" type="button" data-amount="2500">2500</button>
                                <button class="flex-auto preset-amount" type="button" data-amount="3000">3000</button>
                                <button class="flex-auto preset-amount" type="button" data-amount="5000">5000</button>
                                <button id="clearButton" class="button-delete delete-amount" type="button"><svg><use href="/resources/img/sprite.svg#broom"></use></svg></button>
                            </div>
                            <div class="inputs-inline">
                                <label for="numberInput"><svg><use href="/resources/img/sprite.svg#coins"></use></svg><?= $Translate->get_translate_module_phrase('module_page_pay', '_ToUpAmount') ?></label>
                                <div class="number">
                                    <button class="number-minus" type="button" onclick="decrementValue(this);">-</button>
                                    <input id="numberInput" type="number" min="25" max="20000" value="50" name="amount" placeholder="<?= $Translate->get_translate_module_phrase('module_page_pay', '_EnterAmount') ?>" onchange="updateValue(this);">
                                    <button class="number-plus" type="button" onclick="incrementValue(this);">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="popup_promo_block">
                            <div class="inputs-inline">
                                <label for="promoCodeInput"><svg><use href="/resources/img/sprite.svg#ticket"></use></svg><?= $Translate->get_translate_module_phrase('module_page_pay', '_Promo') ?></label>
                                <div class="popup_promo_area">
                                    <input id="promoCodeInput" name="promocode" placeholder="<?= $Translate->get_translate_module_phrase('module_page_pay', '_GetInputPromo') ?>">
                                    <div class="popup_clear_promo" id="popupClearPromo"><svg><use href="/resources/img/sprite.svg#broom"></use></svg></div>
                                </div>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_pay', '_CurrentPromo') ?></label>
                                <div class="popup_promo_list">
                                    <?php foreach ($LK->LkPromocodesVisible() as $key) : ?>
                                        <div class="popup_current_promo popup_copybtn" data-promocode="<?= $key['code'] ?>">
                                            <?= $key['code'] ?><svg><use href="/resources/img/sprite.svg#copy"></use></svg>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        </div>
                        <div class="inputs-inline" id="promoresult"></div>
                        <hr>
                        <div class="inputs-inline">
                            <input type="checkbox" id="checkbox">
                            <label for="checkbox">
                                <?= $Translate->get_translate_module_phrase('module_page_pay', '_AgreeOferta') ?> <a href="<?= $General->arr_general['site'] ?>oferta"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Oferta') ?></a>
                            </label>
                        </div>
                        <button id="paybutton" class="button-pay width-100" form="pay" type="submit" disabled><?= $Translate->get_translate_module_phrase('module_page_pay', '_ButtonPay') ?></button>
                    </div>
                </div>
            </div>
        </form>
        <div style="display: none;" id="resultForm"></div>
    </div>
</div>