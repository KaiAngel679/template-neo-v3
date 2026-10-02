<?php if (!isset($_SESSION['user_admin']) || IN_LR != true) {
    header('Location: ' . $General->arr_general['site']);
    exit;
}; ?>
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_WinsList') ?></h5>
        </div>
        <div class="card-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Пользователь</th>
                            <th>Кейс</th>
                            <th>Приз</th>
                            <th>Где приз</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Wins as $key) : ?>
                            <tr>
                                <td>
                                    <a class="cases__list-gap" href="<?= $General->arr_general['site'] ?>profiles/<?= ($key['steam_id']) ?>/?search=1/">
                                        <?php $General->get_js_relevance_avatar(con_steam32to64($key['steam_id']), 1) ?>
                                        <img class="cases__list-img" src="<?= $General->getAvatar(con_steam32to64($key['steam_id']), 2) ?>">
                                        <?= action_text_clear(action_text_trim($General->checkName(con_steam32to64($key['steam_id'])), 17)) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php $case_info = $CASES->getCaseDatabyID($CASES->getSubjectData($key['subject_id'])['case_id'])?>
                                    <a class="cases__list-gap" href="<?= $this->General->arr_general['site'] ?>cases/?section=case&id=<?= $case_info['case_id'] ?>">
                                        <img width="32" class="cases__list-case" src="<?= $General->arr_general['site'] ?><?= $case_info['case_img'] ?>">
                                        <?= $case_info['case_name'] ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="cases__list-gap">
                                        <img width="32" class="cases__list-case" src="<?= $General->arr_general['site'] ?><?= $key['subject_img'] ?>">
                                        <?= $key['subject_name'] ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($key['up'] == 0 && $key['sell'] == 0):?>
                                        в Инвентаре
                                    <?php elseif($key['up'] == 1 && $key['sell'] == 1):?>
                                        Деньги
                                    <?php elseif($key['up'] == 1):?>
                                        Забрал(а)
                                    <?php elseif($key['sell'] == 1):?>
                                        Продал(а)
                                    <?php endif;?> 
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="pagination">
        <?= Pagination($page_num, $page_max) ?>
    </div>
</div>