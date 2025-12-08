<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectItemModel extends Model
{
    protected $table            = 'project_items';
    protected $primaryKey       = 'pi_id';
    protected $allowedFields    = [
        'pi_name',
        'pi_sort',
        'pi_create_by',
        'pi_update_by',
        'pi_update_at'
    ];

    public function getList($filter = [], $page = 1)
    {
        $builder = $this->baseQuery()
            ->select('pi.pi_id, pi.pi_name, pi.pi_sort, pi.pi_create_at, pi.pi_update_at, u1.u_name as creator, u2.u_name as updater');

        if (!empty($filter['keyword'])) {
            $builder->like('pi.pi_name', $filter['keyword']);
        }

        $builder->orderBy('pi.pi_sort', 'ASC');

        $total = $builder->countAllResults(false);
        $perPage = 10;
        $totalPages = ceil($total / $perPage);
        $data = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return [
            'data' => $data,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }

    public function getDetail($id)
    {
        return $this->baseQuery()
            ->select('pi.pi_id, pi.pi_name, pi.pi_sort, pi.pi_create_at, pi.pi_update_at, u1.u_name as creator, u2.u_name as updater')
            ->where('pi.pi_id', $id)
            ->get()->getRowArray();
    }

    private function baseQuery()
    {
        return $this->builder('project_items pi')
            ->join('users u1', 'u1.u_id=pi.pi_create_by')
            ->join('users u2', 'u2.u_id=pi.pi_update_by', 'left');
    }

    /**
     * 取得項目ID和名稱
     * @return array
     */
    public function getIdAndNames(){
        return $this->select('pi_id, pi_name')->get()->getResultArray();
    }
}
