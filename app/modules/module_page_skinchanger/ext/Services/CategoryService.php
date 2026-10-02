<?php

namespace app\modules\module_page_skinchanger\ext\Services;

use app\modules\module_page_skinchanger\ext\Repositories\{
  CacheRepository,
  CategoryRepository
};

class CategoryService
{
  protected $Translate, $CacheRepository, $CategoryRepository;

  public function __construct(object $Translate)
  {
    $this->Translate     = $Translate;
    $this->CacheRepository = new CacheRepository();
    $this->CategoryRepository = new CategoryRepository();
  }

  public function getCategories(string $lang)
  {
    $categories = $this->CategoryRepository->getCategories();
    if (!is_array($categories)) return $categories;
    $skins = $this->CacheRepository->getCache($lang, 'skins');
    if (!$skins) return $categories;

    $nameMap = [];
    foreach ($skins as $s) {
      if (!empty($s['id_name']) && !empty($s['name'])) {
        $nameMap[$s['id_name']] = $s['name'];
      }
    }

    foreach ($categories as $catKey => &$cat) {
      if (empty($cat['list'])) continue;
      foreach ($cat['list'] as &$item) {
        if (isset($nameMap[$item['id_name']])) {
          $newName = $nameMap[$item['id_name']];
          $type = $item['type'] ?? '';
          if (($type === 'Knife' || $type === 'Gloves') && mb_strpos($newName, '★') === false) {
            $newName = '★ ' . $newName;
          }
          $item['name'] = $newName;
        }
      }
    }

    return $categories;
  }
}
