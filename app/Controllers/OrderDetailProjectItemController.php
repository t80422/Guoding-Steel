<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderDetailProjectItemModel;

class OrderDetailProjectItemController extends BaseController
{
    private $odpiModel;

    public function __construct()
    {
        $this->odpiModel = new OrderDetailProjectItemModel();
    }

    public function getDetail($orderId)
    {
        try {
            $result = $this->odpiModel->getByOrderId($orderId);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            log_message('error', 'OrderDetailProjectItem getByOrderId error: ' . $e->getMessage());
            
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '載入項目數量失敗：' . $e->getMessage()
            ]);
        }
    }

    public function save()
    {
        try {
            // 獲取 JSON 數據
            $json = $this->request->getJSON(true);
            
            if (empty($json)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '沒有接收到數據'
                ]);
            }

            $processedCount = 0;

            // 處理新增操作
            if (isset($json['create']) && !empty($json['create'])) {
                foreach ($json['create'] as $item) {
                    $this->odpiModel->save([
                        'odpi_od_id' => $item['odpi_od_id'],
                        'odpi_pi_id' => $item['odpi_pi_id'],
                        'odpi_qty' => $item['odpi_qty'],
                    ]);
                    $processedCount++;
                }
            }

            // 處理更新操作
            if (isset($json['update']) && !empty($json['update'])) {
                foreach ($json['update'] as $item) {
                    $existing = $this->odpiModel->getByODIdAndPIId($item['odpi_od_id'], $item['odpi_pi_id']);
                    if ($existing) {
                        $this->odpiModel->update($existing['odpi_id'], [
                            'odpi_qty' => $item['odpi_qty'],
                        ]);
                        $processedCount++;
                    }
                }
            }

            // 處理刪除操作
            if (isset($json['delete']) && !empty($json['delete'])) {
                foreach ($json['delete'] as $item) {
                    $existing = $this->odpiModel->getByODIdAndPIId($item['odpi_od_id'], $item['odpi_pi_id']);
                    if ($existing) {
                        $this->odpiModel->delete($existing['odpi_id']);
                        $processedCount++;
                    }
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => "成功處理項目數量記錄",
            ]);

        } catch (\Exception $e) {
            log_message('error', 'OrderDetailProjectItem save error: ' . $e->getMessage());
            
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '儲存失敗：' . $e->getMessage()
            ]);
        }
    }
}
