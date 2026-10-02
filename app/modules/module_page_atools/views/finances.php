<div class="card at__card">
    <?php if ($AccessController->checkPermission('finances.update') || $AccessController->checkPermission('finances.reset')) : ?>
        <div class="at__section" style="gap: .5rem; flex: none;">
            <?php if ($AccessController->checkPermission('finances.update')) : ?>
                <div class="at__panel">
                    <div class="at__panel-title">
                        <h2 class="at__h2">
                            <svg>
                                <use href="/resources/img/sprite.svg#plus"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingBalance') ?>
                        </h2>
                    </div>
                    <div class="at__panel-content">
                        <?php if (empty($Db->db_data['lk'])) : ?>
                            <div class="at__undefined-groups">
                                <div class="at__warning">
                                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingFinancesImpossible') ?>
                                    <hr>
                                    <ul class="at__list">
                                        <li data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingFinancesNeedConnects') ?>" data-tippy-placement="right">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingFinancesConnectAny') ?>
                                            <svg>
                                                <use href="/resources/img/sprite.svg#info-circle"></use>
                                            </svg>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="at__panel-wrapper" id="addingFinance">
                                <div class="inputs-inline">
                                    <label for="financeSteamIdInput"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_balanceSteamId') ?></label>
                                    <input id="financeSteamIdInput" type="text" value="" name="steamid" placeholder="STEAM_1:1:390... / 7656119803..." autocomplete="off" required>
                                </div>
                                <div class="inputs-inline">
                                    <label for="countSummAdding"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_summAdding') ?></label>
                                    <div class="number" id="numberControlAdd">
                                        <button class="number-minus" type="button">-</button>
                                        <input id="countSummAdding" type="number" min="1" step="0.01" value="">
                                        <button class="number-plus" type="button">+</button>
                                    </div>
                                </div>
                                <button class="width-100" id="addFinanceBalance" type="button"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addBalance') ?></button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($AccessController->checkPermission('finances.reset') && !empty($Db->db_data['lk'])) : ?>
                <div class="at__panel">
                    <div class="at__panel-title">
                        <h2 class="at__h2">
                            <svg>
                                <use href="/resources/img/sprite.svg#broom"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_financeActions') ?>
                        </h2>
                    </div>
                    <div class="at__panel-content">
                        <div class="at__panel-wrapper" id="financeActions">
                            <div class="at__settings-info-block">
                                <span class="at__settings-info-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_clearPlayersWithoutDonationDesc') ?></span>
                                <button class="width-100" id="clearFinancePlayersWithoutDonation" type="button">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#ghost"></use>
                                    </svg>
                                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_clearPlayersWithoutDonation') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="at__content">
        <?php if (empty($Db->db_data['lk'])) : ?>
            <div class="at__undefined-groups">
                <div class="at__warning">
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingFinancesImpossible') ?>
                    <hr>
                    <ul class="at__list">
                        <li data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingFinancesNeedConnects') ?>" data-tippy-placement="right">
                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingFinancesConnectAny') ?>
                            <svg>
                                <use href="/resources/img/sprite.svg#info-circle"></use>
                            </svg>
                        </li>
                    </ul>
                </div>
            </div>
        <?php else : ?>
            <div class="at__search">
                <input id="searchInfo" type="search" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_enterNickOrSteamID') ?>" autocomplete="off">
                <button id="buttonSearchInfo" type="button">
                    <svg>
                        <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findPlayer') ?>
                </button>
            </div>
            <div class="at__filter-block">
                <h2 class="at__h2"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_financeList') ?></h2>
                <div class="at__filter-views">
                    <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_wantToSee') ?></span>
                    <button class="filter button-icon" type="button">10</button>
                    <button class="filter button-icon" type="button">25</button>
                    <button class="filter button-icon" type="button">50</button>
                </div>
            </div>
            <div class="at__filters" id="financeFilters">
                <div class="adaptive-select-wrapper" data-change-icon>
                    <ul class="adaptive-select__dropdown-list" id="filterFinanceValue">
                        <li>
                            <label class="adaptive-select__label" for="filterSortDown">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#filterDown"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_sortDown') ?></div>
                                <input class="hide-input" id="filterSortDown" value="down" type="radio" name="filter-sorting" checked>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="filterSortUp">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#filterUp"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_sortUp') ?></div>
                                <input class="hide-input" id="filterSortUp" value="up" type="radio" name="filter-sorting">
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="filterFinanceValue" style="min-width: max-content">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#filters"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_sortDown') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="financeBalanceFilterList">
                        <li>
                            <label class="adaptive-select__label" for="financeBalanceFilterAll">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allFinances') ?></div>
                                <input class="hide-input" id="financeBalanceFilterAll" value="all" type="radio" name="filter-finance-balance" checked>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="financeBalanceFilterWith">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_financesWithBalance') ?></div>
                                <input class="hide-input" id="financeBalanceFilterWith" value="with_balance" type="radio" name="filter-finance-balance">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="financeBalanceFilterEmpty">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_financesEmptyBalance') ?></div>
                                <input class="hide-input" id="financeBalanceFilterEmpty" value="empty" type="radio" name="filter-finance-balance">
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="financeBalanceFilterList" style="min-width: max-content">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#wallet"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allFinances') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <button class="button-delete at__button-reset" id="resetFinanceFilters" type="button">
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
                            <th><?= $Translate->get_translate_phrase('_Current_balance') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allTime') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_lastDeposit') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="financesTable">
                        <?php for ($i = 0; $i < 10; $i++) : ?>
                            <tr class="skeleton--default">
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
            <div id="financesPagination"></div>
        <?php endif; ?>
    </div>
</div>
<div class="popup_modal" id="changeFinance"></div>
