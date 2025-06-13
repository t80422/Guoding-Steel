<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MinorCategoryModel;
use App\Models\MajorCategoryModel;

class MinorCategoryController extends BaseController
{
    protected $minorCategoryModel;
    protected $majorCategoryModel;

    public function __construct()
    {
        $this->minorCategoryModel = new MinorCategoryModel();
        $this->majorCategoryModel = new MajorCategoryModel();
    }

    // 列表
    public function index()
    {
        $keyword = $this->request->getGet('keyword');
        $data = $this->minorCategoryModel->getList($keyword);

        return view('minorCategory/index', [
            'data' => $data,
            'keyword' => $keyword
        ]);
    }

    // 新增
    public function create()
    {
        $majorCategories = $this->majorCategoryModel->getNames();
        return view('minorCategory/form', ['isEdit' => false, 'majorCategories' => $majorCategories]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->minorCategoryModel->find($id);
        $majorCategories = $this->majorCategoryModel->getNames();
        return view('minorCategory/form', ['isEdit' => true, 'data' => $data, 'majorCategories' => $majorCategories]);
    }

    // 儲存
    public function save()
    {
        $data = $this->request->getPost();

        if(empty($data['mic_id'])){
            $data['mic_create_by'] = session()->get('userId');
        }else{
            $data['mic_update_by'] = session()->get('userId');
            $data['mic_update_at'] = date('Y-m-d H:i:s');
        }
        $this->minorCategoryModel->save($data);

        return redirect()->to(url_to('MinorCategoryController::index'));
    }

    // 刪除
    public function delete($id)
    {
        $this->minorCategoryModel->delete($id);
        return redirect()->to(url_to('MinorCategoryController::index'));
    }
}
