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

    // 取得登入用戶列表資料 
    public function getLoginData()
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
}
