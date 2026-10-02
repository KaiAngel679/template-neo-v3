<?php !isset($_SESSION['user_admin']) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die() ?>
<script src="<?= $General->arr_general['site'] ?>storage/assets/js/Sortable.min.js"></script>
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_statsServers') ?></div>
        </div>
        <div class="card-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Server_Name') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_forAllTime') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_inMonth') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_inWeek') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_forToday') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Db->db_data['LevelsRanks'] as $level_rank):
                            $data = $Db->query('LevelsRanks', $level_rank['USER_ID'], $level_rank['DB_num'], "SELECT 
                                    COUNT(*) as `total_count`,
                                    (SELECT COUNT(*) FROM `" . $level_rank['Table'] . "` WHERE `lastconnect` > UNIX_TIMESTAMP(CURDATE() - INTERVAL 7 DAY)) as `7d`,
                                    (SELECT COUNT(*) FROM `" . $level_rank['Table'] . "` WHERE `lastconnect` > UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 DAY)) as `1d`,
                                    (SELECT COUNT(*) FROM `" . $level_rank['Table'] . "` WHERE `lastconnect` > UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 MONTH)) as `month`
                                    FROM `" . $level_rank['Table'] . "` LIMIT 1;") ?>
                            <tr>
                                <td><?= $level_rank['name'] ?></td>
                                <td><?= $data['total_count'] ?></td>
                                <td><?= $data['month'] ?></td>
                                <td><?= $data['7d'] ?></td>
                                <td><?= $data['1d'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div id="add_server_div" class="modal-window server_form">
    <div class="card">
        <div class="card-header">
            <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Add_Server') ?></div>
            <a href="#" title="<?= $Translate->get_translate_phrase('_Close') ?>" class="modal-close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </a>
        </div>
        <div class="card-container">
            <form id="add_server_form" enctype="multipart/form-data" method="post">
                <div class="row_adm_modal">
                    <div class="add_server_block_form">
                        <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Server') ?></h3>
                        <div class="inputs-inline" style="margin-top: .3rem">
                            <input class="switch" type="checkbox" id="checkboxServerStatus" name="server_status" value="1" checked>
                            <label for="checkboxServerStatus"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Status') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Game') ?></label>
                            <div class="adaptive-select-wrapper">
                                <ul class="adaptive-select__dropdown-list" id="game-select">
                                    <li>
                                        <label class="adaptive-select__label" for="game-select_1">
                                            <div class="adaptive-select__label-text">CS2</div>
                                            <input class="hide-input" id="game-select_1" type="radio" name="server_game" value="cs2" checked>
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="game-select_2">
                                            <div class="adaptive-select__label-text">CS:GO</div>
                                            <input class="hide-input" id="game-select_2" type="radio" name="server_game" value="csgo">
                                        </label>
                                    </li>
                                </ul>
                                <div class="adaptive-select" open-select="game-select">
                                    <span class="adaptive-select__fist-icon">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#servers"></use>
                                        </svg>
                                    </span>
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
                            <label for="serverNameCustom"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Server_name') ?></label>
                            <input name="server_name_custom" value="" id="serverNameCustom">
                        </div>
                        <div class="inputs-inline">
                            <label for="serverName"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_TechnoName') ?></label>
                            <input name="server_name" value="" id="serverName">
                        </div>
                        <div class="inputs-inline">
                            <label for="serverBadge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_BageName') ?></label>
                            <input name="server_bage" value="" id="serverBadge">
                        </div>
                        <div class="inputs-inline">
                            <label for="serverAddress"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Ip_Port_Server') ?></label>
                            <input name="server_ip_port" value="127.0.0.1:27015" id="serverAddress">
                        </div>
                        <div class="inputs-inline">
                            <label for="serverAddressFake"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Ip_Port_View') ?></label>
                            <input name="server_ip_port_fake" value="127.0.0.1:27015" id="serverAddressFake">
                        </div>
                        <div class="inputs-inline">
                            <label for="serverRcon"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Rcon') ?></label>
                            <div class="number">
                                <input type="password" name="server_rcon" value="" id="serverRcon">
                                <div class="eye-password" id="show_pass"><svg>
                                        <use href="/resources/img/sprite.svg#eye"></use>
                                    </svg></div>
                            </div>
                        </div>
                    </div>
                    <div class="add_server_block_form">
                        <h3>MySQL</h3>
                        <div class="inputs-inline">
                            <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Game_mod') ?></label>
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
                        </div>
                        <div class="inputs-inline">
                            <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Stat_Table') ?></label>
                            <div class="adaptive-select-wrapper">
                                <ul class="adaptive-select__dropdown-list" id="option-server_stats-select">
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
                                <div class="adaptive-select" open-select="option-server_stats-select">
                                    <span class="adaptive-select__span_text">-</span>
                                    <span class="margin-left-auto adaptive-select__arrow">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($Db->db_data['Vips'])): ?>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Vip_Table') ?></label>
                                <div class="adaptive-select-wrapper">
                                    <ul class="adaptive-select__dropdown-list" id="option-server_vip-select">
                                        <?php for ($q = 0, $c = sizeof($Db->db_data['Vips']); $q < $c; $q++): ?>
                                            <li>
                                                <label class="adaptive-select__label" for="for_Vips_<?= $q ?>">
                                                    <div class="adaptive-select__label-text"><?= $Db->db_data['Vips'][$q]['USER'] . ' -> ' . $Db->db_data['Vips'][$q]['DB'] . ' -> ' . $Db->db_data['Vips'][$q]['Table'] . ' ( ' . $Db->db_data['Vips'][$q]['name'] . ' )' ?></div>
                                                    <input class="hide-input" id="for_Vips_<?= $q ?>" onclick="$('.vip_id').css('display','block')" type="radio" value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['Vips'][$q]['DB_mod'], $Db->db_data['Vips'][$q]['USER_ID'], $Db->db_data['Vips'][$q]['DB_num'], $Db->db_data['Vips'][$q]['Table']) ?>" name="server_vip">
                                                </label>
                                            </li>
                                        <?php endfor ?>
                                    </ul>
                                    <div class="adaptive-select" open-select="option-server_vip-select">
                                        <span class="adaptive-select__span_text">-</span>
                                        <span class="margin-left-auto adaptive-select__arrow">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="vip_id">
                                <div class="inpus-inline">
                                    <label for="serverVipId"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Vip_Sid') ?></label>
                                    <input name="server_vip_id" value="0" id="serverVipId">
                                </div>
                            </div>
                        <?php endif;
                        if (!empty($Db->db_data['IksAdmin'] || $Db->db_data['IksAdminNew'] || $Db->db_data['AdminSystem']) || $Db->db_data['SourceBans']): ?>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Sb_Table') ?></label>
                                <div class="adaptive-select-wrapper">
                                    <ul class="adaptive-select__dropdown-list" id="option-server_sb-select">
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
                                    <div class="adaptive-select" open-select="option-server_sb-select">
                                        <span class="adaptive-select__span_text">-</span>
                                        <span class="margin-left-auto adaptive-select__arrow">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                                            </svg>
                                        </span>
                                    </div>
                                </div>

                            </div>
                            <div class="inputs-inline">
                                <label for="serverSbId"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_iksOrAs') ?></label>
                                <input name="server_sb_id" value="0" id="serverSbId">
                            </div>
                        <?php endif;
                        if (!empty($Db->db_data['Shop'])): ?>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Shop_Table') ?></label>
                                <div class="adaptive-select-wrapper">
                                    <ul class="adaptive-select__dropdown-list" id="option-server_shop-select">
                                        <?php for ($q = 0, $c = sizeof($Db->db_data['Shop']); $q < $c; $q++): ?>
                                            <li>
                                                <label class="adaptive-select__label" for="for_Shop_<?= $q ?>">
                                                    <div class="adaptive-select__label-text"><?= $Db->db_data['Shop'][$q]['USER'] . ' -> ' . $Db->db_data['Shop'][$q]['DB'] . ' -> ' . $Db->db_data['Shop'][$q]['Table'] . ' ( ' . $Db->db_data['Shop'][$q]['name'] . ' )' ?></div>
                                                    <input class="hide-input" id="for_Shop_<?= $q ?>" type="radio" value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['Shop'][$q]['DB_mod'], $Db->db_data['Shop'][$q]['USER_ID'], $Db->db_data['Shop'][$q]['DB_num'], $Db->db_data['Shop'][$q]['Table']) ?>" name="server_shop">
                                                </label>
                                            </li>
                                        <?php endfor ?>
                                    </ul>
                                    <div class="adaptive-select" open-select="option-server_shop-select">
                                        <span class="adaptive-select__span_text">-</span>
                                        <span class="margin-left-auto adaptive-select__arrow">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-bottom">
            <button class="width-100" type="submit" name="save_server" form="add_server_form"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Save') ?></button>
        </div>
    </div>
</div>
<?php if (isset($_GET['id']) || $_GET['id'] !== ''):
    $server_edit = $Admin->action_get_server($_GET['id']); ?>
    <div id="edit_server_div" class="modal-window server_form">
        <div class="card">
            <div class="card-header">
                <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Edit_Server') ?></div>
                <a href="#" title="<?= $Translate->get_translate_phrase('_Close') ?>" class="modal-close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </a>
            </div>
            <div class="card-container">
                <form id="edit_server_div_form" enctype="multipart/form-data" method="post">
                    <div class="row_adm_modal">
                        <input type="hidden" id="server_id" name="server_id_edit" value="<?= $server_edit['id'] ?>">
                        <div class="add_server_block_form">
                            <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Server') ?></h3>
                            <div class="inputs-inline" style="margin-top: .3rem">
                                <input class="switch" type="checkbox" id="checkboxServerStatusEdit" name="server_status_edit" value="<?= $server_edit['server_status'] ?>" <?= $server_edit['server_status'] == 1 ? 'checked' : '' ?>>
                                <label for="checkboxServerStatusEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Status') ?></label>
                            </div>
                            <div class="inputs-inline">
                                <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Game') ?></label>
                                <div class="adaptive-select-wrapper">
                                    <ul class="adaptive-select__dropdown-list" id="game-select-edit">
                                        <li>
                                            <label class="adaptive-select__label" for="game-select-edit_1">
                                                <div class="adaptive-select__label-text">CS2</div>
                                                <input class="hide-input" id="game-select-edit_1" type="radio" name="server_game_edit" value="cs2" <?= $server_edit['server_game'] == 'cs2' ? 'checked' : '' ?>>
                                            </label>
                                        </li>
                                        <li>
                                            <label class="adaptive-select__label" for="game-select-edit_2">
                                                <div class="adaptive-select__label-text">CS:GO</div>
                                                <input class="hide-input" id="game-select-edit_2" type="radio" name="server_game_edit" value="csgo" <?= $server_edit['server_game'] == 'csgo' ? 'checked' : '' ?>>
                                            </label>
                                        </li>
                                    </ul>
                                    <div class="adaptive-select" open-select="game-select-edit">
                                        <span class="adaptive-select__fist-icon">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#servers"></use>
                                            </svg>
                                        </span>
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
                                <label for="serverManeCustomEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Server_name') ?></label>
                                <input name="server_name_custom_edit" value="<?= $server_edit['name_custom'] ?>" id="serverManeCustomEdit">
                            </div>
                            <div class="inputs-inline">
                                <label for="serverBageEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_TechnoName') ?></label>
                                <input name="server_name_edit" value="<?= $server_edit['name'] ?>" id="serverBageEdit">
                            </div>
                            <div class="inputs-inline">
                                <label for="serverNameEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_BageName') ?></label>
                                <input name="server_bage_edit" value="<?= $server_edit['server_bage'] ?>" id="serverNameEdit">
                            </div>
                            <div class="inputs-inline">
                                <label for="serverAddressEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Ip_Port_Server') ?></label>
                                <input name="server_ip_port_edit" value="<?= $server_edit['ip'] ?>" id="serverAddressEdit">
                            </div>
                            <div class="inputs-inline">
                                <label for="serverAddressFakeEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Ip_Port_View') ?></label>
                                <input name="server_ip_port_fake_edit" value="<?= $server_edit['fakeip'] ?>" id="serverAddressFakeEdit">
                            </div>
                            <div class="inputs-inline">
                                <label for="serverRconEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Rcon') ?></label>
                                <div class="number">
                                    <input type="password" name="server_rcon_edit" value="<?= $server_edit['rcon'] ?>" id="serverRconEdit">
                                    <div class="eye-password" id="show_pass">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#eye"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="add_server_block_form">
                            <h3>MySQL</h3>
                            <div class="inputs-inline">
                                <label for="serverModEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Game_mod') ?></label>
                                <select name="server_mod_edit" id="serverModEdit">
                                    <?php foreach ($General->getMods() as $mod_code => $mod_value): ?>
                                        <option <?= $server_edit['server_mod'] == $mod_code ? 'selected' : '' ?> value="<?= $mod_code ?>"><?= $mod_code ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="inputs-inline">
                                <label for="serverStatsEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Stat_Table') ?></label>
                                <select name="server_stats_edit" id="serverStatsEdit">
                                    <?php if (!empty($Db->db_data['LevelsRanks'])):
                                        for ($q = 0, $c = sizeof($Db->db_data['LevelsRanks']); $q < $c; $q++): ?>
                                            <option <?= $server_edit['server_stats'] == sprintf('%s;%d;%d;%s', $Db->db_data['LevelsRanks'][$q]['DB_mod'], $Db->db_data['LevelsRanks'][$q]['USER_ID'], $Db->db_data['LevelsRanks'][$q]['DB_num'], $Db->db_data['LevelsRanks'][$q]['Table']) ? 'selected' : '' ?>
                                                value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['LevelsRanks'][$q]['DB_mod'], $Db->db_data['LevelsRanks'][$q]['USER_ID'], $Db->db_data['LevelsRanks'][$q]['DB_num'], $Db->db_data['LevelsRanks'][$q]['Table']) ?>">
                                                <?= $Db->db_data['LevelsRanks'][$q]['USER'] . ' -> ' . $Db->db_data['LevelsRanks'][$q]['DB'] . ' -> ' . $Db->db_data['LevelsRanks'][$q]['Table'] . ' ( ' . $Db->db_data['LevelsRanks'][$q]['name'] . ' )' ?>
                                            </option>
                                    <?php endfor;
                                    endif ?>
                                </select>
                            </div>
                            <?php if (!empty($Db->db_data['Vips'])): ?>
                                <div class="inputs-inline">
                                    <label for="serverVipEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Vip_Table') ?></label>
                                    <select name="server_vip_edit" onChange="$('.vip_id').css('display','block')" id="serverVipEdit">
                                        <?php for ($q = 0, $c = sizeof($Db->db_data['Vips']); $q < $c; $q++): ?>
                                            <option <?= $server_edit['server_vip'] == sprintf('%s;%d;%d;%s', $Db->db_data['Vips'][$q]['DB_mod'], $Db->db_data['Vips'][$q]['USER_ID'], $Db->db_data['Vips'][$q]['DB_num'], $Db->db_data['Vips'][$q]['Table']) ? 'selected' : '' ?>
                                                value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['Vips'][$q]['DB_mod'], $Db->db_data['Vips'][$q]['USER_ID'], $Db->db_data['Vips'][$q]['DB_num'], $Db->db_data['Vips'][$q]['Table']) ?>">
                                                <?= $Db->db_data['Vips'][$q]['USER'] . ' -> ' . $Db->db_data['Vips'][$q]['DB'] . ' -> ' . $Db->db_data['Vips'][$q]['Table'] . ' ( ' . $Db->db_data['Vips'][$q]['name'] . ' )' ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="vip_id" style="display: block; margin-bottom: 0">
                                    <div class="inputs-inline">
                                        <label for="serverVipIdEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Vip_Sid') ?></label>
                                        <input name="server_vip_id_edit" value="<?= $server_edit['server_vip_id'] ?>" id="serverVipIdEdit">
                                    </div>
                                </div>
                            <?php endif;
                            if (!empty($Db->db_data['IksAdmin'] || $Db->db_data['AdminSystem'] || $Db->db_data['IksAdminNew']) || $Db->db_data['SourceBans']): ?>
                                <div class="inputs-inline">
                                    <label for="serverSbEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Sb_Table') ?></label>
                                    <select name="server_sb_edit" id="serverSbEdit">
                                        <?php if (!empty($Db->db_data['IksAdmin'])): ?>
                                            <?php for ($q = 0, $c = sizeof($Db->db_data['IksAdmin']); $q < $c; $q++): ?>
                                                <option <?= $server_edit['server_sb'] == sprintf('%s;%d;%d;%s', $Db->db_data['IksAdmin'][$q]['DB_mod'], $Db->db_data['IksAdmin'][$q]['USER_ID'], $Db->db_data['IksAdmin'][$q]['DB_num'], $Db->db_data['IksAdmin'][$q]['Table']) ? 'selected' : '' ?>
                                                    value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['IksAdmin'][$q]['DB_mod'], $Db->db_data['IksAdmin'][$q]['USER_ID'], $Db->db_data['IksAdmin'][$q]['DB_num'], $Db->db_data['IksAdmin'][$q]['Table']) ?>">
                                                    <?= $Db->db_data['IksAdmin'][$q]['USER'] . ' -> ' . $Db->db_data['IksAdmin'][$q]['DB'] . ' -> ' . $Db->db_data['IksAdmin'][$q]['Table'] . ' ( ' . $Db->db_data['IksAdmin'][$q]['name'] . ' )' ?>
                                                </option>
                                            <?php endfor ?>
                                        <?php endif; ?>
                                        <?php if (!empty($Db->db_data['IksAdminNew'])): ?>
                                            <?php for ($q = 0, $c = sizeof($Db->db_data['IksAdminNew']); $q < $c; $q++): ?>
                                                <option <?= $server_edit['server_sb'] == sprintf('%s;%d;%d;%s', $Db->db_data['IksAdminNew'][$q]['DB_mod'], $Db->db_data['IksAdminNew'][$q]['USER_ID'], $Db->db_data['IksAdminNew'][$q]['DB_num'], $Db->db_data['IksAdminNew'][$q]['Table']) ? 'selected' : '' ?>
                                                    value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['IksAdminNew'][$q]['DB_mod'], $Db->db_data['IksAdminNew'][$q]['USER_ID'], $Db->db_data['IksAdminNew'][$q]['DB_num'], $Db->db_data['IksAdminNew'][$q]['Table']) ?>">
                                                    <?= $Db->db_data['IksAdminNew'][$q]['USER'] . ' -> ' . $Db->db_data['IksAdminNew'][$q]['DB'] . ' -> ' . $Db->db_data['IksAdminNew'][$q]['Table'] . ' ( ' . $Db->db_data['IksAdminNew'][$q]['name'] . ' )' ?>
                                                </option>
                                            <?php endfor ?>
                                        <?php endif; ?>
                                        <?php if (!empty($Db->db_data['AdminSystem'])): ?>
                                            <?php for ($q = 0, $c = sizeof($Db->db_data['AdminSystem']); $q < $c; $q++): ?>
                                                <option <?= $server_edit['server_sb'] == sprintf('%s;%d;%d;%s', $Db->db_data['AdminSystem'][$q]['DB_mod'], $Db->db_data['AdminSystem'][$q]['USER_ID'], $Db->db_data['AdminSystem'][$q]['DB_num'], $Db->db_data['AdminSystem'][$q]['Table']) ? 'selected' : '' ?>
                                                    value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['AdminSystem'][$q]['DB_mod'], $Db->db_data['AdminSystem'][$q]['USER_ID'], $Db->db_data['AdminSystem'][$q]['DB_num'], $Db->db_data['AdminSystem'][$q]['Table']) ?>">
                                                    <?= $Db->db_data['AdminSystem'][$q]['USER'] . ' -> ' . $Db->db_data['AdminSystem'][$q]['DB'] . ' -> ' . $Db->db_data['AdminSystem'][$q]['Table'] . ' ( ' . $Db->db_data['AdminSystem'][$q]['name'] . ' )' ?>
                                                </option>
                                            <?php endfor ?>
                                        <?php endif; ?>
                                        <?php if (!empty($Db->db_data['SourceBans'])): ?>
                                            <?php for ($q = 0, $c = sizeof($Db->db_data['SourceBans']); $q < $c; $q++): ?>
                                                <option <?= $server_edit['server_sb'] == sprintf('%s;%d;%d;%s', $Db->db_data['SourceBans'][$q]['DB_mod'], $Db->db_data['SourceBans'][$q]['USER_ID'], $Db->db_data['SourceBans'][$q]['DB_num'], $Db->db_data['SourceBans'][$q]['Table']) ? 'selected' : '' ?>
                                                    value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['SourceBans'][$q]['DB_mod'], $Db->db_data['SourceBans'][$q]['USER_ID'], $Db->db_data['SourceBans'][$q]['DB_num'], $Db->db_data['SourceBans'][$q]['Table']) ?>">
                                                    <?= $Db->db_data['SourceBans'][$q]['USER'] . ' -> ' . $Db->db_data['SourceBans'][$q]['DB'] . ' -> ' . $Db->db_data['SourceBans'][$q]['Table'] . ' ( ' . $Db->db_data['SourceBans'][$q]['name'] . ' )' ?>
                                                </option>
                                            <?php endfor ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="inputs-inline">
                                    <label for="serverSbIdEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_serverIDIksAs') ?></label>
                                    <input name="server_sb_id_edit" value="<?= $server_edit['server_sb_id'] ?>" id="serverSbIdEdit">
                                </div>
                            <?php endif;
                            if (!empty($Db->db_data['Shop'])): ?>
                                <div class="inputs-inline">
                                    <label for="serverShopEdit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Shop_Table') ?></label>
                                    <select name="server_shop_edit" id="serverShopEdit">
                                        <?php for ($q = 0, $c = sizeof($Db->db_data['Shop']); $q < $c; $q++): ?>
                                            <option <?= $server_edit['server_shop'] == sprintf('%s;%d;%d;%s', $Db->db_data['Shop'][$q]['DB_mod'], $Db->db_data['Shop'][$q]['USER_ID'], $Db->db_data['Shop'][$q]['DB_num'], $Db->db_data['Shop'][$q]['Table']) ? 'selected' : '' ?>
                                                value="<?= sprintf('%s;%d;%d;%s', $Db->db_data['Shop'][$q]['DB_mod'], $Db->db_data['Shop'][$q]['USER_ID'], $Db->db_data['Shop'][$q]['DB_num'], $Db->db_data['Shop'][$q]['Table']) ?>">
                                                <?= $Db->db_data['Shop'][$q]['USER'] . ' -> ' . $Db->db_data['Shop'][$q]['DB'] . ' -> ' . $Db->db_data['Shop'][$q]['Table'] . ' ( ' . $Db->db_data['Shop'][$q]['name'] . ' )' ?>
                                            </option>
                                        <?php endfor ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-bottom">
                <button class="width-100" form="edit_server_div_form" type="submit" name="save_server_edit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Save') ?></button>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Settings') ?></div>
        </div>
        <div class="card-container">
            <?php $settings_neo = $General->get_neo_options();
            if (($Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_mod') && $Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_country') && $Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_city') && $Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_sb_id') && $Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_bage')) == false): ?>
                <form id="create_table" style="margin-bottom: 10px;">
                    <button class="width-100"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Recreate_db_Servers') ?></button>
                </form>
            <?php endif;
            if (($Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_game') && $Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_status') && $Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'sort') && !$Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_warnsystem')) == false): ?>
                <form id="create_table_neo3_7" style="margin-bottom: 10px;">
                    <button class="width-100"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Recreate_db_Servers') ?></button>
                </form>
            <?php endif; ?>
            <fieldset>
                <legend><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_monitoringSettings') ?></legend>
                <form id="hide_filter_form">
                    <div class="inputs-inline">
                        <input class="switch" type="checkbox" name="hide_filter" id="hide_filter" <?php $settings_neo['hide_filter'] === 1 && print 'checked' ?>>
                        <label for="hide_filter"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Hide_filter_Servers') ?></label>
                    </div>
                </form>
                <?php if ($settings_neo['hide_filter'] == 0): ?>
                    <form id="stretch_filter_form">
                        <div class="inputs-inline">
                            <input class="switch" type="checkbox" name="stretch_filter" id="stretch_filter" <?php $settings_neo['stretch_filter'] === 1 && print 'checked' ?>>
                            <label for="stretch_filter"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Stretch_filter_buttons') ?></label>
                        </div>
                    </form>
                <?php endif; ?>
                <form id="hide_city_form">
                    <div class="inputs-inline">
                        <input class="switch" type="checkbox" name="hide_city" id="hide_city" <?php $settings_neo['hide_city'] === 1 && print 'checked' ?>>
                        <label for="hide_city"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Hide_the_city_in_monitoring') ?></label>
                    </div>
                </form>
                <form id="hide_country_form">
                    <div class="inputs-inline">
                        <input class="switch" type="checkbox" name="hide_country" id="hide_country" <?php $settings_neo['hide_country'] === 1 && print 'checked' ?>>
                        <label for="hide_country"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Hide_country_in_monitoring') ?></label>
                    </div>
                </form>
            </fieldset>
            <?php if (($Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_mod') && $Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_country') && $Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_city') && $Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_sb_id') && $Db->mysql_column_search('Core', 0, 0, 'lvl_web_servers', 'server_bage')) == true): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Id_server') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Status') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Game') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Server_Name') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_IpPort_server') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Game_mod') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Country_server') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_City_server') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="sortable-table-server">
                            <?php for ($i_server = 0; $i_server < $General->server_list_count; $i_server++): ?>
                                <tr id="<?= $General->server_list[$i_server]['id'] ?>" class="handle">
                                    <td>
                                        <svg class="handle-icon">
                                            <use href="/resources/img/sprite.svg#two-lines"></use>
                                        </svg>
                                    </td>
                                    <td><?= $General->server_list[$i_server]['id'] ?></td>
                                    <td><?= $General->server_list[$i_server]['server_status'] == 1 ? '<svg class="green"><use href="/resources/img/sprite.svg#check"></use></svg>' : '<svg class="red"><use href="/resources/img/sprite.svg#x"></use></svg>' ?></td>
                                    <td>
                                        <?php if ($General->server_list[$i_server]['server_game'] == 'cs2'): ?>
                                            <div class="game__cs2">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#cs2"></use>
                                                </svg>
                                            </div>
                                        <?php elseif ($General->server_list[$i_server]['server_game'] == 'csgo'): ?>
                                            <div class="game__csgo">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#csgo"></use>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $General->server_list[$i_server]['name'] ?></td>
                                    <td><?= $General->server_list[$i_server]['ip'] ?></td>
                                    <td><?= $General->server_list[$i_server]['server_mod'] ?></td>
                                    <td><?php if ($General->server_list[$i_server]['server_mod'] == $Translate->get_translate_phrase('_editServer')): ?>-<?php else: ?><img class="country_flag_img" src="/storage/cache/img/icons/custom/flags/<?= mb_strtolower($General->server_list[$i_server]['server_country']) ?>.svg" alt=""><?php endif; ?></td>
                                    <td><?php if ($General->server_list[$i_server]['server_mod'] == $Translate->get_translate_phrase('_editServer')): ?>-<?php else: ?><?= $General->server_list[$i_server]['server_city'] ?><?php endif; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a class="button" type="input" href="<?= set_url_section(get_url(2), 'id', $General->server_list[$i_server]['id']) ?>#edit_server_div"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_change_menu') ?></a>
                                            <button onclick="delete_server(this)" class="button-delete"><?= $Translate->get_translate_phrase('_Delete_Action') ?></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
                <a class="button" style="margin-top: .5rem" type="input" href="#add_server_div"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Add_Server') ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>