<script src="/storage/assets/js/Sortable.min.js"></script>
<script src="/storage/assets/js/vendors/filepond/filepond.min.js"></script>
<script src="/storage/assets/js/vendors/filepond/filepondPlugins/FilePondPluginFileValidateSize.min.js"></script>
<script src="/storage/assets/js/vendors/filepond/filepondPlugins/FilePondPluginFileValidateType.min.js"></script>
<script src="/storage/assets/js/vendors/filepond/filepondPlugins/FilePondPluginImagePreview.min.js"></script>

<?php
$mods_data = $Mon->getMods();
$global_mods = $General->getMods();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_settingsMods') ?></div>
            </div>
            <div class="card-container">
                <div class="mods__container">
                    <div class="mods__wrapper-forms">
                        <div class="ex-min__create-buttons">
                            <div class="ex-mon__description"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_descModCreate') ?></div>
                            <button class="width-100" data-openmodal="ModModal"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_createModeCard') ?></button>
                            <button class="width-100" data-openmodal="submodModal"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_createSubMode') ?></button>
                        </div>
                        <hr>
                        <form class="mods__form" id="mods_settings_form">
                            <div class="inputs-inline">
                                <input type="checkbox" id="useMods" class="switch" name="mon_mods" <?php $Mon->Settings['enable_mods'] === 1 && print 'checked' ?>>
                                <label for="useMods"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_useMods') ?></label>
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="useEmpty" class="switch" name="mon_emptys" <?php $Mon->Settings['enable_emptys'] === 1 && print 'checked' ?>>
                                <label for="useEmpty"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_useEmptys') ?></label>
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="centerdMods" class="switch" name="mon_centered_mods" <?php (isset($Mon->Settings['centered_mods']) && $Mon->Settings['centered_mods'] === 1) && print 'checked' ?>>
                                <label for="centerdMods"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_centeredMods') ?></label>
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="serverCounter" class="switch" name="mon_server_counter" <?php (isset($Mon->Settings['server_counter']) && $Mon->Settings['server_counter'] === 1) && print 'checked' ?>>
                                <label for="serverCounter"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_serverCounter') ?></label>
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="modButton" class="switch" name="mon_button" <?php (isset($Mon->Settings['mon_button']) && $Mon->Settings['mon_button'] === 1) && print 'checked' ?>>
                                <label for="modButton"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_modButtonHide') ?></label>
                            </div>
                            <hr>
                            <label><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_modsInRow') ?></label>
                            <div class="inputs-inline mods__grid-radio">
                                <?php foreach ([6, 5, 4, 3, 2, 1] as $g): ?>
                                    <div>
                                        <input type="radio" id="grid-<?= $g ?>" name="mods_grid" value="<?= $g ?>" <?php if ((string)$Mon->Settings['grid_counts'] === (string)$g) echo 'checked'; ?>>
                                        <label for="grid-<?= $g ?>"><?= $g ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="width-100 active" type="submit"><?= $Translate->get_translate_phrase('_saveSettings') ?></button>
                        </form>
                    </div>
                    <div class="mods__table">
                        <div class="ex-mon__description" style="margin-bottom: .5rem"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_howUse') ?></div>
                        <div class="mods__tree" id="mods-tree">
                            <?php foreach ($mods_data as $mode): ?>
                                <div class="list-group-item nested-1 mods__mode" data-type="mode" data-id="<?= htmlspecialchars($mode['name']) ?>" data-name="<?= htmlspecialchars($mode['name']) ?>">
                                    <span class="item-title mods__row">
                                        <svg class="mods__handle">
                                            <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                        </svg>
                                        <?= htmlspecialchars($mode['name']) ?>
                                        <div class="ex-mon__action">
                                            <button class="button-icon mods__edit-mode" data-openmodal="editModModal" data-id="<?= htmlspecialchars($mode['name']) ?>" data-name="<?= htmlspecialchars($mode['name']) ?>" data-image_1="<?= htmlspecialchars($mode['image_1']) ?>" data-image_2="<?= htmlspecialchars($mode['image_2']) ?>" data-video="<?= htmlspecialchars($mode['video'] ?? '') ?>" data-description="<?= htmlspecialchars($mode['description'] ?? '') ?>" type="button">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#edit-pen"></use>
                                                </svg>
                                            </button>
                                            <button class="button-icon button-delete mods__del-mode" data-id="<?= htmlspecialchars($mode['name']) ?>" type="button">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#trash"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </span>
                                    <?php foreach (($mode['submods'] ?? []) as $sm): ?>
                                        <div class="list-group nested-sort mods__submod" data-type="submod" data-id="<?= htmlspecialchars($sm['id']) ?>" data-mode="<?= htmlspecialchars($mode['name']) ?>">
                                            <span class="item-title mods__row">
                                                <svg class="mods__handle">
                                                    <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                                </svg>
                                                <?= htmlspecialchars($sm['title']) ?>
                                                <div class="ex-mon__action">
                                                    <button class="button-icon mods__edit-submod" data-openmodal="editsubmodModal" data-id="<?= htmlspecialchars($sm['id']) ?>" data-title="<?= htmlspecialchars($sm['title']) ?>" type="button">
                                                        <svg>
                                                            <use href="/resources/img/sprite.svg#edit-pen"></use>
                                                        </svg>
                                                    </button>
                                                    <button class="button-icon button-delete mods__del-submod" data-id="<?= htmlspecialchars($sm['id']) ?>" type="button">
                                                        <svg>
                                                            <use href="/resources/img/sprite.svg#trash"></use>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </span>
                                            <?php foreach (($sm['servers'] ?? []) as $sid): ?>
                                                <div class="list-group-item nested-2 item-server mods__server" data-type="server" data-id="<?= htmlspecialchars($sid) ?>" data-mode="<?= htmlspecialchars($mode['name']) ?>">
                                                    <svg class="mods__handle">
                                                        <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                                    </svg>
                                                    <?= htmlspecialchars($Mon->getServerName($sid)) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php foreach ($Mon->getRootServersForMode($mode) as $srv): ?>
                                        <div class="list-group-item nested-2 item-server mods__server" data-type="server" data-id="<?= htmlspecialchars($srv['id']) ?>" data-mode="<?= htmlspecialchars($mode['name']) ?>">
                                            <svg class="mods__handle">
                                                <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                            </svg>
                                            <?= htmlspecialchars($srv['name']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($mods_data)): ?>
                                <div class="no-data"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_noMods') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="popup_modal" id="ModModal">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_creatingMod') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div>
            <form class="mods__form" id="mods__form" enctype="multipart/form-data">
                <div class="inputs-inline">
                    <label for="option-server_mod-select">
                        <svg data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_allMods') ?>" data-tippy-placement="top">
                            <use href="/resources/img/sprite.svg#question"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_selectMod') ?>
                    </label>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="option-server_mod-select">
                            <?php foreach ($global_mods as $mod_code => $mod_value): ?>
                                <li>
                                    <label class="adaptive-select__label" for="for_<?= $mod_code ?>">
                                        <div class="adaptive-select__label-text"><?= $mod_code ?></div>
                                        <input class="hide-input" id="for_<?= $mod_code ?>" type="radio" value="<?= $mod_code ?>" name="mod_name" data-desc-key="<?= htmlspecialchars($mod_value) ?>" <?php if ($mod_code === array_key_first($global_mods)) echo 'checked'; ?>>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="adaptive-select" open-select="option-server_mod-select">
                            <span class="adaptive-select__span_text"></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="inputs-inline">
                    <label for="modDescription"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_modDescription') ?></label>
                    <textarea id="modDescription" value="" name="mod-description" placeholder=""></textarea>
                </div>
                <hr>
                <label><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_backImage') ?></label>
                <input type="file" class="filepond mods-img-pond" name="mod_image_1" accept="image/*">
                <hr>
                <label><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_frontImage') ?></label>
                <input type="file" class="filepond mods-img-pond" name="mod_image_2" accept="image/*">
                <hr>
                <label><?= $Translate->get_translate_module_phrase('module_page_mon_settings', 'attachVideo') ?></label>
                <input type="file" class="filepond mods-video-pond" name="mod_video" accept="video/*">
                <hr>
                <button class="width-100 active" type="submit"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_createMod') ?></button>
            </form>
        </div>
    </div>
</div>

<div class="popup_modal" id="editModModal">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_editMode') ?: 'Редактирование режима' ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div class="overflow-modal">
            <form class="mods__form" id="edit_mode_form" enctype="multipart/form-data">
                <input type="hidden" name="mode_id" id="edit_mode_id">
                <input type="hidden" name="mode_edit" value="1">
                <div class="inputs-inline">
                    <label><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_selectMod') ?></label>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="edit-mode-select">
                            <?php foreach ($global_mods as $mod_code => $mod_value): ?>
                                <li>
                                    <label class="adaptive-select__label" for="edit_for_<?= $mod_code ?>">
                                        <div class="adaptive-select__label-text"><?= $mod_code ?></div>
                                        <input class="hide-input" id="edit_for_<?= $mod_code ?>" type="radio" value="<?= $mod_code ?>" name="mod_name" data-desc-key="<?= htmlspecialchars($mod_value) ?>">
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="adaptive-select" open-select="edit-mode-select">
                            <span class="adaptive-select__span_text"></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="inputs-inline">
                    <label for="modDescription"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_modDescription') ?></label>
                    <textarea id="modDescription" value="" name="description" placeholder=""></textarea>
                </div>
                <label><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_backImage') ?></label>
                <input type="file" class="filepond mods-img-pond" name="mod_image_1" accept="image/*" data-edit-pond="image_1">
                <label><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_frontImage') ?></label>
                <input type="file" class="filepond mods-img-pond" name="mod_image_2" accept="image/*" data-edit-pond="image_2">
                <label><?= $Translate->get_translate_module_phrase('module_page_mon_settings', 'attachVideo') ?></label>
                <input type="file" class="filepond mods-video-pond" name="mod_video" accept="video/*" data-edit-pond="video">
                <button class="width-100 active" type="submit"><?= $Translate->get_translate_phrase('_saveSettings') ?></button>
            </form>
        </div>
    </div>
</div>

<div class="popup_modal" id="submodModal">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_creatingSubMod') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div>
            <form class="submods__form" id="submods__form">
                <input type="hidden" name="submod_add" value="1">
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="subMod-select">
                        <?php foreach ($mods_data as $mode): ?>
                            <li>
                                <label class="adaptive-select__label" for="Mode-<?= htmlspecialchars($mode['name']) ?>">
                                    <div class="adaptive-select__label-text"><?= htmlspecialchars($mode['name']) ?></div>
                                    <input class="hide-input" id="Mode-<?= htmlspecialchars($mode['name']) ?>" type="radio" name="mode_id" value="<?= htmlspecialchars($mode['name']) ?>">
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="subMod-select">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#list"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_chooseMod') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="inputs-inline">
                    <label for="subModTitle"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_subModTitle') ?></label>
                    <input id="subModTitle" type="text" name="title" placeholder="">
                </div>
                <hr>
                <button class="width-100 active" type="submit"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_createSubMode') ?></button>
            </form>
        </div>
    </div>
</div>

<div class="popup_modal" id="editsubmodModal">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_editSubMod') ?: 'Редактирование подрежима' ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div>
            <form class="submods__form" id="edit_submod_form">
                <input type="hidden" name="submod_edit" value="1">
                <input type="hidden" name="submod_id" id="edit_submod_id">
                <div class="inputs-inline">
                    <label for="editSubModTitle"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_subModTitle') ?></label>
                    <input id="editSubModTitle" type="text" name="title">
                </div>
                <hr>
                <button class="width-100 active" type="submit"><?= $Translate->get_translate_phrase('_saveSettings') ?></button>
            </form>
        </div>
    </div>
</div>