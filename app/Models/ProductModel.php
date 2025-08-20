<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'pr_id';
    protected $allowedFields    = [
        'pr_name',
        'pr_create_by',
        'pr_update_by',
        'pr_update_at',
        'pr_mic_id',
        'pr_weight',
        'pr_is_length'
    ];

    public function getList($filter = [], $page = 1)
    {
        $builder = $this->builder('products pr')
            ->join('minor_categories mic', 'mic.mic_id = pr.pr_mic_id', 'left')
            ->join('major_categories mc', 'mc.mc_id = mic.mic_mc_id', 'left')
            ->join('users u1', 'u1.u_id=pr.pr_create_by', 'left')
            ->join('users u2', 'u2.u_id=pr.pr_update_by', 'left')
            ->select('pr.pr_id, pr.pr_name, mc.mc_name, mic.mic_name, pr.pr_create_at, pr.pr_update_at, u1.u_name as creator, u2.u_name as updater, pr.pr_weight');

        if (!empty($filter['keyword'])) {
            $builder->like('pr.pr_name', $filter['keyword']);
            $builder->orLike('mic.mic_name', $filter['keyword']);
            $builder->orLike('mc.mc_name', $filter['keyword']);
        }

        $builder->orderBy('pr.pr_id', 'DESC');

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

    public function getByMinorCategoryId($minorCategoryId)
    {
        return $this->where('pr_mic_id', $minorCategoryId)
        ->findAll();
    }

    /**
     * 取得列印所需的所有產品資料（含小分類與單位），依小分類名稱、產品名稱升冪排序
     *
     * @return array<int,array<string,mixed>>
     */
    public function getAllForPrint()
    {
        return $this->builder('products pr')
            ->join('minor_categories mic', 'mic.mic_id = pr.pr_mic_id', 'left')
            ->select('pr.pr_id, pr.pr_name, mic.mic_unit, mic.mic_id, mic.mic_name')
            ->orderBy('mic.mic_name', 'ASC')
            ->orderBy('pr.pr_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * 取得所有型鋼/配件產品（含分類資訊）
     *
     * @return array<int,array<string,mixed>>
     */
    public function getSteelAndAccessoryProducts(): array
    {
        return $this->builder('products pr')
            ->join('minor_categories mic', 'mic.mic_id = pr.pr_mic_id', 'left')
            ->join('major_categories mc', 'mc.mc_id = mic.mic_mc_id', 'left')
            ->select('pr.pr_id, pr.pr_name, mic.mic_name, mic.mic_unit, mc.mc_name')
            ->whereIn('mc.mc_name', ['型鋼', '配件'])
            ->orderBy('pr.pr_id', 'ASC')
            ->get()
            ->getResultArray();
    }
}
