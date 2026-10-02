<?php

namespace app\modules\module_page_reviews\ext\Repositories;

class BanRepository
{
    private $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function count(): int
    {
        $row = $this->Db->query('Core', 0, 0, 'SELECT COUNT(*) AS `total` FROM `neo_review_bans`');

        return (int) ($row['total'] ?? 0);
    }

    public function listAll(): array
    {
        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT * FROM `neo_review_bans` ORDER BY `created_at` DESC, `id` DESC'
        );

        return is_array($rows) ? $rows : [];
    }

    public function getById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $row = $this->Db->query('Core', 0, 0, 'SELECT * FROM `neo_review_bans` WHERE `id` = ? LIMIT 1', [$id]);

        return is_array($row) && $row !== [] ? $row : null;
    }

    public function create(array $data): int
    {
        $this->Db->query(
            'Core',
            0,
            0,
            'INSERT INTO `neo_review_bans` (`steamid`, `ip`, `scope`, `reason`, `created_at`, `created_by`)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                (string) ($data['steamid'] ?? ''),
                (string) ($data['ip'] ?? ''),
                (string) ($data['scope'] ?? 'all'),
                (string) ($data['reason'] ?? ''),
                (int) ($data['created_at'] ?? time()),
                (string) ($data['created_by'] ?? ''),
            ]
        );

        return (int) $this->Db->lastInsertId('Core', 0, 0);
    }

    public function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        if ($this->getById($id) === null) {
            return false;
        }

        $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_review_bans` WHERE `id` = ?', [$id]);

        return true;
    }

    public function findMatch(string $scope, string $steamid, string $ip): ?array
    {
        $parts = [];
        $params = [];

        if ($steamid !== '') {
            $parts[] = '(`steamid` != \'\' AND `steamid` = ?)';
            $params[] = $steamid;
        }
        if ($ip !== '') {
            $parts[] = '(`ip` != \'\' AND `ip` = ?)';
            $params[] = $ip;
        }

        if ($parts === []) {
            return null;
        }

        $params[] = $scope;

        $row = $this->Db->query(
            'Core',
            0,
            0,
            'SELECT * FROM `neo_review_bans`
             WHERE (' . implode(' OR ', $parts) . ')
               AND (`scope` = \'all\' OR `scope` = ?)
             ORDER BY `id` DESC
             LIMIT 1',
            $params
        );

        return is_array($row) && $row !== [] ? $row : null;
    }
}
