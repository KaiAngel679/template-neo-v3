<?php if(isset($_SESSION['user_admin'])) : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="admin_nav">
                <button class="admin-nav__btn <?php $page == 'main' && print 'active' ?>" onclick="location.href = '/checker'">
                    <svg><use href="/resources/img/sprite.svg#report-list"></use></svg> Главная
                </button>
                <button class="admin-nav__btn <?php $page == 'settings' && print 'active' ?>" onclick="location.href = '/checker/settings'">
                    <svg><use href="/resources/img/sprite.svg#gear"></use></svg><?= $Translate->get_translate_phrase('_Settings') ?>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require MODULES . "module_page_checker/pages/$page.php"; ?>