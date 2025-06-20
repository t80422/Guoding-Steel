<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController extends BaseController
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    // 登入頁
    public function index()
    {
        $users = $this->authService->getUsersDropdown();
        return view('login', ['users' => $users]);
    }

    // 登入
    public function login()
    {
        $userId = $this->request->getPost('userId');
        $password = $this->request->getPost('password');

        $result = $this->authService->validateAndAuthenticate($userId, $password);

        if ($result['status'] === 'success') {
            $sessionSet = $this->authService->setUserSession($result['data']);

            if ($sessionSet) {
                return view('home');
            } else {
                return redirect()->to('auth')->withInput()->with('error', '系統發生錯誤');
            }
        } else {
            return redirect()->to('auth')->withInput()->with('error', $result['message']);
        }
    }

    // 登出
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }

    // 登入登出紀錄
    public function authLogs()
    {
        $keyword = $this->request->getGet('keyword');
        $data = $this->authService->getAuthLogs($keyword);
        return view('auth_logs', ['data' => $data]);
    }
}
