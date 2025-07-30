<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\MajorCategoryModel;
use App\Models\MinorCategoryModel;

class ProductController extends BaseController
{
    protected $productModel;
    protected $majorCategoryModel;
    protected $minorCategoryModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->majorCategoryModel = new MajorCategoryModel();
        $this->minorCategoryModel = new MinorCategoryModel();
    }

    // 列表
    public function index()
    {
        $filter = $this->request->getGet();
        $page = $filter['page'] ?? 1;

        $data = $this->productModel->getList($filter, $page);

        $pagerData = [
            'currentPage' => $data['currentPage'],
            'totalPages' => $data['totalPages']
        ];
        return view('product/index', [
            'data' => $data['data'],
            'pager' => $pagerData,
            'filter' => $filter
        ]);
    }

    // 新增
    public function create()
    {
        $majorCategories = $this->majorCategoryModel->getDropdown();

        return view('product/form', [
            'isEdit' => false,
            'majorCategories' => $majorCategories
        ]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->productModel->find($id);

        if (!$data) {
            return redirect()->to(url_to('ProductController::index'))
                ->with('error', '找不到該產品資料');
        }

        $majorCategories = $this->majorCategoryModel->getDropdown();
        $mcId=$this->minorCategoryModel->find($data['pr_mic_id'])['mic_mc_id'];

        return view('product/form', [
            'isEdit' => true,
            'data' => $data,
            'majorCategories' => $majorCategories,
            'mcId' => $mcId
        ]);
    }

    // 儲存
    public function save()
    {
        $data = $this->request->getPost();
        $userId = session()->get('userId');

        if (!$userId) {
            return redirect()->to(url_to('AuthController::index'))
                ->with('error', '請先登入！');
        }

        // 處理 checkbox：如果沒有被勾選，設定為 0
        $data['pr_is_length'] = $data['pr_is_length'] ?? 0;

        if (empty($data['pr_id'])) {
            $data['pr_create_by'] = $userId;
        } else {
            $data['pr_update_by'] = $userId;
            $data['pr_update_at'] = date('Y-m-d H:i:s');
        }
        $this->productModel->save($data);

        return redirect()->to(url_to('ProductController::index'));
    }

    // 刪除
    public function delete($id)
    {
        $this->productModel->delete($id);
        return redirect()->to(url_to('ProductController::index'));
    }
}
