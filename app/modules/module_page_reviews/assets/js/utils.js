function rvPhrase(key, fallback) {
    if (typeof get_translate_module_phrase === 'function') {
        var value = get_translate_module_phrase('module_page_reviews', key);
        if (value && value !== key) {
            return value;
        }
    }
    return fallback || key;
}

function sendRequest(body) {
    return $.ajax({
        url: location.pathname + location.search,
        type: 'POST',
        data: body,
        dataType: 'json'
    });
}

var RV_TEXT_MIN = 10;
var RV_TEXT_MAX = 2000;

function rvTextLen(value) {
    return Array.from(String(value || '').trim()).length;
}

function rvValidateRequiredText(value, label) {
    var len = rvTextLen(value);
    label = label || rvPhrase('_rv_textLabel', 'Текст');
    if (len < RV_TEXT_MIN) {
        return rvPhrase('_rv_textMin', '%label%: минимум %min% символов')
            .replace('%label%', label)
            .replace('%min%', String(RV_TEXT_MIN));
    }
    if (len > RV_TEXT_MAX) {
        return rvPhrase('_rv_textMax', '%label%: максимум %max% символов')
            .replace('%label%', label)
            .replace('%max%', String(RV_TEXT_MAX));
    }
    return null;
}

function rvValidateOptionalText(value, label) {
    var len = rvTextLen(value);
    if (len === 0) {
        return null;
    }
    return rvValidateRequiredText(value, label || rvPhrase('_rv_textLabel', 'Текст'));
}

function rvValidateReviewTexts(pros, cons, comment, mode) {
    mode = String(mode || ($('#rvReviewsList').attr('data-fields-mode') || 'all'));
    pros = String(pros || '');
    cons = String(cons || '');
    comment = String(comment || '');

    if (mode === 'comment') {
        return rvValidateRequiredText(comment, rvPhrase('_rv_comment', 'Комментарий'));
    }

    if (mode === 'pros_cons') {
        var pcFields = [
            [pros, rvPhrase('_rv_pros', 'Достоинства')],
            [cons, rvPhrase('_rv_cons', 'Недостатки')]
        ];
        for (var i = 0; i < pcFields.length; i++) {
            var pcErr = rvValidateOptionalText(pcFields[i][0], pcFields[i][1]);
            if (pcErr) {
                return pcErr;
            }
        }
        if (!pros.trim() && !cons.trim()) {
            return rvPhrase('_rv_writeAtLeastOne', 'Напишите хотя бы одно поле от %min% до %max% символов')
                .replace('%min%', String(RV_TEXT_MIN))
                .replace('%max%', String(RV_TEXT_MAX));
        }
        return null;
    }

    var fields = [
        [pros, rvPhrase('_rv_pros', 'Достоинства')],
        [cons, rvPhrase('_rv_cons', 'Недостатки')],
        [comment, rvPhrase('_rv_comment', 'Комментарий')]
    ];
    for (var j = 0; j < fields.length; j++) {
        var err = rvValidateOptionalText(fields[j][0], fields[j][1]);
        if (err) {
            return err;
        }
    }
    if (!pros.trim() && !cons.trim() && !comment.trim()) {
        return rvPhrase('_rv_writeAtLeastOne', 'Напишите хотя бы одно поле от %min% до %max% символов')
            .replace('%min%', String(RV_TEXT_MIN))
            .replace('%max%', String(RV_TEXT_MAX));
    }
    return null;
}

function sendRequestWithButton($button, body) {
    var $btn = $button instanceof jQuery ? $button : $($button);

    if (!$btn.length || $btn.prop('disabled')) {
        return $.Deferred().reject().promise();
    }

    $btn.prop('disabled', true);

    return sendRequest(body).always(function () {
        $btn.prop('disabled', false);
    });
}

function rvToast(type, message) {
    if (typeof noty === 'function') {
        noty(message, type === 'error' ? 'error' : 'success');
        return;
    }
    if (window.iziToast) {
        window.iziToast[type === 'error' ? 'error' : 'success']({
            title: type === 'error'
                ? rvPhrase('_rv_toastError', 'Ошибка')
                : rvPhrase('_rv_toastSuccess', 'Готово'),
            message: message,
            position: 'topRight'
        });
        return;
    }
    console.log(type, message);
}

function rvEscapeHtml(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function rvFormatDate(ts) {
    var d = new Date((Number(ts) || 0) * 1000);
    if (isNaN(d.getTime())) return '';
    var pad = function (n) { return n < 10 ? '0' + n : String(n); };
    return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear();
}

function rvFormatDateTime(ts) {
    var d = new Date((Number(ts) || 0) * 1000);
    if (isNaN(d.getTime())) return '';
    var pad = function (n) { return n < 10 ? '0' + n : String(n); };
    return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

function rvFormatHours(hours) {
    return String(Number(hours) || 0).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function rvRatingIconId() {
    var icon = String($('#rvReviewsList').attr('data-rating-icon') || 'star-fill');
    icon = icon.replace(/[^a-zA-Z0-9_-]/g, '');
    return icon || 'star-fill';
}

function rvRatingIconSvg() {
    return '<svg><use href="/resources/img/sprite.svg#' + rvEscapeHtml(rvRatingIconId()) + '"></use></svg>';
}

function rvStarsHtml(rating, sizeClass) {
    var cls = 'rv__stars' + (sizeClass ? ' ' + sizeClass : '');
    var icons = '';
    var i;
    var one = rvRatingIconSvg();
    for (i = 0; i < 5; i++) {
        icons += one;
    }
    return '<div class="' + cls + '" style="--rv-rating: ' + Number(rating || 0) + '">' +
        '<span class="rv__stars-fill">' + icons + '</span>' +
        '<span class="rv__stars-base" aria-hidden="true">' + icons + '</span>' +
        '</div>';
}

function rvScoreClass(score) {
    if (score > 0) return 'is-positive';
    if (score < 0) return 'is-negative';
    return '';
}

function rvFormatScore(score) {
    var n = Number(score) || 0;
    return (n > 0 ? '+' : '') + String(n);
}

function checkAndRenderAvatar(check, steamid) {
    if (check == 1 && steamid) {
        avatar.push(steamid);
    }
}

function renderReviewsSkeletonList(count) {
    count = count || 10;
    var html = '';
    var i;
    for (i = 0; i < count; i++) {
        html += '<article class="rv__review card skeleton--default"></article>';
    }
    return html;
}

function finishReviewsRender(items) {
    (items || []).forEach(function (item) {
        if (!item) {
            return;
        }
        checkAndRenderAvatar(item.checked_avatar, item.steamid);
        (item.replies || []).forEach(function (reply) {
            checkAndRenderAvatar(reply.checked_avatar, reply.steamid);
        });
    });
    if (typeof RenderingAvatar === 'function') {
        RenderingAvatar();
    }
}

function closePopupModal(id) {
    var $modal = $('#' + id);
    if (!$modal.length) return;
    $modal.removeClass('visible');
    $('body, html').removeClass('modal__opened');
}

function openPopupModal(id) {
    var $modal = $('#' + id);
    if (!$modal.length) return;
    $modal.addClass('visible');
    $('body, html').addClass('modal__opened');
}

function bindRvOverall(modalId, valueId, starsId) {
    var $overall = $('#' + valueId);
    var $stars = $('#' + starsId);
    var $rates = $('#' + modalId + ' .rv__modal-attrs .rv__rate');

    function update() {
        if (!$overall.length) return;
        var sum = 0;
        var count = 0;
        $rates.each(function () {
            var $checked = $(this).find('input:checked');
            if (!$checked.length) return;
            sum += Number($checked.val());
            count += 1;
        });
        var ready = count === $rates.length;
        var avg = ready ? sum / count : 0;
        $overall.text(ready ? '(' + avg.toFixed(1).replace('.', ',') + ')' : '(-)');
        if ($stars.length) {
            $stars.css('--rv-rating', String(avg));
        }
    }

    $(document).on('change', '#' + modalId + ' .rv__modal-attrs input[type="radio"]', update);
    update();
}
