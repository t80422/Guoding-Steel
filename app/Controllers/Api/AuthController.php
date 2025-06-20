<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\AuthService;
use CodeIgniter\API\ResponseTrait;

class AuthController extends BaseController
{
    use ResponseTrait;

    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    // 獲取使用者清單 
    public function getUsersList()
    {
        $data = $this->authService->getUsersDropdown();
        return $this->respond($data);
    }

    // 登入
    public function login()
    {
        // 取得輸入資料
        $userId = $this->request->getJSON(true)['userId'];
        $password = $this->request->getJSON(true)['password'];

        // 使用 AuthService 進行完整的驗證和認證
        $result = $this->authService->validateAndAuthenticate($userId, $password);

        if ($result['status'] === 'success') {
            // 紀錄登入時間
            $this->authService->recordLoginTime($userId);
            // 成功登入，回傳用戶資訊
            return $this->respond($result['message']);
        } else {
            // 處理不同類型的錯誤
            if (isset($result['validationErrors'])) {
                return $this->fail($result['validationErrors']);
            } else {
                return $this->fail($result['message']);
            }
        }
    }

    // 登出
    public function logout($userId)
    {
        $this->authService->recordLogoutTime($userId);
        return $this->respond('登出成功');
    }
}
