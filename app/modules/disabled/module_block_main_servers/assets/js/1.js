let allServersData = [];
let _pendingServersAnim = false;
let _modeTransitioning = false;

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function getServerBadge(server) {
    return (server.Bage || server.server_bage || '').trim();
}

function _stripAnimClasses($el, classes) {
    if (!$el || !$el.length) return;
    classes.forEach(c => $el.removeClass(c));
}

function _onAnimationEnd($el) {
    return new Promise(resolve => {
        if (!$el || !$el.length) { resolve(); return; }
        const el = $el[0];
        let done = false;
        const finish = () => {
            if (done) return;
            done = true;
            el.removeEventListener('animationend', handler);
            resolve();
        };
        const handler = (e) => {
            if (e.target === el) finish();
        };
        el.addEventListener('animationend', handler);
        setTimeout(finish, 900);
    });
}

function _setStaggerIndices($container, selector) {
    if (!$container || !$container.length) return;
    const nodes = $container[0].querySelectorAll(selector);
    nodes.forEach((n, i) => n.style.setProperty('--i', i));
}

let _serversAnimCleanupTimer = null;

function swapText(el, nextText) {
    if (!el) return;
    const incoming = (nextText == null ? '' : String(nextText)).trim();

    let host = el.querySelector(':scope > .text-swap');
    if (!host) {
        const current = (el.textContent || '').trim();
        host = document.createElement('span');
        host.className = 'text-swap';
        const layer = document.createElement('span');
        layer.className = 'text-swap__layer text-swap__layer--current';
        layer.textContent = current;
        el.textContent = '';
        host.appendChild(layer);
        el.appendChild(host);
    }

    const curr = host.querySelector('.text-swap__layer--current');
    if (!curr) return;

    if (host._swapPending) {
        clearTimeout(host._swapPending.timer);
        try { host._swapPending.commit(); } catch (e) { }
        host._swapPending = null;
    }

    if (curr.textContent.trim() === incoming) return;

    host.querySelectorAll('.text-swap__layer--ghost').forEach(n => n.remove());
    curr.classList.remove('text-swap__layer--out');
    void curr.offsetWidth;

    const ghost = document.createElement('span');
    ghost.className = 'text-swap__layer text-swap__layer--ghost text-swap__layer--in';
    ghost.textContent = incoming;
    host.appendChild(ghost);

    curr.classList.add('text-swap__layer--out');

    let done = false;
    const commit = () => {
        if (done) return;
        done = true;
        curr.textContent = incoming;
        curr.classList.remove('text-swap__layer--out');
        if (ghost.parentNode) ghost.remove();
        if (host._swapPending && host._swapPending.commit === commit) {
            host._swapPending = null;
        }
    };
    ghost.addEventListener('animationend', commit, { once: true });
    const timer = setTimeout(commit, 650);
    host._swapPending = { timer, commit };
}
function _scheduleServersStaggerCleanup() {
    const $loaded = $('#monLoaded');
    if (!$loaded.length) return;
    _setStaggerIndices($loaded, '.servers__card-block, .table-mon__server-block, .subMod');
    if (_serversAnimCleanupTimer) clearTimeout(_serversAnimCleanupTimer);
    _serversAnimCleanupTimer = setTimeout(() => {
        $loaded.removeClass('servers-anim-in');
        _serversAnimCleanupTimer = null;
    }, 1100);
}

const filterState = {
    mode: 'All',
    maps: new Set(),
    categories: new Set(),
    locations: new Set(),
    sorting: 'default',
    hideEmpty: false,
    favouritesOnly: false,
};

function readFiltersFromDOM() {
    const readChecked = (sel) => new Set(
        $(`${sel}:checked`).map(function () {
            return ($(this).val() || $(this).closest('label').find('.adaptive-select__label-text').text().trim()).toLowerCase();
        }).get()
    );

    filterState.maps = readChecked('#filterMap input[type="checkbox"]');
    filterState.locations = readChecked('#filterLocation input[type="checkbox"]');
    filterState.categories = readChecked('#filterCategories input[type="checkbox"]');

    filterState.sorting = $('#filterSorting input[type="radio"]:checked').val() || 'default';

    filterState.hideEmpty = $('#filterHideEmptys').is(':checked');
    filterState.favouritesOnly = $('#filterFavourites').is(':checked');

    populateFilters();
    filterState.maps = readChecked('#filterMap input[type="checkbox"]');
    filterState.locations = readChecked('#filterLocation input[type="checkbox"]');
    filterState.categories = readChecked('#filterCategories input[type="checkbox"]');

    renderFilteredServers({ animate: true });
}

function applyServerFilters(servers) {
    let result = servers.slice();

    if (filterState.mode && filterState.mode !== 'All') {
        result = result.filter(s => s.GameMode === filterState.mode);
    }
    if (filterState.maps.size > 0) {
        result = result.filter(s => filterState.maps.has((s.Map || '').toLowerCase()));
    }
    if (filterState.locations.size > 0) {
        result = result.filter(s => filterState.locations.has((s.Location || '').toLowerCase()));
    }
    if (filterState.categories.size > 0) {
        result = result.filter(s => filterState.categories.has((s.Category || '').toLowerCase()));
    }
    if (filterState.hideEmpty) {
        result = result.filter(s => s.Players > 0);
    }
    if (filterState.favouritesOnly) {
        result = result.filter(s => FavouritesAPI.has(s.ip));
    }
    if (filterState.sorting === 'up') {
        result.sort((a, b) => b.Players - a.Players);
    } else if (filterState.sorting === 'down') {
        result.sort((a, b) => a.Players - b.Players);
    }
    return result;
}

function getCurrentViewMode() {
    return $('#showFilter input[type="radio"]:checked').val() || $('#monLoaded').data('default-view') || 'grid';
}

const _FAV_OUTLINE = 'm8.962 18.91l.464-.588zM12 5.5l-.54.52a.75.75 0 0 0 1.08 0zm3.038 13.41l.465.59zm-5.612-.588C7.91 17.127 6.253 15.96 4.938 14.48C3.65 13.028 2.75 11.335 2.75 9.137h-1.5c0 2.666 1.11 4.7 2.567 6.339c1.43 1.61 3.254 2.9 4.68 4.024zM2.75 9.137c0-2.15 1.215-3.954 2.874-4.713c1.612-.737 3.778-.541 5.836 1.597l1.08-1.04C10.1 2.444 7.264 2.025 5 3.06C2.786 4.073 1.25 6.425 1.25 9.137zM8.497 19.5c.513.404 1.063.834 1.62 1.16s1.193.59 1.883.59v-1.5c-.31 0-.674-.12-1.126-.385c-.453-.264-.922-.628-1.448-1.043zm7.006 0c1.426-1.125 3.25-2.413 4.68-4.024c1.457-1.64 2.567-3.673 2.567-6.339h-1.5c0 2.198-.9 3.891-2.188 5.343c-1.315 1.48-2.972 2.647-4.488 3.842zM22.75 9.137c0-2.712-1.535-5.064-3.75-6.077c-2.264-1.035-5.098-.616-7.54 1.92l1.08 1.04c2.058-2.137 4.224-2.333 5.836-1.596c1.659.759 2.874 2.562 2.874 4.713zm-8.176 9.185c-.526.415-.995.779-1.448 1.043s-.816.385-1.126.385v1.5c0 0 1.326-.265 1.883-.59c.558-.326 1.107-.756 1.62-1.16z';
const _FAV_FILLED = 'M2 9.137C2 14 6.02 16.591 8.962 18.911C10 19.729 11 20.5 12 20.5s2-.77 3.038-1.59C17.981 16.592 22 14 22 9.138S16.5.825 12 5.501C7.5.825 2 4.274 2 9.137';
const _TECH_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 16 16"><path fill="currentColor" fill-rule="evenodd" d="M15 4.5A3.5 3.5 0 0 1 11.435 8c-.99-.019-2.093.132-2.7.913l-4.13 5.31a2.015 2.015 0 1 1-2.827-2.828l5.309-4.13c.78-.607.932-1.71.914-2.7L8 4.5a3.5 3.5 0 0 1 4.477-3.362c.325.094.39.497.15.736L10.6 3.902a.48.48 0 0 0-.033.653q.407.472.879.879a.48.48 0 0 0 .653-.033l2.027-2.027c.239-.24.642-.175.736.15q.136.466.138.976M3.75 13a.75.75 0 1 1-1.5 0a.75.75 0 0 1 1.5 0" clip-rule="evenodd"/><path fill="currentColor" d="M11.5 9.5q.47 0 .917-.084l1.962 1.962a2.121 2.121 0 0 1-3 3l-2.81-2.81l1.35-1.734c.05-.064.158-.158.426-.233c.278-.078.639-.11 1.062-.102zM5 4l1.446 1.445a2 2 0 0 1-.047.21c-.075.268-.169.377-.233.427l-.61.474L4 5H2.656a.25.25 0 0 1-.224-.139l-1.35-2.7a.25.25 0 0 1 .047-.289l.745-.745a.25.25 0 0 1 .289-.047l2.7 1.35A.25.25 0 0 1 5 2.654z"/></svg>';

function computeButtonState(server, ip) {
    const isOffline = server.MaxPlayers === 0 || server.techwork;
    const isFull = server.Players >= server.MaxPlayers;
    const isAvailable = !isOffline && !isFull;
    return {
        connectClass: isAvailable ? '' : ' play-access',
        connectOnclick: isAvailable ? `onclick="location.href='steam://run/${server.Mod}//+connect ${ip}'"` : '',
        connectTooltip: !isAvailable
            ? `data-tippy-content="${get_translate_module_phrase('module_block_main_servers', isOffline ? '_serverOffline' : '_serverFull')}" data-tippy-placement="left"`
            : '',
        connectIcon: isAvailable ? 'play-triangle' : 'block-join'
    };
}

function getServerUIData(server, idx, ip, type = 'card') {
    let badgeClass = type === 'card' ? 'servers__card-badge' : 'table-mon__server-badge';
    let pingLoader = server.MaxPlayers === 0 ? '' :
        `<svg class="ping-loader" viewBox="0 0 50 50" aria-hidden="true">
                <circle class="ping-loader__track" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                <circle class="ping-loader__path" cx="25" cy="25" r="20" fill="none" stroke-width="5" stroke-linecap="round"></circle>
            </svg>`;
    return {
        badge: getServerBadge(server) ? `<span class="${badgeClass}" id="server-bage-${idx}">${escapeHtml(getServerBadge(server))}</span>` : '',
        isFav: FavouritesAPI.has(ip) ? ' is-favourite' : '',
        pingEl: monSettings.showPing ? `<div class="servers__card-isActive">
                            <div class="servers__card-ping">
                                <span id="server-ping-${idx}" class="server-ping">${pingLoader}</span>
                                <svg class="low-ping"><use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#ping"></use></svg>
                            </div>
                            <span class="server__card-dot-decor">•</span>
                        </div>` : '',
        showOnlineLine: monSettings.showOnlineLine ? `<div class="servers__card-online server_play_access-${idx}">
                    <div class="servers__card-online-line" id="olineWidth${idx}"></div>
                </div>` : '',
        techWork: server.techwork ? `<div class="ex-mon__tech">${_TECH_SVG}${get_translate_module_phrase('module_block_main_servers', '_techWork')}</div>` : '',
        techStyle: server.techwork ? ' style="pointer-events: none; user-select: none; user-drag: none;"' : ''
    };
}

function buildConnectBtn(server, ip, idx, isTable = false) {
    const buttonClass = isTable ? `table-mon__server-button` : '';
    const btnState = computeButtonState(server, ip);
    return `<a id="connect_server_${idx}" class="${buttonClass} ${btnState.connectClass}" ${btnState.connectTooltip} ${btnState.connectOnclick}>
        <svg><use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#${btnState.connectIcon}"></use></svg>
    </a>`;
}

function buildCopyBtn(server, ip, idx, isTable = false) {
    const copyIp = server.techwork ? '' : `data-clipboard-text="connect ${ip}"`;
    const copyClass = server.techwork ? '' : 'copy-btn';
    const buttonClass = isTable ? `table-mon__server-button ${copyClass}` : copyClass;
    return `<button class="${buttonClass}" id="copy_btn_${idx}" ${copyIp} aria-label="${get_translate_phrase('_TakeIp')}">
        <svg><use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#copy-list"></use></svg>
    </button>`;
}

function buildServerCard(server, idx) {
    const ip = server.ip || '';
    const mapSrc = (server.MapImage && server.MapImage !== 'undefined')
        ? server.MapImage : `/storage/cache/img/maps/730/${server.Map || '-'}.webp`;
    const mapTitle = server.Map ? `<span class="servers__card-map" id="server-map-${idx}">${server.Map}</span>` : '';
    const ui = getServerUIData(server, idx, ip, 'card');

    return `<div class="servers__card-block"${ui.techStyle} id="server-mode-${idx}"
                data-mode="${server.GameMode || ''}"
                data-map="${(server.Map || '').toLowerCase()}"
                data-location="${(server.Location || '').toLowerCase()}"
                data-category="${(server.Category || '').toLowerCase()}">
                ${ui.techWork}
                ${ui.showOnlineLine}
                <div class="servers__card-info" id="${idx}" onclick="get_players_data(id)" href="javascript:void(0);">
                    <div class="servers__card-header">
                        ${ui.pingEl}
                        <span class="server__card-name" id="server-name-${idx}">${server.MaxPlayers === 0 ? get_translate_module_phrase('module_block_main_servers', '_serverDown') : (server.HostName || '')}</span>
                    </div>
                    <div class="servers__card-details">
                        ${ui.badge}
                        <button type="button" class="toFavourite${ui.isFav}" data-favourite-btn data-server-index="${idx}" aria-label="Add to favourites">
                            <svg class="fav-icon fav-icon--outline" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="${_FAV_OUTLINE}"/></svg>
                            <svg class="fav-icon fav-icon--filled" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="${_FAV_FILLED}"/></svg>
                        </button>
                        ${mapTitle}
                        <span class="server__card-dot-decor">•</span>
                        <span class="servers__card-online-text" id="server-players-${idx}">${server.Players}/${server.MaxPlayers}</span>
                    </div>
                </div>
                <div class="servers__card-buttons">
                    ${buildCopyBtn(server, ip, idx)}
                    ${buildConnectBtn(server, ip, idx)}
                </div>
                <img class="servers__card-image-map" ondrag="return false" ondragstart="return false" id="server-map-image-${idx}" src="${mapSrc}" alt="" title="">
            </div>`;
}

function buildTableRow(server, idx) {
    const ip = server.ip || '';
    const mapSrc = (server.MapImage && server.MapImage !== 'undefined')
        ? server.MapImage : `/storage/cache/img/maps/730/${server.Map || '-'}.webp`;
    const ui = getServerUIData(server, idx, ip, 'table');

    return `<div class="table-mon__server-block"${ui.techStyle} id="server-mode-${idx}"
                data-mode="${server.GameMode || ''}"
                data-map="${(server.Map || '').toLowerCase()}"
                data-location="${(server.Location || '').toLowerCase()}"
                data-category="${(server.Category || '').toLowerCase()}">
                ${ui.techWork}
                <img ondrag="return false" ondragstart="return false" id="server-map-image-${idx}" src="${mapSrc}" alt="">
                <button type="button" class="toFavourite${ui.isFav}" data-favourite-btn data-server-index="${idx}" aria-label="Add to favourites">
                    <svg class="fav-icon fav-icon--outline" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="${_FAV_OUTLINE}"/></svg>
                    <svg class="fav-icon fav-icon--filled" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="${_FAV_FILLED}"/></svg>
                </button>
                ${ui.pingEl}
                <span class="table-mon__server-name" id="server-name-${idx}">${server.HostName || ''}</span>
                <div class="table-mon__server-online server_play_access-${idx}" id="${idx}" onclick="get_players_data(id)" href="javascript:void(0);">
                    <div class="table-mon__server-online-line" id="olineWidth${idx}"></div>
                    <span class="table-mon__server-online-players" id="server-players-${idx}">
                        ${server.Players}/${server.MaxPlayers}
                        <svg><use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#question"></use></svg>
                    </span>
                </div>
                ${ui.badge}
                <span class="table-mon__server-map" id="server-map-${idx}">${server.Map || ''}</span>
                <div class="table-mon__server-buttons">
                    ${buildCopyBtn(server, ip, idx, true)}
                    ${buildConnectBtn(server, ip, idx, true)}
                </div>
            </div>`;
}

function buildSubModSeparator(title, totalPlayers, serversCount) {
    const playersLabel = get_translate_module_phrase('module_block_main_servers', '_PlayersInGame');
    const serversLabel = get_translate_module_phrase('module_block_main_servers', '_serversCount');
    return `<div class="subMod subMod--inline">
        <span class="subMod__title">${title}</span>
        <div class="subMod__counter">${playersLabel}: <span>${totalPlayers}</span></div>
        <div class="subMod__dashed"></div>
        <div class="subMod__servers">${serversLabel}: <span>${serversCount}</span></div>
    </div>`;
}

function updateSubMod() {
    $('#subModBlock').hide();
}

function updateModsServerCounts() {
    if (!allServersData.length) return;
    $('.mods__card').each(function () {
        const mod = $(this).data('mod');
        if (!mod) return;
        const count = allServersData.filter(s => s.GameMode === mod).length;
        $(this).find('.mods__servers-count').text(count);
    });
}

function populateFilters() {
    const activeMode = filterState.mode && filterState.mode !== 'All' ? filterState.mode : null;

    function getServersExcluding(exclude) {
        let result = activeMode
            ? allServersData.filter(s => s.GameMode === activeMode)
            : allServersData.slice();
        if (exclude !== 'maps' && filterState.maps.size > 0)
            result = result.filter(s => filterState.maps.has((s.Map || '').toLowerCase()));
        if (exclude !== 'locations' && filterState.locations.size > 0)
            result = result.filter(s => filterState.locations.has((s.Location || '').toLowerCase()));
        if (exclude !== 'categories' && filterState.categories.size > 0)
            result = result.filter(s => filterState.categories.has((s.Category || '').toLowerCase()));
        if (filterState.hideEmpty)
            result = result.filter(s => s.Players > 0);
        if (filterState.favouritesOnly)
            result = result.filter(s => FavouritesAPI.has(s.ip));
        return result;
    }

    const mapsPool = [...new Set(
        getServersExcluding('maps').map(s => s.Map).filter(m => m && m !== '-')
    )].sort();
    const $mapList = $('#filterMap');
    $mapList.empty();
    mapsPool.forEach((map, i) => {
        const isChecked = filterState.maps.has(map.toLowerCase()) ? ' checked' : '';
        $mapList.append(
            `<li><label class="adaptive-select__label" for="filterMap-${i}">
                <div class="adaptive-select__label-text">${map}</div>
                <input class="hide-input" id="filterMap-${i}" type="checkbox" name="filter-map" value="${map}"${isChecked}>
            </label></li>`
        );
    });

    const locPool = {};
    getServersExcluding('locations').forEach(s => {
        const city = (s.Location || '').trim();
        if (city && !locPool[city]) locPool[city] = (s.Country || '').toLowerCase();
    });
    const $locList = $('#filterLocation');
    $locList.empty();
    Object.keys(locPool).sort().forEach((city, i) => {
        const country = locPool[city];
        const flag = country
            ? `<img style="border-radius:var(--br-50)" src="/storage/cache/img/icons/custom/flags/${country}.svg" class="svg" alt="">`
            : '';
        const isChecked = filterState.locations.has(city.toLowerCase()) ? ' checked' : '';
        $locList.append(
            `<li><label class="adaptive-select__label" for="filterLocation-${i}">
                ${flag}
                <div class="adaptive-select__label-text">${city}</div>
                <input class="hide-input" id="filterLocation-${i}" type="checkbox" name="filter-location" value="${city}"${isChecked}>
            </label></li>`
        );
    });

    if (monSettings.enableMods && mon_mods_config && mon_mods_config.length) {
        const catPool = new Set(
            getServersExcluding('categories').map(s => (s.Category || '').trim()).filter(Boolean)
        );
        const modsToShow = activeMode
            ? mon_mods_config.filter(m => m.name === activeMode)
            : mon_mods_config;
        const $catList = $('#filterCategories');
        $catList.empty();
        const seen = new Set();
        modsToShow.forEach(mod => {
            (mod.submods || []).forEach(sub => {
                if (!sub.title || seen.has(sub.title)) return;
                seen.add(sub.title);
                if (!catPool.has(sub.title)) return;
                const isChecked = filterState.categories.has(sub.title.toLowerCase()) ? ' checked' : '';
                const idx = $catList.children().length;
                $catList.append(
                    `<li><label class="adaptive-select__label" for="categories-${idx}">
                        <div class="adaptive-select__label-text">${sub.title}</div>
                        <input class="hide-input" id="categories-${idx}" type="checkbox" name="filter-categories" value="${sub.title}"${isChecked}>
                    </label></li>`
                );
            });
        });
    }

    ['filterMap', 'filterLocation', 'filterCategories'].forEach(id => {
        const $wrapper = $(`#${id}`).closest('.adaptive-select-wrapper');
        if ($wrapper.length && typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($wrapper);
        }
    });
}

function groupByAllMods(filtered) {
    if (!mon_mods_config || !mon_mods_config.length) return groupByCategory(filtered);

    const byId = Object.fromEntries(filtered.map(s => [String(s.id), s]));
    const groups = [];
    const submodIds = new Set(
        mon_mods_config.flatMap(m => (m.submods || []).flatMap(s => s.servers))
    );

    mon_mods_config.forEach(mod => {
        (mod.submods || []).forEach(sub => {
            const srvs = sub.servers.map(id => byId[id]).filter(Boolean);
            if (srvs.length) groups.push({ title: sub.title, servers: srvs });
        });
    });

    const nocat = filtered.filter(s => !submodIds.has(String(s.id)));
    if (nocat.length) groups.push({ title: get_translate_module_phrase('module_block_main_servers', '_noCategory'), servers: nocat });

    return groups;
}

function groupByModsConfig(filtered, gameMode) {
    const cfg = (mon_mods_config || []).find(m => m.name === gameMode);
    if (!cfg) return groupByCategory(filtered);

    const byId = Object.fromEntries(filtered.map(s => [String(s.id), s]));
    const groups = [];
    const usedIds = new Set();

    (cfg.submods || []).forEach(sub => {
        const srvs = sub.servers.map(id => byId[id]).filter(Boolean);
        srvs.forEach(s => usedIds.add(String(s.id)));
        if (srvs.length) groups.push({ title: sub.title, servers: srvs });
    });

    const direct = cfg.servers.map(id => byId[id]).filter(s => s && !usedIds.has(String(s.id)));
    direct.forEach(s => usedIds.add(String(s.id)));

    const allConfigIds = new Set([
        ...cfg.servers,
        ...(cfg.submods || []).flatMap(s => s.servers),
    ]);
    const remaining = filtered.filter(s => !allConfigIds.has(String(s.id)));

    const nocat = [...direct, ...remaining];
    if (nocat.length) groups.push({ title: get_translate_module_phrase('module_block_main_servers', '_noCategory'), servers: nocat });

    return groups;
}

function groupByCategory(servers) {
    const named = new Map();
    const nocat = [];
    servers.forEach(s => {
        const cat = (s.Category || '').trim();
        if (!cat) { nocat.push(s); return; }
        if (!named.has(cat)) named.set(cat, { title: cat, servers: [] });
        named.get(cat).servers.push(s);
    });
    const groups = [...named.values()];
    if (nocat.length) groups.push({ title: '', servers: nocat });
    return groups;
}

function applySortingToGroups(groups) {
    if (filterState.sorting === 'up' || filterState.sorting === 'down') {
        groups.forEach(group => {
            if (filterState.sorting === 'up') {
                group.servers.sort((a, b) => b.Players - a.Players);
            } else if (filterState.sorting === 'down') {
                group.servers.sort((a, b) => a.Players - b.Players);
            }
        });
    }
    return groups;
}


function estimatePing(sLat, sLon, uLat, uLon) {
    const R = 6371;
    const dLat = (uLat - sLat) * Math.PI / 180;
    const dLon = (uLon - sLon) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 +
        Math.cos(sLat * Math.PI / 180) * Math.cos(uLat * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.max(5, Math.round(R * c * 0.026));
}

function renderFilteredServers(opts) {
    const animate = (opts && opts.animate) || _pendingServersAnim;
    _pendingServersAnim = false;
    if (!allServersData.length) {
        $('#monLoaded').hide();
        $('#monNoData').text(get_translate_module_phrase('module_block_main_servers', '_noServers')).show();
        return;
    }

    const viewMode = getCurrentViewMode();
    const filtered = applyServerFilters(allServersData);
    const $loaded = $('#monLoaded');
    const gridClass = $loaded.data('grid-class') || 'servers__card-wrapper';
    const tableClass = $loaded.data('table-class') || 'table-mon__servers-wrap';

    const groups = monSettings.enableMods
        ? (filterState.mode && filterState.mode !== 'All'
            ? groupByModsConfig(filtered, filterState.mode)
            : groupByAllMods(filtered))
        : [{ title: '', servers: filtered }];

    applySortingToGroups(groups);

    const colMatch = gridClass.match(/-(\d+)$/);
    const gridCols = colMatch ? parseInt(colMatch[1]) : 3;

    const buildItems = (servers) => viewMode === 'table'
        ? servers.map(s => buildTableRow(s, s._index)).join('')
        : servers.map(s => buildServerCard(s, s._index)).join('');

    const buildFillers = (count) =>
        '<div class="servers__card-block servers__card-block-empty"></div>'.repeat(count);

    let html = '';
    groups.forEach(group => {
        if (group.title) {
            const totalPlayers = group.servers.reduce((sum, s) => sum + (s.Players || 0), 0);
            html += buildSubModSeparator(group.title, totalPlayers, group.servers.length);
        }
        html += buildItems(group.servers);
        if (viewMode !== 'table' && monSettings.plugs) {
            const rem = group.servers.length % gridCols;
            if (rem > 0) html += buildFillers(gridCols - rem);
        }
    });

    if (viewMode === 'table') {
        $loaded.removeClass(gridClass).addClass(tableClass);
    } else {
        $loaded.removeClass(tableClass).addClass(gridClass);
    }

    if (!filtered.length) {
        $loaded.hide();
        $('#monNoData').text(get_translate_module_phrase('module_block_main_servers', '_noServers')).show();
        return;
    }

    $('#monNoData').hide();

    if (animate) {
        $loaded.removeClass('servers-anim-in servers-anim-out');
        void $loaded[0].offsetWidth;
        $loaded.addClass('servers-anim-in');
    }

    $loaded.html(html);

    if (animate) {
        _scheduleServersStaggerCleanup();
    }

    filtered.forEach(s => updateOnlinePercentage(s._index, s));

    if (window.tippy) {
        $loaded.find('[data-tippy-content]').each(function () { if (this._tippy) this._tippy.destroy(); });
        tippy('#monLoaded [data-tippy-content]', {
            placement: 'left', arrow: false, animation: 'shift-away', theme: 'neo',
        });
    }

    if (typeof ClipboardJS !== 'undefined') {
        if (window._monClipboard) window._monClipboard.destroy();
        window._monClipboard = new ClipboardJS('#monLoaded .copy-btn');
    }
    
    updatePings(allServersData, _userGeoCache);
    updateSubMod();
    syncFavouriteButtons();
    updateModsServerCounts();
    $loaded.show();
}


const sleep = ms => new Promise(r => setTimeout(r, ms));

let ServersUpdating = false;
let _userGeoCache = (typeof monUserGeo !== 'undefined' && monUserGeo?.lat != null) ? monUserGeo : null;
let _serversDataCache = null;
let _serversSnapshot = '';
let _serversStaleRetries = 0;
let _serversLastCacheUntil = 0;
let _filterPoolsSnapshot = '';

function _serversDataFingerprint(data) {
    if (!data || !Array.isArray(data.servers)) return '';
    const mods = data.mods && typeof data.mods === 'object'
        ? Object.entries(data.mods).sort(([a], [b]) => a.localeCompare(b)).map(([k, v]) => `${k}:${v}`).join('|')
        : '';
    return data.servers.map(s => [
        s.id, s.Players, s.MaxPlayers, s.Map, s.techwork, s.Category, s.GameMode, getServerBadge(s),
    ].join(':')).join(';') + '#' + mods;
}

function _filterPoolsFingerprint() {
    const maps = [...new Set(allServersData.map(s => (s.Map || '').toLowerCase()).filter(m => m && m !== '-'))].sort().join(',');
    const locs = [...new Set(allServersData.map(s => (s.Location || '').toLowerCase()).filter(Boolean))].sort().join(',');
    const cats = [...new Set(allServersData.map(s => (s.Category || '').toLowerCase()).filter(Boolean))].sort().join(',');
    return `${filterState.mode}|${maps}|${locs}|${cats}`;
}

function _applyServersPayload(data, { fullRender = true } = {}) {
    allServersData = data.servers.map((s, i) => ({ ...s, _index: i }));
    const poolsKey = _filterPoolsFingerprint();
    if (poolsKey !== _filterPoolsSnapshot) {
        _filterPoolsSnapshot = poolsKey;
        populateFilters();
    }

    $('#currentOnline').html(allServersData.reduce((sum, s) => sum + (s.Players || 0), 0));

    if (data.mods && typeof data.mods === 'object') {
        Object.entries(data.mods).forEach(([k, v]) => {
            const el = document.getElementById(k + '_online');
            if (el) el.textContent = v;
            const elfilter = document.getElementById(k + '_filterOnline');
            if (elfilter) elfilter.textContent = v;
        });
    }

    $('#monLoading').hide();

    if (fullRender) {
        renderFilteredServers();
    } else {
        requestAnimationFrame(() => {
            allServersData.forEach(s => updateOnlinePercentage(s._index, s));
            updatePings(allServersData, _userGeoCache);
            updateModsServerCounts();
            updateServerBadges();
        });
        if (allServersData.length) {
            $('#monLoaded').show();
            $('#monNoData').hide();
        }
    }
}

function updateServerBadges() {
    allServersData.forEach(s => {
        const badge = getServerBadge(s);
        const el = document.getElementById(`server-bage-${s._index}`);
        if (!el) return;
        el.textContent = badge;
        el.style.display = badge ? '' : 'none';
    });
}

async function UpdateServers(options = {}) {
    const forceRender = options.forceRender === true;
    if (ServersUpdating) return;
    ServersUpdating = true;
    try {
        const res = await fetch(
            '/app/modules/module_block_main_servers/includes/js_controller.php?action=servers&_=' + Date.now(),
            {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Cache-Control': 'no-cache' },
            }
        );
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        const cacheUntil = parseInt(res.headers.get('X-Mon-Cache-Until') || data.time, 10) || 0;
        const responseStale = res.headers.get('X-Mon-Cache-Stale') === '1';

        _serversDataCache = data;
        const snapshot = _serversDataFingerprint(data);
        const dataChanged = snapshot !== _serversSnapshot;
        _serversSnapshot = snapshot;
        _applyServersPayload(data, { fullRender: dataChanged || responseStale || forceRender });

        if (responseStale && cacheUntil > 0 && cacheUntil === _serversLastCacheUntil) {
            _serversStaleRetries++;
        } else if (!responseStale) {
            _serversStaleRetries = 0;
            _serversLastCacheUntil = cacheUntil;
        } else {
            _serversLastCacheUntil = cacheUntil;
            _serversStaleRetries = 1;
        }

        if (responseStale && _serversStaleRetries <= 2) {
            setTimeout(() => UpdateServers(), 10000);
        }
    } catch (err) {
    } finally {
        ServersUpdating = false;
    }
}


function updatePings(servers, userGeo) {
    if(!monSettings.showPing) return;
    servers.forEach((server, i) => {
        const el = document.getElementById('server-ping-' + i);
        if (!el) return;
        const svg = el.nextElementSibling;
        if (server.MaxPlayers === 0) {
            el.textContent = '-';
            el.className = 'server-ping low-ping';
            if (svg) svg.className.baseVal = 'low-ping';
            return;
        }
        if (!userGeo || server.lat == null || server.lon == null) {
            el.textContent = '-';
            el.className = 'server-ping low-ping';
            if (svg) svg.className.baseVal = 'low-ping';
            return;
        }
        const ping = estimatePing(server.lat, server.lon, userGeo.lat, userGeo.lon);
        el.textContent = '≈' + ping;
        const pingClass = ping < 50 ? 'low-ping' : ping < 100 ? 'middle-ping' : 'hight-ping';
        el.className = 'server-ping ' + pingClass;
        if (svg) svg.className.baseVal = pingClass;
    });
}

const MON_POLL_MS = monSettings.updateTime ?? 30000;
const MON_MODAL_POLL_MS = MON_POLL_MS + 2000;
let _serversPollTimer = null;
let _modalPollTimer = null;

function _isMonTabActive() {
    return document.visibilityState === 'visible';
}

function _startServersPolling() {
    if (_serversPollTimer !== null) return;
    _serversPollTimer = setInterval(() => {
        if (_isMonTabActive()) UpdateServers();
    }, MON_POLL_MS);
}

function _stopServersPolling() {
    if (_serversPollTimer === null) return;
    clearInterval(_serversPollTimer);
    _serversPollTimer = null;
}

function _startModalPolling() {
    if (_modalPollTimer !== null) return;
    _modalPollTimer = setInterval(() => {
        if (_isMonTabActive()) update_mon_modal();
    }, MON_MODAL_POLL_MS);
}

function _stopModalPolling() {
    if (_modalPollTimer === null) return;
    clearInterval(_modalPollTimer);
    _modalPollTimer = null;
}

function _onMonTabVisibilityChange() {
    if (_isMonTabActive()) {
        _startServersPolling();
        _startModalPolling();
        UpdateServers();
        if (modal_update) update_mon_modal();
    } else {
        _stopServersPolling();
        _stopModalPolling();
    }
}

if (typeof document.visibilityState !== 'undefined') {
    document.addEventListener('visibilitychange', _onMonTabVisibilityChange);
}

UpdateServers();
if (_isMonTabActive()) {
    _startServersPolling();
    _startModalPolling();
}


let ModalLoading = false;

async function ModalInfo() {
    const server = $('#server-players-online').attr('data-server');
    if (!server || ModalLoading) return;
    ModalLoading = true;
    try {
        const res = await fetch(
            `/app/modules/module_block_main_servers/includes/js_controller.php?action=modal&server=${encodeURIComponent(server)}`,
            { method: 'GET', credentials: 'same-origin' }
        );
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const response = await res.json();

        $("#server-players-modal").html(`${response.Players}/${response.MaxPlayers}`);
        $("#server-hostname").html(response.HostName);
        $("#server-admins").html(response.data.admins);
        $("#server-maptwo").html(response.Map);
        if (response.MapImage && response.MapImage !== "undefined") {
            const $modalMapEl = $("#server-map-image-modal");
            if ($modalMapEl.attr("src")) {
                $modalMapEl.attr("src", response.MapImage);
            } else {
                $modalMapEl.attr("data-src", response.MapImage);
            }
        }
        if (response.MapPin && response.MapPin !== "undefined") {
            const $pinEl = $("#server-pin");
            if ($pinEl.attr("src")) {
                $pinEl.attr("src", response.MapPin);
            } else {
                $pinEl.attr("data-src", response.MapPin);
            }
        }
        $("#win-team").html(response.data.winteam);
        $("#team-score").html(`${response.data.score_ct} : ${response.data.score_t}`);
        if (response.data.players?.length > 0) {
            const html = response.type === 'extended'
                ? renderExtendedModal(response.data.players, response.data.game)
                : renderDefaultModal(response.data.players);
            $('#players_online').html(html);
        } else {
            $("#players_online").html(`<div class="modal-card__body-empty">${get_translate_module_phrase('module_block_main_servers', '_EmptyServer')}</div>`);
        }
        $("#connect-server").attr("onclick", `location.href = 'steam://run/${response.Mod}//+connect ${response.Ip}'`);
        $("#copy_btnsecond").attr("data-clipboard-text", `connect ${response.Ip}`);

        tippy('#server-players-online [data-tippy-content]', {
            arrow: false,
            animation: "shift-away",
            theme: "neo",
            allowHTML: true,
            appendTo: (reference) => reference.closest('.modal-card') || document.body,
            popperOptions: {
                strategy: 'absolute'
            }
        });

        $("#playersLoader").hide();
        $("#players_online").show();
    } catch (err) {

    } finally {
        ModalLoading = false;
    }
}

function renderDefaultModal(players) {
    const rows = players.map(p => `
    <tr>
        <td>${p.name}</td>
        <td class="modal-hide">${p.frags}</td>
        <td class="modal-hide">${p.time}</td>
    </tr>`).join('');

    return `<div class="table-responsive">
            <div class="scroll-table-body">
                <form id="MonPlayersForm" method="POST">
                    <table class="table table-monitoring">
                        <thead>
                            <tr class="sticky-header">
                                <th>${get_translate_module_phrase('module_block_main_servers', '_Player')}</th>
                                <th class="modal-hide">${get_translate_module_phrase('module_block_main_servers', '_PlayersKills')}</th>
                                <th class="modal-hide">${get_translate_module_phrase('module_block_main_servers', '_PlayersInGame')}</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </form>
            </div>
        </div>`;
}

function renderExtendedModal(players, game) {
    const isCs2 = game === 'cs2';
    const rows = players.map((player, index) => {
        const prime = isCs2 ? `<svg class="svg-table ${player.prime ? 'prime_enable' : 'prime_disable'}" data-tippy-content="${player.prime ? 'PRIME' : 'NON PRIME'}" data-tippy-placement="top"><use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#prime"></use></svg>` : '';
        const faceit = isCs2 ? `<td class="modal-hide"><img class="modal-card__body-faceit" src="${player.faceit}" alt=""></td>` : '';
        const admin = player.admin ? `<svg data-tippy-content="${player.admin}" data-tippy-placement="top"><use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#shiedl-bold"></use></svg>` : '';
        const adminClass = player.admin ? 'admin-nick' : '';
        const vip = player.vip ? `<svg class="vip-svg" data-tippy-content="${player.vip}" data-tippy-placement="top"><use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#diamond"></use></svg>` : '';
        const value = JSON.stringify({ id: player.id, steam: player.steamid });
        return `
        <tr ${player.team} data-steam="${player.steamid}">
            ${faceit}
            <td>
                <a href="/profiles/${player.steamid}/?search=1" class="modal-card__body-player-info">
                    ${prime} ${admin} ${vip}
                    <span class="modal-card__body-name ${adminClass}">${player.name}</span>
                </a>
            </td>
            <td class="modal-hide">${player.rank}</td>
            <td class="modal-hide">${player.kills}</td>
            <td class="modal-hide">${player.kd}</td>
            <td class="modal-hide">${player.hs}</td>
            <td class="modal-hide">${player.playtime}</td>
            <td class="modal-hide">
                <svg class="svg-table ${getPingColor(player.ping)}" data-tippy-content="PING: ${player.ping}ms" data-tippy-placement="top">
                    <use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#ping"></use>
                </svg>
            </td>
            <td>
                <div class="checkbox-table">
                    <div class="input-form table-checkbox-block">
                        <input class="border-checkbox" type="checkbox" id="mon_act_${index}" name="mon_action[]" value='${value}'>
                        <label class="border-checkbox-label table-label" for="mon_act_${index}"></label>
                    </div>
                </div>
            </td>
        </tr>`;
    }).join('');

    const header = isCs2 ? `<th class="modal-hide"><img src="/app/modules/module_block_main_servers/assets/img/icons/faceit/faceit.svg" alt=""></th>` : '';
    return `<div class="table-responsive">
            <div class="scroll-table-body">
            <form id="MonPlayersForm" method="POST">
                <table class="table table-monitoring">
                <thead>
                    <tr class="sticky-header">
                    ${header}
                    <th>${get_translate_module_phrase('module_block_main_servers', '_Player')}</th>
                    <th class="modal-hide">${get_translate_module_phrase('module_block_main_servers', '_PlayersRang')}</th>
                    <th class="modal-hide">${get_translate_module_phrase('module_block_main_servers', '_PlayersKills')}</th>
                    <th class="modal-hide">K/D</th>
                    <th class="modal-hide">HS%</th>
                    <th class="modal-hide">${get_translate_module_phrase('module_block_main_servers', '_PlayersInGame')}</th>
                    <th class="modal-hide">Ping</th>
                    <th><img src="/app/modules/module_block_main_servers/assets/img/icons/check.svg" alt=""></th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
                </table>
            </form>
            </div>
        </div>`;
}

function updateOnlinePercentage(i, info) {
    const el = document.getElementById('olineWidth' + i);
    if (!el) return;

    const percentage = Math.round((info.Players / info.MaxPlayers) * 100);

    const prevTransition = el.style.transition;
    el.style.transition = 'none';
    el.style.width = '0%';
    void el.offsetWidth;
    el.style.transition = prevTransition;
    requestAnimationFrame(() => {
        el.style.width = `${percentage}%`;
    });

    let slotColor;
    if (percentage >= 100) {
        slotColor = 'var(--havent-slots)';
        const playAccess = document.querySelector('.play-access' + i);
        if (playAccess) {
            playAccess.classList.add('access-dinie');
            playAccess.innerHTML = `<svg><use href="/app/modules/module_block_main_servers/assets/img/icons/sprite.svg#block-join"></use></svg>`;
        }
    } else if (percentage >= 51) {
        slotColor = 'var(--have-small-slots)';
    } else {
        slotColor = 'var(--have-slots)';
    }
    el.style.setProperty('--slot-color', slotColor);
}

function defaultModalData() {
    const spinner = '<span class="fake-loader spinner"><svg viewBox="0 0 50 50"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg></span>'
    $("#server-admins").html(spinner);
    $("#server-players-modal").html(spinner);
    $("#server-maptwo").html(spinner);
    $("#server-hostname").html(spinner);
    $("#server-pin").attr("src", "/storage/cache/img/pins/maps/default.webp");
    $("#server-pin").attr("onerror", "this.onerror=null; this.src='/storage/cache/img/pins/maps/default.jpg';")
    $("#team-score").html(`0 : 0`);
    $("#win-team").html(get_translate_module_phrase('module_block_main_servers', '_scoreLoad'));
}

function getPingColor(ping) {
    if (ping <= 50) return 'low-ping';
    if (ping <= 100) return 'middle-ping';
    return 'hight-ping';
}

function close_modal() {
    resetModal();
}

$("#updateservers").on("click", async function () {
    if (this.disabled) return;
    const btn = $(this);
    btn.addClass('spinner');
    this.disabled = true;
    $("#monLoading").show();
    $("#monLoaded").hide();
    await sleep(1000);
    btn.removeClass('spinner');
    await UpdateServers({ forceRender: true });
    setTimeout(() => { this.disabled = false; }, 14000);
});

window.addEventListener('click', (e) => {
    if (e.target === document.querySelector('.modal_show')) resetModal();
});

function resetModal() {
    $('#server-players-online').attr('data-server', '');
    $('.modal_show').removeClass('modal_show');
    $('body, html').removeClass('hidescroll');
    modal_update = false;
    $('#players_online').html('').hide();
    $('#playersLoader').show();
    defaultModalData();
}

function showSmooth($el) {
    if (!$el || !$el.length || $el.hasClass('show')) return;
    $el.removeClass('hidden');
    requestAnimationFrame(() => $el.addClass('show'));
}

function hideSmooth($el) {
    if (!$el || !$el.length || !$el.hasClass('show')) return;
    $el.removeClass('show');
    setTimeout(() => {
        if (!$el.hasClass('show')) $el.addClass('hidden');
    }, 250);
}

async function filterServers(mode) {
    filterState.mode = (mode && mode !== '') ? mode : 'All';
    filterState.categories = new Set();
    filterState.maps = new Set();
    filterState.locations = new Set();
    populateFilters();

    if (mode && mode !== 'All') {
        const modCfg = mon_mods_config.find(m => m.name === mode);
        if (modCfg) {
            const videoEl = document.querySelector('.modFilter__video');
            if (videoEl) {
                const videoSrc = modCfg.video
                    ? '/app/modules/module_block_main_servers/assets/img/mods/' + modCfg.video
                    : '/app/modules/module_block_main_servers/assets/video/empty-video.mp4';
                if (videoEl.getAttribute('src') !== videoSrc) {
                    videoEl.src = videoSrc;
                    if (videoSrc) videoEl.load();
                }
            }
            const titleEl = document.querySelector('.modFilter__title');
            if (titleEl) swapText(titleEl, modCfg.name || '');
            const descEl = document.querySelector('.modFilter__description');
            if (descEl) {
                let desc = modCfg.description || '';
                if (desc.startsWith('_') && typeof translate !== 'undefined') {
                    const t = translate[desc];
                    if (t) desc = t[lang] || t['EN'] || desc;
                }
                swapText(descEl, desc);
            }
        }
    } else {
        const videoEl = document.querySelector('.modFilter__video');
        if (videoEl) {
            const videoSrc = '/app/modules/module_block_main_servers/assets/video/empty-video.mp4';
            if (videoEl.getAttribute('src') !== videoSrc) {
                videoEl.src = videoSrc;
                if (videoSrc) videoEl.load();
            }
        }
        const titleEl = document.querySelector('.modFilter__title');
        if (titleEl) swapText(titleEl, get_translate_module_phrase('module_block_main_servers', '_AllMod'));
        const descEl = document.querySelector('.modFilter__description');
        if (descEl) swapText(descEl, get_translate_module_phrase('module_block_main_servers', '_AllModDescription'));
    }

    const $rowServers = $('#rowServers');
    const $rowModFilter = $('#rowModFilter');
    const $rowModsServers = $('#rowModsServers');
    const switchingFromMods = $rowModsServers.length && !$rowModsServers.hasClass('hidden') && $rowModsServers.is(':visible');

    if (_modeTransitioning) return;
    _modeTransitioning = true;

    try {
        if (switchingFromMods) {
            const $wrap = $rowModsServers.find('.mods__wrapper');
            _setStaggerIndices($wrap, '.mods__card');
            $wrap.removeClass('mods-anim-in');
            void $wrap[0].offsetWidth;
            $wrap.addClass('mods-anim-out');
            const $lastCard = $wrap.children('.mods__card').last();
            await _onAnimationEnd($lastCard.length ? $lastCard : $wrap);
            $rowModsServers.removeClass('show').addClass('hidden');
            void $rowModsServers[0].offsetWidth;
            $wrap.removeClass('mods-anim-out');
        }

        if ($rowModFilter.length) {
            $rowModFilter.removeClass('hidden').addClass('show');
        }
        if ($rowServers.length) {
            $rowServers.removeClass('hidden').addClass('show');
        }

        _pendingServersAnim = true;
        renderFilteredServers();
    } finally {
        _modeTransitioning = false;
    }
}

$(document).on('change',
    '#filterMap input[type="checkbox"], #filterLocation input[type="checkbox"], #filterCategories input[type="checkbox"], #filterHideEmptys, #filterFavourites',
    readFiltersFromDOM
);

$(document).on('change', '#filterSorting input[type="radio"]', function () {
    filterState.sorting = $(this).val() || 'default';
    renderFilteredServers({ animate: true });
});

$(document).on('change', '#showFilter input[type="radio"]', function () {
    const viewMode = $(this).val();
    localStorage.setItem('mon_view_mode', viewMode);

    const $wrapper = $('[open-select="showFilter"]').closest('.adaptive-select-wrapper');
    if (typeof initAdaptiveSelects === 'function') {
        initAdaptiveSelects($wrapper);
    }

    renderFilteredServers({ animate: true });
});

$(document).on('click', '.mode', function () {
    $(".mode").removeClass("active");
    $(this).addClass("active");
    const mode = $(this).data('mode');
    filterServers(mode);
});

$(document).on('click', '.mods__card', function () {
    const mode = $(this).data('mod');
    if (!mode) return;

    const targetModeBtn = $(`.mode[data-mode='${mode}']`);
    if (!targetModeBtn.length) return;

    $(".mode").removeClass("active");
    targetModeBtn.addClass("active");
    filterServers(mode);
});

$(document).on('click', '#modFilterFastSearch', function () {
    const pool = applyServerFilters(allServersData).filter(s => s.MaxPlayers > 0 && s.Players < s.MaxPlayers);
    if (!pool.length) {
        return noty(get_translate_module_phrase('module_block_main_servers', '_noServers'), 'error');
    }
    const server = pool[Math.floor(Math.random() * pool.length)];
    location.href = `steam://run/${server.Mod}//+connect ${server.ip}`;
});

$(document).on('click', '.mods__button-search', function (e) {
    e.stopPropagation();
    const mode = $(this).closest('.mods__card').data('mod');
    const prevMode = filterState.mode;
    filterState.mode = mode || 'All';
    const pool = applyServerFilters(allServersData).filter(s => s.MaxPlayers > 0 && s.Players < s.MaxPlayers);
    filterState.mode = prevMode;
    if (!pool.length) {
        return noty(get_translate_module_phrase('module_block_main_servers', '_noServers'), 'error');
    }
    const server = pool[Math.floor(Math.random() * pool.length)];
    location.href = `steam://run/${server.Mod}//+connect ${server.ip}`;
});

$(document).on('click', '.mods__back', async function () {
    if (_modeTransitioning) return;
    _modeTransitioning = true;

    const $rowModsServers = $('#rowModsServers');
    const $rowServers = $('#rowServers');
    const $rowModFilter = $('#rowModFilter');
    const $loaded = $('#monLoaded');

    try {
        if ($loaded.length && $loaded.children().length) {
            $loaded.removeClass('servers-anim-in servers-anim-out');
            void $loaded[0].offsetWidth;
            _setStaggerIndices($loaded, '.servers__card-block, .table-mon__server-block, .subMod');
            $loaded.addClass('servers-anim-out');
            const $lastChild = $loaded.children().last();
            await _onAnimationEnd($lastChild.length ? $lastChild : $loaded);
        }

        if ($rowServers.length) {
            $rowServers.removeClass('show').addClass('hidden');
        }
        if ($rowModFilter.length) {
            $rowModFilter.removeClass('show').addClass('hidden');
        }
        if ($loaded.length) {
            void $loaded[0].offsetWidth;
            $loaded.removeClass('servers-anim-out');
        }

        $('.mode').removeClass('active');
        $('.mode[data-mode="All"]').addClass('active');
        filterState.mode = 'All';

        if ($rowModsServers.length) {
            const $wrap = $rowModsServers.find('.mods__wrapper');
            if ($wrap.length) {
                $wrap.removeClass('mods-anim-in mods-anim-out mods-ready');
                _setStaggerIndices($wrap, '.mods__card');
                $wrap.addClass('mods-anim-in');
            }
            $rowModsServers.removeClass('hidden').addClass('show');
            if ($wrap.length) {
                setTimeout(() => $wrap.removeClass('mods-anim-in').addClass('mods-ready'), 1200);
            }
        }
    } finally {
        _modeTransitioning = false;
    }
});

$(document).ready(() => {
    const savedViewMode = localStorage.getItem('mon_view_mode');
    const defaultView = savedViewMode || $('#monLoaded').data('default-view') || 'grid';
    const $radio = $(`#showFilter input[value="${defaultView}"]`);
    $radio.prop('checked', true);

    const $wrapper = $('[open-select="showFilter"]').closest('.adaptive-select-wrapper');
    if (typeof updateAdaptiveSelectText === 'function') {
        updateAdaptiveSelectText($wrapper);
    }

    if (monSettings.enableMods) $('#rowServers').hide();
    if (!monSettings.filterModes || monSettings.enableMods) $('#rowModFilter').hide();

    syncFavouriteButtons();

    $('#rowModsServers, #rowModFilter, #rowServers').each(function () {
        const $el = $(this);
        $el.addClass('smooth-toggle').addClass($el.is(':visible') ? 'show' : 'hidden');
    });

    const $modsWrap = $('#rowModsServers').find('.mods__wrapper');
    if ($modsWrap.length && $('#rowModsServers').is(':visible')) {
        _setStaggerIndices($modsWrap, '.mods__card');
        $modsWrap.addClass('mods-anim-in');
        setTimeout(() => $modsWrap.removeClass('mods-anim-in').addClass('mods-ready'), 1300);
    } else if ($modsWrap.length) {
        $modsWrap.addClass('mods-ready');
    }
});

let modal_update = false;

async function get_players_data(i) {
    const modal = document.getElementById("server-players-online");
    $('#server-players-online').attr('data-server', i)
    if (modal) $(modal).addClass("modal_show");
    await ModalInfo();
    modal_update = true;
}

$("#updatemodal").on("click", async function () {
    if (this.disabled) return;
    const btn = $(this);
    btn.addClass('spinner');
    this.disabled = true;
    $("#playersLoader").show();
    $("#players_online").hide();
    await sleep(1000);
    btn.removeClass('spinner');
    await ModalInfo();
    setTimeout(() => { this.disabled = false; }, 14000);
});

async function update_mon_modal() {
    const form = document.getElementById("MonPlayersForm");
    if (form && modal_update && !form.querySelector('input[name="mon_action[]"]:checked')) {
        await ModalInfo();
    }
}

window.addEventListener("click", (e) => {
    const form = document.getElementById("MonPlayersForm");
    const buttons = $(".modal-card__body-buttons");
    if (form && e.target.closest('input[name="mon_action[]"]')) {
        const anyChecked =
            form.querySelector('input[name="mon_action[]"]:checked') !== null;
        if (anyChecked) {
            buttons.show();
        } else {
            buttons.hide();
        }
    }
});

$(document).on("click", ".modal-card__body-action", function (e) {
    e.preventDefault();
    const action = $(this).data("action");
    const form = $("#MonPlayersForm");
    const sendBtn = $(".modal-card__body-send");

    if (!form.length || !form.find('input[name="mon_action[]"]:checked').length) {
        return noty(get_translate_module_phrase('module_block_main_servers', '_selectPlayers'), "error");
    }
    const time = $("#mon_modal_time");
    const banReason = $("#mon_modal_ban_reason");
    const muteReason = $("#mon_modal_mute_reason");
    const muteType = $("#mon_modal_mute_type");

    switch (action) {
        case "ban":
            time.show();
            banReason.show();
            muteReason.hide();
            muteType.hide();
            sendBtn.show().data("action", "ban");
            break;
        case "mute":
            time.show();
            banReason.hide();
            muteReason.show();
            muteType.show();
            sendBtn.show().data("action", "mute");
            break;
        case "kick":
            time.hide();
            banReason.hide();
            muteReason.hide();
            muteType.hide();
            sendBtn.show().data("action", "kick");
            break;
    }
});

$(document).on("click", ".modal-card__body-send", async function (e) {
    e.preventDefault();
    const action = $(this).data("action");
    const server = $('#server-players-online').attr('data-server');
    const form = $("#MonPlayersForm");

    switch (action) {
        case "ban":
            await sendBanAction(server, form);
            break;
        case "mute":
            await sendMuteAction(server, form);
            break;
        case "kick":
            await sendKickAction(server, form);
            break;
    }
});

let originalBtnText = '';

async function sendKickAction(server, form) {
    const formData = new FormData(form[0]);
    formData.append("action", "kick");
    formData.append("server", server);
    return await sendRequest(formData);
}

async function sendBanAction(server, form) {
    const time = ($('#mon_modal_time input[name="ban_time"]:checked').val()) || ($('#mon_modal_time input[name="ban_time"]').val()) || '';
    const reason = ($('#mon_modal_ban_reason input[name="ban_reason"]:checked').val()) || ($('#mon_modal_ban_reason input[name="ban_reason"]').val()) || '';
    const formData = new FormData(form[0]);
    formData.append("action", "ban");
    formData.append("server", server);
    formData.append("time", time);
    formData.append("ban_reason", reason);
    return await sendRequest(formData);
}

async function sendMuteAction(server, form) {
    const time = ($('#mon_modal_time input[name="ban_time"]:checked').val()) || ($('#mon_modal_time input[name="ban_time"]').val()) || '';
    const reason = ($('#mon_modal_mute_reason input[name="mute_reason"]').val() || $('#mon_modal_mute_reason input').val() || '');
    const type = ($('#mon_modal_mute_type input[name="mute_type"]:checked').val()) || ($('#mon_modal_mute_type input[name="mute_type"]').val()) || '';
    const formData = new FormData(form[0]);
    formData.append("action", "mute");
    formData.append("server", server);
    formData.append("time", time);
    formData.append("mute_reason", reason);
    formData.append("mute_type", type);
    return await sendRequest(formData);
}

const FavouritesAPI = (() => {
    const LS_KEY = 'favourite_servers';

    const readLocal = () => {
        try { return JSON.parse(localStorage.getItem(LS_KEY)) || []; }
        catch (e) { return []; }
    };
    const writeLocal = (arr) => localStorage.setItem(LS_KEY, JSON.stringify(arr));

    return {
        list() {
            return readLocal();
        },
        has(id) {
            return this.list().includes(String(id));
        },
        async add(id) {
            id = String(id);
            const arr = readLocal();
            if (!arr.includes(id)) { arr.push(id); writeLocal(arr); }
            return true;
        },
        async remove(id) {
            id = String(id);
            writeLocal(readLocal().filter(x => x !== id));
            return true;
        },
        async toggle(id) {
            if (this.has(id)) { await this.remove(id); return false; }
            await this.add(id); return true;
        }
    };
})();

function getFavouriteId(btn) {
    const card = btn.closest('.servers__card-block, .table-mon__server-block');
    if (card) {
        const copyBtn = card.querySelector('.copy-btn[data-clipboard-text]');
        const txt = copyBtn && copyBtn.getAttribute('data-clipboard-text');
        if (txt) {
            const ip = txt.replace(/^connect\s+/i, '').trim();
            if (ip) return ip;
        }
    }
    return btn.dataset.serverIndex;
}

function spawnHeartBurst(btn) {
    const colors = ['var(--red)', '#ff7a8a', '#ffb3bd'];
    const count = 6;
    for (let i = 0; i < count; i++) {
        const p = document.createElement('span');
        p.className = 'fav-burst';
        const angle = (Math.PI * 2 * i) / count;
        const dist = 18 + Math.random() * 8;
        p.style.setProperty('--tx', Math.cos(angle) * dist + 'px');
        p.style.setProperty('--ty', Math.sin(angle) * dist + 'px');
        p.style.background = colors[i % colors.length];
        btn.appendChild(p);
        setTimeout(() => p.remove(), 650);
    }
}

function syncFavouriteButtons() {
    document.querySelectorAll('[data-favourite-btn]').forEach(btn => {
        const id = getFavouriteId(btn);
        if (id && FavouritesAPI.has(id)) btn.classList.add('is-favourite');
        else btn.classList.remove('is-favourite');
    });
}

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-favourite-btn]');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();

    const id = getFavouriteId(btn);
    if (!id) return;

    btn.classList.remove('is-bouncing');
    void btn.offsetWidth;
    btn.classList.add('is-bouncing');
    setTimeout(() => btn.classList.remove('is-bouncing'), 600);

    const isNowFav = await FavouritesAPI.toggle(id);
    btn.classList.toggle('is-favourite', isNowFav);
    if (isNowFav) spawnHeartBurst(btn);
}, true);

document.addEventListener('mousedown', (e) => {
    if (e.target.closest('[data-favourite-btn]')) e.stopPropagation();
}, true);

async function sendRequest(formData) {
    const sendBtn = $(".modal-card__body-send");
    const buttons = $(".modal-card__body-buttons");

    if (sendBtn.data("confirm-mode")) {
        clearTimeout(sendBtn.data("timeout-id"));
        sendBtn.removeData("confirm-mode timeout-id");
        sendBtn.html(
            get_translate_module_phrase('module_block_main_servers', '_inProgress') +
            '<svg class="spinner" viewBox="0 0 50 50"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg>'
        ).prop("disabled", true);

        try {
            const res = await fetch(domain, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const response = await res.json();
            $('.table-monitoring input[type="checkbox"]').prop("checked", false);
            if (typeof noty === 'function') noty(response.message, response.success ? "success" : "error");
            if (response.reload) setTimeout(() => location.reload(), 1500);
        } catch (err) {
            noty(get_translate_module_phrase('module_block_main_servers', '_actionError'), 'error');
        } finally {
            setTimeout(() => {
                sendBtn.html(originalBtnText).prop("disabled", false);
            }, 1500);
            $(".mon_selects").removeClass("input_show");
            buttons.hide();
        }
    } else {
        originalBtnText = sendBtn.html();
        sendBtn.html(get_translate_module_phrase('module_block_main_servers', '_areYouSure')).data("confirm-mode", true);
        const timeoutId = setTimeout(() => {
            sendBtn.html(originalBtnText).removeData("confirm-mode timeout-id");
        }, 3000);
        sendBtn.data("timeout-id", timeoutId);
    }
}