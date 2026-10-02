<?php

namespace app\modules\module_page_reviews\ext\Repositories;

use app\modules\module_page_reviews\ext\ModuleHelper;

class ReviewRepository
{
    private $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function create(array $data, array $attrs): int
    {
        $now = time();
        $this->Db->query(
            'Core',
            0,
            0,
            'INSERT INTO `neo_reviews`
            (`steamid`, `server_id`, `server_name`, `game`, `overall`, `hours`, `pros`, `cons`, `comment`, `score`, `created_at`, `updated_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)',
            [
                $data['steamid'],
                $data['server_id'],
                $data['server_name'],
                $data['game'],
                $data['overall'],
                $data['hours'],
                $data['pros'],
                $data['cons'],
                $data['comment'],
                $now,
                $now,
            ]
        );

        $id = (int) $this->Db->lastInsertId('Core', 0, 0);
        $this->replaceAttrs($id, $attrs);

        return $id;
    }

    public function update(int $id, array $data, array $attrs): void
    {
        $this->Db->query(
            'Core',
            0,
            0,
            'UPDATE `neo_reviews` SET
                `server_id` = ?, `server_name` = ?, `game` = ?, `overall` = ?, `hours` = ?,
                `pros` = ?, `cons` = ?, `comment` = ?, `updated_at` = ?
             WHERE `id` = ?',
            [
                $data['server_id'],
                $data['server_name'],
                $data['game'],
                $data['overall'],
                $data['hours'],
                $data['pros'],
                $data['cons'],
                $data['comment'],
                time(),
                $id,
            ]
        );

        $this->replaceAttrs($id, $attrs);
    }

    public function delete(int $id): void
    {
        $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_review_attrs` WHERE `review_id` = ?', [$id]);
        $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_review_votes` WHERE `review_id` = ?', [$id]);

        $comments = $this->Db->queryAll('Core', 0, 0, 'SELECT `id` FROM `neo_review_comments` WHERE `review_id` = ?', [$id]);
        foreach ((array) $comments as $comment) {
            $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_review_comment_votes` WHERE `comment_id` = ?', [(int) $comment['id']]);
        }
        $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_review_comments` WHERE `review_id` = ?', [$id]);
        $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_reviews` WHERE `id` = ?', [$id]);
    }

    public function getById(int $id): ?array
    {
        $row = $this->Db->query('Core', 0, 0, 'SELECT * FROM `neo_reviews` WHERE `id` = ? LIMIT 1', [$id]);

        return is_array($row) && $row !== [] ? $row : null;
    }

    public function findBySteamAndServer(string $steamid, int $serverId): ?array
    {
        $row = $this->Db->query(
            'Core',
            0,
            0,
            'SELECT * FROM `neo_reviews` WHERE `steamid` = ? AND `server_id` = ? LIMIT 1',
            [$steamid, $serverId]
        );

        return is_array($row) && $row !== [] ? $row : null;
    }

    public function countBySteam(string $steamid): int
    {
        $row = $this->Db->query(
            'Core',
            0,
            0,
            'SELECT COUNT(*) AS `total` FROM `neo_reviews` WHERE `steamid` = ?',
            [$steamid]
        );

        return (int) ($row['total'] ?? 0);
    }

    public function listIdsBySteam(string $steamid): array
    {
        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT `id` FROM `neo_reviews` WHERE `steamid` = ?',
            [$steamid]
        );

        $ids = [];
        foreach ((array) $rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    public function getAttrs(int $reviewId): array
    {
        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT `attr`, `value` FROM `neo_review_attrs` WHERE `review_id` = ?',
            [$reviewId]
        );

        $out = [];
        foreach ((array) $rows as $row) {
            $out[(string) $row['attr']] = (int) $row['value'];
        }

        return $out;
    }

    public function list(array $filters, string $sort, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildListWhere($filters);
        $order = $this->buildOrder($sort);

        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT * FROM `neo_reviews`' . $where . ' ORDER BY ' . $order . ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
            $params
        );

        return is_array($rows) ? $rows : [];
    }

    public function count(array $filters): int
    {
        [$where, $params] = $this->buildListWhere($filters);
        $row = $this->Db->query('Core', 0, 0, 'SELECT COUNT(*) AS `total` FROM `neo_reviews`' . $where, $params);

        return (int) ($row['total'] ?? 0);
    }

    public function findListIndex(int $reviewId, array $filters, string $sort): ?int
    {
        $row = $this->getById($reviewId);
        if ($row === null) {
            return null;
        }

        [$where, $filterParams] = $this->buildListWhere($filters);

        $existsSql = 'SELECT `id` FROM `neo_reviews`'
            . ($where === '' ? ' WHERE `id` = ?' : $where . ' AND `id` = ?')
            . ' LIMIT 1';
        $exists = $this->Db->query(
            'Core',
            0,
            0,
            $existsSql,
            array_merge($filterParams, [$reviewId])
        );
        if (empty($exists)) {
            return null;
        }

        [$beforeSql, $beforeParams] = $this->buildBeforePredicate($sort, $row);
        $sql = 'SELECT COUNT(*) AS `cnt` FROM `neo_reviews`';
        if ($where === '') {
            $sql .= ' WHERE (' . $beforeSql . ')';
            $params = $beforeParams;
        } else {
            $sql .= $where . ' AND (' . $beforeSql . ')';
            $params = array_merge($filterParams, $beforeParams);
        }

        $countRow = $this->Db->query('Core', 0, 0, $sql, $params);

        return (int) ($countRow['cnt'] ?? 0);
    }

    public function getSummary(array $criteria = []): array
    {
        $avg = $this->Db->query('Core', 0, 0, 'SELECT AVG(`overall`) AS `avg`, COUNT(*) AS `total` FROM `neo_reviews`');
        $distRows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT FLOOR(`overall`) AS `star`, COUNT(*) AS `cnt` FROM `neo_reviews` GROUP BY FLOOR(`overall`)'
        );
        $attrRows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT `attr`, AVG(`value`) AS `avg` FROM `neo_review_attrs` GROUP BY `attr`'
        );

        $dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ((array) $distRows as $row) {
            $star = (int) $row['star'];
            if ($star < 1) {
                $star = 1;
            }
            if ($star > 5) {
                $star = 5;
            }
            $dist[$star] = (int) $row['cnt'];
        }

        $avgByAttr = [];
        foreach ((array) $attrRows as $row) {
            $avgByAttr[(string) $row['attr']] = round((float) $row['avg'], 1);
        }

        $attrs = [];
        $attrsMap = [];
        foreach ($criteria as $criterion) {
            $key = (string) (int) ($criterion['id'] ?? 0);
            if ($key === '0') {
                continue;
            }
            $value = $avgByAttr[$key] ?? 0.0;
            $attrs[] = [
                'id' => (int) $key,
                'name' => ModuleHelper::resolveI18nName($criterion['name'] ?? '', $key),
                'avg' => $value,
            ];
            $attrsMap[$key] = $value;
        }

        $total = (int) ($avg['total'] ?? 0);

        return [
            'avg' => $total > 0 ? round((float) ($avg['avg'] ?? 0), 1) : 0,
            'total' => $total,
            'distribution' => $dist,
            'attrs' => $attrs,
            'attrs_map' => $attrsMap,
        ];
    }

    public function getVote(int $reviewId, string $steamid): ?int
    {
        $row = $this->Db->query(
            'Core',
            0,
            0,
            'SELECT `value` FROM `neo_review_votes` WHERE `review_id` = ? AND `steamid` = ? LIMIT 1',
            [$reviewId, $steamid]
        );

        return is_array($row) && $row !== [] ? (int) $row['value'] : null;
    }

    public function setVote(int $reviewId, string $steamid, int $value): void
    {
        $existing = $this->getVote($reviewId, $steamid);
        $now = time();

        if ($existing === null) {
            $this->Db->query(
                'Core',
                0,
                0,
                'INSERT INTO `neo_review_votes` (`review_id`, `steamid`, `value`, `created_at`) VALUES (?, ?, ?, ?)',
                [$reviewId, $steamid, $value, $now]
            );
            $this->Db->query('Core', 0, 0, 'UPDATE `neo_reviews` SET `score` = `score` + ? WHERE `id` = ?', [$value, $reviewId]);
            return;
        }

        if ($existing === $value) {
            $this->Db->query(
                'Core',
                0,
                0,
                'DELETE FROM `neo_review_votes` WHERE `review_id` = ? AND `steamid` = ?',
                [$reviewId, $steamid]
            );
            $this->Db->query('Core', 0, 0, 'UPDATE `neo_reviews` SET `score` = `score` - ? WHERE `id` = ?', [$value, $reviewId]);
            return;
        }

        $this->Db->query(
            'Core',
            0,
            0,
            'UPDATE `neo_review_votes` SET `value` = ?, `created_at` = ? WHERE `review_id` = ? AND `steamid` = ?',
            [$value, $now, $reviewId, $steamid]
        );
        $delta = $value - $existing;
        $this->Db->query('Core', 0, 0, 'UPDATE `neo_reviews` SET `score` = `score` + ? WHERE `id` = ?', [$delta, $reviewId]);
    }

    private function replaceAttrs(int $reviewId, array $attrs): void
    {
        $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_review_attrs` WHERE `review_id` = ?', [$reviewId]);
        foreach ($attrs as $attr => $value) {
            $attr = (string) $attr;
            if ($attr === '') {
                continue;
            }
            $this->Db->query(
                'Core',
                0,
                0,
                'INSERT INTO `neo_review_attrs` (`review_id`, `attr`, `value`) VALUES (?, ?, ?)',
                [$reviewId, $attr, (int) $value]
            );
        }
    }

    private function buildListWhere(array $filters): array
    {
        $where = [];
        $params = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $where[] = '(`pros` LIKE ? OR `cons` LIKE ? OR `comment` LIKE ? OR `server_name` LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $stars = $filters['stars'] ?? [];
        if (is_array($stars) && $stars !== []) {
            $stars = array_values(array_unique(array_map('intval', $stars)));
            $stars = array_filter($stars, static fn($s) => $s >= 1 && $s <= 5);
            if ($stars !== []) {
                $parts = [];
                foreach ($stars as $star) {
                    $parts[] = '(FLOOR(`overall`) = ? OR (`overall` = 5 AND ? = 5))';
                    $params[] = $star;
                    $params[] = $star;
                }
                $where[] = '(' . implode(' OR ', $parts) . ')';
            }
        }

        $sql = $where === [] ? '' : (' WHERE ' . implode(' AND ', $where));

        return [$sql, $params];
    }

    private function buildOrder(string $sort): string
    {
        switch ($sort) {
            case 'popular':
                return '`score` DESC, `created_at` DESC, `id` DESC';
            case 'rating':
                return '`overall` DESC, `created_at` DESC, `id` DESC';
            case 'date':
            default:
                return '`created_at` DESC, `id` DESC';
        }
    }

    private function buildBeforePredicate(string $sort, array $row): array
    {
        $id = (int) ($row['id'] ?? 0);
        $createdAt = (int) ($row['created_at'] ?? 0);

        switch ($sort) {
            case 'popular':
                $score = (int) ($row['score'] ?? 0);
                return [
                    '`score` > ? OR (`score` = ? AND `created_at` > ?) OR (`score` = ? AND `created_at` = ? AND `id` > ?)',
                    [$score, $score, $createdAt, $score, $createdAt, $id],
                ];
            case 'rating':
                $overall = (float) ($row['overall'] ?? 0);
                return [
                    '`overall` > ? OR (`overall` = ? AND `created_at` > ?) OR (`overall` = ? AND `created_at` = ? AND `id` > ?)',
                    [$overall, $overall, $createdAt, $overall, $createdAt, $id],
                ];
            case 'date':
            default:
                return [
                    '`created_at` > ? OR (`created_at` = ? AND `id` > ?)',
                    [$createdAt, $createdAt, $id],
                ];
        }
    }
}
