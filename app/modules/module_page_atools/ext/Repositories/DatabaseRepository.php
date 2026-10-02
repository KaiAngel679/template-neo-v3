<?php

namespace app\modules\module_page_atools\ext\Repositories;

class DatabaseRepository
{
    private $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function createTables(int $steamid): void
    {
        $this->Db->query('Core', 0, 0, 'CREATE TABLE IF NOT EXISTS `neo_atools_user_permissions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `steamid` BIGINT(17) NOT NULL,
            `permissions` JSON NOT NULL,
            `created_at` INT UNSIGNED NOT NULL,
            `updated_at` INT UNSIGNED NOT NULL,
            UNIQUE KEY `uq_steamid` (`steamid`),
            KEY `idx_steamid` (`steamid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

        $this->Db->query('Core', 0, 0, 'CREATE TABLE IF NOT EXISTS `neo_atools_warnings` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `target_steamid` BIGINT(17) NOT NULL,
            `admin_steamid` BIGINT(17) NOT NULL,
            `reason` VARCHAR(255) NOT NULL,
            `duration` INT UNSIGNED NOT NULL DEFAULT 0,
            `created_at` INT UNSIGNED NOT NULL,
            `updated_at` INT UNSIGNED NOT NULL,
            `expires_at` INT UNSIGNED NOT NULL,
            KEY `idx_target_steamid` (`target_steamid`),
            KEY `idx_admin_steamid` (`admin_steamid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

        $this->Db->query('Core', 0, 0, 'CREATE TABLE IF NOT EXISTS `neo_atools_check_modal` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `steamid` BIGINT(17) NOT NULL,
            `created_at` INT UNSIGNED NOT NULL,
            KEY `idx_steamid` (`steamid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

        $this->Db->query(
            'Core',
            0,
            0,
            "INSERT IGNORE INTO neo_atools_user_permissions (steamid, permissions, created_at, updated_at) VALUES
            (?, JSON_ARRAY('admins.view', 'admins.create', 'admins.update', 'admins.delete', 'admins.warn.give', 'admins.warn.remove', 'admins.warn.delete',
            'admins.warn.update', 'punishments.view', 'bans.create', 'bans.delete', 'bans.unban', 'bans.update', 'mutes.create', 'mutes.delete', 
            'mutes.unmute', 'mutes.update', 'checks.view', 'checks.delete', 'finances.view', 'finances.update', 'finances.reset', 'privileges.view', 
            'privileges.create', 'privileges.update', 'privileges.delete', 'credits.view', 'credits.update', 'credits.reset', 'experience.view', 
            'experience.update', 'experience.reset', 'logs.view', 'logs.delete'), UNIX_TIMESTAMP(), UNIX_TIMESTAMP())",
            [$steamid]
        );
    }

    public function hasWelcomeModalSeen(int $steamid): bool
    {
        if ($steamid <= 0) {
            return true;
        }

        return (bool) $this->Db->query(
            'Core',
            0,
            0,
            'SELECT `id` FROM `neo_atools_check_modal` WHERE `steamid` = ? LIMIT 1',
            [$steamid]
        );
    }

    public function markWelcomeModalSeen(int $steamid): void
    {
        if ($steamid <= 0 || $this->hasWelcomeModalSeen($steamid)) {
            return;
        }

        $this->Db->query(
            'Core',
            0,
            0,
            'INSERT INTO `neo_atools_check_modal` (`steamid`, `created_at`) VALUES (?, UNIX_TIMESTAMP())',
            [$steamid]
        );
    }

    public function getAccessAdmin(int $steamid): array
    {
        return $this->Db->query('Core', 0, 0, "SELECT * FROM neo_atools_user_permissions WHERE steamid = ?", [$steamid]);
    }

    public function getAccessAdminsBySteamids(array $steamids): array
    {
        $list = RepositoryHelper::uniqueNonEmptyStrings($steamids);
        if ($list == []) {
            return [];
        }

        $placeholders = RepositoryHelper::inPlaceholders(count($list));
        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT steamid, permissions FROM neo_atools_user_permissions WHERE steamid IN (' . $placeholders . ')',
            $list
        );

        $map = [];
        foreach ($rows as $row) {
            if (empty($row['steamid'])) {
                continue;
            }
            $decoded = json_decode((string) ($row['permissions'] ?? ''), true);
            $map[(string) $row['steamid']] = is_array($decoded) ? $decoded : [];
        }

        return $map;
    }

    public function getAllAccessAdmins(): array
    {
        $rows = $this->Db->queryAll('Core', 0, 0, 'SELECT steamid, permissions FROM neo_atools_user_permissions');
        $map = [];

        foreach ($rows as $row) {
            if (empty($row['steamid'])) {
                continue;
            }
            $decoded = json_decode((string) ($row['permissions'] ?? ''), true);
            $map[(string) $row['steamid']] = is_array($decoded) ? $decoded : [];
        }

        return $map;
    }

    public function createAdminWeb(int $steamid, array $permissions): void
    {
        $permissionsJson = json_encode($permissions, JSON_UNESCAPED_UNICODE);
        $this->Db->query(
            'Core',
            0,
            0,
            "INSERT INTO neo_atools_user_permissions (steamid, permissions, created_at, updated_at)
                VALUES (?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
                ON DUPLICATE KEY UPDATE permissions = ?, updated_at = UNIX_TIMESTAMP()",
            [$steamid, $permissionsJson, $permissionsJson]
        );
    }

    public function deleteAdminWeb(int $steamid): void
    {
        $this->Db->query(
            'Core',
            0,
            0,
            "DELETE FROM neo_atools_user_permissions WHERE steamid = ?",
            [$steamid]
        );
    }

    public function isCurrentSiteAdmin(): bool
    {
        if (!empty($_SESSION['user_admin'])) {
            return true;
        }

        $steamid = $_SESSION['steamid64'] ?? $_SESSION['steamid'] ?? '';

        return $this->issetSiteAdmin($steamid);
    }

    public function issetSiteAdmin($steamid): bool
    {
        $steamid64 = con_steam64((string) $steamid);
        if ($steamid64 === false || $steamid64 === '' || $steamid64 === '0') {
            return false;
        }

        $result = $this->Db->query(
            'Core',
            0,
            0,
            'SELECT id FROM lvl_web_admins WHERE steamid = ? LIMIT 1',
            [(string) $steamid64]
        );

        return !empty($result);
    }

    public function siteAdminSteamidsSet(array $steamids): array
    {
        $list = [];
        foreach ($steamids as $steamid) {
            $steamid64 = con_steam64((string) $steamid);
            if ($steamid64 !== false && $steamid64 !== '' && $steamid64 !== '0') {
                $list[(string) $steamid64] = true;
            }
        }
        $list = array_keys($list);
        if ($list == []) {
            return [];
        }
        $placeholders = RepositoryHelper::inPlaceholders(count($list));
        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT steamid FROM lvl_web_admins WHERE steamid IN (' . $placeholders . ')',
            $list
        );
        $set = [];
        foreach ($rows as $row) {
            if (empty($row['steamid'])) {
                continue;
            }
            $steamid64 = con_steam64((string) $row['steamid']);
            if ($steamid64 !== false && $steamid64 !== '') {
                $set[(string) $steamid64] = true;
            }
        }
        return $set;
    }

    public function getWarningsForTarget(string $targetSteamid): array
    {
        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT id, target_steamid, admin_steamid, reason, duration, created_at, updated_at, expires_at
                FROM neo_atools_warnings WHERE target_steamid = ? ORDER BY created_at DESC',
            [$targetSteamid]
        );

        return is_array($rows) ? $rows : [];
    }

    public function countActiveWarnings(string $targetSteamid): int
    {
        $row = $this->Db->query(
            'Core',
            0,
            0,
            'SELECT COUNT(*) FROM neo_atools_warnings WHERE target_steamid = ?
                AND (expires_at = 0 OR expires_at > ?)',
            [$targetSteamid, time()]
        );
        if (!is_array($row) || !isset($row['COUNT(*)'])) {
            return 0;
        }

        return $row['COUNT(*)'];
    }

    public function getWarningByIdForTarget(int $id, string $targetSteamid): ?array
    {
        $row = $this->Db->query(
            'Core',
            0,
            0,
            'SELECT id, target_steamid, admin_steamid, reason, duration, created_at, updated_at, expires_at
                FROM neo_atools_warnings WHERE id = ? AND target_steamid = ?',
            [$id, $targetSteamid]
        );
        if (!is_array($row) || empty($row['id'])) {
            return null;
        }

        return $row;
    }

    public function getWarningReasonsForTarget(string $targetSteamid, array $ids): array
    {
        $ids = RepositoryHelper::uniquePositiveIntIds($ids);
        if ($ids === []) {
            return [];
        }

        $placeholders = RepositoryHelper::inPlaceholders(count($ids));
        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT reason FROM neo_atools_warnings
                WHERE target_steamid = ? AND id IN (' . $placeholders . ')',
            array_merge([$targetSteamid], $ids)
        );

        if (!is_array($rows)) {
            return [];
        }

        $reasons = [];
        foreach ($rows as $row) {
            $reason = trim((string) ($row['reason'] ?? ''));
            if ($reason !== '') {
                $reasons[] = $reason;
            }
        }

        return $reasons;
    }

    public function insertWarning(string $target, string $adminSteam, string $reason, int $duration, int $created, int $updated, int $expiresAt): void
    {
        $this->Db->query(
            'Core',
            0,
            0,
            'INSERT INTO neo_atools_warnings (target_steamid, admin_steamid, reason, duration, created_at, updated_at, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$target, $adminSteam, $reason, $duration, $created, $updated, $expiresAt]
        );
    }

    public function expireWarningsForTarget(string $targetSteamid, array $ids): void
    {
        if ($ids == []) {
            return;
        }
        $placeholders = RepositoryHelper::inPlaceholders(count($ids));
        $params = array_merge([time(), time(), $targetSteamid], $ids, [time()]);
        $this->Db->query(
            'Core',
            0,
            0,
            'UPDATE neo_atools_warnings SET expires_at = ?, updated_at = ?
                WHERE target_steamid = ? AND id IN (' . $placeholders . ')
                AND (expires_at = 0 OR expires_at > ?)',
            $params
        );
    }

    public function deleteWarningsForTarget(string $targetSteamid, array $ids): void
    {
        if ($ids == []) {
            return;
        }
        $placeholders = RepositoryHelper::inPlaceholders(count($ids));
        $params = array_merge([$targetSteamid], $ids);
        $this->Db->query(
            'Core',
            0,
            0,
            'DELETE FROM neo_atools_warnings WHERE target_steamid = ? AND id IN (' . $placeholders . ')',
            $params
        );
    }

    public function updateWarning(int $id, string $targetSteamid, string $reason, int $duration, int $expiresAt, int $updated): void
    {
        $this->Db->query(
            'Core',
            0,
            0,
            'UPDATE neo_atools_warnings SET reason = ?, duration = ?, expires_at = ?, updated_at = ?
                WHERE id = ? AND target_steamid = ?',
            [$reason, $duration, $expiresAt, $updated, $id, $targetSteamid]
        );
    }

    public function getWarningsForTargets(array $targetSteamids): array
    {
        $list = RepositoryHelper::uniquePositiveIntIds($targetSteamids);
        if ($list == []) {
            return [];
        }
        $placeholders = RepositoryHelper::inPlaceholders(count($list));
        $rows = $this->Db->queryAll(
            'Core',
            0,
            0,
            'SELECT id, target_steamid, admin_steamid, reason, duration, created_at, updated_at, expires_at
                FROM neo_atools_warnings WHERE target_steamid IN (' . $placeholders . ')
                ORDER BY created_at DESC',
            $list
        );
        if (!is_array($rows)) {
            return [];
        }
        $byTarget = [];
        foreach ($rows as $row) {
            $tid = $row['target_steamid'];
            if (!isset($byTarget[$tid])) {
                $byTarget[$tid] = [];
            }
            $byTarget[$tid][] = $row;
        }

        return $byTarget;
    }
}
