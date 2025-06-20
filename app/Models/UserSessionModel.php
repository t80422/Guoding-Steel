<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSessionModel extends Model
{
    protected $table            = 'user_sessions';
    protected $primaryKey       = 'us_id';
    protected $allowedFields    = [
        'us_u_id',
        'us_login_time',
        'us_logout_time'
    ];

    public function getLastLogin(string $userId)
    {
        return $this->where('us_u_id', $userId)
            ->orderBy('us_login_time', 'DESC')
            ->first();
    }

    public function getList($keyword)
    {
        $builder = $this->builder('user_sessions us')
            ->join('users u', 'u.u_id = us.us_u_id');

        if ($keyword) {
            $builder->like('u.u_name', $keyword);
        }

        return $builder->select('u.u_name, us.us_login_time, us.us_logout_time')
            ->orderBy('us_login_time', 'DESC')
            ->get()->getResultArray();
    }
}
