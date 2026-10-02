<div class="col-md-2">
    <div class="stats_general_block">
        <div class="stats_total_players"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_realPlayers') ?>
            <span><?= !empty($Db->db_data['LevelsRanks']) ? $Admin->InfoStatsCached()['CountPlayers'] : 0; ?></span>
            <svg>
                <use href="/resources/img/sprite.svg#two-users"></use>
            </svg>
        </div>
        <div class="stats_players_day"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_players7Days') ?>
            <span><?= !empty($Db->db_data['LevelsRanks']) ? $Admin->InfoStats()['CountPlayers7d'] : 0; ?></span>
            <svg>
                <use href="/resources/img/sprite.svg#calendare7"></use>
            </svg>
        </div>
        <div class="stats_players_day"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Stats_24h_players') ?>
            <span><?= !empty($Db->db_data['LevelsRanks']) ? $Admin->InfoStats()['CountPlayers24'] : 0; ?></span>
            <svg>
                <use href="/resources/img/sprite.svg#timer"></use>
            </svg>
        </div>
        <div class="stats_comms stats_new">
            <div><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_newPlayers7Dys') ?></div>
            <div class="stats-value-line">
                <span><?= !empty($Admin->InfoStats()['CountNewPlayers7d']) ? $Admin->InfoStats()['CountNewPlayers7d'] : 0; ?></span>
                <span class="per-seven-days" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_last24Hours') ?>" data-tippy-placement="top">+ <?= !empty($Admin->InfoStats()['CountNewPlayers']) ? $Admin->InfoStats()['CountNewPlayers'] : 0; ?></span>
            </div>
            <svg>
                <use href="/resources/img/sprite.svg#new-users"></use>
            </svg>
        </div>
    </div>
</div>
<div class="col-md-2">
    <div class="stats_general_block">
        <div class="stats_admins">
            <div><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Stats_admins') ?></div>
            <span><?= (!empty($Db->db_data['IksAdmin']) || !empty($Db->db_data['IksAdminNew']) || !empty($Db->db_data['AdminSystem'])) ? $Admin->InfoStatsCached()['CountAdmins'] : 0; ?></span></span>
            <svg>
                <use href="/resources/img/sprite.svg#shiedl-bold"></use>
            </svg>
        </div>
        <div class="stats_vips">
            <div><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Stats_vips') ?></div>
            <span><?= !empty($Db->db_data['Vips']) ? $Admin->InfoStatsCached()['CountVip'] : 0; ?></span>
            <svg>
                <use href="/resources/img/sprite.svg#diamond"></use>
            </svg>
        </div>
        <div class="stats_bans">
            <div><?= $Translate->get_translate_phrase('_BansCount') ?></div>
            <?php if (!empty($Db->db_data['IksAdmin']) || !empty($Db->db_data['IksAdminNew']) || !empty($Db->db_data['AdminSystem'])): ?>
                <div class="stats-value-line">
                    <span><?= $Admin->InfoStatsCached()['CountBans'] ?></span>
                    <span class="per-seven-days" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_overLast7Days') ?>" data-tippy-placement="top">+ <?= $Admin->InfoStatsCached()['CountBans7d'] ?></span>
                </div>
            <?php else: ?>
                <span>0</span>
            <?php endif; ?>
            <svg>
                <use href="/resources/img/sprite.svg#block"></use>
            </svg>
        </div>
        <div class="stats_comms">
            <div><?= $Translate->get_translate_phrase('_CommsCount') ?></div>
            <?php if (!empty($Db->db_data['IksAdmin']) || !empty($Db->db_data['IksAdminNew']) || !empty($Db->db_data['AdminSystem'])): ?>
                <div class="stats-value-line">
                    <span><?= $Admin->InfoStatsCached()['CountMutes'] ?></span>
                    <span class="per-seven-days" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_overLast7Days') ?>" data-tippy-placement="top">+ <?= $Admin->InfoStatsCached()['CountMutes7d'] ?></span>
                </div>
            <?php else: ?>
                <span>0</span>
            <?php endif; ?>
            <svg>
                <use href="/resources/img/sprite.svg#mute"></use>
            </svg>
        </div>
    </div>
</div>
<div class="col-md-2">
    <div class="stats_general_block">
        <div class="stats_admins">
            <div><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_totalReports') ?></div>
            <?php if (!empty($Db->db_data['Reports'])): ?>
                <div class="stats-value-line">
                    <span><?= $Admin->InfoStatsCached()['CountReports'] ?></span>
                    <span class="per-seven-days" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_overLast7Days') ?>" data-tippy-placement="top">+ <?= $Admin->InfoStatsCached()['CountReports7d'] ?></span>
                </div>
            <?php else: ?>
                <span>0</span>
            <?php endif; ?>
            <svg>
                <use href="/resources/img/sprite.svg#report-list"></use>
            </svg>
        </div>
        <div class="stats_vips">
            <?php if (file_exists(MODULES . 'module_page_tickets/description.json')): ?>
                <div><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_totalTickets') ?></div>
            <?php else: ?>
                <div><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_totalRequests') ?></div>
            <?php endif; ?>
            <?php if (file_exists(MODULES . 'module_page_tickets/description.json')): ?>
                <div class="stats-value-line">
                    <span><?= $Admin->InfoStatsCached()['CountTickets'] ?></span>
                    <span class="per-seven-days" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_overLast7Days') ?>" data-tippy-placement="top">+ <?= $Admin->InfoStatsCached()['CountTickets7d'] ?></span>
                </div>
            <?php elseif (!empty($Db->db_data['request'])): ?>
                <div class="stats-value-line">
                    <span><?= $Admin->InfoStatsCached()['CountRequests'] ?></span>
                    <span class="per-seven-days" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_overLast7Days') ?>" data-tippy-placement="top">+ <?= $Admin->InfoStatsCached()['CountRequests7d'] ?></span>
                </div>
            <?php else: ?>
                <span>0</span>
            <?php endif; ?>
            <svg>
                <use href="/resources/img/sprite.svg#request-list"></use>
            </svg>
        </div>
        <div class="stats_bans">
            <div><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_checksCheats') ?></div>
            <?php if (!empty($Db->db_data['Check'])): ?>
                <div class="stats-value-line">
                    <span><?= $Admin->InfoStatsCached()['CountCheckCheats'] ?> </span>
                    <span class="per-seven-days" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_overLast7Days') ?>" data-tippy-placement="top">+ <?= $Admin->InfoStatsCached()['CountCheckCheats7d'] ?></span>
                </div>
            <?php else: ?>
                <span>0</span>
            <?php endif; ?>
            <svg>
                <use href="/resources/img/sprite.svg#check-circle"></use>
            </svg>
        </div>
        <div class="stats_comms">
            <div><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_verifyedPlayers') ?></div>
            <span><?= !empty($Db->db_data['Core']) ? $Admin->InfoStatsCached()['CountVerifications'] : 0; ?></span>
            <svg>
                <use href="/resources/img/sprite.svg#verify"></use>
            </svg>
        </div>
    </div>
</div>
<div class="col-md-2">
    <div class="stats_general_block">
        <div class="stats_admins stats_discord">
            <div><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_dsPlayers') ?></div>
            <span><?= $Admin->InfoStatsCached()['CountDiscord'] ?></span>
            <svg>
                <use href="/resources/img/sprite.svg#ds"></use>
            </svg>
        </div>
        <div class="stats_vips stats_vkontakte">
            <div><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_vkPlayers') ?></div>
            <span><?= $Admin->InfoStatsCached()['CountVk'] ?></span>
            <svg>
                <use href="/resources/img/sprite.svg#vk"></use>
            </svg>
        </div>
        <div class="stats_bans stats_telegram">
            <div><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_tgPlayers') ?></div>
            <span><?= $Admin->InfoStatsCached()['CountTelegram'] ?></span>
            <svg>
                <use href="/resources/img/sprite.svg#tg"></use>
            </svg>
        </div>
        <div class="stats_all_cash">
            <div class="select__period">
                <div class="selected__text"><span><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_currentMounth') ?></span></div>
                <div class="select__list">
                    <span class="select__item" data-id="current_month"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_currentMounth') ?></span>
                    <span class="select__item" data-id="prev_month"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_overPastMonth') ?></span>
                    <span class="select__item" data-id="3months"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_in3Month') ?></span>
                    <span class="select__item" data-id="6months"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_in6Mouth') ?></span>
                    <span class="select__item" data-id="alltime"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Stats_total_earned') ?></span>
                </div>
            </div>
            <span id="current_month" class="money__value"><span class="text-blurred"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_showSum') ?></span><?php if (!empty($Db->db_data['lk'])) {
                                                                                                                                                                                    if ($Admin->InfoStatsCached()['MoneyMonth']['current_month']) {
                                                                                                                                                                                        echo $Admin->InfoStatsCached()['MoneyMonth']['current_month'];
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo '0';
                                                                                                                                                                                    }
                                                                                                                                                                                } else {
                                                                                                                                                                                    echo '—';
                                                                                                                                                                                } ?> <?= $General->currency ?></span>
            <span id="prev_month" class="hidden__summ money__value"><span class="text-blurred"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_showSum') ?></span><?php if (!empty($Db->db_data['lk'])) {
                                                                                                                                                                                                if ($Admin->InfoStatsCached()['MoneyMonth']['prev_month']) {
                                                                                                                                                                                                    echo $Admin->InfoStatsCached()['MoneyMonth']['prev_month'];
                                                                                                                                                                                                } else {
                                                                                                                                                                                                    echo '0';
                                                                                                                                                                                                }
                                                                                                                                                                                            } else {
                                                                                                                                                                                                echo '—';
                                                                                                                                                                                            } ?> <?= $General->currency ?></span>
            <span id="3months" class="hidden__summ money__value"><span class="text-blurred"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_showSum') ?></span><?php if (!empty($Db->db_data['lk'])) {
                                                                                                                                                                                            if ($Admin->InfoStatsCached()['MoneyMonth']['3months']) {
                                                                                                                                                                                                echo $Admin->InfoStatsCached()['MoneyMonth']['3months'];
                                                                                                                                                                                            } else {
                                                                                                                                                                                                echo '0';
                                                                                                                                                                                            }
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo '—';
                                                                                                                                                                                        } ?> <?= $General->currency ?></span>
            <span id="6months" class="hidden__summ money__value"><span class="text-blurred"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_showSum') ?></span><?php if (!empty($Db->db_data['lk'])) {
                                                                                                                                                                                            if ($Admin->InfoStatsCached()['MoneyMonth']['6months']) {
                                                                                                                                                                                                echo $Admin->InfoStatsCached()['MoneyMonth']['6months'];
                                                                                                                                                                                            } else {
                                                                                                                                                                                                echo '0';
                                                                                                                                                                                            }
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo '—';
                                                                                                                                                                                        } ?> <?= $General->currency ?></span>
            <span id="alltime" class="hidden__summ money__value"><span class="text-blurred"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_showSum') ?></span><?php if (!empty($Db->db_data['lk'])) {
                                                                                                                                                                                            if ($Admin->InfoStatsCached()['Money']['cash_summ']) {
                                                                                                                                                                                                echo $Admin->InfoStatsCached()['Money']['cash_summ'];
                                                                                                                                                                                            } else {
                                                                                                                                                                                                echo '0';
                                                                                                                                                                                            }
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo '—';
                                                                                                                                                                                        } ?> <?= $General->currency ?></span>
        </div>
    </div>
</div>
<div class="col-md-4">
    <div class="stats_general_block" style="height: 100%;">
        <div class="stats_visits" style="justify-content: flex-start;">
            <div class="visit_top">
                <div class="visit_title"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Stats_visit_30d') ?></div>
                <div class="visit_count"><?= $Admin->InfoStats()['CountVisit']['visits'] ?> <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Stats_visit_humans') ?></div>
            </div>
            <div class="visit_online">
                <div class="visit_online_title">
                    <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Stats_visit_online') ?>
                    <span id="admin_online_count"></span>
                </div>
                <div class="visit_user_list no-scrollbar" id="admin_online_list"></div>
            </div>
        </div>
    </div>
</div>