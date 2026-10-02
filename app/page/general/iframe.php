<div class="row iframe-center">
    <div class="col-md-12">
        <div class="error_content">
            <div class="error__image-wrapper">
                <img class="lazy" data-src="/storage/cache/img/error/error_new.png" alt="">
                <hr>
                <button onclick="history.back();return false;" class="width-100">
                    <svg>
                        <use href="/resources/img/sprite.svg#single-chevrone-left"></use>
                    </svg>
                    Назад
                </button>
            </div>
            <div class="error_texts_block">
                <div class="error__header">
                    <div class="error_oops">
                        <?= $Translate->get_translate_phrase('_ErrorOops') ?>
                    </div>
                    <div class="error_code"><?= htmlentities($_SESSION["iframe_code"]) ?></div>
                    <div class="description"><?= htmlentities($_SESSION["iframe_description"]) ?></div>
                </div>
                <div class="error__footer">
                    <hr>
                    <div class="flex-inline width-100">
                        <button class="width-100" onclick="window.top.location.href = 'https://'+location.hostname"><svg><use href="/resources/img/sprite.svg#homepage"></use></svg><?= $Translate->get_translate_phrase('_Home') ?></button>
                        <button class="pay-link width-100" onclick="window.top.location.href = 'https://' + location.hostname + '/store'"><svg><use href="/resources/img/sprite.svg#diamond"></use></svg><?= $Translate->get_translate_phrase('_SP') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>