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
        $keyword = $this->request->getGet('keyword');
        $data = $this->productModel->getList($keyword);

        return view('product/index', [
            'data' => $data,
            'keyword' => $keyword
        ]);
    }

    // 新增
    public function create()
    {
        $majorCategories = $this->majorCategoryModel->getNames();

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

        $majorCategories = $this->majorCategoryModel->getNames();
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

        if (empty($data['pr_id'])) {
            $data['pr_create_by'] = session()->get('userId');
        } else {
            $data['pr_update_by'] = session()->get('userId');
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
