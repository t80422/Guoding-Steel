<?php

namespace App\Models;

use CodeIgniter\Model;

class RentalModel extends Model
{
    protected $table            = 'rentals';
    protected $primaryKey       = 'r_id';
    protected $allowedFields    = [
        'r_front_image',
        'r_side_image',
        'r_doc_image',
        'r_memo',
        'r_create_by'
    ];

    public function getList($filter = null, int $page = 1, int $perPage = 5)
    {
        $builder = $this->builder('rentals r')
            ->select('r.*, u.u_name as creator')
            ->join('users u', 'u.u_id = r.r_create_by', 'left');

        if (!empty($filter['r_memo'])) {
            $builder->like('r.r_memo', $filter['r_memo']);
        }

        $builder->orderBy('r.r_create_at', 'DESC');

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        // 計算總筆數
        $totalCount = $builder->countAllResults(false);
        $totalPages = (int) ceil($totalCount / $perPage);

        // 取得分頁資料
        $results = $builder
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'data' => $results,
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalCount' => $totalCount,
                'totalPages' => $totalPages,
            ],
        ];
    }
}
