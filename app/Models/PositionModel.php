<?php

namespace App\Models;

use CodeIgniter\Model;

class PositionModel extends Model
{
    protected $table            = 'positions';
    protected $primaryKey       = 'p_id';
    protected $allowedFields    = [
        'p_name',
        'p_create_by',
        'p_update_by',
        'p_update_at'
    ];
}
