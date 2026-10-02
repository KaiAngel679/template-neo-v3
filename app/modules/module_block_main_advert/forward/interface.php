<?php if (!empty($_SESSION['steamid']) && isset($_SESSION['user_admin'])): ?>
    <script src="<?= $General->arr_general['site'] ?>storage/assets/js/Sortable.min.js"></script>
<?php endif; ?>
<div class="row">
    <div class="col-md-12">
        <div class="info-block__wrapper-global">
            <div class="info-block__wrapper" id="sortable-banners">
                <?php if (empty($banners)): ?>
                    <div target="_blank" class="info-block__card">
                        <h3 class="info-block__title" style="color: #fff"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_exampleTitle'); ?></h3>
                        <span class="info-block__description" style="color: #fff"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_exampleDescription'); ?></span>
                        <img class="info-block__image" src="<?= $General->arr_general['site'] ?>app/modules/module_block_main_advert/assets/img/null-image.svg" alt="" title="">
                        <?php if (!empty($_SESSION['steamid']) && isset($_SESSION['user_admin'])): ?>
                            <div class="info-block__action">
                                <button onclick="event.preventDefault();" data-openmodal="addBanner">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#plus"></use>
                                    </svg>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($banners as $banner): ?>
                        <a href="<?= $banner['url'] ?>" data-bannerid="<?= $banner['id'] ?>" target="_blank" class="info-block__card" style="background-image: linear-gradient(to right, <?= $banner['color_bg_start'] ?>, <?= $banner['color_bg_end'] ?>);">
                            <h3 class="info-block__title" style="color: <?= $banner['color_text'] ?>"><?= $banner['text_main'] ?></h3>
                            <span class="info-block__description" style="color: <?= $banner['color_other_text'] ?>"><?= $banner['text_other'] ?></span>
                            <img class="info-block__image" src="/app/modules/module_block_main_advert/assets/img/<?= $banner['file'] ?>" alt="" title="">
                            <?php if (!empty($_SESSION['steamid']) && isset($_SESSION['user_admin'])): ?>
                                <div class="info-block__action">
                                    <button onclick="event.preventDefault();" data-openmodal="editBanner<?= $banner['id'] ?>">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#gear"></use>
                                        </svg>
                                    </button>
                                    <button onclick="event.preventDefault();" id="del_banner" data-banner="<?= $banner['id'] ?>">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#trash"></use>
                                        </svg>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if (!empty($_SESSION['steamid']) && isset($_SESSION['user_admin'])): ?>
                        <div target="_blank" class="info-block__card small filtered">
                            <img class="info-block__image" src="<?= $General->arr_general['site'] ?>app/modules/module_block_main_advert/assets/img/null-image.svg" alt="" title="">
                            <div class="info-block__action">
                                <button onclick="event.preventDefault();" data-openmodal="addBanner">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#plus"></use>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php foreach ($banners as $banner): ?>
                <?php if (!empty($_SESSION['steamid']) && isset($_SESSION['user_admin'])): ?>
                    <div class="popup_modal" id="editBanner<?= $banner['id'] ?>">
                        <div class="popup_modal_content no-close no-scrollbar">
                            <form id="editBannerForm<?= $banner['id'] ?>" method="post" enctype="multipart/form-data">
                                <input name="editBanner" value="<?= $banner['id'] ?>" hidden>
                                <div class="popup_modal_head">
                                    <?= $Translate->get_translate_module_phrase('module_block_main_advert', '_editingBanner'); ?>
                                    <span class="popup_modal_close">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#x"></use>
                                        </svg>
                                    </span>
                                </div>
                                <div class="inputs-inline">
                                    <label for="linkBanner<?= $banner['id'] ?>"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_link'); ?></label>
                                    <input id="linkBanner<?= $banner['id'] ?>" name="url" type="text" value="<?= $banner['url'] ?>" placeholder="<?= $Translate->get_translate_module_phrase('module_block_main_advert', '_linkPlaceholder'); ?>" required>
                                </div>
                                <div class="flex-inline">
                                    <div class="inputs-inline">
                                        <label for="generalText<?= $banner['id'] ?>"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_mainText'); ?></label>
                                        <input id="generalText<?= $banner['id'] ?>" name="text_main" value="<?= $banner['text_main'] ?>" type="text" placeholder="<?= $Translate->get_translate_module_phrase('module_block_main_advert', '_mainTextPlaceholder'); ?>" required>
                                    </div>
                                    <div class="inputs-inline">
                                        <label for=""><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_mainTextColor'); ?></label>
                                        <input type="text" name="color_text" class="color_input" value="<?= $banner['color_text'] ?>" data-jscolor="">
                                    </div>
                                </div>
                                <div class="flex-inline">
                                    <div class="inputs-inline">
                                        <label for="secondaryText"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_additionalText'); ?></label>
                                        <input id="secondaryText" name="text_other" type="text" value="<?= $banner['text_other'] ?>" placeholder="<?= $Translate->get_translate_module_phrase('module_block_main_advert', '_additionalTextPlaceholder'); ?>" required>
                                    </div>
                                    <div class="inputs-inline">
                                        <label><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_additionalTextColor'); ?></label>
                                        <input type="text" name="color_other_text" class="color_input" value="<?= $banner['color_other_text'] ?>" data-jscolor="">
                                    </div>
                                </div>
                                <hr>
                                <div class="flex-inline">
                                    <div class="inputs-inline">
                                        <label><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_initialBgColor'); ?></label>
                                        <input type="text" name="color_bg_start" class="color_input" value="<?= $banner['color_bg_start'] ?>" data-jscolor="">
                                    </div>
                                    <div class="inputs-inline">
                                        <label><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_finalBgColor'); ?></label>
                                        <input type="text" name="color_bg_end" class="color_input" value="<?= $banner['color_bg_end'] ?>" data-jscolor="">
                                    </div>
                                </div>
                                <hr>
                                <div class="inputs-inline">
                                    <div class="file-upload-container">
                                        <input type="file" form="editBannerForm<?= $banner['id'] ?>" id="file-input<?= $banner['id'] ?>" class="custom-file-input" accept="image/*" name="file">
                                        <label for="file-input<?= $banner['id'] ?>"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_selectImage'); ?></label>
                                        <div id="file-info" class="file-upload-info file-info" style="display: block"><?= $banner['file'] ?></div>
                                    </div>
                                </div>
                                <hr>
                                <div target="_blank" class="info-block__card" id="infoBlockCard" style="flex: none; background-image: linear-gradient(to right, <?= $banner['color_bg_start'] ?>, <?= $banner['color_bg_end'] ?>);">
                                    <h3 class="info-block__title" style="color: <?= $banner['color_text'] ?>" id="generalTextPreview"><?= $banner['text_main'] ?></h3>
                                    <span class="info-block__description" style="color: <?= $banner['color_other_text'] ?>" id="secondaryTextPreview"><?= $banner['text_other'] ?></span>
                                    <img class="info-block__image bannerPreview" id="bannerPreview" src="/app/modules/module_block_main_advert/assets/img/<?= $banner['file'] ?>" alt="" title="">
                                </div>
                                <hr>
                                <button class="width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_editBanner'); ?></button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['steamid']) && isset($_SESSION['user_admin'])): ?>
    <div class="popup_modal" id="addBanner">
        <div class="popup_modal_content no-close no-scrollbar">
            <form id="addBannerForm" method="post" enctype="multipart/form-data">
                <div class="popup_modal_head">
                    <?= $Translate->get_translate_module_phrase('module_block_main_advert', '_addingBanner'); ?>
                    <span class="popup_modal_close">
                        <svg>
                            <use href="/resources/img/sprite.svg#x"></use>
                        </svg>
                    </span>
                </div>
                <div class="inputs-inline">
                    <label for="linkBanner"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_link'); ?></label>
                    <input id="linkBanner" name="url" type="text" placeholder="<?= $Translate->get_translate_module_phrase('module_block_main_advert', '_linkPlaceholder'); ?>" required>
                </div>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for="generalText"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_mainText'); ?></label>
                        <input id="generalText" name="text_main" type="text" placeholder="<?= $Translate->get_translate_module_phrase('module_block_main_advert', '_mainTextPlaceholder'); ?>" required>
                    </div>
                    <div class="inputs-inline">
                        <label for=""><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_mainTextColor'); ?></label>
                        <input type="text" name="color_text" class="color_input" value="" data-jscolor="">
                    </div>
                </div>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for="secondaryText"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_additionalText'); ?></label>
                        <input id="secondaryText" name="text_other" type="text" placeholder="<?= $Translate->get_translate_module_phrase('module_block_main_advert', '_additionalTextPlaceholder'); ?>" required>
                    </div>
                    <div class="inputs-inline">
                        <label for=""><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_additionalTextColor'); ?></label>
                        <input type="text" name="color_other_text" class="color_input" value="" data-jscolor="">
                    </div>
                </div>
                <hr>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for=""><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_initialBgColor'); ?></label>
                        <input type="text" name="color_bg_start" class="color_input" value="" data-jscolor="">
                    </div>
                    <div class="inputs-inline">
                        <label for=""><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_finalBgColor'); ?></label>
                        <input type="text" name="color_bg_end" class="color_input" value="" data-jscolor="">
                    </div>
                </div>
                <hr>
                <div class="inputs-inline">
                    <div class="file-upload-container">
                        <input type="file" id="file-input" class="custom-file-input" accept="image/*" name="file">
                        <label for="file-input"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_selectImage'); ?></label>
                        <div id="file-info" class="file-upload-info file-info" style="display: block"></div>
                    </div>
                </div>
                <hr>
                <div target="_blank" class="info-block__card" id="infoBlockCard" style="flex: none;">
                    <h3 class="info-block__title" style="color: #fff" id="generalTextPreview"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_mainText'); ?></h3>
                    <span class="info-block__description" style="color: #fff" id="secondaryTextPreview"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_additionalText'); ?></span>
                    <img class="info-block__image bannerPreview" id="bannerPreview" src="<?= $General->arr_general['site'] ?>app/modules/module_block_main_advert/assets/img/null-image.svg" alt="" title="">
                </div>
                <hr>
                <button class="width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_block_main_advert', '_CreateBanner'); ?></button>
            </form>
        </div>
    </div>
<?php endif; ?>