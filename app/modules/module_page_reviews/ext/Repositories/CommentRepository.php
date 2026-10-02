<?php

namespace app\modules\module_page_reviews\ext\Repositories;

class CommentRepository
{
    private $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function create(int $reviewId, ?int $parentId, string $steamid, string $text): int
    {
        $this->Db->query(
            'Core',
            0,
            0,
            'INSERT INTO `neo_review_comments` (`review_id`, `parent_id`, `steamid`, `text`, `score`, `created_at`)
             VALUES (?, ?, ?, ?, 0, ?)',
            [$reviewId, $parentId, $steamid, $text, time()]
        );

        return (int) $this->Db->lastInsertId('Core', 0, 0);
    }

    public function getById(int $id): ?array
    {
        $row = $this->Db->query('Core', 0, 0, 'SELECT * FROM `neo_review_comments` WHERE `id` = ? LIMIT 1', [$id]);

        return is_array($row) && $row !== [] ? $row : null;
    }

    public function listByReview(int $reviewId): array
    {
        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT * FROM `neo_review_comments` WHERE `review_id` = ? ORDER BY `created_at` ASC, `id` ASC',
            [$reviewId]
        );

        return is_array($rows) ? $rows : [];
    }

    public function countByReview(int $reviewId): int
    {
        $row = $this->Db->query(
            'Core',
            0,
            0,
            'SELECT COUNT(*) AS `total` FROM `neo_review_comments` WHERE `review_id` = ?',
            [$reviewId]
        );

        return (int) ($row['total'] ?? 0);
    }

    public function getVote(int $commentId, string $steamid): ?int
    {
        $row = $this->Db->query(
            'Core',
            0,
            0,
            'SELECT `value` FROM `neo_review_comment_votes` WHERE `comment_id` = ? AND `steamid` = ? LIMIT 1',
            [$commentId, $steamid]
        );

        return is_array($row) && $row !== [] ? (int) $row['value'] : null;
    }

    public function setVote(int $commentId, string $steamid, int $value): void
    {
        $existing = $this->getVote($commentId, $steamid);
        $now = time();

        if ($existing === null) {
            $this->Db->query(
                'Core',
                0,
                0,
                'INSERT INTO `neo_review_comment_votes` (`comment_id`, `steamid`, `value`, `created_at`) VALUES (?, ?, ?, ?)',
                [$commentId, $steamid, $value, $now]
            );
            $this->Db->query('Core', 0, 0, 'UPDATE `neo_review_comments` SET `score` = `score` + ? WHERE `id` = ?', [$value, $commentId]);
            return;
        }

        if ($existing === $value) {
            $this->Db->query(
                'Core',
                0,
                0,
                'DELETE FROM `neo_review_comment_votes` WHERE `comment_id` = ? AND `steamid` = ?',
                [$commentId, $steamid]
            );
            $this->Db->query('Core', 0, 0, 'UPDATE `neo_review_comments` SET `score` = `score` - ? WHERE `id` = ?', [$value, $commentId]);
            return;
        }

        $this->Db->query(
            'Core',
            0,
            0,
            'UPDATE `neo_review_comment_votes` SET `value` = ?, `created_at` = ? WHERE `comment_id` = ? AND `steamid` = ?',
            [$value, $now, $commentId, $steamid]
        );
        $delta = $value - $existing;
        $this->Db->query('Core', 0, 0, 'UPDATE `neo_review_comments` SET `score` = `score` + ? WHERE `id` = ?', [$delta, $commentId]);
    }

    public function update(int $id, string $text): void
    {
        $this->Db->query(
            'Core',
            0,
            0,
            'UPDATE `neo_review_comments` SET `text` = ? WHERE `id` = ?',
            [$text, $id]
        );
    }

    public function listReplyIds(int $parentId): array
    {
        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT `id` FROM `neo_review_comments` WHERE `parent_id` = ?',
            [$parentId]
        );

        $ids = [];
        foreach ((array) $rows as $row) {
            $ids[] = (int) ($row['id'] ?? 0);
        }

        return array_values(array_filter($ids));
    }

    public function listIdsBySteam(string $steamid, ?string $kind = null): array
    {
        $sql = 'SELECT `id` FROM `neo_review_comments` WHERE `steamid` = ?';
        if ($kind === 'comment') {
            $sql .= ' AND `parent_id` IS NULL';
        } elseif ($kind === 'reply') {
            $sql .= ' AND `parent_id` IS NOT NULL';
        }
        $sql .= ' ORDER BY `id` ASC';

        $rows = $this->Db->queryAll('Core', 0, 0, $sql, [$steamid]);
        $ids = [];
        foreach ((array) $rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    public function deleteVotes(int $commentId): void
    {
        $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_review_comment_votes` WHERE `comment_id` = ?', [$commentId]);
    }

    public function deleteById(int $id): void
    {
        $this->Db->query('Core', 0, 0, 'DELETE FROM `neo_review_comments` WHERE `id` = ?', [$id]);
    }

    public function deleteCascade(int $id): int
    {
        $replyIds = $this->listReplyIds($id);
        $removed = 0;

        foreach ($replyIds as $replyId) {
            $this->deleteVotes($replyId);
            $this->deleteById($replyId);
            $removed++;
        }

        $this->deleteVotes($id);
        $this->deleteById($id);
        $removed++;

        return $removed;
    }
}
