<?php

namespace app\modules\module_block_main_lk_top\ext;

class LkTopExt
{
    protected $Db, $General, $Modules, $Translate;

    public function __construct($Db, $General, $Modules, $Translate)
    {
        $this->Db = $Db;
        $this->General = $General;
        $this->Modules = $Modules;
        $this->Translate = $Translate;
    }

    public function LkTopInfo()
    {
        $lk_top = [
            'lktop7d' => [],
            'lktop30d' => [],
            'lktopAll' => [],
            'Time' => time() + 3600,
        ];

        $data = $this->Modules->get_module_cache('module_block_main_lk_top', 'lk_top');
        if (time() > ($data['Time'] ?? 0)) {
            if (!empty($this->Db->db_data['lk'])) {
                $lk_top['lktop7d'] = $this->Db->queryAll('lk', 0, 0, "SELECT `pay_auth`, SUM(`pay_summ`) AS total_sum
                                                                    FROM `lk_pays`
                                                                    WHERE STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s') >= CURDATE() - INTERVAL 7 DAY
                                                                    AND `pay_status` = 1 AND `pay_system` != 'admin'
                                                                    GROUP BY `pay_auth`
                                                                    ORDER BY total_sum DESC
                                                                    LIMIT 3;");
                $lk_top['lktop30d'] = $this->Db->queryAll('lk', 0, 0, "SELECT `pay_auth`, SUM(`pay_summ`) AS total_sum
                                                                    FROM `lk_pays`
                                                                    WHERE STR_TO_DATE(`pay_data`, '%d.%m.%Y %H:%i:%s') >= CURDATE() - INTERVAL 30 DAY
                                                                    AND `pay_status` = 1 AND `pay_system` != 'admin'
                                                                    GROUP BY `pay_auth`
                                                                    ORDER BY total_sum DESC
                                                                    LIMIT 3;");
                $lk_top['lktopAll'] = $this->Db->queryAll('lk', 0, 0, "SELECT `auth` as `pay_auth`, SUM(`all_cash`) AS total_sum
                                                                    FROM `lk`
                                                                    WHERE `all_cash` != 0
                                                                    GROUP BY `auth`
                                                                    ORDER BY total_sum DESC
                                                                    LIMIT 3;");
            }
            $this->Modules->set_module_cache('module_block_main_lk_top', $lk_top, 'lk_top');
        }
        return $data;
    }

    private function blank($pos)
    {
        return [
            'steam' => '',
            'avatar' => '/storage/cache/img/avatars/1_avatar.jpg',
            'check_avatar' => 0,
            'name' => 'TOP ' . $pos,
            'pos' => $pos,
            'pos_class' => $this->position($pos),
            'pos_text' => $this->Translate->get_translate_module_phrase('module_block_main_lk_top', '_place')
        ];
    }

    public function Render($type)
    {
        $lk_top = $this->LkTopInfo();
        if (!isset($lk_top[$type])) {
            return [];
        }
        $data = [];
        $count = 0;
        foreach ($lk_top[$type] as $position => $row) {
            $steam64 = con_steam32to64($row['pay_auth']);
            $pos = $position + 1;
            $pos_class = $this->position($pos);
            $avatar = $this->General->getAvatar($steam64, 3);
            $check_avatar = $this->General->checkAvatar($steam64);
            $name = $this->General->checkName($steam64);
            $pos_text = $this->Translate->get_translate_module_phrase('module_block_main_lk_top', '_place');
            $data[] = [
                'steam' => $steam64,
                'avatar' => $avatar,
                'check_avatar' => $check_avatar,
                'name' => $name,
                'pos' => $pos,
                'pos_class' => $pos_class,
                'pos_text' => $pos_text
            ];
            $count++;
        }
        for ($i = $count + 1; $i <= 3; $i++) {
            $data[] = $this->blank($i);
        }
        return ['status' => 'success', 'data' => $data];
    }

    public function position($data)
    {
        switch ($data) {
            case '1':
                return 'first';
            case '2':
                return 'second';
            case '3':
                return 'third';
        }
    }
}
