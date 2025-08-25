<?php

namespace App\Controllers\Api;

use App\Models\InventoryModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

class RoadPlateController extends Controller
{
    use ResponseTrait;

    private $inventoryModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
    }

    public function index()
    {
        $filter = $this->request->getGet();
        $page = $filter['page'] ?? 1;

        $data = $this->inventoryModel->getRoadPlateList($filter, $page);

        $pagerData = [
            'currentPage' => $data['currentPage'],
            'totalPages' => $data['totalPages']
        ];

        return $this->response->setJSON([
            'data' => $data['data'],
            'pager' => $pagerData
        ]);
    }
}
