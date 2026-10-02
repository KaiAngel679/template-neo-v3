<?php if ($Modules->route != 'bonuses') : ?>
    <?php if (isset($_SESSION['steamid64'])) : ?>
        <?php if ($modalShow == 0) : ?>
            <?php if ($bcBonuses->getCache('settings')['enabled_ds'] || $bcBonuses->getCache('settings')['enabled_tg'] || $bcBonuses->getCache('settings')['enabled_vk']) : ?>
                <div class="popup_modal visible">
                    <div class="popup_modal_content bonuses__decor no-close no-scrollbar">
                        <div class="popup_modal_head">
                            <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_bonuses') ?> 
                            <span class="popup_modal_close">
                                <svg><use href="/resources/img/sprite.svg#x"></use></svg>
                            </span>
                        </div>
                        <div class="bonuses__modal">
                            <div class="bonuses__modal-text">
                                <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_youHaveNewBonus') ?>
                            </div>
                            <div class="bonuses__modal-h3">
                                <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_youHaveBonus') ?>
                                <div class="bonuses__modal-amount">
                                    <?php if ($bcBonuses->getCache('settings')['enabled_ds']) : ?>
                                        <div><span><svg><use href="/resources/img/sprite.svg#ds"></use></svg><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_forDiscordSubscription') ?></span><b>+<?= $bcBonuses->getCache('settings')['money_ds'] ?> <?= $General->currency ?></b></div>
                                    <?php endif; if ($bcBonuses->getCache('settings')['enabled_tg']) : ?>
                                        <div><span><svg><use href="/resources/img/sprite.svg#tg"></use></svg><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_forTelegramSubscription') ?></span><b>+<?= $bcBonuses->getCache('settings')['money_tg'] ?> <?= $General->currency ?></b></div>
                                    <?php endif; if ($bcBonuses->getCache('settings')['enabled_vk']) : ?>
                                        <div><span><svg><use href="/resources/img/sprite.svg#vk"></use></svg><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_forVkSubscription') ?></span><b>+<?= $bcBonuses->getCache('settings')['money_vk'] ?> <?= $General->currency ?></b></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <hr>
                            <div class="bonuses__modal-buttons">
                                <button class="width-100 active" id="takeBonus"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_takeBonus') ?></button>
                                <button class="width-100 button-delete" id="refuseBonus"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_refuseBonus') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>