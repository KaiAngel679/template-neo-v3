<?php

namespace app\modules\module_page_skinchanger\ext\Repositories;

class CategoryRepository extends BaseRepository
{
  public function getCategories()
  {
    return $this->loadFromJson('category');
  }
}
