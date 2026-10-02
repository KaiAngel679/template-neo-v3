<?php if (!empty($_SESSION['steamid64'])): ?>
    <div class="popup_modal cards__modal" id="openCards" data-modal-type="2"></div>
    <?php if ($progressCards['last_open_date'] != date("Y-m-d") && $progressCards['last_open_date'] != '2000-01-01'): ?>
        <div class="cards__notify">
            <div class="cards__notify-header">
                <h4 class="cards__notify-title">
                    <svg>
                        <use href="/resources/img/sprite.svg#info-circle"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_cards', '_todayOpen') ?> 
                </h4>
                <div class="cards__notify-close closeCardsNotify">
                    <svg>
                        <use href="/resources/img/sprite.svg#x"></use>
                    </svg>
                </div>
            </div>
            <div class="cards__notify-body">
                <div class="cards__notify-text"><?= $Translate->get_translate_module_phrase('module_page_cards', '_notifyText') ?></div>
            </div>
            <hr>
            <div class="cards__notify-buttons">
                <button class="active" data-openmodal="openCards">
                    <svg>
                        <use href="/resources/img/sprite.svg#game-cards"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_page_cards', '_openNow') ?>
                </button>
                <button class="closeCardsNotify"><?= $Translate->get_translate_phrase('_Close') ?></button>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>