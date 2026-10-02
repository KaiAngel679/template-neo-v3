<div class="row">
    <div class="col-md-12">
        <div class="admin_nav">
            <?php if (!empty($access['results']) || !empty($access['theirs']) || !empty($access['full'])): ?>
                <button class="<?php $page == 'results' && print 'active' ?>" onclick="location.href = '/results/'">
                    <svg>
                        <use href="/resources/img/sprite.svg#bolt"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_results', '_Summary') ?>
                </button>
                <button class="<?php $page == 'admintime' && print 'active' ?>" onclick="location.href = '/results/admintime/'">
                    <svg>
                        <use href="/resources/img/sprite.svg#chart"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_results', '_resultsAdmins') ?>
                </button>
            <?php endif; ?>
            <?php if (!empty($access['admins']) || !empty($access['full'])): ?>
                <button class="<?php $page == 'adminsinfo' && print 'active' ?>" onclick="location.href = '/results/adminsinfo/'">
                    <svg>
                        <use href="/resources/img/sprite.svg#shield-check"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_results', '_adminsInfo') ?>
                </button>
            <?php endif; ?>
            <?php if (!empty($access['warns']) || !empty($access['full'])): ?>
                <button class="<?php $page == 'warns' && print 'active' ?>" onclick="location.href = '/results/warns/'">
                    <svg>
                        <use href="/resources/img/sprite.svg#list-info"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_results', '_warnsList') ?>
                </button>
            <?php endif; ?>
            <?php if (!empty($access['full'])): ?>
                <button class="<?php $page == 'settings' && print 'active' ?>" onclick="location.href = '/results/settings/'">
                    <svg>
                        <use href="/resources/img/sprite.svg#gear"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_results', '_moduleSettings') ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="row row_admins">
    <?php require MODULES . 'module_page_results/pages/' . $page . '.php'; ?>
</div>