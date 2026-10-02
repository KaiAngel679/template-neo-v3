<?php

namespace app\modules\module_page_checker\ext\Repositories;

class FileRepository
{
    public function getCache($name)
    {
        $file = MODULES . "module_page_checker/assets/cache/$name.json";
        if (file_exists($file)) {
            $json = json_decode(file_get_contents($file), true);
        }

        return $json;
    }

    public function putCache($data, $name, $id = null)
    {
        $file = MODULES . "module_page_checker/assets/cache/$name.json";
        $jsonData = [];
        if ($id !== null && file_exists($file)) {
            $jsonData = json_decode(file_get_contents($file), true) ?? [];
        }
        if ($id !== null) {
            $jsonData[$id] = $data;
        } else {
            $jsonData = $data;
        }
        $json = json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return file_put_contents($file, $json) !== false;
    }
}
