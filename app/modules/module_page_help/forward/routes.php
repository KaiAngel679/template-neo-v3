<?php
if (isset($_SESSION['user_admin'])) {
  if (isset($_POST['action'])) {
    if ($_POST['action'] === 'upload_image' && isset($_FILES['image'])) {
      exit($help->UploadIMG($_POST, $_FILES));
    }
  }
}

if (isset($_POST['button'])) {
  if ($_POST['button'] == 'load') {
    if(empty($_POST['param1'])){
      exit(json_encode($help->GetContentByID(), true));
    } else {
      exit(json_encode($help->GetContentByID($_POST['param1']), true));
    }
  }
  if (isset($_SESSION['user_admin'])) {
    if ($_POST['button'] == 'add_category') {
      exit(json_encode($help->AddCategory($_POST), true));
    } else if ($_POST['button'] == 'edit_category_btn') {
      exit(json_encode(['category' => $help->GetCategoryByID($_POST['param1']),  'title' => $Translate->get_translate_module_phrase('module_page_help', '_changingCat')], true));
    } else if ($_POST['button'] == 'edit_category') {
      exit(json_encode($help->EditCategory($_POST), true));
    } else if ($_POST['button'] == 'del_category') {
      exit(json_encode($help->DelCategory($_POST), true));
    } else if ($_POST['button'] == 'add_content') {
      exit(json_encode($help->AddContent($_POST), true));
    } else if ($_POST['button'] == 'add_content_btn') {
      exit(json_encode(['title' => $Translate->get_translate_module_phrase('module_page_help', '_addingItem')], true));
    } else if ($_POST['button'] == 'edit_content_btn') {
      exit(json_encode($help->GetContentByID($_POST['param1']), true));
    } else if ($_POST['button'] == 'del_content') {
      exit(json_encode($help->DelContent($_POST), true));
    } else if ($_POST['button'] == 'edit_content') {
      exit(json_encode($help->EditContent($_POST), true));
    } else if ($_POST['button'] == 'add_category_btn') {
      exit(json_encode(['title' => $Translate->get_translate_module_phrase('module_page_help', '_addingCat')], true));
    }
  }
}
