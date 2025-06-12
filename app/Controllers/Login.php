<?php

namespace App\Controllers;

use App\Models\UserModel;

class Login extends BaseController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // 登入頁
    public function index()
    {
        $datas = $this->userModel->getUsersWithPosition();

        $formattedDatas = [];

        foreach ($datas as $data) {
            $formattedDatas[] = [
                'u_id' => $data['u_id'],
                'name' => $data['u_name'] . '-' . $data['p_name']
            ];
        }

        $data = [
            'users' => $formattedDatas
        ];

        return view('login', $data);
    }

    // 登入
    public function authenticate()
    {
        $rules = [
            'userId' => 'required',
            'password' => 'required'
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/')->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = $this->request->getPost('userId');
        $password = $this->request->getPost('password');
        $user = $this->userModel->find($userId);

        if ($user && password_verify($password, $user['u_password'])) {
            session()->set('userName', $user['u_name']);
            session()->set('userId', $user['u_id']);

            return view('home');
        } else {
            return redirect()->to('/')->withInput()->with('error', '使用者名稱或密碼錯誤。');
        }
    }

    // 登出
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
