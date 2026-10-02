<div class="card at__card at__dash">
    <div class="at__dash-grid cols-4">
        <?php if ($AccessController->checkPermission('admins.view')): ?>
            <div class="at__stat accent-blue">
                <div class="at__stat-head">
                    <svg>
                        <use href="/resources/img/sprite.svg#policeman"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statAdmins') ?>
                </div>
                <div class="at__stat-value"><?= (int) $dash['admin_count'] ?> <small><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_peopleShort') ?></small></div>
                <div class="at__stat-foot">
                    <div class="at__stat-split">
                        <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statTemporary') ?></span>
                        <span class="at__kpi-value"><?= (int) $dash['temporary_admin_count'] ?></span>
                    </div>
                    <div class="at__stat-bar blue"><span style="width: <?= (float) $dash['temporary_admin_percent'] ?>%"></span></div>
                </div>
            </div>
        <?php endif; if ($AccessController->checkPermission('privileges.view')): ?>
            <div class="at__stat accent-vip">
                <div class="at__stat-head">
                    <svg>
                        <use href="/resources/img/sprite.svg#diamond"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statVipPlayers') ?>
                </div>
                <div class="at__stat-value"><?= (int) $dash['vip_count'] ?><small><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_peopleShort') ?></small></div>
                <div class="at__stat-foot">
                    <div class="at__stat-split">
                        <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statForever') ?></span>
                        <span class="at__kpi-value"><?= (int) $dash['vip_forever_count'] ?> <small class="at__kpi-small">(<?= number_format((float) $dash['vip_forever_percent'], 2) ?>%)</small></span>
                    </div>
                    <div class="at__stat-bar vip"><span style="width: <?= (float) $dash['vip_forever_percent'] ?>%"></span></div>
                </div>
            </div>
        <?php endif; if ($AccessController->checkPermission('punishments.view')): ?>
            <div class="at__stat accent-red">
                <div class="at__stat-head">
                    <svg>
                        <use href="/resources/img/sprite.svg#block"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statBans') ?>
                </div>
                <div class="at__stat-value"><?= (int) $dash['ban_count'] ?><small><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_piecesShort') ?></small></div>
                <div class="at__stat-foot">
                    <div class="at__stat-split">
                        <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statActive') ?></span>
                        <span class="at__kpi-value"><?= (int) $dash['active_ban_count'] ?><small class="at__kpi-small"> (<?= number_format((float) $dash['ban_active_percent'], 2) ?>%)</small></span>
                    </div>
                    <div class="at__stat-bar red"><span style="width: <?= (float) $dash['ban_active_percent'] ?>%"></span></div>
                </div>
            </div>
            <div class="at__stat accent-span">
                <div class="at__stat-head">
                    <svg>
                        <use href="/resources/img/sprite.svg#micro-slash"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statMutes') ?>
                </div>
                <div class="at__stat-value"><?= (int) $dash['mute_count'] ?><small><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_piecesShort') ?></small></div>
                <div class="at__stat-foot">
                    <div class="at__stat-split">
                        <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statActive') ?></span>
                        <span class="at__kpi-value"><?= (int) $dash['active_mute_count'] ?><small class="at__kpi-small"> (<?= number_format((float) $dash['mute_active_percent'], 2) ?>%)</small></span>
                    </div>
                    <div class="at__stat-bar span"><span style="width: <?= (float) $dash['mute_active_percent'] ?>%"></span></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($AccessController->checkPermission('checks.view') || !empty($dash['reports_available']) || (!empty($dash['violators_day_available']) && $AccessController->checkPermission('punishments.view')) || ($AccessController->checkPermission('finances.view') && !empty($dash['revenue_available']))): ?>
        <div class="at__dash-grid cols-4">
            <?php if ($AccessController->checkPermission('checks.view')): ?>
                <div class="at__stat accent-green">
                    <div class="at__stat-head">
                        <svg>
                            <use href="/resources/img/sprite.svg#check-circle"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statChecks') ?>
                    </div>
                    <div class="at__stat-value"><?= (int) $dash['check_count'] ?><small><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_piecesShort') ?></small></div>
                    <div class="at__stat-foot">
                        <div class="at__stat-split">
                            <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statLast30Days') ?></span>
                            <span class="at__kpi-value at__stat-trend up">+<?= (int) $dash['check_count_30'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; if (!empty($dash['reports_available'])): ?>
                <div class="at__stat accent-blue">
                    <div class="at__stat-head">
                        <svg>
                            <use href="/resources/img/sprite.svg#list-info"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statReports') ?>
                    </div>
                    <div class="at__stat-value"><?= (int) $dash['report_count'] ?><small><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_piecesShort') ?></small></div>
                    <div class="at__stat-foot">
                        <div class="at__stat-split">
                            <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statReviewed') ?></span>
                            <span class="at__kpi-value"><?= (int) $dash['report_reviewed_count'] ?><small class="at__kpi-small"> (<?= number_format((float) $dash['report_reviewed_percent'], 2) ?>%)</small></span>
                        </div>
                        <div class="at__stat-bar blue"><span style="width: <?= (float) $dash['report_reviewed_percent'] ?>%"></span></div>
                    </div>
                </div>
            <?php endif; if (!empty($dash['violators_day_available']) && $AccessController->checkPermission('punishments.view')): ?>
                <div class="at__stat accent-red">
                    <div class="at__stat-head">
                        <svg>
                            <use href="/resources/img/sprite.svg#user-block"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statViolatorsDay') ?>
                    </div>
                    <div class="at__stat-value"><?= (int) $dash['punished_day_count'] ?><small>/ <?= (int) $dash['players_day_count'] ?> <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_peopleShort') ?></small></div>
                    <div class="at__stat-foot">
                        <div class="at__stat-split">
                            <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statPunishedPercent') ?></span>
                            <span class="at__kpi-value"><?= number_format((float) $dash['violators_day_percent'], 2) ?>%</span>
                        </div>
                        <div class="at__stat-bar red"><span style="width: <?= (float) $dash['violators_day_percent'] ?>%"></span></div>
                    </div>
                </div>
            <?php endif; if ($AccessController->checkPermission('finances.view') && !empty($dash['revenue_available'])): ?>
                <div class="at__stat accent-gold">
                    <div class="at__stat-head">
                        <svg>
                            <use href="/resources/img/sprite.svg#wallet"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statRevenueWeek') ?>
                    </div>
                    <div class="at__stat-value"><?= $dash['revenue_week_formatted'] ?><small><?= $General->currency ?></small></div>
                    <div class="at__stat-foot">
                        <div class="at__stat-split">
                            <span><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_statRevenueWeekCompare') ?></span>
                            <span class="at__stat-trend <?= !empty($dash['revenue_trend_up']) ? 'up' : 'down' ?>"><?= $dash['revenue_trend_label'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($AccessController->checkPermission('punishments.view')): ?>
        <div class="at__dash-grid cols-2">
            <div class="at__dash-card">
                <div class="at__dash-card-head">
                    <h2>
                        <svg>
                            <use href="/resources/img/sprite.svg#lock"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_chartBansDynamics') ?>
                    </h2>
                    <div class="at__dash-card-actions at__top-tabs" id="bansChartTabs">
                        <button type="button" class="filter active" data-range="7"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_days7') ?></button>
                        <button type="button" class="filter" data-range="30"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_days30') ?></button>
                        <button type="button" class="filter" data-range="90"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_days90') ?></button>
                    </div>
                </div>
                <div class="at__dash-card-body">
                    <div class="at__chart-legend">
                        <div class="at__legend-item"><span class="at__legend-dot red"></span> <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_chartIssued') ?></div>
                        <div class="at__legend-item"><span class="at__legend-dot green"></span> <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_chartRemoved') ?></div>
                    </div>
                    <div id="chartBans" class="at__chart skeleton--default"></div>
                </div>
            </div>
            <div class="at__dash-card">
                <div class="at__dash-card-head">
                    <h2>
                        <svg>
                            <use href="/resources/img/sprite.svg#micro-slash"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_chartMutesDynamics') ?>
                    </h2>
                    <div class="at__dash-card-actions at__top-tabs" id="mutesChartTabs">
                        <button type="button" class="filter active" data-range="7"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_days7') ?></button>
                        <button type="button" class="filter" data-range="30"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_days30') ?></button>
                        <button type="button" class="filter" data-range="90"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_days90') ?></button>
                    </div>
                </div>
                <div class="at__dash-card-body">
                    <div class="at__chart-legend">
                        <div class="at__legend-item"><span class="at__legend-dot span"></span> <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_chartMute') ?></div>
                        <div class="at__legend-item"><span class="at__legend-dot blue"></span> <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_chartGag') ?></div>
                    </div>
                    <div id="chartMutes" class="at__chart skeleton--default"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($topMetrics)): ?>
        <div class="at__dash-card">
            <div class="at__dash-card-head">
                <h2>
                    <svg>
                        <use href="/resources/img/sprite.svg#policeman"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_topAdminsMonth') ?>
                </h2>
                <?php if (count($topMetrics) > 1): ?>
                    <div class="at__dash-card-actions at__top-tabs" id="topAdminsTabs">
                        <?php foreach ($topMetrics as $meta): ?>
                            <button type="button" class="filter<?= $meta['active'] ? ' active' : '' ?>" data-metric="<?= $meta['metric'] ?>">
                                <svg>
                                    <use href="/resources/img/sprite.svg#<?= $meta['icon'] ?>"></use>
                                </svg> <?= $meta['label'] ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="at__dash-card-body">
                <div class="at__dash-grid">
                    <?php foreach ($topMetrics as $meta): ?>
                        <ul class="at__top at__top-admins-panel" id="topAdminsList-<?= $meta['metric'] ?>" data-metric="<?= $meta['metric'] ?>"<?= !empty($meta['active']) ? '' : ' style="display: none"' ?>>
                            <?php if ($topAdminsByMetric[$meta['metric']] == []): ?>
                                <li class="at__top-empty">
                                    <div class="no-data"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_noAdmins') ?></div>
                                </li>
                            <?php else: ?>
                                <?php foreach ($topAdminsByMetric[$meta['metric']] as $index => $item): ?>
                                    <li class="at__top-item">
                                        <div class="at__top-rank">#<?= $index + 1 ?></div>
                                        <img class="at__top-avatar" src="<?= $item['avatar'] ?>" alt="">
                                        <div class="at__top-name">
                                            <a href="/profiles/<?= $item['steamid'] ?>/?search=1" target="_blank"><?= $item['name'] ?></a>
                                            <small><?= $item['steamid'] ?></small>
                                        </div>
                                        <span class="at__top-value">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#<?= $meta['icon'] ?>"></use>
                                            </svg>
                                            <?= $item['count'] ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>