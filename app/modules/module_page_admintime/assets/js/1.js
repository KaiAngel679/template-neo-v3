
function getAdminTimeList(page = 1) {
    const server = $('input[name="server_id"]:checked').val();
    const steamid = $('input[name="admin_steamid"]').val();
    const start = $('input[name="date_start"]').val();
    const end = $('input[name="date_end"]').val();
    $.ajax({
        type: 'POST',
        url: location.href,
        data: { getAdminTimeList: true, page: page, server: server, steamid: steamid, date_start: start, date_end: end },
        dataType: 'json',
        global: false,
        success: function (data) {
            if (data.admins.length > 0) {
                $('#AdminTimeList').html('');
                data.admins.forEach(function (admin) {
                    $('#AdminTimeList').append(`
                    <div class="admin-time__card">
                        <div id="background" backgroundid="${admin.steamid}">${admin.background}</div>
                        <div class="admin-time__head">
                            <img class="admin-time__avatar" id="avarat" avatarid="${admin.steamid}" src="${admin.avatar}" alt="" title="">
                            <div class="admin-time__user-data">
                            <a href="/profiles/${admin.steamid}/?search=1" target="_blank" class="admin-time__name" id="name" nameid="${admin.steamid}">${admin.name}</a>
                            <div class="admin-time__steam copy-btn" data-clipboard-text="${admin.steamid}">
                                ${admin.steamid}
                                <svg><use href="/resources/img/sprite.svg#copy-list"></use></svg>
                            </div>
                            </div>
                        </div>
                        <div class="admin-time__bottom">
                            <div class="admin-time__details">
                                <p class="admin-time__title">${get_translate_module_phrase('module_page_admintime', '_TimePLayed')}</p>
                                <div class="admin-time__time">${formatTime(admin.total_time || 0)}</div>
                            </div>
                            <div class="action-buttons">
                                <button class="icon_btn_transparent" data-openmodal="sessions" data-admin-name="${get_translate_module_phrase('module_page_admintime', '_adminSession')} ${admin.name}" data-admin-id="${admin.steamid}" data-type="sessions">
                                    <svg><use href="/resources/img/sprite.svg#timer"></use></svg>
                                </button>
                                <button class="icon_btn_transparent" data-openmodal="charts" data-admin-name="${get_translate_module_phrase('module_page_admintime', '_adminSession')} ${admin.name}" data-admin-id="${admin.steamid}" data-type="charts">
                                    <svg><use href="/resources/img/sprite.svg#chart"></use></svg>
                                </button>
                            </div>
                        </div>
                    </div>`);
                    checkAndRenderAvatar(admin.steamid, admin.checked_avatar);
                });
                RenderingAvatar();
            } else {
                $('#AdminTimeList').html('<div class="havent_info">' + get_translate_phrase('_nothingFound') + '</div>');
            }
            const pagination = $('#AdminTimePagination');
            pagination.html(renderPagination(page, data.max_pages));
            pagination.off('click').on('click', 'a[data-page]', function () {
                const newPage = parseInt($(this).data('page'));
                if (!isNaN(newPage)) {
                    getAdminTimeList(newPage);
                }
            });
        },
        error: function () { return false; }
    });
}
getAdminTimeList();

function openAdminSessionModal(page = 1) {
    const server = $('input[name="server_id"]:checked').val();
    const steamid = $('#sessions').data('admin-id');
    const start = $('input[name="date_start"]').val();
    const end = $('input[name="date_end"]').val();
    $.ajax({
        type: 'POST',
        url: location.href,
        data: { AdminSessionModal: true, steamid: steamid, server: server, page: page, date_start: start, date_end: end },
        dataType: 'json',
        global: false,
        success: function (data) {
            if (data.sessions.length > 0) {
                $('#adminName').html(data.name);
                $('#sessions_list').html('');
                data.sessions.forEach(function (session) {
                    const connect_time = new Date(session.connect_time * 1000).toLocaleString();
                    const disconnect_time = new Date(session.disconnect_time * 1000).toLocaleString();
                    const played_time = formatTime(session.played_time || 0);
                    $('#sessions_list').append(`
                        <tr>
                            <td>${session.server_id}</td>
                            <td>${connect_time}</td>
                            <td>${disconnect_time}</td>
                            <td>${played_time}</td>
                        </tr>
                    `);
                });
                const pagination = $('#sessions_pagination');
                pagination.html(renderPagination(page, data.max_pages));
                pagination.off('click').on('click', 'a[data-page]', function () {
                    const newPage = parseInt($(this).data('page'));
                    if (!isNaN(newPage)) {
                        openAdminSessionModal(newPage);
                    }
                });
            } else {
                $('#adminName').html('Error');
                $('#sessions_list').html('<div class="havent_info">' + get_translate_phrase('_nothingFound') + '</div>');
                $('#sessions_pagination').html('');
            }
        },
        error: function () { return false; }
    });
}

function openAdminChartsModal() {
    const server = $('input[name="server_id"]:checked').val();
    const steamid = $('#charts').data('admin-id');
    const start = $('input[name="date_start"]').val();
    const end = $('input[name="date_end"]').val();
    $.ajax({
        type: 'POST',
        url: location.href,
        data: { AdminChartsModal: true, steamid: steamid, server: server, date_start: start, date_end: end },
        dataType: 'json',
        global: false,
        success: function (data) {
            if(!data.start && !data.end) {
                $('#adminNameCharts').html(`${get_translate_module_phrase('module_page_admintime', '_adminCharts')} <a href="/profiles/${steamid}/?search=1">${data.name}</a> ${get_translate_module_phrase('module_page_admintime', '_last30days')}`);
            } else {
                const startDate = new Date(data.start * 1000);
                const endDate = new Date(data.end * 1000);
                const formatDate = (date) => `${date.getDate()}.${date.getMonth() + 1}`;
                $('#adminNameCharts').html(
                    `${get_translate_module_phrase('module_page_admintime', '_adminCharts')} <a href="/profiles/${steamid}/?search=1">${data.name}</a> ${get_translate_module_phrase('module_page_admintime', '_from')} ${formatDate(startDate)} ${get_translate_module_phrase('module_page_admintime', '_to')} ${formatDate(endDate)}`
                );
            }
            if (!chart) return;
            var formattedTimes = data.time.map(t => Number(t) || 0);
            chart.updateSeries([{ name: get_translate_module_phrase('module_page_admintime', '_time'), data: formattedTimes }]);
            chart.updateOptions({
                xaxis: { categories: data.categories },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return formatTime(val);
                        }
                    }
                }
            });
        },
        error: function () { return false; }
    });
}

$(document).ready(function () {
    if (typeof VanillaCalendar !== 'undefined') {
        const calendar = new VanillaCalendar("#VanillaCalendar", {
            settings: {
                lang: lang.toLowerCase(),
                selection: {
                    day: 'multiple-ranged',
                },
                visibility: {
                    theme: 'dark',
                }
            },
            actions: {
                clickDay: function (e, calendarInstance) {
                    const dates = calendarInstance.selectedDates || [];
                    if (dates.length === 0) {
                        $('#dateStart').val('');
                        $('#dateEnd').val('');
                        return;
                    }
                    const startDateUnix = toUnixTime(dates[0]);
                    const endDateUnix = toUnixTime(dates[dates.length - 1]);
                    $('#dateStart').val(startDateUnix);
                    $('#dateEnd').val(endDateUnix);
                    getAdminTimeList();
                },
            },
        });
        calendar.init();
    }
    if (typeof ApexCharts !== 'undefined') {
        var options = {
            chart: {
                type: 'area',
                height: 350,
                zoom: { enabled: true },
                foreColor: 'var(--text-secondary)',
                toolbar: { show: false }
            },
            colors: ['var(--span)'],
            dataLabels: { enabled: false },
            markers: { colors: ['var(--span)'] },
            series: [{ name: get_translate_module_phrase('module_page_admintime', '_time'), data: [] }],
            xaxis: { categories: [] },
            yaxis: {
                labels: {
                    show: false
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return formatTime(val);
                    }
                }
            }
        };

        chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    }
    let timer;
    $('#admin_steamid').on('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            getAdminTimeList();
        }, 800);
    });
    sendAjax("#addServer", {
        param: "addServer",
        success: function (data) {
            if (data.status === 'success') {
                noty(data.text, data.status);
                $('.adm_online_servers .havent_servers').remove();
                $('.adm_online_servers').append(`
                    <div class="adm_online_server">
                        <div class="srv_left">
                            <span>ID: ${data.server.id} | ${data.server.name}</span>
                            <span>${get_translate_module_phrase('module_page_admintime', '_iksServerID')}: ${data.server.server_id}</span>
                        </div>
                        <div class="srv_right">
                            <button server-id="${data.server.id}" class="button-delete width-100 deleteServer">${get_translate_phrase('_Delete_Action')}</button>
                        </div>
                    </div>
                `);
            } else {
                noty(data.text, data.status);
            }
        }
    });
    sendAjax("#addSettings", {
        param: "addSettings",
        success: function (data) {
            if (data.status === 'success') {
                noty(data.text, data.status);
            } else {
                noty(data.text, data.status);
            };
        }
    });
    sendAjax("#addAccess", {
        param: "addAccess",
        success: function (data) {
            if (data.status === 'success') {
                noty(data.text, data.status);
                $('.onlineadm_access_list .havent_accesses').remove();
                $('.onlineadm_access_list').append(`
                    <div class="onlineadm_access_admin">
                        <div class="admin_details">
                            <img id="avatar" avatarid="${data.admin.steamid}" src="${data.admin.avatar}" alt="">
                            <div>
                                <a href="https:<?= $General->arr_general['site'] ?>profiles/${data.admin.steamid}/?search=1" target="_blank">${data.admin.name}</a>
                            </div>
                        </div>
                        <button userId="${data.admin.steamid}" class="button button-delete deleteAccess">${get_translate_phrase('_Delete_Action')}</button>
                    </div>
                `);
                checkAndRenderAvatar(data.admin.avatarCheck, data.admin.steamid);
                RenderingAvatar();
            } else {
                noty(data.text, data.status);
            }
        }
    });
    $(document).on('click', '.deleteServer', function () {
        const serverId = $(this).attr('server-id');
        if (serverId) {
            $.ajax({
                url: location.href,
                method: "POST",
                data: {
                    deleteServer: true,
                    server_id: serverId
                },
                dataType: 'json',
                success: function (data) {
                    noty(data.text, data.status);
                    $(this).closest('.adm_online_server').remove();
                },
                error: function () { return false; }
            });
        }
    });
    $(document).on('click', '.deleteAccess', function () {
        const userId = $(this).attr('userId');
        if (userId) {
            $.ajax({
                url: location.href,
                method: "POST",
                data: {
                    delAccess: true,
                    steamid: userId
                },
                dataType: 'json',
                success: function (data) {
                    noty(data.text, data.status);
                    $(this).closest('.onlineadm_access_admin').remove();
                },
                error: function () { return false; }
            });
        }
    });
    $(document).on("click", "[data-openmodal]", function () {
        const modalType = $(this).data("type");

        const adminId = $(this).data("admin-id");
        if (modalType == 'sessions') {
            if (adminId) $('#sessions').data('admin-id', adminId);
            openAdminSessionModal();
        } else {
            if (adminId) $('#charts').data('admin-id', adminId);
            openAdminChartsModal();
        }
    });
});

function sendAjax(formSelect, options) {
    $(formSelect).on("submit", (e) => {
        e.preventDefault();
        const data = $(e.currentTarget).serialize() + "&" + options.param;
        $.ajax({
            type: 'post',
            url: location.href,
            data: data,
            dataType: "json",
            success: function (data) {
                options.success(data);
            },
            error: function () { return false; }
        });
        return false;
    });
}

function toUnixTime(dateString) {
    if (!dateString) return NaN;
    const date = new Date(dateString + 'T00:00:00');
    return isNaN(date.getTime()) ? NaN : Math.floor(date.getTime() / 1000);
}

$(document)
    .on('mouseenter', '.admin-time__card', function () {
        const video = this.querySelector('.back_video');
        if (video) {
            video.play().catch((err) => console.warn(get_translate_module_phrase('module_page_admins_online', '_failedToPlayVideo'), err));
        }
    })
    .on('mouseleave', '.admin-time__card', function () {
        const video = this.querySelector('.back_video');
        if (video) {
            video.pause();
        }
    });

function formatTime(totalSeconds) {
    totalSeconds = Math.max(0, Math.floor(Number(totalSeconds) || 0));
    const days = Math.floor(totalSeconds / 86400);
    const hours = Math.floor((totalSeconds % 86400) / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    const labelDays = get_translate_module_phrase('module_page_admintime', '_days');
    const labelHours = get_translate_module_phrase('module_page_admintime', '_hours');
    const labelMinutes = get_translate_module_phrase('module_page_admintime', '_minutes');
    const labelSeconds = get_translate_module_phrase('module_page_admintime', '_seconds');
    const parts = [];
    if (days > 0) parts.push(`${days} ${labelDays}`);
    if (hours > 0) parts.push(`${String(hours).padStart(2, '0')} ${labelHours}`);
    if (minutes > 0) parts.push(`${String(minutes).padStart(2, '0')} ${labelMinutes}`);
    if (seconds > 0) parts.push(`${String(seconds).padStart(2, '0')} ${labelSeconds}`);
    if (totalSeconds === 0) return get_translate_phrase('_nothingFound');
    return parts.join(' ');
}

function checkAndRenderAvatar(check, steamid) {
    if (check === 1) {
        avatar.push(steamid);
    }
}

