<?php if(isset($_SESSION['user_admin'])) : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="admin_nav">
                <button class="admin-nav__btn <?= $views == 'main' ? 'active' : '' ?>" onclick="location.href = '/bonuses'">
                    <svg><use href="/resources/img/sprite.svg#gift"></use></svg><?= $Translate->get_translate_module_phrase('module_page_bonuses', '_bonuses') ?>
                </button>
                <button class="admin-nav__btn <?= $views == 'settings' ? 'active' : '' ?>" onclick="location.href = '/bonuses/settings'">
                    <svg><use href="/resources/img/sprite.svg#gear"></use></svg><?= $Translate->get_translate_phrase('_Settings') ?>
                </button>
            </div>
        </div>
    </div>
<?php endif; require MODULES . "module_page_bonuses/views/$views.php"; ?>