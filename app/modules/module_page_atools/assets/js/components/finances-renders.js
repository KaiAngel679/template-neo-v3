function getFinanceListCapabilities(my) {
    const perms = (my && Array.isArray(my.permissions)) ? my.permissions : [];
    const canUpdate = perms.includes('finances.update');
    const canReset = perms.includes('finances.reset');

    return {
        canUpdate,
        canReset,
        canBulkSelect: canReset,
        canAnyRowAction: canUpdate || canReset
    };
}

function updateFinanceTableBulkUi() {
    const caps = getFinanceListCapabilities(window.atFinanceMyData);
    updateBulkColumnUi({
        show: caps.canBulkSelect,
        bulkColSelector: '.at__check-bulk-col'
    });
}

function formatFinanceAmount(value, currency) {
    const num = Number(value);
    const formatted = Number.isFinite(num) ? num : 0;
    return `${formatted}${currency || ''}`;
}

function renderEditFinanceModal(finance) {
    return `
        <div class="popup_modal_content no-close no-scrollbar">
            <div class="popup_modal_head">
                ${get_translate_module_phrase('module_page_atools', '_at_changingBalancePlayer')}
                <span class="popup_modal_close">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </span>
            </div>
            <div>
                <div class="at__panel-wrapper">
                    <div class="inputs-inline">
                        <label for="financeSteamIdEdit">${get_translate_module_phrase('module_page_atools', '_at_account')}</label>
                        <input id="financeSteamIdEdit" type="text" value="${finance.steamid}" autocomplete="off" disabled>
                    </div>
                    <div class="inputs-inline">
                        <label for="countSummEdit">${get_translate_phrase('_Current_balance')}</label>
                        <div class="number" id="numberControlEdit">
                            <button class="number-minus" type="button">-</button>
                            <input id="countSummEdit" type="number" min="0" step="0.01" value="${finance.cash}">
                            <button class="number-plus" type="button">+</button>
                        </div>
                    </div>
                    <button class="width-100" id="confirmEditFinance" type="button" data-auth="${finance.auth}" data-old-cash="${finance.cash}">
                        ${get_translate_module_phrase('module_page_atools', '_at_changeBalance')}
                    </button>
                </div>
            </div>
        </div>
    `;
}

function renderFinancesList(data, currency) {
    if (!data || !data.length) {
        return `<tr><td colspan="6"><div class="no-data">${get_translate_module_phrase('module_page_atools', '_at_noFinances')}</div></td></tr>`;
    }

    const caps = getFinanceListCapabilities(window.atFinanceMyData);

    return data.map(function (finance) {
        const checkboxCell = caps.canBulkSelect
            ? `<td class="at__check-bulk-col"><input type="checkbox" class="row-checkbox" value="${finance.auth}"></td>`
            : `<td class="at__check-bulk-col"></td>`;

        const lastDeposit = finance.last_deposit
            ? `${formatFinanceAmount(finance.last_deposit_summ, currency)} · ${finance.last_deposit}`
            : get_translate_phrase('_absent');

        const actionsCell = caps.canAnyRowAction ? `
                <td>
                    <div class="action-buttons at__action">
                        <button class="button-icon button-more" data-action-menu="finances" data-finance-auth="${finance.auth}" type="button">
                            <svg>
                                <use href="/resources/img/sprite.svg#dots-vertical"></use>
                            </svg>
                        </button>
                    </div>
                </td>
            ` : '<td></td>';

        return `
            <tr data-finance-auth="${finance.auth}">
                ${checkboxCell}
                <td>
                    <div class="at__table-user">
                        <a href="/profiles/${finance.steamid}/?search=1" target="_blank">
                            <img id="avatar" avatarid="${finance.steamid}" src="${finance.avatar}" alt="${finance.name}">
                            <span id="name" nameid="${finance.steamid}">${finance.name}</span>
                        </a>
                    </div>
                </td>
                <td>${formatFinanceAmount(finance.cash, currency)}</td>
                <td>${formatFinanceAmount(finance.all_cash, currency)}</td>
                <td>${lastDeposit}</td>
                ${actionsCell}
            </tr>
        `;
    }).join('');
}
