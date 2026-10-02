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
    if (confirm('Вы уверены, что хотите удалить этот вопрос?')) {
        sendRequest({ delete: true, id: id })
            .done(function (result) {
                if (result.status === 'success') {
                    button.closest('.accordion__details').remove();
                }
                noty(result.text, result.status);
            });
    }
});

$('.edit').click(function () {
    const id = $(this).data('id');
    sendRequest({ modal: true, id: id })
        .done(function (result) {
            if (result.status === 'success') {
                const modalHtml = `
                        <div class="popup_modal_content no-close no-scrollbar">
                            <div class="popup_modal_head">
                                Редактирование вопрос-ответа
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
                            <button class="width-100" id="edit">Изменить FAQ</button>
                        </div>
                    `;
                $('#editFaq').html(modalHtml).addClass('visible');
                $('.popup_modal_close').on('click', function () {
                    $('#editFaq').html('').removeClass('visible');
                });
                $(document).on('click', function (e) {
                    if (!$(e.target).closest('#editFaq .popup_modal_content').length) {
                        $('#editFaq').html('');
                    }
                });
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