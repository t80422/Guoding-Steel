<?php

namespace App\Models;

use CodeIgniter\Model;

class RentalOrderModel extends Model
{
    protected $table            = 'rental_orders';
    protected $primaryKey       = 'ro_id';
    protected $allowedFields    = [
        'ro_type',
        'ro_ma_id',
        'ro_l_id',
        'ro_date',
        'ro_car_number',
        'ro_driver_phone',
        'ro_loading_time',
        'ro_unloading_time',
        'ro_g_id',
        'ro_oxygen',
        'ro_acetylene',
        'ro_memo',
        'ro_create_by',
        'ro_update_by',
        'ro_update_at',
        'ro_number'
    ];

    public const TYPE_IN = 0; // 進工地
    public const TYPE_OUT = 1; // 出工地

    /**
     * 根據 ro_type 值取得中文名稱
     *
     * @param int $typeValue
     * @return string
     */
    public static function getTypeName(int $typeValue): string
    {
        switch ($typeValue) {
            case self::TYPE_IN:
                return '進工地';
            case self::TYPE_OUT:
                return '出工地';
            default:
                return '';
        }
    }

    /**
     * 獲取租賃基礎查詢建構器
     *
     * @return \CodeIgniter\Database\QueryBuilder
     */
    private function baseQuery()
    {
        return $this->builder('rental_orders ro')
            ->join('locations l', 'l.l_id = ro.ro_l_id','left')
            ->join('users u1', 'u1.u_id = ro.ro_create_by','left')
            ->join('users u2', 'u2.u_id = ro.ro_update_by', 'left')
            ->join('gps g', 'g.g_id = ro.ro_g_id', 'left')
            ->join('manufacturers m', 'm.ma_id = ro.ro_ma_id','left')
            ->select('
                ro.*, l.l_name, m.ma_name, u1.u_name as creator, u2.u_name as updater, g.g_name');
    }

    /**
     * 取得租賃列表
     *
     * @param string|null $keyword
     * @param string|null $rentalDateStart
     * @param string|null $rentalDateEnd
     * @return array
     */
    public function getList($keyword = null, $rentalDateStart = null, $rentalDateEnd = null, $type = null)
    {
        $builder = $this->baseQuery();

        if ($keyword) {
            $builder->groupStart()
                ->like('ro.ro_id', $keyword)
                ->orLike('l.l_name', $keyword)
                ->orLike('m.ma_name', $keyword)
                ->groupEnd();
        }

        if ($rentalDateStart) {
            $builder->where('ro.ro_date >= ', $rentalDateStart);
        }

        if ($rentalDateEnd) {
            $builder->where('ro.ro_date <= ', $rentalDateEnd);
        }

        if (is_numeric($type)) {
            $builder->where('ro.ro_type', $type);
        }

        $builder->orderBy('ro.ro_date', 'DESC');

        $results = $builder->get()->getResultArray();

        foreach ($results as &$row) {
            $row['typeName'] = self::getTypeName($row['ro_type']);
        }

        return $results;
    }

    /**
     * 取得租賃詳細資料
     *
     * @param int $id
     * @return array
     */
    public function getDetail($id)
    {
        return $this->baseQuery()
            ->where('ro.ro_id', $id)
            ->get()->getRowArray();
    }

    /**
     * 依地點與產品彙總租賃單的長度總和（僅進工地 ro_type=0，不乘數量，無日期篩選）
     *
     * @param int[] $locationIds ro_l_id 清單
     * @param int[] $productIds rod_pr_id 清單
     * @return array<int,array{location_id:int,product_id:int,total_length:float}>
     */
    public function getLengthSumsByLocationAndProduct(array $locationIds, array $productIds): array
    {
        if (empty($locationIds) || empty($productIds)) {
            return [];
        }

        $builder = $this->db->table('rental_orders ro')
            ->join('rental_order_details rod', 'rod.rod_ro_id = ro.ro_id')
            ->select('ro.ro_l_id AS location_id, rod.rod_pr_id AS product_id, COALESCE(SUM(rod.rod_length), 0) AS total_length', false)
            ->where('ro.ro_type', self::TYPE_IN)
            ->whereIn('ro.ro_l_id', $locationIds)
            ->whereIn('rod.rod_pr_id', $productIds)
            ->groupBy('ro.ro_l_id, rod.rod_pr_id');

        $rows = $builder->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['location_id'] = (int) $row['location_id'];
            $row['product_id'] = (int) $row['product_id'];
            $row['total_length'] = (float) $row['total_length'];
        }
        return $rows;
    }

    /**
     * 取得工地用料情況（包含工地項目和產品明細）
     *
     * @param int $locationId
     * @param array $searchParams
     * @return array
     */
    public function getMaterialDetailsByLocation($locationId, $searchParams = [])
    {
        $builder = $this->db->table('rental_orders ro')
            ->join('manufacturers m', 'm.ma_id = ro.ro_ma_id', 'left')
            ->join('rental_order_details rod', 'ro.ro_id = rod.rod_ro_id', 'left')
            ->join('products p', 'rod.rod_pr_id = p.pr_id', 'left')
            ->join('minor_categories mic', 'p.pr_mic_id = mic.mic_id', 'left')
            ->join('rental_order_detail_project_items rodpi', 'rod.rod_id = rodpi.rodpi_rod_id', 'left')
            ->join('project_items pi', 'rodpi.rodpi_pi_id = pi.pi_id', 'left')
            ->select('
                ro.ro_id,
                ro.ro_car_number,
                ro.ro_date,
                ro.ro_type,
                ro.ro_l_id,
                m.ma_name as manufacturer_name,
                pi.pi_id as project_id,
                pi.pi_sort as project_sort,
                pi.pi_name as project_name,
                CASE 
                    WHEN mic.mic_name = p.pr_name THEN p.pr_name
                    ELSE CONCAT(mic.mic_name, p.pr_name)
                END as product_name,
                rod.rod_length,
                rodpi.rodpi_qty
            ')
            ->where('ro.ro_l_id', $locationId);

        // 加入搜尋條件
        if (!empty($searchParams['start_date'])) {
            $builder->where('ro.ro_date >=', $searchParams['start_date']);
        }
        
        if (!empty($searchParams['end_date'])) {
            $builder->where('ro.ro_date <=', $searchParams['end_date']);
        }
        
        if (!empty($searchParams['keyword'])) {
            $builder->like('ro.ro_car_number', $searchParams['keyword']);
        }

        $builder->orderBy('ro.ro_date', 'DESC')
            ->orderBy('ro.ro_id', 'ASC');

        $rawResults = $builder->get()->getResultArray();

        // 整理資料結構
        return $this->formatRentalMaterialUsageData($rawResults, $locationId);
    }

    /**
     * 格式化租賃單用料情況資料
     *
     * @param array $rawResults
     * @param int $locationId
     * @return array
     */
    private function formatRentalMaterialUsageData($rawResults, $locationId)
    {
        $orders = [];
        $allProjects = [];
        $allProducts = [];
        $projectSorts = [];

        // 按租賃單分組處理資料
        foreach ($rawResults as $row) {
            $rentalId = $row['ro_id'];
            
            // 初始化租賃單基本資訊
            if (!isset($orders[$rentalId])) {
                // 判斷是加還是減：進工地(0)是加，出工地(1)是減
                $isIncrease = ($row['ro_type'] == self::TYPE_IN);
                
                $orders[$rentalId] = [
                    'ro_id' => $rentalId,
                    'vehicle_no' => $row['ro_car_number'],
                    'date' => $row['ro_date'],
                    'type' => self::getTypeName($row['ro_type']),
                    'warehouse' => $row['manufacturer_name'] ?? '',
                    'is_increase' => $isIncrease,
                    'projects' => []
                ];
            }

            // 如果有項目和產品資料
            if ($row['project_name'] && $row['product_name']) {
                $projectId = $row['project_id'] ?? null;
                $projectName = $row['project_name'];
                $productName = $row['product_name'];
                $length = (float)($row['rod_length'] ?? 0);
                $quantity = (int)($row['rodpi_qty'] ?? 0);
                $projectSort = isset($row['project_sort']) ? (int)$row['project_sort'] : PHP_INT_MAX;
                
                // 使用產品名稱作為唯一鍵
                $productKey = $productName;
                
                // 記錄所有出現過的項目和產品（用於動態表頭）
                $allProjects[$projectName] = true;
                $projectSorts[$projectName] = [
                    'sort' => $projectSort,
                    'id' => $projectId ?? PHP_INT_MAX
                ];
                $allProducts[$projectName][$productKey] = [
                    'display_name' => $productName
                ];
                
                // 整理項目下的產品資料
                if (!isset($orders[$rentalId]['projects'][$projectName])) {
                    $orders[$rentalId]['projects'][$projectName] = [];
                }
                
                if (!isset($orders[$rentalId]['projects'][$projectName][$productKey])) {
                    $orders[$rentalId]['projects'][$projectName][$productKey] = [
                        'quantity' => 0,
                        'length' => 0,
                        'display_name' => $productName
                    ];
                }
                
                // 累加數量和米數
                $orders[$rentalId]['projects'][$projectName][$productKey]['quantity'] += $quantity;
                $orders[$rentalId]['projects'][$projectName][$productKey]['length'] += $length;
            }
        }

        // 過濾掉沒有產品明細的租賃單
        $filteredOrders = array_filter($orders, function($order) {
            return !empty($order['projects']);
        });

        return [
            'orders' => array_values($filteredOrders),
            'all_projects' => array_keys($allProjects),
            'all_products' => $allProducts,
            'project_sorts' => $projectSorts
        ];
    }
}
