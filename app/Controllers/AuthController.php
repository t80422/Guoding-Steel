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
        return view('login');
    }

    // 登入
    public function login()
    {
        $userId = 1;
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
