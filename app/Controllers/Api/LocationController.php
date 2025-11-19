<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\LocationModel;
use App\Services\LocationMaterialService;

class LocationController extends BaseController
{
    private $locationModel;
    private $locationMaterialService;

    public function __construct()
    {
        $this->locationModel = new LocationModel();
        $this->locationMaterialService = new LocationMaterialService();
    }

    public function getLocations($type)
    {
        $locations = $this->locationModel->getByType($type);
        return $this->response->setJSON($locations);
    }

    // 工地用料情況
    public function materialUsage($id)
    {
        $location = $this->locationModel->find($id);

        // 取得搜尋參數
        $searchParams = [
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
            'type' => $this->request->getGet('type'),
            'keyword' => $this->request->getGet('keyword')
        ];

        // 取得詳細用料情況（包含工地項目和產品明細）
        $materialData = $this->locationMaterialService->getMaterialUsage($id, $searchParams);

        return $this->response->setJSON([
            'location' => $location,
            'orders' => $materialData['orders'],
            'all_projects' => $materialData['all_projects'],
            'all_products' => $materialData['all_products'],
        ]);
    }
}
