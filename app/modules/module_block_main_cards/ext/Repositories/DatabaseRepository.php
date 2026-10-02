<?php

namespace app\modules\module_block_main_cards\ext\Repositories;

class DatabaseRepository
{
    protected $Db;

    public function __construct($Db)
    {
        $this->Db = $Db;
    }

    public function getLast10Type(): array
    {
        return $this->Db->queryAll('Core', 0, 0, "SELECT h.steamid, h.reward_id, r.rare, r.type, r.count FROM neo_cards_history h JOIN neo_cards_rewards r ON h.reward_id = r.id ORDER BY h.created_at DESC LIMIT 10");
    }

    public function getLast10TypeRare(): array
    {
        return $this->Db->queryAll('Core', 0, 0, "SELECT h.steamid, h.reward_id, r.rare, r.type, r.count FROM neo_cards_history h JOIN neo_cards_rewards r ON h.reward_id = r.id WHERE r.rare IN (1, 2) ORDER BY h.created_at DESC LIMIT 10");
    }
}
