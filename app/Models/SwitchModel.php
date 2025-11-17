<?php

namespace App\Models;

use CodeIgniter\Model;

class SwitchModel extends Model
{
    protected $table            = 'switch';
    protected $primaryKey       = 's_id';
    protected $allowedFields    = [
        's_switch'
    ];
}
