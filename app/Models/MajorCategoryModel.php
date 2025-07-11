<?php

namespace App\Models;

use CodeIgniter\Model;

class MajorCategoryModel extends Model
{
    protected $table            = 'major_categories';
    protected $primaryKey       = 'mc_id';
    protected $allowedFields    = [
        'mc_name',
        'mc_create_by',
        'mc_update_by',
        'mc_update_at'
    ];

    public function getList($keyword){
        $builder=$this->builder('major_categories mc')
            ->join('users u1', 'u1.u_id=mc.mc_create_by','left')
            ->join('users u2', 'u2.u_id=mc.mc_update_by','left')
            ->select('mc.mc_id, mc.mc_name, u1.u_name as creator, mc.mc_create_at, u2.u_name as updater, mc.mc_update_at');

        if(!empty($keyword)){
            $builder->like('mc.mc_name', $keyword);
        }

        return $builder->get()->getResultArray();
    }
    
    public function getDropdown(){
        $builder=$this->builder('major_categories mc')
            ->select('mc.mc_id, mc.mc_name');
        return $builder->get()->getResultArray();
    }
}
