<div class="row">
    <div class="col-md-12">
        <div class="mbr">
            <div class="mbr__header">
                <h4 class="mbr__title">
                    <?= $Translate->get_translate_module_phrase('module_block_main_reviews', '_mbr_title') ?>
                    <?php if (isset($_SESSION['user_admin'])): ?>
                        <a href="/reviews/settings/" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_reviews', '_mbr_settings') ?>" data-tippy-placement="right">
                            <svg><use href="/resources/img/sprite.svg#gear"></use></svg>
                        </a>
                    <?php endif; ?>
                </h4>
                <div class="mbr__header-right">
                    <div class="mbr__summary" aria-label="<?= $Translate->get_translate_module_phrase('module_block_main_reviews', '_mbr_ratingLabel') ?>">
                        <span class="mbr__score"><?= action_text_clear($mbrSummary['avg_label']) ?></span>
                        <div class="mbr__summary-meta">
                            <div class="mbr__stars" style="--mbr-rating: <?= action_text_clear($mbrSummary['avg_label']) ?>">
                                <span class="mbr__stars-fill">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <svg><use href="<?= $mbrRatingUse ?>"></use></svg>
                                    <?php endfor; ?>
                                </span>
                                <span class="mbr__stars-base" aria-hidden="true">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <svg><use href="<?= $mbrRatingUse ?>"></use></svg>
                                    <?php endfor; ?>
                                </span>
                            </div>
                            <span class="mbr__count">
                                <?= str_replace(
                                    '%total%',
                                    action_text_clear($mbrSummary['total_label']),
                                    $Translate->get_translate_module_phrase('module_block_main_reviews', '_mbr_count')
                                ) ?>
                            </span>
                        </div>
                    </div>
                    <a class="filter active mbr__all" href="/reviews/">
                        <?= $Translate->get_translate_module_phrase('module_block_main_reviews', '_mbr_all') ?>
                        <svg><use href="/resources/img/sprite.svg#single-chevrone-right"></use></svg>
                    </a>
                </div>
            </div>

            <?php if ($mbrReviews === []): ?>
                <div class="mbr__empty card">
                    <?= $Translate->get_translate_module_phrase('module_block_main_reviews', '_mbr_empty') ?>
                    <a class="active" href="/reviews/"><?= $Translate->get_translate_module_phrase('module_block_main_reviews', '_mbr_write') ?></a>
                </div>
            <?php else: ?>
                <div class="mbr__scroller is-start" data-mbr-scroller>
                    <div class="mbr__track" data-mbr-track>
                        <?php foreach ($mbrReviews as $review): ?>
                            <a class="mbr__card card <?= action_text_clear($review['tone']) ?>" href="/reviews/?review=<?= (int) $review['id'] ?>">
                                <div class="mbr__card-head">
                                    <div class="mbr__user">
                                        <?php if (!empty($review['checked_avatar'])): ?>
                                            <?= $General->get_js_relevance_avatar($review['steamid']) ?>
                                        <?php endif; ?>
                                        <img src="<?= action_text_clear($review['avatar']) ?>" alt="" id="avatar" avatarid="<?= action_text_clear($review['steamid']) ?>">
                                        <div class="mbr__user-info">
                                            <span class="mbr__user-name" id="name" nameid="<?= action_text_clear($review['steamid']) ?>"><?= action_text_clear($review['name']) ?></span>
                                            <?php if ($review['date'] !== ''): ?>
                                                <time><?= action_text_clear($review['date']) ?></time>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mbr__card-rating">
                                        <div class="mbr__stars mbr__stars--sm" style="--mbr-rating: <?= action_text_clear($review['overall_label']) ?>">
                                            <span class="mbr__stars-fill">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <svg><use href="<?= $mbrRatingUse ?>"></use></svg>
                                                <?php endfor; ?>
                                            </span>
                                            <span class="mbr__stars-base" aria-hidden="true">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <svg><use href="<?= $mbrRatingUse ?>"></use></svg>
                                                <?php endfor; ?>
                                            </span>
                                        </div>
                                        <span class="mbr__overall"><?= action_text_clear($review['overall_label']) ?></span>
                                    </div>
                                </div>

                                <?php if ($review['text'] !== ''): ?>
                                    <p class="mbr__text"><?= action_text_clear($review['text']) ?></p>
                                <?php else: ?>
                                    <p class="mbr__text mbr__text--muted"><?= $Translate->get_translate_module_phrase('module_block_main_reviews', '_mbr_noText') ?></p>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="mbr__nav mbr__nav--prev" data-mbr-nav="prev" aria-label="<?= $Translate->get_translate_module_phrase('module_block_main_reviews', '_mbr_prev') ?>">
                        <svg><use href="/resources/img/sprite.svg#single-chevrone-left"></use></svg>
                    </button>
                    <button type="button" class="mbr__nav mbr__nav--next" data-mbr-nav="next" aria-label="<?= $Translate->get_translate_module_phrase('module_block_main_reviews', '_mbr_next') ?>">
                        <svg><use href="/resources/img/sprite.svg#single-chevrone-right"></use></svg>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
