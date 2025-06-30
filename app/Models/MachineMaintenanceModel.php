<?php

namespace App\Models;

use CodeIgniter\Model;

class MachineMaintenanceModel extends Model
{
    protected $table            = 'machine_maintenances';
    protected $primaryKey       = 'mm_id';
    protected $allowedFields    = [
        'mm_m_id',
        'mm_date',
        'mm_last_km',
        'mm_next_km',
        'mm_create_by',
        'mm_update_by',
        'mm_update_at'
    ];

    /**
     * 取得列表
     * @param array $filter 過濾條件
     * @param int $page 頁碼
     * @param bool $usePaging 是否使用分頁
     * @return array 包含資料、當前頁碼、總頁數 或 直接回傳資料陣列
     */
    public function getList($filter = [], $page = 1, $usePaging = true)
    {
        $builder = $this->builder('machine_maintenances mm')
            ->join('machines m', 'm.m_id = mm.mm_m_id')
            ->join('users u1', 'u1.u_id = mm.mm_create_by', 'left')
            ->join('users u2', 'u2.u_id = mm.mm_update_by', 'left')
            ->select('mm.*, m.m_name, u1.u_name as creator, u2.u_name as updater, mm.mm_create_at, mm.mm_update_at');

        if (!empty($filter['date_start'])) {
            $builder->where('mm.mm_date >=', $filter['date_start']);
        }

        if (!empty($filter['date_end'])) {
            $builder->where('mm.mm_date <=', $filter['date_end']);
        }

        if (!empty($filter['m_name'])) {
            $builder->like('m.m_name', $filter['m_name']);
        }

        $builder->orderBy('mm.mm_date', 'DESC');

        // 如果不使用分頁，直接回傳所有資料
        if (!$usePaging) {
            return $builder->get()->getResultArray();
        }

        // 分頁邏輯
        $total = $builder->countAllResults(false);
        $perPage = 10;
        $totalPages = ceil($total / $perPage);
        $data = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        
        return [
            'data' => $data,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }

    public function getInfoById($id)
    {
        return $this->builder('machine_maintenances mm')
            ->join('users u1', 'u1.u_id = mm.mm_create_by', 'left')
            ->join('users u2', 'u2.u_id = mm.mm_update_by', 'left')
            ->where('mm.mm_id', $id)
            ->select('mm.*, u1.u_name as creator, u2.u_name as updater')
            ->get()->getRowArray();
    }
}
