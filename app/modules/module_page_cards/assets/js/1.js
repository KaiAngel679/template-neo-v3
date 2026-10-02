function sendRequest(body) {
    return $.ajax({
        url: location.href,
        type: 'POST',
        data: body,
        dataType: 'json'
    });
}

let prizeCounter = 1;

$('#addPrizeBtn').on('click', function () {
    const $original = $('#cards-0').closest('.cards_add_wrapper');
    const $clone = $original.clone();

    $clone.attr('id', 'cards-' + prizeCounter);
    $clone.find('.cards__delete-block').removeAttr('style');

    $clone.find('input[type="text"], input[type="number"]').val('');
    $clone.find('input[type="radio"]').prop('checked', false);

    $clone.find('[id], [for], [name]').each(function () {
        const $el = $(this);
        if ($el.attr('id') && $el.attr('id') !== 'removePrizeBtn' && $el.attr('id') !== 'numberControl') {
            if ($el.attr('id') == 'priseType') {
                $el.attr('id', 'priseType-' + prizeCounter);
            } else {
                $el.attr('id', $el.attr('id').replace(/(-\d+)?$/, '-' + prizeCounter));
            }
        }
        if ($el.attr('for')) {
            $el.attr('for', $el.attr('for').replace(/(-\d+)?$/, '-' + prizeCounter));
        }
        if ($el.attr('name')) {
            if ($el.attr('name') === 'typePrize') {
                $el.attr('name', 'typePrize-' + prizeCounter);
            } else {
                $el.attr('name', $el.attr('name').replace(/(-\d+)?$/, '-' + prizeCounter));
            }
        }
    });

    $clone.find('.adaptive-select').attr('open-select', 'priseType-' + prizeCounter);

    $clone.insertBefore('.card-add-prize .card-container > hr');
    prizeCounter++;
});

$(document).on('click', '#removePrizeBtn', function () {
    $(this).closest('.cards_add_wrapper').remove();
});

$('#save-general-settings').on('click', function () {
    sendRequest({
        save_general_settings: true,
        price: $('#cardPrice').val(),
        currency: $('#shopAmount').val(),
        ids: $('input[name="cards-servers"]:checked').map(function () { return parseInt(this.id); }).get(),
        svg: $('#cardImage').val()
    }).done(function (result) {
        noty(result.message, result.status);
    });
});

$('#save-prizes-settings').on('click', function () {
    const prizes = [];

    $('.card-add-prize .card-container').find('.cards_add_wrapper').each(function () {
        const $wrapper = $(this);
        const prizeId = $wrapper.attr('id');

        const rarity = $wrapper.find('.cards__filter_wrapper .filter.active').attr('id');
        const type = $wrapper.find(`input[name="typePrize-${prizeId}"]:checked`).val() || $wrapper.find(`input[name="typePrize"]:checked`).val();
        const count = $wrapper.find(`#cardMoney-${prizeId}, #cardMoney`).val();
        const chance = $wrapper.find(`#cardChance-${prizeId}, #cardChance`).val();

        console.log(`Prize ${prizeId}:`, { rarity, type, count, chance });

        prizes.push({
            id: prizeId,
            rare: parseInt(rarity) || 0,
            type: type,
            count: parseInt(count) || 0,
            chance: parseInt(chance) || 0
        });
    });

    sendRequest({ save_rewards: true, prizes: prizes })
        .done(function (result) {
            if (result.status == 'success') {
                location.reload();
            } else {
                noty(result.message, result.status);
            }
        });
});

$(document).on('click', '.cards__filter_wrapper .filter', function () {
    $(this).closest('.cards__filter_wrapper').find('.filter').removeClass('active');
    $(this).addClass('active');
});

$('.button-delete').on('click', function () {
    const $btn = $(this);
    const prizeId = $btn.data('id');
    openDialog({
        title: get_translate_module_phrase('module_page_cards', '_confirmingAction'),
        message: get_translate_module_phrase('module_page_cards', '_areYouSureDeletePrize'),
        confirmText: get_translate_module_phrase('module_page_cards', '_delete'),
        cancelText: get_translate_module_phrase('module_page_cards', '_cancel'),
        onConfirm: function () {
            sendRequest({ delete_reward: true, id: prizeId })
                .done(function (result) {
                    if (result.status == 'success') {
                        $btn.closest('tr').fadeOut(300, function () {
                            $(this).remove();
                        });
                    }
                    noty(result.message, result.status);
                });
        }
    })
});

$('.button-edit').on('click', function () {
    const id = $(this).data('id');
    sendRequest({ get_reward: true, id: id })
        .done(function (result) {
            if (result.status == 'success') {
                const modalHtml = `
                    <div class="popup_modal_content no-close no-scrollbar">
                        <div class="popup_modal_head">
                            ${get_translate_module_phrase('module_page_cards', '_changingPrise')}
                            <span class="popup_modal_close">
                                <svg>
                                    <use href="/resources/img/sprite.svg#x"></use>
                                </svg>
                            </span>
                        </div>
                        <div class="modal__prises">
                            <div class="cards_add_wrapper">
                                <outline class="cards__outline">
                                    ${get_translate_module_phrase('module_page_cards', '_typeCard')}
                                    <div class="cards__filter_wrapper">
                                        <button class="filter ${result.data.rare == 0 ? 'active' : ''}" id="0">
                                            ${get_translate_module_phrase('module_page_cards', '_commonCard')}
                                        </button>
                                        <button class="filter ${result.data.rare == 1 ? 'active' : ''}" id="1">
                                            ${get_translate_module_phrase('module_page_cards', '_rareCard')}
                                        </button>
                                        <button class="filter ${result.data.rare == 2 ? 'active' : ''}" id="2">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#diamond"></use>
                                            </svg>
                                            ${get_translate_module_phrase('module_page_cards', '_legendaryCard')}
                                        </button>
                                    </div>
                                </outline>
                                <div class="flex-inline" style="margin-top: .5rem">
                                    <div class="adaptive-select-wrapper">
                                        <ul class="adaptive-select__dropdown-list" id="priseType-edit">
                                            <li>
                                                <label class="adaptive-select__label" for="money">
                                                    <div class="adaptive-select__label-text">${get_translate_module_phrase('module_page_cards', '_moneyOnBalance')}</div>
                                                    <input class="hide-input" id="money" type="radio" name="typePrize-edit" value="money" ${result.data.type == 'money' ? 'checked' : ''}>
                                                </label>
                                            </li>
                                            <li>
                                                <label class="adaptive-select__label" for="credits">
                                                    <div class="adaptive-select__label-text">${get_translate_module_phrase('module_page_cards', '_creditsInShop')}</div>
                                                    <input class="hide-input" id="credits" type="radio" name="typePrize-edit" value="credits" ${result.data.type == 'credits' ? 'checked' : ''}>
                                                </label>
                                            </li>
                                        </ul>
                                        <div class="adaptive-select" open-select="priseType-edit">
                                            <span class="adaptive-select__fist-icon">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#gift"></use>
                                                </svg>
                                            </span>
                                            <span class="adaptive-select__span_text">${get_translate_module_phrase('module_page_cards', '_choosePrise')}</span>
                                            <span class="margin-left-auto adaptive-select__arrow">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="inputs-inline" style="margin-bottom: 0">
                                        <div class="number" id="numberControl">
                                            <button class="number-minus" type="button">-</button>
                                            <input id="cardMoney-edit" type="number" min="0" placeholder="Кол-во денег" value="${result.data.count}">
                                            <button class="number-plus" type="button">+</button>
                                        </div>
                                    </div>
                                    <div class="inputs-inline" style="margin-bottom: 0">
                                        <div class="number" id="numberControl">
                                            <button class="number-minus" type="button">-</button>
                                            <input id="cardChance-edit" type="number" min="0" placeholder="Шанс (1-100%)" value="${result.data.chance}">
                                            <button class="number-plus" type="button">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button id="saveChanges" class="width-100">${get_translate_module_phrase('module_page_cards', '_saveChanges')}</button>
                        </div>
                    </div>
                `;
                $('#editPrise').html(modalHtml);
                initAdaptiveSelects('#editPrise');
                $('#saveChanges').on('click', function () {
                    sendRequest({ update_reward: true, id: id, rare: $('.modal__prises .cards__filter_wrapper .filter.active').attr('id'), type: $('input[name="typePrize-edit"]:checked').val(), count: $('#cardMoney-edit').val(), chance: $('#cardChance-edit').val() })
                        .done(function (result) {
                            if (result.status == 'success') {
                                location.reload();
                            } else {
                                noty(result.message, result.status);
                            }
                        });
                });
            }
        });
});