<div class="row">
    <div class="col-md-12">
        <div class="cards__live-header">
            <h4 class="cards__live-title"><?= $Translate->get_translate_module_phrase('module_block_main_cards', '_lastDropFromCards') ?>
                <?php if (isset($_SESSION['user_admin'])): ?>
                    <a href="/cards/settings/" data-tippy-content="Настройка модуля" data-tippy-placement="right">
                        <svg>
                            <use href="/resources/img/sprite.svg#gear"></use>
                        </svg>
                    </a>
                <?php endif; ?>
            </h4>
            <div class="cards__live-filter">
                <button class="filter active" data-cards="allDrop"><?= $Translate->get_translate_module_phrase('module_block_main_cards', '_all') ?></button>
                <button class="filter" data-cards="topDrop">
                    <svg>
                        <use href="/resources/img/sprite.svg#star-fill"></use>
                    </svg>
                    <?= $Translate->get_translate_module_phrase('module_block_main_cards', '_topDrop') ?>
                </button>
            </div>
        </div>
        <div class="cards__live-wrapper" id="allDrop">
            <?php foreach ($CardsController->getLast10Type() as $drop): ?>
                <a href="/profiles/<?= $drop['steamid'] ?>/?search=1" target="_blank" class="cards__live-block <?= $drop['rare'] == 1 ? 'cards__live-rare' : ($drop['rare'] == 2 ? 'cards__live-extra-rare' : '') ?>">
                    <div class="cards__live-avatar">
                        <?= $General->get_js_relevance_avatar($drop['steamid']) ?>
                        <img src="<?= $General->getAvatar($drop['steamid'], 3); ?>" alt="" id="avatar" avatarid="<?= $drop['steamid'] ?>">
                        <div class="cards__live-icon" data-tippy-content="<?= $drop['rare'] == 0 ? $Translate->get_translate_module_phrase('module_page_cards', '_commonCard') : ($drop['rare'] == 1 ? $Translate->get_translate_module_phrase('module_page_cards', '_rareCard') : $Translate->get_translate_module_phrase('module_page_cards', '_legendaryCard')) ?>" data-tippy-placement="top">
                            <svg>
                                <use href="/resources/img/sprite.svg#game-cards"></use>
                            </svg>
                        </div>
                        <div class="cards__live-icon-shadow">
                            <svg>
                                <use href="/resources/img/sprite.svg#game-cards"></use>
                            </svg>
                        </div>
                    </div>
                    <div class="cards__live-info">
                        <div class="cards__live-username"><?= $General->checkName($drop['steamid']) ?></div>
                        <div class="cards__live-prise"><?= $drop['type'] == 'money' ? $drop['count'] . $this->General->currency . $Translate->get_translate_module_phrase('module_block_main_cards', '_balanceSite') : $drop['count'] . $Translate->get_translate_module_phrase('module_block_main_cards', '_creditsOnTheServert') ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="cards__live-wrapper" id="topDrop" style="display: none;">
            <?php foreach ($CardsController->getLast10TypeRare() as $drop): ?>
                <a href="/profiles/<?= $drop['steamid'] ?>/?search=1" target="_blank" class="cards__live-block <?= $drop['rare'] == 1 ? 'cards__live-rare' : ($drop['rare'] == 2 ? 'cards__live-extra-rare' : '') ?>">
                    <div class="cards__live-avatar">
                        <?= $General->get_js_relevance_avatar($drop['steamid']) ?>
                        <img src="<?= $General->getAvatar($drop['steamid'], 3); ?>" alt="" id="avatar" avatarid="<?= $drop['steamid'] ?>">
                        <div class="cards__live-icon" data-tippy-content="<?= $drop['rare'] == 0 ? $Translate->get_translate_module_phrase('module_page_cards', '_commonCard') : ($drop['rare'] == 1 ? $Translate->get_translate_module_phrase('module_page_cards', '_rareCard') : $Translate->get_translate_module_phrase('module_page_cards', '_legendaryCard')) ?>" data-tippy-placement="top">
                            <svg>
                                <use href="/resources/img/sprite.svg#game-cards"></use>
                            </svg>
                        </div>
                        <div class="cards__live-icon-shadow">
                            <svg>
                                <use href="/resources/img/sprite.svg#game-cards"></use>
                            </svg>
                        </div>
                    </div>
                    <div class="cards__live-info">
                        <div class="cards__live-username"><?= $General->checkName($drop['steamid']) ?></div>
                        <div class="cards__live-prise"><?= $drop['type'] == 'money' ? $drop['count'] . $this->General->currency . $Translate->get_translate_module_phrase('module_block_main_cards', '_balanceSite') : $drop['count'] . $Translate->get_translate_module_phrase('module_block_main_cards', '_creditsOnTheServert') ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>