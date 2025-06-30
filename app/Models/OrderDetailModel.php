<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderDetailModel extends Model
{
    protected $table            = 'order_details';
    protected $primaryKey       = 'od_id';
    protected $allowedFields    = [
        'od_o_id',
        'od_pr_id',
        'od_qty',
        'od_length',
        'od_weight'
    ];

    /**
     * 取得訂單明細資料
     *
     * @param int $orderId
     * @return array
     */
    public function getDetailByOrderId($orderId)
    {
        return $this->builder('order_details od')
            ->join('products p', 'p.pr_id = od.od_pr_id', 'left')
            ->join('minor_categories mic', 'mic.mic_id = p.pr_mic_id', 'left')
            ->select('od.*, p.pr_name, mic.mic_name')
            ->where('od.od_o_id', $orderId)
            ->get()->getResultArray();
    }

    /**
     * 取得訂單的明細資料
     *
     * @param int $orderId
     * @return array
     */
    public function getByOrderId($orderId)
    {
        return $this->where('od_o_id', $orderId)->findAll();
    }
}
