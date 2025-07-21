<?php

namespace App\Models;

use CodeIgniter\Model;

class ManufacturerInventoryModel extends Model
{
    protected $table            = 'manufacturer_inventories';
    protected $primaryKey       = 'mi_id';
    protected $allowedFields    = [
        'mi_pr_id',
        'mi_ma_id',
        'mi_qty',
        'mi_initial',
        'mi_create_by',
        'mi_update_by',
        'mi_update_at'
    ];

    public function getList($filter = [], $page = 1)
    {
        $builder = $this->baseQuery()
            ->select('mi.*, pr.pr_name, m.ma_name, u1.u_name as creator, u2.u_name as updater');

        if (!empty($filter['keyword'])) {
            $builder->like('pr.pr_name', $filter['keyword'])
                ->orLike('m.ma_name', $filter['keyword']);
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

    public function isDuplicate($productId, $manufacturerId)
    {
        $builder = $this->where('mi_pr_id', $productId)
            ->where('mi_ma_id', $manufacturerId);

        return $builder->countAllResults() > 0;
    }

    public function getInfoById($id)
    {
        return $this->baseQuery()
            ->join('minor_categories mic', 'mic.mic_id = pr.pr_mic_id')
            ->join('major_categories mc', 'mc.mc_id = mic.mic_mc_id')
            ->select('mi.*, pr.pr_name, pr.pr_mic_id, mic.mic_name, mic.mic_mc_id, mc.mc_name, m.ma_name, u1.u_name as creator, u2.u_name as updater')
            ->where('mi.mi_id', $id)
            ->get()
            ->getRowArray();
    }

    public function baseQuery()
    {
        return $this->builder('manufacturer_inventories mi')
            ->join('products pr', 'pr.pr_id = mi.mi_pr_id')
            ->join('manufacturers m', 'm.ma_id = mi.mi_ma_id')
            ->join('users u1', 'u1.u_id = mi.mi_create_by')
            ->join('users u2', 'u2.u_id = mi.mi_update_by', 'left');
    }

    public function getInventoryBymaIdAndProductId($maId, $productId)
    {
        return $this->where('mi_ma_id', $maId)
            ->where('mi_pr_id', $productId)
            ->first();
    }
}
