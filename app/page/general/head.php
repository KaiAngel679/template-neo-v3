<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?= $Modules->get_page_description() ?>">
    <meta property="og:description" content="<?= $Modules->get_page_description() ?>">
    <link rel="icon" href="<?= $General->arr_general['site'] ?>/favicon.ico" type="image/x-icon">
    <?php if (!empty($Modules->get_page_canonical())): ?>
        <link rel="canonical" href="<?= $Modules->get_page_canonical() ?>" />
    <?php endif; ?>
    <title><?= $Modules->get_page_title() ?></title>
    <meta property="og:title" content="<?= $Modules->get_page_title() ?>">
    <meta property="og:image" content="<?= $Modules->get_page_image() ?>">
    <meta name="robots" content="<?= !isset($_SESSION['metatag_index']) ? 'index' : 'noindex' ?>" />
    <link rel="image_src" href="<?= $Modules->get_page_image() ?>">
    <meta name="twitter:image" content="<?= $Modules->get_page_image() ?>">
    <meta name="keywords" content="<?= $General->arr_general['keywords'] ?>">
    <link rel="stylesheet" type="text/css" href="/app/templates/neo_remastered/assets/css/iziToast.min.css">
    <link rel="stylesheet" type="text/css"
        href="/app/templates/neo_remastered/assets/css/search.css<?php $General->arr_general['css_off_cache'] == 1 && print "?" . time() ?>">
    <link rel="stylesheet" type="text/css" href="/app/templates/neo_remastered/assets/css/shift-away.css">
    <link rel="stylesheet" type="text/css"
        href="/app/templates/neo_remastered/assets/css/stars.css<?php $General->arr_general['css_off_cache'] == 1 && print "?" . time() ?>">
    <?php if ($Modules->route == 'home'): ?>
        <link rel="stylesheet" href="/app/templates/neo_remastered/assets/css/swiper-bundle.min.css" />
    <?php endif; ?>
    <?php for ($style = 0, $style_s = sizeof($Modules->css_library); $style < $style_s; $style++): ?>
        <link rel="stylesheet" type="text/css"
            href="/<?= $Modules->css_library[$style] ?><?php $General->arr_general['css_off_cache'] == 1 && print "?" . time() ?>">
        <?php endfor;
    if (!empty($Modules->arr_module_init['page'][$Modules->route]['css'])):
        for ($css = 0, $css_s = sizeof($Modules->arr_module_init['page'][$Modules->route]['css']); $css < $css_s; $css++): ?>
            <link rel="stylesheet" type="text/css"
                href="/app/modules/<?= $Modules->arr_module_init['page'][$Modules->route]['css'][$css]['name'] . '/assets/css/' . $Modules->arr_module_init['page'][$Modules->route]['css'][$css]['type'] . '.css' ?><?php $General->arr_general['css_off_cache'] == 1 && print "?" . time() ?>">
    <?php endfor;
    endif; ?>
    <?php if (!empty($Modules->arr_module_init['css_always'])) : ?>
        <?php for ($module_id = 0, $c_mi = sizeof($Modules->arr_module_init['css_always']); $module_id < $c_mi; $module_id++) : ?>
            <?php if (file_exists(MODULES . $Modules->arr_module_init['css_always'][$module_id] . '/assets/css/always.css')): ?>
                <link rel="stylesheet" type="text/css"
                    href="/app/modules/<?= $Modules->arr_module_init['css_always'][$module_id] . '/assets/css/always.css' ?><?php $General->arr_general['css_off_cache'] == 1 && print "?" . time() ?>">
            <?php endif; ?>
        <?php endfor; ?>
    <?php endif; ?>
    <?php if (isset($General->arr_general['enable_decoration']) && $General->arr_general['enable_decoration'] === 'snowfall'): ?>
        <link rel="stylesheet" type="text/css" href="/app/templates/neo_remastered/assets/css/snowfall.css<?php $General->arr_general['css_off_cache'] == 1 && print "?" . time() ?>">
    <?php endif; ?>
    <style id="include">
        <?= $Graphics->get_css_color_palette() ?>
    </style>
    <?= file_exists(ASSETS_JS . 'translations.js') ? '<script src="/storage/assets/js/translations.js?' . time() . '"></script>' : '' ?>
    <script>
        var avatar = [],
            faceit = [],
            translate = window.TRANSLATIONS || {},
            lang = '<?= isset($_SESSION["language"]) ? $_SESSION["language"] : $General->arr_general['language'] ?>';
        let domain = '<?= $General->arr_general['site'] ?>';
    </script>
    <script src="/storage/assets/js/translator.js<?php $General->arr_general['css_off_cache'] == 1 && print "?" . time() ?>"></script>
</head>

<body class="sidebar-collapse <?php ($General->background['type'] == 3 || $General->background['type'] == 5) && print  'theme__gradient-' . $General->gradient ?? '1' ?>" <?php if ($General->background['type'] == 2): ?>style="background-image: url('/<?= $General->background['image'] ?>'); background-size: cover; background-attachment:fixed; background-repeat: no-repeat;" <?php endif; ?>>
    <?php if ($General->background['type'] == 4 || $General->background['type'] == 5): ?>
        <canvas id="starfield"></canvas>
    <?php endif; ?>
    <?php if (isset($General->arr_general['enable_decoration']) && $General->arr_general['enable_decoration'] === 'snowfall'): ?>
        <div class="snow" aria-hidden="true">
            <?php for ($i = 0; $i < 100; $i++): $flake = ['❅', '❆', '❄'][array_rand(['❅', '❆', '❄'])]; ?>
                <div class="snow__flake"><?= $flake ?></div>
            <?php endfor; ?>
        </div>
    <?php endif; ?>