function getUrlParams() {
  var params = {};
  new URLSearchParams(location.search).forEach(function (v, k) {
    var key = k.endsWith('[]') ? k.slice(0, -2) : k;
    if (params[key]) {
      if (!Array.isArray(params[key])) params[key] = [params[key]];
      params[key].push(v);
    } else {
      params[key] = v;
    }
  });
  return params;
}

function updateUrlParams(params) {
  var url = new URL(location.href);
  Object.keys(params).forEach(function (k) {
    url.searchParams.delete(k);
    url.searchParams.delete(k + '[]');
    var v = params[k];
    if (v === null || v === undefined || v === '' || (Array.isArray(v) && !v.length)) return;
    if (Array.isArray(v)) {
      v.forEach(function (val) { if (val !== '' && val !== '0') url.searchParams.append(k, val); });
    } else if (v !== '0' && v !== 0) {
      url.searchParams.set(k, v);
    }
  });
  history.replaceState(null, '', url.toString());
}

function applyUrlParamsToFilters() {
  var p = getUrlParams();
  var servers = Array.isArray(p.server) ? p.server : (p.server ? [p.server] : []);
  var groups = Array.isArray(p.group) ? p.group : (p.group ? [p.group] : []);
  if (servers.length) {
    $('#serversSelectAdminTime input[type=checkbox][value="0"]').prop('checked', false);
    servers.forEach(function (v) { $('#serversSelectAdminTime input[type=checkbox][value="' + v + '"]').prop('checked', true); });
  }
  if (groups.length) {
    $('#serversGroupAdminTime input[type=checkbox][value="0"]').prop('checked', false);
    groups.forEach(function (v) { $('#serversGroupAdminTime input[type=checkbox][value="' + v + '"]').prop('checked', true); });
  }
  if (p.timePlayed === '1') $('#timePlayed').prop('checked', true);
  if (p.searchAdmin) $('#searchAdmin').val(p.searchAdmin);
  if (p.sort) {
    $('.table-sort').removeClass('active rotated');
    var $s = $('.table-sort[data-sort="' + p.sort + '"]');
    $s.addClass('active');
    if (p.order === 'asc') $s.addClass('rotated');
  }
  return { page: parseInt(p.page) || 1, dateFrom: p.dateFrom || null, dateTo: p.dateTo || null };
}



function makeAjaxRequest(data, options = {}) {
  var isFormData = data instanceof FormData;
  return $.ajax({
    url: options.url || location.href,
    method: options.method || "POST",
    dataType: options.dataType || 'json',
    global: false,
    data: data,
    processData: !isFormData,
    contentType: isFormData ? false : 'application/x-www-form-urlencoded',
    success: options.success || function (response) {
      if (options.onSuccess) options.onSuccess(response);
      else if (response.success) {
        noty(response.success, 'success');
        if (options.reload !== false) setTimeout(function () { location.reload(); }, 1000);
      } else if (response.error) noty(response.error, 'error');
    },
    error: options.error || function () {
      if (options.onError) options.onError();
      else noty(t('_error'), 'error');
    }
  });
}

function ajaxForm(form, action, options = {}) {
  var formData = form instanceof FormData ? form : new FormData(form);
  formData.append('action', action);
  return makeAjaxRequest(formData, options);
}

function ajaxRequest(action, params = {}, options = {}) {
  return makeAjaxRequest($.param({ action: action, ...params }), options);
}

function t(key) { return get_translate_module_phrase('module_page_results', key); }
function tp(key) { return get_translate_phrase(key); }
function getCheckedVals(sel) { return $(sel).map(function () { return $(this).val(); }).get() || []; }

function importFiltergroup(checkbox, containerSelector) {
  var val = $(checkbox).val();
  var isChecked = $(checkbox).is(':checked');
  var container = $(checkbox).closest(containerSelector);
  if (!container.length || !isChecked) return;
  if (val === '0') {
    container.find('input[type=checkbox]').not(checkbox).prop('checked', false);
  } else {
    container.find('input[type=checkbox][value="0"]').prop('checked', false);
  }
}

function setupPagination(sel, page, maxPage, cb) {
  var p = $(sel);
  p.html(renderPagination(page, maxPage));
  p.off('click').on('click', 'a[data-page]', function () { var n = parseInt($(this).data('page')); if (!isNaN(n)) cb(n); });
}

function checkAndRenderAvatar(check, steamid) { if (check === 1) avatar.push(steamid); }

function adminLink(steam, name) {
  return '<a href="/profiles/' + steam + '/?search=1" id="name" nameid="' + steam + '">' + name + '</a>';
}

function copyBtn(text) {
  return '<svg class="copy-btn" data-clipboard-text="' + text + '"><use href="/resources/img/sprite.svg#copy-list"></use></svg>';
}

function adminCell(steam, name) {
  return '<span class="results__admin">' + adminLink(steam, name) + copyBtn(steam) + '</span>';
}

function iconBtn(cls, steam, icon, tip) {
  return '<button class="icon_btn_transparent ' + cls + '" data-steam="' + steam + '" data-tippy-content="' + tip + '" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#' + icon + '"></use></svg></button>';
}

function confirmDelete(title, message, onConfirm) {
  openDialog({ title: title, message: message, confirmText: tp('_Delete_Action'), cancelText: t('_Cancel_Action'), onConfirm: onConfirm });
}

['addAdminForm', 'SettingsForm', 'DiscordForm', 'ManualGenerationForm', 'AddAwardForm', 'AddAccessForm', 'AddServerForm'].forEach(function (id) {
  var actions = { addAdminForm: 'addAdmin', SettingsForm: 'saveSettings', DiscordForm: 'saveDiscord', ManualGenerationForm: 'manualGenerate', AddAwardForm: 'addAward', AddAccessForm: 'addAccess', AddServerForm: 'addServer' };
  var reloads = { SettingsForm: false };
  $('#' + id).on('submit', function (e) {
    e.preventDefault();
    ajaxForm(this, actions[id], { reload: reloads[id] !== false });
  });
});

$('#importFiltergroup').on('change', 'input[type=checkbox]', function () {
  importFiltergroup(this, '#importFiltergroup');
});

$('#editAdminForm').on('submit', function (e) {
  e.preventDefault();
  var formData = new FormData(this);
  formData.append('id', $(this).attr('data-id'));
  ajaxForm(formData, 'editAdmin');
});

$('#importAdminsForm').on('submit', function (e) {
  e.preventDefault();
  var formData = new FormData(this);
  ajaxForm(formData, 'importAdmins', { reload: false });
  renderAdmins();
});

$('#editAwardForm').on('submit', function (e) {
  e.preventDefault();
  var formData = new FormData(this);
  formData.append('id', $(this).attr('data-id'));
  ajaxForm(formData, 'editAward', { reload: true });
});

if ($('#ResultsBlocks').length) {
  var p = getUrlParams();
  if (!p.rid) {
    var initPage = parseInt(p.page) || 1;
    var initServer = p.server || 0;
    if (initServer) $('#serversSelectResults input[type=radio][value="' + initServer + '"]').prop('checked', true);
    initAdaptiveSelects();
    renderResults(initPage, initServer);
    $('#serversSelectResults').on('change', 'input[type=radio]', function () {
      renderResults(1, $('#serversSelectResults input[type=radio]:checked').val() || 0);
    });
  } else if (p.rid) {
    renderResultsDetails(p.rid, p.server);
  }
}

if ($('#AdminTimeTableBody').length) {
  var urlParams = applyUrlParamsToFilters();
  initAdaptiveSelects();
  var handleFiltersChange = function () {
    importFiltergroup(this, '#serversGroupAdminTime, #serversSelectAdminTime');
    renderAdminTime(1);
  };
  $('#serversSelectAdminTime, #serversGroupAdminTime').on('change', 'input[type=radio], input[type=checkbox]', handleFiltersChange);
  $('#timePlayed').on('change', handleFiltersChange);
  var searchTimeout;
  $('#searchAdmin').on('input', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function () { renderAdminTime(1); }, 500);
  });
  window.adminTimeUrlParams = urlParams;
}

function renderResults(page, server) {
  page = page || 1; server = server || 0;
  updateUrlParams({ page: page > 1 ? page : null, server: server && server !== '0' ? server : null });
  ajaxRequest('getResults', { page: page, server: server }, {
    onSuccess: function (data) {
      if (!data.results || !data.results.length) {
        $('#ResultsBlocks').html('<div class="no-data">' + t('_noResults') + '</div>');
        $('#Pagination').html('');
        return;
      }
      renderResultsBlock(data.results);
      setupPagination('#Pagination', page, data.page_max, function (p) {
        renderResults(p, $('#serversSelectResults input[type=radio]:checked').val() || 0);
      });
    }
  });
}

function renderResultsBlock(data) {

  if (!$('#ResultsBlocksWrapper').length) {
    $('#ResultsBlocks').html('<div class="results__wrapper" id="ResultsBlocksWrapper"></div><div id="Pagination"></div>');
  }
  var html = data.flatMap(function (r) {
    return r.servers.map(function (s) {
      return '<div class="results__block' + (r.new ? ' new' : '') + '" data-id="' + r.id + '" data-server="' + s.server_id + '">' +
        '<div class="results__shadow"></div><span>' + r.period_label + '</span>' +
        '<small>' + t('_summaryOn') + ' ' + s.admins_count + ' ' + t('_onAdmins') + '</small>' +
        '<small>' + t('_server') + ': ' + s.server_name + '</small>' +
        '<div class="results__status ' + (s.award_taken ? 'completed' : '') + '">' +
        '<svg><use href="/resources/img/sprite.svg#bolt"></use></svg> ' + (s.award_taken ? t('_confirmed') : t('_notConfirmed')) +
        '</div></div>';
    });
  });
  $('#ResultsBlocksWrapper').html(html.join(''));
}

function renderResultsDetails(resultId, server) {
  updateUrlParams({ rid: resultId, server: server });
  ajaxRequest('getResultDetails', { id: resultId, server: server }, {
    onSuccess: function (data) { if (data.result) renderResultsTable(data); }
  });
}

function renderResultsTable(data) {
  if (!$('#ResultsBlocks .results__table').length && $('#ResultsBlocks').length) {
    var btn = data.award_taken ? '' : '<button class="results__award-button ml-auto active" data-id="' + data.result_id + '" data-server="' + data.server_id + '">' + t('_giveAwardsWarns') + '</button>';
    var tfoot = '';
    if (data.admins_skiped && Object.keys(data.admins_skiped).length) {
      var adminsArray = Array.isArray(data.admins_skiped) ? data.admins_skiped : Object.values(data.admins_skiped);
      var tfoot_admins = adminsArray.map(function (r) {
        checkAndRenderAvatar(r.checked_avatar, r.steamid);
        return adminLink(r.steamid, r.name);
      }).join(', ');
      tfoot = `
        <tfoot>
            <tr>
                <td colspan="10">
                    <div class="reason__info-noty" style="max-width: 100%; margin-bottom: .5rem;">
                        ${sprintf(t('_skippedAdmins'), tfoot_admins)}
                    </div>
                </td>
            </tr>
        </tfoot>
    `;
    }

    $('#ResultsBlocks').html(
      '<div class="results__table"><div class="results__header-title">' +
      '<a class="result__back"><svg><use href="/resources/img/sprite.svg#single-chevrone-left"></use></svg>' + tp('_Back') + '</a>' +
      '<h2 class="results__title">' + t('_weeklySummary') + btn + '</h2></div>' +
      '<div class="results__info"><span class="results__subtitle">' + t('_interval') + ' ' + data.period_label + '</span>•' +
      '<span class="results__subtitle">' + t('_server') + ': ' + data.server + '</span></div>' +
      '<div class="table-responsive"><table class="table"><thead><tr>' +
      '<th>' + t('_admin') + '</th><th>' + t('_position') + '</th><th>' + t('_played') + '</th><th>' + t('_sessions') + '</th>' +
      '<th>' + t('_bans') + '</th><th>' + t('_gags') + '</th><th>' + t('_reports') + '</th><th>' + t('_checks') + '</th><th>' + t('_award') + '</th>' +
      '</tr></thead><tbody id="ResultsTableBody"></tbody>' + tfoot + '</table></div><div id="Pagination"></div></div>');
    RenderingAvatar();
  }
  if ($('#ResultsTableBody').length) $('#ResultsTableBody').html(renderTableResults(data));
}

function renderTableResults(data) {
  var html = data.result.map(function (r) {
    checkAndRenderAvatar(r.checked_avatar, r.steamid);
    var cls = r.award_sum > 0 ? (data.award_taken ? 'results__taken ' : '') + 'results__yes' : 'results__no';
    var award = r.award_sum > 0 ? '+' + r.award_sum : '<svg><use href="/resources/img/sprite.svg#x"></use></svg>';
    return '<tr><td>' + adminCell(r.steamid, r.name) + '</td><td>' + r.group_name + '</td>' +
      '<td><div class="results__time"><div class="results__time-verdict ' + r.verdict_class + '">' + r.verdict + '</div><span>' + r.time_played + '</span></div></td>' +
      '<td>' + r.sessions_count + '</td><td>' + r.bans + '</td><td>' + r.mutes + ' / ' + r.gags + '</td><td>' + r.reports + '</td><td>' + r.checks + '</td>' +
      '<td><span class="' + cls + '">' + award + '</span></td></tr>';
  });
  RenderingAvatar();
  return html.join('');
}

if ($('#AdminsTableBody').length) {
  var p = getUrlParams();
  var initPage = parseInt(p.page) || 1;
  var initServer = p.server || 0;
  var initGroup = p.group || 0;
  if (initServer) $('#serversSelectAdmins input[type=radio][value="' + initServer + '"]').prop('checked', true);
  if (initGroup) $('#serversGroupAdmins input[type=radio][value="' + initGroup + '"]').prop('checked', true);
  initAdaptiveSelects();
  renderAdmins(initPage, initServer, initGroup);
  $('#serversSelectAdmins, #serversGroupAdmins').on('change', 'input[type=radio]', function () {
    renderAdmins(1, $('#serversSelectAdmins input[type=radio]:checked').val() || 0, $('#serversGroupAdmins input[type=radio]:checked').val() || 0);
  });
}

function renderAdminTime(page) {
  page = page || 1;
  var $sort = $('.table-sort.active');
  var params = {
    page: page,
    server: getCheckedVals('#serversSelectAdminTime input[type=checkbox]:checked'),
    group: getCheckedVals('#serversGroupAdminTime input[type=checkbox]:checked'),
    dateFrom: $('#dateStart').val() || null,
    dateTo: $('#dateEnd').val() || null,
    timePlayedEnable: $('#timePlayed').is(':checked') ? 1 : 0,
    searchAdmin: $('#searchAdmin').val() || '',
    sort: $sort.data('sort') || '',
    order: $sort.hasClass('rotated') ? 'asc' : 'desc'
  };
  updateUrlParams({ page: page > 1 ? page : null, server: params.server, group: params.group, dateFrom: params.dateFrom, dateTo: params.dateTo, timePlayed: params.timePlayedEnable === 1 ? '1' : null, searchAdmin: params.searchAdmin || null, sort: params.sort || null, order: params.sort ? params.order : null });
  ajaxRequest('getAdminsTime', params, {
    onSuccess: function (res) {
      $('#AdminTimeTableBody').html(renderTableAdminTime(res.data));
      setupPagination('#Pagination', page, res.page_max, renderAdminTime);
    }
  });
}

function openAdminSessionsModal(page) {
  page = page || 1;
  ajaxRequest('getSessions', {
    page: page,
    steamid: $('#sessions').data('steam'),
    dateFrom: $('#dateStart').val() || null,
    dateTo: $('#dateEnd').val() || null,
    server: getCheckedVals('#serversSelectAdminTime input[type=checkbox]:checked'),
    group: getCheckedVals('#serversGroupAdminTime input[type=checkbox]:checked')
  }, {
    onSuccess: function (res) {
      if (res.data) {
        var html = res.data.map(function (s) {
          return '<tr><td>' + s.server_name + '</td><td>' + s.date + '</td><td>' + s.connect_time + ' - ' + s.disconnect_time + '</td><td>' + s.played_time + '</td></tr>';
        }).join('');
        $('#sessions_adminName').text(res.lable);
        $('#sessions_list').html(html || '<tr><td colspan="4"><div class="no-data">' + t('_noData') + '</div></td></tr>');
        setupPagination('#sessions_pagination', page, res.page_max, openAdminSessionsModal);
      }
    }
  });
}

function openAdminChartsModal() {
  ajaxRequest('getCharts', {
    steamid: $('#charts').data('steam'),
    dateFrom: $('#dateStart').val() || null,
    dateTo: $('#dateEnd').val() || null,
    server: getCheckedVals('#serversSelectAdminTime input[type=checkbox]:checked'),
    group: getCheckedVals('#serversGroupAdminTime input[type=checkbox]:checked')
  }, {
    onSuccess: function (res) {
      if (!res || !res.categories) return;
      var series = [t('_bans'), t('_gags'), t('_reports'), t('_checks')];
      chart.updateSeries([
        { name: t('_playedHours'), data: res.playtime },
        { name: series[0], data: res.bans },
        { name: series[1], data: res.mutes_gags },
        { name: series[2], data: res.reports },
        { name: series[3], data: res.checks }
      ]);
      chart.updateOptions({
        xaxis: { categories: res.categories, labels: { style: { colors: 'var(--text-secondary)' } } },
        tooltip: { theme: 'dark', shared: true, intersect: false, y: { formatter: function (v, o) { return o.seriesIndex === 0 ? v.toFixed(1) + ' ' + t('_h') : v.toFixed(0); } } }
      });
      series.forEach(function (s) { chart.toggleSeries(s); });
    }
  });
}

function openGiveAwardWarnModal(steam) {
  var $modal = $('#addWarnAward');
  if (!$modal.length) return;
  $modal.data('steam', steam);
  $('#giveAwardWarnForm')[0].reset();
  $modal.addClass('visible');
}

$('#giveAwardWarnForm').on('submit', function (e) {
  e.preventDefault();
  var steam = $('#addWarnAward').data('steam');
  var promises = [];
  var opts = { reload: false, onSuccess: function (r) { return r; } };
  if ($('#warnEnable').is(':checked')) {
    promises.push(ajaxRequest('giveWarn', { steamid: steam, reason: $('#warnReason').val(), duration: $('#warnDuration').val() }, opts));
  }
  if ($('#awardEnable').is(':checked')) {
    promises.push(ajaxRequest('giveAward', { steamid: steam, amount: $('#awardCount').val(), reason: $('#awardReason').val() }, opts));
  }
  if (promises.length) {
    Promise.all(promises).then(function (results) {
      results.forEach(function (r) { if (r.success) noty(r.success, 'success'); else if (r.error) noty(r.error, 'error'); });
      renderAdminTime();
    });
  } else {
    noty(t('_selectOption'), 'warning');
  }
});

function renderAdmins(page, server, group) {
  page = page || 1; server = server || 0; group = group || 0;
  updateUrlParams({ page: page > 1 ? page : null, server: server && server !== '0' ? server : null, group: group && group !== '0' ? group : null });
  ajaxRequest('getAdmins', { page: page, server: server, group: group }, {
    onSuccess: function (res) {
      $('#AdminsTableBody').html(renderTableAdmins(res.data));
      setupPagination('#Pagination', page, res.page_max, function (p) {
        renderAdmins(p, $('#serversSelectAdmins input[type=radio]:checked').val() || 0, $('#serversGroupAdmins input[type=radio]:checked').val() || 0);
      });
      TippyContent()
    }
  });
}

function renderTableAdmins(data) {
  var html = data.map(function (a) {
    checkAndRenderAvatar(a.checked_avatar, a.steam);
    return '<tr><td>' + adminCell(a.steam, a.name) + '</td>' +
      '<td class="copy-btn" data-clipboard-text="' + a.discord + '">' + a.discord + '</td>' +
      '<td class="copy-btn" data-clipboard-text="' + a.telegram + '">' + a.telegram + '</td>' +
      '<td>' + a.group + '</td><td data-tippy-content="' + a.server.replace(/,/g, '<br>') + '" data-tippy-placement="top">' + t('_Servers_list') + '</td><td>' + a.date_added + '</td>' +
      '<td>' + adminLink(a.added_by, a.added_by_name) + '</td>' +
      '<td><div class="action-buttons">' +
      '<button class="edit-admin-btn" data-id="' + a.id + '">' + tp('_Change') + '</button>' +
      '<button class="button-delete delete-admin-btn" data-id="' + a.id + '">' + tp('_Delete_Action') + '</button>' +
      '</div></td></tr>';
  });
  RenderingAvatar();
  return html.join('');
}

function renderTableAdminTime(data) {

  var html = data.map(function (a) {
    var awardWarnBnt = a.access ? iconBtn('btn_open_award_warn', a.steam, 'plus', t('_giveAwardWarn')) : '';
    checkAndRenderAvatar(a.checked_avatar, a.steam);
    return '<tr><td>' + adminCell(a.steam, a.name) + '</td>' +
      '<td>' + a.group_name + '</td><td>' + a.time_played + '</td><td>' + a.bans + '</td><td>' + a.gags + '/' + a.mutes + '</td><td>' + a.checks + '</td><td>' + a.reports + '</td>' +
      '<td><div class="action-buttons">' +
      iconBtn('btn_open_sessions', a.steam, 'timer', t('_sessions')) +
      iconBtn('btn_open_charts', a.steam, 'chart', t('_graphics')) +
      awardWarnBnt +
      '</div></td></tr>';
  });
  RenderingAvatar();
  return html.join('');
}

if ($('#WarnsTableBody').length) renderWarns();

function renderWarns(page) {
  page = page || 1;
  ajaxRequest('getWarns', { page: page }, {
    onSuccess: function (res) {
      $('#WarnsTableBody').html(renderTableWarns(res.data));
      setupPagination('#Pagination', page, res.page_max, renderWarns);
    }
  });
}

function renderTableWarns(data) {
  var html = data.map(function (w) {
    checkAndRenderAvatar(w.checked_avatar, w.steamid);
    return '<tr><td>' + adminLink(w.steamid, w.name) + '</td><td>' + w.reason + '</td><td>' + w.createtime + '</td><td>' + w.time + '</td></tr>';
  });
  RenderingAvatar();
  return html.join('');
}

if ($('#logsTableBody').length) renderLogs();
$('#selectDate').on('change', 'input[type=radio]', function () {
  renderLogs(1, $('#selectDate input[type=radio]:checked').val() || null);
});

function renderLogs(page, date) {
  page = page || 1; date = date || null;
  updateUrlParams({ page: page > 1 ? page : null, date: date || null });
  ajaxRequest('getLogs', { page: page, date: date }, {
    onSuccess: function (res) {
      if (!res.data || !res.data.length) {
        $('#logsTableBody').html('<div class="no-data">' + t('_noResults') + '</div>');
        $('#Pagination').html('');
        return;
      }
      $('#logsTableBody').html(renderTableLogs(res.data));
      setupPagination('#Pagination', page, res.page_max, renderLogs);
    }
  });
}

function renderTableLogs(data) {
  var html = data.map(function (l) {
    return '<tr><td>' + l.formatted_time + '</td><td>' + l.message + '</td></tr>';
  });
  return html.join('');
}

document.addEventListener('click', function (e) {
  var target = e.target;

  var editAdminBtn = target.closest('.edit-admin-btn');
  if (editAdminBtn) {
    ajaxRequest('getAdmin', { id: editAdminBtn.dataset.id }, {
      onSuccess: function (data) {
        if (!data.id) return noty(t('_adminNotFound'), 'error');
        var $form = $('#editAdminForm');
        $form.attr('data-id', data.id);
        $form.find('input[name="group"][value="' + data.group + '"]').prop('checked', true);
        $form.find('input[name="server[]"]').prop('checked', false);
        (data.server || []).forEach(function (id) { $form.find('input[name="server[]"][value="' + id + '"]').prop('checked', true); });
        $form.find('input[name="steam"]').val(data.steam);
        $form.find('input[name="telegram"]').val(data.telegram);
        $form.find('input[name="discord"]').val(data.discord);
        initAdaptiveSelects();
        $('#editAdmin').addClass('visible');
      }
    });
    return;
  }

  var deleteAdminBtn = target.closest('.delete-admin-btn');
  if (deleteAdminBtn) { confirmDelete(t('_deletingAdmin'), t('_deletingAdminText'), function () { ajaxRequest('deleteAdmin', { id: deleteAdminBtn.dataset.id }); }); return; }

  var editAwardBtn = target.closest('.edit-award-btn');
  if (editAwardBtn) {
    ajaxRequest('getAward', { id: editAwardBtn.dataset.id }, {
      onSuccess: function (data) {
        if (!data.id) return noty(t('_awardNotFound'), 'error');
        $('#editAwardForm').attr('data-id', data.id).find('input[name="time"]').val(data.time);
        $('#editAwardForm input[name="amount"]').val(data.amount);
        $('#editAward').addClass('visible');
      }
    });
    return;
  }

  var deleteAwardBtn = target.closest('.delete-award-btn');
  if (deleteAwardBtn) { confirmDelete(t('_deletingAward'), t('_deletingAwardText'), function () { ajaxRequest('deleteAward', { id: deleteAwardBtn.dataset.id }); }); return; }

  var deleteAccessBtn = target.closest('.delete-access-btn');
  if (deleteAccessBtn) { confirmDelete(t('_deletingAccess'), t('_deletingAccessText'), function () { ajaxRequest('deleteAccess', { id: deleteAccessBtn.dataset.id }); }); return; }

  var deleteServerBtn = target.closest('.delete-server-btn');
  if (deleteServerBtn) { confirmDelete(t('_deletingServer'), t('_deletingServerText'), function () { ajaxRequest('deleteServer', { id: deleteServerBtn.dataset.id }); }); return; }

  var resultBlock = target.closest('.results__block');
  if (resultBlock) return renderResultsDetails(resultBlock.dataset.id, resultBlock.dataset.server);

  var resultBack = target.closest('.result__back');
  if (resultBack) { updateUrlParams({ rid: null, server: null }); return renderResults(1, $('#serversSelectResults input[type=radio]:checked').val() || 0); }

  var awardButton = target.closest('.results__award-button');
  if (awardButton) {
    openDialog({
      title: t('_grantingAwards'), message: t('_grantingAwardsText'), confirmText: t('_Deliver_Action'), cancelText: t('_Cancel_Action'),
      onConfirm: function () {
        ajaxRequest('grantAwards', { id: awardButton.dataset.id, server: awardButton.dataset.server }, {
          onSuccess: function (data) {
            if (data.success) { noty(data.success, 'success'); $('#ResultsTableBody .results__yes').addClass('results__taken'); }
            else noty(data.error, 'error');
          }
        });
      }
    });
    return;
  }

  var tableSort = target.closest('.table-sort');
  if (tableSort) {
    e.preventDefault();
    var $sort = $(tableSort), wasActive = $sort.hasClass('active'), wasRotated = $sort.hasClass('rotated');
    $('.table-sort').removeClass('active rotated');
    $sort.addClass('active');
    if (wasActive && !wasRotated) $sort.addClass('rotated');
    if ($('#AdminTimeTableBody').length) { renderAdminTime(1); }
    return;
  }

  var btnSessions = target.closest('.btn_open_sessions');
  if (btnSessions) {
    var steam = $(btnSessions).data('steam');
    if (steam) $('#sessions').data('steam', steam);
    openAdminSessionsModal();
    $('#sessions').addClass('visible');
    return;
  }

  var btnCharts = target.closest('.btn_open_charts');
  if (btnCharts) {
    var steam = $(btnCharts).data('steam');
    if (steam) $('#charts').data('steam', steam);
    openAdminChartsModal();
    $('#charts').addClass('visible');
    return;
  }

  var btnAwardWarn = target.closest('.btn_open_award_warn');
  if (btnAwardWarn) { openGiveAwardWarnModal($(btnAwardWarn).data('steam')); return; }
});

$(document).ready(function () {
  if (window.VanillaCalendarPro) {
    var Calendar = window.VanillaCalendarPro.Calendar;
    var formatDate = function (d) { return d ? d.split('-').reverse().join('.') : ''; };
    var pad = function (n) { return String(n).padStart(2, '0'); };
    var today = new Date(), diff = today.getDay() === 0 ? -6 : 1 - today.getDay();
    var monday = new Date(today); monday.setDate(today.getDate() + diff);
    var sunday = new Date(monday); sunday.setDate(monday.getDate() + 6);
    var toDateStr = function (d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); };
    var urlP = window.adminTimeUrlParams || {};
    var startDate = urlP.dateFrom || toDateStr(monday);
    var endDate = urlP.dateTo || toDateStr(sunday);
    var startD = new Date(startDate);
    var weekRange = { start: startDate, end: endDate, month: startD.getMonth(), year: startD.getFullYear() };
    var updateDates = function (dates) {
      var $start = $('#dateStart'), $end = $('#dateEnd');
      if (!$start.length || !$end.length) return;
      if (!dates || !dates.length) { $start.val(''); $end.val(''); return; }
      $start.val(dates[0]); $end.val(dates[dates.length - 1]);
      $('#adminTimePeriod').text(formatDate($start.val()) + ' - ' + formatDate($end.val()));
      renderAdminTime(urlP.page || 1);
      urlP.page = null;
    };
    new Calendar('#VanillaCalendar', {
      locale: lang.toLowerCase(), selectionDatesMode: 'multiple-ranged',
      selectedDates: [weekRange.start + ':' + weekRange.end], selectedMonth: weekRange.month, selectedYear: weekRange.year,
      onInit: function (self) { updateDates(self.context.selectedDates); },
      onClickDate: function (self) { updateDates(self.context.selectedDates); }
    }).init();
  }
  if (typeof ApexCharts !== 'undefined') {
    chart = new ApexCharts(document.querySelector('#ApexChart'), {
      chart: { type: 'area', height: 400, zoom: { enabled: true }, foreColor: 'var(--text-secondary)', toolbar: { show: false } },
      colors: ['#00E396', '#FF4560', '#775DD0', '#FEB019', '#008FFB'],
      dataLabels: { enabled: false }, stroke: { curve: 'smooth', width: 3 },
      fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
      series: [], xaxis: { categories: [], labels: { style: { colors: 'var(--text-secondary)' } } },
      yaxis: { labels: { show: true, style: { colors: 'var(--text-secondary)' }, formatter: function (v) { return v.toFixed(0); } } },
      legend: { position: 'bottom', horizontalAlign: 'center', labels: { colors: 'var(--text-secondary)' }, fontSize: '12px', offsetY: -5, markers: { width: 10, height: 10 } },
      grid: { show: true, borderColor: '#2d2d2d', strokeDashArray: 3, padding: { left: 10, right: 10 } },
      markers: { size: 4, hover: { size: 6 } },
      tooltip: { theme: 'dark', shared: true, intersect: false, y: { formatter: function (v, o) { return o.seriesIndex === 0 ? v.toFixed(1) + ' ' + t('_h') : v.toFixed(0); } } }
    });
    chart.render();
  }
});

function TippyContent() {
  tippy('[data-tippy-content]', {
    placement: "right",
    arrow: false,
    animation: "shift-away",
    theme: "neo",
    allowHTML: true,
  });
}

function sprintf(fmt) {
  var args = Array.prototype.slice.call(arguments, 1);
  var i = 0;
  return fmt.replace(/%[sd]/g, function () { return args[i++]; });
}