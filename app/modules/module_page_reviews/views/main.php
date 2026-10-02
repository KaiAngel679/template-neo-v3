<div class="rv__layout">
    <div class="rv__sidebar aside__wrapper">
        <aside class="rv__sidebar-card card">
            <div class="rv__summary">
                <div class="rv__summary-top">
                    <div class="rv__score"><?= action_text_clear($summary['avg_label']) ?></div>
                    <div class="rv__summary-meta">
                        <div class="rv__stars" style="--rv-rating: <?= action_text_clear($summary['avg_label']) ?>"
                            aria-label="<?= str_replace('%avg%', action_text_clear($summary['avg_label']), $Translate->get_translate_module_phrase('module_page_reviews', '_rv_ratingAria')) ?>">
                            <span class="rv__stars-fill">
                                <svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg>
                                <svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg>
                                <svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg>
                                <svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg>
                                <svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg>
                            </span>
                            <span class="rv__stars-base" aria-hidden="true">
                                <svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg>
                                <svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg>
                                <svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg>
                                <svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg>
                                <svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg>
                            </span>
                        </div>
                        <div class="rv__summary-count">
                            <?= str_replace('%count%', action_text_clear($summary['total_label']), $Translate->get_translate_module_phrase('module_page_reviews', '_rv_reviewsCount')) ?>
                        </div>
                    </div>
                </div>

                <ul class="rv__dist" id="rvDist">
                    <?php foreach ($summary['bars'] as $bar): ?>
                        <li>
                            <span class="rv__dist-label"><?= (int) $bar['star'] ?></span>
                            <div class="rv__dist-bar"><span style="width: <?= (float) $bar['pct'] ?>%"
                                    data-count="<?= (int) $bar['count'] ?>"></span></div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="rv__attrs<?= $summary['attrs'] === [] ? ' is-empty' : '' ?>" id="rvAttrs"
                    <?= $summary['attrs'] === [] ? ' hidden' : '' ?>>
                    <?php foreach ($summary['attrs'] as $attr): ?>
                        <div class="rv__attr" data-attr="<?= (int) ($attr['id'] ?? 0) ?>">
                            <div class="rv__attr-score"><?= action_text_clear($attr['avg_label']) ?></div>
                            <div class="rv__attr-name"><?= action_text_clear((string) $attr['name']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="rv__cta">
                    <div class="rv__cta-title">
                        <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_ctaTitle') ?></div>
                    <div class="rv__cta-text">
                        <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_ctaText') ?></div>
                    <?php if (empty($isAuth)): ?>
                        <button class="active width-100" type="button"
                            onclick="location.href='?auth=login'"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_loginToReview') ?></button>
                    <?php elseif (!empty($reviewBan)): ?>
                        <button class="width-100" type="button" disabled><?= $reviewBanText ?></button>
                    <?php elseif (empty($canReviewByHours)): ?>
                        <button class="width-100" type="button" disabled>
                            <?= str_replace(
                                '%left%',
                                (string) $hoursLeft,
                                $Translate->get_translate_module_phrase('module_page_reviews', '_rv_minHoursButton')
                            ) ?>
                        </button>
                    <?php else: ?>
                        <button class="active width-100" type="button"
                            data-openmodal="addReview"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_addReview') ?></button>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
        <?php if (!empty($showRewardBanner)): ?>
            <div class="rv__exchange-wrapper">
                <div class="rv__exchange-text">
                    <?= action_text_clear($rewardBannerText) ?>
                </div>
                <img src="/app/modules/module_page_reviews/assets/img/exchange.webp" alt="exchange">
            </div>
        <?php endif; ?>
    </div>

    <div class="rv__main">
        <div class="rv__toolbar-row">
            <div class="rv__search">
                <input id="rvSearch" type="search" maxlength="100"
                    placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_searchPlaceholder') ?>"
                    autocomplete="off">
            </div>

            <div class="rv__filters">
                <div class="adaptive-select-wrapper" data-no-text>
                    <ul class="adaptive-select__dropdown-list" id="rvStarsList">
                        <?php foreach ($summary['bars'] as $bar): ?>
                            <li>
                                <label class="adaptive-select__label" for="rvStar<?= (int) $bar['star'] ?>">
                                    <div class="adaptive-select__label-text">
                                        <span class="rv__filter-stars">
                                            <?php for ($i = 0; $i < (int) $bar['star']; $i++): ?>
                                                <svg>
                                                    <use href="<?= $ratingUse ?>"></use>
                                                </svg>
                                            <?php endfor; ?>
                                        </span>
                                        <em><?= (int) $bar['count'] ?></em>
                                    </div>
                                    <input class="hide-input" id="rvStar<?= (int) $bar['star'] ?>" type="checkbox"
                                        name="rv-star" value="<?= (int) $bar['star'] ?>">
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="rvStarsList">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                        </span>
                        <span
                            class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_filterRating') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="adaptive-select-wrapper" data-change-icon>
                    <ul class="adaptive-select__dropdown-list" id="rvSortList">
                        <li>
                            <label class="adaptive-select__label" for="rvSortDate">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#filterUp"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_sortByDate') ?>
                                </div>
                                <input class="hide-input" id="rvSortDate" type="radio" name="rv-sort" value="date"
                                    checked>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="rvSortPopular">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#like"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_sortByPopular') ?>
                                </div>
                                <input class="hide-input" id="rvSortPopular" type="radio" name="rv-sort"
                                    value="popular">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="rvSortRating">
                                <span class="adaptive-select__icon">
                                    <svg>
                                        <use href="<?= $ratingUse ?>"></use>
                                    </svg>
                                </span>
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_sortByRating') ?>
                                </div>
                                <input class="hide-input" id="rvSortRating" type="radio" name="rv-sort" value="rating">
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="rvSortList">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#filterUp"></use>
                            </svg>
                        </span>
                        <span
                            class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_sortByDate') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>

            <button class="button-delete rv__button-reset" id="rvResetFilters" type="button"
                title="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_resetFilters') ?>">
                <svg>
                    <use href="/resources/img/sprite.svg#broom"></use>
                </svg>
            </button>
        </div>

        <div class="rv__empty" id="rvReviewsEmpty" hidden>
            <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_noReviews') ?></div>
        <div class="rv__list<?= ((int) ($listColumns ?? 1) === 2) ? ' rv__list--cols-2' : '' ?>" id="rvReviewsList" data-is-auth="<?= !empty($isAuth) ? '1' : '0' ?>"
            data-is-admin="<?= !empty($isAdmin) ? '1' : '0' ?>"
            data-viewer-avatar="<?= action_text_clear((string) ($viewerAvatar ?? '')) ?>"
            data-rating-icon="<?= action_text_clear($ratingIcon) ?>"
            data-has-criteria="<?= !empty($hasCriteria) ? '1' : '0' ?>"
            data-fields-mode="<?= action_text_clear((string) ($fieldsMode ?? 'all')) ?>"
            data-list-columns="<?= (int) ($listColumns ?? 1) ?>"
            data-ban-comment="<?= !empty($commentBan) ? '1' : '0' ?>"
            data-ban-comment-reason="<?= action_text_clear((string) ($commentBan['reason'] ?? '')) ?>"
            data-ban-reply="<?= !empty($replyBan) ? '1' : '0' ?>"
            data-ban-reply-reason="<?= action_text_clear((string) ($replyBan['reason'] ?? '')) ?>"
            data-ban-vote="<?= !empty($voteBan) ? '1' : '0' ?>"
            data-ban-vote-reason="<?= action_text_clear((string) ($voteBan['reason'] ?? '')) ?>">
            <?php for ($i = 0; $i < 10; $i++): ?>
                <article class="rv__review card skeleton--default"></article>
            <?php endfor; ?>
        </div>
        <div id="rvReviewsPagination"></div>
    </div>
</div>

<div class="popup_modal" id="addReview">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_addReview') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div class="scroll no-scrollbar">
            <?php if (!empty($showServerSelect)): ?>
                <div class="inputs-inline">
                    <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_server') ?></label>
                </div>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="rvServerList">
                        <div class="inputs-inline">
                            <svg>
                                <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                            </svg>
                            <input id="searchRvServer" type="search" value=""
                                placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_findServer') ?>"
                                autocomplete="off">
                        </div>
                        <li>
                            <label class="adaptive-select__label" for="rvServerAll">
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_allServers') ?>
                                </div>
                                <input class="hide-input" id="rvServerAll" type="radio" value="-1" name="rv-server" checked>
                            </label>
                        </li>
                        <?php foreach ($servers as $srv): ?>
                            <li>
                                <label class="adaptive-select__label" for="rvServer<?= (int) $srv['id'] ?>">
                                    <div class="adaptive-select__label-text"><?= action_text_clear($srv['name_custom']) ?></div>
                                    <span
                                        class="at__cs-label <?= action_text_clear($srv['badge']) ?>"><?= action_text_clear($srv['badge_text']) ?></span>
                                    <input class="hide-input" id="rvServer<?= (int) $srv['id'] ?>" type="radio" name="rv-server"
                                        value="<?= (int) $srv['id'] ?>">
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="rvServerList">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#servers"></use>
                            </svg>
                        </span>
                        <span
                            class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_selectServer') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <hr>
            <?php else: ?>
                <input class="hide-input" type="radio" value="-1" name="rv-server" checked>
            <?php endif; ?>
            <?php if (!empty($hasCriteria)): ?>
                <div class="inputs-inline">
                    <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_rateCriteria') ?></label>
                </div>
                <ul class="rv__modal-attrs" id="rvAddAttrs">
                    <?php foreach ($criteria as $attr): ?>
                        <li>
                            <span class="parametr"><?= action_text_clear((string) $attr['name']) ?>:</span>
                            <span class="value">
                                <div class="rv__rate rv__rate--sm" role="radiogroup"
                                    aria-label="<?= action_text_clear((string) $attr['name']) ?>">
                                    <?php for ($s = 5; $s >= 1; $s--): ?>
                                        <input type="radio" name="attrs[<?= (int) $attr['id'] ?>]"
                                            id="rvAttr<?= (int) $attr['id'] . $s ?>" value="<?= $s ?>">
                                        <label for="rvAttr<?= (int) $attr['id'] . $s ?>"><svg>
                                                <use href="<?= $ratingUse ?>"></use>
                                            </svg></label>
                                    <?php endfor; ?>
                                </div>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <hr>
                <div class="rv__modal-overall">
                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_avgRating') ?>
                    <div class="rv__stars rv__stars--sm" id="rvOverallStars" style="--rv-rating: 0" aria-hidden="true">
                        <span class="rv__stars-fill">
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                        </span>
                        <span class="rv__stars-base">
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                        </span>
                    </div>
                    <span id="rvOverallValue">(-)</span>
                </div>
            <?php else: ?>
                <div class="inputs-inline">
                    <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_overall') ?></label>
                </div>
                <div class="rv__modal-overall-pick">
                    <div class="rv__rate" data-rv-overall-rate role="radiogroup"
                        aria-label="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_overall') ?>">
                        <?php for ($s = 5; $s >= 1; $s--): ?>
                            <input type="radio" name="rv-overall" id="rvOverall<?= $s ?>" value="<?= $s ?>">
                            <label for="rvOverall<?= $s ?>"><svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg></label>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($showProsCons)): ?>
                <div class="inputs-inline">
                    <label
                        for="rvPros"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_pros') ?></label>
                    <textarea id="rvPros" rows="3" maxlength="2000"
                        placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_prosPlaceholder') ?>"></textarea>
                </div>

                <div class="inputs-inline">
                    <label
                        for="rvCons"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_cons') ?></label>
                    <textarea id="rvCons" rows="3" maxlength="2000"
                        placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_consPlaceholder') ?>"></textarea>
                </div>
            <?php endif; ?>

            <?php if (!empty($showComment)): ?>
                <div class="inputs-inline">
                    <label
                        for="rvComment"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_comment') ?></label>
                    <textarea id="rvComment" rows="4" maxlength="2000"
                        placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_commentPlaceholder') ?>"></textarea>
                </div>
            <?php endif; ?>

            <button class="active width-100" type="button"
                id="rvSubmitReview"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_submitReview') ?></button>
        </div>
    </div>
</div>

<div class="popup_modal" id="editReview" data-review-id="">
    <input type="hidden" id="rvEditId" value="">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_editReviewTitle') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div class="scroll no-scrollbar">
            <?php if (!empty($showServerSelect)): ?>
                <div class="inputs-inline">
                    <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_server') ?></label>
                </div>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="rvEditServerList">
                        <div class="inputs-inline">
                            <svg>
                                <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                            </svg>
                            <input id="searchRvEditServer" type="search" value=""
                                placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_findServer') ?>"
                                autocomplete="off">
                        </div>
                        <li>
                            <label class="adaptive-select__label" for="rvEditServerAll">
                                <div class="adaptive-select__label-text">
                                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_allServers') ?>
                                </div>
                                <input class="hide-input" id="rvEditServerAll" type="radio" value="-1" name="rv-edit-server"
                                    checked>
                            </label>
                        </li>
                        <?php foreach ($servers as $srv): ?>
                            <li>
                                <label class="adaptive-select__label" for="rvEditServer<?= (int) $srv['id'] ?>">
                                    <div class="adaptive-select__label-text"><?= action_text_clear($srv['name_custom']) ?></div>
                                    <span
                                        class="at__cs-label <?= action_text_clear($srv['badge']) ?>"><?= action_text_clear($srv['badge_text']) ?></span>
                                    <input class="hide-input" id="rvEditServer<?= (int) $srv['id'] ?>" type="radio"
                                        name="rv-edit-server" value="<?= (int) $srv['id'] ?>">
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="rvEditServerList">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#servers"></use>
                            </svg>
                        </span>
                        <span
                            class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_selectServer') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <hr>
            <?php else: ?>
                <input class="hide-input" type="radio" value="-1" name="rv-edit-server" checked>
            <?php endif; ?>
            <?php if (!empty($hasCriteria)): ?>
                <div class="inputs-inline">
                    <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_rateCriteria') ?></label>
                </div>
                <ul class="rv__modal-attrs" id="rvEditAttrs">
                    <?php foreach ($criteria as $attr): ?>
                        <li>
                            <span class="parametr"><?= action_text_clear((string) $attr['name']) ?>:</span>
                            <span class="value">
                                <div class="rv__rate rv__rate--sm" role="radiogroup"
                                    aria-label="<?= action_text_clear((string) $attr['name']) ?>">
                                    <?php for ($s = 5; $s >= 1; $s--): ?>
                                        <input type="radio" name="edit_attrs[<?= (int) $attr['id'] ?>]"
                                            id="rvEditAttr<?= (int) $attr['id'] . $s ?>" value="<?= $s ?>">
                                        <label for="rvEditAttr<?= (int) $attr['id'] . $s ?>"><svg>
                                                <use href="<?= $ratingUse ?>"></use>
                                            </svg></label>
                                    <?php endfor; ?>
                                </div>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <hr>
                <div class="rv__modal-overall">
                    <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_avgRating') ?>
                    <div class="rv__stars rv__stars--sm" id="rvEditOverallStars" style="--rv-rating: 0" aria-hidden="true">
                        <span class="rv__stars-fill">
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                        </span>
                        <span class="rv__stars-base">
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                            <svg>
                                <use href="<?= $ratingUse ?>"></use>
                            </svg>
                        </span>
                    </div>
                    <span id="rvEditOverallValue">(-)</span>
                </div>
            <?php else: ?>
                <div class="inputs-inline">
                    <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_overall') ?></label>
                </div>
                <div class="rv__modal-overall-pick">
                    <div class="rv__rate" data-rv-overall-rate role="radiogroup"
                        aria-label="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_overall') ?>">
                        <?php for ($s = 5; $s >= 1; $s--): ?>
                            <input type="radio" name="rv-edit-overall" id="rvEditOverall<?= $s ?>" value="<?= $s ?>">
                            <label for="rvEditOverall<?= $s ?>"><svg>
                                    <use href="<?= $ratingUse ?>"></use>
                                </svg></label>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($showProsCons)): ?>
                <div class="inputs-inline">
                    <label
                        for="rvEditPros"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_pros') ?></label>
                    <textarea id="rvEditPros" rows="3" maxlength="2000"
                        placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_prosPlaceholder') ?>"></textarea>
                </div>

                <div class="inputs-inline">
                    <label
                        for="rvEditCons"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_cons') ?></label>
                    <textarea id="rvEditCons" rows="3" maxlength="2000"
                        placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_consPlaceholder') ?>"></textarea>
                </div>
            <?php endif; ?>

            <?php if (!empty($showComment)): ?>
                <div class="inputs-inline">
                    <label
                        for="rvEditComment"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_comment') ?></label>
                    <textarea id="rvEditComment" rows="4" maxlength="2000"
                        placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_commentPlaceholder') ?>"></textarea>
                </div>
            <?php endif; ?>

            <button class="active width-100" type="button"
                id="rvSaveReview"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_saveChanges') ?></button>
        </div>
    </div>
</div>