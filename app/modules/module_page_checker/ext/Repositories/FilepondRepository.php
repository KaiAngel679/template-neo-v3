<?php

namespace app\modules\module_page_checker\ext\Repositories;

class FilepondRepository
{
    public function send($file, $type)
    {
        if ($type == 'file') {
            $directory = MODULES . 'module_page_checker/assets/download/download_tmp/';
        } else {
            $directory = MODULES . 'module_page_checker/assets/img/slide_tmp/';
        }

        if (!file_exists($directory)) {
            return ['status' => 'error'];
        }

        $fileName = time() . '-' . basename($file['name']);
        $filePath = $directory . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['status' => 'success', 'file' => $fileName];
        } else {
            return ['status' => 'error'];
        }
    }

    public function transfer($file, $type)
    {
        $photos = explode(';', $file);
        if ($type == 'file') {
            foreach ($photos as $photo) {
                $tempPath = MODULES . 'module_page_checker/assets/download/download_tmp/' . $photo;
                $finalDir = MODULES . 'module_page_checker/assets/download/download_ready/' . $photo;
                if (file_exists($tempPath)) {
                    if (copy($tempPath, $finalDir)) {
                        unlink($tempPath);
                    }
                }
            }
        } else {
            foreach ($photos as $photo) {
                $tempPath = MODULES . 'module_page_checker/assets/img/slide_tmp/' . $photo;
                $finalDir = MODULES . 'module_page_checker/assets/img/slide_img/' . $photo;
                if (file_exists($tempPath)) {
                    if (copy($tempPath, $finalDir)) {
                        unlink($tempPath);
                    }
                }
            }
        }
    }

    public function remove($file, $type, $status)
    {
        if ($type == 'file') {
            if ($status == 'tmp') {
                $filePath = MODULES . 'module_page_checker/assets/download/download_tmp/' . $file;
            } else {
                $filePath = MODULES . 'module_page_checker/assets/download/download_ready/' . $file;
            }
        } else {
            if ($status == 'tmp') {
                $filePath = MODULES . 'module_page_checker/assets/img/slide_tmp/' . $file;
            } else {
                $filePath = MODULES . 'module_page_checker/assets/img/slide_img/' . $file;
            }
        }
        if (file_exists($filePath)) {
            unlink($filePath);
            return ['status' => 'success'];
        } else {
            return ['status' => 'error'];
        }
    }
}
