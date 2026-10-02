<div class="col-md-9">
    <div class="card">
        <div class="card-header">
            <div class="badge">
                <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Information_player'); ?>
            </div>
        </div>
        <div class="card-container">
            <div class="general_info">
                <div class="fill_blocks">
                    <div class="title_head">
                        <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Admin_info'); ?>
                    </div>
                    <div class="adm_content">
                        <div class="adm_info_string">
                            <div class="adm_info_title">
                                <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Admin_Group'); ?>
                            </div>
                            <div class="adm_info_content admin_bdg">
                                <?php if ((!empty($Db->db_data['IksAdmin']) || !empty($Db->db_data['IksAdminNew']) || !empty($Db->db_data['AdminSystem'])) && in_array($Player->lws['server_sb'][0], ['IksAdmin', 'IksAdminNew', 'AdminSystem'])) {
                                    $groupFound = false;
                                    foreach ($Groups as $key) {
                                        if ($Admins['group_id'] == -1 || $Admins['group_id'] == 0) {
                                            echo $Translate->get_translate_module_phrase('module_page_profiles', '_No_Group');
                                            $groupFound = true;
                                            break;
                                        } else {
                                            if ($Admins['group_id'] == $key['id']) {
                                                echo $key['name'];
                                                $groupFound = true;
                                                break;
                                            }
                                        }
                                    }
                                    if (!$groupFound) {
                                        echo $Translate->get_translate_module_phrase('module_page_profiles', '_No_Group');
                                    }
                                } elseif (!empty($Db->db_data['SourceBans']) && $Player->lws['server_sb'][0] == 'SourceBans') {
                                    echo $Admins['srv_group'];
                                }
                                ?></div>
                        </div>
                        <div class="adm_info_string">
                            <div class="adm_info_title">
                                <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Admin_Access'); ?>
                            </div>
                            <div class="adm_info_content">
                                <?= ($Admins['end'] == 0) ? $Translate->get_translate_phrase('_Forever') : ($Admins['end'] <= time() ? $Translate->get_translate_module_phrase('module_page_profiles', '_Expired') : date('d.m.Y', $Admins['end'])); ?>
                            </div>
                        </div>
                        <div class="adm_info_string">
                            <div class="adm_info_title">
                                <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Admin_Immunity'); ?>
                            </div>
                            <div class="adm_info_content">
                                <?php if ((!empty($Db->db_data['IksAdmin']) || !empty($Db->db_data['IksAdminNew']) || !empty($Db->db_data['AdminSystem'])) && in_array($Player->lws['server_sb'][0], ['IksAdmin', 'IksAdminNew', 'AdminSystem'])) {
                                    if ($Admins['immunity'] == -1 || $Admins['immunity'] == 0 || $Admins['immunity'] == null) {
                                        foreach ($Groups as $key) {
                                            if ($Admins['group_id'] == $key['id']) {
                                                echo $key['immunity'];
                                            }
                                        }
                                    } else {
                                        echo $Admins['immunity'];
                                    }
                                } elseif (!empty($Db->db_data['SourceBans']) && $Player->lws['server_sb'][0] == 'SourceBans') {
                                    echo $Admins['immunity'];
                                } ?>
                            </div>
                        </div>
                    </div>
                    <div class="title_head">
                        <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Admin_activity'); ?>
                    </div>
                    <div class="adm_content">
                        <div class="adm_info_string">
                            <div class="adm_info_title">
                                <?= $Translate->get_translate_module_phrase('module_page_profiles', '_BansGiven'); ?>
                            </div>
                            <div class="adm_info_content bans_count"
                                data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_numberBans7d'); ?>"
                                data-tippy-placement="right"><?= $Admins['bans_count'] ?>
                                <span>(+<?= $Admins['7d_bans_count'] ?? 0 ?>)
                                    <svg x="0" y="0" viewBox="0 0 100 100" xml:space="preserve">
                                        <g>
                                            <path
                                                d="M50 2.5C23.83 2.5 2.5 23.78 2.5 50c0 26.17 21.33 47.5 47.5 47.5S97.5 76.17 97.5 50C97.5 23.78 76.17 2.5 50 2.5zm.013 76a4.75 4.75 0 1 1 0-9.5 4.75 4.75 0 1 1 0 9.5zm4.75-19.608v.62a4.75 4.75 0 1 1-9.5 0v-5.076A4.436 4.436 0 0 1 49.699 50c4.442 0 8.514-2.915 9.534-7.238C60.693 36.578 55.957 31 50 31c-4.148 0-7.704 2.705-8.988 6.435-.643 1.865-2.465 3.065-4.438 3.065h-.01c-3.23 0-5.578-3.176-4.519-6.227C34.625 26.84 41.693 21.5 50 21.5c10.463 0 19 8.512 19 19 0 8.841-6.08 16.29-14.237 18.392z">
                                            </path>
                                        </g>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="adm_info_string">
                            <div class="adm_info_title">
                                <?= $Translate->get_translate_module_phrase('module_page_profiles', '_CommsGiven'); ?>
                            </div>
                            <div class="adm_info_content">
                                <?= $Admins['mutes_count'] ?? 0 ?>/<?= $Admins['gags_count'] ?? 0 ?>
                            </div>
                        </div>
                        <?php if (file_exists(MODULES . 'module_page_reports/description.json')): ?>
                            <div class="adm_info_string">
                                <div class="adm_info_title">
                                    <?= $Translate->get_translate_module_phrase('module_page_profiles', '_reportsReviewed'); ?>
                                </div>
                                <div class="adm_info_content bans_count"
                                    data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_numbersReportsLast7Days'); ?>"
                                    data-tippy-placement="right"><?= $Reports['total_count'] ?>
                                    <span>(+<?= $Reports['7d_count'] ?? 0 ?>)
                                        <svg x="0" y="0" viewBox="0 0 100 100" xml:space="preserve">
                                            <g>
                                                <path
                                                    d="M50 2.5C23.83 2.5 2.5 23.78 2.5 50c0 26.17 21.33 47.5 47.5 47.5S97.5 76.17 97.5 50C97.5 23.78 76.17 2.5 50 2.5zm.013 76a4.75 4.75 0 1 1 0-9.5 4.75 4.75 0 1 1 0 9.5zm4.75-19.608v.62a4.75 4.75 0 1 1-9.5 0v-5.076A4.436 4.436 0 0 1 49.699 50c4.442 0 8.514-2.915 9.534-7.238C60.693 36.578 55.957 31 50 31c-4.148 0-7.704 2.705-8.988 6.435-.643 1.865-2.465 3.065-4.438 3.065h-.01c-3.23 0-5.578-3.176-4.519-6.227C34.625 26.84 41.693 21.5 50 21.5c10.463 0 19 8.512 19 19 0 8.841-6.08 16.29-14.237 18.392z">
                                                </path>
                                            </g>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($CheckCheats)): ?>
                            <div class="adm_info_string">
                                <div class="adm_info_title">
                                    <?= $Translate->get_translate_module_phrase('module_page_profiles', '_checks'); ?>
                                </div>
                                <div class="adm_info_content bans_count"
                                    data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_numbersChecksLast7Days'); ?>"
                                    data-tippy-placement="right"><?= $CheckCheats['checkcheats'] ?>
                                    <span>(+<?= $CheckCheats['checkcheats_7d'] ?? 0 ?>)
                                        <svg x="0" y="0" viewBox="0 0 100 100" xml:space="preserve">
                                            <g>
                                                <path
                                                    d="M50 2.5C23.83 2.5 2.5 23.78 2.5 50c0 26.17 21.33 47.5 47.5 47.5S97.5 76.17 97.5 50C97.5 23.78 76.17 2.5 50 2.5zm.013 76a4.75 4.75 0 1 1 0-9.5 4.75 4.75 0 1 1 0 9.5zm4.75-19.608v.62a4.75 4.75 0 1 1-9.5 0v-5.076A4.436 4.436 0 0 1 49.699 50c4.442 0 8.514-2.915 9.534-7.238C60.693 36.578 55.957 31 50 31c-4.148 0-7.704 2.705-8.988 6.435-.643 1.865-2.465 3.065-4.438 3.065h-.01c-3.23 0-5.578-3.176-4.519-6.227C34.625 26.84 41.693 21.5 50 21.5c10.463 0 19 8.512 19 19 0 8.841-6.08 16.29-14.237 18.392z">
                                                </path>
                                            </g>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (file_exists(MODULES . 'module_page_managersystem/description.json')): ?>
                            <div class="adm_info_string">
                                <div class="adm_info_title">
                                    <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Admin_Warns'); ?>
                                </div>
                                <div class="adm_info_content">
                                    <?= count($Warns) ?>/<?php $WarnSettings = require MODULES . 'module_page_managersystem/assets/cache/settings.php';
                                                            echo $WarnSettings['count_warn'] ?? 3 ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (file_exists(MODULES . 'module_page_managersystem/description.json')):
                        if ($WarnCount != 0): ?>
                            <div class="title_head">
                                <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Warns_List'); ?>
                            </div>
                            <div class="adm_content">
                                <?php foreach ($Warns as $key): ?>
                                    <div class="adm_warn_content">
                                        <?php if ($key['time'] < time()) {
                                            echo $Translate->get_translate_module_phrase('module_page_profiles', '_Expired');
                                        } else {
                                            echo $Translate->get_translate_module_phrase('module_page_profiles', '_valid') . $Modules->action_time_exchange_exact($key['time'] - time());
                                        } ?> | <?= action_text_clear($key['reason']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                    <?php endif;
                    endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>