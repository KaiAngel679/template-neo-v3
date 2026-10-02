<?php

namespace app\modules\module_page_cards\ext\Repositories;

class CardsRepository
{
    public function random(array $filteredRewards): array
    {
        do {
            $roll = mt_rand(0, 100);
            $candidates = array_filter($filteredRewards, function ($reward) use ($roll) {
                return (int)$reward['chance'] > 0 && $roll <= (int)$reward['chance'];
            });
        } while (!$candidates);
        return $candidates[array_rand($candidates)];
    }


    public function filteredRewards(int $streak, array $rewards): array
    {
        $rare = $streak < 5 ? 0 : ($streak < 7 ? 1 : 2);
        return array_values(array_filter($rewards, function ($reward) use ($rare) {
            return $reward['rare'] == $rare;
        }));
    }
}
