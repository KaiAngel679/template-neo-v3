function getExperienceListCapabilities(my) {
    const perms = (my && Array.isArray(my.permissions)) ? my.permissions : [];
    const canUpdate = perms.includes('experience.update');
    const canReset = perms.includes('experience.reset');

    return {
        canUpdate,
        canReset,
        canBulkSelect: canReset,
        canAnyRowAction: canUpdate || canReset
    };
}

function updateExperienceTableBulkUi() {
    const caps = getExperienceListCapabilities(window.atExperienceMyData);
    updateBulkColumnUi({
        show: caps.canBulkSelect,
        bulkColSelector: '.at__check-bulk-col'
    });
}

function renderEditExperienceModal(player) {
    return `
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                ${get_translate_module_phrase('module_page_atools', '_at_changingExperiencePlayer')}
                <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <div>
                <div class="at__panel-wrapper">
                    <div class="inputs-inline">
                        <label for="experienceSteamIdEdit">${get_translate_module_phrase('module_page_atools', '_at_experienceSteamId')}</label>
                        <input id="experienceSteamIdEdit" type="text" value="${player.steamid}" autocomplete="off" disabled>
                    </div>
                    <div class="inputs-inline">
                        <label for="countExperienceEdit">${get_translate_module_phrase('module_page_atools', '_at_colExperience')}</label>
                        <div class="number" id="experienceNumberControlEdit">
                            <button class="number-minus" type="button">-</button>
                            <input id="countExperienceEdit" type="number" min="0" step="1" value="${player.value}">
                            <button class="number-plus" type="button">+</button>
                        </div>
                    </div>
                    <button class="width-100" id="confirmEditExperience" type="button"
                        data-stats-key="${player.stats_key}"
                        data-steam="${player.steam}"
                        data-old-value="${player.value}">
                        ${get_translate_module_phrase('module_page_atools', '_at_changeExperienceBtn')}
                    </button>
                </div>
            </div>
        </div>
    `;
}

function renderExperienceList(data) {
    if (!data || !data.length) {
        return `<tr><td colspan="11"><div class="no-data">${get_translate_module_phrase('module_page_atools', '_at_noExperience')}</div></td></tr>`;
    }

    const caps = getExperienceListCapabilities(window.atExperienceMyData);

    return data.map(function (player) {
        const checkboxCell = caps.canBulkSelect
            ? `<td class="at__check-bulk-col"><input type="checkbox" class="row-checkbox" value="${player.id}"></td>`
            : `<td class="at__check-bulk-col"></td>`;

        const actionsCell = caps.canAnyRowAction ? `
                <td>
                    <div class="action-buttons at__action">
                        <button class="button-icon button-more" data-action-menu="experience" data-experience-id="${player.id}" type="button">
                            <svg>
                                <use href="/resources/img/sprite.svg#dots-vertical"></use>
                            </svg>
                        </button>
                    </div>
                </td>
            ` : '<td></td>';

        return `
            <tr data-experience-id="${player.id}">
                ${checkboxCell}
                <td>
                    <div class="at__table-user">
                        <a href="/profiles/${player.steamid}/?search=1" target="_blank">
                            <img id="avatar" avatarid="${player.steamid}" src="${player.avatar}" alt="${player.name}">
                            <span id="name" nameid="${player.steamid}">${player.name}</span>
                        </a>
                    </div>
                </td>
                <td>${player.value}</td>
                <td>${player.kills}</td>
                <td>${player.deaths}</td>
                <td>${player.shoots}</td>
                <td>${player.hits}</td>
                <td>${player.headshots}</td>
                <td>${player.playtime}</td>
                <td data-tippy-content="${player.server_tooltip}" data-tippy-placement="top">
                    <svg><use href="/resources/img/sprite.svg#servers"></use></svg>
                </td>
                ${actionsCell}
            </tr>
        `;
    }).join('');
}
