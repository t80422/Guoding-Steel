<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MajorCategoryModel;

class MajorCategoryController extends BaseController
{
    protected $majorCategoryModel;

    public function __construct()
    {
        $this->majorCategoryModel = new MajorCategoryModel();
    }

    public function getMajorCategories(){
        $data = $this->majorCategoryModel->getNames();
        return $this->response->setJSON($data);
    }
}
