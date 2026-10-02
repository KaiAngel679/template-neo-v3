<div class="at__settings-content">
    <h3 class="at__settings-title"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_settingsVipGroups') ?></h3>
    <div class="at__sections-wrapper">
        <div class="at__settings-adding">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_creatingVipGroup') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_VipGroupsDescription') ?>
            </div>
            <div class="inputs-inline">
                <label for="vipGroupIniAdd"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroupIni') ?></label>
                <input id="vipGroupIniAdd" type="text" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroupIniPlaceholder') ?>">
            </div>
            <?php foreach ($General->get_arr_languages() as $language): ?>
                <div class="inputs-inline">
                    <label for="vipGroupDisplayAdd<?= $language ?>"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroupDisplay') ?>: <?= mb_strtolower($Translate->get_translate_phrase('_' . $language), 'UTF-8') ?> <?= mb_strtolower($Translate->get_translate_module_phrase('module_page_adminpanel', '_Language'), 'UTF-8') ?></label>
                    <input type="text" id="vipGroupDisplayAdd<?= $language ?>" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroupDisplayPlaceholder') ?>">
                </div>
            <?php endforeach; ?>
            <button type="button" class="at__button-setting active width-100" id="addAtoolsVipGroup"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_createVipGroup') ?></button>
        </div>
        <div class="at__settings-section">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroupIni') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroupDisplay') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="vipGroupsTableBody">
                        <?php foreach ($RendersController->renderVipGroups()['data'] as $group): ?>
                            <tr data-vip-group-id="<?= $group['id'] ?>" data-vip-group="<?= htmlspecialchars(json_encode(['ini' => $group['ini'], 'display' => $group['name_raw']], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                                <td><?= $group['ini'] ?></td>
                                <td><?= $group['name'] ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="button-icon at-btn-vip-group-edit" data-openmodal="editVipGroupAtools" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_editVipGroup') ?>" data-tippy-placement="top">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#edit-pen"></use>
                                            </svg>
                                        </button>
                                        <button type="button" class="button-icon button-delete at-btn-vip-group-delete" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deleteVipGroup') ?>" data-tippy-placement="top">
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

        <div class="popup_modal" id="editVipGroupAtools">
            <div class="popup_modal_content no-close no-scrollbar">
                <div class="popup_modal_head">
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingVipGroup') ?>
                    <span class="popup_modal_close">
                        <svg>
                            <use href="/resources/img/sprite.svg#x"></use>
                        </svg>
                    </span>
                </div>
                <div>
                    <div class="at__settings-editing">
                        <input type="hidden" id="editVipGroupId" value="">
                        <div class="inputs-inline">
                            <label for="vipGroupIniEdit"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroupIni') ?></label>
                            <input id="vipGroupIniEdit" type="text" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroupIniPlaceholder') ?>">
                        </div>
                        <?php foreach ($General->get_arr_languages() as $language): ?>
                            <div class="inputs-inline">
                                <label for="vipGroupDisplayEdit<?= $language ?>"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroupDisplay') ?>: <?= mb_strtolower($Translate->get_translate_phrase('_' . $language), 'UTF-8') ?> <?= mb_strtolower($Translate->get_translate_module_phrase('module_page_adminpanel', '_Language'), 'UTF-8') ?></label>
                                <input type="text" id="vipGroupDisplayEdit<?= $language ?>" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroupDisplayPlaceholder') ?>">
                            </div>
                        <?php endforeach; ?>
                        <button type="button" class="at__button-setting active width-100" id="saveAtoolsVipGroupEdit"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_editVipGroup') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>