<?php

namespace App\Models;

use CodeIgniter\Model;

class MachineModel extends Model
{
    protected $table            = 'machines';
    protected $primaryKey       = 'm_id';
    protected $allowedFields    = [
        'm_name',
        'm_create_by'
    ];

    /**
     * 取得列表
     * @param array $filter
     * @return array
     */
    public function getList($filter = null)
    {
        $builder = $this->builder('machines m')
            ->select('m.*, u.u_name as creator')
            ->join('users u', 'u.u_id = m.m_create_by', 'left');

        if (!empty($filter['m_name'])) {
            $builder->like('m.m_name', $filter['m_name']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * 取得資訊
     * @param int $id
     * @return array
     */
    public function getInfoById($id)
    {
        return $this->builder('machines m')
            ->join('users u1', 'u1.u_id = m.m_create_by', 'left')
            ->select('m.*, u1.u_name as creator')
            ->where('m.m_id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * 取得下拉選單
     * @return array
     */
    public function getDropdown()
    {
        return $this->builder()
            ->select('m_id, m_name')
            ->get()
            ->getResultArray();
    }
}
