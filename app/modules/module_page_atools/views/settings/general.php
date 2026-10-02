<div class="at__settings-content">
    <h3 class="at__settings-title"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_generalModuleSettings') ?></h3>
    <div class="at__sections-wrapper">
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_warns') ?></h4>
            <hr>
            <div class="inputs-inline at__fixed-checkbox">
                <input type="checkbox" id="removeAdminWarn" class="switch" <?= $FileController->get('settings')['auto_delete_admin_max_warns'] ? 'checked' : '' ?>>
                <label for="removeAdminWarn" class="at__fixed-label"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_removeAdminWithMaxWarns') ?></label>
            </div>
            <div class="inputs-inline at__mb0">
                <label for="maxWarnCount"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_maxWarnCount') ?></label>
                <div class="number" id="numberControl">
                    <button class="number-minus" type="button">-</button>
                    <input id="maxWarnCount" type="number" min="1" value="<?= $FileController->get('settings')['max_warns'] ?>" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_maxWarnCountPlaceholder') ?>">
                    <button class="number-plus" type="button">+</button>
                </div>
            </div>
        </div>
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_logs') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_enableDetailedLogs') ?>
            </div>
            <div class="inputs-inline">
                <input type="checkbox" id="debugLogs" class="switch" <?= $FileController->get('settings')['debug_logs'] ? 'checked' : '' ?>>
                <label for="debugLogs"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_detailedLogs') ?></label>
            </div>
        </div>
    </div>

    <div class="at__sections-wrapper">
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_TermTypeVIPs') ?></h4>
            <hr>
            <div class="inputs-inline at__fixed-checkbox">
                <input type="checkbox" id="hideTest" class="switch" <?= $FileController->get('settings')['hide_vip_test'] ? 'checked' : '' ?>>
                <label for="hideTest" class="at__fixed-label"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_hideTestGroup') ?></label>
            </div>
            <div class="inputs-inline">
                <label for="testName"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_testGroupName') ?></label>
                <input id="testName" type="text" value="<?= $FileController->get('settings')['vip_test_group'] ?>" placeholder="VIPTEST">
                </div>
        </div>
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_convenience') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_defaultAllServersHint') ?>
            </div>
            <div class="inputs-inline at__fixed-checkbox">
                <input type="checkbox" id="defaultAllServers" class="switch" <?= !empty($FileController->get('settings')['default_all_servers']) ? 'checked' : '' ?>>
                <label for="defaultAllServers" class="at__fixed-label"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_defaultAllServers') ?></label>
            </div>
        </div>
    </div>

    <div class="at__sections-wrapper">
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_integrations') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_blockdbDescription') ?>
            </div>
            <div class="inputs-inline">
                <div class="number">
                    <input id="blockdbApiKey" type="password" value="<?= $FileController->get('settings')['blockdb_api_key'] ?>" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_blockdbApiKeyPlaceholder') ?>" autocomplete="off">
                    <div class="eye-password" id="show_pass">
                        <svg>
                            <use href="/resources/img/sprite.svg#eye"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_migration') ?></h4>
            <hr>
            <?php if (file_exists(MODULES . 'module_page_managersystem/description.json')) : ?>
                <div class="at__settings-info-text">
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_msDetected') ?>
                </div>
                <button type="button" class="at__button-setting active" id="importMsSettings"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_importSettings') ?></button>
            <?php else : ?>
                <div class="at__settings-info-text">
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_msNotDetected') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($showWelcomeModal)) : ?>
<div class="popup_modal visible" id="newModule">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_welcomeTitle') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div>
            <div class="at__new-image">
                <img src="/app/modules/module_page_atools/assets/img/atools.webp" alt="">
            </div>
            <hr>
            <h2 class="at__new-h2"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_welcomeHeading') ?></h2>
            <div class="at__new-text">
                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_welcomeText1') ?>
            </div>
            <div class="at__new-text">
                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_welcomeText2') ?>
            </div>
            <div class="at__new-text-small">
                <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_welcomeText3') ?>
            </div>
            <hr>
            <button type="button" class="active width-100" id="dismissWelcomeModal"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_understood') ?></button>
        </div>
    </div>
</div>
<?php endif; ?>