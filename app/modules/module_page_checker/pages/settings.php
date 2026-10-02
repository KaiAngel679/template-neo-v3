<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_checker', '_filesSettings'); ?></div>
            </div>
            <div class="card-container">
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" name="" id="access" <?= $settings['auth'] ? 'checked' : '' ?>>
                    <label for="access"><?= $Translate->get_translate_module_phrase('module_page_checker', '_onlyAuth'); ?></label>
                </div>
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for="vtLink"><?= $Translate->get_translate_module_phrase('module_page_checker', '_vtLink'); ?></label>
                        <input type="text" id="vtLink" value="<?= $settings['url_vt'] ?>" placeholder="<?= $Translate->get_translate_module_phrase('module_page_checker', '_blink'); ?>">
                    </div>
                    <div class="inputs-inline">
                        <label for="shareLink"><?= $Translate->get_translate_module_phrase('module_page_checker', '_shareLink'); ?></label>
                        <input type="text" id="shareLink" value="<?= $settings['url_ft'] ?>" placeholder="<?= $Translate->get_translate_module_phrase('module_page_checker', '_blink'); ?>">
                    </div>
                </div>
                <div class="inputs-inline">
                    <label for="checkerName"><?= $Translate->get_translate_module_phrase('module_page_checker', '_checkerName'); ?></label>
                    <input type="text" required value="<?= $settings['name_checker'] ?>" placeholder="PROJECT CHECKER" id="checkerName">
                </div>
                <div class="inputs-inline">
                    <label for="checkerContent"><?= $Translate->get_translate_module_phrase('module_page_checker', '_checkerContent'); ?></label>
                    <textarea name="" id="checkerContent" placeholder="<?= $Translate->get_translate_module_phrase('module_page_checker', '_checkerInside'); ?>"><?= $settings['description_checker'] ?></textarea>
                </div>
                <?php if ($settings['file']) : ?>
                    <span class="current-file">
                        <div id="delete-file" class="delete-file">
                            <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
                        </div>
                        <?= $Translate->get_translate_module_phrase('module_page_checker', '_currentFiles2'); ?> <br> <?= $settings['file'] ?></span>
                <?php endif; ?>
                <input type="file" class="filepond-single">
                <input type="hidden" id="filepond-single">
                <button class="width-100" id="save-one"><?= $Translate->get_translate_phrase('_saveSettings'); ?></button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_checker', '_checkerDemo'); ?></div>
            </div>
            <div class="card-container">
                <button class="width-100" id="add-paragraph"><svg><use href="/resources/img/sprite.svg#plus"></use></svg><?= $Translate->get_translate_module_phrase('module_page_checker', '_addParagraph'); ?></button>
                <hr>
                <?php if (empty($settings['paragraphs'])) {
                    $settings['paragraphs'] = [
                        ['title' => '', 'text' => '']
                    ];
                }
                foreach ($settings['paragraphs'] as $par) : ?>
                    <div class="checker__paragraph">
                        <div class="flex-inline">
                            <div class="inputs-inline">
                                <input class="paragraph-title" type="text" value="<?= action_text_clear($par['title']) ?>" placeholder="<?= $Translate->get_translate_module_phrase('module_page_checker', '_headParagraph'); ?>">
                            </div>
                            <button class="button-delete button-icon paragraph-delete" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_checker', '_delParagraph'); ?>" data-tippy-placement="left">
                                <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
                            </button>
                        </div>
                        <div class="inputs-inline">
                            <textarea class="paragraph-text" placeholder="<?= $Translate->get_translate_module_phrase('module_page_checker', '_contentParagraph'); ?>"><?= action_text_clear($par['text']) ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="hideImg" <?= $settings['slider'] ? 'checked' : '' ?>>
                    <label for="hideImg"><?= $Translate->get_translate_module_phrase('module_page_checker', '_hideImg'); ?></label>
                </div>
                <?php if ($settings['img']) : ?>
                    <span class="current-file">
                        <div id="delete-photo" class="delete-file">
                            <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
                        </div>
                        <?= $Translate->get_translate_module_phrase('module_page_checker', '_currentFiles'); ?> <br> <?= $settings['img'] ?></span>
                <?php endif; ?>
                <input type="file" class="filepond-multiple">
                <input type="hidden" id="filepond-multiple">
                <button class="width-100" id="save-two"><?= $Translate->get_translate_phrase('_saveSettings'); ?></button>
            </div>
        </div>
    </div>
</div>