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
            ->join('manufacturers m1', 'm1.ma_id = l1.l_ma_id', 'left')
            ->join('manufacturers m2', 'm2.ma_id = l2.l_ma_id', 'left')
            ->select('
                o.*,
                l1.l_name as from_location_name,
                l2.l_name as to_location_name,
                m1.ma_name as from_ma_name,
                m2.ma_name as to_ma_name,
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
                ->orLike('m1.ma_name', $keyword)
                ->orLike('m2.ma_name', $keyword)
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
     * 根據使用者地點權限取得進行中的訂單
     *
     * @param array $userLocationIds 使用者有權限的地點ID陣列
     * @param int $userId 使用者ID
     * @return array
     */
    public function getByInProgressWithLocationFilter($userLocationIds = [], $userId)
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
    public function getByCompletedWithLocationFilter($userLocationIds = [], $userId)
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
