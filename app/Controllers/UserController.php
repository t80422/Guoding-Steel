<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PositionModel;

class UserController extends BaseController
{
    private $userModel;
    private $positionModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->positionModel = new PositionModel();
    }

    // 列表
    public function index()
    {
        $keyword = $this->request->getGet('keyword');
        $data = $this->userModel->getList($keyword);
        return view('user/index', ['data' => $data]);
    }

    // 新增
    public function create()
    {
        $positions = $this->positionModel->findAll();
        return view('user/form', ['isEdit' => false, 'positions' => $positions]);
    }

    // 編輯
    public function edit($id)
    {
        $data = $this->userModel->find($id);
        $positions = $this->positionModel->findAll();
        return view('user/form', ['isEdit' => true, 'data' => $data, 'positions' => $positions]);
    }

    // 儲存
    public function save()
    {
        $isEdit = (bool)$this->request->getPost('u_id');

        $rules = [
            'u_name' => 'required',
            'u_p_id' => 'required',
        ];

        $messages = [
            'u_name' => [
                'required' => '使用者名稱為必填項。',
            ],
            'u_p_id' => [
                'required' => '職位為必填項。',
            ],
        ];

        // 根據是新增還是編輯來設定密碼驗證規則
        $password = $this->request->getPost('u_password');

        if (!$isEdit) {
            // 新增時，密碼和確認密碼為必填，且密碼需符合確認密碼
            $rules['u_password'] = 'required|matches[u_confirm_password]';
            $rules['u_confirm_password'] = 'required';
            $messages['u_password'] = [
                'required' => '密碼為必填項。',
                'matches' => '密碼與確認密碼不符。',
            ];
            $messages['u_confirm_password'] = [
                'required' => '確認密碼為必填項。',
            ];
        } else {
            // 編輯時，如果提供了密碼，則驗證密碼和確認密碼
            if (!empty($password)) {
                $rules['u_password'] = 'matches[u_confirm_password]';
                $rules['u_confirm_password'] = 'required';
                $messages['u_password'] = [
                    'matches' => '密碼與確認密碼不符。',
                ];
                $messages['u_confirm_password'] = [
                    'required' => '確認密碼為必填項。',
                ];
            }
        }

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'u_name' => $this->request->getPost('u_name'),
            'u_p_id' => $this->request->getPost('u_p_id'),
        ];

        // 只有在提供了密碼時才更新密碼
        if (!empty($password)) {
            $data['u_password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userId = session()->get('userId');

        if (!$userId) {
            return redirect()->to(url_to('AuthController::index'))
                ->with('error', '請先登入！');
        }

        if ($isEdit) {
            $data['u_id'] = $this->request->getPost('u_id');
            $data['u_update_by'] = $userId;
            $data['u_update_at'] = date('Y-m-d H:i:s');
        }

        $this->userModel->save($data);

        return redirect()->to('user');
    }

    // 刪除
    public function delete($id)
    {
        $this->userModel->delete($id);
        return redirect()->to('user');
    }
}
