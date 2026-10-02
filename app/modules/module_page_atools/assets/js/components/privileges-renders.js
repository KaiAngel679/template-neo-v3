function getVipListCapabilities(my) {
    const perms = (my && Array.isArray(my.permissions)) ? my.permissions : [];
    const canUpdate = perms.includes('privileges.update');
    const canDelete = perms.includes('privileges.delete');

    return {
        canUpdate,
        canDelete,
        canBulkSelect: canDelete,
        canAnyRowAction: canUpdate || canDelete
    };
}

function updateVipTableBulkUi() {
    const caps = getVipListCapabilities(window.atVipMyData);
    updateBulkColumnUi({
        show: caps.canBulkSelect,
        bulkColSelector: '.at__check-bulk-col'
    });
}

function renderVipGroupList(data, name, nameId, openSelect) {
    const rows = Array.isArray(data) ? data : [];

    return `
        <ul class="adaptive-select__dropdown-list" id="${openSelect}">
            <div class="inputs-inline">
                <input id="${nameId}Custom" type="text" value="" placeholder="${get_translate_module_phrase('module_page_atools', '_at_customGroup')}" autocomplete="off">
            </div>
            ${rows.map((group, id) => `
                <li>
                    <label class="adaptive-select__label" for="${nameId}-${id}">
                        <div class="adaptive-select__label-text">${group.name}</div>
                        <input class="hide-input" id="${nameId}-${id}" value="${group.ini}" type="radio" name="${name}">
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
            <span class="adaptive-select__span_text">${get_translate_module_phrase('module_page_atools', '_at_selectVipGroup')}</span>
            <span class="margin-left-auto adaptive-select__arrow">
                <svg>
                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                </svg>
            </span>
        </div>
    `;
}

function renderEditVipModal(vip) {
    return `
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                ${get_translate_module_phrase('module_page_atools', '_at_changingVip')}
                <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <div>
                <div class="inputs-inline">
                    <label for="vipSteamIdEdit">${get_translate_module_phrase('module_page_atools', '_at_account')}</label>
                    <input id="vipSteamIdEdit" type="text" value="${vip.steamid}" autocomplete="off" disabled>
                </div>
                <div class="adaptive-select-wrapper" id="vipGroupEditList"></div>
                <div class="adaptive-select-wrapper" id="vipExpireEditList"></div>
                <div class="adaptive-select-wrapper" id="vipServerEditList" data-no-text></div>
                <button class="width-100" id="confirmEditVip" type="button" data-privilege-id="${vip.id}">${get_translate_module_phrase('module_page_atools', '_at_changeVip')}</button>
            </div>
        </div>
    `;
}

function renderPrivilegesList(data) {
    if (!data || !data.length) {
        return `<tr><td colspan="7"><div class="no-data">${get_translate_module_phrase('module_page_atools', '_at_noVips')}</div></td></tr>`;
    }

    const caps = getVipListCapabilities(window.atVipMyData);

    return data.map(function (vip) {
        const checkboxCell = caps.canBulkSelect
            ? `<td class="at__check-bulk-col"><input type="checkbox" class="row-checkbox" value="${vip.id}"></td>`
            : `<td class="at__check-bulk-col"></td>`;
        const actionsCell = caps.canAnyRowAction ? `
                <td>
                    <div class="action-buttons at__action">
                        <button class="button-icon button-more" data-action-menu="privileges" data-privilege-id="${vip.id}">
                            <svg>
                                <use href="/resources/img/sprite.svg#dots-vertical"></use>
                            </svg>
                        </button>
                    </div>
                </td>
            ` : '<td></td>';

        return `
            <tr data-vip-id="${vip.id}">
                ${checkboxCell}
                <td>
                    <div class="at__table-user">
                        <a href="/profiles/${vip.steamid}/?search=1" target="_blank">
                            <img id="avatar" avatarid="${vip.steamid}" src="${vip.avatar}" alt="${vip.name}">
                            <span id="name" nameid="${vip.steamid}">${vip.name}</span>
                        </a>
                    </div>
                </td>
                <td>${vip.group_name}</td>
                <td data-tippy-content="${vip.server_tooltip}" data-tippy-placement="top">
                    <svg><use href="/resources/img/sprite.svg#servers"></use></svg>
                </td>
                <td><div class="at__expire">${vip.expires_text}</div></td>
                <td><div class="at__expire">${vip.remaining_text}</div></td>
                ${actionsCell}
            </tr>
        `;
    }).join('');
}