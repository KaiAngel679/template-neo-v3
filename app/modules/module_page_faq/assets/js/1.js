function sendRequest(body) {
    return $.ajax({
        url: location.href,
        type: 'POST',
        data: body,
        dataType: 'json'
    });
}

$('#created').click(function () {
    sendRequest({ created: true, question: $('#question').val(), answer: $('#answer').val() })
        .done(function (result) {
            if (result.status == 'success' && result.url == 'reload') {
                location.reload();
            } else {
                noty(result.text, result.status);
            }
        });
});

$('.delete').click(function () {
    const button = $(this);
    const id = button.data('id');
    openDialog({
        title: get_translate_module_phrase('module_page_faq', '_dialogConfirm'),
        message: get_translate_module_phrase('module_page_faq', '_dialogDelete'),
        confirmText: get_translate_phrase('_Delete_Action'),
        cancelText: get_translate_module_phrase('module_page_faq', '_dialogNo'),
        onConfirm: function () {
            sendRequest({ delete: true, id: id })
                .done(function (result) {
                    if (result.status == 'success') {
                        button.closest('.accordion__details').remove();
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
    sendRequest({ modal: true, id: id })
        .done(function (result) {
            if (result.status === 'success') {
                const modalHtml = `
                        <div class="popup_modal_content no-close no-scrollbar">
                            <div class="popup_modal_head">
                                ${get_translate_module_phrase('module_page_faq', '_editingFaq')}
                                <span class="popup_modal_close">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#x"></use>
                                    </svg>
                                </span>
                            </div>
                            <div class="inputs-inline">
                                <label for="questionEdit"></label>
                                <input type="text" id="questionEdit" value="${result.data.title}">
                            </div>
                            <div class="inputs-inline">
                                <label for="answerEdit"></label>
                                <textarea id="answerEdit">${result.data.text}</textarea>
                            </div>
                            <button class="width-100" id="edit">${get_translate_module_phrase('module_page_faq', '_editFaq')}</button>
                        </div>
                    `;
                $('#editFaq').html(modalHtml);
                $('#edit').on('click', function () {
                    sendRequest({ edit: true, id: id, title: $('#questionEdit').val(), text: $('#answerEdit').val() })
                        .done(function (result) {
                            if (result.status == 'success' && result.url == 'reload') {
                                $('#editFaq').html('').removeClass('visible');
                                location.reload();
                            } else {
                                noty(result.text, result.status);
                            }
                        });
                });
            }
        });
});