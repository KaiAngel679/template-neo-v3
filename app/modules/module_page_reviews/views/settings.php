<div class="card at__card-settings">
    <div class="at__button-wrapper">
        <button class="filter <?= ($section ?? 'general') === 'general' ? 'active' : '' ?>" type="button" onclick="location.href='/reviews/settings/general/'">
            <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_tabGeneral') ?>
        </button>
        <button class="filter <?= ($section ?? '') === 'criteria' ? 'active' : '' ?>" type="button" onclick="location.href='/reviews/settings/criteria/'">
            <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_tabCriteria') ?>
        </button>
        <button class="filter <?= ($section ?? '') === 'bans' ? 'active' : '' ?>" type="button" onclick="location.href='/reviews/settings/bans/'">
            <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_tabBans') ?>
        </button>
    </div>
    <?php require MODULES . MODULE_NAME . '/views/settings/' . $section . '.php'; ?>
</div>