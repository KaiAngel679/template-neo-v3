<?php !isset($_SESSION['user_admin']) && get_iframe(403, $Translate->get_translate_phrase('_accessDenied')) ?>
<div class="row">
    <div class="col-md-12">
        <div class="admin_nav">
            <button class="<?php get_section('section', 'general') == 'general' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'general') ?>'">
                <svg>
                    <use href="/resources/img/sprite.svg#gear"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_General_settings') ?>
            </button>
            <button class="<?php get_section('section', 'general') == 'template' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'template') ?>'">
                <svg>
                    <use href="/resources/img/sprite.svg#palette"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Template_settings') ?>
            </button>
            <button class="<?php get_section('section', 'general') == 'modules' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'modules') ?>'">
                <svg>
                    <use href="/resources/img/sprite.svg#folder-gear"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Configuring_modules') ?>
            </button>
            <button class="<?php get_section('section', 'general') == 'servers' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'servers') ?>'">
                <svg>
                    <use href="/resources/img/sprite.svg#servers"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Server_setting') ?>
            </button>
            <button class="<?php get_section('section', 'general') == 'db' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'db') ?>'">
                <svg>
                    <use href="/resources/img/sprite.svg#database"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Database_settings') ?>
            </button>
            <button class="<?php get_section('section', 'general') == 'stats' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'stats') ?>'">
                <svg>
                    <use href="/resources/img/sprite.svg#chart"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Site_stats') ?>
            </button>
            <button class="<?php get_section('section', 'general') == 'users' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'users') ?>'">
                <svg>
                    <use href="/resources/img/sprite.svg#new-users"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Users_page') ?>
            </button>
            <button class="<?php echo (get_section('section', 'general') == 'logsweb' || get_section('section', 'general') == 'logslk' || get_section('section', 'general') == 'logsshop') ? 'active' : '' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'logsweb') ?>'">
                <svg>
                    <use href="/resources/img/sprite.svg#log"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Site_logs') ?>
            </button>
            <button class="<?php echo (get_section('section', 'general') == 'dev') ? 'active' : '' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'dev') ?>'">
                <svg>
                    <use href="/resources/img/sprite.svg#log"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_dev') ?>
            </button>
        </div>
    </div>
</div>
<div class="row row_admins">
    <?php switch (get_section('section', 'general')) {
        case 'modules':
            require MODULES . 'module_page_adminpanel' . '/includes/modules.php';
            break;
        case 'servers':
            require MODULES . 'module_page_adminpanel' . '/includes/servers.php';
            break;
        case 'db':
            require MODULES . 'module_page_adminpanel' . '/includes/db.php';
            break;
        case 'template':
            require MODULES . 'module_page_adminpanel' . '/includes/template.php';
            break;
        case 'stats':
            require MODULES . 'module_page_adminpanel' . '/includes/stats.php';
            break;
        case 'logsweb':
            require MODULES . 'module_page_adminpanel' . '/includes/logsweb.php';
            break;
        case 'logslk':
            require MODULES . 'module_page_adminpanel' . '/includes/logslk.php';
            break;
        case 'logsshop':
            require MODULES . 'module_page_adminpanel' . '/includes/logsshop.php';
            break;
        case 'users':
            require MODULES . 'module_page_adminpanel' . '/includes/users.php';
            break;
        case 'dev':
            require MODULES . 'module_page_adminpanel' . '/includes/dev.php';
            break;
        default:
            require MODULES . 'module_page_adminpanel' . '/includes/general.php';
            break;
    } ?>
</div>