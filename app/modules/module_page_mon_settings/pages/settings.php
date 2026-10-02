<?php if ($Mon->tableSearch()): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="mon__global-container">
                <form class="mon__left-containers" method="post" id="monSettingsForm">
                    <div class="mon__visual-container">
                        <h3>
                            <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_visual') ?>
                            <div class="mon-change-mode-wrapper">
                                <div class="mon-change-mode__button <?php $Mon->Settings['type'] === 1 && print 'active' ?>" data-type="1">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#list-table"></use>
                                    </svg>
                                    <span><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_table') ?></span>
                                </div>
                                <div class="mon-change-mode__button <?php $Mon->Settings['type'] === 0 && print 'active' ?>" data-type="0">
                                    <span><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_grid') ?></span>
                                    <svg>
                                        <use href="/resources/img/sprite.svg#grid-blocks"></use>
                                    </svg>
                                </div>
                                <input style="display: none;" type="checkbox" id="monType" name="mon_type" <?php $Mon->Settings['type'] === 1 && print 'checked' ?>>
                            </div>
                        </h3>
                        <div class="mon__visual-grid <?php $Mon->Settings['type'] === 1 && print 'disabled' ?>"
                            id="monVisualCard">
                            <div class="mon__visual-options">
                                <span><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_modeGrid') ?></span>
                                <div class="mon__visual-radio-wrapper">
                                    <input type="radio" id="fourServers" name="mon_server_card_count" value="4" <?php $Mon->Settings['server_card_count'] === "4" && print 'checked' ?>>
                                    <label for="fourServers">4 <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_countServers') ?></label>
                                </div>
                                <div class="mon__visual-radio-wrapper">
                                    <input type="radio" id="threeServers" name="mon_server_card_count" value="3" <?php $Mon->Settings['server_card_count'] === "3" && print 'checked' ?>>
                                    <label for="threeServers">3 <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_countServers') ?></label>
                                </div>
                                <div class="mon__visual-radio-wrapper">
                                    <input type="radio" id="twoServers" name="mon_server_card_count" value="2" <?php $Mon->Settings['server_card_count'] === "2" && print 'checked' ?>>
                                    <label for="twoServers">2 <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_countServers') ?></label>
                                </div>
                            </div>
                            <div class="mon__visual-example-wrapper">
                                <div class="mon__visual-example" id="example-1">1</div>
                                <div class="mon__visual-example" id="example-2">2</div>
                                <div class="mon__visual-example" id="example-3">3</div>
                                <div class="mon__visual-example" id="example-4">4</div>
                            </div>
                        </div>
                        <div class="mon__visual-table <?php $Mon->Settings['type'] === 0 && print 'disabled' ?>" id="monVisualTable">
                            <div class="mon__visual-options mon__visual-options-table">
                                <span><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_modeTable') ?></span>
                                <div class="mon__visual-radio-wrapper">
                                    <input type="radio" id="oneLineServers" name="mon_server_table_count" value="1" <?php $Mon->Settings['server_table_count'] === "1" && print 'checked' ?>>
                                    <label for="oneLineServers">1 <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_countServers') ?></label>
                                </div>
                                <div class="mon__visual-radio-wrapper">
                                    <input type="radio" id="twoLineServers" name="mon_server_table_count" value="2" <?php $Mon->Settings['server_table_count'] === "2" && print 'checked' ?>>
                                    <label for="twoLineServers">2 <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_countServers') ?></label>
                                </div>
                            </div>
                            <div class="mon__visual-example-wrapper">
                                <div class="mon__visual-example-table" id="table-example-1">1</div>
                                <div class="mon__visual-example-table" id="table-example-2">2</div>
                            </div>
                        </div>
                    </div>
                    <div class="mon__sub-container">
                        <div class="mon__general-container">
                            <h3><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_main') ?></h3>
                            <div class="flex-inline">
                                <div class="mon__settings-switch">
                                    <div class="mon__visual-switch-wrapper">
                                        <input class="switch" type="checkbox" id="debugStatus" name="mon_debug" <?php $Mon->Settings['debug'] === 1 && print 'checked' ?>>
                                        <label for="debugStatus"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_DebugLogs') ?></label>
                                    </div>
                                    <div class="mon__visual-switch-wrapper">
                                        <input class="switch" type="checkbox" id="primeStatus" name="mon_prime" <?php $Mon->Settings['prime'] === 1 && print 'checked' ?>>
                                        <label for="primeStatus"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_showPrime') ?></label>
                                    </div>
                                    <div class="mon__visual-switch-wrapper">
                                        <input class="switch" type="checkbox" id="vipStatus" name="mon_vip" <?php $Mon->Settings['vip'] === 1 && print 'checked' ?>>
                                        <label for="vipStatus"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_showVip') ?></label>
                                    </div>
                                    <div class="mon__visual-switch-wrapper">
                                        <input class="switch" type="checkbox" id="adminStatus" name="mon_admin" <?php $Mon->Settings['admin'] === 1 && print 'checked' ?>>
                                        <label for="adminStatus"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_showAdmin') ?></label>
                                    </div>
                                    <div class="mon__visual-switch-wrapper">
                                        <input class="switch" type="checkbox" id="showOnlineLine" name="mon_show_online_line" <?php $Mon->Settings['show_online_line'] === 1 && print 'checked' ?>>
                                        <label for="showOnlineLine"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_showOnlineLine') ?></label>
                                    </div>
                                </div>
                                <div class="mon__settings-switch">
                                    <div class="mon__visual-switch-wrapper">
                                        <input class="switch" type="checkbox" id="filterModes" name="mon_filter_modes" <?php $Mon->Settings['filter_modes'] === 1 && print 'checked' ?>>
                                        <label for="filterModes"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_filterMode') ?></label>
                                    </div>
                                    <div class="mon__visual-switch-wrapper">
                                        <input class="switch" type="checkbox" id="punishAllServer" name="mon_punish_all_server"
                                            <?php $Mon->Settings['punish_all_server'] === 1 && print 'checked' ?>>
                                        <label for="punishAllServer"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_punishmentsAllServers') ?></label>
                                    </div>
                                    <div class="mon__visual-switch-wrapper">
                                        <input class="switch" type="checkbox" id="plugs" name="mon_plugs" <?php $Mon->Settings['plugs'] === 1 && print 'checked' ?>>
                                        <label for="plugs"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_showEmpty') ?></label>
                                    </div>
                                    <div class="mon__visual-switch-wrapper">
                                        <input class="switch" type="checkbox" id="showPing" name="mon_show_ping" <?php $Mon->Settings['show_ping'] === 1 && print 'checked' ?>>
                                        <label for="showPing"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_showPing') ?></label>
                                    </div>
                                    
                                </div>
                            </div>

                            <div class="flex-inline">
                                <div class="inputs-inline">
                                    <label for="timeUpdateCache"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_updateCache') ?></label>
                                    <div class="mon__visual-input">
                                        <input type="text" placeholder="<?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_timeCache') ?>" name="mon_time" value="<?php !empty($Mon->Settings['time']) && print $Mon->Settings['time'] ?>" id="timeUpdateCache">
                                    </div>
                                </div>
                                <div class="inputs-inline">
                                    <label for="passwordInput"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_Password') ?></label>
                                    <div class="number">
                                        <input type="password" value="<?php !empty($Mon->Settings['password']) && print $Mon->Settings['password'] ?>" name="mon_password" id="monPassword">
                                        <div class="eye-password" id="show_pass">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#eye"></use>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_save') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-12">
            <div class="mon-btn__install">
                <button class="secondary_btn" id="installTables"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_installModule') ?>
                    <span class="fake-loader spinner" id="installTablesSpinner" style="display: none;">
                        <svg viewBox="0 0 50 50">
                            <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>