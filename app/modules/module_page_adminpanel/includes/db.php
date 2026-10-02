<?php !isset($_SESSION['user_admin']) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die();
foreach ($Db->db_data as $mod):
    foreach ($mod as $connection):
        $db_data[] = $connection;
    endforeach;
endforeach;
$db_data_file = require SESSIONS . '/db.php';
$edit_info = explode(";", $_GET['data']);
?>

<div id="add_connect" class="modal-window server_form">
    <div class="card">
        <div class="card-header">
            <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Add_con') ?>
            </div>
            <a href="#" title="<?= $Translate->get_translate_phrase('_Close') ?>" class="modal-close"><svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg></a>
        </div>
        <div class="card-container">
            <div class="new_connect" id="con_mod_name"></div>
            <form enctype="multipart/form-data" method="post" id="form-add-conection">
                <input type="hidden" id="con_mod_id" name="mod">
                <div id="db_option_con">
                    <div class="flex-inline">
                        <div class="inputs-inline">
                            <label
                                for="con_host"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_host') ?></label>
                            <input type="text" name="host" id="con_host">
                        </div>
                        <div class="inputs-inline">
                            <label
                                for="con_db_name"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_name_db') ?></label>
                            <input type="text" name="db_name" id="con_db_name">
                        </div>
                    </div>
                    <div class="flex-inline">
                        <div class="inputs-inline">
                            <label
                                for="con_user_name"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_username') ?></label>
                            <input type="text" name="username" id="con_user_name">
                        </div>
                        <div class="inputs-inline">
                            <label
                                for="con_password"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_password') ?></label>
                            <div class="number">
                                <input type="password" name="password" id="con_password">
                                <div class="eye-password" id="show_pass"><svg>
                                        <use href="/resources/img/sprite.svg#eye"></use>
                                    </svg></div>
                            </div>
                        </div>
                        <div class="inputs-inline">
                            <label
                                for="con_port"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_port') ?></label>
                            <input type="text" name="port" id="con_port" value="3306">
                        </div>
                    </div>
                </div>
                <div class="flex-inline">
                    <div class="inputs-inline flex-1">
                        <label
                            for="con_table_name"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_table_name') ?></label>
                        <input type="text" id="con_table_name" name="table_name">
                    </div>
                    <div id="rank_pack_connection" class="inputs-inline flex-1">
                        <label
                            for="con_rank_pack"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_rank_pack') ?></label>
                        <input type="text" name="rank_pack" id="con_rank_pack" value="default">
                    </div>
                    <div class="inputs-inline flex-1">
                        <label
                            for="con_server_name"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Server_name') ?></label>
                        <input type="text" name="server_name" id="con_server_name">
                    </div>
                </div>
            </form>
        </div>
        <div class="card-bottom">
            <button class="width-100" form="form-add-conection"
                onclick="addConection()"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_add') ?></button>
        </div>
    </div>
</div>
<div id="edit_connect" class="modal-window server_form">
    <div class="card">
        <div class="card-header">
            <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Edit_con') ?>
            </div>
            <a href="#" title="<?= $Translate->get_translate_phrase('_Close') ?>" class="modal-close"><svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg></a>
        </div>
        <div class="card-container">
            <div class="new_connect"><?= $Translate->get_translate_module_phrase('module_page_adminpanel','_mod') ?>: <?= $edit_info[0] ?></div>
            <form enctype="multipart/form-data" method="post" id="form-edit-conection">
                <input type="hidden" name="mod_edit" value="<?= $edit_info[0] ?>">
                <input type="hidden" name="mod_info_edit" value="<?= $_GET['data'] ?>">
                <div id="db_option_con">
                    <div class="flex-inline">
                        <div class="inputs-inline flex-1">
                            <label
                                for="con_host_edit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_host') ?></label>
                            <input id="con_host_edit" type="text" name="host_edit"
                                value="<?= $db_data_file[$edit_info[0]][$edit_info[1]]['HOST'] ?>">
                        </div>
                        <div class="inputs-inline flex-1">
                            <label
                                for="con_db_name_edit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_name_db') ?></label>
                            <input id="con_db_name_edit" type="text" name="db_name_edit"
                                value="<?= $db_data_file[$edit_info[0]][$edit_info[1]]['DB'][$edit_info[2]]['DB'] ?>">
                        </div>
                    </div>
                    <div class="flex-inline">
                        <div class="inputs-inline flex-1">
                            <label
                                for="con_user_name_edit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_username') ?></label>
                            <input id="con_user_name_edit" type="text" name="username_edit"
                                value="<?= $db_data_file[$edit_info[0]][$edit_info[1]]['USER'] ?>">
                        </div>
                        <div class="inputs-inline flex-1">
                            <label
                                for="con_password_edit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_password') ?></label>
                            <div class="number">
                                <input id="con_password_edit" type="password" name="password_edit"
                                    value="<?= $db_data_file[$edit_info[0]][$edit_info[1]]['PASS'] ?>">
                                <div class="eye-password" id="show_pass"><svg>
                                        <use href="/resources/img/sprite.svg#eye"></use>
                                    </svg></div>
                            </div>
                        </div>
                        <div class="inputs-inline flex-1">
                            <label
                                for="con_port_edit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_port') ?></label>
                            <input id="con_port_edit" type="text" name="port_edit" value="3306"
                                value="<?= $db_data_file[$edit_info[0]][$edit_info[1]]['PORT'] ?>">
                        </div>
                    </div>
                </div>
                <div class="flex-inline">
                    <div class="inputs-inline flex-1">
                        <label
                            for="con_table_name_edit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_table_name') ?></label>
                        <input id="con_table_name_edit" type="text" name="table_name_edit"
                            value="<?= $db_data_file[$edit_info[0]][$edit_info[1]]['DB'][$edit_info[2]]['Prefix'][$edit_info[3]]['table'] ?>">
                    </div>
                    <?php if ($edit_info[0] == 'LevelsRanks'): ?>
                        <div id="rank_pack_connection_edit" class="inputs-inline flex-1">
                            <label
                                for="con_rank_pack_edit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_rank_pack') ?></label>
                            <input id="con_rank_pack_edit" type="text" name="rank_pack_edit"
                                value="<?= $db_data_file[$edit_info[0]][$edit_info[1]]['DB'][$edit_info[2]]['Prefix'][$edit_info[3]]['ranks_pack'] ?>">
                        </div>
                    <?php endif; ?>
                    <div class="inputs-inline flex-1">
                        <label
                            for="con_server_name_edit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Server_name') ?></label>
                        <input id="con_server_name_edit" type="text" name="server_name_edit"
                            value="<?= $db_data_file[$edit_info[0]][$edit_info[1]]['DB'][$edit_info[2]]['Prefix'][$edit_info[3]]['name'] ?>">
                    </div>
                </div>
            </form>
        </div>
        <div class="card-bottom">
            <button form="form-edit-conection" class="width-100"
                type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Save_change') ?></button>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card height-100">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_mod_list') ?></h5>
        </div>
        <div class="card-block">
            <div class="list_mods">
                <?php for ($i_db = 0; $i_db < $Db->mod_count; $i_db++): ?>
                    <div class="line_mod">
                        <?= $Db->mod_name[$i_db] ?>
                        <button class="button-delete db-close" name="<?= $Db->mod_name[$i_db] ?>"
                            onclick="action_db_delete_mod(this, this.getAttribute('name'))">
                            <svg>
                                <use href="/resources/img/sprite.svg#x"></use>
                            </svg>
                        </button>
                    </div>
                <?php endfor ?>
            </div>
        </div>
    </div>
</div>
<div class="col-md-9">
    <div class="card height-100">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_configure') ?>
            </h5>
        </div>
        <div class="card-container height-100">
            <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_mod') ?></label>
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="option-mod-select">
                    <li>
                        <label class="adaptive-select__label" for="mods_LevelsRanks">
                            <div class="adaptive-select__label-text">
                                Levels Ranks
                            </div>
                            <input class="hide-input" id="mods_LevelsRanks" onclick="changeConnection(this.value)"
                                type="radio" name="mod-select" value="LevelsRanks">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="mods_IksAdmin">
                            <div class="adaptive-select__label-text">
                                IksAdmin
                            </div>
                            <input class="hide-input" id="mods_IksAdmin" onclick="changeConnection(this.value)"
                                type="radio" name="mod-select" value="IksAdmin">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="mods_IksAdminNew">
                            <div class="adaptive-select__label-text">
                                IksAdminNew
                            </div>
                            <input class="hide-input" id="mods_IksAdminNew" onclick="changeConnection(this.value)"
                                type="radio" name="mod-select" value="IksAdminNew">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="mods_AdminSystem">
                            <div class="adaptive-select__label-text">
                                AdminSystem
                            </div>
                            <input class="hide-input" id="mods_AdminSystem" onclick="changeConnection(this.value)"
                                type="radio" name="mod-select" value="AdminSystem">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="mods_SourceBans">
                            <div class="adaptive-select__label-text">
                                SourceBans
                            </div>
                            <input class="hide-input" id="mods_SourceBans" onclick="changeConnection(this.value)"
                                type="radio" name="mod-select" value="SourceBans">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="mods_Vips">
                            <div class="adaptive-select__label-text">
                                Vips
                            </div>
                            <input class="hide-input" id="mods_Vips" onclick="changeConnection(this.value)" type="radio"
                                name="mod-select" value="Vips">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="mods_Reports">
                            <div class="adaptive-select__label-text">
                                Reports
                            </div>
                            <input class="hide-input" id="mods_Reports" onclick="changeConnection(this.value)"
                                type="radio" name="mod-select" value="Reports">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="mods_Shop">
                            <div class="adaptive-select__label-text">
                                Shop
                            </div>
                            <input class="hide-input" id="mods_Shop" onclick="changeConnection(this.value)" type="radio"
                                name="mod-select" value="Shop">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="mods_custom">
                            <div class="adaptive-select__label-text">
                                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_custom_mode') ?>
                            </div>
                            <input class="hide-input" id="mods_custom" onclick="changeConnection(this.value)"
                                type="radio" name="mod-select" value="custom">
                        </label>
                    </li>
                </ul>
                <div class="adaptive-select" open-select="option-mod-select">
                    <span
                        class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_take_mod') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="custom_mode" id="custom_mod_wrapper" style="display:none;">
                <label for="custom_mod_name"
                    class="add_connection_label"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_enter_mode_name') ?></label>
                <input type="text" name="custom_mod" id="custom_mod_name">
            </div>
        </div>
        <div class="card-bottom">
            <a class="button width-100" type="input" onclick="changeNameModule()" id="add_conection_button"
                href="#"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Add_con') ?></a>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_list_con') ?></h5>
        </div>
        <div class="card-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_mod') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_user') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_db_con') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_table_prefix') ?>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i_db = 0, $_c = sizeof($db_data); $i_db < $_c; $i_db++): ?>
                            <tr>
                                <td><?= $db_data[$i_db]['DB_mod'] ?></td>
                                <td><?= $db_data[$i_db]['USER'] ?></td>
                                <td><?= $db_data[$i_db]['DB'] ?></td>
                                <td><?= $db_data[$i_db]['Table'] ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a class="button" type="input"
                                            href="<?php echo set_url_section(get_url(2), 'data', sprintf('%s;%d;%d;%d', $db_data[$i_db]['DB_mod'], $db_data[$i_db]['USER_ID'], $db_data[$i_db]['DB_num'], $db_data[$i_db]['table_id'])) ?>#edit_connect"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_change_menu') ?></a>
                                        <button
                                            name="<?= sprintf('%s;%d;%d;%d', $db_data[$i_db]['DB_mod'], $db_data[$i_db]['USER_ID'], $db_data[$i_db]['DB_num'], $db_data[$i_db]['table_id']) ?>"
                                            class="button-delete"
                                            onclick="action_db_delete_table(this, this.getAttribute('name'))"><?= $Translate->get_translate_phrase('_Delete_Action') ?></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endfor ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>