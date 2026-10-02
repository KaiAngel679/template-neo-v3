<?php

namespace app\modules\module_page_reviews\ext\Services;

use app\modules\module_page_reviews\ext\ModuleHelper;
use app\modules\module_page_reviews\ext\Repositories\CriteriaRepository;

class CriteriaService
{
    private $criteria;
    private $Translate;

    public function __construct(CriteriaRepository $criteria, ?object $Translate = null)
    {
        $this->criteria = $criteria;
        $this->Translate = $Translate;
    }

    public function listAll(): array
    {
        return [
            'status' => 'success',
            'criteria' => array_map([$this, 'format'], $this->criteria->listAll()),
        ];
    }

    public function listActiveFormatted(): array
    {
        return array_map([$this, 'format'], $this->criteria->listActive());
    }

    public function create(array $display): array
    {
        $display = ModuleHelper::normalizeI18nName($display);
        if ($display === []) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_criterionNameRequired'),
            ];
        }

        $id = $this->criteria->create($display);
        $row = $this->criteria->getById($id);

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_criterionAdded'),
            'criterion' => $this->format($row),
        ];
    }

    public function update(int $id, array $display): array
    {
        $row = $this->criteria->getById($id);
        if ($row === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_criterionNotFound'),
            ];
        }

        $display = ModuleHelper::normalizeI18nName($display);
        if ($display === []) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_criterionNameRequired'),
            ];
        }

        $this->criteria->update($id, $display);
        $updated = $this->criteria->getById($id);

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_criterionUpdated'),
            'criterion' => $this->format($updated),
        ];
    }

    public function delete(int $id): array
    {
        $row = $this->criteria->getById($id);
        if ($row === null) {
            return [
                'status' => 'error',
                'message' => ModuleHelper::phrase($this->Translate, '_rv_criterionNotFound'),
            ];
        }

        $this->criteria->delete($id);

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_criterionDeleted'),
        ];
    }

    public function reorder(array $orderedIds): array
    {
        $this->criteria->reorder($orderedIds);

        return [
            'status' => 'success',
            'message' => ModuleHelper::phrase($this->Translate, '_rv_orderSaved'),
            'criteria' => array_map([$this, 'format'], $this->criteria->listAll()),
        ];
    }

    private function format(?array $row): ?array
    {
        if ($row === null || $row === []) {
            return null;
        }

        $nameRaw = ModuleHelper::parseI18nName($row['name'] ?? '');

        return [
            'id' => (int) $row['id'],
            'name' => ModuleHelper::resolveI18nName($nameRaw),
            'name_raw' => $nameRaw,
        ];
    }
}
