
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_check', '_checkTitle') ?></div>
            </div>
            <div class="card-container">
                <div class="check__inputs">
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="option-server-select">
                            <?php foreach ($servers as $key => $value) : ?>
                                <li>
                                    <label class="adaptive-select__label" for="for_server_<?= $value['id'] ?>">
                                        <div class="adaptive-select__label-text"><?= $value['name'] ?></div>
                                        <input class="hide-input" id="for_server_<?= $value['id'] ?>" type="radio" value="<?=$value['id']?>" name="server_id" <?= $key == 0 ? 'checked' : '' ?>>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="adaptive-select" open-select="option-server-select">
                            <span class="adaptive-select__span_text">-</span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="option-Verdicts-select">
                            <li>
                                <label class="adaptive-select__label" for="for_all">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_check', '_allVerdicts') ?></div>
                                    <input class="hide-input" id="for_all" type="radio"  value="all" name="verdict" checked>
                                </label>
                            </li>
                            <?php foreach ($reasons as $key => $reason) : ?>
                                <li>
                                    <label class="adaptive-select__label" for="for_<?= $key ?>">
                                        <div class="adaptive-select__label-text"><?= $reason ?></div>
                                        <input class="hide-input" id="for_<?= $key ?>" type="radio" value="<?= $key ?>" name="verdict">
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="adaptive-select" open-select="option-Verdicts-select">
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_check', '_allVerdicts') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="inputs-inline width-100"><input id="search" type="search" placeholder="<?= $Translate->get_translate_module_phrase('module_page_check', '_placeholder') ?>"></div>
                </div>
                <div class="table-responsive" id="contentTable">
                    <div class="no-data"><?= $Translate->get_translate_phrase('_nothingFound') ?></div>
                </div>
                <div id="contentPagination"></div>
            </div>
        </div>
    </div>
</div>