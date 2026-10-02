<div class="popup_modal" id="modal_category" category_id="0">
  <div class="popup_modal_content no-close no-scrollbar">
    <div class="popup_modal_head">
      <div id="modal_category_title"><?= $Translate->get_translate_module_phrase('module_page_help', '_addition'); ?></div>
      <span class="popup_modal_close">
        <svg><use href="/resources/img/sprite.svg#x"></use></svg>
      </span>
    </div>
    <form id="category_form">
      <div class="inputs-inline">
        <label for="category_title" class="help_label_name">Title</label>
        <input type="text" id="category_title" name="title" value="">
      </div>
      <div class="inputs-inline">
        <label for="category_svg" class="help_label_name"><?= $Translate->get_translate_module_phrase('module_page_help', '_svgCode'); ?></label>
        <input type="text" id="category_svg" name="svg" value="">
      </div>
      <div class="inputs-inline">
        <label for="category_sort" class="help_label_name"><?= $Translate->get_translate_module_phrase('module_page_help', '_sortNumber'); ?></label>
        <input type="text" id="category_sort" name="sort" value="">
      </div>
    </form>
    <div class="help_accetp_buttons">
      <button id="modal_category_btn" onclick="Ajax('add_category')"><?= $Translate->get_translate_module_phrase('module_page_help', '_save'); ?></button>
      <button class="button-delete popup_modal_close"><?= $Translate->get_translate_module_phrase('module_page_help', '_no'); ?></button>
    </div>
  </div>
</div>