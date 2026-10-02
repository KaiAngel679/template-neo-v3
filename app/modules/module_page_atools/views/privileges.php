<div class="card at__card">
    <?php if ($AccessController->checkPermission('privileges.create')) : ?>
        <div class="at__panel">
            <div class="at__panel-title">
                <h2 class="at__h2">
                    <svg>
                        <use href="/resources/img/sprite.svg#plus"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingVip') ?>
                </h2>
            </div>
            <div class="at__panel-content">
                <?php if (empty($Db->db_data['Vips'])) : ?>
                    <div id="undefinedPrivileges" class="at__undefined-groups">
                        <div class="at__warning">
                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingPrivilegesImpossible') ?>
                            <hr>
                            <ul class="at__list">
                                <li data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingPrivilegesNeedConnects') ?>" data-tippy-placement="right">
                                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingPrivilegesConnectAny') ?>
                                    <svg>
                                        <use href="/resources/img/sprite.svg#info-circle"></use>
                                    </svg>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="at__panel-wrapper" id="addingVip">
                        <div class="inputs-inline">
                            <label for="vipSteamIdInput"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipSteamId') ?></label>
                            <input id="vipSteamIdInput" type="text" value="" name="steamid" placeholder="STEAM_1:1:390... / 7656119803..." autocomplete="off" required>
                        </div>
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="vipGroupList">
                                <div class="inputs-inline">
                                    <input id="vipGroupCustom" type="text" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_customGroup') ?>" autocomplete="off">
                                </div>
                                <?php if (!empty($groups['data'])) : ?>
                                    <?php foreach ($groups['data'] as $key) : ?>
                                        <li>
                                            <label class="adaptive-select__label" for="vipGroup-<?= $key['id'] ?>">
                                                <div class="adaptive-select__label-text"><?= $key['name'] ?></div>
                                                <input class="hide-input" id="vipGroup-<?=$key['id']?>" value="<?= $key['ini'] ?>" type="radio" name="vip-group-list">
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                            <div class="adaptive-select" open-select="vipGroupList">
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
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="vipExpireList">
                                <div class="inputs-inline">
                                    <div class="number" id="vipNumberControl">
                                        <button class="number-minus" type="button">-</button>
                                        <input id="vipExpireCustom" type="number" min="0" value="" step="1" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_ownTime') ?>" autocomplete="off">
                                        <button class="number-plus" type="button">+</button>
                                    </div>
                                </div>
                                <?php foreach ($terms['data'] as $term) : ?>
                                    <li>
                                        <label class="adaptive-select__label" for="vipExpireTime-<?= $term['id'] ?>">
                                            <div class="adaptive-select__label-text"><?= $term['name'] ?></div>
                                            <input class="hide-input" id="vipExpireTime-<?= $term['id'] ?>" value="<?= $term['time'] ?>" type="radio" name="vip-expire-list">
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="adaptive-select" open-select="vipExpireList">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#time-expired"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectVipExpire') ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="adaptive-select-wrapper" data-no-text>
                            <ul class="adaptive-select__dropdown-list" id="vipServerList">
                                <div class="inputs-inline">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                                    </svg>
                                    <input id="searchVipServer" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findServer') ?>" autocomplete="off">
                                </div>
                                <li>
                                    <label class="adaptive-select__label" for="vipServerAll">
                                        <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') ?></div>
                                        <input class="hide-input" id="vipServerAll" type="checkbox" value="-1" name="vip-server"<?= !empty($defaultAllServers) ? ' checked' : '' ?>>
                                    </label>
                                </li>
                                <?php if (!empty($servers['data'])): ?>
                                    <?php foreach ($servers['data'] as $i => $key) : ?>
                                        <li>
                                            <label class="adaptive-select__label" for="vipServer-<?= $i ?>">
                                                <div class="adaptive-select__label-text"><?= $key['name_custom'] ?> <span class="at__cs-label <?= $key['server_game'] == 'CS2' ? 'cs2' : 'csgo' ?>"><?= $key['server_game'] ?></span></div>
                                                <input class="hide-input" id="vipServer-<?= $i ?>" value="<?= $key['id'] ?>" type="checkbox" name="vip-server">
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                            <div class="adaptive-select" open-select="vipServerList">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#servers"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text"><?= !empty($defaultAllServers) ? $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') : $Translate->get_translate_module_phrase('module_page_atools', '_at_selectVipServers') ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <button class="width-100" id="createVip"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addVip') ?></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="at__content">
        <?php if (!empty($Db->db_data['Vips'])) : ?>
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
                <h2 class="at__h2"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipList') ?></h2>
                <div class="at__filter-views">
                    <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_wantToSee') ?></span>
                    <button class="filter button-icon">10</button>
                    <button class="filter button-icon">25</button>
                    <button class="filter button-icon">50</button>
                </div>
            </div>
            <div class="at__filters" id="vipFilters">
                <div class="adaptive-select-wrapper" id="vipServerListFilter" data-no-text>
                    <ul class="adaptive-select__dropdown-list" id="vipServerListFilterInner">
                        <div class="inputs-inline">
                            <svg>
                                <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                            </svg>
                            <input id="searchVipServerFilter" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findServer') ?>" autocomplete="off">
                        </div>
                        <li>
                            <label class="adaptive-select__label" for="vipServerAllFilter">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') ?></div>
                                <input class="hide-input" id="vipServerAllFilter" type="checkbox" value="-1" name="filter-vip-server" checked>
                            </label>
                        </li>
                        <?php if (!empty($servers['data'])) : ?>
                            <?php foreach ($servers['data'] as $i => $server) : ?>
                                <li>
                                    <label class="adaptive-select__label" for="vipServerFilter-<?= $i ?>">
                                        <div class="adaptive-select__label-text"><?= $server['name_custom'] ?> <span class="at__cs-label <?= $server['server_game'] == 'CS2' ? 'cs2' : 'csgo' ?>"><?= $server['server_game'] ?></span></div>
                                        <input class="hide-input" id="vipServerFilter-<?= $i ?>" value="<?= $server['id'] ?>" type="checkbox" name="filter-vip-server">
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <div class="adaptive-select" open-select="vipServerListFilterInner">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#servers"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_servers') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <?php if (!empty($filterGroups['data'])) : ?>
                    <div class="adaptive-select-wrapper" id="vipGroupListFilter">
                        <ul class="adaptive-select__dropdown-list" id="vipGroupListFilterInner">
                            <div class="inputs-inline">
                                <svg>
                                    <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                                </svg>
                                <input id="searchVipGroupFilter" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findGroup') ?>" autocomplete="off">
                            </div>
                            <li>
                                <label class="adaptive-select__label" for="vipGroupFilterAll">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allGroups') ?></div>
                                    <input class="hide-input" id="vipGroupFilterAll" value="-1" type="radio" name="filter-vip-group" checked>
                                </label>
                            </li>
                            <?php foreach ($filterGroups['data'] as $i => $group) : ?>
                                <li>
                                    <label class="adaptive-select__label" for="vipGroupFilter-<?= $i ?>">
                                        <div class="adaptive-select__label-text"><?= $group['name'] ?></div>
                                        <input class="hide-input" id="vipGroupFilter-<?= $i ?>" value="<?= $group['ini'] ?>" type="radio" name="filter-vip-group">
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="adaptive-select" open-select="vipGroupListFilterInner">
                            <span class="adaptive-select__fist-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#list"></use>
                                </svg>
                            </span>
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroup') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="vipExpireFilterList">
                        <li>
                            <label class="adaptive-select__label" for="vipExpireFilterAll">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allPrivileges') ?></div>
                                <input class="hide-input" id="vipExpireFilterAll" value="all" type="radio" name="filter-vip-expire" checked>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="vipExpireFilterForever">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_foreverPrivileges') ?></div>
                                <input class="hide-input" id="vipExpireFilterForever" value="forever" type="radio" name="filter-vip-expire">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="vipExpireFilterTemporary">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_temporaryPrivileges') ?></div>
                                <input class="hide-input" id="vipExpireFilterTemporary" value="temporary" type="radio" name="filter-vip-expire">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="vipExpireFilterExpired">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_expiredPrivileges') ?></div>
                                <input class="hide-input" id="vipExpireFilterExpired" value="expired" type="radio" name="filter-vip-expire">
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="vipExpireFilterList" style="min-width: max-content">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#time-expired"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allPrivileges') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <button class="button-delete at__button-reset" id="resetVipFilters">
                    <svg>
                        <use href="/resources/img/sprite.svg#broom"></use>
                    </svg>
                </button>
            </div>
            <div class="table-responsive at__table">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="at__check-bulk-col" style="opacity: 1"><input type="checkbox" id="selectAll"></th>
                            <th><?= $Translate->get_translate_phrase('_Player') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroup') ?></th>
                            <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_servers') ?>" data-tippy-placement="top">
                                <svg>
                                    <use href="/resources/img/sprite.svg#servers"></use>
                                </svg>
                            </th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_expire') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_remaining') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="vipsTable">
                        <?php for ($i = 0; $i < 10; $i++) : ?>
                            <tr class="skeleton--default">
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
            <div id="vipsPagination"></div>
        <?php endif; ?>
    </div>
</div>
<div class="popup_modal" id="changeVip"></div>