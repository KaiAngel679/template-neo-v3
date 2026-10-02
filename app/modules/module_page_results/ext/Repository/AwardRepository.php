<?php

namespace app\modules\module_page_results\ext\Repository;

class AwardRepository extends BaseRepository
{
  public function getAll(): array
  {
    return $this->loadFromJson('main', 'awards') ?: [];
  }

  public function getById(int $id): array
  {
    $awards = $this->getAll();
    foreach ($awards as $award) {
      if ((int)($award['id'] ?? 0) === $id) {
        return $award;
      }
    }
    return [];
  }

  public function add(array $data): bool
  {
    $awards = $this->getAll();
    $newAward = [
      'id' => $this->getNextId($awards),
      'time' => (int)($data['time'] ?? 0),
      'amount' => trim($data['amount'] ?? ''),
    ];
    $awards[] = $newAward;
    return $this->saveToJson('main', 'awards', $awards);
  }

  public function update(int $id, array $data): bool
  {
    $awards = $this->getAll();
    foreach ($awards as &$award) {
      if ((int)($award['id'] ?? 0) === $id) {
        $award['time'] = (int)($data['time'] ?? 0);
        $award['amount'] = trim($data['amount'] ?? '');
        return $this->saveToJson('main', 'awards', $awards);
      }
    }
    return false;
  }

  public function delete(int $id): bool
  {
    $awards = $this->getAll();
    $updatedAwards = array_filter($awards, function ($award) use ($id) {
      return (int)($award['id'] ?? 0) !== $id;
    });
    return $this->saveToJson('main', 'awards', array_values($updatedAwards));
  }

  public function exists(int $id): bool
  {
    return !empty($this->getById($id));
  }
}
