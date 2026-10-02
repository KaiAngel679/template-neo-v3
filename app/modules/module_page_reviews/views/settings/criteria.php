<div class="at__settings-content">
    <h3 class="at__settings-title"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_criteriaTitle') ?></h3>
    <div class="at__sections-wrapper">
        <div class="at__settings-adding">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_creatingCriterion') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_criteriaHint') ?>
            </div>
            <?php foreach ($languages as $language): ?>
                <div class="inputs-inline">
                    <label for="rvCriterionNameAdd<?= action_text_clear((string) $language) ?>">
                        <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_criterionName') ?>:
                        <?= mb_strtolower($Translate->get_translate_phrase('_' . $language), 'UTF-8') ?>
                        <?= mb_strtolower($Translate->get_translate_module_phrase('module_page_adminpanel', '_Language'), 'UTF-8') ?>
                    </label>
                    <input
                        type="text"
                        id="rvCriterionNameAdd<?= action_text_clear((string) $language) ?>"
                        maxlength="64"
                        value=""
                        placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_criterionPlaceholder') ?>"
                        autocomplete="off"
                    >
                </div>
            <?php endforeach; ?>
            <button type="button" class="at__button-setting active width-100" id="rvCriterionAdd">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_createCriterion') ?>
            </button>
        </div>

        <div class="at__settings-section">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_criterionName') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="rvCriteriaTableBody">
                        <?php foreach ($criteria as $item): ?>
                            <tr data-criterion-id="<?= (int) $item['id'] ?>" data-criterion="<?= $item['payload'] ?>">
                                <td><?= action_text_clear((string) ($item['name'] ?? '')) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="button-icon rv-btn-criterion-edit" data-openmodal="editCriterionReviews" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_editCriterion') ?>" data-tippy-placement="top">
                                            <svg><use href="/resources/img/sprite.svg#edit-pen"></use></svg>
                                        </button>
                                        <button type="button" class="button-icon button-delete rv-btn-criterion-delete" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_deleteCriterion') ?>" data-tippy-placement="top">
                                            <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="popup_modal" id="editCriterionReviews">
            <div class="popup_modal_content no-close no-scrollbar">
                <div class="popup_modal_head">
                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_changingCriterion') ?>
                    <span class="popup_modal_close">
                        <svg><use href="/resources/img/sprite.svg#x"></use></svg>
                    </span>
                </div>
                <div>
                    <div class="at__settings-editing">
                        <input type="hidden" id="editCriterionId" value="">
                        <?php foreach ($languages as $language): ?>
                            <div class="inputs-inline">
                                <label for="rvCriterionNameEdit<?= action_text_clear((string) $language) ?>">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_criterionName') ?>:
                                    <?= mb_strtolower($Translate->get_translate_phrase('_' . $language), 'UTF-8') ?>
                                    <?= mb_strtolower($Translate->get_translate_module_phrase('module_page_adminpanel', '_Language'), 'UTF-8') ?>
                                </label>
                                <input
                                    type="text"
                                    id="rvCriterionNameEdit<?= action_text_clear((string) $language) ?>"
                                    maxlength="64"
                                    value=""
                                    placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_criterionPlaceholder') ?>"
                                    autocomplete="off"
                                >
                            </div>
                        <?php endforeach; ?>
                        <button type="button" class="at__button-setting active width-100" id="rvCriterionSaveEdit">
                            <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_editCriterion') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
