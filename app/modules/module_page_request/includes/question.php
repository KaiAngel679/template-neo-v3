<?php if ($RQ->access < 5) {
    header('Location: ' . $General->arr_general['site']);
    exit;
}
$qid = $_GET['qid'];
$RequestQuestion = $RQ->getRequestQuestionAdmin($qid); ?>
<script src="<?= $General->arr_general['site'] ?>storage/assets/js/Sortable.min.js"></script>
<div class="col-md-9">
    <div class="card">
        <div class="card-header">
            <h5 class="badge">
                <?= $Translate->get_translate_module_phrase('module_page_request', '_ApplicationQuestions') ?>
            </h5>
        </div>
        <div class="card-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><svg><use href="/resources/img/sprite.svg#move"></use></svg></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_request', '_Question') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_request', '_Description') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_request', '_Hint') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="sortable-table" data-type="question" data-id="<?=$qid?>">
                        <?php foreach ($RequestQuestion as $key): ?>
                            <tr class="handle">
                                <td><svg><use href="/resources/img/sprite.svg#two-lines"></use></svg></td>
                                <td class="break-space"><?= $key['question'] ?></td>
                                <td class="break-space"><?= $key['desc'] ?></td>
                                <td><?= $key['clue'] ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="location.href =  '<?= set_url_section(get_url(2), 'question', $key['id']) ?>'">
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
<div class="col-md-3">
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_request', '_Settings') ?></h5>
        </div>
        <div class="card-container">
            <button class="width-100" onclick="location.href = '<?= set_url_section(get_url(2), 'question', 'add') ?>'">
                <?= $Translate->get_translate_module_phrase('module_page_request', '_AddQuestions') ?>
            </button>
        </div>
    </div>
</div>
<?php if (isset($_GET['question']) && $_GET['question'] == 'add'): ?>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_request', '_AddingQuestion') ?>
                    <a class="close_settings">
                        <svg data-del="delete" data-get="question"><use href="/resources/img/sprite.svg#x"></use></svg>
                    </a>
                </h5>
            </div>
            <div class="card-container">
                <form id="question_add" method="post"
                    onsubmit="SendAjax('#question_add', 'question_add', '', '', ''); return false;">
                    <input type="hidden" name="request_id_question" value="<?= $qid ?>">
                    <div class="inputs-inline">
                        <label for="questionInput"><?= $Translate->get_translate_module_phrase('module_page_request', '_Question') ?></label>
                        <input id="questionInput" type="text" name="question">
                    </div>
                    <div class="inputs-inline">
                        <label for="descInput"><?= $Translate->get_translate_module_phrase('module_page_request', '_Description') ?></label>
                        <input id="descInput" type="text"  name="desc">
                    </div>
                    <div class="inputs-inline">
                        <label for="clueInput"><?= $Translate->get_translate_module_phrase('module_page_request', '_Hint') ?></label>
                        <input id="clueInput" type="text"  name="clue">
                    </div>
                    <div class="inputs-inline">
                        <label for="sortInput"><?= $Translate->get_translate_module_phrase('module_page_request', '_Sorting') ?></label>
                        <input id="sortInput" type="text"  name="sort">
                    </div>
                    <button type="submit" class="width-100"><?= $Translate->get_translate_module_phrase('module_page_request', '_Add') ?></button>
                </form>
            </div>
        </div>
    </div>
<?php elseif (!empty($_GET['question'])):
    $questionEdit = $RQ->getQuestionData($_GET['question']); ?>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_request', '_ChangingQuestion') ?>
                    <a class="close_settings">
                        <svg data-del="delete" data-get="question"><use href="/resources/img/sprite.svg#x"></use></svg>
                    </a>
                </h5>
            </div>
            <div class="card-container">
                <form id="question_edit" method="post" onsubmit="SendAjax('#question_edit', 'question_edit', '', '', ''); return false;">
                    <input type="hidden" name="question_id_edit" value="<?= $_GET['question'] ?>">
                    <input type="hidden" name="request_id_edit" value="<?= $qid ?>">
                        <div class="inputs-inline">
                            <label for="questionEditInput"><?= $Translate->get_translate_module_phrase('module_page_request', '_Question') ?></label>
                            <input id="questionEditInput" type="text" name="question_edit" value="<?= $questionEdit['question'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label for="descEditInput"><?= $Translate->get_translate_module_phrase('module_page_request', '_Description') ?></label>
                            <input id="descEditInput" type="text" name="desc_edit" value="<?= $questionEdit['desc'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label for="clueEditInput"><?= $Translate->get_translate_module_phrase('module_page_request', '_Hint') ?></label>
                            <input id="clueEditInput" type="text" name="clue_edit" value="<?= $questionEdit['clue'] ?>">
                        </div>
                        <div class="inputs-inline">
                            <label for="sortEditINput"><?= $Translate->get_translate_module_phrase('module_page_request', '_Sorting') ?></label>
                            <input id="sortEditINput" type="text" name="sort_edit" value="<?= $questionEdit['sort'] ?>">
                        </div>
                </form>
                <div class="row__requestform_buttons">
                    <button type="submit" form="question_edit"><?= $Translate->get_translate_module_phrase('module_page_request', '_Save') ?></button>
                    <button class="button-delete margin-left-auto" onclick="SendAjax('','question_del','<?= $_GET['question'] ?>','<?= $qid ?>','')">
                        <?= $Translate->get_translate_module_phrase('module_page_request', '_Del') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>