<?php
!isset($_SESSION['user_admin']) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) ?>
<div class="row">
    <div class="col-md-12">
        <div class="admin_nav">
            <button class="<?php $page == 'main' && print 'active' ?>" onclick="location.href = '/check'">
                <svg>
                    <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_check', '_checkTitle') ?>
            </button>
            <?php if (isset($_SESSION['user_admin'])): ?>
                <button class="<?php $page == 'settings' && print 'active' ?>" onclick="location.href = '/check/settings'">
                    <svg>
                        <use href="/resources/img/sprite.svg#gear"></use>
                    </svg>
                    <?= $Translate->get_translate_phrase('_Settings') ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require MODULES . "module_page_check/pages/{$page}.php";