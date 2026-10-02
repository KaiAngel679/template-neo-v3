<?php

namespace app\modules\module_page_atools\ext\Repositories\AdminDrivers;

class AdminDriverFactory
{
    public static function resolveCs2Backend(object $db): ?string
    {
        if (!empty($db->db_data['AdminSystem'])) {
            return 'AdminSystem';
        }
        if (!empty($db->db_data['IksAdminNew'])) {
            return 'IksAdminNew';
        }

        return null;
    }

    public static function resolve(object $db, string $gameType): ?AdminDriverInterface
    {
        switch ($gameType) {
            case 'cs2':
                $backend = self::resolveCs2Backend($db);
                if ($backend === 'AdminSystem') {
                    return new AdminSystemDriver($db);
                }
                if ($backend === 'IksAdminNew') {
                    return new IksAdminDriver($db);
                }

                return null;
            case 'csgo':
                if (!empty($db->db_data['SourceBans'])) {
                    return new SourceBansAdminDriver($db);
                }

                return null;
            default:
                return null;
        }
    }

    public static function resolveChecks(object $db): ?AdminDriverInterface
    {
        return self::resolve($db, 'cs2');
    }
}
