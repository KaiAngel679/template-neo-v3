<?php

namespace app\modules\module_page_atools\ext\Repositories;

final class RepositoryHelper
{
    public static function uniqueNonEmptyStrings(array $values): array
    {
        $unique = [];
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                $unique[$value] = true;
            }
        }

        return array_keys($unique);
    }

    public static function uniquePositiveIntIds(array $ids): array
    {
        $unique = [];
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $unique[$id] = true;
            }
        }

        return array_keys($unique);
    }

    public static function inPlaceholders(int $count): string
    {
        if ($count <= 0) {
            return '';
        }

        return implode(',', array_fill(0, $count, '?'));
    }

    public static function sqlExcludeAdminSystemConsole(string $alias = 'a'): string
    {
        return " AND {$alias}.id != 1";
    }

    public static function sqlExcludeIksAdminConsole(string $alias = 'a'): string
    {
        return " AND {$alias}.id != 1";
    }

    public static function sqlExcludeSourceBansConsole(string $alias = 'a'): string
    {
        return " AND {$alias}.aid != 0";
    }

    public static function sqlExcludeInvalidPlayerSteamid(string $column): string
    {
        return " AND {$column} IS NOT NULL AND {$column} != '' AND {$column} != '0' AND {$column} != 'STEAM_ID_SERVER'";
    }
}
