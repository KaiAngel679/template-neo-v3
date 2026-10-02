<?php if ($RQ->access < 3) {
    header('Location: ' . $General->arr_general['site']);
    exit;
} ?>
<div class="col-md-3 fix-width-tablet">
    <div class="card sticky-block">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_request', '_Sorting') ?></h5>
        </div>
        <div class="card-container">
            <div class="request_sort_buttons">
                <button class="width-100 <?php if (empty(strip_tags($_GET['type']))) {
                                                echo 'active';
                                            } ?>" onclick="location.href =  '<?= set_url_section(get_url(2), 'type', 0) ?>'">
                    <?= $Translate->get_translate_module_phrase('module_page_request', '_allRequests') ?>
                    <span class="margin-left-auto request_sort_btn-count"><?= count($RQ->getAllList()) ?></span>
                </button>
                <?php foreach ($requests as $key) : ?>
                    <button class="width-100 <?php if ($key['id'] == strip_tags($_GET['type'])) {
                                                    echo 'active';
                                                } ?>" onclick="location.href =  '<?= set_url_section(get_url(2), 'type', $key['id']) ?>'">
                        <?= $key['title'] ?><span class="margin-left-auto request_sort_btn-count"><?= $RQ->getCountList($key['id'])[0] ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            <hr>
            <h3 class="request__mb-1"><?= $Translate->get_translate_module_phrase('module_page_request', '_requestStatus') ?></h3>
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="option-server-select">
                    <li>
                        <label class="adaptive-select__label" for="for_server_all">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_request', '_allRequests') ?></div>
                            <input class="hide-input" id="for_server_all" name="status" onclick="window.location.href=this.value" type="radio" value="<?= set_url_section(get_url(2), 'status', 0) ?>" <?php get_section('status', 0) == 0 && print 'checked' ?> >
                        </label>
                    </li>
                    <?php for ($i = 0; $i < count($RQ->status); $i++): ?>
                        <li>
                            <label class="adaptive-select__label" for="for_server_<?= $i ?>">
                                <div class="adaptive-select__label-text"><?= $RQ->status[$i] ?></div>
                                <input class="hide-input" id="for_server_<?= $i ?>" name="status" onclick="window.location.href=this.value" type="radio" value="<?= set_url_section(get_url(2), 'status', $i + 1) ?>" <?php get_section('status', 0) == $i + 1 && print 'checked' ?>>
                            </label>
                        </li>
                    <?php endfor; ?>
                </ul>
                <div class="adaptive-select" open-select="option-server-select">
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_request', '_allRequests') ?></span>
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
<div class="col-md-9 fix-width-tablet">
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_request', '_ListApplications') ?></h5>
        </div>
        <div class="card-container">
            <div class="request_list">
                <?php if (!empty($List)): ?>
                    <?php foreach ($List as $key) : $Request = $RQ->getRequest($key['rid']); ?>
                        <div class="popup_modal" id="DeleteRequest-<?= $key['id'] ?>">
                            <div class="popup_modal_content no-close no-scrollbar">
                                <div class="popup_modal_head">
                                    <?= $Translate->get_translate_module_phrase('module_page_request', '_confirmation') ?>
                                    <span class="popup_modal_close"><svg>
                                            <use href="/resources/img/sprite.svg#x"></use>
                                        </svg></span>
                                </div>
                                <hr>
                                <div class="request_modal_content">
                                    <?= $Translate->get_translate_module_phrase('module_page_request', '_deleteText') ?>
                                </div>
                                <div class="request_modal_btns">
                                    <button class="button-delete" onclick="SendAjax('','del_list','<?= $key['id'] ?>','','')"><?= $Translate->get_translate_module_phrase('module_page_request', '_yesDelete') ?></button>
                                    <button class="popup_modal_close"><?= $Translate->get_translate_module_phrase('module_page_request', '_missclicked') ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="request_content">
                            <div class="request_user_info_block">
                                <div class="user_avatar_profile none_span" style="position: relative;">
                                    <?= $General->get_js_relevance_avatar(con_steam32to64($key['steamid'])) ?>
                                    <img src="<?= $General->getAvatar(con_steam32to64($key['steamid']), 3) ?>" id="avatar" avatarid="<?= con_steam32to64($key['steamid']) ?>">
                                </div>
                                <div class="request_user_request">
                                    <a href="<?= $General->arr_general['site'] ?>profiles/<?= con_steam32to64($key['steamid']) ?>/?search=1/">
                                        <span class="request_user_nickname" id="name" nameid="<?= con_steam32to64($key['steamid']) ?>"><?= $General->checkName(con_steam32to64($key['steamid'])) ?></span>
                                    </a>
                                    <span class="request_name_request none_span"><?= $Request['title'] ?></span>
                                </div>
                            </div>
                            <div class="request_any_info none_span">
                                <span class="request_idnum">ID: <?= $key['id'] ?></span>
                                <span Class="request_date_time">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#calendare"></use>
                                    </svg>
                                    <?= date('d.m, H:i', $key['date']) ?></span>
                            </div>
                            <div class="request_server_info none_span">
                                <span class="request_servername">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#servers"></use>
                                    </svg>
                                    <?= is_numeric($key['server']) ? $RQ->ServerName($key['server']) : $key['server'] ?>
                                </span>
                                <span Class="request_playtime">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#time"></use>
                                    </svg>
                                    <?= $key['playtime'] . " " . $Translate->get_translate_module_phrase('module_page_request', '_Hours') ?>
                                </span>
                            </div>
                            <div class="request_statusbadge">
                                <span class="request_status_block request_status-<?= $key['status'] ?>"><?= $RQ->status[$key['status']] ?></span>
                            </div>
                            <div class="request_action_buttons">
                                <button onclick="location.href =  '<?= $General->arr_general['site'] ?>request/?page=review&rid=<?= $key['id'] ?>'">
                                    <span><?= $Translate->get_translate_module_phrase('module_page_request', '_ToConsider') ?></span>
                                    <svg>
                                        <use href="/resources/img/sprite.svg#edit-pen"></use>
                                    </svg>
                                </button>
                                <?php if ($RQ->access > 5) : ?>
                                    <button class="button-delete button-icon" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_request', '_DelApplication') ?>" data-tippy-placement="top" data-openmodal="DeleteRequest-<?= $key['id'] ?>">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#trash"></use>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach ?>
                <?php else: ?>
                    <span class="no-data"><?= $Translate->get_translate_phrase('_emptySmile') ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?= Pagination($page_max, $page_num) ?>
</div>