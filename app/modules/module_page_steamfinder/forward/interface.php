<div class="row">
    <div class="col-md-12">
        <h1 class="sf__title"><?= $Translate->get_translate_module_phrase('module_page_steamfinder', '_title') ?></h1>
        <span class="sf__description"><?= $Translate->get_translate_module_phrase('module_page_steamfinder', '_description') ?></span>
        <div class="sf__search">
            <form action="searchPlayer" class="sf__input-wrapper">
                <input id="userSteam" name="steamid" placeholder="<?= $Translate->get_translate_module_phrase('module_page_steamfinder', '_inputPlaceholder') ?>" required>
                <button class="active" id="searchInfo" type="submit"><?= $Translate->get_translate_phrase('_PlaceholderSearch') ?></button>
            </form>
        </div>
    </div>
</div>
<div class="row" id="app"></div>