let currentLogsPage = 1;
let logsData = null;
let logsFiltersResetting = false;
let logsCalendarApi = null;

function buildLogsListRequest(page) {
    var dates = getAtCalendarDateRange(logsCalendarApi);

    return {
        get_logs_list: true,
        category: $('#logsType input[name="logs-type"]:checked').val() || 'all',
        sort: $('#sortByFreshness input[name="logs-sort"]:checked').val() || 'new',
        date_from: dates.from,
        date_to: dates.to,
        limit: getListLimit(),
        offset: (page - 1) * getListLimit(),
        search: getSearchQuery()
    };
}

function loadLogsList(page) {
    page = page || 1;
    currentLogsPage = page;

    var limit = getListLimit();
    $('#logsList').html(renderLogsSkeletonList(limit));

    sendRequest(buildLogsListRequest(page))
        .done(function (result) {
            if (result.status !== 'success') {
                if (result.message) {
                    noty(result.message, result.status);
                }
                return;
            }

            logsData = result.data || [];
            $('#logsList').html(renderLogsList(logsData));

            var totalPages = Math.max(1, Math.ceil((result.total || 0) / getListLimit()));
            $('#logsPagination').html(renderPagination(page, totalPages));

            (logsData || []).forEach(function (log) {
                if (log.admin && log.admin.steamid) {
                    checkAndRenderAvatar(log.admin.checked_avatar, log.admin.steamid);
                }
                (log.targets || []).forEach(function (target) {
                    if (target.steamid) {
                        checkAndRenderAvatar(target.checked_avatar, target.steamid);
                    }
                });
            });

            initTippy();
            RenderingAvatar();
        });
}

function resetLogFilters() {
    logsFiltersResetting = true;

    if (logsCalendarApi) {
        logsCalendarApi.reset();
    }

    resetFilterPanel('logsFilters', function () {
        $('#logsAll').prop('checked', true);
        $('#newLogs').prop('checked', true);
        if (typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($('#logsType').closest('.adaptive-select-wrapper'));
            updateAdaptiveSelectText($('#sortByFreshness').closest('.adaptive-select-wrapper'));
        }
    });

    clearSearch();
    logsFiltersResetting = false;
    loadLogsList(1);
}

function confirmDeleteLog(fileDate, index) {
    confirmAndRequest({
        message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteLog'),
        confirmText: get_translate_phrase('_Delete_Action'),
        body: { delete_log: true, file_date: fileDate, index: index },
        onSuccess: function () {
            loadLogsList(currentLogsPage);
        }
    });
}

$(document).ready(function () {
    logsCalendarApi = initAtRangeCalendar({
        calendarSelector: '#logsCalendar',
        startInput: '#logStartDate',
        endInput: '#logEndDate',
        periodSelector: '#logsCalendarPeriod',
        triggerSelector: '#logsCalendarTrigger',
        filterSelector: '#logsCalendarFilter',
        emptyLabel: get_translate_module_phrase('module_page_atools', '_at_selectDateRange'),
        onChange: function () {
            if (logsFiltersResetting) {
                return;
            }
            loadLogsList(1);
        }
    });

    loadLogsList(1);

    $(document).on('change', '#logsType input[name="logs-type"], #sortByFreshness input[name="logs-sort"]', function () {
        if (logsFiltersResetting) {
            return;
        }
        loadLogsList(1);
    });

    bindAtListRefresh(function () {
        loadLogsList(1);
    }, { resetPage: true });

    $(document).on('click', '#resetLogFilters', function () {
        resetLogFilters();
    });

    bindAtPagination(loadLogsList);

    $(document).on('click', '.at__logs-delete', function () {
        const fileDate = $(this).data('file-date');
        const index = parseInt($(this).data('log-index'), 10);
        if (fileDate && Number.isFinite(index)) {
            confirmDeleteLog(fileDate, index);
        }
    });
});
