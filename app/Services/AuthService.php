<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserSessionModel;

class AuthService
{
    protected $userModel;
    protected $userSessionModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userSessionModel = new UserSessionModel();
    }

    /**
     * 取得登入驗證規則
     * @return array
     */
    public function getLoginValidationRules(): array
    {
        return [
            'userId' => 'required',
            'password' => 'required'
        ];
    }

    /**
     * 取得登入驗證錯誤訊息
     * @return array
     */
    public function getLoginValidationMessages(): array
    {
        return [
            'userId' => [
                'required' => '使用者 ID 為必填項。',
            ],
            'password' => [
                'required' => '密碼為必填項。',
            ],
        ];
    }

    /**
     * 驗證登入資料
     * @param array $data
     * @return array
     */
    public function validateLoginData(array $data): array
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->getLoginValidationRules(), $this->getLoginValidationMessages());

        if (!$validation->run($data)) {
            return [
                'status' => 'error',
                'validationErrors' => $validation->getErrors()
            ];
        }

        return [
            'status' => 'success',
            'message' => '資料驗證成功'
        ];
    }

    /**
     * 處理登入驗證和身份驗證的核心邏輯
     * @param string $userId
     * @param string $password
     * @return array 包含狀態、訊息、資料或錯誤的陣列
     */
    public function validateAndAuthenticate(string $userId, string $password): array
    {
        try {
            // 先驗證輸入資料
            $validationResult = $this->validateLoginData([
                'userId' => $userId,
                'password' => $password
            ]);

            if ($validationResult['status'] === 'error') {
                return $validationResult;
            }

            // 查找使用者
            $user = $this->userModel->find($userId);

            if ($user && password_verify($password, $user['u_password'])) {
                return [
                    'status' => 'success',
                    'message' => '登入成功',
                    'data' => [
                        'userName' => $user['u_name'],
                        'userId' => $user['u_id']
                    ]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => '使用者名稱或密碼錯誤。',
                    'statusCode' => 401
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'AuthService 驗證錯誤: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => '伺服器內部錯誤',
                'statusCode' => 500
            ];
        }
    }

    /**
     * 獲取登入頁面所需的用戶數據
     * @return array
     */
    public function getUsersDropdown(): array
    {
        try {
            $datas = $this->userModel->getUsersWithPosition();

            $formattedDatas = [];

            foreach ($datas as $data) {
                $formattedDatas[] = [
                    'u_id' => $data['u_id'],
                    'name' => $data['u_name'] . '-' . $data['p_name']
                ];
            }

            return $formattedDatas;
        } catch (\Exception $e) {
            log_message('error', 'AuthService 取得登入資料錯誤: ' . $e->getMessage());
            return [
                'users' => []
            ];
        }
    }

    /**
     * 處理 Session 設定
     * @param array $userData
     * @return bool
     */
    public function setUserSession(array $userData): bool
    {
        try {
            session()->set('userName', $userData['userName']);
            session()->set('userId', $userData['userId']);
            return true;
        } catch (\Exception $e) {
            log_message('error', 'AuthService Session 設定錯誤: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 紀錄登入時間
     * @param string $userId
     * @return void
     */
    public function recordLoginTime(string $userId)
    {
        try {
            $this->userSessionModel->insert([
                'us_u_id' => $userId,
                'us_login_time' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 紀錄登出時間
     * @param string $userId
     * @return void
     */
    public function recordLogoutTime(string $userId)
    {
        try {
            $lastLogin = $this->userSessionModel->getLastLogin($userId);
            $this->userSessionModel->update($lastLogin['us_id'], ['us_logout_time' => date('Y-m-d H:i:s')]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAuthLogs($keyword, $page)
    {
        return $this->userSessionModel->getList($keyword, $page);
    }
}
