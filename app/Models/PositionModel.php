<?php

namespace App\Models;

use CodeIgniter\Model;

class PositionModel extends Model
{
    protected $table            = 'positions';
    protected $primaryKey       = 'p_id';
    protected $allowedFields    = [
        'p_name',
        'p_create_by',
        'p_update_by',
        'p_update_at'
    ];

    public function getList($keyword)
    {
        $builder = $this->builder('positions p')
            ->join('users u1', 'u1.u_id=p.p_create_by','left')
            ->join('users u2', 'u2.u_id=p.p_update_by','left')
            ->select('p.p_id,p.p_name,p.p_create_at,p.p_update_at,u1.u_name as creator,u2.u_name as updater');

        if(!empty($keyword)){
            $builder->like('p.p_name',$keyword);
        }

        return $builder->get()->getResultArray();
    }
}
