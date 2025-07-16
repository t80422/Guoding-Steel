<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Files\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExcelController extends BaseController
{

    // 主頁
    public function index()
    {
        return view('excel/index');
    }

    // 匯入Excel
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

            // 讀取Excel檔案到記憶體
            $excelResult = $this->readExcelFile($file);

            if (!$excelResult['success']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $excelResult['message']
                ]);
            }

            // 將處理後的資料暫存到記憶體（session）
            $this->storeDataInMemory($excelResult['data'], $file->getClientName());

            // 總結統計資料回傳給前端顯示
            $summaryData = $this->getSummaryData($excelResult['data']);

            return $this->response->setJSON([
                'success' => true,
                'data' => $summaryData
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

    // 儲存匯入的資料
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

            // 您在此處理資料庫儲存邏輯
            $result = $this->saveDataToDatabase($memoryData['data']);

            // 清除記憶體中的暫存資料
            $this->clearMemoryData();

            return $this->response->setJSON([
                'success' => true,
                'message' => "匯入完成！新增 {$result['created_count']} 筆、更新 {$result['updated_count']} 筆",
                'data' => $result
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

    /**
     * 將資料儲存到資料庫 - 您自己實作
     */
    private function saveDataToDatabase(array $data): array
    {
        // TODO: 您在此實作資料庫儲存邏輯
        // 現在 $data 包含了結構化的資料和原始Excel資料陣列

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($data as $record) {
            // 取得處理後的資料
            $carNo = $record['carNo'];          // B欄 - 車號
            $no = $record['no'];                // C欄 - 編號
            $date = $record['date'];            // D欄 - 日期
            $manufacturer = $record['manufacturer']; // E欄 - 廠商
            $location = $record['location'];    // F欄 - 地點
            $type = $record['type'];            // rental 或 order
            $totalQuantity = $record['totalQuantity']; // 總數量
            $quantities = $record['quantities']; // 各欄位數量
            $rawRecord = $record['raw_record']; // 完整Excel原始資料

            // 根據類型儲存到不同的資料表
            if ($type === 'rental') {
                // 儲存到租賃相關的 Model
                // 檢查是否已存在（更新）或新增
                // 可以用 $carNo 或 $no 作為唯一識別
                // $createdCount++ 或 $updatedCount++
            } else {
                // 儲存到訂單相關的 Model
                // $createdCount++ 或 $updatedCount++
            }
        }

        return [
            'created_count' => $createdCount,
            'updated_count' => $updatedCount
        ];
    }
}
