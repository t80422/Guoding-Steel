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
     * 檢查指定使用者是否為唯讀權限
     * @param int|null $userId
     * @return bool
     */
    public function isReadonlyUser($userId = null): bool
    {
        if ($userId === null) {
            $userId = session()->get('userId');
            if (!$userId) return false;

            // 從 session 取得權限狀態（優先）
            $isReadonly = session()->get('isReadonly');
            if ($isReadonly !== null) {
                return (bool) $isReadonly;
            }
        }

        // 如果 session 中沒有或手動帶入 ID，從資料庫查詢
        $user = $this->userModel->find($userId);
        return $user ? (bool) ($user['u_is_readonly'] ?? false) : false;
    }

    /**
     * 檢查指定使用者是否為「只能編輯」權限
     * @param int|null $userId
     * @return bool
     */
    public function isEditOnlyUser($userId = null): bool
    {
        if ($userId === null) {
            $userId = session()->get('userId');
            if (!$userId) return false;

            // 從 session 取得比例者權限狀態（優先）
            $isEditOnly = session()->get('isEditOnly');
            if ($isEditOnly !== null) {
                return (bool) $isEditOnly;
            }
        }

        // 如果手動帶入 ID 或者是從 session 查無 EditOnly 標記，從資料庫查詢
        $user = $this->userModel->find($userId);
        if (!$user) return false;

        return (bool) ($user['u_is_edit_only'] ?? false);
    }

    /**
     * 檢查指定使用者是否可以編輯資料
     * @param int|null $userId
     * @return bool
     */
    public function canEditData($userId = null): bool
    {
        // 唯讀使用者不能編輯，其餘(一般、管理員、只能編輯)都可以編輯
        return !$this->isReadonlyUser($userId);
    }

    /**
     * 檢查指定使用者是否可以新增資料
     * @param int|null $userId
     * @return bool
     */
    public function canCreateData($userId = null): bool
    {
        // 唯讀與「只能編輯」權限者，都不能新增
        if ($this->isReadonlyUser($userId) || $this->isEditOnlyUser($userId)) {
            return false;
        }

        // 一般使用者可以新增
        return true;
    }

    /**
     * 檢查指定使用者是否可以刪除資料
     * @param int|null $userId
     * @return bool
     */
    public function canDeleteData($userId = null): bool
    {
        // 唯讀與「只能編輯」權限者，都不能刪除
        if ($this->isReadonlyUser($userId) || $this->isEditOnlyUser($userId)) {
            return false;
        }

        // 一般使用者可以刪除
        return true;
    }

    /**
     * 檢查指定使用者是否為可以使用後台
     * @param int|null $userId
     * @return bool
     */
    public function isAdmin($userId = null): bool
    {
        if ($userId === null) {
            $userId = session()->get('userId');
            if (!$userId) return false;

            // 從 session 取得管理員狀態（優先）
            $isAdmin = session()->get('isAdmin');
            if ($isAdmin !== null) {
                return (bool) $isAdmin;
            }
        }

        // 如果 session 中沒有或手動帶入 ID，從資料庫查詢
        $user = $this->userModel->find($userId);
        return $user ? (bool) ($user['u_is_admin'] ?? false) : false;
    }

    /**
     * 驗證編輯權限
     * @param bool $throwException 是否拋出例外
     * @param int|null $userId
     * @return array|bool
     */
    public function validateEditPermission($throwException = false, $userId = null)
    {
        if (!$this->canEditData($userId)) {
            if ($throwException) {
                throw new \Exception('您的帳號權限不足，無法執行編輯操作。');
            }

            return [
                'status' => 'error',
                'message' => '您的帳號權限不足，無法執行編輯操作。',
                'statusCode' => 403
            ];
        }

        return $throwException ? true : [
            'status' => 'success',
            'message' => '權限驗證通過'
        ];
    }

    /**
     * 驗證新增權限
     * @param bool $throwException 是否拋出例外
     * @param int|null $userId
     * @return array|bool
     */
    public function validateCreatePermission($throwException = false, $userId = null)
    {
        if (!$this->canCreateData($userId)) {
            if ($throwException) {
                throw new \Exception('您的帳號權限不足，無法執行新增操作。');
            }

            return [
                'status' => 'error',
                'message' => '您的帳號權限不足，無法執行新增操作。',
                'statusCode' => 403
            ];
        }

        return $throwException ? true : [
            'status' => 'success',
            'message' => '權限驗證通過'
        ];
    }

    /**
     * 驗證刪除權限
     * @param bool $throwException 是否拋出例外
     * @param int|null $userId
     * @return array|bool
     */
    public function validateDeletePermission($throwException = false, $userId = null)
    {
        if (!$this->canDeleteData($userId)) {
            if ($throwException) {
                throw new \Exception('您的帳號權限不足，無法執行刪除操作。');
            }

            return [
                'status' => 'error',
                'message' => '您的帳號權限不足，無法執行刪除操作。',
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

        if ($user['u_is_edit_only']) {
            return '只能編輯(不新增刪除)';
        }

        return '一般使用者';
    }

    /**
     * 取得權限狀態陣列（用於前端顯示）
     * @param int|null $userId
     * @return array
     */
    public function getPermissionStatus($userId = null): array
    {
        return [
            'canCreate' => $this->canCreateData($userId),
            'canEdit' => $this->canEditData($userId),
            'canDelete' => $this->canDeleteData($userId),
            'isReadonly' => $this->isReadonlyUser($userId),
            'isEditOnly' => $this->isEditOnlyUser($userId),
            'isAdmin' => $this->isAdmin($userId),
            'permissionLevel' => $this->getUserPermissionLevel($userId)
        ];
    }
}
