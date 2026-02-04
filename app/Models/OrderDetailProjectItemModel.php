<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderDetailProjectItemModel extends Model
{
    protected $table            = 'order_detail_project_items';
    protected $primaryKey       = 'odpi_id';
    protected $allowedFields    = [
        'odpi_od_id',
        'odpi_pi_id',
        'odpi_qty',
        'odpi_type', // 0: Source, 1: Target
    ];

    public function getByUniqueKey($odId, $piId, $type)
    {
        return $this->where('odpi_od_id', $odId)
            ->where('odpi_pi_id', $piId)
            ->where('odpi_type', $type)
            ->first();
    }

    public function getByOrderId($orderId)
    {
        return $this->builder('order_detail_project_items odpi')
            ->join('order_details od', 'odpi.odpi_od_id = od.od_id')
            ->where('od.od_o_id', $orderId)
            ->get()->getResultArray();
    }
}
