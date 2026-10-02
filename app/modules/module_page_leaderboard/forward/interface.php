<div class="row">
    <div class="col-md-3 fix-width-tablet">
        <div class="card sticky-filter">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_phrase('_infoIziToast') ?></h5>
            </div>
            <div class="card-container">
                <div id="userStatsContainer"></div>
                <div class="inputs-inline">
                    <label for=""><?= $Translate->get_translate_phrase('_selectServer'); ?></label>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="option-server-select">
                            <?php if (!empty($Db->db_data['LevelsRanks'])): ?>
                                <?php for ($b = 0; $b < $Db->table_statistics_count; $b++): ?>
                                    <li>
                                        <label class="adaptive-select__label" for="for_<?= $b ?>">
                                            <div class="adaptive-select__label-text"><?= $Db->statistics_table[$b]['name'] ?></div>
                                            <input class="hide-input" id="for_<?= $b ?>" type="radio" name="server" value="<?= $b ?>" <?= ($server_group == $b) ? 'checked' : ''; ?>>
                                        </label>
                                    </li>
                                <?php endfor; ?>
                            <?php else: ?>
                                <li>
                                    <label class="adaptive-select__label" for="for_0">
                                        <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_Server_Not_Found') ?></div>
                                        <input class="hide-input" id="for_0" type="radio" name="server" value="0" checked>
                                    </label>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <div class="adaptive-select" open-select="option-server-select">
                            <span class="adaptive-select__span_text"><?= $Db->statistics_table[$server_group]['name'] ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <fieldset class="leaderboard__fieldset">
                    <legend><?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_sortBy'); ?></legend>
                    <div class="leaderboard__form">
                        <div class="inputs-inline">
                            <label for="value" class="leaderboard__form-label"><input id="value" type="radio" name="filter" value="0" checked>
                                <?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_gamePoints'); ?>
                            </label>
                        </div>
                        <div class="inputs-inline">
                            <label for="kills" class="leaderboard__form-label"><input id="kills" type="radio" name="filter" value="1">
                                <?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_numbersKills'); ?>
                            </label>
                        </div>
                        <div class="inputs-inline">
                            <label for="deaths" class="leaderboard__form-label"><input id="deaths" type="radio" name="filter" value="2">
                                <?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_numbersDeaths'); ?>
                            </label>
                        </div>
                        <div class="inputs-inline">
                            <label for="kd" class="leaderboard__form-label"><input id="kd" type="radio" name="filter" value="3">K/D</label>
                        </div>
                        <div class="inputs-inline">
                            <label for="headshots" class="leaderboard__form-label"><input id="headshots" type="radio" name="filter" value="4">
                                <?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_killsHead'); ?>
                            </label>
                        </div>
                        <div class="inputs-inline">
                            <label for="playtime" class="leaderboard__form-label"><input id="playtime" type="radio" name="filter" value="5">
                                <?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_playedTime'); ?>
                            </label>
                        </div>
                        <div class="inputs-inline">
                            <label for="lastconnect" class="leaderboard__form-label"><input id="lastconnect" type="radio" name="filter" value="6">
                                <?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_lastSession'); ?>
                            </label>
                        </div>
                        <button class="button-delete width-100" id="resetFilter">
                            <?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_reset'); ?>
                        </button>
                    </div>
                </fieldset>
                <fieldset>
                    <legend><?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_other'); ?></legend>
                    <div class="inputs-inline">
                        <input type="checkbox" id="clear_banned" class="switch" name="clear_banned">
                        <label for="clear_banned"><?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_clear_banned'); ?></label>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
    <div class="col-md-9 fix-width-tablet">
        <div class="table-responsive" id="contentTable">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><svg><use href="/resources/img/sprite.svg#faceit-logo"></use></svg></th>
                        <th style="width: 242px"><?= $Translate->get_translate_phrase('_Player') ?></th>
                        <th style="width: 112px"><?= $Translate->get_translate_phrase('_Rank') ?></th>
                        <?= isset($General->arr_general['premier_ranks']) && $General->arr_general['premier_ranks'] ? '' : '<th>' . $Translate->get_translate_module_phrase('module_page_leaderboard', '_Experience') . '</th>' ?>
                        <th><?= $Translate->get_translate_phrase('_Kills') ?></th>
                        <th><?= $Translate->get_translate_phrase('_Deaths') ?></th>
                        <th><?= $Translate->get_translate_phrase('_Ratio_KD_short') ?></th>
                        <th><?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_KilHead') ?></th>
                        <th><?= $Translate->get_translate_module_phrase('module_page_leaderboard', '_Played') ?></th>
                        <th><?= $Translate->get_translate_phrase('_Plays_since') ?></th>
                    </tr>
                </thead>
                <tbody id="leaderboardTableBody">
                </tbody>
            </table>
        </div>
        <div id="contentPagination"></div>
    </div>
</div>