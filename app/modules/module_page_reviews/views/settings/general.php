<div class="at__settings-content">
    <h3 class="at__settings-title">
        <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_generalTitle') ?></h3>
    <div class="at__sections-wrapper">
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_accessTitle') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_minHoursHint') ?>
            </div>
            <div class="inputs-inline at__fixed-checkbox">
                <input type="checkbox" id="rvMinHoursEnabled" class="switch" <?= $minHoursEnabled ? 'checked' : '' ?>>
                <label for="rvMinHoursEnabled"
                    class="at__fixed-label"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_minHoursEnable') ?></label>
            </div>
            <div class="inputs-inline at__mb0">
                <label
                    for="rvMinHours"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_minHours') ?></label>
                <div class="number" id="rvMinHoursControl">
                    <button class="number-minus" type="button">-</button>
                    <input id="rvMinHours" type="number" min="0" step="1" value="<?= $minHours ?>" <?= $minHoursEnabled ? '' : 'disabled' ?>>
                    <button class="number-plus" type="button">+</button>
                </div>
            </div>
        </div>
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_rewardTitle') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_rewardHint') ?>
            </div>
            <?php if (empty($lkConnected)): ?>
                <div class="at__settings-info-text" style="color: var(--red, #e57373);">
                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_lkMissing') ?>
                </div>
            <?php endif; ?>
            <div class="inputs-inline at__fixed-checkbox">
                <input type="checkbox" id="rvRewardEnabled" class="switch" <?= $rewardEnabled ? 'checked' : '' ?>
                    <?= empty($lkConnected) ? 'disabled' : '' ?>>
                <label for="rvRewardEnabled"
                    class="at__fixed-label"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_rewardEnable') ?></label>
            </div>
            <div class="inputs-inline at__mb0">
                <label
                    for="rvRewardAmount"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_rewardAmount') ?></label>
                <div class="number" id="rvRewardAmountControl">
                    <button class="number-minus" type="button">-</button>
                    <input id="rvRewardAmount" type="number" min="0" step="1" value="<?= $rewardAmount ?>"
                        <?= empty($lkConnected) ? 'disabled' : '' ?>>
                    <button class="number-plus" type="button">+</button>
                </div>
            </div>
        </div>
    </div>

    <div class="at__sections-wrapper">
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistTitle') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistHint') ?>
            </div>
            <div class="inputs-inline at__fixed-checkbox">
                <input type="checkbox" id="rvBlacklistEnabled" class="switch" <?= $blacklistEnabled ? 'checked' : '' ?>>
                <label for="rvBlacklistEnabled"
                    class="at__fixed-label"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistEnable') ?></label>
            </div>
            <div class="inputs-inline">
                <label
                    for="rvBlacklistWords"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistWords') ?></label>
                <textarea id="rvBlacklistWords" rows="3"
                    placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistPlaceholder') ?>"
                    <?= $blacklistEnabled ? '' : 'disabled' ?>><?= action_text_clear($blacklistWords) ?></textarea>
            </div>
            <div class="inputs-inline">
                <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistMode') ?></label>
                <div class="adaptive-select-wrapper<?= $blacklistEnabled ? '' : ' is-disabled' ?>"
                    id="rvBlacklistModeWrap" data-change-icon>
                    <ul class="adaptive-select__dropdown-list" id="rvBlacklistModeList">
                        <li>
                            <label class="adaptive-select__label" for="rvBlacklistModeCensor">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#eye"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistModeCensor') ?>
                                </div>
                                <input class="hide-input" id="rvBlacklistModeCensor" type="radio"
                                    name="rv-blacklist-mode" value="censor" <?= $blacklistMode === 'censor' ? 'checked' : '' ?> <?= $blacklistEnabled ? '' : 'disabled' ?>>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="rvBlacklistModeBlock">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#block"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistModeBlock') ?>
                                </div>
                                <input class="hide-input" id="rvBlacklistModeBlock" type="radio"
                                    name="rv-blacklist-mode" value="block" <?= $blacklistMode === 'block' ? 'checked' : '' ?> <?= $blacklistEnabled ? '' : 'disabled' ?>>
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="rvBlacklistModeList">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use
                                    href="/resources/img/sprite.svg#<?= $blacklistMode === 'block' ? 'block' : 'eye' ?>">
                                </use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text">
                            <?= $blacklistMode === 'block'
                                ? $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistModeBlock')
                                : $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistModeCensor') ?>
                        </span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="inputs-inline at__mb0">
                <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistStyle') ?></label>
                <div class="adaptive-select-wrapper<?= $blacklistStyleEnabled ? '' : ' is-disabled' ?>"
                    id="rvBlacklistStyleWrap" data-change-icon>
                    <ul class="adaptive-select__dropdown-list" id="rvBlacklistStyleList">
                        <li>
                            <label class="adaptive-select__label" for="rvBlacklistStyleHearts">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#heart"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistHearts') ?>
                                </div>
                                <input class="hide-input" id="rvBlacklistStyleHearts" type="radio"
                                    name="rv-blacklist-style" value="hearts" <?= $blacklistStyle === 'hearts' ? 'checked' : '' ?> <?= $blacklistStyleEnabled ? '' : 'disabled' ?>>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="rvBlacklistStyleStars">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#star-fill"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistStars') ?>
                                </div>
                                <input class="hide-input" id="rvBlacklistStyleStars" type="radio"
                                    name="rv-blacklist-style" value="stars" <?= $blacklistStyle === 'stars' ? 'checked' : '' ?> <?= $blacklistStyleEnabled ? '' : 'disabled' ?>>
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="rvBlacklistStyleList">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use
                                    href="/resources/img/sprite.svg#<?= $blacklistStyle === 'stars' ? 'star-fill' : 'heart' ?>">
                                </use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text">
                            <?= $blacklistStyle === 'stars'
                                ? $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistStars')
                                : $Translate->get_translate_module_phrase('module_page_reviews', '_rv_blacklistHearts') ?>
                        </span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_discordTitle') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_discordHint') ?>
            </div>
            <div class="inputs-inline">
                <label for="rvDiscordWebhookUrl">
                    <svg>
                        <use href="/resources/img/sprite.svg#ds"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_discordWebhookUrl') ?>
                </label>
                <div class="number">
                    <input id="rvDiscordWebhookUrl" type="password" value="<?= action_text_clear($discordWebhookUrl) ?>"
                        placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_discordWebhookUrlPlaceholder') ?>"
                        autocomplete="off">
                    <div class="eye-password" id="rvShowDiscordWebhookPass">
                        <svg>
                            <use href="/resources/img/sprite.svg#eye"></use>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="inputs-inline">
                <label
                    for="rvDiscordWebhookColor"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_discordWebhookColor') ?></label>
                <input id="rvDiscordWebhookColor" type="text" value="<?= action_text_clear($discordWebhookColor) ?>"
                    data-jscolor="{format:'hex', alphaChannel:false, value:'<?= action_text_clear($discordWebhookColor) ?>'}">
            </div>
            <div class="inputs-inline at__mb0">
                <label
                    for="rvDiscordWebhookImage"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_discordWebhookImage') ?></label>
                <input id="rvDiscordWebhookImage" type="text" value="<?= action_text_clear($discordWebhookImage) ?>"
                    placeholder="https://site.com/img/banner.png">
            </div>
        </div>
    </div>

    <div class="at__sections-wrapper">
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_ratingIconTitle') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_ratingIconHint') ?>
            </div>
            <div class="inputs-inline at__mb0">
                <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_ratingIconLabel') ?></label>
                <div class="adaptive-select-wrapper" data-change-icon>
                    <ul class="adaptive-select__dropdown-list" id="rvRatingIconList">
                        <div class="inputs-inline">
                            <svg>
                                <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                            </svg>
                            <input id="rvRatingIconSearch" type="search" value=""
                                placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_findIcon') ?>"
                                autocomplete="off">
                        </div>
                        <?php foreach ($ratingIcons as $iconId): ?>
                            <li>
                                <label class="adaptive-select__label" for="rvRatingIcon-<?= action_text_clear($iconId) ?>">
                                    <span class="adaptive-select__icon">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#<?= action_text_clear($iconId) ?>"></use>
                                        </svg>
                                    </span>
                                    <div class="adaptive-select__label-text"><?= action_text_clear($iconId) ?></div>
                                    <input class="hide-input" id="rvRatingIcon-<?= action_text_clear($iconId) ?>"
                                        type="radio" name="rv-rating-icon" value="<?= action_text_clear($iconId) ?>"
                                        <?= $ratingIcon === $iconId ? 'checked' : '' ?>>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="rvRatingIconList">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#<?= action_text_clear($ratingIcon) ?>"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= action_text_clear($ratingIcon) ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_designTitle') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_designHint') ?>
            </div>
            <div class="inputs-inline at__mb0">
                <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_listColumns') ?></label>
                <div class="adaptive-select-wrapper" id="rvListColumnsWrap" data-change-icon>
                    <ul class="adaptive-select__dropdown-list" id="rvListColumnsList">
                        <li>
                            <label class="adaptive-select__label" for="rvListColumnsOne">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#list"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_listColumnsOne') ?>
                                </div>
                                <input class="hide-input" id="rvListColumnsOne" type="radio"
                                    name="rv-list-columns" value="1" <?= (int) $listColumns === 1 ? 'checked' : '' ?>>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="rvListColumnsTwo">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#grid-elements"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_listColumnsTwo') ?>
                                </div>
                                <input class="hide-input" id="rvListColumnsTwo" type="radio"
                                    name="rv-list-columns" value="2" <?= (int) $listColumns === 2 ? 'checked' : '' ?>>
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="rvListColumnsList">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#<?= action_text_clear($listColumnsIcon) ?>"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text">
                            <?= action_text_clear($listColumnsLabel) ?>
                        </span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="at__sections-wrapper">
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_uxTitle') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_fieldsHint') ?>
            </div>
            <div class="inputs-inline">
                <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_fieldsMode') ?></label>
                <div class="adaptive-select-wrapper" id="rvFieldsModeWrap" data-change-icon>
                    <ul class="adaptive-select__dropdown-list" id="rvFieldsModeList">
                        <li>
                            <label class="adaptive-select__label" for="rvFieldsModeComment">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chat"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_fieldsModeComment') ?>
                                </div>
                                <input class="hide-input" id="rvFieldsModeComment" type="radio"
                                    name="rv-fields-mode" value="comment" <?= $fieldsMode === 'comment' ? 'checked' : '' ?>>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="rvFieldsModeProsCons">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#list"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_fieldsModeProsCons') ?>
                                </div>
                                <input class="hide-input" id="rvFieldsModeProsCons" type="radio"
                                    name="rv-fields-mode" value="pros_cons" <?= $fieldsMode === 'pros_cons' ? 'checked' : '' ?>>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="rvFieldsModeAll">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#layers"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_fieldsModeAll') ?>
                                </div>
                                <input class="hide-input" id="rvFieldsModeAll" type="radio"
                                    name="rv-fields-mode" value="all" <?= $fieldsMode === 'all' ? 'checked' : '' ?>>
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="rvFieldsModeList">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#<?= action_text_clear($fieldsModeIcon) ?>"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text">
                            <?= action_text_clear($fieldsModeLabel) ?>
                        </span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="inputs-inline at__fixed-checkbox at__mb0">
                <input type="checkbox" id="rvServerSelectEnabled" class="switch" <?= !empty($serverSelectEnabled) ? 'checked' : '' ?>>
                <label for="rvServerSelectEnabled"
                    class="at__fixed-label"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_serverSelectEnable') ?></label>
            </div>
        </div>
        <div class="at__settings-section">
            <h4 class="at__settings-subtitle">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_migrationTitle') ?></h4>
            <hr>
            <?php if (!empty($canMigrateLegacy)): ?>
                <div class="at__settings-info-text">
                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_migrationHint') ?>
                </div>
                <button type="button" class="at__button-setting active" id="rvMigrateLegacy">
                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_migrationRun') ?>
                </button>
            <?php else: ?>
                <div class="at__settings-info-text">
                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_migrationMissing') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>