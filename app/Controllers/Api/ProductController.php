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

    public function getProductsByMinorCategoryId($minorCategoryId)
    {
        $product = $this->productModel->getNames($minorCategoryId);
        return $this->response->setJSON($product);
    }
}
