<?php

namespace App\Services;

use App\Models\LocationModel;
use App\Models\ManufacturerModel;
use App\Models\ProductModel;
use App\Models\ProjectItemModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * 解析新版 Excel 匯入格式，彙整錯誤、輸出可落地 orders/rental_orders 的結構。
 */
class ExcelImportService
{
    private ProductModel $productModel;
    private ProjectItemModel $projectItemModel;
    private LocationModel $locationModel;
    private ManufacturerModel $manufacturerModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->projectItemModel = new ProjectItemModel();
        $this->locationModel = new LocationModel();
        $this->manufacturerModel = new ManufacturerModel();
    }

    /**
     * 解析上傳的 Excel，回傳成功與否、錯誤清單、摘要與落地資料
     *
     * @return array{
     *   success: bool,
     *   errors?: array<int,array<string,mixed>>,
     *   summary?: array<string,mixed>,
     *   data?: array<string,mixed>
     * }
     */
    public function parse(UploadedFile $file): array
    {
        try {
            $filePath = $file->getTempName();
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
            if ($highestRow < 6) {
                $this->cleanupSpreadsheet($spreadsheet);
                return [
                    'success' => false,
                    'errors' => [
                        [
                            'type' => 'file_structure',
                            'message' => 'Excel 檔案資料不足，至少需要 6 列（第1列項目、第5列表頭、第6列起資料）'
                        ]
                    ]
                ];
            }

            // 準備字典：產品、項目、地點、廠商
            $dict = $this->buildDictionaries();

            // 建立第1列「工地項目」對每個欄位的名稱（處理合併儲存格）
            $projectItemHeaderMap = $this->buildProjectItemHeaderMap($sheet, $highestCol);
            // 自 G 起（7）掃到最右，每 2 欄一組：左=數量、右=產品名/米數
            $startCol = 7; // G
            $productHeaderByPair = $this->buildProductHeaderByPair($sheet, $startCol, $highestCol);

            $errors = [];
            $orders = [];
            $rentals = [];
            $stats = [
                'order_count' => 0,
                'rental_count' => 0,
                'project_item_counts' => [],
                'location_counts' => []  // 統計各工地的租賃單數量
            ];

            // 第6列起逐列掃描
            for ($row = 6; $row <= $highestRow; $row++) {
                $carNo = trim((string) $this->getCellCalculatedValue($sheet, 1, $row)); // A
                $number = trim((string) $this->getCellCalculatedValue($sheet, 2, $row)); // B
                $dateRaw = $this->getCellCalculatedValue($sheet, 3, $row); // C
                $manufacturerName = trim((string) $this->getCellCalculatedValue($sheet, 4, $row)); // D
                $eValue = trim((string) $this->getCellCalculatedValue($sheet, 5, $row)); // E

                // 若 A~E 皆為空，視為資料結束，停止掃描
                $isEmptyA = ($carNo === '');
                $isEmptyB = ($number === '');
                $isEmptyC = ($dateRaw === null || $dateRaw === '');
                $isEmptyD = ($manufacturerName === '');
                $isEmptyE = ($eValue === '');
                if ($isEmptyA && $isEmptyB && $isEmptyC && $isEmptyD && $isEmptyE) {
                    break;
                }

                // 基本欄位檢查：A、B、C、E必填
                if ($carNo === '' || $number === '' || $dateRaw === null || $eValue === '') {
                    $missing = [];
                    if ($carNo === '') $missing[] = 'A(車號)';
                    if ($number === '') $missing[] = 'B(單號)';
                    if ($dateRaw === null || $dateRaw === '') $missing[] = 'C(日期)';
                    if ($eValue === '') $missing[] = 'E(地點資訊)';
                    $errors[] = [
                        'row' => $row,
                        'type' => 'basic_missing',
                        'message' => '缺少必填欄位：' . implode('、', $missing)
                    ];
                    // 缺基本欄位則跳過此列
                    continue;
                }

                // 日期轉換
                $date = $this->convertExcelDate($dateRaw);
                if ($date === null) {
                    $errors[] = [
                        'row' => $row,
                        'type' => 'date_parse_error',
                        'message' => 'C(日期) 無法解析'
                    ];
                    continue;
                }

                // 解析 E 欄內容
                $eParseResult = $this->parseEColumnContent($eValue, $dict);
                if (!$eParseResult['success']) {
                    $errors[] = [
                        'row' => $row,
                        'type' => 'e_column_error',
                        'message' => $eParseResult['error']
                    ];
                    continue;
                }

                // 根據E欄是否包含廠商決定建立訂單或租賃單
                $hasManufacturer = $eParseResult['has_manufacturer'];
                
                if (!$hasManufacturer) {
                    // 沒有廠商 → 建立訂單（兩端都是地點）
                    $locLeft = $eParseResult['left_data'];
                    $locRight = $eParseResult['right_data'];
                    
                    $leftType = (int) $locLeft['l_type'];
                    $rightType = (int) $locRight['l_type'];
                    
                    // 決定訂單類型和From/To邏輯
                    $oType = null;
                    $oFrom = (int) $locLeft['l_id'];
                    $oTo = (int) $locRight['l_id'];
                    
                    // 根據地點組合決定類型
                    if ($leftType === LocationModel::TYPE_WAREHOUSE && $rightType === LocationModel::TYPE_WAREHOUSE) {
                        // 倉庫-倉庫：出倉庫
                        $oType = 1;
                    } elseif ($leftType === LocationModel::TYPE_CONSTRUCTION_SITE && $rightType === LocationModel::TYPE_CONSTRUCTION_SITE) {
                        // 工地-工地：出倉庫
                        $oType = 1;
                    } elseif ($leftType === LocationModel::TYPE_WAREHOUSE && $rightType === LocationModel::TYPE_CONSTRUCTION_SITE) {
                        // 倉庫-工地：出倉庫
                        $oType = 1;
                    } elseif ($leftType === LocationModel::TYPE_CONSTRUCTION_SITE && $rightType === LocationModel::TYPE_WAREHOUSE) {
                        // 工地-倉庫：進倉庫
                        $oType = 0;
                    } else {
                        $errors[] = [
                            'row' => $row,
                            'type' => 'unsupported_location_combination',
                            'message' => 'E欄地點組合不支援：' . $eValue
                        ];
                        continue;
                    }

                    $rowResult = [
                        'header' => [
                            'o_date' => $date,
                            'o_car_number' => $carNo,
                            'o_number' => $number,
                            'o_type' => $oType,
                            'o_from_location' => $oFrom,
                            'o_to_location' => $oTo,
                            'o_status' => 1,
                        ],
                        'details' => [] // 後面補
                    ];

                    $detailsBundle = $this->collectDetailsForRow($sheet, $row, $startCol, $highestCol, $projectItemHeaderMap, $productHeaderByPair, $dict, $errors);
                    $rowResult['details'] = $detailsBundle['details'];
                    $stats['order_count']++;
                    $this->accumulateProjectStats($stats['project_item_counts'], $detailsBundle['projectAllocations']);
                    $orders[] = $rowResult;
                } else {
                    // 有廠商 → 建立租賃單
                    $leftType = $eParseResult['left_type'];
                    $rightType = $eParseResult['right_type'];
                    $leftData = $eParseResult['left_data'];
                    $rightData = $eParseResult['right_data'];
                    
                    // 找出廠商和工地
                    $manufacturer = null;
                    $location = null;
                    $manufacturerOnLeft = false;
                    
                    if ($leftType === 'manufacturer') {
                        $manufacturer = $leftData;
                        $location = $rightData;
                        $manufacturerOnLeft = true;
                    } else {
                        $manufacturer = $rightData;
                        $location = $leftData;
                        $manufacturerOnLeft = false;
                    }
                    
                    
                    // 地點在前 → 出貨(1)，地點在後 → 進貨(0)
                    $roType = $manufacturerOnLeft ? 0 : 1;

                    $rowResult = [
                        'header' => [
                            'ro_type' => $roType,
                            'ro_ma_id' => (int) $manufacturer['ma_id'],
                            'ro_l_id' => (int) $location['l_id'],
                            'ro_date' => $date,
                            'ro_car_number' => $carNo,
                            'ro_number' => $number,
                        ],
                        'details' => [] // 後面補
                    ];

                    $detailsBundle = $this->collectDetailsForRow($sheet, $row, $startCol, $highestCol, $projectItemHeaderMap, $productHeaderByPair, $dict, $errors);
                    $rowResult['details'] = $detailsBundle['details'];
                    $stats['rental_count']++;
                    
                    // 統計工地的租賃單數量
                    $locationName = $location['l_name'];
                    $stats['location_counts'][$locationName] = ($stats['location_counts'][$locationName] ?? 0) + 1;
                    
                    $this->accumulateProjectStats($stats['project_item_counts'], $detailsBundle['projectAllocations']);
                    $rentals[] = $rowResult;
                }
            }

            $this->cleanupSpreadsheet($spreadsheet);

            if (!empty($errors)) {
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }

            return [
                'success' => true,
                'summary' => [
                    'rental_data' => [
                        'total_count' => $stats['rental_count'],
                        'locations' => $stats['location_counts']
                    ],
                    'order_data' => [
                        'total_count' => $stats['order_count']
                    ],
                    'total_records' => $stats['order_count'] + $stats['rental_count']
                ],
                'data' => [
                    'orders' => $orders,
                    'rentals' => $rentals
                ]
            ];
        } catch (ReaderException $e) {
            return [
                'success' => false,
                'errors' => [[ 'type' => 'reader_exception', 'message' => $e->getMessage() ]]
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'errors' => [[ 'type' => 'exception', 'message' => $e->getMessage() ]]
            ];
        }
    }

    private function cleanupSpreadsheet($spreadsheet): void
    {
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    /**
     * 將 Excel 的日期轉為 Y-m-d；失敗回 null
     */
    private function convertExcelDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            try {
                $dateObj = ExcelDate::excelToDateTimeObject($value);
                return $dateObj->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }
        // 嘗試當成字串日期
        $ts = strtotime((string) $value);
        if ($ts === false) {
            return null;
        }
        return date('Y-m-d', $ts);
    }

    /**
     * 準備查詢字典
     * @return array{
     *  products_by_key: array<string,int>,
     *  products_by_name: array<string,array<int,int>>, // pr_name => [pr_id,...]
     *  products_mic_by_pr_id: array<int,string>,
     *  locations_by_name: array<string,array<string,mixed>>,
     *  manufacturers_by_name: array<string,array<string,mixed>>,
     *  project_items_by_name: array<string,int>
     * }
     */
    private function buildDictionaries(): array
    {
        // 產品（含小分類名稱）
        $rows = $this->productModel
            ->builder('products pr')
            ->join('minor_categories mic', 'mic.mic_id = pr.pr_mic_id', 'left')
            ->select('pr.pr_id, pr.pr_name, mic.mic_name')
            ->get()->getResultArray();

        $productsByKey = [];
        $productsByName = [];
        $productsMicByPrId = [];
        foreach ($rows as $r) {
            $prId = (int) $r['pr_id'];
            $prName = (string) $r['pr_name'];
            $micName = (string) ($r['mic_name'] ?? '');
            $key = trim($micName) !== '' ? ($micName . '-' . $prName) : $prName;
            $productsByKey[$key] = $prId;
            $productsByName[$prName] = $productsByName[$prName] ?? [];
            $productsByName[$prName][] = $prId;
            $productsMicByPrId[$prId] = $micName;
        }

        // 地點
        $locRows = $this->locationModel->select('l_id,l_name,l_type')->get()->getResultArray();
        $locationsByName = [];
        foreach ($locRows as $r) {
            $locationsByName[$r['l_name']] = $r;
        }
        


        // 廠商
        $maRows = $this->manufacturerModel->select('ma_id,ma_name')->get()->getResultArray();
        $manufacturersByName = [];
        foreach ($maRows as $r) {
            $manufacturersByName[$r['ma_name']] = $r;
        }

        // 工地項目
        $piRows = $this->projectItemModel->select('pi_id,pi_name')->get()->getResultArray();
        $projectItemsByName = [];
        foreach ($piRows as $r) {
            $projectItemsByName[$r['pi_name']] = (int) $r['pi_id'];
        }

        return [
            'products_by_key' => $productsByKey,
            'products_by_name' => $productsByName,
            'products_mic_by_pr_id' => $productsMicByPrId,
            'locations_by_name' => $locationsByName,
            'manufacturers_by_name' => $manufacturersByName,
            'project_items_by_name' => $projectItemsByName,
        ];
    }

    /**
     * 解析第 1 列的工地項目（處理合併儲存格），回傳每個欄位的項目名稱（可為空字串）
     * @return array<int,string>
     */
    private function buildProjectItemHeaderMap(Worksheet $sheet, int $highestCol): array
    {
        $map = [];
        $mergeCells = $sheet->getMergeCells();

        // 建立 quick lookup：row1 的合併區段
        $ranges = [];
        foreach ($mergeCells as $range) {
            // ex: A1:C1
            [$c1, $c2] = explode(':', $range);
            $startCol = Coordinate::columnIndexFromString(preg_replace('/\d+/', '', $c1));
            $startRow = (int) preg_replace('/\D+/', '', $c1);
            $endCol = Coordinate::columnIndexFromString(preg_replace('/\d+/', '', $c2));
            $endRow = (int) preg_replace('/\D+/', '', $c2);
            if ($startRow === 1 && $endRow === 1) {
                $ranges[] = [$startCol, $endCol, (string) $this->getCellCalculatedValue($sheet, $startCol, 1)];
            }
        }

        for ($col = 1; $col <= $highestCol; $col++) {
            $name = '';
            // 找看看是否在某個 row1 合併範圍內
            foreach ($ranges as [$s, $e, $val]) {
                if ($col >= $s && $col <= $e) {
                    $name = trim((string) $val);
                    break;
                }
            }
            if ($name === '') {
                // 非合併或未涵蓋，取自身
                $name = trim((string) $this->getCellCalculatedValue($sheet, $col, 1));
            }
            $map[$col] = $name;
        }
        return $map;
    }

    /**
     * 建立每組（兩欄）對應的產品表頭（右欄，第5列）。
     * @return array<int,string> key: 左欄索引(數量欄), value: 右欄產品名
     */
    private function buildProductHeaderByPair(Worksheet $sheet, int $startCol, int $highestCol): array
    {
        $map = [];
        for ($col = $startCol; $col <= $highestCol; $col += 2) {
            $prodName = trim((string) $this->getCellCalculatedValue($sheet, $col + 1, 5));
            if ($prodName !== '') {
                $map[$col] = $prodName; // 左欄索引 -> 產品名（右欄）
            }
        }
        return $map;
    }

    /**
     * 解析單列的所有明細，彙總成 (pr_id,length)=>total 與各項目配置
     * @return array{
     *  details: array<int,array{pr_id:int, length:float, qty:int, allocations: array<int|null,int>}>,
     *  projectAllocations: array<int,int>
     * }
     */
    private function collectDetailsForRow(
        Worksheet $sheet,
        int $row,
        int $startCol,
        int $highestCol,
        array $projectItemHeaderMap,
        array $productHeaderByPair,
        array &$dict,
        array &$errors
    ): array {
        $detailMap = []; // key: pr_id|length
        $allocMap = []; // pi_id => total qty

        for ($col = $startCol; $col <= $highestCol; $col += 2) {
            if (!isset($productHeaderByPair[$col])) {
                continue; // 產品名空白的組，跳過
            }
            $prodHeader = $productHeaderByPair[$col];

            // 解析產品：優先 mic-pr 精準；否則使用 pr_name，若多筆 → 錯誤
            $prId = $this->resolveProductId($prodHeader, $dict, $errors, $row, $col);
            if ($prId === null) {
                continue; // 產品錯誤，已記錄錯誤
            }

            // 數量與米數
            $qtyRaw = $this->getCellCalculatedValue($sheet, $col, $row);
            $lenRaw = $this->getCellCalculatedValue($sheet, $col + 1, $row);
            $qty = (int) (is_numeric($qtyRaw) ? $qtyRaw : 0);
            $length = (float) (is_numeric($lenRaw) ? $lenRaw : 0);
            if ($qty <= 0) {
                continue; // 僅米數或無效數量 → 不保留
            }

            // 項目（第1列，以右欄為對應即可）
            $piName = $projectItemHeaderMap[$col + 1] ?? '';
            $piId = null;
            
            if ($piName !== '') {
                $piId = $dict['project_items_by_name'][$piName] ?? null;
                
                if ($piId === null) {
                    $errors[] = [
                        'row' => $row,
                        'col' => $col + 1,
                        'type' => 'unknown_project_item',
                        'message' => '第1列工地項目找不到：' . $piName
                    ];
                    // 即使項目錯，仍計入產品總量，項目配置忽略
                }
            }

            $key = $prId . '|' . $length;
            if (!isset($detailMap[$key])) {
                $detailMap[$key] = [
                    'pr_id' => $prId,
                    'length' => $length,
                    'qty' => 0,
                    'allocations' => [] // pi_id => qty
                ];
            }
            $detailMap[$key]['qty'] += $qty;
            if ($piId !== null) {
                $detailMap[$key]['allocations'][$piId] = ($detailMap[$key]['allocations'][$piId] ?? 0) + $qty;
                $allocMap[$piId] = ($allocMap[$piId] ?? 0) + $qty;
            }
        }

        return [
            'details' => array_values($detailMap),
            'projectAllocations' => $allocMap
        ];
    }

    /**
     * 解析產品表頭為 pr_id
     */
    private function resolveProductId(string $header, array &$dict, array &$errors, int $row, int $col): ?int
    {
        $header = trim($header);
        if ($header === '') return null;

        if (strpos($header, '-') !== false) {
            // mic-pr 精準
            if (isset($dict['products_by_key'][$header])) {
                return (int) $dict['products_by_key'][$header];
            }
            $errors[] = [
                'row' => $row,
                'col' => $col + 1,
                'type' => 'unknown_product',
                'message' => '找不到產品（小分類-產品）：' . $header
            ];
            return null;
        }

        // 僅產品名：若多筆同名 → 無法唯一判定 → 錯誤
        $candidates = $dict['products_by_name'][$header] ?? [];
        if (count($candidates) === 1) {
            return (int) $candidates[0];
        }
        if (count($candidates) === 0) {
            $errors[] = [
                'row' => $row,
                'col' => $col + 1,
                'type' => 'unknown_product',
                'message' => '找不到產品：' . $header
            ];
        } else {
            $errors[] = [
                'row' => $row,
                'col' => $col + 1,
                'type' => 'ambiguous_product',
                'message' => '產品名稱在資料庫非唯一，請使用「小分類-產品」指定：' . $header
            ];
        }
        return null;
    }

    private function accumulateProjectStats(array &$stats, array $allocMap): void
    {
        foreach ($allocMap as $piId => $qty) {
            $stats[$piId] = ($stats[$piId] ?? 0) + $qty;
        }
    }

    /**
     * 解析E欄內容，判斷左右兩側是廠商還是地點
     * @return array{
     *   success: bool,
     *   error?: string,
     *   left_type?: string,  // 'manufacturer' | 'location'
     *   right_type?: string, // 'manufacturer' | 'location'
     *   left_data?: array,
     *   right_data?: array,
     *   has_manufacturer?: bool
     * }
     */
    private function parseEColumnContent(string $eValue, array &$dict): array
    {
        $parts = explode('-', $eValue);
        if (count($parts) !== 2) {
            return [
                'success' => false,
                'error' => 'E欄格式錯誤，必須為「甲-乙」'
            ];
        }

        $left = trim($parts[0]);
        $right = trim($parts[1]);

        // 判斷左側是廠商還是地點
        $leftType = null;
        $leftData = null;
        if (isset($dict['manufacturers_by_name'][$left])) {
            $leftType = 'manufacturer';
            $leftData = $dict['manufacturers_by_name'][$left];
        } elseif (isset($dict['locations_by_name'][$left])) {
            $leftType = 'location';
            $leftData = $dict['locations_by_name'][$left];
        }

        // 判斷右側是廠商還是地點
        $rightType = null;
        $rightData = null;
        if (isset($dict['manufacturers_by_name'][$right])) {
            $rightType = 'manufacturer';
            $rightData = $dict['manufacturers_by_name'][$right];
        } elseif (isset($dict['locations_by_name'][$right])) {
            $rightType = 'location';
            $rightData = $dict['locations_by_name'][$right];
        }

        // 檢查是否有未知的名稱
        if ($leftType === null || $rightType === null) {
            $unknowns = [];
            if ($leftType === null) $unknowns[] = $left;
            if ($rightType === null) $unknowns[] = $right;
            return [
                'success' => false,
                'error' => 'E欄中找不到以下廠商或地點：' . implode('、', $unknowns)
            ];
        }

        // 檢查是否兩端都是廠商
        if ($leftType === 'manufacturer' && $rightType === 'manufacturer') {
            return [
                'success' => false,
                'error' => 'E欄兩端都是廠商，無法處理：' . $eValue
            ];
        }

        $hasManufacturer = ($leftType === 'manufacturer' || $rightType === 'manufacturer');

        return [
            'success' => true,
            'left_type' => $leftType,
            'right_type' => $rightType,
            'left_data' => $leftData,
            'right_data' => $rightData,
            'has_manufacturer' => $hasManufacturer
        ];
    }

    /**
     * 取得計算後的儲存格值（支援公式），以欄序與列序取值
     * @return mixed
     */
    private function getCellCalculatedValue(Worksheet $sheet, int $col, int $row)
    {
        $coord = Coordinate::stringFromColumnIndex($col) . $row;
        return $sheet->getCell($coord)->getCalculatedValue();
    }
}