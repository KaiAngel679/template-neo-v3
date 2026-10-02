<script>
    <?php if (isset($_SESSION['language'])) : ?>
        var lang = <?= json_encode($_SESSION['language']) ?>
    <?php endif; ?>
</script>
<?php
if ($RQ->access >= 3) : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="admin_nav">
                <button class="admin-nav__btn <?php get_section('page', '') == '' && print 'active' ?>" onclick="location.href = '<?php echo $General->arr_general['site'] ?>request/';">
                    <svg><use href="/resources/img/sprite.svg#request-list"></use></svg>
                    <?= $Translate->get_translate_phrase('_Home') ?>
                </button>
                <?php if ($RQ->access >= 10) : ?>
                    <button class="admin-nav__btn <?php (get_section('page', '') == 'admin' || get_section('page', '') == 'question') && print 'active' ?>" onclick="location.href = '<?php echo set_url_section(get_url(2), 'page', 'admin') ?>';">
                        <svg><use href="/resources/img/sprite.svg#gear"></use></svg>
                        <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_General_settings') ?>
                    </button>
                <?php endif; ?>
                <?php if ($RQ->access >= 3) : ?>
                    <button class="admin-nav__btn <?php get_section('page', '') == 'list' && print 'active' ?>" onclick="location.href = '<?php echo set_url_section(get_url(2), 'page', 'list') ?>';">
                        <svg><use href="/resources/img/sprite.svg#layers"></use></svg>
                        <?= $Translate->get_translate_module_phrase('module_page_request', '_requestsList') ?>
                    </button>
                <?php endif; ?>
                <?php if ($RQ->access >= 8) : ?>
                    <button class="admin-nav__btn <?php get_section('page', '') == 'perm' && print 'active' ?>" onclick="location.href = '<?php echo set_url_section(get_url(2), 'page', 'perm') ?>';">
                        <svg><use href="/resources/img/sprite.svg#shield-check"></use></svg>
                        <?= $Translate->get_translate_module_phrase('module_page_request', '_SettingAccess') ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (isset($_GET['page'])) : ?>
    <div class="row">
        <?php switch ($_GET['page']) {
            case  'admin':
                require MODULES . 'module_page_request' . '/includes/admin.php';
                break;
            case  'list':
                require MODULES . 'module_page_request' . '/includes/list.php';
                break;
            case  'question':
                require MODULES . 'module_page_request' . '/includes/question.php';
                break;
            case  'review':
                require MODULES . 'module_page_request' . '/includes/review.php';
                break;
            case  'my':
                require MODULES . 'module_page_request' . '/includes/my.php';
                break;
            case  'perm':
                require MODULES . 'module_page_request' . '/includes/permission.php';
                break;
        } ?>
    </div>
    
<?php else : ?>
    <div class="row">
        <div class="col-md-3">
            <div class="card sticky-block">
                <div class="card-header">
                    <div class="badge">
                        <svg><use href="/resources/img/sprite.svg#filters"></use></svg>
                        <?= $Translate->get_translate_module_phrase('module_page_request', '_Applications') ?>
                    </div>
                </div>
                <div class="card-container">
                    <?php if (isset($_SESSION['steamid32'])) : ?>
                        <?php if (!empty($myList)) : ?>
                            <button class="width-100" onclick="location.href='<?= $General->arr_general['site'] ?>request/?page=my';">
                                <svg><use href="/resources/img/sprite.svg#request-list"></use></svg>
                                <?= $Translate->get_translate_module_phrase('module_page_request', '_MyApplications') ?>
                            </button>
                        <?php else : ?>
                            <button class="width-100" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_request', '_NoApplications') ?>" data-tippy-placement="top" disabled>
                                <svg><use href="/resources/img/sprite.svg#request-list"></use></svg>
                                <?= $Translate->get_translate_module_phrase('module_page_request', '_MyApplications') ?>
                            </button>
                        <?php endif; ?>
                    <?php else:?>
                        <button onclick="location.href='?auth=login'" class="width-100"><svg><use href="/resources/img/sprite.svg#steam"></use></svg> <?= $Translate->get_translate_module_phrase('module_page_request', '_Auth') ?></button>
                    <?php endif; ?>
                    <hr>
                    <div class="request_choose_buttons">
                        <?php for ($b = 0; $b < sizeof($requests); $b++) { ?>
                            <a class="button width-100 <?php if (($requests[$b]['id']) == ($requests[$request_id]['id'])) {
                                                                echo 'active';
                                                            } ?>" href="<?= set_url_section(get_url(2), 'id', $b) ?>"><?= $requests[$b]['title'] ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="badge">
                        <svg><use href="/resources/img/sprite.svg#edit-pen"></use></svg>
                        <?= empty($requests) ? $Translate->get_translate_module_phrase('module_page_request', '_Applications1')  : $requests[$request_id]['title'] ?>
                    </div>
                </div>
                <div class="card-container">
                    <?php if (empty($requests)) : ?>
                        <div class="empty_request">
                            <svg><use href="/resources/img/sprite.svg#box-empty"></use></svg>
                            <?= $Translate->get_translate_module_phrase('module_page_request', '_NOApplications') ?>
                        </div>
                    <?php else : ?>
                        <form id="request" method="post" class="row__requestform_formblock" onsubmit="SendAjax('#request', 'request', '', '', ''); return false;">
                            <input type="hidden" name="request_id" value="<?= $requests[$request_id]['id'] ?>">
                            <?php if (!empty($requests[$request_id]['discord'])) : ?>
                                <div class="inputs-inline">
                                    <label for="discordInput"><svg><use href="/resources/img/sprite.svg#ds"></use></svg> Discord</label>
                                    <div class="input-wrapper">
                                        <span data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_request', '_YourDs') ?>" data-tippy-placement="right">
                                            <svg><use href="/resources/img/sprite.svg#question"></use></svg>
                                        </span>
                                        <input id="discordInput" type="text" name="discord" placeholder="<?= $Translate->get_translate_module_phrase('module_page_request', '_NicknameDS') ?>">
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($requests[$request_id]['telegram'])) : ?>
                                <div class="inputs-inline">
                                    <label for="telegramInput"><svg><use href="/resources/img/sprite.svg#tg"></use></svg>telegram</label>
                                    <div class="input-wrapper">
                                        <span data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_request', '_YourTg') ?>" data-tippy-placement="right">
                                            <svg><use href="/resources/img/sprite.svg#question"></use></svg> https://t.me/
                                        </span>
                                        <input id="telegramInput" type="text" name="telegram" placeholder="<?= $Translate->get_translate_module_phrase('module_page_request', '_NicknameDS') ?>">
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($requests[$request_id]['vk'])) : ?>
                                <div class="inputs-inline">
                                    <label for="vk"><svg><use href="/resources/img/sprite.svg#vk"></use></svg>vk</label>
                                    <div class="input-wrapper">
                                        <span data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_request', '_VKID') ?>" data-tippy-placement="right">
                                            <svg><use href="/resources/img/sprite.svg#question"></use></svg> https://vk.com/
                                        </span>
                                        <input type="text" name="vk" placeholder="VK ID">
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php foreach ($Question as $key) : ?>
                                <div class="inputs-inline">
                                    <label class="hide-long-text" for="<?= $key['id'] ?>"><?= $key['question'] ?></label>
                                    <div class="input-wrapper">
                                        <span <?php if (!empty($key['clue'])) : ?> data-tippy-content="<?= $key['clue'] ?>" data-tippy-placement="right" <?php endif; ?>>
                                            <svg><use href="/resources/img/sprite.svg#question"></use></svg>
                                        </span>
                                        <input id="<?= $key['id'] ?>" type="text" name="question<?= $key['id'] ?>" placeholder="<?= $key['desc'] ?>" required>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (!empty($requests[$request_id]['age_act'])) : ?>
                                <div class="inputs-inline">
                                    <label for="ageInput">
                                        <svg><use href="/resources/img/sprite.svg#user-solo"></use></svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_request', '_Age') ?>
                                    </label>
                                    <div class="input-wrapper">
                                        <span data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_request', '_realAge') ?>" data-tippy-placement="right">
                                            <svg><use href="/resources/img/sprite.svg#question"></use></svg>
                                        </span>
                                        <input id="ageInput" type="text" name="age" placeholder="<?= $Translate->get_translate_module_phrase('module_page_request', '_YourAge') ?>">
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($requests[$request_id]['server'])) : ?>
                                <fieldset>
                                    <legend><?= $Translate->get_translate_module_phrase('module_page_request', '_GameServer') ?></legend>
                                    <div class="servers-buttons">
                                        <?php foreach ($General->server_list as $key) :
                                            if (in_array($key['id'], $ignore_servers)) :
                                                continue;
                                            endif; ?>
                                            <div class="inputs-inline grid servers-grid">
                                                <input name="server" type="radio" id="server<?= $key['id'] ?>" value="<?= $key['id']; ?>" <?= ($key['id'] == $requests[$request_id]['default_server']) ? "checked" : ""; ?>>
                                                <label class="hide-long-text" for="server<?= $key['id'] ?>"><?= $key['name']; ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </fieldset>
                            <?php endif; ?>
                            <?php if (!empty($requests[$request_id]['rules'])) : ?>
                                <fieldset>
                                    <legend><?= $Translate->get_translate_module_phrase('module_page_request', '_FamiliarServerRules') ?></legend>
                                    <div class="input_radio_buttons">
                                        <div class="inputs-inline">
                                            <input name="rules" type="radio" id="rules0" value="<?= $Translate->get_translate_module_phrase('module_page_request', '_NotFamiliar') ?>" checked>
                                            <label for="rules0"><?= $Translate->get_translate_module_phrase('module_page_request', '_NotFamiliar') ?></label>
                                        </div>
                                        <div class="inputs-inline">
                                            <input name="rules" type="radio" id="rules1" value="<?= $Translate->get_translate_module_phrase('module_page_request', '_Familiar') ?>">
                                            <label for="rules1"><?= $Translate->get_translate_module_phrase('module_page_request', '_Familiar') ?></label>
                                        </div>
                                    </div>
                                </fieldset>
                            <?php endif; ?>
                            <?php if (!empty($requests[$request_id]['criteria'])) : ?>
                                <fieldset>
                                    <legend><?= $Translate->get_translate_module_phrase('module_page_request', '_FamiliarCriteria') ?></legend>
                                    <div class="input_radio_buttons">
                                        <div class="inputs-inline">
                                            <input name="criteria" type="radio" id="criteria0" value="<?= $Translate->get_translate_module_phrase('module_page_request', '_NotFamiliar') ?>" checked>
                                            <label for="criteria0"><?= $Translate->get_translate_module_phrase('module_page_request', '_NotFamiliar') ?></label>
                                        </div>
                                        <div class="inputs-inline">
                                            <input name="criteria" type="radio" id="criteria1" value="<?= $Translate->get_translate_module_phrase('module_page_request', '_Familiar') ?>">
                                            <label for="criteria1"><?= $Translate->get_translate_module_phrase('module_page_request', '_Familiar') ?></label>
                                        </div>
                                    </div>
                                </fieldset>
                            <?php endif; ?>
                        </form>
                        <div class="row__requestform_buttons">
                            <?php if (!empty($_SESSION['steamid'])) : ?>
                                <button form="request" type="submit"><?= $Translate->get_translate_module_phrase('module_page_request', '_SendRequest') ?></button>
                                <button class="button-delete margin-left-auto" form="request" type="reset"><?= $Translate->get_translate_module_phrase('module_page_request', '_ChangedMyMind') ?></button>
                            <?php else : ?>
                                <button class="width-100" onclick="location.href='?auth=login'"><svg><use href="/resources/img/sprite.svg#steam"></use></svg><?= $Translate->get_translate_module_phrase('module_page_request', '_Auth') ?></button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <div class="badge">
                        <?= $Translate->get_translate_module_phrase('module_page_request', '_Info') ?>
                    </div>
                </div>
                <div class="card-container">
                    <?php if (empty($requests)) : ?>
                        <div class="empty_request">
                            <svg><use href="/resources/img/sprite.svg#list-info"></use></svg>
                            <?= $Translate->get_translate_module_phrase('module_page_request', '_NOInfo') ?>
                        </div>
                    <?php else : ?>
                        <picture>
                            <source srcset="/app/modules/module_page_request/assets/img/info.webp" type="image/webp">
                            <img src="/app/modules/module_page_request/assets/img/info.webp" alt="" />
                        </picture>
                        <div class="text_recommend">
                            <?= $Translate->get_translate_module_phrase('module_page_request', '_Recommendations') ?>
                        </div>
                        <div class="requests_rec_block">
                            <div><?php if (!empty($requests[$request_id]['text'])) echo str_replace("\n", "</div><div>", $requests[$request_id]['text']) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>