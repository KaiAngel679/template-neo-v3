<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_check', '_verdictCheck') ?></div>
            </div>
            <div class="card-container">
                <span class="chek__info">
                    <?= $Translate->get_translate_module_phrase('module_page_check', '_config') ?>
                </span>
                <button id="addReason" class="width-100 check__verdict-add">
                    <svg>
                        <use href="/resources/img/sprite.svg#plus"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_check', '_addVerdict') ?>
                </button>
                <form id="addReasons" method="post">
                    <div id="checkreasonsList" class="check__verdict-list">
                        <?php if (empty($Check->getReasonFile())): ?>
                            <div class="check__verdict-flex">
                                <input type="text" placeholder="<?= $Translate->get_translate_module_phrase('module_page_check', '_setVerdict') ?>" name="reason[]" value="">
                                <button type="button" class="button-delete">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#trash"></use>
                                    </svg>
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($Check->getReasonFile() as $reason): ?>
                                <div class="check__verdict-flex">
                                    <input type="text" placeholder="<?= $Translate->get_translate_module_phrase('module_page_check', '_setVerdict') ?>" name="reason[]" value="<?= $reason ?>">
                                    <button type="button" class="button-delete">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#trash"></use>
                                        </svg>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button class="width-100" type="submit"><?= $Translate->get_translate_phrase('_saveSettings') ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_check', '_checkAccess') ?></div>
            </div>
            <div class="card-container">
                <div class="inputs-inline">
                    <input id="giveAccess" type="checkbox" class="switch" onclick="changeSettings(this.id)" <?php $Check->settings['giveAccess'] === 1 && print 'checked' ?>>
                    <label for="giveAccess"><?= $Translate->get_translate_module_phrase('module_page_check', '_giveAccess') ?></label>
                </div>
                <form id="addAdminAccess" method="post">
                    <div class="flex-inline inputs-inline add-admin">
                        <input type="text" placeholder="<?= $Translate->get_translate_module_phrase('module_page_check', '_setSteam') ?>" name="admin">
                        <button><?= $Translate->get_translate_module_phrase('module_page_check', '_giveAccessButton') ?></button>
                    </div>
                </form>
                <hr>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= $Translate->get_translate_phrase('_Admin') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($Check->getAccess())): ?>
                                <tr>
                                    <td>
                                        <?= $Translate->get_translate_phrase('_absent') ?>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($Check->getAccess() as $steamid): ?>
                                    <tr>
                                        <td>
                                            <span class="grid-text">
                                                <?php $General->get_js_relevance_avatar($steamid); ?>
                                                <a href="/profiles/<?= $steamid ?>/?search=1" class="hide-long-text" id="name" nameid="<?= $steamid ?>"><?= $General->checkName($steamid) ?></a>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="button-delete button-icon checkDelAccess" id_del="<?= $steamid ?>">
                                                    <svg>
                                                        <use href="/resources/img/sprite.svg#trash"></use>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>