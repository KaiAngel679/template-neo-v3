<?php if (isset($_SESSION['user_admin'])): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="admin_nav">
                <button class="<?php echo (isset($page) && $page == 'index') ? 'active' : '' ?>" onclick="location.href = '/skinchanger/'">
                    <svg>
                        <use href="/resources/img/sprite.svg#bolt"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_skinChanger') ?>
                </button>
                <button class="<?php echo (isset($page) && $page == 'settings') ? 'active' : '' ?>" onclick="location.href = '/skinchanger/settings/'">
                    <svg>
                        <use href="/resources/img/sprite.svg#gear"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_skinchanger', '_module_settings') ?>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require MODULES . 'module_page_skinchanger/pages/' . $page . '.php'; ?>