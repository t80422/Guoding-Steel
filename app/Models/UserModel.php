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
}
