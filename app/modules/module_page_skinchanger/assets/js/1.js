$(document).ready(function () {
    const t = key => get_translate_module_phrase('module_page_skinchanger', key);

    function sprintf(text, ...args) {
        let i = 0;
        return text.replace(/%([+\-]?\d*\.?\d*)?([sdfeoxXb%])/g, function (match, flags, type) {
            if (type === '%') return '%';
            const arg = args[i++];
            const prec = n => flags?.includes('.') ? parseInt(flags.split('.')[1], 10) : n;
            switch (type) {
                case 's': return String(arg);
                case 'd':
                case 'i': return parseInt(arg, 10).toString();
                case 'f': return parseFloat(arg).toFixed(prec(6));
                case 'e': return parseFloat(arg).toExponential(prec(6));
                case 'o': return parseInt(arg, 10).toString(8);
                case 'x': return parseInt(arg, 10).toString(16).toLowerCase();
                case 'X': return parseInt(arg, 10).toString(16).toUpperCase();
                case 'b': return parseInt(arg, 10).toString(2);
                default: return match;
            }
        });
    }

    function renderTippy(elements, placement = 'right', options = {}) {
        const list = (Array.isArray(elements) || elements instanceof NodeList)
            ? elements
            : (elements ? [elements] : document.querySelectorAll('.sc__skin-item'));
        list.forEach(el => {
            if (!el) return;
            if (el._tippy) {
                el._tippy.destroy();
            }
            const content = el._tippyContent || el.dataset.tippyContent;
            if (!content) return;
            tippy(el, Object.assign({
                content: content,
                placement: el.dataset.tippyPlacement || placement,
                interactive: false, arrow: false, animation: 'shift-away',
                theme: 'neo', allowHTML: true,
                appendTo: () => document.body, popperOptions: { strategy: 'absolute' }
            }, options));
        });
    }

    function initSkinsTippy() {
        renderTippy(document.querySelectorAll('.sc__skins-stickers-keychains'), 'left', { interactive: true });
        renderTippy(document.querySelectorAll('.sc__skin-item'), 'bottom', { delay: [300, 0] });
        renderTippy(document.querySelectorAll('.sc__skins-checkbox[data-tippy-content]'), 'right');
    }

    function updateCollectionIdTippy(id) {
        const el = document.querySelector('.sc__id-tippy');
        if (!el || !id) return;
        const content = `<span class="sc__id copy-btn" data-clipboard-text="${parseInt(id)}">${escapeHtml(t('_ap_collection_id'))}: ${parseInt(id)} <svg><use href="/resources/img/sprite.svg#copy-list"></use></svg></span>`;
        if (el._tippy) el._tippy.destroy();
        el.setAttribute('data-tippy-content', content);
        renderTippy(el, 'top', { interactive: true });
        $('#currentCollectionId').text('#' + parseInt(id));
    }

    const API_BASE = '/skinchanger/api';

    async function api(path, options = {}) {
        const { method = 'GET', body = null, query = null, loader = null, trigger = null } = options;

        const $trigger = trigger ? $(trigger) : null;
        const $loader = loader ? $(loader) : null;

        if ($trigger) $trigger.prop('disabled', true).addClass('loading');
        if ($loader) $loader.addClass('loading');

        try {
            let url = API_BASE + path;
            if (!url.endsWith('/')) url += '/';

            if (query) {
                const params = new URLSearchParams();
                for (const [k, v] of Object.entries(query)) {
                    if (v != null && v !== '') params.append(k, v);
                }
                const qs = params.toString();
                if (qs) url += '?' + qs;
            }

            const init = { method, credentials: 'same-origin' };

            if (body && method !== 'GET') {
                init.headers = { 'Content-Type': 'application/json' };
                init.body = JSON.stringify(body);
            }

            const res = await fetch(url, init);
            if (res.status === 304) return null;

            const data = await res.json();

            if (data?.success === false && data?.error === 'rate_limited') {
                noty(t('_noty_rate_limited'), 'error');
                return null;
            }

            return data;
        } catch (err) {
            console.error('API error:', err);
            return null;
        } finally {
            if ($trigger) $trigger.prop('disabled', false).removeClass('loading');
            if ($loader) $loader.removeClass('loading');
        }
    }

    const SPECIAL_TYPES = ['record', 'coin', 'agent_ct', 'agent_t'];
    const NAMED_SIDE_TYPES = new Set([...SPECIAL_TYPES, 'knife', 'gloves']);
    const SIDE_MAP_BY_TEAM = { 0: 't', 1: 'ct', 2: 'both' };
    const SPECIAL_SIDE_MAP = { agent_ct: 'ct', agent_t: 't', record: 'both', coin: 'both' };
    const PAGE_SIZE = { collectibles: 20, items: 21, community: 6, communityPreview: 9 };
    const $categories = $('.sc__categories');
    const chooseSide = '<div class="sc__choose-side"><svg><use href="/resources/img/sprite.svg#plus"></use></svg></div>';
    const RARITY_ORDER = [
        'rarity_default', 'rarity_common', 'rarity_common_weapon',
        'rarity_uncommon_weapon', 'rarity_rare', 'rarity_rare_weapon', 'rarity_rare_character',
        'rarity_mythical', 'rarity_mythical_weapon', 'rarity_mythical_character',
        'rarity_legendary', 'rarity_legendary_weapon', 'rarity_legendary_character',
        'rarity_ancient', 'rarity_ancient_weapon', 'rarity_ancient_character',
        'rarity_contraband', 'rarity_contraband_weapon'
    ];

    let playerSkinsData = [];
    let collectionsData = [];
    let collectionsLimit = 5;
    let activeCollectionId = null;
    let communityPage = 1;
    let browsingSide = null;

    function getCardSide($card) {
        const fromInfo = $card.data('skinInfo')?.side;
        if (fromInfo) return fromInfo;
        const $sd = $card.find('.sc__skins-side').first();
        return $sd.hasClass('t') ? 't' : $sd.hasClass('ct') ? 'ct' : 'both';
    }

    function skinKey(nameId, weaponIndex, side) {
        const sideVal = side || 'both';
        return NAMED_SIDE_TYPES.has(nameId)
            ? nameId + ':' + sideVal
            : weaponIndex + ':' + sideVal;
    }

    function reinsertPlaceholder($wrapper, ph, allPlaceholders) {
        $wrapper.find(
            '.sc__skins-card.placeholder[data-name-id="' + (ph.id_name || '') + '"], ' +
            '.sc__skins-card.placeholder[data-id="' + ph.id + '"]'
        ).remove();
        const $newPh = genPlaceholder([ph])[0];
        if (!$newPh) return;
        const phIndex = allPlaceholders.indexOf(ph);
        const $existingPhs = $wrapper.children('.sc__skins-card.placeholder');
        let inserted = false;
        $existingPhs.each(function () {
            const thisNameId = $(this).attr('data-name-id');
            const thisId = $(this).attr('data-id');
            const thisIndex = allPlaceholders.findIndex(p => p.id_name === thisNameId || String(p.id) === thisId);
            if (thisIndex > phIndex) {
                $(this).before($newPh);
                inserted = true;
                return false;
            }
        });
        if (!inserted) $wrapper.append($newPh);
    }

    function updateActiveCollectionCard(skinsCount, skins) {
        if (!activeCollectionId || skinsCount === undefined || skinsCount === null) return;
        const $card = $('#myCollectionsList .sc__collection-button[data-collection-id="' + activeCollectionId + '"]');
        if (!$card.length) return;

        $card.find('.sc__collection-count').text(sprintf(t('_skins'), skinsCount));
        const realSkins = (skins || []).filter(s => !SPECIAL_TYPES.includes(s.name_id));
        const $icon = $card.find('.sc__collection-icon');
        if ($icon.find('img').length === 0 && realSkins.length > 0) {
            const firstImg = realSkins[0].img || '';
            if (firstImg) $icon.html('<img src="' + escapeHtml(firstImg) + '" alt="">');
        } else if (skinsCount === 0) {
            $icon.html('<span>?</span>');
        }
        const col = collectionsData.find(c => parseInt(c.id) === parseInt(activeCollectionId));
        if (col) {
            col.skins_count = skinsCount;
            col.updated_at = Math.floor(Date.now() / 1000);
            if (realSkins.length > 0) col.cover_img = realSkins[0].img || col.cover_img;
            else col.cover_img = '';
        }
    }

    function applyCollectionButtonsForActive($activeBtn, collectionId) {
        if (!$activeBtn || !$activeBtn.length) return;
        const isReady = $activeBtn.hasClass('ready');
        if (isReady) {
            $('#editCollectionButton').hide();
            $('#publishCollectionButton').hide();
            $('#copyReadyCollectionButton').css('display', 'flex');
        } else {
            $('#copyReadyCollectionButton').hide();
            $('#editCollectionButton').css('display', 'flex');
            if (collectionId) syncPublishButton(collectionId);
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML.replace(/"/g, '&quot;');
    }

    function makeSvgUse(href) {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
        use.setAttribute('href', href);
        svg.appendChild(use);
        return svg;
    }

    function checkAndRenderAvatar(check, steamid) {
        if (check !== 1 || !steamid) return;
        if (!Array.isArray(window.avatar) || typeof window.RenderingAvatar !== 'function') return;
        window.avatar.push(String(steamid));
    }

    function formatCount(n) {
        n = parseInt(n) || 0;
        return n >= 1000 ? (n / 1000).toFixed(1).replace(/\.0$/, '') + 'k' : n;
    }

    function formatTimestamp(ts) {
        if (!ts) return '\u2014';
        const d = new Date(ts * 1000);
        const pad = n => String(n).padStart(2, '0');
        return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function resetToCategories() {
        $categories.find('.sc__category-title, .sc__skins-list').hide();
        $('#itemsListPagination').empty();
        $('#searchResultsList').hide().empty();
        $('#categoriesList').show();
        $('.sc__search input').val('');
        $('#categoriesList .sc__category-item').show();
        $('#categoriesList .show-more').each(function () {
            $(this).closest('.sc__category-list').find('.sc__category-item').slice(6).hide();
        });
        browsingSide = null;
        skinsRarityFilter = '';
        $('#skinsRarityFilter').hide();
    }

    const floatSlider = document.getElementById('floatRange');
    const floatOutput = document.getElementById('floatValue');
    const patternSlider = document.getElementById('patternRange');
    const patternOutput = document.getElementById('patternValue');
    let updateFloat = () => { };

    if (floatSlider && floatOutput) {
        const floatRanges = [
            { id: 'factoryNew', min: 0.0001, max: 0.0699, setValue: 0.0001 },
            { id: 'minimalWear', min: 0.07, max: 0.1499, setValue: 0.07 },
            { id: 'filedTested', min: 0.15, max: 0.3799, setValue: 0.15 },
            { id: 'wellWorm', min: 0.38, max: 0.4499, setValue: 0.38 },
            { id: 'battleScared', min: 0.45, max: 0.9999, setValue: 0.45 }
        ];
        const floatRadios = Object.fromEntries(floatRanges.map(r => [r.id, document.getElementById(r.id)]));

        updateFloat = function (raw) {
            const fmin = parseFloat(floatSlider.min || 0.0001), fmax = parseFloat(floatSlider.max || 0.9999);
            const v = Math.min(Math.max(parseFloat(String(raw).replace(',', '.')) || fmin, fmin), fmax);
            floatSlider.value = floatOutput.value = v.toFixed(4);
            const range = floatRanges.find(r => v >= r.min && v <= r.max);
            if (range && floatRadios[range.id]) floatRadios[range.id].checked = true;
        };

        floatRanges.forEach(r => floatRadios[r.id]?.addEventListener('change', () => updateFloat(r.setValue)));
        floatSlider.addEventListener('input', () => updateFloat(floatSlider.value));
        floatOutput.addEventListener('input', () => updateFloat(floatOutput.value));
        updateFloat(floatSlider.value || 0.0001);
    }

    if (patternSlider && patternOutput) {
        function updatePattern() {
            patternOutput.value = patternSlider.value;
            const v = parseFloat(patternSlider.value);
            const pct = ((v - parseFloat(patternSlider.min || 0)) / (parseFloat(patternSlider.max || 100) - parseFloat(patternSlider.min || 0))) * 100;
            patternSlider.style.background = `linear-gradient(to right, var(--span) ${pct}%, var(--input-form) ${pct}%)`;
        }
        updatePattern();
        patternSlider.oninput = updatePattern;
    }

    function makeSkinItem(cls, weaponId, skinId, imgSrc, altText, isNew) {
        const newBadge = isNew ? '<div class="sc__skin-new">NEW</div>' : '';
        const $item = $(
            `<div class="sc__skin-item ${escapeHtml(cls || '')}" data-weapon-id="${escapeHtml(String(weaponId))}" data-skin-id="${escapeHtml(String(skinId || ''))}">
                ${chooseSide}
                <img class="lazy" data-src="${escapeHtml(imgSrc || '')}" alt="${escapeHtml(altText || '')}">
                ${newBadge}
            </div>`
        );

        if (altText && imgSrc) {
            const rarity = cls || '';
            const displayName = escapeHtml(altText);
            const escapedImgSrc = escapeHtml(imgSrc);
            const html = `<div class="sc__collection-skin-preview ${rarity}">
                <div class="sc__collection-img-preview"><img src="${escapedImgSrc}" alt=""></div>
                <div class="sc__collection-skin-preview-name">${displayName}</div>
            </div>`;
            $item[0]._tippyContent = html;
        }

        return $item;
    }

    function buildDecorationHtml(skin) {
        const stickers = (skin.stickers || []).filter(s => s && s.image);
        const keychain = skin.keychain || null;
        if (!stickers.length && !keychain) return '';
        let decorInner = '';
        if (stickers.length) {
            const stickersHtml = stickers.map(s => `<img class="sc__skins-sticker" src="${escapeHtml(s.image)}" alt="">`).join('');
            const tippyHtml = `<div class="sc__skins-decoration-wrapper">${stickersHtml}</div>`;
            decorInner += `<div class="sc__skins-stickers-keychains" data-tippy-content="${escapeHtml(tippyHtml)}"><svg><use href="/app/modules/module_page_skinchanger/assets/img/icons.svg#sticker"></use></svg></div>`;
        }
        if (keychain) {
            const tippyHtml = `<div class="sc__skins-decoration-wrapper"><img class="sc__skins-keychain" src="${escapeHtml(keychain.image)}" alt=""></div>`;
            decorInner += `<div class="sc__skins-stickers-keychains" data-tippy-content="${escapeHtml(tippyHtml)}"><svg><use href="/app/modules/module_page_skinchanger/assets/img/icons.svg#keychain-2"></use></svg></div>`;
        }
        return `<div class="sc__skins-decoration">${decorInner}</div>`;
    }

    function makeSkinCard(skin) {
        const side = skin.side || 'both';
        const rarity = escapeHtml(skin.id_rarity || '');
        const nameId = escapeHtml(skin.name_id || '');
        const imgSrc = escapeHtml(skin.img || '');
        const weaponName = escapeHtml(skin.weapon_name || '');
        const floatLabel = escapeHtml(skin.float_label || '');
        const skinName = escapeHtml(skin.tag ? skin.tag : skin.skin_name || '');
        const stattrackHtml = skin.stattrack && skin.name_id !== 'gloves' && parseInt(skin.stattrack_count) > 0
            ? `<span class="stattrack">${parseInt(skin.stattrack_count)}</span>`
            : '';

        const isSpecial = SPECIAL_TYPES.includes(skin.name_id);
        const hoverContentHtml = !isSpecial
            ? `<div class="sc__skins-hover-content">
            <svg><use href="/resources/img/sprite.svg#filters"></use></svg>
            <div class="sc__skins-hover-text">${escapeHtml(t('_configure_skin'))}</div></div>`
            : '';
        const hoverHtml = `<div class="sc__skins-hover-layer"${!isSpecial ? ' data-openmodal="skinSettings"' : ''}>
            ${hoverContentHtml}
            <label class="sc__skins-checkbox" data-tippy-content="${escapeHtml(t('_select_skin'))}" for="sc__skins-checkbox_${escapeHtml(String(skin.weapon_index || 0))}${escapeHtml(skin.side || 'both')}"><input type="checkbox" id="sc__skins-checkbox_${escapeHtml(String(skin.weapon_index || 0))}${escapeHtml(skin.side || 'both')}" data-weapon-index="${escapeHtml(String(skin.weapon_index || 0))}" data-side="${escapeHtml(skin.side || 'both')}" value=""></label>
            </div>`;

        const decorationHtml = buildDecorationHtml(skin);

        const $card = $(
            `<div class="sc__skins-card ${rarity}" data-id="${skin.weapon_index}" data-name-id="${nameId}">
                <div class="sc__skins-side ${side}"><img src="/app/modules/module_page_skinchanger/assets/img/sides/${side}.svg" alt=""></div>
                <img class="sc__skins-image lazy" data-src="${imgSrc}" alt="">
                <div class="sc__skins_bg"></div>
                <div class="sc__skins-details">
                    <span class="sc__skins-float">${floatLabel}</span>
                    <div class="sc__skins-type-weapon">${weaponName}${stattrackHtml}</div>
                    <div class="sc__skins-name">${skinName}</div>
                </div>
                ${hoverHtml}
                <button class="sc__skins-open-action" type="button"><span></span><span></span><span></span></button>
                ${decorationHtml}
            </div>`
        );

        $card.data('skinInfo', skin);
        return $card;
    }

    function genPlaceholder(placeholders, exclude = new Set()) {
        const addSkinText = escapeHtml(t('_add_skin'));
        return placeholders.filter(p => !exclude.has(p.id_name) && !exclude.has(p.id)).map(p => $(
            `<div class="sc__skins-card placeholder" data-id="${escapeHtml(String(p.id))}" data-name-id="${escapeHtml(p.id_name)}">
                <div class="sc__skins-placeholder-content">
                    <svg><use href="${escapeHtml(p.image)}"></use></svg>
                    <div class="sc__skins-placeholder-wrapper">
                        <span>${addSkinText}</span>
                        <span>${escapeHtml(p.name)}</span>
                    </div>
                </div>
            </div>`
        ));
    }

    function buildSkinPreviewTooltip(skin) {
        const rarity = skin.rarity ? escapeHtml(skin.rarity) : '';
        const imgSrc = escapeHtml(skin.img || '');
        const weaponName = escapeHtml(skin.weapon_name || '');
        const skinName = escapeHtml(skin.name || '');
        const displayName = skinName || weaponName;

        let sideText;
        const team = parseInt(skin.team);
        if (team === 0) sideText = escapeHtml(t('_t_side'));
        else if (team === 1) sideText = escapeHtml(t('_ct_side'));
        else sideText = escapeHtml(t('_both_sides'));

        const floatLabel = skin.float_label ? escapeHtml(skin.float_label) : '—';
        const pattern = parseInt(skin.pattern) || 0;
        const stattrack = parseInt(skin.stattrack) || 0;
        const stattrakCount = parseInt(skin.stattrack_count) || 0;
        const tag = escapeHtml(skin.tag || '');

        const details = [
            [escapeHtml(t('_type')), weaponName],
            [escapeHtml(t('_team')), sideText],
            [escapeHtml(t('_float')), floatLabel],
            [escapeHtml(t('_pattern')), pattern || '—'],
            ['StatTrak\u2122', stattrack ? stattrakCount : '—'],
            [escapeHtml(t('_nameTag')), tag || '—']
        ];
        const detailsHtml = details.map(([title, value]) => `
                <div class="sc__collection-skin-preview-details">
                <div class="sc__collection-skin-preview-title">${title}</div>
                <div class="sc__collection-skin-preview-value">${value}</div></div>`).join('');

        let html = `<div class="sc__collection-skin-preview ${rarity}">
                <div class="sc__collection-img-preview"><img src="${imgSrc}" alt=""></div>
                <div class="sc__collection-skin-preview-name">${displayName}</div>
                <div class="sc__collection-skin-preview-info">${detailsHtml}</div>`;

        const stickers = (skin.stickers || []).filter(s => s && s.image);
        const keychain = skin.keychain;
        if (stickers.length || keychain) {
            html += '<div class="sc__collection-skin-preview-stickers-wrapper">' +
                '<div class="sc__collection-skin-preview-stickers-keychain">';
            stickers.forEach(s => { html += `<img src="${escapeHtml(s.image)}" alt="">`; });
            if (keychain && keychain.image) html += `<img src="${escapeHtml(keychain.image)}" alt="">`;
            html += '</div>';

            let namesText = '';
            if (stickers.length) {
                namesText += `${escapeHtml(t('_stickers'))}: ${stickers.map(s => escapeHtml((s.name || '').replace(/^Наклейка\s*\|\s*/i, '').replace(/^Sticker\s*\|\s*/i, ''))).join(', ')}`;
            }
            if (keychain && keychain.name) {
                if (namesText) namesText += '<br>';
                namesText += `${escapeHtml(t('_keychain'))}: ${escapeHtml(keychain.name.replace(/^Брелок\s*\|\s*/i, '').replace(/^Keychain\s*\|\s*/i, ''))}`;
            }
            if (namesText) html += `<div class="sc__collection-skin-preview-stickers-keychain-names">${namesText}</div>`;
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    function renderMyCollections(data) {
        collectionsData = data.collections || [];
        collectionsLimit = data.limit || 5;
        activeCollectionId = null;

        if (!collectionsData.length) {
            api('/player/collections', { method: 'POST', body: { name: t('_main_collection') } }).then(res => {
                if (res?.success) loadMyCollections(true);
            });
            return;
        }

        const $list = $('#myCollectionsList').empty();
        const remaining = collectionsLimit - collectionsData.length;

        const hasSlots = collectionsData.length < collectionsLimit;
        const desc = hasSlots
            ? sprintf(t('_available_slots'), remaining, collectionsLimit)
            : sprintf(t('_limit'), collectionsData.length, collectionsLimit);
        $list.append(
            `<div class="sc__add-button sc__collection-button${hasSlots ? '' : ' sc__collection-limit'}"${hasSlots ? ' data-openmodal="newCollection"' : ''}>
                <div class="sc__add-icon"><svg><use href="/resources/img/sprite.svg#${hasSlots ? 'plus' : 'lock'}"></use></svg></div>
                <div class="sc__add-info">
                    <div class="sc__add-name">${escapeHtml(t('_create_collection'))}</div>
                    <div class="sc__add-description">${escapeHtml(desc)}</div>
                </div>
            </div>
            <div class="sc__subtitle">${escapeHtml(t('_your_collections'))}</div>`
        );

        collectionsData.forEach(col => {
            const isActive = parseInt(col.is_active) === 1;
            if (isActive) {
                activeCollectionId = col.id;
                $('#currentCollectionName').text(col.name);
                updateCollectionIdTippy(col.id);
            }
            const skinCount = parseInt(col.skins_count) || 0;
            const countText = sprintf(t('_skins'), skinCount);
            const coverImg = col.cover_img || '';

            $list.append(
                `<div class="sc__collection-button${isActive ? ' active' : ''}" data-collection-id="${col.id}">
                    <div class="sc__collection-icon">${coverImg ? `<img class="lazy" data-src="${escapeHtml(coverImg)}" alt="">` : '<span>?</span>'}</div>
                    <div class="sc__collection-info">
                        <div class="sc__collection-name">${escapeHtml(col.name)}</div>
                        <div class="sc__collection-count">${escapeHtml(countText)}</div>
                    </div>
                    <div class="sc__goto"><span></span><span></span><span></span></div>
                </div>`
            );
        });

        if (!activeCollectionId && collectionsData.length) {
            const first = collectionsData[0];
            activeCollectionId = first.id;
            $('#currentCollectionName').text(first.name);
            updateCollectionIdTippy(first.id);
            $list.find('.sc__collection-button[data-collection-id="' + first.id + '"]').addClass('active');
            api('/player/collections/activate', { method: 'POST', body: { collection_id: first.id } });
        }

        syncPublishButton(activeCollectionId);

        const activeCol = collectionsData.find(c => parseInt(c.id) === parseInt(activeCollectionId));
        const isDefaultActive = activeCol && parseInt(activeCol.is_default) === 1;
        if (isDefaultActive) {
            $('#editCollectionButton').hide();
            $('#publishCollectionButton').hide();
            $('#copyReadyCollectionButton').css('display', 'flex');
        } else if (!$('#closeEditCollectionButton').is(':visible')) {
            $('#copyReadyCollectionButton').hide();
            $('#editCollectionButton').css('display', 'flex');
        }
    }

    function attachPagination($el, page, totalPages, onChange, scrollEl) {
        if (!$el || !$el.length) return;
        if (totalPages <= 1) { $el.empty().off('click'); return; }
        $el.html(renderPagination(page, totalPages));
        $el.off('click').on('click', 'a[data-page]', function () {
            const n = parseInt($(this).data('page'));
            if (isNaN(n)) return;
            onChange(n);
            if (scrollEl) scrollEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }

    function renderDefaultCollections(data) {
        const collections = data.collections || [];
        defaultCollectionsCache = collections;
        const $block = $('#defaultCollectionsBlock');
        const $list = $('#defaultCollectionsList').empty();

        if (!collections.length) { $block.hide(); return; }

        if (!$('#categoriesList').is(':visible')) $block.show();

        collections.forEach(col => {
            const skinCount = parseInt(col.skins_count) || 0;
            const countText = sprintf(t('_skins'), skinCount);
            const coverImg = col.cover_img || '';

            $list.append(
                `<div class="sc__collection-button ready" data-install-collection="${parseInt(col.id)}">
                    <div class="sc__collection-icon">${coverImg ? `<img class="lazy" data-src="${escapeHtml(coverImg)}" alt="">` : '<span>?</span>'}</div>
                    <div class="sc__collection-info">
                        <div class="sc__collection-name">${escapeHtml(col.name)}</div>
                        <div class="sc__collection-count">${escapeHtml(countText)}</div>
                    </div>
                </div>`
            );
        });
    }

    let communityCollectionsCache = [];
    let defaultCollectionsCache = [];
    let viewingCommunityCollectionId = null;
    let searchCollectionsTimer = null;

    function renderCommunityCollections(data) {
        const collections = data.collections || [];
        communityCollectionsCache = collections;
        const $main = $('#communityCollectionsMain').empty();
        const $pagination = $('#communityPagination');

        if (!collections.length) {
            $main.append($('<div class="no-data">').text(t('_no_community_collections')));
            $pagination.hide();
            return;
        }

        const $wrapper = $('<div class="sc__collections-wrapper">');
        collections.forEach(col => {
            const isLiked = !!col.player_liked;
            const previews = col.skins_preview || [];
            const authorSteamid = col.steamid || '';
            const authorName = col.player_name || 'Unnamed';
            const authorUrl = authorSteamid ? '/profiles/' + encodeURIComponent(authorSteamid) + '/?search=1' : '#';
            const $card = $(`<div class="sc__collection-card" data-collection-id="${col.id}">`);
            const $header = $(
                `<div class="sc__collection-card-header">
                    <div class="sc__collection-left">
                        <div class="sc__collection-title">${escapeHtml(col.name)}</div>
                        <a href="${authorUrl}" class="sc__collection-author" id="name" nameid="${escapeHtml(authorSteamid)}" data-tippy-content="${escapeHtml(t('_collection_author'))}" data-tippy-placement="top">${escapeHtml(authorName)}</a></div>
                        <div class="sc__collection-right ml-auto">
                        <div class="sc__collection-downloads" data-tippy-content="${escapeHtml(sprintf(t('_installations'), formatCount(col.installs_count)))}" data-tippy-placement="top">
                        <svg><use href="/resources/img/sprite.svg#cloud-load"></use></svg>${formatCount(col.installs_count)}</div>
                        <div class="sc__collection-likes${isLiked ? ' voted' : ''}" data-collection-id="${col.id}" data-tippy-content="${escapeHtml(sprintf(t('_collection_ratings'), formatCount(col.likes_count)))}" data-tippy-placement="top">
                        <svg><use href="/resources/img/sprite.svg#heart"></use></svg>
                        <span class="likes-count">${formatCount(col.likes_count)}</span></div>
                        <button class="button active button-icon" type="button" data-install-collection="${col.id}" data-tippy-content="${escapeHtml(t('_copy_collection'))}" data-tippy-placement="top">
                        <svg><use href="/resources/img/sprite.svg#copy-list"></use></svg></button>
                    </div>
                </div>`
            );

            const $content = $('<div class="sc__collection-card-content">');
            previews.forEach(skin => {
                const rarityClass = skin.rarity ? ' ' + escapeHtml(skin.rarity) : '';
                const tippyHtml = buildSkinPreviewTooltip(skin);
                const imgHtml = skin.img ? `<img alt="" src="${escapeHtml(skin.img)}">` : '';
                $content.append(
                    `<div class="sc__collection-skin${rarityClass}" aria-expanded="false"
                    data-tippy-placement="left"
                    data-tippy-content="${escapeHtml(tippyHtml)}">${imgHtml}</div>`
                );
            });
            for (let i = previews.length; i < PAGE_SIZE.communityPreview; i++) {
                $content.append('<div class="sc__collection-skin"></div>');
            }
            $content.append(
                `<button class="sc__collection-skin-button show-collection" type="button" data-install-collection="${col.id}">
                ${escapeHtml(t('_go_to_collection'))}
                <svg><use href="/resources/img/sprite.svg#arrow-top-tight-circle"></use></svg></button>`
            );

            $card.append($header).append($content);
            $wrapper.append($card);
            checkAndRenderAvatar(col.checked_avatar, col.steamid);
        });

        RenderingAvatar();

        $main.append($wrapper);

        const total = parseInt(data.total) || 0;
        const totalPages = Math.ceil(total / PAGE_SIZE.community);
        $pagination.html(renderPagination(communityPage, totalPages)).show();
        $pagination.off('click').on('click', 'a[data-page]', function () {
            communityPage = parseInt($(this).data('page'));
            loadCommunityCollections();
        });

        $wrapper[0].querySelectorAll('[data-tippy-content]').forEach(el => {
            const isCollectionSkin = el.classList.contains('sc__collection-skin');
            const options = { interactive: true };
            if (isCollectionSkin) {
                options.delay = [300, 0];
            }
            if (!el._tippy) renderTippy(el, el.dataset.tippyPlacement || 'top', options);
        });
    }

    const collectibles = {
        sticker: { data: [], filtered: [] },
        keychain: { data: [], filtered: [] }
    };
    let collectiblesType = 'sticker';
    let collectiblesPage = 1;
    let collectiblesSearchQuery = '';
    let collectiblesRarityFilter = '';
    let collectiblesSearchTimer = null;

    function renderCollectibles() {
        const items = collectibles[collectiblesType].filtered;
        const totalPages = Math.ceil(items.length / PAGE_SIZE.collectibles);
        const start = (collectiblesPage - 1) * PAGE_SIZE.collectibles;
        const page = items.slice(start, start + PAGE_SIZE.collectibles);
        const $wrapper = $('#collectiblesWrapper').empty();

        page.forEach(item => {
            const rarity = escapeHtml(item.id_rarity || 'rarity_default');
            const id = parseInt(item.id) || 0;
            const image = escapeHtml(item.image || '');
            const name = escapeHtml(item.name || '');
            const $el = $(
                `<div class="sc__modal-sticker-keychain ${rarity}"
                data-type="${collectiblesType}"
                data-id="${id}">
                <img class="lazy" data-src="${image}" alt="">
                <span>${name}</span>
                </div>`
            );

            const html = `<div class="sc__collection-skin-preview ${rarity}">
                <div class="sc__collection-img-preview"><img src="${image}" alt=""></div>
                <div class="sc__collection-skin-preview-name">${name}</div>
            </div>`;
            $el[0]._tippyContent = html;

            $wrapper.append($el);
        });

        $wrapper[0].querySelectorAll('.sc__modal-sticker-keychain').forEach(el => {
            if (el._tippy) el._tippy.destroy();
            renderTippy(el, 'bottom', { delay: [300, 0] });
        });

        attachPagination(
            $('#collectiblesPagination'), collectiblesPage, totalPages,
            n => { collectiblesPage = n; renderCollectibles(); },
            document.getElementById('collectiblesWrapper')
        );
    }

    let itemsListPage = 1;
    let itemsListAll = [];
    let itemsListFull = [];
    let itemsListSkinInfo = null;
    let itemsListCfg = null;

    function renderItemsListPage() {
        const items = itemsListAll;
        const usePagination = itemsListCfg && (itemsListCfg.path === '/music' || itemsListCfg.path === '/coins');
        const totalPages = usePagination ? Math.ceil(items.length / PAGE_SIZE.items) : 1;
        const page = usePagination ? items.slice((itemsListPage - 1) * PAGE_SIZE.items, itemsListPage * PAGE_SIZE.items) : items;
        const $skinsList = $('#weaponSkinsList').empty();

        page.forEach(item => {
            const rarity = escapeHtml(item.id_rarity || '');
            const itemId = parseInt(item.id) || 0;
            const field = escapeHtml(itemsListCfg.field);
            const imgSrc = escapeHtml(item.image || '');
            const name = escapeHtml(item.name || '');
            const tippy = name ? ` data-tippy-content="${name}" data-tippy-placement="top"` : '';
            $skinsList.append($(
                `<div class="sc__skin-item sc__special-item ${rarity}" data-item-id="${itemId}" data-field="${field}"${tippy}>${itemsListCfg.team === null ? chooseSide : ''}<img class="lazy" data-src="${imgSrc}" alt="${name}"></div>`
            ));
        });


        $skinsList.data('itemSkinInfo', itemsListSkinInfo);
        $skinsList.data('itemConfig', itemsListCfg);

        attachPagination(
            $('#itemsListPagination'), itemsListPage, totalPages,
            n => { itemsListPage = n; renderItemsListPage(); },
            $('#weaponSkinsList')[0]
        );
        renderTippy(document.querySelectorAll('.sc__skin-item'), 'bottom', { delay: [300, 0] });
    }

    function renderItemsList(items, skinInfo, cfg) {
        if (cfg.filterKey) items = items.filter(item => item[cfg.filterKey] === cfg.filterVal);

        items = items.slice().sort(function (a, b) {
            const ai = RARITY_ORDER.indexOf(a.id_rarity || '');
            const bi = RARITY_ORDER.indexOf(b.id_rarity || '');
            return (bi === -1 ? 999 : bi) - (ai === -1 ? 999 : ai);
        });

        skinsRarityFilter = '';
        currentWeaponData = null;
        itemsListFull = items;
        itemsListAll = items;
        itemsListPage = 1;
        itemsListSkinInfo = skinInfo;
        itemsListCfg = cfg;

        buildSkinsRarityFilter(items);

        const $catTitle = $categories.find('.sc__category-title');
        const titleMap = { agent_ct: t('_cat_ct_agents'), agent_t: t('_cat_t_agents'), record: t('_cat_music_kits'), coin: t('_cat_coins'), knife: t('_cat_knives'), gloves: t('_cat_gloves') };
        $catTitle.find('.sc__choosenWeapon-name').text(titleMap[skinInfo.name_id] || '');

        renderItemsListPage();

        $('#categoriesList').hide();
        $catTitle.show();
        $('#weaponSkinsList').show();
    }

    function applySkinsRefresh(skins, placeholders, skinsCount) {
        skins = Array.isArray(skins) ? skins : [];
        playerSkinsData = skins;
        const $wrapper = $('#mySkinsWrapper').empty().removeClass('edit-mode');
        skins.forEach(skin => $wrapper.append(makeSkinCard(skin)));
        $('.sc__skins-fake-wrapper').hide();
        initSkinsTippy();
        clearEditSelectionState();
        const needEdit = localStorage.getItem('sc_edit_mode') === '1';
        $('.sc__no-skins').toggle(!needEdit && skins.length === 0);
        if (needEdit) {
            $wrapper.addClass('edit-mode');
            if (Array.isArray(placeholders)) {
                $('#editCollectionButton').hide();
                $('#resetCollectionButton, #closeEditCollectionButton').css('display', 'flex');
                const existingNameIds = new Set();
                $wrapper.find('.sc__skins-card').each((_, el) => {
                    existingNameIds.add($(el).attr('data-name-id') || $(el).attr('data-id'));
                });
                $wrapper.append(genPlaceholder(placeholders, existingNameIds));
            } else {
                $wrapper.addClass('edit-mode');
                const existingNameIds = new Set();
                $wrapper.find('.sc__skins-card').each((_, el) => {
                    existingNameIds.add($(el).attr('data-name-id') || $(el).attr('data-id'));
                });
                api('/player/placeholders').then(res => {
                    if (res?.success && res.data) $wrapper.append(genPlaceholder(res.data, existingNameIds));
                });
            }
        }

        updateActiveCollectionCard(skinsCount, skins);
    }

    function applySkinPatch(skins, placeholders, skinsCount, weaponIndex) {
        skins = Array.isArray(skins) ? skins : [];
        playerSkinsData = skins;
        const $wrapper = $('#mySkinsWrapper');
        const wasEditMode = $wrapper.hasClass('edit-mode');

        clearEditSelectionState();

        const serverMap = {};
        skins.forEach(s => {
            serverMap[skinKey(s.name_id, s.weapon_index, s.side)] = s;
        });

        const domCards = {};
        $wrapper.find('.sc__skins-card:not(.placeholder)').each(function () {
            const $card = $(this);
            const nameId = $card.attr('data-name-id') || '';
            domCards[skinKey(nameId, $card.attr('data-id'), getCardSide($card))] = $card;
        });

        for (const key in domCards) {
            if (!serverMap[key]) {
                const nameId = domCards[key].attr('data-name-id') || '';
                domCards[key].remove();
                if (wasEditMode && Array.isArray(placeholders)) {
                    const ph = placeholders.find(p => p.id_name === nameId);
                    if (ph && !$wrapper.find('.sc__skins-card.placeholder[data-name-id="' + nameId + '"]').length) {
                        const $newPh = genPlaceholder([ph])[0];
                        if ($newPh) $wrapper.append($newPh);
                    }
                }
            }
        }

        skins.forEach((skin, skinIdx) => {
            const key = skinKey(skin.name_id, skin.weapon_index, skin.side);
            const $existing = domCards[key];
            const $newCard = makeSkinCard(skin);
            const nameId = skin.name_id || '';
            const phSelector = '.sc__skins-card.placeholder[data-name-id="' + nameId + '"], .sc__skins-card.placeholder[data-id="' + skin.weapon_index + '"]';

            if ($existing && $existing.closest('html').length) {
                $wrapper.find(phSelector).remove();
                $existing.replaceWith($newCard);
            } else {
                $wrapper.find(phSelector).first().remove();
                let inserted = false;
                $wrapper.children('.sc__skins-card:not(.placeholder)').each(function () {
                    const cardWi = parseInt($(this).attr('data-id'));
                    const cardSide = getCardSide($(this));
                    const cardIdx = skins.findIndex(s => s.weapon_index === cardWi && (s.side || 'both') === cardSide);
                    if (cardIdx > skinIdx) {
                        $(this).before($newCard);
                        inserted = true;
                        return false;
                    }
                });
                if (!inserted) {
                    const $placeholders = $wrapper.children('.sc__skins-card.placeholder');
                    $placeholders.length ? $placeholders.first().before($newCard) : $wrapper.append($newCard);
                }
            }
        });

        $('.sc__skins-fake-wrapper').hide();
        initSkinsTippy();

        if (wasEditMode) {
            $wrapper.addClass('edit-mode');
        }

        const needEdit = wasEditMode || localStorage.getItem('sc_edit_mode') === '1';
        $('.sc__no-skins').toggle(!needEdit && skins.length === 0);
        if (needEdit) {
            $wrapper.addClass('edit-mode');
            $('#editCollectionButton').hide();
            $('#resetCollectionButton, #closeEditCollectionButton').css('display', 'flex');
        }

        updateActiveCollectionCard(skinsCount, skins);
    }

    async function refreshPlayerSkins() {
        const res = await api('/player/skins', { loader: $('#mySkinsWrapper') });
        if (res?.success) applySkinsRefresh(res.data, null);
    }

    async function loadMyCollections(autoEnter) {
        const res = await api('/player/collections', { loader: $('#myCollectionsList') });
        if (res?.success) renderMyCollections(res.data);
    }

    async function loadCommunityCollections() {
        const sort = $('input[name="collections-sort"]:checked').attr('id') || '';
        const sortMap = { popular: 'popular', liked: 'liked', newCollections: 'new', last7Days: 'last7d' };

        const res = await api('/collections', {
            query: {
                page: communityPage,
                order: 'likes',
                search: ($('#searchCollections').val() || '').trim(),
                sort: sortMap[sort] || '',
                with_downloads: $('#oblyWithDowloads').is(':checked') ? 1 : 0,
                liked_me: $('#oblyLikedMe').is(':checked') ? 1 : 0,
                my_only: $('#oblyMyCollections').is(':checked') ? 1 : 0
            },
            loader: $('#communityCollectionsMain')
        });
        if (res?.success) renderCommunityCollections(res.data);
    }

    async function loadCollectibles(type, callback) {
        if (collectibles[type].data.length) { callback?.(); return; }
        const res = await api('/collectibles', { query: { type }, loader: $('#collectiblesWrapper') });
        if (res?.success && Array.isArray(res.data)) {
            collectibles[type].data = res.data;
            collectibles[type].filtered = res.data;
            buildCollectiblesRarityFilter(res.data);
        }
        callback?.();
    }

    function buildRarityFilterHtml(items, radioName, allLabel, defaultRarity) {
        const seen = {};
        items.forEach(item => {
            const r = item.id_rarity || defaultRarity;
            if (r) seen[r] = true;
        });
        const sorted = RARITY_ORDER.filter(r => seen[r]).reverse();
        Object.keys(seen).forEach(r => { if (!sorted.includes(r)) sorted.push(r); });
        const visible = defaultRarity ? sorted.filter(r => r !== defaultRarity) : sorted;
        if (visible.length <= 1) return null;
        const bolt = '<svg class="sc__rarity-icon"><use href="/resources/img/sprite.svg#bolt"></use></svg>';
        let html = `<label class="sc__modal-custom-radio"><input type="radio" name="${radioName}" value="" checked><span>${escapeHtml(allLabel)}</span></label>`;
        visible.forEach(r => {
            html += `<label class="sc__modal-custom-radio"><input type="radio" name="${radioName}" value="${r}"><span class="${r}">${bolt}</span></label>`;
        });
        return html;
    }

    function buildCollectiblesRarityFilter(items) {
        const $filter = $('#collectiblesRarityFilter');
        const html = buildRarityFilterHtml(items, 'stickers-type', t('_all'), 'rarity_default');
        if (!html) { $filter.html(''); collectiblesRarityFilter = ''; return; }
        $filter.html(html);
        collectiblesRarityFilter = '';
    }

    function filterCollectibles() {
        let src = collectibles[collectiblesType].data;
        const q = collectiblesSearchQuery.toLowerCase();
        if (q) {
            src = src.filter(item => {
                const name = (item.name || '').toLowerCase();
                const nameEn = (item.name_en || '').toLowerCase();
                return name.includes(q) || nameEn.includes(q);
            });
        }
        if (collectiblesRarityFilter) src = src.filter(item => (item.id_rarity || 'rarity_default') === collectiblesRarityFilter);
        collectibles[collectiblesType].filtered = src;
        collectiblesPage = 1;
    }

    function initCollectibles() {
        collectiblesRarityFilter = '';
        const cached = collectibles[collectiblesType].data;
        if (cached.length) buildCollectiblesRarityFilter(cached);
        loadCollectibles(collectiblesType, () => {
            filterCollectibles();
            renderCollectibles();
        });
    }

    function applyCacheInfo(data) {
        const $items = $('.sc__cache-status[data-cache-type]');
        if (!$items.length) return;
        $items.each(function () {
            const type = $(this).data('cache-type');
            const info = data[type];
            if (!info) return;

            const label = {
                skins: t('_cache_type_skins'), stickers: t('_cache_type_stickers'), keychains: t('_cache_type_keychains'),
                agents: t('_cache_type_agents'), coins: t('_cache_type_coins'), music: t('_cache_type_music'), collections: t('_cache_type_collections')
            }[type] || type;

            const dateStr = info.date || t('_cache_no_data');
            const tooltip = info.exists
                ? label + ': ' + dateStr + (info.stale ? ' ' + t('_cache_stale') : ' ' + t('_cache_actual'))
                : label + ': ' + t('_cache_absent');

            $(this)
                .removeClass('actual-date not-actual-date')
                .addClass(info.stale ? 'not-actual-date' : 'actual-date')
                .attr('data-tippy-content', tooltip);

            if ($(this)[0]._tippy) {
                $(this)[0]._tippy.setContent(tooltip);
            } else {
                renderTippy($(this)[0], 'top');
            }
        });
    }

    async function loadCacheInfo() {
        const $items = $('.sc__cache-status[data-cache-type]');
        if (!$items.length || $('#saveSettingsBtn').length) return;
        const res = await api('/admin/cache');
        if (res?.success) applyCacheInfo(res.data);
    }

    function editModeFlag() {
        return ($('#closeEditCollectionButton').is(':visible') || !playerSkinsData.length) ? 1 : 0;
    }

    async function assignSkinToTeams(weaponIndex, skinId, side, callback) {
        const res = await api('/player/skins', {
            method: 'POST',
            body: {
                weapon_index: weaponIndex,
                skin_id: skinId || 0,
                side: side || 'both',
                collection_id: activeCollectionId || 0,
                edit_mode: editModeFlag()
            },
            loader: $('#mySkinsWrapper')
        });
        if (res?.success) {
            applySkinPatch(res.skins, res.placeholders, res.skins_count, weaponIndex);
            noty(t('_noty_skin_set'), 'success');
            callback?.();
        }
    }

    async function resetSkinForTeams(weaponIndex, side) {
        const $wrapper = $('#mySkinsWrapper');
        const sideVal = side || 'both';
        const removedCard = $wrapper.find('.sc__skins-card:not(.placeholder)[data-id="' + weaponIndex + '"]').filter(function () {
            return getCardSide($(this)) === sideVal;
        });
        const removedNameId = removedCard.attr('data-name-id') || '';

        const res = await api('/player/skins', {
            method: 'DELETE',
            body: {
                weapon_index: weaponIndex,
                side: side || 'both',
                collection_id: activeCollectionId || 0,
                edit_mode: editModeFlag()
            },
            loader: $wrapper
        });
        if (res?.success) {
            const skins = Array.isArray(res.skins) ? res.skins : [];
            playerSkinsData = skins;

            removedCard.remove();

            if ($wrapper.hasClass('edit-mode') && Array.isArray(res.placeholders)) {
                const stillExists = $wrapper.find('.sc__skins-card:not(.placeholder)[data-id="' + weaponIndex + '"]').length > 0;
                if (!stillExists) {
                    const ph = res.placeholders.find(p => p.id_name === removedNameId || String(p.id) === String(weaponIndex));
                    if (ph) reinsertPlaceholder($wrapper, ph, res.placeholders);
                }
            }

            clearEditSelectionState();
            if ($wrapper.hasClass('edit-mode')) $wrapper.addClass('edit-mode');
            initSkinsTippy();
            $('.sc__no-skins').toggle(!$wrapper.hasClass('edit-mode') && skins.length === 0);

            updateActiveCollectionCard(res.skins_count, skins);
            noty(t('_noty_skin_removed'), 'success');
        }
    }

    async function removeItemForCard(skinInfo) {
        const cfg = itemTypeConfig[skinInfo.name_id];
        if (!cfg) return;
        const side = cfg.team === 1 ? 'ct' : cfg.team === 0 ? 't' : (skinInfo.side || 'both');
        const $wrapper = $('#mySkinsWrapper');
        const nameId = skinInfo.name_id || '';
        const $card = $wrapper.find('.sc__skins-card:not(.placeholder)[data-name-id="' + nameId + '"]');
        const res = await api('/player/items', {
            method: 'DELETE',
            body: {
                field: cfg.field,
                side,
                collection_id: activeCollectionId || 0,
                edit_mode: editModeFlag()
            },
            loader: $wrapper
        });
        if (res?.success) {
            playerSkinsData = Array.isArray(res.skins) ? res.skins : [];
            $card.remove();
            if ($wrapper.hasClass('edit-mode') && Array.isArray(res.placeholders)) {
                const ph = res.placeholders.find(p => p.id_name === nameId);
                if (ph) reinsertPlaceholder($wrapper, ph, res.placeholders);
            }
            clearEditSelectionState();
            initSkinsTippy();
            $('.sc__no-skins').toggle(!$wrapper.hasClass('edit-mode') && playerSkinsData.length === 0);
            updateActiveCollectionCard(res.skins_count, playerSkinsData);
            noty(t('_noty_item_removed'), 'success');
        }
    }

    const itemTypeConfig = {
        agent_ct: { path: '/agents', field: 'agent', team: 1, filterKey: 'team', filterVal: 1 },
        agent_t: { path: '/agents', field: 'agent', team: 0, filterKey: 'team', filterVal: 0 },
        record: { path: '/music', field: 'music', team: null },
        coin: { path: '/coins', field: 'coin', team: null }
    };
    const itemsCache = {};

    async function showItemsList(skinInfo) {
        const cfg = itemTypeConfig[skinInfo.name_id];
        if (!cfg) return;
        const cacheKey = cfg.path;
        if (itemsCache[cacheKey]) {
            renderItemsList(itemsCache[cacheKey], skinInfo, cfg);
            return;
        }
        const res = await api(cfg.path, { loader: $('#weaponSkinsList') });
        if (res?.success && Array.isArray(res.data)) {
            itemsCache[cacheKey] = res.data;
            renderItemsList(res.data, skinInfo, cfg);
        }
    }

    function syncPublishButton(collectionId) {
        const col = collectionsData.find(c => c.id == collectionId);
        const $btn = $('#publishCollectionButton');
        if (!col || parseInt(col.skins_count) === 0 || parseInt(col.is_default) === 1) {
            $btn.hide();
        } else {
            const isPublic = parseInt(col.is_public) === 1;
            $btn.show()
                .removeClass('active')
                .toggleClass('active', !isPublic);
            $btn.find('svg use').attr('href', isPublic ? '/resources/img/sprite.svg#eye' : '/resources/img/sprite.svg#cloud-load');
            $btn.find('span.publish-label').text(t(isPublic ? '_hide_from_public' : '_publish'));
        }
    }

    async function enableEditMode($wrapper) {
        $wrapper.addClass('edit-mode');
        $('.sc__no-skins').hide();
        $('#publishCollectionButton').hide();
        $('#editCollectionButton').hide();
        $('#deleteCheckedSkinsButton').hide();
        $('#resetCollectionButton, #closeEditCollectionButton').css('display', 'flex');
        $('#myCollectionsList, #defaultCollectionsBlock').hide();
        $('.sc__categories, .sc__back-colletions, .sc__search, #categoriesList').show();
        const $cat = $('#categoriesList');
        if ($cat.length) $cat[0].scrollTop = 0;
        const existingNameIds = new Set();
        $wrapper.find('.sc__skins-card').each((_, el) => {
            existingNameIds.add($(el).attr('data-name-id') || $(el).attr('data-id'));
        });
        const res = await api('/player/placeholders', { loader: $wrapper });
        if (res?.success && res.data) $wrapper.append(genPlaceholder(res.data, existingNameIds));
    }

    function clearEditSelectionState() {
        const $wrapper = $('#mySkinsWrapper');
        $wrapper.find('.sc__skins-checkbox input[type="checkbox"]').prop('checked', false);
        $wrapper.find('.sc__skins-hover-layer.active').removeClass('active');
        $('#deleteCheckedSkinsButton').hide();
    }

    function exitEditMode() {
        localStorage.removeItem('sc_edit_mode');
        $('#mySkinsWrapper').removeClass('edit-mode');
        clearEditSelectionState();
        $('#resetCollectionButton, #closeEditCollectionButton').hide();

        applyCollectionButtonsForActive($('.sc__collection-button.active'), activeCollectionId);
        $('.sc__skins-card.placeholder').remove();
    }

    function hideBrowsingPanels() {
        $('.sc__back-colletions, .sc__search, .sc__category-title, .sc__skins-list').hide();
        skinsRarityFilter = ''; $('#skinsRarityFilter').hide();
        $('#categoriesList').hide();
        $('#itemsListPagination').empty();
    }

    function scrollToCategory(catKey) {
        $categories.find('.sc__category-title, .sc__skins-list').hide();
        skinsRarityFilter = ''; $('#skinsRarityFilter').hide();
        $('#itemsListPagination').empty();
        $('#categoriesList').show();
        const $section = $(`#categoriesList [data-category="${catKey}"]`);
        if ($section.length) {
            $section.find('.sc__category-item:hidden').show();
            $section.find('.show-more').remove();
            setTimeout(() => {
                const $cat = $('#categoriesList');
                if ($cat.length) $cat[0].scrollTop = $section[0].offsetTop - $cat[0].offsetTop;
            }, 50);
        }
    }

    $(document).on('click', '.sc__skins-checkbox', function (e) {
        e.stopPropagation();
    });

    $(document).on('change', '.sc__skins-wrapper.edit-mode .sc__skins-checkbox input[type="checkbox"]', function () {
        const $hoverLayer = $(this).closest('.sc__skins-hover-layer');
        $hoverLayer.toggleClass('active', this.checked);
        const anyChecked = $('.sc__skins-wrapper.edit-mode .sc__skins-checkbox input[type="checkbox"]:checked').length > 0;
        $('#deleteCheckedSkinsButton').toggle(anyChecked);
    });

    $(document).on('click', '#deleteCheckedSkinsButton', function () {
        const $btn = $(this);
        const checked = [];
        $('.sc__skins-wrapper.edit-mode .sc__skins-checkbox input[type="checkbox"]:checked').each(function () {
            const $card = $(this).closest('.sc__skins-card');
            checked.push({
                weapon_index: parseInt($(this).data('weapon-index')) || 0,
                side: $(this).data('side') || 'both',
                name_id: $card.attr('data-name-id') || ''
            });
        });
        if (!checked.length) return;

        openDialog({
            title: t('_sc_checked_delete'),
            message: t('_delete_checked_skins_message'),
            confirmText: t('_sc_checked_delete'),
            cancelText: t('_cancel'),
            onConfirm: async () => {
                $btn.prop('disabled', true);
                const res = await api('/player/skins/batch', {
                    method: 'DELETE',
                    body: {
                        skins: JSON.stringify(checked),
                        collection_id: activeCollectionId || 0,
                        edit_mode: 1
                    },
                    loader: $('#mySkinsWrapper')
                });
                $btn.prop('disabled', false).hide();
                if (res?.success) {
                    applySkinsRefresh(res.skins, res.placeholders, res.skins_count);
                    noty(t('_noty_skin_removed'), 'success');
                    resetToCategories();
                    enableEditMode($('#mySkinsWrapper'));
                }
            }
        });
    });

    function populateSkinModal(skinInfo) {
        const $modal = $('#skinSettings');
        $modal.data('activeSkin', skinInfo);

        const isKnifeOrGloves = skinInfo.name_id === 'knife' || skinInfo.name_id === 'gloves';
        const isGloves = skinInfo.name_id === 'gloves';
        $modal.find('.sc__modal-skin-left').toggle(!isKnifeOrGloves);
        $modal.find('.sc__modal-decoration').toggle(!isKnifeOrGloves);
        $modal.find('.sc__modal-stattrak').toggle(!isGloves);
        $('#stattrakCounter').toggle(!isGloves);

        const title = (skinInfo.skin_name ? ' ' + skinInfo.skin_name : '');
        $('#modalTitleSkinSettings').text(t('_skin_settings') + ': ' + title);

        $modal.find('.sc__modal-skin-preview-skin').attr({ 'data-src': skinInfo.img || '', src: skinInfo.img || '' });
        $modal.find('.sc__modal-skin-preview').attr('class', 'sc__modal-skin-preview ' + (skinInfo.id_rarity || ''));

        const sideTexts = { both: '_both_sides', t: '_t_side', ct: '_ct_side' };
        $modal.find('.sc__modal-skin-side').text(t(sideTexts[skinInfo.side] || '_both_sides'));
        const sideRadioMap = { t: 'modalTSide', both: 'modalBothSide', ct: 'modalCtSide' };
        $(`#${sideRadioMap[skinInfo.side] || 'modalBothSide'}`).prop('checked', true);

        $('#modalStatTrak').prop('checked', !!skinInfo.stattrack);
        $('#stattrakCounter').val(skinInfo.stattrack_count || '');

        updateFloat(skinInfo.float > 0 ? skinInfo.float : 0.0001);

        if (patternSlider && patternOutput) {
            patternSlider.value = skinInfo.pattern || 0;
            patternOutput.value = skinInfo.pattern || 0;
            patternSlider.dispatchEvent(new Event('input'));
        }

        $('#nameTag').val(skinInfo.tag || '');

        const $stickerBlocks = $modal.find('.sc__modal-sticker-block');
        $stickerBlocks.each(function (i) {
            const $sticker = $(this).find('.sc__modal-sticker');
            const stickerData = skinInfo.stickers?.[i];
            $(this).removeClass('active').removeData('stickerId');
            if (stickerData && stickerData.image) {
                $sticker.empty().append($('<img>').attr('src', stickerData.image));
                $(this).data('stickerId', stickerData.id);
            } else {
                $sticker.empty().append(makeSvgUse('/resources/img/sprite.svg#plus'));
            }
        });
        const $firstEmpty = $stickerBlocks.filter(function () { return !$(this).data('stickerId'); }).first();
        if ($firstEmpty.length) $firstEmpty.addClass('active');

        const $keychainBlock = $modal.find('.sc__modal-keychain-block');
        const $keychain = $keychainBlock.find('.sc__modal-keychain');
        $keychainBlock.removeData('keychainId');
        if (skinInfo.keychain && skinInfo.keychain.image) {
            $keychain.empty().append($('<img>').attr('src', skinInfo.keychain.image));
            $keychainBlock.data('keychainId', skinInfo.keychain.id);
        } else {
            $keychain.empty().append(makeSvgUse('/resources/img/sprite.svg#plus'));
        }
    }

    let copyTargetCollectionId = null;
    let editTargetCollectionId = null;

    function deleteActiveCollection() {
        if (!editTargetCollectionId) return;
        openDialog({
            title: t('_delete_collection'),
            message: t('_delete_collection_message'),
            confirmText: t('_delete_collection'),
            cancelText: t('_cancel'),
            onConfirm: async () => {
                const res = await api(`/player/collections/${editTargetCollectionId}`, { method: 'DELETE' });
                if (!res?.success) {
                    if (res?.error === 'last_collection') noty(t('_last_collection_error'), 'error');
                    return;
                }
                $('.popup_modal#editCollectionName .popup_modal_close').trigger('click');
                if (res.collections) {
                    renderMyCollections(res.collections);
                } else {
                    loadMyCollections();
                }
                if (Array.isArray(res.skins)) {
                    applySkinsRefresh(res.skins, null);
                } else {
                    refreshPlayerSkins();
                }
            }
        });
    }

    const categoryTranslations = {
        knives: '_cat_knives', gloves: '_cat_gloves', rifles: '_cat_rifles',
        sniper_rifles: '_cat_sniper_rifles', pistols: '_cat_pistols', heavy: '_cat_heavy',
        smg: '_cat_smg', utility: '_cat_utility', agents: '_cat_agents'
    };

    function renderCategories(data) {
        const $wrapper = $('#categoriesList').addClass('sc__category-wrapper');
        const [goToSkins, hiddenText, showAllText] = ['_go_to_skins', '_hidden', '_show_all'].map(t);
        $.each(data, (catKey, cat) => {
            if (!cat.list?.length) return;
            const catName = categoryTranslations[catKey] ? t(categoryTranslations[catKey]) : cat.name;
            const $catList = $('<div class="sc__category-list">');
            $.each(cat.list, (i, weapon) => {
                const wName = escapeHtml(weapon.name || '');
                const wImg = escapeHtml(weapon.img || '');
                const showMoreHtml = (i === 5 && cat.list.length > 6)
                    ? `<div class="show-more"><span>${escapeHtml(sprintf(hiddenText, cat.list.length - 6))}</span><p>${escapeHtml(showAllText)}</p></div>`
                    : '';
                const $item = $(
                    `<div class="sc__category-item" data-id="${escapeHtml(String(weapon.id))}" data-name_id="${escapeHtml(weapon.id_name || '')}" data-type="${escapeHtml(catKey)}" data-tippy-content="${wName}" data-tippy-placement="top">
                    <img class="lazy" data-src="${wImg}" alt="">
                    ${showMoreHtml}
                    <div class="sc__arrow" title="${escapeHtml(goToSkins)}"><svg><use href="/resources/img/sprite.svg#arrow-top-right"></use></svg></div>
                    </div>`
                );
                if (i >= 6) $item.hide();
                $catList.append($item);
                renderTippy($item[0], 'top', { interactive: false });
            });
            $wrapper.append($('<div>').attr('data-category', catKey)
                .append(`<div class="sc__subtitle">${escapeHtml(catName)}</div>`)
                .append($catList));
        });
        initCategories();
    }

    function initCategories() {
        $('.sc__category-list').each(function () {
            const $list = $(this);
            const $item = $list.find('.sc__category-item').eq(5);
            if (!$item.find('.show-more').length) return;

            ['data-tippy-content', 'data-tippy-placement'].forEach(attr => {
                if ($item.attr(attr)) $item.attr(attr + '-original', $item.attr(attr)).removeAttr(attr);
            });
            $item[0]?._tippy?.destroy();

            $item.off('click.scShowMore').on('click.scShowMore', function (e) {
                e.stopPropagation();
                $item.off('click.scShowMore');
                $item.find('.show-more').remove();
                ['data-tippy-content', 'data-tippy-placement'].forEach(attr => {
                    const val = $item.attr(attr + '-original');
                    if (val) $item.attr(attr, val).removeAttr(attr + '-original');
                });
                if (typeof tippy !== 'undefined' && $item.attr('data-tippy-content')) renderTippy($item[0], 'top', { interactive: false });
                $list.find('.sc__category-item').slice(6).show();
            });
        });
    }

    function setMyCollectionsVisible(show) {
        viewingCommunityCollectionId = null;
        isDefaultCollectionDetail = false;

        $('#myCollectionsList').toggle(show);
        $('#defaultCollectionsBlock').toggle(show && $('#defaultCollectionsList').children().length > 0);
        $('#communityCollectionsList').toggle(!show);

        if (show && !$('#closeEditCollectionButton').is(':visible')) {
            $('.sc__back-colletions, .sc__search, .sc__category-title, .sc__skins-list').hide();
            $('#categoriesList').hide();
            $('#itemsListPagination').empty();
            skinsRarityFilter = ''; $('#skinsRarityFilter').hide();
        }

        $('.sc__collections-aside').toggle(!show);
        $('.sc__collections-filters').toggle(!show);
        $('.sc__collections-info').hide();
        $('.sc__categories').toggle(show);

        $('.sc__right-area .sc__loadout').first().toggle(show);
        $('#communityLoadout').toggle(!show);
        $('#communityCollectionDetail').hide();

        if (!show) {
            $('#editCollectionButton').hide();
            $('#publishCollectionButton').hide();
            $('#copyReadyCollectionButton').hide();
        }

        if (show && !$('#closeEditCollectionButton').is(':visible')) {
            const isEditMode = localStorage.getItem('sc_edit_mode') === '1';
            $('.sc__no-skins').toggle(!isEditMode && playerSkinsData.length === 0);

            const $activeBtn = $('.sc__collection-button.active');
            if (!$activeBtn.length) return;

            const isReady = $activeBtn.hasClass('ready');
            if (isReady) {
                $('#editCollectionButton').hide();
                $('#publishCollectionButton').hide();
                $('#copyReadyCollectionButton').css('display', 'flex');
            } else if (activeCollectionId) {
                $('#copyReadyCollectionButton').hide();
                if (!isEditMode) $('#editCollectionButton').css('display', 'flex');
                syncPublishButton(activeCollectionId);
            }
        }
    }

    let isDefaultCollectionDetail = false;

    async function showDefaultCollectionDetail(collectionId) {
        const col = defaultCollectionsCache.find(c => c.id == collectionId);
        if (!col) return;
        isDefaultCollectionDetail = true;

        $('#editCollectionButton').hide();
        $('#publishCollectionButton').hide();
        $('#communityDetailInstall').show();
        $('.sc__right-area .sc__loadout').first().hide();
        $('#communityLoadout').hide();
        $('#communityPagination').hide();
        $('#communityCollectionDetail').show();
        $('#communityCollectionDetailName').text(col.name || '');
        $('#communityDetailInstall').data('install-collection', collectionId);
        $('#communityShareButton').show().attr('data-clipboard-text', location.origin + '/skinchanger/community/' + collectionId + '/');

        const $wrapper = $('#communityCollectionDetailSkins').empty();
        const res = await api(`/presets/${collectionId}`, { loader: $wrapper });
        if (res?.success && res.data) renderCollectionDetailSkins($wrapper, res.data.skins_preview || []);
    }

    function populateCollectionInfo(collectionId, data) {
        const $info = $('.sc__collections-info');
        const steamid = data.steamid;
        const $userWrapper = $info.find('.sc__collections-user-wrapper');
        $userWrapper.attr('href', `/profiles/${encodeURIComponent(steamid)}/?search=1`);
        const $img = $userWrapper.find('img');
        const $nickname = $userWrapper.find('.sc__collections-user-nickname');
        $img.attr('src', data.player_avatar).attr('id', 'avatar').attr('avatarid', steamid);
        $nickname.text(data.player_name).attr('id', 'name').attr('nameid', steamid);
        checkAndRenderAvatar(parseInt(data.checked_avatar, 10) === 1 ? 1 : 0, steamid);
        if (typeof window.RenderingAvatar === 'function') window.RenderingAvatar();
        const vals = $info.find('.sc__collections-about .value');
        vals.eq(0).text(parseInt(collectionId));
        vals.eq(1).text(formatTimestamp(data.created_at));
        vals.eq(2).text(formatTimestamp(data.updated_at || data.created_at));
        vals.eq(3).text(data.published_at ? formatTimestamp(data.published_at) : '—');
        vals.eq(4).text(formatCount(data.installs_count || 0));
        vals.eq(5).text(formatCount(data.likes_count || 0));
    }

    async function showCommunityCollectionDetail(collectionId) {
        isDefaultCollectionDetail = false;
        const col = communityCollectionsCache.find(c => c.id == collectionId);
        if (!col) return;
        viewingCommunityCollectionId = collectionId;

        $('.sc__collections-filters').hide();
        $('.sc__collections-info').show();

        populateCollectionInfo(collectionId, col);

        $('#communityLoadout').hide();
        $('#communityPagination').hide();
        $('#communityCollectionDetail').show();
        $('#communityCollectionDetailName').text(col.name || '');
        $('#communityDetailInstall').show().data('install-collection', collectionId);
        $('#communityShareButton').show().attr('data-clipboard-text', location.origin + '/skinchanger/community/' + collectionId + '/');

        const $wrapper = $('#communityCollectionDetailSkins').empty();
        const res = await api(`/collections/${collectionId}`, { loader: $wrapper });
        if (res?.success && res.data) {
            populateCollectionInfo(collectionId, res.data);
            const previews = res.data.skins_preview || [];
            renderCollectionDetailSkins($wrapper, previews);
        }
    }

    function renderCollectionDetailSkins($wrapper, previews) {
        $wrapper.empty();
        previews.forEach(skin => {
            const rarity = escapeHtml(skin.rarity || '');
            const side = SIDE_MAP_BY_TEAM[skin.team] || 'both';
            const imgSrc = escapeHtml(skin.img || '');
            const weaponName = escapeHtml(skin.weapon_name || '');
            const floatLabel = escapeHtml(skin.float_label || '');
            const skinName = escapeHtml(skin.tag ? skin.tag : skin.name || '');
            const stattrackHtml = parseInt(skin.stattrack) && parseInt(skin.stattrack_count) > 0
                ? `<span class="stattrack">${parseInt(skin.stattrack_count)}</span>`
                : '';

            const decorationHtml = buildDecorationHtml(skin);

            $wrapper.append(
                `<div class="sc__skins-card ${rarity}">
                    <div class="sc__skins-side ${side}"><img src="/app/modules/module_page_skinchanger/assets/img/sides/${side}.svg" alt=""></div>
                    <img class="sc__skins-image" src="${imgSrc}" alt="">
                    <div class="sc__skins_bg"></div>
                    <div class="sc__skins-details">
                        <span class="sc__skins-float">${floatLabel}</span>
                        <div class="sc__skins-type-weapon">${weaponName}${stattrackHtml}</div>
                        <div class="sc__skins-name">${skinName}</div>
                    </div>
                    ${decorationHtml}
                </div>`
            );
        });
        renderTippy(document.querySelectorAll('.sc__skins-stickers-keychains'), 'left', { interactive: true });
    }

    let currentSidesMenu = null, currentButton = null;
    let sidesMenuTimer = null;

    function createSidesMenu() {
        const sides = [
            { key: 'both', icon: 'both.svg', phrase: '_both_sides' },
            { key: 't', icon: 't.svg', phrase: '_t_side' },
            { key: 'ct', icon: 'ct.svg', phrase: '_ct_side' }
        ];
        const $menu = $(
            '<div class="sc__sides-list">' +
            sides.map(({ key, icon, phrase }) =>
                `<div class="sc__side-option" data-side="${key}"><img src="/app/modules/module_page_skinchanger/assets/img/sides/${icon}" alt="">${escapeHtml(t(phrase))}</div>`
            ).join('') +
            '</div>'
        );
        $menu.on('click', '.sc__side-option', function (e) {
            e.stopPropagation();
            $menu.find('.sc__side-option').removeClass('active');
            $(this).addClass('active');
            const $skinItem = $(currentButton).closest('.sc__skin-item');
            $('.sc__skin-item').removeClass('active');
            $skinItem.addClass('active');
            const side = $(this).attr('data-side');
            const weaponIndex = parseInt($skinItem.attr('data-weapon-id'));
            const skinId = parseInt($skinItem.attr('data-skin-id')) || 0;
            const itemId = parseInt($skinItem.attr('data-item-id')) || 0;
            const itemField = $skinItem.attr('data-field');
            if (itemId && itemField) {
                (async () => {
                    const res = await api('/player/items', {
                        method: 'POST',
                        body: {
                            field: itemField,
                            weapon_index: itemId,
                            side,
                            collection_id: activeCollectionId || 0,
                            edit_mode: editModeFlag()
                        }
                    });
                    if (res?.success) {
                        applySkinPatch(res.skins, res.placeholders, res.skins_count, itemId);
                        noty(t('_noty_item_set'), 'success');
                    }
                })();
            } else if (weaponIndex) {
                assignSkinToTeams(weaponIndex, skinId, side);
            }
            removeSidesMenu();
        });
        return $menu;
    }

    function positionSidesMenu($menu, button) {
        const r = button.getBoundingClientRect();
        const offset = parseFloat(getComputedStyle(document.documentElement).fontSize) * 1.4;
        const mw = $menu.outerWidth();
        const left = ($(window).width() - r.right >= mw + 20) ? r.right - offset + 2
            : (r.left >= mw + 20) ? r.left - mw + offset - 2
                : r.right - offset + 2;
        $menu.css({ top: (r.top + r.height / 2) + 'px', left: left + 'px' });
    }

    function removeSidesMenu() {
        currentSidesMenu?.remove();
        currentSidesMenu = currentButton = null;
    }

    function setCardState(btn, on) {
        const $btn = $(btn), $card = $btn.closest('.sc__skins-card');
        $btn.toggleClass('hover-active show-button', on);
        $card.toggleClass('hover-active', on);
        $card.find('.sc__skins-hover-layer').toggleClass('show-layer', on);
    }

    let currentActionsMenu = null, currentActionButton = null;
    let currentGotoMenu = null, currentGotoButton = null;
    let gotoMenuTimer = null;

    function createCollectionGotoMenu(collectionId) {
        const items = [
            { text: t('_edit_collection'), action: 'edit', icon: 'filters' },
            { text: t('_change'), action: 'rename', icon: 'edit-pen' },
            { text: t('_delete_collection'), action: 'delete', icon: 'trash' }
        ];
        const $menu = $(
            '<div class="sc__more-actions sc__goto-menu">' +
            items.map(({ text, action, icon }) =>
                `<div class="sc__more-option" data-action="${action}"><svg><use href="/resources/img/sprite.svg#${icon}"></use></svg>${escapeHtml(text)}</div>`
            ).join('') +
            '</div>'
        );
        $menu.on('click', '.sc__more-option', async function (e) {
            e.stopPropagation();
            const act = $(this).attr('data-action');
            removeGotoMenu();
            const col = collectionsData.find(c => c.id == collectionId);
            if (!col) return;
            if (act === 'edit') {
                const activate = async () => {
                    activeCollectionId = collectionId;
                    $('#myCollectionsList .sc__collection-button, #defaultCollectionsList .sc__collection-button').removeClass('active');
                    $(`#myCollectionsList .sc__collection-button[data-collection-id="${collectionId}"]`).addClass('active');
                    $('#currentCollectionName').text(col.name);
                    updateCollectionIdTippy(collectionId);
                    localStorage.setItem('sc_edit_mode', '1');
                    const skinRes = await api('/player/skins', { loader: $('#mySkinsWrapper') });
                    if (skinRes?.success) {
                        applySkinsRefresh(skinRes.data, null);
                        await enableEditMode($('#mySkinsWrapper'));
                    }
                };
                if (collectionId !== activeCollectionId) {
                    const actRes = await api('/player/collections/activate', {
                        method: 'POST', body: { collection_id: collectionId }
                    });
                    if (actRes?.success) await activate();
                } else {
                    localStorage.setItem('sc_edit_mode', '1');
                    enableEditMode($('#mySkinsWrapper'));
                }
            } else if (act === 'rename') {
                $('#editCollectionName [name="collection-name"]').val(col.name);
                $('#visabilityCollectionEdit').prop('checked', parseInt(col.is_public) === 1);
                editTargetCollectionId = col.id;
                $('#editCollectionName').addClass('visible');
                $('body,html').addClass('modal__opened');
            } else if (act === 'delete') {
                editTargetCollectionId = col.id;
                deleteActiveCollection();
            }
        });
        return $menu;
    }

    function setCollectionButtonHover($btn, on) {
        if (!$btn || !$btn.length) return;
        $btn.css('background-color', on ? 'var(--transparent-2-w)' : '');
        $btn.find('.sc__goto').css({ opacity: on ? 1 : '', visibility: on ? 'visible' : '' });
    }

    function removeGotoMenu() {
        if (!currentGotoMenu) return;
        if (currentGotoButton) {
            setCollectionButtonHover($(currentGotoButton).closest('.sc__collection-button'), false);
        }
        currentGotoMenu.remove();
        currentGotoMenu = null;
        currentGotoButton = null;
    }

    function createActionsMenu() {
        const actions = [
            { text: t('_choose_another_skin'), action: 'choose', icon: 'replace' },
            { text: t('_delete_skin'), action: 'delete', icon: 'trash' }
        ];
        const $menu = $(
            '<div class="sc__more-actions">' +
            actions.map(({ text, action, icon }) =>
                `<div class="sc__more-option" data-action="${action}"><svg><use href="/resources/img/sprite.svg#${icon}"></use></svg>${escapeHtml(text)}</div>`
            ).join('') +
            '</div>'
        );
        $menu.on('click', '.sc__more-option', function (e) {
            e.stopPropagation();
            const $card = $(currentActionButton).closest('.sc__skins-card');
            const skinInfo = $card.data('skinInfo');
            const action = $(this).attr('data-action');
            removeActionsMenu();
            if (!skinInfo) return;
            const isSpecial = SPECIAL_TYPES.includes(skinInfo.name_id);
            if (action === 'delete') {
                isSpecial ? removeItemForCard(skinInfo) : resetSkinForTeams(skinInfo.weapon_index, skinInfo.side);
            } else if (action === 'choose') {
                browsingSide = skinInfo.side || null;
                if (isSpecial) {
                    showItemsList(skinInfo);
                } else if (skinInfo.name_id === 'knife' || skinInfo.name_id === 'gloves') {
                    scrollToCategory(skinInfo.name_id === 'knife' ? 'knives' : 'gloves');
                } else {
                    loadWeaponSkins(skinInfo.weapon_index);
                }
            }
        });
        return $menu;
    }

    function positionActionsMenu($menu, button) {
        const r = button.getBoundingClientRect();
        const offset = parseFloat(getComputedStyle(document.documentElement).fontSize) * 0.5;
        const mw = $menu.outerWidth(), mh = $menu.outerHeight(), vh = $(window).height();
        const left = ($(window).width() - r.right >= mw + 20) ? r.right + offset
            : (r.left >= mw + 20) ? r.left - mw - offset
                : r.right + offset;
        $menu.css({ top: Math.min(r.top, vh - mh - 10) + 'px', left: left + 'px' });
    }

    function removeActionsMenu() {
        if (!currentActionsMenu) return;
        currentActionsMenu.remove();
        currentActionsMenu = null;
        if (currentActionButton) { setCardState(currentActionButton, false); currentActionButton = null; }
    }

    $(document)
        .on('mouseenter', '.sc__choose-side', function () {
            if (currentButton === this) return;
            const btn = this;
            clearTimeout(sidesMenuTimer);
            sidesMenuTimer = setTimeout(() => {
                removeSidesMenu();
                currentButton = btn;
                currentSidesMenu = createSidesMenu();
                $('body').append(currentSidesMenu);
                positionSidesMenu(currentSidesMenu, btn);
                $(btn).closest('.sc__skin-item').addClass('menu-active');
                setTimeout(() => currentSidesMenu?.addClass('visible'), 10);
            }, 300);
        })
        .on('mouseleave', '.sc__choose-side', function (e) {
            clearTimeout(sidesMenuTimer);
            if ($(e.relatedTarget).closest('.sc__sides-list').length) return;
            $(this).closest('.sc__skin-item').removeClass('menu-active');
            setTimeout(() => { if (!$('.sc__sides-list:hover').length) removeSidesMenu(); }, 50);
        })
        .on('mouseenter', '.sc__sides-list', function () {
            $(currentButton).closest('.sc__skin-item').addClass('menu-active');
        })
        .on('mouseleave', '.sc__sides-list', function () {
            $(currentButton).closest('.sc__skin-item').removeClass('menu-active');
            setTimeout(() => { if (!$('.sc__choose-side:hover').length) removeSidesMenu(); }, 50);
        });

    $(document)
        .on('mouseenter', '.sc__skins-open-action', function () {
            if (currentActionButton === this) return;
            removeActionsMenu();
            currentActionButton = this;
            currentActionsMenu = createActionsMenu();
            $('body').append(currentActionsMenu);
            positionActionsMenu(currentActionsMenu, this);
            setCardState(this, true);
            setTimeout(() => currentActionsMenu?.addClass('visible'), 10);
        })
        .on('mouseleave', '.sc__skins-open-action', function (e) {
            if ($(e.relatedTarget).closest('.sc__more-actions').length) return;
            setCardState(this, false);
            setTimeout(() => { if (!$('.sc__more-actions:hover').length) removeActionsMenu(); }, 50);
        })
        .on('mouseenter', '.sc__more-actions:not(.sc__goto-menu)', function () {
            setCardState(currentActionButton, true);
        })
        .on('mouseleave', '.sc__more-actions:not(.sc__goto-menu)', function () {
            setCardState(currentActionButton, false);
            setTimeout(() => { if (!$('.sc__skins-open-action:hover').length) removeActionsMenu(); }, 50);
        });

    $(document)
        .on('mouseenter', '#myCollectionsList .sc__collection-button[data-collection-id] .sc__goto', function () {
            clearTimeout(gotoMenuTimer);
            const $goto = $(this);
            if (currentGotoButton === $goto[0] && currentGotoMenu) return;
            removeGotoMenu();
            const collectionId = $goto.closest('.sc__collection-button').data('collection-id');
            if (!collectionId) return;
            setCollectionButtonHover($goto.closest('.sc__collection-button'), true);
            currentGotoButton = $goto[0];
            currentGotoMenu = createCollectionGotoMenu(collectionId);
            $('body').append(currentGotoMenu);
            positionActionsMenu(currentGotoMenu, currentGotoButton);
            setTimeout(() => currentGotoMenu?.addClass('visible'), 10);
        })
        .on('mouseleave', '#myCollectionsList .sc__collection-button[data-collection-id] .sc__goto', function (e) {
            if ($(e.relatedTarget).closest('.sc__goto-menu').length) return;
            gotoMenuTimer = setTimeout(() => removeGotoMenu(), 150);
        })
        .on('mouseenter', '.sc__goto-menu', function () {
            clearTimeout(gotoMenuTimer);
            if (currentGotoButton) setCollectionButtonHover($(currentGotoButton).closest('.sc__collection-button'), true);
        })
        .on('mouseleave', '.sc__goto-menu', function () {
            gotoMenuTimer = setTimeout(() => removeGotoMenu(), 150);
        });

    $(window).on('scroll resize', function () {
        if (currentSidesMenu && currentButton) positionSidesMenu(currentSidesMenu, currentButton);
        if (currentActionsMenu && currentActionButton) positionActionsMenu(currentActionsMenu, currentActionButton);
        if (currentGotoMenu && currentGotoButton) positionActionsMenu(currentGotoMenu, currentGotoButton);
    });

    const sidesPhraseMap = { modalTSide: '_t_side', modalBothSide: '_both_sides', modalCtSide: '_ct_side' };
    $('input[name="filter-sides"]').on('change', function () {
        $('.sc__modal-skin-side').text(t(sidesPhraseMap[$(this).attr('id')] || '_both_sides'));
    });

    $(document).on('click', '.sc__modal-sticker-block, .sc__modal-keychain-block', function () {
        const $block = $(this), wasActive = $block.hasClass('active');
        $('.sc__modal-sticker-block.active, .sc__modal-keychain-block.active').removeClass('active');
        if (!wasActive) $block.addClass('active');
        const $content = $block.find('.sc__modal-sticker, .sc__modal-keychain').first();
        if ($content.length && $content.find('img').length && !$content.find('svg use[href*="#plus"]').length) {
            $content.empty().append('<svg><use href="/resources/img/sprite.svg#plus"></use></svg>');
        }
    });

    $(document).on('click', '.sc__skins-hover-layer[data-openmodal="skinSettings"]', function () {
        const $card = $(this).closest('.sc__skins-card');
        const skinInfo = $card.data('skinInfo');
        if (skinInfo) populateSkinModal(skinInfo);
    });

    $(document).on('click', '.sc__modal-sticker-block', function (e) {
        if ($(e.target).closest('.sc__modal-sticker-remove').length) return;
        $('.sc__modal-sticker-block').removeClass('active');
        $(this).addClass('active');
        $('input[name="collectibles"][value="sticker"]').prop('checked', true).trigger('change');
    });

    $(document).on('click', '.sc__modal-keychain-block', function (e) {
        if ($(e.target).closest('.sc__modal-keychain-remove').length) return;
        $('.sc__modal-sticker-block').removeClass('active');
        $(this).addClass('active');
        $('input[name="collectibles"][value="keychain"]').prop('checked', true).trigger('change');
    });

    $(document).on('click', '.sc__modal-sticker-keychain[data-type="sticker"]', function () {
        const $modal = $('#skinSettings');
        const skinInfo = $modal.data('activeSkin');
        if (!skinInfo) return;

        const stickerId = parseInt($(this).data('id')) || 0;
        const stickerImg = $(this).find('img').attr('src') || $(this).find('img').data('src') || '';
        if (!stickerId) return;

        const $activeSlot = $modal.find('.sc__modal-sticker-block.active');
        if (!$activeSlot.length) return;

        $activeSlot.find('.sc__modal-sticker').empty().append($('<img>').attr('src', stickerImg));
        $activeSlot.data('stickerId', stickerId);
        $activeSlot.removeClass('active');
        const $next = $modal.find('.sc__modal-sticker-block').filter(function () { return !$(this).data('stickerId'); }).first();
        if ($next.length) $next.addClass('active');
    });

    $(document).on('click', '.sc__modal-sticker-keychain[data-type="keychain"]', function () {
        const $modal = $('#skinSettings');
        const skinInfo = $modal.data('activeSkin');
        if (!skinInfo) return;

        const keychainId = parseInt($(this).data('id')) || 0;
        const keychainImg = $(this).find('img').attr('src') || $(this).find('img').data('src') || '';
        if (!keychainId) return;

        const $keychainBlock = $modal.find('.sc__modal-keychain-block');
        $keychainBlock.find('.sc__modal-keychain').empty().append($('<img>').attr('src', keychainImg));
        $keychainBlock.data('keychainId', keychainId);
    });

    $(document).on('click', '.sc__modal-sticker-remove', function () {
        const $modal = $('#skinSettings');
        const skinInfo = $modal.data('activeSkin');
        if (!skinInfo) return;

        const $block = $(this).closest('.sc__modal-sticker-block');
        if (!$block.data('stickerId')) return;

        $block.find('.sc__modal-sticker').empty().append(makeSvgUse('/resources/img/sprite.svg#plus'));
        $block.removeData('stickerId');
    });

    $(document).on('click', '.sc__modal-keychain-remove', function () {
        const $modal = $('#skinSettings');
        const skinInfo = $modal.data('activeSkin');
        if (!skinInfo) return;

        const $block = $(this).closest('.sc__modal-keychain-block');
        if (!$block.data('keychainId')) return;

        $block.find('.sc__modal-keychain').empty().append(makeSvgUse('/resources/img/sprite.svg#plus'));
        $block.removeData('keychainId');
    });

    $(document).on('click', '.sc__modal-save-button', async function () {
        const $modal = $('#skinSettings');
        const skinInfo = $modal.data('activeSkin');
        if (!skinInfo) return;

        const $btn = $(this);
        $btn.prop('disabled', true);

        let side = 'both';
        if ($('#modalTSide').is(':checked')) side = 't';
        else if ($('#modalCtSide').is(':checked')) side = 'ct';
        else if ($('#modalBothSide').is(':checked')) side = 'both';

        const floatVal = parseFloat($('#floatValue').val()) || 0.0001;
        const patternVal = parseInt($('#patternValue').val()) || 0;
        const stattrack = $('#modalStatTrak').is(':checked') ? 1 : 0;
        const stattrackCount = parseInt($('#stattrakCounter').val()) || 0;
        const tag = $('#nameTag').val() || '';

        const stickers = {};
        $modal.find('.sc__modal-sticker-block').each(function (idx) {
            if (idx < 4) {
                const stickerId = $(this).data('stickerId');
                stickers[idx] = stickerId ? parseInt(stickerId) : 0;
            }
        });

        const keychainId = $modal.find('.sc__modal-keychain-block').data('keychainId') || null;

        let oppositeWeaponIndex = 0;
        const isKnifeOrGloveSkin = skinInfo.name_id === 'knife' || skinInfo.name_id === 'gloves';
        if (isKnifeOrGloveSkin && (skinInfo.side === 't' || skinInfo.side === 'ct') && side === 'both') {
            const oppSide = skinInfo.side === 't' ? 'ct' : 't';
            const oppSkin = playerSkinsData.find(s => s.name_id === skinInfo.name_id && s.side === oppSide);
            if (oppSkin && oppSkin.weapon_index !== skinInfo.weapon_index) {
                oppositeWeaponIndex = oppSkin.weapon_index;
            }
        }

        const res = await api('/player/skins', {
            method: 'PUT',
            body: {
                side,
                original_side: skinInfo.side || 'both',
                opposite_weapon_index: oppositeWeaponIndex,
                weapon_index: skinInfo.weapon_index,
                float: floatVal, pattern: patternVal,
                stattrack, stattrack_count: stattrackCount, tag,
                stickers: JSON.stringify(stickers),
                keychain_id: keychainId,
                collection_id: activeCollectionId || 0
            }
        });
        $btn.prop('disabled', false);
        if (res?.success) {
            refreshPlayerSkins();
            noty(t('_noty_skin_settings_saved'), 'success');
            $modal.find('.popup_modal_close').trigger('click');
        }
    });

    $(document).on('click', '.sc__skins-list .sc__skin-item', function (e) {
        if ($(e.target).closest('.sc__choose-side').length) return;
        $('.sc__skin-item').removeClass('active');
        $(this).addClass('active');
        const weaponIndex = parseInt($(this).attr('data-weapon-id'));
        const skinId = parseInt($(this).attr('data-skin-id')) || 0;
        const side = browsingSide || 'both';
        browsingSide = null;
        if (weaponIndex) assignSkinToTeams(weaponIndex, skinId, side);
    });

    let skinsRarityFilter = '';
    let currentWeaponData = null;

    function buildSkinsRarityFilter(skins) {
        const $filter = $('#skinsRarityFilter');
        const html = buildRarityFilterHtml(skins, 'skin-rarity-type', t('_all'), '');
        if (!html) { $filter.hide(); return; }
        $filter.html(html).show();
    }

    function destroyAllSkinTippies() {
        document.querySelectorAll('.sc__skin-item').forEach(el => {
            if (el._tippy) {
                el._tippy.destroy();
            }
        });
    }

    function renderWeaponSkinsList() {
        if (!currentWeaponData) return;
        destroyAllSkinTippies();
        const $skinsList = $('#weaponSkinsList').empty();
        const isKnife = currentWeaponData.type === 'Knife';
        if (!skinsRarityFilter && isKnife) {
            $skinsList.append(makeSkinItem('vanilla', currentWeaponData.id, null, currentWeaponData.img, currentWeaponData.name, false));
        }
        const skins = skinsRarityFilter
            ? (currentWeaponData.skins || []).filter(function (s) { return (s.id_rarity || '') === skinsRarityFilter; })
            : (currentWeaponData.skins || []);
        skins.forEach(function (s) {
            const isNew = s.new === true;
            $skinsList.append(makeSkinItem(s.id_rarity, currentWeaponData.id, s.id_skin, s.image, s.name, isNew));
        });
        renderTippy(document.querySelectorAll('.sc__skin-item'), 'bottom', { delay: [300, 0] });
    }

    async function loadWeaponSkins(weaponId) {
        skinsRarityFilter = '';
        itemsListFull = [];
        itemsListAll = [];
        $('#itemsListPagination').empty();
        $('#categoriesList').hide();
        $('#searchResultsList').hide().empty();
        $categories.find('.sc__category-title').show();
        $('#weaponSkinsList').show();
        const res = await api(`/weapons/${weaponId}/skins`, { loader: $('#weaponSkinsList') });
        if (res?.success && res.data) {
            currentWeaponData = res.data;
            const $catTitle = $categories.find('.sc__category-title');
            $catTitle.find('.sc__choosenWeapon-name').text(res.data.name || '');
            $('.sc__search input').val('');
            buildSkinsRarityFilter(res.data.skins || []);
            renderWeaponSkinsList();
        }
    }

    $(document).on('click', '.sc__category-item', function () {
        if ($(this).find('.show-more').length) return;
        const nameId = $(this).attr('data-name_id') || '';
        if (SPECIAL_TYPES.includes(nameId)) {
            showItemsList({ name_id: nameId, side: SPECIAL_SIDE_MAP[nameId] || 'both' });
            return;
        }
        const id = $(this).attr('data-id');
        if (!id) return;
        loadWeaponSkins(id);
    });

    $(document).on('click', '.sc__back', function () {
        resetToCategories();
    });

    $(document).on('click', '#editCollectionButton', function () {
        localStorage.setItem('sc_edit_mode', '1');
        enableEditMode($('#mySkinsWrapper'));
    });

    $(document).on('click', '#closeEditCollectionButton', function () {
        exitEditMode();
        syncPublishButton(activeCollectionId);
        hideBrowsingPanels();
        $('#myCollectionsList').show();
        $('#defaultCollectionsBlock').toggle($('#defaultCollectionsList').children().length > 0);
        $('.sc__no-skins').toggle(playerSkinsData.length === 0);
    });

    $(document).on('click', '.sc__no-skins button', function () {
        localStorage.setItem('sc_edit_mode', '1');
        enableEditMode($('#mySkinsWrapper'));
    });

    $(document).on('click', '.sc__skins-card.placeholder', function () {
        const nameId = $(this).attr('data-name-id') || '';
        if (SPECIAL_TYPES.includes(nameId)) {
            showItemsList({ name_id: nameId, side: SPECIAL_SIDE_MAP[nameId] || 'both' });
        } else if (nameId === 'knife' || nameId === 'gloves') {
            scrollToCategory(nameId === 'knife' ? 'knives' : 'gloves');
            browsingSide = null;
        } else {
            const weaponId = $(this).attr('data-id');
            if (!weaponId) return;
            loadWeaponSkins(parseInt(weaponId));
        }
    });

    $(document).on('click', '#resetCollectionButton', function () {
        if (!playerSkinsData.length) return;
        const $btn = $(this);
        openDialog({
            title: t('_reset_collection'),
            message: t('_reset_collection_message'),
            confirmText: t('_reset'),
            cancelText: t('_cancel'),
            onConfirm: async () => {
                $btn.prop('disabled', true);
                const res = await api('/player/skins/all', {
                    method: 'DELETE',
                    body: { collection_id: activeCollectionId || 0, edit_mode: editModeFlag() }
                });
                $btn.prop('disabled', false);
                if (res?.success) {
                    applySkinsRefresh(res.skins, res.placeholders, res.skins_count);
                    noty(t('_noty_all_cleared'), 'success');
                }
            }
        });
    });


    $(document).on('click', '.sc__skins-hover-layer.sc__item-choose', function () {
        const $card = $(this).closest('.sc__skins-card');
        const skinInfo = $card.data('skinInfo');
        if (skinInfo) showItemsList(skinInfo);
    });

    $(document).on('click', '.sc__special-item', function (e) {
        if ($(e.target).closest('.sc__choose-side').length) return;
        const $skinsList = $(this).closest('.sc__skins-list');
        const skinInfo = $skinsList.data('itemSkinInfo');
        const cfg = $skinsList.data('itemConfig');
        if (!skinInfo || !cfg) return;

        const itemId = parseInt($(this).attr('data-item-id')) || 0;
        if (!itemId) return;

        const side = cfg.team === 0 ? 't' : cfg.team === 1 ? 'ct' : 'both';
        const $el = $(this);
        $el.addClass('loading');

        (async () => {
            const res = await api('/player/items', {
                method: 'POST',
                body: {
                    field: cfg.field,
                    weapon_index: itemId,
                    side,
                    collection_id: activeCollectionId || 0,
                    edit_mode: editModeFlag()
                }
            });
            $el.removeClass('loading');
            if (res?.success) {
                applySkinPatch(res.skins, res.placeholders, res.skins_count, itemId);
                noty(t('_noty_item_set'), 'success');
            }
        })();
    });

    let searchTimer = null;

    $(document).on('input', '.sc__search input', function () {
        if (typeof tippy !== 'undefined') { if (tippy.hideAll) tippy.hideAll({ duration: 0 }); if (tippy.getAll) tippy.getAll().forEach(function (i) { i.destroy(); }); }
        const query = $(this).val().toLowerCase().trim();
        if ($('#categoriesList').is(':visible') || $('#searchResultsList').is(':visible')) {
            clearTimeout(searchTimer);
            if (!query) {
                $('#searchResultsList').hide().empty();
                $('#categoriesList').show();
                $('#categoriesList [data-category]').each(function () {
                    $(this).show().find('.sc__category-item').show();
                    $(this).find('.show-more').each(function () {
                        $(this).show();
                        $(this).closest('.sc__category-list').find('.sc__category-item').slice(6).hide();
                    });
                });
                return;
            }
            searchTimer = setTimeout(async () => {
                const res = await api('/skins/search', { method: 'POST', body: { query } });
                if (res?.success && Array.isArray(res.data)) {
                    $('#categoriesList').hide();
                    const $list = $('#searchResultsList').length
                        ? $('#searchResultsList').empty().show()
                        : $('<div id="searchResultsList" class="sc__skins-list">').insertAfter('#categoriesList').show();
                    if (!res.data.length) {
                        $list.append($('<div class="no-data">').text(t('_nothing_found')));
                        return;
                    }
                    res.data.forEach(s => $list.append(
                        makeSkinItem(s.id_rarity, s.weapon_id, s.id_skin, s.image, s.name)
                    ));
                    renderTippy(document.querySelectorAll('.sc__skin-item'), 'bottom', { delay: [300, 0] });
                }
            }, 300);
        } else if ($('#weaponSkinsList').is(':visible') && currentWeaponData) {
            clearTimeout(searchTimer);
            if (!query) {
                buildSkinsRarityFilter(currentWeaponData.skins || []);
                renderWeaponSkinsList();
                return;
            }
            searchTimer = setTimeout(async () => {
                const res = await api('/skins/search', { method: 'POST', body: { query } });
                if (res?.success && Array.isArray(res.data)) {
                    const weaponId = currentWeaponData.id;
                    const filtered = res.data.filter(s => parseInt(s.weapon_id) === parseInt(weaponId));
                    const $skinsList = $('#weaponSkinsList').empty();
                    filtered.forEach(function (s) {
                        $skinsList.append(makeSkinItem(s.id_rarity, s.weapon_id, s.id_skin, s.image, s.name));
                    });
                    if (!filtered.length) {
                        $skinsList.append($('<div class="no-data">').text(t('_nothing_found')));
                    }
                    renderTippy(document.querySelectorAll('.sc__skin-item'), 'bottom', { delay: [300, 0] });
                }
            }, 300);
        } else if ($('#weaponSkinsList').is(':visible') && itemsListCfg && itemsListFull.length) {
            clearTimeout(searchTimer);
            if (!query) {
                itemsListAll = itemsListFull;
                itemsListPage = 1;
                renderItemsListPage();
                return;
            }
            searchTimer = setTimeout(async () => {
                const res = await api('/skins/search', { method: 'POST', body: { query } });
                if (res?.success && Array.isArray(res.data)) {
                    const names = new Set(res.data.map(s => (s.name || '').toLowerCase()));
                    itemsListAll = itemsListFull.filter(function (item) {
                        return names.has((item.name || '').toLowerCase());
                    });
                    itemsListPage = 1;
                    renderItemsListPage();
                }
            }, 300);
        }
    });

    $(document).on('change', 'input[name="collectibles"]', function () {
        collectiblesType = $(this).val() || 'sticker';
        collectiblesPage = 1;
        collectiblesSearchQuery = '';
        collectiblesRarityFilter = '';
        $('#searchItem').val('');
        const cached = collectibles[collectiblesType].data;
        if (cached.length) {
            buildCollectiblesRarityFilter(cached);
        }
        loadCollectibles(collectiblesType, () => {
            filterCollectibles();
            renderCollectibles();
        });
    });

    $(document).on('change', 'input[name="skin-rarity-type"]', function () {
        skinsRarityFilter = $(this).val() || '';
        if (currentWeaponData) {
            renderWeaponSkinsList();
        } else if (itemsListFull.length) {
            itemsListAll = skinsRarityFilter
                ? itemsListFull.filter(function (item) { return (item.id_rarity || '') === skinsRarityFilter; })
                : itemsListFull;
            itemsListPage = 1;
            renderItemsListPage();
        }
    });

    $(document).on('change', 'input[name="stickers-type"]', function () {
        collectiblesRarityFilter = $(this).val() || '';
        filterCollectibles();
        renderCollectibles();
    });

    $(document).on('input', '#searchItem', function () {
        if (typeof tippy !== 'undefined') { if (tippy.hideAll) tippy.hideAll({ duration: 0 }); if (tippy.getAll) tippy.getAll().forEach(function (i) { i.destroy(); }); }
        clearTimeout(collectiblesSearchTimer);
        const val = $(this).val();
        collectiblesSearchTimer = setTimeout(() => {
            collectiblesSearchQuery = val;
            filterCollectibles();
            renderCollectibles();
        }, 300);
    });

    $(document).on('click', '[data-openmodal="skinSettings"]', function () {
        collectiblesType = $('input[name="collectibles"]:checked').val() || 'sticker';
        collectiblesPage = 1;
        collectiblesSearchQuery = '';
        $('#searchItem').val('');
        initCollectibles();
        document.querySelectorAll('.sc__modal-float-title, .sc__modal-pattern-title').forEach(el => {
            if (el._tippy) el._tippy.destroy();
        });
        renderTippy(document.querySelectorAll('.sc__modal-float-title, .sc__modal-pattern-title'), 'bottom', { interactive: true });
    });

    $('#cacheUpdateBtn').on('click', async function () {
        const selectedType = $('input[name="sc-cache"]:checked').val() || 'all';
        const $btn = $('#cacheUpdateBtn');

        if (selectedType !== 'all') {
            const res = await api('/admin/cache', {
                method: 'POST', body: { name: selectedType }, trigger: '#cacheUpdateBtn'
            });
            if (res?.success) loadCacheInfo();
            return;
        }

        const types = ['skins', 'stickers', 'keychains', 'agents', 'music', 'coins', 'collections'];
        const typeLabels = {
            skins: t('_cache_type_skins'), stickers: t('_cache_type_stickers'), keychains: t('_cache_type_keychains'),
            agents: t('_cache_type_agents'), music: t('_cache_type_music'), coins: t('_cache_type_coins'), collections: t('_cache_type_collections')
        };
        $btn.prop('disabled', true);
        const originalText = $btn.text();
        let failed = false;

        for (let i = 0; i < types.length; i++) {
            const type = types[i];
            $btn.text(sprintf(t('_cache_updating'), typeLabels[type], i + 1, types.length));
            const res = await api('/admin/cache', {
                method: 'POST', body: { name: type }
            });
            if (!res?.success) { failed = true; break; }
        }

        $btn.prop('disabled', false).text(originalText);
        if (!failed) loadCacheInfo();
    });

    if ($('#saveSettingsBtn').length) {
        (async () => {
            const res = await api('/admin/settings');
            if (!res?.success) return;
            if (res.collection_settings) {
                $('#collectionsLimitInput').val(res.collection_settings.collections_limit || 5);
                const pluginVal = res.collection_settings.plugin || 'pisex';
                $(`input[name="sc-plugin"][value="${pluginVal}"]`).prop('checked', true).trigger('change');
                renderVipGroups(res.collection_settings.vip_groups || []);
                $('#vipAccessGroups').val(res.collection_settings.vip_access_groups || '');
            }
            if (res.collections) {
                renderCollectionsList(res.collections);
                if (res.collection_settings && res.collection_settings.new_collections) {
                    const savedCollections = res.collection_settings.new_collections || [];
                    savedCollections.forEach(function (collectionId) {
                        $(`#selectNewCollections input[value="${collectionId}"]`).prop('checked', true);
                    });
                }
            }
            initAdaptiveSelects();
            if (res.cache_info) applyCacheInfo(res.cache_info);
            if (res.default_collections) renderAdminDefaultCollections(res.default_collections);
            if (!res.tables_installed) $('#installTablesBlock').show();
        })();

        function makeVipRow(group, slots) {
            const safeGroup = $('<span>').text(group).html();
            const $tr = $(
                `<tr>
                <td>${safeGroup}</td>
                <td>+${parseInt(slots)}</td>
                <td><div class="action-buttons"><button class="button-delete button-icon"><svg><use href="/resources/img/sprite.svg#trash"></use></svg></button></div></td>
                </tr>`
            );
            $tr.find('.button-delete').on('click', function () { $tr.remove(); });
            $tr.data('vip-entry', { group: group, slots: slots });
            return $tr;
        }

        function renderVipGroups(groups) {
            const $tbody = $('#vipGroupsList').empty();
            groups.forEach(function (g) {
                $tbody.append(makeVipRow(g.group, g.slots));
            });
        }

        function renderCollectionsList(collections) {
            const $list = $('#selectNewCollections').empty();

            const $searchWrapper = $(
                `<div class="inputs-inline">
                    <svg>
                        <use href="/resources/img/sprite.svg#magnifying-glass"></use>
                    </svg>
                    <input id="searchNewCollections" type="search" value="" placeholder="${escapeHtml(t('_searchCollection'))}" autocomplete="off">
                </div>`
            );
            $list.append($searchWrapper);

            collections.forEach(function (collection) {
                const safeName = $('<span>').text(collection.name).html();
                const $li = $(
                    `<li data-collection-name="${escapeHtml(collection.name.toLowerCase())}">
                        <label class="adaptive-select__label" for="collection_${collection.id}">
                            <div class="adaptive-select__label-text">${safeName}</div>
                            <input class="hide-input" id="collection_${collection.id}" type="checkbox" name="new_collections[]" value="${collection.id}">
                        </label>
                    </li>`
                );
                $list.append($li);
            });
        }

        function collectVipGroups() {
            return $('#vipGroupsList > tr').map(function () {
                return $(this).data('vip-entry');
            }).get();
        }

        $(document).on('input', '#searchNewCollections', function () {
            const query = $(this).val().toLowerCase().trim();
            $('#selectNewCollections li[data-collection-name]').each(function () {
                const name = $(this).data('collection-name');
                $(this).toggle(!query || name.includes(query));
            });
        });

        $('#addVipGroupBtn').on('click', function () {
            const name = $('#vipGroupNameInput').val().trim();
            const slots = parseInt($('#vipGroupSlotsInput').val()) || 0;
            if (!name || slots < 1) return;
            $('#vipGroupsList').append(makeVipRow(name, slots));
            $('#vipGroupNameInput').val('');
            $('#vipGroupSlotsInput').val('');
        });

        $('#saveSettingsBtn, #saveVipAccessBtn').on('click', async function () {
            const selectedCollections = $('#selectNewCollections input[type="checkbox"]:checked').map(function () {
                return $(this).val();
            }).get();

            const res = await api('/admin/settings', {
                method: 'POST',
                trigger: this,
                body: {
                    collections_limit: parseInt($('#collectionsLimitInput').val()) || 5,
                    plugin: $('input[name="sc-plugin"]:checked').val() || 'pisex',
                    vip_groups: JSON.stringify(collectVipGroups()),
                    vip_access_groups: $('#vipAccessGroups').val(),
                    new_collections: JSON.stringify(selectedCollections)
                }
            });
            if (res?.success) noty(t('_settings_saved'), 'success');
        });

        function renderAdminDefaultCollections(data) {
            const $tbody = $('#defaultCollectionsList').empty();
            const cols = (data && data.collections) || [];
            if (!cols.length) {
                $tbody.append(`<tr><td colspan="5">${escapeHtml(t('_no_presets'))}</td></tr>`);
                return;
            }
            cols.forEach(function (col) {
                const safeColName = $('<span>').text(col.name).html();
                const $tr = $(
                    `<tr data-preset-id="${col.id}">
                    <td><svg style="cursor:grab"><use href="/resources/img/sprite.svg#two-lines"></use></svg></td>
                    <td><strong>#${col.id}</strong></td>
                    <td>${safeColName}</td>
                    <td>${col.skins_count || 0}</td>
                    <td><div class="action-buttons"><button class="button-delete button-icon"><svg><use href="/resources/img/sprite.svg#trash"></use></svg></button></div></td>
                    </tr>`
                );
                $tr.find('.button-delete').on('click', async function () {
                    const r = await api(`/admin/presets/${col.id}`, { method: 'DELETE' });
                    if (r?.success) loadAdminDefaultCollections();
                });
                $tbody.append($tr);
            });
            if (window.Sortable && $tbody[0]) {
                Sortable.create($tbody[0], {
                    animation: 150,
                    handle: 'svg',
                    onEnd: async function () {
                        const ids = $tbody.children('[data-preset-id]').map(function () {
                            return $(this).data('preset-id');
                        }).get();
                        const r = await api('/admin/presets/reorder', {
                            method: 'PUT', body: { ids: ids.join(',') }
                        });
                        if (r?.success) noty(t('_order_saved'), 'success');
                    }
                });
            }
        }

        async function loadAdminDefaultCollections() {
            const res = await api('/presets');
            if (res?.success) renderAdminDefaultCollections(res.data);
        }


        $('#addDefaultCollectionBtn').on('click', async function () {
            const id = parseInt($('#defaultCollectionIdInput').val());
            if (!id) return;
            const name = $('#defaultCollectionNameInput').val().trim();
            const res = await api('/admin/presets', {
                method: 'POST', body: { collection_id: id, name }, trigger: '#addDefaultCollectionBtn'
            });
            if (res?.success) {
                $('#defaultCollectionIdInput').val('');
                $('#defaultCollectionNameInput').val('');
                loadAdminDefaultCollections();
                noty(t('_default_collection_added'), 'success');
            } else {
                noty(t('_not_found'), 'error');
            }
        });

        $('#installTablesBtn').on('click', async function () {
            const res = await api('/admin/tables', {
                method: 'POST', trigger: '#installTablesBtn'
            });
            if (res?.success) {
                noty(t('_tables_installed'), 'success');
                $('#installTablesBlock').hide();
            } else {
                noty(t('_error'), 'error');
            }
        });

    }

    $('#myCollectionsList').on('click', '.sc__collection-button', function () {
        if (!$(this).hasClass('sc__add-button')) {
            $('.sc__collection-button').removeClass('active');
            $(this).addClass('active');
        }
    });

    $(document).on('click', '.sc__back-colletions', function () {
        hideBrowsingPanels();
        exitEditMode();
        $('#myCollectionsList').show();
        $('#defaultCollectionsBlock').toggle($('#defaultCollectionsList').children().length > 0);
        loadMyCollections();
        refreshPlayerSkins();
    });

    let initDataLoaded = false;

    async function loadInitData(callback) {
        if (initDataLoaded) { callback?.(); return; }
        const res = await api('/player/init', { loader: $('#mySkinsWrapper') });
        if (!res?.success || !res.data) return;
        initDataLoaded = true;

        if (res.data.collections) {
            renderMyCollections(res.data.collections);
        }
        if (res.data.categories && $('#categoriesList').length) {
            renderCategories(res.data.categories);
        }
        if (res.data.skins) {
            const skins = Array.isArray(res.data.skins) ? res.data.skins : [];
            playerSkinsData = skins;
            const $wrapper = $('#mySkinsWrapper');
            skins.forEach(skin => $wrapper.append(makeSkinCard(skin)));
            $('.sc__skins-fake-wrapper').hide();
            initSkinsTippy();
            if (skins.length === 0 || localStorage.getItem('sc_edit_mode') === '1') {
                localStorage.setItem('sc_edit_mode', '1');
                await enableEditMode($wrapper);
            }
        }
        if (res.data.default_collections) {
            renderDefaultCollections(res.data.default_collections);
        }

        callback?.();
    }

    function switchTab(isMyTab, pushUrl, skipLoad) {
        $('.sc__aside .sc__header .filter').removeClass('active');
        $('.sc__aside .sc__header .filter').eq(isMyTab ? 0 : 1).addClass('active');

        if (isMyTab) {
            localStorage.removeItem('sc_edit_mode');
            $('#mySkinsWrapper').removeClass('edit-mode');
            clearEditSelectionState();
            $('#resetCollectionButton, #closeEditCollectionButton').hide();
            $('.sc__skins-card.placeholder').remove();
            $('#communityCollectionDetail').hide();
            $('.sc__right-area .sc__loadout').first().show();

            loadInitData(() => {
                setMyCollectionsVisible(true);
                applyCollectionButtonsForActive($('.sc__collection-button.active'), activeCollectionId);
            });
        } else {
            $('#editCollectionButton').hide();
            $('#publishCollectionButton').hide();
            $('#communityDetailInstall').hide();
            setMyCollectionsVisible(false);
            if (!skipLoad) {
                communityPage = 1;
                loadCommunityCollections();
            }
        }
        if (pushUrl) {
            const url = isMyTab ? '/skinchanger/' : '/skinchanger/community/';
            history.pushState({ tab: isMyTab ? 'my' : 'community' }, '', url);
        }
    }

    async function openCommunityCollectionById(collectionId) {
        switchTab(false, false, true);
        const res = await api(`/collections/${collectionId}`, {
            loader: $('#communityCollectionDetailSkins')
        });
        if (!res?.success || !res.data) return;
        viewingCommunityCollectionId = collectionId;
        isDefaultCollectionDetail = false;
        $('.sc__collections-filters').hide();
        $('.sc__collections-info').show();
        populateCollectionInfo(collectionId, res.data);
        $('#communityLoadout').hide();
        $('#communityCollectionDetail').show();
        $('#communityCollectionDetailName').text(res.data.name || '');
        $('#communityDetailInstall').show().data('install-collection', collectionId);
        $('#communityShareButton').show().attr('data-clipboard-text', location.origin + '/skinchanger/community/' + collectionId + '/');
        const $wrapper = $('#communityCollectionDetailSkins').empty();
        renderCollectionDetailSkins($wrapper, res.data.skins_preview || []);
    }

    $(document).on('click', '.sc__aside .sc__header .filter', function () {
        const isMyTab = $(this).index() === 0;
        switchTab(isMyTab, true);
    });

    $(window).on('popstate', function (e) {
        const state = e.originalEvent.state;
        if (!state) return;
        if (state.collection) {
            openCommunityCollectionById(state.collection);
        } else if (state.tab) {
            switchTab(state.tab === 'my', false);
        }
    });

    $(document).on('click', '#myCollectionsList .sc__collection-button:not(.sc__add-button)', async function (e) {
        if ($(e.target).closest('.sc__goto').length) return;
        const collectionId = $(this).data('collection-id');
        if (!collectionId) return;
        if ($(this).closest('#defaultCollectionsList').length) return;

        if (isDefaultCollectionDetail || $('#communityCollectionDetail').is(':visible')) {
            isDefaultCollectionDetail = false;
            $('#communityCollectionDetail').hide();
            $('.sc__right-area .sc__loadout').first().show();
        }

        exitEditMode();
        hideBrowsingPanels();

        if (collectionId === activeCollectionId) {
            applyCollectionButtonsForActive($(this), collectionId);
            return;
        }
        const res = await api('/player/collections/activate', {
            method: 'POST', body: { collection_id: collectionId }
        });
        if (!res?.success) return;
        activeCollectionId = collectionId;
        $('#myCollectionsList .sc__collection-button, #defaultCollectionsList .sc__collection-button').removeClass('active');

        const $activeBtn = $(`#myCollectionsList .sc__collection-button[data-collection-id="${collectionId}"]`).addClass('active');
        const col = collectionsData.find(c => c.id == collectionId);
        if (col) $('#currentCollectionName').text(col.name);
        updateCollectionIdTippy(collectionId);

        if ($activeBtn.hasClass('ready')) {
            $('#editCollectionButton').hide();
            $('#publishCollectionButton').hide();
            $('#copyReadyCollectionButton').css('display', 'flex');
        } else {
            $('#copyReadyCollectionButton').hide();
            syncPublishButton(collectionId);
        }

        refreshPlayerSkins();
        noty(t('_collection_activated'), 'success');
    });

    $(document).on('click', '#publishCollectionButton', async function () {
        if (!activeCollectionId) return;
        const col = collectionsData.find(c => c.id == activeCollectionId);
        if (!col) return;
        const newIsPublic = parseInt(col.is_public) === 1 ? 0 : 1;
        const res = await api(`/player/collections/${activeCollectionId}/public`, {
            method: 'PUT', body: { is_public: newIsPublic }, trigger: $(this)
        });
        if (!res?.success) return;
        col.is_public = newIsPublic;
        $('#visabilityCollectionEdit').prop('checked', newIsPublic === 1);
        syncPublishButton(activeCollectionId);
        noty(t(newIsPublic === 1 ? '_collection_published' : '_collection_unpublished'), newIsPublic === 1 ? 'success' : 'info');
    });

    $(document).on('click', '[data-openmodal="editCollectionName"]', function () {
        const col = collectionsData.find(c => c.id == activeCollectionId);
        if (col) {
            $('#editCollectionName [name="collection-name"]').val(col.name);
            $('#visabilityCollectionEdit').prop('checked', parseInt(col.is_public) === 1);
            editTargetCollectionId = col.id;
        }
    });

    $(document).on('submit', '#newCollection form', async function (e) {
        e.preventDefault();
        const $form = $(this);
        const name = $form.find('[name="collection-name"]').val().trim();
        if (!name) return;
        const isPublic = $form.find('#visabilityCollection').is(':checked') ? 1 : 0;
        const res = await api('/player/collections', {
            method: 'POST', body: { name, is_public: isPublic },
            trigger: $form.find('[type="submit"]')
        });
        if (!res?.success) return;
        $form.find('[name="collection-name"]').val('');
        $('#visabilityCollection').prop('checked', false);
        $('.popup_modal#newCollection .popup_modal_close').trigger('click');
        loadMyCollections();
    });

    $(document).on('submit', '#editCollectionName form', async function (e) {
        e.preventDefault();
        const $submitBtn = $(this).find('[type="submit"]:not(.button-delete)');
        const $deleteBtn = $(this).find('[type="submit"].button-delete');
        const $clicked = $(document.activeElement);

        if ($clicked.hasClass('button-delete') || $deleteBtn.is(':focus')) {
            deleteActiveCollection();
        } else {
            const name = $(this).find('[name="collection-name"]').val().trim();
            if (!name || !editTargetCollectionId) return;
            const isPublic = $('#visabilityCollectionEdit').is(':checked') ? 1 : 0;
            const renameRes = await api(`/player/collections/${editTargetCollectionId}`, {
                method: 'PUT', body: { name }, trigger: $submitBtn
            });
            if (!renameRes?.success) return;
            await api(`/player/collections/${editTargetCollectionId}/public`, {
                method: 'PUT', body: { is_public: isPublic }
            });
            $('.popup_modal#editCollectionName .popup_modal_close').trigger('click');
            loadMyCollections();
            noty(t('_noty_collection_saved'), 'success');
        }
    });

    $(document).on('click', '#editCollectionName .button-delete', function (e) {
        e.preventDefault();
        deleteActiveCollection();
    });

    $(document).on('click', '#defaultCollectionsList [data-install-collection]', function (e) {
        e.stopImmediatePropagation();
        const collectionId = $(this).data('install-collection');
        if (!collectionId) return;
        $('.sc__collection-button').removeClass('active');
        $(this).addClass('active');
        showDefaultCollectionDetail(collectionId);
    });

    $(document).on('click', '#copyReadyCollectionButton', function () {
        if (!activeCollectionId) return;
        copyTargetCollectionId = activeCollectionId;
        $('#copyCollectionName').val('');
        $('#copyCollection').addClass('visible');
        $('body,html').addClass('modal__opened');
    });

    $(document).on('click', '[data-install-collection]', function () {
        if ($(this).hasClass('show-collection')) return;
        if ($(this).closest('#defaultCollectionsList').length) return;
        copyTargetCollectionId = $(this).data('install-collection');
        $('#copyCollectionName').val('');
        $('#copyCollection').addClass('visible');
        $('body,html').addClass('modal__opened');
    });

    $(document).on('click', '#copyCollection .sc__modal-buttons .active', async function () {
        if (!copyTargetCollectionId) return;
        const res = await api('/player/collections/activate', {
            method: 'POST',
            body: { collection_id: copyTargetCollectionId, install: 1, name: $('#copyCollectionName').val().trim() },
            trigger: $(this)
        });
        if (!res?.success) {
            if (res?.error === 'limit_reached') noty(t('_no_free_slots'), 'error');
            return;
        }
        $('.popup_modal#copyCollection .popup_modal_close').trigger('click');
        noty(t('_collection_copied'), 'success');
        loadMyCollections(true);
        refreshPlayerSkins();
        switchTab(true, true, false);
        copyTargetCollectionId = null;
    });

    $(document).on('click', '#copyCollection .sc__modal-buttons button:not(.active)', function () {
        $('.popup_modal#copyCollection .popup_modal_close').trigger('click');
        copyTargetCollectionId = null;
    });

    $(document).on('click', '.sc__collection-likes', async function () {
        const $btn = $(this);
        const collectionId = $btn.data('collection-id');
        if (!collectionId) return;
        const res = await api(`/player/collections/${collectionId}/like`, { method: 'POST' });
        if (!res?.success) return;
        const liked = !!res.liked;
        const count = res.count;
        $btn.toggleClass('voted', liked);
        $btn.find('.likes-count').text(formatCount(count));
        noty(t(liked ? '_collection_liked' : '_collection_unliked'), liked ? 'success' : 'info');
        const cached = communityCollectionsCache.find(c => c.id == collectionId);
        if (cached) { cached.likes_count = count; cached.player_liked = liked; }
        if (viewingCommunityCollectionId == collectionId) {
            $('.sc__collections-info .sc__collections-about .value').eq(3).text(formatCount(count));
        }
    });



    $(document).on('change', 'input[name="collections-sort"]', function () {
        communityPage = 1;
        const text = $(this).closest('label').find('.adaptive-select__label-text').text();
        if (text) $('[open-select="collectionFilters"] .adaptive-select__span_text').text(text);
        loadCommunityCollections();
    });

    $(document).on('change', '#oblyWithDowloads, #oblyLikedMe, #oblyMyCollections', function () {
        communityPage = 1;
        loadCommunityCollections();
    });

    $(document).on('input', '#searchCollections', function () {
        clearTimeout(searchCollectionsTimer);
        searchCollectionsTimer = setTimeout(function () {
            communityPage = 1;
            loadCommunityCollections();
        }, 400);
    });

    $(document).on('click', '.show-collection', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        const collectionId = $(this).data('install-collection') || $(this).closest('.sc__collection-card').data('collection-id');
        if (collectionId) {
            showCommunityCollectionDetail(collectionId);
            history.pushState({ tab: 'community', collection: collectionId }, '', '/skinchanger/community/' + collectionId + '/');
        }
    });

    $(document).on('click', '#backToCommunityList', function () {
        viewingCommunityCollectionId = null;
        $('#communityCollectionDetail').hide();
        history.pushState({ tab: 'community' }, '', '/skinchanger/community/');
        if (isDefaultCollectionDetail) {
            isDefaultCollectionDetail = false;
            $('.sc__collection-button.ready').removeClass('active');
            if (activeCollectionId) {
                $(`#myCollectionsList .sc__collection-button[data-collection-id="${activeCollectionId}"]`).addClass('active');
            }
            $('.sc__right-area .sc__loadout').first().show();
            exitEditMode();
            $('#myCollectionsList').show();
            $('#defaultCollectionsBlock').toggle($('#defaultCollectionsList').children().length > 0);
            refreshPlayerSkins();
        } else {
            $('.sc__collections-filters').show();
            $('.sc__collections-info').hide();
            $('#communityLoadout').show();
            $('#communityPagination').show();
            if (!communityCollectionsCache.length) {
                communityPage = 1;
                loadCommunityCollections();
            }
        }
    });

    $(document).on('click', '#communityDetailInstall', async function () {
        const collectionId = $(this).data('install-collection');
        if (isDefaultCollectionDetail) {
            const $btn = $(this);
            $btn.addClass('loading');
            const res = await api(`/player/presets/${collectionId}/activate`, { method: 'POST' });
            $btn.removeClass('loading');
            if (!res?.success) {
                if (res?.error === 'limit_reached') noty(t('_no_free_slots'), 'error');
                return;
            }
            $('#backToCommunityList').trigger('click');
            noty(t('_collection_copied'), 'success');
            loadMyCollections(true);
            refreshPlayerSkins();
            setMyCollectionsVisible(true);
        } else {
            copyTargetCollectionId = collectionId;
            $('#copyCollectionName').val('');
            $('#copyCollection').addClass('visible');
            $('body,html').addClass('modal__opened');
        }
    });

    loadCacheInfo();

    if (localStorage.getItem('hideInfoCard') !== 'true') {
        $('.sc__info-card').show();
    } else {
        $('.sc__info-card').remove();
    }

    $(document).on('click', '#hideThisInfo', function() {
        localStorage.setItem('hideInfoCard', 'true');
        $('.sc__info-card').remove();
    });

    if ($('#mySkinsWrapper').length || $('#categoriesList').length) {
        const communityMatch = location.pathname.match(/^\/skinchanger\/community\/(?:(\d+)\/)?$/);
        const initialState = communityMatch
            ? (communityMatch[1] ? { tab: 'community', collection: parseInt(communityMatch[1]) } : { tab: 'community' })
            : { tab: 'my' };
        history.replaceState(initialState, '', location.pathname);

        if (communityMatch) {
            if (communityMatch[1]) {
                openCommunityCollectionById(parseInt(communityMatch[1]));
            } else {
                switchTab(false, false);
            }
        } else {
            loadInitData();
        }
    }
});
