<?php 
use app\modules\module_page_faq\ext\Controllers\FaqController;
use app\modules\module_page_faq\ext\Repositories\FileRepository;

$fr = new FileRepository;
$cache = $fr->getCache();

if (isset($_SESSION['user_admin'])) {
    $fc = new FaqController($Translate);
    if (isset($_POST['created'])) {
        exit(json_encode($fc->created($_POST['question'], $_POST['answer']), true));
    } elseif (isset($_POST['delete'])) {
        exit(json_encode($fc->delete($_POST['id']), true));
    } elseif (isset($_POST['modal'])) {
        exit(json_encode($fc->modal($_POST['id']), true));
    } elseif (isset($_POST['edit'])) {
        exit(json_encode($fc->edit($_POST['id'], $_POST['title'], $_POST['text']), true));
    }
}

$Modules->set_page_title('FAQ | ' . $General->arr_general['short_name']);
$Modules->set_page_description('FAQ');