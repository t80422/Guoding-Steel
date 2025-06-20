<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\GpsModel;
use CodeIgniter\API\ResponseTrait;

class GpsController extends BaseController
{
    use ResponseTrait;

    protected $gpsModel;

    public function __construct()
    {
        $this->gpsModel = new GpsModel();
    }

    public function getOptions(){
        $data = $this->gpsModel->getOptions();
        return $this->respond($data);
    }
}
