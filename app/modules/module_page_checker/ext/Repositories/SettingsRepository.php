<?php

namespace app\modules\module_page_checker\ext\Repositories;

use app\modules\module_page_checker\ext\Repositories\FileRepository;
use app\modules\module_page_checker\ext\Repositories\FilepondRepository;

class SettingsRepository
{
    protected $fr, $fpr;

    public function __construct()
    {
        $this->fr = new FileRepository;
        $this->fpr = new FilepondRepository;
    }

    public function saveOne($auth, $vt, $ft, $name, $description, $file)
    {
        $oldData = $this->fr->getCache('settings');
        $oldFile = isset($oldData['file']) ? trim($oldData['file']) : '';
        $finalFile = $file !== '' ? $file : $oldFile;
        $newData = array_merge($oldData ?: [], [
            "auth" => $auth,
            "url_vt"  => $vt,
            "url_ft" => $ft,
            "name_checker" => $name,
            "description_checker" => $description,
            "file" => $finalFile
        ]);
        $this->fr->putCache($newData, 'settings');
        if ($file !== '' && $file !== $oldFile) {
            $this->fpr->transfer($file, 'file');
            if ($oldFile) {
                $this->fpr->remove($oldFile, 'file', 'ready');
            }
        }
    }

    public function saveTwo($slider, $paragraphs, $img)
    {
        $oldData = $this->fr->getCache('settings') ?: [];
        if (is_string($paragraphs)) {
            $paragraphsArr = json_decode($paragraphs, true);
            if (!is_array($paragraphsArr)) {
                $paragraphsArr = [];
            }
        } else {
            $paragraphsArr = $paragraphs;
        }
        $paragraphsArr = array_values(array_filter($paragraphsArr, function ($p) {
            if (!is_array($p)) return false;
            $title = isset($p['title']) ? trim($p['title']) : '';
            $text  = isset($p['text']) ? trim($p['text']) : '';
            return $title !== '' || $text !== '';
        }));
        $oldImgsStr = isset($oldData['img']) ? trim($oldData['img']) : '';
        $oldImgs = $oldImgsStr !== '' ? array_filter(explode(';', $oldImgsStr)) : [];
        if ($img === '') {
            $finalImgsStr = $oldImgsStr;
        } else {
            if (!empty($oldImgs)) {
                foreach ($oldImgs as $old) {
                    $this->fpr->remove($old, 'img', 'ready');
                }
            }
            $this->fpr->transfer($img, 'img');
            $finalImgsStr = $img;
        }
        $newData = array_merge($oldData, [
            'slider' => $slider,
            'paragraphs' => $paragraphsArr,
            'img' => $finalImgsStr,
        ]);
        $this->fr->putCache($newData, 'settings');
    }

    public function delete($type)
    {
        $settings = $this->fr->getCache('settings') ?: [];
        if ($type === 'file') {
            $oldFile = isset($settings['file']) ? trim($settings['file']) : '';
            if ($oldFile !== '') {
                $this->fpr->remove($oldFile, 'file', 'ready');
            }
            $settings['file'] = '';
        } elseif ($type === 'img') {
            $oldImgsStr = isset($settings['img']) ? trim($settings['img']) : '';
            if ($oldImgsStr !== '') {
                foreach (array_filter(explode(';', $oldImgsStr)) as $img) {
                    $this->fpr->remove($img, 'img', 'ready');
                }
            }
            $settings['img'] = '';
        }

        $this->fr->putCache($settings, 'settings');
    }
}
