<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table            = 'inventories';
    protected $primaryKey       = 'i_id';
    protected $allowedFields    = [
        'i_pr_id',
        'i_l_id',
        'i_initial',
        'i_qty',
        'i_create_by',
        'i_update_by',
        'i_update_at'
    ];

    public function getList($filter = [], $page = 1, $usePaging = true)
    {
        $builder = $this->builder('inventories i')
            ->join('products pr', 'pr.pr_id = i.i_pr_id', 'left')
            ->join('minor_categories mic', 'mic.mic_id = pr.pr_mic_id', 'left')
            ->join('locations l', 'l.l_id = i.i_l_id', 'left')
            ->join('users u1', 'u1.u_id = i.i_create_by', 'left')
            ->join('users u2', 'u2.u_id = i.i_update_by', 'left')
            ->select('i.*, pr.pr_name as productName, mic.mic_name, l.l_name as locationName, u1.u_name as creator, u2.u_name as updater');

        if (!empty($filter['p_name'])) {
            $builder->like('pr.pr_name', $filter['p_name']);
        }

        if (!empty($filter['l_name'])) {
            $builder->like('l.l_name', $filter['l_name']);
        }

        $builder->orderBy('i.i_id', 'DESC');

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
        return $this->builder('inventories i')
            ->join('products pr', 'pr.pr_id = i.i_pr_id', 'left')
            ->join('minor_categories mic', 'mic.mic_id = pr.pr_mic_id', 'left')
            ->join('major_categories mc', 'mc.mc_id = mic.mic_mc_id', 'left')
            ->join('locations l', 'l.l_id = i.i_l_id', 'left')
            ->join('users u1', 'u1.u_id = i.i_create_by', 'left')
            ->join('users u2', 'u2.u_id = i.i_update_by', 'left')
            ->select('i.*, pr.pr_name, pr.pr_mic_id, mic.mic_name, mic.mic_mc_id, mc.mc_name, l.l_name, u1.u_name as creator, u2.u_name as updater')
            ->where('i.i_id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * 檢查地點和產品組合是否重複
     * 
     * @param int $productId 產品ID
     * @param int $locationId 地點ID
     * @return bool 是否重複
     */
    public function isDuplicateLocationProduct($productId, $locationId)
    {
        $builder = $this->where('i_pr_id', $productId)
            ->where('i_l_id', $locationId);

        return $builder->countAllResults() > 0;
    }

    public function getRoadPlateList($filter = [], $page = 1)
    {
        $builder = $this->builder('inventories i')
            ->join('locations l', 'l.l_id = i.i_l_id')
            ->join('products pr', 'pr.pr_id = i.i_pr_id')
            ->select('i.i_qty, l.l_name')
            ->where('pr.pr_name', '鋪路鋼板')
            ->orderBy('i.i_id', 'DESC');

        if (!empty($filter['keyword'])) {
            $builder->groupStart()
                ->orLike('l.l_name', $filter['keyword'])
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $perPage = 10;
        $totalPages = ceil($total / $perPage);
        $data = $builder->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }
}
