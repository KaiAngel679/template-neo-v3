<?php 

namespace app\modules\module_page_faq\ext\Repositories;

class FileRepository {
    public function getCache()
    {
        $file = MODULES . "module_page_faq/assets/cache/faq.json";
        if (file_exists($file)) {
            $json = json_decode(file_get_contents($file), true);
        }

        return $json;
    }

    private function putCache($data, $id = null)
    {
        $file = MODULES . "module_page_faq/assets/cache/faq.json";
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

    public function created($title, $text) {
        $data = [];
        $data = $this->getCache();
        $newId = !empty($data) ? max(array_keys($data)) + 1 : 1;
        $faq = [
            'title' => $title,
            'text' => $text
        ];
        $this->putCache($faq, $newId);
        return ['status' => 'success', 'url' => 'reload'];
    }

    public function delete($id)
    {
        $data = $this->getCache();
        if (isset($data[$id])) {
            unset($data[$id]);
            $this->putCache($data);
            return ['status' => 'success'];
        }
        return ['status' => 'error'];
    }

    public function modal($id)
    {
        $data = $this->getCache();
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if (isset($data[$id])) {
            return ['status' => 'success', 'data' => $data[$id]];
        }
        return ['status' => 'error'];
    }

    public function edit($id, $title, $text)
    {
        $data = $this->getCache();
        if (isset($data[$id])) {
            $data[$id] = [
                'title' => $title,
                'text' => $text
            ];
            $this->putCache($data);
            return ['status' => 'success'];
        }
        return ['status' => 'error'];
    }
}