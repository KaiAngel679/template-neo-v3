<?php if ($General->arr_general['navigation'] === 'sidebar'): ?>
    <aside class="sidebar <?= ($General->arr_general['sidebarState'] == 1) ? 'opened' : ''; ?>">
        <nav class="sidebar__wrapper">
            <span class="sidebar__header">
                <span class="sidebar__header-text">
                    <?= $Translate->get_translate_phrase('_navigation') ?>
                </span>
                <div class="sidebar__toggle">
                    <svg>
                        <use href="/resources/img/sprite.svg#single-chevrone-right"></use>
                    </svg>
                    <span class="sidebar__slider"></span>
                </div>
            </span>
            <ul class="sidebar__list">
                <?php foreach ($General->get_neo_menu() as $menu): ?>
                    <?php
                    $hasMenuRestrictions = !empty($menu['onlyAdminSite']) || !empty($menu['onlyAuth']) || !empty($menu['onlyAdmin']) || !empty($menu['onlyAdminServer']);
                    $menuAllows = !$hasMenuRestrictions || (
                        (!empty($menu['onlyAdminSite']) && $isAdmin) ||
                        (!empty($menu['onlyAuth']) && $isAuth) ||
                        (!empty($menu['onlyAdmin']) && $hasAdminAccess) ||
                        (!empty($menu['onlyAdminServer']) && $hasServerAccess)
                    );
                    $link = !empty($menu['url']) ? $menu['url'] : '/' . ($Modules->array_modules[$menu['module']]['page'] ?? '');
                    if ($isAdmin || $menuAllows): ?>
                        <?php if ($menu['type'] == 'category'): ?>
                            <?php
                            $childLinks = array_map(function ($point) use ($Modules) {
                                $lp = !empty($point['url']) ? $point['url'] : '/' . ($Modules->array_modules[$point['module']]['page'] ?? '');
                                return trim($lp, '/');
                            }, $menu['children']);
                            $categoryActive = in_array(trim($Modules->route, '/'), $childLinks, true);
                            ?>
                            <li>
                                <a class="sidebal__item sidebar__submenu <?= $categoryActive ? 'active' : '' ?>" role="button" aria-expanded="false">
                                    <?= $menu['icon'] ?>
                                    <span class="sidebar__item-text">
                                        <?= (isset($menu['title'][0]) && $menu['title'][0] === '_' ? $Translate->get_translate_phrase($menu['title']) : $menu['title']) ?>
                                    </span>
                                    <svg class="chevron-icon">
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </a>
                                <ul class="sidebar__sublist">
                                    <?php foreach ($menu['children'] as $point): ?>
                                        <?php
                                        $hasMenuRestrictions = !empty($point['onlyAdminSite']) || !empty($point['onlyAuth']) || !empty($point['onlyAdmin']) || !empty($point['onlyAdminServer']);
                                        $pointAllows = !$hasMenuRestrictions || (
                                            (!empty($point['onlyAdminSite']) && $isAdmin) ||
                                            (!empty($point['onlyAuth']) && $isAuth) ||
                                            (!empty($point['onlyAdmin']) && $hasAdminAccess) ||
                                            (!empty($point['onlyAdminServer']) && $hasServerAccess)
                                        );
                                        $link_point = !empty($point['url']) ? $point['url'] : '/' . ($Modules->array_modules[$point['module']]['page'] ?? '');
                                        if ($isAdmin || $pointAllows): ?>
                                            <li>
                                                <a class="sidebar__subitem <?= (trim($Modules->route, '/') == trim($link_point, '/')) ? 'active' : ''; ?>" href="<?= $link_point ?>" <?= $point['blank'] ? 'target="_blank"' : '' ?>>
                                                    <?= $point['icon'] ?>
                                                    <?= (isset($point['title'][0]) && $point['title'][0] === '_' ? $Translate->get_translate_phrase($point['title']) : $point['title']) ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php elseif ($menu['type'] == 'point'): ?>
                            <li>
                                <a class="sidebal__item <?= ($Modules->route == substr($link, 1) || (substr($link, 1) == '' && $Modules->route == 'home')) ? 'active' : ''; ?>" href="<?= $link ?>" <?= $menu['blank'] ? 'target="_blank"' : '' ?>>
                                    <?= $menu['icon'] ?>
                                    <span class="sidebar__item-text">
                                        <?= (isset($menu['title'][0]) && $menu['title'][0] === '_' ? $Translate->get_translate_phrase($menu['title']) : $menu['title']) ?>
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
    </aside>
    <script>
        if (localStorage.getItem('sidebarOpened') === 'true') {
            document.querySelector('.sidebar')?.classList.add('opened');
        }
    </script>
<?php endif; ?>