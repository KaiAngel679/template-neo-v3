<?php if (!isset($_SESSION['user_admin']) || IN_LR != true) {
    header('Location: ' . $General->arr_general['site']);
    exit;
}; ?>
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_OpenCaseList') ?></h5>
        </div>
        <div class="card-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Пользователь</th>
                            <th>Кейс</th>
                            <th>Приз</th>
                            <th>Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($List as $key) : ?>
                            <tr>
                                <td>
                                    <a class="cases__list-gap" href="<?= $General->arr_general['site'] ?>profiles/<?= ($key['steam_id']) ?>/?search=1/">
                                        <?php if ($General->arr_general['avatars'] != 0) : $General->get_js_relevance_avatar(con_steam32to64($key['steam_id']), 1) ?>
                                            <img class="cases__list-img" src="<?= $General->getAvatar(con_steam32to64($key['steam_id']), 2) ?>">
                                        <?php endif; ?>
                                        <?= action_text_clear(action_text_trim($General->checkName(con_steam32to64($key['steam_id'])), 17)) ?>
                                    </a>
                                </td>
                                <td>
                                    <a class="cases__list-gap" href="<?= $this->General->arr_general['site'] ?>cases/?section=case&id=<?= $key['case_id'] ?>">
                                        <img width="32" class="cases__list-case" src="<?= $General->arr_general['site'] ?><?= $key['case_img'] ?>">
                                        <?= $key['case_name'] ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="cases__list-gap">
                                        <img width="32" class="cases__list-case" src="<?= $General->arr_general['site'] ?><?= $key['subject_img'] ?>">
                                        <?= $key['subject_name'] ?>
                                    </div>
                                </td>
                                <td><?= $key['date'] ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="pagination">
        <?= Pagination($page_max, $page_num) ?>
    </div>
</div>