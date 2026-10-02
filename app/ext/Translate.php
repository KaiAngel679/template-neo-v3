<?php

namespace app\ext;

class Translate
{
    public $arr_translations = [];
    public $arr_languages = [];
    public $arr_languages_count = [];

    function __construct()
    {

        defined('IN_LR') != true && die();

        $this->arr_translations = $this->get_arr_translations();

        $this->arr_languages = $this->get_arr_languages();

        $this->arr_languages_count = sizeof($this->arr_languages);
    }

    public function get_arr_translations()
    {
        $cacheFile = SESSIONS . 'translator.json';
        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true);
        } else {
            return $this->create_translator_cache();
        }
    }

    public function create_translator_cache()
    {
        $result_translation = [];

        $scan_modules = array_diff(scandir(MODULES, 1), array('..', '.', 'disabled'));

        $scan_ranks_pack = array_diff(scandir(RANKS_PACK, 1), array('..', '.'));

        for ($i = 0, $c = sizeof($scan_modules); $i < $c; $i++) {
            $result[$scan_modules[$i]] = json_decode(file_get_contents(MODULES . $scan_modules[$i] . '/description.json'), true);

            if (array_key_exists('translation', $result[$scan_modules[$i]]['setting']) && $result[$scan_modules[$i]]['setting']['translation'] == 1) {
                $result_translation[$scan_modules[$i]] = json_decode(file_get_contents(MODULES . $scan_modules[$i] . '/translation.json'), true);
            }
        }

        for ($i = 0, $c = sizeof($scan_ranks_pack); $i < $c; $i++):
            $rank_pack['ranks_' . $scan_ranks_pack[$i]] = json_decode(file_get_contents(RANKS_PACK . $scan_ranks_pack[$i] . '/title.json'), true);
        endfor;

        if (isset($rank_pack)) {
            $result_translation += $rank_pack;
        }

        $result_translation += require SESSIONS . 'translator.php';

        $jsonPath = SESSIONS . 'translator.json';
        file_put_contents($jsonPath, json_encode($result_translation, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $jsPath = ASSETS_JS . 'translations.js';
        $jsContent = 'window.TRANSLATIONS=' . json_encode($result_translation, JSON_UNESCAPED_UNICODE) . ';';
        file_put_contents($jsPath, $jsContent);

        return $result_translation;
    }

    public function get_translate_phrase($phrase, $group = '')
    {
        if (empty($group)):
            if (empty($this->arr_translations[$phrase][$_SESSION['language']])):
                return empty($this->arr_translations[$phrase]['EN']) ? 'No Translation' : $this->arr_translations[$phrase]['EN'];
            else:
                return $this->arr_translations[$phrase][$_SESSION['language']];
            endif;
        else:
            if (empty($this->arr_translations[$group][$phrase][$_SESSION['language']])):
                return empty($this->arr_translations[$group][$phrase]['EN']) ? 'No Translation' : $this->arr_translations[$group][$phrase]['EN'];
            else:
                return $this->arr_translations[$group][$phrase][$_SESSION['language']];
            endif;
        endif;
    }

    public function get_translate_module_phrase($module_id, $phrase)
    {
        if (empty($this->arr_translations[$module_id][$phrase][$_SESSION['language']])):
            return empty($this->arr_translations[$module_id][$phrase]['EN']) ? 'No Translation' : $this->arr_translations[$module_id][$phrase]['EN'];
        else:
            return $this->arr_translations[$module_id][$phrase][$_SESSION['language']];
        endif;
    }

    public function get_arr_translate_module($module_id)
    {
        return empty($this->arr_translations[$module_id]) ? [] : $this->arr_translations[$module_id];
    }
    
    public function get_arr_languages()
    {
        return file_exists(SESSIONS . 'languages.json') ? json_decode(file_get_contents(SESSIONS . 'languages.json'), true) : ['EN', 'RU'];
    }
}
