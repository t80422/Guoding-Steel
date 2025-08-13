<?php

namespace App\Models;

use CodeIgniter\Model;

class RentalDetailProjectItemModel extends Model
{
    protected $table            = 'rental_order_detail_project_items';
    protected $primaryKey       = 'rodpi_id';
    protected $allowedFields    = [
        'rodpi_rod_id',
        'rodpi_pi_id',
        'rodpi_qty'
    ];

    /**
     * 依租賃單 ID 取得所有「租賃明細 × 項目」配置
     */
    public function getByRentalId(int $rentalId): array
    {
        return $this->builder('rental_order_detail_project_items rodpi')
            ->join('rental_order_details rod', 'rodpi.rodpi_rod_id = rod.rod_id')
            ->where('rod.rod_ro_id', $rentalId)
            ->get()->getResultArray();
    }

    /**
     * 取得特定租賃明細與項目的配置紀錄
     */
    public function getByRODIdAndPIId(int $rodId, int $piId): ?array
    {
        return $this->where('rodpi_rod_id', $rodId)
            ->where('rodpi_pi_id', $piId)
            ->first();
    }
}
