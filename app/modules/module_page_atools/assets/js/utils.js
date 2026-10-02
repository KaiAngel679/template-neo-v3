function atDefaultAllServersEnabled() {
    return $('#addingAdmin').attr('data-default-all-servers') === '1';
}

function updateAdaptiveSelectTextsIn($context) {
    if (typeof updateAdaptiveSelectText !== 'function') {
        return;
    }

    var $scope = ($context && $context.length) ? $context : $(document);

    $scope.each(function () {
        var $el = $(this);
        if ($el.hasClass('adaptive-select-wrapper')) {
            updateAdaptiveSelectText($el);
            return;
        }

        $el.find('.adaptive-select-wrapper').each(function () {
            updateAdaptiveSelectText($(this));
        });
    });
}

function resetDefaultAllServersCheckbox(allSelector) {
    var $all = $(allSelector);
    if (!$all.length || !$all.prop('defaultChecked')) {
        return;
    }

    var $wrap = $all.closest('.adaptive-select-wrapper');
    $wrap.find('input[type="checkbox"]').not($all).prop('checked', false);
    $all.prop('checked', true);

    if (typeof updateAdaptiveSelectText === 'function') {
        updateAdaptiveSelectText($wrap);
    }
}

function isAtSiteAdmin(my) {
    return !!(my && my.is_site_admin);
}

function isRestrictedAdminTarget(admin, my) {
    if (!admin || !my) {
        return true;
    }
    if (isAtSiteAdmin(my)) {
        return false;
    }
    return String(admin.steamid) === String(my.steamid) || !!admin.is_site_admin;
}

function sendRequest(body) {
    return $.ajax({
        url: location.href,
        type: 'POST',
        data: body,
        dataType: 'json'
    });
}

function sendRequestWithButton($button, body) {
    var $btn = $button instanceof jQuery ? $button : $($button);

    if (!$btn.length || $btn.prop('disabled')) {
        return $.Deferred().reject().promise();
    }

    $btn.prop('disabled', true);

    return sendRequest(body).always(function () {
        $btn.prop('disabled', false);
    });
}

function getCheckedServerFilterIds(selector) {
    var servers = [];
    $(selector).each(function () {
        servers.push(parseInt($(this).val(), 10));
    });
    return servers.length ? servers : [-1];
}

function bindAtPagination(loadPageFn) {
    $(document).on('click', '.button_pagination[data-page]', function (e) {
        e.preventDefault();
        loadPageFn(parseInt($(this).data('page'), 10));
    });
}

function bindAtListRefresh(loadFn, options) {
    options = options || {};
    $(document).on('limitChanged searchTriggered', function () {
        if (typeof options.guard === 'function' && !options.guard()) {
            return;
        }
        if (options.selector && !$(options.selector).length) {
            return;
        }
        loadFn(options.resetPage ? 1 : undefined);
    });
}

function renderTableAvatars(items, pairs) {
    (items || []).forEach(function (item) {
        (pairs || []).forEach(function (pair) {
            if (item[pair.flagField] && item[pair.steamField]) {
                checkAndRenderAvatar(item[pair.flagField], item[pair.steamField]);
            }
        });
    });
}

function confirmAndRequest(options) {
    openDialog({
        title: options.title || get_translate_module_phrase('module_page_atools', '_at_confirmAction'),
        message: options.message,
        confirmText: options.confirmText,
        cancelText: options.cancelText || get_translate_module_phrase('module_page_atools', '_at_no'),
        onConfirm: function () {
            sendRequest(options.body).done(function (result) {
                noty(result.message, result.status);
                if (result.status === 'success' && typeof options.onSuccess === 'function') {
                    options.onSuccess(result);
                }
            });
        }
    });
}

function finishTableRender(items, avatarPairs) {
    renderTableAvatars(items, avatarPairs);
    initTippy();
    RenderingAvatar();
}

function initTippy() {
    tippy('[data-tippy-content]', {
        animation: 'shift-away',
        theme: 'neo',
        allowHTML: true
    });
}

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function collectSelectedRowIds(tableSelector) {
    const ids = [];
    $(tableSelector + ' .row-checkbox:checked').each(function () {
        ids.push(parseInt($(this).val(), 10));
    });
    return ids.filter(Boolean);
}

function updateBulkSelectAll(tableSelector, selectAllSelector) {
    selectAllSelector = selectAllSelector || '#selectAll';
    const all = $(tableSelector + ' .row-checkbox').length;
    const checked = $(tableSelector + ' .row-checkbox:checked').length;
    const $selectAll = $(selectAllSelector);

    $selectAll.prop('checked', all === checked && all > 0);
    $selectAll.prop('indeterminate', checked > 0 && checked < all);
}

function removeBulkActionsPanel() {
    $('.at__checked-action').remove();
}

function toggleBulkActionsPanel(opts) {
    opts = opts || {};
    const enabled = !!opts.enabled;
    const tableSelector = opts.tableSelector || '';
    const containerSelector = opts.containerSelector || '.table-responsive.at__table';
    const buildHtml = opts.buildHtml;
    const checkedCount = tableSelector
        ? $(tableSelector + ' .row-checkbox:checked').length
        : $('.row-checkbox:checked').length;

    if (!enabled || checkedCount === 0) {
        $('.at__checked-action').fadeOut(150, function () {
            $(this).remove();
        });
        return;
    }

    const html = typeof buildHtml === 'function' ? buildHtml() : '';
    if (!html) {
        removeBulkActionsPanel();
        return;
    }

    const $container = $(containerSelector).first();
    let $panel = $('.at__checked-action');

    if (!$panel.length) {
        $panel = $('<div class="at__checked-action">' + html + '</div>');
        $container.after($panel.hide());
        $panel.fadeIn(150);
    } else {
        $panel.html(html);
    }
}

function updateBulkColumnUi(opts) {
    opts = opts || {};
    const show = !!opts.show;
    const bulkColSelector = opts.bulkColSelector || '.at__bulk-col';
    const selectAllSelector = opts.selectAllSelector || '#selectAll';

    $(bulkColSelector).toggle(show);

    if (!show) {
        $(selectAllSelector).prop({ checked: false, indeterminate: false });
        removeBulkActionsPanel();
    }
}

function initBulkSelection(opts) {
    opts = opts || {};
    const tableSelector = opts.tableSelector;
    const selectAllSelector = opts.selectAllSelector || '#selectAll';
    const isEnabled = opts.isEnabled || function () { return true; };
    const onToggle = opts.onToggle || function () {};
    const ignoreClickSelector = opts.ignoreClickSelector || 'button, a, input';

    $(selectAllSelector).on('change', function () {
        if (!isEnabled()) {
            return;
        }

        const checked = $(this).prop('checked');
        $(tableSelector + ' .row-checkbox').each(function () {
            $(this).prop('checked', checked).closest('tr').toggleClass('at__choosen', checked);
        });
        onToggle();
    });

    $(tableSelector).on('click', 'tr', function (e) {
        if ($(e.target).closest(ignoreClickSelector).length) {
            return;
        }
        if (!isEnabled()) {
            return;
        }

        const $checkbox = $(this).find('.row-checkbox');
        if (!$checkbox.length) {
            return;
        }

        const next = !$checkbox.prop('checked');
        $checkbox.prop('checked', next);
        $(this).toggleClass('at__choosen', next);
        updateBulkSelectAll(tableSelector, selectAllSelector);
        onToggle();
    });

    $(tableSelector).on('change', '.row-checkbox', function () {
        $(this).closest('tr').toggleClass('at__choosen', $(this).prop('checked'));
        updateBulkSelectAll(tableSelector, selectAllSelector);
        onToggle();
    });
}

function resolveAdaptiveRenderer(renderer, renderKey) {
    if (typeof renderer === 'function') {
        return renderer;
    }
    if (renderer && typeof renderKey === 'string' && typeof renderer[renderKey] === 'function') {
        return renderer[renderKey];
    }
    return null;
}

function getAtCatalogPath(path) {
    var parts = String(path || '').split('.');
    var cur = window.atCatalog || {};
    for (var i = 0; i < parts.length; i++) {
        if (cur == null || cur[parts[i]] === undefined) {
            return [];
        }
        cur = cur[parts[i]];
    }
    return Array.isArray(cur) ? cur : [];
}

var atCatalogPromise = null;

function loadAtCatalog() {
    if (window.atCatalog) {
        return $.Deferred().resolve(window.atCatalog).promise();
    }
    if (!atCatalogPromise) {
        atCatalogPromise = sendRequest({ get_catalog: true }).done(function (result) {
            window.atCatalog = (result && result.status === 'success' && result.data) ? result.data : {};
        });
    }
    return atCatalogPromise;
}

function mountLocalSelect(catalogPath, opts) {
    mountAdaptiveSelectItem(opts, getAtCatalogPath(catalogPath));
}

function mountAdaptiveSelectItem(opts, data) {
    if (!opts || typeof opts !== 'object') {
        return;
    }

    var renderFn = resolveAdaptiveRenderer(opts.renderer, opts.renderKey);
    if (!renderFn) {
        return;
    }

    var $root = $(opts.containerSelector);
    var isFilterSelect = $root.closest('.at__filters').length > 0;

    if (isFilterSelect) {
        $root.addClass('skeleton--default');
    }

    $root.html(renderFn(data, opts.name, opts.nameId, opts.openSelect, opts.renderOpts));
    initAdaptiveSelects(opts.containerSelector);
    if ($root.is('.adaptive-select-wrapper') && typeof updateAdaptiveSelectText === 'function') {
        updateAdaptiveSelectText($root);
    }
    if (typeof opts.afterMount === 'function') {
        opts.afterMount($root, data);
    }

    if (isFilterSelect) {
        $root.removeClass('skeleton--default');
    }
}

function applyServerListAllSync($wrap, inputName, allSelector) {
    if (!$wrap || !$wrap.length) {
        return;
    }

    const allInputSelector = allSelector || ('input[name="' + inputName + '"][value="-1"]');
    const $dropdown = $wrap.find('.adaptive-select__dropdown-list');
    const $all = $wrap.find(allInputSelector);
    const $items = $dropdown.find('input[name="' + inputName + '"]').not('[value="-1"]');
    const total = $items.length;
    const checked = $items.filter(':checked').length;

    if (total > 0 && checked === total) {
        $items.prop('checked', false);
        $all.prop('checked', true);
    }

    if (typeof updateAdaptiveSelectText === 'function') {
        updateAdaptiveSelectText($wrap);
    }
}

function onServerListCheckboxChange($input, inputName, allSelector) {
    const $wrap = $input.closest('.adaptive-select-wrapper');
    const allInputSelector = allSelector || ('input[name="' + inputName + '"][value="-1"]');
    const $items = $wrap.find('input[name="' + inputName + '"]').not('[value="-1"]');
    const $all = $wrap.find(allInputSelector);

    if (String($input.val()) === '-1') {
        if ($input.is(':checked')) {
            $items.prop('checked', false);
        }
        if (typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($wrap);
        }
        return;
    }

    if ($all.is(':checked')) {
        $all.prop('checked', false);
    }

    applyServerListAllSync($wrap, inputName, allInputSelector);
}

$(document).on('change', '.adaptive-select__dropdown-list input[type="checkbox"]', function () {
    const $input = $(this);
    const inputName = $input.attr('name');
    if (!inputName) {
        return;
    }

    const $wrap = $input.closest('.adaptive-select-wrapper');
    if (!$wrap.find('input[type="checkbox"][name="' + inputName + '"][value="-1"]').length) {
        return;
    }

    onServerListCheckboxChange($input, inputName);
});

let $activeMenu = null;
let $activeTrigger = null;
let activeMenuId = null;
const registeredMenus = {};

function positionMenu() {
    if (!$activeMenu || !$activeTrigger || !$activeTrigger.length) return;

    const btnRect = $activeTrigger[0].getBoundingClientRect();
    const menuWidth = $activeMenu.outerWidth();
    const menuHeight = $activeMenu.outerHeight();
    const winW = window.innerWidth;
    const winH = window.innerHeight;

    let top = btnRect.bottom + 10;
    let left = btnRect.right - menuWidth;

    if (left < 8) left = 8;
    if (left + menuWidth > winW - 8) left = winW - menuWidth - 8;
    if (top + menuHeight > winH - 8) {
        top = btnRect.top - menuHeight - 10;
    }
    if (top < 8) top = 8;

    $activeMenu.css({ top: top, left: left });
}

function closeMenu() {
    if ($activeMenu) {
        const $menu = $activeMenu;
        $activeMenu = null;
        activeMenuId = null;
        $menu.fadeOut(150, function () {
            $(this).remove();
        });
    }
    if ($activeTrigger) {
        $activeTrigger.data('menu-open', false);
        $activeTrigger = null;
    }
}

function buildMenuHtml(items) {
    let html = '<div class="at__action-list visible">';
    items.forEach(function (item) {
        const cls = item.className ? ` class="${item.className}"` : '';
        const icon = item.icon
            ? `<svg><use href="/resources/img/sprite.svg#${item.icon}"></use></svg> `
            : '';
        html += `<button${cls} data-action="${item.action}">${icon}${item.label}</button>`;
    });
    html += '</div>';
    return html;
}

function resolveVisibleItems(config, $trigger) {
    const rowSelector = (config && config.rowSelector) || 'tr';
    const $row = $trigger && $trigger.length ? $trigger.closest(rowSelector) : $();
    return (config.items || []).filter(function (item) {
        if (typeof item.visible === 'function') {
            return !!item.visible($trigger, $row);
        }
        return true;
    });
}

registerActionMenu = function (menuId, config) {
    registeredMenus[menuId] = config;
};

initActionMenus = function () {
    $(document).on('click', '[data-action-menu]', function (e) {
        e.stopPropagation();

        const $button = $(this);
        const menuId = $button.data('action-menu');
        const config = registeredMenus[menuId];

        if (!config) return;

        if ($button.data('menu-open')) {
            closeMenu();
            return;
        }

        closeMenu();

        const visibleItems = resolveVisibleItems(config, $button);
        if (visibleItems.length === 0) {
            return;
        }

        $activeMenu = $(buildMenuHtml(visibleItems)).hide();
        $activeTrigger = $button;
        activeMenuId = menuId;

        $('body').append($activeMenu);
        positionMenu();
        $activeMenu.fadeIn(150);
        $button.data('menu-open', true);
    });

    $(window).on('scroll resize', function () {
        if ($activeMenu && $activeTrigger) positionMenu();
    });

    if (typeof window._atScrollCapture === 'undefined') {
        window._atScrollCapture = true;
        document.addEventListener('scroll', function () {
            if ($activeMenu && $activeTrigger) positionMenu();
        }, true);
    }

    $(document).on('click', function () {
        closeMenu();
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });

    $(document).on('click', '.at__action-list', function (e) {
        e.stopPropagation();
    });

    $(document).on('click', '.at__action-list button', function () {
        const action = $(this).data('action');
        const config = activeMenuId ? registeredMenus[activeMenuId] : null;
        const $trigger = $activeTrigger;
        const rowSelector = (config && config.rowSelector) || 'tr';
        const $row = $trigger ? $trigger.closest(rowSelector) : null;

        closeMenu();

        if (config && typeof config.onAction === 'function') {
            config.onAction(action, $trigger, $row);
        }
    });
};