function getListLimit() {
    return parseInt(localStorage.getItem('admtoolsLimit') || 10, 10);
}

function setListLimit(limit) {
    localStorage.setItem('admtoolsLimit', limit);
}

function resetFilterPanel(containerId, callback) {
    $(`#${containerId} input[type="checkbox"]`).prop('checked', false);
    $(`#${containerId} input[type="radio"]`).prop('checked', false);
    if (typeof callback === 'function') {
        callback();
    }
}

function getSearchQuery() {
    return $('#searchInfo').val().trim();
}

function clearSearch() {
    $('#searchInfo').val('');
}

function checkAndRenderAvatar(check, steamid) {
    if (check == 1) {
        avatar.push(steamid);
    }
}

$(document).ready(function () {
    initActionMenus();
    let limit = getListLimit();
    $(`.filter.button-icon:contains(${limit})`).addClass('active');

    var prefillSearch = sessionStorage.getItem('atools_prefill_search');
    if (prefillSearch && $('#searchInfo').length) {
        $('#searchInfo').val(prefillSearch);
        sessionStorage.removeItem('atools_prefill_search');
        $(document).trigger('searchTriggered', [prefillSearch]);
    }

    $(document).on('click', '.filter.button-icon', function () {
        $('.filter.button-icon').removeClass('active');
        $(this).addClass('active');
        const newLimit = parseInt($(this).text(), 10);
        setListLimit(newLimit);
        $(document).trigger('limitChanged', [newLimit]);
    });

    function triggerSearch() {
        $(document).trigger('searchTriggered', [getSearchQuery()]);
    }

    let searchInfoHadText = ($('#searchInfo').val() || '').trim().length > 0;

    $(document).on('click', '#buttonSearchInfo', triggerSearch);

    $(document).on('input', '#searchInfo', function () {
        const q = getSearchQuery();
        if (searchInfoHadText && q == '') {
            triggerSearch();
        }
        searchInfoHadText = q.length > 0;
    });

    $(document).on('keypress', '#searchInfo', function (e) {
        if (e.which == 13) {
            e.preventDefault();
            triggerSearch();
        }
    });
});