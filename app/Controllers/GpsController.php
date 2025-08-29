<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GpsModel;
use App\Services\PermissionService;

class GpsController extends BaseController
{
    protected $gpsModel;
    protected $permissionService;

    public function __construct()
    {
        $this->gpsModel = new GpsModel();
        $this->permissionService = new PermissionService();
    }

    public function index()
    {
        $keyword = $this->request->getGet('keyword');
        $data = $this->gpsModel->getList($keyword);
        return view('gps/index', [
            'data' => $data,
            'keyword' => $keyword
        ]);
    }

    public function create()
    {
        return view('gps/form', ['isEdit' => false]);
    }

    public function edit($id)
    {
        $data = $this->gpsModel->find($id);
        return view('gps/form', ['isEdit' => true, 'data' => $data]);
    }

    public function delete($id)
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        $this->gpsModel->delete($id);
        return redirect()->to(url_to('GpsController::index'));
    }

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
            return redirect()->to(url_to('AuthController::index'))->with('error', '請先登入');
        }

        if (isset($data['g_id'])) {
            $data['g_update_by'] = $userId;
            $data['g_update_at'] = date('Y-m-d H:i:s');
        } else {
            $data['g_create_by'] = $userId;
        }

        $this->gpsModel->save($data);
        return redirect()->to(url_to('GpsController::index'));
    }
}
