<?php if (isset($_SESSION['user_admin'])) { ?>
    <script src="/app/templates/neo_remastered/assets/js/tabs.js" async></script>
    <link rel="stylesheet" href="/app/modules/module_block_main_theme/assets/css/colorpicker.min.css" />
    <script src="/app/modules/module_block_main_theme/assets/js/colorpicker.iife.min.js" async></script>
    <aside class="theme__sidebar" id="themeSide">
        <div class="theme__sidebar-content">
            <div class="theme__sidebar-head">
                <h3 id="tablist-1"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_changing') ?></h3>
                <div class="theme__sidebar-close" id="closeThemeEditor">
                    ESC
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </div>
            </div>

            <div class="tabs theme__sidebar-tabs-area">
                <div class="theme__sidebar-tabs" role="tablist" aria-labelledby="tablist-1">
                    <button class="filter" id="tab-1" type="button" role="tab" aria-selected="true" aria-controls="tabpanel-1"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_palette') ?></button>
                    <button class="filter" id="tab-2" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-2" tabindex="-1"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_bg') ?></button>
                    <button class="filter" id="tab-3" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-3" tabindex="-1"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_themes') ?></button>
                </div>

                <!-- tab 1 -->
                <form id="tabpanel-1" role="tabpanel" tabindex="0" aria-labelledby="tab-1" class="theme__sidebar-tabpanel">
                    <div class="theme__sidebar-color-wrapper">
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_primary') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell" data-color="<?= $colorTheme['span'] ?>" data-color-name="span" style="background-color: <?= $colorTheme['span'] ?>">
                                    <div class="color" style="color:<?= $colorTheme['span'] ?>"><?= $colorTheme['span'] ?></div>
                                    <input type="hidden" name="theme_colors[span]" value="<?= $colorTheme['span'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_shadesPrimary') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-shades">
                                    <div class="theme__sidebar-picker theme__sidebar-shade" id="span-low" style="background-color: var(--span-low)">
                                        <div class="theme__sidebar-shade-text" style="color: var(--span-low)">5%</div>
                                    </div>
                                    <div class="theme__sidebar-picker theme__sidebar-shade" id="span-middle" style="background-color: var(--span-middle)">
                                        <div class="theme__sidebar-shade-text" style="color: var(--span-middle)">25%</div>
                                    </div>
                                    <div class="theme__sidebar-picker theme__sidebar-shade" id="span-half" style="background-color: var(--span-half)">
                                        <div class="theme__sidebar-shade-text" style="color: var(--span-half)">50%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_textDefault') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell" data-color="<?= $colorTheme['text-default'] ?>" data-color-name="text-default" style="background-color: <?= $colorTheme['text-default'] ?>">
                                    <div class="color" style="color:<?= $colorTheme['text-default'] ?>"><?= $colorTheme['text-default'] ?></div>
                                    <input type="hidden" name="theme_colors[text-default]" value="<?= $colorTheme['text-default'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_textInversion') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell" data-color="<?= $colorTheme['text-default-invert'] ?>" data-color-name="text-default-invert" style="background-color: <?= $colorTheme['text-default-invert'] ?>">
                                    <div class="color" style="color:<?= $colorTheme['text-default-invert'] ?>"><?= $colorTheme['text-default-invert'] ?></div>
                                    <input type="hidden" name="theme_colors[text-default-invert]" value="<?= $colorTheme['text-default-invert'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_textCustom') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell" data-color="<?= $colorTheme['text-custom'] ?>" data-color-name="text-custom" style="background-color: <?= $colorTheme['text-custom'] ?>">
                                    <div class="color" style="color:<?= $colorTheme['text-custom'] ?>"><?= $colorTheme['text-custom'] ?></div>
                                    <input type="hidden" name="theme_colors[text-custom]" value="<?= $colorTheme['text-custom'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_textSecondary') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell" data-color="<?= $colorTheme['text-secondary'] ?>" data-color-name="text-secondary" style="background-color: <?= $colorTheme['text-secondary'] ?>">
                                    <div class="color" style="color:<?= $colorTheme['text-secondary'] ?>"><?= $colorTheme['text-secondary'] ?></div>
                                    <input type="hidden" name="theme_colors[text-secondary]" value="<?= $colorTheme['text-secondary'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_money') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell" data-color="<?= $colorTheme['money'] ?>" data-color-name="money" style="background-color: <?= $colorTheme['money'] ?>">
                                    <div class="color" style="color:<?= $colorTheme['money'] ?>"><?= $colorTheme['money'] ?></div>
                                    <input type="hidden" name="theme_colors[money]" value="<?= $colorTheme['money'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_moneyShade') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker" id="money-bg" style="background-color: var(--money-bg)">
                                    <div class="color" style="color:var(--money-bg)">10%</div>
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_bgSite') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell bad-visibility" data-color="<?= $colorTheme['bg'] ?>" data-color-name="bg" style="background-color: <?= $colorTheme['bg'] ?>">
                                    <div class="color bad-visibility-text"><?= $colorTheme['bg'] ?></div>
                                    <input type="hidden" name="theme_colors[bg]" value="<?= $colorTheme['bg'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_card') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell bad-visibility" data-color="<?= $colorTheme['card'] ?>" data-color-name="card" style="background-color: <?= $colorTheme['card'] ?>">
                                    <div class="color bad-visibility-text"><?= $colorTheme['card'] ?></div>
                                    <input type="hidden" name="theme_colors[card]" value="<?= $colorTheme['card'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_tooltip') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell bad-visibility" data-color="<?= $colorTheme['tooltip'] ?>" data-color-name="tooltip" style="background-color: <?= $colorTheme['tooltip'] ?>">
                                    <div class="color bad-visibility-text"><?= $colorTheme['tooltip'] ?></div>
                                    <input type="hidden" name="theme_colors[tooltip]" value="<?= $colorTheme['tooltip'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_bgModal') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell bad-visibility" data-color="<?= $colorTheme['bg-modal'] ?>" data-color-name="bg-modal" style="background-color: <?= $colorTheme['bg-modal'] ?>">
                                    <div class="color bad-visibility-text"><?= $colorTheme['bg-modal'] ?></div>
                                    <input type="hidden" name="theme_colors[bg-modal]" value="<?= $colorTheme['bg-modal'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_inputs') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell bad-visibility" data-color="<?= $colorTheme['input-form'] ?>" data-color-name="input-form" style="background-color: <?= $colorTheme['input-form'] ?>">
                                    <div class="color bad-visibility-text"><?= $colorTheme['input-form'] ?></div>
                                    <input type="hidden" name="theme_colors[input-form]" value="<?= $colorTheme['input-form'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_disabledButton') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell bad-visibility" data-color="<?= $colorTheme['btn-disabled'] ?>" data-color-name="btn-disabled" style="background-color: <?= $colorTheme['btn-disabled'] ?>">
                                    <div class="color bad-visibility-text"><?= $colorTheme['btn-disabled'] ?></div>
                                    <input type="hidden" name="theme_colors[btn-disabled]" value="<?= $colorTheme['btn-disabled'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_button') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell" data-color="<?= $colorTheme['button'] ?>" data-color-name="button" style="background-color: <?= $colorTheme['button'] ?>">
                                    <div class="color bad-visibility-text"><?= $colorTheme['button'] ?></div>
                                    <input type="hidden" name="theme_colors[button]" value="<?= $colorTheme['button'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_buttonHover') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell" data-color="<?= $colorTheme['button-hover'] ?>" data-color-name="button-hover" style="background-color: <?= $colorTheme['button-hover'] ?>">
                                    <div class="color bad-visibility-text"><?= $colorTheme['button-hover'] ?></div>
                                    <input type="hidden" name="theme_colors[button-hover]" value="<?= $colorTheme['button-hover'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="theme__sidebar-block">
                            <span class="theme__sidebar-name-color"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_stars') ?></span>
                            <div id="palette">
                                <div class="theme__sidebar-picker cell" data-color="<?= $colorTheme['stars'] ?>" data-color-name="stars" style="background-color: <?= $colorTheme['stars'] ?>">
                                    <div class="color bad-visibility-text"><?= $colorTheme['stars'] ?></div>
                                    <input type="hidden" name="theme_colors[stars]" value="<?= $colorTheme['stars'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <button class="width-100" id="savePalette">
                        <svg>
                            <use href="/resources/img/sprite.svg#plus"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_block_main_theme', '_saveTheme') ?>
                    </button>
                </form>
                <!-- tab 2 -->
                <form id="tabpanel-2" role="tabpanel" tabindex="0" aria-labelledby="tab-2" class="theme__sidebar-tabpanel is-hidden">
                    <div class="theme__radio-wrapper">
                        <div class="theme__radio-area">
                            <label for="background-0" class="theme__radio-label">
                                <input id="background-0" class="theme__radio-hidden" type="radio" name="background" value="1" <?= ($background['type'] == 1) ? 'checked' : '' ?>>
                                <div class="theme__radio-title"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_nothing') ?>
                                    <svg data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_bgColor') ?>">
                                        <use href="/resources/img/sprite.svg#question"></use>
                                    </svg>
                                </div>
                            </label>
                        </div>

                        <div class="theme__radio-area">
                            <label for="background-1" class="theme__radio-label have-content">
                                <input id="background-1" class="theme__radio-hidden" type="radio" name="background" value="2" <?= ($background['type'] == 2) ? 'checked' : '' ?>>
                                <div class="theme__radio-title"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_image') ?>
                                    <svg data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_ratio') ?>">
                                        <use href="/resources/img/sprite.svg#question"></use>
                                    </svg>
                                </div>
                            </label>
                            <div class="theme__radio-content">
                                <div class="inputs-inline no-mb">
                                    <div class="file-upload-container">
                                        <input type="file" id="file-bg" class="custom-file-input" accept="image/*" name="background_image">
                                        <label for="file-bg"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_selectImage') ?></label>
                                        <div id="file-info" class="file-upload-info file-info" style="display: block"><?= basename($background['image'])?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="theme__radio-area">
                            <label for="background-2" class="theme__radio-label have-content">
                                <input id="background-2" class="theme__radio-hidden" type="radio" name="background" value="3" <?= ($background['type'] == 3) ? 'checked' : '' ?>>
                                <div class="theme__radio-title"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_gradient') ?>
                                    <svg data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_gradientText') ?>">
                                        <use href="/resources/img/sprite.svg#question"></use>
                                    </svg>
                                </div>
                            </label>
                            <div class="theme__radio-content">
                                <div class="theme__gradients-list">
                                    <label for="gradient-0" class="theme__gradient theme__gradient-1" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_0deg') ?>">
                                        <input id="gradient-0" type="radio" name="gradients" value="1" class="theme__gradients-hidden" <?= ($background['gradients'] == 1) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-1" class="theme__gradient theme__gradient-2" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_diagonal') ?>">
                                        <input id="gradient-1" type="radio" name="gradients" value="2" class="theme__gradients-hidden" <?= ($background['gradients'] == 2) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-2" class="theme__gradient theme__gradient-3" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_180deg') ?>">
                                        <input id="gradient-2" type="radio" name="gradients" value="3" class="theme__gradients-hidden" <?= ($background['gradients'] == 3) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-3" class="theme__gradient theme__gradient-4" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_radialCenter') ?>">
                                        <input id="gradient-3" type="radio" name="gradients" value="4" class="theme__gradients-hidden" <?= ($background['gradients'] == 4) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-4" class="theme__gradient theme__gradient-5" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_radialSide') ?>">
                                        <input id="gradient-4" type="radio" name="gradients" value="5" class="theme__gradients-hidden" <?= ($background['gradients'] == 5) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-5" class="theme__gradient theme__gradient-6" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_stripes') ?>">
                                        <input id="gradient-5" type="radio" name="gradients" value="6" class="theme__gradients-hidden" <?= ($background['gradients'] == 6) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-6" class="theme__gradient theme__gradient-7" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_mixed') ?>">
                                        <input id="gradient-6" type="radio" name="gradients" value="7" class="theme__gradients-hidden" <?= ($background['gradients'] == 7) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-7" class="theme__gradient theme__gradient-8" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_halftone') ?>">
                                        <input id="gradient-7" type="radio" name="gradients" value="8" class="theme__gradients-hidden" <?= ($background['gradients'] == 8) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-8" class="theme__gradient theme__gradient-9" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_diagonalOnly') ?>">
                                        <input id="gradient-8" type="radio" name="gradients" value="9" class="theme__gradients-hidden" <?= ($background['gradients'] == 9) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-9" class="theme__gradient theme__gradient-10" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_threeMixed') ?>">
                                        <input id="gradient-9" type="radio" name="gradients" value="10" class="theme__gradients-hidden" <?= ($background['gradients'] == 10) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-10" class="theme__gradient theme__gradient-11" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_radialSoft') ?>">
                                        <input id="gradient-10" type="radio" name="gradients" value="11" class="theme__gradients-hidden" <?= ($background['gradients'] == 11) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-11" class="theme__gradient theme__gradient-12" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_metallic') ?>">
                                        <input id="gradient-11" type="radio" name="gradients" value="12" class="theme__gradients-hidden" <?= ($background['gradients'] == 12) ? 'checked' : '' ?>>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="theme__radio-area">
                            <label for="background-4" class="theme__radio-label">
                                <input id="background-4" class="theme__radio-hidden" type="radio" name="background" value="4" <?= ($background['type'] == 4) ? 'checked' : '' ?>>
                                <div class="theme__radio-title"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_starsOnly') ?>
                                    <svg data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_starsOnlyInfo') ?>">
                                        <use href="/resources/img/sprite.svg#question"></use>
                                    </svg>
                                </div>
                            </label>
                        </div>

                        <div class="theme__radio-area">
                            <label for="background-5" class="theme__radio-label have-content">
                                <input id="background-5" class="theme__radio-hidden" type="radio" name="background" value="5" <?= ($background['type'] == 5) ? 'checked' : '' ?>>
                                <div class="theme__radio-title"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_starsGradient') ?></div>
                            </label>
                            <div class="theme__radio-content">
                                <div class="theme__gradients-list">
                                    <label for="gradient-stars-0" class="theme__gradient theme__gradient-1" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_0deg') ?>">
                                        <input id="gradient-stars-0" type="radio" name="gradients-stars" value="1" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 1) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-stars-1" class="theme__gradient theme__gradient-2" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_diagonal') ?>">
                                        <input id="gradient-stars-1" type="radio" name="gradients-stars" value="2" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 2) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-stars-2" class="theme__gradient theme__gradient-3" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_180deg') ?>">
                                        <input id="gradient-stars-2" type="radio" name="gradients-stars" value="3" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 3) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-stars-3" class="theme__gradient theme__gradient-4" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_radialCenter') ?>">
                                        <input id="gradient-stars-3" type="radio" name="gradients-stars" value="4" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 4) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-stars-4" class="theme__gradient theme__gradient-5" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_radialSide') ?>">
                                        <input id="gradient-stars-4" type="radio" name="gradients-stars" value="5" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 5) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-stars-5" class="theme__gradient theme__gradient-6" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_stripes') ?>">
                                        <input id="gradient-stars-5" type="radio" name="gradients-stars" value="6" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 6) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-stars-6" class="theme__gradient theme__gradient-7" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_mixed') ?>">
                                        <input id="gradient-stars-6" type="radio" name="gradients-stars" value="7" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 7) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-stars-7" class="theme__gradient theme__gradient-8" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_halftone') ?>">
                                        <input id="gradient-stars-7" type="radio" name="gradients-stars" value="8" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 8) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-stars-8" class="theme__gradient theme__gradient-9" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_diagonalOnly') ?>">
                                        <input id="gradient-stars-8" type="radio" name="gradients-stars" value="9" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 9) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-stars-9" class="theme__gradient theme__gradient-10" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_threeMixed') ?>">
                                        <input id="gradient-stars-9" type="radio" name="gradients-stars" value="10" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 10) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-stars-10" class="theme__gradient theme__gradient-11" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_radialSoft') ?>">
                                        <input id="gradient-stars-10" type="radio" name="gradients-stars" value="11" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 11) ? 'checked' : '' ?>>
                                    </label>
                                    <label for="gradient-stars-11" class="theme__gradient theme__gradient-12" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_metallic') ?>">
                                        <input id="gradient-stars-11" type="radio" name="gradients-stars" value="12" class="theme__gradients-hidden" <?= ($background['gradients-stars'] == 12) ? 'checked' : '' ?>>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- tab 3 -->
                <div id="tabpanel-3" role="tabpanel" tabindex="0" aria-labelledby="tab-3" class="theme__sidebar-tabpanel is-hidden">
                    <h3 class="themes__h3"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_readyThemes') ?></h3>
                    <div class="themes__wrapper">
                        <?php foreach ($Theme->getThemes() as $key => $theme) { ?>
                            <div class="themes__theme">
                                <span class="themes__text"><?= (is_string($theme['name']) && $theme['name'] !== '' && $theme['name'][0] === '_') ? $Translate->get_translate_module_phrase('module_block_main_theme', $theme['name']) : $theme['name']; ?></span>
                                <label for="default_<?= $key ?>" class="themes__label">
                                    <input id="default_<?= $key ?>" type="radio" name="theme_colors" value="default_<?= $key ?>" data-type="default" class="themes__radio-hidden">
                                    <div class="themes__list">
                                        <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--span'] ?>"></div>
                                        <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--text-default'] ?>"></div>
                                        <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--card'] ?>"></div>
                                        <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--button'] ?>"></div>
                                        <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--bg'] ?>"></div>
                                        <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--money'] ?>"></div>
                                    </div>
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                    <hr>
                    <h3 class="themes__h3"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_createdThemes') ?></h3>
                    <?php if (!empty($Theme->getThemes('custom'))): ?>
                        <div class="themes__wrapper">
                            <?php foreach ($Theme->getThemes('custom') as $key => $theme) { ?>
                                <div class="themes__theme">
                                    <span class="themes__text"><?= (is_string($theme['name']) && $theme['name'] !== '' && $theme['name'][0] === '_') ? $Translate->get_translate_module_phrase('module_block_main_theme', $theme['name']) : $theme['name']; ?></span>
                                    <div class="theme__delete" data-theme="<?= $key ?>">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#x"></use>
                                        </svg>
                                    </div>
                                    <label for="custom_<?= $key ?>" class="themes__label">
                                        <input id="custom_<?= $key ?>" type="radio" name="theme_colors" value="custom_<?= $key ?>" data-type="custom" class="themes__radio-hidden">
                                        <div class="themes__list">
                                            <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--span'] ?>"></div>
                                            <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--text-default'] ?>"></div>
                                            <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--card'] ?>"></div>
                                            <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--button'] ?>"></div>
                                            <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--bg'] ?>"></div>
                                            <div class="themes__theme-block" style="background-color: <?= $theme['colors']['--money'] ?>"></div>
                                        </div>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_noThemes') ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="theme__sidebar-action">
                <button id="resetTheme" data-title="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_reset_theme') ?>" data-text="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_reset_theme_confirm') ?>" data-button-cancel="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_cancel') ?>" data-button-ok="<?= $Translate->get_translate_module_phrase('module_block_main_theme', '_reset_theme') ?>"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_reset') ?></button>
                <button class="active" id="saveTheme"><?= $Translate->get_translate_module_phrase('module_block_main_theme', '_apply') ?></button>
            </div>
        </div>
    </aside>

<?php } ?>