<?php !isset($_SESSION['user_admin']) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) && die() ?>
<div class="col-md-6">
    <div class="card height-100">
        <div class="card-header">
            <h5 class="badge">
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_General_settings') ?>
            </h5>
        </div>
        <div class="card-container height-100">
            <form id="options_one" enctype="multipart/form-data" method="post">
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label
                            for="fullName"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Full_name') ?></label>
                        <input name="full_name" value="<?= $General->arr_general['full_name'] ?>" id="fullName">
                    </div>
                    <div class="inputs-inline">
                        <label
                            for="shortName"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Short_name') ?></label>
                        <input name="short_name" value="<?= $General->arr_general['short_name'] ?>" id="shortName">
                    </div>
                </div>
                <div class="inputs-inline">
                    <label
                        for="generalInfo"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Basic_information') ?></label>
                    <input name="info" value="<?= $General->arr_general['info'] ?>" id="generalInfo">
                </div>
                <div class="inputs-inline">
                    <label
                        for="metaTags"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_metaTags') ?></label>
                    <input name="keywords" value="<?= $General->arr_general['keywords'] ?>" id="metaTags">
                </div>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Language') ?></label>
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="option-language-select">
                                <?php for ($i = 0; $i < $Translate->arr_languages_count; $i++): ?>
                                    <li>
                                        <label class="adaptive-select__label" for="<?= $Translate->arr_languages[$i] ?>">
                                            <img style="border-radius: 10px;" src="/storage/cache/img/icons/custom/flags/<?= strtolower($Translate->arr_languages[$i]) ?>.svg">
                                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_phrase('_' . $Translate->arr_languages[$i]) ?></div>
                                            <input class="hide-input" id="<?= $Translate->arr_languages[$i] ?>" type="radio" name="language" value="<?= $Translate->arr_languages[$i] ?>" <?php $General->arr_general['language'] === $Translate->arr_languages[$i] && print 'checked' ?>>
                                        </label>
                                    </li>
                                <?php endfor ?>
                            </ul>
                            <div class="adaptive-select" open-select="option-language-select">
                                <span class="adaptive-select__span_text"><?= $Translate->get_translate_phrase('_' . $General->arr_general['language']) ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="inputs-inline">
                        <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Currency') ?></label>
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="option-currency-select">
                                <?php foreach ($General->currencies as $key => $currency): ?>
                                    <li>
                                        <label class="adaptive-select__label" for="currency_<?= $key ?>">
                                            <div class="adaptive-select__label-text"><?= $key ?></div>
                                            <input class="hide-input" id="currency_<?= $key ?>" type="radio" name="currency" value="<?= $key ?>" <?php $General->arr_general['currency'] === $key && print 'checked' ?>>
                                        </label>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                            <div class="adaptive-select" open-select="option-currency-select">
                                <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_select_currency') ?></span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="inputs-inline" style="margin-top: .5rem">
                    <input onclick="set_options_data(this.id,'')" class="switch" type="checkbox" id="disabledLanguage" <?php $General->arr_general['disabledLanguage'] === 1 && print 'checked' ?>>
                    <label for="disabledLanguage"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_disabledLanguage') ?></label>
                </div>

                <div class="inputs-inline" style="margin-top: .5rem">
                    <input onclick="set_options_data(this.id,'')" class="switch" type="checkbox" id="disabledSearch" <?php $General->arr_general['disabledSearch'] === 1 && print 'checked' ?>>
                    <label for="disabledSearch"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_disabledSearch') ?></label>
                </div>

                <div class="inputs-inline" style="margin-top: .5rem">
                    <input onclick="set_options_data(this.id,'')" class="switch" type="checkbox" id="searchForAuth" <?php $General->arr_general['searchForAuth'] === 1 && print 'checked' ?>>
                    <label for="searchForAuth"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_onlyAuthSearch') ?></label>
                </div>

                <fieldset>
                    <legend><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_typeNavigaruon') ?></legend>
                    <div class="flex-inline width-100">
                        <div class="inputs-inline">
                            <input onclick="set_options_data_select(getAttribute('name'), value)" type="radio" id="typeNavbar" name="navigation" value="navbar" <?php $General->arr_general['navigation'] === 'navbar' && print 'checked' ?>>
                            <label for="typeNavbar"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_navbar') ?></label>
                        </div>
                        <div class="inputs-inline">
                            <input onclick="set_options_data_select(getAttribute('name'), value)" type="radio" id="typeSidebar" name="navigation" value="sidebar" <?php $General->arr_general['navigation'] === 'sidebar' && print 'checked' ?>>
                            <label for="typeSidebar"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_sidebar') ?></label>
                        </div>
                    </div>
                </fieldset>

                <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_ifChooseNav') ?></label>
                <div class="inputs-inline">
                    <input onclick="set_options_data(this.id,'')" class="switch" type="checkbox" id="compactNav" <?php $General->arr_general['compactNav'] === 1 && print 'checked' ?>>
                    <label for="compactNav"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_compactNav') ?></label>
                </div>

                <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_openSidebarLabel') ?></label>
                <div class="inputs-inline">
                    <input onclick="set_options_data(this.id,'')" class="switch" type="checkbox" id="sidebarState" <?php $General->arr_general['sidebarState'] === 1 && print 'checked' ?>>
                    <label for="sidebarState"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_sidebarAlwaysOpen') ?></label>
                </div>

                <hr>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for="webApiKey">
                            <a href="https://steamcommunity.com/dev/apikey" target="_blank">Steam Web Api Key</a>
                        </label>
                        <div class="number">
                            <input name="web_key" type="password" value="<?= $General->arr_general['web_key'] ?>"
                                id="webApiKey">
                            <div class="eye-password" id="show_pass">
                                <svg>
                                    <use href="/resources/img/sprite.svg#eye"></use>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="inputs-inline">
                        <label for="faceitApiKey"><a target="_blank"
                                href="https://developers.faceit.com/"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_faceitApiKey') ?></a></label>
                        <div class="number">
                            <input name="faceit_key" type="password" value="<?= $General->arr_general['faceit_key'] ?>"
                                placeholder="" id="faceitApiKey">
                            <div class="eye-password" id="show_pass">
                                <svg>
                                    <use href="/resources/img/sprite.svg#eye"></use>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label
                            for="saveCasheTimeAvatars"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Timecache_avatars') ?></label>
                        <input name="avatars_cache_time" value="<?= $General->arr_general['avatars_cache_time'] ?>"
                            id="saveCasheTimeAvatars">
                    </div>
                    <div class="inputs-inline">
                        <label
                            for="saveCasheTimeFaceit"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_saveTimeFaceit') ?></label>
                        <input name="faceit_cache_time" value="<?= $General->arr_general['faceit_cache_time'] ?>"
                            id="saveCasheTimeFaceit">
                    </div>
                </div>
            </form>
        </div>
        <div class="card-bottom">
            <button class="margin-top-auto width-100" name="option_one_save" type="submit"
                form="options_one"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Save') ?></button>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="card height-100">
        <div class="card-header">
            <h5 class="badge">
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Extra_settingss') ?>
            </h5>
        </div>
        <div class="card-container height-100">
            <div class="template_version">
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_TemplateVersion') ?>:
                <?= VERSION ?>
            </div>
            <div class="flex-inline">
                <div class="inputs-inline" style="margin-bottom: 1rem">
                    <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Template_rangs') ?></label>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="option-rank_pack-select">
                            <li>
                                <label class="adaptive-select__label" for="svg">
                                    <div class="adaptive-select__label-text">SVG</div>
                                    <input class="hide-input" id="svg" type="radio" value="svg" name="rank_pack" onclick="set_options_data_select( getAttribute('name'), value )" <?php $General->arr_general['rank_pack'] === 'svg' && print 'checked' ?>>
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="png">
                                    <div class="adaptive-select__label-text">PNG</div>
                                    <input class="hide-input" id="png" type="radio" value="png" name="rank_pack" onclick="set_options_data_select( getAttribute('name'), value )" <?php $General->arr_general['rank_pack'] === 'png' && print 'checked' ?>>
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="webp">
                                    <div class="adaptive-select__label-text">WEBP</div>
                                    <input class="hide-input" id="webp" type="radio" value="webp" name="rank_pack" onclick="set_options_data_select( getAttribute('name'), value )" <?php $General->arr_general['rank_pack'] === 'webp' && print 'checked' ?>>
                                </label>
                            </li>
                        </ul>
                        <div class="adaptive-select" open-select="option-rank_pack-select">
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_rangsFormat') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="inputs-inline" style="margin-bottom: 1rem">
                    <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_ranksType') ?></label>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="option-premier_ranks-select">
                            <li>
                                <label class="adaptive-select__label" for="default_rank">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_defaultRanks') ?></div>
                                    <input class="hide-input" id="default_rank" type="radio" value="false" name="premier_ranks" onclick="set_options_data_select('premier_ranks', 0)" <?php $General->arr_general['premier_ranks'] == 0 && print 'checked' ?>>
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="premier_rank">
                                    <div class="adaptive-select__label-text">Premier</div>
                                    <input class="hide-input" id="premier_rank" type="radio" value="true" name="premier_ranks" onclick="set_options_data_select('premier_ranks', 1)" <?php $General->arr_general['premier_ranks'] == 1 && print 'checked' ?>>
                                </label>
                            </li>
                        </ul>
                        <div class="adaptive-select" open-select="option-premier_ranks-select">
                            <span class="adaptive-select__span_text"></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <form id="options_two" enctype="multipart/form-data" method="post">
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for="dsToken">Discord token</label>
                        <div class="number">
                            <input id="dsToken" type="password"
                                value="<?= $Admin->get_social()['discord']['botToken'] ?>" placeholder=""
                                name="botToken">
                            <div class="eye-password" id="show_pass">
                                <svg>
                                    <use href="/resources/img/sprite.svg#eye"></use>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="inputs-inline">
                        <label
                            for="dsguild"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_dsGuild') ?></label>
                        <div class="number">
                            <input id="dsguild" type="password"
                                value="<?= $Admin->get_social()['discord']['guildId'] ?>" placeholder="" name="guildId">
                            <div class="eye-password" id="show_pass"><svg>
                                    <use href="/resources/img/sprite.svg#eye"></use>
                                </svg></div>
                        </div>
                    </div>
                </div>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for="vkToken">VK token</label>
                        <div class="number">
                            <input id="vkToken" type="password" value="<?= $Admin->get_social()['vk']['vkToken'] ?>"
                                placeholder="" name="vkToken">
                            <div class="eye-password" id="show_pass"><svg>
                                    <use href="/resources/img/sprite.svg#eye"></use>
                                </svg></div>
                        </div>
                    </div>
                    <div class="inputs-inline">
                        <label
                            for="vkGroupId"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_vkGroupID') ?></label>
                        <div class="number">
                            <input id="vkGroupId" type="password" value="<?= $Admin->get_social()['vk']['groupId'] ?>"
                                placeholder="" name="groupId">
                            <div class="eye-password" id="show_pass"><svg>
                                    <use href="/resources/img/sprite.svg#eye"></use>
                                </svg></div>
                        </div>
                    </div>
                </div>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for="tgToken">Telegram token</label>
                        <div class="number">
                            <input id="tgToken" type="password"
                                value="<?= $Admin->get_social()['telegram']['telegramToken'] ?>" placeholder=""
                                name="telegramToken">
                            <div class="eye-password" id="show_pass"><svg>
                                    <use href="/resources/img/sprite.svg#eye"></use>
                                </svg></div>
                        </div>
                    </div>
                    <div class="inputs-inline">
                        <label
                            for="tgChatId"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_tgChatId') ?></label>
                        <div class="number">
                            <input id="tgChatId" type="password"
                                value="<?= $Admin->get_social()['telegram']['chatId'] ?>" placeholder="" name="chatId">
                            <div class="eye-password" id="show_pass">
                                <svg>
                                    <use href="/resources/img/sprite.svg#eye"></use>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="width-100" name="option_two_save" type="submit"
                    form="options_two"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Save') ?></button>
            </form>
            <hr>
            <div class="inputs-inline">
                <input onclick="set_options_data(this.id,'')" class="switch" type="checkbox" name="thoseworks"
                    id="thoseworks" <?php $General->arr_general['thoseworks'] === 1 && print 'checked' ?>>
                <label
                    for="thoseworks"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_EnabletechWork') ?></label>
            </div>
            <div class="inputs-inline">
                <input onclick="set_options_data(this.id,'')" class="switch" type="checkbox" name="antivpn" id="antivpn"
                    <?php $General->arr_general['antivpn'] === 1 && print 'checked' ?>>
                <label for="antivpn">Anti-VPN</label>
            </div>
            <div class="inputs-inline">
                <input onclick="set_options_data(this.id,'')" class="switch" type="checkbox" name="css_off_cache"
                    id="css_off_cache" <?php $General->arr_general['css_off_cache'] === 1 && print 'checked' ?>>
                <label
                    for="css_off_cache"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Css_off_cache') ?></label>
            </div>
            <div class="inputs-inline">
                <input onclick="set_options_data(this.id,'')" class="switch" type="checkbox" name="session_check"
                    id="session_check" <?php $General->arr_general['session_check'] === 1 && print 'checked' ?>>
                <label
                    for="session_check"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Session_check') ?></label>
            </div>
            <div class="inputs-inline">
                <input onclick="set_options_data(this.id,'')" class="switch" type="checkbox" name="auth_cock"
                    id="auth_cock" <?php $General->arr_general['auth_cock'] === 1 && print 'checked' ?>>
                <label
                    for="auth_cock"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Cockie') ?></label>
            </div>
            <div class="inputs-inline">
                <input onclick="set_options_data(this.id,'')" class="switch" type="checkbox" name="watermark"
                    id="watermark" <?php $General->arr_general['watermark'] === 1 && print 'checked' ?>>
                <label
                    for="watermark"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_HideWatermark') ?></label>
            </div>
            <div class="inputs-inline" style="margin-bottom: 1rem">
                <label><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Decoration') ?></label>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="option-decoration-select">
                        <li>
                            <label class="adaptive-select__label" for="off_decoration">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_offDecoration') ?></div>
                                <input class="hide-input" id="off_decoration" type="radio" value="0" name="enable_decoration" onclick="set_options_data_select( getAttribute('name'), '0' )" <?= empty($General->arr_general['enable_decoration']) || ($General->arr_general['enable_decoration'] === 0 || $General->arr_general['enable_decoration'] === 1) ? 'checked' : '' ?>>
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="decoration_snow">
                                <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_snowDecoration') ?></div>
                                <input class="hide-input" id="decoration_snow" type="radio" value="snowfall" name="enable_decoration" onclick="set_options_data_select( getAttribute('name'), 'snowfall' )" <?= $General->arr_general['enable_decoration'] === 'snowfall' ? 'checked' : '' ?>>
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="option-decoration-select">
                        <span class="adaptive-select__span_text"></span>
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
</div>