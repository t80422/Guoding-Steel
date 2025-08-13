<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MajorCategoryModel;
use App\Models\LocationModel;
use App\Services\InventoryService;

class InventoryController extends BaseController
{
    private $majorCategoryModel;
    private $locationModel;
    private $inventoryService;

    public function __construct()
    {
        $this->majorCategoryModel = new MajorCategoryModel();
        $this->locationModel = new LocationModel();
        $this->inventoryService = new InventoryService();
    }

    // 列表
    public function index()
    {
        $filter = $this->request->getGet();
        $page = $filter['page'] ?? 1;

        $datas = $this->inventoryService->getInventoryList($filter, $page);

        $pagerData = [
            'currentPage' => $datas['currentPage'],
            'totalPages' => $datas['totalPages']
        ];

        return view('inventory/index', [
            'data' => $datas['data'],
            'pager' => $pagerData,
            'filter' => $filter
        ]);
    }

    // 新增
    public function create()
    {
        $majorCategories = $this->majorCategoryModel->getDropdown();
        $locations = $this->locationModel->getDropdown();
        return view('inventory/form', [
            'isEdit' => false,
            'majorCategories' => $majorCategories,
            'locations' => $locations
        ]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->inventoryService->getInventoryInfo($id);
        return view('inventory/form', [
            'isEdit' => true, 
            'data' => $data
        ]);
    }

    // 刪除
    public function delete($id)
    {
        $this->inventoryService->deleteInventory($id);
        return redirect()->to(url_to('InventoryController::index'))->with('success', '刪除成功');
    }

    // 儲存
    public function save()
    {
        try {
            $data = $this->request->getPost();
            $userId = session()->get('userId');
        
            if(empty($userId)){
                return redirect()->to(url_to('AuthController::index'))
                    ->with('error', '請先登入！');
            }

            $this->inventoryService->saveInventory($data);
            return redirect()->to(url_to('InventoryController::index'))
                ->with('success', '儲存成功！');
                
        } catch (\Exception $e) {
            $redirectUrl = isset($data['i_id']) && !empty($data['i_id'])
                ? url_to('InventoryController::edit', $data['i_id'])
                : url_to('InventoryController::create');

            return redirect()->to($redirectUrl)
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
