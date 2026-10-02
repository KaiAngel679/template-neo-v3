<?php

namespace app\modules\module_page_atools\ext\Formatters;

final class ChecksVerdictFormatter
{
    public static function label(array $row, bool $isIks, object $Translate): string
    {
        if ($isIks) {
            $code = (int) ($row['check_result'] ?? -1);
            if ($code === 4 || $code === 5) {
                $reason = trim((string) ($row['verdict'] ?? ''));

                return $reason !== ''
                    ? $reason
                    : $Translate->get_translate_module_phrase('module_page_atools', '_at_reason' . $code . 'IksAdminNew');
            }

            return $Translate->get_translate_module_phrase('module_page_atools', '_at_reason' . $code . 'IksAdminNew');
        }

        return (string) ($row['verdict'] ?? '');
    }

    public static function dropdownEntry(array $row, bool $isIks, object $Translate): ?array
    {
        if ($isIks) {
            $code = (int) ($row['check_result'] ?? -1);
            $reason = trim((string) ($row['result_reason'] ?? $row['verdict'] ?? ''));

            if ($code === 4 || $code === 5) {
                if ($reason === '') {
                    return [
                        'id' => $code,
                        'name' => $Translate->get_translate_module_phrase('module_page_atools', '_at_reason' . $code . 'IksAdminNew'),
                    ];
                }

                return ['id' => $reason, 'name' => $reason];
            }

            return [
                'id' => $code,
                'name' => $Translate->get_translate_module_phrase('module_page_atools', '_at_reason' . $code . 'IksAdminNew'),
            ];
        }

        $name = trim((string) ($row['verdict'] ?? ''));
        if ($name === '') {
            return null;
        }

        return ['id' => $name, 'name' => $name];
    }
}
