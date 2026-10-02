const adaptiveRenderers = {
    vipGroupList: renderVipGroupList,
    termsList: renderTermsList,
    serverList: renderServerList
};

let currentVipsPage = 1;
let vipsData = null;
let myData = null;

function getFilterVipServers() {
    const servers = [];
    $('#vipServerListFilterInner input[name="filter-vip-server"]:checked').each(function () {
        servers.push(parseInt($(this).val(), 10));
    });
    if (!servers.length) {
        return [-1];
    }
    return servers;
}

function loadVipsList(page) {
    page = page || 1;
    currentVipsPage = page;

    return sendRequest({
        get_privileges_list: true,
        servers: getFilterVipServers(),
        group: $('#vipGroupListFilterInner input[name="filter-vip-group"]:checked').val() || '-1',
        expire_filter: $('#vipExpireFilterList input[name="filter-vip-expire"]:checked').val() || 'all',
        limit: getListLimit(),
        offset: (page - 1) * getListLimit(),
        search: getSearchQuery()
    }).done(function (result) {
        if (result.status !== 'success') {
            return;
        }

        vipsData = result.data;
        myData = result.my_data;
        window.atVipMyData = myData;

        $('#vipsTable').html(renderPrivilegesList(result.data));
        updateVipTableBulkUi();
        $('#vipsPagination').html(renderPagination(page, Math.ceil(result.total / getListLimit())));

        finishTableRender(result.data, [{ flagField: 'checked_avatar', steamField: 'steamid' }]);
    });
}

function findVipById(privilegeId) {
    if (privilegeId == null || !Array.isArray(vipsData)) {
        return null;
    }

    return vipsData.find(function (vip) {
        return String(vip.id) === String(privilegeId);
    }) || null;
}

function fillEditVipGroup(vip, $wrap) {
    const group = vip.group || '';
    $wrap.find('input[name="vip-group-edit"]').prop('checked', false);
    $wrap.find('#editVipGroupsCustom').val('');

    const $match = $wrap.find('input[name="vip-group-edit"]').filter(function () {
        return $(this).val() == group;
    });
    if ($match.length) {
        $match.first().prop('checked', true).trigger('change');
    } else if (group) {
        $wrap.find('#editVipGroupsCustom').val(group);
    }

    if (typeof updateAdaptiveSelectText === 'function') {
        updateAdaptiveSelectText($wrap);
    }
}

function fillEditVipExpire(vip, $wrap) {
    const name = 'vip-expire-edit';
    const expUnix = vip.expires != null ? Number(vip.expires) : 0;

    $wrap.find('input[name="' + name + '"]').prop('checked', false);
    $wrap.find('#editVipTermsCustom').val('');

    if (expUnix === 0) {
        const $forever = $wrap.find('input[name="' + name + '"][value="0"]');
        if ($forever.length) {
            $forever.prop('checked', true).trigger('change');
        } else {
            $wrap.find('#editVipTermsCustom').val('0');
        }
    } else {
        const left = Math.max(0, Math.floor(expUnix - Date.now() / 1000));
        const $term = $wrap.find('input[name="' + name + '"][value="' + String(left) + '"]');
        if ($term.length) {
            $term.prop('checked', true).trigger('change');
        } else {
            $wrap.find('#editVipTermsCustom').val(String(left));
        }
    }

    if (typeof updateAdaptiveSelectText === 'function') {
        updateAdaptiveSelectText($wrap);
    }
}

function fillEditVipServers(vip, $wrap) {
    const ids = Array.isArray(vip.server_panel_ids) ? vip.server_panel_ids.map(String) : [];
    const $dropdown = $wrap.find('.adaptive-select__dropdown-list');
    const $all = $wrap.find('#editVipServersAll');

    $dropdown.find('input[name="vip-server-edit"]').prop('checked', false);
    $all.prop('checked', false);

    if (!ids.length) {
        if (typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($wrap);
        }
        return;
    }

    if (ids.indexOf('-1') >= 0) {
        $all.prop('checked', true).trigger('change');
        return;
    }

    ids.forEach(function (sid) {
        $dropdown.find('input[name="vip-server-edit"][value="' + sid + '"]').prop('checked', true);
    });
    applyServerListAllSync($wrap, 'vip-server-edit', '#editVipServersAll');
}

function openEditVipModal(privilegeId) {
    const vip = findVipById(privilegeId);
    if (!vip) {
        return;
    }

    loadAtCatalog().done(function () {
        const $modal = $('#changeVip');
        $modal.html(renderEditVipModal(vip));
        initAdaptiveSelects('#changeVip');

        mountLocalSelect('vipGroups', {
            containerSelector: '#vipGroupEditList',
            renderer: adaptiveRenderers,
            renderKey: 'vipGroupList',
            name: 'vip-group-edit',
            nameId: 'editVipGroups',
            openSelect: 'editVipGroupsList',
            afterMount: function ($wrap) {
                fillEditVipGroup(vip, $wrap);
            }
        });

        mountLocalSelect('terms.vips', {
            containerSelector: '#vipExpireEditList',
            renderer: adaptiveRenderers,
            renderKey: 'termsList',
            name: 'vip-expire-edit',
            nameId: 'editVipTerms',
            openSelect: 'editVipTermsList',
            afterMount: function ($wrap) {
                fillEditVipExpire(vip, $wrap);
            }
        });

        mountLocalSelect('servers', {
            containerSelector: '#vipServerEditList',
            renderer: adaptiveRenderers,
            renderKey: 'serverList',
            name: 'vip-server-edit',
            nameId: 'editVipServers',
            openSelect: 'editVipServersList',
            afterMount: function ($wrap) {
                fillEditVipServers(vip, $wrap);
            }
        });

        $modal.addClass('visible');
    });
}

function getEditVipServers() {
    const servers = [];
    $('#changeVip #vipServerEditList input[name="vip-server-edit"]:checked').each(function () {
        servers.push($(this).val());
    });
    if (servers.indexOf('-1') >= 0) {
        return ['-1'];
    }
    return servers;
}

function buildUpdatePrivilegePayload() {
    return {
        update_privilege: true,
        privilege_id: String($('#confirmEditVip').data('privilege-id') || ''),
        group: $('#changeVip input[name="vip-group-edit"]:checked').val() || $('#changeVip #editVipGroupsCustom').val() || '',
        expire: $('#changeVip input[name="vip-expire-edit"]:checked').val() || $('#changeVip #editVipTermsCustom').val(),
        servers: getEditVipServers()
    };
}

function buildVipBulkActionsHtml() {
    const caps = getVipListCapabilities(myData);
    if (!caps.canDelete) {
        return '';
    }

    return `<button class="filter" data-bulk-action="delete">${get_translate_module_phrase('module_page_atools', '_at_deleteVip')}</button>`;
}

function toggleVipBulkActions() {
    const caps = getVipListCapabilities(myData);
    toggleBulkActionsPanel({
        enabled: caps.canBulkSelect,
        tableSelector: '#vipsTable',
        buildHtml: buildVipBulkActionsHtml
    });
}

function collectSelectedPrivilegeIds() {
    const ids = [];
    $('#vipsTable .row-checkbox:checked').each(function () {
        const val = String($(this).val() || '').trim();
        if (val) {
            ids.push(val);
        }
    });
    return ids;
}

function confirmDeletePrivileges(ids) {
    if (!ids.length) {
        return;
    }

    confirmAndRequest({
        message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteVips'),
        confirmText: get_translate_phrase('_Delete_Action'),
        body: { delete_privileges: true, privilege_ids: ids },
        onSuccess: function () {
            loadVipsList(currentVipsPage);
        }
    });
}

function getSelectedVipServers() {
    const servers = [];
    $('#vipServerList input[name="vip-server"]:checked').each(function () {
        servers.push($(this).val());
    });
    if (servers.indexOf('-1') >= 0) {
        return ['-1'];
    }
    return servers;
}

function buildCreatePrivilegePayload() {
    const selectedServers = getSelectedVipServers();

    const expire = $('#vipExpireList input[name="vip-expire-list"]:checked').val()
        || $('#vipExpireCustom').val();

    return {
        create_privilege: true,
        steamid: $('#vipSteamIdInput').val(),
        group: $('#vipGroupList input[name="vip-group-list"]:checked').val() || $('#vipGroupCustom').val() || '',
        expire: expire,
        servers: selectedServers
    };
}

function resetCreatePrivilegeForm() {
    $('#addingVip').find('input').each(function () {
        const $input = $(this);
        if ($input.is(':radio') || $input.is(':checkbox')) {
            $input.prop('checked', false);
        } else {
            $input.val('');
        }
    });
    resetDefaultAllServersCheckbox('#vipServerAll');
    $('#addingVip .adaptive-select-wrapper').each(function () {
        if (typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($(this));
        }
    });
}

registerActionMenu('privileges', {
    items: [
        {
            action: 'edit',
            label: get_translate_phrase('_Change'),
            icon: 'edit-pen',
            visible: function () {
                return getVipListCapabilities(myData).canUpdate;
            }
        },
        {
            action: 'delete',
            label: get_translate_module_phrase('module_page_atools', '_at_deleteVip'),
            icon: 'trash',
            className: 'button-delete',
            visible: function () {
                return getVipListCapabilities(myData).canDelete;
            }
        }
    ],
    rowSelector: 'tr',
    onAction: function (action, $trigger) {
        const privilegeId = $trigger.data('privilege-id');
        if (!privilegeId) {
            return;
        }

        const caps = getVipListCapabilities(myData);

        switch (action) {
            case 'edit':
                if (!caps.canUpdate) {
                    return;
                }
                openEditVipModal(privilegeId);
                break;
            case 'delete':
                if (!caps.canDelete) {
                    return;
                }
                confirmDeletePrivileges([String(privilegeId)]);
                break;
        }
    }
});

$(document).ready(function () {
    if ($('#vipsTable').length) {
        if (typeof initAdaptiveSelects === 'function') {
            initAdaptiveSelects('#vipFilters');
        }
        loadVipsList();

        $(document).on('change', '#vipServerListFilterInner input[name="filter-vip-server"], #vipGroupListFilterInner input[name="filter-vip-group"], #vipExpireFilterList input[name="filter-vip-expire"]', function () {
            loadVipsList();
        });

        bindAtListRefresh(function () {
            loadVipsList();
        }, { selector: '#vipsTable' });

        $(document).on('click', '#resetVipFilters', function () {
            resetFilterPanel('vipFilters', function () {
                $('#vipGroupFilterAll').prop('checked', true);
                $('#vipExpireFilterAll').prop('checked', true);
                updateAdaptiveSelectTextsIn($('#vipFilters'));
            });
            loadVipsList();
        });

        bindAtPagination(loadVipsList);

        $(document).on('click', '.at__checked-action [data-bulk-action="delete"]', function () {
            if (!getVipListCapabilities(myData).canDelete) {
                return;
            }
            confirmDeletePrivileges(collectSelectedPrivilegeIds());
        });

        $(document).on('click', '#confirmEditVip', function () {
            sendRequest(buildUpdatePrivilegePayload())
                .done(function (result) {
                    noty(result.message, result.status);
                    if (result.status === 'success') {
                        $('#changeVip').removeClass('visible');
                        loadVipsList(currentVipsPage);
                    }
                });
        });

        initBulkSelection({
            tableSelector: '#vipsTable',
            ignoreClickSelector: 'button, a, input, .copy-btn',
            isEnabled: function () {
                return getVipListCapabilities(myData).canBulkSelect;
            },
            onToggle: toggleVipBulkActions
        });
    }

    if (!$('#addingVip').length) {
        return;
    }

    if (typeof initAdaptiveSelects === 'function') {
        initAdaptiveSelects('#addingVip');
    }

    $('#createVip').on('click', function () {
        var $btn = $(this);
        sendRequestWithButton($btn, buildCreatePrivilegePayload())
            .done(function (result) {
                noty(result.message, result.status);
                if (result.status === 'success') {
                    resetCreatePrivilegeForm();
                    if ($('#vipsTable').length) {
                        loadVipsList(currentVipsPage);
                    }
                }
            });
    });
});