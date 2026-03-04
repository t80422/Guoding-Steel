<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SwitchModel;
use CodeIgniter\API\ResponseTrait;

class SwitchController extends BaseController
{
    use ResponseTrait;

    protected $switchModel;
    protected $permissionService;

    public function __construct()
    {
        $this->switchModel = new SwitchModel();
        $this->permissionService = new \App\Services\PermissionService();
    }

    public function getSwitch()
    {
        $switch = $this->switchModel->find(1);
        return $this->respond($switch);
    }

    public function update()
    {
        try {
            $data = $this->request->getJSON(true);
            $userId = $this->request->getHeaderLine('X-User-ID') ?: ($data['userId'] ?? null);

            // 檢查權限 (API)
            $permissionCheck = $this->permissionService->getPermissionStatus('edit', $userId);
            if ($permissionCheck['status'] === 'error') {
                return $this->failForbidden($permissionCheck['message']);
            }

            $this->switchModel->update(1, $data);
            return $this->respondNoContent();
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return $this->fail('更新失敗');
        }
    }
}
