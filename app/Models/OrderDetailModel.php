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

    /**
     * 依訂單ID彙總明細重量（公斤）
     *
     * @param int[] $orderIds
     * @return array<int,float> key: o_id, value: total_weight_kg
     */
    public function getWeightSumsByOrderIds(array $orderIds): array
    {
        if (empty($orderIds)) {
            return [];
        }

        $rows = $this->builder('order_details od')
            ->select('od.od_o_id AS o_id, COALESCE(SUM(od.od_weight), 0) AS total_kg', false)
            ->whereIn('od.od_o_id', $orderIds)
            ->groupBy('od.od_o_id')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['o_id']] = (float) $row['total_kg'];
        }
        return $map;
    }
    /**
     * 取得指定訂單的產品數量彙總（以產品ID聚合）
     *
     * @param int $orderId
     * @return array<int,int> key: pr_id, value: total_qty
     */
    public function getOrderProductQuantities(int $orderId): array
    {
        $rows = $this->builder('order_details od')
            ->select('od.od_pr_id AS pr_id, SUM(od.od_qty) AS total_qty')
            ->where('od.od_o_id', $orderId)
            ->groupBy('od.od_pr_id')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['pr_id']] = (int) $row['total_qty'];
        }
        return $map;
    }
}
