<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_settings') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="bonuses__settings-wrapper">
                    <div class="bonuses__settings-block">
                        <h3>
                            <svg>
                                <use href="/resources/img/sprite.svg#tg"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_telegramSettings') ?>
                        </h3>
                        <hr>
                        <div class="inputs-inline">
                            <input type="checkbox" id="enableTg" class="switch" <?= $bs->getCache('settings')['enabled_tg'] == 1 ? 'checked' : '' ?>>
                            <label for="enableTg"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_enableBonus') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <label for="descriptionTg"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_bannerDescription') ?></label>
                            <textarea id="descriptionTg" rows="4" placeholder="<?= $Translate->get_translate_module_phrase('module_page_bonuses', '_yourDescriptionHere') ?>" autofocus="" required="" maxlength="1000" spellcheck="true" wrap="hard"><?= $bs->getCache('settings')['text_tg'] ?></textarea>
                        </div>
                        <div class="inputs-inline">
                            <label for="tgMoney"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_amountOfMoney') ?></label>
                            <div class="number" id="numberControl">
                                <button class="number-minus" type="button">-</button>
                                <input id="tgMoney" type="number" min="0" value="<?= $bs->getCache('settings')['money_tg'] ?>">
                                <button class="number-plus" type="button">+</button>
                            </div>
                        </div>
                        <div class="inputs-inline">
                            <label for="tgLink"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_telegramLink') ?></label>
                            <input type="text" id="tgLink" value="<?= $bs->getCache('settings')['url_tg'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label for="tgKey"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_telegramBotSecretKey') ?></label>
                            <div class="number">
                                <input type="password" id="tgKey" value="<?= $bs->getCache('settings')['bot_key_tg'] ?>">
                                <div class="eye-password" id="show_pass">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#eye"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="inputs-inline">
                            <label for="tgId"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_telegramBotId') ?></label>
                            <input type="text" id="tgId" value="<?= $bs->getCache('settings')['bot_id_tg'] ?>">
                        </div>
                        <button class="width-100" id="save-settings-tg"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_saveSettings') ?></button>
                    </div>
                    <div class="bonuses__settings-block">
                        <h3>
                            <svg>
                                <use href="/resources/img/sprite.svg#ds"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_discordSettings') ?>
                        </h3>
                        <hr>
                        <div class="inputs-inline">
                            <input type="checkbox" id="enableDs" class="switch" <?= $bs->getCache('settings')['enabled_ds'] == 1 ? 'checked' : '' ?>>
                            <label for="enableDs"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_enableBonus') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <label for="descriptionDs"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_bannerDescription') ?></label>
                            <textarea id="descriptionDs" rows="4" placeholder="<?= $Translate->get_translate_module_phrase('module_page_bonuses', '_yourDescriptionHere') ?>" autofocus="" required="" maxlength="1000" spellcheck="true" wrap="hard"><?= $bs->getCache('settings')['text_ds'] ?></textarea>
                        </div>
                        <div class="inputs-inline">
                            <label for="DsMoney"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_amountOfMoney') ?></label>
                            <div class="number" id="numberControl">
                                <button class="number-minus" type="button">-</button>
                                <input id="DsMoney" type="number" min="0" value="<?= $bs->getCache('settings')['money_ds'] ?>">
                                <button class="number-plus" type="button">+</button>
                            </div>
                        </div>
                        <div class="inputs-inline">
                            <label for="DsLink"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_discordInviteLink') ?></label>
                            <input type="text" id="DsLink" value="<?= $bs->getCache('settings')['url_ds'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label for="DsGuild"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_discordGuildId') ?></label>
                            <input type="number" id="DsGuild" value="<?= $bs->getCache('settings')['guild_id_ds'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label for="DsPubKey"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_discordPublicKey') ?></label>
                            <div class="number">
                                <input type="password" id="DsPubKey" value="<?= $bs->getCache('settings')['client_id_ds'] ?>">
                                <div class="eye-password" id="show_pass">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#eye"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="inputs-inline">
                            <label for="DsSecKey"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_discordSecretKey') ?></label>
                            <div class="number">
                                <input type="password" id="DsSecKey" value="<?= $bs->getCache('settings')['secret_id_ds'] ?>">
                                <div class="eye-password" id="show_pass">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#eye"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <button class="width-100" id="save-settings-ds"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_saveSettings') ?></button>
                    </div>
                    <div class="bonuses__settings-block">
                        <h3>
                            <svg>
                                <use href="/resources/img/sprite.svg#vk"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_vkSettings') ?>
                        </h3>
                        <hr>
                        <div class="inputs-inline">
                            <input type="checkbox" id="enableVk" class="switch" <?= $bs->getCache('settings')['enabled_vk'] == 1 ? 'checked' : '' ?>>
                            <label for="enableVk"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_enableBonus') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <label for="descriptionVk"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_bannerDescription') ?></label>
                            <textarea id="descriptionVk" rows="4" placeholder="<?= $Translate->get_translate_module_phrase('module_page_bonuses', '_yourDescriptionHere') ?>" autofocus="" required="" maxlength="1000" spellcheck="true" wrap="hard"><?= $bs->getCache('settings')['text_vk'] ?></textarea>
                        </div>
                        <div class="inputs-inline">
                            <label for="VkMoney"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_amountOfMoney') ?></label>
                            <div class="number" id="numberControl">
                                <button class="number-minus" type="button">-</button>
                                <input id="VkMoney" type="number" min="0" value="<?= $bs->getCache('settings')['money_vk'] ?>">
                                <button class="number-plus" type="button">+</button>
                            </div>
                        </div>
                        <div class="inputs-inline">
                            <label for="VkLink"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_vkPublicLink') ?></label>
                            <input type="text" id="VkLink" value="<?= $bs->getCache('settings')['url_vk'] ?>">
                        </div>
                        <button class="width-100" id="save-settings-vk"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_saveSettings') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>