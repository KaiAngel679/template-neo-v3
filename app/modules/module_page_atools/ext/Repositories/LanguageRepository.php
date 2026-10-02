<?php

namespace app\modules\module_page_atools\ext\Repositories;

class LanguageRepository
{
    public function translate(array $json): string
    {
        $lang = strtolower($_SESSION['language'] ?? 'ru');

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else {
            $data = $json;
        }

        return $data[$lang] ?? $data['ru'] ?? '';
    }
}
