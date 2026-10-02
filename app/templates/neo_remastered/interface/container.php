<?php $settings_neo = $General->get_neo_options(); ?>
<div class="navbar row">
    <div class="navbar_menu_left">
        <a class="logo-text" href="/">
            <?php if ($settings_neo['typeLofo'] == '2' && file_exists(STORAGE . 'cache/img/global/' . $settings_neo['Logo'])): ?>
                <div class="site_logo_neo" data-tippy-content="<?= $settings_neo['SiteName'] ?>" data-tippy-placement="bottom">
                    <img class="logo" src="/storage/cache/img/global/<?= $settings_neo['Logo'] ?>" alt="">
                </div>
            <?php else: ?>
                <div class="nav_sitename">
                    <?= $settings_neo['SiteName'] ?>
                </div>
            <?php endif; ?>
        </a>
        <?php if (file_exists(MODULES . 'module_block_main_online_stats/description.json')): ?>
            <div class="online">
                <div class="online__counter" id="counter">
                    <div class="online__square-wrapper"><span class="online__square-figure"></span></div>
                    <div class="online__count" id="servers_total"><?= $General->getOnlineSiteAndMods()['servers'] ?></div>
                </div>
                <div class="online__wrapper" id="counterWrapper">
                    <?php foreach ($General->getOnlineSiteAndMods()['server_mods'] as $name => $mod): ?>
                        <div class="online__item">
                            <svg>
                                <use href="/resources/img/sprite.svg#gamepad"></use>
                            </svg>
                            <div class="online__item-server-name"><?= $name ?>:</div><span
                                id="mod_<?= $name ?>"><?= $mod ?></span>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="online__item">
                        <svg>
                            <use href="/resources/img/sprite.svg#three-users"></use>
                        </svg> <?= $Translate->get_translate_phrase('_Navbar_Online') ?>: <span
                            id="site_olnine"><?= $General->getOnlineSiteAndMods()['site'] ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!isset($General->arr_general['navigation']) || $General->arr_general['navigation'] == 'navbar'): ?>
            <div class="drpdwn_menu  <?= ($General->arr_general['compactNav'] == 1) ? 'compact' : ''; ?>">
                <?php foreach ($General->get_neo_menu() as $menu): ?>
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
                        <?php if ($menu['type'] == 'category'): ?>
                            <?php
                            $childLinks = array_map(function ($point) use ($Modules) {
                                $lp = !empty($point['url']) ? $point['url'] : '/' . ($Modules->array_modules[$point['module']]['page'] ?? '');
                                return trim($lp, '/');
                            }, $menu['children']);
                            $categoryActive = in_array(trim($Modules->route, '/'), $childLinks, true);
                            ?>
                            <div class="dropdown">
                                <div class="dropbtn <?= $categoryActive ? 'active_btn' : '' ?>">
                                    <?= $menu['icon'] ?>
                                    <span class="desc_nav_item"><?= (isset($menu['title'][0]) && $menu['title'][0] === '_' ? $Translate->get_translate_phrase($menu['title']) : $menu['title']) ?></span>
                                </div>
                                <div class="dropdown_block">
                                    <?php foreach ($menu['children'] as $point): ?>
                                        <?php
                                        $hasPointRestrictions = !empty($point['onlyAdminSite']) || !empty($point['onlyAuth']) || !empty($point['onlyAdmin']) || !empty($point['onlyAdminServer']);
                                        $pointAllows = !$hasPointRestrictions || (
                                            (!empty($point['onlyAdminSite']) && $isAdmin) ||
                                            (!empty($point['onlyAuth']) && $isAuth) ||
                                            (!empty($point['onlyAdmin']) && $hasAdminAccess) ||
                                            (!empty($point['onlyAdminServer']) && $hasServerAccess)
                                        );
                                        $link_point = !empty($point['url']) ? $point['url'] : '/' . $Modules->array_modules[$point['module']]['page'];
                                        if ($isAdmin || $pointAllows): ?>
                                            <a href="<?= $link_point ?>" <?= $point['blank'] ? 'target="_blank"' : '' ?> class="<?= ($Modules->route == substr($link_point, 1)) ? 'active_btn' : ''; ?>">
                                                <div class="dropdown_item">
                                                    <?= $point['icon'] ?>
                                                    <div>
                                                        <h4><?= (isset($point['title'][0]) && $point['title'][0] === '_' ? $Translate->get_translate_phrase($point['title']) : $point['title']) ?></h4>
                                                        <p><?= $point['description'] ?></p>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php elseif ($menu['type'] == 'point'): ?>
                            <div class="dropdown">
                                <a href="<?= $link ?>" <?= $menu['blank'] ? 'target="_blank"' : '' ?> class="dropbtn <?= ($Modules->route == substr($link, 1) || (substr($link, 1) == '' && $Modules->route == 'home')) ? 'active_btn' : ''; ?>">
                                    <?= $menu['icon'] ?>
                                    <span class="desc_nav_item"><?= (isset($menu['title'][0]) && $menu['title'][0] === '_' ? $Translate->get_translate_phrase($menu['title']) : $menu['title']) ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="header_burger">
            <span></span>
        </div>
        <div class="nav_header_menu no_scroll">
            <ul class="header_list">
                <?php foreach ($General->get_neo_menu() as $menu): ?>
                    <?php
                    $link = !empty($menu['url']) ? $menu['url'] : '/' . ($Modules->array_modules[$menu['module']]['page'] ?? '');
                    $hasMenuRestrictions = !empty($menu['onlyAdminSite']) || !empty($menu['onlyAuth']) || !empty($menu['onlyAdmin']) || !empty($menu['onlyAdminServer']);
                    $menuAllows = !$hasMenuRestrictions || (
                        (!empty($menu['onlyAdminSite']) && $isAdmin) ||
                        (!empty($menu['onlyAuth']) && $isAuth) ||
                        (!empty($menu['onlyAdmin']) && $hasAdminAccess) ||
                        (!empty($menu['onlyAdminServer']) && $hasServerAccess)
                    );
                    if ($isAdmin || $menuAllows): ?>
                        <?php if ($menu['type'] == 'category'): ?>
                            <?php foreach ($menu['children'] as $point): ?>
                                <?php
                                $hasPointRestrictions = !empty($point['onlyAdminSite']) || !empty($point['onlyAuth']) || !empty($point['onlyAdmin']) || !empty($point['onlyAdminServer']);
                                $pointAllows = !$hasPointRestrictions || (
                                    (!empty($point['onlyAdminSite']) && $isAdmin) ||
                                    (!empty($point['onlyAuth']) && $isAuth) ||
                                    (!empty($point['onlyAdmin']) && $hasAdminAccess) ||
                                    (!empty($point['onlyAdminServer']) && $hasServerAccess)
                                );
                                $link_point = !empty($point['url']) ? $point['url'] : '/' . ($Modules->array_modules[$point['module']]['page'] ?? '');
                                if ($isAdmin || $pointAllows): ?>
                                    <li>
                                        <a href="<?= $link_point ?>" <?= $point['blank'] ? 'target="_blank"' : '' ?>><?= (isset($point['title'][0]) && $point['title'][0] === '_' ? $Translate->get_translate_phrase($point['title']) : $point['title']) ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php elseif ($menu['type'] == 'point'): ?>
                            <li>
                                <a href="<?= $link ?>" <?= $menu['blank'] ? 'target="_blank"' : '' ?>><?= (isset($menu['title'][0]) && $menu['title'][0] === '_' ? $Translate->get_translate_phrase($menu['title']) : $menu['title']) ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="navbar_usermenu">
        <?php if (isset($General->arr_general['enable_decoration']) && $General->arr_general['enable_decoration'] === 'snowfall'): ?>
            <button class="snowfal-icon button-icon" data-openmodal="popupSnowfall">
                <svg width="512" height="512" x="0" y="0" viewBox="0 0 390.4 390.4" style="enable-background:new 0 0 512 512" xml:space="preserve" class="">
                    <path d="m293.2 195.2-48.8-16.8-28.8 16.8 28.8 16.8 48.8-16.8zm-87.6-17.6 28.8-16.8 10-50.4-38.8 33.6v33.6zM244 280l-10-50.4-28.8-16.8V246l38.8 34zm-97.6 0 38.8-33.6v-33.2L156.4 230l-10 50zm-49.2-84.8L146 212l28.8-16.8-28.8-16.8-48.8 16.8zm58.8-34.4 28.8 16.8v-33.2L146 110.8l10 50zm28.8-150.4c0-5.6 4.4-10.4 10.4-10.4 5.6 0 10.4 4.4 10.4 10.4v28.8l21.2-18.4c4.4-3.6 10.8-3.2 14.4.8 3.6 4.4 3.2 10.8-.8 14.4L206 66v51.2l45.6-39.6c3.6-3.2 8.8-3.2 12.4-.8 4.4 2 6.8 6.4 6 11.2l-12.4 59.2 44.4-25.6 8.8-44.8c1.2-5.6 6.4-9.2 12-8s9.2 6.4 8 12l-5.6 27.6L350 94c4.8-2.8 11.2-1.2 14 3.6s1.2 11.2-3.6 14L335.6 126l26.4 9.2c5.2 2 8 7.6 6.4 12.8-2 5.2-7.6 8-12.8 6.4l-43.2-14.8-44.4 25.6 57.2 19.6c4.4 1.6 7.2 6 6.8 10.8.4 4.8-2.4 9.2-6.8 10.8L268 226l44.4 25.6 43.2-14.8c5.2-2 11.2 1.2 12.8 6.4 2 5.2-1.2 11.2-6.4 12.8l-26.4 9.2 24.8 14.4c4.8 2.8 6.4 9.2 3.6 14s-9.2 6.4-14 3.6l-24.8-14.4 5.6 27.6c1.2 5.6-2.4 10.8-8 12s-10.8-2.4-12-8l-8.8-44.8-44.4-26.4 11.6 59.2c.8 4.8-1.6 9.6-6 11.2-3.6 2.8-8.8 2.4-12.4-.8l-45.6-39.6v51.2l34.4 30c4.4 3.6 4.8 10 .8 14.4-3.6 4.4-10 4.8-14.4.8l-21.2-18.4V380c0 5.6-4.4 10.4-10.4 10.4-5.6 0-10.4-4.4-10.4-10.4v-28.8l-21.2 18.4c-4.4 3.6-10.8 3.2-14.4-.8-3.6-4.4-3.2-10.8.8-14.4l34.4-30v-51.2L138 312.8c-3.6 3.2-8.8 3.2-12.4.8-4.4-2-6.8-6.4-6-11.2l13.2-59.2-44.4 25.6-8.8 44.8c-1.2 5.6-6.4 9.2-12 8s-9.2-6.4-8-12l5.6-27.6-24.8 14.4c-4.8 2.8-11.2 1.2-14-3.6-2.8-4.8-1.2-11.2 3.6-14l24.8-14.4-26.4-9.2c-5.2-2-8-7.6-6.4-12.8 2-5.2 7.6-8 12.8-6.4L78 250.8l38.4-22 6-3.6-57.2-19.6c-4.4-1.6-7.2-6-6.8-10.8-.4-4.8 2.4-9.2 6.8-10.8l57.2-19.6L78 138.8l-43.2 14.8c-5.2 2-11.2-1.2-12.8-6.4-2-5.2 1.2-11.2 6.4-12.8l26.4-9.2L30 110.8c-4.8-2.8-6.4-9.2-3.6-14s9.2-6.4 14-3.6l24.8 14.4L60 80c-1.2-5.6 2.4-10.8 8-12s10.8 2.4 12 8l8.8 44.8 44 26.4L121.2 88c-.8-4.8 1.6-9.6 6-11.2 3.6-2.8 8.8-2.4 12.4.8l45.6 39.6V66l-34.4-30c-4.4-3.6-4.8-10-.8-14.4 3.6-4.4 10-4.8 14.4-.8l21.2 18.4V10.4h-.8z" style="" fill="#acd3f0" data-original="#acd3f0" class=""></path>
                    <path d="m146.4 280 10-50.4 28.8-16.8V246l-38.8 34zm38.4-102.4L156 160.8l-10-50.4 38.8 33.6v33.6zM146 212l-48.8-16.8 48.8-16.8 28.8 16.8L146 212zm-57.6 56.4 44.4-25.2-11.6 59.2c-.8 4.8 1.6 9.6 6 11.2 3.6 2.8 8.8 2.4 12.4-.8l45.6-39.6v51.2l-34.4 30c-4.4 3.6-4.8 10-.8 14.4 3.6 4.4 10 4.8 14.4.8l21.2-18.4V380c0 5.6 4.4 10.4 10.4 10.4V0c-5.6 0-10.4 4.4-10.4 10.4v28.8l-21.2-18.4c-4.4-3.6-10.8-3.2-14.4.8-3.6 4.4-3.2 10.8.8 14.4l34.4 30v51.2l-45.6-39.6c-3.6-3.2-8.8-3.2-12.4-.8-4.4 2-6.8 6.4-6 11.2l11.6 59.2-44.4-25.6-8.8-44.8c-1.2-5.6-6.4-9.2-12-8s-9.2 6.4-8 12l5.2 27.6L40 94c-4.8-2.8-11.2-1.2-14 3.6s-1.2 11.2 3.6 14L54.4 126l-26 9.2c-5.2 2-8 7.6-6.4 12.8 2 5.2 7.6 8 12.8 6.4L78 139.6l44.4 25.6-57.2 19.6c-4.4 1.6-7.2 6-6.8 10.8-.4 4.8 2.4 9.2 6.8 10.8l57.2 19.6-6 3.6-38.4 22-43.2-14.8c-5.2-2-11.2 1.2-12.8 6.4-2 5.2 1.2 11.2 6.4 12.8l26.4 9.2L30 279.6c-4.8 2.8-6.4 9.2-3.6 14s9.2 6.4 14 3.6l24.8-14.4-5.6 27.6c-1.2 5.6 2.4 10.8 8 12s10.8-2.4 12-8l8.8-46z" style="" fill="#c7e2f5" data-original="#c7e2f5" class=""></path>
                </svg>
            </button>
        <?php endif; ?>
        <?php if ((!$General->arr_general['searchForAuth'] || isset($_SESSION['steamid64'])) && !$General->arr_general['disabledSearch']): ?>
            <div class="search_players" id="open_search">
                <div class="modal-searchcontent">
                    <form enctype="multipart/form-data" method="post">
                        <input class="search-input-block" type="text"
                            placeholder="<?= $Translate->get_translate_phrase('_PlaceholderSearch') ?>" name="_steam_id">
                        <input type="hidden" name="btn_search">
                        <svg>
                            <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                        </svg>
                    </form>
                </div>
                <div class="modal_users_container">
                    <div class="modal_blocks_content no-scrollbar">
                        <div class="modal_users_header" id="search_header"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($Db->db_data['lk'])): ?>
            <button class="pay_button" data-openmodal="popupPay">
                <svg>
                    <use href="/resources/img/sprite.svg#wallet"></use>
                </svg>
                <span class="hide_balance_text">
                    <?= $Translate->get_translate_module_phrase('module_page_pay', '_ButtonPay') ?>
                </span>
            </button>
        <?php endif; ?>
        <?php if (!empty($_SESSION['steamid'])): ?>
            <?php if (file_exists(MODULES . 'module_page_voucher/description.json')):
                require MODULES . 'module_page_voucher/ext/inc/buttons/1.php';
            endif; ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['steamid'])): ?>
            <button class="user__notifications button-icon" id="notifyOpen">
                <svg>
                    <use href="/resources/img/sprite.svg#bell"></use>
                </svg>
                <div class="search notification"><span id="main_notifications_badge"></span></div>
            </button>
            <div class="notification-wrapper">
                <div class="notifications__header">
                    <div class="notifications__title"><?= $Translate->get_translate_phrase('_Notifications') ?></div>
                    <div class="noty_clear_all" id="main_notifications_all_del">
                        <?= $Translate->get_translate_phrase('_NotificationsClear') ?>
                        <svg>
                            <use href="/resources/img/sprite.svg#trash"></use>
                        </svg>
                    </div>
                </div>
                <div class="notifications__main no-scrollbar" id="main_notifications"></div>
            </div>
        <?php endif; ?>
        <?php if (!$General->arr_general['disabledLanguage']): ?>
            <button class="language button-icon" id="openLanguage">
                <svg>
                    <use href="/resources/img/sprite.svg#lang"></use>
                </svg>
            </button>
            <div class="language__modal">
                <div class="language__modal-current">
                    <span class="language__modal-title"><?= $Translate->get_translate_phrase('_CurrentLang') ?></span>
                    <div class="language__modal-current-lang">
                        <?php $General->get_icon('custom', strtolower($_SESSION["language"]), 'flags') ?>
                        <?= $Translate->get_translate_phrase(isset($_SESSION["language"]) ? '_' . $_SESSION["language"] : '_' . $General->arr_general['language']) ?>
                    </div>
                </div>
                <hr>
                <div class="language__modal-list">
                    <span class="language__modal-title"><?= $Translate->get_translate_phrase('_selectLang') ?></span>
                    <?php for ($i = 0; $i < $Translate->arr_languages_count; $i++): ?>
                        <?php if ($Translate->arr_languages[$i] != (isset($_SESSION["language"]) ? $_SESSION["language"] : $General->arr_general['language'])): ?>
                            <button class="language__modal-item width-100"
                                onclick="location.href = '<?= '?language=' . $Translate->arr_languages[$i] ?>'">
                                <?php $General->get_icon('custom', strtolower($Translate->arr_languages[$i]), 'flags') ?>
                                <?= $Translate->get_translate_phrase('_' . $Translate->arr_languages[$i]) ?>
                            </button>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (empty($_SESSION['steamid'])): ?>
            <button onclick="location.href='?auth=login'">
                <svg>
                    <use href="/resources/img/sprite.svg#steam"></use>
                </svg>
                <?= $Translate->get_translate_phrase('_Steam_login') ?>
            </button>
        <?php endif; ?>
        <?php if (!empty($_SESSION['steamid'])): ?>
            <div id="profMenuOpen">
                <div class="user__avatar button-icon">
                    <?= $General->get_js_relevance_avatar($_SESSION['steamid']) ?>
                    <img src="<?= $General->getAvatar($_SESSION['steamid'], 3) ?>" id="avatar" avatarid="<?= $_SESSION['steamid'] ?>" alt="">
                </div>
            </div>
            <div class="user__menu">
                <div class="user_profile_wrapper">
                    <a href="<?= $General->arr_general['site'] ?>profiles/<?= empty($_SESSION['steamid']) ? 0 : $_SESSION['steamid'] ?>?search=1">
                        <div class="user__profile">
                            <div class="user_avatar_profile" style="position: relative;">
                                <img src="<?= $General->getAvatar($_SESSION['steamid'], 3) ?>" id="avatar"
                                    avatarid="<?= $_SESSION['steamid'] ?>">
                            </div>
                            <div class="username_profile">
                                <div class="prof_nickname" id="name" nameid="<?= $_SESSION['steamid64'] ?>">
                                    <?= $General->checkName($_SESSION['steamid64']) ?>
                                </div>
                                <?php if (!empty($Db->db_data['lk'])): ?>
                                    <div class="open_user_prof"><?= $Translate->get_translate_phrase('_Current_balance') ?>:
                                        <?= $Modules->get_balance() ?? 0 ?>
                                        <?= $General->currency ?>
                                    </div>
                                <?php endif; ?>
                                <span
                                    class="mini-prof__description-text"><?= $Translate->get_translate_phrase('_Goto_profile') ?></span>
                            </div>
                        </div>
                    </a>
                    <div class="user__links">
                        <?php if (!empty($_SESSION['steamid']) && isset($_SESSION['user_admin'])): ?>
                            <a class="button width-100 flex-start" href="/adminpanel">
                                <svg>
                                    <use href="/resources/img/sprite.svg#grid-elements"></use>
                                </svg>
                                <?= $Translate->get_translate_phrase('_Admin_panel') ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['steamid']) && isset($_SESSION['user_admin'])): ?>
                            <a class="button width-100 flex-start pay-link" href="/pay">
                                <svg>
                                    <use href="/resources/img/sprite.svg#wallet"></use>
                                </svg>
                                <?= $Translate->get_translate_phrase('_FinancesPLayer') ?>
                            </a>
                        <?php endif; ?>
                        <?php if (file_exists(MODULES . 'module_page_cards/description.json')): ?>
                            <a class="button width-100 flex-start" data-openmodal="openCards">
                                <svg>
                                    <use href="/resources/img/sprite.svg#game-cards"></use>
                                </svg>
                                <?= $Translate->get_translate_phrase('_dailyCards') ?>
                            </a>
                        <?php endif; ?>
                        <?php if (file_exists(MODULES . 'module_page_reports/description.json') && !empty($this->Db->db_data['Reports'])): ?>
                            <?php $array = $Db->query('Reports', 0, 0, "SELECT `steamid` FROM `rs_admins` WHERE `steamid` = :steamid", ['steamid' => $_SESSION['steamid64']]); ?>
                            <?php if (!empty($array['steamid']) || isset($_SESSION['user_admin'])): ?>
                                <a class="button width-100 flex-start" href="/reports">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#report-list"></use>
                                    </svg>
                                    <?= $Translate->get_translate_phrase('_Reports') ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php foreach ($General->get_neo_userbar() as $menu): ?>
                            <?php
                            $hasMenuRestrictions = !empty($menu['onlyAdminSite']) || !empty($menu['onlyAuth']) || !empty($menu['onlyAdmin']) || !empty($menu['onlyAdminServer']);
                            $menuAllows = !$hasMenuRestrictions || (
                                (!empty($menu['onlyAdminSite']) && $isAdmin) ||
                                (!empty($menu['onlyAdmin']) && $hasAdminAccess) ||
                                (!empty($menu['onlyAdminServer']) && $hasServerAccess)
                            );
                            $link = !empty($menu['url']) ? $menu['url'] : '/' . ($Modules->array_modules[$menu['module']]['page'] ?? '');
                            if ($isAdmin || $menuAllows): ?>
                                <a class="button width-100 flex-start" href="<?= $link ?>" <?= $menu['blank'] ? 'target="_blank"' : '' ?>>
                                    <?= $menu['icon'] ?>
                                    <?= (isset($menu['title'][0]) && $menu['title'][0] === '_' ? $Translate->get_translate_phrase($menu['title']) : $menu['title']) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="user_profile_footer">
                    <a class="user_logout" href="<?= $General->arr_general['site'] ?>?auth=logout">
                        <svg>
                            <use href="/resources/img/sprite.svg#logout"></use>
                        </svg>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if (isset($General->arr_general['enable_decoration']) && $General->arr_general['enable_decoration'] === 'snowfall'): ?>
    <div class="popup_modal" id="popupSnowfall">
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                <?= $Translate->get_translate_phrase('_SnowfallSettings') ?>
                <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <hr>
            <div class="snowfall-body">
                <div class="snowfall-banner">
                    <img src="/storage/cache/img/ny/agent.webp" alt="" loading="lazy">
                </div>
                <h2 id="timer" class="newyear-timer">
                    <?= $Translate->get_translate_phrase('_SnowfallLoading') ?>
                </h2>
                <p class="timer-items">
                    <span><?= $Translate->get_translate_phrase('_timerDays') ?></span>
                    <span><?= $Translate->get_translate_phrase('_timerHours') ?></span>
                    <span><?= $Translate->get_translate_phrase('_timerMinutes') ?></span>
                    <span><?= $Translate->get_translate_phrase('_timerSeconds') ?></span>
                </p>
                <hr>
                <div class="snow-toggle">
                    <button class="snow-toggle__btn" data-value="snowfall">
                        <?= $Translate->get_translate_phrase('_SnowfallShow') ?>
                    </button>
                    <button class="snow-toggle__btn" data-value="none">
                        <?= $Translate->get_translate_phrase('_SnowfallHide') ?>
                    </button>
                    <button class="stop-sound button-icon button-delete">
                        <svg width="512" height="512" x="0" y="0" viewBox="0 0 24 24" xml:space="preserve">
                            <g>
                                <g>
                                    <path fill-rule="evenodd" d="M1.293 1.293a1 1 0 0 1 1.414 0l20 20a1 1 0 0 1-1.414 1.414l-20-20a1 1 0 0 1 0-1.414z" clip-rule="evenodd"></path>
                                    <path d="M10.992 3.976c.686-.528 1.504-.956 2.375-.588.863.365 1.14 1.245 1.26 2.11.123.887.123 2.108.123 3.626v.643c0 .617 0 .925-.185 1.002s-.403-.142-.84-.577l-4.12-4.12c-.2-.2-.3-.3-.299-.425 0-.125.1-.224.3-.42.52-.513.984-.943 1.386-1.251zM14.75 14.88c0 1.517 0 2.738-.123 3.625-.12.865-.397 1.745-1.26 2.11-.871.369-1.689-.06-2.375-.587-.703-.54-1.595-1.451-2.645-2.524-.54-.55-.898-.817-1.26-.967-.365-.15-.808-.214-1.58-.214-.67 0-1.267 0-1.72-.047-.475-.05-.916-.157-1.313-.427-.756-.516-1.045-1.271-1.154-1.965-.082-.518-.073-1.086-.065-1.543v-.678c-.008-.458-.017-1.026.065-1.544.11-.694.398-1.449 1.154-1.964.397-.271.838-.378 1.313-.428.453-.047 1.05-.047 1.72-.047.772 0 1.215-.064 1.58-.214.067-.028.1-.042.12-.047.1-.026.176-.011.258.051.016.012.036.032.074.07l7.12 7.12.006.006a.3.3 0 0 1 .085.206z"></path>
                                    <path fill-rule="evenodd" d="M19.287 7.299a1 1 0 0 1 1.414-.012C22.113 8.674 23 10.487 23 12.5c0 1.393-.426 2.696-1.161 3.828a1 1 0 0 1-1.677-1.09c.542-.835.838-1.764.838-2.738 0-1.397-.611-2.715-1.701-3.787a1 1 0 0 1-.012-1.414zm-2.922 1.928a1 1 0 0 1 1.408.138c.763.93 1.227 2.089 1.227 3.354v.079a1 1 0 1 1-2-.079c0-.762-.277-1.48-.773-2.084a1 1 0 0 1 .138-1.408z" clip-rule="evenodd"></path>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>