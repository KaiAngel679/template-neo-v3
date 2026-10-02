function getCheckListCapabilities(my) {
    const perms = (my && Array.isArray(my.permissions)) ? my.permissions : [];
    const canDelete = perms.includes('checks.delete');

    return {
        canDelete,
        canBulkSelect: canDelete,
        canAnyRowAction: canDelete
    };
}

function isCheckSelf(check, mySteamid) {
    if (!mySteamid || !check || !check.admin_steamid) {
        return false;
    }

    return String(check.admin_steamid) === String(mySteamid);
}

function updateCheckTableBulkUi() {
    const caps = getCheckListCapabilities(window.atCheckMyData);
    updateBulkColumnUi({
        show: caps.canBulkSelect,
        bulkColSelector: '.at__check-bulk-col'
    });
}

function renderChecksList(data) {
    if (!data || !data.length) {
        return `<tr><td colspan="9"><div class="no-data">${get_translate_phrase('_nothingFound')}</div></td></tr>`;
    }

    const my = window.atCheckMyData;
    const mySteamid = my && my.steamid ? String(my.steamid) : null;
    const caps = getCheckListCapabilities(my);

    return data.map(function (check) {
        const isSelf = isCheckSelf(check, mySteamid);
        const canDelete = caps.canDelete && !isSelf;
        const checkboxCell = caps.canBulkSelect && !isSelf
            ? `<td class="at__check-bulk-col"><input type="checkbox" class="row-checkbox" value="${check.id}"></td>`
            : `<td class="at__check-bulk-col"></td>`;

        const contactHtml = check.contact && check.contact !== get_translate_phrase('_absent')
            ? `<div class="at__expire at__contact copy-btn" data-clipboard-text="${escapeHtml(check.contact)}">
                    ${escapeHtml(check.contact)}
                    <svg><use href="/resources/img/sprite.svg#copy-list"></use></svg>
               </div>`
            : `<div class="at__expire at__contact">${escapeHtml(check.contact)}</div>`;

        return `
            <tr data-check-id="${check.id}"${isSelf ? ' data-check-self="1"' : ''}>
                ${checkboxCell}
                <td>${check.id}</td>
                <td>
                    <div class="at__table-user">
                        <a href="/profiles/${check.player_steamid}/?search=1" target="_blank">
                            <img id="avatar" avatarid="${check.player_steamid}" src="${check.player_avatar}" alt="${check.player_name}">
                            <span id="name" nameid="${check.player_steamid}">${check.player_name}</span>
                        </a>
                    </div>
                </td>
                <td>
                    <div class="at__table-user">
                        <a href="/profiles/${check.admin_steamid}/?search=1" target="_blank">
                            <img id="avatar" avatarid="${check.admin_steamid}" src="${check.admin_avatar}" alt="${check.admin_name}">
                            <span id="name" nameid="${check.admin_steamid}">${check.admin_name}</span>
                        </a>
                    </div>
                </td>
                <td>${check.datestart}</td>
                <td>${check.date_end}</td>
                <td><div class="at__table-reason"><span>${escapeHtml(check.verdict)}</span></div></td>
                <td>${contactHtml}</td>
                <td>
                    ${canDelete ? `
                        <div class="action-buttons">
                            <button class="button-delete button-icon at-btn-check-delete" data-check-id="${check.id}" data-tippy-content="${get_translate_phrase('_Delete_Action')}" data-tippy-placement="top">
                                <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
                            </button>
                        </div>
                    ` : ''}
                </td>
            </tr>
        `;
    }).join('');
}