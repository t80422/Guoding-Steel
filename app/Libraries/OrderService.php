<?php

namespace App\Libraries;

use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Services\InventoryService;
use App\Libraries\FileManager;
use Exception;

class OrderService
{
    const UPLOAD_PATH = WRITEPATH . 'uploads/signatures/';

    // 支援的檔案類型
    const SIGNATURE_KEYS = [
        'o_driver_signature',
        'o_from_signature',
        'o_to_signature',
        'o_img_car_head',
        'o_img_car_tail'
    ];

    protected $orderModel;
    protected $orderDetailModel;
    protected $inventoryService;
    protected $fileManager;
    protected $db;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();
        $this->inventoryService = new InventoryService();
        $this->fileManager = new FileManager(self::UPLOAD_PATH);
        $this->db = \Config\Database::connect();
    }

    /**
     * 建立新訂單 (統一處理邏輯)
     */
    public function createOrder(array $orderData, array $detailsData, array $files = [], ?int $userId = null): int
    {
        $this->db->transException(true);
        $this->db->transStart();

        try {
            // 處理檔案上傳
            $uploadedFiles = $this->handleFileUploads($files);

            // 將檔案名稱加入訂單資料
            foreach (self::SIGNATURE_KEYS as $key) {
                $orderData[$key] = $uploadedFiles[$key] ?? null;
            }

            // 設定訂單狀態和系統欄位
            $orderData['o_status'] = $this->determineOrderStatus($orderData);
            $orderData['o_number'] = $this->orderModel->generateOrderNumber();
            $orderData['o_create_by'] = $userId;
            $orderData['o_create_at'] = date('Y-m-d H:i:s');

            // 新增主表
            $orderId = $this->orderModel->insert($orderData);

            // 新增明細表
            foreach ($detailsData as &$detail) {
                $detail['od_o_id'] = $orderId;
                // 處理外鍵欄位：空字串轉為 NULL
                if (isset($detail['od_ma_id']) && $detail['od_ma_id'] === '') {
                    $detail['od_ma_id'] = null;
                }
            }
            unset($detail);

            if (!empty($detailsData)) {
                $this->orderDetailModel->insertBatch($detailsData);
            }

            // 更新庫存
            $this->inventoryService->updateInventoryForOrder($orderId, 'CREATE');

            $this->db->transComplete();
            return $orderId;
        } catch (Exception $e) {
            $this->db->transRollback();

            // 清理已上傳的檔案
            if (isset($uploadedFiles)) {
                $this->cleanupFiles($uploadedFiles);
            }

            log_message('error', 'OrderService::createOrder - ' . $e->getMessage());
            throw $e;
        } finally {
            $this->db->transException(false);
        }
    }

    /**
     * 更新訂單 (統一處理邏輯)
     */
    public function updateOrder(int $orderId, array $orderData, array $detailsData, array $files = [], ?int $userId = null): bool
    {
        $this->db->transException(true);
        $this->db->transStart();

        try {
            // 取得舊資料
            $oldOrder = $this->orderModel->find($orderId);
            if (!$oldOrder) {
                throw new Exception('訂單不存在');
            }

            $oldOrderDetails = $this->orderDetailModel->getByOrderId($orderId);

            // 處理檔案上傳/更新
            $fileResult = $this->handleFileUpdates($files, $oldOrder, $orderData);
            $orderData = $fileResult['data'];

            // 處理外鍵欄位
            $this->sanitizeForeignKeys($orderData);

            // 設定更新欄位
            $orderData['o_update_by'] = $userId;
            $orderData['o_update_at'] = date('Y-m-d H:i:s');

            // 合併舊資料和新資料，用於判斷訂單狀態
            $mergedData = array_merge($oldOrder, $orderData);
            $orderData['o_status'] = $this->determineOrderStatus($mergedData);

            // 更新主表
            $this->orderModel->update($orderId, $orderData);

            // 更新明細表
            if (!empty($detailsData)) {
                $this->updateOrderDetails($orderId, $detailsData);
            }

            // 更新庫存
            $this->inventoryService->updateInventoryForOrder($orderId, 'UPDATE', $oldOrder, $oldOrderDetails);

            $this->db->transComplete();
            return true;
        } catch (Exception $e) {
            $this->db->transRollback();
            log_message('error', 'OrderService::updateOrder - ' . $e->getMessage());
            throw $e;
        } finally {
            $this->db->transException(false);
        }
    }

    /**
     * 刪除訂單 (統一處理邏輯)
     */
    public function deleteOrder(int $orderId): bool
    {
        $this->db->transStart();

        try {
            $order = $this->orderModel->find($orderId);
            if (!$order) {
                throw new Exception('訂單不存在');
            }

            // 更新庫存 (在實際刪除前)
            $this->inventoryService->updateInventoryForOrder($orderId, 'DELETE');

            // 刪除訂單明細
            $this->orderDetailModel->where('od_o_id', $orderId)->delete();

            // 刪除訂單主表
            $this->orderModel->delete($orderId);

            // 刪除相關檔案
            $this->deleteOrderFiles($order);

            $this->db->transComplete();
            return true;
        } catch (Exception $e) {
            $this->db->transRollback();
            log_message('error', 'OrderService::deleteOrder - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 處理檔案上傳 (新增時)
     */
    private function handleFileUploads(array $files): array
    {
        try {
            return $this->fileManager->uploadFiles(self::SIGNATURE_KEYS, $files);
        } catch (Exception $e) {
            log_message('error', 'OrderService::handleFileUploads - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 處理檔案更新
     */
    private function handleFileUpdates(array $files, array $oldOrder, array $requestData): array
    {
        try {
            $filesToDelete = [];
            $updatedData = $requestData;

            // 處理每個檔案欄位
            foreach (self::SIGNATURE_KEYS as $key) {
                if (isset($files[$key]) && $files[$key]->isValid() && !$files[$key]->hasMoved()) {
                    // 如果有新檔案上傳，先記錄要刪除的舊檔案
                    if (!empty($oldOrder[$key] ?? null)) {
                        $filesToDelete[] = $oldOrder[$key];
                    }
                } else if (array_key_exists($key, $requestData) && $requestData[$key] === null) {
                    // 如果前端傳送 null，表示要刪除檔案
                    if (!empty($oldOrder[$key] ?? null)) {
                        $filesToDelete[] = $oldOrder[$key];
                    }
                    $updatedData[$key] = null;
                } else {
                    // 如果沒有新檔案上傳，且前端沒有傳送該欄位或值不為 null，則保留原有檔案
                    if (!isset($requestData[$key])) {
                        $updatedData[$key] = $oldOrder[$key] ?? null;
                    }
                }
            }

            // 刪除舊檔案
            if (!empty($filesToDelete)) {
                $this->fileManager->deleteFiles($filesToDelete);
            }

            // 上傳新檔案
            $uploadedFiles = $this->fileManager->uploadFiles(self::SIGNATURE_KEYS, $files);

            // 將新上傳的檔案名稱更新到資料中
            foreach (self::SIGNATURE_KEYS as $key) {
                if ($uploadedFiles[$key] !== null) {
                    $updatedData[$key] = $uploadedFiles[$key];
                }
            }

            return [
                'data' => $updatedData,
                'uploaded_files' => $uploadedFiles,
                'deleted_files' => $filesToDelete
            ];
        } catch (Exception $e) {
            log_message('error', 'OrderService::handleFileUpdates - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 刪除訂單相關檔案
     */
    private function deleteOrderFiles(array $orderData): bool
    {
        try {
            $filesToDelete = [];

            foreach (self::SIGNATURE_KEYS as $key) {
                if (!empty($orderData[$key] ?? null)) {
                    $filesToDelete[] = $orderData[$key];
                }
            }

            if (!empty($filesToDelete)) {
                $result = $this->fileManager->deleteFiles($filesToDelete);
                return $result !== null ? $result : true;
            }

            return true;
        } catch (Exception $e) {
            log_message('error', 'OrderService::deleteOrderFiles - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 清理上傳失敗的檔案
     */
    private function cleanupFiles(array $uploadedFiles): bool
    {
        try {
            if (!empty($uploadedFiles)) {
                $result = $this->fileManager->deleteFiles($uploadedFiles);
                return $result !== null ? $result : true;
            }
            return true;
        } catch (Exception $e) {
            log_message('error', 'OrderService::cleanupFiles - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 處理外鍵欄位：空字串轉為 NULL
     */
    private function sanitizeForeignKeys(array &$data): void
    {
        $foreignKeyFields = ['o_g_id', 'o_from_location', 'o_to_location', 'o_ct_id'];
        foreach ($foreignKeyFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
    }

    /**
     * 判斷訂單狀態
     * 當三個簽名欄位和卸貨時間都有值時，狀態為已完成；否則為進行中
     */
    private function determineOrderStatus(array $orderData): string
    {
        $requiredFields = [
            'o_driver_signature',
            'o_from_signature',
            'o_to_signature',
            'o_unloading_time'
        ];

        foreach ($requiredFields as $field) {
            if (empty($orderData[$field] ?? null)) {
                return OrderModel::STATUS_IN_PROGRESS;
            }
        }

        return OrderModel::STATUS_COMPLETED;
    }

    /**
     * 更新訂單明細 (保留原有方法)
     */
    public function updateOrderDetails(int $orderId, array $newDetails)
    {
        try {
            // 取得現有的明細
            $existingDetails = $this->orderDetailModel->getByOrderId($orderId);
            $existingDetailIds = array_column($existingDetails, 'od_id');

            $newDetailIds = array_column($newDetails, 'od_id');

            $toInsert = [];
            $toUpdate = [];
            $toDeleteIds = [];

            // 識別要新增或更新的明細
            foreach ($newDetails as $detail) {
                // 確保每個明細都有 od_o_id
                $detail['od_o_id'] = $orderId;

                // 處理外鍵欄位：空字串轉為 NULL
                if (isset($detail['od_ma_id']) && $detail['od_ma_id'] === '') {
                    $detail['od_ma_id'] = null;
                }

                if (empty($detail['od_id'])) {
                    // 新增的明細，沒有 od_id
                    $toInsert[] = $detail;
                } else if (in_array($detail['od_id'], $existingDetailIds)) {
                    // 存在的明細，需要更新
                    $toUpdate[] = $detail;
                }
            }

            // 識別要刪除的明細
            foreach ($existingDetailIds as $existingId) {
                if (!in_array($existingId, $newDetailIds)) {
                    $toDeleteIds[] = $existingId;
                }
            }

            // 執行刪除操作
            if (!empty($toDeleteIds)) {
                $this->orderDetailModel->delete($toDeleteIds);
            }

            // 執行更新操作
            if (!empty($toUpdate)) {
                $this->orderDetailModel->updateBatch($toUpdate, 'od_id');
            }

            // 執行新增操作
            if (!empty($toInsert)) {
                $this->orderDetailModel->insertBatch($toInsert);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 獲取支援的檔案類型
     */
    public function getSupportedKeys(): array
    {
        return self::SIGNATURE_KEYS;
    }
}
