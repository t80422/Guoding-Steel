<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'o_id';
    protected $allowedFields    = [
        'o_type',
        'o_from_location',
        'o_to_location',
        'o_date',
        'o_car_number',
        'o_driver_phone',
        'o_loading_time',
        'o_unloading_time',
        'o_g_id',
        'o_oxygen',
        'o_acetylene',
        'o_remark',
        'o_driver_signature',
        'o_from_signature',
        'o_to_signature',
        'o_create_by',
        'o_update_by',
        'o_update_at',
        'o_status',
        'o_number',
        'o_img_car_head',
        'o_img_car_tail'
    ];

    public const TYPE_IN_WAREHOUSE = 0; // 進倉庫
    public const TYPE_OUT_WAREHOUSE = 1; // 出倉庫
    public const TYPE_TRANSFER_WAREHOUSE = 2; // 轉倉庫

    public const STATUS_IN_PROGRESS = 0; // 進行中
    public const STATUS_COMPLETED = 1; // 完成

    /**
     * 根據 o_type 值取得中文名稱
     *
     * @param int $typeValue
     * @return string
     */
    public static function getTypeName(int $typeValue): string
    {
        switch ($typeValue) {
            case self::TYPE_IN_WAREHOUSE:
                return '進倉庫';
            case self::TYPE_OUT_WAREHOUSE:
                return '出倉庫';
            case self::TYPE_TRANSFER_WAREHOUSE:
                return '轉倉庫';
            default:
                return '';
        }
    }

    /**
     * 根據 o_status 值取得中文名稱
     *
     * @param int $statusValue
     * @return string
     */
    public static function getStatusName(int $statusValue): string
    {
        switch ($statusValue) {
            case self::STATUS_IN_PROGRESS:
                return '進行中';
            case self::STATUS_COMPLETED:
                return '完成';
            default:
                return '';
        }
    }

    /**
     * 獲取訂單基礎查詢建構器
     *
     * @return \CodeIgniter\Database\QueryBuilder
     */
    private function baseQuery()
    {
        return $this->builder('orders o')
            ->join('locations l1', 'l1.l_id = o.o_from_location', 'left')
            ->join('locations l2', 'l2.l_id = o.o_to_location', 'left')
            ->join('users u1', 'u1.u_id = o.o_create_by', 'left')
            ->join('users u2', 'u2.u_id = o.o_update_by', 'left')
            ->join('gps g', 'g.g_id = o.o_g_id', 'left')
            ->select('
                o.*,
                l1.l_name as from_location_name,
                l2.l_name as to_location_name,
                u1.u_name as create_name,
                u2.u_name as update_name,
                g.g_name as gps_name
            ');
    }

    /**
     * 取得訂單列表
     *
     * @param string|null $keyword
     * @param string|null $orderDateStart
     * @param string|null $orderDateEnd
     * @return array
     */
    public function getList($keyword = null, $orderDateStart = null, $orderDateEnd = null, $type = null, int $page = 1, int $perPage = 10)
    {
        $builder = $this->baseQuery();

        // 加入明細重量彙總的子查詢（公斤）
        $subSql = $this->db->table('order_details')
            ->select('od_o_id, SUM(od_weight) AS total_kg')
            ->groupBy('od_o_id')
            ->getCompiledSelect();

        $builder->join("($subSql) od_sum", 'od_sum.od_o_id = o.o_id', 'left')
            ->select('COALESCE(od_sum.total_kg, 0) AS total_kg', false);

        if ($keyword) {
            $builder->groupStart()
                ->like('o.o_id', $keyword)
                ->orLike('l1.l_name', $keyword)
                ->orLike('l2.l_name', $keyword)
                ->orLike('o.o_car_number', $keyword)
                ->groupEnd();
        }

        if ($orderDateStart) {
            $builder->where('o.o_date >= ', $orderDateStart);
        }

        if ($orderDateEnd) {
            $builder->where('o.o_date <= ', $orderDateEnd);
        }

        if (is_numeric($type)) {
            $builder->where('o.o_type', $type);
        }

        $builder->orderBy('o.o_date', 'DESC')
            ->orderBy('o.o_id', 'DESC');

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $totalCount = $builder->countAllResults(false);
        $totalPages = (int) ceil($totalCount / $perPage);

        $results = $builder
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        foreach ($results as &$row) {
            $row['typeName'] = self::getTypeName($row['o_type']);
            $row['o_status'] = self::getStatusName($row['o_status']);
            // 換算噸數（保留兩位小數），不顯示單位
            $sumKg = (float) ($row['total_kg'] ?? 0);
            $row['o_total_tons'] = number_format($sumKg / 1000, 2, '.', '');
        }

        return [
            'data' => $results,
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalCount' => $totalCount,
                'totalPages' => $totalPages,
            ],
        ];
    }

    /**
     * 取得訂單詳細資料
     *
     * @param int $id
     * @return array
     */
    public function getDetail($id)
    {
        return $this->baseQuery()
            ->where('o.o_id', $id)
            ->get()->getRowArray();
    }

    /**
     * 取得特定地點的用料情況
     *
     * @param int $locationId 地點ID
     * @return array
     */
    public function getMaterialUsageByLocation($locationId)
    {
        $builder = $this->baseQuery()
            ->groupStart()
            ->where('o.o_from_location', $locationId)
            ->orWhere('o.o_to_location', $locationId)
            ->groupEnd()
            ->orderBy('o.o_date', 'DESC');

        $results = $builder->get()->getResultArray();

        // 格式化資料並加入中文名稱
        foreach ($results as &$row) {
            $row['typeName'] = self::getTypeName($row['o_type']);

            // 判斷倉庫名稱（如果是從該地點出去，顯示目的地；如果是到該地點，顯示來源地）
            if ($row['o_from_location'] == $locationId) {
                $row['warehouse'] = $row['to_location_name'] ?? '未知地點';
            } else {
                $row['warehouse'] = $row['from_location_name'] ?? '未知地點';
            }

            // 格式化顯示用的欄位
            $row['vehicle_no'] = $row['o_car_number'];
            $row['date'] = $row['o_date'];
            $row['type'] = $row['typeName'];
        }

        return $results;
    }

    /**
     * 取得特定地點的詳細用料情況 (包含工地項目和產品明細)
     *
     * @param int $locationId 地點ID
     * @param array $searchParams 搜尋參數
     * @return array
     */
    public function getMaterialDetailsWithProjectsByLocation($locationId, $searchParams = [])
    {
        $builder = $this->db->table('orders o')
            ->join('locations l1', 'l1.l_id = o.o_from_location', 'left')
            ->join('locations l2', 'l2.l_id = o.o_to_location', 'left')
            ->join('order_details od', 'o.o_id = od.od_o_id', 'left')
            ->join('products p', 'od.od_pr_id = p.pr_id', 'left')
            ->join('minor_categories mic', 'p.pr_mic_id = mic.mic_id', 'left')
            ->join('order_detail_project_items odpi', 'od.od_id = odpi.odpi_od_id', 'left')
            ->join('project_items pi', 'odpi.odpi_pi_id = pi.pi_id', 'left')
            ->select('
                o.o_id,
                o.o_car_number,
                o.o_date,
                o.o_type,
                o.o_from_location,
                o.o_to_location,
                l1.l_name as from_location_name,
                l2.l_name as to_location_name,
                pi.pi_id as project_id,
                pi.pi_sort as project_sort,
                pi.pi_name as project_name,
                CASE 
                    WHEN mic.mic_name = p.pr_name THEN p.pr_name
                    ELSE CONCAT(mic.mic_name, p.pr_name)
                END as product_name,
                od.od_length,
                odpi.odpi_qty
            ')
            ->groupStart()
            ->where('o.o_from_location', $locationId)
            ->orWhere('o.o_to_location', $locationId)
            ->groupEnd();

        // 加入搜尋條件
        if (!empty($searchParams['start_date'])) {
            $builder->where('o.o_date >=', $searchParams['start_date']);
        }

        if (!empty($searchParams['end_date'])) {
            $builder->where('o.o_date <=', $searchParams['end_date']);
        }

        if (isset($searchParams['type']) && $searchParams['type'] !== '') {
            $builder->where('o.o_type', $searchParams['type']);
        }

        if (!empty($searchParams['keyword'])) {
            $keyword = $searchParams['keyword'];
            $builder->groupStart()
                ->like('o.o_car_number', $keyword)
                ->orLike('l1.l_name', $keyword)
                ->orLike('l2.l_name', $keyword)
                ->groupEnd();
        }

        $builder->orderBy('o.o_date', 'DESC')
            ->orderBy('o.o_id', 'ASC');

        $rawResults = $builder->get()->getResultArray();

        // 整理資料結構
        return $this->formatMaterialUsageData($rawResults, $locationId);
    }

    /**
     * 格式化用料情況資料
     *
     * @param array $rawResults
     * @param int $locationId
     * @return array
     */
    private function formatMaterialUsageData($rawResults, $locationId)
    {
        $orders = [];
        $allProjects = [];
        $allProducts = [];
        $projectSorts = [];

        // 按訂單分組處理資料
        foreach ($rawResults as $row) {
            $orderId = $row['o_id'];

            // 初始化訂單基本資訊
            if (!isset($orders[$orderId])) {
                // 判斷是加還是減：當前工地在 o_to_location 是加，在 o_from_location 是減
                $isIncrease = ($row['o_to_location'] == $locationId);

                $orders[$orderId] = [
                    'o_id' => $orderId,
                    'vehicle_no' => $row['o_car_number'],
                    'date' => $row['o_date'],
                    'type' => self::getTypeName($row['o_type']),
                    'warehouse' => $this->getWarehouseName($row, $locationId),
                    'is_increase' => $isIncrease,
                    'projects' => []
                ];
            }

            // 如果有項目和產品資料
            if ($row['project_name'] && $row['product_name']) {
                $projectId = $row['project_id'];
                $projectName = $row['project_name'];
                $productName = $row['product_name'];
                $length = (float)($row['od_length'] ?? 0);
                $quantity = (float)($row['odpi_qty'] ?? 0);
                $projectSort = isset($row['project_sort']) ? (int)$row['project_sort'] : PHP_INT_MAX;

                // 使用產品名稱作為唯一鍵（合併相同產品的不同長度）
                $productKey = $productName;

                // 記錄所有出現過的項目和產品（用於動態表頭），記錄 pi_id 用於排序
                $allProjects[$projectName] = $projectId;
                $projectSorts[$projectName] = [
                    'sort' => $projectSort,
                    'id' => $projectId ?? PHP_INT_MAX
                ];
                $allProducts[$projectName][$productKey] = [
                    'display_name' => $productName
                ];

                // 整理項目下的產品資料
                if (!isset($orders[$orderId]['projects'][$projectName])) {
                    $orders[$orderId]['projects'][$projectName] = [];
                }

                if (!isset($orders[$orderId]['projects'][$projectName][$productKey])) {
                    $orders[$orderId]['projects'][$projectName][$productKey] = [
                        'quantity' => 0.0,
                        'length' => 0,
                        'display_name' => $productName
                    ];
                }

                // 累加數量並將米數乘以數量
                $orders[$orderId]['projects'][$projectName][$productKey]['quantity'] += $quantity;
                $orders[$orderId]['projects'][$projectName][$productKey]['length'] += $length * $quantity;
            }
        }

        // 過濾掉沒有產品明細的訂單（projects 為空的訂單）
        $filteredOrders = array_filter($orders, function ($order) {
            return !empty($order['projects']);
        });

        // 按照 pi_sort（次要以 pi_id 與名稱）排序專案
        $sortedProjects = array_keys($projectSorts);
        usort($sortedProjects, function ($a, $b) use ($projectSorts) {
            $aSort = $projectSorts[$a]['sort'] ?? PHP_INT_MAX;
            $bSort = $projectSorts[$b]['sort'] ?? PHP_INT_MAX;
            if ($aSort === $bSort) {
                $aId = $projectSorts[$a]['id'] ?? PHP_INT_MAX;
                $bId = $projectSorts[$b]['id'] ?? PHP_INT_MAX;
                if ($aId === $bId) {
                    return strcmp($a, $b);
                }
                return $aId <=> $bId;
            }
            return $aSort <=> $bSort;
        });

        return [
            'orders' => array_values($filteredOrders),
            'all_projects' => $sortedProjects,
            'all_products' => $allProducts,
            'project_sorts' => $projectSorts
        ];
    }

    /**
     * 依地點與產品彙總訂單的長度總和（不乘數量，無日期篩選）
     *
     * @param int[] $locationIds o_to_location 清單
     * @param int[] $productIds od_pr_id 清單
     * @return array<int,array{location_id:int,product_id:int,total_length:float}>
     */
    public function getLengthSumsByLocationAndProduct(array $locationIds, array $productIds): array
    {
        if (empty($locationIds) || empty($productIds)) {
            return [];
        }

        // 建立 UNION ALL 查詢：to_location 為正，from_location 為負
        $locationIdsStr = implode(',', array_map('intval', $locationIds));
        $productIdsStr = implode(',', array_map('intval', $productIds));

        $sql = "
            SELECT location_id, product_id, SUM(length_change) AS total_length
            FROM (
                SELECT o.o_to_location AS location_id, od.od_pr_id AS product_id, od.od_length AS length_change
                FROM orders o
                JOIN order_details od ON od.od_o_id = o.o_id
                WHERE o.o_to_location IN ({$locationIdsStr})
                  AND od.od_pr_id IN ({$productIdsStr})
                
                UNION ALL
                
                SELECT o.o_from_location AS location_id, od.od_pr_id AS product_id, -od.od_length AS length_change
                FROM orders o
                JOIN order_details od ON od.od_o_id = o.o_id
                WHERE o.o_from_location IN ({$locationIdsStr})
                  AND od.od_pr_id IN ({$productIdsStr})
            ) AS combined
            GROUP BY location_id, product_id
        ";

        $rows = $this->db->query($sql)->getResultArray();

        // 正規化型別
        foreach ($rows as &$row) {
            $row['location_id'] = (int) $row['location_id'];
            $row['product_id'] = (int) $row['product_id'];
            $row['total_length'] = (float) $row['total_length'];
        }
        return $rows;
    }

    /**
     * 取得倉庫名稱
     *
     * @param array $row
     * @param int $locationId
     * @return string
     */
    private function getWarehouseName($row, $locationId)
    {
        if ($row['o_from_location'] == $locationId) {
            return $row['to_location_name'] ?? '未知地點';
        } else {
            return $row['from_location_name'] ?? '未知地點';
        }
    }

    /**
     * 取得進行中的訂單
     *
     * @return array
     */
    public function getByInProgress()
    {
        return $this->baseQuery()
            ->where('o.o_status', self::STATUS_IN_PROGRESS)
            ->orderBy('o.o_id', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * 取得已完成的訂單
     *
     * @return array
     */
    public function getByCompleted()
    {
        return $this->baseQuery()
            ->where('o.o_status', self::STATUS_COMPLETED)
            ->orderBy('o.o_date', 'DESC')
            ->orderBy('o.o_id', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * 生成訂單編號
     *
     * @return string
     */
    public function generateOrderNumber()
    {
        $prefix = date('Ym');
        $lastOrder = $this->like('o_number', $prefix, 'after')->orderBy('o_number', 'DESC')->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder['o_number'], 6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
