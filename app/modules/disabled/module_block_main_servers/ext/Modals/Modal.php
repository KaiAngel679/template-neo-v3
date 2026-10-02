<?php
namespace app\modules\module_block_main_servers\ext\Modals;

class Modal
{
    private object $Translate;

    public function __construct(object $Translate)
    {
        $this->Translate = $Translate;
    }

    public function Render(array $players)
    {
        if (empty($players)) {
            return $this->Empty();
        } else {
            return $this->Data($players);
        }
    }

    private function Empty()
    {
        return [
            'admins' => 0,
            'winteam' => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_unknown'),
            'score_ct' => 0,
            'score_t' => 0,
            'players' => []
        ];
    }

    private function Data(array $players)
    {
        $data['winteam'] = $this->Translate->get_translate_module_phrase('module_block_main_servers', '_noDataAvailable');
        $data['score_ct'] = 0;
        $data['score_t'] = 0;

        if (empty($players)) {
            $data['admins'] = 0;
            $data['players'] = [];
        } else {
            $data['admins'] = 0;
            $data['players'] = $this->Players($players);
        }
        return $data;
    }

    private function Players(array $players)
    {
        $rows = [];
        foreach ($players as $index => $player) {
            $rows[] = [
                'name' => action_text_clear($player['Name']),
                'frags' => $player['Frags'],
                'time' => $player['TimeF']
            ];
        }
        return $rows;
    }
}
