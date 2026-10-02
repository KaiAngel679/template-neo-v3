function renderLogsSkeletonList(count) {
    count = count || 10;
    var html = '';
    for (var i = 0; i < count; i++) {
        html += '<div class="at__logs-card skeleton--default"></div>';
    }
    return html;
}

function renderLogsList(data) {
    if (!data || !data.length) {
        return `<div class="no-data">${get_translate_phrase('_nothingFound')}</div>`;
    }

    return data.map(renderLogCard).join('');
}

function renderLogPlayer(actor) {
    if (!actor || !actor.steamid) {
        return '<span>—</span>';
    }

    return `
        <span>
            <a href="/profiles/${actor.steamid}/?search=1" target="_blank" id="name" nameid="${actor.steamid}">
                <img id="avatar" avatarid="${actor.steamid}" src="${actor.avatar}" alt="${actor.name}">
                ${actor.name}
            </a>
        </span>
    `;
}

function renderLogTargets(targets) {
    if (!targets || !targets.length) {
        return '';
    }

    const valid = targets.filter(function (target) {
        return target && target.steamid;
    });

    if (!valid.length) {
        return '';
    }

    if (valid.length === 1) {
        return renderLogPlayer(valid[0]);
    }

    return valid.map(function (target) {
        return renderLogPlayer(target);
    }).join('');
}

function renderLogDetails(details, detailsClass) {
    if (!details || !details.length) {
        return '';
    }

    const items = details.map(function (row) {
        const tooltip = row.tooltip
            ? ` data-tippy-content="${row.tooltip}" data-tippy-placement="top" data-tippy-interactive="true"`
            : '';

        return `
            <li${tooltip}>
                <svg><use href="/resources/img/sprite.svg#${row.icon}"></use></svg>
                <span>${row.html || row.text}</span>
            </li>
        `;
    }).join('');

    return `
        <div class="at__logs-body">
            <div class="at__logs-details ${detailsClass || ''}">
                <span>${get_translate_module_phrase('module_page_atools', '_at_logDetails')}</span>
                <ul>${items}</ul>
            </div>
        </div>
    `;
}

function renderLogCard(log) {
    const timeTooltip = log.time_tooltip ? ` data-tippy-content="${log.time_tooltip}" data-tippy-placement="top"` : '';
    const deleteBtn = log.can_delete
        ? `<button class="button-delete button-icon at__logs-delete" data-file-date="${log.file_date}" data-log-index="${log.index}" data-tippy-content="${get_translate_phrase('_Delete_Action')}" data-tippy-placement="top">
                <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
           </button>`
        : '';

    const targetsHtml = renderLogTargets(log.targets);
    const targetBlock = targetsHtml
        ? `<div class="at__logs-dot"></div>
                <div class="at__logs-target">
                    ${get_translate_module_phrase('module_page_atools', '_at_logTarget')}
                    ${targetsHtml}
                </div>`
        : '';

    return `
        <div class="at__logs-card" data-log-id="${log.id}">
            <div class="at__logs-header">
                <div class="at__logs-time"${timeTooltip}>${log.date_label}</div>
                <div class="at__logs-dot"></div>
                <div class="at__logs-type">
                    <svg><use href="/resources/img/sprite.svg#${log.type_icon}"></use></svg>
                    ${log.type_label}
                </div>
                <div class="at__logs-dot"></div>
                <div class="at__logs-from">
                    ${get_translate_module_phrase('module_page_atools', '_at_logAdmin')}
                    ${renderLogPlayer(log.admin)}
                </div>
                ${targetBlock}
                ${deleteBtn}
            </div>
            ${renderLogDetails(log.details, log.details_class)}
        </div>
    `;
}
