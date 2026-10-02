<?php $sliders = $Modules->get_settings_modules('module_block_main_banner_slider', 'settings'); ?>
<?php if ($banner_open_row): ?>
    <div class="row">
    <?php endif; ?>
    <div class="<?= $banner_col_class ?>">
        <div class="image-slider swiper">
            <div class="image-slider__wrapper swiper-wrapper">
                <?php foreach ($sliders['slides'] as $key) : ?>
                    <div class="image-slider__slide swiper-slide">
                        <div class="image-slider__image">
                            <p data-swiper-parallax="-100" data-swiper-parallax-duration="800"><?= $key['description'] ?></p>
                            <h3 data-swiper-parallax="-200" data-swiper-parallax-duration="800"><?= $key['title'] ?></h3>
                            <?php if (!empty($key['button_url'])) : ?>
                                <div data-swiper-parallax-y="-40" data-swiper-parallax-opacity="0.5" class="swiper_btn" onclick="window.open('<?= $key['button_url'] ?>','_blank')">
                                    <?= $key['button_text'] ?>
                                    <svg x="0" y="0" viewBox="0 0 512 512" xml:space="preserve">
                                        <g>
                                            <path d="M512 40v432a40 40 0 0 1-80 0V136.568L68.284 500.285a40 40 0 0 1-56.569-56.569L375.432 80H40a40 40 0 0 1 0-80h432a40 40 0 0 1 40 40z"></path>
                                        </g>
                                    </svg>
                                    <div class="swiper_action_svg"></div>
                                </div>
                            <?php endif; ?>
                            <img class="lazy" data-src="<?= $General->arr_general['site'] ?>app/modules/module_block_main_banner_slider/assets/img/<?= $key['img'] ?>" loading="lazy" alt="">
                            <div class="swiper-lazy-preloader"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-buttons"></div>
        </div>
    </div>
    <?php if ($banner_close_row): ?>
    </div>
<?php endif; ?>