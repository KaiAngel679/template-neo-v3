<script>
    var monSettings = <?= json_encode($monSettings, JSON_UNESCAPED_UNICODE) ?>;
    var mon_mods_config = <?= json_encode(!empty($Mon->Settings['enable_mods']) ? $Mon->getModsConfigForJs() : [], JSON_UNESCAPED_UNICODE) ?>;
    <?php if (!empty($Mon->Settings['show_ping'])): ?>
        var monUserGeo = <?= json_encode($Mon->getUserGeo(), JSON_UNESCAPED_UNICODE) ?>;
    <?php endif; ?>
</script>
<?php if (!empty($Mon->Settings['enable_mods'])): ?>
    <div class="row" id="rowModsServers">
        <div class="col-md-12">
            <div class="mods__wrapper mods__row-<?= $Mon->Settings['grid_counts'] ?? '6' ?> <?= $Mon->Settings['centered_mods'] == 1 ? 'mods__centered' : '' ?>">
                <?php foreach ($Mon->getModsList() as $mod): ?>
                    <div class="mods__card" data-mod="<?= $mod['name'] ?>">
                        <?php if (!empty($Mon->Settings['server_counter'])): ?>
                            <div class="mods__servers-counter">
                                <svg>
                                    <use href="/resources/img/sprite.svg#servers"></use>
                                </svg>
                                <span class="mods__servers-count preloader-size">
                                    <svg class="ping-loader" viewBox="0 0 50 50" aria-hidden="true" style="margin-right: 0;">
                                        <circle class="ping-loader__track" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                                        <circle class="ping-loader__path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke-linecap="round"></circle>
                                    </svg>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div class="mods__info">
                            <div class="mods__title"><?= $mod['name'] ?></div>
                            <div class="mods__online">
                                <div class="ring-online" aria-hidden="true"></div>
                                <span id="<?= $mod['name'] ?>_online">
                                    <svg class="ping-loader" viewBox="0 0 50 50" aria-hidden="true" style="margin-right: 0;">
                                        <circle class="ping-loader__track" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                                        <circle class="ping-loader__path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke-linecap="round"></circle>
                                    </svg>
                                </span> <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_InGameMod') ?>
                            </div>
                        </div>
                        <?php if (!empty($mod['image_2'])): ?>
                            <img class="lazy mods__second-image" data-src="<?= '/app/modules/module_block_main_servers/assets/img/mods/' . $mod['image_2'] ?>" alt="">
                        <?php endif; ?>
                        <img class="lazy mods__first-image" <?= empty($mod['image_2']) ? 'style="opacity: 0.6;"' : '' ?> data-src="<?= !empty($mod['image_1']) ? '/app/modules/module_block_main_servers/assets/img/mods/' . $mod['image_1'] : '/app/modules/module_block_main_servers/assets/img/emptys/empty.webp' ?>" alt="">
                        <div class="mods__shadow"></div>
                        <div class="mod__bottom-info">
                            <div class="mod__desc-text"><?= $mod['description'] ?></div>
                            <?php if (empty($Mon->Settings['mon_button'])): ?>
                                <button class="mods__button-search active filter">
                                    <svg>
                                        <use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#play-triangle"></use>
                                    </svg>
                                    <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_fastGame') ?>
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
                <?php for ($i = 0; $i < $Mon->getPlugsModsCount(); ++$i): ?>
                    <div class="mods__card servers__card-block-empty"></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row" id="rowModFilter" style="<?= empty($Mon->Settings['enable_mods']) ? '' : 'display:none;' ?>">
    <div class="col-md-12">
        <div class="servers_filter">
            <div class="filter_chips">
                <?php if (!empty($Mon->Settings['enable_mods'])): ?>
                    <div class="mods__back button filter">
                        <svg>
                            <use href="/resources/img/sprite.svg#single-chevrone-left"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_Back') ?>
                    </div>
                <?php endif; ?>
                <button class="filter active mode" data-mode="All">
                    <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_AllMod') ?>
                    <span id="currentOnline" class="mode-counter-number">
                        <svg class="ping-loader" viewBox="0 0 50 50" aria-hidden="true" style="margin-right: 0;">
                            <circle class="ping-loader__track" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                            <circle class="ping-loader__path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke-linecap="round"></circle>
                        </svg>
                    </span>
                </button>
                <?php if (!empty($Mon->Settings['enable_mods'])): ?>
                    <?php foreach ($Mon->getModsList() as $mod): ?>
                        <button class="filter mode" data-mode="<?= $mod['name'] ?>">
                            <?= $mod['name'] ?>
                            <span id="<?= $mod['name'] ?>_filterOnline" class="mode-counter-number">
                                <svg class="ping-loader" viewBox="0 0 50 50" aria-hidden="true" style="margin-right: 0;">
                                    <circle class="ping-loader__track" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                                    <circle class="ping-loader__path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke-linecap="round"></circle>
                                </svg>
                            </span>
                        </button>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php $added_modes = array();
                    foreach ($General->server_list as $server):
                        $server_mode = $server['server_mod'];
                        if (!in_array($server_mode, $added_modes)):
                            array_push($added_modes, $server_mode); ?>
                            <button class="filter mode" data-mode="<?= $server_mode; ?>">
                                <?= $server_mode; ?>
                                <span id="<?= $server_mode ?>_filterOnline" class="mode-counter-number">
                                    <svg class="ping-loader" viewBox="0 0 50 50" aria-hidden="true" style="margin-right: 0;">
                                        <circle class="ping-loader__track" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                                        <circle class="ping-loader__path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke-linecap="round"></circle>
                                    </svg>
                                </span>
                                <span class="mode-about" data-tippy-content="<?= $Translate->get_translate_phrase($General->mods[$server_mode] ?? '') ?>" data-tippy-placement="top">?</span>
                            </button>
                    <?php endif;
                    endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="servers_filter-data">
                <span class="servers_filter-update-button" id="updateservers">
                    <svg>
                        <use href="/resources/img/sprite.svg#spinner"></use>
                    </svg>
                </span>
            </div>
        </div>
        <?php if (!empty($Mon->Settings['enable_mods'])): ?>
            <div class="modFilter__wrapper">
                <div class="modFilter__banner-container">
                    <video class="modFilter__video" playsinline loop preload autoplay muted></video>
                    <div class="modFilter__content">
                        <div class="modFilter__title"></div>
                        <div class="modFilter__description"></div>
                        <button class="active" id="modFilterFastSearch">
                            <svg>
                                <use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#play-triangle"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_fastGame') ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="modFilter__filter-wrapper">
            <div class="adaptive-select-wrapper" data-change-icon>
                <ul class="adaptive-select__dropdown-list" id="showFilter">
                    <li>
                        <label class="adaptive-select__label" for="filterScreeningGrid">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#grid-blocks"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_filterGrids') ?></div>
                            <input class="hide-input" id="filterScreeningGrid" type="radio" name="filter-screening" value="grid" <?= $monType == 0 ? 'checked' : '' ?>>
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="filterScreeningTable">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#list"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_filterTable') ?></div>
                            <input class="hide-input" id="filterScreeningTable" type="radio" name="filter-screening" value="table" <?= $monType == 1 ? 'checked' : '' ?>>
                        </label>
                    </li>
                </ul>
                <div class="adaptive-select" open-select="showFilter">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#replace"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text">
                        <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_filterGrids') ?>
                    </span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <?php if (!empty($Mon->Settings['enable_mods'])): ?>
                <div class="adaptive-select-wrapper" data-no-text>
                    <ul class="adaptive-select__dropdown-list" id="filterCategories"></ul>
                    <div class="adaptive-select" open-select="filterCategories">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#layers"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_subModesFilters') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="adaptive-select-wrapper" data-no-text>
                <ul class="adaptive-select__dropdown-list" id="filterMap"></ul>
                <div class="adaptive-select" open-select="filterMap">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#badge-star"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_filterMaps') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="adaptive-select-wrapper" data-no-text>
                <ul class="adaptive-select__dropdown-list" id="filterLocation"></ul>
                <div class="adaptive-select" open-select="filterLocation">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#geo"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_filterLocation') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="inputs-inline modFilter__unset">
                <input type="checkbox" id="filterHideEmptys" class="switch">
                <label for="filterHideEmptys"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_filterHideEmptys') ?></label>
            </div>
            <div class="inputs-inline modFilter__unset">
                <input type="checkbox" id="filterFavourites" class="switch">
                <label for="filterFavourites"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_filterFavourites') ?></label>
            </div>
            <div class="adaptive-select-wrapper modFilter__last" data-change-icon>
                <ul class="adaptive-select__dropdown-list" id="filterSorting">
                    <li>
                        <label class="adaptive-select__label" for="filterSorting-0">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#arrow-up-down"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_sortingDefault') ?></div>
                            <input class="hide-input" id="filterSorting-0" type="radio" name="filter-map" value="default">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="filterSorting-1">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#filterUp"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_sortingUp') ?></div>
                            <input class="hide-input" id="filterSorting-1" type="radio" name="filter-map" value="down">
                        </label>
                    </li>
                    <li>
                        <label class="adaptive-select__label" for="filterSorting-2">
                            <span class="adaptive-select__icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#filterDown"></use>
                                </svg>
                            </span>
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_sortingDown') ?></div>
                            <input class="hide-input" id="filterSorting-2" type="radio" name="filter-map" value="up">
                        </label>
                    </li>
                </ul>
                <div class="adaptive-select" open-select="filterSorting">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#filters"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_filterSorting') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <?php if (!empty($_SESSION['steamid']) && isset($_SESSION['user_admin'])): ?>
                <a href="/monitoring/" class="button-icon button">
                    <svg>
                        <use href="/resources/img/sprite.svg#gear"></use>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row" id="rowServers" style="<?= empty($Mon->Settings['enable_mods']) ? '' : 'display:none;' ?>">
    <div class="col-md-12">
        <div class="subMod" id="subModBlock" style="display:none;">
            <span class="subMod__title" id="subModTitle"></span>
            <div class="subMod__counter"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_PlayersInGame') ?>: <span id="subModPlayers">0</span></div>
            <div class="subMod__dashed"></div>
            <div class="subMod__servers"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_serversCount') ?>: <span id="subModServersCount">0</span></div>
        </div>
        <div class="<?= ($mondefaultView === 'grid') ? $mongridClass : $montableClass ?>"
            id="monLoaded"
            data-grid-class="<?= $mongridClass ?>"
            data-table-class="<?= $montableClass ?>"
            data-default-view="<?= $mondefaultView ?>"
            style="display:none;">
        </div>
        <div class="no-data" id="monNoData" style="display:none;"></div>

        <div class="<?= ($mondefaultView === 'grid') ? $mongridClass : $montableClass ?>" id="monLoading">
            <?php for ($i = 0; $i < $monloaderCount + $monplugsLoader; ++$i): ?>
                <div class="<?= $monloaderTag ?>"></div>
            <?php endfor; ?>
        </div>
    </div>
</div>


<div id="server-players-online" class="modal-window-server modal_players_online" data-server="">
    <div class="modal-card">
        <div class="modal-card__header">
            <button class="modal-btn__refresh" id="updatemodal">
                <svg>
                    <use href="/resources/img/sprite.svg#spinner"></use>
                </svg>
            </button>
            <?php if (isset($_SESSION['user_admin'])): ?>
                <a title="" id="" onclick="" href="/monitoring/" class="modal-btn__settings">
                    <svg>
                        <use href="/resources/img/sprite.svg#gear"></use>
                    </svg>
                </a>
            <?php endif; ?>
            <div title="" id="<?= $i ?>" onclick="close_modal()" href="javascript:void(0);" class="modal-btn__close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </div>
            <img class="lazy" ondrag="return false" ondragstart="return false" id="server-map-image-modal" data-src="/storage/cache/img/maps/730/-.webp" alt="" title="">
            <div class="modal-card__header-shadow"></div>
            <div class="modal-card__header-content">
                <div class="modal-card__header-content-top">
                    <div class="modal-card__server-details">
                        <span>
                            <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_CurrentPlayers') ?>:
                            <b id="server-players-modal">
                                <span class="fake-loader spinner">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#spinner"></use>
                                    </svg>
                                </span>
                            </b>
                            <svg>
                                <use href="/resources/img/sprite.svg#three-users"></use>
                            </svg>
                        </span>
                        <span>
                            <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_Admins_sb') ?>:
                            <b id="server-admins">
                                <span class="fake-loader spinner">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#spinner"></use>
                                    </svg>
                                </span>
                            </b>
                            <svg>
                                <use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#admin"></use>
                            </svg>
                        </span>
                    </div>
                    <img id="server-pin" class="lazy" data-src="/storage/cache/img/pins/maps/default.webp" alt="" ondrag="return false" ondragstart="return false">
                    <div class="modal-card__server-details right">
                        <span>
                            <svg>
                                <use href="/resources/img/sprite.svg#servers"></use>
                            </svg>
                            <?= $Translate->get_translate_phrase('_Server') ?>:
                            <b class="hide-long-name" id="server-hostname">
                                <span class="fake-loader spinner">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#spinner"></use>
                                    </svg>
                                </span>
                            </b>
                        </span>
                        <span>
                            <svg>
                                <use href="/resources/img/sprite.svg#geo"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_CurrentMapPlayShort') ?>:
                            <b class="hide-long-name" id="server-maptwo">
                                <span class="fake-loader spinner">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#spinner"></use>
                                    </svg>
                                </span>
                            </b>
                        </span>
                    </div>
                </div>
                <div class="modal-card__header-content-bottom">
                    <div class="win-team" id="win-team"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_scoreLoad') ?></div>
                    <div class="team-score-block">
                        <img class="lazy" data-src="/app/modules/module_block_main_servers/assets/img/icons/ct.svg" alt="">
                        <div class="team-score" id="team-score">0 : 0</div>
                        <img class="lazy" data-src="/app/modules/module_block_main_servers/assets/img/icons/t.svg" alt="">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-card__body-table">
            <div class="table-list-loader" id="playersLoader"></div>
            <div id="players_online" style="display: none;">
            </div>
            <?php if (isset($_SESSION['monAccess'])): ?>
                <div class="modal-card__body-bottom-wrapper">
                    <div class="modal-card__body-buttons" style="display: none;">
                        <button class="secondary_btn modal-card__body-action" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_servers', '_MuteMicrofon') ?>" data-tippy-placement="top" data-action="mute">
                            <svg>
                                <use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#mute"></use>
                            </svg>
                        </button>
                        <button class="secondary_btn modal-card__body-action" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_servers', '_KickPlayer') ?>" data-tippy-placement="top" data-action="kick">
                            <svg>
                                <use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#kick"></use>
                            </svg>
                        </button>
                        <button class="secondary_btn modal-card__body-action btn_delete" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_servers', '_GiveBan') ?>" data-tippy-placement="top" data-action="ban">
                            <svg>
                                <use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#ban"></use>
                            </svg>
                        </button>
                    </div>
                    <div class="mon_selects">
                        <?php if (!empty($Mon->getTimeForMs()) && $Mon->getSettingsForMs()['time_choice_punishment'] != '0'): ?>
                            <div class="mon_action_select_time" id="mon_modal_time" style="display: none;">
                                <div class="adaptive-select-wrapper">
                                    <ul class="adaptive-select__dropdown-list adaptive-select__bottom" id="option-ban_time-select">
                                        <?php foreach ($Mon->getTimeForMs() as $key => $reason): ?>
                                            <li>
                                                <label class="adaptive-select__label" for="ban_time-<?= $key ?>">
                                                    <div class="adaptive-select__label-text"><?= $reason['name_time'] ?></div>
                                                    <input class="hide-input" id="ban_time-<?= $key ?>" type="radio" value="<?= $reason['duration'] ?>" name="ban_time" <?php $key == 0 && print 'checked' ?>>
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="adaptive-select" open-select="option-ban_time-select">
                                        <span class="adaptive-select__span_text"></span>
                                        <span class="margin-left-auto adaptive-select__arrow">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mon_action_select_time" id="mon_modal_time" style="display: none;">
                                <input name="ban_time" placeholder="<?= $Translate->get_translate_module_phrase('module_block_main_servers', '_timeSecond') ?>">
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($Mon->getBanReasonsForMs()) && $Mon->getSettingsForMs()['reason_ban'] != '0'): ?>
                            <div class="mon_action_select_ban_reason" id="mon_modal_ban_reason" style="display: none;">
                                <div class="adaptive-select-wrapper">
                                    <ul class="adaptive-select__dropdown-list adaptive-select__bottom" id="option-ban_reason-select">
                                        <?php foreach ($Mon->getBanReasonsForMs() as $key => $reason): ?>
                                            <li>
                                                <label class="adaptive-select__label" for="ban_reason-<?= $key ?>">
                                                    <div class="adaptive-select__label-text"><?= $reason['reason_name'] ?></div>
                                                    <input class="hide-input" id="ban_reason-<?= $key ?>" type="radio" value="<?= $reason['reason_name'] ?>" name="ban_reason" <?php $key == 0 && print 'checked' ?>>
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="adaptive-select" open-select="option-ban_reason-select">
                                        <span class="adaptive-select__span_text"></span>
                                        <span class="margin-left-auto adaptive-select__arrow">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mon_action_select_ban_reason" id="mon_modal_ban_reason" style="display: none;">
                                <input name="ban_reason" placeholder="<?= $Translate->get_translate_module_phrase('module_block_main_servers', '_banReason') ?>">
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($Mon->getMuteReasonsForMs()) && $Mon->getSettingsForMs()['reason_mute'] != '0'): ?>
                            <div class="mon_action_select_mute_reason" id="mon_modal_mute_reason" style="display: none;">
                                <div class="adaptive-select-wrapper">
                                    <ul class="adaptive-select__dropdown-list adaptive-select__bottom" id="option-mute_reason-select">
                                        <?php foreach ($Mon->getMuteReasonsForMs() as $key => $reason): ?>
                                            <li>
                                                <label class="adaptive-select__label" for="mute_reason-<?= $key ?>">
                                                    <div class="adaptive-select__label-text"><?= $reason['reason_name'] ?></div>
                                                    <input class="hide-input" id="mute_reason-<?= $key ?>" type="radio" value="<?= $reason['reason_name'] ?>" name="mute_reason" <?php $key == 0 && print 'checked' ?>>
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="adaptive-select" open-select="option-mute_reason-select">
                                        <span class="adaptive-select__span_text"></span>
                                        <span class="margin-left-auto adaptive-select__arrow">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mon_action_select_mute_reason" id="mon_modal_mute_reason" style="display: none;">
                                <input name="mute_reason" placeholder="<?= $Translate->get_translate_module_phrase('module_block_main_servers', '_muteReason') ?>">
                            </div>
                        <?php endif; ?>
                        <div class="mon_action_select_mute_type" id="mon_modal_mute_type" style="display: none;">
                            <div class="adaptive-select-wrapper">
                                <ul class="adaptive-select__dropdown-list adaptive-select__bottom" id="option-mute_type-select">
                                    <li>
                                        <label class="adaptive-select__label" for="mute_type-mute">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_mute') ?></div>
                                            <input class="hide-input" id="mute_type-mute" type="radio" value="mute" name="mute_type" checked>
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="mute_type-gag">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_gag') ?></div>
                                            <input class="hide-input" id="mute_type-gag" type="radio" value="gag" name="mute_type">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="mute_type-full">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_silence') ?></div>
                                            <input class="hide-input" id="mute_type-full" type="radio" value="full" name="mute_type">
                                        </label>
                                    </li>
                                </ul>
                                <div class="adaptive-select" open-select="option-mute_type-select">
                                    <span class="adaptive-select__span_text"></span>
                                    <span class="margin-left-auto adaptive-select__arrow">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button class="secondary_btn modal-card__body-action modal-card__body-send" style="display: none;" data-action="send">
                            <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_punish') ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="modal-card__footer">
            <a class="modal-btn btn-clipboard copy-btn" id="copy_btnsecond" data-clipboard-text="">
                <svg>
                    <use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#copy-list"></use>
                </svg>
                <?= $Translate->get_translate_phrase('_TakeIp') ?>
            </a>
            <a class="modal-btn hide-mobile" id="connect-server">
                <svg>
                    <use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#play-triangle"></use>
                </svg>
                <?= $Translate->get_translate_phrase('_Connect') ?>
            </a>
        </div>
    </div>
</div>