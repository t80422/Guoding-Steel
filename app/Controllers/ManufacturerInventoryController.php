<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ManufacturerInventoryModel;
use App\Models\ManufacturerModel;
use App\Models\MajorCategoryModel;
use App\Services\ManufacturerInventoryService;
use App\Services\PermissionService;

class ManufacturerInventoryController extends BaseController
{
    protected $manufacturerInventoryModel;
    protected $manufacturerModel;
    protected $majorCategoryModel;
    protected $manufacturerInventoryService;
    protected $permissionService;

    public function __construct()
    {
        $this->manufacturerInventoryModel = new ManufacturerInventoryModel();
        $this->manufacturerModel = new ManufacturerModel();
        $this->majorCategoryModel = new MajorCategoryModel();
        $this->manufacturerInventoryService = new ManufacturerInventoryService();
        $this->permissionService = new PermissionService();
    }

    public function index()
    {
        $filter = $this->request->getGet();
        $page = $filter['page'] ?? 1;

        $datas = $this->manufacturerInventoryModel->getList($filter, $page);
        $pagerData = [
            'currentPage' => $datas['currentPage'],
            'totalPages' => $datas['totalPages']
        ];

        return view('manufacturer_inventory/index', [
            'data' => $datas['data'],
            'pager' => $pagerData,
            'filter' => $filter
        ]);
    }

    public function create()
    {
        $manufacturers = $this->manufacturerModel->getDropdown();
        $majorCategories = $this->majorCategoryModel->getDropdown();

        return view('manufacturer_inventory/form', [
            'isEdit' => false,
            'majorCategories' => $majorCategories,
            'manufacturers' => $manufacturers
        ]);
    }

    public function edit($id)
    {
        $data = $this->manufacturerInventoryService->getInventoryInfo($id);
        return view('manufacturer_inventory/form', [
            'isEdit' => true,
            'data' => $data
        ]);
    }

    // 儲存
    public function save()
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        try {
            $data = $this->request->getPost();
            $userId = session()->get('userId');

            if (empty($userId)) {
                return redirect()->to(url_to('AuthController::index'))
                    ->with('error', '請先登入！');
            }

            $this->manufacturerInventoryService->saveManufacturerInventory($data);
            return redirect()->to(url_to('ManufacturerInventoryController::index'));
        } catch (\Exception $e) {
            $redirectUrl = isset($data['mi_id']) && !empty($data['mi_id'])
                ? url_to('ManufacturerInventoryController::edit', $data['mi_id'])
                : url_to('ManufacturerInventoryController::create');

            return redirect()->to($redirectUrl)
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
