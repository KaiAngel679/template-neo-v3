<div class="row" <?php $option = $General->get_neo_options();
                    if (isset($option['hide_filter']) && $option['hide_filter'] == 1) {
                        echo 'style="display:none;"';
                    } ?>>
    <div class="col-md-12">
        <div class="servers_filter card">
            <div class="filter_chips">
                <button class="chips_btn chips_active <?php if ($option['stretch_filter'] == 1) {
                                                            echo 'fill_width';
                                                        } ?> mode" data-mode="Все"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_AllMod') ?></button>
                <?php $added_modes = array();
                foreach ($General->server_list as $server) :
                    $server_mode = $server['server_mod'];
                    if (!in_array($server_mode, $added_modes)) :
                        array_push($added_modes, $server_mode); ?>
                        <button class="chips_btn <?php if ($option['stretch_filter'] == 1) {
                                                        echo 'fill_width';
                                                    } ?> mode" data-mode="<?= $server_mode; ?>"><?= $server_mode; ?></button>
                <?php endif;
                endforeach; ?>
            </div>
            <a id="updateservers" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_servers', '_refreshInfo') ?>" data-tippy-placement="top">
                <svg viewBox="0 0 50 50">
                    <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                </svg>
            </a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="servers_wrap">
            <?php for ($i_server = 0; $i_server < $General->server_list_count; $i_server++) : ?>
                <div class="server_block" id="server-mode-<?= $i_server ?>">
                    <div class="server_map_image">
                        <img class="map" ondrag="return false" ondragstart="return false" id="server-map-image-<?= $i_server ?>" src="<?= $General->arr_general['site'] ?>storage/cache/img/maps/730/-.webp" alt="" title="">
                    </div>
                    <div class="top_server_block">
                        <div class="server_info_block">
                            <div class="server_name_ip copybtn3" id="copy_btn_<?= $i_server ?>" data-clipboard-text="" data-tippy-content="<?= $Translate->get_translate_phrase('_TakeIp') ?>" data-tippy-placement="right">
                                <?php if ($General->server_list[$i_server]['server_bage']) : ?>
                                    <div class="server_badge" id="server-bage-<?= $i_server ?>"></div>
                                <?php endif; ?>
                                <div class="server_name_custom" id="server-name-<?= $i_server ?>"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_Loading') ?></div>
                                <div class="btn-clipboard">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#copy-list"></use>
                                    </svg>
                                </div>
                            </div>
                            <div class="server_players_mapname">
                                <div class="server_players_block">
                                    <div id="server-players-<?= $i_server ?>">0/0</div>
                                </div>
                                <div class="server_map_name" id="server-map-<?= $i_server ?>"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_Loading') ?></div>
                                <div class="server_geoip">
                                    <div <?php if ($option['hide_country'] == 1) : ?>style="display:none;" <?php endif; ?> class="server_country" id="server-country-<?= $i_server ?>"></div>
                                    <div <?php if ($option['hide_city'] == 1) : ?>style="display:none;" <?php endif; ?>class="server_city" id="server-city-<?= $i_server ?>"></div>
                                </div>
                            </div>
                        </div>
                        <div class="server_button_play">
                            <button class="server_button play" id="<?= $i_server ?>" onclick="get_players_data(id)" href="javascript:void(0);">
                                <svg>
                                    <use href="/resources/img/sprite.svg#play-triangle"></use>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="bottom_server_block">
                        <div class="progress">
                            <div class="progress-value" id="progess-formula-<?= $i_server ?>"></div>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>
<?php for ($i_server = 0; $i_server < $General->server_list_count; $i_server++) : ?>
    <div id="server-players-online-<?= $i_server ?>" class="modal-window-server modal_players_online">
        <div class="modal-card">
            <div class="modal-card__header">
                <a title="" id="<?= $i_server ?>" onclick="close_modal(id)" href="javascript:void(0);" class="modal-btn__close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </a>
                <div class="cover server-modal__bg">
                    <img ondrag="return false" ondragstart="return false" id="server-map-image-modal-<?= $i_server ?>" src="<?= $General->arr_general['site'] ?>storage/cache/img/maps/730/-.webp" alt="" title="">
                    <div class="shadow"></div>
                </div>
                <div class="server-modal__header">
                    <div class="map_name_block">
                        <div class="server_map_now_play_text">
                            <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_CurrentMapPlay') ?>
                        </div>
                        <div class="server_map_name_second" id="server-maptwo-<?= $i_server ?>">
                            <?= $Translate->get_translate_module_phrase('module_block_main_servers', '_Loading') ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mon_header">
                <span class="mon_player_name"><?= $Translate->get_translate_module_phrase('module_block_main_servers', '_CurrentPlayers') ?> <span id="server-players-modal-<?= $i_server ?>" class="players_modal">0/0</span></span>
                <span class="non_mob"><?= $Translate->get_translate_phrase('_Point') ?></span>
                <span><?= $Translate->get_translate_phrase('_Play_time') ?></span>
            </div>
            <ul class="mon_list_body mon_list_scroll no-scrollbar" id="players_online_<?= $i_server ?>"></ul>
            <div class="modal-card__footer">
                <a class="button secondary_btn modal-btn_copy btn-clipboard copybtn3" id="copy_btnsecond_<?= $i_server ?>" data-clipboard-text="">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <?= $Translate->get_translate_phrase('_TakeIp') ?>
                </a>
                <a class="button secondary_btn modal-btn non_mob" id="connect_server_<?= $i_server ?>">
                    <svg>
                        <use href="/resources/img/sprite.svg#steam"></use>
                    </svg>
                    <?= $Translate->get_translate_phrase('_Connect') ?>
                </a>
            </div>
        </div>

    </div>
<?php endfor ?>