<script src="/storage/assets/js/Sortable.min.js"></script>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_settingsServers') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="ex-mon__servers-action" style="display:none;">
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="changeStatusSelected">
                            <li>
                                <label class="adaptive-select__label" for="changeToTechnicalWorks">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_technicalWork') ?></div>
                                    <input class="hide-input" id="changeToTechnicalWorks" type="radio" name="changeMassStatus" value="2">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="changeToHidden">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_hidden') ?></div>
                                    <input class="hide-input" id="changeToHidden" type="radio" name="changeMassStatus" value="0">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="changeToActive">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_active') ?></div>
                                    <input class="hide-input" id="changeToActive" type="radio" name="changeMassStatus" value="1">
                                </label>
                            </li>
                        </ul>
                        <div class="adaptive-select" open-select="changeStatusSelected">
                            <span class="adaptive-select__fist-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#unlock"></use>
                                </svg>
                            </span>
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_selectStatus') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <button type="button" id="bulkStatusBtn">Изменить статус</button>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="ex-mon__table-width"><input type="checkbox" id="checkAllServers"></th>
                                <th class="ex-mon__table-width">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#move"></use>
                                    </svg>
                                </th>
                                <th class="ex-mon__table-width"><?= $Translate->get_translate_phrase('_Game') ?></th>
                                <th><?= $Translate->get_translate_phrase('_Server') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_address') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_serverStatus') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="sortable-table">
                            <?php foreach ($General->server_list as $srv): ?>
                                <tr data-mod="<?= (int)$srv['id'] ?>"
                                    data-server-id="<?= (int)$srv['id'] ?>"
                                    data-name="<?= htmlspecialchars($srv['name_custom'] ?? '') ?>"
                                    data-game="<?= htmlspecialchars($srv['server_game'] ?? 'cs2') ?>"
                                    data-ip="<?= htmlspecialchars($srv['ip'] ?? '') ?>"
                                    data-mod-val="<?= htmlspecialchars($srv['server_mod'] ?? '') ?>"
                                    data-status="<?= (int)($srv['server_status'] ?? 1) ?>"
                                    data-stats="<?= htmlspecialchars($srv['server_stats'] ?? '') ?>"
                                    data-vip="<?= htmlspecialchars($srv['server_vip'] ?? '') ?>"
                                    data-vip-id="<?= htmlspecialchars($srv['server_vip_id'] ?? '') ?>"
                                    data-sb="<?= htmlspecialchars($srv['server_sb'] ?? '') ?>"
                                    data-sb-id="<?= htmlspecialchars($srv['server_sb_id'] ?? '') ?>"
                                    data-rcon="<?= htmlspecialchars($srv['rcon'] ?? '') ?>"
                                    data-bage="<?= htmlspecialchars($srv['server_bage'] ?? '') ?>">
                                    <td class="ex-mon__table-width"><input type="checkbox" class="server-checkbox"></td>
                                    <td class="ex-mon__table-width handle">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                        </svg>
                                    </td>
                                    <td class="ex-mon__table-width">
                                        <div class="<?= ($srv['server_game'] ?? 'cs2') === 'csgo' ? 'game__csgo' : 'game__cs2' ?>">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#<?= ($srv['server_game'] ?? 'cs2') === 'csgo' ? 'csgo' : 'cs2'; ?>"></use>
                                            </svg>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ex-mon__settings-server-table">
                                            <?php if (!empty($srv['server_vip'])): ?>
                                                <svg class="ex-mon__vip-icon" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_connectedVip') ?>" data-tippy-placement="top">
                                                    <use href="/resources/img/sprite.svg#diamond"></use>
                                                </svg>
                                            <?php endif; ?>
                                            <?php if (!empty($srv['server_sb'])): ?>
                                                <svg class="ex-mon__admin-icon" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_connectedAdminSystem') ?> <?= explode(';', $srv['server_sb'] ?? '')[0] ?>" data-tippy-placement="top">
                                                    <use href="/resources/img/sprite.svg#policeman"></use>
                                                </svg>
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($srv['name_custom'] ?? '') ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($srv['ip'] ?? '') ?></td>
                                    <td><?php
                                        $srvStatus = (int)($srv['server_status'] ?? 1);
                                        if ($srvStatus === 0) {
                                            echo $Translate->get_translate_module_phrase('module_page_mon_settings', '_hidden');
                                        } elseif ($srvStatus === 2) {
                                            echo $Translate->get_translate_module_phrase('module_page_mon_settings', '_technicalWork');
                                        } else {
                                            echo $Translate->get_translate_module_phrase('module_page_mon_settings', '_active');
                                        } ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="button-icon server-edit-btn" data-tippy-content="<?= $Translate->get_translate_phrase('_Change') ?>" data-tippy-placement="top" data-openmodal="editServerModal" type="button">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#edit-pen"></use>
                                                </svg>
                                            </button>
                                            <button class="button-icon button-delete server-del-btn" data-tippy-content="<?= $Translate->get_translate_phrase('_Delete_Action') ?>" data-tippy-placement="top" data-id="<?= (int)$srv['id'] ?>" type="button">
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
        </div>
    </div>
</div>

<div class="popup_modal" id="editServerModal">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_editServer') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div>
            <form id="editServerForm">
                <input type="hidden" name="edit_server" value="1">
                <input type="hidden" name="server_id" id="editServerId">
                <label for="editSelectGame"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_selectGame') ?></label>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="serverGame">
                        <li>
                            <label class="adaptive-select__label" for="serverGameCs2">
                                <div class="adaptive-select__label-text">CS 2</div>
                                <input class="hide-input" id="serverGameCs2" type="radio" name="game" value="cs2">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="serverGameCsgo">
                                <div class="adaptive-select__label-text">CS:GO</div>
                                <input class="hide-input" id="serverGameCsgo" type="radio" name="game" value="csgo">
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="serverGame">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#cs2"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_phrase('_Game') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>

                <label for="editSelectMod"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_selectMod') ?></label>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="option-server_mod-select">
                        <?php foreach ($General->getMods() as $mod_code => $mod_value): ?>
                            <li>
                                <label class="adaptive-select__label" for="for_<?= $mod_code ?>">
                                    <div class="adaptive-select__label-text"><?= $mod_code ?></div>
                                    <input class="hide-input" id="for_<?= $mod_code ?>" type="radio" value="<?= $mod_code ?>" name="server_mod">
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="option-server_mod-select">
                        <span class="adaptive-select__span_text">-</span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="inputs-inline">
                    <label for="serverName"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_serverName') ?></label>
                    <input id="serverName" type="text" value="" name="server_name" placeholder="">
                </div>
                <div class="inputs-inline">
                    <label for="serverBage"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_serverBage') ?></label>
                    <input id="serverBage" type="text" value="" name="server_bage" placeholder="">
                </div>
                <div class="inputs-inline">
                    <label for="serverAddress">IP:PORT</label>
                    <input id="serverAddress" type="text" value="" name="server_ip_port" placeholder="">
                </div>
                <div class="inputs-inline">
                    <label for="rconPasswordEdit"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_rconPassword') ?></label>
                    <div class="number">
                        <input type="password" name="server_rcon" id="rconPasswordEdit">
                        <div class="eye-password" id="show_pass">
                            <svg>
                                <use href="/resources/img/sprite.svg#eye"></use>
                            </svg>
                        </div>
                    </div>
                </div>
                <label for="editSelectStatus"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_selectStatus') ?></label>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="statusServer">
                        <li>
                            <label class="adaptive-select__label" for="serverStatus-0">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_technicalWork') ?></div>
                                <input class="hide-input" id="serverStatus-0" type="radio" name="server_status" value="2">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="serverStatus-1">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_hidden') ?></div>
                                <input class="hide-input" id="serverStatus-1" type="radio" name="server_status" value="0">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="serverStatus-2">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_active') ?></div>
                                <input class="hide-input" id="serverStatus-2" type="radio" name="server_status" value="1">
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="statusServer">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#unlock"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_serverStatus') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <label for="editSelectStats"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_selectStats') ?></label>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="statsDatabase">
                        <?php if (!empty($Db->db_data['LevelsRanks'])):
                            for ($q = 0, $c = sizeof($Db->db_data['LevelsRanks']); $q < $c; $q++): ?>
                                <li>
                                    <label class="adaptive-select__label" for="for_LevelsRanks_<?= $q ?>">
                                        <div class="adaptive-select__label-text"> <?= $Db->db_data['LevelsRanks'][$q]['USER'] . ' -> ' . $Db->db_data['LevelsRanks'][$q]['DB'] . ' -> ' . $Db->db_data['LevelsRanks'][$q]['Table'] . ' ( ' . $Db->db_data['LevelsRanks'][$q]['name'] . ' )' ?></div>
                                        <input class="hide-input" id="for_LevelsRanks_<?= $q ?>" type="radio" value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['LevelsRanks'][$q]['DB_mod'], $Db->db_data['LevelsRanks'][$q]['USER_ID'], $Db->db_data['LevelsRanks'][$q]['DB_num'], $Db->db_data['LevelsRanks'][$q]['Table']) ?>" name="server_stats">
                                    </label>
                                </li>
                        <?php endfor;
                        endif ?>
                    </ul>
                    <div class="adaptive-select" open-select="statsDatabase">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#link"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text">LR Base Name</span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <?php if (!empty($Db->db_data['Vips'])): ?>
                    <label for="editSelectVip"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_selectVip') ?></label>
                    <div class="ex-mon__db-id">
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="vipDatabase">
                                <?php for ($q = 0, $c = sizeof($Db->db_data['Vips']); $q < $c; $q++): ?>
                                    <li>
                                        <label class="adaptive-select__label" for="for_Vips_<?= $q ?>">
                                            <div class="adaptive-select__label-text"><?= $Db->db_data['Vips'][$q]['USER'] . ' -> ' . $Db->db_data['Vips'][$q]['DB'] . ' -> ' . $Db->db_data['Vips'][$q]['Table'] . ' ( ' . $Db->db_data['Vips'][$q]['name'] . ' )' ?></div>
                                            <input class="hide-input" id="for_Vips_<?= $q ?>" onclick="$('.vip_id').css('display','block')" type="radio" value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['Vips'][$q]['DB_mod'], $Db->db_data['Vips'][$q]['USER_ID'], $Db->db_data['Vips'][$q]['DB_num'], $Db->db_data['Vips'][$q]['Table']) ?>" name="server_vip">
                                        </label>
                                    </li>
                                <?php endfor ?>
                            </ul>
                            <div class="adaptive-select" open-select="vipDatabase">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#link"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text">Vip Base Name</span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <input id="vipID" type="text" value="" name="server_vip_id" placeholder="VIP ID">
                    </div>
                <?php endif; ?>
                <?php if (!empty($Db->db_data['IksAdmin'] || $Db->db_data['IksAdminNew'] || $Db->db_data['AdminSystem']) || $Db->db_data['SourceBans']): ?>
                    <label for="editSelectAdmin"><?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_selectAdmin') ?></label>
                    <div class="ex-mon__db-id">
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="adminDatabase">
                                <?php if (!empty($Db->db_data['IksAdmin'])): ?>
                                    <?php for ($q = 0, $c = sizeof($Db->db_data['IksAdmin']); $q < $c; $q++): ?>
                                        <li>
                                            <label class="adaptive-select__label" for="for_IksAdmin_<?= $q ?>">
                                                <div class="adaptive-select__label-text"><?= $Db->db_data['IksAdmin'][$q]['USER'] . ' -> ' . $Db->db_data['IksAdmin'][$q]['DB'] . ' -> ' . $Db->db_data['IksAdmin'][$q]['Table'] . ' ( ' . $Db->db_data['IksAdmin'][$q]['name'] . ' )' ?></div>
                                                <input class="hide-input" id="for_IksAdmin_<?= $q ?>" type="radio" value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['IksAdmin'][$q]['DB_mod'], $Db->db_data['IksAdmin'][$q]['USER_ID'], $Db->db_data['IksAdmin'][$q]['DB_num'], $Db->db_data['IksAdmin'][$q]['Table']) ?>" name="server_sb">
                                            </label>
                                        </li>
                                    <?php endfor ?>
                                <?php endif; ?>
                                <?php if (!empty($Db->db_data['IksAdminNew'])): ?>
                                    <?php for ($q = 0, $c = sizeof($Db->db_data['IksAdminNew']); $q < $c; $q++): ?>
                                        <li>
                                            <label class="adaptive-select__label" for="for_IksAdminNew_<?= $q ?>">
                                                <div class="adaptive-select__label-text"><?= $Db->db_data['IksAdminNew'][$q]['USER'] . ' -> ' . $Db->db_data['IksAdminNew'][$q]['DB'] . ' -> ' . $Db->db_data['IksAdminNew'][$q]['Table'] . ' ( ' . $Db->db_data['IksAdminNew'][$q]['name'] . ' )' ?></div>
                                                <input class="hide-input" id="for_IksAdminNew_<?= $q ?>" type="radio" value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['IksAdminNew'][$q]['DB_mod'], $Db->db_data['IksAdminNew'][$q]['USER_ID'], $Db->db_data['IksAdminNew'][$q]['DB_num'], $Db->db_data['IksAdminNew'][$q]['Table']) ?>" name="server_sb">
                                            </label>
                                        </li>
                                    <?php endfor ?>
                                <?php endif; ?>
                                <?php if (!empty($Db->db_data['AdminSystem'])): ?>
                                    <?php for ($q = 0, $c = sizeof($Db->db_data['AdminSystem']); $q < $c; $q++): ?>
                                        <li>
                                            <label class="adaptive-select__label" for="for_AdminSystem_<?= $q ?>">
                                                <div class="adaptive-select__label-text"><?= $Db->db_data['AdminSystem'][$q]['USER'] . ' -> ' . $Db->db_data['AdminSystem'][$q]['DB'] . ' -> ' . $Db->db_data['AdminSystem'][$q]['Table'] . ' ( ' . $Db->db_data['AdminSystem'][$q]['name'] . ' )' ?></div>
                                                <input class="hide-input" id="for_AdminSystem_<?= $q ?>" type="radio" value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['AdminSystem'][$q]['DB_mod'], $Db->db_data['AdminSystem'][$q]['USER_ID'], $Db->db_data['AdminSystem'][$q]['DB_num'], $Db->db_data['AdminSystem'][$q]['Table']) ?>" name="server_sb">
                                            </label>
                                        </li>
                                    <?php endfor ?>
                                <?php endif; ?>
                                <?php if (!empty($Db->db_data['SourceBans'])): ?>
                                    <?php for ($q = 0, $c = sizeof($Db->db_data['SourceBans']); $q < $c; $q++): ?>
                                        <li>
                                            <label class="adaptive-select__label" for="for_SourceBans_<?= $q ?>">
                                                <div class="adaptive-select__label-text"><?= $Db->db_data['SourceBans'][$q]['USER'] . ' -> ' . $Db->db_data['SourceBans'][$q]['DB'] . ' -> ' . $Db->db_data['SourceBans'][$q]['Table'] . ' ( ' . $Db->db_data['SourceBans'][$q]['name'] . ' )' ?></div>
                                                <input class="hide-input" id="for_SourceBans_<?= $q ?>" type="radio" value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['SourceBans'][$q]['DB_mod'], $Db->db_data['SourceBans'][$q]['USER_ID'], $Db->db_data['SourceBans'][$q]['DB_num'], $Db->db_data['SourceBans'][$q]['Table']) ?>" name="server_sb">
                                            </label>
                                        </li>
                                    <?php endfor ?>
                                <?php endif; ?>
                            </ul>
                            <div class="adaptive-select" open-select="adminDatabase">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#link"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text">Admin Base Name</span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <input id="adminID" type="text" value="" name="server_sb_id" placeholder="Admin ID">
                    </div>
                <?php endif; ?>
                <hr>
                <button class="width-100 active" type="submit"> <?= $Translate->get_translate_phrase('_saveSettings') ?></button>
            </form>
        </div>
    </div>
</div>