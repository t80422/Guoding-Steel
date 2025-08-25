<?php

namespace App\Models;

use CodeIgniter\Model;

class UserLocationModel extends Model
{
    protected $table = 'user_locations';
    protected $primaryKey = 'ul_id';
    protected $allowedFields = [
        'ul_u_id',
        'ul_l_id'
    ];

    /**
     * 取得使用者有權限的地點ID陣列
     *
     * @param int $userId
     * @return array
     */
    public function getUserLocationIds($userId)
    {
        $result = $this->builder()
            ->select('ul_l_id')
            ->where('ul_u_id', $userId)
            ->get()
            ->getResultArray();
        
        return array_column($result, 'ul_l_id');
    }

    /**
     * 設定使用者的地點權限（先刪除既有權限再新增）
     *
     * @param int $userId
     * @param array $locationIds
     * @return bool
     */
    public function setUserLocations($userId, $locationIds)
    {
        $this->db->transStart();

        try {
            // 先刪除使用者所有既有權限
            $this->where('ul_u_id', $userId)->delete();

            // 如果有選擇地點，則新增權限
            if (!empty($locationIds)) {
                $data = [];
                foreach ($locationIds as $locationId) {
                    $data[] = [
                        'ul_u_id' => $userId,
                        'ul_l_id' => $locationId,
                    ];
                }
                $this->insertBatch($data);
            }

            $this->db->transComplete();
            return $this->db->transStatus();
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', $e->getMessage());
            return false;
        }
    }
    
    /**
     * 取得使用者有權限的地點
     *
     * @param int $userId
     * @return array
     */
    public function getUserLocations($userId)
    {
        return $this->builder()
            ->select('l_id, l_name')
            ->join('locations l', 'l.l_id = ul_l_id')
            ->where('ul_u_id', $userId)
            ->get()
            ->getResultArray();
    }
} 