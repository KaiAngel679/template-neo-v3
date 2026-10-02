<?php

namespace app\modules\module_page_skinchanger\ext\Repositories;

class CacheRepository extends BaseRepository
{
  public function getCache(string $file, string $folder = '')
  {
    return $this->loadFromJson($file, $folder);
  }

  public function setCache(string $file, array $data, string $folder = ''): bool
  {
    return $this->saveToJson($file, $data, $folder);
  }
}
