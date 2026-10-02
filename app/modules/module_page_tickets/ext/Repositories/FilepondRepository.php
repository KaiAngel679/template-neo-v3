<?php

namespace app\modules\module_page_tickets\ext\Repositories;

class FilepondRepository
{
    public function sendPhoto($file)
    {
        $directory = MODULES . 'module_page_tickets/assets/img/img_tmp/';
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
            chmod($directory, 0777);
        }

        $fileName = time() . '-' . basename($file['name']);
        $filePath = $directory . $fileName;
        $compressedImage = $this->compressImage($file['tmp_name'], $filePath);

        if ($compressedImage) {
            $this->removeOldPhotoTmp();
            return ['status' => 'success', 'file' => $fileName];
        } else {
            return ['status' => 'error'];
        }
    }

    private function compressImage($source, $destination)
    {
        $imageInfo = getimagesize($source);
        $mime = $imageInfo['mime'];

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($source);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($source);
                break;
            default:
                return false;
        }

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($image, $destination, 50);
                break;
            case 'image/png':
                imagepng($image, $destination, 1);
                break;
            case 'image/gif':
                imagegif($image, $destination);
                break;
            case 'image/webp':
                imagewebp($image, $destination, 50);
                break;
            default:
                return false;
        }

        imagedestroy($image);

        return true;
    }

    public function removePhoto($file)
    {
        $filePath = MODULES . 'module_page_tickets/assets/img/img_tmp/' . $file;
        if (file_exists($filePath)) {
            unlink($filePath);
            return ['status' => 'success'];
        } else {
            return ['status' => 'error'];
        }
    }

    public function transferPhoto($file)
    {
        $photos = explode(';', $file);
        foreach ($photos as $photo) {
            $tempPath = MODULES . 'module_page_tickets/assets/img/img_tmp/' . $photo;
            $finalDir = MODULES . 'module_page_tickets/assets/img/img_chat/' . $photo;
            if (file_exists($tempPath)) {
                if (copy($tempPath, $finalDir)) {
                    unlink($tempPath);
                }
            }
        }
    }

    private function removeOldPhotoTmp()
    {
        foreach (glob(MODULES . 'module_page_tickets/assets/img/img_tmp/' . '*') as $file) {
            if (is_file($file) && (time() - filemtime($file)) > 3600 * 24) {
                unlink($file);
            }
        }
    }

    public function deletePhoto($file)
    {
        $filePath = MODULES . 'module_page_tickets/assets/img/img_chat/' . $file;
        if (file_exists($filePath)) {
            unlink($filePath);
            return ['status' => 'success'];
        } else {
            return ['status' => 'error'];
        }
    }
}
