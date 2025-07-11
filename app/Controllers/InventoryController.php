<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventoryModel;
use App\Models\MajorCategoryModel;
use App\Models\MinorCategoryModel;
use App\Models\ProductModel;
use App\Models\LocationModel;

class InventoryController extends BaseController
{
    private $inventoryModel;
    private $majorCategoryModel;
    private $minorCategoryModel;
    private $productModel;
    private $locationModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->majorCategoryModel = new MajorCategoryModel();
        $this->minorCategoryModel = new MinorCategoryModel();
        $this->productModel = new ProductModel();
        $this->locationModel = new LocationModel();
    }

    // 列表
    public function index()
    {
        $filter = $this->request->getGet();
        $page = $filter['page'] ?? 1;

        $datas = $this->inventoryModel->getList($filter, $page);

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
        $data = $this->inventoryModel->getInfoById($id);
        return view('inventory/form', [
            'isEdit' => true, 
            'data' => $data
        ]);
    }

    // 取得小分類 (AJAX)
    public function getMinorCategories()
    {
        $mcId = $this->request->getPost('mc_id');
        if (!$mcId) {
            return $this->response->setJSON(['status' => 'error', 'message' => '大分類ID不能為空']);
        }

        $minorCategories = $this->minorCategoryModel->getNames($mcId);
        return $this->response->setJSON(['status' => 'success', 'data' => $minorCategories]);
    }

    // 取得品名 (AJAX)
    public function getProducts()
    {
        $micId = $this->request->getPost('mic_id');
        if (!$micId) {
            return $this->response->setJSON(['status' => 'error', 'message' => '小分類ID不能為空']);
        }

        $products = $this->productModel->getByMinorCategoryId($micId);
        return $this->response->setJSON(['status' => 'success', 'data' => $products]);
    }

    // 刪除
    public function delete($id)
    {
        $this->inventoryModel->delete($id);
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

            if (isset($data['i_id']) && !empty($data['i_id'])) {
                // 更新
                $data['i_update_by'] = $userId;
                $data['i_update_at'] = date('Y-m-d H:i:s');
            } else {
                // 新增 - 檢查地點和產品是否重複
                if ($this->inventoryModel->isDuplicateLocationProduct($data['i_pr_id'], $data['i_l_id'])) {
                    throw new \Exception("此地點和產品的組合已存在庫存記錄！");
                }
                
                $data['i_create_by'] = $userId;
            }
            
            $this->inventoryModel->save($data);
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
