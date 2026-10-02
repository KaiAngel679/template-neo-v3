const LS = {
  get: (key) => localStorage.getItem('leaderboard_' + key),
  set: (key, val) => localStorage.setItem('leaderboard_' + key, val)
};

const LEADERBOARD_TIPPY_CFG = {
  arrow: false,
  animation: "shift-away",
  theme: "neo",
  allowHTML: true,
  appendTo: (reference) => reference.closest('#leaderboardTableBody') || document.body,
  popperOptions: { strategy: 'absolute' }
};

window.__lbTippyInstances = window.__lbTippyInstances || [];

$(document).on('change', 'input[name="server"], input[name="filter"], input[name="clear_banned"]', () => Render());
$(document).on('change', 'input[name="server"]', () => RenderUserStats());

$(document).ready(function () {
  let url = new URLSearchParams(document.location.search);
  let page = url.get('page');
  let server = url.get('server');
  if (page) page = parseInt(page); else page = 1;
  if (server) $(`input[name="server"][value="${server}"]`).prop('checked', true);
  const filter = LS.get('filter');
  const clearBanned = LS.get('clear_banned');
  if (filter) $(`input[name="filter"][value="${filter}"]`).prop('checked', true);
  if (clearBanned !== null) $('input[name="clear_banned"]').prop('checked', clearBanned === '1');
  Render(page);
  RenderUserStats();
  initAdaptiveSelects();
});

function reinitLeaderboardTippy() {
  if (typeof window.tippy !== 'function') return;
  if (Array.isArray(window.__lbTippyInstances)) {
    window.__lbTippyInstances.forEach((i) => i?.destroy?.());
  }
  window.__lbTippyInstances = [];
  document.querySelectorAll('#contentTable [data-tippy-content]').forEach((el) => {
    if (el && el._tippy && typeof el._tippy.destroy === 'function') {
      el._tippy.destroy();
    }
  });
  window.__lbTippyInstances = window.tippy('#contentTable [data-tippy-content]', {
    ...LEADERBOARD_TIPPY_CFG,
    placement: (reference) => reference.getAttribute('data-tippy-placement') || 'top'
  });
}

function Render(page = 1) {
  const server = $('input[name="server"]:checked').val();
  const filter = $('input[name="filter"]:checked').val();
  const clear_banned = $('input[name="clear_banned"]').is(':checked') ? 1 : 0;
  LS.set('filter', filter);
  LS.set('clear_banned', clear_banned);
  if (server == 0 && page == 1) {
    history.replaceState(null, '', location.pathname);
  } else {
    history.pushState(null, '', `?server=${server}&page=${page}`);
  }
  $.ajax({
    type: "POST",
    url: location.href,
    dataType: "json",
    data: { getLeaderboardList: true, page: page, server: server, filter: filter, clear_banned: clear_banned },
    beforeSend: function () {
      if ($('#leaderboardTableBody').length === 0) {
        $('#contentTable').html('<table class="table"><tbody>' + fakeLoader() + '</tbody></table>');
      } else {
        $('#leaderboardTableBody').html(fakeLoader());
      }
    },
    success: function (response) {
      if (response.list.length > 0) {
        if ($('#leaderboardTableBody').length === 0) {
          $('#contentTable').html('<table class="table"><tbody>' + fakeLoader() + '</tbody></table>');
        } else {
          $('#leaderboardTableBody').html(renderTableBody(response));
        }
        reinitLeaderboardTippy();
      } else {
        $('#contentTable').html('<div class="no-data">' + get_translate_phrase('_nothingFound') + '</div>');
        $('#contentPagination').html('');
      }
      const pagination = $('#contentPagination');
      pagination.html(renderPagination(page, response.page_max));
      pagination.off('click').on('click', 'a[data-page]', function () {
        const newPage = parseInt($(this).data('page'));
        if (!isNaN(newPage)) {
          Render(newPage);
        }
      });
    },
    error: function () { return false; }
  });
}

function RenderUserStats() {
  const server = $('input[name="server"]:checked').val();
  $.ajax({
    type: "POST",
    url: location.href,
    dataType: "json",
    data: { getUserStats: true, server: server },
    beforeSend: function () {
      $('#userStatsContainer').html('<div class="skeleton--default" style="height: 175px;"></div>');
    },
    success: function (response) {
      if (response && response.steamid) {
        $('#userStatsContainer').html(RenderUserStatsBlock(response));
      } else {
        $('#userStatsContainer').html(emptyUserStatsBlock());
      }
    },
    error: function () { return false; }
  });
}

function renderTableBody(data) {
  let rows = [];
  data.list.forEach((item, index) => {
    let vip = '', admin = '', banned = '';
    if (item.vip) {
      vip = `<svg class="vip-svg" data-tippy-content="${item.vip_group}" data-tippy-placement="top"><use href="/resources/img/sprite.svg#diamond"></use></svg>`;
    }
    if (item.admin) {
      admin = `<svg class="admin-svg" data-tippy-content="${item.admin_groups}" data-tippy-placement="top"><use href="/resources/img/sprite.svg#shiedl-bold"></use></svg>`;
    }
    if (item.banned) {
      banned = `<svg class="banned-svg" data-tippy-content="${get_translate_module_phrase('module_page_leaderboard', '_bannedPlayer')}" data-tippy-placement="top"><use href="/resources/img/sprite.svg#user-block"></use></svg>`;
    }
    const badges = (banned || vip || admin) ? `<div class="leaderboard__badges">${banned} ${vip} ${admin}</div>` : '';
    rows.push(`
      <tr class="pointer ${item.top_class ? 'leaderboard__table-top-background-' + item.top_class : ''}" onclick="window.open('${item.link}', '_blank');">
        <td>
          <div class="leaderboard__table-place ${item.top_class ? 'leaderboard__table-top-img-' + item.top_class : ''}">
            ${item.place}
          </div>
        </td>
        <td><img data-src="${item.faceit}" alt="" class="leaderboard__table-faceit lazy" id="faceit" faceit_level_img="${item.steamid}"></td>
        <td>
          <div class="leaderboard__table-user">
            <img class="lazy top_players_avatar ${item.top_class ? 'leaderboard__table-top-border-' + item.top_class : ''}" data-src="${item.avatar}" id="avatar" avatarid="${item.steamid}">
            <div class="user_online_status" style="${item.online}"></div>
            <span class="leaderboard__table-nickname">
              <span class="leaderboard__nick-hidden ${item.banned ? 'banned' : ''} ${item.top_class ? 'leaderboard__table-top-nick-' + item.top_class : ''}" id="name" nameid="${item.steamid}">${item.name}</span> ${badges}
            </span>
          </div>
        </td>
        <td>${item.rank}</td>
        ${data.premier_ranks ? '' : '<td class="leaderboard__table-grey-text">' + item.value + '</td>'}
        <td class="leaderboard__table-grey-text">${item.kills}</td>
        <td class="leaderboard__table-grey-text">${item.deaths}</td>
        <td class="leaderboard__table-grey-text">${item.kd ?? 0}</td>
        <td class="leaderboard__table-grey-text">${item.headshots}</td>
        <td class="leaderboard__table-grey-text">${item.playtime}</td>
        <td class="leaderboard__table-grey-text">${formatLastConnect(item.lastconnect)}</td>
      </tr>`);
    checkAndRenderAvatar(item.checked_avatar, item.steamid);
    checkAndRenderFaceit(item.checked_faceit, item.steamid);
  });
  RenderingAvatar();
  RenderingFaceit();
  return rows.join('');
}

function RenderUserStatsBlock(data) {
  let points = `
    <div class="leaderboard__my-stat-block">
      <span class="leaderboard__my-stat-title">${get_translate_module_phrase('module_page_leaderboard', '_points')}</span>
      <span class="leaderboard__my-stat-value">${data.points ?? 0}</span>
    </div>
  `;
  checkAndRenderAvatar(data.checked_avatar, data.steamid);
  RenderingAvatar();
  return `
    <div class="leaderboard__my-stat pointer">
      <div class="leaderboard__my-stat-header">
        <div class="leaderboard__my-stat-text">
          <div class="leaderboard__my-stat-hello-text">
              ${get_translate_module_phrase('module_page_leaderboard', '_hello')}
              <span class="leaderboard__my-stat-nickname" id="name" nameid="${data.steamid}">
                ${data.name ?? get_translate_module_phrase('module_page_leaderboard', '_guest')}
              </span>
          </div>
          <div class="leaderboard__my-stat-description">
            ${get_translate_module_phrase('module_page_leaderboard', '_yourGameStat')}
          </div>
        </div>
        ${data.rank}
      </div>
      <div class="leaderboard__my-stat-info">
        <div class="leaderboard__my-stat-line-1">
          <img data-src="${data.avatar}" id="avatar" avatarid="${data.steamid}" alt="" class="leaderboard__my-stat-avatar lazy">
          <div class="leaderboard__my-stat-block">
            <span class="leaderboard__my-stat-title">${get_translate_module_phrase('module_page_leaderboard', '_place')}</span>
            <span class="leaderboard__my-stat-value">${data.place ?? 0}</span>
          </div>
          ${data.premier_rank ? '' : points}
          <div class="leaderboard__my-stat-block">
            <span class="leaderboard__my-stat-title">K/D</span>
            <span class="leaderboard__my-stat-value">${data.kd ?? 0}</span>
          </div>
        </div>
          <div class="leaderboard__my-stat-line-2">
            <div class="leaderboard__my-stat-block">
              <span class="leaderboard__my-stat-title">${get_translate_module_phrase('module_page_leaderboard', '_kills')}</span>
              <span class="leaderboard__my-stat-value">${data.kills ?? 0}</span>
            </div>
            <div class="leaderboard__my-stat-block">
              <span class="leaderboard__my-stat-title">${get_translate_module_phrase('module_page_leaderboard', '_deaths')}</span>
              <span class="leaderboard__my-stat-value">${data.deaths ?? 0}</span>
            </div>
            <div class="leaderboard__my-stat-block">
              <span class="leaderboard__my-stat-title">${get_translate_module_phrase('module_page_leaderboard', '_inHead')}</span>
              <span class="leaderboard__my-stat-value">${data.headshots ?? 0}</span>
            </div>
          </div>
      </div>
      <div class="leaderboard__ghost" style="${data.havent_play ? '' : 'display: none;'}">
        ${get_translate_module_phrase('module_page_leaderboard', '_haventPlay')}
      </div>
    </div>
  `;
}

function emptyUserStatsBlock() {
  return `
    <div class="leaderboard__my-stat-auth">
      <div class="leaderboard__my-stat-auth-text">
        <span>${get_translate_module_phrase('module_page_leaderboard', '_logIn')}</span>
      </div>
      <button class="width-100 active" onclick="location.href='?auth=login'">
        <svg><use href="/resources/img/sprite.svg#steam"></use></svg>
        ${get_translate_phrase('_Auth_login')}
      </button>
    </div>
  `;
}

function fakeLoader() {
  let rows = [];
  for (let i = 0; i < 13; i++) {
    rows.push(`
      <tr class="pointer skeleton--default">
        <td>
          <div class="leaderboard__table-place"></div>
        </td>
        <td>
          <div class="leaderboard__table-user">
            <span class="leaderboard__table-nickname">/////////////////////////////////////////</span>
          </div>
        </td>
        <td style="width: 112px">0</td>
        <td class="leaderboard__table-grey-text">0</td>
        <td class="leaderboard__table-grey-text">0</td>
        <td class="leaderboard__table-grey-text">0</td>
        <td class="leaderboard__table-grey-text">0</td>
        <td class="leaderboard__table-grey-text">0</td>
        <td class="leaderboard__table-grey-text">0</td>
      </tr>
    `);
  }
  return rows.join('');
}

function checkAndRenderAvatar(check, steamid) {
  if (check === 1) {
    avatar.push(steamid);
  }
}
function checkAndRenderFaceit(check, steamid) {
  if (check === 1) {
    faceit.push(steamid);
  }
}

$('#resetFilter').on('click', function (e) {
  e.preventDefault();
  const $firstFilter = $('input[name="filter"][value="0"]').first();
  if ($firstFilter.length) {
    $firstFilter.prop('checked', true).trigger('change');
  }
});

function formatLastConnect(timestamp) {
  if (!timestamp) return '';
  const date = new Date(timestamp * 1000);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${day}.${month}.${year} ${get_translate_phrase('_on')} ${hours}:${minutes}`;
}