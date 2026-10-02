<?php if (isset($_SESSION['user_admin'])) : ?>
<div class="row">
    <div class="col-md-12">
        <div class="admin_nav">
            <button class="<?= $view == 'settings' ? 'active' : '' ?>" onclick="location.href = '/cards/settings'">
                <svg>
                    <use href="/resources/img/sprite.svg#gear"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_cards', '_cardsSettings'); ?>
            </button>
            <button class="<?= $view == 'stats' ? 'active' : '' ?>" onclick="location.href = '/cards/stats'">
                <svg>
                    <use href="/resources/img/sprite.svg#chart"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_cards', '_cardsStats'); ?>
            </button>
        </div>
    </div>
</div>
<?php require MODULES . "module_page_cards/views/$view.php"; endif; ?>