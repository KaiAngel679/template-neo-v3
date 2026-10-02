<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_cards', '_generalSettings') ?></div>
            </div>
            <div class="card-container">
                <div class="flex-inline">
                    <div class="inputs-inline">
                        <label for="cardPrice"><?= $Translate->get_translate_module_phrase('module_page_cards', '_costPerOpen') ?></label>
                        <div class="number" id="numberControl">
                            <button class="number-minus" type="button">-</button>
                            <input id="cardPrice" type="number" min="0" value="<?= $csсс->getCacheSettings()['price'] ?>" placeholder="50">
                            <button class="number-plus" type="button">+</button>
                        </div>
                    </div>
                    <div class="inputs-inline">
                        <label for="shopAmount"><?= $Translate->get_translate_module_phrase('module_page_cards', '_oneAmountShop') ?></label>
                        <input id="shopAmount" type="text" value="<?= $csсс->getCacheSettings()['currency'] ?>" placeholder="credits, gold">
                    </div>
                </div>
                <div class="inputs-inline">
                    <label for="cardImage"><?= $Translate->get_translate_module_phrase('module_page_cards', '_cardImage') ?></label>
                    <input id="cardImage" type="text" value="<?= action_text_clear($csсс->getCacheSettings()['svg']) ?>" placeholder='<svg><use href="/resources/img/sprite.svg#game-cards"></use></svg>'>
                </div>
                <label for=""><?= $Translate->get_translate_module_phrase('module_page_cards', '_chooseServers') ?></label>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="id-select">
                        <?php foreach ($General->server_list as $key): ?>
                            <li>
                                <label class="adaptive-select__label" for="<?= $key['id'] ?>">
                                    <div class="adaptive-select__label-text"><?= $key['name_custom'] ?></div>
                                    <input class="hide-input" id="<?= $key['id'] ?>" type="checkbox" name="cards-servers" <?= in_array($key['id'], $csсс->getCacheSettings()['ids']) ? 'checked' : '' ?>>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="id-select">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#servers"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_cards', '_chooseCardsServers') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <hr>
                <button class="width-100" id="save-general-settings"><?= $Translate->get_translate_phrase('_saveSettings') ?></button>
            </div>
        </div>
        <div class="card card-add-prize">
            <div class="card-header">
                <div class="badge" style="justify-content: space-between;"><?= $Translate->get_translate_module_phrase('module_page_cards', '_addingPrizes') ?>
                    <button class="active" id="addPrizeBtn">
                        <svg>
                            <use href="/resources/img/sprite.svg#plus"></use>
                        </svg>
                        <?= $Translate->get_translate_module_phrase('module_page_cards', '_addMore') ?>
                    </button>
                </div>
            </div>
            <div class="card-container">
                <div class="cards_add_wrapper" id="cards-0">
                    <div class="cards__delete-block" id="removePrizeBtn" style="display:none;">
                        <svg>
                            <use href="/resources/img/sprite.svg#x"></use>
                        </svg>
                    </div>
                    <outline class="cards__outline">
                        <?= $Translate->get_translate_module_phrase('module_page_cards', '_typeCard') ?>
                        <div class="cards__filter_wrapper">
                            <button class="filter" id="0">
                                <?= $Translate->get_translate_module_phrase('module_page_cards', '_commonCard') ?>
                            </button>
                            <button class="filter" id="1">
                                <?= $Translate->get_translate_module_phrase('module_page_cards', '_rareCard') ?>
                            </button>
                            <button class="filter" id="2">
                                <svg>
                                    <use href="/resources/img/sprite.svg#diamond"></use>
                                </svg>
                                <?= $Translate->get_translate_module_phrase('module_page_cards', '_legendaryCard') ?>
                            </button>
                        </div>
                    </outline>
                    <div class="flex-inline" style="margin-top: .5rem">
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="priseType">
                                <li>
                                    <label class="adaptive-select__label" for="money">
                                        <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_cards', '_moneyOnBalance') ?></div>
                                        <input class="hide-input" id="money" value="money" type="radio" name="typePrize">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="credits">
                                        <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_cards', '_creditsInShop') ?></div>
                                        <input class="hide-input" id="credits" value="credits" type="radio" name="typePrize">
                                    </label>
                                </li>
                            </ul>
                            <div class="adaptive-select" open-select="priseType">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#gift"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_cards', '_choosePrise') ?></span>
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
                                <input id="cardMoney" type="number" min="1" placeholder="<?= $Translate->get_translate_module_phrase('module_page_cards', '_countMoney') ?>">
                                <button class="number-plus" type="button">+</button>
                            </div>
                        </div>
                        <div class="inputs-inline" style="margin-bottom: 0">
                            <div class="number" id="numberControl">
                                <button class="number-minus" type="button">-</button>
                                <input id="cardChance" type="number" min="1" max="100" placeholder="<?= $Translate->get_translate_module_phrase('module_page_cards', '_chancePercentage') ?>">
                                <button class="number-plus" type="button">+</button>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <button class="width-100" id="save-prizes-settings"><?= $Translate->get_translate_phrase('_saveSettings') ?></button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_cards', '_listOfPrizes') ?></div>
            </div>
            <div class="card-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= $Translate->get_translate_module_phrase('module_page_cards', '_typeOfCard') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_cards', '_prise') ?></th>
                                <th><?= $Translate->get_translate_module_phrase('module_page_cards', '_chance') ?></th>
                                <th style="text-align: end"><?= $Translate->get_translate_module_phrase('module_page_cards', '_action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($csсс->getRewards() as $reward):
                                switch ($reward['rare']) {
                                    case 0:
                                        $rarityClass = 'normal';
                                        $rarityText = $Translate->get_translate_module_phrase('module_page_cards', '_commonCard');
                                        break;
                                    case 1:
                                        $rarityClass = 'rare';
                                        $rarityText = $Translate->get_translate_module_phrase('module_page_cards', '_rareCard');
                                        break;
                                    case 2:
                                        $rarityClass = 'extra-rare';
                                        $rarityText = $Translate->get_translate_module_phrase('module_page_cards', '_legendaryCard');
                                        break;
                                    default:
                                        $rarityClass = 'normal';
                                        $rarityText = '';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <div class="card__rarity <?= $rarityClass ?>"><?= $rarityText ?></div>
                                    </td>
                                    <td><?= $reward['count'] ?><?= $reward['type'] === 'money' ? '₽' : '★' ?></td>
                                    <td><?= $reward['chance'] ?>%</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="icon_btn_transparent button-edit" data-id="<?= $reward['id'] ?>" data-openmodal="editPrise">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#edit-pen"></use>
                                                </svg>
                                            </button>
                                            <button class="icon_btn_transparent button-delete" data-id="<?= $reward['id'] ?>">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#trash"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="popup_modal" id="editPrise" data-modal-type="2"></div>