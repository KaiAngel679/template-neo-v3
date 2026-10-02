<?php

namespace app\modules\module_page_steamfinder\ext\Services;

class AdditionalService
{
    protected $Translate;

    public function __construct($Translate)
    {
        $this->Translate = $Translate;
    }

    public function getUserStatus($state)
    {
        $statuses = [
            0 => $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_offline'),
            1 => $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_online'),
            2 => $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_busy'),
            3 => $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_moved'),
            4 => $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_sleep'),
            5 => $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_tardeWants'),
            6 => $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_playWants')
        ];

        return $statuses[$state] ?? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown');
    }

    public function getCreatedTime($time)
    {
        return isset($time) ? date('d.m.Y H:i', $time) . ' (' . $this->convertTimestamp(time() - $time) . ')' : $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown');
    }

    public function getLastLogOff($time)
    {
        return isset($time) ? date('d.m.Y H:i', $time) : $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown');
    }

    public function getPrivacyState($id)
    {
        return $id == 'public' ? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_public') : $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_private');
    }

    public function checkUnknown($count)
    {
        return isset($count) ? $count : $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown');
    }

    public function getCommunityBan($id)
    {
        return $id ? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_yesCom') : $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_no');
    }

    public function getVacBan($vac, $count, $day)
    {
        return $vac ? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_vacCount') . $count . $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_vacLast') . $day . $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_days)') : $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_no');
    }

    public function getGameBan($count)
    {
        return $count > 0 ? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_yesGame') . $count . ")" : $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_no');
    }

    public function hoursConverter($hour)
    {
        return empty($hour) ? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown') : $hour . ' ' . $this->Translate->get_translate_phrase('_Hour');
    }

    public function countConverter($count)
    {
        return empty($count) ? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown') : $count . ' ' . $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_pcs');
    }

    public function getEconomyBanStatus($economyBan)
    {
        switch ($economyBan) {
            case 'none':
                return $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_noRestrictions');
            case 'probation':
                return $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_limitedMode');
            case 'banned':
                return $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_fullLockdown');
            default:
                return $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown');
        }
    }

    public function getTradeStatus($economyBan)
    {
        return $economyBan == 'probation' ? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_tradeLim') : $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_tradeAll');
    }

    public function getOverallBanStatus($ban)
    {
        return $ban == 'clean' ? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_accClean') : $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_restrictionsFound');
    }

    public function getProfileStateText($state)
    {
        $states = [
            0 => $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_profileYes'),
            1 => $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_profileNo'),
            null => $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown')
        ];

        return $states[$state] ?? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_unknown');
    }

    public function formatLastMatches($matches)
    {
        $html = '';
        if ($matches) {
            foreach ($matches as $match) {
                $class = ($match == 'W') ? 'sf__faceit-win' : 'sf__faceit-lose';
                $html .= '<span class="' . $class . '">' . $match . '</span>';
            }
        } else {
            $html = <<<HTML
                <span class="sf__faceit-win">N</span>
                <span class="sf__faceit-win">N</span>
                <span class="sf__faceit-win">N</span>
                <span class="sf__faceit-win">N</span>
                <span class="sf__faceit-win">N</span>
            HTML;
        }

        return $html;
    }

    private function convertTimestamp($seconds)
    {
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $days = floor($hours / 24);

        $years = floor($days / 365);
        $remainingDays = $days % 365;

        $months = floor($remainingDays / 30);
        $remainingDays = $remainingDays % 30;

        $pluralize = function ($number, $forms) {
            $cases = [2, 0, 1, 1, 1, 2];
            return $number . ' ' . $forms[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
        };

        $parts = [];
        if ($years > 0) {
            $parts[] = $pluralize($years, [$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_year'), $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_yearss'), $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_years')]);
        }
        if ($months > 0) {
            $parts[] = $pluralize($months, [$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_monss'), $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_mons'), $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_mon')]);
        }
        if ($remainingDays > 0) {
            $parts[] = $pluralize($remainingDays, [$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_dayNNN'), $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_dayNN'), $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_dayN')]);
        }

        return implode(', ', $parts) ?: $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_minDay');
    }
}
