<?php if ($RQ->access < 10) {
    header('Location: ' . $General->arr_general['site']);
    exit;
} ?>
<script src="<?= $General->arr_general['site'] ?>storage/assets/js/Sortable.min.js"></script>
<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_request', '_Settings') ?></div>
        </div>
        <div class="card-container">
            <form id="settings" method="post" onsubmit="SendAjax('#settings', 'settings', '', '', ''); return false;">
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="webhoock_offon" id="webhoock_offon" <?php $data['auth'] && print 'checked'; ?>>
                    <label for="webhoock_offon"><?= $Translate->get_translate_module_phrase('module_page_request', '_Discord_notifications') ?></label>
                </div>
                <div class="inputs-inline">
                    <label for="webhookUrl">Webhook URL:</label>
                    <div class="number">
                        <input id="webhookUrl" type="password" name="webhoock_url" value="<?php $data['url'] && print $data['url']; ?>">
                        <div class="eye-password" id="show_pass"><svg><use href="/resources/img/sprite.svg#eye"></use></svg></div>
                    </div>
                </div>
                <button class="width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_request', '_Save') ?></button>
            </form>
            <hr>
            <h3><?= $Translate->get_translate_module_phrase('module_page_request', '_ListOfApplications') ?></h3>
            <a class="button button-add width-100" href="<?= set_url_section(get_url(2), 'request', 'add') ?>">
                <?= $Translate->get_translate_module_phrase('module_page_request', '_NewApplication') ?>
            </a>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><svg><use href="/resources/img/sprite.svg#move"></use></svg></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_request', '_Applications_sort') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_request', '_Status') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="sortable-table" data-type="request">
                        <?php foreach ($List as $key) : ?>
                            <tr class="handle">
                                <td><svg><use href="/resources/img/sprite.svg#two-lines"></use></svg></td>
                                <td><?= $key['title'] ?></th>
                                <td>
                                    <?php if (empty($key['status'])) : ?>
                                        <?= $Translate->get_translate_module_phrase('module_page_request', '_OffRequest') ?>
                                    <?php else : ?>
                                        <?= $Translate->get_translate_module_phrase('module_page_request', '_OnRequest') ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= set_url_section(get_url(2), 'request', $key['id']) ?>" class="button">
                                            <?= $Translate->get_translate_module_phrase('module_page_request', '_ToChange') ?>
                                        </a>
                                        <a href="<?= $General->arr_general['site'] ?>request/?page=question&qid=<?= $key['id'] ?>" class="button">
                                            <?= $Translate->get_translate_module_phrase('module_page_request', '_AddQuestions') ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="col-md-6">
    <?php if (isset($_GET['request']) && $_GET['request'] == 'add') : ?>
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_request', '_AddingApplications') ?>
                <a class="close_settings">
                    <svg data-del="delete" data-get="request"><use href="/resources/img/sprite.svg#x"></use></svg>
                </a>
            </h5>
        </div>
        <div class="card-container">
            <form id="request_add" method="post" onsubmit="SendAjax('#request_add', 'request_add', '', '', ''); return false;">
                    <div class="inputs-inline">
                        <input class="switch" type="checkbox" name="request_offon" id="request_offon">
                        <label for="request_offon"><?= $Translate->get_translate_module_phrase('module_page_request', '_Status') ?></label>
                    </div>
                    <div class="flex-inline">
                        <div class="inputs-inline">
                            <label for="requestName"><?= $Translate->get_translate_module_phrase('module_page_request', '_ApplicationName') ?></label>
                            <input id="requestName" type="text" name="title">
                        </div>
                    </div>
                <textarea id="editor" name="message"></textarea>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="vk_offon" id="vk_offon">
                    <label for="vk_offon">Vk</label>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="discord_offon" id="discord_offon">
                    <label for="discord_offon">Discord</label>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="telegram_offon" id="telegram_offon">
                    <label for="telegram_offon">Telegram</label>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="rules_offon" id="rules_offon">
                    <label for="rules_offon"><?= $Translate->get_translate_module_phrase('module_page_request', '_Rules') ?></label>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="age_offon" id="age_offon">
                    <label for="age_offon"><?= $Translate->get_translate_module_phrase('module_page_request', '_Age') ?></label>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="criteria_offon" id="criteria_offon">
                    <label for="criteria_offon"><?= $Translate->get_translate_module_phrase('module_page_request', '_Criteria') ?></label>
                </div>
                <hr>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for="TimeNextApplication"><?= $Translate->get_translate_module_phrase('module_page_request', '_TimeNextApplication') ?></label>
                        <input id="TimeNextApplication" type="number" name="time" placeholder="<?= $Translate->get_translate_module_phrase('module_page_request', '_SpecifySeconds') ?>">
                    </div>
                    <div class="inputs-inline">
                        <label for="MinimumAge"><?= $Translate->get_translate_module_phrase('module_page_request', '_MinimumAge') ?></label>
                        <input id="MinimumAge" type="number" name="age" placeholder="<?= $Translate->get_translate_module_phrase('module_page_request', '_SpecifyTheMinimumAge') ?>">
                    </div>
                </div>
                <hr>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="hours_act" id="hours_act_offon">
                    <label for="hours_act_offon"><?= $Translate->get_translate_module_phrase('module_page_request', '_LimitHours') ?></label>
                </div>
                <div class="inputs-inline">
                    <label for="PlayToApply"><?= $Translate->get_translate_module_phrase('module_page_request', '_MinimumHours') ?>:</label>
                    <input id="PlayToApply" placeholder="<?= $Translate->get_translate_module_phrase('module_page_request', '_PlayToApply') ?>" name="hours">
                </div>
                <hr>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="server_offon" id="server_offon">
                    <label for="server_offon"><?= $Translate->get_translate_module_phrase('module_page_request', '_GameServer') ?></label>
                </div>
                <hr>
                <fieldset>
                    <legend><?= $Translate->get_translate_module_phrase('module_page_request', '_ServerPY') ?></legend>
                    <div class="input_radio_buttons">
                        <?php foreach ($General->server_list as $key => $server) : ?>
                            <div class="inputs-inline">
                                <input name="default_server" type="radio" id="default_server<?= $key ?>" value="<?= $server['id']; ?>">
                                <label for="default_server<?= $key ?>"><?= $server['name']; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <hr>
                <fieldset>
                    <legend><?= $Translate->get_translate_module_phrase('module_page_request', '_IgnoreSelectedServers') ?></legend>
                    <div class="input_radio_buttons">
                        <?php foreach ($General->server_list as $key => $server) : ?>
                            <div class="inputs-inline">
                                <input name="ignore_server[]" type="checkbox" id="ignore_server<?= $key ?>" value="<?= $server['id']; ?>">
                                <label for="ignore_server<?= $key ?>"><?= $server['name']; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <button class="width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_request', '_ToCreate') ?></button>
            </form>
        </div>
    </div>
        <?php elseif (!empty($_GET['request'])) : $requestEdit = $RQ->getRequest($_GET['request']);
        $ignore_servers = explode(';', $requestEdit['ignore_servers']); ?>
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_request', '_ChangingApplication') ?>
                <a class="close_settings">
                    <svg data-del="delete" data-get="request"><use href="/resources/img/sprite.svg#x"></use></svg>
                </a>
            </h5>
        </div>
        <div class="card-container">
            <form id="request_edit" method="post" onsubmit="SendAjax('#request_edit', 'request_edit', '', '', ''); return false;">
                <input type="hidden" name="request_id_edit" value="<?= $_GET['request'] ?>">
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="request_offon_edit" id="request_offon_edit" <?php $requestEdit['status'] && print 'checked'; ?>>
                    <label for="request_offon_edit"><?= $Translate->get_translate_module_phrase('module_page_request', '_Status') ?></label>
                </div>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for="titleEdit"><?= $Translate->get_translate_module_phrase('module_page_request', '_ApplicationName') ?></label>
                        <input id="titleEdit" name="title_edit" value="<?= $requestEdit['title'] ?>">
                    </div>
                </div>
                <textarea id="editor" name="message_edit"><?php if (!empty($requestEdit['text'])) echo $RQ->OpenBB()->convertFromHtml($requestEdit['text']) ?></textarea>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="vk_offon_edit" id="vk_offon_edit" <?php $requestEdit['vk'] && print 'checked'; ?>>
                    <label for="vk_offon_edit">VK</label>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="discord_offon_edit" id="discord_offon_edit" <?php $requestEdit['discord'] && print 'checked'; ?>>
                    <label for="discord_offon_edit">Discord</label>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="telegram_offon_edit" id="telegram_offon_edit" <?php $requestEdit['telegram'] && print 'checked'; ?>>
                    <label for="telegram_offon_edit">Telegram</label>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="rules_offon_edit" id="rules_offon_edit" <?php $requestEdit['rules'] && print 'checked'; ?>>
                    <label for="rules_offon_edit"><?= $Translate->get_translate_module_phrase('module_page_request', '_Rules') ?></label>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="age_offon_edit" id="age_offon_edit" <?php $requestEdit['age_act'] && print 'checked'; ?>>
                    <label for="age_offon_edit"><?= $Translate->get_translate_module_phrase('module_page_request', '_Age') ?></label>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="criteria_offon_edit" id="criteria_offon_edit" <?php $requestEdit['criteria'] && print 'checked'; ?>>
                    <label for="criteria_offon_edit"><?= $Translate->get_translate_module_phrase('module_page_request', '_Criteria') ?></label>
                </div>
                <hr>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for="timeEdit"><?= $Translate->get_translate_module_phrase('module_page_request', '_TimeNextApplication') ?></label>
                        <input id="timeEdit" name="time_edit" value="<?php $requestEdit['time'] && print $requestEdit['time']; ?>" placeholder="<?= $Translate->get_translate_module_phrase('module_page_request', '_SpecifySeconds') ?>">
                    </div>
                    <div class="inputs-inline">
                        <label for="ageEdit"><?= $Translate->get_translate_module_phrase('module_page_request', '_MinimumAge') ?></label>
                        <input id="ageEdit" name="age_edit" value="<?php $requestEdit['age'] && print $requestEdit['age']; ?>" placeholder="<?= $Translate->get_translate_module_phrase('module_page_request', '_SpecifyTheMinimumAge') ?>">
                    </div>
                </div>
                <hr>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="hours_act_edit" id="hours_act_offon_edit" <?php $requestEdit['hours_act'] && print 'checked'; ?>>
                    <label for="hours_act_offon_edit"><?= $Translate->get_translate_module_phrase('module_page_request', '_LimitHours') ?></label>
                </div>
                <div class="inputs-inline">
                    <label for="hoursEdit"><?= $Translate->get_translate_module_phrase('module_page_request', '_MinimumHours') ?></label>
                    <input id="hoursEdit" name="hours_edit" value="<?php $requestEdit['hours'] && print $requestEdit['hours']; ?>">
                </div>
                <hr>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="server_offon_edit" id="server_offon_edit" <?php $requestEdit['server'] && print 'checked'; ?>>
                    <label for="server_offon_edit"><?= $Translate->get_translate_module_phrase('module_page_request', '_GameServer') ?></label>
                </div>
                <hr>
                <fieldset>
                    <legend><?= $Translate->get_translate_module_phrase('module_page_request', '_ServerPY') ?></legend>
                    <div class="input_radio_buttons">
                        <?php foreach ($General->server_list as $key => $server) : ?>
                            <div class="inputs-inline">
                                <input name="default_server_edit" type="radio" id="default_server_edit<?= $key ?>" value="<?= $server['id']; ?>" <?php ($requestEdit['default_server'] == $server['id']) && print 'checked'; ?>>
                                <label for="default_server_edit<?= $key ?>"><?= $server['name']; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <hr>
                <fieldset>
                    <legend><?= $Translate->get_translate_module_phrase('module_page_request', '_IgnoreSelectedServers') ?></legend>
                    <div class="input_radio_buttons">
                        <?php foreach ($General->server_list as $key => $server) : ?>
                            <div class="inputs-inline">
                                <input name="ignore_server_edit[]" type="checkbox" id="ignore_server_edit<?= $key ?>" value="<?= $server['id']; ?>" <?php in_array($server['id'], $ignore_servers) && print 'checked'; ?>>
                                <label for="ignore_server_edit<?= $key ?>"><?= $server['name']; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            </form>
            <div class="row__requestform_buttons">
                <button type="submit" class="" form="request_edit"><?= $Translate->get_translate_module_phrase('module_page_request', '_Save') ?></button>
                <button class="button-delete margin-left-auto" onclick="SendAjax('','request_del','<?= $_GET['request'] ?>','','')"><?= $Translate->get_translate_module_phrase('module_page_request', '_DelApplication') ?></button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
</div>