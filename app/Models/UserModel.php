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
        'u_p_id'
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

    public function getList($keyword)
    {
        $builder = $this->builder('users u')
            ->join('positions p', 'p.p_id=u.u_p_id','left')
            ->select('u.u_id, u.u_name, p.p_name, u.u_create_at');

        if(!empty($keyword)){
            $builder->groupStart()
                ->like('u.u_name',$keyword)
                ->orLike('p.p_name',$keyword)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }
}
