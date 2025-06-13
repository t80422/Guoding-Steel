<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MinorCategoryModel;

class MinorCategoryController extends BaseController
{
    protected $minorCategoryModel;

    public function __construct()
    {
        $this->minorCategoryModel = new MinorCategoryModel();
    }

    public function getMinorCategories($mcId){
        $data = $this->minorCategoryModel->getNames($mcId);
        return $this->response->setJSON($data);
    }
}
