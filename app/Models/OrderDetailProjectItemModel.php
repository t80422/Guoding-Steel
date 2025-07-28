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
    ];

    public function getByODIdAndPIId($odId, $piId)
    {
        return $this->where('odpi_od_id', $odId)
            ->where('odpi_pi_id', $piId)
            ->first();
    }

    public function getByOrderId($orderId)
    {
        return $this->builder('order_detail_project_items odpi')
            ->join('order_details od', 'odpi.odpi_od_id = od.od_id')
            ->where('od.od_o_id', $orderId)
            ->get()->getResultArray();
    }

    /**
     * 取得訂單的項目明細統計 (用於列印倉庫單)
     *
     * @param int $orderId
     * @return array
     */
    public function getProjectItemsDetailForPrint($orderId)
    {
        $results = $this->builder('order_detail_project_items odpi')
            ->join('order_details od', 'odpi.odpi_od_id = od.od_id')
            ->join('products p', 'od.od_pr_id = p.pr_id')
            ->join('minor_categories mic', 'p.pr_mic_id = mic.mic_id')
            ->join('project_items pi', 'odpi.odpi_pi_id = pi.pi_id')
            ->select('p.pr_id, p.pr_name, pi.pi_id, pi.pi_name, mic.mic_name, od.od_length, od.od_qty')
            ->where('od.od_o_id', $orderId)
            ->get()->getResultArray();

        // 按 pi_id 分組，每個項目獨立顯示
        $grouped = [];
        foreach ($results as $row) {
            $piId = $row['pi_id'];
            $piName = $row['pi_name'];
            $prName = $row['pr_name'];
            
            if (!isset($grouped[$piId])) {
                $grouped[$piId] = [
                    'pi_name' => $piName,
                    'pr_name' => $prName,
                    'length_details' => [],
                    'total_count' => 0,
                    'total_length' => 0
                ];
            }
            
            // 記錄長度明細
            $lengthDetail = $row['od_length'] . 'm';
            if ($row['od_qty'] > 1) {
                $lengthDetail .= '*' . $row['od_qty'];
            }
            $grouped[$piId]['length_details'][] = $lengthDetail;
            $grouped[$piId]['total_count'] += (int)$row['od_qty'];
            $grouped[$piId]['total_length'] += (float)$row['od_length'] * (int)$row['od_qty'];
        }

        // 格式化輸出
        $formatted = [];
        foreach ($grouped as $piId => $item) {
            $spec = $item['pi_name'] . $item['pr_name'] . '/' . implode('/', $item['length_details']);
            $summary = '計 ' . $item['total_count'] . '支,' . $item['total_length'] . 'M';
            
            $formatted[] = [
                'spec' => $spec . ' ' . $summary,
                'unit' => '',
                'qty' => ''
            ];
        }

        return $formatted;
    }
}
