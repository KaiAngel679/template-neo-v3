<?php if (!isset($_SESSION['steamid'])) {
    header('Location: ' . $General->arr_general['site']);
    exit;
} ?>
<?php if (isset($_GET['page']) && !isset($_GET['rid'])) : ?>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_request', '_ListMyApplications') ?></h5>
            </div>
            <div class="card-container">
                <div class="request_list scroll">
                    <?php foreach ($myList as $key) : $Request = $RQ->getRequest($key['rid']); ?>
                        <div class="request_content request_my_req">
                            <div class="request_user_info_block">
                                <div class="request_user_request">
                                    <span class="request_name_request"><?= $Request['title'] ?></span>
                                </div>
                            </div>
                            <div class="request_any_info none_span">
                                <span Class="request_date_time">
                                    <svg><use href="/resources/img/sprite.svg#calendare"></use></svg>
                                    <?= date('d.m, H:i', $key['date']) ?></span>
                            </div>
                            <div class="request_server_info none_span">
                                <span class="request_servername">
                                    <svg><use href="/resources/img/sprite.svg#servers"></use></svg>
                                    <?= is_numeric($key['server']) ? $RQ->ServerName($key['server']) : $key['server'] ?></span>
                                <span Class="request_playtime">
                                    <svg><use href="/resources/img/sprite.svg#time"></use></svg>
                                    <?= $key['playtime'] . " " . $Translate->get_translate_module_phrase('module_page_request', '_Hours') ?></span>
                            </div>
                            <div class="request_statusbadge">
                                <span class="request_status_block request_status-<?= $key['status'] ?>"><?= $RQ->status[$key['status']] ?></span>
                            </div>
                            <div class="request_action_buttons">
                                <button onclick="location.href =  '<?= $General->arr_general['site'] ?>request/?page=my&rid=<?= $key['id'] ?>'">
                                    <?= $Translate->get_translate_module_phrase('module_page_request', '_Open') ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>
<?php elseif (isset($_GET['rid'])) : ?>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_request', '_RequestUser') ?> <?= $General->checkName(con_steam32to64($List['steamid'])) ?>
                </div>
            </div>
            <div class="card-container">
                <div class="request_list_info">
                    <div class="request_title_id"><?= $Request['title'] ?> (#<?= $List['id'] ?>)</div>
                    <div class="request_status_block request_status-<?= $List['status'] ?>"><?= $RQ->status[$List['status']] ?></div>
                </div>
                <div class="req_text_title"><?= $Translate->get_translate_module_phrase('module_page_request', '_ApplicationText') ?></div>
                <div class="request_list_info_text">
                    <div class="request_answers"><?= $List['text'] ?></div>
                </div>
            </div>
        </div>
        <br>
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_request', '_ResponseToRequest') ?>
                </div>
            </div>
            <div class="card-container module_block">
                <form id="answer" method="post" onsubmit="SendAjax('#answer', 'answer', '', '', ''); return false;">
                    <input type="hidden" name="review_id" value="<?= $_GET['rid'] ?>">
                    <textarea id="editor" name="message"></textarea>
                </form>
                <div class="req_answer_but">
                    <button class="secondary_btn w100" type="submit" form="answer">
                        <?= $Translate->get_translate_module_phrase('module_page_request', '_ToAnswer') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_request', '_ResponsesToApplication') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="message_block_of scroll no-scrollbar">
                    <?php foreach ($Review as $key) : ?>
                        <?php if ($key['admin'] == 1) : ?>
                            <div class="message_block">
                                <div class="message_user_text">
                                    <div class="message_nickname" id="name" nameid="<?= con_steam32to64($key['steamid']) ?>"><?= $General->checkName(con_steam32to64($key['steamid'])) ?></div>
                                    <div class="message_text"><?= $key['text'] ?></div>
                                </div>
                                <div class="message_date">
                                    <?= date('d.m.Y H:i:s', $key['date']) ?>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="message_block_player">
                                <div class="message_user_text_player">
                                    <div class="message_nickname_player" id="name" nameid="<?= con_steam32to64($key['steamid']) ?>"><?= $General->checkName(con_steam32to64($key['steamid'])) ?></div>
                                    <div class="message_text_player"><?= $key['text'] ?></div>
                                </div>
                                <div class="message_date_player">
                                    <div class="delete_message" onclick="SendAjax('','del_answer','<?= $key['id'] ?>','','')"><?= $Translate->get_translate_module_phrase('module_page_request', '_DelMsg') ?></div>
                                    <?= date('d.m.Y H:i:s', $key['date']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>