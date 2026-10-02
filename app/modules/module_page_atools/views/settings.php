<div class="card at__card-settings">
    <div class="at__button-wrapper">
        <button class="filter <?= $section == 'general' ? 'active' : '' ?>" onclick="location.href = '/atools/settings/general/'">
            <svg>
                <use href="/resources/img/sprite.svg#gear"></use>
            </svg>
            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_general') ?>
        </button>
        <button class="filter <?= $section == 'accesses' ? 'active' : '' ?>" onclick="location.href = '/atools/settings/accesses/'">
            <svg>
                <use href="/resources/img/sprite.svg#lock"></use>
            </svg>
            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_accessesPanel') ?>
        </button>
        <button class="filter <?= $section == 'reasons' ? 'active' : '' ?>" onclick="location.href = '/atools/settings/reasons/'">
            <svg>
                <use href="/resources/img/sprite.svg#list-info"></use>
            </svg>
            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_reasonsPunishments') ?>
        </button>
        <button class="filter <?= $section == 'terms' ? 'active' : '' ?>" onclick="location.href = '/atools/settings/terms/'">
            <svg>
                <use href="/resources/img/sprite.svg#time-expired"></use>
            </svg>
            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_expires') ?>
        </button>
        <button class="filter <?= $section == 'admin-groups' ? 'active' : '' ?>" onclick="location.href = '/atools/settings/admin-groups/'">
            <svg>
                <use href="/resources/img/sprite.svg#policeman"></use>
            </svg>
            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_adminGroups') ?>
        </button>
        <button class="filter <?= $section == 'vip-groups' ? 'active' : '' ?>" onclick="location.href = '/atools/settings/vip-groups/'">
            <svg>
                <use href="/resources/img/sprite.svg#diamond"></use>
            </svg>
            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_vipGroups') ?>
        </button>
    </div>
    <?php require MODULES . MODULE_NAME . "/views/settings/$section.php"; ?>
</div>