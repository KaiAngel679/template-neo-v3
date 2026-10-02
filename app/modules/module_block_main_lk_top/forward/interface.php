<?php if ($lk_open_row): ?>
    <div class="row">
    <?php endif; ?>
    <div class="<?= $lk_col_class ?>">
        <div class="card lk-top__card">
            <div class="lk-top__wrapper">
                <h4 class="lk-top__title" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_lk_top', '_infoUpdate') ?>" data-tippy-placement="top">
                    <svg>
                        <use href="/resources/img/sprite.svg#question"></use>
                    </svg>
                    <span><?= $Translate->get_translate_module_phrase('module_block_main_lk_top', '_topDonors') ?></span>
                </h4>
                <div class="lk-top__buttons">
                    <button class="filter lk-top__chips-btn <?= !$lk_full ? 'width-100' : '' ?> active" data-show-id="lktop7d"><?= $Translate->get_translate_module_phrase('module_block_main_lk_top', '_sevenDays') ?></button>
                    <button class="filter lk-top__chips-btn <?= !$lk_full ? 'width-100' : '' ?>" data-show-id="lktop30d"><?= $Translate->get_translate_module_phrase('module_block_main_lk_top', '_thirtyDays') ?></button>
                    <button class="filter lk-top__chips-btn <?= !$lk_full ? 'width-100' : '' ?>" data-show-id="lktopAll"><?= $Translate->get_translate_module_phrase('module_block_main_lk_top', '_allTime') ?></button>
                </div>
                <div class="lk-top__content-wrapper">
                    <div>
                        <div id="lk-top-loaders" class="<?= $lk_full ? 'lk-top__content-line' : 'lk-top__content' ?>">
                            <div class="loader"></div>
                            <div class="loader"></div>
                            <div class="loader"></div>
                        </div>
                        <div id="lk-top-content" class="<?= $lk_full ? 'lk-top__content-line' : 'lk-top__content' ?>"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($lk_close_row): ?>
    </div>
<?php endif; ?>