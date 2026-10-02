<?php

namespace app\modules\module_page_results\ext\Repository;

class WarnRepository extends BaseRepository
{
  public $Db;
  public function __construct($Db)
  {
    $this->Db = $Db;
  }
  public function getAll(): array
  {
    $this->syncWithDatabase();
    return $this->loadFromJson('main', 'warns') ?: [];
  }

  public function add(string $steamid, string $reason, int $durationSeconds): bool
  {
    $newWarn = [
      'steamid' => $steamid,
      'reason' => $reason,
      'time' => time() + $durationSeconds,
      'createtime' => time(),
    ];
    
    if ($this->Db) {
      $this->Db->query('Core', 0, 0, 
        "INSERT INTO `lvl_web_managersystem_warn` (`steamid`, `reason`, `time`, `createtime`) VALUES (:steamid, :reason, :time, :createtime);", 
        $newWarn
      );
    }
    
    $this->syncWithDatabase();
    return true;
  }

  private function getFromDatabase(): array
  {
    if (!$this->Db) return [];
    return $this->Db->queryAll('Core', 0, 0, 'SELECT * FROM `lvl_web_managersystem_warn` ORDER BY id DESC') ?: [];
  }

  private function syncWithDatabase(): void
  {
    $cachedWarns = $this->loadFromJson('main', 'warns') ?: [];
    $dbWarns = $this->getFromDatabase();
    
    $warnsById = [];
    foreach ($cachedWarns as $warn) {
      $warnsById[$warn['id']] = $warn;
    }
    foreach ($dbWarns as $warn) {
      $warnsById[$warn['id']] = $warn;
    }
    
    $mergedWarns = array_values($warnsById);
    usort($mergedWarns, function ($a, $b) {
      return ($b['createtime'] ?? 0) <=> ($a['createtime'] ?? 0);
    });
    
    $this->saveToJson('main', 'warns', $mergedWarns);
  }
}
