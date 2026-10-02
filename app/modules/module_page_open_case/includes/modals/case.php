<div class="popup_modal" id="modal_case" case="">
  <div class="popup_modal_content no-close no-scrollbar">
    <div class="popup_modal_head">
      <div id="case_modal_title"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_AddCase') ?></div>
      <span class="popup_modal_close">
        <svg><use href="/resources/img/sprite.svg#x"></use></svg>
      </span>
    </div>
    <div class="case_content">
      <form id="case_form" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-6">
            <div class="inputs-inline cases-label">
              <label for="case_name"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_CaseName') ?></label>
              <input type="text" name="case_name" id="case_name">
            </div>
            <div class="inputs-inline cases-label">
              <label for="case_type"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_CaseType') ?></label>
              <select id="case_type" onchange="descript()" name="case_type">
                <option value="1"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_ForMoney') ?></option>
                <option value="2"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Free') ?></option>
              </select>
            </div>
            <div class="inputs-inline cases-label">
              <label for="case_cat"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_CaseCat') ?></label>
              <select id="case_cat" name="case_cat">
                <option value="0"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Uncategorized') ?></option>
                <?php foreach($cats as $key):?>
                  <option value="<?=$key['id']?>"><?=$key['name']?></option>
                <?php endforeach;?>
              </select>
            </div>
            <div class="inputs-inline cases-label">
              <label for="case_price" id="case_price_info"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_OpPrice') ?></label>
              <input type="number" name="case_price" id="case_price">
            </div>
            <div class="inputs-inline cases-label">
              <label for="case_sort"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Sort') ?></label>
              <input type="number" name="case_sort" id="case_sort">
            </div>
          </div>
          <div class="col-md-6">
            <div class="inputs-inline cases-label">
              <label><?= $Translate->get_translate_module_phrase('module_page_open_case', '_IMGASE') ?></label>
            </div>
            <div id="drop-area">
              <div id="gallery"></div>
              <input type="file" id="fileElem" name="case_img" accept="image/png">
              <label class="button float_none" for="fileElem"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_SelectImages') ?></label>
              <p><?= $Translate->get_translate_module_phrase('module_page_open_case', '_InfoImages') ?></p>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="case_buttons">
      <div class="button" id="case_modal_btn" onclick="CasesAjax('add_case', '', 'case_form')"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Save') ?></div>
      <button class="button button-delete popup_modal_close">Нет</button>
    </div>
  </div>
</div>
<script type="text/javascript" src="<?= $General->arr_general['site'] ?>app/modules/module_page_open_case/assets/js/draganddrop.js"></script>
<script type="text/javascript">
  function descript() {
    if ($('#case_type').val() == 1) {
      $('#case_price_info').html('<?= $Translate->get_translate_module_phrase('module_page_open_case', '_OpPrice') ?>');
    } else if ($('#case_type').val() == 2) {
      $('#case_price_info').html('<?= $Translate->get_translate_module_phrase('module_page_open_case', '_OpenTime') ?> <a href="https://www.cy-pr.com/tools/time/" target="_blanck">UNIX TIME</a>');
    }
  }
</script>