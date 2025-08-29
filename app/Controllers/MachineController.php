<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MachineModel;
use App\Services\PermissionService;
use Throwable;
use Exception;

class MachineController extends BaseController
{
    private $machineModel;
    private $permissionService;

    public function __construct()
    {
        $this->machineModel = new MachineModel();
        $this->permissionService = new PermissionService();
    }

    // 列表
    public function index()
    {
        $filter = [
            'm_name' => $this->request->getGet('name')
        ];
        $data = $this->machineModel->getList($filter);

        return view('machine/index', ['data' => $data, 'filter' => $filter]);
    }

    // 新增
    public function create()
    {
        return view('machine/form', ['isEdit' => false]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->machineModel->getInfoById($id);
        return view('machine/form', ['isEdit' => true, 'data' => $data]);
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
        try {
            $userId = session()->get('userId');

            if (!$userId) {
                return redirect()->to(url_to('AuthController::index'))->with('error', '請先登入！');
            }

            if (empty($data['m_id'])) {
                $data['m_create_by'] = $userId;
            } else {
                $data['m_update_by'] = $userId;
                $data['m_update_at'] = date('Y-m-d H:i:s');
            }

            $this->machineModel->save($data);

            return redirect()->to(url_to('MachineController::index'));
        } catch (Throwable $th) {
            $redirectUrl = !empty($data['m_id'])
                ? url_to('MachineController::edit', $data['m_id'])
                : url_to('MachineController::create');

            return redirect()->to($redirectUrl)
                             ->withInput()
                             ->with('error', $th->getMessage());
        }
    }

    // 刪除
    public function delete($id)
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        $this->machineModel->delete($id);
        return redirect()->to(url_to('MachineController::index'));
    }
}
