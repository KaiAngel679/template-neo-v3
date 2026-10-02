<?php if (!isset($_SESSION['user_admin']) || IN_LR != true) {
    header('Location: ' . $General->arr_general['site']);
    exit;
}; ?>

<div class="col-md-7">
    <div class="card">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_CasesList') ?></h5>
        </div>
        <div class="card-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Name') ?></th>
                            <th>Тип</th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Price') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Sort') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($CasesList as $list) : ?>
                            <tr>
                                <td><img class="case_img" src="<?= $General->arr_general['site'] ?><?= $list['case_img'] ?>"></td>
                                <td><?= $list['case_name'] ?></td>
                                <td>
                                    <?php if ($list['case_type'] == 1) {
                                        print "Платный";
                                    } else {
                                        print "Free";
                                    } ?>
                                </td>

                                <td><?= $list['case_price'] ?></td>
                                <td><?= $list['case_sort'] ?></td>
                                <td class="buttons_adm_case">
                                    <button onclick="CasesAjax('edit_case_btn', <?= $list['id'] ?>)" class="icon_btn_transparent" data-tippy-content="<?= $Translate->get_translate_phrase('_Change') ?>" data-tippy-placement="top">
                                        <svg viewBox="0 0 512 512">
                                            <path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.8 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z" />
                                        </svg>
                                    </button>
                                    <a href="?section=case&id=<?= $list['id'] ?>" class="button icon_btn_transparent" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_open_case', '_Case_ChangeIt') ?>" data-tippy-placement="top">
                                        <svg viewBox="0 0 448 512">
                                            <path d="M206.5 344.7L70.55 200.6C63.97 193.7 62.17 183.4 65.95 174.6C69.75 165.8 78.42 160.1 88 160.1H160V32.02C160 14.33 174.3 0 192 0H256C273.7 0 288 14.33 288 32.02V160.1H360C369.6 160.1 378.2 165.8 382 174.6C385.8 183.4 384 193.7 377.5 200.6L241.5 344.7C232.4 354.3 215.6 354.3 206.5 344.7zM352 512H96C42.98 512 0 469 0 416V352C0 334.3 14.33 319.1 32 319.1C49.67 319.1 64 334.3 64 352V416C64 433.7 78.33 448 96 448H352C369.7 448 384 433.7 384 416V352C384 334.3 398.3 319.1 416 319.1C433.7 319.1 448 334.3 448 352V416C448 469 405 512 352 512z" />
                                        </svg>
                                    </a>
                                    <button onclick="CasesAjax('del_case_btn', <?= $list['id'] ?>)" class="button-delete icon_btn_transparent" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_open_case', '_Case_ClearCaseNow') ?>" data-tippy-placement="top">
                                        <svg viewBox="0 0 320 512">
                                            <path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <button class="width-100" onclick="CasesAjax('add_case_btn')" style="margin-top: .5rem">Добавить кейс</button>
        </div>
    </div>
    <div class="card" style="margin-top: .5rem;">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_CarList') ?></h5>
        </div>
        <div class="card-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Name') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Sort') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cats as $key) : ?>
                            <tr>
                                <td><?= $key['id'] ?></td>
                                <td><?= $key['name'] ?></td>
                                <td><?= $key['sort'] ?></td>
                                <td class="buttons_adm_case">
                                    <button onclick="CasesAjax('edit_cat_btn', <?= $key['id'] ?>)" class="icon_btn_transparent" data-tippy-content="<?= $Translate->get_translate_phrase('_Change') ?>" data-tippy-placement="top">
                                        <svg viewBox="0 0 512 512">
                                            <path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.8 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z" />
                                        </svg>
                                    </button>
                                    <button onclick="CasesAjax('del_cat_btn', <?= $key['id'] ?>)" class="icon_btn_transparent button-delete" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_open_case', '_Case_ClearCaseNow') ?>" data-tippy-placement="top">
                                        <svg viewBox="0 0 320 512">
                                            <path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <button class="width-100" style="margin-top: .5rem" onclick="CasesAjax('add_cat_btn')">Добавить категорию</button>
        </div>
    </div>
</div>

<div class="col-md-5">
    <div class="card">
        <div class="card-header">
            <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Options') ?></div>
        </div>
        <div class="card-container">
            <form id="case_settings" enctype="multipart/form-data" class="case_settings-form">
                <div class="inputs-inline">
                    <label for="caseCurrency">Валюта</label>
                    <input class="in_f" name="course" value='<?php $settings['course'] && print $settings['course']; ?>' id="caseCurrency">
                </div>
                <div class="inputs-inline">
                    <input class="switch" type="checkbox" name="webhook_offon" id="webhook_offon" <?php $settings['webhook_offon'] == 1 && print 'checked'; ?>>
                    <label for="webhook_offon"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_DiscordMessage') ?></label>
                </div>
                <div class="inputs-inline">
                    <label for="webHookUrl">Webhook URL:</label>
                    <input name="webhook" value="<?php $settings['webhook'] && print $settings['webhook']; ?>" id="webHookUrl">
                </div>
                <div class="inputs-inline">
                    <label for="speed">Скорость прокрутки:</label>
                    <select name="speed" id="speed">
                        <option value="1" <?php $settings['speed'] == 1 && print 'selected'; ?>>Медлено</option>
                        <option value="2" <?php $settings['speed'] == 2 && print 'selected'; ?>>Обычная</option>
                        <option value="3" <?php $settings['speed'] == 3 && print 'selected'; ?>>Быстро</option>
                    </select>
                </div>
            </form>
            <hr>
            <div class="cases__settings-buttons">
                <button class="width-100" onclick="CasesAjax('case_settings', '', 'case_settings')"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Save') ?></button>
                <button class="width-100 button-delete" onclick="CasesAjax('clear_gifts_btn')"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Case_ClearGifts') ?></button>
                <button class="width-100 button-delete" onclick="CasesAjax('clear_list_btn')"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Case_ClearList') ?></button>
                <button class="width-100 button-delete" onclick="CasesAjax('clear_live_btn')">Очистить лайв ленту</button>
            </div>
        </div>
    </div>
</div>
<?php require MODULES . 'module_page_open_case/includes/modals/case.php'; ?>
<?php require MODULES . 'module_page_open_case/includes/modals/cat.php'; ?>
<?php require MODULES . 'module_page_open_case/includes/modals/delete.php'; ?>