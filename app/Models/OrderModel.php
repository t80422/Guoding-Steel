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
        'o_status'
    ];

    public const TYPE_IN_WAREHOUSE = 0; // 進倉庫
    public const TYPE_OUT_WAREHOUSE = 1; // 出倉庫

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
    public function getList($keyword = null, $orderDateStart = null, $orderDateEnd = null, $type = null)
    {
        $builder = $this->baseQuery();

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

        $builder->orderBy('o.o_date', 'DESC');

        $results = $builder->get()->getResultArray();

        foreach ($results as &$row) {
            $row['typeName'] = self::getTypeName($row['o_type']);
            $row['o_status'] = self::getStatusName($row['o_status']);
        }

        return $results;
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
     * @return array
     */
    public function getMaterialDetailsWithProjectsByLocation($locationId)
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
                pi.pi_name as project_name,
                CONCAT(mic.mic_name, p.pr_name) as product_name,
                od.od_length,
                odpi.odpi_qty
            ')
            ->groupStart()
                ->where('o.o_from_location', $locationId)
                ->orWhere('o.o_to_location', $locationId)
            ->groupEnd()
            ->orderBy('o.o_date', 'DESC')
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

        // 按訂單分組處理資料
        foreach ($rawResults as $row) {
            $orderId = $row['o_id'];
            
            // 初始化訂單基本資訊
            if (!isset($orders[$orderId])) {
                $orders[$orderId] = [
                    'o_id' => $orderId,
                    'vehicle_no' => $row['o_car_number'],
                    'date' => $row['o_date'],
                    'type' => self::getTypeName($row['o_type']),
                    'warehouse' => $this->getWarehouseName($row, $locationId),
                    'projects' => []
                ];
            }

            // 如果有項目和產品資料
            if ($row['project_name'] && $row['product_name']) {
                $projectName = $row['project_name'];
                $productName = $row['product_name'];
                $length = $row['od_length'] > 0 ? $row['od_length'] . 'm' : 'N/A';
                
                // 使用複合鍵來區分相同產品的不同長度，但顯示時只顯示產品名稱
                $productKey = $productName . '|' . $length; // 內部識別用
                
                // 記錄所有出現過的項目和產品（用於動態表頭）
                $allProjects[$projectName] = true;
                $allProducts[$projectName][$productKey] = [
                    'display_name' => $productName,
                    'length' => $length
                ];
                
                // 整理項目下的產品資料
                if (!isset($orders[$orderId]['projects'][$projectName])) {
                    $orders[$orderId]['projects'][$projectName] = [];
                }
                
                if (!isset($orders[$orderId]['projects'][$projectName][$productKey])) {
                    $orders[$orderId]['projects'][$projectName][$productKey] = [
                        'quantity' => 0,
                        'length' => $length,
                        'display_name' => $productName
                    ];
                }
                
                $orders[$orderId]['projects'][$projectName][$productKey]['quantity'] += $row['odpi_qty'];
            }
        }

        return [
            'orders' => array_values($orders),
            'all_projects' => array_keys($allProjects),
            'all_products' => $allProducts
        ];
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
     * 根據使用者地點權限取得進行中的訂單
     *
     * @param array $userLocationIds 使用者有權限的地點ID陣列
     * @param int $userId 使用者ID
     * @return array
     */
    public function getByInProgressWithLocationFilter($userId,$userLocationIds = [])
    {
        if (empty($userLocationIds)) {
            return [];
        }

        return $this->baseQuery()
            ->where('o.o_status', self::STATUS_IN_PROGRESS)
            ->groupStart()
                ->whereIn('o.o_from_location', $userLocationIds)
                ->orWhereIn('o.o_to_location', $userLocationIds)
                ->orWhere('o.o_create_by', $userId)
            ->groupEnd()
            ->get()->getResultArray();
    }

    /**
     * 根據使用者地點權限取得已完成的訂單
     *
     * @param array $userLocationIds 使用者有權限的地點ID陣列
     * @param int $userId 使用者ID
     * @return array
     */
    public function getByCompletedWithLocationFilter($userId, $userLocationIds = [])
    {
        if (empty($userLocationIds)) {
            return [];
        }

        return $this->baseQuery()
            ->where('o.o_status', self::STATUS_COMPLETED)
            ->groupStart()
                ->whereIn('o.o_from_location', $userLocationIds)
                ->orWhereIn('o.o_to_location', $userLocationIds)
                ->orWhere('o.o_create_by', $userId)
            ->groupEnd()
            ->get()->getResultArray();
    }
}
