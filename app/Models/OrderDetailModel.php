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
            ->select('od.*, p.pr_name, mic.mic_name, p.pr_weight AS pr_weight_per_unit, p.pr_is_length')
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

    /**
     * 取得列印用的明細
     *
     * @param int $orderId
     * @return array<int,array{spec:string}>
     */
    public function getDetailsForPrint(int $orderId): array
    {
        $rows = $this->builder('order_details od')
            ->join('products p', 'p.pr_id = od.od_pr_id', 'left')
            ->join('minor_categories mic', 'mic.mic_id = p.pr_mic_id', 'left')
            ->join('major_categories mc', 'mc.mc_id = mic.mic_mc_id', 'left')
            ->select('p.pr_id, p.pr_name, mic.mic_name, od.od_length, od.od_qty')
            ->where('od.od_o_id', $orderId)
            ->where('mc.mc_name', '型鋼')
            ->where('p.pr_is_length', true)
            ->get()
            ->getResultArray();

        // 依產品分組
        $groups = [];
        foreach ($rows as $row) {
            $prId = (int) ($row['pr_id'] ?? 0);
            if (!isset($groups[$prId])) {
                $groups[$prId] = [
                    'pr_id' => $prId,
                    'pr_name' => (string) ($row['pr_name'] ?? ''),
                    'mic_name' => (string) ($row['mic_name'] ?? ''),
                    'length_counts' => [],
                    'total_count' => 0,
                    'total_length' => 0.0,
                ];
            }

            $lengthVal = (float) ($row['od_length'] ?? 0);
            $qtyVal = (int) ($row['od_qty'] ?? 0);

            // 以原始長度數值作為 key，為避免浮點誤差，正規化為最多 6 位小數字串
            $lengthKey = rtrim(rtrim(number_format($lengthVal, 6, '.', ''), '0'), '.');
            if ($lengthKey === '') {
                $lengthKey = '0';
            }

            if (!isset($groups[$prId]['length_counts'][$lengthKey])) {
                $groups[$prId]['length_counts'][$lengthKey] = [
                    'value' => $lengthVal,
                    'count' => 0,
                ];
            }
            // 合併相同長度為單一片段，count 為該長度的總支數（以 od_qty 加總）
            $groups[$prId]['length_counts'][$lengthKey]['count'] += $qtyVal;

            // 總支數：Σ od_qty
            $groups[$prId]['total_count'] += $qtyVal;
            // 總長度：Σ (od_length × od_qty)
            $groups[$prId]['total_length'] += ($lengthVal * $qtyVal);
        }

        // 數字格式化：整數不顯示小數，否則 1 位小數
        $formatNumber = static function (float $num): string {
            if (floor($num) == $num) {
                return (string) ((int) $num);
            }
            return number_format($num, 1, '.', '');
        };

        // 依 pr_id 升冪輸出
        ksort($groups);

        $formatted = [];
        foreach ($groups as $group) {
            // 組內長度由小到大
            $lengthEntries = array_values($group['length_counts']);
            usort($lengthEntries, function ($a, $b) {
                if ($a['value'] == $b['value']) {
                    return 0;
                }
                return ($a['value'] < $b['value']) ? -1 : 1;
            });

            $parts = [];
            foreach ($lengthEntries as $entry) {
                $lenStr = $formatNumber((float) $entry['value']) . 'm';
                if ((int) $entry['count'] > 1) {
                    $lenStr .= '*' . (int) $entry['count'];
                }
                $parts[] = $lenStr;
            }

            $prefix = trim(($group['mic_name'] ?? '') . ' ' . ($group['pr_name'] ?? ''));
            $summary = '計 ' . (int) $group['total_count'] . '支, ' . $formatNumber((float) $group['total_length']) . 'M';
            $spec = $prefix;
            if (!empty($parts)) {
                $spec .= ' / ' . implode('/', $parts);
            }
            $spec .= ' ' . $summary;

            $formatted[] = [
                'spec' => $spec,
            ];
        }

        return $formatted;
    }

    /**
     * 取得訂單的完整產品資料（含分類資訊）
     */
    public function getOrderProductsWithCategories(int $orderId): array
    {
        return $this->builder('order_details od')
            ->join('products p', 'p.pr_id = od.od_pr_id', 'left')
            ->join('minor_categories mic', 'mic.mic_id = p.pr_mic_id', 'left')
            ->join('major_categories mc', 'mc.mc_id = mic.mic_mc_id', 'left')
            ->select('od.od_pr_id, od.od_qty, p.pr_name, mic.mic_name, mic.mic_unit, mc.mc_name, p.pr_id')
            ->where('od.od_o_id', $orderId)
            ->orderBy('p.pr_id', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    public function getTotalWeight(int $orderId): float
    {
        return $this->builder('order_details od')
            ->select('SUM(od.od_weight) as total_weight')
            ->where('od.od_o_id', $orderId)
            ->get()->getRowArray()['total_weight'] ?? 0;
    }
}
