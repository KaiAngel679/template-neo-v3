<?php

namespace app\modules\module_page_checker\ext\Repositories;

class TextareaRepository
{
    public function textareaConvert($description)
    {
        $textMark = str_replace("\\n", "\n", $description);
        $lines = explode("\n", $textMark);
        $result = '';
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if ($trimmedLine !== '') {
                $result .= "$trimmedLine\n";
            }
        }
        return $result;
    }

    public function formatParagraph($title, $text)
    {
        $title = trim($title);
        $html = '';
        if ($title !== '') {
            $html .= '<h3 class="checker__h3">' . action_text_clear($title) . '</h3>';
        }
        $text = str_replace("\\n", "\n", $text);
        $lines = explode("\n", $text);
        $items = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $items[] = '<li>' . action_text_clear($line) . '</li>';
        }
        if ($items) {
            $html .= '<ul>' . implode('', $items) . '</ul>';
        }
        return $html;
    }

    public function formatParagraphs($paragraphs)
    {
        $out = '';
        foreach ($paragraphs as $p) {
            if (!is_array($p)) continue;
            $t = $p['title'] ?? '';
            $tx = $p['text'] ?? '';
            if (trim($t) === '' && trim($tx) === '') continue;
            $out .= $this->formatParagraph($t, $tx);
        }
        return $out;
    }
}
