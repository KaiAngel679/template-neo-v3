<?php

namespace app\modules\module_page_results\ext\Repository;

class CacheRepository extends BaseRepository
{
  public function getCache(): array
  {
    return $this->loadFromJson('main', 'cache') ?: [];
  }

  public function saveCache(array $settings): bool
  {
    return $this->saveToJson('main', 'cache', $settings);
  }
}
