<?php

namespace App\Services;

use App\Models\OrderModel;
use App\Models\RentalOrderModel;

class LocationMaterialService
{
    private $orderModel;
    private $rentalOrderModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->rentalOrderModel = new RentalOrderModel();
    }

    /**
     * 取得工地用料情況（整合一般訂單和租賃單）
     *
     * @param int $locationId
     * @param array $searchParams
     * @return array
     */
    public function getMaterialUsage($locationId, $searchParams = [])
    {
        // 取得一般訂單資料
        $orderData = $this->orderModel->getMaterialDetailsWithProjectsByLocation($locationId, $searchParams);
        
        // 取得租賃單資料
        $rentalData = $this->rentalOrderModel->getMaterialDetailsByLocation($locationId, $searchParams);
        
        // 合併所有項目（去重）
        $allProjects = array_unique(array_merge(
            $orderData['all_projects'] ?? [],
            $rentalData['all_projects'] ?? []
        ));
        
        // 合併產品資料
        $allProducts = [];
        foreach ($allProjects as $projectName) {
            $allProducts[$projectName] = [];
            
            // 合併訂單的產品
            if (isset($orderData['all_products'][$projectName])) {
                $allProducts[$projectName] = array_merge(
                    $allProducts[$projectName],
                    $orderData['all_products'][$projectName]
                );
            }
            
            // 合併租賃單的產品
            if (isset($rentalData['all_products'][$projectName])) {
                foreach ($rentalData['all_products'][$projectName] as $productKey => $productInfo) {
                    if (!isset($allProducts[$projectName][$productKey])) {
                        $allProducts[$projectName][$productKey] = $productInfo;
                    }
                }
            }
        }
        
        // 合併所有訂單/租賃單記錄
        $allOrders = array_merge(
            $orderData['orders'] ?? [],
            $rentalData['orders'] ?? []
        );
        
        // 按日期排序（新的在前）
        usort($allOrders, function($a, $b) {
            // 先按日期排序（降序）
            $dateCompare = strcmp($b['date'], $a['date']);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            
            // 日期相同時，按 ID 排序（升序）
            $idA = $a['o_id'] ?? $a['ro_id'] ?? 0;
            $idB = $b['o_id'] ?? $b['ro_id'] ?? 0;
            return $idA - $idB;
        });
        
        return [
            'orders' => $allOrders,
            'all_projects' => array_values($allProjects),
            'all_products' => $allProducts
        ];
    }
}

