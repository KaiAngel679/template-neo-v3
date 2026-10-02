let currentChecksPage = 1;
let checksData = null;
let myData = null;
let checkFiltersResetting = false;
let checksCalendarApi = null;

function getFilterServersOrDefault() {
    return getCheckedServerFilterIds('#checkServersListFilter input[type="checkbox"]:checked');
}

function buildChecksListRequest(page) {
    const verdict = $('#checkVerdictsListFilter input[type="radio"]:checked').val() || 'all';
    const dates = getAtCalendarDateRange(checksCalendarApi);

    return {
        get_checks_list: true,
        admin: parseInt($('#checkAdminListFilter input[type="radio"]:checked').val() || -1, 10),
        servers: getFilterServersOrDefault(),
        verdict: verdict,
        date_from: dates.from,
        date_to: dates.to,
        limit: getListLimit(),
        offset: (page - 1) * getListLimit(),
        search: getSearchQuery()
    };
}

function loadChecksList(page) {
    page = page || 1;
    currentChecksPage = page;

    sendRequest(buildChecksListRequest(page))
        .done(function (result) {
            if (result.status !== 'success') {
                return;
            }

            checksData = result.data;
            myData = result.my_data;
            window.atCheckMyData = myData;

            $('#checkTable').html(renderChecksList(result.data));
            updateCheckTableBulkUi();

            var totalPages = Math.ceil(result.total / getListLimit());
            $('#checksPagination').html(renderPagination(page, totalPages));

            finishTableRender(result.data, [
                { flagField: 'admin_checked_avatar', steamField: 'admin_steamid' },
                { flagField: 'player_checked_avatar', steamField: 'player_steamid' }
            ]);
        });
}

function resetCheckFilters() {
    checkFiltersResetting = true;

    if (checksCalendarApi) {
        checksCalendarApi.reset();
    }

    resetFilterPanel('checkFilters', function () {
        $('#checkAdminListFilterAll').prop('checked', true);
        $('#checkServersListFilterAll').prop('checked', true);
        const $allVerdict = $('#checkVerdictsListFilter input[value="all"]');
        if ($allVerdict.length) {
            $allVerdict.prop('checked', true);
        }
        if (typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($('#checkFilterByAdmin'));
            updateAdaptiveSelectText($('#checkFilterServers'));
            updateAdaptiveSelectText($('#checkFilterVerdicts'));
        }
    });

    checkFiltersResetting = false;
    loadChecksList(1);
}

function buildCheckBulkActionsHtml() {
    const caps = getCheckListCapabilities(myData);
    if (!caps.canDelete) {
        return '';
    }

    return `<button class="filter" data-bulk-action="delete">${get_translate_phrase('_Delete_Action')}</button>`;
}

function toggleCheckBulkActions() {
    const caps = getCheckListCapabilities(myData);
    toggleBulkActionsPanel({
        enabled: caps.canBulkSelect,
        tableSelector: '#checkTable',
        buildHtml: buildCheckBulkActionsHtml
    });
}

function confirmDeleteChecks(ids) {
    if (!ids.length) {
        return;
    }

    confirmAndRequest({
        message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteChecks'),
        confirmText: get_translate_phrase('_Delete_Action'),
        body: { delete_checks: true, check_ids: ids },
        onSuccess: function () {
            loadChecksList(currentChecksPage);
        }
    });
}

$(document).ready(function () {
    checksCalendarApi = initAtRangeCalendar({
        calendarSelector: '#checksCalendar',
        startInput: '#checkStartDate',
        endInput: '#checkEndDate',
        periodSelector: '#checksCalendarPeriod',
        triggerSelector: '#checksCalendarTrigger',
        filterSelector: '#checksCalendarFilter',
        emptyLabel: get_translate_module_phrase('module_page_atools', '_at_selectDateRange'),
        onChange: function () {
            if (checkFiltersResetting) {
                return;
            }
            loadChecksList(1);
        }
    });

    loadChecksList(1);

    $(document).on('change', '#checkAdminListFilter input[type="radio"], #checkServersListFilter input[type="checkbox"], #checkVerdictsListFilter input[type="radio"]', function () {
        if (checkFiltersResetting) {
            return;
        }
        loadChecksList(1);
    });

    bindAtListRefresh(function () {
        loadChecksList(1);
    }, { resetPage: true });

    $(document).on('click', '#resetCheckFilters', function () {
        resetCheckFilters();
    });

    bindAtPagination(loadChecksList);

    $(document).on('click', '.at-btn-check-delete', function () {
        const caps = getCheckListCapabilities(myData);
        if (!caps.canDelete) {
            return;
        }
        const id = parseInt($(this).data('check-id'), 10);
        if (id) {
            confirmDeleteChecks([id]);
        }
    });

    $(document).on('click', '.at__checked-action [data-bulk-action="delete"]', function () {
        const caps = getCheckListCapabilities(myData);
        if (!caps.canDelete) {
            return;
        }
        confirmDeleteChecks(collectSelectedRowIds('#checkTable'));
    });

    initBulkSelection({
        tableSelector: '#checkTable',
        ignoreClickSelector: 'button, a, input, .copy-btn',
        isEnabled: function () {
            return getCheckListCapabilities(myData).canBulkSelect;
        },
        onToggle: toggleCheckBulkActions
    });
});
