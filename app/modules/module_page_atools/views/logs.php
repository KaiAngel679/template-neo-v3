<div class="card at__card">
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
            <h2 class="at__h2"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsList') ?></h2>
            <div class="at__filter-views">
                <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_wantToSee') ?></span>
                <button class="filter button-icon">10</button>
                <button class="filter button-icon">25</button>
                <button class="filter button-icon">50</button>
            </div>
        </div>
        <div class="at__filters" id="logsFilters">
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="logsType">
                    <li>
                        <label class="adaptive-select__label" for="logsAll">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsShowAll') ?></div>
                            <input class="hide-input" id="logsAll" value="all" type="radio" name="logs-type" checked>
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="logsPunish">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsPunish') ?></div>
                            <input class="hide-input" id="logsPunish" value="punish" type="radio" name="logs-type">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="logsAdmins">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsAdmins') ?></div>
                            <input class="hide-input" id="logsAdmins" value="admins" type="radio" name="logs-type">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="logsChecks">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsChecks') ?></div>
                            <input class="hide-input" id="logsChecks" value="checks" type="radio" name="logs-type">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="logsFinances">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsFinances') ?></div>
                            <input class="hide-input" id="logsFinances" value="finances" type="radio" name="logs-type">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="logsPrivileges">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsPrivilegies') ?></div>
                            <input class="hide-input" id="logsPrivileges" value="privileges" type="radio" name="logs-type">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="logsCredits">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsCredits') ?></div>
                            <input class="hide-input" id="logsCredits" value="credits" type="radio" name="logs-type">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="logsExperience">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsExperience') ?></div>
                            <input class="hide-input" id="logsExperience" value="experience" type="radio" name="logs-type">
                        </label>
                    </li>
                </ul>
                <div class="adaptive-select" open-select="logsType">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#list"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsType') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="adaptive-select-wrapper" data-change-icon>
                <ul class="adaptive-select__dropdown-list" id="sortByFreshness">
                    <li>
                        <label class="adaptive-select__label" for="newLogs">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#filterUp"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsNewestFirst') ?></div>
                            <input class="hide-input" id="newLogs" value="new" type="radio" name="logs-sort" checked>
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="oldLogs">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#filterDown"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsOldestFirst') ?></div>
                            <input class="hide-input" id="oldLogs" value="old" type="radio" name="logs-sort">
                        </label>
                    </li>
                </ul>
                <div class="adaptive-select" open-select="sortByFreshness">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#filters"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logsByFreshness') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="at__calendar-filter" id="logsCalendarFilter">
                <button type="button" class="at__calendar-trigger" id="logsCalendarTrigger">
                    <span class="at__calendar-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#calendare"></use>
                        </svg>
                    </span>
                    <span class="at__calendar-period" id="logsCalendarPeriod"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectDateRange') ?></span>
                    <span class="margin-left-auto at__calendar-arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </button>
                <div class="at__calendar-dropdown">
                    <div id="logsCalendar"></div>
                    <button type="button" class="at__calendar-dropdown-reset button-delete width-100"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_calendarReset') ?></button>
                </div>
                <input type="hidden" id="logStartDate">
                <input type="hidden" id="logEndDate">
            </div>
            <button class="button-delete at__button-reset" id="resetLogFilters">
                <svg>
                    <use href="/resources/img/sprite.svg#broom"></use>
                </svg>
            </button>
        </div>
        <div class="at__logs-wrapper" id="logsList">
            <?php for ($i = 0; $i < 10; $i++) : ?>
                <div class="at__logs-card skeleton--default"></div>
            <?php endfor; ?>
        </div>
        <div id="logsPagination"></div>
    </div>
</div>
