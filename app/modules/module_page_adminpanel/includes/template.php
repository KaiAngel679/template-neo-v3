<script src="/app/templates/neo_remastered/assets/js/tabs.js" async></script>
<?php !isset($_SESSION['user_admin']) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die() ?>
<?php require MODULES . 'module_page_adminpanel/forward/vendors.php'; ?>
<script src="<?= $General->arr_general['site'] ?>storage/assets/js/Sortable.min.js"></script>
<div class="col-md-12">
    <div class="tabs tabs--adminpanel">
        <div class="tabs__buttons navigation-filters" role="tablist" aria-labelledby="tablist-1">
            <button class="filter" id="tab-1" type="button" role="tab" aria-selected="true" aria-controls="tabpanel-1"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_navigation') ?></button>
            <button class="filter" id="tab-2" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-2" tabindex="-1"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_userbar') ?></button>
            <button class="filter" id="tab-3" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-3" tabindex="-1"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_footer') ?></button>
        </div>
        <div class="card tabs--card" id="tabpanel-1" role="tabpanel" tabindex="0" aria-labelledby="tab-1">
            <div class="card-header">
                <h5 class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_navigation') ?>
                    <div class="create_buttons">
                        <a class="button" data-openmodal="CategoryNav">
                            <svg>
                                <use href="/resources/img/sprite.svg#folder-plus"></use>
                            </svg> <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_createCat') ?>
                        </a>
                        <a class="button" data-openmodal="PointNav">
                            <svg>
                                <use href="/resources/img/sprite.svg#list-plus"></use>
                            </svg> <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_createItem') ?>
                        </a>
                    </div>
                </h5>
            </div>
            <div class="card-container">
                <div id="nested-nav" class="navigation__menu-list nested-sortable">
                    <?php if (empty($General->get_neo_menu())): ?>
                        <div class="no-data">
                            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_noMenuItems') ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($General->get_neo_menu() as $menu): ?>
                            <?php if ($menu['type'] == 'category'): ?>
                                <div class="navigation__menu-category handle" data-menu_id="<?= $menu['id'] ?>" data-type="category">
                                    <div class="navigation__menu-category-wrapper">
                                        <svg class="handle-icon">
                                            <use href="/resources/img/sprite.svg#two-lines"></use>
                                        </svg>
                                        <?php if ($menu['onlyAuth']): ?>
                                            <div class="only-for-auth" id="onlyAuth" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAuth') ?>"
                                                data-tippy-placement="top">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#steam"></use>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($menu['onlyAdmin']): ?>
                                            <div class="only-for-admins" id="onlyAdmin" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdmin') ?>"
                                                data-tippy-placement="top">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#key"></use>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($menu['onlyAdminSite']): ?>
                                            <div class="only-for-admins" id="onlyAdminSite" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdminSite') ?>"
                                                data-tippy-placement="top">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#shiedl-bold"></use>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($menu['onlyAdminServer']): ?>
                                            <div class="only-for-admins" id="onlyAdminServer" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdminServer') ?>"
                                                data-tippy-placement="top">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#servers"></use>
                                                </svg>
                                            </div>
                                        <?php endif; ?>

                                        <div class="navigation-icon" id="navigationIcon"><?= $menu['icon'] ?></div>
                                        <div class="navigation__menu-category-title" id="navigation-title"><?= (isset($menu['title'][0]) && $menu['title'][0] === '_' ? $Translate->get_translate_phrase($menu['title']) : $menu['title']) ?></div>
                                        <div class="action-buttons margin-left-auto">
                                            <button class="mobile-button" id="edit_category" data-menu="<?= $menu['id'] ?>">
                                                <span class="hide-text-button"><?= $Translate->get_translate_phrase('_Change') ?></span>
                                                <span class="hide-svg-button hide-svg-button-change"><svg>
                                                        <use href="/resources/img/sprite.svg#edit-pen"></use>
                                                    </svg></span>
                                            </button>
                                            <button class="button-delete mobile-button" id="category_del" data-del="<?= $menu['id'] ?>">
                                                <span class="hide-text-button"><?= $Translate->get_translate_phrase('_Delete_Action') ?></span>
                                                <span class="hide-svg-button"><svg>
                                                        <use href="/resources/img/sprite.svg#x"></use>
                                                    </svg></span>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="subMenus" class="nested-sortable navigation__menu-submenus">
                                        <?php foreach ($menu['children'] as $point): ?>
                                            <div class="navigation__menu-point handle" data-menu_id="<?= $menu['id'] ?>" data-point_id="<?= $point['id'] ?>" data-type="point">
                                                <div id="pointLink" style="display: none" data-url-blank="<?= $point['blank'] ?>"><?= $point['url'] ?></div>
                                                <div class="navigation__menu-point-submenu">
                                                    <svg>
                                                        <use href="/resources/img/sprite.svg#arrow-b-r"></use>
                                                    </svg>
                                                </div>
                                                <svg class="handle-icon">
                                                    <use href="/resources/img/sprite.svg#two-lines"></use>
                                                </svg>
                                                <?php if ($point['blank']): ?>
                                                    <div class="open-blank" id="open_blank" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_open_blank') ?>"
                                                        data-tippy-placement="top">
                                                        <svg>
                                                            <use href="/resources/img/sprite.svg#arrow-top-tight-circle"></use>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($point['onlyAuth']): ?>
                                                    <div class="only-for-auth" id="onlyAuth" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAuth') ?>"
                                                        data-tippy-placement="top">
                                                        <svg>
                                                            <use href="/resources/img/sprite.svg#steam"></use>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($point['onlyAdmin']): ?>
                                                    <div class="only-for-admins" id="onlyAdmin" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdmin') ?>"
                                                        data-tippy-placement="top">
                                                        <svg>
                                                            <use href="/resources/img/sprite.svg#key"></use>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($point['onlyAdminSite']): ?>
                                                    <div class="only-for-admins" id="onlyAdminSite" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdminSite') ?>"
                                                        data-tippy-placement="top">
                                                        <svg>
                                                            <use href="/resources/img/sprite.svg#shiedl-bold"></use>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($point['onlyAdminServer']): ?>
                                                    <div class="only-for-admins" id="onlyAdminServer" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdminServer') ?>"
                                                        data-tippy-placement="top">
                                                        <svg>
                                                            <use href="/resources/img/sprite.svg#servers"></use>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="navigation-icon" id="navigationIcon">
                                                    <?= $point['icon'] ?>
                                                </div>
                                                <div class="navigation__menu-point-wrapper">
                                                    <div class="navigation__menu-point-title" id="navigation-title">
                                                        <?= (isset($point['title'][0]) && $point['title'][0] === '_' ? $Translate->get_translate_phrase($point['title']) : $point['title']) ?>
                                                    </div>
                                                    <div class="navigation__menu-point-description" id="navigation-description">
                                                        <?= $point['description'] ?>
                                                    </div>
                                                </div>
                                                <div class="action-buttons margin-left-auto">
                                                    <button class="mobile-button" id="edit_subpoint" data-menu="<?= $menu['id'] ?>" data-point="<?= $point['id'] ?>">
                                                        <span class="hide-text-button"><?= $Translate->get_translate_phrase('_Change') ?></span>
                                                        <span class="hide-svg-button hide-svg-button-change"><svg>
                                                                <use href="/resources/img/sprite.svg#edit-pen"></use>
                                                            </svg></span>
                                                    </button>
                                                    <button class="button-delete mobile-button" id="subpoint_del" data-del="<?= $menu['id'] ?>" data-point="<?= $point['id'] ?>">
                                                        <span class="hide-text-button"><?= $Translate->get_translate_phrase('_Delete_Action') ?></span>
                                                        <span class="hide-svg-button"><svg>
                                                                <use href="/resources/img/sprite.svg#x"></use>
                                                            </svg></span>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($menu['type'] == 'point'): ?>
                                <div class="navigation__menu-point handle" data-menu_id="<?= $menu['id'] ?>" data-type="point">
                                    <div id="pointLink" style="display: none" data-url-blank="<?= $menu['blank'] ?>"><?= $menu['url'] ?></div>
                                    <svg class="handle-icon">
                                        <use href="/resources/img/sprite.svg#two-lines"></use>
                                    </svg>
                                    <?php if ($menu['blank']): ?>
                                        <div class="open-blank" id="open_blank" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_open_blank') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#arrow-top-tight-circle"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($menu['onlyAuth']): ?>
                                        <div class="only-for-auth" id="onlyAuth" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAuth') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#steam"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($menu['onlyAdmin']): ?>
                                        <div class="only-for-admins" id="onlyAdmin" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdmin') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#key"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($menu['onlyAdminSite']): ?>
                                        <div class="only-for-admins" id="onlyAdminSite" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdminSite') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#shiedl-bold"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($menu['onlyAdminServer']): ?>
                                        <div class="only-for-admins" id="onlyAdminServer" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdminServer') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#servers"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div class="navigation-icon" id="navigationIcon"><?= $menu['icon'] ?></div>
                                    <div class="navigation__menu-point-wrapper">
                                        <div class="navigation__menu-point-title" id="navigation-title"><?= (isset($menu['title'][0]) && $menu['title'][0] === '_' ? $Translate->get_translate_phrase($menu['title']) : $menu['title']) ?></div>
                                        <div class="navigation__menu-point-description" style="display: none;" id="navigation-description"><?= $menu['description'] ?></div>
                                    </div>
                                    <div class="action-buttons margin-left-auto">
                                        <button class="mobile-button" id="edit_point" data-menu="<?= $menu['id'] ?>">
                                            <span class="hide-text-button"><?= $Translate->get_translate_phrase('_Change') ?></span>
                                            <span class="hide-svg-button hide-svg-button-change"><svg>
                                                    <use href="/resources/img/sprite.svg#edit-pen"></use>
                                                </svg></span>
                                        </button>
                                        <button class="button-delete mobile-button" id="point_del" data-del="<?= $menu['id'] ?>">
                                            <span class="hide-text-button"><?= $Translate->get_translate_phrase('_Delete_Action') ?></span>
                                            <span class="hide-svg-button"><svg>
                                                    <use href="/resources/img/sprite.svg#x"></use>
                                                </svg></span>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card tabs--card is-hidden" id="tabpanel-2" role="tabpanel" tabindex="0" aria-labelledby="tab-2">
            <div class="card-header">
                <h5 class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_userbar') ?>
                    <div class="create_buttons">
                        <a class="button" data-openmodal="addUserbarItem">
                            <svg>
                                <use href="/resources/img/sprite.svg#list-plus"></use>
                            </svg> <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_createItem') ?>
                        </a>
                    </div>
                </h5>
            </div>
            <div class="card-container">
                <div id="nested-userbar" class="navigation__menu-list nested-sortable">
                    <?php if (empty($General->get_neo_userbar())): ?>
                        <div class="no-data">
                            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_noMenuItems') ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($General->get_neo_userbar() as $menu): ?>
                            <?php if ($menu['type'] == 'point'): ?>
                                <div class="navigation__menu-point handle" data-menu_id="<?= $menu['id'] ?>" data-type="point">
                                    <div id="pointLink" style="display: none" data-url-blank="false"><?= $menu['url'] ?></div>
                                    <svg class="handle-icon">
                                        <use href="/resources/img/sprite.svg#two-lines"></use>
                                    </svg>
                                    <?php if ($menu['blank']): ?>
                                        <div class="open-blank" id="open_blank" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_open_blank') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#arrow-top-tight-circle"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($menu['onlyAdmin']): ?>
                                        <div class="only-for-admins" id="onlyAdmin" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdmin') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#key"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($menu['onlyAdminSite']): ?>
                                        <div class="only-for-admins" id="onlyAdminSite" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdminSite') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#shiedl-bold"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($menu['onlyAdminServer']): ?>
                                        <div class="only-for-admins" id="onlyAdminServer" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdminServer') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#servers"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div class="navigation-icon" id="navigationIcon"><?= $menu['icon'] ?></div>
                                    <div class="navigation__menu-point-wrapper">
                                        <div class="navigation__menu-point-title" id="navigation-title"><?= (isset($menu['title'][0]) && $menu['title'][0] === '_' ? $Translate->get_translate_phrase($menu['title']) : $menu['title']) ?></div>
                                        <div class="navigation__menu-point-description" style="display: none;" id="navigation-description"><?= $menu['description'] ?></div>
                                    </div>
                                    <div class="action-buttons margin-left-auto">
                                        <button class="mobile-button" id="edit_userbar" data-menu="<?= $menu['id'] ?>">
                                            <span class="hide-text-button"><?= $Translate->get_translate_phrase('_Change') ?></span>
                                            <span class="hide-svg-button hide-svg-button-change"><svg>
                                                    <use href="/resources/img/sprite.svg#edit-pen"></use>
                                                </svg></span>
                                        </button>
                                        <button class="button-delete mobile-button" id="userbar_del" data-del="<?= $menu['id'] ?>">
                                            <span class="hide-text-button"><?= $Translate->get_translate_phrase('_Delete_Action') ?></span>
                                            <span class="hide-svg-button"><svg>
                                                    <use href="/resources/img/sprite.svg#x"></use>
                                                </svg></span>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card tabs--card is-hidden" id="tabpanel-3" role="tabpanel" tabindex="0" aria-labelledby="tab-3">
            <div class="card-header">
                <h5 class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_footer') ?>
                    <div class="create_buttons">
                        <a class="button" data-openmodal="addFooterLink">
                            <svg>
                                <use href="/resources/img/sprite.svg#list-plus"></use>
                            </svg> <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_createItem') ?>
                        </a>
                    </div>
                </h5>
            </div>
            <div class="card-container">
                <div id="nested-footer" class="navigation__menu-list nested-sortable">
                    <?php if (empty($General->get_neo_footer())): ?>
                        <div class="no-data">
                            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_noMenuItems') ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($General->get_neo_footer() as $menu): ?>
                            <?php if ($menu['type'] == 'point'): ?>
                                <div class="navigation__menu-point handle" data-menu_id="<?= $menu['id'] ?>" data-type="point">
                                    <div id="pointLink" style="display: none" data-url-blank="false"><?= $menu['url'] ?></div>
                                    <svg class="handle-icon">
                                        <use href="/resources/img/sprite.svg#two-lines"></use>
                                    </svg>
                                    <?php if ($menu['blank']): ?>
                                        <div class="open-blank" id="open_blank" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_open_blank') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#arrow-top-tight-circle"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($menu['onlyAdmin']): ?>
                                        <div class="only-for-admins" id="onlyAdmin" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdmin') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#key"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($menu['onlyAdminSite']): ?>
                                        <div class="only-for-admins" id="onlyAdminSite" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdminSite') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#shiedl-bold"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($menu['onlyAdminServer']): ?>
                                        <div class="only-for-admins" id="onlyAdminServer" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessOnlyAdminServer') ?>"
                                            data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#servers"></use>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div class="navigation__menu-point-wrapper">
                                        <div class="navigation__menu-point-title" id="navigation-title"><?= (isset($menu['title'][0]) && $menu['title'][0] === '_' ? $Translate->get_translate_phrase($menu['title']) : $menu['title']) ?></div>
                                        <div class="navigation__menu-point-description" style="display: none;" id="navigation-description"><?= $menu['description'] ?></div>
                                    </div>
                                    <div class="action-buttons margin-left-auto">
                                        <button class="mobile-button" id="edit_footer" data-menu="<?= $menu['id'] ?>">
                                            <span class="hide-text-button"><?= $Translate->get_translate_phrase('_Change') ?></span>
                                            <span class="hide-svg-button hide-svg-button-change"><svg>
                                                    <use href="/resources/img/sprite.svg#edit-pen"></use>
                                                </svg></span>
                                        </button>
                                        <button class="button-delete mobile-button" id="footer_del" data-del="<?= $menu['id'] ?>">
                                            <span class="hide-text-button"><?= $Translate->get_translate_phrase('_Delete_Action') ?></span>
                                            <span class="hide-svg-button"><svg>
                                                    <use href="/resources/img/sprite.svg#x"></use>
                                                </svg></span>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-md-4">
    <div class="card">
        <div class="card-header">
            <h5 class="badge">
                <?= $Translate->get_translate_phrase('_Sidebar_social') ?>
            </h5>
        </div>
        <div class="card-container">
            <form id="template_socials" class="form-social">
                <div class="social-block vk">
                    <input id="vkInput" name="social_vk" placeholder="https://vk.com/yourid" value="<?= $settings_neo['VK'] ?>">
                    <svg>
                        <use href="/resources/img/sprite.svg#vk"></use>
                    </svg>
                </div>
                <div class="social-block tg">
                    <input id="tgInput" name="social_tg" placeholder="https://t.me/yourtag" value="<?= $settings_neo['TG'] ?>">
                    <svg>
                        <use href="/resources/img/sprite.svg#tg"></use>
                    </svg>
                </div>
                <div class="social-block ds">
                    <input id="dsInput" name="social_ds" placeholder="https://discord.gg/invitecode" value="<?= $settings_neo['DS'] ?>">
                    <svg>
                        <use href="/resources/img/sprite.svg#ds"></use>
                    </svg>
                </div>
                <div class="social-block steam">
                    <input id="steamInput" name="social_steam" placeholder="https://steamcommunity.com/groups/grouptag" value="<?= $settings_neo['Steam'] ?>">
                    <svg>
                        <use href="/resources/img/sprite.svg#steam"></use>
                    </svg>
                </div>
                <div class="social-block yt">
                    <input id="ytInput" name="social_yt" placeholder="https://www.youtube.com/@yourtag" value="<?= $settings_neo['YT'] ?>">
                    <svg>
                        <use href="/resources/img/sprite.svg#yt"></use>
                    </svg>
                </div>
                <div class="social-block tt">
                    <input id="ttInput" name="social_tt" placeholder="https://tiktok.com/@yourtag" value="<?= $settings_neo['TT'] ?>">
                    <svg>
                        <use href="/resources/img/sprite.svg#tt"></use>
                    </svg>
                </div>
                <button class="width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Save') ?></button>
            </form>
        </div>
    </div>
</div>
<div class="col-md-4">
    <div class="card">
        <div class="card-header">
            <h5 class="badge">
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Logotype') ?>
            </h5>
        </div>
        <div class="card-container">
            <form id="logo-settings" class="form-logo">
                <div class="inputs-inline">
                    <label for="logoText"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_footer_text') ?></label>
                    <input id="logoText" name="site_name" placeholder="Sitename#CS2" value="<?= $settings_neo['SiteName'] ?>">
                </div>
                <fieldset>
                    <legend><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_selectFormatLogo') ?></legend>
                    <div class="inputs-inline">
                        <input type="radio" id="typeText" name="typeLofo" value="1" <?php $settings_neo['typeLofo'] == '1' && print 'checked' ?>>
                        <label for="typeText"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_logoText') ?></label>
                    </div>
                    <div class="inputs-inline">
                        <input type="radio" id="typeImage" name="typeLofo" value="2" <?php $settings_neo['typeLofo'] == '2' && print 'checked' ?>>
                        <label for="typeImage"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_logoImage') ?></label>
                    </div>
                </fieldset>
                <?php if (isset($settings_neo['Logo']) && !empty($settings_neo['Logo'])) : ?>
                    <span class="current-file">
                        <div class="current-file-info">
                            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_currentFiles'); ?>
                            <div class="current-file-image">
                                <img src="/storage/cache/img/global/<?= $settings_neo['Logo'] ?>" alt="">
                            </div>
                        </div>
                        <button type="button" class="width-100 button-delete" id="delete-logo">
                            <svg>
                                <use href="/resources/img/sprite.svg#trash"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_deleteLogo'); ?>
                        </button>
                    </span>
                <?php endif; ?>
                <input type="file" class="filepond-single" name="filepond" value="<?= $settings_neo['Logo'] ?>">
                <input type="hidden" id="filepond-single">
                <button class="width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Save') ?></button>
            </form>
        </div>
    </div>
</div>
<div class="col-md-4">
    <div class="card">
        <div class="card-header">
            <h5 class="badge">
                <?= $Translate->get_translate_phrase('_Other_Sidebar') ?>
            </h5>
        </div>
        <div class="card-container">
            <form id="other-settings" class="form-other">
                <div class="inputs-inline">
                    <label for="supportInput"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Link_tech') ?></label>
                    <input id="supportInput" name="support_link" placeholder="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Link_placeholder') ?>" value="<?= $settings_neo['SupportLink'] ?>">
                </div>
                <div class="inputs-inline">
                    <label for="mailInput"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Email') ?></label>
                    <input id="mailInput" name="contact_email" placeholder="nameproject@email.com" value="<?= $settings_neo['ContactEmail'] ?>">
                </div>
                <button class="width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Save') ?></button>
            </form>
        </div>
    </div>
</div>

<div class="popup_modal" id="CategoryNav">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_titleCreateTitle') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div class="navigarion-modal-body">
            <form id="add_category">
                <div class="inputs-inline">
                    <label for="pointNameCatCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_catName') ?></label>
                    <input id="pointNameCatCreate" name="title" required>
                </div>
                <div class="inputs-inline">
                    <label for="svgIconCatCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_icon') ?>
                        <a href="https://fontawesome.com/icons" target="_blank"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Icons_here') ?></a>
                    </label>
                    <input id="svgIconCatCreate" name="svg" placeholder="<svg viewBox=0 0 512 512><path d=M49..." required>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_auth" id="only_authCatCreate">
                    <label for="only_authCatCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAuth') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin" id="only_adminCatCreate">
                    <label for="only_adminCatCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdmins') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin_site" id="only_adminSiteCatCreate">
                    <label for="only_adminSiteCatCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdminsSite') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin_server" id="only_adminServerCatEdit">
                    <label for="only_adminServerCatEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOblyADmSrv') ?></label>
                </div>
                <button class="margin-top-auto width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_add') ?></button>
            </form>
        </div>
    </div>
</div>

<div class="popup_modal" id="PointNav">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_creatingItem') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div class="navigarion-modal-body">
            <form id="add_point">
                <div class="inputs-inline">
                    <label for="pointNamePointCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_point') ?></label>
                    <input id="pointNamePointCreate" name="title" required>
                </div>
                <div class="inputs-inline">
                    <label
                        for="svgIconPointCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_icon') ?>
                        <a href="https://fontawesome.com/icons" target="_blank"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Icons_here') ?></a>
                    </label>
                    <input id="svgIconPointCreate" name="svg" placeholder="<svg viewBox=0 0 512 512><path d=M49..." required>
                </div>
                <div class="inputs-inline">
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="pointModuleSelectCreate">
                            <li>
                                <label class="adaptive-select__label" for="pointCreatemodules_Option_no">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_no_selected') ?></div>
                                    <input class="hide-input" id="pointCreatemodules_Option_no" type="radio" name="module" value="">
                                </label>
                            </li>
                            <?php foreach ($Modules->array_modules as $module_key => $module_value) { ?>
                                <?php if ($module_value['page'] == 'home') {
                                    continue;
                                } ?>
                                <li>
                                    <label class="adaptive-select__label" for="pointCreatemodulesOption_<?= $module_key ?>">
                                        <div class="adaptive-select__label-text"><?= $module_value['title'] ?></div>
                                        <input class="hide-input" id="pointCreatemodulesOption_<?= $module_key ?>" type="radio" name="module" value="<?= $module_key ?>">
                                    </label>
                                </li>
                            <?php } ?>
                        </ul>
                        <div class="adaptive-select" open-select="pointModuleSelectCreate">
                            <span class="adaptive-select__fist-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#list"></use>
                                </svg>
                            </span>
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_chooseModule') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="userbar__points-divider">
                    <div class="userbar__points-divider-text">
                        <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_dividerText') ?>
                    </div>
                </div>
                <div class="inputs-inline">
                    <label for="pointLinkCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_link_place') ?></label>
                    <input id="pointLinkCreate" name="link" placeholder="https://.... || /....">
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input id="openNewTabPointCreate" type="checkbox" name="blank" class="switch">
                    <label for="openNewTabPointCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_NewTab') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_auth" id="onlyAuthPointCreate">
                    <label for="onlyAuthPointCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAuth') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin" id="onlyAdminPointCreate">
                    <label for="onlyAdminPointCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdmins') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin_site" id="onlyAdminSitePointCreate">
                    <label for="onlyAdminSitePointCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdminsSite') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin_server" id="onlyAdminServerPointCreate">
                    <label for="onlyAdminServerPointCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOblyADmSrv') ?></label>
                </div>
                <hr>
                <div class="inputs-inline" id="descriptionDiv">
                    <label for="pointDescriptionCreate"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_itemDescription') ?></label>
                    <input id="pointDescriptionCreate" name="description">
                </div>
                <button class="margin-top-auto width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_add') ?></button>
            </form>
        </div>
    </div>
</div>

<div class="popup_modal" id="EditCategoryNav">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_editingCat') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div class="navigarion-modal-body">
            <form id="edit_category_form">
                <div class="inputs-inline">
                    <label for="pointNameCatEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_catName') ?></label>
                    <input id="pointNameCatEdit" name="title" required>
                </div>
                <div class="inputs-inline">
                    <label
                        for="svgIconCatEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_icon') ?>
                        <a href="https://fontawesome.com/icons" target="_blank"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Icons_here') ?></a>
                    </label>
                    <input id="svgIconCatEdit" name="svg" placeholder="<svg viewBox=0 0 512 512><path d=M49..." required>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_auth" id="only_authCatEdit">
                    <label for="only_authCatEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAuth') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin" id="only_adminCatEdit">
                    <label for="only_adminCatEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdmins') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin_site" id="only_adminSiteCatEdit">
                    <label for="only_adminSiteCatEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdminsSite') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin_server" id="only_adminServerCatEdit">
                    <label for="only_adminServerCatEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOblyADmSrv') ?></label>
                </div>
                <button class="margin-top-auto width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_change_menu') ?></button>
            </form>
        </div>
    </div>
</div>

<div class="popup_modal" id="EditPointNav">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_editingItem') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div class="navigarion-modal-body">
            <form id="edit_point_form">
                <div class="inputs-inline">
                    <label for="pointNameEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_point') ?></label>
                    <input id="pointNameEdit" name="title" required>
                </div>
                <div class="inputs-inline">
                    <label for="svgIconEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_icon') ?>
                        <a href="https://fontawesome.com/icons" target="_blank"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Icons_here') ?></a>
                    </label>
                    <input id="svgIconEdit" name="svg" placeholder="<svg viewBox=0 0 512 512><path d=M49..." required>
                </div>
                <div class="inputs-inline">
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="pointModuleSelectEdit">
                            <li>
                                <label class="adaptive-select__label" for="pointEditmodules_Option_no">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_no_selected') ?></div>
                                    <input class="hide-input" id="pointEditmodules_Option_no" type="radio" name="module" value="">
                                </label>
                            </li>
                            <?php foreach ($Modules->array_modules as $module_key => $module_value) { ?>
                                <?php if ($module_value['page'] == 'home') {
                                    continue;
                                } ?>
                                <li>
                                    <label class="adaptive-select__label" for="pointEditmodulesOption_<?= $module_key ?>">
                                        <div class="adaptive-select__label-text"><?= $module_value['title'] ?></div>
                                        <input class="hide-input" id="pointEditmodulesOption_<?= $module_key ?>" type="radio" name="module" value="<?= $module_key ?>">
                                    </label>
                                </li>
                            <?php } ?>
                        </ul>
                        <div class="adaptive-select" open-select="pointModuleSelectEdit">
                            <span class="adaptive-select__fist-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#list"></use>
                                </svg>
                            </span>
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_chooseModule') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="userbar__points-divider">
                    <div class="userbar__points-divider-text">
                        <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_dividerText') ?>
                    </div>
                </div>
                <div class="inputs-inline">
                    <label for="pointLinkEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_link_place') ?></label>
                    <input id="pointLinkEdit" name="link" placeholder="https://.... || /....">
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input id="openNewTabEdit" type="checkbox" name="blank" class="switch">
                    <label for="openNewTabEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_NewTab') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_auth" id="onlyAuthEdit">
                    <label for="onlyAuthEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAuth') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin" id="onlyAdminEdit">
                    <label for="onlyAdminEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdmins') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin_site" id="onlyAdminSiteEdit">
                    <label for="onlyAdminSiteEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdminsSite') ?></label>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input class="switch" type="checkbox" name="only_admin_server" id="onlyAdminServerEdit">
                    <label for="onlyAdminServerEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOblyADmSrv') ?></label>
                </div>
                <hr>
                <div class="inputs-inline">
                    <label for="pointDescriptionEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_itemDescription') ?></label>
                    <input id="pointDescriptionEdit" name="description">
                </div>
                <button class="margin-top-auto width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_change_menu') ?></button>
            </form>
        </div>
    </div>
</div>

<div class="popup_modal" id="addUserbarItem">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_addUserbarPoint') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form id="add_userbar" class="navigarion-modal-body">
            <div class="inputs-inline">
                <label for="usPointIcon">
                    <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_icon') ?> (<a href="https://fontawesome.com/icons" target="_blank"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Icons_here') ?></a>)</label>
                <input id="usPointIcon" name="svg" placeholder='<svg><use href="/resources/img/sprite.svg#arrow-top-tight-circle"></use></svg>'>
            </div>
            <div class="inputs-inline">
                <label for="usPointName"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_point') ?></label>
                <input id="usPointName" name="title" placeholder="Shop" required>
            </div>
            <div class="inputs-inline">
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="id-select2">
                        <li>
                            <label class="adaptive-select__label" for="modules_Option_no">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_no_selected') ?></div>
                                <input class="hide-input" id="modules_Option_no" type="radio" name="module" value="">
                            </label>
                        </li>
                        <?php foreach ($Modules->array_modules as $module_key => $module_value) { ?>
                            <?php if ($module_value['page'] == 'home') {
                                continue;
                            } ?>
                            <li>
                                <label class="adaptive-select__label" for="modulesOption_<?= $module_key ?>">
                                    <div class="adaptive-select__label-text"><?= $module_value['title'] ?></div>
                                    <input class="hide-input" id="modulesOption_<?= $module_key ?>" type="radio" name="module" value="<?= $module_key ?>">
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="adaptive-select" open-select="id-select2">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#list"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_chooseModule') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="userbar__points-divider">
                <div class="userbar__points-divider-text">
                    <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_dividerText') ?>
                </div>
            </div>
            <div class="inputs-inline">
                <label for="usPointSelfLink"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_otherLink') ?></label>
                <input id="usPointSelfLink" name="link" placeholder="https://.... || /....">
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input id="usPointNewTab" type="checkbox" class="switch" name="blank">
                <label for="usPointNewTab"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_NewTab') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin" id="usPointOnlyAdmin">
                <label for="usPointOnlyAdmin"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdmins') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin_site" id="usPointOnlyAdminSite">
                <label for="usPointOnlyAdminSite"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdminsSite') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin_server" id="usPointOnlyAdminServer">
                <label for="usPointOnlyAdminServer"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOblyADmSrv') ?></label>
            </div>
            <hr>
            <button class="margin-top-auto width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_add') ?></button>
        </form>
    </div>
</div>

<div class="popup_modal" id="editUserbarItem">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_editUserbarPoint') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form id="edit_userbar_form" class="navigarion-modal-body">
            <div class="inputs-inline">
                <label for="usPointIconEdit">
                    <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_icon') ?> (<a href="https://fontawesome.com/icons" target="_blank"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Icons_here') ?></a>)</label>
                <input id="usPointIconEdit" name="svg" placeholder='<svg><use href="/resources/img/sprite.svg#arrow-top-tight-circle"></use></svg>'>
            </div>
            <div class="inputs-inline">
                <label for="usPointNameEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_point') ?></label>
                <input id="usPointNameEdit" name="title" placeholder="Shop" required>
            </div>
            <div class="inputs-inline">
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="editUsSelect2">
                        <li>
                            <label class="adaptive-select__label" for="modules_edit_Option_no">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_no_selected') ?></div>
                                <input class="hide-input" id="modules_edit_Option_no" type="radio" name="module" value="">
                            </label>
                        </li>
                        <?php foreach ($Modules->array_modules as $module_key => $module_value) { ?>
                            <?php if ($module_value['page'] == 'home') {
                                continue;
                            } ?>
                            <li>
                                <label class="adaptive-select__label" for="modules_edit_Option_<?= $module_key ?>">
                                    <div class="adaptive-select__label-text"><?= $module_value['title'] ?></div>
                                    <input class="hide-input" id="modules_edit_Option_<?= $module_key ?>" type="radio" name="module" value="<?= $module_key ?>">
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="adaptive-select" open-select="editUsSelect2">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#list"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_chooseModule') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="userbar__points-divider">
                <div class="userbar__points-divider-text">
                    <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_dividerText') ?>
                </div>
            </div>
            <div class="inputs-inline">
                <label for="usPointSelfLinkEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_otherLink') ?></label>
                <input id="usPointSelfLinkEdit" name="link" placeholder="https://.... || /....">
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input id="usPointNewTabEdit" type="checkbox" class="switch" name="blank">
                <label for="usPointNewTabEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_NewTab') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin" id="usPointOnlyAdminEdit">
                <label for="usPointOnlyAdminEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdmins') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin_site" id="usPointOnlyAdminSiteEdit">
                <label for="usPointOnlyAdminSiteEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdminsSite') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin_server" id="usPointOnlyAdminServerEdit">
                <label for="usPointOnlyAdminServerEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOblyADmSrv') ?></label>
            </div>
            <hr>
            <button class="margin-top-auto width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_change_menu') ?></button>
        </form>
    </div>
</div>

<div class="popup_modal" id="addFooterLink">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_addFooterLink') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form id="add_footer" class="navigarion-modal-body">
            <div class="inputs-inline">
                <label for="footerLinkName"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_linkName') ?></label>
                <input id="footerLinkName" name="title" placeholder="Link name" required>
            </div>
            <div class="inputs-inline">
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="footerCreateModuleSelect">
                        <li>
                            <label class="adaptive-select__label" for="footerCreateModuleSelectOption_no">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_no_selected') ?></div>
                                <input class="hide-input" id="footerCreateModuleSelectOption_no" type="radio" name="module" value="">
                            </label>
                        </li>
                        <?php foreach ($Modules->array_modules as $module_key => $module_value) { ?>
                            <?php if ($module_value['page'] == 'home') {
                                continue;
                            } ?>
                            <li>
                                <label class="adaptive-select__label" for="footerCreateModuleSelectOption_<?= $module_key ?>">
                                    <div class="adaptive-select__label-text"><?= $module_value['title'] ?></div>
                                    <input class="hide-input" id="footerCreateModuleSelectOption_<?= $module_key ?>" type="radio" name="module" value="<?= $module_key ?>">
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="adaptive-select" open-select="footerCreateModuleSelect">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#list"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_chooseModule') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="userbar__points-divider">
                <div class="userbar__points-divider-text">
                    <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_dividerText') ?>
                </div>
            </div>
            <div class="inputs-inline">
                <label for="footerLink"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_footerLink') ?></label>
                <input id="footerLink" name="link" placeholder="https://.... || /....">
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input id="footerLinkNewTab" type="checkbox" class="switch" name="blank">
                <label for="footerLinkNewTab"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_NewTab') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_auth" id="footerLinkOnlyAuth">
                <label for="footerLinkOnlyAuth"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAuth') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin" id="footerLinkOnlyAdmin">
                <label for="footerLinkOnlyAdmin"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdmins') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin_site" id="footerLinkOnlyAdminSite">
                <label for="footerLinkOnlyAdminSite"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdminsSite') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin_server" id="footerPointOnlyAdminServer">
                <label for="footerPointOnlyAdminServer"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOblyADmSrv') ?></label>
            </div>
            <hr>
            <button class="margin-top-auto width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_add') ?></button>
        </form>
    </div>
</div>

<div class="popup_modal" id="editFooterLink">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_editFooterLink') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form id="edit_footer_form" class="navigarion-modal-body">
            <div class="inputs-inline">
                <label for="footerLinkNameEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_linkName') ?></label>
                <input id="footerLinkNameEdit" name="title" placeholder="Link name" required>
            </div>
            <div class="inputs-inline">
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="footerEditModuleSelect">
                        <li>
                            <label class="adaptive-select__label" for="footerEditModuleSelectOption_no">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_no_selected') ?></div>
                                <input class="hide-input" id="footerEditModuleSelectOption_no" type="radio" name="module" value="">
                            </label>
                        </li>
                        <?php foreach ($Modules->array_modules as $module_key => $module_value) { ?>
                            <?php if ($module_value['page'] == 'home') {
                                continue;
                            } ?>
                            <li>
                                <label class="adaptive-select__label" for="footerEditModuleSelectOption_<?= $module_key ?>">
                                    <div class="adaptive-select__label-text"><?= $module_value['title'] ?></div>
                                    <input class="hide-input" id="footerEditModuleSelectOption_<?= $module_key ?>" type="radio" name="module" value="<?= $module_key ?>">
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="adaptive-select" open-select="footerEditModuleSelect">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#list"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_chooseModule') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="userbar__points-divider">
                <div class="userbar__points-divider-text">
                    <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_dividerText') ?>
                </div>
            </div>
            <div class="inputs-inline">
                <label for="footerLinkEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_footerLink') ?></label>
                <input id="footerLinkEdit" name="link" placeholder="https://.... || /....">
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input id="footerLinkNewTabEdit" type="checkbox" name="blank" class="switch">
                <label for="footerLinkNewTabEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_NewTab') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_auth" id="footerLinkOnlyAuthEdit">
                <label for="footerLinkOnlyAuthEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAuth') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin" id="footerLinkOnlyAdminEdit">
                <label for="footerLinkOnlyAdminEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdmins') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin_site" id="footerLinkOnlyAdminSiteEdit">
                <label for="footerLinkOnlyAdminSiteEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOnlyForAdminsSite') ?></label>
            </div>
            <div class="inputs-inline" style="margin-top: .5rem">
                <input class="switch" type="checkbox" name="only_admin_server" id="footerPointOnlyAdminServerEdit">
                <label for="footerPointOnlyAdminServerEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_displayOblyADmSrv') ?></label>
            </div>
            <hr>
            <button class="margin-top-auto width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_change_menu') ?></button>
        </form>
    </div>
</div>