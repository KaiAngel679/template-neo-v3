let currentFinancesPage = 1;
let financesData = null;
let myData = null;
let financeCurrency = '';

function getFinanceSort() {
    return $('#financeFilters input[name="filter-sorting"]:checked').val() || 'down';
}

function getFinanceBalanceFilter() {
    return $('#financeFilters input[name="filter-finance-balance"]:checked').val() || 'all';
}

function refreshFinanceFilterSelects() {
    if (typeof updateAdaptiveSelectText !== 'function') {
        return;
    }
    $('#financeFilters .adaptive-select-wrapper').each(function () {
        updateAdaptiveSelectText($(this));
    });
}

function loadFinancesList(page) {
    page = page || 1;
    currentFinancesPage = page;

    return sendRequest({
        get_finances_list: true,
        sort: getFinanceSort(),
        balance_filter: getFinanceBalanceFilter(),
        limit: getListLimit(),
        offset: (page - 1) * getListLimit(),
        search: getSearchQuery()
    }).done(function (result) {
        if (result.status !== 'success') {
            if (result.message) {
                noty(result.message, result.status);
            }
            return;
        }

        financesData = result.data || [];
        myData = result.my_data || null;
        financeCurrency = result.currency || '';
        window.atFinanceMyData = myData;

        $('#financesTable').html(renderFinancesList(financesData, financeCurrency));
        updateFinanceTableBulkUi();

        const totalPages = Math.max(1, Math.ceil((result.total || 0) / getListLimit()));
        $('#financesPagination').html(renderPagination(page, totalPages));

        finishTableRender(financesData, [{ flagField: 'checked_avatar', steamField: 'steamid' }]);
        initTippy();
    });
}

function findFinanceByAuth(auth) {
    if (!auth || !Array.isArray(financesData)) {
        return null;
    }

    return financesData.find(function (row) {
        return String(row.auth) === String(auth);
    }) || null;
}

function openEditFinanceModal(auth) {
    const finance = findFinanceByAuth(auth);
    if (!finance) {
        return;
    }

    const $modal = $('#changeFinance');
    $modal.html(renderEditFinanceModal(finance));
    $modal.addClass('visible');
    initTippy();
}

function confirmResetBalances(authList) {
    if (!authList || !authList.length) {
        return;
    }

    const message = authList.length > 1
        ? get_translate_module_phrase('module_page_atools', '_at_confirmDeleteBalancesBulk')
        : get_translate_module_phrase('module_page_atools', '_at_confirmDeleteBalance');

    confirmAndRequest({
        message: message,
        confirmText: get_translate_module_phrase('module_page_atools', '_at_deleteBalance'),
        body: { reset_balances: true, auth_list: authList },
        onSuccess: function () {
            loadFinancesList(currentFinancesPage);
        }
    });
}

function buildFinanceBulkActionsHtml() {
    const caps = getFinanceListCapabilities(myData);
    if (!caps.canReset) {
        return '';
    }

    return `<button class="filter" data-bulk-action="reset">${get_translate_module_phrase('module_page_atools', '_at_deleteBalance')}</button>`;
}

function toggleFinanceBulkActions() {
    const caps = getFinanceListCapabilities(myData);
    toggleBulkActionsPanel({
        enabled: caps.canBulkSelect,
        tableSelector: '#financesTable',
        buildHtml: buildFinanceBulkActionsHtml
    });
}

function resetFinanceFilters() {
    resetFilterPanel('financeFilters', function () {
        $('#filterSortDown').prop('checked', true);
        $('#financeBalanceFilterAll').prop('checked', true);
        refreshFinanceFilterSelects();
    });
    clearSearch();
    loadFinancesList(1);
}

registerActionMenu('finances', {
    items: [
        {
            action: 'edit',
            label: get_translate_phrase('_Change'),
            icon: 'edit-pen',
            visible: function () {
                return getFinanceListCapabilities(myData).canUpdate;
            }
        },
        {
            action: 'delete',
            label: get_translate_module_phrase('module_page_atools', '_at_deleteBalance'),
            icon: 'trash',
            className: 'button-delete',
            visible: function () {
                return getFinanceListCapabilities(myData).canReset;
            }
        }
    ],
    rowSelector: 'tr',
    onAction: function (action, $trigger) {
        const auth = $trigger.data('finance-auth');
        if (!auth) {
            return;
        }

        const caps = getFinanceListCapabilities(myData);

        switch (action) {
            case 'edit':
                if (!caps.canUpdate) {
                    return;
                }
                openEditFinanceModal(String(auth));
                break;
            case 'delete':
                if (!caps.canReset) {
                    return;
                }
                confirmResetBalances([String(auth)]);
                break;
        }
    }
});

$(document).ready(function () {
    if (!$('#financesTable').length) {
        return;
    }

    if (typeof initAdaptiveSelects === 'function') {
        initAdaptiveSelects('#financeFilters');
    }

    loadFinancesList(1);

    $(document).on('change', '#financeFilters input[name="filter-sorting"], #financeFilters input[name="filter-finance-balance"]', function () {
        loadFinancesList(1);
    });

    bindAtListRefresh(function () {
        loadFinancesList(1);
    }, { selector: '#financesTable', resetPage: true });

    $(document).on('click', '#resetFinanceFilters', function () {
        resetFinanceFilters();
    });

    bindAtPagination(loadFinancesList);

    $(document).on('click', '#addFinanceBalance', function () {
        var $btn = $(this);
        sendRequestWithButton($btn, {
            add_balance: true,
            steamid: $('#financeSteamIdInput').val(),
            amount: $('#countSummAdding').val()
        }).done(function (result) {
            if (result.message) {
                noty(result.message, result.status);
            }
            if (result.status === 'success') {
                $('#financeSteamIdInput, #countSummAdding').val('');
                loadFinancesList(1);
            }
        });
    });

    $(document).on('click', '#confirmEditFinance', function () {
        if (!getFinanceListCapabilities(myData).canUpdate) {
            return;
        }

        sendRequest({
            update_balance: true,
            auth: $(this).data('auth'),
            cash: $('#countSummEdit').val(),
            old_cash: $(this).data('old-cash')
        }).done(function (result) {
            if (result.message) {
                noty(result.message, result.status);
            }
            if (result.status === 'success') {
                $('#changeFinance').removeClass('visible');
                loadFinancesList(currentFinancesPage);
            }
        });
    });

    $(document).on('click', '.at__checked-action [data-bulk-action="reset"]', function () {
        if (!getFinanceListCapabilities(myData).canReset) {
            return;
        }
        confirmResetBalances(collectSelectedRowIds('#financesTable'));
    });

    $(document).on('click', '#clearFinancePlayersWithoutDonation', function () {
        confirmAndRequest({
            message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteFinancePlayersWithoutDonation'),
            confirmText: get_translate_module_phrase('module_page_atools', '_at_clearPlayersWithoutDonation'),
            body: { delete_finances_without_donation: true },
            onSuccess: function () {
                loadFinancesList(1);
            }
        });
    });

    initBulkSelection({
        tableSelector: '#financesTable',
        ignoreClickSelector: 'button, a, input',
        isEnabled: function () {
            return getFinanceListCapabilities(myData).canBulkSelect;
        },
        onToggle: toggleFinanceBulkActions
    });
});
