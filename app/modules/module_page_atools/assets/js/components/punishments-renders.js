function getPunishFilterType() {
    return (typeof $ === 'function' && $('#punishFilterType').length)
        ? $('#punishFilterType input[type="radio"]:checked').val()
        : 'ban';
}

function renderPunishTypeIcon(punishType) {
    const icons = ['block', 'mute', 'chat-slash', 'face-mute'];
    const phrases = ['_at_gameBlock', '_at_micBlock', '_at_chatBlock', '_at_SilenceBlock'];
    const idx = Number(punishType);
    const safeIdx = idx >= 0 && idx < icons.length ? idx : 0;
    const label = get_translate_module_phrase('module_page_atools', phrases[safeIdx]);

    return `<span data-tippy-content="${label}" data-tippy-placement="top"><svg><use href="/resources/img/sprite.svg#${icons[safeIdx]}"></use></svg></span>`;
}

function getPunishPermissionKeys(filterType) {
    const t = filterType || getPunishFilterType();
    return {
        edit: t === 'mute' ? 'mutes.update' : 'bans.update',
        remove: t === 'mute' ? 'mutes.unmute' : 'bans.unban',
        delete: t === 'mute' ? 'mutes.delete' : 'bans.delete'
    };
}

function getPunishListCapabilities(my) {
    const perms = (my && Array.isArray(my.permissions)) ? my.permissions : [];
    const keys = getPunishPermissionKeys();
    const canRemove = perms.includes(keys.remove);
    const canDelete = perms.includes(keys.delete);
    const canEdit = perms.includes(keys.edit);

    return {
        keys,
        canRemove,
        canDelete,
        canEdit,
        canBulkSelect: canRemove || canDelete,
        canAnyRowAction: canEdit || canRemove || canDelete
    };
}

function isPunishmentSelf(punish, mySteamid) {
    if (!mySteamid || !punish || !punish.offender_steamid) {
        return false;
    }
    return String(punish.offender_steamid) === String(mySteamid);
}

function isPunishmentActionBlocked(punish, my) {
    if (isAtSiteAdmin(my)) {
        return false;
    }
    return isPunishmentSelf(punish, my && my.steamid);
}

function isPunishmentEditable(punish) {
    return !!(punish && !punish.is_removed && !punish.is_expired);
}

function updatePunishTableBulkUi() {
    const caps = getPunishListCapabilities(window.atPunishMyData);
    updateBulkColumnUi({
        show: caps.canBulkSelect,
        bulkColSelector: '.at__punish-bulk-col'
    });
}

function renderPunishmentsList(data) {
    if (!data || !data.length) {
        return `<tr><td colspan="10"><div class="no-data">${get_translate_module_phrase('module_page_atools', '_at_noPunishments')}</div></td></tr>`;
    }

    const my = window.atPunishMyData;
    const mySteamid = my && my.steamid ? String(my.steamid) : null;
    const caps = getPunishListCapabilities(my);

    return data.map(p => {
        const isSelf = isPunishmentActionBlocked(p, my);
        const canEdit = caps.canEdit && !isSelf && isPunishmentEditable(p);
        const canRemove = caps.canRemove && !isSelf && isPunishmentEditable(p);
        const canDelete = caps.canDelete && !isSelf;
        const canAnyAction = canEdit || canRemove || canDelete;
        const checkboxCell = caps.canBulkSelect && !isSelf
            ? `<td class="at__punish-bulk-col"><input type="checkbox" class="row-checkbox" value="${p.id}"></td>`
            : `<td class="at__punish-bulk-col"></td>`;

        return `
            <tr data-punish-id="${p.id}"${isSelf ? ' data-punish-self="1"' : ''}>
                ${checkboxCell}
                <td>${renderPunishTypeIcon(p.punish_type)}</td>
                <td><span data-tippy-content="${p.created_time}" data-tippy-placement="top">${p.created_at}</span></td>
                <td>
                    <div class="at__table-user">
                        <a href="/profiles/${p.offender_steamid}/?search=1" target="_blank">
                            <img id="avatar" avatarid="${p.offender_steamid}" src="${p.offender_avatar}" alt="${p.offender_name}">
                            <span id="name" nameid="${p.offender_steamid}">${p.offender_name}</span>
                        </a>
                    </div>
                </td>
                <td><div class="at__table-reason"><span>${p.reason}</span></div></td>
                <td>
                    <div class="at__table-user">
                        ${p.admin_steamid ? `
                        <a href="/profiles/${p.admin_steamid}/?search=1" target="_blank">
                            <img id="avatar" avatarid="${p.admin_steamid}" src="${p.admin_avatar}" alt="${p.admin_name}">
                            <span id="name" nameid="${p.admin_steamid}">${p.admin_name}</span>
                        </a>` : `<span>${p.admin_name}</span>`}
                    </div>
                </td>
                <td data-tippy-content="${p.servers.join('<br>')}" data-tippy-placement="top">
                    <svg><use href="/resources/img/sprite.svg#servers"></use></svg>
                </td>
                <td><div class="at__expire">${p.duration_text}</div></td>
                <td><div class="at__expire ${p.expire_class}">${p.expire_text}</div></td>
                <td>
                    ${canAnyAction ? `
                        <div class="action-buttons at__action">
                            <button class="button-icon button-more" data-action-menu="punishments" data-punish-id="${p.id}">
                                <svg>
                                    <use href="/resources/img/sprite.svg#dots-vertical"></use>
                                </svg>
                            </button>
                        </div>
                    ` : ''}
                </td>
            </tr>
        `;
    }).join('');
}

function renderPunishReasonList(data, name, nameId, openSelect) {
    const items = Array.isArray(data) ? data : [];
    return `
        <ul class="adaptive-select__dropdown-list" id="${openSelect}">
            <div class="inputs-inline">
                <input id="${nameId}Custom" type="text" value="" placeholder="${get_translate_module_phrase('module_page_atools', '_at_customReason')}" autocomplete="off">
            </div>
            ${items.map((reason, id) => `
                <li>
                    <label class="adaptive-select__label" for="${nameId}-${id}">
                        <div class="adaptive-select__label-text">${escapeHtml(reason.name)}</div>
                        <input class="hide-input" id="${nameId}-${id}" value="${escapeHtml(reason.name)}" type="radio" name="${name}">
                    </label>
                </li>
            `).join('')}
        </ul>
        <div class="adaptive-select" open-select="${openSelect}">
            <span class="adaptive-select__fist-icon">
                <svg>
                    <use href="/resources/img/sprite.svg#list"></use>
                </svg>
            </span>
            <span class="adaptive-select__span_text">${get_translate_module_phrase('module_page_atools', '_at_selectPunishReason')}</span>
            <span class="margin-left-auto adaptive-select__arrow">
                <svg>
                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                </svg>
            </span>
        </div>
    `;
}

function renderPunishTypeEditOptions() {
    const filterType = getPunishFilterType();
    const perms = (myData && Array.isArray(myData.permissions)) ? myData.permissions : [];
    const parts = [];

    if (filterType === 'ban' && perms.includes('bans.update')) {
        parts.push(`
            <li>
                <label class="adaptive-select__label" for="punishTypeBanEdit">
                    <div class="adaptive-select__label-text">${get_translate_module_phrase('module_page_atools', '_at_gameBlock')}</div>
                    <input class="hide-input" id="punishTypeBanEdit" value="0" type="radio" name="punish-type-edit">
                </label>
            </li>
        `);
    }
    if (filterType === 'mute' && perms.includes('mutes.update')) {
        parts.push(`
            <li>
                <label class="adaptive-select__label" for="punishTypeMuteEdit">
                    <div class="adaptive-select__label-text">${get_translate_module_phrase('module_page_atools', '_at_micBlock')}</div>
                    <input class="hide-input" id="punishTypeMuteEdit" value="1" type="radio" name="punish-type-edit">
                </label>
            </li>
            <li>
                <label class="adaptive-select__label" for="punishTypeChatEdit">
                    <div class="adaptive-select__label-text">${get_translate_module_phrase('module_page_atools', '_at_chatBlock')}</div>
                    <input class="hide-input" id="punishTypeChatEdit" value="2" type="radio" name="punish-type-edit">
                </label>
            </li>
            <li>
                <label class="adaptive-select__label" for="punishTypeSilenceEdit">
                    <div class="adaptive-select__label-text">${get_translate_module_phrase('module_page_atools', '_at_SilenceBlock')}</div>
                    <input class="hide-input" id="punishTypeSilenceEdit" value="3" type="radio" name="punish-type-edit">
                </label>
            </li>
        `);
    }

    return parts.join('');
}

function renderEditPunishmentModal(punish) {
    return `
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                ${get_translate_module_phrase('module_page_atools', '_at_editingPunish')}
                <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <div>
                <div class="inputs-inline">
                    <label for="steamIdInputEdit">${get_translate_module_phrase('module_page_atools', '_at_account')}</label>
                    <input id="steamIdInputEdit" type="text" value="${escapeHtml(punish.offender_steamid || '')}" name="steamid" autocomplete="off" disabled>
                </div>
                <div class="inputs-inline">
                    <input type="checkbox" id="enableIPEdit" class="switch">
                    <label for="enableIPEdit">${get_translate_module_phrase('module_page_atools', '_at_addIP')}</label>
                </div>
                <div class="inputs-inline at__ip-toggle" style="display: none;">
                    <label for="ipInputEdit">${get_translate_module_phrase('module_page_atools', '_at_userIP')}</label>
                    <input id="ipInputEdit" type="text" value="" name="ip" placeholder="192.168.0.1" autocomplete="off">
                </div>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="punishTypeEdit">
                        ${renderPunishTypeEditOptions()}
                    </ul>
                    <div class="adaptive-select" open-select="punishTypeEdit">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#user-block"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text">${get_translate_module_phrase('module_page_atools', '_at_selectPunishType')}</span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="adaptive-select-wrapper" id="punishReasonEditList"></div>
                <div class="adaptive-select-wrapper" id="punishTimeEditList"></div>
                <div class="adaptive-select-wrapper" id="punishServerEditList" data-no-text></div>
                <button class="width-100" id="confirmEditPunish" type="button" data-punish-id="${punish.id}">${get_translate_module_phrase('module_page_atools', '_at_editPunish')}</button>
            </div>
        </div>
    `;
}