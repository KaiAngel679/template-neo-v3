const adaptiveRenderers = {
    adminList: renderAdminsSelectList,
    serverList: renderServerList,
    termsList: renderTermsList,
    punishReasonList: renderPunishReasonList
};

let currentPunishmentsPage = 1;
let punishmentsData = null;
let myData = null;
let punishFiltersResetting = false;
let punishCalendarApi = null;
let punishmentReasons = [];

function syncOnlinePlayersEmptyState() {
    const $list = $('#onlinePlayerPunishList');
    if (!$list.length) {
        return;
    }

    const hasPlayers = $list.find('input[type="radio"][name="online-player-punish"]').length > 0;
    const $empty = $list.find('.at__online-players-empty');
    const emptyText = get_translate_module_phrase('module_page_atools', '_at_playersNotFound');

    if (!hasPlayers) {
        if ($empty.length) {
            $empty.html('<div class="no-data">' + emptyText + '</div>').show();
        } else {
            $list.append('<li class="at__online-players-empty"><div class="no-data">' + emptyText + '</div></li>');
        }
        $list.children('.inputs-inline').remove();
    } else {
        $empty.remove();
    }
}

function initOnlinePlayerPunishPicker(players) {
    const $switch = $('#useOnlinePlayerPunish');
    const $switchRow = $('#onlinePlayerPunishSwitchRow');
    const $steam = $('#steamIdInput');
    const $wrap = $('#onlinePlayerPunishWrap');

    if (!$switch.length || !$steam.length || !$wrap.length || !$switchRow.length) {
        return;
    }

    const items = (Array.isArray(players) ? players : []).filter(function (player) {
        return player && String(player.steamid || '').trim() !== '';
    });

    mountAdaptiveSelectItem({
        containerSelector: '#onlinePlayerPunishWrap',
        renderer: renderOnlinePlayersList,
        name: 'online-player-punish',
        nameId: 'onlinePlayerPunish',
        openSelect: 'onlinePlayerPunishList'
    }, items);

    syncOnlinePlayersEmptyState();

    const apply = function () {
        const enabled = $switch.is(':checked');
        $wrap.toggle(enabled);
        $steam.closest('.inputs-inline').toggle(!enabled);
        $steam.prop('required', !enabled);

        if (!enabled) {
            $wrap.find('input[type="radio"]').prop('checked', false);
            if (typeof updateAdaptiveSelectText === 'function') {
                updateAdaptiveSelectText($wrap);
            }
        }
    };

    $switch.off('change.atOnline').on('change.atOnline', apply);
    $(document).off('click.atOnlineEmpty').on('click.atOnlineEmpty', '#onlinePlayerPunishWrap [open-select="onlinePlayerPunishList"]', function () {
        setTimeout(syncOnlinePlayersEmptyState, 0);
    });
    $(document).off('change.atOnlinePunish').on('change.atOnlinePunish', '#onlinePlayerPunishWrap input[type="radio"]', function () {
        const sid = String($(this).val() || '').trim();
        if (sid) {
            $steam.val(sid);
        }
    });

    if (typeof updateAdaptiveSelectText === 'function') {
        updateAdaptiveSelectText($wrap);
    }
    apply();
}

function getFilterServersOrDefault() {
    return getCheckedServerFilterIds('#serversListFilter input[type="checkbox"]:checked');
}

function buildPunishmentsListRequest(page) {
    const dates = getAtCalendarDateRange(punishCalendarApi);

    return {
        get_punishments_list: true,
        type: $('#punishFilterGame input[type="radio"]:checked').val(),
        punish_type: $('#punishFilterType input[type="radio"]:checked').val(),
        admin: parseInt($('#adminListFilter input[type="radio"]:checked').val() || -1, 10),
        servers: getFilterServersOrDefault(),
        date_from: dates.from,
        date_to: dates.to,
        expire_filter: $('#expiresPunishes input[name="type-expire-punish"]:checked').val() || 'all',
        limit: getListLimit(),
        offset: (page - 1) * getListLimit(),
        search: getSearchQuery()
    };
}

function loadAdaptivePair(type) {
    mountLocalSelect('admins.' + type, {
        containerSelector: '#punishFilterByAdmin',
        renderer: adaptiveRenderers,
        renderKey: 'adminList',
        name: 'admin-list-filter',
        nameId: 'adminListFilter',
        openSelect: 'adminListFilter',
        renderOpts: { defaultAllAdmins: true }
    });

    mountLocalSelect('servers.' + type, {
        containerSelector: '#punishFilterServers',
        renderer: adaptiveRenderers,
        renderKey: 'serverList',
        name: 'servers-list-filter',
        nameId: 'serversListFilter',
        openSelect: 'serversListFilter',
        renderOpts: { defaultAllServers: true }
    });

    loadPunishmentsList();
}

function applyPunishmentsListResult(result, page) {
    if (result.status != 'success') {
        return;
    }
    punishmentsData = result.data;
    myData = result.my_data;
    window.atPunishMyData = myData;
    $('#punishTable').html(renderPunishmentsList(result.data));
    updatePunishTableBulkUi();
    const totalPages = Math.ceil(result.total / getListLimit());
    $('#punishmentsPagination').html(renderPagination(page, totalPages));
    finishTableRender(result.data, [
        { flagField: 'admin_checked_avatar', steamField: 'admin_steamid' },
        { flagField: 'offender_checked_avatar', steamField: 'offender_steamid' }
    ]);
}

function loadPunishmentsList(page) {
    page = page || 1;
    currentPunishmentsPage = page;
    sendRequest(buildPunishmentsListRequest(page))
        .done(function (result) {
            applyPunishmentsListResult(result, page);
        });
}

function resetPunishFilters() {
    punishFiltersResetting = true;

    if (punishCalendarApi) {
        punishCalendarApi.reset();
    }

    resetFilterPanel('punishFilters', function () {
        $('#punishFilterTypeBan').prop('checked', true);

        const $allExpire = $('#allPunishments');
        $allExpire.prop('checked', true);
        if (typeof updateAdaptiveSelectText == 'function') {
            updateAdaptiveSelectText($allExpire.closest('.adaptive-select-wrapper'));
        }

        $('#punishFilterGame input[type="radio"]').first().prop('checked', true);
    });

    punishFiltersResetting = false;

    const game = $('#punishFilterGame input[type="radio"]:checked').val();
    if (game) {
        loadAdaptivePair(game);
    } else {
        loadPunishmentsList(1);
    }
}

function buildCreatePunishmentPayload() {
    const servers = [];
    $('#punishServer input[name="punish-server"]:checked').each(function () {
        servers.push($(this).val());
    });

    return {
        create_punishment: true,
        steamid: ($('#steamIdInput').val()).trim(),
        ip: ($('#ipInput').val() || '').trim(),
        punish_type: $('#punishType input[name="punish-type"]:checked').val(),
        reason: $('#punishReasonWrap input[name="punish-reason"]:checked').val() || $('#punishReasonCustom').val(),
        expire: $('#punishTime input[name="punish-time"]:checked').val() || $('#punishCustomTime').val(),
        servers: servers
    };
}

function resolvePunishReasonListType(punishTypeValue) {
    if (punishTypeValue == 'ban' || punishTypeValue == 'mute') {
        return punishTypeValue;
    }

    return String(punishTypeValue) == '0' ? 'ban' : 'mute';
}

function getPunishmentReasonsByType(punishTypeValue) {
    const listType = resolvePunishReasonListType(punishTypeValue);

    return punishmentReasons.filter(function (reason) {
        return reason.type === listType;
    });
}

function loadPunishmentReasons() {
    punishmentReasons = getAtCatalogPath('reasons');
    return $.Deferred().resolve().promise();
}

function mountPunishmentReasonSelect(containerSelector, punishTypeValue, opts) {
    const data = getPunishmentReasonsByType(punishTypeValue);
    const renderFn = adaptiveRenderers[opts.renderKey];
    const $root = $(containerSelector);

    $root.html(renderFn(data, opts.name, opts.nameId, opts.openSelect));
    initAdaptiveSelects(containerSelector);

    if ($root.is('.adaptive-select-wrapper') && typeof updateAdaptiveSelectText === 'function') {
        updateAdaptiveSelectText($root);
    }

    if (typeof opts.afterMount === 'function') {
        opts.afterMount($root, data);
    }
}

function getCreatePunishmentReasonListType() {
    const $selected = $('#punishType input[name="punish-type"]:checked');
    if (!$selected.length) {
        return null;
    }

    return resolvePunishReasonListType($selected.val());
}

function getCreatePunishTypeWrapper() {
    return $('#punishType').closest('.adaptive-select-wrapper');
}

function removeCreatePunishmentReasonSelect() {
    $('#punishReasonWrap').remove();
}

function mountCreatePunishmentReasonSelect() {
    removeCreatePunishmentReasonSelect();
    $('<div class="adaptive-select-wrapper" id="punishReasonWrap"></div>')
        .insertAfter(getCreatePunishTypeWrapper());
}

function toggleCreatePunishmentReasonSelect() {
    if (!getCreatePunishmentReasonListType()) {
        removeCreatePunishmentReasonSelect();
        return;
    }

    mountCreatePunishmentReasonSelect();
    loadCreatePunishmentReasons();
}

function loadCreatePunishmentReasons() {
    const $selected = $('#punishType input[name="punish-type"]:checked');

    if (!$selected.length) {
        return;
    }

    mountPunishmentReasonSelect('#punishReasonWrap', $selected.val(), {
        renderer: adaptiveRenderers,
        renderKey: 'punishReasonList',
        name: 'punish-reason',
        nameId: 'punishReason',
        openSelect: 'punishReason'
    });
}

function buildUpdatePunishmentPayload() {
    const servers = [];
    $('#editPunish #punishServerEditList input[name="punish-server-edit"]:checked').each(function () {
        servers.push($(this).val());
    });

    return {
        update_punishment: true,
        type: $('#punishFilterGame input[type="radio"]:checked').val(),
        filter_punish_type: $('#punishFilterType input[type="radio"]:checked').val(),
        punish_id: parseInt($('#confirmEditPunish').data('punish-id'), 10),
        ip: ($('#ipInputEdit').val() || '').trim(),
        punish_type: $('#editPunish input[name="punish-type-edit"]:checked').val(),
        reason: $('#editPunish input[name="punish-reason-edit"]:checked').val() || $('#editPunishReasonCustom').val(),
        expire: $('#editPunish input[name="punish-time-edit"]:checked').val() || $('#editPunishTermsCustom').val(),
        servers: servers
    };
}

function fillEditPunishmentType(punish) {
    const typeVal = punish.punish_type != null ? String(punish.punish_type) : '';
    const $radio = $('#editPunish input[name="punish-type-edit"][value="' + typeVal + '"]');
    if ($radio.length) {
        $radio.prop('checked', true).trigger('change');
    }
    if (typeof updateAdaptiveSelectText == 'function') {
        updateAdaptiveSelectText($('#punishTypeEdit').closest('.adaptive-select-wrapper'));
    }
}

function fillEditPunishmentReason(punish, $wrap) {
    const reason = punish.reason || '';
    $wrap.find('input[name="punish-reason-edit"]').prop('checked', false);
    $wrap.find('#editPunishReasonCustom').val('');
    const $match = $wrap.find('input[name="punish-reason-edit"]').filter(function () {
        return $(this).val() == reason;
    });
    if ($match.length) {
        $match.first().prop('checked', true).trigger('change');
    } else if (reason) {
        $wrap.find('#editPunishReasonCustom').val(reason);
    }
    if (typeof updateAdaptiveSelectText == 'function') {
        updateAdaptiveSelectText($wrap);
    }
}

function fillEditPunishmentTime(punish, $wrap) {
    const name = 'punish-time-edit';
    const expUnix = punish.expires != null ? Number(punish.expires) : 0;

    $wrap.find('input[name="' + name + '"]').prop('checked', false);
    $wrap.find('#editPunishTermsCustom').val('');

    if (expUnix == 0) {
        const $forever = $wrap.find('input[name="' + name + '"][value="0"]');
        if ($forever.length) {
            $forever.prop('checked', true).trigger('change');
        } else {
            $wrap.find('#editPunishTermsCustom').val('0');
        }
    } else {
        const left = Math.max(0, Math.floor(expUnix - Date.now() / 1000));
        const $term = $wrap.find('input[name="' + name + '"][value="' + String(left) + '"]');
        if ($term.length) {
            $term.prop('checked', true).trigger('change');
        } else {
            $wrap.find('#editPunishTermsCustom').val(String(left));
        }
    }
    if (typeof updateAdaptiveSelectText == 'function') {
        updateAdaptiveSelectText($wrap);
    }
}

function fillEditPunishmentServers(punish, $wrap) {
    const ids = Array.isArray(punish.server_panel_ids) ? punish.server_panel_ids.map(String) : [];
    const $dropdown = $wrap.find('.adaptive-select__dropdown-list');
    const $all = $wrap.find('#editPunishServersAll');

    $dropdown.find('input[name="punish-server-edit"]').prop('checked', false);
    $all.prop('checked', false);

    if (!ids.length) {
        if (typeof updateAdaptiveSelectText == 'function') {
            updateAdaptiveSelectText($wrap);
        }
        return;
    }

    if (ids.indexOf('-1') >= 0) {
        $all.prop('checked', true).trigger('change');
        return;
    }

    ids.forEach(function (sid) {
        $dropdown.find('input[name="punish-server-edit"][value="' + sid + '"]').prop('checked', true);
    });
    const $anyChecked = $dropdown.find('input[type="checkbox"]:checked').first();
    if ($anyChecked.length) {
        $anyChecked.trigger('change');
    }
}

function fillEditPunishmentIp(punish) {
    const ip = (punish.ip || '').trim();
    if (ip) {
        $('#enableIPEdit').prop('checked', true);
        $('#enableIPEdit').closest('.inputs-inline').next('.at__ip-toggle').show();
        $('#ipInputEdit').val(ip);
    } else {
        $('#enableIPEdit').prop('checked', false);
        $('#enableIPEdit').closest('.inputs-inline').next('.at__ip-toggle').hide();
        $('#ipInputEdit').val('');
    }
}

function openEditPunishmentModal(punishId) {
    const punish = findPunishmentById(punishId);
    if (!punish || !isPunishmentEditable(punish)) {
        return;
    }

    const $modal = $('#editPunish');
    $modal.html(renderEditPunishmentModal(punish));
    initAdaptiveSelects('#editPunish');
    fillEditPunishmentType(punish);
    fillEditPunishmentIp(punish);

    const gameType = $('#punishFilterGame input[type="radio"]:checked').val();

    mountPunishmentReasonSelect('#punishReasonEditList', punish.punish_type, {
        renderer: adaptiveRenderers,
        renderKey: 'punishReasonList',
        name: 'punish-reason-edit',
        nameId: 'editPunishReason',
        openSelect: 'editPunishReasonList',
        afterMount: function ($wrap) {
            fillEditPunishmentReason(punish, $wrap);
        }
    });

    mountLocalSelect('terms.punishments', {
        containerSelector: '#punishTimeEditList',
        renderer: adaptiveRenderers,
        renderKey: 'termsList',
        name: 'punish-time-edit',
        nameId: 'editPunishTerms',
        openSelect: 'editPunishTermsList',
        afterMount: function ($wrap) {
            fillEditPunishmentTime(punish, $wrap);
        }
    });

    mountLocalSelect('servers.' + gameType, {
        containerSelector: '#punishServerEditList',
        renderer: adaptiveRenderers,
        renderKey: 'serverList',
        name: 'punish-server-edit',
        nameId: 'editPunishServers',
        openSelect: 'editPunishServersList',
        afterMount: function ($wrap) {
            fillEditPunishmentServers(punish, $wrap);
        }
    });

    $modal.addClass('visible');
}

function resetCreatePunishmentForm() {
    $('#steamIdInput, #ipInput, #punishReasonCustom, #punishCustomTime').val('');
    $('#punishType input[name="punish-type"]').prop('checked', false);
    $('#punishTime input[name="punish-time"]').prop('checked', false);
    $('#punishServer input[name="punish-server"]').prop('checked', false);
    resetDefaultAllServersCheckbox('#punishServerAll');

    removeCreatePunishmentReasonSelect();

    $('#punishType, #punishTime, #punishServer').each(function () {
        if (typeof updateAdaptiveSelectText == 'function') {
            updateAdaptiveSelectText($(this).closest('.adaptive-select-wrapper'));
        }
    });
}

function collectSelectedPunishIds() {
    return collectSelectedRowIds('#punishTable');
}

function findPunishmentById(punishId) {
    if (punishId == null || !Array.isArray(punishmentsData)) {
        return null;
    }
    return punishmentsData.find(function (p) {
        return p.id == punishId;
    }) || null;
}

function buildPunishBulkActionsHtml() {
    const caps = getPunishListCapabilities(myData);
    const parts = [];

    if (caps.canRemove) {
        parts.push(`<button class="filter" data-bulk-action="remove">${get_translate_module_phrase('module_page_atools', '_at_removePunishments')}</button>`);
    }
    if (caps.canDelete) {
        parts.push(`<button class="filter" data-bulk-action="delete">${get_translate_phrase('_Delete_Action')}</button>`);
    }

    return parts.join('');
}

function togglePunishBulkActions() {
    const caps = getPunishListCapabilities(myData);
    toggleBulkActionsPanel({
        enabled: caps.canBulkSelect,
        tableSelector: '#punishTable',
        buildHtml: buildPunishBulkActionsHtml
    });
}

registerActionMenu('punishments', {
    items: [
        {
            action: 'edit',
            label: get_translate_phrase('_Change'),
            icon: 'edit-pen',
            visible: function ($trigger) {
                const caps = getPunishListCapabilities(myData);
                if (!caps.canEdit) {
                    return false;
                }
                const punish = findPunishmentById($trigger.data('punish-id'));
                return !!(punish && !isPunishmentActionBlocked(punish, myData) && isPunishmentEditable(punish));
            }
        },
        {
            action: 'remove',
            label: get_translate_module_phrase('module_page_atools', '_at_removePunish'),
            icon: 'unlock',
            className: 'button-green',
            visible: function ($trigger) {
                const caps = getPunishListCapabilities(myData);
                if (!caps.canRemove) {
                    return false;
                }
                const punish = findPunishmentById($trigger.data('punish-id'));
                return !!(punish && !isPunishmentActionBlocked(punish, myData) && isPunishmentEditable(punish));
            }
        },
        {
            action: 'delete',
            label: get_translate_phrase('_Delete_Action'),
            icon: 'trash',
            className: 'button-delete',
            visible: function ($trigger) {
                const caps = getPunishListCapabilities(myData);
                if (!caps.canDelete) {
                    return false;
                }
                const punish = findPunishmentById($trigger.data('punish-id'));
                return !!(punish && !isPunishmentActionBlocked(punish, myData));
            }
        }
    ],
    rowSelector: 'tr',
    onAction: function (action, $trigger, _row) {
        const punishId = $trigger.data('punish-id');
        const type = $('#punishFilterGame input[type="radio"]:checked').val();
        const punishType = $('#punishFilterType input[type="radio"]:checked').val();

        if (!punishId) {
            return;
        }

        const caps = getPunishListCapabilities(myData);

        switch (action) {
            case 'edit':
                if (!caps.canEdit) {
                    return;
                }
                openEditPunishmentModal(punishId);
                break;
            case 'remove': {
                if (!caps.canRemove) {
                    return;
                }
                const punishToRemove = findPunishmentById(punishId);
                if (!punishToRemove || !isPunishmentEditable(punishToRemove)) {
                    return;
                }
                openDialog({
                    title: get_translate_module_phrase('module_page_atools', '_at_confirmAction'),
                    message: get_translate_module_phrase('module_page_atools', '_at_confirmRemovePunish'),
                    confirmText: get_translate_module_phrase('module_page_atools', '_at_remove'),
                    cancelText: get_translate_module_phrase('module_page_atools', '_at_no'),
                    onConfirm: function () {
                        sendRequest({ remove_punishments: true, type: type, punish_type: punishType, punish_ids: [punishId] })
                            .done(function (result) {
                                noty(result.message, result.status);
                                if (result.status == 'success') {
                                    loadPunishmentsList(currentPunishmentsPage);
                                }
                            });
                    }
                });
                break;
            }
            case 'delete': {
                if (!caps.canDelete) {
                    return;
                }
                openDialog({
                    title: get_translate_module_phrase('module_page_atools', '_at_confirmAction'),
                    message: get_translate_module_phrase('module_page_atools', '_at_confirmDeletePunish'),
                    confirmText: get_translate_phrase('_Delete_Action'),
                    cancelText: get_translate_module_phrase('module_page_atools', '_at_no'),
                    onConfirm: function () {
                        sendRequest({ delete_punishments: true, type: type, punish_type: punishType, punish_ids: [punishId] })
                            .done(function (result) {
                                noty(result.message, result.status);
                                if (result.status == 'success') {
                                    loadPunishmentsList(currentPunishmentsPage);
                                }
                            });
                    }
                });
                break;
            }
        };
    }
});

$(document).ready(function () {
    if (!$('#punishFilterGame').length) {
        return;
    }

    punishCalendarApi = initAtRangeCalendar({
        calendarSelector: '#punishCalendar',
        startInput: '#punishStartDate',
        endInput: '#punishEndDate',
        periodSelector: '#punishCalendarPeriod',
        triggerSelector: '#punishCalendarTrigger',
        filterSelector: '#punishCalendarFilter',
        emptyLabel: get_translate_module_phrase('module_page_atools', '_at_selectDateRange'),
        onChange: function () {
            if (punishFiltersResetting) {
                return;
            }
            loadPunishmentsList();
        }
    });

    loadAtCatalog().done(function () {
        loadPunishmentReasons();
        initOnlinePlayerPunishPicker((window.atCatalog && window.atCatalog.onlinePlayers) ? window.atCatalog.onlinePlayers : []);
        $('#punishFilterGame input[type="radio"]:checked').trigger('change');
    });

    $('#enableIP').on('change', function () {
        const $toggle = $(this).closest('.inputs-inline').next('.at__ip-toggle');
        if ($(this).is(':checked')) {
            $toggle.slideDown(200);
        } else {
            $toggle.slideUp(200);
            $('#ipInput').val('');
        }
    });

    $(document).on('change', '#enableIPEdit', function () {
        const $toggle = $(this).closest('.inputs-inline').next('.at__ip-toggle');
        if ($(this).is(':checked')) {
            $toggle.slideDown(200);
        } else {
            $toggle.slideUp(200);
            $('#ipInputEdit').val('');
        }
    });

    $(document).on('click', '#confirmEditPunish', function () {
        sendRequest(buildUpdatePunishmentPayload()).done(function (result) {
            noty(result.message, result.status);
            if (result.status == 'success') {
                $('#editPunish').removeClass('visible');
                loadPunishmentsList(currentPunishmentsPage);
            }
        });
    });

    $('#givePunish').on('click', function () {
        var $btn = $(this);
        sendRequestWithButton($btn, buildCreatePunishmentPayload()).done(function (result) {
            noty(result.message, result.status);
            if (result.status == 'success') {
                resetCreatePunishmentForm();
                loadPunishmentsList();
            }
        });
    });

    $(document).on('change', '#punishFilterGame input[type="radio"]', function () {
        loadAdaptivePair($(this).val());
    });

    $(document).on('change', '#punishFilterType input[type="radio"]', function () {
        if (punishFiltersResetting) {
            return;
        }
        loadPunishmentsList();
    });

    $(document).on('change', '#punishType input[name="punish-type"]', function () {
        toggleCreatePunishmentReasonSelect();
    });

    $(document).on('change', '#adminListFilter input[type="radio"], #serversListFilter input[type="checkbox"], #expiresPunishes input[type="radio"]', function () {
        if (punishFiltersResetting) {
            return;
        }
        loadPunishmentsList();
    });

    bindAtListRefresh(function () {
        loadPunishmentsList();
    });

    $(document).on('click', '#resetPunishFilters', function () {
        resetPunishFilters();
    });

    bindAtPagination(loadPunishmentsList);

    $(document).on('click', '.at__checked-action [data-bulk-action]', function () {
        const action = $(this).data('bulk-action');
        const ids = collectSelectedPunishIds();
        if (!ids.length) {
            return;
        }
        const type = $('#punishFilterGame input[type="radio"]:checked').val();
        const punishType = $('#punishFilterType input[type="radio"]:checked').val();
        const caps = getPunishListCapabilities(myData);

        switch (action) {
            case 'remove': {
                if (!caps.canRemove) {
                    return;
                }
                openDialog({
                    title: get_translate_module_phrase('module_page_atools', '_at_confirmAction'),
                    message: get_translate_module_phrase('module_page_atools', '_at_confirmRemovePunishmentsBulk'),
                    confirmText: get_translate_module_phrase('module_page_atools', '_at_remove'),
                    cancelText: get_translate_module_phrase('module_page_atools', '_at_no'),
                    onConfirm: function () {
                        sendRequest({ remove_punishments: true, type: type, punish_type: punishType, punish_ids: ids })
                            .done(function (result) {
                                noty(result.message, result.status);
                                if (result.status == 'success') {
                                    loadPunishmentsList(currentPunishmentsPage);
                                }
                            });
                    }
                });
                break;
            }
            case 'delete': {
                if (!caps.canDelete) {
                    return;
                }
                openDialog({
                    title: get_translate_module_phrase('module_page_atools', '_at_confirmAction'),
                    message: get_translate_module_phrase('module_page_atools', '_at_confirmDeletePunishmentsBulk'),
                    confirmText: get_translate_phrase('_Delete_Action'),
                    cancelText: get_translate_module_phrase('module_page_atools', '_at_no'),
                    onConfirm: function () {
                        sendRequest({ delete_punishments: true, type: type, punish_type: punishType, punish_ids: ids })
                            .done(function (result) {
                                noty(result.message, result.status);
                                if (result.status == 'success') {
                                    loadPunishmentsList(currentPunishmentsPage);
                                }
                            });
                    }
                });
                break;
            }
        };
    });

    initBulkSelection({
        tableSelector: '#punishTable',
        ignoreClickSelector: 'button, a, input, .at__action',
        isEnabled: function () {
            return getPunishListCapabilities(myData).canBulkSelect;
        },
        onToggle: togglePunishBulkActions
    });
});