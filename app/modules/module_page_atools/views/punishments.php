<div class="card at__card">
    <?php if ($AccessController->checkPermission('bans.create') || $AccessController->checkPermission('mutes.create')) : ?>
        <div class="at__panel">
            <div class="at__panel-title">
                <h2 class="at__h2">
                    <svg>
                        <use href="/resources/img/sprite.svg#plus"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingPunishnemt') ?>
                </h2>
            </div>
            <div class="at__panel-content">
                <?php if (empty($Db->db_data['SourceBans']) && empty($Db->db_data['AdminSystem']) && empty($Db->db_data['IksAdminNew'])) : ?>
                    <div id="undefinedPunishments" class="at__undefined-groups">
                        <div class="at__warning">
                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingPunishmentsImpossible') ?>
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
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="at__panel-wrapper" id="addingPunishment">
                        <div class="inputs-inline" id="onlinePlayerPunishSwitchRow">
                            <input type="checkbox" id="useOnlinePlayerPunish" class="switch">
                            <label for="useOnlinePlayerPunish"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectPlayerFromServer') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <label for="steamIdInput"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_account') ?></label>
                            <input id="steamIdInput" type="text" value="" name="steamid" placeholder="https://steamcommunity.com/profiles/... / STEAM_1:1:390... / 7656119803... / [U:1:1234234]" autocomplete="off" required>
                        </div>
                        <div class="adaptive-select-wrapper" id="onlinePlayerPunishWrap" style="display:none" data-no-text></div>
                        <div class="inputs-inline">
                            <input type="checkbox" id="enableIP" class="switch">
                            <label for="enableIP"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addIP') ?></label>
                        </div>
                        <div class="inputs-inline at__ip-toggle" style="display: none;">
                            <label for="ipInput"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_userIP') ?></label>
                            <input id="ipInput" type="text" value="" name="ip" placeholder="192.168.0.1" autocomplete="off">
                        </div>
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="punishType">
                                <?php if ($AccessController->checkPermission('bans.create')) : ?>
                                    <li>
                                        <label class="adaptive-select__label" for="punishTypeBan">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_gameBlock') ?></div>
                                            <input class="hide-input" id="punishTypeBan" value="0" type="radio" name="punish-type">
                                        </label>
                                    </li>
                                <?php endif; ?>
                                <?php if ($AccessController->checkPermission('mutes.create')) : ?>
                                    <li>
                                        <label class="adaptive-select__label" for="punishTypeMute">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_micBlock') ?></div>
                                            <input class="hide-input" id="punishTypeMute" value="1" type="radio" name="punish-type">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="punishTypeChat">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_chatBlock') ?></div>
                                            <input class="hide-input" id="punishTypeChat" value="2" type="radio" name="punish-type">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="punishTypeSilence">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_SilenceBlock') ?></div>
                                            <input class="hide-input" id="punishTypeSilence" value="3" type="radio" name="punish-type">
                                        </label>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            <div class="adaptive-select" open-select="punishType">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#user-block"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectPunishType') ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="adaptive-select-wrapper" id="punishReasonWrap"></div>
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="punishTime">
                                <div class="inputs-inline">
                                    <div class="number" id="numberControl">
                                        <button class="number-minus" type="button">-</button>
                                        <input id="punishCustomTime" type="number" min="0" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_customTime') ?>">
                                        <button class="number-plus" type="button">+</button>
                                    </div>
                                </div>
                                <?php if (!empty($terms['data'])) : ?>
                                    <?php foreach ($terms['data'] as $id => $term) : ?>
                                        <li>
                                            <label class="adaptive-select__label" for="punishTime-<?= $id ?>">
                                                <div class="adaptive-select__label-text"><?= action_text_clear($term['name']) ?></div>
                                                <input class="hide-input" id="punishTime-<?= $id ?>" value="<?= $term['time'] ?>" type="radio" name="punish-time">
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                            <div class="adaptive-select" open-select="punishTime">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#time"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectPunishTime') ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="adaptive-select-wrapper" data-no-text>
                            <ul class="adaptive-select__dropdown-list" id="punishServer">
                                <div class="inputs-inline">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                                    </svg>
                                    <input id="punishFindServer" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findServer') ?>">
                                </div>
                                <li>
                                    <label class="adaptive-select__label" for="punishServerAll">
                                        <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') ?></div>
                                        <input class="hide-input" id="punishServerAll" type="checkbox" value="-1" name="punish-server"<?= !empty($defaultAllServers) ? ' checked' : '' ?>>
                                    </label>
                                </li>
                                <?php if (!empty($servers['data'])): ?>
                                    <?php foreach ($servers['data'] as $id => $key) : ?>
                                        <li>
                                            <label class="adaptive-select__label" for="punishServer-<?= $id ?>">
                                                <div class="adaptive-select__label-text"><?= action_text_clear($key['name_custom']) ?> <span class="at__cs-label <?= $key['server_game'] == 'CS2' ? 'cs2' : 'csgo' ?>"><?= $key['server_game'] ?></span></div>
                                                <input class="hide-input" id="punishServer-<?= $id ?>" value="<?= $key['id'] ?>" type="checkbox" name="punish-server">
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                            <div class="adaptive-select" open-select="punishServer">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#servers"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text"><?= !empty($defaultAllServers) ? $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') : $Translate->get_translate_module_phrase('module_page_atools', '_at_chooseServers') ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <button class="width-100" id="givePunish" type="button"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_givePunish') ?></button>
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
                <h2 class="at__h2"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_PunishList') ?></h2>
                <div class="at__filter-views">
                    <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_wantToSee') ?></span>
                    <button class="filter button-icon">10</button>
                    <button class="filter button-icon">25</button>
                    <button class="filter button-icon">50</button>
                </div>
            </div>
            <div class="at__filters" id="punishFilters">
                <div class="toggle__game-wrapper" id="punishFilterGame">
                    <?php if ($Db->db_data['AdminSystem'] || $Db->db_data['IksAdminNew']) : ?>
                        <label class="toggle__game-custom-radio" for="punishFilterGameCs2" data-tippy-content="CS 2" data-tippy-placement="top">
                            <input id="punishFilterGameCs2" type="radio" value="cs2" name="filter-punish-game" <?= $Db->db_data['AdminSystem'] || $Db->db_data['IksAdminNew'] ? 'checked' : '' ?>>
                            <span>
                                <svg>
                                    <use href="/resources/img/sprite.svg#cs2"></use>
                                </svg>
                            </span>
                        </label>
                    <?php endif; ?>
                    <?php if ($Db->db_data['SourceBans']) : ?>
                        <label class="toggle__game-custom-radio" for="punishFilterGameCsgo" data-tippy-content="CS:GO" data-tippy-placement="top">
                            <input id="punishFilterGameCsgo" type="radio" value="csgo" name="filter-punish-game" <?= $Db->db_data['SourceBans'] && !$Db->db_data['AdminSystem'] && !$Db->db_data['IksAdminNew'] ? 'checked' : '' ?>>
                            <span>
                                <svg>
                                    <use href="/resources/img/sprite.svg#csgo"></use>
                                </svg>
                            </span>
                        </label>
                    <?php endif; ?>
                </div>
                <div class="toggle__type-wrapper" id="punishFilterType">
                    <label class="toggle__type-custom-radio" for="punishFilterTypeBan" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_PunishTypeBan') ?>" data-tippy-placement="top">
                        <input id="punishFilterTypeBan" type="radio" value="ban" name="filter-punish-type" checked>
                        <span>
                            <svg>
                                <use href="/resources/img/sprite.svg#block"></use>
                            </svg>
                        </span>
                    </label>
                    <label class="toggle__type-custom-radio" for="punishFilterTypeMute" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_PunishTypeMute') ?>" data-tippy-placement="top">
                        <input id="punishFilterTypeMute" type="radio" value="mute" name="filter-punish-type">
                        <span>
                            <svg>
                                <use href="/resources/img/sprite.svg#mute"></use>
                            </svg>
                        </span>
                    </label>
                </div>
                <div class="adaptive-select-wrapper skeleton--default" id="punishFilterByAdmin"></div>
                <div class="at__calendar-filter" id="punishCalendarFilter">
                    <button type="button" class="at__calendar-trigger" id="punishCalendarTrigger">
                        <span class="at__calendar-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#calendare"></use>
                            </svg>
                        </span>
                        <span class="at__calendar-period" id="punishCalendarPeriod"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectDateRange') ?></span>
                        <span class="margin-left-auto at__calendar-arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </button>
                    <div class="at__calendar-dropdown">
                        <div id="punishCalendar"></div>
                        <button type="button" class="at__calendar-dropdown-reset button-delete width-100"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_calendarReset') ?></button>
                    </div>
                    <input type="hidden" id="punishStartDate">
                    <input type="hidden" id="punishEndDate">
                </div>
                <div class="adaptive-select-wrapper skeleton--default" id="punishFilterServers" data-no-text></div>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="expiresPunishes">
                        <li>
                            <label class="adaptive-select__label" for="allPunishments">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allPunishments') ?></div>
                                <input class="hide-input" id="allPunishments" value="all" type="radio" name="type-expire-punish" checked>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="activePunishments">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_activePunishments') ?></div>
                                <input class="hide-input" id="activePunishments" value="active" type="radio" name="type-expire-punish">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="expiredPunishments">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_expiredPunishments') ?></div>
                                <input class="hide-input" id="expiredPunishments" value="expired" type="radio" name="type-expire-punish">
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="expiresPunishes" style="min-width: max-content">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#list"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allPunishments') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <button class="button-delete at__button-reset" id="resetPunishFilters">
                    <svg>
                        <use href="/resources/img/sprite.svg#broom"></use>
                    </svg>
                </button>
            </div>
            <div class="table-responsive at__table">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="at__punish-bulk-col" style="opacity: 1"><input type="checkbox" id="selectAll"></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_punishType') ?></th>
                            <th><?= $Translate->get_translate_phrase('_Date') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_offender') ?></th>
                            <th><?= $Translate->get_translate_phrase('_Reason') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_admin') ?></th>
                            <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_servers') ?>" data-tippy-placement="top"><svg>
                                    <use href="/resources/img/sprite.svg#servers"></use>
                                </svg></th>
                            <th><?= $Translate->get_translate_phrase('_Term') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_expiring') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="punishTable">
                        <?php for ($i = 0; $i < 10; $i++) : ?>
                            <tr class="skeleton--default">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            <div id="punishmentsPagination"></div>
        </div>
    <?php endif; ?>
</div>

<div class="popup_modal" id="editPunish" data-modal-type="2"></div>