<?php

namespace App\Models;

use CodeIgniter\Model;

class LocationModel extends Model
{
    protected $table            = 'locations';
    protected $primaryKey       = 'l_id';
    protected $allowedFields    = [
        'l_name',
        'l_type', // 類型(0:倉庫、1:工地)
        'l_create_by',
        'l_update_by',
        'l_update_at'
    ];

    // 定義 l_type 常數
    public const TYPE_WAREHOUSE = 0; // 倉庫
    public const TYPE_CONSTRUCTION_SITE = 1; // 工地

    /**
     * 根據 l_type 值取得中文名稱
     *
     * @param int $typeValue
     * @return string
     */
    public static function getTypeName(int $typeValue): string
    {
        switch ($typeValue) {
            case self::TYPE_WAREHOUSE:
                return '倉庫';
            case self::TYPE_CONSTRUCTION_SITE:
                return '工地';
            default:
                return '';
        }
    }

    public function getList($filter = [], $page = 1)
    {
        $builder = $this->builder('locations l')
            ->join('users u1', 'u1.u_id=l.l_create_by','left')
            ->join('users u2', 'u2.u_id=l.l_update_by', 'left')
            ->select('l.l_id, l.l_name, l.l_type, l.l_create_at, l.l_update_at, u1.u_name as creator, u2.u_name as updater');

        if (!empty($filter['keyword'])) {
            $builder->like('l.l_name', $filter['keyword']);
        }

        // 新增 l_type 篩選條件
        if (isset($filter['type']) && $filter['type'] !== '') {
            $builder->where('l.l_type', $filter['type']);
        }

        $builder->orderBy('l.l_id', 'DESC');

        $total = $builder->countAllResults(false);
        $perPage = 10;
        $totalPages = ceil($total / $perPage);
        $data = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        // 加入 l_type 的中文名稱
        foreach ($data as &$row) {
            $row['typeName'] = self::getTypeName($row['l_type']);
        }

        return [
            'data' => $data,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }

    public function getByType($type)
    {
        return $this->where('l_type', $type)
            ->select('l_id,l_name')
            ->findAll();
    }

    /**
     * 取得地點選項
     *
     * @return array
     */
    public function getDropdown()
    {
        return $this->select('l_id, l_name')
            ->orderBy('l_name', 'ASC')
            ->findAll();
    }

    /**
     * 取得工地選項
     *
     * @return array
     */
    public function getConstructionSiteDropdown()
    {
        return $this->where('l_type', self::TYPE_CONSTRUCTION_SITE)
            ->select('l_id, l_name')
            ->orderBy('l_name', 'ASC')
            ->findAll();
    }
}
