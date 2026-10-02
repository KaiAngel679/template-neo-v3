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
    if (check === 1) {
        avatar.push(steamid);
    }
}

$(document).ready(function () {
    initActionMenus();
    let limit = getListLimit();
    $(`.filter.button-icon:contains(${limit})`).addClass('active');

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

    function toggleDateInputClass(input) {
        if (input.value) {
            input.classList.add('has-value');
        } else {
            input.classList.remove('has-value');
        }
    }

    $('.at__input-date').each(function () {
        toggleDateInputClass(this);
    });

    $(document).on('change input', '.at__input-date', function () {
        toggleDateInputClass(this);
        $(this).removeClass('picker-open');
    });

    $(document).on('blur focusout', '.at__input-date', function () {
        const $input = $(this);
        setTimeout(function () {
            $input.removeClass('picker-open');
        }, 150);
    });

    $(document).on('click', '.at__datepicker-icon', function (e) {
        e.stopPropagation();
        const input = $(this).siblings('.at__input-date')[0];
        if (input) {
            try {
                input.showPicker();
                $(input).addClass('picker-open');
            } catch (error) {
                input.focus();
                input.click();
            }
        }
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.at__datepicker').length) {
            $('.at__input-date').removeClass('picker-open');
        }
    });
});