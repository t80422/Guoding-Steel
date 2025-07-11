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

    /**
     * 取得最後一次登入
     * @param string $userId 使用者ID
     * @return array
     */
    public function getLastLogin(string $userId)
    {
        return $this->where('us_u_id', $userId)
            ->orderBy('us_login_time', 'DESC')
            ->first();
    }

    /**
     * 取得登入登出紀錄
     * @param string $keyword 關鍵字
     * @param int $page 頁碼
     * @return array
     */
    public function getList($keyword, $page)
    {
        $builder = $this->builder('user_sessions us')
            ->join('users u', 'u.u_id = us.us_u_id')
            ->select('u.u_name, us.us_login_time, us.us_logout_time');

        if ($keyword) {
            $builder->like('u.u_name', $keyword);
        }

        $builder->orderBy('us_login_time', 'DESC');

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
}
