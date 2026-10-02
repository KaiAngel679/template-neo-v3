<h2 class="tickets__h2"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_catSettings') ?></h2>
<div class="tickets__settings">
    <div class="tickets__settings-block">
        <div class="card height-100">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_tickets', '_addingSettings') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="tickets__settings-filters">
                    <button class="filter type-category active" data-type="1"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_default') ?></button>
                    <button class="filter type-category" data-type="2"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_strong') ?></button>
                </div>
                <hr>
                <div class="inputs-inline">
                    <label for="catName"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_catName') ?></label>
                    <input type="text" id="catName" required name="category">
                </div>
                <div id="type-render"></div>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="enableDescription">
                    <label for="enableDescription"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_addonDescriptionEnable') ?></label>
                </div>
                <div class="inputs-inline" style="display: none;">
                    <label for="catDescription"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_addonDescription') ?></label>
                    <textarea id="catDescription"></textarea>
                </div>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="responseTime">
                    <label for="responseTime"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_useResponseTime') ?></label>
                </div>
                <div class="inputs-inline" style="display: none;">
                    <label for="responseTimeInput"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_specifyTimeSec') ?></label>
                    <input type="text" id="responseTimeInput">
                </div>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="replayTime">
                    <label for="replayTime"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_restrictOpenTicket') ?></label>
                </div>
                <div class="inputs-inline" style="display: none;">
                    <label for="replayTimeInput"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_specifyTimeSec') ?></label>
                    <input type="text" id="replayTimeInput">
                </div>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="enableServers">
                    <label for="enableServers"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_enableServer') ?></label>
                </div>
                <div class="tickets__settings-show" id="selectedenableServers" style="display: none;">
                    <span class="tickets__settings-warning"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_ticketWarn') ?></span>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="servers-add">
                            <?php foreach ($ds->getServers() as $key): ?>
                                <li>
                                    <label class="adaptive-select__label" for="server-add-<?= $key['id'] ?>">
                                        <div class="adaptive-select__label-text"><?= action_text_clear($key['name_custom']) ?></div>
                                        <input class="hide-input" id="server-add-<?= $key['id'] ?>" value="<?= $key['id'] ?>" type="checkbox" name="servers-add">
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="adaptive-select" open-select="servers-add">
                            <span class="adaptive-select__fist-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#servers"></use>
                                </svg>
                            </span>
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_displayServers') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="bannedPLayer">
                    <label for="bannedPLayer"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_TimeForTicket') ?></label>
                </div>
                <div class="inputs-inline" style="display: none;">
                    <label for="playedTime"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_setTime') ?></label>
                    <input type="text" id="playedTime">
                </div>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="moneyTicket">
                    <label for="moneyTicket"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_giveMoney') ?></label>
                </div>
                <div class="inputs-inline" style="display: none;">
                    <label for="moneyCount"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_summMoney') ?></label>
                    <input type="text" id="moneyCount">
                </div>
                <div class="inputs-inline">
                    <label for="sortId"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_sortingID') ?></label>
                    <input type="number" id="sortId" required>
                </div>
                <button class="width-100" id="new-category"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_addCategoryAction') ?></button>
            </div>
        </div>
    </div>
    <div class="tickets__settings-block">
        <div class="card height-100">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_tickets', '_catList') ?>
                </div>
            </div>
            <div class="card-container height-100">
                <div class="tickets__categotys">
                    <?php foreach ($ds->getCategoriesFull() as $key): ?>
                        <div class="tickets__cat-edit">
                            <?= $key['title'] ?>
                            <div class="tickets__cat-buttons">
                                <button class="change-description" data-openmodal="editCategory" data-id="<?= $key['id'] ?>"><?= $Translate->get_translate_phrase('_Change') ?></button>
                                <button class="deleteDescription button-delete" data-id="<?= $key['id'] ?>"><?= $Translate->get_translate_phrase('_Delete_Action') ?></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="tickets__settings">
    <div class="tickets__settings-block">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_tickets', '_addingReadyResponses') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="inputs-inline">
                    <label for="buttonText"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_buttonText') ?></label>
                    <input type="text" id="buttonText" required>
                </div>
                <div class="inputs-inline">
                    <label for="answerText"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_replyText') ?></label>
                    <textarea id="answerText" required></textarea>
                </div>
                <button class="width-100" id="new-answer"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_replyAddButton') ?></button>
            </div>
        </div>
    </div>
    <div class="tickets__settings-block">
        <div class="card height-100">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_tickets', '_readyAnswers') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="tickets__ready-answer settings">
                    <div class="tickets__ready-buttons">
                        <?php foreach($jr->getCache('answers') as $id => $key) : ?>
                            <div class="tickets__ready-wrapper">
                                <div class="button settings-button" data-tippy-content="<?= action_text_clear($key['text_answer']) ?>" data-tippy-placement="top">
                                    <span class="settings-text"><?= action_text_clear($key['text_button']) ?></span>
                                    <div class="settings-icon">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#question"></use>
                                        </svg>
                                    </div>
                                </div>
                                <div class="tickets__ready-action-buttons">
                                    <button class="edit" data-openmodal="editAnswer" data-id="<?= $id ?>"><svg><use href="/resources/img/sprite.svg#edit-pen"></use></svg></button>
                                    <button class="button-delete delete" data-id="<?= $id ?>"><svg><use href="/resources/img/sprite.svg#trash"></use></svg></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="tickets__settings">
    <div class="tickets__settings-block">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_tickets', '_notifySettings') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="inputs-inline grid-server-wrapper">
                    <input type="checkbox" id="siteNoty" class="switch" <?= $settings['noty'] == 1 ? 'checked' : '' ?>>
                    <label for="siteNoty"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_notifyEnable') ?></label>
                </div>
                <hr>
                <div class="inputs-inline">
                    <label for="webhookUrl"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_webhookLink') ?></label>
                    <input type="text" id="webhookUrl" value="<?= action_text_clear($settings['url']) ?>">
                </div>
                <div class="inputs-inline">
                    <label for="webhookColor"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_webhookColor') ?></label>
                    <input type="text" id="webhookColor" value="<?= action_text_clear($settings['color']) ?>">
                </div>
                <div class="inputs-inline">
                    <label for="webhookImg"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_webhookImage') ?></label>
                    <input type="text" id="webhookImg" value="<?= action_text_clear($settings['img']) ?>">
                </div>
                <button class="width-100" id="save-settings"><?= $Translate->get_translate_phrase('_saveSettings') ?></button>
            </div>
        </div>
    </div>
    <div class="tickets__settings-block">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_tickets', '_addonSettings') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="inputs-inline grid-server-wrapper">
                    <input type="checkbox" id="slowModeEnable" class="switch" <?= $newSettings['slow'] == 1 ? 'checked' : '' ?>>
                    <label for="slowModeEnable"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_enableSlowMode') ?></label>
                </div>
                <hr>
                <div class="inputs-inline" style="display: none;">
                    <label for="slowModeInterval"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_intervalSlowMode') ?></label>
                    <input type="number" id="slowModeInterval" value="<?= action_text_clear($newSettings['slow_time']) ?>" style="margin-bottom: .3rem;">
                </div>
                <div class="inputs-inline grid-server-wrapper">
                    <input type="checkbox" id="autoCloseEnable" class="switch" <?= $newSettings['auto_close'] == 1 ? 'checked' : '' ?>>
                    <label for="autoCloseEnable"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_enableAutoClose') ?></label>
                </div>
                <hr>
                <div class="inputs-inline" style="display: none;">
                    <label for="autoCloseInterval"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_timeAutoClose') ?></label>
                    <input type="number" id="autoCloseInterval" value="<?= action_text_clear($newSettings['duration']) ?>">
                </div>
                <button class="width-100" id="save-settings-new"><?= $Translate->get_translate_phrase('_saveSettings') ?></button>
            </div>
        </div>
    </div>
</div>
<div class="popup_modal" id="editCategory" data-modal-type="2"></div>
<div class="popup_modal" id="editAnswer" data-modal-type="2"></div>