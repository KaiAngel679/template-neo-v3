<div class="card at__card">
    <?php if ($AccessController->checkPermission('experience.update') || $AccessController->checkPermission('experience.reset')) : ?>
        <div class="at__section" style="gap: .5rem; flex: none;">
            <?php if ($AccessController->checkPermission('experience.update')) : ?>
                <div class="at__panel">
                    <div class="at__panel-title">
                        <h2 class="at__h2">
                            <svg>
                                <use href="/resources/img/sprite.svg#plus"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingExperience') ?>
                        </h2>
                    </div>
                    <div class="at__panel-content">
                        <?php if (empty($Db->db_data['LevelsRanks'])) : ?>
                            <div class="at__undefined-groups">
                                <div class="at__warning">
                                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingExperienceImpossible') ?>
                                    <hr>
                                    <ul class="at__list">
                                        <li data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingExperienceNeedConnects') ?>" data-tippy-placement="right">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingExperienceConnectAny') ?>
                                            <svg>
                                                <use href="/resources/img/sprite.svg#info-circle"></use>
                                            </svg>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="at__panel-wrapper" id="addingExperience">
                                <div class="inputs-inline">
                                    <label for="experienceSteamIdInput"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_experienceSteamId') ?></label>
                                    <input id="experienceSteamIdInput" type="text" value="" name="steamid" placeholder="https://steamcommunity.com/profiles/... / STEAM_1:1:390... / 7656119803... / [U:1:1234234]" autocomplete="off" required>
                                </div>
                                <div class="inputs-inline">
                                    <label for="countExperienceAdding"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_summExperienceAdding') ?></label>
                                    <div class="number" id="experienceNumberControl">
                                        <button class="number-minus" type="button">-</button>
                                        <input id="countExperienceAdding" type="number" min="1" step="1" value="">
                                        <button class="number-plus" type="button">+</button>
                                    </div>
                                </div>
                                <div class="adaptive-select-wrapper" data-no-text>
                                    <ul class="adaptive-select__dropdown-list" id="experienceServerList">
                                        <div class="inputs-inline">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                                            </svg>
                                            <input id="searchExperienceServer" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findServer') ?>" autocomplete="off">
                                        </div>
                                        <li>
                                            <label class="adaptive-select__label" for="experienceServerAll">
                                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') ?></div>
                                                <input class="hide-input" id="experienceServerAll" type="checkbox" value="-1" name="experience-server"<?= !empty($defaultAllServers) ? ' checked' : '' ?>>
                                            </label>
                                        </li>
                                        <?php if (!empty($servers['data'])) : ?>
                                            <?php foreach ($servers['data'] as $i => $server) : ?>
                                                <li>
                                                    <label class="adaptive-select__label" for="experienceServer-<?= $i ?>">
                                                        <div class="adaptive-select__label-text"><?= action_text_clear($server['name_custom']) ?> <span class="at__cs-label <?= $server['server_game'] === 'CS2' ? 'cs2' : 'csgo' ?>"><?= $server['server_game'] ?></span></div>
                                                        <input class="hide-input" id="experienceServer-<?= $i ?>" value="<?= (int) $server['id'] ?>" type="checkbox" name="experience-server">
                                                    </label>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                    <div class="adaptive-select" open-select="experienceServerList">
                                        <span class="adaptive-select__fist-icon">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#servers"></use>
                                            </svg>
                                        </span>
                                        <span class="adaptive-select__span_text"><?= !empty($defaultAllServers) ? $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') : $Translate->get_translate_module_phrase('module_page_atools', '_at_selectExperienceServers') ?></span>
                                        <span class="margin-left-auto adaptive-select__arrow">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <button class="width-100" id="addExperience" type="button"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addExperience') ?></button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($AccessController->checkPermission('experience.reset') && !empty($Db->db_data['LevelsRanks'])) : ?>
                <div class="at__panel">
                    <div class="at__panel-title">
                        <h2 class="at__h2">
                            <svg>
                                <use href="/resources/img/sprite.svg#broom"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_experienceActions') ?>
                        </h2>
                    </div>
                    <div class="at__panel-content">
                        <div class="at__panel-wrapper" id="experienceActions">
                            <div class="adaptive-select-wrapper" data-no-text>
                                <ul class="adaptive-select__dropdown-list" id="experienceActionServerList">
                                    <div class="inputs-inline">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                                        </svg>
                                        <input id="searchExperienceActionServer" type="search" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findServer') ?>" autocomplete="off">
                                    </div>
                                    <li>
                                        <label class="adaptive-select__label" for="experienceActionServerAll">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') ?></div>
                                            <input class="hide-input" id="experienceActionServerAll" value="-1" type="checkbox" name="experience-action-server"<?= !empty($defaultAllServers) ? ' checked' : '' ?>>
                                        </label>
                                    </li>
                                    <?php if (!empty($servers['data'])) : ?>
                                        <?php foreach ($servers['data'] as $i => $server) : ?>
                                            <li>
                                                <label class="adaptive-select__label" for="experienceActionServer-<?= $i ?>">
                                                    <div class="adaptive-select__label-text"><?= action_text_clear($server['name_custom']) ?> <span class="at__cs-label <?= $server['server_game'] === 'CS2' ? 'cs2' : 'csgo' ?>"><?= $server['server_game'] ?></span></div>
                                                    <input class="hide-input" id="experienceActionServer-<?= $i ?>" value="<?= (int) $server['id'] ?>" type="checkbox" name="experience-action-server">
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                                <div class="adaptive-select" open-select="experienceActionServerList">
                                    <span class="adaptive-select__fist-icon">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#servers"></use>
                                        </svg>
                                    </span>
                                    <span class="adaptive-select__span_text"><?= !empty($defaultAllServers) ? $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') : $Translate->get_translate_module_phrase('module_page_atools', '_at_servers') ?></span>
                                    <span class="margin-left-auto adaptive-select__arrow">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div class="at__settings-info-block">
                                <span class="at__settings-info-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_clearAllStatsDesc') ?></span>
                                <button class="button-delete width-100" id="clearAllExperienceStats" type="button">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#broom"></use>
                                    </svg>
                                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_clearAllStats') ?>
                                </button>
                            </div>
                            <div class="at__settings-info-block">
                                <span class="at__settings-info-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_clearEmptyPlayersDesc') ?></span>
                                <button class="width-100" id="clearEmptyExperiencePlayers" type="button">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#ghost"></use>
                                    </svg>
                                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_clearEmptyPlayers') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="at__content">
            <div class="at__search">
                <input id="searchInfo" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_enterNickOrSteamID') ?>" autocomplete="off">
                <button id="buttonSearchInfo" type="button">
                    <svg>
                        <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findPlayer') ?>
                </button>
            </div>
            <div class="at__filter-block">
                <h2 class="at__h2"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_experienceList') ?></h2>
                <div class="at__filter-views">
                    <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_wantToSee') ?></span>
                    <button class="filter button-icon" type="button">10</button>
                    <button class="filter button-icon" type="button">25</button>
                    <button class="filter button-icon" type="button">50</button>
                </div>
            </div>
            <div class="at__filters" id="experienceFilters">
                <div class="adaptive-select-wrapper" id="experienceServerListFilter" data-no-text>
                    <ul class="adaptive-select__dropdown-list" id="experienceServerListFilterInner">
                        <div class="inputs-inline">
                            <svg>
                                <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                            </svg>
                            <input id="searchExperienceServerFilter" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findServer') ?>" autocomplete="off">
                        </div>
                        <li>
                            <label class="adaptive-select__label" for="experienceServerAllFilter">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_allServers') ?></div>
                                <input class="hide-input" id="experienceServerAllFilter" type="checkbox" value="-1" name="filter-experience-server" checked>
                            </label>
                        </li>
                        <?php if (!empty($servers['data'])) : ?>
                            <?php foreach ($servers['data'] as $i => $server) : ?>
                                <li>
                                    <label class="adaptive-select__label" for="experienceServerFilter-<?= $i ?>">
                                        <div class="adaptive-select__label-text"><?= action_text_clear($server['name_custom']) ?> <span class="at__cs-label <?= $server['server_game'] === 'CS2' ? 'cs2' : 'csgo' ?>"><?= $server['server_game'] ?></span></div>
                                        <input class="hide-input" id="experienceServerFilter-<?= $i ?>" value="<?= (int) $server['id'] ?>" type="checkbox" name="filter-experience-server">
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <div class="adaptive-select" open-select="experienceServerListFilterInner">
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
                <div class="adaptive-select-wrapper" data-change-icon>
                    <ul class="adaptive-select__dropdown-list" id="filterExperienceValue">
                        <li>
                            <label class="adaptive-select__label" for="filterExperienceSortDown">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#filterDown"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_sortDown') ?></div>
                                <input class="hide-input" id="filterExperienceSortDown" value="down" type="radio" name="filter-experience-sorting" checked>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="filterExperienceSortUp">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#filterUp"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_sortUp') ?></div>
                                <input class="hide-input" id="filterExperienceSortUp" value="up" type="radio" name="filter-experience-sorting">
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="filterExperienceValue" style="min-width: max-content">
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
                <button class="button-delete at__button-reset" id="resetExperienceFilters" type="button">
                    <svg>
                        <use href="/resources/img/sprite.svg#broom"></use>
                    </svg>
                </button>
            </div>
            <div class="table-responsive at__table">
                <table class="table">
                    <thead>
                        <tr>
                            <?php if ($AccessController->checkPermission('experience.reset')) : ?>
                                <th class="at__check-bulk-col" style="opacity: 1"><input type="checkbox" id="selectAll"></th>
                            <?php else : ?>
                                <th class="at__check-bulk-col"></th>
                            <?php endif; ?>
                            <th><?= $Translate->get_translate_phrase('_Player') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_colExperience') ?></th>
                            <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_colKills') ?>" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#pistol"></use></svg></th>
                            <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_colDeaths') ?>" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#skull"></use></svg></th>
                            <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_colShots') ?>" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#crosshair"></use></svg></th>
                            <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_colHits') ?>" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#target"></use></svg></th>
                            <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_colHeadshots') ?>" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#headshot"></use></svg></th>
                            <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_colPlaytime') ?>" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#time-timer"></use></svg></th>
                            <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_servers') ?>" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#servers"></use></svg></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="experienceTable">
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
                                <td></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        <div id="experiencePagination"></div>
    </div>
</div>
<div class="popup_modal" id="changeExperience"></div>
