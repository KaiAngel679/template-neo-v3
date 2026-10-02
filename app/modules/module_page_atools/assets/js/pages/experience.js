let currentExperiencePage = 1;
let experienceData = null;
let myData = null;

function getExperienceSort() {
    return $('#experienceFilters input[name="filter-experience-sorting"]:checked').val() || 'down';
}

function getFilterExperienceServers() {
    const servers = [];
    $('#experienceServerListFilterInner input[name="filter-experience-server"]:checked').each(function () {
        servers.push(parseInt($(this).val(), 10));
    });
    if (!servers.length) {
        return [-1];
    }
    return servers;
}

function getExperienceServers() {
    return getCheckedServerFilterIds('#experienceServerList input[name="experience-server"]:checked');
}

function getExperienceActionServers() {
    const servers = [];
    $('#experienceActionServerList input[name="experience-action-server"]:checked').each(function () {
        servers.push(parseInt($(this).val(), 10));
    });
    if (!servers.length) {
        return [-1];
    }
    return servers;
}

function updateExperienceFilterTexts() {
    updateAdaptiveSelectTextsIn($('#experienceServerListFilter'));
    updateAdaptiveSelectTextsIn($('#experienceFilters .adaptive-select-wrapper[data-change-icon]'));
}

function loadExperienceList(page) {
    page = page || 1;
    currentExperiencePage = page;

    return sendRequest({
        get_experience_list: true,
        servers: getFilterExperienceServers(),
        sort: getExperienceSort(),
        limit: getListLimit(),
        offset: (page - 1) * getListLimit(),
        search: getSearchQuery()
    }).done(function (result) {
        if (result.status !== 'success') {
            if (result.message) {
                noty(result.message, result.status);
            }
            return;
        }

        experienceData = result.data || [];
        myData = result.my_data || null;
        window.atExperienceMyData = myData;

        $('#experienceTable').html(renderExperienceList(experienceData));
        updateExperienceTableBulkUi();

        const totalPages = Math.max(1, Math.ceil((result.total || 0) / getListLimit()));
        $('#experiencePagination').html(renderPagination(page, totalPages));

        finishTableRender(experienceData, [{ flagField: 'checked_avatar', steamField: 'steamid' }]);
        initTippy();
    });
}

function findExperienceById(playerId) {
    if (!playerId || !Array.isArray(experienceData)) {
        return null;
    }

    return experienceData.find(function (row) {
        return String(row.id) === String(playerId);
    }) || null;
}

function openEditExperienceModal(playerId) {
    const player = findExperienceById(playerId);
    if (!player) {
        return;
    }

    const $modal = $('#changeExperience');
    $modal.html(renderEditExperienceModal(player));
    $modal.addClass('visible');
    initTippy();
}

function collectSelectedExperienceIds() {
    const ids = [];
    $('#experienceTable .row-checkbox:checked').each(function () {
        ids.push(String($(this).val()));
    });
    return ids.filter(Boolean);
}

function confirmResetExperiences(playerList) {
    if (!playerList || !playerList.length) {
        return;
    }

    const message = playerList.length > 1
        ? get_translate_module_phrase('module_page_atools', '_at_confirmResetExperienceBulk')
        : get_translate_module_phrase('module_page_atools', '_at_confirmResetExperience');

    confirmAndRequest({
        message: message,
        confirmText: get_translate_module_phrase('module_page_atools', '_at_resetExperienceBtn'),
        body: { reset_experiences: true, player_list: playerList },
        onSuccess: function () {
            loadExperienceList(currentExperiencePage);
        }
    });
}

function buildExperienceBulkActionsHtml() {
    const caps = getExperienceListCapabilities(myData);
    if (!caps.canReset) {
        return '';
    }

    return `<button class="filter" data-bulk-action="reset">${get_translate_module_phrase('module_page_atools', '_at_resetExperienceBtn')}</button>`;
}

function toggleExperienceBulkActions() {
    const caps = getExperienceListCapabilities(myData);
    toggleBulkActionsPanel({
        enabled: caps.canBulkSelect,
        tableSelector: '#experienceTable',
        buildHtml: buildExperienceBulkActionsHtml
    });
}

function resetExperienceFilters() {
    resetFilterPanel('experienceFilters', function () {
        $('#filterExperienceSortDown').prop('checked', true);
        $('#experienceServerAllFilter').prop('checked', true);
        updateExperienceFilterTexts();
    });
    clearSearch();
    loadExperienceList(1);
}

registerActionMenu('experience', {
    items: [
        {
            action: 'edit',
            label: get_translate_phrase('_Change'),
            icon: 'edit-pen',
            visible: function () {
                return getExperienceListCapabilities(myData).canUpdate;
            }
        },
        {
            action: 'reset',
            label: get_translate_module_phrase('module_page_atools', '_at_resetExperienceBtn'),
            icon: 'broom',
            className: 'button-delete',
            visible: function () {
                return getExperienceListCapabilities(myData).canReset;
            }
        }
    ],
    rowSelector: 'tr',
    onAction: function (action, $trigger) {
        const playerId = $trigger.data('experience-id');
        if (!playerId) {
            return;
        }

        const caps = getExperienceListCapabilities(myData);

        switch (action) {
            case 'edit':
                if (!caps.canUpdate) {
                    return;
                }
                openEditExperienceModal(String(playerId));
                break;
            case 'reset':
                if (!caps.canReset) {
                    return;
                }
                confirmResetExperiences([String(playerId)]);
                break;
        }
    }
});

$(document).ready(function () {
    if (!$('#experienceTable').length) {
        return;
    }

    if (typeof initAdaptiveSelects === 'function') {
        initAdaptiveSelects('#experienceFilters, #addingExperience, #experienceActions');
    }
    updateExperienceFilterTexts();

    loadExperienceList(1);

    $(document).on('change', '#experienceFilters input[name="filter-experience-sorting"]', function () {
        updateAdaptiveSelectText($(this).closest('.adaptive-select-wrapper'));
        loadExperienceList(1);
    });

    $(document).on('change', '#experienceFilters input[name="filter-experience-server"]', function () {
        loadExperienceList(1);
    });

    bindAtListRefresh(function () {
        loadExperienceList(1);
    }, { selector: '#experienceTable', resetPage: true });

    $(document).on('click', '#resetExperienceFilters', function () {
        resetExperienceFilters();
    });

    bindAtPagination(loadExperienceList);

    $(document).on('click', '#addExperience', function () {
        var $btn = $(this);
        sendRequestWithButton($btn, {
            add_experience: true,
            steamid: $('#experienceSteamIdInput').val(),
            amount: $('#countExperienceAdding').val(),
            servers: getExperienceServers()
        }).done(function (result) {
            if (result.message) {
                noty(result.message, result.status);
            }
            if (result.status === 'success') {
                $('#experienceSteamIdInput, #countExperienceAdding').val('');
                resetDefaultAllServersCheckbox('#experienceServerAll');
                loadExperienceList(1);
            }
        });
    });

    $(document).on('click', '#confirmEditExperience', function () {
        if (!getExperienceListCapabilities(myData).canUpdate) {
            return;
        }

        sendRequest({
            update_experience: true,
            stats_key: $(this).data('stats-key'),
            steam: $(this).data('steam'),
            value: $('#countExperienceEdit').val(),
            old_value: $(this).data('old-value')
        }).done(function (result) {
            if (result.message) {
                noty(result.message, result.status);
            }
            if (result.status === 'success') {
                $('#changeExperience').removeClass('visible');
                loadExperienceList(currentExperiencePage);
            }
        });
    });

    $(document).on('click', '.at__checked-action [data-bulk-action="reset"]', function () {
        if (!getExperienceListCapabilities(myData).canReset) {
            return;
        }
        confirmResetExperiences(collectSelectedExperienceIds());
    });

    $(document).on('click', '#clearAllExperienceStats', function () {
        confirmAndRequest({
            message: get_translate_module_phrase('module_page_atools', '_at_confirmWipeExperienceStats'),
            confirmText: get_translate_module_phrase('module_page_atools', '_at_clearAllStats'),
            body: {
                wipe_experience_stats: true,
                servers: getExperienceActionServers()
            },
            onSuccess: function () {
                loadExperienceList(1);
            }
        });
    });

    $(document).on('click', '#clearEmptyExperiencePlayers', function () {
        confirmAndRequest({
            message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteEmptyExperiencePlayers'),
            confirmText: get_translate_module_phrase('module_page_atools', '_at_clearEmptyPlayers'),
            body: {
                delete_empty_experience_players: true,
                servers: getExperienceActionServers()
            },
            onSuccess: function () {
                loadExperienceList(1);
            }
        });
    });

    initBulkSelection({
        tableSelector: '#experienceTable',
        ignoreClickSelector: 'button, a, input',
        isEnabled: function () {
            return getExperienceListCapabilities(myData).canBulkSelect;
        },
        onToggle: toggleExperienceBulkActions
    });
});
