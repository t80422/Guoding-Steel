<?php

namespace App\Models;

use CodeIgniter\Model;

class MinorCategoryModel extends Model
{
    protected $table            = 'minor_categories';
    protected $primaryKey       = 'mic_id';
    protected $allowedFields    = [
        'mic_name',
        'mic_create_by',
        'mic_create_at',
        'mic_update_by',
        'mic_update_at',
        'mic_mc_id'
    ];

    public function getList($keyword)
    {
        $builder = $this->builder('minor_categories mic')
            ->join('major_categories mc', 'mc.mc_id=mic.mic_mc_id', 'left')
            ->join('users u1', 'u1.u_id=mic.mic_create_by', 'left')
            ->join('users u2', 'u2.u_id=mic.mic_update_by', 'left')
            ->select('mic.mic_id, mic.mic_name, mc.mc_name, mic.mic_create_at, mic.mic_update_at, u1.u_name as creator, u2.u_name as updater');

        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('mic.mic_name', $keyword)
                ->orLike('mc.mc_name', $keyword)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function getNames($mcId)
    {
        $builder = $this->builder('minor_categories mic')
            ->select('mic.mic_id, mic.mic_name')
            ->where('mic.mic_mc_id', $mcId);
        return $builder->get()->getResultArray();
    }
}
