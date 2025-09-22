<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Models\UserModel;

class AuthController extends BaseController
{
    protected $authService;
    protected $userModel;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->userModel = new UserModel();
    }

    // 登入頁
    public function index()
    {
        $userDropdown = $this->userModel->getDropdownByIsAdmin();
        return view('login', [
            'users' => $userDropdown
        ]);
    }

    // 登入
    public function login()
    {
        $data = $this->request->getPost();
        $result = $this->authService->validateAndAuthenticate($data['u_id'], $data['password']);
        $this->authService->recordLoginTime($data['u_id']);

        if ($result['status'] === 'success') {
            $sessionSet = $this->authService->setUserSession($result['data']);

            if ($sessionSet) {
                return view('home');
            } else {
                return redirect()->to('/')->withInput()->with('error', '系統發生錯誤');
            }
        } else {
            return redirect()->to('/')->withInput()->with('error', $result['message']);
        }
    }

    // 登出
    public function logout()
    {
        $this->authService->recordLogoutTime(session()->get('userId'));
        session()->destroy();
        return redirect()->to('/');
    }

    // 登入登出紀錄
    public function authLogs()
    {
        $keyword = $this->request->getGet('keyword');
        $page = $this->request->getGet('page') ?? 1;
        $data = $this->authService->getAuthLogs($keyword, $page);
        $pagerData = [
            'currentPage' => $data['currentPage'],
            'totalPages' => $data['totalPages']
        ];
        return view('auth_logs', [
            'data' => $data['data'],
            'pager' => $pagerData,
            'keyword' => $keyword
        ]);
    }
}
