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
                return $this->response->setJSON([
                    'success' => false,
                    'errors' => $result['errors'] ?? []
                ]);
            }

            // 暫存於 session
            $this->storeDataInMemory($result['data'], $file->getClientName());

            return $this->response->setJSON([
                'success' => true,
                'data' => $result['summary'] ?? []
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '檔案處理失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 讀取Excel檔案
     */
    private function readExcelFile(UploadedFile $file): array
    {
        try {
            // 直接從上傳檔案的暫存路徑讀取
            $filePath = $file->getTempName();

            // 載入 Excel 檔案
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            // 選擇工作表
            $worksheet = $spreadsheet->getSheet(0);

            // 讀取資料範圍
            $highestRow = $worksheet->getHighestRow();

            if ($highestRow < 8) {
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
                return [
                    'success' => false,
                    'message' => 'Excel 檔案中沒有足夠的資料（至少需要8列）',
                    'data' => []
                ];
            }

            // 從第9列開始讀取資料，建立陣列
            $dataArray = [];
            $incompleteRows = []; // 記錄資料不完全的列
            for ($row = 9; $row <= $highestRow; $row++) {
                $rowData = $worksheet->rangeToArray('A' . $row . ':CF' . $row);

                // 檢查是否遇到結束標記
                if (isset($rowData[0][2]) && $rowData[0][2] == '選擇性總計') break; // 遇到選擇性總計就停止處理

                if (!empty($rowData[0]) && (!empty($rowData[0][1]) || !empty($rowData[0][2]) || !empty($rowData[0][3]))) { // 檢查B欄C欄D欄有資料
                    // 處理日期欄位（D欄）
                    $dateValue = $rowData[0][3] ?? '';
                    if (is_numeric($dateValue) && $dateValue > 0) {
                        try {
                            // 將Excel日期序號轉換為日期格式
                            $dateObj = Date::excelToDateTimeObject($dateValue);
                            $dateValue = $dateObj->format('Y-m-d');
                        } catch (\Exception $e) {
                            // 如果轉換失敗，保持原值
                            $dateValue = $rowData[0][3] ?? '';
                        }
                    }

                    // 建立以Excel欄位名稱為key的陣列
                    $record = [
                        'B' => $rowData[0][1] ?? '',   // B欄 - 車號
                        'C' => $rowData[0][2] ?? '',   // C欄 - 編號
                        'D' => $dateValue,             // D欄 - 日期（已轉換）
                        'E' => $rowData[0][4] ?? '',   // E欄 - 廠商
                        'F' => $rowData[0][5] ?? '',   // F欄 - 地點
                    ];

                    // H到CF欄的數量資料 (索引7到83)
                    $columns = [
                        'H',
                        'I',
                        'J',
                        'K',
                        'L',
                        'M',
                        'N',
                        'O',
                        'P',
                        'Q',
                        'R',
                        'S',
                        'T',
                        'U',
                        'V',
                        'W',
                        'X',
                        'Y',
                        'Z',
                        'AA',
                        'AB',
                        'AC',
                        'AD',
                        'AE',
                        'AF',
                        'AG',
                        'AH',
                        'AI',
                        'AJ',
                        'AK',
                        'AL',
                        'AM',
                        'AN',
                        'AO',
                        'AP',
                        'AQ',
                        'AR',
                        'AS',
                        'AT',
                        'AU',
                        'AV',
                        'AW',
                        'AX',
                        'AY',
                        'AZ',
                        'BA',
                        'BB',
                        'BC',
                        'BD',
                        'BE',
                        'BF',
                        'BG',
                        'BH',
                        'BI',
                        'BJ',
                        'BK',
                        'BL',
                        'BM',
                        'BN',
                        'BO',
                        'BP',
                        'BQ',
                        'BR',
                        'BS',
                        'BT',
                        'BU',
                        'BV',
                        'BW',
                        'BX',
                        'BY',
                        'BZ',
                        'CA',
                        'CB',
                        'CC',
                        'CD',
                        'CE',
                        'CF'
                    ];

                    for ($i = 0; $i < count($columns) && ($i + 7) < count($rowData[0]); $i++) {
                        $record[$columns[$i]] = $rowData[0][$i + 7] ?? '';
                    }

                    $dataArray[] = $record;
                } else {
                    // 記錄資料不完全的列
                    $missingFields = [];
                    if (empty($rowData[0][1])) $missingFields[] = 'B欄(車號)';
                    if (empty($rowData[0][2])) $missingFields[] = 'C欄(單號)';
                    if (empty($rowData[0][3])) $missingFields[] = 'D欄(日期)';

                    if (!empty($missingFields)) {
                        $incompleteRows[] = [
                            'row' => $row,
                            'missing_fields' => $missingFields,
                            'data' => [
                                'B' => $rowData[0][1] ?? '',
                                'C' => $rowData[0][2] ?? '',
                                'D' => $rowData[0][3] ?? '',
                            ]
                        ];
                    }
                }
            }

            // 檢查是否有資料不完全的列
            if (!empty($incompleteRows)) {
                $errorMessages = [];
                foreach ($incompleteRows as $incompleteRow) {
                    $missingText = implode('、', $incompleteRow['missing_fields']);
                    $errorMessages[] = "第{$incompleteRow['row']}列缺少：{$missingText}";
                }

                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);

                return [
                    'success' => false,
                    'message' => 'Excel 檔案有資料不完全的列，請檢查後重新上傳：' . "\n" . implode("\n", $errorMessages),
                    'incomplete_rows' => $incompleteRows,
                    'data' => []
                ];
            }

            $result = [
                'success' => true,
                'data' => $dataArray,
                'total_records' => count($dataArray),
                'message' => "成功讀取 Excel 檔案，共 " . count($dataArray) . " 筆有效資料"
            ];

            // 清理記憶體並關閉檔案
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return $result;
        } catch (ReaderException $e) {
            return [
                'success' => false,
                'message' => 'Excel 檔案讀取錯誤: ' . $e->getMessage(),
                'data' => []
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '處理檔案時發生錯誤: ' . $e->getMessage(),
                'data' => []
            ];
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

    /**
     * 總結統計資料供前端顯示
     */
    private function getSummaryData(array $data): array
    {
        $rentalCount = 0;
        $orderCount = 0;
        $manufacturerSummary = []; // 廠商統計（租賃單明細）

        // 統計邏輯
        foreach ($data as $record) {
            $manufacturer = $record['E'] ?? ''; // E欄 - 廠商
            
            // 判斷類型：E欄是"國鼎"就是訂單，其他都是租賃單
            if ($manufacturer === '國鼎') {
                $orderCount++;
            } else {
                $rentalCount++;
                
                // 統計租賃單的廠商分佈（用E欄廠商名稱）
                $manufacturerName = $manufacturer ?: '未指定廠商';
                if (!isset($manufacturerSummary[$manufacturerName])) {
                    $manufacturerSummary[$manufacturerName] = 0;
                }
                $manufacturerSummary[$manufacturerName]++;
            }
        }

        return [
            'rental_data' => [
                'total_count' => $rentalCount,
                'locations' => $manufacturerSummary // 租賃單明細（按廠商分組）
            ],
            'order_data' => [
                'total_count' => $orderCount,
            ],
            'total_records' => count($data)
        ];
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
            $this->clearMemoryData();

            return $this->response->setJSON([
                'success' => true,
                'message' => "匯入完成！新增 {$created} 筆、更新 {$updated} 筆",
                'data' => [ 'created' => $created, 'updated' => $updated ]
            ]);
        } catch (\Exception $e) {
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

    // 舊版保留的方法移除，避免混淆
}
