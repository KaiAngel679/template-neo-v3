<div class="at__settings-content">
    <h3 class="at__settings-title"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_settingsReasons') ?></h3>
    <div class="at__sections-wrapper">
        <div class="at__settings-adding">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_creatingReasons') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_ReasonsDeasription') ?>
            </div>
            <?php foreach ($General->get_arr_languages() as $language): ?>
                <div class="inputs-inline">
                    <label for="reasonName<?= $language ?>"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_ReasonTitle') ?>: <?= mb_strtolower($Translate->get_translate_phrase('_' . $language), 'UTF-8') ?> <?= mb_strtolower($Translate->get_translate_module_phrase('module_page_adminpanel', '_Language'), 'UTF-8') ?></label>
                    <input type="text" id="reasonName<?= $language ?>" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_ReasonTitlePlaceholder') ?>">
                </div>
            <?php endforeach; ?>
            <label for="reasonTypeLabel"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectPunishType') ?></label>
            <div class="adaptive-select-wrapper" data-change-icon>
                <ul class="adaptive-select__dropdown-list" id="reasonTypePunish">
                    <li>
                        <label class="adaptive-select__label" for="reasonBan">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#block"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_gameBlock') ?></div>
                            <input class="hide-input" id="reasonBan" value="ban" type="radio" name="reason-punish-type">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="reasonMute">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#mute"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_PunishMute') ?></div>
                            <input class="hide-input" id="reasonMute" value="mute" type="radio" name="reason-punish-type">
                        </label>
                    </li>
                </ul>
                <div class="adaptive-select" open-select="reasonTypePunish">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#list"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_PunishType') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <button type="button" class="at__button-setting active width-100" id="addAtoolsReason"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_createReason') ?></button>
        </div>
        <div class="at__settings-section">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_phrase('_Type') ?></th>
                            <th><?= $Translate->get_translate_phrase('_Reason') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="reasonsTableBody">
                        <?php foreach ($RendersController->renderReasons()['data'] as $reason): ?>
                            <tr data-reason-id="<?= $reason['id'] ?>" data-reason="<?= htmlspecialchars(json_encode(['name' => $reason['name_raw'], 'type' => $reason['type']], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                                <td>
                                    <svg>
                                        <use href="/resources/img/sprite.svg#<?= $reason['type'] === 'ban' ? 'block' : 'mute' ?>"></use>
                                    </svg>
                                </td>
                                <td>
                                    <div class="reason-wrapper">
                                        <span><?= $reason['name'] ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="button-icon at-btn-reason-edit" data-openmodal="editReasonAtools" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_editReason') ?>" data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#edit-pen"></use>
                                            </svg>
                                        </button>
                                        <button type="button" class="button-icon button-delete at-btn-reason-delete" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deleteReason') ?>" data-tippy-placement="top">
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
        <div class="popup_modal" id="editReasonAtools">
            <div class="popup_modal_content no-close no-scrollbar">
                <div class="popup_modal_head">
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingReason') ?>
                    <span class="popup_modal_close">
                        <svg>
                            <use href="/resources/img/sprite.svg#x"></use>
                        </svg>
                    </span>
                </div>
                <div>
                    <div class="at__settings-editing">
                        <input type="hidden" id="editReasonId" value="">
                        <?php foreach ($General->get_arr_languages() as $language): ?>
                            <div class="inputs-inline">
                                <label for="reasonName<?= $language ?>Edit"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_ReasonTitle') ?>: <?= mb_strtolower($Translate->get_translate_phrase('_' . $language), 'UTF-8') ?> <?= mb_strtolower($Translate->get_translate_module_phrase('module_page_adminpanel', '_Language'), 'UTF-8') ?></label>
                                <input type="text" id="reasonName<?= $language ?>Edit" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_ReasonTitlePlaceholder') ?>">
                            </div>
                        <?php endforeach; ?>
                        <label for="reasonTypeEditLabel"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectPunishType') ?></label>
                        <div class="adaptive-select-wrapper" data-change-icon>
                            <ul class="adaptive-select__dropdown-list" id="reasonTypePunishEdit">
                                <li>
                                    <label class="adaptive-select__label" for="reasonBanEdit">
                                        <span class="adaptive-select__icon">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#block"></use>
                                            </svg>
                                        </span>
                                        <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_gameBlock') ?></div>
                                        <input class="hide-input" id="reasonBanEdit" value="ban" type="radio" name="reason-punish-type-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="reasonMuteEdit">
                                        <span class="adaptive-select__icon">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#mute"></use>
                                            </svg>
                                        </span>
                                        <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_PunishMute') ?></div>
                                        <input class="hide-input" id="reasonMuteEdit" value="mute" type="radio" name="reason-punish-type-edit">
                                    </label>
                                </li>
                            </ul>
                            <div class="adaptive-select" open-select="reasonTypePunishEdit">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#list"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_PunishType') ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <button type="button" class="at__button-setting active width-100" id="saveAtoolsReasonEdit"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_editReason') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
