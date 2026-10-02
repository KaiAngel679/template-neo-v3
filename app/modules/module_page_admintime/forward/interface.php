<?php if (isset($_SESSION['user_admin'])): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="admin_nav">
                <button class="<?php ($page == 'home') && print 'active' ?>" onclick="location.href = '<?= $General->arr_general['site'] . 'admintime/' ?>'">
                    <svg>
                        <use href="/resources/img/sprite.svg#chart"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_admintime', '_adminsStats') ?>
                </button>
                <button class="<?php ($page == 'settings') && print 'active' ?>" onclick="location.href = '<?= $General->arr_general['site'] . 'admintime/settings/' ?>'">
                    <svg>
                        <use href="/resources/img/sprite.svg#gear"></use>
                    </svg>
                    <?= $Translate->get_translate_phrase('_Settings') ?>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require MODULES . 'module_page_admintime/pages/' . $page . '.php'; ?>