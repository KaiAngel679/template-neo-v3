<div class="popup_modal" id="modal_subject" subject="">
  <div class="popup_modal_content no-close no-scrollbar">
    <div class="popup_modal_head">
      <div id="subject_modal_title"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_AddItem') ?></div>
      <span class="popup_modal_close">
        <svg>
          <use href="/resources/img/sprite.svg#x"></use>
        </svg>
      </span>
    </div>
    <div class="subject_content">
      <form id="subject_form" enctype="multipart/form-data">
        <input type="hidden" name="case_id_subject" value="<?= $_GET['id'] ?>">
        <div class="card">
          <div class="row card_edit_block">
            <div class="col-md-4">
              <div class="inputs-inline cases-label">
                <label for="subject_server"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Server') ?></label>
                <select name="subject_server" id="subject_server">
                  <option value="-1">Выбор игрока</option>
                  <?php foreach ($General->server_list as $key) : ?>
                    <option value="<?= $key['id'] ?>"><?= $key['name'] ?></option>
                  <?php endforeach ?>
                </select>
              </div>
              <div class="inputs-inline cases-label">
                <label for="subject_type"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_SelectType') ?></label>
                <select name="subject_type" id="subject_type" onchange="descript()">
                  <option value="1"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_TypeMoney') ?></option>
                  <option value="2"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_TypeCustom') ?></option>
                  <option value="3">VIP R1KO/Pisex/thesamefabius</option>
                  <option value="4">Iks Admin</option>
                  <option value="10">Iks Admin New</option>
                  <option value="11">AdminSystem</option>
                  <option value="5">ADMIN MA/SB</option>
                  <option value="6"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_TypeShop') ?></option>
                  <option value="7"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_TypeShopItems') ?></option>
                  <option value="8"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_TypeExp') ?></option>
                  <option value="9"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_TypeRcon') ?></option>
                </select>
              </div>
              <div class="inputs-inline cases-label">
                <label for="subject_name"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Title') ?></label>
                <input type="text" name="subject_name" id="subject_name">
              </div>
            </div>
            <div class="col-md-4">
              <div class="inputs-inline cases-label">
                <label for="subject_desc"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Desc') ?></label>
                <input type="text" id="subject_desc" name="subject_desc" value="<?= $Translate->get_translate_module_phrase('module_page_open_case', '_TypeMoney') ?>" readonly>
              </div>
              <div class="inputs-inline cases-label">
                <label id="subject_content_html" for="subject_content"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Content') ?></label>
                <input type="text" id="subject_content" name="subject_content">
              </div>
              <div class="inputs-inline cases-label">
                <label for="subject_class"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Background') ?></label>
                <select name="subject_class" id="subject_class">
                  <option value="1"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Gold') ?></option>
                  <option value="2"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Red') ?></option>
                  <option value="3"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Pink') ?></option>
                  <option value="4"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Purple') ?></option>
                  <option value="5"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Blue') ?></option>
                  <option value="6"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Turquoise') ?></option>
                  <option value="7"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Grey') ?></option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="inputs-inline cases-label">
                <label for="subject_chance"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Chance') ?></label>
                <input type="text" name="subject_chance" id="subject_chance">
              </div>
              <div class="inputs-inline cases-label">
                <label for="subject_sale"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_PriceToSale') ?></label>
                <input type="text" id="subject_sale" name="subject_sale" readonly>
              </div>
              <div class="inputs-inline cases-label">
                <label for="subject_sort"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Sort') ?></label>
                <input type="text" name="subject_sort" id="subject_sort">
              </div>
            </div>
            <div class="col-md-12">
              <div class="inputs-inline cases-label">
                <label><?= $Translate->get_translate_module_phrase('module_page_open_case', '_IMGSUB') ?></label>
              </div>
              <div id="drop-area">
                <div id="gallery"></div>
                <input type="file" id="fileElem" name="subject_img">
                <label class="button float_none" for="fileElem"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_SelectImages') ?></label>
                <p><?= $Translate->get_translate_module_phrase('module_page_open_case', '_InfoImages') ?></p>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="case_buttons">
      <div class="button" id="subject_modal_btn" onclick="CasesAjax('add_subject', '', 'subject_form')"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Save') ?></div>
      <div class="button button-delete popup_modal_close">Нет</div>
    </div>
  </div>
</div>
<script type="text/javascript" src="<?= $General->arr_general['site'] ?>app/modules/module_page_open_case/assets/js/draganddrop.js"></script>
<script type="text/javascript">
  function descript() {
    if ($('#subject_type').val() == 1) {
      $('#subject_desc').attr("readonly", "readonly");
      $('#subject_desc').val("<?= $Translate->get_translate_module_phrase('module_page_open_case', '_TypeMoney') ?>");
      $('#subject_sale').attr("readonly", "readonly");
      $('#subject_content_html').html('<?= $Translate->get_translate_module_phrase('module_page_open_case', '_ToBalance') ?>');
    } else if ($('#subject_type').val() == 2) {
      if ($('#subject_desc').attr("readonly")) {
        $('#subject_desc').removeAttr("readonly");
      }
      if ($('#subject_sale').attr("readonly")) {
        $('#subject_sale').removeAttr("readonly");
      }
      $('#subject_content_html').html("<?= $Translate->get_translate_module_phrase('module_page_open_case', '_SubjectContent') ?>");
    } else if ($('#subject_type').val() == 3 || $('#subject_type').val() == 4 || $('#subject_type').val() == 5) {

      if ($('#subject_desc').attr("readonly")) {
        $('#subject_desc').removeAttr("readonly");
      }
      if ($('#subject_sale').attr("readonly")) {
        $('#subject_sale').removeAttr("readonly");
      }
      if ($('#subject_type').val() == 3) {
        $('#subject_content_html').html("<?= $Translate->get_translate_module_phrase('module_page_open_case', '_GroupVIP') ?>");
      } else if ($('#subject_type').val() == 4 || $('#subject_type').val() == 5 || $('#subject_type').val() == 10  || $('#subject_type').val() == 11) {
        $('#subject_content_html').html("<?= $Translate->get_translate_module_phrase('module_page_open_case', '_GroupADM') ?>");
      }
    } else if ($('#subject_type').val() == 6) {
      if ($('#subject_desc').attr("readonly")) {
        $('#subject_desc').removeAttr("readonly");
      }
      if ($('#subject_sale').attr("readonly")) {
        $('#subject_sale').removeAttr("readonly");
      }
      $('#subject_content_html').html("<?= $Translate->get_translate_module_phrase('module_page_open_case', '_CreditsCredited') ?>");
    } else if ($('#subject_type').val() == 7) {
      if ($('#subject_desc').attr("readonly")) {
        $('#subject_desc').removeAttr("readonly");
      }
      if ($('#subject_sale').attr("readonly")) {
        $('#subject_sale').removeAttr("readonly");
      }
      $('#subject_content_html').html("ID Предмета в SHOP");
    } else if ($('#subject_type').val() == 8) {
      if ($('#subject_desc').attr("readonly")) {
        $('#subject_desc').removeAttr("readonly");
      }
      if ($('#subject_sale').attr("readonly")) {
        $('#subject_sale').removeAttr("readonly");
      }
      $('#subject_content_html').html("<?= $Translate->get_translate_module_phrase('module_page_open_case', '_ExperienceCredited') ?>");
    } else if ($('#subject_type').val() == 9) {
      if ($('#subject_desc').attr("readonly")) {
        $('#subject_desc').removeAttr("readonly");
      }
      if ($('#subject_sale').attr("readonly")) {
        $('#subject_sale').removeAttr("readonly");
      }
      $('#subject_content_html').html("<?= $Translate->get_translate_module_phrase('module_page_open_case', '_RconComm') ?>");
    }
  }
</script>