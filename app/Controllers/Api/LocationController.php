<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\LocationModel;

class LocationController extends BaseController
{
    private $locationModel;

    public function __construct()
    {
        $this->locationModel = new LocationModel();
    }

    public function getLocations($type){
        $locations = $this->locationModel->getByType($type);
        return $this->response->setJSON($locations);
    }
}
