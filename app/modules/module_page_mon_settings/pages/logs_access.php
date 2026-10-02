<div class="row">
    <div class="col-md-4">
        <div class="height-100">
            <div class="mon__access-container height-100">
                <h3><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_accessManage') ?></h3>
                <form method="post" id="panelAccessForm">
                    <div class="mon__visual-switch-wrapper">
                        <input class="switch" type="checkbox" id="panelStatus" name="mon_panel" <?php $Mon->Settings['panel'] === 1 && print 'checked' ?>>
                        <label for="panelStatus"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_panelAccessAdmin') ?></label>
                    </div>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="excludedAdminsSelect">
                            <?php $adminGroups = $Mon->getAdminGroups(); ?>
                            <?php if (!empty($adminGroups['cs2'])): ?>
                                <?php foreach ($adminGroups['cs2'] as $group): ?>
                                    <li>
                                        <label class="adaptive-select__label" for="excluded_admin_<?= $group['id'] ?>">
                                            <div class="adaptive-select__label-text"><?= htmlspecialchars($group['name']) ?> [CS2]</div>
                                            <input class="hide-input" id="excluded_admin_<?= $group['id'] ?>" type="checkbox" name="excluded-admins-cs2[]" value="<?= htmlspecialchars($group['name']) ?>" <?= in_array($group['name'], $Mon->Settings['excluded_admins']['cs2'] ?? [], true) ? 'checked' : '' ?>>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (!empty($adminGroups['csgo'])): ?>
                                <?php foreach ($adminGroups['csgo'] as $group): ?>
                                    <li>
                                        <label class="adaptive-select__label" for="excluded_admin_csgo_<?= $group['id'] ?>">
                                            <div class="adaptive-select__label-text"><?= htmlspecialchars($group['name']) ?> [CS:GO]</div>
                                            <input class="hide-input" id="excluded_admin_csgo_<?= $group['id'] ?>" type="checkbox" name="excluded-admins-csgo[]" value="<?= htmlspecialchars($group['name']) ?>" <?= in_array($group['name'], $Mon->Settings['excluded_admins']['csgo'] ?? [], true) ? 'checked' : '' ?>>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        <div class="adaptive-select" open-select="excludedAdminsSelect">
                            <span class="adaptive-select__fist-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#eye"></use>
                                </svg>
                            </span>
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_selectExcludedAdmins') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="button width-100"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_save') ?></button>
                </form>
                <hr>
                <button type="button" class="mon__access-add width-100" data-openmodal="addAccess"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_addAccess') ?></button>
                <ul class="mon__access-wrapper" id="accessList" style="display: none;"></ul>
                <span class="mon__empty-access-text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_adminsEmpty') ?></span>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="mon__logs-container">
            <h3><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_eventLog') ?></h3>
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="option-server-select">
                    <li>
                        <label class="adaptive-select__label" for="for_server_all">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_phrase('_allServers') ?></div>
                            <input class="hide-input" id="for_server_all" onclick="getLogsList()" type="radio" name="mon_server" value="all" checked>
                        </label>
                    </li>
                    <?php foreach ($General->server_list as $server): ?>
                        <li>
                            <label class="adaptive-select__label" for="for_server_<?= $server['id'] ?>">
                                <div class="adaptive-select__label-text"><?= $server['name'] ?></div>
                                <input class="hide-input" id="for_server_<?= $server['id'] ?>" name="mon_server" onclick="getLogsList()" type="radio" value="<?= $server['id'] ?>">
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="adaptive-select" open-select="option-server-select">
                    <span class="adaptive-select__span_text">-</span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="mon__logs-list" id="logsList" style="display: none;"></div>
            <span class="mon__empty-list-text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_eventEmpty') ?></span>
            <div class="mon__logs-pagination-wrapper">
                <div id="logsPagination" class="mon__logs-pagination"></div>
            </div>
        </div>
    </div>
</div>

<div class="popup_modal" id="addAccess">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_addingAccess') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <hr>
        <div class="mon__access-body">
            <form method="post" id="addAccessForm">
                <div class="input-form">
                    <?php foreach ($General->server_list as $server): ?>
                        <div class="mon__visual-switch-wrapper">
                            <input style="padding: 0" class="switch" type="checkbox" id="server-<?= $server['id'] ?>"
                                name="servers_access[]" value="<?= $server['id'] ?>">
                            <label for="server-<?= $server['id'] ?>"><?= $server['name'] ?></label>
                        </div>
                    <?php endforeach; ?>
                    <input type="text" name="steam_mon" placeholder="STEAMID64">
                </div>
            </form>
            <button class="width-100" type="submit" form="addAccessForm"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_addAccess') ?></button>
        </div>
    </div>
</div>