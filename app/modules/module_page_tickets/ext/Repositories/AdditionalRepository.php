<?php

namespace app\modules\module_page_tickets\ext\Repositories;

class AdditionalRepository
{
    protected $Translate;

    public function __construct($Translate)
    {
        $this->Translate = $Translate;
    }

    public function formatTicketTime($seconds)
    {
        if ($seconds < 60) {
            return $this->Translate->get_translate_module_phrase('module_page_tickets', '_lessMinute');
        }

        $minutes = floor($seconds / 60);
        $hours = floor($seconds / 3600);
        $days = floor($seconds / 86400);

        if ($days > 0) {
            $remainingHours = floor(($seconds % 86400) / 3600);
            return $days . $this->Translate->get_translate_module_phrase('module_page_tickets', '_days') . $remainingHours . $this->Translate->get_translate_module_phrase('module_page_tickets', '_hour');
        }

        if ($hours > 0) {
            $remainingMinutes = floor(($seconds % 3600) / 60);
            return $hours . $this->Translate->get_translate_module_phrase('module_page_tickets', '_hour') . $remainingMinutes . $this->Translate->get_translate_module_phrase('module_page_tickets', '_minute');
        }

        return $minutes . $this->Translate->get_translate_module_phrase('module_page_tickets', '_minute');
    }

    public function isValidSteamId($steam)
    {
        return !empty($steam) && preg_match('/^7656\d{13}$/', $steam);
    }
}
