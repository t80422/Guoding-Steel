<?php

namespace App\Models;

use CodeIgniter\Model;

class GpsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'gps';
    protected $primaryKey       = 'g_id';
    protected $allowedFields    = [
        'g_name',
        'g_create_by',
        'g_update_by',
        'g_update_at'
    ];

    public function getList($keyword)
    {
        $builder = $this->builder('gps g')
            ->join('users u1', 'u1.u_id = g.g_create_by', 'left')
            ->join('users u2', 'u2.u_id = g.g_update_by', 'left')
            ->select('g.g_id, g.g_name, u1.u_name as creator, u2.u_name as updater, g.g_create_at, g.g_update_at');

        if (!empty($keyword)) {
            $builder->like('g.g_name', $keyword);
        }

        return $builder->get()->getResultArray();
    }

    public function getOptions(){
        return $this->select('g_id, g_name')
            ->get()->getResultArray();
    }
}
