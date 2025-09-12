<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MachineRepairModel;
use App\Models\MachineModel;
use App\Services\PermissionService;
use Exception;
use Throwable;

class MachineRepairController extends BaseController
{
    protected $machineRepairModel;
    protected $machineModel;
    protected $permissionService;

    public function __construct()
    {
        $this->machineRepairModel = new MachineRepairModel();
        $this->machineModel = new MachineModel();
        $this->permissionService = new PermissionService();
    }

    public function index()
    {
        $filter = $this->request->getGet();
        $page = $filter['page'] ?? 1;

        $datas = $this->machineRepairModel->getList($filter, $page);

        $pagerData = [
            'currentPage' => $datas['currentPage'],
            'totalPages' => $datas['totalPages']
        ];

        return view('machine_repair/index', [
            'data' => $datas['data'],
            'pager' => $pagerData,
            'filter' => $filter
        ]);
    }

    // 新增
    public function create()
    {
        $machines = $this->machineModel->getDropdown();
        return view('machine_repair/form', [
            'isEdit' => false,
            'data' => [],
            'machines' => $machines
        ]);
    }

    // 編輯

    public function edit($id)
    {
        $data = $this->machineRepairModel->getInfoById($id);
        $machines = $this->machineModel->getDropdown();
        return view('machine_repair/form', [
            'isEdit' => true,
            'data' => $data,
            'machines' => $machines
        ]);
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

            if (!empty($data['mr_id'])) {
                $data['mr_update_by'] = $userId;
                $data['mr_update_at'] = date('Y-m-d H:i:s');
            } else {
                $data['mr_create_by'] = $userId;
            }

            $this->machineRepairModel->save($data);
            return redirect()->to(url_to('MachineRepairController::index'));
        } catch (Throwable $th) {
            $redirectUrl = !empty($data['mr_id'])
                ? url_to('MachineRepairController::edit', $data['mr_id'])
                : url_to('MachineRepairController::create');

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
        try {
            $this->machineRepairModel->delete($id);
            return redirect()->to(url_to('MachineRepairController::index'));
        } catch (Exception $e) {
            return redirect()->to(url_to('MachineRepairController::index'))->with('error', $e->getMessage());
        }
    }
}
