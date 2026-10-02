function renderTermsList (data, name, nameId, openSelect) {
    return `
        <ul class="adaptive-select__dropdown-list" id="${openSelect}">
            <div class="inputs-inline">
                <div class="number" id="numberControl">
                    <button class="number-minus" type="button">-</button>
                    <input id="${nameId}Custom" type="number" min="0" value="" step="1" placeholder="${get_translate_module_phrase('module_page_atools', '_at_ownTime')}" autocomplete="off">
                    <button class="number-plus" type="button">+</button>
                </div>
            </div>
            ${data.map((term, id) => `
                <li>
                    <label class="adaptive-select__label" for="${nameId}-${id}">
                        <div class="adaptive-select__label-text">${escapeHtml(term.name)}</div>
                        <input class="hide-input" id="${nameId}-${id}" value="${term.time}" type="radio" name="${name}">
                    </label>
                </li>
            `).join('')}
        </ul>
        <div class="adaptive-select" open-select="${openSelect}">
            <span class="adaptive-select__fist-icon">
                <svg>
                    <use href="/resources/img/sprite.svg#time-expired"></use>
                </svg>
            </span>
            <span class="adaptive-select__span_text">${get_translate_module_phrase('module_page_atools', '_at_selectExpire')}</span>
            <span class="margin-left-auto adaptive-select__arrow">
                <svg>
                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                </svg>
            </span>
        </div>
    `;
};

function renderServerList (data, name, nameId, openSelect, opts) {
    opts = opts || {};
    const allServersChecked = !!opts.defaultAllServers;
    const defaultSpanText = allServersChecked
        ? get_translate_module_phrase('module_page_atools', '_at_allServers')
        : get_translate_module_phrase('module_page_atools', '_at_chooseServers');
    return `
        <ul class="adaptive-select__dropdown-list" id="${openSelect}">
            <div class="inputs-inline">
                <svg>
                    <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                </svg>
                <input id="search${nameId}" type="search" placeholder="${get_translate_module_phrase('module_page_atools', '_at_findServer')}" autocomplete="off">
            </div>
            <li>
                <label class="adaptive-select__label" for="${nameId}All">
                    <div class="adaptive-select__label-text">${get_translate_module_phrase('module_page_atools', '_at_allServers')}</div>
                    <input class="hide-input" id="${nameId}All" value="-1" type="checkbox" name="${name}"${allServersChecked ? ' checked' : ''}>
                </label>
            </li>
            ${data.map((server, id) => `
                <li>
                    <label class="adaptive-select__label" for="${nameId}-${id}">
                        <div class="adaptive-select__label-text">${escapeHtml(server.name_custom)}</div>
                        <input class="hide-input" id="${nameId}-${id}" value="${server.id}" type="checkbox" name="${name}">
                    </label>
                </li>
            `).join('')}
        </ul>
        <div class="adaptive-select" open-select="${openSelect}">
            <span class="adaptive-select__fist-icon">
                <svg>
                    <use href="/resources/img/sprite.svg#servers"></use>
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