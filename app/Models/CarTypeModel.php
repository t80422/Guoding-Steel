<?php

namespace App\Models;

use CodeIgniter\Model;

class CarTypeModel extends Model
{
    protected $table            = 'car_types';
    protected $primaryKey       = 'ct_id';
    protected $allowedFields    = [
        'ct_name',
        'ct_created_by',
        'ct_updated_by',
    ];

    public function getList($keyword)
    {
        $builder = $this->builder('car_types ct')
            ->join('users u1', 'u1.u_id=ct.ct_created_by', 'left')
            ->join('users u2', 'u2.u_id=ct.ct_updated_by', 'left')
            ->select('ct.ct_id, ct.ct_name, u1.u_name as creator, ct.ct_created_at, u2.u_name as updater, ct.ct_updated_at');

        if (!empty($keyword)) {
            $builder->like('ct.ct_name', $keyword);
        }

        return $builder->get()->getResultArray();
    }

    public function getDropdown()
    {
        $builder = $this->builder('car_types ct')
            ->select('ct.ct_id, ct.ct_name');
        return $builder->get()->getResultArray();
    }
}
