<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProjectItemModel;
use App\Services\PermissionService;

class ProjectItemController extends BaseController
{
    protected $projectItemModel;
    protected $permissionService;

    public function __construct()
    {
        $this->projectItemModel = new ProjectItemModel();
        $this->permissionService = new PermissionService();
    }

    public function index()
    {
        $filter = $this->request->getGet();
        $page = $filter['page'] ?? 1;

        $data = $this->projectItemModel->getList($filter, $page);

        $pagerData = [
            'currentPage' => $data['currentPage'],
            'totalPages' => $data['totalPages']
        ];

        return view('project_item/index', [
            'data' => $data['data'],
            'pager' => $pagerData,
            'filter' => $filter
        ]);
    }

    // 新增
    public function create()
    {
        return view('project_item/form', [
            'isEdit' => false
        ]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->projectItemModel->getDetail($id);

        if (!$data) {
            return redirect()->to(url_to('ProjectItemController::index'))
                ->with('error', '找不到該產品資料');
        }

        return view('project_item/form', [
            'isEdit' => true,
            'data' => $data,
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

        $data = $this->request->getPost();
        $userId = session()->get('userId');

        if (!$userId) {
            return redirect()->to(url_to('AuthController::index'))
                ->with('error', '請先登入！');
        }

        if (empty($data['pi_id'])) {
            $data['pi_create_by'] = $userId;
        } else {
            $data['pi_update_by'] = $userId;
            $data['pi_update_at'] = date('Y-m-d H:i:s');
        }
        $this->projectItemModel->save($data);

        return redirect()->to(url_to('ProjectItemController::index'));
    }

    // 刪除
    public function delete($id)
    {
        // 檢查權限
        $permissionCheck = $this->permissionService->validateEditPermission();
        if ($permissionCheck['status'] === 'error') {
            return redirect()->back()->with('error', $permissionCheck['message']);
        }

        $this->projectItemModel->delete($id);
        return redirect()->to(url_to('ProjectItemController::index'));
    }

    // 取得項目
    public function getItems(){
        $items = $this->projectItemModel->getIdAndNames();
        return $this->response->setJSON($items);
    }
}
