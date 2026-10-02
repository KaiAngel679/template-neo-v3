<h2 class="tickets__h2"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_creatingTicket') ?></h2>
<span class="tickets__span-text"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_selectCategory') ?></span>
<div class="tickets__wrapper">
    <?php $first = true; foreach($categories as $key) : ?>
        <label for="ticket-category-<?= $key['id'] ?>" class="ticket__radio-label">
            <input class="ticket__radio-input" id="ticket-category-<?= $key['id'] ?>" value="<?= $key['id'] ?>" type="radio" name="ticket-category" <?= $first ? 'checked' : '' ?>>
            <p class="ticket__radio-title"><?= action_text_clear($key['title']) ?></p>
            <p class="ticket__radio-time <?= $key['id'] == $fastestCategoryId ? 'fast' : '' ?>">
                <?= $key['id'] == $fastestCategoryId ? '<svg><use href="/resources/img/sprite.svg#bolt"></use></svg>' : '<svg><use href="/resources/img/sprite.svg#timer"></use></svg>' ?>
                <?= $responseTimesFormat[$key['id']] ?>
            </p>
        </label>
    <?php $first = false; endforeach; ?>
</div>
<div id="category-render"></div>