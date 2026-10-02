<?php if (isset($_SESSION['user_admin'])): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="admin_nav">
                <button class="<?php ($page == 'notifications') && print 'active' ?>" onclick="location.href = '<?= $General->arr_general['site'] . 'notifications/' ?>'">
                    <svg>
                        <use href="/resources/img/sprite.svg#bell"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_notifications', '_notifications') ?>
                </button>
                <button class="<?php ($page == 'settings') && print 'active' ?>" onclick="location.href = '<?= $General->arr_general['site'] . 'notifications/settings/' ?>'">
                    <svg>
                        <use href="/resources/img/sprite.svg#gear"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_notifications', '_Settings') ?>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require MODULES . 'module_page_notifications/pages/' . $page . '.php'; ?>