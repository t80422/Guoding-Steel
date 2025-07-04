<?php

namespace App\Models;

use CodeIgniter\Model;

class ManufacturerModel extends Model
{
    protected $table            = 'manufacturers';
    protected $primaryKey       = 'ma_id';
    protected $allowedFields    = [
        'ma_name',
        'ma_create_by',
        'ma_update_by',
        'ma_update_at'
    ];

    /**
     * 取得列表
     * @param array $filter
     * @param int $page
     * @param bool $usePaging
     * @return array
     */
    public function getList($filter = [], $page = 1, $usePaging = true)
    {
        $builder = $this->builder('manufacturers ma')
            ->join('users u1', 'u1.u_id = ma.ma_create_by', 'left')
            ->join('users u2', 'u2.u_id = ma.ma_update_by', 'left')
            ->select('ma.*, u1.u_name as creator, u2.u_name as updater, ma.ma_create_at, ma.ma_update_at');

        if (!empty($filter['ma_name'])) {
            $builder->like('ma.ma_name', $filter['ma_name']);
        }

        // 分頁邏輯
        $total = $builder->countAllResults(false);
        $perPage = 10;
        $totalPages = ceil($total / $perPage);

        if ($usePaging) {
            $data = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        } else {
            $data = $builder->get()->getResultArray();
        }

        return [
            'data' => $data,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }

    /**
     * 取得單筆資料
     * @param int $id
     * @return array
     */
    public function getInfoById($id)
    {
        return $this->builder('manufacturers ma')
            ->join('users u1', 'u1.u_id = ma.ma_create_by', 'left')
            ->join('users u2', 'u2.u_id = ma.ma_update_by', 'left')
            ->where('ma.ma_id', $id)
            ->select('ma.*, u1.u_name as creator, u2.u_name as updater, ma.ma_create_at, ma.ma_update_at')
            ->get()->getRowArray();
    }

    /**
     * 取得下拉選單
     * @return array
     */
    public function getDropdown()
    {
        return $this->builder('manufacturers ma')
            ->select('ma.ma_id, ma.ma_name')
            ->get()->getResultArray();
    }
}
