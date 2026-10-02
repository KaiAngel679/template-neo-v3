let adminsData = null;
let myData = null;
let currentAdminInfoId = null;
let currentAdminGameType = null;
let currentWarnTargetSteamid = null;
let lastWarnsById = {};
let currentAdminsListPage = 1;

const adaptiveRenderers = {
    groupList: renderAdminsGroupList,
    accessGroupList: renderChangeAdminAccessGroupsList,
    termsList: renderTermsList,
    serverList: renderServerList
};

function loadAdaptivePair(type, filter) {
    const groupSel = filter ? '#groupListFilter' : '#groupList';
    const serverSel = filter ? '#serverListFilter' : '#serverList';

    mountLocalSelect('groups.' + type, {
        containerSelector: groupSel,
        renderer: adaptiveRenderers,
        renderKey: 'groupList',
        name: filter ? 'admin-groups-list-filter' : 'admin-groups-list',
        nameId: filter ? 'adminGroupsFilter' : 'adminGroups',
        openSelect: filter ? 'adminGroupsListFilter' : 'adminGroupsList',
        afterMount: filter
            ? function ($wrap) {
                const name = 'admin-groups-list-filter';
                $wrap.find('input[name="' + name + '"][value="-1"]').prop('checked', true);
                $wrap.find('input[name="' + name + '"]').not('[value="-1"]').prop('checked', false);
                if (typeof updateAdaptiveSelectText == 'function') {
                    updateAdaptiveSelectText($wrap);
                }
            }
            : undefined,
        renderOpts: filter ? { filterWithAllOption: true } : undefined
    });

    mountLocalSelect('servers.' + type, {
        containerSelector: serverSel,
        renderer: adaptiveRenderers,
        renderKey: 'serverList',
        name: filter ? 'admin-servers-list-filter' : 'admin-servers-list',
        nameId: filter ? 'adminServersFilter' : 'adminServers',
        openSelect: filter ? 'adminServersListFilter' : 'adminServersList',
        renderOpts: filter ? { defaultAllServers: true } : (atDefaultAllServersEnabled() ? { defaultAllServers: true } : undefined)
    });
}

function getFilterServersOrDefault() {
    return getCheckedServerFilterIds('#adminServersListFilter input[type="checkbox"]:checked');
}

function getFilterAccessPermissions() {
    const permissions = [];
    $('#adminAccessListFilter input[name="admin-access-list-filter"]:checked').each(function () {
        permissions.push($(this).val());
    });
    return permissions;
}

function buildAdminsListRequest(page) {
    return {
        get_admins_list: true,
        type: $('#adminFilterGame input[type="radio"]:checked').val(),
        servers: getFilterServersOrDefault(),
        group: parseInt($('#adminGroupsListFilter input[type="radio"]:checked').val() || -1, 10),
        access_permissions: getFilterAccessPermissions(),
        limit: getListLimit(),
        offset: (page - 1) * getListLimit(),
        search: getSearchQuery()
    };
}

function buildUpdateAdminPayload() {
    const adminId = parseInt($('#confirmChangeAdmin').data('admin-id'), 10);
    const type = $('#adminFilterGame input[type="radio"]:checked').val();
    const group = $('#changeGroupList input[name="change-admin-groups-list"]:checked').val();
    const expire = $('#changeExpireList input[name="change-admin-terms-list"]:checked').val() || $('#changeAdminTermsCustom').val();
    const selectedServers = [];
    $('#changeServerList input[name="change-admin-servers-list"]:checked').each(function () {
        selectedServers.push($(this).val());
    });
    const selectedGroupAccess = $('#changeAccessGroupList input[name="change-access-group-list"]:checked').val();
    const selectedFlags = [];
    $('#changeAdmin input[name="change-flag-access-list"]:checked').each(function () {
        selectedFlags.push($(this).val());
    });
    let permissions;

    if (selectedGroupAccess === '-1') {
        permissions = { flags: [] };
    } else if (selectedGroupAccess) {
        permissions = { group: selectedGroupAccess };
    } else {
        permissions = { flags: selectedFlags };
    }

    return {
        update_admin: true,
        admin_id: adminId,
        type: type,
        group: group,
        expire: expire,
        servers: selectedServers,
        permissions: permissions
    };
}

function bindChangeAdminAccessControls($modal) {
    $modal.off('change.atAccessGroup').on('change.atAccessGroup', 'input[name="change-access-group-list"]', function () {
        if (!$(this).is(':checked')) {
            return;
        }
        $modal.find('input[name="change-flag-access-list"]').prop('checked', false);
        if (typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($('#changeFlagsAccessWrap'));
        }
    });

    $modal.off('change.atAccessFlags').on('change.atAccessFlags', 'input[name="change-flag-access-list"]', function () {
        if (!$(this).is(':checked')) {
            return;
        }
        const $groupRadio = $modal.find('input[name="change-access-group-list"]:checked');
        if (!$groupRadio.length) {
            return;
        }
        $groupRadio.prop('checked', false);
        if (typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($('#changeAccessGroupList'));
        }
    });
}

function fillEditAdminAccessGroup(admin, $wrap) {
    const groupId = admin.panel_access_group_id;
    const permissions = Array.isArray(admin.permissions) ? admin.permissions : [];

    if (groupId != null && groupId !== '') {
        const $radio = $wrap.find('input[name="change-access-group-list"][value="' + String(groupId) + '"]');
        if ($radio.length) {
            $radio.prop('checked', true);
            if (typeof updateAdaptiveSelectText === 'function') {
                updateAdaptiveSelectText($wrap);
            }
        }
        return;
    }

    if (permissions.length === 0) {
        const $none = $wrap.find('input[name="change-access-group-list"][value="-1"]');
        if ($none.length) {
            $none.prop('checked', true);
            if (typeof updateAdaptiveSelectText === 'function') {
                updateAdaptiveSelectText($wrap);
            }
        }
    }
}

function fillEditAdminFlags(admin, $wrap) {
    const permissions = Array.isArray(admin.permissions) ? admin.permissions : [];
    $wrap.find('input[name="change-flag-access-list"]').each(function () {
        $(this).prop('checked', permissions.includes($(this).val()));
    });
    if (typeof updateAdaptiveSelectText === 'function') {
        updateAdaptiveSelectText($wrap);
    }
}

function buildCreateAdminPayload() {
    const selectedGroupType = $('#groupTypesList input[type="radio"]:checked').val();
    const selectedGroup = $('#adminGroupList input[type="radio"]:checked, #groupList input[type="radio"]:checked').val();
    const expireTime = $('#adminExpireList input[type="radio"]:checked').val() || $('#adminExpireCustom').val();
    const selectedServers = [];
    $('#adminServersList input[type="checkbox"]:checked').each(function () {
        selectedServers.push($(this).val());
    });
    const selectedGroupAccess = $('#adminGroupAccessList input[type="radio"]:checked').val();
    const selectedFlags = [];
    $('#adminFlagsAccessList input[type="checkbox"]:checked').each(function () {
        selectedFlags.push($(this).val());
    });
    const permissions = selectedGroupAccess ? { group: selectedGroupAccess } : { flags: selectedFlags };
    const payload = {
        create_admin: true,
        steamid: $('#steamIdInput').val(),
        group_type: selectedGroupType,
        group: selectedGroup,
        expire: expireTime,
        servers: selectedServers,
        permissions: permissions
    };
    const vipGroup = $('#adminVipGroupList input[name="admin-vip-group-list"]:checked').val()
        || $('#adminVipGroupCustom').val()
        || '';
    if (vipGroup) {
        payload.vip_group = vipGroup;
    }
    return payload;
}

function resetCreateAdminForm() {
    $('#addingAdmin').find('input').each(function () {
        const $input = $(this);
        if ($input.is(':radio') || $input.is(':checkbox')) {
            $input.prop('checked', false);
        } else {
            $input.val('');
        }
    });
    $('#groupList').empty();
    $('#serverList').empty();
    initAdaptiveSelects();
}

function openChangeAdminModal(adminId) {
    const list = adminsData;
    if (!Array.isArray(list)) return;
    const admin = list.find(function (a) {
        return a.id == adminId;
    });
    if (!admin) return;

    const $modal = $('#changeAdmin');
    $modal.html(renderChangeAdminModal(admin));
    initAdaptiveSelects('#changeAdmin');

    const type = $('#adminFilterGame input[type="radio"]:checked').val();

    mountLocalSelect('groups.' + type, {
        containerSelector: '#changeGroupList',
        renderer: adaptiveRenderers,
        renderKey: 'groupList',
        name: 'change-admin-groups-list',
        nameId: 'changeAdminGroups',
        openSelect: 'changeAdminGroupsList',
        afterMount: function ($wrap) {
            const gid = admin.group_id;
            if (gid == null || gid == '') {
                return;
            }
            const $radio = $wrap.find('input[name="change-admin-groups-list"][value="' + String(gid) + '"]');
            if (!$radio.length) {
                return;
            }
            $radio.prop('checked', true).trigger('change');
        }
    });

    mountLocalSelect('servers.' + type, {
        containerSelector: '#changeServerList',
        renderer: adaptiveRenderers,
        renderKey: 'serverList',
        name: 'change-admin-servers-list',
        nameId: 'changeAdminServers',
        openSelect: 'changeAdminServersList',
        afterMount: function ($wrap) {
            const servers = Array.isArray(admin.servers) ? admin.servers : [];
            const ids = servers.map(function (s) {
                return s.id;
            });
            if (!ids.length) {
                return;
            }
            const $dropdown = $wrap.find('.adaptive-select__dropdown-list');
            const $all = $wrap.find('#changeAdminServersAll');
            if (ids.length == 1 && ids[0] == -1) {
                $all.prop('checked', true).trigger('change');
                return;
            }
            $all.prop('checked', false);
            ids.forEach(function (sid) {
                if (sid == -1) {
                    $all.prop('checked', true);
                    return;
                }
                $dropdown.find('input[name="change-admin-servers-list"][value="' + String(sid) + '"]').prop('checked', true);
            });
            const $anyChecked = $dropdown.find('input[type="checkbox"]:checked').first();
            if ($anyChecked.length) {
                $anyChecked.trigger('change');
            }
        }
    });

    mountLocalSelect('terms.admins', {
        containerSelector: '#changeExpireList',
        renderer: adaptiveRenderers,
        renderKey: 'termsList',
        name: 'change-admin-terms-list',
        nameId: 'changeAdminTerms',
        openSelect: 'changeAdminTermsList',
        afterMount: function ($wrap) {
            const servers = Array.isArray(admin.servers) ? admin.servers : [];
            const s0 = servers[0];
            const expUnix = admin.expires != null && admin.expires !== ''
                ? Number(admin.expires)
                : (s0 && s0.expires != null ? Number(s0.expires) : 0);
            const name = 'change-admin-terms-list';

            if (expUnix == 0) {
                $wrap.find('#changeAdminTermsCustom').val('');
                const $forever = $wrap.find('input[name="' + name + '"][value="0"]');
                if ($forever.length) {
                    $forever.prop('checked', true).trigger('change');
                } else {
                    $wrap.find('#changeAdminTermsCustom').val('0');
                }
                return;
            }

            const left = Math.max(0, Math.floor(expUnix - Date.now() / 1000));
            $wrap.find('input[name="' + name + '"]').prop('checked', false);
            $wrap.find('#changeAdminTermsCustom').val(String(left));
            if (typeof updateAdaptiveSelectText == 'function') {
                updateAdaptiveSelectText($wrap);
            }
        }
    });

    const accessGroups = getAtCatalogPath('accessGroups');
    mountAdaptiveSelectItem({
        containerSelector: '#changeAccessGroupList',
        renderer: adaptiveRenderers,
        renderKey: 'accessGroupList',
        name: 'change-access-group-list',
        nameId: 'changeAdminGroupAccess',
        openSelect: 'changeAdminGroupAccessList',
        afterMount: function ($wrap) {
            fillEditAdminAccessGroup(admin, $wrap);
        }
    }, accessGroups);

    fillEditAdminFlags(admin, $('#changeFlagsAccessWrap'));
    bindChangeAdminAccessControls($modal);

    $modal.addClass('visible');
}

function loadGameData(type) {
    loadAdaptivePair(type, false);
}

function loadGameDataFilter(type) {
    loadAdaptivePair(type, true);
    loadAdminsList();
}

function applyAdminsListResult(result, page) {
    if (!result || result.status != 'success') {
        return;
    }
    adminsData = result.data;
    myData = result.my_data;
    $('#adminsTable').html(renderAdminsList(result.data));
    const totalPages = Math.ceil(result.total / getListLimit());
    $('#adminsPagination').html(renderPagination(page, totalPages));
    finishTableRender(result.data, [{ flagField: 'checked_avatar', steamField: 'steamid' }]);
}

function loadAdminsList(page, onDone) {
    page = page || 1;
    currentAdminsListPage = page;
    return sendRequest(buildAdminsListRequest(page))
        .done(function (result) {
            applyAdminsListResult(result, page);
            if (typeof onDone === 'function') {
                onDone();
            }
        });
}

function showAdminInfo(adminId) {
    if (!adminsData) return;
    if (typeof isGlobalSearchOpen === 'function' && isGlobalSearchOpen()) {
        return;
    }
    const admin = adminsData.find(function (a) {
        return a.id == adminId;
    });
    if (!admin) return;
    currentAdminInfoId = adminId;
    currentAdminGameType = $('#adminFilterGame input[type="radio"]:checked').val() || null;
    currentWarnTargetSteamid = admin.steamid;
    const warns = Array.isArray(admin.warnings) ? admin.warnings : [];
    lastWarnsById = {};
    warns.forEach(function (w) {
        lastWarnsById[w.id] = w;
    });
    $('.admin-info').html(renderAdminInfo(admin, myData, warns));
    $('.admin-info').addClass('show');
    initTippy();
}

function openAddWarnModal(steamid) {
    const admin = adminsData.find(function (a) {
        return String(a.steamid) == String(steamid);
    });
    if (!admin) return;
    const $modal = $('#addWarnToAdmin');
    $modal.html(renderCreateWarnModal());
    mountLocalSelect('terms.punishments', {
        containerSelector: '#addWarnExpireList',
        renderer: adaptiveRenderers,
        renderKey: 'termsList',
        name: 'warn-admin-terms-list',
        nameId: 'addWarnTerms',
        openSelect: 'addWarnTermsList'
    });
    initAdaptiveSelects('#addWarnToAdmin');
    $modal.addClass('visible');
}

function openEditWarnModal(warnId) {
    const warn = lastWarnsById[warnId];
    if (!warn) return;
    const $modal = $('#changeWarnToAdmin');
    $modal.html(renderEditWarnModal(warn));
    mountLocalSelect('terms.punishments', {
        containerSelector: '#editWarnExpireList',
        renderer: adaptiveRenderers,
        renderKey: 'termsList',
        name: 'edit-warn-admin-terms-list',
        nameId: 'editWarnTerms',
        openSelect: 'editWarnTermsList',
        afterMount: function ($wrap) {
            const $match = $wrap.find('input[name="edit-warn-admin-terms-list"][value="' + String(warn.expires_at) + '"]');
            if ($match.length) {
                $match.prop('checked', true);
            } else if (warn.expires_at == 0) {
                const $z = $wrap.find('input[name="edit-warn-admin-terms-list"][value="0"]');
                if ($z.length) {
                    $z.prop('checked', true);
                }
            } else {
                const now = Math.floor(Date.now() / 1000);
                if (warn.expires_at > 0) {
                    const left = warn.expires_at - now;
                    if (left > 0) {
                        $wrap.find('#editWarnTermsCustom').val(String(left));
                    }
                }
            }
            if (typeof updateAdaptiveSelectText == 'function') {
                updateAdaptiveSelectText($wrap);
            }
        }
    });
    initAdaptiveSelects('#changeWarnToAdmin');
    $modal.addClass('visible');
}

function collectSelectedWarnIds() {
    const ids = [];
    $('.admin-info .at-warn-select:checked').each(function () {
        ids.push($(this).val());
    });
    return ids;
}

function refreshAdminInfoPanel() {
    if (currentAdminInfoId == null || !$('.admin-info').hasClass('show')) {
        return;
    }
    const keepId = currentAdminInfoId;
    loadAdminsList(currentAdminsListPage, function () {
        showAdminInfo(keepId);
    });
}

registerActionMenu('admins', {
    items: [
        {
            action: 'info',
            label: get_translate_phrase('_infoIziToast'),
            icon: 'info-circle'
        },
        {
            action: 'edit',
            label: get_translate_phrase('_Change'),
            icon: 'edit-pen',
            visible: function ($trigger) {
                const my = myData;
                if (!my || !Array.isArray(my.permissions) || !my.permissions.includes('admins.update')) {
                    return false;
                }
                const adminId = $trigger.data('admin-id');
                const list = adminsData;
                if (adminId == null || !Array.isArray(list)) {
                    return false;
                }
                const admin = list.find(function (a) {
                    return a.id == adminId;
                });
                return !!(admin && !isRestrictedAdminTarget(admin, my));
            }
        },
        {
            action: 'delete',
            label: get_translate_phrase('_Delete_Action'),
            icon: 'trash',
            className: 'button-delete',
            visible: function ($trigger) {
                const my = myData;
                if (!my || !Array.isArray(my.permissions) || !my.permissions.includes('admins.delete')) {
                    return false;
                }
                const adminId = $trigger.data('admin-id');
                const list = adminsData;
                if (adminId == null || !Array.isArray(list)) {
                    return false;
                }
                const admin = list.find(function (a) {
                    return a.id == adminId;
                });
                return !!(admin && !isRestrictedAdminTarget(admin, my));
            }
        }
    ],
    rowSelector: 'tr',
    onAction: function (action, $trigger, _row) {
        const adminId = $trigger.data('admin-id');
        switch (action) {
            case 'info':
                showAdminInfo(adminId);
                break;
            case 'edit':
                openChangeAdminModal(adminId);
                break;
            case 'delete': {
                const gameType = $('#adminFilterGame input[type="radio"]:checked').val();
                confirmAndRequest({
                    message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteAdmin'),
                    confirmText: get_translate_phrase('_Delete_Action'),
                    body: { delete_admin: true, admin_id: adminId, type: gameType },
                    onSuccess: function () {
                        loadAdminsList();
                    }
                });
                break;
            }
        }
    }
});

$(document).ready(function () {
    if (!$('#adminFilterGame').length) {
        return;
    }

    loadAtCatalog().done(function () {
        $('#adminFilterGame input[type="radio"]:checked').trigger('change');
    });

    $(document).on('change', '#groupTypesList input[type="radio"]', function () {
        loadGameData($(this).val());
    });

    $(document).on('change', '#adminFilterGame input[type="radio"]', function () {
        loadGameDataFilter($(this).val());
    });

    $(document).on('change', '#adminGroupsListFilter input[type="radio"], #adminServersListFilter input[type="checkbox"], #adminAccessListFilter input[type="checkbox"]', function () {
        if ($(this).closest('#adminAccessListFilter').length && typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($(this).closest('.adaptive-select-wrapper'));
        }
        loadAdminsList();
    });

    bindAtListRefresh(function () {
        loadAdminsList();
    }, { selector: '#adminsTable' });

    $(document).on('click', '#hideAdminInfo', function () {
        $('.admin-info').removeClass('show');
    });

    $('#createAdmin').on('click', function () {
        var $btn = $(this);
        sendRequestWithButton($btn, buildCreateAdminPayload())
            .done(function (result) {
                noty(result.message, result.status);
                if (result.status == 'success') {
                    resetCreateAdminForm();
                    loadAdminsList();
                }
            });
    });

    $(document).on('keydown', function (e) {
        if (e.key == 'Escape' && $('.at__driver').hasClass('show')) {
            $('.admin-info').removeClass('show');
        }
    });

    $(document).on('click', '#resetAdminFilters', function () {
        resetFilterPanel('adminFilters', function () {
            $('#adminFilterGame input[type="radio"]:first').prop('checked', true).trigger('change');
        });
    });

    bindAtPagination(loadAdminsList);

    $(document).on('click', '#confirmChangeAdmin', function () {
        sendRequest(buildUpdateAdminPayload())
            .done(function (result) {
                noty(result.message, result.status);
                if (result.status == 'success') {
                    $('#changeAdmin .popup_modal_close').trigger('click');
                    loadAdminsList();
                }
            });
    });

    $(document).on('click', '.btn-at-open-add-warn', function () {
        openAddWarnModal($(this).data('steamid'));
    });

    $(document).on('click', '#atConfirmCreateWarn', function () {
        const expire = $('#addWarnExpireList input[name="warn-admin-terms-list"]:checked').val() || $('#addWarnTermsCustom').val();
        sendRequest({
            give_warn: true,
            target_steamid: currentWarnTargetSteamid,
            admin_id: currentAdminInfoId,
            type: currentAdminGameType || $('#adminFilterGame input[type="radio"]:checked').val(),
            reason: $('#addReasonWarn').val(),
            expire: expire
        }).done(function (result) {
            noty(result.message, result.status);
            if (result.status == 'success') {
                $('#addWarnToAdmin .popup_modal_close').trigger('click');
                refreshAdminInfoPanel();
            }
        });
    });

    $(document).on('click', '#atConfirmEditWarn', function () {
        const warnId = $(this).data('warn-id');
        const expire = $('#editWarnExpireList input[name="edit-warn-admin-terms-list"]:checked').val() || $('#editWarnTermsCustom').val();
        sendRequest({
            update_warn: true,
            target_steamid: currentWarnTargetSteamid,
            warn_id: warnId,
            reason: $('#editReasonWarn').val(),
            expire: expire
        }).done(function (result) {
            noty(result.message, result.status);
            if (result.status == 'success') {
                $('#changeWarnToAdmin .popup_modal_close').trigger('click');
                refreshAdminInfoPanel();
            }
        });
    });

    $(document).on('click', '.at-btn-warn-edit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openEditWarnModal($(this).data('warn-id'));
    });

    $(document).on('click', '.at-btn-warn-remove', function () {
        const ids = collectSelectedWarnIds();
        openDialog({
            title: get_translate_module_phrase('module_page_atools', '_at_confirmAction'),
            message: get_translate_module_phrase('module_page_atools', '_at_confirmRemoveWarn'),
            confirmText: get_translate_module_phrase('module_page_atools', '_at_remove'),
            cancelText: get_translate_module_phrase('module_page_atools', '_at_no'),
            onConfirm: function () {
                sendRequest({
                    remove_warns: true,
                    target_steamid: currentWarnTargetSteamid,
                    warn_ids: ids
                }).done(function (result) {
                    noty(result.message, result.status);
                    if (result.status == 'success') {
                        refreshAdminInfoPanel();
                    }
                });
            }
        });
    });

    $(document).on('click', '.at-btn-warn-delete', function () {
        const ids = collectSelectedWarnIds();
        openDialog({
            title: get_translate_module_phrase('module_page_atools', '_at_confirmAction'),
            message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteWarn'),
            confirmText: get_translate_phrase('_Delete_Action'),
            cancelText: get_translate_module_phrase('module_page_atools', '_at_no'),
            onConfirm: function () {
                sendRequest({
                    delete_warns: true,
                    target_steamid: currentWarnTargetSteamid,
                    warn_ids: ids
                }).done(function (result) {
                    noty(result.message, result.status);
                    if (result.status == 'success') {
                        refreshAdminInfoPanel();
                    }
                });
            }
        });
    });
});