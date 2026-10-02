<section class="at__section">
    <div class="row">
        <div class="col-md-12">
            <div class="admin_nav">
                <?php if ($AccessController->hasAnyPermission()): ?>
                    <button class="button-icon at__global-search-open" data-open-search>
                        <svg>
                            <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                        </svg>
                    </button>
                    <button class="<?= $view == 'main' ? 'active' : '' ?>" onclick="location.href = '/atools/main/'"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_navHome') ?></button>
                <?php endif; ?>
                <?php if ($AccessController->checkPermission('punishments.view')): ?>
                    <button class="<?= $view == 'punishments' ? 'active' : '' ?>" onclick="location.href = '/atools/punishments/'"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_navPunishments') ?></button>
                <?php endif; ?>
                <?php if ($AccessController->checkPermission('admins.view')): ?>
                    <button class="<?= $view == 'admins' ? 'active' : '' ?>" onclick="location.href = '/atools/admins/'"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_navAdmins') ?></button>
                <?php endif; ?>
                <?php if ($AccessController->checkPermission('checks.view')): ?>
                    <button class="<?= $view == 'checks' ? 'active' : '' ?>" onclick="location.href = '/atools/checks/'"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_navChecks') ?></button>
                <?php endif; ?>
                <?php if ($AccessController->checkPermission('finances.view')): ?>
                    <button class="<?= $view == 'finances' ? 'active' : '' ?>" onclick="location.href = '/atools/finances/'"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_navFinances') ?></button>
                <?php endif; ?>
                <?php if ($AccessController->checkPermission('privileges.view')): ?>
                    <button class="<?= $view == 'privileges' ? 'active' : '' ?>" onclick="location.href = '/atools/privileges/'"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_navPrivileges') ?></button>
                <?php endif; ?>
                <!-- <?php if ($AccessController->checkPermission('credits.view')): ?>
                    <button class="<?= $view == 'credits' ? 'active' : '' ?>" onclick="location.href = '/atools/credits/'"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_navCredits') ?></button>
                <?php endif; ?> -->
                <?php if ($AccessController->checkPermission('experience.view')): ?>
                    <button class="<?= $view == 'experience' ? 'active' : '' ?>" onclick="location.href = '/atools/experience/'"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_navExperience') ?></button>
                <?php endif; ?>
                <?php if ($AccessController->checkPermission('logs.view')): ?>
                    <button class="<?= $view == 'logs' ? 'active' : '' ?>" onclick="location.href = '/atools/logs/'"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_navLogs') ?></button>
                <?php endif; if (isset($_SESSION['user_admin'])): ?>
                    <button class="<?= $view == 'settings' ? 'active' : '' ?>" onclick="location.href = '/atools/settings/'"><?= $Translate->get_translate_module_phrase('module_page_atools', '_at_navSettings') ?></button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if ($AccessController->hasAnyPermission()): ?>
        <div class="at__global-search">
            <div class="at__global-search-wrapper">
                <div class="at__global-search-title">
                    <span class="at__global-search-title-text">
                        <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_navGlobalSearch') ?>
                        <span class="at__global-search-description">
                            <?= $Translate->get_translate_module_phrase('module_page_atools', '_at_globalSearchDescription') ?>
                        </span>
                    </span>
                    <button class="button-icon at__global-search-close" data-close-search>
                        <svg>
                            <use href="/resources/img/sprite.svg#x"></use>
                        </svg>
                    </button>
                </div>
                <div class="at__global-search-input-wrapper">
                    <input type="search" class="at__global-search-input" id="searchUserGlobal" name="user" placeholder="<?= $Translate->get_translate_module_phrase('module_page_atools', '_at_globalSearchPlaceholder') ?>">
                </div>
            </div>
            <div class="at__global-search-results">
                <div class="at__global-search-results-wrapper" id="globalSearchResults">
                    <div class="at__global-search-user-results" id="globalSearchUser"></div>
                    <div id="globalSearchSections"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php require MODULES . MODULE_NAME . "/views/$view.php"; ?>
</section>