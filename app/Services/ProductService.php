<?php

namespace App\Services;

use App\Models\ProductModel;

class ProductService
{
    protected $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    /**
     * 儲存產品資料
     * 
     * @param array $data 產品資料
     * @param int $userId 使用者ID
     * @return bool
     */
    public function save($data, $userId)
    {
        // 處理 checkbox：如果沒有被勾選，設定為 0
        $data['pr_is_length'] = $data['pr_is_length'] ?? 0;

        if (empty($data['pr_id'])) {
            $data['pr_create_by'] = $userId;
        } else {
            $data['pr_update_by'] = $userId;
            $data['pr_update_at'] = date('Y-m-d H:i:s');
        }

        return $this->productModel->save($data);
    }

    /**
     * 根據小分類ID建立無型號產品
     * 
     * @param int $minorCategoryId 小分類ID
     * @param int $userId 使用者ID
     * @return bool
     */
    public function createNoModelProduct($minorCategoryId, $userId,$productName)
    {
        $data = [
            'pr_name' => $productName,
            'pr_mic_id' => $minorCategoryId
        ];

        return $this->save($data, $userId);
    }
} 