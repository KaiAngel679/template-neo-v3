let cardOpened = false;

const CARDS_NOTIFY_KEY = 'cardsNotifyClosed';

function initCardsNotify() {
    const closedDate = localStorage.getItem(CARDS_NOTIFY_KEY);
    const today = new Date().toISOString().split('T')[0];
    
    if (closedDate == today) {
        $('.cards__notify').remove();
    }
}

function sendRequestCards(body) {
    return $.ajax({
        url: location.href,
        type: 'POST',
        data: body,
        dataType: 'json'
    });
}

function initCards() {
    cardOpened = false;
    $('.card__rotate').removeClass('disabled rotated');
    $('.card__item').removeClass('choosen');
    $('.card__content').css('opacity', '1');
}

function updateProgress(streak) {
    let html = '';

    for (let i = 1; i <= 7; i++) {
        let classes = '';
        let content = i;
        if (i <= streak) {
            if (i <= 4) classes = ' taken-0';
            else if (i <= 6) classes = ' taken-1';
            else if (i == 7) classes = ' taken-2';
            content = `<svg><use href="/resources/img/sprite.svg#check"></use></svg>`;
        }
        html += `<div class="cards__progress-item${classes}">${content}</div>`;
    }

    return html;
}

function startDailyTimer() {
    function updateTimer() {
        const now = new Date();
        const midnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0);
        const diff = midnight - now;
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        $('#cardsTimerHours').text(hours.toString().padStart(2, '0'));
        $('#cardsTimerMinutes').text(minutes.toString().padStart(2, '0'));
        $('#cardsTimerSeconds').text(seconds.toString().padStart(2, '0'));
    }
    updateTimer();
    setInterval(updateTimer, 1000);
}

function renderCard(svg) {
    return `<div class="card__rotate">
                <div class="card__item">
                    <div class="card__pattern"></div>
                    <div class="card__content">
                        <div class="card__icon">
                            ${svg}
                        </div>
                    </div>
                </div>
            </div>`;
}

function renderAction(price) {
    if (parseInt(price) != 0 || price == null) {
        return `<div class="cards__action">
                <span>${get_translate_module_phrase('module_page_cards', '_nextOpen')}</span>
                <div class="cards__action-buttons">
                    <button class="button-pay" id="buyOpenCards">${get_translate_module_phrase('module_page_cards', '_openBtn')} ${price}</button>
                </div>
            </div>`;
    } else {
        return '';
    }
}

function playRenderSound() {
    const sound = new Audio('/app/modules/module_page_cards/assets/sound/card.mp3');
    sound.volume = 0.5;
    sound.play();
}

function renderCards() {
    sendRequestCards({ check_open: true })
        .done(function (result) {
            let html;
            if (result.status == 'success') {
                html = `<svg class="absolite-cinema popup_modal_close"><use href="/resources/img/sprite.svg#x"></use></svg>
                    <div class="cards">
                    <div class="card__wrapper">
                        <div class="card__wrapper-header">
                            <h2 class="cards__title">${get_translate_module_phrase('module_page_cards', '_cardProgress')}</h2>
                            <div class="cards__progress">
                                ${updateProgress(result.data.streak)}
                            </div>
                        </div>
                        <div class="card__wrapper-list">
                            ${renderCard(result.data.svg)}
                            ${renderCard(result.data.svg)}
                            ${renderCard(result.data.svg)}
                        </div>
                        <div class="card__wrapper-action"></div>
                    </div>
                </div>`;
            } else {
                html = `<svg class="absolite-cinema popup_modal_close"><use href="/resources/img/sprite.svg#x"></use></svg>
                    <div class="cards">
                    <div class="card__wrapper">
                        <div class="cards__timer">
                            <p>${get_translate_module_phrase('module_page_cards', '_nextOpenTimer')}</p>
                            <div class="cards__timer-wrapper" id="cardsTimer">
                                <div class="cards__timer-block">
                                    <span class="cards__timer-number" id="cardsTimerHours">00</span>
                                    <span class="cards__timer-text">${get_translate_module_phrase('module_page_cards', '_hour')}</span>
                                </div>
                                <span class="cards__timer-separate">:</span>
                                <div class="cards__timer-block">
                                    <span class="cards__timer-number" id="cardsTimerMinutes">00</span>
                                    <span class="cards__timer-text">${get_translate_module_phrase('module_page_cards', '_minutes')}</span>
                                </div>
                                <span class="cards__timer-separate">:</span>
                                <div class="cards__timer-block">
                                    <span class="cards__timer-number" id="cardsTimerSeconds">00</span>
                                    <span class="cards__timer-text">${get_translate_module_phrase('module_page_cards', '_seconds')}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card__wrapper-header">
                            <h2 class="cards__title">${get_translate_module_phrase('module_page_cards', '_cardProgress')}</h2>
                            <div class="cards__progress">
                                ${updateProgress(result.data.streak)}
                            </div>
                        </div>
                        <div class="card__wrapper-list cards__blur">
                            ${renderCard(result.data.svg)}
                            ${renderCard(result.data.svg)}
                            ${renderCard(result.data.svg)}
                        </div>
                        <div class="card__wrapper-action">
                            ${renderAction(result.data.price)}
                        </div>
                    </div>
                </div>`;
                startDailyTimer();
            }
            $('#openCards').html(html);
            playRenderSound();
            initCards();
        });
}

$(document).on('click', '.card__rotate', function () {
    const $card = $(this);
    if (cardOpened || $card.hasClass('rotated')) return;
    sendRequestCards({ open_card: true })
        .done(function (result) {
            if (result.status == 'success') {
                cardOpened = true;
                const $cardItem = $card.find('.card__item');
                const $content = $card.find('.card__content');
                requestAnimationFrame(() => {
                    $card.addClass('rotated');
                    $cardItem.addClass('choosen');
                    $('.card__rotate').not($card).addClass('disabled');
                    $content.css({
                        transition: '0.3s',
                        opacity: '0'
                    });
                    setTimeout(() => {
                        $content.html(`
                        <div class="cards__mirror-content">
                            <span class="cards__mirror-prise">${result.data.reward}</span>
                            <span class="cards__mirror-description">${result.data.description}</span>
                        </div>
                    `).css('opacity', '1');
                        $content.one('transitionend', function () {
                            const off = $card.offset();
                            const cx = (off.left + $card.outerWidth() / 2) - window.pageXOffset;
                            const cy = (off.top + $card.outerHeight() / 2) - window.pageYOffset;
                            confetti({
                                position: { x: cx, y: cy },
                                count: 200,
                                size: 1,
                                velocity: 200,
                                fade: false
                            });
                            $('.cards__progress').html(updateProgress(result.data.streak));
                            $('.card__wrapper-action').html(`
                                ${renderAction(result.data.price)}
                            `);
                        });
                    }, 600);
                });
                $('.cards__notify').remove();
            } else {
                cardOpened = false;
                noty(result.message, result.status);
            }
        })
});

$('[data-openmodal="openCards"]').on('click', function () {
    renderCards();
});

initCardsNotify();

$(document).on('click', '#buyOpenCards', function () {
    sendRequestCards({ buy_card: true })
        .done(function (result) {
            if (result.status == 'success') {
                renderCards();
            } else {
                noty(result.message, result.status);
            }
        });
});

$(document).on('click', '.closeCardsNotify', function () {
    const today = new Date().toISOString().split('T')[0];
    localStorage.setItem(CARDS_NOTIFY_KEY, today);
    $('.cards__notify').remove();
});