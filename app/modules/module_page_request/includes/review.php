<?php if ($RQ->access < 3) {
    header('Location: ' . $General->arr_general['site']);
    exit;
}
$rid = htmlentities($_GET['rid']);
?>
<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <div class="badge">
                <?= $Translate->get_translate_module_phrase('module_page_request', '_RequestUser') ?> <?= action_text_clear($General->checkName(con_steam32to64($List['steamid']))) ?>
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
    <div class="input-form change-category">
        <select name="change-category">
            <?php foreach ($requests as $key) : ?>
                <option <?= $key['id'] == $List['rid'] ? 'selected' : '' ?> value="<?= $key['id'] ?>"><?= $key['title'] ?></option>
            <?php endforeach; ?>
        </select>
        <button id="change"><?= $Translate->get_translate_module_phrase('module_page_request', '_changeCategory') ?></button>
    </div>
    <div class="move_request"><?= $Translate->get_translate_module_phrase('module_page_request', '_manageRequests') ?></div>
    <div class="request_verdict_buttons">
        <button class="verdict_btn under" onclick="SendAjax('','status','1','<?= $rid ?>','')">
            <?= $Translate->get_translate_module_phrase('module_page_request', '_UnderСonsideration') ?>
        </button>
        <?php if ($List['status'] <= 1) : ?>
            <button class="verdict_btn reviewed" onclick="SendAjax('','status','2','<?= $rid ?>','')">
                <?= $Translate->get_translate_module_phrase('module_page_request', '_Reviewed') ?>
            </button>
            <button class="verdict_btn rejected button-delete" onclick="SendAjax('','status','3','<?= $rid ?>','')">
                <?= $Translate->get_translate_module_phrase('module_page_request', '_Rejected') ?>
            </button>
            <button class="width-100" onclick="SendAjax('','status','4','<?= $rid ?>','')">
                <?= $Translate->get_translate_module_phrase('module_page_request', '_CloseApplication') ?>
            </button>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="badge">
                <?= $Translate->get_translate_module_phrase('module_page_request', '_ResponseToRequest') ?>
            </div>
        </div>
        <div class="card-container">
            <form id="answer_admin" method="post" onsubmit="SendAjax('#answer_admin', 'answer_admin', '', '', ''); return false;">
                <input type="hidden" name="review_id" value="<?= $rid ?>">
                <textarea id="editor" name="message"></textarea>
                <div class="req_answer_but">
                    <button class="width-100" type="submit">
                        <?= $Translate->get_translate_module_phrase('module_page_request', '_ToAnswer') ?>
                    </button>
                </div>
            </form>
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
                                <div class="delete_message" onclick="SendAjax('','del_answer','<?= $key['id'] ?>','','')"><?= $Translate->get_translate_module_phrase('module_page_request', '_DelMsg') ?></div>
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
                                <?php if ($RQ->access > 5) : ?>
                                    <div class="delete_message" onclick="SendAjax('','del_answer','<?= $key['id'] ?>','','')"><?= $Translate->get_translate_module_phrase('module_page_request', '_DelMsg') ?></div>
                                <?php endif; ?>
                                <?= date('d.m.Y H:i:s', $key['date']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</div>