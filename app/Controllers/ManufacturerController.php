<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ManufacturerModel;
use App\Services\PermissionService;
use Throwable;

class ManufacturerController extends BaseController
{
    protected $manufacturerModel;
    protected $permissionService;

    public function __construct()
    {
        $this->manufacturerModel = new ManufacturerModel();
        $this->permissionService = new PermissionService();
    }

    // 列表
    public function index()
    {
        $filter = $this->request->getGet();
        $page = $filter['page'] ?? 1;

        $datas = $this->manufacturerModel->getList($filter, $page);

        $pagerData = [
            'currentPage' => $datas['currentPage'],
            'totalPages' => $datas['totalPages']
        ];

        return view('manufacturer/index', [
            'data' => $datas['data'],
            'pager' => $pagerData,
            'filter' => $filter
        ]);
    }

    // 新增
    public function create()
    {
        return view('manufacturer/form', [
            'isEdit' => false
        ]);
    }

    // 編輯
    public function edit($id)
    {
        return view('manufacturer/form', [
            'isEdit' => true,
            'data' => $this->manufacturerModel->getInfoById($id)
        ]);
    }

    // 刪除
    public function delete($id)
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        $this->manufacturerModel->delete($id);
        return redirect()->to(url_to('ManufacturerController::index'))->with('success', '刪除成功');
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

            if (!$userId) {
                return redirect()->to(url_to('AuthController::index'))->with('error', '請先登入！');
            }

            if (!empty($data['ma_id'])) {
                $data['ma_update_by'] = $userId;
                $data['ma_update_at'] = date('Y-m-d H:i:s');
            } else {
                $data['ma_create_by'] = $userId;
            }

            $this->manufacturerModel->save($data);
            return redirect()->to(url_to('ManufacturerController::index'));
        } catch (Throwable $th) {
            $redirectUrl = !empty($data['ma_id'])
                ? url_to('ManufacturerController::edit', $data['ma_id'])
                : url_to('ManufacturerController::create');

            return redirect()->to($redirectUrl)
                ->withInput()
                ->with('error', $th->getMessage());
        }
    }
}
