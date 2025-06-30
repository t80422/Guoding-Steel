<?php

namespace App\Models;

use CodeIgniter\Model;

class RentalModel extends Model
{
    protected $table            = 'rentals';
    protected $primaryKey       = 'r_id';
    protected $allowedFields    = [
        'r_front_image',
        'r_side_image',
        'r_doc_image',
        'r_memo',
        'r_create_by'
    ];

    public function getList($filter = null)
    {
        $builder = $this->builder('rentals r')
            ->select('r.*, u.u_name as creator')
            ->join('users u', 'u.u_id = r.r_create_by', 'left');

        if (!empty($filter['r_memo'])) {
            $builder->like('r.r_memo', $filter['r_memo']);
        }

        return $builder->get()->getResultArray();
    }
}
