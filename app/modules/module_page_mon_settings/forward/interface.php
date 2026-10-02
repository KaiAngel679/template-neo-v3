<?php if (isset($_SESSION['user_admin'])): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="admin_nav">
                <button class="<?php ($page == 'settings') && print 'active' ?>" onclick="location.href = '<?= $General->arr_general['site'] . 'monitoring/' ?>'">
                    <svg>
                        <use href="/resources/img/sprite.svg#gear"></use>
                    </svg>
                    <?= $Translate->get_translate_phrase('_Settings') ?>
                </button>
                <button class="<?php ($page == 'server_list') && print 'active' ?>" onclick="location.href = '<?= $General->arr_general['site'] . 'monitoring/server_list/' ?>'">
                    <svg>
                        <use href="/resources/img/sprite.svg#servers"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_serverList') ?>
                </button>
                <button class="<?php ($page == 'mods') && print 'active' ?>" onclick="location.href = '<?= $General->arr_general['site'] . 'monitoring/mods/' ?>'">
                    <svg>
                        <use href="/resources/img/sprite.svg#list"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_modsSettings') ?>
                </button>
                <button class="<?php ($page == 'logs_access') && print 'active' ?>" onclick="location.href = '<?= $General->arr_general['site'] . 'monitoring/logs_access/' ?>'">
                    <svg>
                        <use href="/resources/img/sprite.svg#key"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_mon_settings', '_accessLogs') ?>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require MODULES . 'module_page_mon_settings/pages/' . $page . '.php'; ?>