function atGlobalSearchFormatAmount(value, currency) {
    var num = Number(value);
    var formatted = Number.isFinite(num) ? num : 0;
    return String(formatted) + (currency || '');
}

function atGlobalSearchPunishTypeIcon(punishType) {
    const icons = ['block', 'mute', 'chat-slash', 'face-mute'];
    const phrases = ['_at_gameBlock', '_at_micBlock', '_at_chatBlock', '_at_SilenceBlock'];
    const idx = Number(punishType);
    const safeIdx = idx >= 0 && idx < icons.length ? idx : 0;
    const label = get_translate_module_phrase('module_page_atools', phrases[safeIdx]);

    return `<span data-tippy-content="${label}" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#${icons[safeIdx]}"></use></svg></span>`;
}

function renderGlobalSearchSectionBlock(title, total, sectionUrl, query, contentHtml, empty) {
    var showAll = '';
    if (total > 0 && sectionUrl) {
        showAll = '<span class="at__global-search-line"></span>' +
            '<a href="' + sectionUrl + '" class="at__global-search-result-title-show-all">' +
            get_translate_module_phrase('module_page_atools', '_at_globalSearchShowAll') + ' (' + total + ')' +
            '</a>';
    }

    var body;
    if (empty) {
        body = '<div class="at__global-search-result-content-not-found">' +
            '<svg><use href="/resources/img/sprite.svg#box-empty"></use></svg>' +
            '<div class="at__global-search-result-content-not-found-text">' +
            get_translate_module_phrase('module_page_atools', '_at_globalSearchResultsNotFound') +
            '</div></div>';
    } else {
        body = '<div class="at__global-search-result-content">' + contentHtml + '</div>';
    }

    return '<div class="at__global-search-result-block">' +
        '<div class="at__global-search-result-title">' +
        '<div class="at__global-search-result-title-text">' + title + '</div>' +
        showAll +
        '</div>' + body +
        '</div>';
}

function renderGlobalSearchSkeletonSections() {
    return '<div class="at__global-search-result-block">' +
        '<div class="at__global-search-result-title">' +
        '<div class="at__global-search-result-title-text skeleton--default"></div>' +
        '</div>' +
        '<div class="at__global-search-result-content-not-found skeleton--default"></div>' +
        '</div>';
}

function renderGlobalSearchPlayerCard(player) {
    if (!player) {
        return '';
    }

    return `<a href="${player.profile_url}" target="_blank" class="at__global-search-user-results-block">` +
        '<div class="at__global-search-user-avatar">' +
        `<img id="avatar" avatarid="${player.steamid}" src="${player.avatar}" alt="">` +
        '</div>' +
        '<div class="at__global-search-user-info">' +
        `<span class="at__global-search-user-info-name" id="name" nameid="${player.steamid}">${player.name}</span>` +
        `<span class="at__global-search-user-info-steamid copy-btn" data-clipboard-text="${player.steamid}">${player.steamid}</span>` +
        '</div></a>';
}

function renderGlobalSearchCandidates(candidates) {
    if (!candidates || !candidates.length) {
        return '';
    }

    return candidates.map(function (item) {
        return `<a class="at__global-search-user-results-block at__global-search-candidate" data-steamid="${item.steamid}">` +
            '<div class="at__global-search-user-avatar">' +
            `<img id="avatar" avatarid="${item.steamid}" src="${item.avatar}" alt="">` +
            '</div>' +
            '<div class="at__global-search-user-info">' +
            `<span class="at__global-search-user-info-name" id="name" nameid="${item.steamid}">${item.name}</span>` +
            `<span class="at__global-search-user-info-steamid">${item.steamid}</span>` +
            '</div></a>';
    }).join('');
}

function renderGlobalSearchNotFound(message) {
    return '<div class="at__global-search-result-block">' +
        '<div class="at__global-search-result-content-not-found">' +
        '<svg><use href="/resources/img/sprite.svg#box-empty"></use></svg>' +
        `<div class="at__global-search-result-content-not-found-text">${message}</div>` +
        '</div></div>';
}

function renderGlobalSearchPunishRow(item) {
    var servers = Array.isArray(item.servers) ? item.servers.join('<br>') : '';
    var typeCell = atGlobalSearchPunishTypeIcon(item.punish_type);

    return '<tr>' +
        '<td>' + typeCell + '</td>' +
        `<td><span data-tippy-content="${item.created_time}" data-tippy-placement="top">${item.created_at}</span></td>` +
        '<td><div class="at__table-user">' +
        `<a href="/profiles/${item.offender_steamid}/?search=1" target="_blank">` +
        `<img id="avatar" avatarid="${item.offender_steamid}" src="${item.offender_avatar}" alt="">` +
        `<span id="name" nameid="${item.offender_steamid}">${item.offender_name}</span>` +
        '</a></div></td>' +
        '<td><div class="at__table-reason"><span>' + escapeHtml(item.reason || '') + '</span></div></td>' +
        '<td><div class="at__table-user">' +
        (item.admin_steamid
            ? `<a href="/profiles/${item.admin_steamid}/?search=1" target="_blank">` +
                `<img id="avatar" avatarid="${item.admin_steamid}" src="${item.admin_avatar}" alt="">` +
                `<span id="name" nameid="${item.admin_steamid}">${item.admin_name}</span></a>`
            : `<span>${item.admin_name}</span>`) +
        '</div></td>' +
        `<td data-tippy-content="${servers}" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#servers"></use></svg></td>` +
        `<td><div class="at__expire">${item.duration_text}</div></td>` +
        `<td><div class="at__expire ${item.expire_class}">${item.expire_text}</div></td>` +
        '</tr>';
}

function renderGlobalSearchPunishmentsSection(section, query) {
    var title = get_translate_module_phrase('module_page_atools', '_at_navPunishments');
    var rows = section.items || [];
    if (!rows.length) {
        return renderGlobalSearchSectionBlock(title, 0, section.section_url, query, '', true);
    }

    var table = '<div class="table-responsive"><table class="table"><thead><tr>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_punishType') + '</th>' +
        '<th>' + get_translate_phrase('_Date') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_offender') + '</th>' +
        '<th>' + get_translate_phrase('_Reason') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_admin') + '</th>' +
        '<th data-tippy-content="' + get_translate_module_phrase('module_page_atools', '_at_servers') + '" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#servers"></use></svg></th>' +
        '<th>' + get_translate_phrase('_Term') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_expiring') + '</th>' +
        '</tr></thead><tbody>' + rows.map(renderGlobalSearchPunishRow).join('') + '</tbody></table></div>';

    return renderGlobalSearchSectionBlock(title, section.total || 0, section.section_url, query, table, false);
}

function renderGlobalSearchChecksSection(section, query) {
    var title = get_translate_module_phrase('module_page_atools', '_at_navChecks');
    var items = section.items || [];
    if (!items.length) {
        return renderGlobalSearchSectionBlock(title, 0, section.section_url, query, '', true);
    }

    var rows = items.map(function (check) {
        var contactHtml = check.contact && check.contact !== get_translate_phrase('_absent')
            ? '<div class="at__expire at__contact copy-btn" data-clipboard-text="' + escapeHtml(check.contact) + '">' + escapeHtml(check.contact) + '</div>'
            : '<div class="at__expire at__contact">' + escapeHtml(check.contact || '') + '</div>';

        return '<tr>' +
            '<td>' + check.id + '</td>' +
            '<td><div class="at__table-user">' +
            `<a href="/profiles/${check.player_steamid}/?search=1" target="_blank">` +
            `<img id="avatar" avatarid="${check.player_steamid}" src="${check.player_avatar}" alt="">` +
            `<span id="name" nameid="${check.player_steamid}">${check.player_name}</span></a></div></td>` +
            '<td><div class="at__table-user">' +
            `<a href="/profiles/${check.admin_steamid}/?search=1" target="_blank">` +
            `<img id="avatar" avatarid="${check.admin_steamid}" src="${check.admin_avatar}" alt="">` +
            `<span id="name" nameid="${check.admin_steamid}">${check.admin_name}</span></a></div></td>` +
            `<td>${check.datestart}</td>` +
            `<td>${check.date_end}</td>` +
            `<td><div class="at__table-reason"><span>${check.verdict}</span></div></td>` +
            `<td>${contactHtml}</td>` +
            '</tr>';
    }).join('');

    var table = '<div class="table-responsive"><table class="table"><thead><tr>' +
        '<th>ID</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_checkedPlayer') + '</th>' +
        '<th>' + get_translate_phrase('_Admin') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_checkStart') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_checkEnd') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_checkVerdict') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_contact') + '</th>' +
        '</tr></thead><tbody>' + rows + '</tbody></table></div>';

    return renderGlobalSearchSectionBlock(title, section.total || 0, section.section_url, query, table, false);
}

function renderGlobalSearchReportsSection(section, query) {
    var title = get_translate_module_phrase('module_page_atools', '_at_sendedReports');
    var items = section.items || [];
    if (!items.length) {
        return renderGlobalSearchSectionBlock(title, 0, section.section_url, query, '', true);
    }

    var rows = items.map(function (report) {
        return '<tr>' +
            `<td>${report.time}</td>` +
            '<td><div class="at__table-user">' +
            `<a href="/profiles/${report.player_steamid}/?search=1" target="_blank">` +
            `<img id="avatar" avatarid="${report.player_steamid}" src="${report.player_avatar}" alt="">` +
            `<span id="name" nameid="${report.player_steamid}">${report.player_name}</span></a></div></td>` +
            `<td><div class="at__table-reason"><span>${escapeHtml(report.reason)}</span></div></td>` +
            `<td>${report.kd}</td>` +
            `<td>${report.status_label}</td>` +
            '<td>' + (report.admin_steamid
                ? '<div class="at__table-user">' +
                    `<a href="/profiles/${report.admin_steamid}/?search=1" target="_blank">` +
                    `<img id="avatar" avatarid="${report.admin_steamid}" src="${report.admin_avatar}" alt="">` +
                    `<span id="name" nameid="${report.admin_steamid}">${report.admin_name}</span></a></div>`
                : `<span>${report.admin_name}</span>`) +
            '</td>' +
            `<td><div class="at__table-reason"><span>${escapeHtml(report.verdict)}</span></div></td>` +
            '</tr>';
    }).join('');

    var table = '<div class="table-responsive"><table class="table"><thead><tr>' +
        '<th>' + get_translate_phrase('_Date') + '</th>' +
        '<th>' + get_translate_phrase('_Player') + '</th>' +
        '<th>' + get_translate_phrase('_Reason') + '</th>' +
        '<th>K/D</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_statusReport') + '</th>' +
        '<th>' + get_translate_phrase('_Admin') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_verdictReport') + '</th>' +
        '</tr></thead><tbody>' + rows + '</tbody></table></div>';

    return renderGlobalSearchSectionBlock(title, section.total || 0, section.section_url, query, table, false);
}

function renderGlobalSearchAdminsSection(section, query) {
    var title = get_translate_module_phrase('module_page_atools', '_at_navAdmins');
    var items = section.items || [];
    if (!items.length) {
        return renderGlobalSearchSectionBlock(title, 0, section.section_url, query, '', true);
    }

    var rows = items.map(function (admin) {
        var serverTooltip = Array.isArray(admin.servers)
            ? admin.servers.map(function (server) {
                return escapeHtml(server.name || '');
            }).filter(Boolean).join('<br>')
            : '';

        return '<tr>' +
            '<td><div class="at__table-user">' +
            `<a href="/profiles/${admin.steamid}/?search=1" target="_blank">` +
            `<img id="avatar" avatarid="${admin.steamid}" src="${admin.avatar}" alt="">` +
            `<span id="name" nameid="${admin.steamid}">${admin.name}</span></a></div></td>` +
            `<td>${escapeHtml(admin.group_name || '')}</td>` +
            `<td data-tippy-content="${serverTooltip}" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#servers"></use></svg></td>` +
            `<td><div class="at__expire">${admin.expires_text}</div></td>` +
            '</tr>';
    }).join('');

    var table = '<div class="table-responsive"><table class="table"><thead><tr>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_admin') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_serverGroup') + '</th>' +
        '<th data-tippy-content="' + get_translate_module_phrase('module_page_atools', '_at_servers') + '" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#servers"></use></svg></th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_expire') + '</th>' +
        '</tr></thead><tbody>' + rows + '</tbody></table></div>';

    return renderGlobalSearchSectionBlock(title, section.total || 0, section.section_url, query, table, false);
}

function renderGlobalSearchVipSection(section, query) {
    var title = get_translate_module_phrase('module_page_atools', '_at_navPrivileges');
    var items = section.items || [];
    if (!items.length) {
        return renderGlobalSearchSectionBlock(title, 0, section.section_url, query, '', true);
    }

    var rows = items.map(function (vip) {
        return '<tr>' +
            '<td><div class="at__table-user">' +
            `<a href="/profiles/${vip.steamid}/?search=1" target="_blank">` +
            `<img id="avatar" avatarid="${vip.steamid}" src="${vip.avatar}" alt="">` +
            `<span id="name" nameid="${vip.steamid}">${vip.name}</span></a></div></td>` +
            `<td>${vip.group_name}</td>` +
            `<td data-tippy-content="${vip.server_tooltip}" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#servers"></use></svg></td>` +
            `<td><div class="at__expire">${vip.expires_text}</div></td>` +
            `<td><div class="at__expire">${vip.remaining_text}</div></td>` +
            '</tr>';
    }).join('');

    var table = '<div class="table-responsive"><table class="table"><thead><tr>' +
        '<th>' + get_translate_phrase('_Player') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_vipGroup') + '</th>' +
        '<th data-tippy-content="' + get_translate_module_phrase('module_page_atools', '_at_servers') + '" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#servers"></use></svg></th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_expire') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_remaining') + '</th>' +
        '</tr></thead><tbody>' + rows + '</tbody></table></div>';

    return renderGlobalSearchSectionBlock(title, section.total || 0, section.section_url, query, table, false);
}

function renderGlobalSearchFinancesSection(section, query) {
    var title = get_translate_module_phrase('module_page_atools', '_at_navFinances');
    var items = section.items || [];
    if (!items.length) {
        return renderGlobalSearchSectionBlock(title, 0, section.section_url, query, '', true);
    }

    var currency = section.currency || '';
    var rows = items.map(function (item) {
        return '<tr>' +
            '<td><div class="at__table-user">' +
            `<a href="/profiles/${item.steamid}/?search=1" target="_blank">` +
            `<img id="avatar" avatarid="${item.steamid}" src="${item.avatar}" alt="">` +
            `<span id="name" nameid="${item.steamid}">${item.name}</span></a></div></td>` +
            `<td>${atGlobalSearchFormatAmount(item.cash, currency)}</td>` +
            `<td>${atGlobalSearchFormatAmount(item.all_cash, currency)}</td>` +
            `<td>${atGlobalSearchFormatAmount(item.last_deposit_summ, currency)} · ${item.last_deposit}</td>` +
            '</tr>';
    }).join('');

    var table = '<div class="table-responsive"><table class="table"><thead><tr>' +
        '<th>' + get_translate_phrase('_Player') + '</th>' +
        '<th>' + get_translate_phrase('_Current_balance') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_allTime') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_lastDeposit') + '</th>' +
        '</tr></thead><tbody>' + rows + '</tbody></table></div>';

    return renderGlobalSearchSectionBlock(title, section.total || 0, section.section_url, query, table, false);
}

function renderGlobalSearchExperienceSection(section, query) {
    var title = get_translate_module_phrase('module_page_atools', '_at_navExperience');
    var items = section.items || [];
    if (!items.length) {
        return renderGlobalSearchSectionBlock(title, 0, section.section_url, query, '', true);
    }

    var rows = items.map(function (player) {
        return '<tr>' +
            '<td><div class="at__table-user">' +
            `<a href="/profiles/${player.steamid}/?search=1" target="_blank">` +
            `<img id="avatar" avatarid="${player.steamid}" src="${player.avatar}" alt="">` +
            `<span id="name" nameid="${player.steamid}">${player.name}</span></a></div></td>` +
            `<td>${player.value}</td>` +
            `<td>${player.kills}</td>` +
            `<td>${player.deaths}</td>` +
            `<td>${player.shoots}</td>` +
            `<td>${player.hits}</td>` +
            `<td>${player.headshots}</td>` +
            `<td>${player.playtime}</td>` +
            `<td data-tippy-content="${player.server_tooltip}" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#servers"></use></svg></td>` +
            '</tr>';
    }).join('');

    var table = '<div class="table-responsive"><table class="table"><thead><tr>' +
        '<th>' + get_translate_phrase('_Player') + '</th>' +
        '<th>' + get_translate_module_phrase('module_page_atools', '_at_colExperience') + '</th>' +
        '<th data-tippy-content="' + get_translate_module_phrase('module_page_atools', '_at_colKills') + '" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#pistol"></use></svg></th>' +
        '<th data-tippy-content="' + get_translate_module_phrase('module_page_atools', '_at_colDeaths') + '" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#skull"></use></svg></th>' +
        '<th data-tippy-content="' + get_translate_module_phrase('module_page_atools', '_at_colShots') + '" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#crosshair"></use></svg></th>' +
        '<th data-tippy-content="' + get_translate_module_phrase('module_page_atools', '_at_colHits') + '" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#target"></use></svg></th>' +
        '<th data-tippy-content="' + get_translate_module_phrase('module_page_atools', '_at_colHeadshots') + '" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#headshot"></use></svg></th>' +
        '<th data-tippy-content="' + get_translate_module_phrase('module_page_atools', '_at_colPlaytime') + '" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#time-timer"></use></svg></th>' +
        '<th data-tippy-content="' + get_translate_module_phrase('module_page_atools', '_at_servers') + '" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#servers"></use></svg></th>' +
        '</tr></thead><tbody>' + rows + '</tbody></table></div>';

    return renderGlobalSearchSectionBlock(title, section.total || 0, section.section_url, query, table, false);
}

function renderGlobalSearchSections(sections, query) {
    if (!sections) {
        return '';
    }

    var html = '';
    if (sections.punishments) {
        html += renderGlobalSearchPunishmentsSection(sections.punishments, query);
    }
    if (sections.checks) {
        html += renderGlobalSearchChecksSection(sections.checks, query);
    }
    if (sections.reports) {
        html += renderGlobalSearchReportsSection(sections.reports, query);
    }
    if (sections.admins) {
        html += renderGlobalSearchAdminsSection(sections.admins, query);
    }
    if (sections.vip) {
        html += renderGlobalSearchVipSection(sections.vip, query);
    }
    if (sections.finances) {
        html += renderGlobalSearchFinancesSection(sections.finances, query);
    }
    if (sections.experience) {
        html += renderGlobalSearchExperienceSection(sections.experience, query);
    }

    return html;
}

function finishGlobalSearchRender(player, sectionsItems) {
    var items = [];
    if (player) {
        items.push(player);
    }
    if (sectionsItems && sectionsItems.length) {
        items = items.concat(sectionsItems);
    }

    renderTableAvatars(items, [
        { flagField: 'checked_avatar', steamField: 'steamid' },
        { flagField: 'player_checked_avatar', steamField: 'player_steamid' },
        { flagField: 'admin_checked_avatar', steamField: 'admin_steamid' },
        { flagField: 'offender_checked_avatar', steamField: 'offender_steamid' }
    ]);
    initTippy();
    RenderingAvatar();
}