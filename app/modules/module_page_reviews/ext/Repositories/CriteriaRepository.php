<?php

namespace app\modules\module_page_reviews\ext\Repositories;

use app\modules\module_page_reviews\ext\ModuleHelper;

class CriteriaRepository
{
    public const DEFAULTS = [
        ['name' => ['ru' => 'Администрация', 'en' => 'Administration', 'ua' => 'Адміністрація']],
        ['name' => ['ru' => 'Комьюнити', 'en' => 'Community', 'ua' => "Ком'юніті"]],
        ['name' => ['ru' => 'Стабильность', 'en' => 'Stability', 'ua' => 'Стабільність']],
        ['name' => ['ru' => 'Честность', 'en' => 'Fairness', 'ua' => 'Чесність']],
        ['name' => ['ru' => 'Поддержка', 'en' => 'Support', 'ua' => 'Підтримка']],
    ];

    private $files;
    private $Db;
    private $booted = false;

    public function __construct(object $Db, ?FileRepository $files = null)
    {
        $this->Db = $Db;
        $this->files = $files ?? new FileRepository();
    }

    private function boot(): void
    {
        if ($this->booted) {
            return;
        }
        $this->booted = true;

        if ($this->files->exists('criteria')) {
            $this->normalizeStoredFile();
            return;
        }

        if ($this->migrateFromDb()) {
            return;
        }

        $this->seedDefaults();
    }

    private function normalizeStoredFile(): void
    {
        $data = $this->files->get('criteria');
        if (!is_array($data) || $data === []) {
            return;
        }

        $clean = [];
        $slugToId = [];
        $dirty = false;

        foreach ($data as $id => $row) {
            if (!is_array($row)) {
                $dirty = true;
                continue;
            }

            $idKey = (string) max(1, (int) $id);
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug !== '') {
                $slugToId[$slug] = $idKey;
            }

            if (
                array_key_exists('slug', $row)
                || array_key_exists('sort_order', $row)
                || array_key_exists('active', $row)
            ) {
                $dirty = true;
            }

            $clean[$idKey] = [
                'name' => is_array($row['name'] ?? null)
                    ? ModuleHelper::normalizeI18nName($row['name'])
                    : ModuleHelper::parseI18nName($row['name'] ?? ''),
            ];
        }

        if ($slugToId !== []) {
            $this->remapAttrKeys($slugToId);
            $dirty = true;
        }

        if ($dirty) {
            $this->files->update('criteria', static fn(): array => $clean);
        }
    }

    private function remapAttrKeys(array $slugToId): void
    {
        foreach ($slugToId as $slug => $id) {
            if ($slug === '' || $slug === $id) {
                continue;
            }
            try {
                $this->Db->query(
                    'Core',
                    0,
                    0,
                    'UPDATE IGNORE `neo_review_attrs` SET `attr` = ? WHERE `attr` = ?',
                    [$id, $slug]
                );
                $this->Db->query(
                    'Core',
                    0,
                    0,
                    'DELETE FROM `neo_review_attrs` WHERE `attr` = ?',
                    [$slug]
                );
            } catch (\Throwable $e) {
            }
        }
    }

    private function migrateFromDb(): bool
    {
        try {
            $rows = $this->Db->queryAll(
                'Core',
                0,
                0,
                'SELECT * FROM `neo_review_criteria` ORDER BY `id` ASC'
            );
        } catch (\Throwable $e) {
            return false;
        }

        if (!is_array($rows) || $rows === []) {
            return false;
        }

        $out = [];
        $slugToId = [];
        foreach ($rows as $row) {
            $id = (string) max(1, (int) ($row['id'] ?? 0));
            if ($id === '0') {
                continue;
            }
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug !== '') {
                $slugToId[$slug] = $id;
            }
            $out[$id] = [
                'name' => ModuleHelper::parseI18nName($row['name'] ?? ''),
            ];
        }

        if ($out === []) {
            return false;
        }

        $this->files->update('criteria', static fn(): array => $out);
        if ($slugToId !== []) {
            $this->remapAttrKeys($slugToId);
        }

        return true;
    }

    public function seedDefaults(): void
    {
        $out = [];
        $id = 1;
        foreach (self::DEFAULTS as $row) {
            $out[(string) $id] = [
                'name' => $row['name'],
            ];
            $id++;
        }
        $this->files->update('criteria', static fn(): array => $out);
    }

    private function allRaw(): array
    {
        $this->boot();
        $data = $this->files->get('criteria');
        $rows = [];
        foreach ($data as $id => $row) {
            if (!is_array($row)) {
                continue;
            }
            $rows[] = $this->normalizeRow((int) $id, $row);
        }

        return $rows;
    }

    private function normalizeRow(int $id, array $row): array
    {
        return [
            'id' => $id,
            'name' => is_array($row['name'] ?? null)
                ? ModuleHelper::normalizeI18nName($row['name'])
                : ModuleHelper::parseI18nName($row['name'] ?? ''),
        ];
    }

    public function listAll(): array
    {
        return $this->allRaw();
    }

    public function listActive(): array
    {
        return $this->allRaw();
    }

    public function getById(int $id): ?array
    {
        $this->boot();
        $data = $this->files->get('criteria');
        $key = (string) $id;
        if (!isset($data[$key]) || !is_array($data[$key])) {
            return null;
        }

        return $this->normalizeRow($id, $data[$key]);
    }

    public function create($name): int
    {
        $this->boot();
        $nameMap = is_array($name) ? ModuleHelper::normalizeI18nName($name) : ModuleHelper::parseI18nName($name);
        $newId = 0;

        $this->files->update('criteria', function (array $data) use ($nameMap, &$newId): array {
            $maxId = 0;
            foreach ($data as $id => $row) {
                $maxId = max($maxId, (int) $id);
            }
            $newId = $maxId + 1;
            $data[(string) $newId] = [
                'name' => $nameMap,
            ];

            return $data;
        });

        return $newId;
    }

    public function update(int $id, $name): void
    {
        $this->boot();
        $nameMap = is_array($name) ? ModuleHelper::normalizeI18nName($name) : ModuleHelper::parseI18nName($name);

        $this->files->update('criteria', function (array $data) use ($id, $nameMap): array {
            $key = (string) $id;
            if (!isset($data[$key]) || !is_array($data[$key])) {
                return $data;
            }
            $data[$key]['name'] = $nameMap;

            return $data;
        });
    }

    public function delete(int $id): ?int
    {
        $row = $this->getById($id);
        if ($row === null) {
            return null;
        }

        $this->files->update('criteria', function (array $data) use ($id): array {
            unset($data[(string) $id]);

            return $data;
        });

        $attrKey = (string) $id;
        try {
            $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_review_attrs` WHERE `attr` = ?', [$attrKey]);
        } catch (\Throwable $e) {
        }

        return $id;
    }

    public function reorder(array $orderedIds): void
    {
        $this->boot();
        $this->files->update('criteria', function (array $data) use ($orderedIds): array {
            $out = [];
            foreach ($orderedIds as $id) {
                $key = (string) (int) $id;
                if ($key === '0' || !isset($data[$key]) || !is_array($data[$key])) {
                    continue;
                }
                $out[$key] = [
                    'name' => is_array($data[$key]['name'] ?? null)
                        ? ModuleHelper::normalizeI18nName($data[$key]['name'])
                        : ModuleHelper::parseI18nName($data[$key]['name'] ?? ''),
                ];
            }
            foreach ($data as $key => $row) {
                if (isset($out[$key]) || !is_array($row)) {
                    continue;
                }
                $out[(string) $key] = [
                    'name' => is_array($row['name'] ?? null)
                        ? ModuleHelper::normalizeI18nName($row['name'])
                        : ModuleHelper::parseI18nName($row['name'] ?? ''),
                ];
            }

            return $out;
        });
    }
}
