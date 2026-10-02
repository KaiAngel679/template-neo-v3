<script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/index.js" defer></script>
<script src="/app/modules/module_page_results/assets/js/apexcharts.js?<?= time() ?>" defer></script>
<div class="col-md-9">
    <div class="card">
        <div class="card-header">
            <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_results', '_resultsInterval') ?> <span id="adminTimePeriod">02.02.26-08.02.26</span></div>
        </div>
        <div class="card-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 12rem;"><?= $Translate->get_translate_module_phrase('module_page_results', '_admin') ?></th>
                            <th class="table-sort active" data-sort="group">
                                <div class="table-filter">
                                    <?= $Translate->get_translate_module_phrase('module_page_results', '_position') ?>
                                    <svg>
                                        <use href="/resources/img/sprite.svg#arrow-up-down"></use>
                                    </svg>
                                </div>
                            </th>
                            <th class="table-sort" data-sort="playtime">
                                <div class="table-filter">
                                    <?= $Translate->get_translate_module_phrase('module_page_results', '_played') ?>
                                    <svg>
                                        <use href="/resources/img/sprite.svg#arrow-up-down"></use>
                                    </svg>
                                </div>
                            </th>
                            <th class="table-sort" data-sort="bans">
                                <div class="table-filter">
                                    <?= $Translate->get_translate_module_phrase('module_page_results', '_bans') ?>
                                    <svg>
                                        <use href="/resources/img/sprite.svg#arrow-up-down"></use>
                                    </svg>
                                </div>
                            </th>
                            <th class="table-sort" data-sort="gags">
                                <div class="table-filter">
                                    <?= $Translate->get_translate_module_phrase('module_page_results', '_gags') ?>
                                    <svg>
                                        <use href="/resources/img/sprite.svg#arrow-up-down"></use>
                                    </svg>
                                </div>
                            </th>
                            <th class="table-sort" data-sort="checks">
                                <div class="table-filter">
                                    <?= $Translate->get_translate_module_phrase('module_page_results', '_checks') ?>
                                    <svg>
                                        <use href="/resources/img/sprite.svg#arrow-up-down"></use>
                                    </svg>
                                </div>
                            </th>
                            <th class="table-sort" data-sort="reports">
                                <div class="table-filter">
                                    <?= $Translate->get_translate_module_phrase('module_page_results', '_reports') ?>
                                    <svg>
                                        <use href="/resources/img/sprite.svg#arrow-up-down"></use>
                                    </svg>
                                </div>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="AdminTimeTableBody">
                        <?php for ($i = 0; $i < 10; $i++): ?>
                            <tr class="skeleton--default">
                                <td>
                                    <span class="results__admin">
                                        <a id="name" nameid="">
                                            Unnamed
                                        </a>
                                    </span>
                                </td>
                                <td>Group</td>
                                <td>0.0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="Pagination"></div>
    </div>

</div>
<div class="col-md-3">
    <div class="card">
        <div class="card-header">
            <div class="badge">
                <?= $Translate->get_translate_module_phrase('module_page_results', '_filter') ?>
            </div>
        </div>
        <div class="card-container">
            <div class="results__calendare">
                <span class="results__filter-label"><?= $Translate->get_translate_module_phrase('module_page_results', '_selectInterval') ?></span>
                <div id="VanillaCalendar" class="skeleton--default results__calendare-height"></div>
                <input type="hidden" name="date_start" id="dateStart">
                <input type="hidden" name="date_end" id="dateEnd">
            </div>
            <div class="results__sorting">
                <span class="results__filter-label"><?= $Translate->get_translate_module_phrase('module_page_results', '_selectGroups') ?></span>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="serversGroupAdminTime">
                        <li>
                            <label class="adaptive-select__label" for="group-all">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_results', '_allGroups') ?></div>
                                <input class="hide-input" id="group-all" type="checkbox" name="group[]" value="0" checked>
                            </label>
                        </li>
                        <?php foreach ($groups as $index => $group): ?>
                            <li>
                                <label class="adaptive-select__label" for="group-<?= $index ?>">
                                    <div class="adaptive-select__label-text"><?= $group['name'] ?></div>
                                    <input class="hide-input" id="group-<?= $index ?>" type="checkbox" name="group[]" value="<?= $group['id'] ?>">
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="serversGroupAdminTime">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#policeman"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_results', '_selectGroups') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <span class="results__filter-label" style="margin-top: .5rem"><?= $Translate->get_translate_module_phrase('module_page_results', '_selectServer') ?></span>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="serversSelectAdminTime">
                        <li>
                            <label class="adaptive-select__label" for="servers-all">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_results', '_allServers') ?></div>
                                <input class="hide-input" id="servers-all" type="checkbox" name="server[]" value="0" checked>
                            </label>
                        </li>
                        <?php foreach ($servers as $index => $server): ?>
                            <li>
                                <label class="adaptive-select__label" for="servers-<?= $index ?>">
                                    <div class="adaptive-select__label-text"><?= $server['name'] ?></div>
                                    <input class="hide-input" id="servers-<?= $index ?>" type="checkbox" name="server[]" value="<?= $server['id'] ?>">
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="serversSelectAdminTime">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#servers"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_results', '_selectServer') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <span class="results__filter-label" style="margin-top: .5rem"><?= $Translate->get_translate_module_phrase('module_page_results', '_normOnline') ?></span>
                <div class="inputs-inline">
                    <input type="checkbox" id="timePlayed" class="switch" name="timePlayedEnable">
                    <label for="timePlayed"><?= $Translate->get_translate_module_phrase('module_page_results', '_withNorm') ?></label>
                </div>
                <div class="inputs-inline">
                    <label for="searchAdmin"><?= $Translate->get_translate_module_phrase('module_page_results', '_searchAdmin') ?></label>
                    <input id="searchAdmin" type="search" value="" name="searchAdmin" placeholder="<?= $Translate->get_translate_module_phrase('module_page_results', '_searchAdminPlaceholder') ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="popup_modal" id="addWarnAward">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_results', '_givingAwardWarn') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form id="giveAwardWarnForm">
            <div class="reason__info-noty">
                <?= $Translate->get_translate_module_phrase('module_page_results', '_infoText') ?>
                <a href="/results/">
                    "<?= $Translate->get_translate_module_phrase('module_page_results', '_infoText2') ?>"
                </a>
                <?= $Translate->get_translate_module_phrase('module_page_results', '_infoText3') ?>
            </div>
            <div class="inputs-inline">
                <input type="checkbox" id="warnEnable" class="switch">
                <label for="warnEnable"><?= $Translate->get_translate_module_phrase('module_page_results', '_giveWarn') ?></label>
            </div>
            <div class="flex-inline">
                <div class="inputs-inline">
                    <label for="warnReason"><?= $Translate->get_translate_module_phrase('module_page_results', '_warnReason') ?></label>
                    <input id="warnReason" type="text" value="" name="warnReason" placeholder="<?= $Translate->get_translate_module_phrase('module_page_results', '_Describe_Reason') ?>">
                </div>
                <div class="inputs-inline">
                    <label for="warnDuration"><?= $Translate->get_translate_module_phrase('module_page_results', '_warnTime') ?></label>
                    <input id="warnDuration" type="text" value="" name="warnDuration" placeholder="24">
                </div>
            </div>
            <hr>
            <div class="inputs-inline">
                <input type="checkbox" id="awardEnable" class="switch">
                <label for="awardEnable"><?= $Translate->get_translate_module_phrase('module_page_results', '_giveAward') ?></label>
            </div>
            <div class="flex-inline">
                <div class="inputs-inline">
                    <label for="awardReason"><?= $Translate->get_translate_module_phrase('module_page_results', '_awardFor') ?></label>
                    <input id="awardReason" type="text" value="" name="awardReason" placeholder="<?= $Translate->get_translate_module_phrase('module_page_results', '_awardForplaceholder') ?>">
                </div>
                <div class="inputs-inline">
                    <label for="awardCount"><?= $Translate->get_translate_module_phrase('module_page_results', '_count') ?></label>
                    <input id="awardCount" type="text" value="" name="awardCount" placeholder="100">
                </div>
            </div>
            <button class="width-100"><?= $Translate->get_translate_module_phrase('module_page_results', '_giveAwardWarn') ?></button>
        </form>
    </div>
</div>

<div class="popup_modal" id="sessions">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <span id="sessions_adminName"></span>
            <div class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </div>
        </div>
        <div class="popup_sessions_body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_phrase('_Server') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_results', '_Date') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_results', '_joinleaveTime') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_results', '_played') ?></th>
                        </tr>
                    </thead>
                    <tbody id="sessions_list"></tbody>
                </table>
            </div>
            <div id="sessions_pagination"></div>
        </div>
    </div>
</div>

<div class="popup_modal" id="charts">
    <div class="popup_modal_content no-close no-scrollbar apexchatr__content">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_results', '_graphics') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div class="">
            <div class="reason__info-noty apexchatr__info-noty">
                <?= $Translate->get_translate_module_phrase('module_page_results', '_graphicsInfo') ?>
            </div>
            <div id="ApexChart"></div>
        </div>
    </div>
</div>