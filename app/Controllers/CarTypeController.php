<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CarTypeModel;
use App\Services\PermissionService;

// 車種管理
class CarTypeController extends BaseController
{
    protected $carTypeModel;
    protected $permissionService;

    public function __construct()
    {
        $this->carTypeModel = new CarTypeModel();
        $this->permissionService = new PermissionService();
        // 載入資料清理 Helper
        helper('data_sanitizer');
    }

    // 列表
    public function index()
    {
        $keyword = $this->request->getGet('keyword');
        $data = $this->carTypeModel->getList($keyword);

        return view('car_type/index', [
            'data' => $data,
            'keyword' => $keyword
        ]);
    }

    // 新增
    public function create()
    {
        return view('car_type/form', ['isEdit' => false]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->carTypeModel->find($id);
        return view('car_type/form', ['isEdit' => true, 'data' => $data]);
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
        $data = sanitize_form_data($data, ['ct_id']);
        $userId = session()->get('userId');

        if (!$userId) {
            return redirect()->to(url_to('AuthController::index'))
                ->with('error', '請先登入！');
        }

        if (empty($data['ct_id'])) {
            $data['ct_created_by'] = $userId;
        } else {
            $data['ct_updated_by'] = $userId;
        }
        $this->carTypeModel->save($data);

        return redirect()->to(url_to('CarTypeController::index'));
    }

    // 刪除
    public function delete($id)
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        $this->carTypeModel->delete($id);
        return redirect()->to(url_to('CarTypeController::index'));
    }
}
