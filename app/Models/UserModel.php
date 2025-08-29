<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'u_id';
    protected $allowedFields    = [
        'u_name',
        'u_password',
        'u_p_id',
        'u_is_admin',
        'u_is_readonly'
    ];

    /**
     * 取得使用者下拉選單
     * @return array
     */
    public function getUsersWithPosition()
    {
        return $this->builder('users u')
            ->join('positions p', 'p.p_id=u.u_p_id','left')
            ->select('u.u_id, u.u_name, p.p_name')
            ->get()->getResultArray();
    }

    public function getList($keyword, $page)
    {
        $builder = $this->builder('users u')
            ->join('positions p', 'p.p_id=u.u_p_id','left')
            ->select('u.u_id, u.u_name, p.p_name, u.u_create_at, u.u_is_admin, u.u_is_readonly');

        if(!empty($keyword)){
            $builder->groupStart()
                ->like('u.u_name',$keyword)
                ->orLike('p.p_name',$keyword)
                ->groupEnd();
        }

        $builder->orderBy('u.u_create_at', 'DESC');
        
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

    public function getDropdownByIsAdmin(){
        return $this->builder()
            ->where('u_is_admin', 1)
            ->select('u_id, u_name')
            ->get()->getResultArray();
    }
}
