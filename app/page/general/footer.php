</div>
<?php if (!empty($settings_neo['ContactEmail'])): ?>
    <div class="popup_modal" id="popupContacts">
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                <?= $Translate->get_translate_phrase('_ContactInfo') ?>
                <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <div class="contact_body">
                <span><?= $Translate->get_translate_phrase('_ContactRegarding') ?>
                    <img src="/resources/img/contact/mail.png" alt="">
                    <br>
                    <?php if (!empty($settings_neo['ContactEmail'])) : ?>
                        <a class="contact_mail" href="mailto:<?= $settings_neo['ContactEmail'] ?>&body=<?= $Translate->get_translate_phrase('_Greetings') ?>?subject=<?= $Translate->get_translate_phrase('_haveQuestion') ?>">
                            <?= $settings_neo['ContactEmail'] ?>
                        </a>
                    <?php else: ?>
                        <?= $Translate->get_translate_phrase('_IsHidden'); ?>
                    <?php endif; ?>
                </span>
                <?php if (!empty($settings_neo['SupportLink'])): ?>
                    <button class="width-100" onclick="location.href='<?= !empty($settings_neo['SupportLink']) ? $settings_neo['SupportLink'] : '' ?>'">
                        <?= $Translate->get_translate_phrase('_ContactFeedback') ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<footer class="footer_fluid">
    <div class="tabbar_mobile">
        <a href="<?= $General->arr_general['site'] ?>" class="<?php if ($Modules->route == 'home') echo 'tabbar_active' ?>">
            <svg>
                <use href="/resources/img/sprite.svg#gamepad"></use>
            </svg>
            <?= $Translate->get_translate_phrase('_Sidebar_servers') ?>
        </a>
        <a href="<?= $General->arr_general['site'] ?>store" class="<?php if ($Modules->route == 'store') echo 'tabbar_active' ?>">
            <svg>
                <use href="/resources/img/sprite.svg#store-build"></use>
            </svg>
            <?= $Translate->get_translate_phrase('_SP') ?>
        </a>
        <?php if (!empty($Db->db_data['lk'])): ?>
            <a data-openmodal="popupPay">
                <svg>
                    <use href="/resources/img/sprite.svg#wallet"></use>
                </svg>
                <?= $Translate->get_translate_phrase('_Current_balance') ?>
            </a>
        <?php endif;
        if ($_SESSION['steamid']): ?>
            <a href="<?= $General->arr_general['site'] ?>profiles/<?= $_SESSION['steamid'] ?>/?search=1" class="<?php if ($Modules->route == 'profiles')
                                                                                                                    echo 'tabbar_active' ?>">
                <svg>
                    <use href="/resources/img/sprite.svg#user-solo"></use>
                </svg>
                <?= $Translate->get_translate_phrase('_Profile') ?>
            </a>
        <?php endif; ?>
    </div>
    <div class="footer_global">
        <div class="footer-top">
            <div class="left_footer">
                <a href="<?= $General->arr_general['site'] ?>">
                    <div class="footer_sitename">
                        <?= $settings_neo['SiteName'] ?>
                    </div>
                </a>
            </div>
            <div class="footer_links">
                <ul>
                    <?php foreach ($General->get_neo_footer() as $menu): ?>
                        <?php
                        $hasMenuRestrictions = !empty($menu['onlyAdminSite']) || !empty($menu['onlyAuth']) || !empty($menu['onlyAdmin']) || !empty($menu['onlyAdminServer']);
                        $menuAllows = !$hasMenuRestrictions || (
                            (!empty($menu['onlyAdminSite']) && $isAdmin) ||
                            (!empty($menu['onlyAuth']) && $isAuth) ||
                            (!empty($menu['onlyAdmin']) && $hasAdminAccess) ||
                            (!empty($menu['onlyAdminServer']) && $hasServerAccess)
                        );
                        $link = !empty($menu['url']) ? $menu['url'] : '/' . ($Modules->array_modules[$menu['module']]['page'] ?? '');
                        if ($isAdmin || $menuAllows): ?>
                            <li class="">
                                <a href="<?= $link ?>" <?= !empty($menu['blank']) ? 'target="_blank"' : '' ?>>
                                    <?= (isset($menu['title'][0]) && $menu['title'][0] === '_' ? $Translate->get_translate_phrase($menu['title']) : $menu['title']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!empty($settings_neo['SupportLink'])): ?>
                        <li class="">
                            <a href="<?= $settings_neo['SupportLink'] ?>"><?= $Translate->get_translate_phrase('_Support') ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($settings_neo['ContactEmail'])): ?>
                        <li class="contact_link" data-openmodal="popupContacts">
                            <a><?= $Translate->get_translate_phrase('_Contacts') ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="social_buttons">
                <?php if (!empty($settings_neo['VK'])): ?>
                    <a class="social_button" href="<?= $settings_neo['VK'] ?>" target="_blank">
                        <svg>
                            <use href="/resources/img/sprite.svg#vk"></use>
                        </svg>
                    </a>
                <?php endif; ?>
                <?php if (!empty($settings_neo['TG'])): ?>
                    <a class="social_button" href="<?= $settings_neo['TG'] ?>" target="_blank">
                        <svg>
                            <use href="/resources/img/sprite.svg#tg"></use>
                        </svg>
                    </a>
                <?php endif; ?>
                <?php if (!empty($settings_neo['Steam'])): ?>
                    <a class="social_button" href="<?= $settings_neo['Steam'] ?>" target="_blank">
                        <svg>
                            <use href="/resources/img/sprite.svg#steam"></use>
                        </svg>
                    </a>
                <?php endif; ?>
                <?php if (!empty($settings_neo['DS'])): ?>
                    <a class="social_button" href="<?= $settings_neo['DS'] ?>" target="_blank">
                        <svg>
                            <use href="/resources/img/sprite.svg#ds"></use>
                        </svg>
                    </a>
                <?php endif; ?>
                <?php if (!empty($settings_neo['YT'])): ?>
                    <a class="social_button" href="<?= $settings_neo['YT'] ?>" target="_blank">
                        <svg>
                            <use href="/resources/img/sprite.svg#yt"></use>
                        </svg>
                    </a>
                <?php endif; ?>
                <?php if (!empty($settings_neo['TT'])): ?>
                    <a class="social_button" href="<?= $settings_neo['TT'] ?>" target="_blank">
                        <svg>
                            <use href="/resources/img/sprite.svg#tt"></use>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <div class="footer-bottom">
            <p class="footer-description"><?= $General->arr_general['info'] ?></p>
        </div>
        <?php if (empty($General->arr_general['watermark'])): ?>
            <noindex class="footer-watermark" data-noindex="true">
                <?= $Translate->get_translate_phrase('_Made') ?>
            </noindex>
        <?php endif; ?>
        <?php if ($Modules->route == 'home'): ?>
            <?php if (!empty($_SESSION['steamid']) && isset($_SESSION['user_admin'])): ?>
                <button class="footer__theme-button" id="openThemeEditor">
                    <svg>
                        <use href='/resources/img/sprite.svg#palette'></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_block_main_theme', '_change') ?>
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <dialog id="modal" aria-labelledby="dialog-header" aria-describedby="dialog-content">
        <h1 id="dialog-header">Подтверждение действия</h1>
        <div id="dialog-content" class="dialog-content">
            Удалить?
        </div>
        <div class="dialog-buttons">
            <button class="width-100" id="confirm-btn">Да</button>
            <button class="width-100" id="cancel-btn">Нет</button>
        </div>
    </dialog>

</footer>
</div>
<script src="/storage/assets/js/vendors/jquery/jquery-3.7.1.min.js"></script>
<script src="/storage/assets/js/vendors/jquery/jquery-ui.min.js"></script>

<?php if ($Modules->route == 'home'): ?>
    <script src="/app/templates/neo_remastered/assets/js/swiper-bundle.min.js"></script>
<?php endif; ?>
<script src="/app/templates/neo_remastered/assets/js/popper.min.js"></script>
<script src="/app/templates/neo_remastered/assets/js/iziToast.min.js"></script>
<script src="/app/templates/neo_remastered/assets/js/clipboard.min.js"></script>
<script src="/app/templates/neo_remastered/assets/js/search.js" defer></script>
<script src="/app/templates/neo_remastered/assets/js/stars.js<?php $General->arr_general['css_off_cache'] == 1 && print '?' . time(); ?>" defer></script>
<script src="/storage/assets/js/jscolor.min.js"></script>
<?php if (isset($General->arr_general['enable_decoration']) && $General->arr_general['enable_decoration'] === 'snowfall'): ?>
    <script src="/app/templates/neo_remastered/assets/js/snowfall.js<?php $General->arr_general['css_off_cache'] == 1 && print '?' . time(); ?>" defer></script>
<?php endif; ?>
<?php for ($js = 0, $js_s = sizeof($Modules->js_library); $js < $js_s; $js++): ?>
    <script
        src="/<?= $Modules->js_library[$js] ?><?php $General->arr_general['css_off_cache'] == 1 && print "?" . time() ?>"></script>
<?php endfor; ?>
<?php if (!empty($Modules->arr_module_init['js_always'])) :?>
    <?php for ($module_id = 0, $c_mi = sizeof($Modules->arr_module_init['js_always']); $module_id < $c_mi; $module_id++) :?>
        <?php if(file_exists(MODULES . $Modules->arr_module_init['js_always'][$module_id] . '/assets/js/always.js')): ?>
            <script src="/app/modules/<?= $Modules->arr_module_init['js_always'][$module_id] . '/assets/js/always.js' ?><?php $General->arr_general['css_off_cache'] == 1 && print "?" . time() ?>"></script>
        <?php endif; ?>
    <?php endfor; ?>
<?php endif; ?>
<?php if (!empty($Modules->arr_module_init['page'][$Modules->route]['js'])):
    for ($js = 0, $js_s = sizeof($Modules->arr_module_init['page'][$Modules->route]['js']); $js < $js_s; $js++): ?>
        <script src="/app/modules/<?= $Modules->arr_module_init['page'][$Modules->route]['js'][$js]['name'] . '/assets/js/' . $Modules->arr_module_init['page'][$Modules->route]['js'][$js]['type'] . '.js' ?><?php $General->arr_general['css_off_cache'] == 1 && print "?" . time() ?>"></script>
<?php endfor;
endif; ?>
</body>

</html>