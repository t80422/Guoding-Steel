<?php

namespace App\Models;

use CodeIgniter\Model;

class MachineRepairModel extends Model
{
    protected $table            = 'machine_repairs';
    protected $primaryKey       = 'mr_id';
    protected $allowedFields    = [
        'mr_m_id',
        'mr_date',
        'mr_status', // 0:未歸還 1:已歸還
        'mr_memo',
        'mr_create_by',
        'mr_update_by',
        'mr_update_at'
    ];

    const STATUS_UNRETURNED = 0;
    const STATUS_RETURNED = 1;

    public static function getStatusName($status)
    {
        return $status == self::STATUS_UNRETURNED ? '未歸還' : '已歸還';
    }

    /**
     * 取得對應狀態的 Bootstrap 樣式
     *
     * @param int $status
     * @return string
     */
    public static function getStatusBadgeClass($status)
    {
        return $status == self::STATUS_UNRETURNED ? 'bg-warning text-dark' : 'bg-success';
    }

    /**
     * 取得列表
     * @param array $filter 過濾條件
     * @param int $page 頁碼
     * @param bool $usePaging 是否使用分頁
     * @return array 包含資料、當前頁碼、總頁數 或 直接回傳資料陣列
     */
    public function getList($filter = [], $page = 1, $usePaging = true)
    {
        $builder = $this->builder('machine_repairs mr')
            ->join('machines m', 'm.m_id = mr.mr_m_id')
            ->join('users u1', 'u1.u_id = mr.mr_create_by', 'left')
            ->join('users u2', 'u2.u_id = mr.mr_update_by', 'left')
            ->select('mr.*, m.m_name, u1.u_name as creator, u2.u_name as updater, mr.mr_create_at, mr.mr_update_at');

        if (!empty($filter['date_start'])) {
            $builder->where('mr.mr_date >=', $filter['date_start']);
        }

        if (!empty($filter['date_end'])) {
            $builder->where('mr.mr_date <=', $filter['date_end']);
        }

        if (!empty($filter['m_name'])) {
            $builder->like('m.m_name', $filter['m_name']);
        }

        $builder->orderBy('mr.mr_date', 'DESC');

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

    public function getInfoById($id)
    {
        return $this->builder('machine_repairs mr')
            ->join('users u1', 'u1.u_id = mr.mr_create_by', 'left')
            ->join('users u2', 'u2.u_id = mr.mr_update_by', 'left')
            ->where('mr.mr_id', $id)
            ->select('mr.*, u1.u_name as creator, u2.u_name as updater')
            ->get()->getRowArray();
    }

}
