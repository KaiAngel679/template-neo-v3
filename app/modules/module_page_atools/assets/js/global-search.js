var globalSearchRequestId = 0;
var globalSearchDebounceTimer = null;

function hideAtDriverPanelsForGlobalSearch() {
    $('.at__driver.show').removeClass('show');
}

function openGlobalSearch() {
    var $search = $('.at__global-search');

    if (!$search.length) {
        return;
    }

    hideAtDriverPanelsForGlobalSearch();
    $search.addClass('is-open');
    $('#searchUserGlobal').trigger('focus');
}

function closeGlobalSearch() {
    $('.at__global-search').removeClass('is-open');
    sessionStorage.removeItem('atools_prefill_search');
}

function isGlobalSearchOpen() {
    return $('.at__global-search').hasClass('is-open');
}

function resetGlobalSearchResults() {
    $('#globalSearchUser').empty();
    $('#globalSearchSections').empty();
}

function showGlobalSearchLoading() {
    resetGlobalSearchResults();
    $('#globalSearchSections').html(renderGlobalSearchSkeletonSections() + renderGlobalSearchSkeletonSections());
}

function runGlobalSearch(query) {
    query = String(query || '').trim();
    if (query.length < 2) {
        resetGlobalSearchResults();
        return;
    }

    var requestId = ++globalSearchRequestId;
    showGlobalSearchLoading();

    sendRequest({
        global_search: 1,
        search: query
    }).done(function (result) {
        if (requestId !== globalSearchRequestId) {
            return;
        }

        if (!result || result.status === 'error') {
            resetGlobalSearchResults();
            if (result && result.message) {
                noty(result.message, 'error');
            }
            return;
        }

        if (result.mode === 'not_found') {
            $('#globalSearchUser').empty();
            $('#globalSearchSections').html(renderGlobalSearchNotFound(result.message || get_translate_module_phrase('module_page_atools', '_at_searchNotFound')));
            return;
        }

        if (result.mode === 'candidates') {
            $('#globalSearchUser').html(renderGlobalSearchCandidates(result.candidates || []));
            $('#globalSearchSections').empty();
            finishGlobalSearchRender(null, result.candidates || []);
            return;
        }

        if (result.mode === 'player') {
            $('#globalSearchUser').html(renderGlobalSearchPlayerCard(result.player));
            $('#globalSearchSections').html(renderGlobalSearchSections(result.sections || {}, query));

            var avatarItems = [];
            if (result.sections) {
                ['punishments', 'checks', 'reports', 'admins', 'vip', 'finances', 'experience'].forEach(function (key) {
                    var section = result.sections[key];
                    if (!section) {
                        return;
                    }
                    if (section.items) {
                        avatarItems = avatarItems.concat(section.items);
                    }
                });
            }

            finishGlobalSearchRender(result.player, avatarItems);
        }
    }).fail(function () {
        if (requestId !== globalSearchRequestId) {
            return;
        }
        resetGlobalSearchResults();
    });
}

function scheduleGlobalSearch(query) {
    clearTimeout(globalSearchDebounceTimer);
    globalSearchDebounceTimer = setTimeout(function () {
        runGlobalSearch(query);
    }, 350);
}

$(document).ready(function () {
    $(document).on('click', '[data-open-search]', function (e) {
        e.preventDefault();
        openGlobalSearch();
    });

    $(document).on('click', '[data-close-search]', function (e) {
        e.preventDefault();
        closeGlobalSearch();
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && isGlobalSearchOpen()) {
            closeGlobalSearch();
        }
    });

    $(document).on('input', '#searchUserGlobal', function () {
        scheduleGlobalSearch($(this).val());
    });

    $(document).on('keydown', '#searchUserGlobal', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(globalSearchDebounceTimer);
            runGlobalSearch($(this).val());
        }
    });

    $(document).on('click', '.at__global-search-candidate', function (e) {
        e.preventDefault();
        var steamid = $(this).data('steamid');
        if (!steamid) {
            return;
        }
        $('#searchUserGlobal').val(steamid);
        runGlobalSearch(steamid);
    });

    $(document).on('click', '.at__global-search-result-title-show-all', function () {
        var query = $('#searchUserGlobal').val().trim();
        if (query) {
            sessionStorage.setItem('atools_prefill_search', query);
        }
    });
});
