
<?php if($access['access'] == 1) : ?>
    <h2 class="tickets__h2"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_listClosedTickets') ?></h2>
    <div class="tickets__open-header">
        <div class="tickets__selects-wrapper">
            <input class="flex-1" type="search" id="search" placeholder="<?= $Translate->get_translate_module_phrase('module_page_tickets', '_searchTickets') ?>">
            <div class="adaptive-select-wrapper flex-1 width-100">
                <ul class="adaptive-select__dropdown-list" id="categories-filter">
                    <li>
                        <label class="adaptive-select__label" for="category-filter">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_allCat') ?></div>
                            <input class="hide-input" value="all" id="category-filter" type="radio" name="categories-filter" checked>
                        </label>
                    </li>
                    <?php foreach($ds->getCategoriesTabs() as $key) : ?>
                        <?php if (empty($categories) || in_array($key['id'], $categories)) : ?>
                            <li>
                                <label class="adaptive-select__label" for="category-filter-<?= $key['id'] ?>">
                                    <div class="adaptive-select__label-text"><?= action_text_clear($key['title']) ?></div>
                                    <input class="hide-input" value="<?= $key['id'] ?>" id="category-filter-<?= $key['id'] ?>" type="radio" name="categories-filter">
                                </label>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <div class="adaptive-select" open-select="categories-filter">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#list"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_allCat') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="adaptive-select-wrapper flex-1 width-100">
                <ul class="adaptive-select__dropdown-list" id="servers-filter">
                    <li>
                        <label class="adaptive-select__label" for="server-filter">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_phrase('_allServers') ?></div>
                            <input class="hide-input" value="all" id="server-filter" type="radio" name="servers-filter" checked>
                        </label>
                    </li>
                    <?php foreach($ds->getServers() as $key) : ?>
                        <li>
                            <label class="adaptive-select__label" for="server-filter-<?= $key['id'] ?>">
                                <div class="adaptive-select__label-text"><?= action_text_clear($key['name_custom']) ?></div>
                                <input class="hide-input" value="<?= $key['id'] ?>" id="server-filter-<?= $key['id'] ?>" type="radio" name="servers-filter">
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="adaptive-select" open-select="servers-filter">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#servers"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_phrase('_allServers') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="width-100 flex-1">
                <input type="checkbox" class="switch" id="myTickets">
                <label for="myTickets"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_ticketMy') ?></label>
            </div>
        </div>
        <div class="tickets__count-wrapper">
            <span><?= $Translate->get_translate_module_phrase('module_page_tickets', '_wantSee') ?></span>
            <div class="tickets__count-buttons">
                <button class="filter tickets__filter-button">10</button>
                <button class="filter tickets__filter-button">20</button>
                <button class="filter tickets__filter-button">50</button>
            </div>
        </div>
    </div>
<?php else: ?>
    <h2 class="tickets__h2"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_archiveYourTickets') ?></h2>
<?php endif; ?>
<div id="archive-render"></div>
<div id="pagination"></div>