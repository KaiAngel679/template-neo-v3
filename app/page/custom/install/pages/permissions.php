<?php !defined("IN_LR") && die() ?>
<div class="back">
    <h2><?= $options['language'] == 'EN' ? 'Give permissions to files' : 'Дайте разрешения на файлы' ?></h2>
    <hr>
    <div class="install_permissions">
        <div class="add_peersim_text"><?= $options['language'] == 'EN' ? 'Grant 755 permissions to folders:' : 'Выдайте права 755 на папки:' ?></div>
        <span class="<?= substr(sprintf('%o', fileperms(SESSIONS)), -4) == '0777' ? 'ready' : 'not_ready' ?>">
            /storage/cache/sessions/
            <?= substr(sprintf('%o', fileperms(SESSIONS)), -4) == '0777' ? '<svg style="width: 20px; height: auto; fill: var(--green);" viewBox="0 0 512 512"><path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"></path></svg>' : '<svg style="width: 15px; height: auto; fill: var(--red);" viewBox="0 0 320 512"><path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"></path></svg>' ?>
        </span>
        <span class="<?= substr(sprintf('%o', fileperms(CACHE . 'img/avatars/')), -4) == '0777' ? 'ready' : 'not_ready' ?>">
            /storage/cache/img/avatars/
            <?= substr(sprintf('%o', fileperms(CACHE . 'img/avatars/')), -4) == '0777' ? '<svg style="width: 20px; height: auto; fill: var(--green);" viewBox="0 0 512 512"><path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"></path></svg>' : '<svg style="width: 15px; height: auto; fill: var(--red);" viewBox="0 0 320 512"><path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"></path></svg>' ?>
        </span>
        <span class="<?= substr(sprintf('%o', fileperms(MODULESCACHE)), -4) == '0777' ? 'ready' : 'not_ready' ?>">
            /storage/modules_cache/ рекурсивно
            <?= substr(sprintf('%o', fileperms(MODULESCACHE)), -4) == '0777' ? '<svg style="width: 20px; height: auto; fill: var(--green);" viewBox="0 0 512 512"><path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"></path></svg>' : '<svg style="width: 15px; height: auto; fill: var(--red);" viewBox="0 0 320 512"><path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"></path></svg>' ?>
        </span>
    </div>
    <a href="/" class="button width-100"><?= $options['language'] == 'EN' ? 'Check' : 'Проверить' ?></a>
</div>