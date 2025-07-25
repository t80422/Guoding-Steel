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
        'ro_update_at'
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
}
