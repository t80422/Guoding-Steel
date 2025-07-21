<?php

namespace App\Services;

use App\Models\ManufacturerInventoryModel;
use App\Models\ManufacturerModel;
use App\Models\MajorCategoryModel;
use Exception;

class ManufacturerInventoryService
{
    protected $manufacturerInventoryModel;
    protected $manufacturerModel;
    protected $majorCategoryModel;
    protected $inventoryService;

    public function __construct()
    {
        $this->manufacturerInventoryModel = new ManufacturerInventoryModel();
        $this->manufacturerModel = new ManufacturerModel();
        $this->majorCategoryModel = new MajorCategoryModel();
        $this->inventoryService   = new InventoryService();
    }

    /**
     * 儲存庫存資料
     *
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function saveManufacturerInventory($data)
    {
        $userId = session()->get('userId');

        if (empty($userId)) {
            throw new Exception('請先登入！');
        }

        if (isset($data['mi_id']) && !empty($data['mi_id'])) {
            // 更新
            $data['mi_update_by'] = $userId;
            $data['mi_update_at'] = date('Y-m-d H:i:s');
        } else {
            // 新增 - 檢查地點和產品是否重複
            if ($this->manufacturerInventoryModel->isDuplicate($data['mi_pr_id'], $data['mi_ma_id'])) {
                throw new Exception("此產品和廠商的組合已存在庫存記錄！");
            }

            $data['mi_create_by'] = $userId;
        }

        return $this->manufacturerInventoryModel->save($data);
    }

    /**
     * 根據租賃操作更新庫存
     *
     * @param int $rentalId 租賃ID
     * @param string $operation 操作類型 (CREATE, DELETE, UPDATE)
     * @param array $oldRentalData 舊租賃資料 (UPDATE時使用)
     * @param array $oldRentalDetails 舊租賃明細 (UPDATE時使用)
     * @return bool
     * @throws Exception
     */
    public function updateInventoryForRental($rentalId, $operation, $oldRentalData = null, $oldRentalDetails = null)
    {
        try {
            switch ($operation) {
                case 'CREATE':
                    return $this->handleCreateRental($rentalId);
                case 'DELETE':
                    return $this->handleDeleteRental($rentalId);
                case 'UPDATE':
                    return $this->handleUpdateRental($rentalId, $oldRentalData, $oldRentalDetails);
                default:
                    throw new Exception('不支援的操作類型');
            }
        } catch (Exception $e) {
            log_message('error', 'ManufacturerInventoryService::updateInventoryForRental - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 處理新增租賃的庫存更新
     *
     * @param int $rentalId
     * @return bool
     * @throws Exception
     */
    private function handleCreateRental($rentalId)
    {
        try {
        $rentalModel = new \App\Models\RentalOrderModel();
        $rentalDetailModel = new \App\Models\RentalOrderDetailModel();
        
        $rental = $rentalModel->find($rentalId);
        if (!$rental) {
            throw new Exception('租賃訂單不存在');
        }

        $rentalDetails = $rentalDetailModel->getByRentalId($rentalId);

        foreach ($rentalDetails as $detail) {
            // 根據租賃類型決定庫存調整
            if ($rental['ro_type'] == 0) {
                // 進工地：廠商租賃增加，地點庫存增加
                $this->adjustInventory($detail['rod_pr_id'], $rental['ro_ma_id'], $detail['rod_qty']);
                // 地點庫存使用 InventoryService
                $this->inventoryService->adjustInventory($detail['rod_pr_id'], $rental['ro_l_id'], $detail['rod_qty']);
            } else {
                // 出工地：地點庫存減少，廠商租賃減少
                // 地點庫存使用 InventoryService
                $this->inventoryService->adjustInventory($detail['rod_pr_id'], $rental['ro_l_id'], -$detail['rod_qty']);
                $this->adjustInventory($detail['rod_pr_id'], $rental['ro_ma_id'], -$detail['rod_qty']);
            }
        }

            return true;
        } catch (Exception $e) {
            log_message('error', 'ManufacturerInventoryService::handleCreateRental - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 處理刪除租賃的庫存更新
     *
     * @param int $rentalId
     * @return bool
     * @throws Exception
     */
    private function handleDeleteRental($rentalId)
    {
        $rentalModel = new \App\Models\RentalOrderModel();
        $rentalDetailModel = new \App\Models\RentalOrderDetailModel();
        
        $rental = $rentalModel->find($rentalId);
        if (!$rental) {
            throw new Exception('租賃訂單不存在');
        }

        $rentalDetails = $rentalDetailModel->getByRentalId($rentalId);

        foreach ($rentalDetails as $detail) {
            // 回復庫存調整
            if ($rental['ro_type'] == 0) {
                // 回復進工地：廠商庫存減少，地點庫存減少
                $this->adjustInventory($detail['rod_pr_id'], $rental['ro_ma_id'], -$detail['rod_qty']);
                // 地點庫存使用 InventoryService
                $this->inventoryService->adjustInventory($detail['rod_pr_id'], $rental['ro_l_id'], -$detail['rod_qty']);
            } else {
                // 回復出工地：地點庫存增加，廠商庫存增加
                // 地點庫存使用 InventoryService
                $this->inventoryService->adjustInventory($detail['rod_pr_id'], $rental['ro_l_id'], $detail['rod_qty']);
                $this->adjustInventory($detail['rod_pr_id'], $rental['ro_ma_id'], $detail['rod_qty']);
            }
        }

        return true;
    }

    /**
     * 處理修改租賃的庫存更新
     *
     * @param int $rentalId
     * @param array $oldRentalData
     * @param array $oldRentalDetails
     * @return bool
     * @throws Exception
     */
    private function handleUpdateRental($rentalId, $oldRentalData, $oldRentalDetails)
    {
        // 先回復原有的庫存影響
        foreach ($oldRentalDetails as $detail) {
            if ($oldRentalData['ro_type'] == 0) {
                // 回復進工地：廠商庫存減少，地點庫存減少
                $this->adjustInventory($detail['rod_pr_id'], $oldRentalData['ro_ma_id'], -$detail['rod_qty']);
                // 地點庫存使用 InventoryService
                $this->inventoryService->adjustInventory($detail['rod_pr_id'], $oldRentalData['ro_l_id'], -$detail['rod_qty']);
            } else {
                // 回復出工地：地點庫存增加，廠商庫存增加
                // 地點庫存使用 InventoryService
                $this->inventoryService->adjustInventory($detail['rod_pr_id'], $oldRentalData['ro_l_id'], $detail['rod_qty']);
                $this->adjustInventory($detail['rod_pr_id'], $oldRentalData['ro_ma_id'], $detail['rod_qty']);
            }
        }

        // 再套用新的庫存影響
        return $this->handleCreateRental($rentalId);
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
        try {
        // 確保庫存記錄存在
        $this->ensureInventoryExists($productId, $locationId);

        // 取得當前庫存
        $inventory = $this->manufacturerInventoryModel->getInventoryBymaIdAndProductId($locationId, $productId);

        if (!$inventory) {
            throw new Exception('庫存記錄不存在');
        }

        // 更新庫存數量
        $newQty = $inventory['mi_qty'] + $qtyChange;

        $updateData = [
            'mi_qty' => $newQty,
            'i_update_by' => session()->get('userId') ?? 1, // 預設系統使用者
            'mi_update_at' => date('Y-m-d H:i:s')
        ];

        return $this->manufacturerInventoryModel->update($inventory['mi_id'], $updateData);
        } catch (Exception $e) {
            log_message('error', 'ManufacturerInventoryService::adjustInventory - ' . $e->getMessage());
            throw $e;
        }
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
        try {
        $exists = $this->manufacturerInventoryModel->isDuplicate($productId, $locationId);

        if (!$exists) {
            $inventoryData = [
                'mi_pr_id' => $productId,
                'mi_ma_id' => $locationId,
                'mi_initial' => 0,
                'mi_qty' => 0,
                'mi_create_by' => session()->get('userId') ?? 1 // 預設系統使用者
            ];
            log_message('debug', print_r($inventoryData, true));
            return $this->manufacturerInventoryModel->insert($inventoryData);
        }

        return true;
        } catch (Exception $e) {
            log_message('error', 'ManufacturerInventoryService::ensureInventoryExists - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 取得庫存詳細資料 (供 InventoryController 使用)
     *
     * @param int $id
     * @return array
     */
    public function getInventoryInfo($id)
    {
        return $this->manufacturerInventoryModel->getInfoById($id);
    }

    /**
     * 刪除庫存資料 (供 InventoryController 使用)
     *
     * @param int $id
     * @return bool
     */
    public function deleteInventory($id)
    {
        return $this->manufacturerInventoryModel->delete($id);
    }
}
