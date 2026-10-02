<?php !isset($_SESSION['user_admin']) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die() ?>
<div class="row">
    <div class="col-md-12" id="installTablesBlock" style="display:none;">
        <div class="card">
            <div class="card-container">
                <p style="margin-block:.3rem .75rem;font-size:.875rem;">
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_install_tables_desc') ?>
                </p>
                <button id="installTablesBtn">
                    <svg>
                        <use href="/resources/img/sprite.svg#database"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_install_tables') ?>
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_settings') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="sc__settings-wrapper">
                    <span class="sc__settings-title"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_plugin_for_skins') ?></span>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="pluginSelectList">
                            <li>
                                <label class="adaptive-select__label" for="pluginPisex">
                                    <div class="adaptive-select__label-text">Skinchanger by Pisex</div>
                                    <input class="hide-input" id="pluginPisex" type="radio" name="sc-plugin" value="pisex" checked>
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="pluginWeaponpaints">
                                    <div class="adaptive-select__label-text">WeaponPaints by Nereziel</div>
                                    <input class="hide-input" id="pluginWeaponpaints" type="radio" name="sc-plugin" value="weaponpaints">
                                </label>
                            </li>
                        </ul>
                        <div class="adaptive-select" open-select="pluginSelectList">
                            <span class="adaptive-select__fist-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#pistol"></use>
                                </svg>
                            </span>
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_select_plugin') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <hr>
                    <div class="sc__settings-title"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_showNew') ?></div>
                    <span class="sc__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_showNew_desc') ?></span>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="selectNewCollections">
                        </ul>
                        <div class="adaptive-select" open-select="selectNewCollections">
                            <span class="adaptive-select__fist-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#servers"></use>
                                </svg>
                            </span>
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_select_new_collections') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <hr>
                    <span class="sc__settings-title"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_collections_limit_desc') ?></span>
                    <div class="inputs-inline">
                        <label for="collectionsLimitInput">
                            <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_collections_limit') ?>:
                        </label>
                        <input id="collectionsLimitInput" type="number" min="1" max="100" value="5">
                    </div>
                    <hr>
                    <span class="sc__settings-title"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_vip_slots_title') ?></span>
                    <div class="sc__settings-subtitle">
                        <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_vip_slots_desc') ?>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_group_name') ?></th>
                                    <th><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_extra_slots') ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="vipGroupsList"></tbody>
                        </table>
                    </div>
                    <div class="flex-inline sc__settings-flex-inline">
                        <div class="inputs-inline">
                            <label for="vipGroupNameInput"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_group_name') ?></label>
                            <input id="vipGroupNameInput" type="text" maxlength="64" placeholder="PREMIUM">
                        </div>
                        <div class="inputs-inline">
                            <label for="vipGroupSlotsInput"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_extra_slots') ?></label>
                            <input id="vipGroupSlotsInput" type="number" min="1" max="100" placeholder="3">
                        </div>
                        <button id="addVipGroupBtn" class="active button-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#plus"></use>
                            </svg>
                        </button>
                    </div>
                    <button id="saveSettingsBtn" class="width-100 active">
                        <svg>
                            <use href="/resources/img/sprite.svg#floppy"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_save') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_skin_cache') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="sc__ap-repo">
                    <span><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_update_status') ?></span>
                    <ul class="sc__ap-repo-list">
                        <li class="sc__cache-status" data-cache-type="skins" data-tippy-placement="top">
                            <svg>
                                <use href="/resources/img/sprite.svg#pistol"></use>
                            </svg>
                        </li>
                        <li class="sc__cache-status" data-cache-type="stickers" data-tippy-placement="top">
                            <svg>
                                <use href="/app/modules/module_page_skinchanger/assets/img/icons.svg#sticker"></use>
                            </svg>
                        </li>
                        <li class="sc__cache-status" data-cache-type="keychains" data-tippy-placement="top">
                            <svg>
                                <use href="/app/modules/module_page_skinchanger/assets/img/icons.svg#keychain-2"></use>
                            </svg>
                        </li>
                        <li class="sc__cache-status" data-cache-type="agents" data-tippy-placement="top">
                            <svg>
                                <use href="/resources/img/sprite.svg#csgo"></use>
                            </svg>
                        </li>
                        <li class="sc__cache-status" data-cache-type="coins" data-tippy-placement="top">
                            <svg>
                                <use href="/app/modules/module_page_skinchanger/assets/img/icons.svg#coin"></use>
                            </svg>
                        </li>
                        <li class="sc__cache-status" data-cache-type="music" data-tippy-placement="top">
                            <svg>
                                <use href="/app/modules/module_page_skinchanger/assets/img/icons.svg#record"></use>
                            </svg>
                        </li>
                        <li class="sc__cache-status" data-cache-type="collections" data-tippy-placement="top">
                            <svg>
                                <use href="/app/modules/module_page_skinchanger/assets/img/icons.svg#collection"></use>
                            </svg>
                        </li>
                    </ul>
                </div>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="cacheTypeList">
                        <li>
                            <label class="adaptive-select__label" for="refreshAll">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_refresh_all') ?></div>
                                <input class="hide-input" id="refreshAll" type="radio" name="sc-cache" value="all" checked>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="refreshSkins">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_refresh_skins') ?></div>
                                <input class="hide-input" id="refreshSkins" type="radio" name="sc-cache" value="skins">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="refreshStickers">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_refresh_stickers') ?></div>
                                <input class="hide-input" id="refreshStickers" type="radio" name="sc-cache" value="stickers">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="refreshKeychains">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_refresh_keychains') ?></div>
                                <input class="hide-input" id="refreshKeychains" type="radio" name="sc-cache" value="keychains">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="refreshAgents">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_refresh_agents') ?></div>
                                <input class="hide-input" id="refreshAgents" type="radio" name="sc-cache" value="agents">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="refreshCoins">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_refresh_coins') ?></div>
                                <input class="hide-input" id="refreshCoins" type="radio" name="sc-cache" value="coins">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="refreshMusicKits">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_refresh_music') ?></div>
                                <input class="hide-input" id="refreshMusicKits" type="radio" name="sc-cache" value="music">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="refreshCollections">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_refresh_collections') ?></div>
                                <input class="hide-input" id="refreshCollections" type="radio" name="sc-cache" value="collections">
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="cacheTypeList">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#spinner"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_select_update_type') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <button id="cacheUpdateBtn" class="width-100 active">
                    <svg>
                        <use href="/resources/img/sprite.svg#spinner"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_update') ?>
                </button>
            </div>
        </div>
        <div class="card" style="margin-top:.5rem;">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_vip_access') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="sc__settings-wrapper">
                    <span class="sc__settings-title"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_vip_only') ?></span>
                    <span class="sc__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_vip_groups_desc') ?></span>
                    <div class="inputs-inline">
                        <label for="vipAccessGroups"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_vip_groups') ?></label>
                        <input id="vipAccessGroups" type="text" placeholder="PREMIUM;LITE">
                    </div>
                    <button id="saveVipAccessBtn" class="width-100 active"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_save') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_preset_collections') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="sc__settings-wrapper">
                    <span class="sc__settings-title"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_preset_collections') ?></span>
                    <div class="sc__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_preset_collections_desc') ?></div>
                    <div class="flex-inline">
                        <div class="inputs-inline">
                            <label for=""><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_collection_id') ?></label>
                            <input id="defaultCollectionIdInput" type="number" class="sc__modal-input" min="1" placeholder="546">
                        </div>
                        <div class="inputs-inline">
                            <label for=""><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_name_optional') ?></label>
                            <input id="defaultCollectionNameInput" type="text" class="sc__modal-input" maxlength="32" placeholder="">
                        </div>
                    </div>
                    <button id="addDefaultCollectionBtn" class="active width-100">
                        <svg>
                            <use href="/resources/img/sprite.svg#plus"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_add_preset_collection') ?>
                    </button>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>
                                        <svg>
                                            <use href="/resources/img/sprite.svg#move"></use>
                                        </svg>
                                    </th>
                                    <th>ID</th>
                                    <th><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_col_name') ?></th>
                                    <th><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_col_skins') ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="defaultCollectionsList"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/storage/assets/js/Sortable.min.js"></script>