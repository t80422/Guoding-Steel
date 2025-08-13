<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RentalDetailProjectItemModel;

class RentalDetailProjectItemController extends BaseController
{
    private RentalDetailProjectItemModel $model;

    public function __construct()
    {
        $this->model = new RentalDetailProjectItemModel();
    }

    public function getDetail(int $rentalId)
    {
        try {
            log_message('debug',print_r($rentalId,true));
            $result = $this->model->getByRentalId($rentalId);
            log_message('debug',print_r($result,true));

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'RentalDetailProjectItem getByRentalId error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '載入項目數量失敗：' . $e->getMessage(),
            ]);
        }
    }

    public function save()
    {
        try {
            $json = $this->request->getJSON(true);
            if (empty($json)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '沒有接收到數據',
                ]);
            }

            $processedCount = 0;

            // create
            if (isset($json['create']) && is_array($json['create'])) {
                foreach ($json['create'] as $item) {
                    $rodId = (int)($item['rodpi_rod_id'] ?? 0);
                    $piId  = (int)($item['rodpi_pi_id'] ?? 0);
                    $qty   = (int)($item['rodpi_qty'] ?? 0);
                    if ($rodId <= 0 || $piId <= 0 || $qty < 0) {
                        continue;
                    }
                    $this->model->save([
                        'rodpi_rod_id' => $rodId,
                        'rodpi_pi_id' => $piId,
                        'rodpi_qty' => $qty,
                    ]);
                    $processedCount++;
                }
            }

            // update
            if (isset($json['update']) && is_array($json['update'])) {
                foreach ($json['update'] as $item) {
                    $rodId = (int)($item['rodpi_rod_id'] ?? 0);
                    $piId  = (int)($item['rodpi_pi_id'] ?? 0);
                    $qty   = (int)($item['rodpi_qty'] ?? 0);
                    if ($rodId <= 0 || $piId <= 0 || $qty < 0) {
                        continue;
                    }
                    $existing = $this->model->getByRODIdAndPIId($rodId, $piId);
                    if ($existing) {
                        $this->model->update($existing['rodpi_id'], [
                            'rodpi_qty' => $qty,
                        ]);
                        $processedCount++;
                    }
                }
            }

            // delete
            if (isset($json['delete']) && is_array($json['delete'])) {
                foreach ($json['delete'] as $item) {
                    $rodId = (int)($item['rodpi_rod_id'] ?? 0);
                    $piId  = (int)($item['rodpi_pi_id'] ?? 0);
                    if ($rodId <= 0 || $piId <= 0) {
                        continue;
                    }
                    $existing = $this->model->getByRODIdAndPIId($rodId, $piId);
                    if ($existing) {
                        $this->model->delete($existing['rodpi_id']);
                        $processedCount++;
                    }
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '成功處理項目數量記錄',
                'processed' => $processedCount,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'RentalDetailProjectItem save error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '儲存失敗：' . $e->getMessage(),
            ]);
        }
    }
}


