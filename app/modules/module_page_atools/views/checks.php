<div class="card at__card">
    <?php if ($AccessController->checkPermission('checks.view')) : ?>
        <?php if (empty($Db->db_data['AdminSystem']) && empty($Db->db_data['IksAdminNew'])) : ?>
            <div class="at__content">
                <div class="at__undefined-groups">
                    <div class="at__warning">
                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_checksImpossible') ?>
                        <hr>
                        <ul class="at__list">
                            <li data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_checksNeedConnects') ?>" data-tippy-placement="right">
                                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingAdminConnectAny') ?>
                                <svg>
                                    <use href="/resources/img/sprite.svg#info-circle"></use>
                                </svg>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php else : ?>
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
                    <h2 class="at__h2"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_CheckList') ?></h2>
                    <div class="at__filter-views">
                        <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_wantToSee') ?></span>
                        <button class="filter button-icon">10</button>
                        <button class="filter button-icon">25</button>
                        <button class="filter button-icon">50</button>
                    </div>
                </div>
                <div class="at__filters" id="checkFilters">
                    <div class="adaptive-select-wrapper" id="checkFilterByAdmin">
                        <ul class="adaptive-select__dropdown-list" id="checkAdminListFilter">
                            <div class="inputs-inline">
                                <svg>
                                    <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                                </svg>
                                <input id="searchCheckAdminListFilter" type="search" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findAdmin') ?>" autocomplete="off">
                            </div>
                            <li>
                                <label class="adaptive-select__label" for="checkAdminListFilterAll">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_byAdminAll') ?></div>
                                    <input class="hide-input" id="checkAdminListFilterAll" value="-1" type="radio" name="check-admin-list-filter" checked>
                                </label>
                            </li>
                            <?php foreach ($admins['data'] as $i => $admin) : ?>
                                <li>
                                    <label class="adaptive-select__label" for="checkAdminListFilter-<?= $i ?>">
                                        <div class="adaptive-select__label-text"><?= action_text_clear($admin['name']) ?></div>
                                        <input class="hide-input" id="checkAdminListFilter-<?= $i ?>" value="<?= $admin['id'] ?>" type="radio" name="check-admin-list-filter">
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="adaptive-select" open-select="checkAdminListFilter" style="min-width: max-content">
                            <span class="adaptive-select__fist-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#policeman"></use>
                                </svg>
                            </span>
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_byAdminAll') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="at__calendar-filter" id="checksCalendarFilter">
                        <button type="button" class="at__calendar-trigger" id="checksCalendarTrigger">
                            <span class="at__calendar-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#calendare"></use>
                                </svg>
                            </span>
                            <span class="at__calendar-period" id="checksCalendarPeriod"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectDateRange') ?></span>
                            <span class="margin-left-auto at__calendar-arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </button>
                        <div class="at__calendar-dropdown">
                            <div id="checksCalendar"></div>
                            <button type="button" class="at__calendar-dropdown-reset button-delete width-100"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_calendarReset') ?></button>
                        </div>
                        <input type="hidden" id="checkStartDate">
                        <input type="hidden" id="checkEndDate">
                    </div>
                    <div class="adaptive-select-wrapper" id="checkFilterVerdicts">
                        <ul class="adaptive-select__dropdown-list" id="checkVerdictsListFilter">
                            <li>
                                <label class="adaptive-select__label" for="checkVerdictsListFilterAll">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allVerdicts') ?></div>
                                    <input class="hide-input" id="checkVerdictsListFilterAll" value="all" type="radio" name="check-verdicts-list-filter" checked>
                                </label>
                            </li>
                            <?php if (!empty($verdicts['data'])) : ?>
                                <?php foreach ($verdicts['data'] as $i => $verdict) : ?>
                                    <li>
                                        <label class="adaptive-select__label" for="checkVerdictsListFilter-<?= $i ?>">
                                            <div class="adaptive-select__label-text"><?= action_text_clear($verdict['name'] ?? '') ?></div>
                                            <input class="hide-input" id="checkVerdictsListFilter-<?= $i ?>" value="<?= $verdict['id'] ?>" type="radio" name="check-verdicts-list-filter">
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        <div class="adaptive-select" open-select="checkVerdictsListFilter" style="min-width: max-content">
                            <span class="adaptive-select__fist-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#list"></use>
                                </svg>
                            </span>
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allVerdicts') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="adaptive-select-wrapper" id="checkFilterServers" data-no-text>
                        <ul class="adaptive-select__dropdown-list" id="checkServersListFilter">
                            <div class="inputs-inline">
                                <svg>
                                    <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                                </svg>
                                <input id="searchCheckServersListFilter" type="search" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findServer') ?>" autocomplete="off">
                            </div>
                            <li>
                                <label class="adaptive-select__label" for="checkServersListFilterAll">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') ?></div>
                                    <input class="hide-input" id="checkServersListFilterAll" value="-1" type="checkbox" name="check-servers-list-filter" checked>
                                </label>
                            </li>
                            <?php if (!empty($servers['data'])) : ?>
                                <?php foreach ($servers['data'] as $i => $server) : ?>
                                    <li>
                                        <label class="adaptive-select__label" for="checkServersListFilter-<?= $i ?>">
                                            <div class="adaptive-select__label-text"><?= $server['name_custom'] ?></div>
                                            <input class="hide-input" id="checkServersListFilter-<?= $i ?>" value="<?= $server['id'] ?>" type="checkbox" name="check-servers-list-filter">
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        <div class="adaptive-select" open-select="checkServersListFilter">
                            <span class="adaptive-select__fist-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#servers"></use>
                                </svg>
                            </span>
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <button class="button-delete at__button-reset" id="resetCheckFilters">
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
                                <th>ID</th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_checkedPlayer') ?></th>
                                <th><?= $Translate->get_translate_phrase('_Admin') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_checkStart') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_checkEnd') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_checkVerdict') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_contact') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="checkTable">
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
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
                <div id="checksPagination"></div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
