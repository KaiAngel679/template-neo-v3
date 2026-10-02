<?php

namespace app\modules\module_page_atools\ext\Helpers;

use app\modules\module_page_atools\ext\Repositories\AdminDrivers\AdminDriverFactory;

final class GameBackendResolver
{
    public const GAME_CS2 = 'cs2';
    public const GAME_CSGO = 'csgo';

    private $Db;

    public function __construct(object $Db)
    {
        $this->Db = $Db;
    }

    public function isCs2Connected(): bool
    {
        return AdminDriverFactory::resolveCs2Backend($this->Db) !== null;
    }

    public function getCs2Backend(): ?string
    {
        return AdminDriverFactory::resolveCs2Backend($this->Db);
    }

    public function isAdminSystemCs2(): bool
    {
        return $this->getCs2Backend() === 'AdminSystem';
    }

    public function isIksAdminCs2(): bool
    {
        return $this->getCs2Backend() === 'IksAdminNew';
    }

    public function isCsgoConnected(): bool
    {
        return !empty($this->Db->db_data['SourceBans']);
    }

    public function isGameConnected(string $game): bool
    {
        if ($game === self::GAME_CS2) {
            return $this->isCs2Connected();
        }

        if ($game === self::GAME_CSGO) {
            return $this->isCsgoConnected();
        }

        return false;
    }

    public function cs2AdminReloadCommand(string $steamid64): string
    {
        if ($this->isAdminSystemCs2()) {
            return "mm_as_reload_admin {$steamid64}";
        }

        return 'css_am_reload';
    }

    public function cs2PunishReloadCommand(string $steamid64): string
    {
        if ($this->isAdminSystemCs2()) {
            return "mm_as_reload_punish {$steamid64}";
        }

        return "css_reload_infractions {$steamid64}";
    }

    public function csgoAdminReloadCommand(): string
    {
        return 'ma_rehashadm';
    }
}
