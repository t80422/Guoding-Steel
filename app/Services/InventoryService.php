<?php

namespace App\Services;

use App\Models\InventoryModel;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use Exception;

class InventoryService
{
    protected $inventoryModel;
    protected $orderModel;
    protected $orderDetailModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->orderModel = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();
    }

    /**
     * 根據訂單操作更新庫存
     *
     * @param int $orderId 訂單ID
     * @param string $operation 操作類型 (CREATE, DELETE, UPDATE)
     * @param array $oldOrderData 舊訂單資料 (UPDATE時使用)
     * @param array $oldOrderDetails 舊訂單明細 (UPDATE時使用)
     * @return bool
     * @throws Exception
     */
    public function updateInventoryForOrder($orderId, $operation, $oldOrderData = null, $oldOrderDetails = null)
    {
        try {
            switch ($operation) {
                case 'CREATE':
                    return $this->handleCreateOrder($orderId);
                case 'DELETE':
                    return $this->handleDeleteOrder($orderId);
                case 'UPDATE':
                    return $this->handleUpdateOrder($orderId, $oldOrderData, $oldOrderDetails);
                default:
                    throw new Exception('不支援的操作類型');
            }
        } catch (Exception $e) {
            log_message('error', 'InventoryService::updateInventoryForOrder - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 處理新增訂單的庫存更新
     *
     * @param int $orderId
     * @return bool
     * @throws Exception
     */
    private function handleCreateOrder($orderId)
    {
        $order = $this->orderModel->find($orderId);
        if (!$order) {
            throw new Exception('訂單不存在');
        }

        $orderDetails = $this->orderDetailModel->getByOrderId($orderId);

        foreach ($orderDetails as $detail) {
            // 出庫地點減少庫存
            $this->adjustInventory($detail['od_pr_id'], $order['o_from_location'], -$detail['od_qty']);
            
            // 入庫地點增加庫存
            $this->adjustInventory($detail['od_pr_id'], $order['o_to_location'], $detail['od_qty']);
        }

        return true;
    }

    /**
     * 處理刪除訂單的庫存更新
     *
     * @param int $orderId
     * @return bool
     * @throws Exception
     */
    private function handleDeleteOrder($orderId)
    {
        $order = $this->orderModel->find($orderId);
        if (!$order) {
            throw new Exception('訂單不存在');
        }

        $orderDetails = $this->orderDetailModel->getByOrderId($orderId);
        
        foreach ($orderDetails as $detail) {
            // 回復出庫地點庫存
            $this->adjustInventory($detail['od_pr_id'], $order['o_from_location'], $detail['od_qty']);
            
            // 回復入庫地點庫存
            $this->adjustInventory($detail['od_pr_id'], $order['o_to_location'], -$detail['od_qty']);
        }

        return true;
    }

    /**
     * 處理修改訂單的庫存更新
     *
     * @param int $orderId
     * @param array $oldOrderData
     * @param array $oldOrderDetails
     * @return bool
     * @throws Exception
     */
    private function handleUpdateOrder($orderId, $oldOrderData, $oldOrderDetails)
    {
        // 先回復原有的庫存影響
        foreach ($oldOrderDetails as $detail) {
            // 回復原出庫地點庫存
            $this->adjustInventory($detail['od_pr_id'], $oldOrderData['o_from_location'], $detail['od_qty']);
            
            // 回復原入庫地點庫存
            $this->adjustInventory($detail['od_pr_id'], $oldOrderData['o_to_location'], -$detail['od_qty']);
        }

        // 再套用新的庫存影響
        return $this->handleCreateOrder($orderId);
    }

    /**
     * 調整庫存數量
     *
     * @param int $productId 產品ID
     * @param int $locationId 地點ID
     * @param int $qtyChange 數量變化 (正數增加，負數減少)
     * @return bool
     * @throws Exception
     */
    public function adjustInventory($productId, $locationId, $qtyChange)
    {
        // 確保庫存記錄存在
        $this->ensureInventoryExists($productId, $locationId);
        // 取得當前庫存
        $inventory = $this->inventoryModel
            ->where('i_pr_id', $productId)
            ->where('i_l_id', $locationId)
            ->first();

        if (!$inventory) {
            throw new Exception('庫存記錄不存在');
        }

        // 更新庫存數量
        $newQty = $inventory['i_qty'] + $qtyChange;
        
        $updateData = [
            'i_qty' => $newQty,
            'i_update_by' => session()->get('userId') ?? 1, // 預設系統使用者
            'i_update_at' => date('Y-m-d H:i:s')
        ];

        return $this->inventoryModel->update($inventory['i_id'], $updateData);
    }

    /**
     * 確保庫存記錄存在，如果不存在則建立
     *
     * @param int $productId
     * @param int $locationId
     * @return bool
     */
    public function ensureInventoryExists($productId, $locationId)
    {
        $exists = $this->inventoryModel->isDuplicateLocationProduct($productId, $locationId);
        
        if (!$exists) {
            $inventoryData = [
                'i_pr_id' => $productId,
                'i_l_id' => $locationId,
                'i_initial' => 0,
                'i_qty' => 0,
                'i_create_by' => session()->get('userId') ?? 1 // 預設系統使用者
            ];
            
            return $this->inventoryModel->insert($inventoryData);
        }
        
        return true;
    }

    /**
     * 取得庫存列表 (供 InventoryController 使用)
     *
     * @param array $filter
     * @param int $page
     * @param bool $usePaging
     * @return array
     */
    public function getInventoryList($filter = [], $page = 1, $usePaging = true)
    {
        return $this->inventoryModel->getList($filter, $page, $usePaging);
    }

    /**
     * 取得庫存詳細資料 (供 InventoryController 使用)
     *
     * @param int $id
     * @return array
     */
    public function getInventoryInfo($id)
    {
        return $this->inventoryModel->getInfoById($id);
    }

    /**
     * 儲存庫存資料 (供 InventoryController 使用)
     *
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function saveInventory($data)
    {
        $userId = session()->get('userId');
        
        if (empty($userId)) {
            throw new Exception('請先登入！');
        }

        if (isset($data['i_id']) && !empty($data['i_id'])) {
            // 更新
            $data['i_update_by'] = $userId;
            $data['i_update_at'] = date('Y-m-d H:i:s');
        } else {
            // 新增 - 檢查地點和產品是否重複
            if ($this->inventoryModel->isDuplicateLocationProduct($data['i_pr_id'], $data['i_l_id'])) {
                throw new Exception("此地點和產品的組合已存在庫存記錄！");
            }
            
            $data['i_create_by'] = $userId;
        }
        
        return $this->inventoryModel->save($data);
    }

    /**
     * 刪除庫存資料 (供 InventoryController 使用)
     *
     * @param int $id
     * @return bool
     */
    public function deleteInventory($id)
    {
        return $this->inventoryModel->delete($id);
    }
} 