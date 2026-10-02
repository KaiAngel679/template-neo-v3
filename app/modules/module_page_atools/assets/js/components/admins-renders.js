function renderAdminsGroupList(data, name, nameId, openSelect, opts) {
    opts = opts || {};
    const filterWithAllOption = !!opts.filterWithAllOption;
    const allGroupsLabel = get_translate_module_phrase('module_page_atools', '_at_allGroups');
    const rows = Array.isArray(data) ? data : [];
    const allOptionBlock = filterWithAllOption ? `
            <li>
                <label class="adaptive-select__label" for="${nameId}-all">
                    <div class="adaptive-select__label-text">${allGroupsLabel}</div>
                    <input class="hide-input" id="${nameId}-all" value="-1" type="radio" name="${name}" checked>
                </label>
            </li>` : '';
    const defaultSpanText = filterWithAllOption
        ? allGroupsLabel
        : get_translate_module_phrase('module_page_atools', '_at_selectGroup');
    return `
        <ul class="adaptive-select__dropdown-list" id="${openSelect}">
            <div class="inputs-inline">
                <svg>
                    <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                </svg>
                <input id="search${nameId}" type="search" value="" name="" placeholder="${get_translate_module_phrase('module_page_atools', '_at_findGroup')}" autocomplete="off">
            </div>
            ${allOptionBlock}
            ${rows.map((group, id) => `
                <li>
                    <label class="adaptive-select__label" for="${nameId}-${id}">
                        <div class="adaptive-select__label-text">${escapeHtml(group.name)}</div>
                        <input class="hide-input" id="${nameId}-${id}" value="${group.id}" type="radio" name="${name}">
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
            <span class="adaptive-select__span_text">${defaultSpanText}</span>
            <span class="margin-left-auto adaptive-select__arrow">
                <svg>
                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                </svg>
            </span>
        </div>
    `;
};

function renderAdminsList(data) {
    if (!data || data.length === 0) {
        return `
            <td colspan="4">
                <div class="no-data">${get_translate_module_phrase('module_page_atools', '_at_noAdmins')}</div>
            </td>
        `;
    }

    return `
        ${data.map(admin => {
        return `
            <tr data-admin-id="${admin.id}">
                <td>
                    <div class="at__table-user">
                        <a href="/profiles/${admin.steamid}/?search=1" target="_blank">
                            <img id="avatar" avatarid="${admin.steamid}" src="${admin.avatar}" alt="${admin.name}">
                            <span id="name" nameid="${admin.steamid}">${admin.name}</span>
                        </a>
                    </div>
                </td>
                <td>${escapeHtml(admin.group_name)}</td>
                <td><div class="at__expire">${admin.expires_text}</div></td>
                <td>
                    <div class="action-buttons at__action">
                        <button class="button-icon button-more" data-action-menu="admins" data-admin-id="${admin.id}">
                            <svg>
                                <use href="/resources/img/sprite.svg#dots-vertical"></use>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('')}
    `;
};

function getAdminPanelPermissionsMap() {
    return {
        'admins.view': get_translate_module_phrase('module_page_atools', '_at_watchAdmins'),
        'admins.create': get_translate_module_phrase('module_page_atools', '_at_addingAdmins'),
        'admins.delete': get_translate_module_phrase('module_page_atools', '_at_deletingAdmins'),
        'admins.update': get_translate_module_phrase('module_page_atools', '_at_changingAdmins'),
        'admins.warn.give': get_translate_module_phrase('module_page_atools', '_at_addingWarns'),
        'admins.warn.remove': get_translate_module_phrase('module_page_atools', '_at_removingWarns'),
        'admins.warn.delete': get_translate_module_phrase('module_page_atools', '_at_deletingWarns'),
        'admins.warn.update': get_translate_module_phrase('module_page_atools', '_at_changingWarns'),
        'punishments.view': get_translate_module_phrase('module_page_atools', '_at_watchPunishments'),
        'bans.create': get_translate_module_phrase('module_page_atools', '_at_addingBans'),
        'bans.unban': get_translate_module_phrase('module_page_atools', '_at_removingBans'),
        'bans.delete': get_translate_module_phrase('module_page_atools', '_at_deletingBans'),
        'bans.update': get_translate_module_phrase('module_page_atools', '_at_changingBans'),
        'mutes.create': get_translate_module_phrase('module_page_atools', '_at_addingMutes'),
        'mutes.unmute': get_translate_module_phrase('module_page_atools', '_at_removingMutes'),
        'mutes.delete': get_translate_module_phrase('module_page_atools', '_at_deletingMutes'),
        'mutes.update': get_translate_module_phrase('module_page_atools', '_at_changingMutes'),
        'checks.view': get_translate_module_phrase('module_page_atools', '_at_watchChecks'),
        'checks.delete': get_translate_module_phrase('module_page_atools', '_at_deletingChecks'),
        'finances.view': get_translate_module_phrase('module_page_atools', '_at_watchFinances'),
        'finances.update': get_translate_module_phrase('module_page_atools', '_at_changingFinances'),
        'finances.reset': get_translate_module_phrase('module_page_atools', '_at_resettingFinances'),
        'privileges.view': get_translate_module_phrase('module_page_atools', '_at_watchVIPs'),
        'privileges.create': get_translate_module_phrase('module_page_atools', '_at_addingVIPs'),
        'privileges.delete': get_translate_module_phrase('module_page_atools', '_at_deletingVIPs'),
        'privileges.update': get_translate_module_phrase('module_page_atools', '_at_changingVIPs'),
        'credits.view': get_translate_module_phrase('module_page_atools', '_at_watchCredits'),
        'credits.update': get_translate_module_phrase('module_page_atools', '_at_changingCredits'),
        'credits.reset': get_translate_module_phrase('module_page_atools', '_at_viperResettingCredits'),
        'experience.view': get_translate_module_phrase('module_page_atools', '_at_watchExperience'),
        'experience.update': get_translate_module_phrase('module_page_atools', '_at_changingExperience'),
        'experience.reset': get_translate_module_phrase('module_page_atools', '_at_viperResettingExperience'),
        'logs.view': get_translate_module_phrase('module_page_atools', '_at_watchLogs'),
        'logs.delete': get_translate_module_phrase('module_page_atools', '_at_deletingLogs')
    };
}

function renderAdminInfo(admin, myData = {}, warns = []) {
    const permissionsMap = getAdminPanelPermissionsMap();
    const permissions = Array.isArray(admin.permissions) ? admin.permissions : [];
    const allPermissions = Object.keys(permissionsMap);
    const permissionsHtml = allPermissions.map(perm => {
        const hasPermission = permissions.includes(perm);
        return `                                                                                                                                  
            <div class="at__driver-access ${hasPermission ? 'enable' : 'disable'}">                                                               
                ${permissionsMap[perm]}                                                                                                           
                <svg class="${hasPermission ? 'green' : 'red'}">                                                                                  
                    <use href="/resources/img/sprite.svg#${hasPermission ? 'check' : 'x'}"></use>                                                 
                </svg>                                                                                                                            
            </div>                                                                                                                                
        `;
    }).join('');

    const serversHtml = admin.servers.map(server => `
        <div class="at__driver-server">
            <span data-tippy-content="${server.name}" data-tippy-placement="top">${get_translate_module_phrase('module_page_atools', '_at_server')}: ${server.name}</span>
        </div>
    `).join('');

    const list = Array.isArray(warns) ? warns : [];
    const activeCount = list.filter(w => !w.is_expired).length;
    const canInteractWarns = admin.steamid != myData.steamid;

    const warnRows = list.map(w => {
        const tip = `${get_translate_module_phrase('module_page_atools', '_at_dateGiving')} ${w.created_at_text} <br> ${get_translate_module_phrase('module_page_atools', '_at_msgExpiresAt')} ${w.expires_at_text} <br> ${get_translate_module_phrase('module_page_atools', '_at_gave')} ${escapeHtml(w.admin_name)}`;
        const expiredClass = w.is_expired ? 'at__warns-expired' : '';
        const cb = canInteractWarns
            ? `${!w.is_expired ? `<svg class="at-btn-warn-edit" data-warn-id="${w.id}"><use href="/resources/img/sprite.svg#edit-pen"></use></svg>` : ''}<input type="checkbox" class="at-warn-select" name="at-warn-select" value="${w.id}">`
            : '';
        return `
            <label class="${expiredClass}" data-tippy-content="${tip}" data-tippy-placement="left">
                <svg>
                    <use href="/resources/img/sprite.svg#info-circle"></use>
                </svg>
                ${w.reason}
                ${cb}
            </label>
        `;
    }).join('');

    const warnsListHtml = list.length
        ? warnRows
        : `<div class="no-data">${get_translate_module_phrase('module_page_atools', '_at_noWarns')}</div>`;

    return `
        <button class="width-100" id="hideAdminInfo">${get_translate_module_phrase('module_page_atools', '_at_hideInfo')} <span>ESC</span></button>
        <div class="at__driver-header">
            <span id="name" nameid="${admin.steamid}">${get_translate_module_phrase('module_page_atools', '_at_infoAdmin')} ${admin.name}</span>
        </div>
        <div class="at__driver-content">
            <span class="at__driver-title">${get_translate_module_phrase('module_page_atools', '_at_main')}</span>
            <button class="at__driver-steamid width-100 copy-btn" data-clipboard-text="${admin.steamid}" data-tippy-content="${get_translate_module_phrase('module_page_atools', '_at_copySteamID')}" data-tippy-placement="top">
                ${admin.steamid}
                <svg>
                    <use href="/resources/img/sprite.svg#copy-list"></use>
                </svg>
            </button>
            <ul class="at__driver-info-list">
                <li>
                    <span class="at__parametr">
                        <svg>
                            <use href="/resources/img/sprite.svg#policeman"></use>
                        </svg>
                        ${get_translate_module_phrase('module_page_atools', '_at_adminGroup')}
                    </span>
                    <span class="at__value">${admin.group_name}</span>
                </li>
                <li>
                    <span class="at__parametr">
                        <svg>
                            <use href="/resources/img/sprite.svg#time-expired"></use>
                        </svg>
                        ${get_translate_module_phrase('module_page_atools', '_at_expire')}
                    </span>
                    <span class="at__value">${admin.expires_text}</span>
                </li>
            </ul>
            <span class="at__driver-title">${get_translate_module_phrase('module_page_atools', '_at_warns')} (${activeCount}/${myData.warn_settings.max_warns ?? 3})</span>
            ${canInteractWarns ? `
                <div class="at__warns-buttons">
                    ${myData.permissions.includes('admins.warn.give') ? `<button type="button" class="width-100 filter btn-at-open-add-warn" data-steamid="${admin.steamid}">${get_translate_module_phrase('module_page_atools', '_at_give')}</button>` : ''}
                    ${myData.permissions.includes('admins.warn.remove') && activeCount > 0 ? `<button type="button" class="width-100 filter at-btn-warn-remove">${get_translate_module_phrase('module_page_atools', '_at_remove')}</button>` : ''}
                    ${myData.permissions.includes('admins.warn.delete') && list.length > 0 ? `<button type="button" class="width-100 filter at-btn-warn-delete">${get_translate_phrase('_Delete_Action')}</button>` : ''}
                </div>
            ` : ''}
            <div class="at__warns-list">
                ${warnsListHtml}
            </div>
            <span class="at__driver-title">${get_translate_module_phrase('module_page_atools', '_at_accesses')} (${permissions.length})</span>
            <div class="at__driver-accesses-list">
                ${permissionsHtml || `<div class="no-data">${get_translate_module_phrase('module_page_atools', '_at_noAccesses')}</div>`}
            </div>
            <span class="at__driver-title">${get_translate_module_phrase('module_page_atools', '_at_servers')}</span>
            <div class="at__driver-servers-list">
                ${serversHtml || `<div class="no-data">${get_translate_module_phrase('module_page_atools', '_at_noServers')}</div>`}
            </div>
        </div>
    `;
};

function renderChangeAdminAccessGroupsList(data, name, nameId, openSelect) {
    const items = Array.isArray(data) ? data : [];
    const noGroupLabel = get_translate_module_phrase('module_page_atools', '_at_noGroup');

    return `
        <ul class="adaptive-select__dropdown-list" id="${openSelect}">
            <div class="inputs-inline">
                <svg>
                    <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                </svg>
                <input id="search${nameId}" type="search" value="" placeholder="${get_translate_module_phrase('module_page_atools', '_at_findGroup')}" autocomplete="off">
            </div>
            <li>
                <label class="adaptive-select__label" for="${nameId}-none">
                    <div class="adaptive-select__label-text">${noGroupLabel}</div>
                    <input class="hide-input" id="${nameId}-none" value="-1" type="radio" name="${name}">
                </label>
            </li>
            ${items.map(function (group) {
                return `
                    <li>
                        <label class="adaptive-select__label" for="${nameId}-${group.id}">
                            <div class="adaptive-select__label-text">${escapeHtml(group.name)}</div>
                            <input class="hide-input" id="${nameId}-${group.id}" value="${group.id}" type="radio" name="${name}">
                        </label>
                    </li>
                `;
            }).join('')}
        </ul>
        <div class="adaptive-select" open-select="${openSelect}">
            <span class="adaptive-select__fist-icon">
                <svg>
                    <use href="/resources/img/sprite.svg#list"></use>
                </svg>
            </span>
            <span class="adaptive-select__span_text">${get_translate_module_phrase('module_page_atools', '_at_selectPanelAccessGroup')}</span>
            <span class="margin-left-auto adaptive-select__arrow">
                <svg>
                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                </svg>
            </span>
        </div>
    `;
}

function renderChangeAdminModal(admin) {
    const permissionsMap = getAdminPanelPermissionsMap();
    const flagsHtml = Object.keys(permissionsMap).map((perm, id) => `
                        <li>
                            <label class="adaptive-select__label" for="changeAdminFlagAccess-${id}">
                                <div class="adaptive-select__label-text">${permissionsMap[perm]}</div>
                                <input class="hide-input" id="changeAdminFlagAccess-${id}" value="${perm}" type="checkbox" name="change-flag-access-list">
                            </label>
                        </li>`).join('');

    return `
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                ${get_translate_module_phrase('module_page_atools', '_at_changingAdmin')} ${admin.name}
                <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <div>
                <div class="inputs-inline">
                    <label for="steamIdInputEdit">${get_translate_module_phrase('module_page_atools', '_at_account')}</label>
                    <input id="steamIdInputEdit" type="text" value="${escapeHtml(admin.steamid || '')}" name="steamid" placeholder="https://steamcommunity.com/profiles/... / STEAM_1:1:390... / 7656119803... / [U:1:1234234]" autocomplete="off" disabled>
                </div>
                <div class="adaptive-select-wrapper" id="changeGroupList"></div>
                <div class="adaptive-select-wrapper" id="changeExpireList"></div>
                <div class="adaptive-select-wrapper" id="changeServerList" data-no-text></div>
                <div class="adaptive-select-wrapper" id="changeAccessGroupList"></div>
                <div class="adaptive-select-wrapper" id="changeFlagsAccessWrap" data-no-text>
                    <ul class="adaptive-select__dropdown-list" id="changeFlagsAccessList">
                        <div class="inputs-inline">
                            <svg>
                                <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                            </svg>
                            <input id="searchChangeAdminFlagsAccess" type="search" value="" name="" placeholder="${get_translate_module_phrase('module_page_atools', '_at_findFlags')}" autocomplete="off">
                        </div>
                        ${flagsHtml}
                    </ul>
                    <div class="adaptive-select" open-select="changeFlagsAccessList">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#list"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text">${get_translate_module_phrase('module_page_atools', '_at_selectPanelAccessFlags')}</span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <hr>
                <button type="button" class="width-100" id="confirmChangeAdmin" data-admin-id="${admin.id}">${get_translate_module_phrase('module_page_atools', '_at_changeAdmin')}</button>
            </div>
        </div>
    `;
};

function renderCreateWarnModal() {
    return `
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                ${get_translate_module_phrase('module_page_atools', '_at_givingWarn')}
                <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <div>
                <div class="inputs-inline">
                    <label for="addReasonWarn">${get_translate_module_phrase('module_page_atools', '_at_reason')}:</label>
                    <input id="addReasonWarn" type="text" value="" name="reason" placeholder="${get_translate_module_phrase('module_page_atools', '_at_enterReason')}">
                </div>
                <div class="adaptive-select-wrapper" id="addWarnExpireList"></div>
                <hr>
                <button type="button" class="width-100" id="atConfirmCreateWarn">${get_translate_module_phrase('module_page_atools', '_at_giveWarn')}</button>
            </div>
        </div>
    `;
};

function renderEditWarnModal(warn) {
    return `
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                ${get_translate_module_phrase('module_page_atools', '_at_changingWarn')}
                <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <div>
                <div class="inputs-inline">
                    <label for="editReasonWarn">${get_translate_module_phrase('module_page_atools', '_at_reason')}:</label>
                    <input id="editReasonWarn" type="text" value="${escapeHtml(warn.reason)}" name="editReason" placeholder="${get_translate_module_phrase('module_page_atools', '_at_enterReason')}">
                </div>
                <div class="adaptive-select-wrapper" id="editWarnExpireList"></div>
                <hr>
                <button type="button" class="width-100" id="atConfirmEditWarn" data-warn-id="${warn.id}">${get_translate_module_phrase('module_page_atools', '_at_changeWarn')}</button>
            </div>
        </div>
    `;
};