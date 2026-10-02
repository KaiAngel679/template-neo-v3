<?php

?>
<section class="rv">
    <?php if (!empty($isAdmin)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="admin_nav">
                    <button class="<?= ($view ?? 'main') === 'main' ? 'active' : '' ?>" type="button" onclick="location.href='/reviews/'">
                        <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_navHome') ?>
                    </button>
                    <button class="<?= ($view ?? '') === 'settings' ? 'active' : '' ?>" type="button" onclick="location.href='/reviews/settings/'">
                        <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_navSettings') ?>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php require MODULES . MODULE_NAME . '/views/' . $view . '.php'; ?>
</section>
