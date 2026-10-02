<?php !isset($_SESSION['user_admin']) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die() ?>
<div class="col-md-7">
    <div class="card">
        <div class="card-header">
            <div class="badge">
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Module_loading') ?>
            </div>
            <div class="select-panel absolute-select">
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="option-module_page-select">
                        <?php for ($i = 0; $i < $Modules->arr_module_init_page_count; $i++) {
                            $id_module = array_keys($Modules->arr_module_init['page'])[$i] ?>
                            <li>
                                <label class="adaptive-select__label" for="<?= $id_module ?>">
                                    <div class="adaptive-select__label-text">
                                        <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Page') . ':' ?>
                                        <?= $id_module ?></div>
                                    <input class="hide-input" id="<?= $id_module ?>" type="radio" name="module_page"
                                        onclick="window.location.href=this.value"
                                        value="<?= set_url_section(get_url(2), 'module_page', $id_module) ?>">
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="adaptive-select" open-select="option-module_page-select">
                        <span
                            class="adaptive-select__span_text"><?= get_section('module_page', 'home') == 'sidebar' ? ' ' : $Translate->get_translate_module_phrase('module_page_adminpanel', '_Page') . ':' ?>
                            <?= get_section('module_page', 'home') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-container">
            <?php if (get_section('module_page', 'home') != ''): ?>
                <div class="dd" id="nestable">
                    <ol class="dd-list">
                        <?php
                        if (get_section('module_page', 'home') == 'sidebar'):
                            $c_m_p = sizeof($Modules->arr_module_init['sidebar']);
                        else:
                            $c_m_p = sizeof($Modules->arr_module_init['page'][get_section('module_page', 'home')]['interface'][get_section('module_interface_adjacent', 'afternavbar')]);
                        endif;
                        for ($i = 0; $i < $c_m_p; $i++) {
                            if (get_section('module_page', 'home') == 'sidebar'):
                                $data_id = $Modules->arr_module_init['sidebar'][$i];
                                $data_title = $Modules->array_modules[$Modules->arr_module_init['sidebar'][$i]]['title'];
                            else:
                                $data_id = $Modules->arr_module_init['page'][get_section('module_page', 'home')]['interface'][get_section('module_interface_adjacent', 'afternavbar')][$i];
                                $data_title = $Modules->array_modules[$Modules->arr_module_init['page'][get_section('module_page', 'home')]['interface'][get_section('module_interface_adjacent', 'afternavbar')][$i]]['title'];
                            endif ?>
                            <li class="dd-item" data-id="<?= $data_id ?>">
                                <a class="module_setting"
                                    href="<?= $General->arr_general['site'] ?>adminpanel/?section=modules&options=<?= $data_id ?>">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#edit-pen"></use>
                                    </svg>
                                </a>
                                <div class="dd-handle"><?= $data_title ?></div>
                            </li>
                        <?php } ?>
                    </ol>
                    <input type="hidden" id="nestable-output">
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="col-md-5">
    <div class="card">
        <div class="card-header">
            <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Options') ?>
            </div>
        </div>
        <div class="card-container">
            <div class="joke-block">
                <span><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_catText') ?></span>
                <img src="/app/modules/module_page_adminpanel/assets/img/cat.gif" alt="">
                <form class="joke-button" id="clear_modules_initialization">
                    <button class="width-100 active" type="submit" name="clear_modules_initialization"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_ClearallCache') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if (!empty($_GET['options'])): ?>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Mset') ?>
                    <?= $Modules->array_modules[$_GET['options']]['title'] ?>
                </h5>
            </div>
            <form id="settings_modules_core">
                <div class="card-container module_block">
                    <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_MMode') ?></label>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="option-module_type-select">
                            <?php for ($ia = 0, $cia = sizeof($_cia = explode(";", $Modules->array_modules[$_GET['options']]['setting']['available_types'])); $ia < $cia; $ia++): ?>
                                <li>
                                    <label class="adaptive-select__label" for="<?= $_cia[$ia] ?>">

                                        <div class="adaptive-select__label-text"><?= $_cia[$ia] ?></div>
                                        <input class="hide-input" id="<?= $_cia[$ia] ?>" type="radio" value="<?= $_cia[$ia] ?>" name="module_type" <?php if ($Modules->array_modules[$_GET['options']]['setting']['type'] == $_cia[$ia]) {
                                            echo 'checked';
                                        } ?>>
                                    </label>
                                </li>
                            <?php endfor ?>
                        </ul>
                        <div class="adaptive-select" open-select="option-module_type-select">
                            <span
                                class="adaptive-select__span_text"><?= (int) $Modules->array_modules[$_GET['options']]['setting']['type'] ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-bottom"><button class="width-100"
                        type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Save') ?></button>
                </div>
            </form>
            <form id="settings_modules">
                <?php if ($_GET['options'] == 'module_page_leaderboard'): ?>
                    <div class="card-container module_block">
                        <div class="lr-stats__block">
                            <div class="lr-stats__description">
                                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_vipe') ?>
                            </div>
                            <button class="button-delete width-100" id="admin_clear_stats">
                                <svg>
                                    <use href="/resources/img/sprite.svg#broom"></use>
                                </svg> <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_clearAllStat') ?>
                            </button>
                        </div>
                        <div class="lr-stats__block">
                            <div class="lr-stats__description">
                                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_clearGhost') ?>
                            </div>
                            <button class="width-100" id="admin_clear_empty_players">
                                <svg>
                                    <use href="/resources/img/sprite.svg#ghost"></use>
                                </svg> <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_remoteEmpty') ?>
                            </button>
                        </div>
                        <div class="lr-stats__block">
                            <div class="lr-stats__description">
                                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_clear90Days') ?>
                            </div>
                            <button class="width-100" id="admin_clear_unactive_players">
                                <svg>
                                    <use href="/resources/img/sprite.svg#time-timer"></use>
                                </svg>
                                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_deleteInactive') ?>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($_GET['options'] == 'module_page_profiles'): ?>
                    <div class="card-container module_block">
                        <?php $option = $Modules->get_settings_modules($_GET['options'], 'settings'); ?>
                        <div class="inputs-inline">
                            <input class="switch" type="checkbox" name="use_all_vips_servers_in_one_table"
                                id="use_all_vips_servers_in_one_table" <?php $option['use_all_vips_servers_in_one_table'] == 1 && print 'checked'; ?>>
                            <label
                                for="use_all_vips_servers_in_one_table"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_oneVipBase') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <input class="switch" type="checkbox" name="punishment_all_servers" id="punishment_all_servers"
                                <?php $option['punishment_all_servers'] == 1 && print 'checked'; ?>>
                            <label
                                for="punishment_all_servers"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_punishAllServers') ?></label>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($_GET['options'] == 'module_page_punishment'): ?>
                    <div class="card-container module_block">
                        <?php $option = $Modules->get_settings_modules($_GET['options'], 'settings'); ?>
                        <div class="inputs-inline">
                            <input class="switch" type="checkbox" name="punishment_all_servers" id="punishment_all_servers"
                                <?php $option['punishment_all_servers'] == 1 && print 'checked'; ?>>
                            <label
                                for="punishment_all_servers"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_punishAllServers') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <input class="switch" type="checkbox" name="func_unban" id="func_unban" <?php $option['func_unban'] == 1 && print 'checked'; ?>>
                            <label
                                for="func_unban"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_saleUnban') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <label
                                for="unbanPrice"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_unbanSale') ?></label>
                            <input id="unbanPrice" name="price_unban" value="<?= $option['price_unban'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <input class="switch" type="checkbox" name="func_unmute" id="func_unmute" <?php $option['func_unmute'] == 1 && print 'checked'; ?>>
                            <label
                                for="func_unmute"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_saleUnmute') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <label
                                for="unmutePrice"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_unmutePrice') ?></label>
                            <input id="unmutePrice" name="price_unmute" value="<?= $option['price_unmute'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <input class="switch" type="checkbox" name="func_like" id="func_like" <?php $option['func_like'] == 1 && print 'checked'; ?>>
                            <label
                                for="func_like"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_hoursToLike') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <label
                                for="hoursToLike"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_hoursToLikeCount') ?></label>
                            <input id="hoursToLike" name="hoursToLike" value="<?= $option['hoursToLike'] ?>">
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ((!isset($_GET['baner_edit']) || $_GET['baner_edit'] === '') && $_GET['options'] == 'module_block_main_banner_slider'): ?>
                    <?php $option = $Modules->get_settings_modules($_GET['options'], 'settings'); ?>
                    <div class="card-container module_block">
                        <div class="inputs-inline">
                            <label for="slideName"><?= $Translate->get_translate_phrase('_Name') ?></label>
                            <input id="slideName" name="title">
                        </div>
                        <div class="inputs-inline">
                            <label for="slideDescription"><?= $Translate->get_translate_phrase('_Description') ?></label>
                            <input id="slideDescription" name="description">
                        </div>
                        <div class="inputs-inline">
                            <label
                                for="slideText"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_BannerText') ?></label>
                            <input id="slideText" name="button_text">
                        </div>
                        <div class="inputs-inline">
                            <label
                                for="urlSlide"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_link') ?></label>
                            <input id="urlSlide" name="button_url">
                        </div>
                        <div class="inputs-inline">
                            <label><?= $Translate->get_translate_phrase('_image') ?></label>
                            <div class="file-upload-container">
                                <input type="file" id="file-input" class="custom-file-input" accept="image/*" name="file">
                                <label for="file-input"><?= $Translate->get_translate_phrase('_selectFile') ?></label>
                                <div id="file-info" class="file-upload-info" style="display: block"></div>
                            </div>
                        </div>
                    </div>
                <?php elseif ((isset($_GET['baner_edit']) || $_GET['baner_edit'] !== '') && $_GET['options'] == 'module_block_main_banner_slider'):
                    $baner_edit = $Admin->info_modules_settings($_GET['baner_edit']); ?>
                    <?php $option = $Modules->get_settings_modules($_GET['options'], 'settings'); ?>
                    <div class="card-container module_block">
                        <div class="inputs-inline">
                            <label for="editName"><?= $Translate->get_translate_phrase('_Name') ?></label>
                            <input id="editName" name="title" value="<?= $baner_edit['title'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label for="editDescription"><?= $Translate->get_translate_phrase('_Description') ?></label>
                            <input id="editDescription" name="description" value="<?= $baner_edit['description'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label
                                for="editButton"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_BannerText') ?></label>
                            <input id="editButton" name="button_text" value="<?= $baner_edit['button_text'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label
                                for="editLink"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_link') ?></label>
                            <input id="editLink" name="button_url" value="<?= $baner_edit['button_url'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label><?= $Translate->get_translate_phrase('_image') ?></label>
                            <div class="file-upload-container">
                                <input type="file" id="file-input" class="custom-file-input" accept="image/*" name="file">
                                <label for="file-input"><?= $Translate->get_translate_phrase('_selectFile') ?></label>
                                <div id="file-info" class="file-upload-info" style="display: block;"><?= $baner_edit['img'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($_GET['options'] == 'module_block_main_banner_slider' || $_GET['options'] == 'module_page_profiles' || $_GET['options'] == 'module_page_punishment'): ?>
                    <div class="card-bottom">
                        <button class="width-100"
                            type="submit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Save') ?></button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
<?php endif ?>
<?php if (!empty($_GET['options']) && $_GET['options'] == 'module_block_main_banner_slider'): ?>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_bannersList') ?>
                </h5>
            </div>
            <div class="card-container">
                <div class="banners_list">
                    <?php foreach ($option['slides'] as $id => $key): ?>
                        <div class="banner_content">
                            <div class="banner_rightside">
                                <div class="banner_peview">
                                    <div class="text">
                                        <div class="title"><?= $key['title'] ?></div>
                                        <div class="subtitle"><?= $key['description'] ?></div>
                                    </div>
                                    <div class="link"
                                        data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_link') ?>"
                                        data-tippy-placement="left">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#link"></use>
                                        </svg>
                                        <?= $key['button_url'] ?>
                                    </div>
                                    <img src="/app/modules/module_block_main_banner_slider/assets/img/<?= $key['img'] ?>"
                                        alt="">
                                    <button><?= $key['button_text'] ?></button>
                                </div>
                                <div class="banner_actions">
                                    <a class="button width-100" href="<?= set_url_section(get_url(2), 'baner_edit', $id) ?>">
                                        <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_change_menu') ?>
                                    </a>
                                    <button class="button-delete margin-left-auto" id="baner_del" id_del="<?= $id ?>">
                                        <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Nav_delete') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>