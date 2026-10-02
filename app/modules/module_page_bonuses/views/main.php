<div class="row">
    <div class="col-md-12">
        <div class="bonuses__wrapper">
            <h1 class="bonuses__title"><svg><use href="/resources/img/sprite.svg#gift"></use></svg><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_bonuses') ?></h1>
            <div class="bonuses__containers">
                <?php if ($bs->getCache('settings')['enabled_tg']) : ?>
                    <div class="bonuses__container-tg">
                        <div class="bonuses__container-content">
                            <div class="bonuses__container-title"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_subscriptionTelegram') ?></div>
                            <div class="bonuses__container-description"><?= $bs->getCache('settings')['text_tg'] ?></div>
                            <?php if(!$bs->checkReward($_SESSION['steamid64'])['tg_reward']) : ?>
                                <div class="bonuses__container-buttons">
                                    <button data-openmodal="subTg">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#tg"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_execute') ?>
                                    </button>
                                    <div class="bonuses__container-price">+<?= $bs->getCache('settings')['money_tg'] ?><?= $General->currency ?></div>
                                </div>
                            <?php else: ?>
                                <div class="bonuses__container-buttons">
                                    <button disabled>
                                        <svg>
                                            <use href="/resources/img/sprite.svg#tg"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_done') ?>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="bonuses__container-image">
                            <img src="/app/modules/module_page_bonuses/assets/img/tg.png" alt="">
                        </div>
                    </div>
                <?php endif; if ($bs->getCache('settings')['enabled_ds']) : ?>
                    <div class="bonuses__container-ds">
                        <div class="bonuses__container-content">
                            <div class="bonuses__container-title"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_subscriptionDiscord') ?></div>
                            <div class="bonuses__container-description"><?= $bs->getCache('settings')['text_ds'] ?></div>
                            <?php if(!$bs->checkReward($_SESSION['steamid64'])['ds_reward']) : ?>
                                <div class="bonuses__container-buttons">
                                    <button data-openmodal="subDs">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#ds"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_execute') ?>
                                    </button>
                                    <div class="bonuses__container-price">+<?= $bs->getCache('settings')['money_ds'] ?><?= $General->currency ?></div>
                                </div>
                            <?php else: ?>
                                <div class="bonuses__container-buttons">
                                    <button disabled>
                                        <svg>
                                            <use href="/resources/img/sprite.svg#ds"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_done') ?>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="bonuses__container-image">
                            <img src="/app/modules/module_page_bonuses/assets/img/ds.png" alt="">
                        </div>
                    </div>
                <?php endif; if ($bs->getCache('settings')['enabled_vk']) : ?>
                    <div class="bonuses__container-vk">
                        <div class="bonuses__container-content">
                            <div class="bonuses__container-title"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_subscriptionVk') ?></div>
                            <div class="bonuses__container-description"><?= $bs->getCache('settings')['text_vk'] ?></div>
                            <?php if(!$bs->checkReward($_SESSION['steamid64'])['vk_reward']) : ?>
                                <div class="bonuses__container-buttons">
                                    <button data-openmodal="subVk">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#vk"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_execute') ?>
                                    </button>
                                    <div class="bonuses__container-price">+<?= $bs->getCache('settings')['money_vk'] ?><?= $General->currency ?></div>
                                </div>
                            <?php else: ?>
                                <div class="bonuses__container-buttons">
                                    <button disabled>
                                        <svg>
                                            <use href="/resources/img/sprite.svg#vk"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_done') ?>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="bonuses__container-image">
                            <img src="/app/modules/module_page_bonuses/assets/img/vk.png" alt="">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="bonuses__container-socials">
                <div class="bonuses__container-content">
                    <div class="bonuses__container-title"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_heyFriend') ?></div>
                    <div class="bonuses__container-description"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_heyFriendDescription') ?></div>
                    <div class="bonuses__container-buttons">
                        <?php if ($General->get_neo_options()['TG']) : ?>
                            <button onclick="window.open('<?= $General->get_neo_options()['TG'] ?>', '_blank')">
                                <svg>
                                    <use href="/resources/img/sprite.svg#tg"></use>
                                </svg>
                            </button>
                        <?php endif; if ($General->get_neo_options()['DS']) : ?>
                            <button onclick="window.open('<?= $General->get_neo_options()['DS'] ?>', '_blank')">
                                <svg>
                                    <use href="/resources/img/sprite.svg#ds"></use>
                                </svg>
                            </button>
                        <?php endif; if ($General->get_neo_options()['VK']) : ?>
                            <button onclick="window.open('<?= $General->get_neo_options()['VK'] ?>', '_blank')">
                                <svg>
                                    <use href="/resources/img/sprite.svg#vk"></use>
                                </svg>
                            </button>
                        <?php endif; if ($General->get_neo_options()['TT']) : ?>
                            <button onclick="window.open('<?= $General->get_neo_options()['TT'] ?>', '_blank')">
                                <svg>
                                    <use href="/resources/img/sprite.svg#tt"></use>
                                </svg>
                            </button>
                        <?php endif; if ($General->get_neo_options()['YT']) : ?>
                            <button onclick="window.open('<?= $General->get_neo_options()['YT'] ?>', '_blank')">
                                <svg>
                                    <use href="/resources/img/sprite.svg#yt"></use>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bonuses__container-video">
                    <video class="store__video-empty" preload="auto" playsinline autoplay loop muted>
                        <source src="/app/modules/module_page_bonuses/assets/video/other.mp4" type="video/mp4; codecs=&quot;hvc1&quot;">
                        <source src="/app/modules/module_page_bonuses/assets/video/other.webm" type="video/webm">
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if(!$bs->checkReward($_SESSION['steamid64'])['tg_reward'] && $bs->getCache('settings')['enabled_tg']) : ?>
    <div class="popup_modal" id="subTg">
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_checkingSubscription') ?> <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <div class="bonuses__modal-sub">
                <a href="<?= $bs->getCache('settings')['url_tg'] ?>" target="_blank" class="button active width-100">
                    <svg><use href="/resources/img/sprite.svg#tg"></use></svg>
                    <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_subscribe') ?>
                </a>
                <a href="https://oauth.telegram.org/auth?bot_id=<?= $bs->getCache('settings')['bot_id_tg'] ?>&origin=https:<?= $General->arr_general['site'] ?>&request_access=write&return_to=https:<?= $General->arr_general['site'] ?>bonuses" class="button width-100"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_checkSubscription') ?></a>
            </div>
        </div>
    </div>
<?php endif; if(!$bs->checkReward($_SESSION['steamid64'])['ds_reward'] && $bs->getCache('settings')['enabled_ds']) : ?>
    <div class="popup_modal" id="subDs">
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_checkingSubscription') ?> <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <div class="bonuses__modal-sub">
                <a href="<?= $bs->getCache('settings')['url_ds'] ?>" target="_blank" class="button active width-100">
                    <svg><use href="/resources/img/sprite.svg#ds"></use></svg>
                    <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_subscribe') ?>
                </a>
                <a href="https://discord.com/api/oauth2/authorize?client_id=<?= $bs->getCache('settings')['client_id_ds'] ?>&redirect_uri=<?= urlencode('https:' . $General->arr_general['site'] . 'bonuses') ?>&response_type=code&scope=identify%20guilds&state=discord" class="button width-100"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_checkSubscription') ?></a>
            </div>
        </div>
    </div>
<?php endif; if(!$bs->checkReward($_SESSION['steamid64'])['vk_reward'] && $bs->getCache('settings')['enabled_vk']) : ?>
    <div class="popup_modal" id="subVk">
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_checkingSubscription') ?> <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <div class="bonuses__modal-sub">
                <a href="<?= $bs->getCache('settings')['url_vk'] ?>" target="_blank" class="button active width-100">
                    <svg><use href="/resources/img/sprite.svg#vk"></use></svg>
                    <?= $Translate->get_translate_module_phrase('module_page_bonuses', '_subscribe') ?>
                </a>
                <button class="width-100" id="checkSubVk"><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_checkSubscription') ?></button>
            </div>
        </div>
    </div>
<?php endif; ?>