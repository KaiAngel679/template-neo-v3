function delay(fn, ms) {
    let timer = 0;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(fn.bind(this, ...args), ms || 0);
    }
}

$(document).on('keyup', '#search', delay(function () {
    getCheckList();
}, 500));

$(document).on('change', 'input[name="server_id"]', function () {
    getCheckList();
});

$(document).on('change', 'input[name="verdict"]', function () {
    getCheckList();
});

function getCheckList(page = 1) {
    const server = $('input[name="server_id"]:checked').val();
    const verdict = $('input[name="verdict"]:checked').val();
    const search = $('#search').val();
    $.ajax({
        type: "POST",
        url: location.href,
        dataType: "json",
        data: { getCheckList: true, page: page, server: server, verdict: verdict, search: search },
        success: function (data) {
            if (data.checks.length > 0) {
                $('#contentTable').html(RenderTable(data.checks));
            } else {
                $('#contentTable').html('<div class="no-data">' + get_translate_phrase('_nothingFound') + '</div>');
            }
            const pagination = $('#contentPagination');
            pagination.html(renderPagination(page, data.max_pages));
            pagination.off('click').on('click', 'a[data-page]', function () {
                const newPage = parseInt($(this).data('page'));
                if (!isNaN(newPage)) {
                    getCheckList(newPage);
                }
            });
        },
        error: function () { return false; }
    });
}
getCheckList();

$(document).on('click', '#addReason', function () {
    $('#checkreasonsList').append(`
        <div class="check__verdict-flex">
            <input type="text" placeholder="`+ get_translate_module_phrase('module_page_check', '_setVerdict') + `" name="reason[]" value="">
            <button type="button" class="button-delete">
                <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
            </button>
        </div>
    `);
});

$(document).on('click', '.button-delete', function () {
    const $list = $('#checkreasonsList');
    if ($list.find('.check__verdict-flex').length > 1) {
        $(this).closest('.check__verdict-flex').remove();
    }
});

$('#addReasons').on('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
        url: location.href,
        method: "POST",
        data: formData,
        dataType: 'json',
        global: false,
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.status === 'success') {
                noty(data.text, data.status);
                setTimeout(() => location.reload(), 1000);
            } else {
                noty(data.text, data.status);
            }
        }
    });
});

$('#addAdminAccess').on('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
        url: location.href,
        method: "POST",
        data: formData,
        dataType: 'json',
        global: false,
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.status === 'success') {
                noty(data.text, data.status);
                setTimeout(() => location.reload(), 1000);
            } else {
                noty(data.text, data.status);
            }
        }
    });
});

$(document).on('click', '.checkDelAccess', function () {
    let button = $(this);
    const id_del = button.attr('id_del');
    $.ajax({
        type: 'post',
        url: location.href,
        data: { accessdel: true, id_del: id_del },
        dataType: 'json',
        global: false,
        success: function (data) {
            if (data.status == "success") {
                noty(data.text, data.status);
                button.closest('tr').remove();
            } else {
                noty(data.text, data.status)
            }
        },
    });
});

function changeSettings(name) {
    $.ajax({
        type: 'post',
        url: location.href,
        data: { changeSettings: true, name: name },
        dataType: 'json',
        global: false,
        success: function (data) {
            if (data.status == "success") {
                noty(data.text, data.status);
                button.closest('tr').remove();
            } else {
                noty(data.text, data.status)
            }
        },
    });
}

function RenderTable(data) {
    let rows = [];
    data.forEach((check, index) => {
        if (check.suspect_discord) {
            var discordContent = `
            <code class="copy-btn grid-container" data-tippy-content="${get_translate_phrase('_Copytext')}" data-tippy-placement="top" data-clipboard-text="${check.suspect_discord}">
                <span class="check__long-text">${check.suspect_discord ?? ''}</span>
                <svg>
                    <use href="/resources/img/sprite.svg#copy-list"></use>
                </svg>
            </code>`;
        } else {
            var discordContent = `${get_translate_phrase('_absent')}`;
        }
        rows.push(`
            <tr>
                <td>${check.id}</td>
                <td>
                    <div class="grid-container">
                        <a class="check__long-text" href="/profiles/${check.player_steamid}/?search=1" target="_blank" id="name" nameid="${check.player_steamid}">
                            ${check.player_name}
                        </a>
                    </div>
                </td>
                <td>
                    <div class="grid-container">
                        <a class="check__long-text" href="/profiles/${check.admin_steamid}/?search=1" target="_blank" id="name" nameid="${check.admin_steamid}">
                            ${check.admin_name}
                        </a>
                    </div>
                </td>
                <td>${check.datestart ?? ''}</td>
                <td>${check.date_end ?? ''}</td>
                <td>${check.verdict ?? ''}</td>
                <td>
                    ${discordContent}
                </td>
            </tr>
        `);
        checkAndRenderAvatar(check.admin_checked_avatar, check.admin_steamid);
        checkAndRenderAvatar(check.player_checked_avatar, check.player_steamid);
    });
    RenderingAvatar();
    return `<table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>${get_translate_module_phrase('module_page_check', '_checkedPlayer')}</th>
                        <th>${get_translate_phrase('_Admin')}</th>
                        <th>${get_translate_module_phrase('module_page_check', '_startCheck')}</th>
                        <th>${get_translate_module_phrase('module_page_check', '_endCheck')}</th>
                        <th>${get_translate_module_phrase('module_page_check', '_resultCheck')}</th>
                        <th>${get_translate_module_phrase('module_page_check', '_contact')}</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows.join('')}
                </tbody>
            </table>`;
}

function checkAndRenderAvatar(check, steamid) {
    if (check === 1) {
        avatar.push(steamid);
    }
}

