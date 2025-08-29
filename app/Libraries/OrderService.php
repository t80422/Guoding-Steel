<?php

namespace App\Libraries;

use CodeIgniter\Files\File;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use Exception;

class OrderService
{
    const UPLOAD_PATH = WRITEPATH . 'uploads/signatures/';

    protected $orderModel;
    protected $orderDetailModel;
    protected $db;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * 確保資料夾存在
     */
    public function ensureUploadPath()
    {
        if (!is_dir(self::UPLOAD_PATH)) {
            mkdir(self::UPLOAD_PATH, 0777, true);
        }
    }

    /**
     * 上傳簽名檔案
     *
     * @param File $file
     * @return string 檔名
     * @throws Exception
     */
    public function uploadSignature(File $file): string
    {
        $this->ensureUploadPath();
        $newName = $file->getRandomName();
        if (!$file->move(self::UPLOAD_PATH, $newName)) {
            throw new Exception('無法移動簽名檔案。');
        }
        return $newName;
    }

    /**
     * 刪除簽名檔案
     *
     * @param string $filename
     */
    public function deleteSignature($fileName)
    {
        $path = self::UPLOAD_PATH . $fileName;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * 更新訂單明細
     *
     * @param int $orderId 訂單 ID
     * @param array $newDetails 前端傳來的最新明細資料
     * @throws Exception
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
                // updateBatch 需要指定用哪個欄位來匹配
                $this->orderDetailModel->updateBatch($toUpdate, 'od_id');
            }

            // 執行新增操作
            if (!empty($toInsert)) {
                $this->orderDetailModel->insertBatch($toInsert);
            }

        } catch (Exception $e) {
            throw $e; // 重新拋出異常，讓控制器捕獲並處理
        }
    }
}
