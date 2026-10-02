<div class="skinchanger">
    <?php if (!isset($_SESSION['steamid']) && empty($_SESSION['steamid'])): ?>
        <div class="sc__auth-access-wrapper">
            <img src="/app/modules/module_page_skinchanger/assets/img/scbg.png" alt="">
            <div class="sc__auth-access-content">
                <div class="sc__auth-access-title"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_access_denied') ?></div>
                <div class="sc__auth-access-subtitle">
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_auth_access_desc') ?>
                </div>
                <a href="/?auth=login" class="button active">
                    <svg>
                        <use href="/resources/img/sprite.svg#steam"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_auth') ?>
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php if (!$hasVipAccess): ?>
            <div class="sc__vip-access-wrapper">
                <img src="/app/modules/module_page_skinchanger/assets/img/scbg.png" alt="">
                <div class="sc__vip-access-content">
                    <div class="sc__vip-access-title"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_access_denied') ?></div>
                    <div class="sc__vip-access-subtitle">
                        <?= sprintf($Translate->get_translate_module_phrase('module_page_skinchanger', '_vip_access_desc'), htmlspecialchars($vipAccessGroupsList)) ?>
                    </div>
                    <button class="button-pay" onclick="window.location.href='/store'">
                        <svg>
                            <use href="/resources/img/sprite.svg#diamond"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_buy_vip') ?>
                    </button>
                </div>
            </div>
        <?php else: ?>
            <aside class="sc__aside">
                <div class="sc__info-card" style="display:none;">
                    <button class="button-icon" id="hideThisInfo"><svg><use href="/resources/img/sprite.svg#x"></use></svg></button>
                    <img class="lazy" data-src="/app/modules/module_page_skinchanger/assets/img/skin.webp" alt="">
                    <div class="sc__info-title"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_warning') ?></div>
                    <div class="sc__info-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_apply_skins_warning') ?></div>
                    <div class="sc__info-lights"></div>
                </div>
                <div class="sc__header">
                    <button class="filter active flex-1"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_my_collections') ?></button>
                    <button class="filter flex-1"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_community') ?></button>
                </div>
                <div class="sc__content">
                    <div class="sc__categories">

                        <div id="myCollectionsList">
                            <div class="sc__collection-button-fake sc__add-button-fake skeleton--default">
                                <div class="sc__add-icon"></div>
                            </div>
                            <div class="sc__subtitle"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_your_collections') ?></div>
                            <div class="sc__collection-button-fake skeleton--default">
                                <div class="sc__collection-icon"></div>
                            </div>
                            <div class="sc__collection-button-fake skeleton--default">
                                <div class="sc__collection-icon"></div>
                            </div>
                            <div class="sc__collection-button-fake skeleton--default">
                                <div class="sc__collection-icon"></div>
                            </div>
                            <div class="sc__collection-button-fake skeleton--default">
                                <div class="sc__collection-icon"></div>
                            </div>
                            <div class="sc__collection-button-fake skeleton--default">
                                <div class="sc__collection-icon"></div>
                            </div>
                            <div class="sc__subtitle" style="margin-top: .6rem;"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ready_collections') ?></div>
                            <div class="sc__collection-button-fake skeleton--default">
                                <div class="sc__collection-icon"></div>
                            </div>
                        </div>
                        <div id="defaultCollectionsBlock" style="display:none;">
                            <div class="sc__subtitle">
                                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ready_collections') ?>
                            </div>
                            <div id="defaultCollectionsList"></div>
                        </div>

                        <div id="communityCollectionsList" style="display:none;"></div>

                        <div class="sc__back-colletions" style="display:none;">
                            <svg>
                                <use href="/resources/img/sprite.svg#single-chevrone-left"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_backToCollections') ?>
                        </div>
                        <div class="sc__search" style="display:none;">
                            <input type="text" placeholder="<?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_find_skin') ?>">
                            <svg>
                                <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                            </svg>
                        </div>
                        <div id="categoriesList" style="display:none;">
                        </div>
                        <div class="sc__category-title" style="display:none;">
                            <div class="sc__back">
                                <svg>
                                    <use href="/resources/img/sprite.svg#single-chevrone-left"></use>
                                </svg>
                                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_back') ?>
                            </div>
                            <span class="sc__choosenWeapon-name"></span>
                        </div>

                        <div id="skinsRarityFilter" class="sc__filter-rarity" style="display:none;"></div>

                        <div id="weaponSkinsList" class="sc__skins-list" style="display:none;">
                        </div>
                        <div id="itemsListPagination"></div>
                    </div>
                    <div class="sc__collections-aside" style="display:none;">
                        <div class="sc__collections-filters">
                            <div class="inputs-inline">
                                <label for="searchCollections"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_search_collections') ?></label>
                                <input id="searchCollections" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_search_collections_placeholder') ?>">
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="oblyWithDowloads" class="switch">
                                <label for="oblyWithDowloads"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_only_with_downloads') ?></label>
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="oblyLikedMe" class="switch">
                                <label for="oblyLikedMe"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_only_liked_me') ?></label>
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="oblyMyCollections" class="switch">
                                <label for="oblyMyCollections"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_only_my_collections') ?></label>
                            </div>

                            <div class="adaptive-select-wrapper sc__select">
                                <ul class="adaptive-select__dropdown-list" id="collectionFilters">
                                    <li>
                                        <label class="adaptive-select__label" for="popular">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_most_popular') ?></div>
                                            <input class="hide-input" id="popular" type="radio" name="collections-sort">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="liked">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_most_liked') ?></div>
                                            <input class="hide-input" id="liked" type="radio" name="collections-sort">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="newCollections">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_new_collections') ?></div>
                                            <input class="hide-input" id="newCollections" type="radio" name="collections-sort">
                                        </label>
                                    </li>
                                    <li>
                                        <label class="adaptive-select__label" for="last7Days">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_last_7_days') ?></div>
                                            <input class="hide-input" id="last7Days" type="radio" name="collections-sort">
                                        </label>
                                    </li>
                                </ul>
                                <div class="adaptive-select" open-select="collectionFilters">
                                    <span class="adaptive-select__fist-icon">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#filters"></use>
                                        </svg>
                                    </span>
                                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_most_popular') ?></span>
                                    <span class="margin-left-auto adaptive-select__arrow">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="sc__collections-info" style="display:none;">
                            <a href="" class="sc__collections-user-wrapper">
                                <img src="" alt="">
                                <div class="sc__collections-user-info">
                                    <span class="sc__collections-user-title"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_collection_creator') ?></span>
                                    <span class="sc__collections-user-nickname"></span>
                                </div>
                            </a>
                            <ul class="sc__collections-about">
                                <li>
                                    <span class="parametr">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#info-circle"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_ap_collection_id') ?>:
                                    </span>
                                    <span class="value"></span>
                                </li>
                                <li>
                                    <span class="parametr">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#time"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_created') ?>:
                                    </span>
                                    <span class="value"></span>
                                </li>
                                <li>
                                    <span class="parametr">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#spinner"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_updated') ?>:
                                    </span>
                                    <span class="value"></span>
                                </li>
                                <li>
                                    <span class="parametr">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#eye"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_published') ?>:
                                    </span>
                                    <span class="value"></span>
                                </li>
                                <li>
                                    <span class="parametr">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#cloud-load"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_installed') ?>:
                                    </span>
                                    <span class="value"></span>
                                </li>
                                <li>
                                    <span class="parametr">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#heart"></use>
                                        </svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_rated') ?>:
                                    </span>
                                    <span class="value"></span>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </aside>
            <div class="sc__right-area">
                <div class="sc__loadout">
                    <div class="sc__header">
                        <div class="sc__loadout-title" data-openmodal="editCollectionName">
                            <span id="currentCollectionName"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_main_collection') ?></span>
                            <svg>
                                <use href="/resources/img/sprite.svg#edit-pen"></use>
                            </svg>
                        </div>
                        <svg class="grey pointer sc__id-tippy" data-tippy-placement="top">
                            <use href="/resources/img/sprite.svg#info-circle"></use>
                        </svg>
                        <div class="ml-auto flex-inline">
                            <button class="filter" type="button" id="copyReadyCollectionButton" style="display: none;">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy-list"></use>
                                </svg>
                                <span><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_copy_collection') ?></span>
                            </button>
                            <button class="filter" type="button" id="publishCollectionButton" style="display: none;">
                                <svg>
                                    <use href="/resources/img/sprite.svg#cloud-load"></use>
                                </svg>
                                <span class="publish-label"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_publish') ?></span>
                            </button>
                            <button class="filter" id="editCollectionButton">
                                <svg>
                                    <use href="/resources/img/sprite.svg#gear"></use>
                                </svg>
                                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_edit_collection') ?>
                            </button>
                            <button class="filter" id="closeEditCollectionButton" style="display: none;">
                                <svg>
                                    <use href="/resources/img/sprite.svg#single-chevrone-left"></use>
                                </svg>
                                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_backPreview') ?>
                            </button>
                            <button class="filter active" id="deleteCheckedSkinsButton" style="display: none;">
                                <svg>
                                    <use href="/resources/img/sprite.svg#layers"></use>
                                </svg>
                                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_sc_checked_delete') ?>
                            </button>
                            <button class="filter" id="resetCollectionButton" style="display: none;">
                                <svg>
                                    <use href="/resources/img/sprite.svg#trash"></use>
                                </svg>
                                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_deleteAllSkins') ?>
                            </button>
                        </div>
                    </div>
                    <div class="sc__content">
                        <div class="sc__skins-fake-wrapper">
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                            <div class="sc__skins-card skeleton--default"></div>
                        </div>
                        <div class="sc__no-skins" style="display:none">
                            <div class="no-data"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_skins_not_installed') ?></div>
                            <button><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_install_skins') ?></button>
                        </div>

                        <div class="sc__skins-wrapper" id="mySkinsWrapper">

                        </div>
                    </div>
                </div>
                <div class="sc__loadout" id="communityLoadout" style="display:none;">
                    <div class="sc__header">
                        <div class="sc__loadout-title">
                            <svg>
                                <use href="/resources/img/sprite.svg#three-users"></use>
                            </svg>
                            <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_collections') ?>
                        </div>
                    </div>
                    <div class="sc__content">
                        <div id="communityCollectionsMain"></div>
                    </div>
                    <div id="communityPagination"></div>
                </div>
                <div class="sc__loadout" id="communityCollectionDetail" style="display:none;">
                    <div class="sc__header">
                        <div class="sc__loadout-title">
                            <button class="filter" id="backToCommunityList">
                                <svg>
                                    <use href="/resources/img/sprite.svg#single-chevrone-left"></use>
                                </svg>
                                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_backToCollections') ?>
                            </button>
                            <span id="communityCollectionDetailName"></span>
                        </div>
                        <div class="ml-auto flex-inline">
                            <button class="filter" type="button" id="communityDetailInstall">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy-list"></use>
                                </svg>
                                <span id="communityDetailInstallLabel"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_copy_collection') ?></span>
                            </button>
                            <button class="filter copy-btn" type="button" id="communityShareButton" data-clipboard-text="">
                                <svg>
                                    <use href="/resources/img/sprite.svg#share"></use>
                                </svg>
                                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_share_collection') ?>
                            </button>
                        </div>
                    </div>
                    <div class="sc__content">
                        <div class="sc__skins-wrapper" id="communityCollectionDetailSkins"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<div class="popup_modal" id="newCollection">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_create_collection_title') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form>
            <div class="sc__modal-info">
                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_create_collection_desc') ?>
            </div>
            <input name="collection-name" type="text" class="sc__modal-input" placeholder="<?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_collection_name') ?>" minlength="2" maxlength="50">
            <div class="inputs-inline" style="margin-block: .5rem">
                <input type="checkbox" id="visabilityCollection" class="switch">
                <label for="visabilityCollection"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_collection_visible_to_all') ?></label>
            </div>
            <button type="submit" class="sc__modal-button active width-100">
                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_create') ?>
            </button>
        </form>
    </div>
</div>

<div class="popup_modal" id="editCollectionName">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_edit_collection_name_title') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form>
            <div class="sc__modal-info">
                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_edit_collection_desc') ?>
            </div>
            <input value="Main collection" name="collection-name" type="text" class="sc__modal-input" placeholder="<?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_collection_name') ?>" minlength="2" maxlength="50">
            <div class="inputs-inline" style="margin-block: .5rem">
                <input type="checkbox" id="visabilityCollectionEdit" class="switch">
                <label for="visabilityCollectionEdit"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_collection_visible_to_all') ?></label>
            </div>
            <div class="flex-inline">
                <button type="submit" class="sc__modal-button width-100">
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_saveChanges') ?>
                </button>
                <button type="submit" class="sc__modal-button button-delete width-100">
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_delete_collection') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="popup_modal" id="copyCollection">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_copying_collection') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div>
            <div class="sc__modal-info">
                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_copy_collection_desc') ?>
            </div>
            <div class="sc__modal-field" style="margin-top:12px;">
                <input type="text" id="copyCollectionName" class="sc__input" maxlength="32"
                    placeholder="<?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_collection_name') ?>">
            </div>
        </div>
        <div class="sc__modal-buttons">
            <button class="flex-1">
                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_cancel') ?>
            </button>
            <button class="active flex-1">
                <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_copy_collection') ?>
            </button>
        </div>
    </div>
</div>

<div class="popup_modal" id="skinSettings">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head sc__modal-header-fix">
            <span id="modalTitleSkinSettings"></span>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div class="sc__modal-skin-settings">
            <div class="sc__modal-skin-left">
                <div class="flex-inline">
                    <input id="searchItem" type="search" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_enterName') ?>">
                    <div class="sc__modal-type-collectibles">
                        <label class="sc__modal-custom-radio">
                            <input type="radio" name="collectibles" value="sticker" checked>
                            <span>
                                <svg>
                                    <use href="/app/modules/module_page_skinchanger/assets/img/icons.svg#sticker"></use>
                                </svg>
                            </span>
                        </label>
                        <label class="sc__modal-custom-radio">
                            <input type="radio" name="collectibles" value="keychain">
                            <span>
                                <svg>
                                    <use href="/app/modules/module_page_skinchanger/assets/img/icons.svg#keychain"></use>
                                </svg>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="sc__modal-filter-stickers">
                    <div id="collectiblesRarityFilter" class="sc__filter-rarity">
                        <label class="sc__modal-custom-radio skeleton--default" style="border: 2px solid transparent;"><input type="radio"></label>
                        <label class="sc__modal-custom-radio skeleton--default" style="border: 2px solid transparent;"><input type="radio"></label>
                        <label class="sc__modal-custom-radio skeleton--default" style="border: 2px solid transparent;"><input type="radio"></label>
                        <label class="sc__modal-custom-radio skeleton--default" style="border: 2px solid transparent;"><input type="radio"></label>
                        <label class="sc__modal-custom-radio skeleton--default" style="border: 2px solid transparent;"><input type="radio"></label>
                        <label class="sc__modal-custom-radio skeleton--default" style="border: 2px solid transparent;"><input type="radio"></label>
                    </div>
                </div>
                <div class="sc__modal-collectibles-wrapper" id="collectiblesWrapper">
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                    <div class="sc__modal-sticker-keychain skeleton--default"></div>
                </div>
                <div class="sc__modal-collectibles-pagination" id="collectiblesPagination">
                    <div class="pagination skeleton--default" style="display: flex;">
                        <a class="button_pagination"></a>
                        <a class="button_pagination"></a>
                        <a class="button_pagination"></a>
                        <a class="button_pagination"></a>
                        <a class="button_pagination"></a>
                        <a class="button_pagination"></a>
                        <a class="button_pagination"></a>
                    </div>
                </div>
            </div>
            <div class="sc__modal-skin-right">
                <div class="sc__modal-skin-preview rarity_ancient_weapon">
                    <img class="lazy sc__modal-skin-preview-skin" data-src="https://cloud.cybershoke.net/img/csgoweapons/276.png" alt="">
                    <div class="sc__modal-skin-side"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_both_sides') ?></div>
                    <div class="sc__modal-decoration">
                        <div class="sc__modal-sticker-block" data-slot="0">
                            <div class="sc__modal-sticker-remove"><svg>
                                    <use href="/resources/img/sprite.svg#x"></use>
                                </svg></div>
                            <div class="sc__modal-sticker"><svg>
                                    <use href="/resources/img/sprite.svg#plus"></use>
                                </svg></div>
                        </div>
                        <div class="sc__modal-sticker-block" data-slot="1">
                            <div class="sc__modal-sticker-remove"><svg>
                                    <use href="/resources/img/sprite.svg#x"></use>
                                </svg></div>
                            <div class="sc__modal-sticker"><svg>
                                    <use href="/resources/img/sprite.svg#plus"></use>
                                </svg></div>
                        </div>
                        <div class="sc__modal-sticker-block" data-slot="2">
                            <div class="sc__modal-sticker-remove"><svg>
                                    <use href="/resources/img/sprite.svg#x"></use>
                                </svg></div>
                            <div class="sc__modal-sticker"><svg>
                                    <use href="/resources/img/sprite.svg#plus"></use>
                                </svg></div>
                        </div>
                        <div class="sc__modal-sticker-block" data-slot="3">
                            <div class="sc__modal-sticker-remove"><svg>
                                    <use href="/resources/img/sprite.svg#x"></use>
                                </svg></div>
                            <div class="sc__modal-sticker"><svg>
                                    <use href="/resources/img/sprite.svg#plus"></use>
                                </svg></div>
                        </div>
                        <div class="sc__modal-keychain-block">
                            <div class="sc__modal-keychain-remove"><svg>
                                    <use href="/resources/img/sprite.svg#x"></use>
                                </svg></div>
                            <div class="sc__modal-keychain"><svg>
                                    <use href="/resources/img/sprite.svg#plus"></use>
                                </svg></div>
                        </div>
                    </div>
                </div>
                <div class="sc__modal-st-sides">
                    <label class="sc__modal-stattrak" for="modalStatTrak">
                        <input type="checkbox" id="modalStatTrak" class="switch">
                        <span><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_stattrak') ?>
                            <svg data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_stattrakInfo') ?>" data-tippy-placement="top">
                                <use href="/resources/img/sprite.svg#info-circle"></use>
                            </svg>
                        </span>
                    </label>
                    <input id="stattrakCounter" type="nuumber" value="" name="" placeholder="99999" maxlength="6">
                    <div class="sc__modal-filter-sides">
                        <label class="sc__modal-custom-radio">
                            <input id="modalTSide" type="radio" name="filter-sides" value="0">
                            <span>
                                <img src="/app/modules/module_page_skinchanger/assets/img/sides/t.svg" alt="">
                            </span>
                        </label>
                        <label class="sc__modal-custom-radio">
                            <input id="modalBothSide" type="radio" name="filter-sides" value="2" checked>
                            <span>
                                <img src="/app/modules/module_page_skinchanger/assets/img/sides/both.svg" alt="">
                            </span>
                        </label>
                        <label class="sc__modal-custom-radio">
                            <input id="modalCtSide" type="radio" name="filter-sides" value="1">
                            <span>
                                <img src="/app/modules/module_page_skinchanger/assets/img/sides/ct.svg" alt="">
                            </span>
                        </label>
                    </div>
                </div>
                <div class="sc__modal-float-wrapper">
                    <div class="sc__modal-float-title" data-tippy-placement="bottom" aria-expanded="false" data-tippy-content='
                                <div class="sc__modal-float-description">
                                    <div><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_factory_new') ?>: <span>0.01 - 0.07</span></div>
                                    <div><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_minimal_wear') ?>: <span>0.07 - 0.15</span></div>
                                    <div><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_field_tested') ?>: <span>0.15 - 0.38</span></div>
                                    <div><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_well_worn') ?>: <span>0.38 - 0.45</span></div>
                                    <div><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_battle_scarred') ?>: <span>0.45 - 1.00</span></div>
                                </div>
                            '>
                        <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_float') ?>
                        <svg>
                            <use href="/resources/img/sprite.svg#info-circle"></use>
                        </svg>
                    </div>
                    <div class="sc__modal-float-value">
                        <div class="sc__modal-float-radios">
                            <label class="sc__modal-custom-radio">
                                <input id="factoryNew" type="radio" name="float-type" checked>
                                <span>FN</span>
                            </label>
                            <label class="sc__modal-custom-radio">
                                <input id="minimalWear" type="radio" name="float-type">
                                <span>MW</span>
                            </label>
                            <label class="sc__modal-custom-radio">
                                <input id="filedTested" type="radio" name="float-type">
                                <span>FT</span>
                            </label>
                            <label class="sc__modal-custom-radio">
                                <input id="wellWorm" type="radio" name="float-type">
                                <span>WW</span>
                            </label>
                            <label class="sc__modal-custom-radio">
                                <input id="battleScared" type="radio" name="float-type">
                                <span>BS</span>
                            </label>
                        </div>
                        <input class="ml-auto" style="width: 4.5rem" id="floatValue" type="number" value="" name="" placeholder="0.0000" step="0.0001" min="0" max="1">
                    </div>
                    <input type="range" class="sc__modal-range sc__modal-range-float" min="0.0001" max="0.9999" step="0.0001" value="0.0001" id="floatRange">
                    <div class="sc__modal-pattern-value">
                        <div class="sc__modal-pattern-title" data-tippy-placement="bottom" data-tippy-content='<?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_pattern2') ?>'>
                            <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_pattern') ?>
                            <svg>
                                <use href="/resources/img/sprite.svg#info-circle"></use>
                            </svg>
                        </div>
                        <input class="ml-auto" style="width: 4.5rem" id="patternValue" type="number" value="" name="" placeholder="0.0000" step="0.0001" min="0" max="1">
                    </div>
                    <input type="range" class="sc__modal-range sc__modal-range-pattern" min="0" max="1000" step="1" value="0" id="patternRange">
                </div>
                <div class="inputs-inline sc__modal-name-tag">
                    <label for="nameTag"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_nameTag') ?></label>
                    <input id="nameTag" type="text" value="" name="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_placeholder_nameTag') ?>" maxlength="20">
                </div>
                <button class="width-100 active sc__modal-save-button"><?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_apply_changes') ?></button>
            </div>
        </div>
    </div>
</div>