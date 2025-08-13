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
            ->join('locations l', 'l.l_id = ro.ro_l_id')
            ->join('users u1', 'u1.u_id = ro.ro_create_by')
            ->join('users u2', 'u2.u_id = ro.ro_update_by', 'left')
            ->join('gps g', 'g.g_id = ro.ro_g_id', 'left')
            ->join('manufacturers m', 'm.ma_id = ro.ro_ma_id')
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
}
