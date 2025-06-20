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
        'pr_weight'
    ];

    public function getList($keyword)
    {
        $builder = $this->builder('products pr')
            ->join('minor_categories mic', 'mic.mic_id = pr.pr_mic_id', 'left')
            ->join('major_categories mc', 'mc.mc_id = mic.mic_mc_id', 'left')
            ->join('users u1', 'u1.u_id=pr.pr_create_by', 'left')
            ->join('users u2', 'u2.u_id=pr.pr_update_by', 'left')
            ->select('pr.pr_id, pr.pr_name, mc.mc_name, mic.mic_name, pr.pr_create_at, pr.pr_update_at, u1.u_name as creator, u2.u_name as updater, pr.pr_weight');

        if (!empty($keyword)) {
            $builder->like('pr.pr_name', $keyword);
            $builder->orLike('mic.mic_name', $keyword);
            $builder->orLike('mc.mc_name', $keyword);
        }

        return $builder->get()->getResultArray();
    }

    public function getNames($minorCategoryId)
    {
        return $this->where('pr_mic_id', $minorCategoryId)
        ->select('pr_id,pr_name')
        ->findAll();
    }
}
