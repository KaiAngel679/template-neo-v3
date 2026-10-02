<?php

namespace app\modules\module_page_tickets\ext\Repositories;

class SendRepository
{
    public function textareaConvert($description)
    {
        $textMark = str_replace("\\n", "\n", $description);
        $lines = explode("\n", $textMark);
        $result = '';
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if ($trimmedLine !== '') {
                $result .= "<p class='ticket__embed-string'>$trimmedLine</p>\n";
            }
        }

        return $result;
    }

    public function filterServersId($servers, $idsString)
    {
        $requiredIds = explode(';', $idsString);
        $requiredIds = array_map('strval', $requiredIds);
        $filteredServers = array_filter($servers, function ($server) use ($requiredIds) {
            return in_array($server['id'], $requiredIds);
        });
        return array_values($filteredServers);
    }
}
