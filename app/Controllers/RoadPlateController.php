<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventoryModel;
use App\Models\ProductModel;

class RoadPlateController extends BaseController
{
    private $inventoryModel;
    private $productModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $filter = $this->request->getGet();
        $page = $filter['page'] ?? 1;

        // 1. 取得所有鋪路鋼板產品清單 (用於表頭) - 改用 productModel
        $productRows = $this->productModel->getRoadPlateProducts();
        $products = array_column($productRows, 'pr_name');

        // 2. 取得分頁資料 (原始地點 x 產品 列表)
        $result = $this->inventoryModel->getRoadPlateList($filter, (int)$page);

        // 3. 樞紐轉換 (Pivot)
        // 格式: [ '地點名稱' => [ '產品A' => 數量, '產品B' => 數量 ], ... ]
        $locationData = [];
        foreach ($result['data'] as $row) {
            $locationData[$row['l_name']][$row['pr_name']] = $row['i_qty'];
        }

        $pagerData = [
            'currentPage' => $result['currentPage'],
            'totalPages' => $result['totalPages']
        ];

        return view('road_plate/index', [
            'products'     => $products,
            'locationData' => $locationData,
            'pager'        => $pagerData,
            'filter'       => $filter
        ]);
    }
}
