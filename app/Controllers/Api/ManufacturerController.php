<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ManufacturerModel;
use CodeIgniter\API\ResponseTrait;

class ManufacturerController extends BaseController
{
    use ResponseTrait;

    private $manufacturerModel;

    public function __construct()
    {
        $this->manufacturerModel = new ManufacturerModel();
    }

    // 下拉選單
    public function dropdown()
    {
        $data = $this->manufacturerModel->getDropdown();
        return $this->respond($data);
    }
}
