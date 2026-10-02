<?php
$isIksCs2 = empty($Db->db_data['AdminSystem']) && !empty($Db->db_data['IksAdminNew']);

$sbFlags = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'z'];
$iksFlags = ['b', 'c', 'g', 'k', 'm', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'z'];

$defaultGame = $DatabaseController->hasCs2AdminBackend() ? 'cs2' : 'csgo';

$flagLabel = static function (string $type, string $flag) use ($Translate, $isIksCs2): string {
    $flag = strtolower($flag);

    if ($type === 'csgo') {
        return $Translate->get_translate_module_phrase('module_page_atools', '_at_sbFlag_' . $flag);
    }

    if ($isIksCs2) {
        return $Translate->get_translate_module_phrase('module_page_atools', '_at_iksFlag_' . $flag);
    }

    return strtoupper($flag);
};
?>
<div class="at__settings-content">
    <h3 class="at__settings-title"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_settingsAdminGroups') ?></h3>
    <div class="at__sections-wrapper">
        <?php if (!$DatabaseController->hasCsgoAdminBackend() && !$DatabaseController->hasCs2AdminBackend()) : ?>
            <div class="at__settings-section">
                <div class="at__warning">
                    <ul class="at__list">
                        <?php if (empty($Db->db_data['SourceBans']) && (empty($Db->db_data['AdminSystem']) || empty($Db->db_data['IksAdminNew']))) : ?>
                            <li data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingAdminNeedConnects') ?>" data-tippy-placement="right">
                                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingAdminConnectAny') ?>
                                <svg>
                                    <use href="/resources/img/sprite.svg#info-circle"></use>
                                </svg>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        <?php else : ?>
            <div class="at__settings-adding">
                <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_creatingAdminGroup') ?></h4>
                <hr>
                <div class="at__settings-info-text">
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_AdminGroupsDescription') ?>
                </div>
                <div class="inputs-inline">
                    <label for="adminGroupNameAdd"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_adminGroupName') ?></label>
                    <input id="adminGroupNameAdd" type="text" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_adminGroupNamePlaceholder') ?>">
                </div>
                <div class="inputs-inline">
                    <label for="adminGroupImmunityAdd"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_immunity') ?></label>
                    <input id="adminGroupImmunityAdd" type="number" min="0" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_immunityPlaceholder') ?>">
                </div>
                <label for="adminGroupGameLabel"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectGame') ?></label>
                <div class="adaptive-select-wrapper" data-change-icon>
                    <ul class="adaptive-select__dropdown-list" id="adminGroupGame">
                        <?php if ($DatabaseController->hasCs2AdminBackend()) : ?>
                            <li>
                                <label class="adaptive-select__label" for="gameCS2">
                                    <span class="adaptive-select__icon">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#cs2"></use>
                                        </svg>
                                    </span>
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_gameCS2') ?></div>
                                    <input class="hide-input" id="gameCS2" value="cs2" type="radio" name="admin-group-game" <?= $defaultGame === 'cs2' ? 'checked' : '' ?>>
                                </label>
                            </li>
                        <?php endif; ?>
                        <?php if ($DatabaseController->hasCsgoAdminBackend()) : ?>
                            <li>
                                <label class="adaptive-select__label" for="gameCSGO">
                                    <span class="adaptive-select__icon">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#csgo"></use>
                                        </svg>
                                    </span>
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_gameCSGO') ?></div>
                                    <input class="hide-input" id="gameCSGO" value="csgo" type="radio" name="admin-group-game" <?= $defaultGame === 'csgo' ? 'checked' : '' ?>>
                                </label>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <div class="adaptive-select" open-select="adminGroupGame">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#list"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_game') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <label for="adminGroupFlagsLabel"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectFlags') ?></label>
                <div class="adaptive-select-wrapper" data-no-text>
                    <ul class="adaptive-select__dropdown-list" id="adminGroupFlags">
                        <div class="inputs-inline">
                            <svg>
                                <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                            </svg>
                            <input id="searchAdminGroupFlags" type="search" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findFlags') ?>" autocomplete="off">
                        </div>
                        <div class="at-admin-group-flags-set" data-game="cs2"<?= $defaultGame !== 'cs2' ? ' style="display:none"' : '' ?>>
                            <?php if ($isIksCs2) : ?>
                                <?php foreach ($iksFlags as $flag) : ?>
                                    <li>
                                        <label class="adaptive-select__label" for="adminGroupFlag-cs2-<?= $flag ?>">
                                            <div class="adaptive-select__label-text"><?= $flagLabel('cs2', $flag) ?></div>
                                            <input class="hide-input" id="adminGroupFlag-cs2-<?= $flag ?>" value="<?= $flag ?>" type="checkbox" name="admin-group-flag-add">
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <?php foreach (range('a', 'z') as $flag) : ?>
                                    <li>
                                        <label class="adaptive-select__label" for="adminGroupFlag-cs2-<?= $flag ?>">
                                            <div class="adaptive-select__label-text"><?= strtoupper($flag) ?></div>
                                            <input class="hide-input" id="adminGroupFlag-cs2-<?= $flag ?>" value="<?= $flag ?>" type="checkbox" name="admin-group-flag-add">
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="at-admin-group-flags-set" data-game="csgo"<?= $defaultGame !== 'csgo' ? ' style="display:none"' : '' ?>>
                            <?php foreach ($sbFlags as $flag) : ?>
                                <li>
                                    <label class="adaptive-select__label" for="adminGroupFlag-csgo-<?= $flag ?>">
                                        <div class="adaptive-select__label-text"><?= $flagLabel('csgo', $flag) ?></div>
                                        <input class="hide-input" id="adminGroupFlag-csgo-<?= $flag ?>" value="<?= $flag ?>" type="checkbox" name="admin-group-flag-add">
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </div>
                    </ul>
                    <div class="adaptive-select" open-select="adminGroupFlags">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#lock"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_flags') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <button type="button" class="at__button-setting active width-100" id="addAtoolsAdminGroup"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_createAdminGroup') ?></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($Db->db_data['SourceBans']) || !empty($Db->db_data['AdminSystem']) || !empty($Db->db_data['IksAdminNew'])) : ?>
            <div class="at__settings-section">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_adminGroupName') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_game') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_immunity') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_flags') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="adminGroupsTableBody">
                            <?php foreach ($DatabaseController->listAdminGroups()['data'] as $group) : ?>
                                <tr data-admin-server-group-id="<?= $group['id'] ?>" data-admin-server-group-type="<?= htmlspecialchars($group['type'], ENT_QUOTES, 'UTF-8') ?>" data-admin-server-group="<?= htmlspecialchars(json_encode(['type' => $group['type'], 'name' => $group['name'], 'immunity' => $group['immunity'], 'flags' => $group['flags']], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                                    <td><?= $group['name'] ?></td>
                                    <td><?= $Translate->get_translate_module_phrase('module_page_atools', $group['type'] == 'csgo' ? '_at_gameCSGO' : '_at_gameCS2') ?></td>
                                    <td><?= $group['immunity'] ?></td>
                                    <td>
                                        <?php
                                        $groupFlags = $group['flags'] != '' ? str_split($group['flags']) : [];
                                        ?>
                                        <?php if ($groupFlags !== []) : ?>
                                            <?php
                                            $flagsTooltip = array_map(
                                                static fn(string $flag): string => $flagLabel($group['type'], $flag),
                                                $groupFlags
                                            );
                                            ?>
                                            <span class="at_flags-counter" data-tippy-content="<?= htmlspecialchars(implode('<br>', $flagsTooltip), ENT_QUOTES, 'UTF-8') ?>" data-tippy-placement="right" data-tippy-interactive="true"><?= count($groupFlags) ?></span>
                                        <?php else : ?>
                                            <span class="at_flags-counter">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="button-icon at-btn-admin-server-group-edit" data-openmodal="editAdminGroupAtools" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_editAdminGroup') ?>" data-tippy-placement="top">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#edit-pen"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="button-icon button-delete at-btn-admin-server-group-delete" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deleteAdminGroup') ?>" data-tippy-placement="top">
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
        <?php endif; ?>
        <div class="popup_modal" id="editAdminGroupAtools">
            <div class="popup_modal_content no-close no-scrollbar">
                <div class="popup_modal_head">
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingAdminGroup') ?>
                    <span class="popup_modal_close">
                        <svg>
                            <use href="/resources/img/sprite.svg#x"></use>
                        </svg>
                    </span>
                </div>
                <div>
                    <div class="at__settings-editing">
                        <input type="hidden" id="editAdminGroupId" value="">
                        <input type="hidden" id="editAdminGroupType" value="">
                        <div class="inputs-inline">
                            <label for="adminGroupNameEdit"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_adminGroupName') ?></label>
                            <input id="adminGroupNameEdit" type="text" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_adminGroupNamePlaceholder') ?>">
                        </div>
                        <div class="inputs-inline">
                            <label for="adminGroupImmunityEdit"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_immunity') ?></label>
                            <input id="adminGroupImmunityEdit" type="number" min="0" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_immunityPlaceholder') ?>">
                        </div>
                        <label for="adminGroupFlagsEditLabel"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_selectFlags') ?></label>
                        <div class="adaptive-select-wrapper" data-no-text>
                            <ul class="adaptive-select__dropdown-list" id="adminGroupFlagsEdit">
                                <div class="inputs-inline">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                                    </svg>
                                    <input id="searchAdminGroupFlagsEdit" type="search" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findFlags') ?>" autocomplete="off">
                                </div>
                                <div class="at-admin-group-flags-set" data-game="cs2" style="display:none">
                                    <?php if ($isIksCs2) : ?>
                                        <?php foreach ($iksFlags as $flag) : ?>
                                            <li>
                                                <label class="adaptive-select__label" for="adminGroupFlagEdit-cs2-<?= $flag ?>">
                                                    <div class="adaptive-select__label-text"><?= $flagLabel('cs2', $flag) ?></div>
                                                    <input class="hide-input" id="adminGroupFlagEdit-cs2-<?= $flag ?>" value="<?= $flag ?>" type="checkbox" name="admin-group-flag-edit">
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <?php foreach (range('a', 'z') as $flag) : ?>
                                            <li>
                                                <label class="adaptive-select__label" for="adminGroupFlagEdit-cs2-<?= $flag ?>">
                                                    <div class="adaptive-select__label-text"><?= strtoupper($flag) ?></div>
                                                    <input class="hide-input" id="adminGroupFlagEdit-cs2-<?= $flag ?>" value="<?= $flag ?>" type="checkbox" name="admin-group-flag-edit">
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="at-admin-group-flags-set" data-game="csgo" style="display:none">
                                    <?php foreach ($sbFlags as $flag) : ?>
                                        <li>
                                            <label class="adaptive-select__label" for="adminGroupFlagEdit-csgo-<?= $flag ?>">
                                                <div class="adaptive-select__label-text"><?= $flagLabel('csgo', $flag) ?></div>
                                                <input class="hide-input" id="adminGroupFlagEdit-csgo-<?= $flag ?>" value="<?= $flag ?>" type="checkbox" name="admin-group-flag-edit">
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                </div>
                            </ul>
                            <div class="adaptive-select" open-select="adminGroupFlagsEdit">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#lock"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_flags') ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <button type="button" class="at__button-setting active width-100" id="saveAtoolsAdminGroupEdit"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_editAdminGroup') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>