<?php
$termTypeIcons = [
    'admins' => 'policeman',
    'punishments' => 'block',
    'vips' => 'diamond',
];
?>
<div class="at__settings-content">
    <h3 class="at__settings-title"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_settingsTerms') ?></h3>
    <div class="at__sections-wrapper">
        <div class="at__settings-adding">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_creatingTerms') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermsDescription') ?>
            </div>
            <?php foreach ($General->get_arr_languages() as $language): ?>
                <div class="inputs-inline">
                    <label for="termName<?= $language ?>"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTitle') ?>: <?= mb_strtolower($Translate->get_translate_phrase('_' . $language), 'UTF-8') ?> <?= mb_strtolower($Translate->get_translate_module_phrase('module_page_adminpanel', '_Language'), 'UTF-8') ?></label>
                    <input type="text" id="termName<?= $language ?>" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTitlePlaceholder') ?>">
                </div>
            <?php endforeach; ?>
            <div class="inputs-inline">
                <label for="termValueAdd"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermValue') ?></label>
                <input id="termValueAdd" type="number" min="0" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermValuePlaceholder') ?>">
            </div>
            <label for="termTypeLabel"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectTermType') ?></label>
            <div class="adaptive-select-wrapper" data-change-icon>
                <ul class="adaptive-select__dropdown-list" id="termType">
                    <li>
                        <label class="adaptive-select__label" for="termAdmins">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#policeman"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTypeAdmins') ?></div>
                            <input class="hide-input" id="termAdmins" value="admins" type="radio" name="term-type">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="termPunishments">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#block"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTypePunishments') ?></div>
                            <input class="hide-input" id="termPunishments" value="punishments" type="radio" name="term-type">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="termVIPs">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#diamond"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTypeVIPs') ?></div>
                            <input class="hide-input" id="termVIPs" value="vips" type="radio" name="term-type">
                        </label>
                    </li>
                </ul>
                <div class="adaptive-select" open-select="termType">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#list"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermType') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <button type="button" class="at__button-setting active width-100" id="addAtoolsTerm"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_createTerm') ?></button>
        </div>
        <div class="at__settings-section">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_phrase('_Type') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTitle') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_term') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="termsTableBody">
                        <?php foreach ($RendersController->renderTerms()['data'] as $term): ?>
                            <tr data-term-id="<?= $term['id'] ?>" data-term="<?= htmlspecialchars(json_encode(['name' => $term['name_raw'], 'type' => $term['type'], 'time' => $term['time']], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                                <td>
                                    <svg>
                                        <use href="/resources/img/sprite.svg#<?= $termTypeIcons[$term['type']] ?? 'list' ?>"></use>
                                    </svg>
                                </td>
                                <td><?= $term['name'] ?></td>
                                <td><?= $term['time'] ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="button-icon at-btn-term-edit" data-openmodal="editTermAtools" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_editTerm') ?>" data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#edit-pen"></use>
                                            </svg>
                                        </button>
                                        <button type="button" class="button-icon button-delete at-btn-term-delete" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deleteTerm') ?>" data-tippy-placement="top">
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

        <div class="popup_modal" id="editTermAtools">
            <div class="popup_modal_content no-close no-scrollbar">
                <div class="popup_modal_head">
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingTerm') ?>
                    <span class="popup_modal_close">
                        <svg>
                            <use href="/resources/img/sprite.svg#x"></use>
                        </svg>
                    </span>
                </div>
                <div>
                    <div class="at__settings-editing">
                        <input type="hidden" id="editTermId" value="">
                        <?php foreach ($General->get_arr_languages() as $language): ?>
                            <div class="inputs-inline">
                                <label for="termName<?= $language ?>Edit"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTitle') ?>: <?= mb_strtolower($Translate->get_translate_phrase('_' . $language), 'UTF-8') ?> <?= mb_strtolower($Translate->get_translate_module_phrase('module_page_adminpanel', '_Language'), 'UTF-8') ?></label>
                                <input type="text" id="termName<?= $language ?>Edit" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTitlePlaceholder') ?>">
                            </div>
                        <?php endforeach; ?>
                        <div class="inputs-inline">
                            <label for="termValueEdit"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermValue') ?></label>
                            <input id="termValueEdit" type="number" min="0" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermValuePlaceholder') ?>">
                        </div>
                        <label for="termTypeEditLabel"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectTermType') ?></label>
                        <div class="adaptive-select-wrapper" data-change-icon>
                            <ul class="adaptive-select__dropdown-list" id="termTypeEdit">
                                <li>
                                    <label class="adaptive-select__label" for="termAdminsEdit">
                                        <span class="adaptive-select__icon">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#policeman"></use>
                                            </svg>
                                        </span>
                                        <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTypeAdmins') ?></div>
                                        <input class="hide-input" id="termAdminsEdit" value="admins" type="radio" name="term-type-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="termPunishmentsEdit">
                                        <span class="adaptive-select__icon">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#block"></use>
                                            </svg>
                                        </span>
                                        <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTypePunishments') ?></div>
                                        <input class="hide-input" id="termPunishmentsEdit" value="punishments" type="radio" name="term-type-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="termVIPsEdit">
                                        <span class="adaptive-select__icon">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#diamond"></use>
                                            </svg>
                                        </span>
                                        <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTypeVIPs') ?></div>
                                        <input class="hide-input" id="termVIPsEdit" value="vips" type="radio" name="term-type-edit">
                                    </label>
                                </li>
                            </ul>
                            <div class="adaptive-select" open-select="termTypeEdit">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#list"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermType') ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <button type="button" class="at__button-setting active width-100" id="saveAtoolsTermEdit"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_editTerm') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
