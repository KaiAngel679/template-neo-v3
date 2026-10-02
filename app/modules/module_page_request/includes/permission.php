<?php if ($RQ->access < 7) {
    header('Location: ' . $General->arr_general['site']);
    exit;
} ?>
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_request', '_ListAdmins') ?></h5>
        </div>
        <div class="card-container">
            <button class="width-100" data-openmodal="addAccess">
                <?= $Translate->get_translate_module_phrase('module_page_request', '_NewAdmin') ?>
            </button>
            <hr>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_request', '_NicknameTg') ?>
                            </th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_request', '_Applications_send') ?>
                            </th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_request', '_AccessApplications') ?>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Admins as $key): ?>
                            <tr>
                                <td><?= $key['aid'] ?></td>
                                <td>
                                    <a href="<?= $General->arr_general['site'] ?>profiles/<?= ($key['steamid']) ?>/?search=1/" id="name" nameid="<?= $key['steamid'] ?>">
                                        <?= $General->checkName($key['steamid']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (empty($key['request'])): ?>
                                        <svg class="red">
                                            <use href="/resources/img/sprite.svg#x"></use>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="green">
                                            <use href="/resources/img/sprite.svg#check"></use>
                                        </svg>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (empty($key['review'])): ?>
                                        <svg class="red">
                                            <use href="/resources/img/sprite.svg#x"></use>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="green">
                                            <use href="/resources/img/sprite.svg#check"></use>
                                        </svg>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button data-openmodal="editAccess<?= $key['aid'] ?>">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#edit-pen"></use>
                                            </svg>
                                            <?= $Translate->get_translate_module_phrase('module_page_request', '_ToChange') ?>
                                        </button>
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
<?php foreach ($Admins as $key):
    $AdminEdit = $RQ->getAdmin($key['aid']); ?>
    <div class="popup_modal" id="editAccess<?= $key['aid'] ?>">
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                <?= $Translate->get_translate_module_phrase('module_page_request', '_ChangingAdmin') ?> <span
                    class="popup_modal_close">
                    <svg viewBox="0 0 320 512">
                        <path
                            d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z">
                        </path>
                    </svg>
                </span>
            </div>
            <form id="perm_edit<?= $key['aid'] ?>" method="post"
                onsubmit="SendAjax('#perm_edit<?= $key['aid'] ?>', 'perm_edit', '', '', ''); return false;">
                <input type="hidden" name="admin_id_edit" value="<?= $key['aid'] ?>">
                <div class="inputs-inline">
                    <label for="steamAdminEdit">steamid 64</label>
                    <input id="steamAdminEdit" type="text" name="steamid_edit" value="<?= $AdminEdit['steamid'] ?>">
                </div>
                <hr>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="request_edit" id="request_edit" <?php $AdminEdit['request'] && print 'checked'; ?>>
                    <label
                        for="request_edit"><?= $Translate->get_translate_module_phrase('module_page_request', '_Applications_sort') ?></label>
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="review_edit" id="review_edit" <?php $AdminEdit['review'] && print 'checked'; ?>>
                    <label
                        for="review_edit"><?= $Translate->get_translate_module_phrase('module_page_request', '_AccessApplications') ?></label>
                </div>
            </form>
            <hr>
            <div class="row__requestform_buttons">
                <button type="submit"
                    form="perm_edit<?= $key['aid'] ?>"><?= $Translate->get_translate_module_phrase('module_page_request', '_Save') ?></button>
                <button class="button-delete margin-left-auto"
                    onclick="SendAjax('', 'perm_del', '<?= $key['aid'] ?>', '', '')">
                    <?= $Translate->get_translate_module_phrase('module_page_request', '_Del') ?>
                </button>
            </div>
        </div>
    </div>
<?php endforeach ?>
<div class="popup_modal" id="addAccess">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_request', '_AddingAdmin') ?> <span
                class="popup_modal_close">
                <svg viewBox="0 0 320 512">
                    <path
                        d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z">
                    </path>
                </svg>
            </span>
        </div>
        <form id="perm_add" method="post" onsubmit="SendAjax('#perm_add', 'perm_add', '', '', ''); return false;">
            <div class="inputs-inline">
                <label for="steamAdmin">steamid 64</label>
                <input id="steamAdmin" type="text" name="steamid">
            </div>
            <hr>
            <div class="inputs-inline">
                <input class="switch" type="checkbox" name="request" id="request">
                <label
                    for="request"><?= $Translate->get_translate_module_phrase('module_page_request', '_Applications_send') ?></label>
            </div>
            <div class="inputs-inline">
                <input class="switch" type="checkbox" name="review" id="review">
                <label
                    for="review"><?= $Translate->get_translate_module_phrase('module_page_request', '_AccessApplications') ?></label>
            </div>
            <hr>
            <button type="submit"
                class="width-100"><?= $Translate->get_translate_module_phrase('module_page_request', '_Add') ?></button>
        </form>
    </div>
</div>