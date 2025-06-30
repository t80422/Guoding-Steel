<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MachineMaintenanceModel;
use App\Models\MachineModel;
use Throwable;

// 機械保養
class MachineMaintenanceController extends BaseController
{
    private $machineMaintenanceModel;
    private $machineModel;

    public function __construct()
    {
        $this->machineMaintenanceModel = new MachineMaintenanceModel();
        $this->machineModel = new MachineModel();
    }

    // 列表
    public function index()
    {
        $filter = $this->request->getGet();
        $page = $this->request->getGet('page') ?? 1;

        $result = $this->machineMaintenanceModel->getList($filter, $page);

        $pagerData = [
            'currentPage' => $result['currentPage'],
            'totalPages' => $result['totalPages']
        ];

        return view('machine_maintenance/index', [
            'data' => $result['data'],
            'pager' => $pagerData,
            'filter' => $filter
        ]);
    }

    // 新增
    public function create()
    {
        $machines = $this->machineModel->getDropdown();
        return view('machine_maintenance/form', ['isEdit' => false, 'machines' => $machines]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->machineMaintenanceModel->getInfoById($id);
        $machines = $this->machineModel->getDropdown();

        return view('machine_maintenance/form', [
            'isEdit' => true,
            'data' => $data,
            'machines' => $machines
        ]);
    }

    // 儲存
    public function save()
    {
        try {
            $data = $this->request->getPost();
            $userId = session()->get('userId');

            if (!$userId) {
                return redirect()->to(url_to('AuthController::index'))->with('error', '請先登入！');
            }

            if (!empty($data['mm_id'])) {
                $data['mm_update_by'] = $userId;
                $data['mm_update_at'] = date('Y-m-d H:i:s');
            } else {
                $data['mm_create_by'] = $userId;
            }

            $this->machineMaintenanceModel->save($data);
            return redirect()->to(url_to('MachineMaintenanceController::index'));
        } catch (Throwable $th) {
            $redirectUrl = !empty($data['mm_id'])
                ? url_to('MachineMaintenanceController::edit', $data['mm_id'])
                : url_to('MachineMaintenanceController::create');

            return redirect()->to($redirectUrl)
                ->withInput()
                ->with('error', $th->getMessage());
        }
    }

    // 刪除
    public function delete($id)
    {
        $this->machineMaintenanceModel->delete($id);
        return redirect()->to(url_to('MachineMaintenanceController::index'));
    }
}
