<div class="popup_modal" id="modal_editor">
  <div class="popup_modal_content no-close no-scrollbar">
    <div class="popup_modal_head">
      <div id="modal_editor_title"><?= $Translate->get_translate_module_phrase('module_page_help', '_addition'); ?></div>
      <span class="popup_modal_close">
        <svg><use href="/resources/img/sprite.svg#x"></use></svg>
      </span>
    </div>
    <form id="content_form">
      <div class="flex-inline">
        <div class="inputs-inline">
          <label for="content_title" class="help_label_name">Title</label>
          <input type="text" id="content_title" name="title" value="">
        </div>
        <div class="inputs-inline">
          <label for="content_svg" class="help_label_name"><?= $Translate->get_translate_module_phrase('module_page_help', '_svgCode'); ?></label>
          <input type="text" id="content_svg" name="svg" value="">
        </div>
      </div>
      <div class="flex-inline">
        <div class="inputs-inline">
          <label for="content_cat" class="help_label_name"><?= $Translate->get_translate_module_phrase('module_page_help', '_category'); ?></label>
          <select id="content_cat" name="category"></select>
        </div>
        <div class="inputs-inline">
          <label for="content_sort" class="help_label_name"><?= $Translate->get_translate_module_phrase('module_page_help', '_sortNumber'); ?></label>
          <input type="text" id="content_sort" name="sort" value="">
        </div>
      </div>
    </form>
    <div id="editor"></div>
    <div class="help_accetp_buttons">
      <button id="modal_editor_btn" onclick="Ajax('add_content')"><?= $Translate->get_translate_module_phrase('module_page_help', '_save'); ?></button>
      <button class="button-delete popup_modal_close"><?= $Translate->get_translate_module_phrase('module_page_help', '_no'); ?></button>
    </div>
  </div>
</div>