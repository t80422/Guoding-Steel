<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MajorCategoryModel;

// 大分類
class MajorCategoryController extends BaseController
{
    protected $majorCategoryModel;

    public function __construct()
    {
        $this->majorCategoryModel = new MajorCategoryModel();
    }

    // 列表
    public function index()
    {
        $keyword = $this->request->getGet('keyword');
        $data = $this->majorCategoryModel->getList($keyword);

        return view('majorCategory/index', [
            'data' => $data,
            'keyword' => $keyword
        ]);
    }

    // 新增
    public function create()
    {
        return view('majorCategory/form', ['isEdit' => false]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->majorCategoryModel->find($id);
        return view('majorCategory/form', ['isEdit' => true, 'data' => $data]);
    }

    // 儲存
    public function save()
    {
        $data = $this->request->getPost();

        if(empty($data['mc_id'])){
            $data['mc_create_by'] = session()->get('userId');
        }else{
            $data['mc_update_by'] = session()->get('userId');
            $data['mc_update_at'] = date('Y-m-d H:i:s');
        }
        $this->majorCategoryModel->save($data);

        return redirect()->to(url_to('MajorCategoryController::index'));
    }

    // 刪除
    public function delete($id)
    {
        $this->majorCategoryModel->delete($id);
        return redirect()->to(url_to('MajorCategoryController::index'));
    }
}
