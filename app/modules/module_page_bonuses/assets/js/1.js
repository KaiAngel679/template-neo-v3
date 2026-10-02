function sendRequest(body) {
    return $.ajax({
        url: location.href,
        type: 'POST',
        data: body,
        dataType: 'json'
    });
}

const params_tg = new URLSearchParams(window.location.hash.substring(1));
if (params_tg.has('tgAuthResult')) {
    sendRequest({ hash_tg: true, hash: params_tg.get('tgAuthResult') })
        .done(function (result) {
            noty(result.message, result.status);
            if (result.status === 'success') {
                $('#subTg').removeClass('visible');
                const $btn = $('[data-openmodal="subTg"]');
                if ($btn.length) {
                    $btn
                        .prop('disabled', true)
                        .removeAttr('data-openmodal')
                        .html(`
                            <svg>
                                <use href="/resources/img/sprite.svg#tg"></use>
                            </svg>
                            ${get_translate_module_phrase('module_page_bonuses', '_done')}
                        `);
                    $btn.closest('.bonuses__container-buttons')
                        .find('.bonuses__container-price')
                        .remove();
                }
            }
            history.replaceState(null, '', window.location.pathname + window.location.search);
        });
}

const params_ds = new URLSearchParams(window.location.search);
if (params_ds.has('code')) {
    sendRequest({ auth_ds: true, code: params_ds.get('code') })
        .done(function (result) {
            noty(result.message, result.status);
            if (result.status === 'success') {
                $('#subDs').removeClass('visible');
                const $btn = $('[data-openmodal="subDs"]');
                if ($btn.length) {
                    $btn
                        .prop('disabled', true)
                        .removeAttr('data-openmodal')
                        .html(`
                            <svg>
                                <use href="/resources/img/sprite.svg#ds"></use>
                            </svg>
                            ${get_translate_module_phrase('module_page_bonuses', '_done')}
                        `);
                    $btn.closest('.bonuses__container-buttons')
                        .find('.bonuses__container-price')
                        .remove();
                }
            }
            history.replaceState(null, '', window.location.pathname);
        });
}

$('#checkSubVk').click(function () {
    sendRequest({ auth_vk: true })
        .done(function (result) {
            noty(result.message, result.status);
            if (result.status === 'success') {
                $('#subVk').removeClass('visible');
                const $btn = $('[data-openmodal="subVk"]');
                if ($btn.length) {
                    $btn
                        .prop('disabled', true)
                        .removeAttr('data-openmodal')
                        .html(`
                            <svg>
                                <use href="/resources/img/sprite.svg#vk"></use>
                            </svg>
                            ${get_translate_module_phrase('module_page_bonuses', '_done')}
                        `);
                    $btn.closest('.bonuses__container-buttons')
                        .find('.bonuses__container-price')
                        .remove();
                }
            }
        });
});

$('#save-settings-tg').click(function () {
    sendRequest({
        save_settings_tg: true,
        enabled_tg: $('#enableTg').is(':checked') ? 1 : 0,
        text_tg: $('#descriptionTg').val(),
        money_tg: $('#tgMoney').val(),
        url_tg: $('#tgLink').val(),
        bot_key_tg: $('#tgKey').val(),
        bot_id_tg: $('#tgId').val()
    }).done(function (result) {
        noty(result.message, result.status);
    });
});

$('#save-settings-ds').click(function () {
    sendRequest({
        save_settings_ds: true,
        enabled_ds: $('#enableDs').is(':checked') ? 1 : 0,
        text_ds: $('#descriptionDs').val(),
        money_ds: $('#DsMoney').val(),
        url_ds: $('#DsLink').val(),
        guild_id_ds: $('#DsGuild').val(),
        client_id_ds: $('#DsPubKey').val(),
        secret_id_ds: $('#DsSecKey').val()
    }).done(function (result) {
        noty(result.message, result.status);
    });
});

$('#save-settings-vk').click(function () {
    sendRequest({
        save_settings_vk: true,
        enabled_vk: $('#enableVk').is(':checked') ? 1 : 0,
        text_vk: $('#descriptionVk').val(),
        money_vk: $('#VkMoney').val(),
        url_vk: $('#VkLink').val()
    }).done(function (result) {
        noty(result.message, result.status);
    });
});