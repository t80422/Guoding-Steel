<?php

namespace App\Libraries;

use CodeIgniter\Files\File;
use Exception;

class FileManager
{
    private $uploadPath;
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct(string $uploadPath)
    {
        $this->uploadPath = $uploadPath;

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0777, true);
        }
    }

    /**
     * 上傳檔案
     *
     * @param array $fileKeys
     * @param array $files
     * @return array
     */
    public function uploadFiles(array $fileKeys, array $files): array
    {
        $newFileNames = [];
        foreach ($fileKeys as $key) {
            if (isset($files[$key]) && $files[$key]->isValid() && !$files[$key]->hasMoved()) {
                $file = $files[$key];
                $this->validateImageFile($file);
                $newFileNames[$key] = $this->uploadFile($file);
            } else {
                $newFileNames[$key] = null;
            }
        }
        return $newFileNames;
    }

    /**
     * 驗證圖片檔案
     *
     * @param File $file
     * @throws Exception
     */
    private function validateImageFile(File $file): void
    {
        $extension = strtolower($file->getExtension());
        if (!in_array($extension, $this->allowedTypes)) {
            throw new Exception('檔案格式不支援，僅允許：' . implode(', ', $this->allowedTypes));
        }

        // 驗證檔案的 MIME 類型
        $mimeType = $file->getMimeType();
        if (!str_starts_with($mimeType, 'image/')) {
            throw new Exception('上傳的檔案不是有效的圖片格式');
        }
    }

    /**
     * 上傳檔案
     *
     * @param File $file
     * @return string 檔名
     * @throws Exception
     */
    public function uploadFile(File $file): string
    {
        $newName = $file->getRandomName();
        if (!$file->move($this->uploadPath, $newName)) {
            throw new Exception('無法移動檔案');
        }
        return $newName;
    }

    /**
     * 刪除檔案
     *
     * @param string $fileName
     * @return void
     */
    public function deleteFile(string $fileName): void
    {
        if (empty($fileName)) {
            return;
        }
        
        $filePath = $this->uploadPath . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * 刪除多個檔案
     *
     * @param array $fileNames
     * @return void
     */
    public function deleteFiles(array $fileNames): void
    {
        foreach ($fileNames as $fileName) {
            if ($fileName) {
                $this->deleteFile($fileName);
            }
        }
    }
}
