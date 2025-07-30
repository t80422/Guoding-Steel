<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class ProductController extends BaseController
{
    protected $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function getProductList($minorCategoryId)
    {
        $product = $this->productModel->getByMinorCategoryId($minorCategoryId);
        $data = [];
        foreach ($product as $item) {
            $data[] = [
                'pr_id' => $item['pr_id'],
                'pr_name' => $item['pr_name'],
                'pr_weight' => $item['pr_weight'],
                'pr_is_length' => $item['pr_is_length'],
            ];
        }
        return $this->response->setJSON($data);
    }
}
