<div class="popup_modal" id="modal_cat" cat="">
  <div class="popup_modal_content no-close no-scrollbar">
    <div class="popup_modal_head">
      <div id="cat_modal_title"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_AddCat') ?></div>
      <span class="popup_modal_close">
        <svg><use href="/resources/img/sprite.svg#x"></use></svg>
      </span>
    </div>
    <div class="case_content">
      <form id="cat_form" enctype="multipart/form-data">
        <div class="inputs-inline">
          <label for="cat_name"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Name') ?></label>
          <input type="text" name="cat_name" id="cat_name">
        </div>
        <div class="inputs-inline">
          <label for="cat_sort"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Sort') ?></label>
          <input type="text" name="cat_sort" id="cat_sort">
        </div>
      </form>
    </div>
    <div class="case_buttons">
      <div class="button" id="cat_modal_btn" onclick="CasesAjax('add_cat', '', 'cat_form')"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Save') ?></div>
      <div class="button button-delete popup_modal_close">Нет</div>
    </div>
  </div>
</div>