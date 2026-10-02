<?php (!isset($_SESSION['user_admin'])) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die(); ?>
<script src="/app/templates/neo_remastered/assets/js/tabs.js" async></script>
<div class="tabs">
    <div class="tabs__buttons navigation-filters" role="tablist" aria-labelledby="tablist-1">
        <button class="filter" id="tab-1" type="button" role="tab" aria-selected="true" aria-controls="tabpanel-1">
            <span><?= $Translate->get_translate_module_phrase('module_page_results', '_MainSettings') ?></span>
        </button>
        <button class="filter" id="tab-2" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-2" tabindex="-1">
            <span><?= $Translate->get_translate_module_phrase('module_page_results', '_accesses') ?></span>
        </button>
        <button class="filter" id="tab-3" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-3" tabindex="-1">
            <span><?= $Translate->get_translate_module_phrase('module_page_results', '_Logs') ?></span>
        </button>
    </div>

    <div class="row" id="tabpanel-1" role="tabpanel" tabindex="0" aria-labelledby="tab-1">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="badge">
                        <?= $Translate->get_translate_module_phrase('module_page_results', '_settings') ?>
                    </div>
                </div>
                <form class="card-container" id="SettingsForm">
                    <div class="inputs-inline">
                        <input type="checkbox" id="onlyTheirs" class="switch" name="only_theirs" <?= !empty($settings['only_theirs']) ? 'checked' : '' ?>>
                        <label for="onlyTheirs"><?= $Translate->get_translate_module_phrase('module_page_results', '_AdminTheirAccess') ?></label>
                    </div>
                    <div class="inputs-inline">
                        <input type="checkbox" id="allInOneResult" class="switch" name="allInOneReport" <?= !empty($settings['allInOneReport']) ? 'checked' : '' ?>>
                        <label for="allInOneResult"><?= $Translate->get_translate_module_phrase('module_page_results', '_AllInOneReport') ?></label>
                    </div>
                    <div class="inputs-inline">
                        <label for="hours"><?= $Translate->get_translate_module_phrase('module_page_results', '_normHours') ?></label>
                        <input id="hours" type="text" value="<?= $settings['time'] ?? 0 ?>" name="time" placeholder="7">
                    </div>
                    <div class="inputs-inline">
                        <input type="checkbox" id="addWarn" class="switch" name="add_warn" <?= !empty($settings['add_warn']) ? 'checked' : '' ?>>
                        <label for="addWarn"><?= $Translate->get_translate_module_phrase('module_page_results', '_warnIfNotPlayed') ?></label>
                    </div>
                    <div class="inputs-inline">
                        <label for="warnTime"><?= $Translate->get_translate_module_phrase('module_page_results', '_warnTimeSeconds') ?></label>
                        <input id="warnTime" type="text" value="<?= $settings['warn_time'] ?? 0 ?>" name="warn_time" placeholder="168">
                    </div>
                    <button class="width-100"><?= $Translate->get_translate_phrase('_saveSettings') ?></button>
                </form>
                <form class="card-container" id="ManualGenerationForm">
                    <hr>
                    <div class="reason__info-noty"><?= $Translate->get_translate_module_phrase('module_page_results', '_ManualGeneration') ?></div>
                    <div class="inputs-inline">
                        <label for="handGenerate"><?= $Translate->get_translate_module_phrase('module_page_results', '_SpecifyWeeks') ?></label>
                        <input id="handGenerate" type="text" name="count_weeks" placeholder="">
                    </div>
                    <button class="active width-100"><?= $Translate->get_translate_module_phrase('module_page_results', '_Generate') ?></button>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="badge">
                        <?= $Translate->get_translate_module_phrase('module_page_results', '_serverList') ?>
                    </div>
                </div>
                <div class="card-container">
                    <form id="AddServerForm">
                        <div class="flex-inline">
                            <div class="inputs-inline">
                                <label for="serverName"><?= $Translate->get_translate_module_phrase('module_page_results', '_serverName') ?></label>
                                <input id="serverName" type="text" value="" name="name" placeholder="My server #1">
                            </div>
                            <div class="inputs-inline">
                                <label for="serverId"><?= $Translate->get_translate_module_phrase('module_page_results', '_serverId') ?></label>
                                <input id="serverId" type="text" value="" name="sid" placeholder="1">
                            </div>
                            <div class="inputs-inline">
                                <label for="resultId"><?= $Translate->get_translate_module_phrase('module_page_results', '_reportsId') ?></label>
                                <input id="resultId" type="text" value="" name="rid" placeholder="1">
                            </div>
                        </div>
                        <button class="width-100"><?= $Translate->get_translate_module_phrase('module_page_results', '_addServer') ?></button>
                    </form>
                    <hr>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?= $Translate->get_translate_module_phrase('module_page_results', '_serverName') ?></th>
                                    <th>SID</th>
                                    <th>RID</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($Results->getServers() as $server): ?>
                                    <tr>
                                        <td><?= $server['name'] ?></td>
                                        <td><?= $server['sid'] ?></td>
                                        <td><?= $server['rid'] ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="button-delete delete-server-btn icon_btn_transparent" data-id="<?= $server['id'] ?>">
                                                    <svg>
                                                        <use href="/resources/img/sprite.svg#trash"></use>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="badge">
                        <?= $Translate->get_translate_module_phrase('module_page_results', '_awards') ?>
                    </div>
                </div>
                <div class="card-container">
                    <button data-openmodal="addAward" class="width-100"><svg>
                            <use href="/resources/img/sprite.svg#plus"></use>
                        </svg><?= $Translate->get_translate_module_phrase('module_page_results', '_addAward') ?></button>
                    <hr>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?= $Translate->get_translate_module_phrase('module_page_results', '_time') ?></th>
                                    <th><?= $Translate->get_translate_module_phrase('module_page_results', '_award') ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($Results->getAwards() as $award): ?>
                                    <tr>
                                        <td><?= $award['time'] ?> <?= $Translate->get_translate_phrase('_timerHours') ?></td>
                                        <td><?= $award['amount'] ?> <?= $General->currency ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="edit-award-btn" data-id="<?= $award['id'] ?>"><?= $Translate->get_translate_phrase('_Change') ?></button>
                                                <button class="button-delete delete-award-btn" data-id="<?= $award['id'] ?>"><?= $Translate->get_translate_phrase('_Delete_Action') ?></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="tabpanel-2" role="tabpanel" tabindex="0" aria-labelledby="tab-2" class="is-hidden">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="badge">
                        <?= $Translate->get_translate_module_phrase('module_page_results', '_accesses') ?>
                    </div>
                </div>
                <div class="card-container">
                    <form id="AddAccessForm">
                        <div class="inputs-inline">
                            <label for="accessSteam">STEAMID</label>
                            <input id="accessSteam" type="text" value="" name="steamid" placeholder="">
                        </div>
                        <fieldset style="gap: .3rem;">
                            <legend><?= $Translate->get_translate_module_phrase('module_page_results', '_accesses') ?></legend>
                            <div class="inputs-inline">
                                <input type="checkbox" id="AwardWarnsAccess" class="switch" name="awardwarns">
                                <label for="AwardWarnsAccess"><?= $Translate->get_translate_module_phrase('module_page_results', '_AwardWarnsAccess') ?></label>
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="resultsAccess" class="switch" name="results">
                                <label for="resultsAccess"><?= $Translate->get_translate_module_phrase('module_page_results', '_resultsAccess') ?></label>
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="warnsAccess" class="switch" name="warns">
                                <label for="warnsAccess"><?= $Translate->get_translate_module_phrase('module_page_results', '_warnsAccess') ?></label>
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="adminsAccess" class="switch" name="admins">
                                <label for="adminsAccess"><?= $Translate->get_translate_module_phrase('module_page_results', '_adminsAccess') ?></label>
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="FullAccess" class="switch" name="full">
                                <label for="FullAccess"><?= $Translate->get_translate_module_phrase('module_page_results', '_fullAccess') ?></label>
                            </div>
                        </fieldset>
                        <button class="width-100"><?= $Translate->get_translate_module_phrase('module_page_results', '_addAccess') ?></button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="badge">
                        <?= $Translate->get_translate_module_phrase('module_page_results', '_accesses') ?>
                    </div>
                </div>
                <div class="card-container">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ник</th>
                                    <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_results', '_AwardWarnsAccess') ?>" data-tippy-placement="top"><svg>
                                            <use href="/resources/img/sprite.svg#warning"></use>
                                        </svg></th>
                                    <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_results', '_resultsAccess') ?>" data-tippy-placement="top"><svg>
                                            <use href="/resources/img/sprite.svg#bolt"></use>
                                        </svg></th>
                                    <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_results', '_warnsAccess') ?>" data-tippy-placement="top"><svg>
                                            <use href="/resources/img/sprite.svg#list-info"></use>
                                        </svg></th>
                                    <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_results', '_adminsAccess') ?>" data-tippy-placement="top"><svg>
                                            <use href="/resources/img/sprite.svg#policeman"></use>
                                        </svg></th>
                                    <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_results', '_fullAccess') ?>" data-tippy-placement="top"><svg>
                                            <use href="/resources/img/sprite.svg#gear"></use>
                                        </svg></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($Results->getAccesses() as $access): ?>
                                    <tr>
                                        <td>
                                            <?= $General->get_js_relevance_avatar($access['steamid']) ?>
                                            <a href="/profiles/<?= $access['steamid'] ?>/?search=1" id="name" nameid="<?= $access['steamid'] ?>">
                                                <?= $General->checkName($access['steamid']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <svg>
                                                <use href="/resources/img/sprite.svg#<?= $access['awardwarns'] ? 'check' : 'x' ?>"></use>
                                            </svg>
                                        </td>
                                        <td>
                                            <svg>
                                                <use href="/resources/img/sprite.svg#<?= $access['results'] ? 'check' : 'x' ?>"></use>
                                            </svg>
                                        </td>
                                        <td>
                                            <svg>
                                                <use href="/resources/img/sprite.svg#<?= $access['warns'] ? 'check' : 'x' ?>"></use>
                                            </svg>
                                        </td>
                                        <td>
                                            <svg>
                                                <use href="/resources/img/sprite.svg#<?= $access['admins'] ? 'check' : 'x' ?>"></use>
                                            </svg>
                                        </td>
                                        <td>
                                            <svg>
                                                <use href="/resources/img/sprite.svg#<?= $access['full'] ? 'check' : 'x' ?>"></use>
                                            </svg>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="button-delete delete-access-btn icon_btn_transparent" data-id="<?= $access['id'] ?>">
                                                    <svg>
                                                        <use href="/resources/img/sprite.svg#trash"></use>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="tabpanel-3" role="tabpanel" tabindex="0" aria-labelledby="tab-3" class="is-hidden">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="badge">
                        <?= $Translate->get_translate_module_phrase('module_page_results', '_logSettings') ?>
                    </div>
                </div>
                <div class="card-container">
                    <form id="DiscordForm">
                        <div class="inputs-inline">
                            <input type="checkbox" id="dsEnable" class="switch" name="enable_webhook" <?= !empty($settings['enable_webhook']) ? 'checked' : '' ?>>
                            <label for="dsEnable"><?= $Translate->get_translate_module_phrase('module_page_results', '_SendHooks') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <label for="dsHook"><?= $Translate->get_translate_module_phrase('module_page_results', '_DiscordWebhook') ?></label>
                            <div class="number">
                                <input type="password" value="<?= htmlspecialchars($settings['webhook_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" name="webhook_url" id="dsHook" placeholder="https://discord.com/api/webhooks/123456789012345678/abcdefghijklmnopqrstuvwxyz">
                                <div class="eye-password" id="show_pass">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#eye"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="inputs-inline">
                            <label for="imageUrl"><?= $Translate->get_translate_module_phrase('module_page_results', '_imageLink') ?></label>
                            <input id="imageUrl" type="text" value="<?= htmlspecialchars($settings['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" name="image_url" placeholder="https://example.com/image.png">
                        </div>
                        <button class="width-100"><?= $Translate->get_translate_module_phrase('module_page_results', '_saveChanges') ?></button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="badge">
                        <?= $Translate->get_translate_module_phrase('module_page_results', '_Logs') ?>
                    </div>
                    <div class="adaptive-select-wrapper abs-select">
                        <ul class="adaptive-select__dropdown-list" id="selectDate">
                            <?php foreach ($Results->getDatesWithLogs() as $index => $date): ?>
                                <li>
                                    <label class="adaptive-select__label" for="logDate_<?= $index ?>">
                                        <div class="adaptive-select__label-text"><?= $date ?></div>
                                        <input class="hide-input" id="logDate_<?= $index ?>" type="radio" name="date" value="<?= $date ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="adaptive-select" open-select="selectDate">
                            <span class="adaptive-select__span_text"></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-container">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?= $Translate->get_translate_module_phrase('module_page_results', '_Date') ?></th>
                                    <th><?= $Translate->get_translate_module_phrase('module_page_results', '_Log') ?></th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody">

                            </tbody>
                        </table>
                    </div>
                    <div id="Pagination">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="popup_modal" id="addAward">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_results', '_addingAwards') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form id="AddAwardForm">
            <div class="inputs-inline">
                <label for="playTimeAdd"><?= $Translate->get_translate_module_phrase('module_page_results', '_playedTime') ?></label>
                <input id="playTimeAdd" type="text" value="" name="time" placeholder="">
            </div>
            <div class="inputs-inline">
                <label for="addBalance"><?= $Translate->get_translate_module_phrase('module_page_results', '_howMuchMoney') ?></label>
                <input id="addBalance" type="text" value="" name="amount" placeholder="">
            </div>
            <button class="width-100"><?= $Translate->get_translate_module_phrase('module_page_results', '_addAward') ?></button>
        </form>
    </div>
</div>

<div class="popup_modal" id="editAward">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_results', '_editingAward') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form id="editAwardForm">
            <div class="inputs-inline">
                <label for="playTimeEdit"><?= $Translate->get_translate_module_phrase('module_page_results', '_playedTime') ?></label>
                <input id="playTimeEdit" type="text" value="" name="time" placeholder="">
            </div>
            <div class="inputs-inline">
                <label for="editBalance"><?= $Translate->get_translate_module_phrase('module_page_results', '_howMuchMoney') ?></label>
                <input id="editBalance" type="text" value="" name="amount" placeholder="">
            </div>
            <button class="width-100"><?= $Translate->get_translate_module_phrase('module_page_results', '_editAward') ?></button>
        </form>
    </div>
</div>