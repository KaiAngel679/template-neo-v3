var limit = localStorage.getItem('ticketsLimit') || 10;
var currentFilters = {
    search: '',
    category: 'all',
    server: 'all',
    limit: limit,
    my: 0,
    page: 1
};

function sendRequest(body) {
    return $.ajax({
        url: location.href,
        type: 'POST',
        data: body,
        dataType: 'json'
    });
}

function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    return String(unsafe)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

const $arrow = $('#ticketArrow');
const $navList = $('.tickets__nav-list');
const $listItems = $navList.find('li');

function checkScrollAvailability() {
    const needsScroll = $navList[0].scrollWidth > $navList[0].clientWidth;
    $arrow.css('display', needsScroll ? 'flex' : 'none');
    return needsScroll;
}

function checkScrollPosition() {
    const maxScroll = $navList[0].scrollWidth - $navList[0].clientWidth;
    if ($navList.scrollLeft() >= maxScroll - 5) {
        $arrow.css('display', 'none');
    }
}

if ($arrow.length && $navList.length && $listItems.length) {
    checkScrollAvailability();
    if (checkScrollAvailability()) {
        $arrow.on('click', function () {
            const currentScroll = $navList.scrollLeft();
            let nextItemPos = null;
            $listItems.each(function () {
                const $item = $(this);
                if ($item.position().left + $navList.scrollLeft() > currentScroll + 5) {
                    nextItemPos = $item.position().left + $navList.scrollLeft();
                    return false;
                }
            });
            if (nextItemPos !== null) {
                $navList.animate({ scrollLeft: nextItemPos }, 300);
            }
            setTimeout(checkScrollPosition, 300);
        });
        let lastScrollPos = $navList.scrollLeft();
        let scrollTimeout;
        $navList.on('scroll', function () {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                if ($navList.scrollLeft() < lastScrollPos) {
                    $arrow.css('display', 'flex');
                }
                lastScrollPos = $navList.scrollLeft();
                checkScrollPosition();
            }, 100);
        });
    }
    let resizeObserver = new ResizeObserver(() => {
        if (!checkScrollAvailability() && $arrow.css('display') !== 'none') {
            $arrow.css('display', 'none');
        }
    });
    resizeObserver.observe($navList[0]);
}

let pond;

function initFilepond() {
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginFileValidateSize,
        FilePondPluginFileValidateType
    );

    const filepondElement = document.querySelector('.filepond-multiple');
    if (filepondElement) {
        pond = FilePond.create(filepondElement, {
            allowMultiple: true,
            allowPaste: true,
            credits: false,
            maxFileSize: '3MB',
            acceptedFileTypes: ['image/*'],
            maxFiles: 5,
            labelIdle: '<div class="tickets__file-load"><span class="tickets__file-text">' + get_translate_module_phrase('module_page_tickets', '_dragDrop') + '<span class="filepond--label-action">' + get_translate_module_phrase('module_page_tickets', '_selectFile') + '</span></span><p>' + get_translate_module_phrase('module_page_tickets', '_noMore') + '</p></div>',
            server: {
                url: location.href,
                process: {
                    method: 'POST',
                    ondata: (formData) => {
                        formData.append('send_filepond', 'true');
                        return formData;
                    },
                    onload: (result) => {
                        const res = JSON.parse(result);
                        const filepondInput = document.getElementById('filepond');
                        let currentFiles = filepondInput.value;
                        currentFiles = currentFiles ? currentFiles + ';' + res.file : res.file;
                        filepondInput.value = currentFiles;
                        return res.file;
                    }
                },
                revert: (fileId, load) => {
                    fetch(location.href, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ file: fileId })
                    })
                        .then(() => {
                            const filepondInput = document.getElementById('filepond');
                            let currentFiles = filepondInput.value.split(';');
                            currentFiles = currentFiles.filter(f => f !== fileId);
                            filepondInput.value = currentFiles.join(';');
                            load();
                        });
                }
            }
        });
    }
}

function initTippy() {
    tippy('[data-tippy-content]', {
        animation: 'shift-away',
        theme: 'neo',
        allowHTML: true
    });
}

function renderPagination(totalItems, perPage, currentPage) {
    const totalPages = Math.ceil(totalItems / perPage);
    const paginationContainer = $('#pagination');
    let paginationHtml = '';
    if (totalPages > 1) {
        paginationHtml += '<div class="pagination">';
        if (currentPage > 1) {
            paginationHtml += `<a class="button_pagination" data-page="1">
                <svg><use href="/resources/img/sprite.svg#double-chevrone-left"></use></svg>
            </a>`;
            paginationHtml += `<a class="button_pagination" data-page="${currentPage - 1}">
                <svg><use href="/resources/img/sprite.svg#single-chevrone-left"></use></svg>
            </a>`;
        }
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);
        if (currentPage <= 3) {
            endPage = Math.min(5, totalPages);
        }
        if (currentPage >= totalPages - 2) {
            startPage = Math.max(1, totalPages - 4);
        }
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `<a class="button_pagination ${i === +currentPage ? 'active' : ''}" data-page="${i}">${i}</a>`;
        }
        if (currentPage < totalPages) {
            paginationHtml += `<a class="button_pagination" data-page="${+currentPage + 1}">
                <svg><use href="/resources/img/sprite.svg#single-chevrone-right"></use></svg>
            </a>`;
            paginationHtml += `<a class="button_pagination" data-page="${totalPages}">
                <svg><use href="/resources/img/sprite.svg#double-chevrone-right"></use></svg>
            </a>`;
        }
        paginationHtml += '</div>';
    }
    paginationContainer.html(paginationHtml);
}

$(document).on('click', '.button_pagination', function (e) {
    e.preventDefault();
    const page = $(this).data('page');
    if (page && page !== currentFilters.page) {
        currentFilters.page = page;
        updateTicketsList();
        $('html, body').animate({
            scrollTop: 0
        }, 300);
    }
});

let searchTimer;
$('#search').on('input', function () {
    clearTimeout(searchTimer);
    currentFilters.search = $(this).val().trim();
    searchTimer = setTimeout(updateTicketsList, 500);
});

$(document).on('change', 'input[name="categories-filter"]', function () {
    currentFilters.category = $('input[name="categories-filter"]:checked').val();
    updateTicketsList();
});

$(document).on('change', 'input[name="servers-filter"]', function () {
    currentFilters.server = $('input[name="servers-filter"]:checked').val();
    updateTicketsList();
});

$(document).on('change', '#myTickets', function () {
    currentFilters.my = $('#myTickets').is(':checked') ? 1 : 0;
    updateTicketsList();
});

$('.tickets__filter-button').on('click', function () {
    $('.tickets__filter-button').removeClass('active');
    $(this).addClass('active');
    currentFilters.limit = $(this).text();
    localStorage.setItem('ticketsLimit', currentFilters.limit);
    updateTicketsList();
});

$('#search, input[name="categories-filter"], input[name="servers-filter"], #myTickets, .tickets__filter-button').on('change input click', function () {
    if (currentFilters.page !== 1) {
        currentFilters.page = 1;
    }
});

function initFilters() {
    $('.tickets__filter-button').removeClass('active');
    $(`.tickets__filter-button:contains('${limit}')`).addClass('active');
}

$(document).on('paste', '#message', function (e) {
    const clipboardData = e.originalEvent.clipboardData || e.clipboardData;
    const items = clipboardData.items;
    $.each(items, function (index, item) {
        if (item.type.indexOf('image') === 0) {
            const file = item.getAsFile();
            pond.addFile(file);
            e.preventDefault();
        }
    });
});

function checkAvatar(checkavatar, steamid) {
    if (checkavatar === 1) {
        avatar.push(steamid);
    }
    RenderingAvatar();
}

const currentPath = window.location.pathname;
if (currentPath === '/tickets/' || currentPath === '/tickets/send/') {
    $('.ticket__radio-input').on('change', function () {
        if ($(this).is(':checked')) {
            const id = $(this).val();
            sendRequest({ send_category: true, category_id: id })
                .done(function (result) {
                    if (result.status == 'success') {
                        let serverOptions = '<li><label class="adaptive-select__label" for="none"><div class="adaptive-select__label-text">' + get_translate_phrase('_selectServer') + '</div><input class="hide-input" id="none" value="none" name="servers" type="radio" disabled checked></label></li>';
                        let serverSelectHtml = '';
                        if (result.server_on == 1 && result.servers && result.servers.length > 0) {
                            result.servers.forEach(function (server) {
                                serverOptions += `
                                <li>
                                    <label class="adaptive-select__label" for="server-${server.id}">
                                        <div class="adaptive-select__label-text">${server.name_custom}</div>
                                        <input class="hide-input" id="server-${server.id}" value="${server.id}" name="servers" type="radio">
                                    </label>
                                </li>
                                `;
                            });
                            serverSelectHtml = `
                            <div class="adaptive-select-wrapper">
                                <ul class="adaptive-select__dropdown-list" id="send-servers">
                                    ${serverOptions}
                                </ul>
                                <div class="adaptive-select" open-select="send-servers">
                                    <span class="adaptive-select__fist-icon">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#servers"></use>
                                        </svg>
                                    </span>
                                    <span class="adaptive-select__span_text">${get_translate_phrase('_selectServer')}</span>
                                    <span class="margin-left-auto adaptive-select__arrow">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>`;
                        }
                        let descriptionHtml = result.description ? `
                            <div class="tickets__embed">
                                <svg><use href="/resources/img/sprite.svg#info-circle"></use></svg>
                                <div class="tickets__embed-area">
                                    ${result.description}
                                </div>
                            </div>
                        ` : '';
                        let typeHtml = '';
                        if (result.type == 1) {
                            typeHtml = `
                                <div class="ticket__message">
                                    <label for="message" class="ticket__message-label">${get_translate_module_phrase('module_page_tickets', '_sendMessage')}</label>
                                    <textarea class="ticket__message-teaxtarea" id="message" placeholder="${get_translate_module_phrase('module_page_tickets', '_textareaPlaceholder')}" required maxlength="1000" spellcheck="true" wrap="hard"></textarea>
                                    <input type="file" class="filepond-multiple">
                                    <input type="hidden" id="filepond">
                                    <button class="active" id="send-form">${get_translate_module_phrase('module_page_tickets', '_creatTicket')}</button>
                                </div>
                            `;
                        } else if (result.type == 2) {
                            typeHtml += `<div class="tickets__send-strong"><h3>${get_translate_module_phrase('module_page_tickets', '_fillFields')}</h3>`;
                            if (result.questions && result.questions.length > 0) {
                                const questionsArr = result.questions.split(';');
                                questionsArr.forEach(function (q, idx) {
                                    typeHtml += `
                                        <div class="inputs-inlin">
                                            <label for="question-${idx}">${q}</label>
                                            <input id="question-${idx}" type="text" placeholder="${get_translate_module_phrase('module_page_tickets', '_answerSpecify')}" required maxlength="500">
                                        </div>
                                    `;
                                });
                            }
                            typeHtml += `<input type="file" class="filepond-multiple">
                                <input type="hidden" id="filepond">
                                <button class="active" id="send-form">${get_translate_module_phrase('module_page_tickets', '_creatTicket')}</button>
                            </div>`;
                        }
                        $('#category-render').html(`
                            ${descriptionHtml}
                            <div class="ticket__details">
                                <input type="text" id="topic" placeholder="${get_translate_module_phrase('module_page_tickets', '_subjectAppeal')}" maxlength="100">
                                ${serverSelectHtml}
                            </div>
                        ${typeHtml}`);
                        initFilepond();
                    } else {
                        $('#category-render').html('');
                    }
                })
        }
    });
    $('.ticket__radio-input:checked').trigger('change');

    $(document).on('click', '#send-form', function () {
        let data = {
            send_form: true,
            category: $('input[name="ticket-category"]:checked').val(),
            topic: $('#topic').val(),
            message: $('#message').val(),
            file: $('#filepond').val()
        };

        if ($('#send-servers').length) {
            data.server = $('input[name="servers"]:checked').val();
        }

        let questions = [];
        $('[id^="question-"]').each(function () {
            questions.push($(this).val());
        });
        let hasQuestions = questions.some(a => a.length > 0);
        if (hasQuestions) {
            data.questions = questions.join(';');
            delete data.message;
        }

        openDialog({
            title: get_translate_module_phrase('module_page_tickets', '_confirm'),
            message: get_translate_module_phrase('module_page_tickets', '_dialogWantCreate'),
            confirmText: get_translate_module_phrase('module_page_tickets', '_creatTicketDialog'),
            cancelText: get_translate_module_phrase('module_page_tickets', '_no'),
            onConfirm: function () {
                sendRequest(data)
                    .done(function (result) {
                        if (result.status == 'success' && result.url) {
                            window.location.href = result.url;
                        } else {
                            noty(result.text, result.status);
                        }
                    });
            }
        })
    });
}
else if (currentPath === '/tickets/list/') {
    function renderList(category, server, limit, search, my, page) {
        sendRequest({ render_list: true, category: category, server: server, limit: limit, search: search, my: my, page: page })
            .done(function (result) {
                if (result.status == 'success') {
                    if (result.count == 0) {
                        $('#list-render').html('<div class="tickets__empty-data">' + get_translate_module_phrase('module_page_tickets', '_noTickets') + '</div>');
                        $('#pagination').html('');
                    } else {
                        if (result.admin == 1) {
                            let ticketsHtml = '<ul class="tickets__open-list">';
                            result.list.forEach(function (ticket) {
                                ticketsHtml += `
                                    <li>
                                        <img class="tickets__open-avatar" src="${ticket.avatar}" id="avatar" avatarid="${ticket.steamid}" loading="lazy">
                                        <a href="/tickets/chat/${ticket.id}/" class="ticket__open-details">
                                            <div class="ticket__open-details-top">
                                                <span class="ticket__open-number">${get_translate_module_phrase('module_page_tickets', '_ticket')} №${ticket.id}</span><span class="ticket__open-category">${escapeHtml(ticket.title)}</span>
                                                <span class="ticket__open-title">${escapeHtml(ticket.topic)}</span>
                                            </div>
                                            <div class="ticket__open-details-bottom">
                                                <div class="ticket__open-date">${ticket.date}</div>
                                                <div class="ticket__open-body">${escapeHtml(ticket.first_message)}</div>
                                            </div>
                                        </a>
                                        <div class="tickets__open-action">
                                            <button class="move" data-openmodal="moveTicketModal" data-id="${ticket.id}" data-category="${ticket.category_id}">
                                                <svg><use href="/resources/img/sprite.svg#change"></use></svg>
                                                <span>${get_translate_module_phrase('module_page_tickets', '_move')}</span>
                                            </button>
                                            <button class="close" data-id="${ticket.id}">
                                                <svg><use href="/resources/img/sprite.svg#lock"></use></svg>
                                                <span>${get_translate_phrase('_Close')}</span>
                                            </button>
                                            ${result.delete == 1 ? `
                                                <button class="button-delete delete" data-id="${ticket.id}">
                                                    <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
                                                    <span>${get_translate_phrase('_Delete_Action')}</span>
                                                </button>
                                            ` : ''}
                                        </div>
                                    </li>
                                `;
                                checkAvatar(ticket.checkAvatar, ticket.steamid);
                            });
                            ticketsHtml += '</ul>';
                            $('#list-render').html(ticketsHtml);
                            renderPagination(result.total, limit, result.page);
                        } else {
                            let ticketsHtml = '<ul class="tickets__open-list">';
                            result.list.forEach(function (ticket) {
                                ticketsHtml += `
                                    <li>
                                        <img class="tickets__open-avatar" src="${ticket.avatar}" id="avatar" avatarid="${ticket.steamid}" loading="lazy">
                                        <a href="/tickets/chat/${ticket.id}/" class="ticket__open-details">
                                            <div class="ticket__open-details-top">
                                                <span class="ticket__open-number">${get_translate_module_phrase('module_page_tickets', '_ticket')} №${ticket.id}</span><span class="ticket__open-category">${ticket.title}</span>
                                                <span class="ticket__open-title">${ticket.topic}</span>
                                            </div>
                                            <div class="ticket__open-details-bottom">
                                                <div class="ticket__open-date">${ticket.date}</div>
                                                <div class="ticket__open-body">${ticket.first_message}</div>
                                            </div>
                                        </a>
                                        <div class="tickets__open-action">
                                            <button class="close" data-id="${ticket.id}">
                                                <svg><use href="/resources/img/sprite.svg#lock"></use></svg>
                                                <span>${get_translate_phrase('_Close')}</span>
                                            </button>
                                        </div>
                                    </li>
                                `;
                                checkAvatar(ticket.checkAvatar, ticket.steamid);
                            });
                            ticketsHtml += '</ul>';
                            $('#list-render').html(ticketsHtml);
                            renderPagination(result.total, limit, result.page);
                        }
                    }
                } else {
                    $('#list-render').html('');
                    $('#pagination').html('');
                }
            });
    }

    function updateTicketsList() {
        renderList(currentFilters.category, currentFilters.server, currentFilters.limit, currentFilters.search, currentFilters.my, currentFilters.page);
    }

    $(document).on('click', '.close', function () {
        const id = $(this).data('id');
        openDialog({
            title: get_translate_module_phrase('module_page_tickets', '_confirm'),
            message: get_translate_module_phrase('module_page_tickets', '_wantCloseTicket'),
            confirmText: get_translate_module_phrase('module_page_tickets', '_dialogClose'),
            cancelText: get_translate_module_phrase('module_page_tickets', '_dialogChangeMind'),
            onConfirm: function () {
                sendRequest({ close_ticket: true, ticket_id: id })
                    .done(function (result) {
                        if (result.status === 'success') {
                            updateTicketsList();
                            noty(result.text, result.status);
                        } else {
                            noty(result.text, result.status);
                        }
                    });
            }
        })
    });

    $(document).on('click', '.delete', function () {
        const id = $(this).data('id');
        openDialog({
            title: get_translate_module_phrase('module_page_tickets', '_confirm'),
            message: get_translate_module_phrase('module_page_tickets', '_dialogCannotUndone'),
            confirmText: get_translate_module_phrase('module_page_tickets', '_dialogDelete'),
            cancelText: get_translate_module_phrase('module_page_tickets', '_dialogLeave'),
            onConfirm: function () {
                sendRequest({ delete_ticket: true, ticket_id: id })
                    .done(function (result) {
                        if (result.status === 'success') {
                            updateTicketsList();
                            noty(result.text, result.status);
                        } else {
                            noty(result.text, result.status);
                        }
                    });
            }
        })
    });

    $(document).on('click', '.move', function () {
        const id = $(this).data('id');
        const categoryId = $(this).data('category');
        sendRequest({ get_categories: true })
            .done(function (result) {
                if (result.status === 'success') {
                    const modalHtml = `
                        <div class="popup_modal_content no-close no-scrollbar">
                            <div class="popup_modal_head">
                                ${get_translate_module_phrase('module_page_tickets', '_moveTicket')}
                                <span class="popup_modal_close">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#x"></use>
                                    </svg>
                                </span>
                            </div>
                            <div class="settings-body">
                                <label for="changeCategory">${get_translate_module_phrase('module_page_tickets', '_changingCategory')}</label>
                                <div class="adaptive-select-wrapper">
                                    <ul class="adaptive-select__dropdown-list" id="categories-move">
                                        ${generateCategoryOptions(result.categories, categoryId)}
                                    </ul>
                                    <div class="adaptive-select" open-select="categories-move">
                                        <span class="adaptive-select__fist-icon">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#list"></use>
                                            </svg>
                                        </span>
                                        <span class="adaptive-select__span_text"></span>
                                        <span class="margin-left-auto adaptive-select__arrow">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                </select>
                                <hr>
                                <button class="width-100" id="confirmMove">${get_translate_module_phrase('module_page_tickets', '_save')}</button>
                            </div>
                        </div>
                    `;
                    $('#moveTicketModal').html(modalHtml);
                    initAdaptiveSelects('#moveTicketModal');
                    $('#confirmMove').on('click', function () {
                        sendRequest({ move_ticket: true, ticket_id: id, new_category_id: $('input[name="category-move"]:checked').val() })
                            .done(function (result) {
                                if (result.status === 'success') {
                                    $('#moveTicketModal').html('').removeClass('visible');
                                    updateTicketsList();
                                    noty(result.text, result.status);
                                } else {
                                    noty(result.text, result.status);
                                }
                            });
                    });
                }
            });
    });

    function generateCategoryOptions(categories, currentCategoryId) {
        let options = '';
        categories.forEach(function (category) {
            options += `<li><label class="adaptive-select__label" for="category-move-${category.id}"><div class="adaptive-select__label-text">${category.title}</div><input class="hide-input" id="category-move-${category.id}" value="${category.id}" type="radio" name="category-move" ${category.id == currentCategoryId ? 'checked' : ''}></label></li>`;
        });
        return options;
    }

    initFilters();
    updateTicketsList();
}
else if (currentPath === '/tickets/archive/') {
    function renderArchive(category, server, limit, search, my, page) {
        sendRequest({ render_archive: true, category: category, server: server, limit: limit, search: search, my: my, page: page })
            .done(function (result) {
                if (result.status == 'success') {
                    if (result.count == 0) {
                        $('#archive-render').html('<div class="tickets__empty-data">' + get_translate_module_phrase('module_page_tickets', '_noTickets') + '</div>');
                        $('#pagination').html('');
                    } else {
                        if (result.admin == 1) {
                            let ticketsHtml = '<ul class="tickets__open-list">';
                            result.archive.forEach(function (ticket) {
                                ticketsHtml += `
                                    <li>
                                        <img class="tickets__open-avatar" src="${ticket.avatar}" id="avatar" avatarid="${ticket.steamid}" loading="lazy">
                                        <a href="/tickets/chat/${ticket.id}/" class="ticket__open-details">
                                            <div class="ticket__open-details-top">
                                                <svg data-tippy-content="${get_translate_module_phrase('module_page_tickets', '_closedBy')} ${ticket.name} • ${ticket.date_close}" data-tippy-placement="right"><use href="/resources/img/sprite.svg#lock"></use></svg>
                                                <span class="ticket__open-number">${get_translate_module_phrase('module_page_tickets', '_ticket')} №${ticket.id}</span><span class="ticket__open-category">${escapeHtml(ticket.title)}</span>
                                                <span class="ticket__open-title">${escapeHtml(ticket.topic)}</span>
                                            </div>
                                            <div class="ticket__open-details-bottom">
                                                <div class="ticket__open-date">${ticket.date}</div>
                                                <div class="ticket__open-body">${escapeHtml(ticket.first_message)}</div>
                                            </div>
                                        </a>
                                        <div class="tickets__open-action">
                                            <button class="open" data-id="${ticket.id}">
                                                <svg><use href="/resources/img/sprite.svg#unlock"></use></svg>
                                                <span>${get_translate_module_phrase('module_page_tickets', '_open')}</span>
                                            </button>
                                            ${result.delete == 1 ? `
                                                <button class="button-delete delete" data-id="${ticket.id}">
                                                    <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
                                                    <span>${get_translate_phrase('_Delete_Action')}</span>
                                                </button>
                                            ` : ''}
                                        </div>
                                    </li>
                                `;
                                checkAvatar(ticket.checkAvatar, ticket.steamid);
                            });
                            ticketsHtml += '</ul>';
                            $('#archive-render').html(ticketsHtml);
                            renderPagination(result.total, limit, result.page);
                        } else {
                            let ticketsHtml = '<ul class="tickets__open-list">';
                            result.archive.forEach(function (ticket) {
                                ticketsHtml += `
                                    <li>
                                        <img class="tickets__open-avatar" src="${ticket.avatar}" id="avatar" avatarid="${ticket.steamid}" loading="lazy">
                                        <a href="/tickets/chat/${ticket.id}/" class="ticket__open-details">
                                            <div class="ticket__open-details-top">
                                                <svg><use href="/resources/img/sprite.svg#lock"></use></svg>
                                                <span class="ticket__open-number">${get_translate_module_phrase('module_page_tickets', '_ticket')} №${ticket.id}</span><span class="ticket__open-category">${ticket.title}</span>
                                                <span class="ticket__open-title">${ticket.topic}</span>
                                            </div>
                                            <div class="ticket__open-details-bottom">
                                                <div class="ticket__open-date">${ticket.date}</div>
                                                <div class="ticket__open-body">${ticket.first_message}</div>
                                            </div>
                                        </a>
                                    </li>
                                `;
                                checkAvatar(ticket.checkAvatar, ticket.steamid);
                            });
                            ticketsHtml += '</ul>';
                            $('#archive-render').html(ticketsHtml);
                            renderPagination(result.total, limit, result.page);
                        }
                    }
                    initTippy();
                } else {
                    $('#archive-render').html('');
                    $('#pagination').html('');
                }
            });
    }

    function updateTicketsList() {
        renderArchive(currentFilters.category, currentFilters.server, currentFilters.limit, currentFilters.search, currentFilters.my, currentFilters.page);
    }

    $(document).on('click', '.open', function () {
        const id = $(this).data('id');
        openDialog({
            title: get_translate_module_phrase('module_page_tickets', '_confirm'),
            message: get_translate_module_phrase('module_page_tickets', '_dialogwantOpen'),
            confirmText: get_translate_module_phrase('module_page_tickets', '_open'),
            cancelText: get_translate_module_phrase('module_page_tickets', '_dialogChangeMind'),
            onConfirm: function () {
                sendRequest({ open_ticket: true, ticket_id: id })
                    .done(function (result) {
                        if (result.status === 'success') {
                            updateTicketsList();
                            noty(result.text, result.status);
                        } else {
                            noty(result.text, result.status);
                        }
                    });
            }
        })
    });

    $(document).on('click', '.delete', function () {
        const id = $(this).data('id');
        openDialog({
            title: get_translate_module_phrase('module_page_tickets', '_confirm'),
            message: get_translate_module_phrase('module_page_tickets', '_dialogCannotUndone'),
            confirmText: get_translate_module_phrase('module_page_tickets', '_dialogDelete'),
            cancelText: get_translate_module_phrase('module_page_tickets', '_dialogLeave'),
            onConfirm: function () {
                sendRequest({ delete_ticket: true, ticket_id: id })
                    .done(function (result) {
                        if (result.status === 'success') {
                            updateTicketsList();
                            noty(result.text, result.status);
                        } else {
                            noty(result.text, result.status);
                        }
                    });
            }
        })
    });

    initFilters();
    updateTicketsList();
}
else if (currentPath === '/tickets/settings/') {
    $('.type-category').on('click', function () {
        $('.type-category').removeClass('active');
        $(this).addClass('active');
        let type = $(this).data('type');
        let html = '';
        if (type == 2) {
            html = `
                <hr>
                <button class="width-100" id="new-question"><svg><use href="/resources/img/sprite.svg#plus"></use></svg>${get_translate_module_phrase('module_page_tickets', '_new')}</button>
                <div class="tickets__settings-flex-inline new-input">
                    <input id="question-0" type="text" placeholder="${get_translate_module_phrase('module_page_tickets', '_enterQuestion')}">
                    <button class="icon_btn_transparent button-delete delete-question" disabled><svg><use href="/resources/img/sprite.svg#trash"></use></svg></button>
                </div>
                <hr>
            `;
        }
        $('#type-render').html(html);
    });
    $(document).on('click', '#new-question', function () {
        var $container = $(this).closest('#type-render').length ? $('#type-render') : $(this).parent();
        var $last = $container.find('.new-input').last();
        var newInput = `
            <div class="tickets__settings-flex-inline new-input">
                <input id="question-${$container.find('.new-input').length}" type="text" placeholder="${get_translate_module_phrase('module_page_tickets', '_enterQuestion')}">
                <button class="icon_btn_transparent button-delete delete-question"><svg><use href="/resources/img/sprite.svg#trash"></use></svg></button>
            </div>
        `;
        $last.after(newInput);
    });
    $(document).on('click', '.new-input .delete-question:not([disabled])', function () {
        var $container = $(this).closest('#type-render').length ? $('#type-render') : $(this).closest('.new-input').parent();
        var $allQuestions = $container.find('.new-input');
        if ($allQuestions.index($(this).closest('.new-input')) > 0) {
            $(this).closest('.new-input').remove();
        }
    });
    const $descriptionBlock = $('#catDescription').closest('.inputs-inline');
    const $serversBlock = $('#selectedenableServers');
    const $playtimeBlock = $('#playedTime').closest('.inputs-inline');
    const $responseTimeBlock = $('#responseTimeInput').closest('.inputs-inline');
    const $replayTimeBlock = $('#replayTimeInput').closest('.inputs-inline');
    const $moneyCountBlock = $('#moneyCount').closest('.inputs-inline');
    $descriptionBlock.toggle($('#enableDescription').is(':checked'));
    $serversBlock.toggle($('#enableServers').is(':checked'));
    $playtimeBlock.toggle($('#bannedPLayer').is(':checked'));
    $responseTimeBlock.toggle($('#responseTime').is(':checked'));
    $replayTimeBlock.toggle($('#replayTime').is(':checked'));
    $moneyCountBlock.toggle($('#moneyTicket').is(':checked'));
    $('#enableDescription').change(function () {
        $descriptionBlock.toggle($(this).is(':checked'));
        if (!$(this).is(':checked')) {
            $('#catDescription').val('');
        }
    });
    $('#enableServers').change(function () {
        $serversBlock.toggle($(this).is(':checked'));
    });
    $('#bannedPLayer').change(function () {
        $playtimeBlock.toggle($(this).is(':checked'));
        if (!$(this).is(':checked')) {
            $('#playedTime').val('');
        }
    });
    $('#responseTime').change(function () {
        $responseTimeBlock.toggle($(this).is(':checked'));
        if (!$(this).is(':checked')) {
            $('#responseTimeInput').val('');
        }
    });
    $('#replayTime').change(function () {
        $replayTimeBlock.toggle($(this).is(':checked'));
        if (!$(this).is(':checked')) {
            $('#replayTimeInput').val('');
        }
    });
    $('#moneyTicket').change(function () {
        $moneyCountBlock.toggle($(this).is(':checked'));
        if (!$(this).is(':checked')) {
            $('#moneyCount').val('');
        }
    });

    $('#new-category').click(function () {
        let selectedServers = [];
        $('input[name="servers-add"]:checked').each(function () {
            selectedServers.push($(this).val());
        });
        let questions = [];
        $('#type-render .tickets__settings-flex-inline input[type="text"]').each(function () {
            if ($(this).val().length > 0) questions.push($(this).val());
        });
        sendRequest({ new_category: true, type: $('.type-category.active').data('type') || 1, category: $('#catName').val(), questions: questions.join(';'), description: $('#enableDescription').is(':checked') ? $('#catDescription').val() : '', server_on: $('#enableServers').is(':checked') ? 1 : 0, servers: $('#enableServers').is(':checked') ? selectedServers.join(';') : '', playtime: $('#bannedPLayer').is(':checked') ? $('#playedTime').val() : '', response_time: $('#responseTime').is(':checked') ? $('#responseTimeInput').val() : '', replay_time: $('#replayTime').is(':checked') ? $('#replayTimeInput').val() : '', amount_money: $('#moneyTicket').is(':checked') ? $('#moneyCount').val() : '', sort: $('#sortId').val() })
            .done(function (result) {
                if (result.status === 'success' && result.url == 'reload') {
                    location.reload();
                } else {
                    noty(result.text, result.status);
                }
            });
    });

    $('#new-answer').click(function () {
        sendRequest({ new_answer: true, button: $('#buttonText').val(), answer: $('#answerText').val() })
            .done(function (result) {
                if (result.status === 'success') {
                    location.reload();
                } else {
                    noty(result.text, result.status);
                }
            });
    });

    const $slowModeInterval = $('#slowModeInterval').closest('.inputs-inline');
    $slowModeInterval.toggle($('#slowModeEnable').is(':checked'));
    $('#slowModeEnable').change(function () {
        $slowModeInterval.toggle($(this).is(':checked'));
        if (!$(this).is(':checked')) {
            $('#slowModeInterval').val('');
        }
    });

    $('#save-settings').click(function () {
        sendRequest({ save_settings: true, noty: $('#siteNoty').is(':checked') ? 1 : 0, url: $('#webhookUrl').val(), color: $('#webhookColor').val(), img: $('#webhookImg').val() })
            .done(function (result) {
                if (result.status === 'success') {
                    noty(result.text, result.status);
                } else {
                    noty(result.text, result.status);
                }
            });
    });

    const $autoClose = $('#autoCloseInterval').closest('.inputs-inline');
    $autoClose.toggle($('#autoCloseEnable').is(':checked'));
    $('#autoCloseEnable').change(function () {
        $autoClose.toggle($(this).is(':checked'));
        if (!$(this).is(':checked')) {
            $('#autoCloseInterval').val('');
        }
    });

    $('#save-settings-new').click(function () {
        sendRequest({ save_new_settings: true, slow: $('#slowModeEnable').is(':checked') ? 1 : 0, slow_time: $('#slowModeInterval').val(), auto_close: $('#autoCloseEnable').is(':checked') ? 1 : 0, duration: $('#autoCloseInterval').val() })
            .done(function (result) {
                if (result.status === 'success') {
                    noty(result.text, result.status);
                } else {
                    noty(result.text, result.status);
                }
            });
    });

    $('.deleteDescription').click(function () {
        const button = $(this);
        const id = button.data('id');
        openDialog({
            title: get_translate_module_phrase('module_page_tickets', '_confirm'),
            message: get_translate_module_phrase('module_page_tickets', '_dialogwantDelCat'),
            confirmText: get_translate_module_phrase('module_page_tickets', '_dialogDelete'),
            cancelText: get_translate_module_phrase('module_page_tickets', '_dialogLeave'),
            onConfirm: function () {
                sendRequest({ delete_category: true, id: id })
                    .done(function (result) {
                        if (result.status === 'success') {
                            button.closest('.tickets__cat-edit').remove();
                            noty(result.text, result.status);
                        } else {
                            noty(result.text, result.status);
                        }
                    });
            }
        });
    });

    $('.edit').click(function () {
        const id = $(this).data('id');
        sendRequest({ get_answer_edit: true, id: id })
            .done(function (result) {
                if (result.status === 'success') {
                    const modalHtml = `
                        <div class="popup_modal_content no-close no-scrollbar">
                            <div class="popup_modal_head">
                                ${get_translate_module_phrase('module_page_tickets', '_changingReady')}
                                <span class="popup_modal_close">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#x"></use>
                                    </svg>
                                </span>
                            </div>
                            <div class="settings-body">
                                <div class="inputs-inline">
                                    <label for="buttonTextEdit">${get_translate_module_phrase('module_page_tickets', '_buttonText')}</label>
                                    <input type="text" id="buttonTextEdit" required value="${result.data.text_button}">
                                </div>
                                <div class="inputs-inline">
                                    <label for="answerTextEdit">${get_translate_module_phrase('module_page_tickets', '_replyText')}</label>
                                    <textarea id="answerTextEdit" required>${result.data.text_answer}</textarea>
                                </div>
                                <button class="width-100" id="saveAnswer">${get_translate_module_phrase('module_page_tickets', '_save')}</button>
                            </div>
                        </div>
                    `;
                    $('#editAnswer').html(modalHtml);
                    $('#saveAnswer').on('click', function () {
                        sendRequest({ edit_answer: true, id: id, button: $('#buttonTextEdit').val(), text: $('#answerTextEdit').val() })
                            .done(function (result) {
                                if (result.status == 'success' && result.url == 'reload') {
                                    location.reload();
                                } else {
                                    noty(result.text, result.status);
                                }
                            });
                    });
                }
            });
    });

    $('.delete').click(function () {
        const button = $(this);
        const id = button.data('id');
        openDialog({
            title: get_translate_module_phrase('module_page_tickets', '_confirm'),
            message: get_translate_module_phrase('module_page_tickets', '_dialogwantDelReply'),
            confirmText: get_translate_module_phrase('module_page_tickets', '_dialogDelete'),
            cancelText: get_translate_module_phrase('module_page_tickets', '_dialogLeave'),
            onConfirm: function () {
                sendRequest({ delete_answer: true, id: id })
                    .done(function (result) {
                        if (result.status === 'success') {
                            button.closest('.tickets__ready-wrapper').remove();
                            noty(result.text, result.status);
                        } else {
                            noty(result.text, result.status);
                        }
                    });
            }
        });
    });

    $('.change-description').click(function () {
        const id = $(this).data('id');
        sendRequest({ get_category_edit: true, id: id })
            .done(function (result) {
                if (result.status === 'success') {
                    const allServers = result.servers;
                    const selectedServers = result.select || '';
                    const selectedServerIds = selectedServers.split(';');
                    const serverOn = result.server_on || '0';
                    let serversHtml = '';
                    allServers.forEach(server => {
                        const isChecked = selectedServerIds.includes(server.id.toString());
                        serversHtml += `
                        <li>
                            <label class="adaptive-select__label" for="server-edit-${server.id}">
                                <div class="adaptive-select__label-text">${server.name_custom}</div>
                                <input class="hide-input" id="server-edit-${server.id}" value="${server.id}" type="checkbox" name="servers-edit" ${isChecked ? 'checked' : ''}>
                            </label>
                        </li>
                    `;
                    });
                    const modalHtml = `
                        <div class="popup_modal_content no-close no-scrollbar">
                            <div class="popup_modal_head">
                                ${get_translate_module_phrase('module_page_tickets', '_changingCat')}
                                <span class="popup_modal_close">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#x"></use>
                                    </svg>
                                </span>
                            </div>
                            <div class="settings-body">
                                <div class="tickets__settings-filters">
                                    <button class="filter type-category-edit ${result.type == 1 ? 'active' : ''}" data-type="1">${get_translate_module_phrase('module_page_tickets', '_default')}</button>
                                    <button class="filter type-category-edit ${result.type == 2 ? 'active' : ''}" data-type="2">${get_translate_module_phrase('module_page_tickets', '_strong')}</button>
                                </div>
                                <hr>
                                <div class="inputs-inline">
                                    <label for="catNameEdit">${get_translate_module_phrase('module_page_tickets', '_catName')}</label>
                                    <input type="text" id="catNameEdit" required name="editCategory" value="${result.title}">
                                </div>
                                <div id="type-render-edit"></div>
                                <div class="inputs-inline">
                                    <label for="catDescriptionEdit">${get_translate_module_phrase('module_page_tickets', '_addonDescription')}</label>
                                    <textarea name="" id="catDescriptionEdit">${result.description}</textarea>
                                </div>
                                <div class="flex-inline">
                                    <div class="inputs-inline">
                                        <label for="resposeTimeInputEdit">${get_translate_module_phrase('module_page_tickets', '_yourTimeResponce')}</label>
                                        <input type="number" id="resposeTimeInputEdit" value="${result.response}">
                                    </div>
                                    <div class="inputs-inline">
                                        <label for="replayTimeInputEdit">${get_translate_module_phrase('module_page_tickets', '_timeUntilNextTicket')}</label>
                                        <input type="number" id="replayTimeInputEdit" value="${result.replay}">
                                    </div>
                                </div>
                                <div class="inputs-inline">
                                    <input type="checkbox" class="switch" id="enableServersEdit" ${serverOn === '1' ? 'checked' : ''}>
                                    <label for="enableServersEdit">${get_translate_module_phrase('module_page_tickets', '_displayServers')}</label>
                                </div>
                                <div class="tickets__settings-show" id="server-none-edit" style="display: ${serverOn === '1' ? 'flex' : 'none'}">
                                    <span class="tickets__settings-warning">${get_translate_module_phrase('module_page_tickets', '_ticketWarn')}</span>
                                    <div class="adaptive-select-wrapper">
                                        <ul class="adaptive-select__dropdown-list" id="servers-edit">
                                            ${serversHtml}
                                        </ul>
                                        <div class="adaptive-select" open-select="servers-edit">
                                            <span class="adaptive-select__fist-icon">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#servers"></use>
                                                </svg>
                                            </span>
                                            <span class="adaptive-select__span_text">${get_translate_module_phrase('module_page_tickets', '_displayServers')}</span>
                                            <span class="margin-left-auto adaptive-select__arrow">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-inline">
                                    <div class="inputs-inline">
                                        <label for="playedTimeEdit">${get_translate_module_phrase('module_page_tickets', '_timeSpentCreate')}</label>
                                        <input type="text" id="playedTimeEdit" value="${result.playtime}">
                                    </div>
                                    <div class="inputs-inline">
                                        <label for="moneyCountEdit">${get_translate_module_phrase('module_page_tickets', '_summMoneyReview')}</label>
                                        <input type="text" id="moneyCountEdit" value="${result.amount}">
                                    </div>
                                </div>
                                <div class="inputs-inline">
                                    <label for="sortIdEdit">${get_translate_module_phrase('module_page_tickets', '_sortingID')}</label>
                                    <input type="number" id="sortIdEdit" required value="${result.sort}">
                                </div>
                                <button class="width-100" id="saveCategory">${get_translate_module_phrase('module_page_tickets', '_save')}</button>
                            </div>
                        </div>
                    `;
                    $('#editCategory').html(modalHtml);
                    renderTypeEdit(result.type, result.questions || '');
                    $('#editCategory').off('click', '.type-category-edit').on('click', '.type-category-edit', function () {
                        $('#editCategory').find('.type-category-edit').removeClass('active');
                        $(this).addClass('active');
                        const newType = $(this).data('type');
                        const existing = $('#type-render-edit .new-input-edit input[type="text"]').map(function () {
                            return $(this).val().trim();
                        }).get().filter(Boolean);
                        renderTypeEdit(newType, existing.length ? existing : []);
                    });
                    $('#editCategory').off('click', '#new-question-edit').on('click', '#new-question-edit', function () {
                        const $container = $('#type-render-edit');
                        const $hr = $container.find('hr').last();
                        const newInput = `
                        <div class="tickets__settings-flex-inline new-input-edit">
                            <input type="text" placeholder="${get_translate_module_phrase('module_page_tickets', '_enterQuestion')}">
                            <button class="icon_btn_transparent button-delete delete-question-edit">
                                <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
                            </button>
                        </div>
                    `;
                        if ($hr.length) $hr.before(newInput);
                        else $container.append(newInput);
                    });
                    $('#editCategory').off('click', '.delete-question-edit').on('click', '.delete-question-edit', function () {
                        const $container = $('#type-render-edit');
                        const $input = $(this).closest('.new-input-edit');
                        const idx = $container.find('.new-input-edit').index($input);
                        if (idx > 0) {
                            $input.remove();
                        }
                    });
                    $('#editCategory').off('click', '#saveCategory').on('click', '#saveCategory', function () {
                        let selectedServers = [];
                        $('#editCategory').find('input[name="servers-edit"]:checked').each(function () {
                            selectedServers.push($(this).val());
                        });
                        let questionsArr = $('#type-render-edit .new-input-edit input[type="text"]').map(function () {
                            return $(this).val().trim();
                        }).get().filter(Boolean);

                        const type = $('#editCategory').find('.type-category-edit.active').data('type') || 1;

                        sendRequest({
                            edit_category: true,
                            id: id,
                            title: $('#catNameEdit').val(),
                            description: $('#catDescriptionEdit').val() ?? '',
                            server_on: $('#enableServersEdit').is(':checked') ? 1 : 0,
                            servers: selectedServers.join(';') ?? '',
                            playtime: $('#playedTimeEdit').val() ?? '',
                            response_time: $('#resposeTimeInputEdit').val() ?? '',
                            replay_time: $('#replayTimeInputEdit').val() ?? '',
                            amount_money: $('#moneyCountEdit').val() ?? '',
                            type: type,
                            questions: questionsArr.join(';'),
                            sort: $('#sortIdEdit').val()
                        }).done(function (res) {
                            if (res.status == 'success' && res.url == 'reload') {
                                location.reload();
                            } else {
                                noty(res.text, res.status);
                            }
                        });
                    });
                    initAdaptiveSelects('#editCategory');
                    $('#enableServersEdit').off('change').on('change', function () {
                        if ($(this).is(':checked')) {
                            $('#server-none-edit').show();
                        } else {
                            $('#server-none-edit').hide();
                            $('input[name="servers-edit"]').prop('checked', false);
                            initAdaptiveSelects('#editCategory');
                        }
                    });
                }
            });
    });

    function renderTypeEdit(type, questions = []) {
        let html = '';
        if (typeof questions === 'string') {
            questions = questions.split(';').map(q => q.trim()).filter(Boolean);
        }
        if (type == 2) {
            html = `
            <button class="width-100" id="new-question-edit">
                <svg><use href="/resources/img/sprite.svg#plus"></use></svg>
                ${get_translate_module_phrase('module_page_tickets', '_new')}
            </button>
        `;
            if (questions.length) {
                questions.forEach((q, i) => {
                    html += `
                <div class="tickets__settings-flex-inline new-input-edit">
                    <input type="text" value="${q.replace(/"/g, '&quot;')}" placeholder="${get_translate_module_phrase('module_page_tickets', '_enterQuestion')}">
                    <button class="icon_btn_transparent button-delete delete-question-edit" ${i === 0 ? 'disabled' : ''}>
                        <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
                    </button>
                </div>
                `;
                });
            } else {
                html += `
            <div class="tickets__settings-flex-inline new-input-edit">
                <input type="text" placeholder="${get_translate_module_phrase('module_page_tickets', '_enterQuestion')}">
                <button class="icon_btn_transparent button-delete delete-question-edit" disabled>
                    <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
                </button>
            </div>
            `;
            }
            html += `<hr>`;
        }
        $('#type-render-edit').html(html);
    }
}
else if (currentPath === '/tickets/access/') {
    const $categoryBlock = $('#categorysTickedAccessInput');
    $('#categorysTickedAccess').change(function () {
        $categoryBlock.toggle($(this).is(':checked'));
    });
    $('#send-form').on('click', function () {
        let selectedCategory = [];
        $('input[name="categories-add"]:checked').each(function () {
            selectedCategory.push($(this).val());
        });
        sendRequest({ send_access: true, steam: $('#adminSteam').val(), add_delete: $('#deleteTickedAccess').is(':checked') ? 1 : 0, add_block: $('#banTickedAccess').is(':checked') ? 1 : 0, add_access: $('#adminsTickedAccess').is(':checked') ? 1 : 0, add_settings: $('#settingsTickedAccess').is(':checked') ? 1 : 0, general: $('#generalTickedAccess').is(':checked') ? 1 : 0, category: $('#categorysTickedAccess').is(':checked') ? selectedCategory.join(';') : '' })
            .done(function (result) {
                if (result.status == 'success' && result.url == 'reload') {
                    location.reload();
                } else {
                    noty(result.text, result.status);
                }
            });
    });

    $('.delete').on('click', function () {
        const steam = $(this).data('steamid');
        const $row = $(this).closest('tr');
        openDialog({
            title: get_translate_module_phrase('module_page_tickets', '_confirm'),
            message: get_translate_module_phrase('module_page_tickets', '_dialogwantDelAccess'),
            confirmText: get_translate_module_phrase('module_page_tickets', '_dialogDelete'),
            cancelText: get_translate_module_phrase('module_page_tickets', '_dialogLeave'),
            onConfirm: function () {
                sendRequest({ delete_access: true, steam: steam })
                    .done(function (result) {
                        if (result.status === 'success') {
                            $row.remove();
                            noty(result.text, result.status);
                        } else {
                            noty(result.text, result.status);
                        }
                    });
            }
        })
    });

    $('.edit').on('click', function () {
        const steam = $(this).data('steamid');
        sendRequest({ get_access_edit: true, steam: steam })
            .done(function (result) {
                if (result.status === 'success') {
                    const allCategories = result.category;
                    const selectedCategories = result.checked || '';
                    const selectedCategoryIds = selectedCategories.split(';');
                    let categoriesHtml = '';
                    allCategories.forEach(category => {
                        const isChecked = selectedCategoryIds.includes(category.id.toString());
                        categoriesHtml += `
                            <li>
                                <label class="adaptive-select__label" for="category-edit-${category.id}">
                                    <div class="adaptive-select__label-text">${category.title}</div>
                                    <input class="hide-input" id="category-edit-${category.id}" value="${category.id}" type="checkbox" name="category-edit" ${isChecked ? 'checked' : ''}>
                                </label>
                            </li>
                        `;
                    });
                    const modalHtml = `
                        <div class="popup_modal_content no-close no-scrollbar">
                            <div class="popup_modal_head">
                                ${get_translate_module_phrase('module_page_tickets', '_changingAccess')}
                                <span class="popup_modal_close">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#x"></use>
                                    </svg>
                                </span>
                            </div>
                            <div class="settings-body">
                                <div class="inputs-inline">
                                    <input type="checkbox" class="switch" id="reviewTickedAccessEdit" disabled checked>
                                    <label for="reviewTickedAccessEdit">${get_translate_module_phrase('module_page_tickets', '_accessReview')}</label>
                                </div>
                                <div class="inputs-inline">
                                    <input type="checkbox" class="switch" id="deleteTickedAccessEdit" ${result.delete == 1 ? 'checked' : ''}>
                                    <label for="deleteTickedAccessEdit">${get_translate_module_phrase('module_page_tickets', '_accessDelete')}</label>
                                </div>
                                <div class="inputs-inline">
                                    <input type="checkbox" class="switch" id="banTickedAccessEdit" ${result.block == 1 ? 'checked' : ''}>
                                    <label for="banTickedAccessEdit">${get_translate_module_phrase('module_page_tickets', '_accessBlock')}</label>
                                </div>
                                <div class="inputs-inline">
                                    <input type="checkbox" class="switch" id="adminsTickedAccessEdit" ${result.add_access == 1 ? 'checked' : ''}>
                                    <label for="adminsTickedAccessEdit">${get_translate_module_phrase('module_page_tickets', '_accessAdmins')}</label>
                                </div>
                                <div class="inputs-inline">
                                    <input type="checkbox" class="switch" id="settingsTickedAccessEdit" ${result.settings == 1 ? 'checked' : ''}>
                                    <label for="settingsTickedAccessEdit">${get_translate_module_phrase('module_page_tickets', '_accessSettings')}</label>
                                </div>
                                <div class="inputs-inline">
                                    <input type="checkbox" class="switch" id="generalTickedAccessEdit" ${result.general == 1 ? 'checked' : ''}>
                                    <label for="generalTickedAccessEdit">${get_translate_module_phrase('module_page_tickets', '_immunDelete')}</label>
                                </div>
                                <div class="adaptive-select-wrapper">
                                    <ul class="adaptive-select__dropdown-list" id="categories-edit">
                                        ${categoriesHtml}
                                    </ul>
                                    <div class="adaptive-select" open-select="categories-edit">
                                        <span class="adaptive-select__fist-icon">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#list"></use>
                                            </svg>
                                        </span>
                                        <span class="adaptive-select__span_text">${get_translate_module_phrase('module_page_tickets', '_selectCategories')}</span>
                                        <span class="margin-left-auto adaptive-select__arrow">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <hr>
                                <button class="width-100" id="send-form-edit">${get_translate_module_phrase('module_page_tickets', '_save')}</button>
                            </div>
                        </div>
                    `;
                    $('#editAccesses').html(modalHtml);
                    initAdaptiveSelects('#editAccesses');
                    $('#send-form-edit').on('click', function () {
                        let selectedCategories = [];
                        $('input[name="category-edit"]:checked').each(function () {
                            selectedCategories.push($(this).val());
                        });
                        sendRequest({ edit_access: true, steam: steam, delete: $('#deleteTickedAccessEdit').is(':checked') ? 1 : 0, block: $('#banTickedAccessEdit').is(':checked') ? 1 : 0, add_access: $('#adminsTickedAccessEdit').is(':checked') ? 1 : 0, settings: $('#settingsTickedAccessEdit').is(':checked') ? 1 : 0, general: $('#generalTickedAccessEdit').is(':checked') ? 1 : 0, category: selectedCategories.join(';') ?? '' })
                            .done(function (result) {
                                if (result.status == 'success' && result.url == 'reload') {
                                    location.reload();
                                } else {
                                    noty(result.text, result.status);
                                }
                            });
                    });
                }
            });
    });
}
else if (currentPath === '/tickets/blocks/') {
    $('#send-form').on('click', function () {
        sendRequest({ send_block: true, steam: $('#adminSteam').val(), reason: $('#reasonBlock').val(), time: $('#timeBlock').val() })
            .done(function (result) {
                if (result.status == 'success' && result.url == 'reload') {
                    location.reload();
                } else {
                    noty(result.text, result.status);
                }
            });
    });

    $('.delete').on('click', function () {
        const steam = $(this).data('steamid');
        const $row = $(this).closest('tr');
        openDialog({
            title: get_translate_module_phrase('module_page_tickets', '_confirm'),
            message: get_translate_module_phrase('module_page_tickets', '_dialogwantDelBlock'),
            confirmText: get_translate_module_phrase('module_page_tickets', '_dialogDelete'),
            cancelText: get_translate_module_phrase('module_page_tickets', '_dialogLeave'),
            onConfirm: function () {
                sendRequest({ delete_block: true, steam: steam })
                    .done(function (result) {
                        if (result.status === 'success') {
                            $row.remove();
                            noty(result.text, result.status);
                        } else {
                            noty(result.text, result.status);
                        }
                    });
            }
        })
    });
}
else if (currentPath.startsWith('/tickets/chat/')) {
    let lastMessageId;
    let chatBlocks = [];
    let isAdmin = false;
    let mySteamid = null;

    function loadMessages() {
        sendRequest({ load_messages: true })
            .done(function (result) {
                if (result.status === 'success') {
                    isAdmin = result.admin;
                    mySteamid = result.steamid;
                    renderMessages(result.messages);
                    if (result.messages.length > 0) {
                        lastMessageId = result.messages[result.messages.length - 1].message_id;
                    }
                }
            });
    }

    function addNewMessage(message, animate = true) {
        const chatContainer = $('.tickets__chat-messages');
        const lastBlock = chatBlocks.length > 0 ? chatBlocks[chatBlocks.length - 1] : null;
        const lastMessageInBlock = lastBlock ? lastBlock.children().last() : null;

        const lastMessageAuthor = lastMessageInBlock ? lastMessageInBlock.data('author') : null;
        const shouldAddToExistingBlock = lastBlock && lastMessageAuthor === message.steamid;

        let targetBlock = shouldAddToExistingBlock ? lastBlock : null;
        if (!targetBlock) {
            targetBlock = $('<div class="tickets__messages-block"></div>');
            chatContainer.append(targetBlock);
            chatBlocks.push(targetBlock);
        }
        const messageElement = createMessageElement(message);
        if (animate) {
            messageElement.hide();
            targetBlock.append(messageElement);
            messageElement.slideDown(300);
        } else {
            targetBlock.append(messageElement);
        }
        lastMessageId = message.message_id;
        chatContainer.stop().animate({
            scrollTop: chatContainer[0].scrollHeight
        }, 500);
    }

    function createMessageElement(message) {
        const messageClass = message.steamid === mySteamid ? 'sender' : '';
        const isAdminMsg = message.admin === 1;

        const messageElement = $(`
        <div class="tickets__messages-message ${messageClass}" data-id="${message.message_id}" data-author="${message.steamid}">
            <div class="tickets__messages-header"></div>
            <div class="tickets__message"></div>
        </div>
    `);
        const header = messageElement.find('.tickets__messages-header');
        if (isAdminMsg) {
            if (message.hide === 1) {
                if (isAdmin) {
                    header.append(`<a href="/profiles/${message.steamid}/?search=1" class="tickets__messages-nickname">${get_translate_module_phrase('module_page_tickets', '_supportResponse')} (${message.name})</a>•`);
                } else {
                    header.append(`<span class="tickets__messages-nickname">${get_translate_module_phrase('module_page_tickets', '_supportResponse')}</span>•`);
                }
            } else {
                header.append(`<a href="/profiles/${message.steamid}/?search=1" class="tickets__messages-nickname">${message.name}</a>•`);
            }
        } else {
            header.append(`<a href="/profiles/${message.steamid}/?search=1" class="tickets__messages-nickname">${message.name}</a>•`);
        }
        header.append(`<time class="tickets__messages-time" data-tippy-content="${message.date}" data-tippy-placement="top">${message.time}</time>`);
        if (isAdmin) {
            header.append(`<svg class="tickets__messages-delete-icon" id="delete-msg"><use href="/resources/img/sprite.svg#trash"></use></svg>`);
        }
        const messageContent = messageElement.find('.tickets__message');
        let safeText = escapeHtml(message.text);
        safeText = safeText.replace(/\n/g, '<br>');
        safeText = safeText.replace(
            /https?:\/\/[^\s]+/g,
            function (url) {
                return '<a href="' + url + '" target="_blank" rel="noopener noreferrer">' + url + '</a>';
            }
        );
        messageContent.html(safeText);
        if (message.img) {
            const imagesContainer = $('<div class="tickets__images"></div>');
            const imageNames = message.img.split(';');
            imageNames.forEach(imgName => {
                if (imgName.trim()) {
                    const safeImgName = escapeHtml(imgName.trim());
                    if (/^[a-zA-Z0-9_.-]+$/.test(imgName.trim())) {
                        const imgUrl = `/app/modules/module_page_tickets/assets/img/img_chat/${safeImgName}`;
                        imagesContainer.append(`
                    <div data-fancybox="gallery" data-src="${imgUrl}" class="tickets__images-image">
                        <img src="${imgUrl}" alt="">
                    </div>
                `);
                    }
                }
            });
            messageContent.append(imagesContainer);
        }

        initTippy();
        return messageElement;
    }

    function renderMessages(messages) {
        const chatContainer = $('.tickets__chat-messages');
        chatContainer.empty();
        chatBlocks = [];
        let currentAuthor = null;
        let currentBlock = null;
        messages.forEach(message => {
            if (currentBlock === null || currentAuthor !== message.steamid) {
                currentBlock = $('<div class="tickets__messages-block"></div>');
                chatContainer.append(currentBlock);
                chatBlocks.push(currentBlock);
                currentAuthor = message.steamid;
            }
            const messageElement = createMessageElement(message);
            currentBlock.append(messageElement);
        });
        chatContainer.scrollTop(chatContainer[0].scrollHeight);
    }

    function checkNewMessages() {
        sendRequest({ load_messages: true })
            .done(function (result) {
                if (result.status === 'success') {
                    const newList = result.messages;
                    const chatContainer = $('.tickets__chat-messages');
                    const existingIds = chatContainer.find('.tickets__messages-message').map(function () {
                        return $(this).data('id');
                    }).get();
                    const newIds = newList.map(m => m.message_id);
                    existingIds.forEach(id => {
                        if (!newIds.includes(id)) {
                            $(`.tickets__messages-message[data-id="${id}"]`)
                                .slideUp(300, function () { $(this).remove(); });
                        }
                    });
                    const newMessages = newList.filter(m => !existingIds.includes(m.message_id));
                    if (newMessages.length > 0) {
                        newMessages.forEach(message => addNewMessage(message, true));
                        lastMessageId = newList[newList.length - 1].message_id;
                    }
                }
            });
    }

    $('#send-message').on('click', function () {
        sendMessage();
    });

    function sendMessage() {
        sendRequest({ send_message: true, message: $('#message').val(), hide: $('#anonim').is(':checked') ? 1 : 0, file: $('#filepond').val() })
            .done(function (result) {
                if (result.status == 'success') {
                    $('#message').val('');
                    pond.removeFiles();
                    $('#filepond').val('');
                    checkNewMessages();
                    if (result.admin == 0) {
                        if (result.slow == 1) {
                            startSlowModeTimer(interval);
                        }
                    }
                } else {
                    if (result.url == 'reload') {
                        setTimeout(function () {
                            location.reload();
                        }, 3000);
                    }
                    if (result.url == 'back') {
                        setTimeout(function () {
                            window.location.href = '/tickets/send/';
                        }, 3000);
                    }
                    noty(result.text, result.status);
                }
            });
    }

    $('#message').on('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    $('.answer').on('click', function () {
        $('#message').val($(this).data('text'));
        $('.answer').removeClass('active');
        $(this).addClass('active');
    });

    function startSlowModeTimer(seconds) {
        const $btn = $('#send-message');
        const $timer = $('#slow-time');
        const $svg = $('#slow-svg');
        let endTime = Date.now() + seconds * 1000;
        localStorage.setItem('slowModeTime', endTime);
        $btn.prop('disabled', true);
        $timer.show();
        $svg.show();
        updateTimerDisplay();
        const interval = setInterval(() => {
            let remaining = Math.max(0, Math.floor((endTime - Date.now()) / 1000));
            updateTimerDisplay();
            if (remaining <= 0) {
                clearInterval(interval);
                localStorage.removeItem('slowModeTime');
                $timer.hide();
                $svg.hide();
                $btn.prop('disabled', false);
            }
        }, 1000);

        function updateTimerDisplay() {
            let remaining = Math.max(0, Math.floor((endTime - Date.now()) / 1000));
            const minutes = Math.floor(remaining / 60);
            const secs = remaining % 60;
            $timer.text(`${minutes}:${secs < 10 ? '0' : ''}${secs}`);
        }
    }

    let storedEndTime = localStorage.getItem('slowModeTime');
    if (storedEndTime) {
        let remaining = Math.max(0, Math.floor((storedEndTime - Date.now()) / 1000));
        if (remaining > 0) {
            startSlowModeTimer(remaining);
        } else {
            localStorage.removeItem('slowModeTime');
        }
    } else {
        $('#slow-time').hide();
        $('#slow-svg').hide();
    }

    $('#slow-mode').on('click', function () {
        sendRequest({ slow_mode: true });
        $(this).toggleClass('activated');
    });

    $('#close-ticket').on('click', function () {
        openDialog({
            title: get_translate_module_phrase('module_page_tickets', '_confirm'),
            message: get_translate_module_phrase('module_page_tickets', '_wantCloseTicket'),
            confirmText: get_translate_module_phrase('module_page_tickets', '_dialogClose'),
            cancelText: get_translate_module_phrase('module_page_tickets', '_dialogChangeMind'),
            onConfirm: function () {
                sendRequest({ close_ticket: true })
                    .done(function (result) {
                        if (result.status == 'success' && result.url == 'reload') {
                            location.reload();
                        } else {
                            noty(result.text, result.status);
                        }
                    });
            }
        })
    });

    $(document).on('click', '#delete-msg', function () {
        const $message = $(this).closest('.tickets__messages-message');
        const messageId = $message.data('id');
        openDialog({
            title: get_translate_module_phrase('module_page_tickets', '_confirm'),
            message: get_translate_module_phrase('module_page_tickets', '_deleteMsgDialog'),
            confirmText: get_translate_module_phrase('module_page_tickets', '_dialogDelete'),
            cancelText: get_translate_module_phrase('module_page_tickets', '_dialogChangeMind'),
            onConfirm: function () {
                sendRequest({ delete_message: true, message_id: messageId })
                    .done(function (result) {
                        if (result.status === 'success') {
                            $message.slideUp(300, function () { $(this).remove(); });
                        }
                    });
            }
        });
    });


    loadMessages();
    Fancybox.bind('[data-fancybox="gallery"]');
    setInterval(checkNewMessages, 5000);
    initFilepond();
}