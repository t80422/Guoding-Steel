<?php

namespace App\Services;

use App\Models\UserModel;

class PermissionService
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * 檢查目前使用者是否為唯讀權限
     * @return bool
     */
    public function isReadonlyUser(): bool
    {
        $userId = session()->get('userId');
        if (!$userId) {
            return false;
        }

        // 從 session 取得權限狀態（優先）
        $isReadonly = session()->get('isReadonly');
        if ($isReadonly !== null) {
            return (bool) $isReadonly;
        }

        // 如果 session 中沒有，從資料庫查詢
        $user = $this->userModel->find($userId);
        return $user ? (bool) ($user['u_is_readonly'] ?? false) : false;
    }

    /**
     * 檢查目前使用者是否可以編輯資料
     * @return bool
     */
    public function canEditData(): bool
    {
        return !$this->isReadonlyUser();
    }

    /**
     * 檢查目前使用者是否為管理員
     * @return bool
     */
    public function isAdmin(): bool
    {
        $userId = session()->get('userId');
        if (!$userId) {
            return false;
        }

        // 從 session 取得管理員狀態（優先）
        $isAdmin = session()->get('isAdmin');
        if ($isAdmin !== null) {
            return (bool) $isAdmin;
        }

        // 如果 session 中沒有，從資料庫查詢
        $user = $this->userModel->find($userId);
        return $user ? (bool) ($user['u_is_admin'] ?? false) : false;
    }

    /**
     * 驗證編輯權限，如果是唯讀使用者則拋出例外或返回錯誤訊息
     * @param bool $throwException 是否拋出例外
     * @return array|bool 如果不拋出例外，返回檢查結果陣列；否則返回 true 或拋出例外
     * @throws \Exception
     */
    public function validateEditPermission($throwException = false)
    {
        if ($this->isReadonlyUser()) {
            if ($throwException) {
                throw new \Exception('您的帳號為唯讀權限，無法執行編輯操作。');
            }
            
            return [
                'status' => 'error',
                'message' => '您的帳號為唯讀權限，無法執行此操作。',
                'statusCode' => 403
            ];
        }

        return $throwException ? true : [
            'status' => 'success',
            'message' => '權限驗證通過'
        ];
    }

    /**
     * 取得使用者權限等級描述
     * @param int|null $userId 使用者ID，若為 null 則使用當前登入使用者
     * @return string
     */
    public function getUserPermissionLevel($userId = null): string
    {
        if ($userId === null) {
            $userId = session()->get('userId');
        }

        if (!$userId) {
            return '未登入';
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return '使用者不存在';
        }

        if ($user['u_is_admin']) {
            return '管理員';
        }

        if ($user['u_is_readonly']) {
            return '唯讀使用者';
        }

        return '一般使用者';
    }

    /**
     * 取得權限狀態陣列（用於前端顯示）
     * @return array
     */
    public function getPermissionStatus(): array
    {
        return [
            'canEdit' => $this->canEditData(),
            'isReadonly' => $this->isReadonlyUser(),
            'isAdmin' => $this->isAdmin(),
            'permissionLevel' => $this->getUserPermissionLevel()
        ];
    }
}
