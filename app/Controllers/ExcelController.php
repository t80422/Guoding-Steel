<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Files\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Services\ExcelImportService;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\OrderDetailProjectItemModel;
use App\Models\RentalOrderModel;
use App\Models\RentalOrderDetailModel;
use App\Models\RentalDetailProjectItemModel;
use App\Services\InventoryService;

class ExcelController extends BaseController
{

    // 主頁
    public function index()
    {
        return view('excel/index');
    }

    // 匯入Excel（新版）
    public function import()
    {
        try {
            // 檢查檔案上傳
            $file = $this->request->getFile('excel_file');

            if (!$file || !$file->isValid()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => '請選擇有效的Excel檔案'
                ]);
            }

            // 檢查檔案類型
            $allowedTypes = [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => '請上傳Excel檔案格式(.xls或.xlsx)'
                ]);
            }

            // 使用 Service 解析新版格式
            $service = new ExcelImportService();
            $result = $service->parse($file);

            if (!$result['success']) {
                $errors = $result['errors'] ?? [];
                log_message('error', 'Excel import errors: ' . print_r($errors, true));
                
                // 將錯誤陣列轉換為可讀的訊息
                $errorMessage = $this->formatErrorMessage($errors);
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $errors
                ]);
            }

            // 暫存於 session
            $this->storeDataInMemory($result['data'], $file->getClientName());

            return $this->response->setJSON([
                'success' => true,
                'data' => $result['summary'] ?? []
            ]);
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => '檔案處理失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 將資料暫存到記憶體（Session）
     */
    private function storeDataInMemory(array $data, string $filename): void
    {
        session()->set('excel_memory_data', [
            'data' => $data,
            'timestamp' => time(),
            'filename' => $filename
        ]);
    }

    // 儲存匯入的資料（寫入 orders / rental_orders，重建明細與庫存）
    public function save()
    {
        try {
            // 從記憶體取得暫存的資料
            $memoryData = $this->getDataFromMemory();

            if (!$memoryData) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => '沒有可儲存的資料,或資料已過期,請重新匯入Excel檔案'
                ]);
            }
            
            $userId = session()->get('userId');
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => '請先登入'
                ]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $orderModel = new OrderModel();
            $orderDetailModel = new OrderDetailModel();
            $odpiModel = new OrderDetailProjectItemModel();
            $rentalModel = new RentalOrderModel();
            $rentalDetailModel = new RentalOrderDetailModel();
            $rodpiModel = new RentalDetailProjectItemModel();
            $inventoryService = new InventoryService();

            $created = 0; $updated = 0;

            $data = $memoryData['data'] ?? [];
            $orders = $data['orders'] ?? [];
            $rentals = $data['rentals'] ?? [];

            // Orders 寫入
            foreach ($orders as $o) {
                $header = $o['header'];
                $details = $o['details'];

                // 以 o_number 決定新增或更新
                $exist = $orderModel->where('o_number', $header['o_number'])->first();
                $oldOrder = null; $oldDetails = null;
                if ($exist) {
                    $header['o_id'] = $exist['o_id'];
                    $oldOrder = $exist;
                    $oldDetails = $orderDetailModel->getByOrderId((int)$exist['o_id']);
                    $orderModel->save($header);

                    // 刪舊明細與關聯
                    if (!empty($oldDetails)) {
                        foreach ($oldDetails as $d) {
                            $odpiModel->where('odpi_od_id', $d['od_id'])->delete();
                        }
                    }
                    $orderDetailModel->where('od_o_id', $exist['o_id'])->delete();
                    $updated++;
                } else {
                    $orderModel->insert($header);
                    $header['o_id'] = $orderModel->getInsertID();
                    $oldOrder = null; $oldDetails = [];
                    $created++;
                }

                // 聚合 (pr_id,length) → 建立 order_details
                $detailIdByKey = [];
                foreach ($details as $d) {
                    $key = $d['pr_id'].'|'.$d['length'];
                    if (!isset($detailIdByKey[$key])) {
                        $orderDetailModel->insert([
                            'od_o_id' => $header['o_id'],
                            'od_pr_id' => $d['pr_id'],
                            'od_qty' => $d['qty'],
                            'od_length' => $d['length'],
                        ]);
                        $detailIdByKey[$key] = $orderDetailModel->getInsertID();
                    } else {
                        // 若同鍵多筆（理論上已聚合），仍保險相加
                        $odId = $detailIdByKey[$key];
                        $row = $orderDetailModel->find($odId);
                        $orderDetailModel->update($odId, ['od_qty' => ((int)$row['od_qty']) + (int)$d['qty']]);
                    }

                    // 建立項目配置
                    $odId = $detailIdByKey[$key];
                    foreach ($d['allocations'] as $piId => $qty) {
                        $odpiModel->insert([
                            'odpi_od_id' => $odId,
                            'odpi_pi_id' => $piId,
                            'odpi_qty' => $qty,
                        ]);
                    }
                }

                // 更新庫存
                $inventoryService->updateInventoryForOrder((int)$header['o_id'], $exist ? 'UPDATE' : 'CREATE', $oldOrder, $oldDetails);
            }

            // Rentals 寫入
            foreach ($rentals as $r) {
                $header = $r['header'];
                $details = $r['details'];

                $exist = $rentalModel->where('ro_number', $header['ro_number'])->first();
                $oldRental = null; $oldDetails = null;
                if ($exist) {
                    $header['ro_id'] = $exist['ro_id'];
                    $header['ro_update_by'] = $userId;
                    $header['ro_update_at'] = date('Y-m-d H:i:s');
                    $oldRental = $exist;
                    $oldDetails = $rentalDetailModel->getByRentalId((int)$exist['ro_id']);
                    $rentalModel->save($header);

                    // 刪舊明細與關聯
                    if (!empty($oldDetails)) {
                        foreach ($oldDetails as $d) {
                            $rodpiModel->where('rodpi_rod_id', $d['rod_id'])->delete();
                        }
                    }
                    $rentalDetailModel->where('rod_ro_id', $exist['ro_id'])->delete();
                    $updated++;
                } else {
                    $rentalModel->insert($header);
                    $header['ro_id'] = $rentalModel->getInsertID();
                    $header['ro_create_by'] = $userId;
                    $oldRental = null; $oldDetails = [];
                    $created++;
                }

                // 聚合 (pr_id,length) → 建立 rental_order_details
                $detailIdByKey = [];
                foreach ($details as $d) {
                    $key = $d['pr_id'].'|'.$d['length'];
                    if (!isset($detailIdByKey[$key])) {
                        $rentalDetailModel->insert([
                            'rod_ro_id' => $header['ro_id'],
                            'rod_pr_id' => $d['pr_id'],
                            'rod_qty' => $d['qty'],
                            'rod_length' => $d['length'],
                        ]);
                        $detailIdByKey[$key] = $rentalDetailModel->getInsertID();
                    } else {
                        $rodId = $detailIdByKey[$key];
                        $row = $rentalDetailModel->find($rodId);
                        $rentalDetailModel->update($rodId, ['rod_qty' => ((int)$row['rod_qty']) + (int)$d['qty']]);
                    }

                    // 建立項目配置
                    $rodId = $detailIdByKey[$key];
                    foreach ($d['allocations'] as $piId => $qty) {
                        $rodpiModel->insert([
                            'rodpi_rod_id' => $rodId,
                            'rodpi_pi_id' => $piId,
                            'rodpi_qty' => $qty,
                        ]);
                    }
                }

                // 更新庫存（只調工地）
                $inventoryService->updateInventoryForRental((int)$header['ro_id'], $exist ? 'UPDATE' : 'CREATE', $oldRental, $oldDetails);
            }

            $db->transComplete();
            
            if ($db->transStatus() === FALSE) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => '資料庫交易失敗'
                ]);
            }
            
            $this->clearMemoryData();

            return $this->response->setJSON([
                'success' => true,
                'message' => "匯入完成！新增 {$created} 筆、更新 {$updated} 筆",
                'data' => [ 'created' => $created, 'updated' => $updated ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Save operation failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => '儲存失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 從記憶體取得暫存資料
     */
    private function getDataFromMemory(): ?array
    {
        $memoryData = session()->get('excel_memory_data');

        if (empty($memoryData)) {
            return null;
        }

        // 檢查是否過期（30分鐘）
        $maxAge = 30 * 60;
        if (time() - $memoryData['timestamp'] > $maxAge) {
            $this->clearMemoryData();
            return null;
        }

        return $memoryData;
    }

    /**
     * 清除記憶體中的暫存資料
     */
    private function clearMemoryData(): void
    {
        session()->remove('excel_memory_data');
    }

    /**
     * 將錯誤陣列格式化為可讀的訊息
     */
    private function formatErrorMessage(array $errors): string
    {
        if (empty($errors)) {
            return '未知錯誤';
        }

        $messages = [];
        foreach ($errors as $error) {
            $message = $error['message'] ?? '未知錯誤';
            
            // 如果有行號資訊，加入行號
            if (isset($error['row'])) {
                $message = "第{$error['row']}列：{$message}";
            }
            
            $messages[] = $message;
        }

        // 限制顯示的錯誤數量，避免訊息過長
        if (count($messages) > 10) {
            $displayMessages = array_slice($messages, 0, 10);
            $remaining = count($messages) - 10;
            $displayMessages[] = "...及其他 {$remaining} 個錯誤";
            return implode("\n", $displayMessages);
        }

        return implode("\n", $messages);
    }
}
