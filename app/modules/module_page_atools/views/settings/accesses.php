<div class="at__settings-content">
    <h3 class="at__settings-title"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_settingsAccesses') ?></h3>
    <div class="at__sections-wrapper">
        <div class="at__settings-adding">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_creatingGroup') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_createReadyGroups') ?>
            </div>
            <?php foreach ($General->get_arr_languages() as $language): ?>
                <div class="inputs-inline">
                    <label for="groupName<?= $language ?>"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_groupName') ?>: <?= mb_strtolower($Translate->get_translate_phrase('_' . $language), 'UTF-8') ?> <?= mb_strtolower($Translate->get_translate_module_phrase('module_page_adminpanel', '_Language'), 'UTF-8') ?></label>
                    <input type="text" id="groupName<?= $language ?>" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_groupNamePlaceholder') ?>">
                </div>
            <?php endforeach; ?>
            <div class="adaptive-select-wrapper" data-no-text>
                <ul class="adaptive-select__dropdown-list" id="groupFlags">
                    <div class="inputs-inline">
                        <svg>
                            <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                        </svg>
                        <input id="searchAdminFlagsAccess" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findFlags') ?>" autocomplete="off">
                    </div>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-0">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchAdmins') ?></div>
                            <input class="hide-input" id="groupFlag-0" value="admins.view" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-1">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingAdmins') ?></div>
                            <input class="hide-input" id="groupFlag-1" value="admins.create" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-2">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingAdmins') ?></div>
                            <input class="hide-input" id="groupFlag-2" value="admins.delete" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-3">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingAdmins') ?></div>
                            <input class="hide-input" id="groupFlag-3" value="admins.update" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-4">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingWarns') ?></div>
                            <input class="hide-input" id="groupFlag-4" value="admins.warn.give" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-5">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_removingWarns') ?></div>
                            <input class="hide-input" id="groupFlag-5" value="admins.warn.remove" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-6">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingWarns') ?></div>
                            <input class="hide-input" id="groupFlag-6" value="admins.warn.delete" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-7">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingWarns') ?></div>
                            <input class="hide-input" id="groupFlag-7" value="admins.warn.update" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-8">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchPunishments') ?></div>
                            <input class="hide-input" id="groupFlag-8" value="punishments.view" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-9">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingBans') ?></div>
                            <input class="hide-input" id="groupFlag-9" value="bans.create" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-10">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_removingBans') ?></div>
                            <input class="hide-input" id="groupFlag-10" value="bans.unban" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-11">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingBans') ?></div>
                            <input class="hide-input" id="groupFlag-11" value="bans.delete" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-12">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingBans') ?></div>
                            <input class="hide-input" id="groupFlag-12" value="bans.update" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-13">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingMutes') ?></div>
                            <input class="hide-input" id="groupFlag-13" value="mutes.create" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-14">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_removingMutes') ?></div>
                            <input class="hide-input" id="groupFlag-14" value="mutes.unmute" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-15">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingMutes') ?></div>
                            <input class="hide-input" id="groupFlag-15" value="mutes.delete" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-16">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingMutes') ?></div>
                            <input class="hide-input" id="groupFlag-16" value="mutes.update" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-17">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchChecks') ?></div>
                            <input class="hide-input" id="groupFlag-17" value="checks.view" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-18">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingChecks') ?></div>
                            <input class="hide-input" id="groupFlag-18" value="checks.delete" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-19">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchFinances') ?></div>
                            <input class="hide-input" id="groupFlag-19" value="finances.view" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-20">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingFinances') ?></div>
                            <input class="hide-input" id="groupFlag-20" value="finances.update" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-21">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_resettingFinances') ?></div>
                            <input class="hide-input" id="groupFlag-21" value="finances.reset" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-22">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchVIPs') ?></div>
                            <input class="hide-input" id="groupFlag-22" value="privileges.view" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-23">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingVIPs') ?></div>
                            <input class="hide-input" id="groupFlag-23" value="privileges.create" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-24">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingVIPs') ?></div>
                            <input class="hide-input" id="groupFlag-24" value="privileges.delete" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-25">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingVIPs') ?></div>
                            <input class="hide-input" id="groupFlag-25" value="privileges.update" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-26">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchCredits') ?></div>
                            <input class="hide-input" id="groupFlag-26" value="credits.view" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-27">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingCredits') ?></div>
                            <input class="hide-input" id="groupFlag-27" value="credits.update" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-28">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_viperResettingCredits') ?></div>
                            <input class="hide-input" id="groupFlag-28" value="credits.reset" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-29">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchExperience') ?></div>
                            <input class="hide-input" id="groupFlag-29" value="experience.view" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-30">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingExperience') ?></div>
                            <input class="hide-input" id="groupFlag-30" value="experience.update" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-31">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_viperResettingExperience') ?></div>
                            <input class="hide-input" id="groupFlag-31" value="experience.reset" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-32">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchLogs') ?></div>
                            <input class="hide-input" id="groupFlag-32" value="logs.view" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="groupFlag-33">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingLogs') ?></div>
                            <input class="hide-input" id="groupFlag-33" value="logs.delete" type="checkbox" name="group-flag-add">
                        </label>
                    </li>
                </ul>
                <div class="adaptive-select" open-select="groupFlags">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#lock"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_flagsSelect') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <button class="at__button-setting active width-100" id="addAtoolsGroup"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_createGrpup') ?></button>
        </div>
        <div class="at__settings-section">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_groupName') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_flagsAccess') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="accessGroupsTableBody">
                        <?php foreach ($RendersController->renderGroups()['data'] as $group): ?>
                            <tr data-group-id="<?= $group['id'] ?>" data-group="<?= htmlspecialchars(json_encode(['name' => $group['name_raw'], 'permissions' => $group['permissions']], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                                <td><?= $group['name'] ?></td>
                                <td>
                                    <?php $permissionLabels = [
                                            'admins.view' => '_at_watchAdmins',
                                            'admins.create' => '_at_addingAdmins',
                                            'admins.delete' => '_at_deletingAdmins',
                                            'admins.update' => '_at_changingAdmins',
                                            'admins.warn.give' => '_at_addingWarns',
                                            'admins.warn.remove' => '_at_removingWarns',
                                            'admins.warn.delete' => '_at_deletingWarns',
                                            'admins.warn.update' => '_at_changingWarns',
                                            'punishments.view' => '_at_watchPunishments',
                                            'bans.create' => '_at_addingBans',
                                            'bans.unban' => '_at_removingBans',
                                            'bans.delete' => '_at_deletingBans',
                                            'bans.update' => '_at_changingBans',
                                            'mutes.create' => '_at_addingMutes',
                                            'mutes.unmute' => '_at_removingMutes',
                                            'mutes.delete' => '_at_deletingMutes',
                                            'mutes.update' => '_at_changingMutes',
                                            'checks.view' => '_at_watchChecks',
                                            'checks.delete' => '_at_deletingChecks',
                                            'finances.view' => '_at_watchFinances',
                                            'finances.update' => '_at_changingFinances',
                                            'finances.reset' => '_at_resettingFinances',
                                            'privileges.view' => '_at_watchVIPs',
                                            'privileges.create' => '_at_addingVIPs',
                                            'privileges.delete' => '_at_deletingVIPs',
                                            'privileges.update' => '_at_changingVIPs',
                                            'credits.view' => '_at_watchCredits',
                                            'credits.update' => '_at_changingCredits',
                                            'credits.reset' => '_at_viperResettingCredits',
                                            'experience.view' => '_at_watchExperience',
                                            'experience.update' => '_at_changingExperience',
                                            'experience.reset' => '_at_viperResettingExperience',
                                            'logs.view' => '_at_watchLogs',
                                            'logs.delete' => '_at_deletingLogs',
                                        ];
                                        $flagsTooltip = [];
                                        foreach ($permissionLabels as $permission => $phraseKey) {
                                            if (in_array($permission, $group['permissions'], true)) {
                                                $flagsTooltip[] = $Translate->get_translate_module_phrase('module_page_atools', $phraseKey);
                                            }
                                        } ?>
                                    <span class="at_flags-counter" data-tippy-content="<?= implode('<br>', $flagsTooltip) ?>" data-tippy-placement="right" data-tippy-interactive="true">
                                        <?= count($group['permissions']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="button-icon at-btn-group-edit" data-openmodal="editGroupAtools" title="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_editGroup') ?>">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#edit-pen"></use>
                                            </svg>
                                        </button>
                                        <button type="button" class="button-icon button-delete at-btn-group-delete" title="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deleteGroup') ?>">
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

        <div class="popup_modal" id="editGroupAtools">
            <div class="popup_modal_content no-close no-scrollbar">
                <div class="popup_modal_head">
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingGroup') ?>
                    <span class="popup_modal_close">
                        <svg>
                            <use href="/resources/img/sprite.svg#x"></use>
                        </svg>
                    </span>
                </div>
                <div>
                    <div class="at__settings-editing">
                        <input type="hidden" id="editGroupId" value="">
                        <?php foreach ($General->get_arr_languages() as $language): ?>
                            <div class="inputs-inline">
                                <label for="groupName<?= $language ?>Edit"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_groupName') ?>: <?= mb_strtolower($Translate->get_translate_phrase('_' . $language), 'UTF-8') ?> <?= mb_strtolower($Translate->get_translate_module_phrase('module_page_adminpanel', '_Language'), 'UTF-8') ?></label>
                                <input type="text" id="groupName<?= $language ?>Edit" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_groupNamePlaceholder') ?>">
                            </div>
                        <?php endforeach; ?>
                        <div class="adaptive-select-wrapper" data-no-text>
                            <ul class="adaptive-select__dropdown-list" id="groupFlagsEdit">
                                <div class="inputs-inline">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                                    </svg>
                                    <input id="searchAdminFlagsAccess" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_findFlags') ?>" autocomplete="off">
                                </div>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-0">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchAdmins') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-0" value="admins.view" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-1">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingAdmins') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-1" value="admins.create" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-2">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingAdmins') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-2" value="admins.delete" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-3">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingAdmins') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-3" value="admins.update" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-4">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingWarns') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-4" value="admins.warn.give" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-5">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_removingWarns') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-5" value="admins.warn.remove" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-6">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingWarns') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-6" value="admins.warn.delete" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-7">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingWarns') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-7" value="admins.warn.update" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-8">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchPunishments') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-8" value="punishments.view" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-9">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingBans') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-9" value="bans.create" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-10">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_removingBans') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-10" value="bans.unban" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-11">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingBans') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-11" value="bans.delete" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-12">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingBans') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-12Edit" value="bans.update" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-13">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingMutes') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-13" value="mutes.create" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-14">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_removingMutes') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-14" value="mutes.unmute" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-15">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingMutes') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-15" value="mutes.delete" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-16">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingMutes') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-16" value="mutes.update" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-17">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchChecks') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-17" value="checks.view" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-18">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingChecks') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-18" value="checks.delete" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-19">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchFinances') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-19" value="finances.view" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-20">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingFinances') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-20" value="finances.update" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-21">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_resettingFinances') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-21" value="finances.reset" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-22">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchVIPs') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-22" value="privileges.view" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-23">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_addingVIPs') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-23" value="privileges.create" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-24">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingVIPs') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-24" value="privileges.delete" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-25">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingVIPs') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-25" value="privileges.update" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-26">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchCredits') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-26" value="credits.view" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-27">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingCredits') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-27" value="credits.update" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-28">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_viperResettingCredits') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-28" value="credits.reset" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-29">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchExperience') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-29" value="experience.view" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-30">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_changingExperience') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-30" value="experience.update" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-31">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_viperResettingExperience') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-31" value="experience.reset" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-32">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_watchLogs') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-32" value="logs.view" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="groupFlagEdit-33">
                                        <div class="adaptive-select__label-text">
                                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_deletingLogs') ?>
                                        </div>
                                        <input class="hide-input" id="groupFlagEdit-33" value="logs.delete" type="checkbox" name="group-flag-edit">
                                    </label>
                                </li>
                            </ul>
                            <div class="adaptive-select" open-select="groupFlagsEdit">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#lock"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text">
                                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_flagsSelect') ?>
                                </span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <button type="button" class="at__button-setting active width-100" id="saveAtoolsGroupEdit">
                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_editGroup') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>