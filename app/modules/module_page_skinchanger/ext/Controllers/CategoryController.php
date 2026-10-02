<?php

namespace app\modules\module_page_skinchanger\ext\Controllers;

use app\modules\module_page_skinchanger\ext\Services\{
  CategoryService
};

class CategoryController
{
  public $CategoryService;

  public function __construct(object $Db, object $Translate, object $General)
  {
    $this->CategoryService = new CategoryService($Translate);
  }

  public function getCategories(string $lang)
  {
    return $this->CategoryService->getCategories($lang);
  }
}
