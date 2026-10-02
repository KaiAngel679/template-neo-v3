<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="checker__header">
                <h2 class="checker__h2"><?= $Translate->get_translate_module_phrase('module_page_checker', '_checkTitle'); ?></h2>
                <p class="checker__description"><?= $Translate->get_translate_module_phrase('module_page_checker', '_checkDescription'); ?></p>
                <svg><use href="/resources/img/sprite.svg#shiedl-bold"></use></svg>
            </div>
            <?php if(!$settings['slider'] && $settings['img']): ?>
                <div class="checker__card">
                    <div class="checker__slider">
                        <div class="swiper checker__swiper">
                            <div class="swiper-wrapper">
                                <?php foreach($img as $key): ?>
                                    <div class="swiper-slide">  
                                        <div class="swiper-image">
                                            <img data-fancybox="gallery" data-src="/<?= MODULES . 'module_page_checker/assets/img/slide_img/' . $key ?>" class="lazy" alt="">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card sticky-block">
            <div class="checker__card">
                <div class="checker__card-wrapper">
                    <h2 class="checker__card-header"><?= $Translate->get_translate_module_phrase('module_page_checker', '_download'); ?></h2>
                    <hr>
                    <div class="checker__card-icon">
                        <svg><use href="/resources/img/sprite.svg#load-file"></use></svg>
                    </div>
                    <?php if ($settings['url_vt']): ?>
                        <a href="<?= $settings['url_vt'] ?>" target="_blank" class="checker__virustotal">
                            <svg><use href="/resources/img/sprite.svg#check"></use></svg>
                            <?= $Translate->get_translate_module_phrase('module_page_checker', '_vtotal'); ?>
                        </a>
                    <?php endif; ?>
                    <hr>
                    <?php if($settings['auth']): ?>
                        <?php if(!isset($_SESSION['steamid64'])): ?>
                            <button onclick="location.href='?auth=login'" class="width-100"><?= $Translate->get_translate_module_phrase('module_page_checker', '_login'); ?></button>
                        <?php endif; ?>
                        <?php if($settings['file'] || $settings['url_ft']): ?>
                            <div class="inputs-inline">
                                <input type="checkbox" id="read">
                                <label for="read"><?= $Translate->get_translate_module_phrase('module_page_checker', '_iread'); ?></label>
                            </div>
                        <?php endif; ?>
                        <?php if($settings['file']): ?>
                            <button class="width-100 download-btn download-btn-site" disabled><?= $Translate->get_translate_module_phrase('module_page_checker', '_loadSite'); ?></button>
                        <?php endif; if($settings['url_ft']):?>
                            <button class="width-100 download-btn download-btn-share" disabled><?= $Translate->get_translate_module_phrase('module_page_checker', '_loadShare'); ?></button>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if($settings['file'] || $settings['url_ft']): ?>
                            <div class="inputs-inline">
                                <input type="checkbox" id="read">
                                <label for="read"><?= $Translate->get_translate_module_phrase('module_page_checker', '_iread'); ?></label>
                            </div>
                        <?php endif; ?>
                        <?php if($settings['file']): ?>
                            <button class="width-100 download-btn download-btn-site" disabled><?= $Translate->get_translate_module_phrase('module_page_checker', '_loadSite'); ?></button>
                        <?php endif; if($settings['url_ft']):?>
                            <button class="width-100 download-btn download-btn-share" disabled><?= $Translate->get_translate_module_phrase('module_page_checker', '_loadShare'); ?></button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="height: -webkit-fill-available;">
            <div class="checker__card">
                <div class="checker__card-wrapper">
                    <h2 class="checker__card-header"><?= $Translate->get_translate_module_phrase('module_page_checker', '_content'); ?></h2>
                    <hr>
                    <div class="checker__content"><?= $textareaRepository->textareaConvert($settings['description_checker']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card" style="height: -webkit-fill-available;">
            <div class="checker__card">
                <div class="checker__card-wrapper">
                    <h2 class="checker__card-header"><?= $settings['name_checker'] ?></h2>
                    <hr>
                    <div class="checker__info">
                        <?= $textareaRepository->formatParagraphs($settings['paragraphs']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>