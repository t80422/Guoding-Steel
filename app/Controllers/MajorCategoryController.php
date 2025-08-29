<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MajorCategoryModel;
use App\Services\PermissionService;

// 大分類
class MajorCategoryController extends BaseController
{
    protected $majorCategoryModel;
    protected $permissionService;

    public function __construct()
    {
        $this->majorCategoryModel = new MajorCategoryModel();
        $this->permissionService = new PermissionService();
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
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        $data = $this->request->getPost();
        $userId = session()->get('userId');

        if (!$userId) {
            return redirect()->to(url_to('AuthController::index'))
                ->with('error', '請先登入！');
        }

        if(empty($data['mc_id'])){
            $data['mc_create_by'] = $userId;
        }else{
            $data['mc_update_by'] = $userId;
            $data['mc_update_at'] = date('Y-m-d H:i:s');
        }
        $this->majorCategoryModel->save($data);

        return redirect()->to(url_to('MajorCategoryController::index'));
    }

    // 刪除
    public function delete($id)
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        $this->majorCategoryModel->delete($id);
        return redirect()->to(url_to('MajorCategoryController::index'));
    }
}
