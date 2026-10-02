const fourServers = document.getElementById('fourServers');
const threeServers = document.getElementById('threeServers');
const twoServers = document.getElementById('twoServers');
const oneLineServers = document.getElementById('oneLineServers');
const twoLineServers = document.getElementById('twoLineServers');

const example1 = document.getElementById('example-1');
const example2 = document.getElementById('example-2');
const example3 = document.getElementById('example-3');
const example4 = document.getElementById('example-4');
const tableExample = document.getElementById('table-example-1');
const tableExample2 = document.getElementById('table-example-2');

function resetFlex() {
    [example1, example2, example3, example4, tableExample, tableExample2].forEach(el => {
        if (el) el.style.flex = '';
    });
}

function applyVisualSettings() {
    if (fourServers?.checked) {
        resetFlex();
    } else if (threeServers?.checked) {
        resetFlex();
        if (example4) example4.style.flex = '0';
    } else if (twoServers?.checked) {
        resetFlex();
        if (example3) example3.style.flex = '0';
        if (example4) example4.style.flex = '0';
    }
}

function applyVisualTableSettings() {
    if (oneLineServers?.checked) {
        if (tableExample2) tableExample2.style.flex = '0';
    } else if (twoLineServers?.checked) {
        resetFlex();
    }
}

[
    [fourServers, applyVisualSettings],
    [threeServers, applyVisualSettings],
    [twoServers, applyVisualSettings],
    [oneLineServers, applyVisualTableSettings],
    [twoLineServers, applyVisualTableSettings]
].forEach(([el, handler]) => {
    if (el) el.addEventListener('change', handler);
});

applyVisualSettings();
applyVisualTableSettings();

function getLogsList(page = 1, limit = 10) {
    const server = $('input[name="mon_server"]:checked').val();
    if (!server) return;
    $.ajax({
        type: 'POST',
        url: location.href,
        data: { getLogsList: true, page: page, limit: limit, server: server },
        dataType: 'json',
        global: false,
        success: function (data) {
            if (data.html) {
                $('.mon__logs-list').show();
                $('.mon__empty-list-text').hide();
                $('#logsList').html(data.html);
            } else {
                $('.mon__logs-list').hide();
                $('.mon__empty-list-text').show();
            }
            const pagination = $('#logsPagination');
            pagination.html(data.pagination);
            pagination.off('click').on('click', 'a[data-page]', function () {
                const newPage = parseInt($(this).data('page'));
                if (!isNaN(newPage)) {
                    getLogsList(newPage, limit);
                }
            });
        },
        error: function () { return false; }
    });
}
getLogsList();

$.ajax({
    type: 'POST',
    url: location.href,
    data: { getAccessList: true },
    dataType: 'json',
    global: false,
    success: function (data) {
        if (data.html) {
            $('.mon__access-wrapper').show();
            $('.mon__empty-access-text').hide();
            $('#accessList').html(data.html);
        }
    },
    error: function () { return false; }
});

$(document).on('click', '#access_del', function () {
    let button = $(this);
    const id_del = button.attr('id_del');
    $.ajax({
        type: 'POST',
        url: location.href,
        data: { mon_access_del: true, steamid: id_del },
        dataType: 'json',
        global: false,
        success: function (data) {
            if (data.status == "success") {
                noty(data.text, data.status)
                button.closest('.mon__access-block').remove();
            } else {
                noty(data.text, data.status)
            }
        },
    });
});

$(document).on('click', '#mod_del', function () {
    let button = $(this);
    const id_del = button.attr('id_del');
    $.ajax({
        type: 'POST',
        url: location.href,
        data: { mon_mod_del: true, mod: id_del },
        dataType: 'json',
        global: false,
        success: function (data) {
            if (data.status == "success") {
                noty(data.text, data.status)
                button.closest('.handle').remove();
            } else {
                noty(data.text, data.status)
            }
        },
    });
});

const modsTree = document.getElementById('mods-tree');
if (modsTree && typeof Sortable !== 'undefined') {

    function collectTree() {
        const result = [];
        modsTree.querySelectorAll(':scope > .mods__mode').forEach(modeEl => {
            const mode = { id: modeEl.dataset.id, type: 'mode', children: [] };
            Array.from(modeEl.children).forEach(child => {
                if (child.classList.contains('mods__submod')) {
                    const submod = { id: child.dataset.id, type: 'submod', children: [] };
                    Array.from(child.children).forEach(srv => {
                        if (srv.classList.contains('mods__server')) {
                            submod.children.push({ id: srv.dataset.id, type: 'server' });
                        }
                    });
                    mode.children.push(submod);
                } else if (child.classList.contains('mods__server')) {
                    mode.children.push({ id: child.dataset.id, type: 'server' });
                }
            });
            result.push(mode);
        });
        return result;
    }

    function saveTree() {
        const tree = collectTree();
        $.ajax({
            url: location.href,
            type: 'POST',
            data: { changeSort: true, tree: JSON.stringify(tree) },
            dataType: 'json',
            success: function (data) {
                if (data && data.success !== undefined) {
                    noty(data.success, 'success');
                } else if (data && data.error) {
                    noty(data.error, 'error');
                }
            },
            error: function () {
                noty(get_translate_module_phrase('module_page_mon_settings', '_sortingError'), 'error');
            }
        });
    }

    new Sortable(modsTree, {
        group: 'modes',
        animation: 150,
        handle: '.mods__handle',
        draggable: '.mods__mode',
        ghostClass: 'grey-bg',
        onEnd: saveTree
    });

    function initModeSort(modeEl) {
        const modeName = modeEl.dataset.id;
        const childrenGroup = 'mode-children-' + modeName;
        const serversGroup = 'mode-servers-' + modeName;

        new Sortable(modeEl, {
            group: {
                name: childrenGroup,
                put: function (to, from, dragEl) {
                    if (!dragEl.classList.contains('mods__server')) return false;
                    return from.options.group.name === serversGroup || from.options.group.name === childrenGroup;
                },
                pull: function (to, from, dragEl) {
                    if (dragEl.classList.contains('mods__server')) {
                        return to.options.group.name === serversGroup || to.options.group.name === childrenGroup;
                    }
                    return to.options.group.name === childrenGroup;
                }
            },
            animation: 150,
            handle: '.mods__handle',
            draggable: '.mods__submod, .mods__server',
            ghostClass: 'grey-bg',
            onMove: function (evt) {
                const dragged = evt.dragged;
                const related = evt.related;
                if (evt.to === modeEl && dragged.classList.contains('mods__server')) {
                    const children = Array.from(modeEl.children);
                    let insertIdx;
                    if (!related) {
                        return true;
                    }
                    const relatedIdx = children.indexOf(related);
                    if (relatedIdx === -1) return true;
                    insertIdx = evt.willInsertAfter ? relatedIdx + 1 : relatedIdx;
                    const hasSubmodAfter = children.slice(insertIdx).some(c => c.classList.contains('mods__submod'));
                    if (hasSubmodAfter) return false;
                }
                return true;
            },
            onEnd: saveTree
        });

        modeEl.querySelectorAll(':scope > .mods__submod').forEach(submodEl => {
            new Sortable(submodEl, {
                group: {
                    name: serversGroup,
                    put: function (to, from, dragEl) {
                        return dragEl.classList.contains('mods__server') &&
                            (from.options.group.name === serversGroup || from.options.group.name === childrenGroup);
                    },
                    pull: [serversGroup, childrenGroup]
                },
                animation: 150,
                handle: '.mods__handle',
                draggable: '.mods__server',
                ghostClass: 'grey-bg',
                onEnd: saveTree
            });
        });
    }

    modsTree.querySelectorAll('.mods__mode').forEach(initModeSort);
}

$(document).on('submit', '#mods__form', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    appendPondFiles(formData, 'mods__form');
    $.ajax({
        type: 'POST',
        url: location.href,
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            noty(data.text, data.status);
            if (data.status === 'success') {
                setTimeout(() => { location.reload(); }, 700);
            }
        }
    });
});

$(document).on('click', '.mods__del-mode', function () {
    const btn = $(this);
    const id = btn.data('id');
    $.ajax({
        type: 'POST',
        url: location.href,
        data: { mon_mod_del: true, mod: id },
        dataType: 'json',
        success: function (data) {
            noty(data.text, data.status);
            if (data.status === 'success') {
                btn.closest('.mods__mode').remove();
            }
        }
    });
});

const imgBase = '/app/modules/module_block_main_servers/assets/img/mods/';

$(document).on('change', '#option-server_mod-select input[type="radio"]', function () {
    const key = $(this).data('desc-key') || '';
    $('#mods__form textarea[name="mod-description"]').val(key);
});

$(document).on('click', '[data-openmodal="ModModal"]', function () {
    const checked = $('#option-server_mod-select input[type="radio"]:checked');
    const key = checked.length ? (checked.data('desc-key') || '') : '';
    $('#mods__form textarea[name="mod-description"]').val(key);
});

$(document).on('change', '#edit-mode-select input[type="radio"]', function () {
    const key = $(this).data('desc-key') || '';
    $('#editModModal textarea[name="description"]').val(key);
});

$(document).on('click', '.mods__edit-mode', function () {
    const btn = $(this);
    $('#edit_mode_id').val(btn.data('id'));
    const name = btn.data('name');
    $('#edit-mode-select input[type="radio"]').each(function () {
        this.checked = (this.value === name);
        $(this).trigger('change');
    });

    const description = btn.data('description') || '';
    $('#editModModal textarea[name="description"]').val(description);

    const img1 = btn.data('image_1');
    const img2 = btn.data('image_2');
    const video = btn.data('video');

    const pond1 = modsPondInstances['edit:image_1'];
    const pond2 = modsPondInstances['edit:image_2'];
    const pondV = modsPondInstances['edit:video'];

    if (pond1) {
        pond1.removeFiles();
        if (img1) pond1.addFile(imgBase + img1, { type: 'load' });
    }
    if (pond2) {
        pond2.removeFiles();
        if (img2) pond2.addFile(imgBase + img2, { type: 'load' });
    }
    if (pondV) {
        pondV.removeFiles();
        if (video) pondV.addFile(imgBase + video, { type: 'load' });
    }
});

$(document).on('submit', '#edit_mode_form', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    appendPondFiles(formData, 'edit_mode_form');
    $.ajax({
        type: 'POST',
        url: location.href,
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            noty(data.text, data.status);
            if (data.status === 'success') setTimeout(() => location.reload(), 700);
        }
    });
});

$(document).on('submit', '#submods__form', function (e) {
    e.preventDefault();
    const formData = $(this).serialize();
    $.ajax({
        type: 'POST',
        url: location.href,
        data: formData,
        dataType: 'json',
        success: function (data) {
            noty(data.text, data.status);
            if (data.status === 'success') setTimeout(() => location.reload(), 700);
        }
    });
});

$(document).on('click', '.mods__edit-submod', function () {
    const btn = $(this);
    $('#edit_submod_id').val(btn.data('id'));
    $('#editSubModTitle').val(btn.data('title'));
});

$(document).on('submit', '#edit_submod_form', function (e) {
    e.preventDefault();
    const formData = $(this).serialize();
    $.ajax({
        type: 'POST',
        url: location.href,
        data: formData,
        dataType: 'json',
        success: function (data) {
            noty(data.text, data.status);
            if (data.status === 'success') setTimeout(() => location.reload(), 700);
        }
    });
});

$(document).on('click', '.mods__del-submod', function () {
    const btn = $(this);
    $.ajax({
        type: 'POST',
        url: location.href,
        data: { submod_del: true, submod_id: btn.data('id') },
        dataType: 'json',
        success: function (data) {
            noty(data.text, data.status);
            if (data.status === 'success') btn.closest('.mods__submod').remove();
        }
    });
});

$('#addAccessForm').on('submit', function (e) {
    e.preventDefault();
    const formData = $(this).serialize();
    $.ajax({
        type: 'POST',
        url: location.href,
        data: formData,
        dataType: 'json',
        success: function (data) {
            if (data.status == "success") {
                noty(data.text, data.status)
                $('#addAccess').removeClass('visible');
                $('#accessList').append(data.html);
                $('#addAccessForm').trigger('reset');
                $('.mon__empty-access-text').hide();
                $('.mon__access-wrapper').show();
            } else {
                noty(data.text, data.status)
            }
        }
    });
});

$('#panelAccessForm').on('submit', function (e) {
    e.preventDefault();
    const formData = $(this).serializeArray();
    const data = { panel_access_settings: true };
    data['mon_panel'] = $('#panelStatus').is(':checked') ? 1 : 0;
    data['excluded_admins_cs2'] = [];
    data['excluded_admins_csgo'] = [];
    formData.forEach(function (item) {
        if (item.name === 'excluded-admins-cs2[]') {
            data['excluded_admins_cs2'].push(item.value);
        } else if (item.name === 'excluded-admins-csgo[]') {
            data['excluded_admins_csgo'].push(item.value);
        }
    });
    $.ajax({
        type: 'POST',
        url: location.href,
        data: data,
        dataType: 'json',
        success: function (res) {
            noty(res.text, res.status);
        }
    });
});

$('#monSettingsForm').on('change', function () {
    const formData = new FormData(this);
    formData.append('mon_settings', true);
    $.ajax({
        type: 'POST',
        url: location.href,
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            noty(data.text, data.status);
        }
    });
});

$(document).on('click', '#installTables', function () {
    $('#installTablesSpinner').show();
    $.ajax({
        type: 'POST',
        url: location.href,
        data: { installTables: true },
        dataType: 'json',
        global: false,
        success: function (data) {
            noty(data.text, data.status);
            setTimeout(() => {
                location.reload();
            }, 2000);
        },
    });
});

$(document).on('submit', '#mods_settings_form', function (e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    $.ajax({
        type: 'POST',
        url: location.href,
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            noty(data.text, data.status);
        }
    });
});

const sortableTable = document.getElementById('sortable-table');
if (typeof Sortable !== 'undefined' && sortableTable) {
    const sortableRows = sortableTable.querySelectorAll('tr[data-mod]');
    if (sortableRows.length > 0) {
        new Sortable(sortableTable, {
            animation: 150,
            handle: '.handle',
            ghostClass: 'grey-bg',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onEnd: function () {
                let order = [];
                let table = $('#sortable-table');
                table.find('tr').each(function (index) {
                    let id = $(this).data('mod');
                    if (id) {
                        order.push({ id: id, sort: index + 1 });
                    }
                });

                $.ajax({
                    url: location.href,
                    type: 'POST',
                    data: {
                        changeSort: true,
                        order: JSON.stringify(order)
                    },
                    success: function (response) {
                        var jsonData = $.parseJSON(response);
                        if (jsonData.success !== undefined) {
                            noty(jsonData.success, 'success');
                        } else {
                            noty(jsonData.error || get_translate_module_phrase('module_page_mon_settings', '_sortingError'), 'error');
                        }
                    }
                });
            }
        });
    }
}

$(document).on('change', '.custom-file-input', function () {
    const fileInput = this;
    const file = fileInput.files[0];
    const infoBlock = $(fileInput).closest('.file-upload-container').find('.file-upload-info');

    if (file) {
        const size = (file.size / 1024).toFixed(1) + ' KB';
        const name = file.name.length > 40 ? file.name.substring(0, 37) + '...' : file.name;

        infoBlock.text(`${name} (${size})`).show();
    } else {
        infoBlock.text(get_translate_module_phrase('module_page_mon_settings', '_noFileSelected')).show();
    }
});

const toggleButtons = document.querySelectorAll(".mon-change-mode__button")

toggleButtons.forEach(button => {
    button.addEventListener("click", () => {
        const type = button.getAttribute("data-type");
        if (type === "0") {
            $('#monType').prop('checked', false).trigger('change');
            $('#monVisualTable').addClass('disabled');
            $('#monVisualCard').removeClass('disabled');
        } else {
            $('#monType').prop('checked', true).trigger('change');
            $('#monVisualTable').removeClass('disabled');
            $('#monVisualCard').addClass('disabled');
        }
        toggleActive(button);
    });
});

function toggleActive() {
    toggleButtons.forEach(elem => {
        elem.classList.toggle("active");
    })
}

if (typeof FilePond !== 'undefined') {

    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginFileValidateSize,
        FilePondPluginFileValidateType
    );

    const inputImages = document.querySelectorAll('input.images-pond');
    const videoEl = document.querySelector('.videos-pond');
    let videoServerFileLoaded = false;
    const inputVideo = videoEl ? FilePond.create(videoEl) : null;

    if (inputVideo) inputVideo.setOptions({
        labelIdle: get_translate_module_phrase('module_page_mon_settings', '_videoInput'),
        acceptedFileTypes: ['video/*'],
        fileValidateTypeLabelExpectedTypes: get_translate_module_phrase('module_page_mon_settings', '_videoInput'),
        allowMultiple: false,
        maxFiles: 1,
        onaddfile: (error, file) => {
            if (error) return;
            if (file.origin === 3 || file.origin === FilePond.FileOrigin?.LOCAL) {
                videoServerFileLoaded = true;
            }
        },
        onremovefile: (error, file) => {
            if (error) return;
            if (videoServerFileLoaded && (file.origin === 3 || file.origin === FilePond.FileOrigin?.LOCAL)) {
                videoServerFileLoaded = false;
            }
        },
        server: {
            process: {
                url: location.href,
                method: 'POST',
                withCredentials: false,
                headers: {},
                timeout: 7000,
                onload: (response) => {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        videoServerFileLoaded = true;
                        noty(res.text, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        noty(res.text, 'error');
                    }
                },
                onerror: (response) => {
                    noty(get_translate_module_phrase('module_page_mon_settings', '_uploadError'), 'error');
                }
            },
            revert: {
                url: location.href,
                method: 'POST',
                withCredentials: false,
                headers: {},
                timeout: 7000,
                onload: (response) => {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        videoServerFileLoaded = false;
                        noty(res.text, 'success');
                    } else {
                        noty(res.text, 'error');
                    }
                },
                onerror: (response) => {
                    noty(get_translate_module_phrase('module_page_mon_settings', '_deleteError'), 'error');
                }
            }
        }
    });

    const imagePondInstances = {};

    Array.from(inputImages).forEach(inputElement => {
        const fieldName = inputElement.name;
        let serverFileLoaded = false;
        let userAddedFile = false;

        const pond = FilePond.create(inputElement, {
            labelIdle: get_translate_module_phrase('module_page_mon_settings', '_Image'),
            acceptedFileTypes: ['image/*'],
            fileValidateTypeLabelExpectedTypes: get_translate_module_phrase('module_page_mon_settings', '_Image'),
            allowMultiple: false,
            maxFiles: 1,
            files: inputElement.dataset.src ? [
                {
                    source: inputElement.dataset.src,
                    options: {
                        type: 'local',
                    }
                }
            ] : [],
            onaddfile: (error, file) => {
                if (error) return;
                const originLocal = 3;
                const originLocalValue = FilePond.FileOrigin?.LOCAL || 3;
                if (file.origin === originLocal || file.origin === originLocalValue) {
                    userAddedFile = true;
                }
            },
            onremovefile: (error, file) => {
                if (error || !userAddedFile) return;
                userAddedFile = false;
                serverFileLoaded = false;
            },
            server: {
                process: {
                    url: location.href,
                    method: 'POST',
                    withCredentials: false,
                    headers: {},
                    timeout: 7000,
                    onload: (response) => {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            serverFileLoaded = true;
                            noty(res.text, 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            noty(res.text, 'error');
                        }
                    },
                    onerror: (response) => {
                        noty(get_translate_module_phrase('module_page_mon_settings', '_uploadError'), 'error');
                    }
                },
                revert: {
                    url: location.href,
                    method: 'POST',
                    withCredentials: false,
                    headers: {},
                    timeout: 7000,
                    onload: (response) => {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            serverFileLoaded = false;
                            noty(res.text, 'success');
                        } else {
                            noty(res.text, 'error');
                        }
                    },
                    onerror: (response) => {
                        noty(get_translate_module_phrase('module_page_mon_settings', '_deleteError'), 'error');
                    }
                },
                load: (source, load, error, progress, abort) => {

                    fetch(source)
                        .then(res => res.blob())
                        .then(blob => {
                            load(blob);
                        })
                        .catch(() => {
                            error(get_translate_module_phrase('module_page_mon_settings', '_uploadError'));
                        });

                    return {
                        abort: () => {
                            abort();
                        }
                    };
                }
            }
        });

        if (fieldName) imagePondInstances[fieldName] = { pond, serverFileLoaded };
    });

    const modsPondInstances = window._modsPondInstances = {};

    document.querySelectorAll('input.mods-img-pond, input.mods-video-pond').forEach(el => {
        const formId = el.closest('form')?.id;
        const fieldName = el.name;
        const editKey = el.dataset.editPond;

        let serverFileLoaded = false;
        let userAddedFile = false;

        const pond = FilePond.create(el, {
            labelIdle: el.classList.contains('mods-video-pond')
                ? get_translate_module_phrase('module_page_mon_settings', '_videoInput')
                : get_translate_module_phrase('module_page_mon_settings', '_Image'),
            acceptedFileTypes: el.classList.contains('mods-video-pond') ? ['video/*'] : ['image/*'],
            allowMultiple: false,
            maxFiles: 1,
            onremovefile: (error, file) => {
                if (error || !editKey || !serverFileLoaded || !userAddedFile) return;
                userAddedFile = false;
                serverFileLoaded = false;
                const modeId = document.getElementById('edit_mode_id')?.value;
                if (!modeId) return;
                $.post(location.href, { del_mod_file: 1, mode_id: modeId, field: editKey });
            },
            onaddfile: (error, file) => {
                if (error) return;
                const originLocal = 3;
                const originLocalValue = FilePond.FileOrigin?.LOCAL || 3;
                if (file.origin === originLocal || file.origin === originLocalValue) {
                    userAddedFile = true;
                }
            },
        });
        if (formId && fieldName) modsPondInstances[formId + ':' + fieldName] = pond;
        if (editKey) {
            modsPondInstances['edit:' + editKey] = pond;
            const origAddFile = pond.addFile.bind(pond);
            pond.addFile = function (source, options) {
                if (options && options.type === 'load') {
                    serverFileLoaded = true;
                    userAddedFile = false;
                }
                return origAddFile(source, options);
            };
            pond.removeFiles_orig = pond.removeFiles.bind(pond);
            pond.removeFiles = function () {
                serverFileLoaded = false;
                userAddedFile = false;
                return pond.removeFiles_orig();
            };
        }
    });

}

const modsPondInstances = window._modsPondInstances || {};

function appendPondFiles(formData, formId) {
    Object.entries(modsPondInstances).forEach(([key, pond]) => {
        if (!key.startsWith(formId + ':')) return;
        const fieldName = key.slice(formId.length + 1);
        const item = pond.getFile();
        if (!item) return;
        const fileObj = item.file;
        if (!(fileObj instanceof Blob)) return;
        if (item.origin !== 1 && item.origin !== 2) return;
        formData.append(fieldName, fileObj, item.filename || fileObj.name || fieldName);
    });
}

$(document).on('click', '.server-edit-btn', function () {
    const row = $(this).closest('tr');
    $('#editServerId').val(row.data('server-id'));
    $('#serverName').val(row.data('name'));
    $('#serverBage').val(row.data('bage') || '');
    $('#serverAddress').val(row.data('ip'));
    $('#rconPasswordEdit').val(row.data('rcon') || '');

    function setRadio(listId, value) {
        const $list = $('#' + listId);
        $list.find('input[type="radio"]').prop('checked', false);
        if (value !== undefined && value !== '' && value !== null) {
            $list.find('input[type="radio"][value="' + value + '"]').prop('checked', true);
        }
        updateAdaptiveSelectText($list.closest('.adaptive-select-wrapper'));
    }

    setRadio('serverGame', row.data('game'));
    setRadio('option-server_mod-select', row.data('mod-val'));
    setRadio('statusServer', String(row.data('status')));
    setRadio('statsDatabase', row.data('stats') || null);
    setRadio('vipDatabase', row.data('vip') || null);
    $('#vipID').val(row.data('vip-id') || '');
    setRadio('adminDatabase', row.data('sb') || null);
    $('#adminID').val(row.data('sb-id') || '');
});

$(document).on('submit', '#editServerForm', function (e) {
    e.preventDefault();
    $.ajax({
        type: 'POST',
        url: location.href,
        data: $(this).serialize(),
        dataType: 'json',
        success: function (data) {
            noty(data.text, data.status);
            if (data.status === 'success') setTimeout(() => location.reload(), 700);
        }
    });
});

$(document).on('click', '.server-del-btn', function () {
    const btn = $(this);
    $.ajax({
        type: 'POST',
        url: location.href,
        data: { del_server: true, server_id: btn.data('id') },
        dataType: 'json',
        success: function (data) {
            noty(data.text, data.status);
            if (data.status === 'success') btn.closest('tr').remove();
        }
    });
});

$(document).on('change', '#checkAllServers', function () {
    const checked = $(this).prop('checked');
    $('.server-checkbox').prop('checked', checked);
    $('.ex-mon__servers-action').toggle($('.server-checkbox:checked').length > 0);
});

$(document).on('change', '.server-checkbox', function () {
    const total    = $('.server-checkbox').length;
    const selected = $('.server-checkbox:checked').length;
    $('#checkAllServers').prop('indeterminate', selected > 0 && selected < total);
    $('#checkAllServers').prop('checked', selected === total);
    $('.ex-mon__servers-action').toggle(selected > 0);
});

$(document).on('click', '#bulkStatusBtn', function () {
    const status = $('#changeStatusSelected input[type="radio"]:checked').val();
    if (status === undefined) {
        noty(get_translate_module_phrase('module_page_mon_settings', '_bulkSelectStatus'), 'error');
        return;
    }
    const ids = [];
    $('.server-checkbox:checked').each(function () {
        ids.push($(this).closest('tr').data('server-id'));
    });
    if (ids.length === 0) {
        noty(get_translate_module_phrase('module_page_mon_settings', '_bulkSelectServers'), 'error');
        return;
    }
    $.ajax({
        type: 'POST',
        url: location.href,
        data: { bulk_status: true, ids: ids, status: status },
        dataType: 'json',
        success: function (data) {
            noty(data.text, data.status);
            if (data.status === 'success') setTimeout(() => location.reload(), 700);
        }
    });
});
