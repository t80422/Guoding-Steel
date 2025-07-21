<?php

namespace App\Models;

use CodeIgniter\Model;

class RentalOrderDetailModel extends Model
{
    protected $table            = 'rental_order_details';
    protected $primaryKey       = 'rod_id';
    protected $allowedFields    = [
        'rod_ro_id',
        'rod_pr_id',
        'rod_qty',
        'rod_length',
        'rod_weight'
    ];

    /**
     * 取得租賃明細資料
     *
     * @param int $rentalId
     * @return array
     */
    public function getDetailByRentalId($rentalId)
    {
        return $this->builder('rental_order_details rod')
            ->join('products p', 'p.pr_id = rod.rod_pr_id')
            ->join('minor_categories mic', 'mic.mic_id = p.pr_mic_id')
            ->select('rod.*, p.pr_name, mic.mic_name')
            ->where('rod.rod_ro_id', $rentalId)
            ->get()->getResultArray();
    }

    /**
     * 取得租賃的明細資料
     *
     * @param int $rentalId
     * @return array
     */
    public function getByRentalId($rentalId)
    {
        return $this->where('rod_ro_id', $rentalId)->findAll();
    }
} 