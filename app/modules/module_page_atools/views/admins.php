<div class="card at__card">
    <?php if ($AccessController->checkPermission('admins.create')) : ?>
        <div class="at__panel">
            <div class="at__panel-title">
                <h2 class="at__h2">
                    <svg>
                        <use href="/resources/img/sprite.svg#plus"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingAdmin') ?>
                </h2>
            </div>
            <div class="at__panel-content">
                <?php if ((empty($Db->db_data['SourceBans']) && empty($Db->db_data['AdminSystem']) && empty($Db->db_data['IksAdminNew'])) || (!empty($Db->db_data['SourceBans']) && count($csgo) == 0) || ((!empty($Db->db_data['AdminSystem']) || !empty($Db->db_data['IksAdminNew'])) && count($cs2) == 0)) : ?>
                    <div id="undefinedGroups" class="at__undefined-groups">
                        <div class="at__warning">
                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingAdminImpossible') ?>
                            <hr>
                            <ul class="at__list">
                                <?php if (empty($Db->db_data['SourceBans']) && (empty($Db->db_data['AdminSystem']) || empty($Db->db_data['IksAdminNew']))) : ?>
                                    <li data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingAdminNeedConnects') ?>" data-tippy-placement="right">
                                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingAdminConnectAny') ?>
                                        <svg>
                                            <use href="/resources/img/sprite.svg#info-circle"></use>
                                        </svg>
                                    </li>
                                <?php endif; ?>
                                <?php if (!empty($Db->db_data['SourceBans']) && count($csgo) == 0) : ?>
                                    <li data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_MaIts') ?>" data-tippy-placement="right">
                                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_MaItsShort') ?>
                                        <svg>
                                            <use href="/resources/img/sprite.svg#info-circle"></use>
                                        </svg>
                                    </li>
                                <?php endif; ?>
                                <?php if (!empty($Db->db_data['AdminSystem']) && count($cs2) == 0) : ?>
                                    <li data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_IksAdminIts') ?>" data-tippy-placement="right">
                                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_IksAdminItsShort') ?>
                                        <svg>
                                            <use href="/resources/img/sprite.svg#info-circle"></use>
                                        </svg>
                                    </li>
                                <?php endif; ?>
                                <?php if (!empty($Db->db_data['IksAdminNew']) && count($cs2) == 0) : ?>
                                    <li data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_IksAdminIts') ?>" data-tippy-placement="right">
                                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_IksAdminItsShort') ?>
                                        <svg>
                                            <use href="/resources/img/sprite.svg#info-circle"></use>
                                        </svg>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php if ((!empty($Db->db_data['SourceBans']) && count($csgo) == 0) || (!empty($Db->db_data['AdminSystem']) && count($cs2) == 0) || (!empty($Db->db_data['IksAdminNew']) && count($cs2) == 0)) : ?>
                            <button class="width-100" onclick="location.href = '/atools/settings/'">
                                <svg>
                                    <use href="/resources/img/sprite.svg#arrow-top-tight-circle"></use>
                                </svg>
                                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_createGroups') ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="at__panel-wrapper" id="addingAdmin" data-default-all-servers="<?= !empty($defaultAllServers) ? '1' : '0' ?>">
                        <div class="inputs-inline">
                            <label for="steamIdInput"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_account') ?></label>
                            <input id="steamIdInput" type="text" value="" name="steamid" placeholder="https://steamcommunity.com/profiles/... / STEAM_1:1:390... / 7656119803... / [U:1:1234234]" autocomplete="off" required>
                        </div>
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="groupTypesList">
                                <?php if (count($cs2) > 0) : ?>
                                    <li>
                                        <label class="adaptive-select__label" for="cs2">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_cs2Group') ?></div>
                                            <input class="hide-input" id="cs2" value="cs2" type="radio" name="group-types-list">
                                        </label>
                                    </li>
                                <?php endif; ?>
                                <?php if (count($csgo) > 0) : ?>
                                    <li>
                                        <label class="adaptive-select__label" for="csgo">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_csgoGroup') ?></div>
                                            <input class="hide-input" id="csgo" value="csgo" type="radio" name="group-types-list">
                                        </label>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            <div class="adaptive-select" open-select="groupTypesList">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#list"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectGroupType') ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="adaptive-select-wrapper" id="groupList"></div>
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="adminExpireList">
                                <div class="inputs-inline">
                                    <div class="number" id="numberControl">
                                        <button class="number-minus" type="button">-</button>
                                        <input id="adminExpireCustom" type="number" min="0" value="" step="1" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_ownTime') ?>" autocomplete="off">
                                        <button class="number-plus" type="button">+</button>
                                    </div>
                                </div>
                                <?php foreach ($terms['data'] as $id => $file) : ?>
                                    <li>
                                        <label class="adaptive-select__label" for="adminExpireTime-<?= $id ?>">
                                            <div class="adaptive-select__label-text"><?= action_text_clear($file['name']) ?></div>
                                            <input class="hide-input" id="adminExpireTime-<?= $id ?>" value="<?= $file['time'] ?>" type="radio" name="admin-expire-list">
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="adaptive-select" open-select="adminExpireList">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#time-expired"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectExpire') ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="adaptive-select-wrapper" id="serverList" data-no-text></div>
                        <?php if (!empty($Db->db_data['Vips']) && $AccessController->checkPermission('privileges.create')) : ?>
                            <div class="adaptive-select-wrapper">
                                <ul class="adaptive-select__dropdown-list" id="adminVipGroupList">
                                    <div class="inputs-inline">
                                        <input id="adminVipGroupCustom" type="text" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_customGroup') ?>" autocomplete="off">
                                    </div>
                                    <?php if (!empty($vipGroups['data'])) : ?>
                                        <?php foreach ($vipGroups['data'] as $key) : ?>
                                            <li>
                                                <label class="adaptive-select__label" for="adminVipGroup-<?= $key['id'] ?>">
                                                    <div class="adaptive-select__label-text"><?= $key['name'] ?></div>
                                                    <input class="hide-input" id="adminVipGroup-<?= $key['id'] ?>" value="<?= $key['ini'] ?>" type="radio" name="admin-vip-group-list">
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                                <div class="adaptive-select" open-select="adminVipGroupList">
                                    <span class="adaptive-select__fist-icon">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#list"></use>
                                        </svg>
                                    </span>
                                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectVipGroup') ?></span>
                                    <span class="margin-left-auto adaptive-select__arrow">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($groups['data'])) : ?>
                            <div class="adaptive-select-wrapper">
                                <ul class="adaptive-select__dropdown-list" id="adminGroupAccessList">
                                    <div class="inputs-inline">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                                        </svg>
                                        <input id="searchAdminGroupAccess" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findGroup') ?>" autocomplete="off">
                                    </div>
                                    <?php foreach ($groups['data'] as $group) : ?>
                                        <li>
                                            <label class="adaptive-select__label" for="adminGroupAccessAt-<?= $group['id'] ?>">
                                                <div class="adaptive-select__label-text"><?= action_text_clear($group['name']) ?></div>
                                                <input class="hide-input" id="adminGroupAccessAt-<?= $group['id'] ?>" value="<?= $group['id'] ?>" type="radio" name="access-group-list">
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="adaptive-select" open-select="adminGroupAccessList">
                                    <span class="adaptive-select__fist-icon">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#list"></use>
                                        </svg>
                                    </span>
                                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectPanelAccessGroup') ?></span>
                                    <span class="margin-left-auto adaptive-select__arrow">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="adaptive-select-wrapper" data-no-text>
                                <ul class="adaptive-select__dropdown-list" id="adminFlagsAccessList">
                                    <div class="inputs-inline">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                                        </svg>
                                        <input id="searchAdminFlagsAccess" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findFlags') ?>" autocomplete="off">
                                    </div>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-0">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchAdmins') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-0" value="admins.view" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-1">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingAdmins') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-1" value="admins.create" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-2">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingAdmins') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-2" value="admins.delete" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-3">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingAdmins') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-3" value="admins.update" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-4">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingWarns') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-4" value="admins.warn.give" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-5">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_removingWarns') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-5" value="admins.warn.remove" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-6">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingWarns') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-6" value="admins.warn.delete" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-7">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingWarns') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-7" value="admins.warn.update" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-8">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchPunishments') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-8" value="punishments.view" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-9">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingBans') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-9" value="bans.create" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-10">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_removingBans') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-10" value="bans.unban" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-11">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingBans') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-11" value="bans.delete" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-12">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingBans') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-12" value="bans.update" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-13">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingMutes') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-13" value="mutes.create" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-14">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_removingMutes') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-14" value="mutes.unmute" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-15">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingMutes') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-15" value="mutes.delete" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-16">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingMutes') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-16" value="mutes.update" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-17">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchChecks') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-17" value="checks.view" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-18">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingChecks') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-18" value="checks.delete" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-19">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchFinances') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-19" value="finances.view" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-20">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingFinances') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-20" value="finances.update" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-21">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_resettingFinances') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-21" value="finances.reset" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-22">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchVIPs') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-22" value="privileges.view" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-23">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingVIPs') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-23" value="privileges.create" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-24">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingVIPs') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-24" value="privileges.delete" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-25">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingVIPs') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-25" value="privileges.update" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-26">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchCredits') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-26" value="credits.view" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-27">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingCredits') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-27" value="credits.update" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-28">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_viperResettingCredits') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-28" value="credits.reset" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-29">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchExperience') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-29" value="experience.view" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-30">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingExperience') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-30" value="experience.update" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-31">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_viperResettingExperience') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-31" value="experience.reset" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-32">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchLogs') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-32" value="logs.view" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="adminFlagAccessAt-33">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingLogs') ?></div>
                                            <input class="hide-input" id="adminFlagAccessAt-33" value="logs.delete" type="checkbox" name="flag-access-list">
                                        </label>
                                    </li>
                                </ul>
                                <div class="adaptive-select" open-select="adminFlagsAccessList">
                                    <span class="adaptive-select__fist-icon">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#list"></use>
                                        </svg>
                                    </span>
                                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectPanelAccessFlags') ?></span>
                                    <span class="margin-left-auto adaptive-select__arrow">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <button class="width-100" id="createAdmin"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addAdmin') ?></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($Db->db_data['SourceBans']) || !empty($Db->db_data['AdminSystem']) || !empty($Db->db_data['IksAdminNew'])) : ?>
        <div class="at__content">
            <div class="at__search">
                <input id="searchInfo" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_enterNickOrSteamID') ?>" autocomplete="off">
                <button id="buttonSearchInfo">
                    <svg>
                        <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findPlayer') ?>
                </button>
            </div>
            <div class="at__filter-block">
                <h2 class="at__h2"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_adminsList') ?></h2>
                <div class="at__filter-views">
                    <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_wantToSee') ?></span>
                    <button class="filter button-icon">10</button>
                    <button class="filter button-icon">25</button>
                    <button class="filter button-icon">50</button>
                </div>
            </div>
            <div class="at__filters" id="adminFilters">
                <div class="toggle__game-wrapper" id="adminFilterGame">
                    <?php if (!empty($Db->db_data['AdminSystem']) || !empty($Db->db_data['IksAdminNew'])) : ?>
                        <label class="toggle__game-custom-radio" for="adminFilterGameCs2" data-tippy-content="CS 2" data-tippy-placement="top">
                            <input id="adminFilterGameCs2" type="radio" value="cs2" name="filter-admin-game" <?= !empty($Db->db_data['AdminSystem']) || !empty($Db->db_data['IksAdminNew']) ? 'checked' : '' ?>>
                            <span>
                                <svg>
                                    <use href="/resources/img/sprite.svg#cs2"></use>
                                </svg>
                            </span>
                        </label>
                    <?php endif; ?>
                    <?php if (!empty($Db->db_data['SourceBans'])) : ?>
                        <label class="toggle__game-custom-radio" for="adminFilterGameCsgo" data-tippy-content="CS:GO" data-tippy-placement="top">
                            <input id="adminFilterGameCsgo" type="radio" value="csgo" name="filter-admin-game" <?= !empty($Db->db_data['SourceBans']) && empty($Db->db_data['AdminSystem']) && empty($Db->db_data['IksAdminNew']) ? 'checked' : '' ?>>
                            <span>
                                <svg>
                                    <use href="/resources/img/sprite.svg#csgo"></use>
                                </svg>
                            </span>
                        </label>
                    <?php endif; ?>
                </div>
                <div class="adaptive-select-wrapper skeleton--default" id="serverListFilter" data-no-text></div>
                <div class="adaptive-select-wrapper skeleton--default" id="groupListFilter"></div>
                <div class="adaptive-select-wrapper" data-no-text>
                    <ul class="adaptive-select__dropdown-list" id="adminAccessListFilter">
                        <div class="inputs-inline">
                            <svg>
                                <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                            </svg>
                            <input id="searchAdminAccessFilter" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findFlags') ?>" autocomplete="off">
                        </div>
                        <?php
                        $adminAccessFilterOptions = [
                            ['admins.view', '_at_watchAdmins'],
                            ['admins.create', '_at_addingAdmins'],
                            ['admins.delete', '_at_deletingAdmins'],
                            ['admins.update', '_at_changingAdmins'],
                            ['admins.warn.give', '_at_addingWarns'],
                            ['admins.warn.remove', '_at_removingWarns'],
                            ['admins.warn.delete', '_at_deletingWarns'],
                            ['admins.warn.update', '_at_changingWarns'],
                            ['punishments.view', '_at_watchPunishments'],
                            ['bans.create', '_at_addingBans'],
                            ['bans.unban', '_at_removingBans'],
                            ['bans.delete', '_at_deletingBans'],
                            ['bans.update', '_at_changingBans'],
                            ['mutes.create', '_at_addingMutes'],
                            ['mutes.unmute', '_at_removingMutes'],
                            ['mutes.delete', '_at_deletingMutes'],
                            ['mutes.update', '_at_changingMutes'],
                            ['checks.view', '_at_watchChecks'],
                            ['checks.delete', '_at_deletingChecks'],
                            ['finances.view', '_at_watchFinances'],
                            ['finances.update', '_at_changingFinances'],
                            ['finances.reset', '_at_resettingFinances'],
                            ['privileges.view', '_at_watchVIPs'],
                            ['privileges.create', '_at_addingVIPs'],
                            ['privileges.delete', '_at_deletingVIPs'],
                            ['privileges.update', '_at_changingVIPs'],
                            ['credits.view', '_at_watchCredits'],
                            ['credits.update', '_at_changingCredits'],
                            ['credits.reset', '_at_viperResettingCredits'],
                            ['experience.view', '_at_watchExperience'],
                            ['experience.update', '_at_changingExperience'],
                            ['experience.reset', '_at_viperResettingExperience'],
                            ['logs.view', '_at_watchLogs'],
                            ['logs.delete', '_at_deletingLogs'],
                        ];
                        foreach ($adminAccessFilterOptions as $i => $option) : ?>
                            <li>
                                <label class="adaptive-select__label" for="adminAccessFilterAt-<?= $i ?>">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', $option[1]) ?></div>
                                    <input class="hide-input" id="adminAccessFilterAt-<?= $i ?>" value="<?= $option[0] ?>" type="checkbox" name="admin-access-list-filter">
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="adminAccessListFilter">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#list"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectPanelAccessFlags') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <button class="button-delete at__button-reset" id="resetAdminFilters">
                    <svg>
                        <use href="/resources/img/sprite.svg#broom"></use>
                    </svg>
                </button>
            </div>
            <div class="table-responsive at__table">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_admin') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_serverGroup') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_expire') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="adminsTable">
                        <?php for ($i = 0; $i < 10; $i++) : ?>
                            <tr class="skeleton--default">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            <div id="adminsPagination"></div>
        </div>
    <?php endif; ?>
</div>
<div class="at__driver admin-info"></div>
<div class="popup_modal" id="addWarnToAdmin" data-modal-type="2"></div>
<div class="popup_modal" id="changeWarnToAdmin" data-modal-type="2"></div>
<div class="popup_modal" id="changeAdmin" data-modal-type="2"></div>