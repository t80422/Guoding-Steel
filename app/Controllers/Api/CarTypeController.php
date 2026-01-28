<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\CarTypeModel;

class CarTypeController extends BaseController
{
    use ResponseTrait;

    protected $carTypeModel;

    public function __construct()
    {
        $this->carTypeModel = new CarTypeModel();
    }

    public function getOptions()
    {
        $data = $this->carTypeModel->getDropdown();
        return $this->respond($data);
    }
}
